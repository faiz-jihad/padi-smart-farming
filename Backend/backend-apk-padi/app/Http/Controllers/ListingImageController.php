<?php

namespace App\Http\Controllers;

use App\Models\ListingImage;

class ListingImageController extends Controller
{
    public function index()
    {
        $images = ListingImage::with('listing')->get();

        return response()->json([
            'success' => true,
            'data' => $images,
        ]);
    }
}