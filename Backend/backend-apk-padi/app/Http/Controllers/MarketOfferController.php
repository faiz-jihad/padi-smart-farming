<?php

namespace App\Http\Controllers;

use App\Http\Resources\MarketOfferResource;
use App\Models\MarketOffer;

class MarketOfferController extends Controller
{
    public function index()
    {
        $offers = MarketOffer::with(['listing', 'partner'])->get();

        return MarketOfferResource::collection($offers);
    }
}