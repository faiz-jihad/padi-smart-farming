<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Farm\StoreFarmRequest;
use App\Http\Requests\Api\V1\Farm\UpdateFarmRequest;
use App\Http\Resources\FarmResource;
use App\Models\AlertSubscription;
use App\Models\CropSeason;
use App\Models\DiseaseScan;
use App\Models\Farm;
use App\Models\MarketListing;
use App\Services\Geography\LocationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FarmController extends Controller
{
    public function __construct(
        private LocationService $locationService
    ) {}

    /**
     * List current user's farms
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $farms = Farm::query()
            ->when(! $user->hasRole('admin'), function ($query) use ($user): void {
                $query->where('farmer_user_id', $user->id);
            })
            ->with(['province', 'regency', 'district', 'village'])
            ->latest('id')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar lahan berhasil diambil',
            'data'    => FarmResource::collection($farms),
        ]);
    }

    /**
     * Store a new farm with auto-resolving region from GPS if not provided
     */
    public function store(StoreFarmRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $data['farmer_user_id'] = $user->id;

        // Auto-resolve region if not explicitly provided
        if (empty($data['district_id']) && empty($data['village_id'])) {
            $resolved = $this->locationService->resolveCoordinates(
                (float) $data['latitude'],
                (float) $data['longitude']
            );

            if ($resolved) {
                $data['province_id'] = $resolved['province']['id'] ?? $data['province_id'] ?? null;
                $data['regency_id']  = $resolved['regency']['id'] ?? $data['regency_id'] ?? null;
                $data['district_id'] = $resolved['district']['id'] ?? null;
                $data['village_id']  = $resolved['village']['id'] ?? null;
            }
        }

        $farm = Farm::create($data);
        $farm->load(['province', 'regency', 'district', 'village']);

        return response()->json([
            'success' => true,
            'message' => 'Lahan berhasil didaftarkan',
            'data'    => new FarmResource($farm),
        ], 201);
    }

    /**
     * Show farm detail
     */
    public function show(Request $request, Farm $farm): JsonResponse
    {
        $this->authorizeFarm($request->user(), $farm);

        $farm->load(['province', 'regency', 'district', 'village']);

        return response()->json([
            'success' => true,
            'message' => 'Detail lahan berhasil diambil',
            'data'    => new FarmResource($farm),
        ]);
    }

    /**
     * Update farm
     */
    public function update(UpdateFarmRequest $request, Farm $farm): JsonResponse
    {
        $this->authorizeFarm($request->user(), $farm);

        $data = $request->validated();

        // If coordinates changed and region not explicitly set, auto-resolve again
        if ((isset($data['latitude']) || isset($data['longitude'])) && empty($data['district_id'])) {
            $lat = $data['latitude'] ?? $farm->latitude;
            $lng = $data['longitude'] ?? $farm->longitude;

            $resolved = $this->locationService->resolveCoordinates((float) $lat, (float) $lng);
            if ($resolved) {
                $data['province_id'] = $resolved['province']['id'] ?? $farm->province_id;
                $data['regency_id']  = $resolved['regency']['id'] ?? $farm->regency_id;
                $data['district_id'] = $resolved['district']['id'] ?? null;
                $data['village_id']  = $resolved['village']['id'] ?? null;
            }
        }

        $farm->update($data);
        $farm->load(['province', 'regency', 'district', 'village']);

        return response()->json([
            'success' => true,
            'message' => 'Data lahan berhasil diperbarui',
            'data'    => new FarmResource($farm),
        ]);
    }

    /**
     * Delete farm
     */
    public function destroy(Request $request, Farm $farm): JsonResponse
    {
        $this->authorizeFarm($request->user(), $farm);

        DB::transaction(function () use ($farm) {
            // Bersihkan atau lepaskan relasi terkait agar tidak melanggar foreign key constraint
            $farm->irrigationSchedules()->delete();
            $farm->soilDetections()->delete();
            $farm->weatherSnapshots()->delete();
            AlertSubscription::where('farm_id', $farm->id)->delete();
            $farm->cropSeasons()->delete();
            DiseaseScan::where('farm_id', $farm->id)->update(['farm_id' => null]);
            MarketListing::where('farm_id', $farm->id)->update(['farm_id' => null]);

            $farm->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Lahan berhasil dihapus',
            'data'    => null,
        ]);
    }

    private function authorizeFarm($user, Farm $farm): void
    {
        if ($farm->farmer_user_id !== $user->id && !$user->hasRole('admin')) {
            abort(403, 'Anda tidak memiliki akses ke data lahan ini');
        }
    }
}
