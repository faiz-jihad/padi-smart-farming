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
    public function indexData(Request $request): array
    {
        $scansQuery = DiseaseScan::query()->with(['farmer', 'farm'])->latest('id');

        if ($search = $request->input('search')) {
            $scansQuery->where(function ($q) use ($search) {
                $q->where('predicted_class', 'like', "%{$search}%")
                  ->orWhereHas('farmer', fn ($f) => $f->where('name', 'like', "%{$search}%"));
            });
        }

        if ($quality = $request->input('quality')) {
            $scansQuery->where('quality_status', $quality);
        }

        if ($disease = $request->input('disease')) {
            $scansQuery->where('predicted_class', $disease);
        }

        return [
            'title' => 'Laporan Penyakit',
            'scans' => $scansQuery->paginate(10)->withQueryString(),
            'reports' => CommunityReport::query()
                ->with(['farmer', 'scan'])
                ->latest('id')
                ->paginate(10, ['*'], 'report_page')
                ->withQueryString(),
            'stats' => [
                'scans' => DiseaseScan::query()->count(),
                'reported' => CommunityReport::query()->count(),
                'pending_reports' => CommunityReport::query()->where('status', 'pending')->count(),
                'valid_images' => DiseaseScan::query()->where('quality_status', 'valid')->count(),
            ],
            'diseaseClasses' => DiseaseScan::query()
                ->whereNotNull('predicted_class')
                ->distinct()
                ->pluck('predicted_class')
                ->sort()
                ->values(),
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
