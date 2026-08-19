<?php

namespace App\Http\Controllers;

use App\Http\Resources\MarketOfferResource;
use App\Services\Api\ApiResourceIndexService;

class MarketOfferController extends Controller
{
    public function index(ApiResourceIndexService $resources)
    {
        return MarketOfferResource::collection($resources->marketOffers());
    }
}
