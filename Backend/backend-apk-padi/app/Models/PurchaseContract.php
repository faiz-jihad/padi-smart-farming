<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseContract extends Model
{
    protected $fillable = [
        'listing_id',
        'farmer_id',
        'partner_id',
        'offer_id',
        'quantity',
        'agreed_price',
        'total_amount',
        'status',
        'contracted_at',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'agreed_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'contracted_at' => 'datetime',
    ];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(
            MarketListing::class,
            'listing_id'
        );
    }

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'farmer_id'
        );
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'partner_id'
        );
    }

    public function offer(): BelongsTo
    {
        return $this->belongsTo(
            MarketOffer::class,
            'offer_id'
        );
    }
}   
