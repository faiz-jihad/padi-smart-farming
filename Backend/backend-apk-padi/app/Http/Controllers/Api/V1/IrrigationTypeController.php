<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\IrrigationType;
use Illuminate\Http\JsonResponse;

class IrrigationTypeController extends Controller
{
    /**
     * Get list of active irrigation types
     */
    public function index(): JsonResponse
    {
        $irrigationTypes = IrrigationType::active()
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar tipe irigasi berhasil diambil',
            'data' => $irrigationTypes,
        ]);
    }
}
