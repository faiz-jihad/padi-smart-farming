<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VillageBoundary extends Model
{
    protected $fillable = [
        'village_id',
        'geometry',
        'bbox',
    ];

    protected function casts(): array
    {
        return [
            'bbox' => 'array',
        ];
    }

    public function village(): BelongsTo
    {
        return $this->belongsTo(Village::class);
    }

    /**
     * Decode geometry JSON string menjadi array PHP.
     */
    public function getGeometryArrayAttribute(): ?array
    {
        return $this->geometry ? json_decode($this->geometry, true) : null;
    }
}
