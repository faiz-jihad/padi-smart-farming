<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Farm\StoreFarmRequest;
use App\Http\Requests\Api\V1\Farm\UpdateFarmRequest;
use App\Http\Resources\FarmResource;
use App\Models\Farm;
use App\Services\FarmService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FarmController extends Controller
{
    public function __construct(
        private FarmService $farmService
    ) {}

    /**
     * List current user's farms
     */
    public function index(Request $request): JsonResponse
    {
        $farms = $this->farmService->getFarms($request->user());

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
        $farm = $this->farmService->createFarm($request->user(), $request->validated());

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

        $farm = $this->farmService->updateFarm($farm, $request->validated());

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

        $this->farmService->deleteFarm($farm);

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
