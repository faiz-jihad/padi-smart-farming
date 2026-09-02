<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'body',
        'data',
        'data_json',
        'read_at',
    ];

    protected $casts = [
        'data_json' => 'array',
        'read_at' => 'datetime',
    ];

    public function setDataAttribute($value): void
    {
        $this->attributes['data_json'] = is_array($value) ? json_encode($value) : $value;
    }

    public function getDataAttribute(): ?array
    {
        if (isset($this->attributes['data_json'])) {
            return is_array($this->attributes['data_json'])
                ? $this->attributes['data_json']
                : json_decode((string) $this->attributes['data_json'], true);
        }

        return null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}