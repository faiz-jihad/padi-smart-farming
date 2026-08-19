<?php

namespace App\Services;

use App\Models\MarketListing;
use Illuminate\Database\Eloquent\Collection;

class MarketListingService
{
    public function getListings(): Collection
    {
        return MarketListing::with([
            'farmer',
            'farm',
            'cropSeason',
            'harvest',
            'images',
            'offers',
        ])->get();
    }
}