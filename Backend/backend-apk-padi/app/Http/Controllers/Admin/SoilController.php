<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSoilRequest;
use App\Models\Farm;
use App\Models\SoilDetection;
use App\Services\Admin\AdminSoilService;
use App\Services\Soil\SoilDetectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;

class SoilController extends Controller
{
    public function __construct(
        private AdminSoilService $adminSoilService,
        private SoilDetectionService $soilDetectionService
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
        ]);
    }

    /**
     * Store a new soil detection sample and run evaluation
     */
    public function store(StoreSoilRequest $request): RedirectResponse
    {
        try {
            $soil = $this->soilDetectionService->analyzeAndCreate(
                $request->validated(),
                auth()->id()
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
    $soil->load([
        'farm.farmer',
        'creator',
    ]);

    // Ambil data yang sama seperti halaman detail
    $data = $this->adminSoilService->showData($soil);

    $irrigation = $this->soilDetectionService->calculateIrrigationSchedule(
        (float) $soil->moisture_percentage,
        $soil->soil_temp_celsius
            ? (float) $soil->soil_temp_celsius
            : null
    );

    $data['irrigation'] = $irrigation;

    $pdf = Pdf::loadView('admin.soil.report-pdf', $data);

    return $pdf->download(
        'Laporan-Tanah-' . $soil->sample_code . '.pdf'
    );
}

    /**
     * Delete a soil detection record
     */
    public function destroy(SoilDetection $soil): RedirectResponse
    {
        try {
            $code = $soil->sample_code;
            $this->adminSoilService->deleteSoilDetection($soil, auth()->id());

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

        \App\Models\IrrigationSchedule::create([
            'farm_id' => $validated['farm_id'],
            'schedule_date' => $validated['schedule_date'],
            'start_time' => $validated['start_time'] ?? null,
            'end_time' => $validated['end_time'] ?? null,
            'status' => 'scheduled',
            'source' => $validated['source'],
            'officer_name' => $validated['officer_name'] ?? null,
            'irrigation_block' => $validated['irrigation_block'] ?? null,
            'water_source' => $validated['water_source'] ?? null,
            'notes' => $validated['notes'] ?? null,
        ]);

        $soilIdentifier = $request->input('soil_detection_id') ?? $request->input('soil_id');
        $soil = $this->resolveSoilDetection($soilIdentifier);

        $redirect = $soil
            ? redirect()->route('admin.soil.show', $soil)
            : back();

        return $redirect->with('status', 'Jadwal irigasi lapangan berhasil disimpan dan analisis komparasi telah diperbarui!');
    }

    /**
     * Update irrigation schedule from Admin Soil detail page
     */
    public function updateIrrigationSchedule(Request $request, \App\Models\IrrigationSchedule $schedule): RedirectResponse
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

        $schedule->update($validated);

        $soilIdentifier = $request->input('soil_detection_id') ?? $request->input('soil_id');
        $soil = $this->resolveSoilDetection($soilIdentifier);

        $redirect = $soil
            ? redirect()->route('admin.soil.show', $soil)
            : back();

        return $redirect->with('status', 'Jadwal irigasi berhasil diperbarui!');
    }

    /**
     * Delete irrigation schedule from Admin Soil detail page
     */
    public function destroyIrrigationSchedule(Request $request, \App\Models\IrrigationSchedule $schedule): RedirectResponse
    {
        $schedule->delete();

        $soilIdentifier = $request->input('soil_detection_id') ?? $request->input('soil_id');
        $soil = $this->resolveSoilDetection($soilIdentifier);

        $redirect = $soil
            ? redirect()->route('admin.soil.show', $soil)
            : back();

        return $redirect->with('status', 'Jadwal irigasi lapangan berhasil dihapus.');
    }

    /**
     * Resolve SoilDetection model by sample_code or numeric ID
     */
    protected function resolveSoilDetection(mixed $identifier): ?SoilDetection
    {
        if (empty($identifier)) {
            return null;
        }

        if ($identifier instanceof SoilDetection) {
            return $identifier;
        }

        return SoilDetection::where('sample_code', $identifier)
            ->orWhere('id', is_numeric($identifier) ? (int) $identifier : 0)
            ->first();
    }
}
