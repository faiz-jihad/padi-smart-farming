<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SoilType extends Model
{
    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Scope query to only include active soil types.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * The admin or extension officer who created this soil type.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Farms with this soil type.
     */
    public function farms(): HasMany
    {
        return $this->hasMany(Farm::class, 'soil_type_id');
    }

    /**
     * Soil detections with this soil type.
     */
    public function soilDetections(): HasMany
    {
        return $this->hasMany(SoilDetection::class, 'soil_type_id');
    }
}
