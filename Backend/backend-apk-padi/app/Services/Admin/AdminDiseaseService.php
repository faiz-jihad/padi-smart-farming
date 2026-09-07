<?php

namespace App\Services\Admin;

use App\Models\CommunityReport;
use App\Models\DiseaseScan;
use App\Models\PplValidation;
use App\Models\User;
use App\Services\PadiCacheService;
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

        // PPL Validations Query
        $pplQuery = PplValidation::query()
            ->with(['scan.farm', 'scan.farmer', 'scan.recommendation', 'ppl'])
            ->latest('id');

        if ($pplStatus = $request->input('ppl_status')) {
            $pplQuery->where('status', $pplStatus);
        }

        if ($pplSearch = $request->input('ppl_search')) {
            $pplQuery->where(function ($q) use ($pplSearch) {
                $q->whereHas('scan', function ($sq) use ($pplSearch) {
                    $sq->where('predicted_class', 'like', "%{$pplSearch}%")
                      ->orWhereHas('farmer', fn ($f) => $f->where('name', 'like', "%{$pplSearch}%"))
                      ->orWhereHas('farm', fn ($fm) => $fm->where('name', 'like', "%{$pplSearch}%"));
                })->orWhereHas('ppl', fn ($p) => $p->where('name', 'like', "%{$pplSearch}%"));
            });
        }

        // Petugas PPL & Admin yang dapat ditugaskan
        $extensionOfficers = User::query()
            ->where(function ($q) {
                $q->where('role', 'extension_officer')
                  ->orWhere('role', 'admin');
            })
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'phone', 'role']);

        return [
            'title' => 'Laporan Penyakit & Validasi PPL',
            'scans' => $scansQuery->paginate(10, ['*'], 'scan_page')->withQueryString(),
            'reports' => CommunityReport::query()
                ->with(['farmer', 'scan'])
                ->latest('id')
                ->paginate(10, ['*'], 'report_page')
                ->withQueryString(),
            'pplValidations' => $pplQuery->paginate(10, ['*'], 'ppl_page')->withQueryString(),
            'extensionOfficers' => $extensionOfficers,
            'stats' => [
                'scans' => DiseaseScan::query()->count(),
                'reported' => CommunityReport::query()->count(),
                'pending_reports' => CommunityReport::query()->where('status', 'pending')->count(),
                'valid_images' => DiseaseScan::query()->where('quality_status', 'valid')->count(),
                'ppl_total' => PplValidation::query()->count(),
                'ppl_pending' => PplValidation::query()->where('status', 'pending')->count(),
                'ppl_validated' => PplValidation::query()->where('status', 'validated')->count(),
                'ppl_revisit' => PplValidation::query()->where('status', 'needs_revisit')->count(),
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
     * Update Community Report (Laporan Radar Komunitas)
     *
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
            'Status laporan komunitas diperbarui',
            "Laporan komunitas #{$report->id} menjadi {$report->status}.",
        );

        // Sinkronkan notifikasi ke Petani pelapor
        $report->loadMissing(['farmer', 'scan']);
        if ($report->farmer) {
            $diseaseName = $report->scan?->predicted_class ?? 'Penyakit Padi';
            $statusIndo = match ($report->status) {
                'verified' => 'Terverifikasi',
                'resolved' => 'Selesai / Ditangani',
                'rejected' => 'Ditolak',
                default    => ucfirst($report->status),
            };

            $notifications->notifyUser(
                user: $report->farmer,
                title: "Laporan Radar {$statusIndo}",
                body: "Laporan siaran radar Anda untuk {$diseaseName} telah berstatus '{$statusIndo}' oleh Admin.",
                type: 'early_warning',
                data: [
                    'report_id'  => $report->id,
                    'status'     => $report->status,
                    'action_url' => '/community-alert',
                ]
            );
        }

        // Invalidate cache radar agar sinkron dengan peta petani
        PadiCacheService::invalidateRadarCache();
    }

    /**
     * Update PPL Validation (Kasus Validasi Lapangan Petani)
     *
     * @param  array{status: string, ppl_id?: int|null, notes?: string|null}  $data
     */
    public function updatePplValidation(
        PplValidation $pplValidation,
        array $data,
        Request $request,
        AdminAuditLogger $audit,
        AdminNotificationService $notifications,
    ): void {
        $oldValues = $pplValidation->only(['status', 'ppl_id', 'notes']);

        if (in_array($data['status'], ['validated', 'rejected']) && empty($pplValidation->validated_at)) {
            $data['validated_at'] = now();
        }

        $pplValidation->update($data);
        $pplValidation->refresh();

        $audit->write('admin_ppl_validation_updated', $pplValidation, $oldValues, $pplValidation->only(['status', 'ppl_id', 'notes']), $request);

        // 1. Notifikasi ke Admin
        $notifications->notifyAdmins(
            'Validasi PPL Diperbarui',
            "Kasus validasi PPL #{$pplValidation->id} diperbarui menjadi '{$pplValidation->status}'.",
        );

        // 2. Notifikasi sinkronisasi langsung ke Petani pemilik scan
        $scan = $pplValidation->scan()->with('farmer')->first();
        if ($scan && $scan->farmer) {
            $statusLabel = match ($pplValidation->status) {
                'validated'     => 'Divalidasi',
                'rejected'      => 'Tidak Terkonfirmasi',
                'needs_revisit' => 'Perlu Kunjungan Ulang',
                default         => 'Diperbarui',
            };

            $pplName = $pplValidation->ppl?->name ?? 'Admin / Petugas Pertanian';
            $diseaseName = $scan->predicted_class ?? 'Penyakit Tanaman';
            $notesSnippet = !empty($pplValidation->notes) ? " Catatan: \"{$pplValidation->notes}\"" : '';

            $notifications->notifyUser(
                user: $scan->farmer,
                title: "Kasus Validasi Telah {$statusLabel}",
                body: "Laporan dugaan {$diseaseName} telah diverifikasi dengan status '{$statusLabel}' oleh {$pplName}.{$notesSnippet}",
                type: 'ppl_result',
                data: [
                    'validation_id' => $pplValidation->id,
                    'scan_id'       => $scan->id,
                    'status'        => $pplValidation->status,
                    'action_url'    => "/ppl-cases",
                ]
            );
        }

        // 3. Notifikasi penugasan ke Petugas PPL jika baru ditugaskan dan bukan diri sendiri
        if (!empty($data['ppl_id']) && $data['ppl_id'] !== $request->user()?->id) {
            $pplUser = User::find($data['ppl_id']);
            if ($pplUser) {
                $farmerName = $scan?->farmer?->name ?? 'Petani';
                $diseaseName = $scan?->predicted_class ?? 'Penyakit';
                $notifications->notifyUser(
                    user: $pplUser,
                    title: 'Penugasan Kasus Lapangan Baru',
                    body: "Anda ditugaskan oleh Admin untuk memverifikasi kasus #{$pplValidation->id} ({$diseaseName}) milik {$farmerName}.",
                    type: 'ppl_assignment',
                    data: [
                        'validation_id' => $pplValidation->id,
                        'scan_id'       => $scan?->id,
                        'action_url'    => "/ppl-cases",
                    ]
                );
            }
        }
    }
}
