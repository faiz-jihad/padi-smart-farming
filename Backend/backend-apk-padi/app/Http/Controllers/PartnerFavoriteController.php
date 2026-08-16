<?php

namespace App\Http\Controllers;

use App\Http\Resources\PartnerFavoriteResource;
use App\Services\PartnerFavoriteService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PartnerFavoriteController extends Controller
{
    public function index(
        PartnerFavoriteService $service
    ): AnonymousResourceCollection {
        $favorites = $service->getFavorites();

        return PartnerFavoriteResource::collection($favorites);
    }
}