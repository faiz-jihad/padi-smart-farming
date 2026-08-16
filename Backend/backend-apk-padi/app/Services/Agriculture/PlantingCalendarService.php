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
}
