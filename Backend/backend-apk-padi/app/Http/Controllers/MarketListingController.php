<?php

namespace App\Http\Controllers;

use App\Http\Resources\MarketListingResource;
use App\Services\Api\ApiResourceIndexService;

class MarketListingController extends Controller
{
    public function index(ApiResourceIndexService $resources)
    {
        return MarketListingResource::collection($resources->marketListings());
    }
}
