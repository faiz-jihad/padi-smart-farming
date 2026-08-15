<?php

namespace App\Http\Controllers;

use App\Models\MarketOffer;

class MarketOfferController extends Controller
{
    public function index()
    {
        $offers = MarketOffer::with(['listing', 'partner'])->get();

        return response()->json([
            'success' => true,
            'data' => $offers,
        ]);
    }
}