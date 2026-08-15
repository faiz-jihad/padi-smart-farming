<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FarmerProfile extends Model
{
    protected $fillable = [
        'user_id',
        'village',
        'district',
        'regency',
        'province',
        'experience_years',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}