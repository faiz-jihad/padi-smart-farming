<?php

namespace App\Http\Controllers;

use App\Http\Resources\ListingImageResource;
use App\Models\ListingImage;

class ListingImageController extends Controller
{
    public function index()
    {
        $images = ListingImage::with('listing')->get();

        return ListingImageResource::collection($images);
    }
}