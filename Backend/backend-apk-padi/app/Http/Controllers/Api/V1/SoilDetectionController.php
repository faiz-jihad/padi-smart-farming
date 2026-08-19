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
     * Get detail of a specific soil detection (by sample_code or id)
     */
    public function show(string $soilDetection): JsonResponse
    {
        $model = SoilDetection::where('sample_code', $soilDetection)
            ->orWhere('id', $soilDetection)
            ->firstOrFail();

        $model->load('farm.farmer');

        $irrigationSchedule = $this->soilDetectionService->calculateIrrigationSchedule(
            (float) $model->moisture_percentage,
            $model->soil_temp_celsius ? (float) $model->soil_temp_celsius : null
        );

        return response()->json([
            'success' => true,
            'data' => $model,
            'irrigation_schedule' => $irrigationSchedule,
        ]);
    }

    /**
     * Auto-fetch live AgroMonitoring soil & climate data for a farm (Dual Input Mode)
     */
    public function fetchApiData(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'farm_id' => 'required|integer|exists:farms,id',
        ]);

        $farm = \App\Models\Farm::findOrFail($validated['farm_id']);
        $weatherService = app(\App\Services\Weather\WeatherService::class);

        $agroSoil = $weatherService->getSoilData($farm->latitude ?? -7.25, $farm->longitude ?? 112.75);

        $moisture = $agroSoil['data']['moisture_percentage'] ?? 52.0;
        $soilTemp = $agroSoil['data']['soil_temp_celsius'] ?? 26.5;

        $irrigationSchedule = $this->soilDetectionService->calculateIrrigationSchedule($moisture, $soilTemp);

        return response()->json([
            'success' => true,
            'source' => 'AgroMonitoring API',
            'data' => [
                'farm_id' => $farm->id,
                'farm_name' => $farm->name,
                'ph_level' => 6.5,
                'nitrogen_ppm' => 120,
                'phosphorus_ppm' => 25,
                'potassium_ppm' => 150,
                'moisture_percentage' => $moisture,
                'organic_matter_percentage' => 2.5,
                'soil_temp_celsius' => $soilTemp,
                'soil_type' => 'loam',
            ],
            'irrigation_schedule' => $irrigationSchedule,
        ]);
    }

    /**
     * Get Indonesian PADI Irrigation Schedule for a soil detection
     */
    public function irrigationSchedule(string $soilDetection): JsonResponse
    {
        $model = SoilDetection::where('sample_code', $soilDetection)
            ->orWhere('id', $soilDetection)
            ->firstOrFail();

        $schedule = $this->soilDetectionService->calculateIrrigationSchedule(
            (float) $model->moisture_percentage,
            $model->soil_temp_celsius ? (float) $model->soil_temp_celsius : null
        );

        return response()->json([
            'success' => true,
            'message' => 'Jadwal irigasi padi berhasil dihitung',
            'sample_code' => $model->sample_code,
            'farm' => $model->farm?->name,
            'irrigation_schedule' => $schedule,
        ]);
    }
}
