<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\FarmActivity\StoreFarmActivityRequest;
use App\Http\Requests\Api\V1\FarmActivity\UpdateFarmActivityRequest;
use App\Http\Resources\FarmActivityResource;
use App\Models\FarmActivity;
use App\Services\FarmActivityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FarmActivityController extends Controller
{
    public function index(
        Request $request,
        FarmActivityService $farmActivities
    ): JsonResponse {
        $activities = $farmActivities->getActivities($request->user());

        if ($request->filled('crop_season_id')) {
            $activities = $activities->where(
                'crop_season_id',
                $request->integer('crop_season_id')
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Daftar aktivitas pertanian berhasil diambil',
            'data' => FarmActivityResource::collection($activities),
        ]);
    }

    public function store(
        StoreFarmActivityRequest $request,
        FarmActivityService $farmActivities
    ): JsonResponse {
        $activity = $farmActivities->createActivity(
            $request->user(),
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Aktivitas pertanian berhasil ditambahkan',
            'data' => new FarmActivityResource($activity),
        ], 201);
    }

    public function show(
        Request $request,
        FarmActivity $farmActivity,
        FarmActivityService $farmActivities
    ): JsonResponse {
        $activity = $farmActivities->getActivity(
            $request->user(),
            $farmActivity
        );

        return response()->json([
            'success' => true,
            'message' => 'Detail aktivitas pertanian berhasil diambil',
            'data' => new FarmActivityResource($activity),
        ]);
    }

    public function update(
        UpdateFarmActivityRequest $request,
        FarmActivity $farmActivity,
        FarmActivityService $farmActivities
    ): JsonResponse {
        $activity = $farmActivities->updateActivity(
            $request->user(),
            $farmActivity,
            $request->validated()
        );

        return response()->json([
            'success' => true,
            'message' => 'Aktivitas pertanian berhasil diperbarui',
            'data' => new FarmActivityResource($activity),
        ]);
    }

    public function destroy(
        Request $request,
        FarmActivity $farmActivity,
        FarmActivityService $farmActivities
    ): JsonResponse {
        $farmActivities->deleteActivity(
            $request->user(),
            $farmActivity
        );

        return response()->json([
            'success' => true,
            'message' => 'Aktivitas pertanian berhasil dihapus',
            'data' => null,
        ]);
    }
}