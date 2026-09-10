<?php

namespace App\Services\Government;

use App\Models\CropSeason;
use App\Models\DiseaseScan;
use App\Models\Farm;
use App\Models\FarmActivity;
use App\Models\Harvest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class GovernmentDataService
{
    /**
     * Get B2G High-level Overview Data
     *
     * @return array<string, mixed>
     */
    public function getOverviewData(): array
    {
        $totalFarmers = User::where('role', 'farmer')->count();
        $totalFarms = Farm::count();
        $totalActivities = FarmActivity::count();
        $totalDiseaseScans = DiseaseScan::where('quality_status', '!=', 'healthy')->count();

        // Calculate productivity status counts
        $productivityCounts = $this->calculateProductivityCounts();

        return [
            'total_farmers'             => $totalFarmers,
            'total_farms'               => $totalFarms,
            'total_activities'          => $totalActivities,
            'total_disease_detections'  => $totalDiseaseScans,
            'productive_farms'          => $productivityCounts['productive'],
            'need_attention_farms'      => $productivityCounts['need_attention'],
            'insufficient_data_farms'   => $productivityCounts['insufficient_data'],
        ];
    }

    /**
     * Get Paginated Agricultural Activities with Database Filtering
     */
    public function getActivitiesData(Request $request, int $perPage = 15): LengthAwarePaginator
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $region = trim((string) $request->query('region', ''));
        $commodity = trim((string) $request->query('commodity', ''));
        $activityType = trim((string) $request->query('activity_type', ''));

        $query = FarmActivity::query()
            ->with([
                'cropSeason.farm.province',
                'cropSeason.farm.regency',
                'cropSeason.farm.district',
                'cropSeason.farm.village',
                'cropSeason.variety',
            ])
            ->when($startDate, function (Builder $q) use ($startDate): void {
                $q->whereDate('occurred_at', '>=', $startDate);
            })
            ->when($endDate, function (Builder $q) use ($endDate): void {
                $q->whereDate('occurred_at', '<=', $endDate);
            })
            ->when($activityType !== '', function (Builder $q) use ($activityType): void {
                $q->where('type', 'like', "%{$activityType}%");
            })
            ->when($commodity !== '', function (Builder $q) use ($commodity): void {
                $q->whereHas('cropSeason.variety', function (Builder $vq) use ($commodity): void {
                    $vq->where('name', 'like', "%{$commodity}%");
                });
            })
            ->when($region !== '', function (Builder $q) use ($region): void {
                $q->whereHas('cropSeason.farm', function (Builder $fq) use ($region): void {
                    $fq->whereHas('regency', fn ($rq) => $rq->where('name', 'like', "%{$region}%"))
                        ->orWhereHas('district', fn ($dq) => $dq->where('name', 'like', "%{$region}%"))
                        ->orWhereHas('province', fn ($pq) => $pq->where('name', 'like', "%{$region}%"));
                });
            })
            ->latest('occurred_at')
            ->latest('id');

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Get Paginated Disease Monitoring Data with Database Filtering
     */
    public function getDiseasesData(Request $request, int $perPage = 15): LengthAwarePaginator
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $region = trim((string) $request->query('region', ''));
        $disease = trim((string) $request->query('disease', ''));

        $query = DiseaseScan::query()
            ->with([
                'farm.province',
                'farm.regency',
                'farm.district',
                'farm.village',
                'recommendation',
            ])
            ->when($startDate, function (Builder $q) use ($startDate): void {
                $q->whereDate('scanned_at', '>=', $startDate);
            })
            ->when($endDate, function (Builder $q) use ($endDate): void {
                $q->whereDate('scanned_at', '<=', $endDate);
            })
            ->when($disease !== '', function (Builder $q) use ($disease): void {
                $q->where('predicted_class', 'like', "%{$disease}%");
            })
            ->when($region !== '', function (Builder $q) use ($region): void {
                $q->whereHas('farm', function (Builder $fq) use ($region): void {
                    $fq->whereHas('regency', fn ($rq) => $rq->where('name', 'like', "%{$region}%"))
                        ->orWhereHas('district', fn ($dq) => $dq->where('name', 'like', "%{$region}%"))
                        ->orWhereHas('province', fn ($pq) => $pq->where('name', 'like', "%{$region}%"));
                });
            })
            ->latest('scanned_at')
            ->latest('id');

        return $query->paginate($perPage)->withQueryString();
    }

    /**
     * Get Productivity Calculations per Farm with Database Filtering
     */
    public function getProductivityData(Request $request, int $perPage = 15): LengthAwarePaginator
    {
        $region = trim((string) $request->query('region', ''));
        $commodity = trim((string) $request->query('commodity', ''));

        $query = Farm::query()
            ->with([
                'province',
                'regency',
                'district',
                'village',
                'cropSeasons.harvests',
                'cropSeasons.variety',
            ])
            ->when($region !== '', function (Builder $q) use ($region): void {
                $q->whereHas('regency', fn ($rq) => $rq->where('name', 'like', "%{$region}%"))
                    ->orWhereHas('district', fn ($dq) => $dq->where('name', 'like', "%{$region}%"))
                    ->orWhereHas('province', fn ($pq) => $pq->where('name', 'like', "%{$region}%"));
            })
            ->when($commodity !== '', function (Builder $q) use ($commodity): void {
                $q->whereHas('cropSeasons.variety', function (Builder $vq) use ($commodity): void {
                    $vq->where('name', 'like', "%{$commodity}%");
                });
            })
            ->latest('id');

        return $query->paginate($perPage)->through(function (Farm $farm) {
            return $this->formatFarmProductivity($farm);
        })->withQueryString();
    }

    /**
     * Compute productivity assessment for a single Farm entity
     *
     * @return array<string, mixed>
     */
    public function formatFarmProductivity(Farm $farm): array
    {
        $areaHa = (float) ($farm->area_ha ?: 0);
        $totalHarvestKg = 0.0;
        $harvestRecordsCount = 0;
        $activeCommodity = 'Padi';

        foreach ($farm->cropSeasons as $season) {
            if ($season->variety?->name) {
                $activeCommodity = 'Padi (' . $season->variety->name . ')';
            }
            foreach ($season->harvests as $h) {
                $harvestRecordsCount++;
                $qty = (float) $h->quantity;
                $unit = strtolower(trim((string) $h->unit));
                if ($unit === 'ton') {
                    $totalHarvestKg += ($qty * 1000);
                } else {
                    $totalHarvestKg += $qty;
                }
            }
        }

        $totalHarvestTon = $totalHarvestKg / 1000.0;
        $yieldPerHa = ($areaHa > 0 && $harvestRecordsCount > 0) ? round($totalHarvestTon / $areaHa, 2) : 0.0;

        // Determine Status based on Transparent Business Rules
        if ($harvestRecordsCount > 0 && $areaHa > 0) {
            if ($yieldPerHa >= 4.5) {
                $status = 'PRODUCTIVE';
                $statusLabel = 'Produktif';
            } else {
                $status = 'NEED_ATTENTION';
                $statusLabel = 'Perlu Perhatian';
            }
        } else {
            $status = 'INSUFFICIENT_DATA';
            $statusLabel = 'Data Belum Lengkap';
        }

        return [
            'farm_id'           => $farm->id,
            'farm_name'         => $farm->name,
            'area_ha'           => $areaHa,
            'commodity'         => $activeCommodity,
            'region'            => $farm->regency?->name ?? ($farm->province?->name ?? 'Wilayah Terdaftar'),
            'total_harvest_ton' => round($totalHarvestTon, 2),
            'productivity_ton_per_ha' => $yieldPerHa,
            'status'            => $status,
            'status_label'      => $statusLabel,
            'harvest_records'   => $harvestRecordsCount,
        ];
    }

    /**
     * Get Aggregated Agricultural Insights for Dinas Pertanian
     *
     * @return array<string, mixed>
     */
    public function getInsightsData(): array
    {
        $totalFarmers = User::where('role', 'farmer')->count();
        $totalFarms = Farm::count();
        $totalActivities = FarmActivity::count();
        $totalDiseases = DiseaseScan::where('quality_status', '!=', 'healthy')->count();

        // Top activities breakdown
        $topActivities = FarmActivity::query()
            ->selectRaw('type, count(*) as count')
            ->groupBy('type')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'type'  => ucfirst((string) $row->type),
                'count' => (int) $row->count,
            ])
            ->toArray();

        // Top diseases breakdown
        $topDiseases = DiseaseScan::query()
            ->where('quality_status', '!=', 'healthy')
            ->whereNotNull('predicted_class')
            ->selectRaw('predicted_class, count(*) as count')
            ->groupBy('predicted_class')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->map(fn ($row) => [
                'disease' => (string) $row->predicted_class,
                'count'   => (int) $row->count,
            ])
            ->toArray();

        // Productivity rates
        $productivityCounts = $this->calculateProductivityCounts();
        $totalAssessed = max(1, $totalFarms);
        $productiveRate = round(($productivityCounts['productive'] / $totalAssessed) * 100, 1);
        $attentionRate = round(($productivityCounts['need_attention'] / $totalAssessed) * 100, 1);
        $insufficientRate = round(($productivityCounts['insufficient_data'] / $totalAssessed) * 100, 1);

        return [
            'total_active_farmers'     => $totalFarmers,
            'total_active_farms'       => $totalFarms,
            'total_activities'         => $totalActivities,
            'top_activities'           => $topActivities,
            'total_disease_detections' => $totalDiseases,
            'top_diseases'             => $topDiseases,
            'affected_commodities'     => ['Padi Sawah (Oryza sativa)'],
            'productive_rate_pct'      => $productiveRate,
            'need_attention_rate_pct'  => $attentionRate,
            'insufficient_data_rate_pct' => $insufficientRate,
            'counts'                   => $productivityCounts,
        ];
    }

    /**
     * Calculate global productivity counts
     *
     * @return array{productive: int, need_attention: int, insufficient_data: int}
     */
    protected function calculateProductivityCounts(): array
    {
        $farms = Farm::with(['cropSeasons.harvests', 'cropSeasons.variety', 'regency', 'province'])->get();
        $productive = 0;
        $needAttention = 0;
        $insufficient = 0;

        foreach ($farms as $farm) {
            $calc = $this->formatFarmProductivity($farm);
            if ($calc['status'] === 'PRODUCTIVE') {
                $productive++;
            } elseif ($calc['status'] === 'NEED_ATTENTION') {
                $needAttention++;
            } else {
                $insufficient++;
            }
        }

        return [
            'productive'        => $productive,
            'need_attention'    => $needAttention,
            'insufficient_data' => $insufficient,
        ];
    }
}
