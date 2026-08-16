<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Resources\FarmActivityResource;
use App\Services\FarmActivityService;
use Illuminate\Http\JsonResponse;

class FarmActivityController extends Controller
{
    public function index(FarmActivityService $service): JsonResponse
    {
        $activities = $service->getActivities();

        return ApiResponse::success(
            'Data aktivitas lahan berhasil diambil.',
            [
                'farm_activities' => FarmActivityResource::collection($activities),
            ],
        );
    }
}