<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeatherSnapshot extends Model
{
    protected $fillable = [
        'farm_id',
        'provider',
        'observed_at',
        'payload_json',
        'expires_at',
    ];

    protected $casts = [
        'observed_at' => 'datetime',
        'payload_json' => 'array',
        'expires_at' => 'datetime',
    ];

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class, 'farm_id');
    }
}