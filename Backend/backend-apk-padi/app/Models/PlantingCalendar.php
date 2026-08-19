<?php

namespace App\Models;

use App\Enums\PlantingCalendarStatus;
use App\Enums\PlantingSeason;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlantingCalendar extends Model
{
    protected $fillable = [
        'province_id',
        'regency_id',
        'district_id',
        'village_id',
        'season',
        'year',
        'planting_start',
        'planting_end',
        'planting_pattern',
        'rice_variety',
        'recommended_area',
        'status',
        'source',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'season'         => PlantingSeason::class,
            'status'         => PlantingCalendarStatus::class,
            'planting_start' => 'date',
            'planting_end'   => 'date',
            'year'           => 'integer',
        ];
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function regency(): BelongsTo
    {
        return $this->belongsTo(Regency::class);
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    /**
     * Kembalikan label level wilayah terendah yang terdefinisi.
     */
    public function getRegionLevelAttribute(): string
    {
        return match (true) {
            $this->village_id !== null  => 'village',
            $this->district_id !== null => 'district',
            $this->regency_id !== null  => 'regency',
            $this->province_id !== null => 'province',
            default                     => 'national',
        };
    }
}
