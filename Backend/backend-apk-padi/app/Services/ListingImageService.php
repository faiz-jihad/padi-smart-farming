<?php

namespace App\Services;

use App\Models\ListingImage;
use Illuminate\Database\Eloquent\Collection;

class ListingImageService
{
    public function getImages(): Collection
    {
        return ListingImage::with('listing')->get();
    }
}