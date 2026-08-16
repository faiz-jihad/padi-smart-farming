<?php

namespace App\Http\Controllers;

use App\Http\Resources\MarketListingResource;
use App\Services\MarketListingService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MarketListingController extends Controller
{
    public function index(
        MarketListingService $service
    ): AnonymousResourceCollection {
        $listings = $service->getListings();

        return MarketListingResource::collection($listings);
    }
}