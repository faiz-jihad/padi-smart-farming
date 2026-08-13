<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Harvest extends Model
{
    protected $fillable = [
        'crop_season_id',
        'harvest_date',
        'quantity',
        'unit',
        'quality_grade',
        'moisture_percent',
        'verification_status',
    ];

    public function cropSeason(): BelongsTo
    {
        return $this->belongsTo(CropSeason::class);
    }
}