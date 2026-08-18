<?php

namespace App\Services\Admin;

use App\Models\AuditLog;
use App\Models\Farm;
use App\Models\SoilDetection;
use App\Services\Soil\SoilDetectionService;
use Illuminate\Http\Request;

class AdminSoilService
{
    public function __construct(
        private SoilDetectionService $soilDetectionService
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
            'farms' => Farm::orderBy('name')->get(),
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
     * Delete soil detection with audit log
     */
    public function deleteSoilDetection(SoilDetection $soilDetection, ?int $actorId = null): bool
    {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($soilDetection, $actorId) {
            $sampleCode = $soilDetection->sample_code;
            $detectionId = $soilDetection->id;

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

            return true;
        });
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
}
