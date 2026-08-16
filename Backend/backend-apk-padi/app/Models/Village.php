<?php

namespace App\Models;

use App\Enums\VillageType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Village extends Model
{
    protected $fillable = [
        'district_id',
        'code',
        'name',
        'type',
        'latitude',
        'longitude',
    ];

    protected function casts(): array
    {
        return [
            'type' => VillageType::class,
        ];
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function boundary(): HasOne
    {
        return $this->hasOne(VillageBoundary::class);
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
