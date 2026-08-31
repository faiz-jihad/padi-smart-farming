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
        'detection_metadata',
        'model_version',
        'user_feedback',
        'verified_class',
        'is_learned',
        'feedback_notes',
        'scanned_at',
    ];

    protected $casts = [
        'confidence' => 'decimal:4',
        'detection_metadata' => 'array',
        'is_learned' => 'boolean',
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

    public function recommendation(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(DiseaseRecommendation::class, 'scan_id');
    }
}
