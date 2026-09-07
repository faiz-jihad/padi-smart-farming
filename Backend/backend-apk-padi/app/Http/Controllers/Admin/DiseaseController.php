<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateCommunityReportRequest;
use App\Models\CommunityReport;
use App\Models\PplValidation;
use App\Services\Admin\AdminAuditLogger;
use App\Services\Admin\AdminDiseaseService;
use App\Services\Admin\AdminNotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DiseaseController extends Controller
{
    public function index(Request $request, AdminDiseaseService $disease): View
    {
        return view('admin.disease.index', $disease->indexData($request));
    }

    public function updateReport(
        UpdateCommunityReportRequest $request,
        CommunityReport $report,
        AdminDiseaseService $disease,
        AdminAuditLogger $audit,
        AdminNotificationService $notifications,
    ): RedirectResponse {
        $disease->updateReport($report, $request->validated(), $request, $audit, $notifications);

        return back()->with('status', 'Status laporan penyakit berhasil diperbarui.');
    }

    public function updatePplValidation(
        Request $request,
        PplValidation $pplValidation,
        AdminDiseaseService $disease,
        AdminAuditLogger $audit,
        AdminNotificationService $notifications,
    ): RedirectResponse {
        $validated = $request->validate([
            'status' => 'required|in:pending,validated,rejected,needs_revisit',
            'ppl_id' => 'nullable|integer|exists:users,id',
            'notes'  => 'nullable|string|max:1000',
        ]);

        $disease->updatePplValidation($pplValidation, $validated, $request, $audit, $notifications);

        return back()->with('status', "Status validasi PPL #{$pplValidation->id} berhasil diperbarui dan disinkronkan ke petani.");
    }
}
