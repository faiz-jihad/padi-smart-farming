<?php

namespace App\Http\Controllers;

use App\Models\PartnerFavorite;

class PartnerFavoriteController extends Controller
{
    public function index()
    {
        $favorites = PartnerFavorite::with([
            'partner',
            'listing',
        ])->get();

        return response()->json([
            'success' => true,
            'data' => $favorites,
        ]);
    }
}