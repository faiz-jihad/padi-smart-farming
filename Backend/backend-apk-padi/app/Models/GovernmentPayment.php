<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GovernmentPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'government_subscription_id',
        'order_id',
        'transaction_id',
        'amount',
        'payment_method',
        'transaction_status',
        'snap_token',
        'snap_redirect_url',
        'paid_at',
        'raw_response',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'paid_at'      => 'datetime',
        'raw_response' => 'array',
    ];

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(GovernmentSubscription::class, 'government_subscription_id');
    }
}
