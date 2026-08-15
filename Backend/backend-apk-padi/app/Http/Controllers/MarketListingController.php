<?php

namespace App\Http\Controllers;

use App\Http\Resources\MarketListingResource;
use App\Models\MarketListing;

class MarketListingController extends Controller
{
    public function index()
    {
        $listings = MarketListing::with([
            'farmer',
            'farm',
            'cropSeason',
            'harvest',
            'images',
            'offers',
        ])->get();

        return MarketListingResource::collection($listings);
    }
}