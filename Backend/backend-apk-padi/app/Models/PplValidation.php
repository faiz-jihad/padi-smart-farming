<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PplValidation extends Model
{
    protected $fillable = [
        'scan_id',
        'ppl_id',
        'status',
        'notes',
        'validated_at',
    ];

    protected $casts = [
        'validated_at' => 'datetime',
    ];

    public function ppl(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ppl_id');
    }
}