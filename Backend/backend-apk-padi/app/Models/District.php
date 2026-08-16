<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class District extends Model
{
    protected $fillable = [
        'regency_id',
        'code',
        'name',
        'latitude',
        'longitude',
    ];

    public function regency(): BelongsTo
    {
        return $this->belongsTo(Regency::class);
    }

    public function villages(): HasMany
    {
        return $this->hasMany(Village::class);
    }

    public function boundary(): HasOne
    {
        return $this->hasOne(DistrictBoundary::class);
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
