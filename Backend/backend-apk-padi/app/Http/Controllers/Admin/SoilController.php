<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSoilRequest;
use App\Http\Requests\Admin\StoreSoilTypeRequest;
use App\Models\Farm;
use App\Models\IrrigationSchedule;
use App\Models\SoilDetection;
use App\Services\Admin\AdminSoilService;
use App\Models\SoilType;
use App\Services\Admin\AdminAuditLogger;
use App\Services\Admin\AdminNotificationService;
use App\Services\Admin\AdminSoilService;
use App\Services\Soil\SoilDetectionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SoilController extends Controller
{
    public function __construct(
        private AdminSoilService $adminSoilService
    ) {}

    /**
     * Display listing of soil detections & dashboard
     */
    public function index(Request $request): View
    {
        return view('admin.soil.index', $this->adminSoilService->indexData($request));
    }

    /**
     * Show form for creating new soil detection sample
     */
    public function create(): View
    {
        return view('admin.soil.create', [
            'farms' => Farm::with('farmer')->orderBy('name')->get(),
            'soilTypes' => SoilType::active()->orderBy('name')->get(),
        ]);
    }

    /**
     * Get list of active soil types as JSON
     */
    public function getSoilTypes(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => SoilType::active()->orderBy('name')->get(),
        ]);
    }

    /**
     * Store a new master soil type via AJAX
     */
    public function storeSoilType(StoreSoilTypeRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();

            $name = trim($validated['name']);
            $code = ! empty($validated['code'])
                ? Str::slug($validated['code'], '_')
                : Str::slug($name, '_');

            if (SoilType::where('code', $code)->exists() && empty($validated['code'])) {
                $code .= '_' . Str::lower(Str::random(4));
            }

            $soilType = SoilType::create([
                'name' => $name,
                'code' => $code,
                'description' => $validated['description'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
                'created_by' => auth()->id(),
            ]);

            $this->auditLogger->write('admin_soil_type_created', $soilType, null, $soilType->toArray(), $request);

            return response()->json([
                'success' => true,
                'message' => "Jenis tanah '{$soilType->name}' berhasil ditambahkan ke master data.",
                'data' => $soilType,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan jenis tanah: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a new soil detection sample and run evaluation
     */
    public function store(StoreSoilRequest $request): RedirectResponse
    {
        try {
            $soil = $this->adminSoilService->createSoilDetection(
                $request->validated(),
                auth()->id(),
                $request
            );

            return redirect()
                ->route('admin.soil.show', $soil)
                ->with('status', "Sampel tanah {$soil->sample_code} berhasil dianalisis dengan Skor Kesehatan {$soil->soil_health_score}/100!");
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Gagal memproses data tanah: ' . $e->getMessage());
        }
    }

    /**
     * Display detailed soil analysis report
     */
    public function show(SoilDetection $soil): View
    {
        return view('admin.soil.show', $this->adminSoilService->showData($soil));
    }

    /**
     * Download soil analysis report as PDF
     */
    public function downloadReport(SoilDetection $soil)
    {
        return $this->adminSoilService->generateReportPdf($soil);
    }

    /**
     * Delete a soil detection record
     */
    public function destroy(Request $request, SoilDetection $soil): RedirectResponse
    {
        try {
            $code = $soil->sample_code;
            $this->adminSoilService->deleteSoilDetection($soil, auth()->id(), $request);

            return redirect()
                ->route('admin.soil.index')
                ->with('status', "Data sampel tanah {$code} telah dihapus.");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    /**
     * Export soil detection data to CSV or JSON
     */
    public function export(Request $request)
    {
        $validated = $request->validate([
            'farm_id' => 'nullable|integer|exists:farms,id',
            'status' => 'nullable|string',
            'format' => 'nullable|in:csv,json',
        ]);

        return $this->adminSoilService->exportSoilData($validated);
    }

    /**
     * Store manual/field irrigation schedule from Admin Soil detail page
     */
    public function storeIrrigationSchedule(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'farm_id' => ['required', 'integer', 'exists:farms,id'],
            'soil_detection_id' => ['nullable'],
            'soil_id' => ['nullable'],
            'schedule_date' => ['required', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'source' => ['required', 'string', 'in:manual,raksa_bumi,officer,system'],
            'officer_name' => ['nullable', 'string', 'max:100'],
            'irrigation_block' => ['nullable', 'string', 'max:100'],
            'water_source' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $this->adminSoilService->storeIrrigationSchedule($validated, $request);

        $soilIdentifier = $request->input('soil_detection_id') ?? $request->input('soil_id');
        $soil = $this->adminSoilService->resolveSoilDetection($soilIdentifier);

        $redirect = $soil
            ? redirect()->route('admin.soil.show', $soil)
            : back();

        return $redirect->with('status', 'Jadwal irigasi lapangan berhasil disimpan dan analisis komparasi telah diperbarui!');
    }

    /**
     * Update irrigation schedule from Admin Soil detail page
     */
    public function updateIrrigationSchedule(Request $request, IrrigationSchedule $schedule): RedirectResponse
    {
        $validated = $request->validate([
            'soil_detection_id' => ['nullable'],
            'soil_id' => ['nullable'],
            'schedule_date' => ['sometimes', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'status' => ['sometimes', 'in:scheduled,completed,cancelled'],
            'source' => ['sometimes', 'string', 'in:manual,raksa_bumi,officer,system'],
            'officer_name' => ['nullable', 'string', 'max:100'],
            'irrigation_block' => ['nullable', 'string', 'max:100'],
            'water_source' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $this->adminSoilService->updateIrrigationSchedule($schedule, $validated, $request);

        $soilIdentifier = $request->input('soil_detection_id') ?? $request->input('soil_id');
        $soil = $this->adminSoilService->resolveSoilDetection($soilIdentifier);

        $redirect = $soil
            ? redirect()->route('admin.soil.show', $soil)
            : back();

        return $redirect->with('status', 'Jadwal irigasi berhasil diperbarui!');
    }

    /**
     * Delete irrigation schedule from Admin Soil detail page
     */
    public function destroyIrrigationSchedule(Request $request, IrrigationSchedule $schedule): RedirectResponse
    {
        $this->adminSoilService->deleteIrrigationSchedule($schedule, $request);

        $soilIdentifier = $request->input('soil_detection_id') ?? $request->input('soil_id');
        $soil = $this->adminSoilService->resolveSoilDetection($soilIdentifier);

        $redirect = $soil
            ? redirect()->route('admin.soil.show', $soil)
            : back();

        return $redirect->with('status', 'Jadwal irigasi lapangan berhasil dihapus.');
    }
}
