<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IrrigationSchedule extends Model
{
    protected $fillable = [
        'farm_id',
        'schedule_date',
        'start_time',
        'end_time',
        'status',
        'source',
        'officer_name',
        'irrigation_block',
        'water_source',
        'notes',
    ];

    protected $casts = [
        'schedule_date' => 'date',
    ];

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }
}