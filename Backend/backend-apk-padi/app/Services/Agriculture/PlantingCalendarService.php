<?php

namespace App\Services\Agriculture;

use App\Enums\PlantingCalendarStatus;
use App\Enums\PlantingSeason;
use App\Models\District;
use App\Models\Farm;
use App\Models\PlantingCalendar;
use App\Models\Village;
use Carbon\Carbon;

class PlantingCalendarService
{
    /**
     * Get active planting calendar for a location with hierarchical fallback:
     * 1. Village level
     * 2. District level
     * 3. Regency level
     * 4. Province level
     */
    public function getForLocation(
        ?int $villageId = null,
        ?int $districtId = null,
        ?int $regencyId = null,
        ?int $provinceId = null,
        ?int $year = null,
        ?string $season = null
    ): ?array {
        $year = $year ?: (int) date('Y');

        // Infer hierarchy upwards if lower level ID is given
        if ($villageId && !$districtId) {
            $village = Village::with('district.regency')->find($villageId);
            if ($village) {
                $districtId = $village->district_id;
                $regencyId  = $village->district?->regency_id;
                $provinceId = $village->district?->regency?->province_id;
            }
        } elseif ($districtId && !$regencyId) {
            $district = District::with('regency')->find($districtId);
            if ($district) {
                $regencyId  = $district->regency_id;
                $provinceId = $district->regency?->province_id;
            }
        }

        $baseQuery = PlantingCalendar::with(['province', 'regency', 'district', 'village'])
            ->where('status', PlantingCalendarStatus::Active)
            ->where('year', $year);

        if ($season) {
            $baseQuery->where('season', $season);
        }

        // 1. Check Village level
        if ($villageId) {
            $calendar = (clone $baseQuery)->where('village_id', $villageId)->first();
            if ($calendar) {
                return $this->formatCalendarWithFallback($calendar, 'village');
            }
        }

        // 2. Check District level
        if ($districtId) {
            $calendar = (clone $baseQuery)->where('district_id', $districtId)->first();
            if ($calendar) {
                return $this->formatCalendarWithFallback($calendar, 'district');
            }
        }

        // 3. Check Regency level
        if ($regencyId) {
            $calendar = (clone $baseQuery)->where('regency_id', $regencyId)->first();
            if ($calendar) {
                return $this->formatCalendarWithFallback($calendar, 'regency');
            }
        }

        // 4. Check Province level
        if ($provinceId) {
            $calendar = (clone $baseQuery)->where('province_id', $provinceId)->first();
            if ($calendar) {
                return $this->formatCalendarWithFallback($calendar, 'province');
            }
        }

        return null;
    }

    /**
     * Get planting calendar tailored to a specific Farm
     */
    public function getForFarm(Farm $farm, ?int $year = null, ?string $season = null): ?array
    {
        return $this->getForLocation(
            villageId: $farm->village_id,
            districtId: $farm->district_id,
            regencyId: $farm->regency_id,
            provinceId: $farm->province_id,
            year: $year,
            season: $season
        );
    }

    private function formatCalendarWithFallback(PlantingCalendar $calendar, string $resolvedLevel): array
    {
        $today = Carbon::today();
        $start = Carbon::parse($calendar->planting_start);
        $end = Carbon::parse($calendar->planting_end);

        $isCurrentlyPlanting = $today->between($start, $end);
        $daysUntilStart = $today->lessThan($start) ? $today->diffInDays($start) : 0;
        $daysUntilEnd = $today->lessThanOrEqualTo($end) ? $today->diffInDays($end) : 0;

        return [
            'id'                  => $calendar->id,
            'resolved_level'      => $resolvedLevel,
            'is_fallback'         => $resolvedLevel !== 'village',
            'season'              => $calendar->season?->value,
            'season_label'        => $calendar->season?->label(),
            'season_code'         => $calendar->season?->indonesian(),
            'year'                => $calendar->year,
            'planting_start'      => $calendar->planting_start->format('Y-m-d'),
            'planting_end'        => $calendar->planting_end->format('Y-m-d'),
            'planting_pattern'    => $calendar->planting_pattern,
            'rice_variety'        => $calendar->rice_variety,
            'recommended_area'    => $calendar->recommended_area ? (float) $calendar->recommended_area : null,
            'status'              => $calendar->status?->value,
            'source'              => $calendar->source,
            'notes'               => $calendar->notes,
            'is_planting_window'  => $isCurrentlyPlanting,
            'days_until_start'    => $daysUntilStart,
            'days_until_end'      => $daysUntilEnd,
            'region'              => [
                'village'  => $calendar->village?->name,
                'district' => $calendar->district?->name,
                'regency'  => $calendar->regency?->name,
                'province' => $calendar->province?->name,
            ],
        ];
    }

