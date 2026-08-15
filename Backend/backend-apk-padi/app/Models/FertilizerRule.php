<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FertilizerRule extends Model
{
    protected $fillable = [
        'variety_id',
        'phase',
        'nutrient',
        'kg_per_ha',
        'source',
        'version',
    ];

    protected $casts = [
        'kg_per_ha' => 'decimal:2',
    ];

    public function variety(): BelongsTo
    {
        return $this->belongsTo(RiceVariety::class, 'variety_id');
    }
}