<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FarmActivity extends Model
{
    protected $fillable = [
        'crop_season_id',
        'type',
        'occurred_at',
        'notes',
        'cost',
    ];

    public function cropSeason(): BelongsTo
    {
        return $this->belongsTo(CropSeason::class);
    }
}