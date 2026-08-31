<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ListingImage extends Model
{
    protected $fillable = [
        'listing_id',
        'image_url',
        'sort_order',
    ];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(MarketListing::class, 'listing_id');
    }
}
