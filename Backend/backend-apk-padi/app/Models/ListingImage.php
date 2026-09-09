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

    public function getFormattedImageUrlAttribute(): ?string
    {
        $raw = $this->image_url;

        if (blank($raw)) {
            return null;
        }

        if (preg_match('/^(?:https?:|\/\/|data:)/i', $raw)) {
            return $raw;
        }

        $cleaned = ltrim($raw, '/');
        if (str_starts_with($cleaned, 'storage/')) {
            $cleaned = substr($cleaned, 8);
        }

        return asset('storage/' . $cleaned);
    }
}
