<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommunityReport extends Model
{
    protected $fillable = [
        'scan_id',
        'farmer_id',
        'latitude',
        'longitude',
        'radius_km',
        'consent_given',
        'status',
        'reported_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'radius_km' => 'decimal:2',
        'consent_given' => 'boolean',
        'reported_at' => 'datetime',
    ];

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'farmer_id');
    }
}