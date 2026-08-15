<?php

namespace App\Http\Controllers;

use App\Http\Resources\PartnerFavoriteResource;
use App\Services\Api\ApiResourceIndexService;

class PartnerFavoriteController extends Controller
{
    public function index(ApiResourceIndexService $resources)
    {
        return PartnerFavoriteResource::collection($resources->partnerFavorites());
    }
}
