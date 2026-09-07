<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SoilType;
use Illuminate\Http\JsonResponse;

class SoilTypeController extends Controller
{
    /**
     * Get list of active soil types
     */
    public function index(): JsonResponse
    {
        $soilTypes = SoilType::active()
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar jenis tanah berhasil diambil',
            'data' => $soilTypes,
        ]);
    }
}
