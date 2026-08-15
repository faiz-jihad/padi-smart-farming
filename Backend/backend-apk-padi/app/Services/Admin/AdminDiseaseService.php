<?php

namespace App\Services\Admin;

use App\Models\CommunityReport;
use App\Models\DiseaseScan;
use Illuminate\Http\Request;

class AdminDiseaseService
{
    /**
     * @return array<string, mixed>
     */
    public function indexData(): array
    {
        return [
            'title' => 'Laporan Penyakit',
            'scans' => DiseaseScan::query()->with(['farmer', 'farm'])->latest('id')->paginate(10),
            'reports' => CommunityReport::query()->with('farmer')->latest('id')->limit(10)->get(),
            'stats' => [
                'scans' => DiseaseScan::query()->count(),
                'reported' => CommunityReport::query()->count(),
                'pending_reports' => CommunityReport::query()->where('status', 'pending')->count(),
                'valid_images' => DiseaseScan::query()->where('quality_status', 'valid')->count(),
            ],
        ];
    }

    /**
     * @param  array{status: string}  $data
     */
    public function updateReport(
        CommunityReport $report,
        array $data,
        Request $request,
        AdminAuditLogger $audit,
        AdminNotificationService $notifications,
    ): void {
        $oldValues = $report->only(['status']);
        $report->update($data);

        $audit->write('admin_report_updated', $report, $oldValues, $report->only(['status']), $request);
        $notifications->notifyAdmins(
            'Status laporan diperbarui',
            "Laporan komunitas #{$report->id} menjadi {$report->status}.",
        );
    }
}
