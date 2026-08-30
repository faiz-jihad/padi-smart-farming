<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Farm;
use App\Models\IrrigationSchedule;
use App\Services\Irrigation\IrrigationComparisonService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IrrigationScheduleController extends Controller
{
    public function __construct(
        protected ?IrrigationComparisonService $comparisonService = null
    ) {
        $this->comparisonService = $comparisonService ?? app(IrrigationComparisonService::class);
    }

    public function index(Request $request, int $farm): JsonResponse
    {
        $farmModel = Farm::findOrFail($farm);

        $schedules = IrrigationSchedule::where('farm_id', $farmModel->id)
            ->orderBy('schedule_date')
            ->orderBy('start_time')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $schedules,
        ]);
    }

    public function store(Request $request, int $farm): JsonResponse
    {
        $farmModel = Farm::findOrFail($farm);

        $validated = $request->validate([
            'schedule_date' => ['required', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'source' => ['nullable', 'string', 'in:manual,raksa_bumi,officer,system'],
            'officer_name' => ['nullable', 'string', 'max:100'],
            'irrigation_block' => ['nullable', 'string', 'max:100'],
            'water_source' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $schedule = IrrigationSchedule::create([
            'farm_id' => $farmModel->id,
            'schedule_date' => $validated['schedule_date'],
            'start_time' => $validated['start_time'] ?? null,
            'end_time' => $validated['end_time'] ?? null,
            'status' => 'scheduled',
            'source' => $validated['source'] ?? 'manual',
            'officer_name' => $validated['officer_name'] ?? null,
            'irrigation_block' => $validated['irrigation_block'] ?? null,
            'water_source' => $validated['water_source'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Jadwal irigasi berhasil dibuat',
            'data' => $schedule,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $schedule = IrrigationSchedule::findOrFail($id);

        $validated = $request->validate([
            'schedule_date' => ['sometimes', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'status' => ['sometimes', 'in:scheduled,completed,cancelled'],
            'source' => ['sometimes', 'string', 'in:manual,raksa_bumi,officer,system'],
            'officer_name' => ['nullable', 'string', 'max:100'],
            'irrigation_block' => ['nullable', 'string', 'max:100'],
            'water_source' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
        ]);

        $schedule->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Jadwal irigasi berhasil diperbarui',
            'data' => $schedule->fresh(),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $schedule = IrrigationSchedule::findOrFail($id);

        $schedule->delete();

        return response()->json([
            'success' => true,
            'message' => 'Jadwal irigasi berhasil dihapus',
        ]);
    }

    /**
     * Endpoint Analisis Komparasi 3 Sumber Informasi Irigasi
     * (Rekomendasi Sistem vs Jadwal Lapangan vs Data Resmi PU/WRDC)
     */
    public function comparison(Request $request, int $farm): JsonResponse
    {
        $farmModel = Farm::findOrFail($farm);

        $result = $this->comparisonService->compareForFarm($farmModel);

        return response()->json($result);
    }
}