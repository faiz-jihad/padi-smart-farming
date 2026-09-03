<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\IrrigationSchedule;

class Farm extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'farmer_user_id',
        'province_id',
        'regency_id',
        'district_id',
        'village_id',
        'name',
        'area_ha',
        'latitude',
        'longitude',
        'boundary_coordinates',
        'irrigation_type',
        'irrigation_notes',
        'soil_type',
        'status',
    ];

    protected $casts = [
        'boundary_coordinates' => 'array',
    ];

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'farmer_user_id');
    }

    public function irrigationSchedules(): HasMany
    {
        return $this->hasMany(IrrigationSchedule::class);
    }

    public function cropSeasons(): HasMany
    {
        return $this->hasMany(CropSeason::class);
    }

    public function weatherSnapshots(): HasMany
    {
        return $this->hasMany(WeatherSnapshot::class);
    }

    public function soilDetections(): HasMany
    {
        return $this->hasMany(SoilDetection::class);
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
}