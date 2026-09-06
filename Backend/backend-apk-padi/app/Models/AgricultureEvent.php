<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AgricultureEvent extends Model
{
    use HasFactory;

    protected $table = 'agriculture_events';

    protected $fillable = [
        'title',
        'description',
        'category',
        'event_date',
        'event_time',
        'location_name',
        'location_address',
        'is_online',
        'organizer',
        'speaker',
        'quota',
        'registered_count',
        'price_type',
        'asset_image',
        'contact_person',
        'status',
        'created_by',
        'source',
        'approval_status',
        'rejection_reason',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'event_date' => 'date',
        'is_online' => 'boolean',
        'quota' => 'integer',
        'registered_count' => 'integer',
        'approved_at' => 'datetime',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class, 'event_id');
    }

    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('approval_status', 'pending');
    }

    public function scopeFarmerSubmissions($query)
    {
        return $query->where('source', 'farmer_submission');
    }

    public function isRegisteredBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $this->registrations()->where('user_id', $user->id)->exists();
    }

    public function getRegistrationForUser(?User $user): ?EventRegistration
    {
        if (! $user) {
            return null;
        }

        return $this->registrations()->where('user_id', $user->id)->first();
    }
}


