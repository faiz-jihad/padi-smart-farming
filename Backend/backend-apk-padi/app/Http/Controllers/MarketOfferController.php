<?php

namespace App\Http\Controllers;

use App\Http\Resources\MarketOfferResource;
use App\Services\MarketOfferService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MarketOfferController extends Controller
{
    public function index(
        MarketOfferService $service
    ): AnonymousResourceCollection {
        $offers = $service->getOffers();

        return MarketOfferResource::collection($offers);
    }
}