<?php

namespace App\Http\Controllers;

use App\Http\Resources\FarmerProfileResource;
use App\Services\Api\ApiResourceIndexService;
use Illuminate\Http\JsonResponse;

class FarmerProfileController extends Controller
{
    public function index(ApiResourceIndexService $resources): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Data farmer berhasil diambil.',
            'data' => [
                'farmers' => FarmerProfileResource::collection($resources->farmerProfiles()),
            ],
        ]);
    }
}
