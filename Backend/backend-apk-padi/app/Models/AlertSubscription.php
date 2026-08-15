<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlertSubscription extends Model
{
    protected $fillable = [
        'farmer_id',
        'farm_id',
        'is_active',
        'radius_km',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'radius_km' => 'decimal:2',
    ];

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'farmer_id');
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class, 'farm_id');
    }
}