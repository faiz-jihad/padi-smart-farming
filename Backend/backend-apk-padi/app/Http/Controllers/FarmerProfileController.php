<?php

namespace App\Http\Controllers;

use App\Http\Resources\FarmerProfileResource;
use App\Services\FarmerProfileService;
use Illuminate\Http\JsonResponse;

class FarmerProfileController extends Controller
{
    public function index(
        FarmerProfileService $service
    ): JsonResponse {
        $farmers = $service->getFarmers();

        return response()->json([
            'success' => true,
            'message' => 'Data farmer berhasil diambil.',
            'data' => [
                'farmers' => FarmerProfileResource::collection($farmers),
            ],
        ]);
    }
}