<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CropSeason extends Model
{
    protected $fillable = [
        'farm_id',
        'variety_id',
        'planned_planting_date',
        'planting_date',
        'estimated_harvest_date',
        'status',
    ];

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function variety(): BelongsTo
    {
        return $this->belongsTo(RiceVariety::class);
    }

    public function farmActivities(): HasMany
    {
        return $this->hasMany(FarmActivity::class);
    }
}