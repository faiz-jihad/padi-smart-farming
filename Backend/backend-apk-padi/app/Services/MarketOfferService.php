<?php

namespace App\Services;

use App\Models\MarketOffer;
use Illuminate\Database\Eloquent\Collection;

class MarketOfferService
{
    public function getOffers(): Collection
    {
        return MarketOffer::with([
            'listing',
            'partner',
        ])->get();
    }
}