<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DistrictBoundary extends Model
{
    protected $fillable = [
        'district_id',
        'geometry',
        'bbox',
    ];

    protected function casts(): array
    {
        return [
            'bbox' => 'array',
        ];
    }

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    /**
     * Decode geometry JSON string menjadi array PHP.
     */
    public function getGeometryArrayAttribute(): ?array
    {
        return $this->geometry ? json_decode($this->geometry, true) : null;
    }
}
