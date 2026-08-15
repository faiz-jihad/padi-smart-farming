<?php

namespace App\Http\Controllers;

use App\Http\Resources\FarmerProfileResource;
use App\Models\FarmerProfile;
use Illuminate\Http\JsonResponse;

class FarmerProfileController extends Controller
{
    public function index(): JsonResponse
    {
        $farmers = FarmerProfile::query()
            ->with('user')
            ->latest('id')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data farmer berhasil diambil.',
            'data' => [
                'farmers' => FarmerProfileResource::collection($farmers),
            ],
        ]);
    }
}