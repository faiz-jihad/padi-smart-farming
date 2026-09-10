<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class GovernmentSubscription extends Model
{
    use HasFactory;

    public const STATUS_PENDING_PAYMENT  = 'PENDING_PAYMENT';
    public const STATUS_PAID             = 'PAID';
    public const STATUS_PENDING_APPROVAL = 'PENDING_APPROVAL';
    public const STATUS_ACTIVE           = 'ACTIVE';
    public const STATUS_EXPIRED          = 'EXPIRED';
    public const STATUS_REVOKED          = 'REVOKED';
    public const STATUS_REJECTED         = 'REJECTED';

    protected $fillable = [
        'agency_name',
        'agency_email',
        'pic_name',
        'pic_phone',
        'purpose',
        'plan_name',
        'plan_days',
        'amount',
        'status',
        'token_hash',
        'token_preview',
        'started_at',
        'expires_at',
        'revoked_at',
        'approved_at',
    ];

    protected $casts = [
        'amount'      => 'decimal:2',
        'plan_days'   => 'integer',
        'started_at'  => 'datetime',
        'expires_at'  => 'datetime',
        'revoked_at'  => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function payments(): HasMany
    {
        return $this->hasMany(GovernmentPayment::class);
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(GovernmentPayment::class)->latestOfMany();
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->whereNull('revoked_at')
            ->where('expires_at', '>', Carbon::now());
    }

    public function isActive(): bool
    {
        if ($this->status !== self::STATUS_ACTIVE) {
            return false;
        }

        if ($this->revoked_at !== null) {
            return false;
        }

        if ($this->expires_at !== null && $this->expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Generate secure cryptographically random 6-digit access token
     */
    public function generateSixDigitToken(): string
    {
        $rawToken = (string) random_int(100000, 999999);

        $this->token_hash = hash('sha256', $rawToken);
        $this->token_preview = substr($rawToken, 0, 2) . '****';
        $this->status = self::STATUS_ACTIVE;
        $this->approved_at = Carbon::now();
        $this->started_at = Carbon::now();
        $this->expires_at = Carbon::now()->addDays($this->plan_days ?: 30);
        $this->revoked_at = null;
        $this->save();

        return $rawToken;
    }

    public function revoke(): void
    {
        $this->status = self::STATUS_REVOKED;
        $this->revoked_at = Carbon::now();
        $this->save();
    }

    public function reject(): void
    {
        $this->status = self::STATUS_REJECTED;
        $this->save();
    }

    public function getRemainingDaysAttribute(): int
    {
        if (! $this->isActive() || ! $this->expires_at) {
            return 0;
        }

        return max(0, (int) Carbon::now()->diffInDays($this->expires_at, false));
    }
}
