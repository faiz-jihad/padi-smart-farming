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

        $farm = \App\Models\Farm::findOrFail($validated['farm_id']);
        $this->authorizeFarm($request->user(), $farm);

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
        $farm = \App\Models\Farm::findOrFail($request->validated('farm_id'));
        $this->authorizeFarm($request->user(), $farm);

        $soil = $this->soilDetectionService->analyzeAndCreate(
            $request->validated(),
            $request->user()->id
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
    public function show(Request $request, string $soilDetection): JsonResponse
    {
        $model = SoilDetection::where('sample_code', $soilDetection)
            ->orWhere('id', $soilDetection)
            ->firstOrFail();

        $model->load('farm.farmer');
        $this->authorizeFarm($request->user(), $model->farm);

        $irrigationSchedule = $this->soilDetectionService->calculateIrrigationSchedule(
            (float) $model->moisture_percentage,
            $model->soil_temp_celsius ? (float) $model->soil_temp_celsius : null,
            $model->farm_id
        );

        $comparisonResult = null;
        if ($model->farm) {
            $comparisonService = app(\App\Services\Irrigation\IrrigationComparisonService::class);
            $comparisonResult = $comparisonService->compareForFarm($model->farm, $model);
        }

        return response()->json([
            'success' => true,
            'data' => $model,
            'irrigation_schedule' => $irrigationSchedule,
            'field_schedule' => $comparisonResult['field_schedule'] ?? null,
            'official_context' => $comparisonResult['official_context'] ?? null,
            'comparison' => $comparisonResult['comparison'] ?? null,
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
        $this->authorizeFarm($request->user(), $farm);

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
    public function irrigationSchedule(Request $request, string $soilDetection): JsonResponse
    {
        $model = SoilDetection::where('sample_code', $soilDetection)
            ->orWhere('id', $soilDetection)
            ->firstOrFail();

        $model->loadMissing('farm');
        $this->authorizeFarm($request->user(), $model->farm);

        $schedule = $this->soilDetectionService->calculateIrrigationSchedule(
            (float) $model->moisture_percentage,
            $model->soil_temp_celsius ? (float) $model->soil_temp_celsius : null,
            $model->farm_id
        );

        $comparisonResult = null;
        if ($model->farm) {
            $comparisonService = app(\App\Services\Irrigation\IrrigationComparisonService::class);
            $comparisonResult = $comparisonService->compareForFarm($model->farm, $model);
        }

        return response()->json([
            'success' => true,
            'message' => 'Jadwal irigasi padi berhasil dihitung',
            'sample_code' => $model->sample_code,
            'farm' => $model->farm?->name,
            'irrigation_schedule' => $schedule,
            'field_schedule' => $comparisonResult['field_schedule'] ?? null,
            'official_context' => $comparisonResult['official_context'] ?? null,
            'comparison' => $comparisonResult['comparison'] ?? null,
        ]);
    }

    private function authorizeFarm($user, \App\Models\Farm $farm): void
    {
        $isAdmin = $user && ($user->role === 'admin' || (method_exists($user, 'hasRole') && $user->hasRole('admin')));
        $isOfficer = $user && ($user->role === 'extension_officer' || (method_exists($user, 'hasRole') && $user->hasRole('extension_officer')));

        if (! $isAdmin && ! $isOfficer && (! $user || $farm->farmer_user_id !== $user->id)) {
            abort(403, 'Anda tidak memiliki akses ke data lahan ini.');
        }
    }
}
