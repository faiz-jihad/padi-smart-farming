<?php

namespace App\Http\Controllers;

use App\Http\Resources\ListingImageResource;
use App\Services\Api\ApiResourceIndexService;

class ListingImageController extends Controller
{
    public function index(ApiResourceIndexService $resources)
    {
        return ListingImageResource::collection($resources->listingImages());
    }
}
