<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventRegistration extends Model
{
    protected $table = 'event_registrations';

    protected $fillable = [
        'event_id',
        'user_id',
        'notes',
        'registered_at',
    ];

    protected $casts = [
        'registered_at' => 'datetime',
    ];

    protected $appends = [
        'ticket_code',
        'ticket_status',
    ];

    /**
     * Generate deterministik atau tersimpan kode tiket resmi e-ticket pertanian
     */
    public function getTicketCodeAttribute(): string
    {
        $eventId = str_pad((string) $this->event_id, 3, '0', STR_PAD_LEFT);
        $userId = str_pad((string) $this->user_id, 3, '0', STR_PAD_LEFT);
        $regId = str_pad((string) ($this->id ?? 1), 4, '0', STR_PAD_LEFT);

        return "TKT-PAD-{$eventId}-{$userId}-{$regId}";
    }

    public function getTicketStatusAttribute(): string
    {
        return 'active';
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(AgricultureEvent::class, 'event_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

