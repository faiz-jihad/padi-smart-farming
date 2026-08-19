<?php

namespace App\Http\Controllers;

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
                'farm_activities' => FarmActivityResource::collection($resources->farmActivities()),
            ],
        ]);
    }
}
