<?php

namespace App\Services\Admin;

use App\Models\AuditLog;
use App\Models\Farm;
use App\Models\IrrigationSchedule;
use App\Models\SoilDetection;
use App\Services\Admin\AdminAuditLogger;
use App\Services\Admin\AdminNotificationService;
use App\Services\Soil\SoilDetectionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminSoilService
{
    public function __construct(
        private SoilDetectionService $soilDetectionService,
        private AdminAuditLogger $auditLogger,
        private AdminNotificationService $notificationService
    ) {}

    /**
     * Get data for soil dashboard and listing
     */
    public function indexData(Request $request): array
    {
        $search = trim((string) $request->input('search', ''));
        $farmId = $request->input('farm_id');
        $status = $request->input('status');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        $query = SoilDetection::with(['farm.farmer', 'creator']);

        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('sample_code', 'like', "%{$search}%")
                    ->orWhere('soil_type', 'like', "%{$search}%")
                    ->orWhereHas('farm', function ($fq) use ($search): void {
                        $fq->where('name', 'like', "%{$search}%")
                            ->orWhereHas('farmer', function ($u) use ($search): void {
                                $u->where('name', 'like', "%{$search}%");
                            });
                    });
            });
        }

        if ($farmId) {
            $query->where('farm_id', $farmId);
        }

        if ($status) {
            $query->where('soil_status', $status);
        }

        if ($fromDate) {
            $query->whereDate('tested_at', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('tested_at', '<=', $toDate);
        }

        $detections = $query->latest('tested_at')->paginate(15);

        $stats = [
            'total_samples' => SoilDetection::count(),
            'avg_ph' => round(SoilDetection::avg('ph_level') ?? 6.5, 2),
            'optimal_count' => SoilDetection::where('soil_status', 'optimal')->count(),
            'critical_count' => SoilDetection::where('soil_status', 'critical')->count(),
            'warning_count' => SoilDetection::where('soil_status', 'warning')->count(),
            'needs_fertilizer_count' => SoilDetection::where('soil_status', 'needs_fertilizer')->count(),
        ];

        return [
            'detections' => $detections,
            'farms' => Farm::with('farmer')->orderBy('name')->get(),
            'stats' => $stats,
            'filters' => [
                'search' => $search,
                'farm_id' => $farmId,
                'status' => $status,
                'from_date' => $fromDate,
                'to_date' => $toDate,
            ],
        ];
    }

    /**
     * Get soil detail data with farm weather correlation
     */
    public function showData(SoilDetection $soilDetection): array
    {
        $soilDetection->load(['farm.farmer', 'farm.weatherSnapshots' => function ($q) {
            $q->latest('observed_at')->limit(1);
        }, 'creator']);

        $latestWeather = $soilDetection->farm->weatherSnapshots->first();

        return [
            'soilDetection' => $soilDetection,
            'latestWeather' => $latestWeather,
        ];
    }

    /**
     * Create and evaluate soil detection, then dispatch notifications and audit log
     */
    public function createSoilDetection(array $data, ?int $actorId = null, ?Request $request = null): SoilDetection
    {
        $soil = $this->soilDetectionService->analyzeAndCreate($data, $actorId);
        $soil->load('farm.farmer');

        // Audit Log
        $this->auditLogger->write('admin_soil_created', $soil, null, $soil->toArray(), $request);

        // System Notification to Admins
        $this->notificationService->notifyAdmins(
            'Analisis Tanah Selesai',
            "Sampel {$soil->sample_code} pada lahan {$soil->farm->name} selesai diuji. Skor: {$soil->soil_health_score}/100 ({$soil->soil_status}).",
            'soil',
            ['id' => $soil->id, 'sample_code' => $soil->sample_code]
        );

        // Notification to the farm owner (Farmer)
        if ($soil->farm?->farmer_user_id) {
            $this->notificationService->notifyUser(
                $soil->farm->farmer_user_id,
                'Hasil Uji Tanah Lahan Anda Telah Terbit',
                "Hasil pengujian tanah {$soil->sample_code} di {$soil->farm->name} telah keluar dengan Skor Kesehatan {$soil->soil_health_score}/100. Rekomendasi pemupukan & irigasi telah tersedia.",
                'crop_alert',
                ['soil_id' => $soil->id, 'url' => '/farms']
            );
        }

        return $soil;
    }

    /**
     * Delete soil detection with audit log and admin notification
     */
    public function deleteSoilDetection(SoilDetection $soilDetection, ?int $actorId = null, ?Request $request = null): bool
    {
        return DB::transaction(function () use ($soilDetection, $actorId, $request) {
            $sampleCode = $soilDetection->sample_code;
            $detectionId = $soilDetection->id;
            $oldValues = $soilDetection->toArray();

            $soilDetection->delete();

            if ($actorId) {
                AuditLog::create([
                    'user_id' => $actorId,
                    'action' => 'delete_soil_detection',
                    'target_type' => SoilDetection::class,
                    'target_id' => $detectionId,
                    'payload_json' => [
                        'sample_code' => $sampleCode,
                    ],
                ]);
            }

            $this->auditLogger->write('admin_soil_deleted', SoilDetection::class, $oldValues, null, $request, $detectionId);
            $this->notificationService->notifyAdmins(
                'Data Tanah Dihapus',
                "Sampel tanah {$sampleCode} telah dihapus dari sistem.",
                'soil'
            );

            return true;
        });
    }

    /**
     * Generate PDF report for soil detection
     */
    public function generateReportPdf(SoilDetection $soil)
    {
        $soil->load([
            'farm.farmer',
            'creator',
        ]);

        $data = $this->showData($soil);

        $irrigation = $this->soilDetectionService->calculateIrrigationSchedule(
            (float) $soil->moisture_percentage,
            $soil->soil_temp_celsius ? (float) $soil->soil_temp_celsius : null
        );

        $data['irrigation'] = $irrigation;

        $pdf = Pdf::loadView('admin.soil.report-pdf', $data);

        return $pdf->download(
            'Laporan-Tanah-' . $soil->sample_code . '.pdf'
        );
    }

    /**
     * Export soil detections to CSV or JSON
     */
    public function exportSoilData(array $filters)
    {
        $query = SoilDetection::with('farm');

        if (isset($filters['farm_id'])) {
            $query->where('farm_id', $filters['farm_id']);
        }

        if (isset($filters['status'])) {
            $query->where('soil_status', $filters['status']);
        }

        $data = $query->latest('tested_at')->get();

        if (($filters['format'] ?? 'csv') === 'json') {
            return response()->json($data->toArray(), 200, [], JSON_PRETTY_PRINT);
        }

        $csv = "Sample Code,Farm ID,Farm Name,pH Level,Nitrogen (ppm),Phosphorus (ppm),Potassium (ppm),Moisture (%),Organic Matter (%),Soil Temp (C),Health Score,Status,Tested At\n";

        foreach ($data as $row) {
            $csv .= sprintf(
                '"%s",%d,"%s",%.2f,%.2f,%.2f,%.2f,%.2f,%.2f,%s,%d,"%s","%s"',
                $row->sample_code,
                $row->farm_id,
                str_replace('"', '""', $row->farm?->name ?? 'N/A'),
                $row->ph_level,
                $row->nitrogen_ppm,
                $row->phosphorus_ppm,
                $row->potassium_ppm,
                $row->moisture_percentage,
                $row->organic_matter_percentage,
                $row->soil_temp_celsius ?? 'N/A',
                $row->soil_health_score,
                $row->soil_status,
                $row->tested_at
            ) . "\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="soil-detections.csv"',
        ]);
    }

    /**
     * Store manual/field irrigation schedule, with audit log and notifications
     */
    public function storeIrrigationSchedule(array $validated, ?Request $request = null): IrrigationSchedule
    {
        $schedule = IrrigationSchedule::create([
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

        $schedule->load('farm.farmer');

        // Audit & Notifications
        $this->auditLogger->write('admin_irrigation_created', $schedule, null, $schedule->toArray(), $request);
        $this->notificationService->notifyAdmins(
            'Jadwal Irigasi Dibuat',
            "Jadwal irigasi baru tanggal {$schedule->schedule_date} di lahan {$schedule->farm->name} telah dijadwalkan.",
            'system'
        );

        if ($schedule->farm?->farmer_user_id) {
            $this->notificationService->notifyUser(
                $schedule->farm->farmer_user_id,
                'Jadwal Irigasi Lahan Ditetapkan',
                "Penyuluh telah menjadwalkan irigasi untuk {$schedule->farm->name} pada {$schedule->schedule_date} ({$schedule->start_time} - {$schedule->end_time}).",
                'crop_alert',
                ['url' => '/farms']
            );
        }

        return $schedule;
    }

    /**
     * Update irrigation schedule, with audit log and admin notification
     */
    public function updateIrrigationSchedule(IrrigationSchedule $schedule, array $validated, ?Request $request = null): IrrigationSchedule
    {
        $oldValues = $schedule->toArray();
        $schedule->update($validated);
        $schedule->load('farm.farmer');

        // Audit & Notification
        $this->auditLogger->write('admin_irrigation_updated', $schedule, $oldValues, $schedule->toArray(), $request);
        $this->notificationService->notifyAdmins(
            'Jadwal Irigasi Diperbarui',
            "Jadwal irigasi {$schedule->farm->name} diubah menjadi status: {$schedule->status}.",
            'system'
        );

        return $schedule;
    }

    /**
     * Delete irrigation schedule with audit log and admin notification
     */
    public function deleteIrrigationSchedule(IrrigationSchedule $schedule, ?Request $request = null): bool
    {
        $oldValues = $schedule->toArray();
        $scheduleId = $schedule->id;
        $schedule->delete();

        $this->auditLogger->write('admin_irrigation_deleted', IrrigationSchedule::class, $oldValues, null, $request, $scheduleId);
        $this->notificationService->notifyAdmins('Jadwal Irigasi Dihapus', 'Jadwal irigasi telah dihapus dari sistem.', 'system');

        return true;
    }

    /**
     * Resolve SoilDetection model by sample_code or numeric ID
     */
    public function resolveSoilDetection(mixed $identifier): ?SoilDetection
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
