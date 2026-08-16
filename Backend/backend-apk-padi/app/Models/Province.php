<?php

namespace App\Models;

use App\Enums\RegencyType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Province extends Model
{
    protected $fillable = [
        'code',
        'name',
        'latitude',
        'longitude',
    ];

    public function regencies(): HasMany
    {
        return $this->hasMany(Regency::class);
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
