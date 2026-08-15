<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketOffer extends Model
{
    protected $fillable = [
        'listing_id',
        'partner_id',
        'offered_price',
        'quantity',
        'message',
        'status',
    ];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(MarketListing::class, 'listing_id');
    }

    public function partner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'partner_id');
    }
}