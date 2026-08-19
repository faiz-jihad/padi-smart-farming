<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SoilDetection extends Model
{
    protected $fillable = [
        'farm_id',
        'sample_code',
        'ph_level',
        'nitrogen_ppm',
        'phosphorus_ppm',
        'potassium_ppm',
        'moisture_percentage',
        'organic_matter_percentage',
        'soil_temp_celsius',
        'soil_type',
        'soil_health_score',
        'soil_status',
        'recommendations_json',
        'tested_at',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'ph_level' => 'decimal:2',
        'nitrogen_ppm' => 'decimal:2',
        'phosphorus_ppm' => 'decimal:2',
        'potassium_ppm' => 'decimal:2',
        'moisture_percentage' => 'decimal:2',
        'organic_matter_percentage' => 'decimal:2',
        'soil_temp_celsius' => 'decimal:2',
        'soil_health_score' => 'integer',
        'recommendations_json' => 'array',
        'tested_at' => 'datetime',
    ];

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getRouteKeyName(): string
    {
        return 'sample_code';
    }
}
