<?php

namespace App\Http\Controllers;

use App\Http\Resources\FarmActivityResource;
use App\Models\FarmActivity;
use Illuminate\Http\JsonResponse;

class FarmActivityController extends Controller
{
    public function index(): JsonResponse
    {
        $activities = FarmActivity::query()
            ->latest('occurred_at')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data aktivitas lahan berhasil diambil.',
            'data' => [
                'farm_activities' => FarmActivityResource::collection($activities),
            ],
        ]);
    }
}