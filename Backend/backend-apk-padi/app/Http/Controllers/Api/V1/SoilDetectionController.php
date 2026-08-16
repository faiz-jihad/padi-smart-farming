<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSoilRequest;
use App\Models\SoilDetection;
use App\Services\Soil\SoilDetectionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SoilDetectionController extends Controller
{
    public function __construct(
        private SoilDetectionService $soilDetectionService
    ) {}

    /**
     * Get list of soil detections (filtered by farm_id)
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'farm_id' => 'required|integer|exists:farms,id',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $detections = SoilDetection::where('farm_id', $validated['farm_id'])
            ->latest('tested_at')
            ->limit($validated['limit'] ?? 20)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Data deteksi tanah berhasil diambil',
            'data' => $detections,
        ]);
    }

    /**
     * Submit soil sample / IoT sensor reading for instant evaluation
     */
    public function store(StoreSoilRequest $request): JsonResponse
    {
        $soil = $this->soilDetectionService->analyzeAndCreate(
            $request->validated(),
            auth()->id()
        );

        return response()->json([
            'success' => true,
            'message' => 'Analisis sampel tanah berhasil diproses',
            'data' => $soil,
        ], 201);
    }

    /**
     * Get detail of a specific soil detection
     */
    public function show(SoilDetection $soilDetection): JsonResponse
    {
        $soilDetection->load('farm.farmer');

        return response()->json([
            'success' => true,
            'data' => $soilDetection,
        ]);
    }
}
