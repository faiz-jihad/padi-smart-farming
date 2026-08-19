<?php

namespace App\Models;

use App\Enums\RegencyType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Regency extends Model
{
    protected $fillable = [
        'province_id',
        'code',
        'name',
        'type',
        'latitude',
        'longitude',
        'geometry',
        'bbox',
    ];

    protected function casts(): array
    {
        return [
            'type'     => RegencyType::class,
            'geometry' => 'array',
            'bbox'     => 'array',
        ];
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function districts(): HasMany
    {
        return $this->hasMany(District::class);
    }

    public function farms(): HasMany
    {
        return $this->hasMany(Farm::class);
    }

    public function plantingCalendars(): HasMany
    {
        return $this->hasMany(PlantingCalendar::class);
    }
}
