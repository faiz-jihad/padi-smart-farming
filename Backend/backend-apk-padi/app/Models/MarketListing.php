<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketListing extends Model
{
    protected $fillable = [
        'farmer_id',
        'farm_id',
        'crop_season_id',
        'harvest_id',
        'commodity',
        'quantity',
        'unit',
        'price_per_unit',
        'description',
        'status',
        'published_at',
        'expires_at',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'price_per_unit' => 'decimal:2',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function farmer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'farmer_id');
    }

    public function farm(): BelongsTo
    {
        return $this->belongsTo(Farm::class);
    }

    public function cropSeason(): BelongsTo
    {
        return $this->belongsTo(CropSeason::class);
    }

    public function harvest(): BelongsTo
    {
        return $this->belongsTo(Harvest::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ListingImage::class, 'listing_id');
    }

    public function offers(): HasMany
    {
        return $this->hasMany(MarketOffer::class);
    }
}