    /**
     * Calculate Best Planting Window & Growth Stage Timeline for a Farm
     */
    public function calculateBestPlantingWindow(?int $farmId = null, ?string $plannedDateStr = null, ?int $varietyId = null): array
    {
        $farm = $farmId ? Farm::find($farmId) : null;
        $variety = $varietyId ? \App\Models\RiceVariety::find($varietyId) : \App\Models\RiceVariety::first();

        $durationDays = $variety ? $variety->duration_days : 115;
        $varietyName = $variety ? $variety->name : 'Inpari 32 HDB';

        $plantDate = $plannedDateStr ? Carbon::parse($plannedDateStr) : Carbon::today()->addDays(7);
        $harvestDate = $plantDate->copy()->addDays($durationDays);

        $windowStart = $plantDate->copy()->subDays(5);
        $windowEnd = $plantDate->copy()->addDays(7);

        // Milestones
        $milestones = [
            [
                'phase' => 'Fase Persemaian & Pengolahan Lahan',
                'day_range' => '-15 s/d 0 HST',
                'start_date' => $plantDate->copy()->subDays(15)->format('d M Y'),
                'end_date' => $plantDate->format('d M Y'),
                'action' => 'Pengolahan tanah sempurna (bajak 1 & 2), pemupukan kandang 2 ton/ha, dan persemaian benih ' . $varietyName . '.',
            ],
            [
                'phase' => 'Fase Vegetatif (Anakan Aktif)',
                'day_range' => '0 s/d 55 HST',
                'start_date' => $plantDate->format('d M Y'),
                'end_date' => $plantDate->copy()->addDays(55)->format('d M Y'),
                'action' => 'Pindah tanam jajar legowo, pemupukan NPK dasar (7-10 HST) dan susulan I (21-25 HST), pengairan macak-macak.',
            ],
            [
                'phase' => 'Fase Generatif (Primordia & Pembungaan)',
                'day_range' => '56 s/d 85 HST',
                'start_date' => $plantDate->copy()->addDays(56)->format('d M Y'),
                'end_date' => $plantDate->copy()->addDays(85)->format('d M Y'),
                'action' => 'Pemupukan susulan II (KCl/Urea BWD), pertahankan genangan air 3-5 cm, semprot fungisida pencegah blas jika curah hujan tinggi.',
            ],
            [
                'phase' => 'Fase Pematangan & Panen Presisi',
                'day_range' => '86 s/d ' . $durationDays . ' HST',
                'start_date' => $plantDate->copy()->addDays(86)->format('d M Y'),
                'end_date' => $harvestDate->format('d M Y'),
                'action' => 'Pengeringan total air lahan 10-14 hari sebelum panen. Panen saat 90-95% bulir menguning.',
            ],
        ];

        return [
            'success' => true,
            'farm_name' => $farm?->name ?? 'Lahan Pertanian',
            'variety_name' => $varietyName,
            'duration_days' => $durationDays,
            'recommended_planting_window' => [
                'start' => $windowStart->format('d M Y'),
                'end' => $windowEnd->format('d M Y'),
                'label' => $windowStart->format('d M') . ' - ' . $windowEnd->format('d M Y'),
            ],
            'estimated_harvest_date' => $harvestDate->format('d M Y'),
            'climate_suitability' => [
                'status' => 'optimal',
                'label' => 'Sangat Sesuai (Fase Basah BMKG)',
                'rain_risk' => 'Rendah - Sedang',
                'recommendation' => 'Kondisi iklim dan ketersediaan air mendukung untuk memulai tanam pada jendela waktu ini.',
            ],
            'milestones' => $milestones,
        ];
    }
    /**
     * Create a new planting calendar / recommendation record.
     */
    public function create(array $data): PlantingCalendar
    {
        return PlantingCalendar::query()->create($data)->load(['province', 'regency', 'district', 'village']);
    }

    /**
     * Find a planting calendar by ID.
     */
    public function find(int $id): ?PlantingCalendar
    {
        return PlantingCalendar::with(['province', 'regency', 'district', 'village'])->find($id);
    }

    /**
     * Update an existing planting calendar / recommendation record.
     */
    public function update(PlantingCalendar $calendar, array $data): PlantingCalendar
    {
        $calendar->update($data);

        return $calendar->fresh(['province', 'regency', 'district', 'village']);
    }

    /**
     * Delete a planting calendar record.
     */
    public function delete(PlantingCalendar $calendar): bool
    {
        return (bool) $calendar->delete();
    }
}
