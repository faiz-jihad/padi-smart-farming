<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\FarmActivity\StoreFarmActivityRequest;
use App\Http\Requests\Api\V1\FarmActivity\UpdateFarmActivityRequest;
use App\Http\Resources\FarmActivityResource;
use App\Models\CropSeason;
use App\Models\FarmActivity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FarmActivityController extends Controller
{
    /**
     * List current user's farm activities (with optional crop_season_id filter)
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = FarmActivity::query()
            ->whereHas('cropSeason.farm', function ($q) use ($user): void {
                if (! $user->hasRole('admin')) {
                    $q->where('farmer_user_id', $user->id);
                }
            })
            ->with(['cropSeason.farm'])
            ->latest('occurred_at');

        if ($request->filled('crop_season_id')) {
            $query->where('crop_season_id', $request->integer('crop_season_id'));
        }

        $activities = $query->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar aktivitas pertanian berhasil diambil',
            'data'    => FarmActivityResource::collection($activities),
        ]);
    }

    /**
     * Store a new farm activity
     */
    public function store(StoreFarmActivityRequest $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validated();

        $this->authorizeCropSeason($user, (int) $data['crop_season_id']);

        $activity = FarmActivity::create($data);
        $activity->load(['cropSeason.farm']);

        return response()->json([
            'success' => true,
            'message' => 'Aktivitas pertanian berhasil ditambahkan',
            'data'    => new FarmActivityResource($activity),
        ], 201);
    }

    /**
     * Show farm activity detail
     */
    public function show(Request $request, FarmActivity $farmActivity): JsonResponse
    {
        $this->authorizeActivity($request->user(), $farmActivity);

        $farmActivity->load(['cropSeason.farm']);

        return response()->json([
            'success' => true,
            'message' => 'Detail aktivitas pertanian berhasil diambil',
            'data'    => new FarmActivityResource($farmActivity),
        ]);
    }

    /**
     * Update farm activity
     */
    public function update(UpdateFarmActivityRequest $request, FarmActivity $farmActivity): JsonResponse
    {
        $user = $request->user();
        $this->authorizeActivity($user, $farmActivity);

        $data = $request->validated();

        if (isset($data['crop_season_id']) && (int) $data['crop_season_id'] !== $farmActivity->crop_season_id) {
            $this->authorizeCropSeason($user, (int) $data['crop_season_id']);
        }

        $farmActivity->update($data);
        $farmActivity->load(['cropSeason.farm']);

        return response()->json([
            'success' => true,
            'message' => 'Aktivitas pertanian berhasil diperbarui',
            'data'    => new FarmActivityResource($farmActivity),
        ]);
    }

    /**
     * Delete farm activity
     */
    public function destroy(Request $request, FarmActivity $farmActivity): JsonResponse
    {
        $this->authorizeActivity($request->user(), $farmActivity);

        $farmActivity->delete();

        return response()->json([
            'success' => true,
            'message' => 'Aktivitas pertanian berhasil dihapus',
            'data'    => null,
        ]);
    }

    private function authorizeActivity($user, FarmActivity $farmActivity): void
    {
        $farmActivity->loadMissing('cropSeason.farm');
        $farmerUserId = $farmActivity->cropSeason?->farm?->farmer_user_id;

        if ($farmerUserId !== $user->id && ! $user->hasRole('admin')) {
            abort(403, 'Anda tidak memiliki akses ke aktivitas pertanian ini');
        }
    }

    private function authorizeCropSeason($user, int $cropSeasonId): CropSeason
    {
        $cropSeason = CropSeason::with('farm')->findOrFail($cropSeasonId);
        $farmerUserId = $cropSeason->farm?->farmer_user_id;

        if ($farmerUserId !== $user->id && ! $user->hasRole('admin')) {
            abort(403, 'Anda tidak memiliki akses ke musim tanam ini');
        }

        return $cropSeason;
    }
}
