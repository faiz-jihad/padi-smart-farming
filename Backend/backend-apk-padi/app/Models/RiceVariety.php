<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiceVariety extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'name',
        'description',
        'duration_days',
        'source_reference',
        'is_active',
    ];

    protected $casts = [
        'duration_days' => 'integer',
        'is_active' => 'boolean',
    ];
}