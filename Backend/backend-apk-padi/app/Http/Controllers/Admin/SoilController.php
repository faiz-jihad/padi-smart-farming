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
}
