<?php

namespace App\Http\Controllers;

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

        return response()->json([
            'success' => true,
            'data' => $listings,
        ]);
    }
}