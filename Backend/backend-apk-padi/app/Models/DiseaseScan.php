<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiseaseScan extends Model
{
    protected $fillable = [
        'farmer_id',
        'farm_id',
        'image_url',
        'image_hash',
        'quality_status',
        'predicted_class',
        'confidence',
        'model_version',
        'scanned_at',
    ];

    protected $casts = [
        'confidence' => 'decimal:4',
        'scanned_at' => 'datetime',
    ];

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'farmer_id');
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }
}
