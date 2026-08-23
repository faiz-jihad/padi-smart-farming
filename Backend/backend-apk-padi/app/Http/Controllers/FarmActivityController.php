<?php

namespace App\Http\Controllers;

use App\Domain\FarmActivity\Actions\CreateFarmActivityAction;
use App\Helpers\ApiResponse;
use App\Http\Requests\Api\V1\FarmActivity\StoreFarmActivityRequest;
use App\Http\Resources\FarmActivityResource;
use App\Services\Api\ApiResourceIndexService;
use Illuminate\Http\JsonResponse;

class FarmActivityController extends Controller
{
    public function index(ApiResourceIndexService $resources): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Data aktivitas lahan berhasil diambil.',
            'data' => [
                'farm_activities' => FarmActivityResource::collection(
                    $resources->farmActivities()
                ),
            ],
        ]);
    }

    public function store(
        StoreFarmActivityRequest $request,
        CreateFarmActivityAction $action
    ): JsonResponse {
        $farmActivity = $action->execute(
            $request->user(),
            $request->validated()
        );

        return ApiResponse::success(
            'Aktivitas lahan berhasil ditambahkan.',
            [
                'farm_activity' => FarmActivityResource::make($farmActivity),
            ],
            201,
        );
    }
}
