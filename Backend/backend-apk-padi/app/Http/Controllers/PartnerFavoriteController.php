<?php

namespace App\Http\Controllers;

use App\Http\Resources\PartnerFavoriteResource;
use App\Models\PartnerFavorite;

class PartnerFavoriteController extends Controller
{
    public function index()
    {
        $favorites = PartnerFavorite::with([
            'partner',
            'listing',
        ])->get();

        return PartnerFavoriteResource::collection($favorites);
    }
}