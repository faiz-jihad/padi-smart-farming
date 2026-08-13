<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Farm extends Model
{
    protected $fillable = [
        'farmer_user_id',
        'name',
        'area_ha',
        'latitude',
        'longitude',
        'irrigation_type',
        'irrigation_notes',
    ];

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'farmer_user_id');
    }
}