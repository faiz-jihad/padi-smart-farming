<?php

namespace App\Http\Controllers;

use App\Http\Resources\ListingImageResource;
use App\Services\ListingImageService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ListingImageController extends Controller
{
    public function index(
        ListingImageService $service
    ): AnonymousResourceCollection {
        $images = $service->getImages();

        return ListingImageResource::collection($images);
    }
}