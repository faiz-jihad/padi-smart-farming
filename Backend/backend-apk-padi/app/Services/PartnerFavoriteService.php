<?php

namespace App\Services;

use App\Models\PartnerFavorite;
use Illuminate\Database\Eloquent\Collection;

class PartnerFavoriteService
{
    public function getFavorites(): Collection
    {
        return PartnerFavorite::with([
            'partner',
            'listing',
        ])->get();
    }
}