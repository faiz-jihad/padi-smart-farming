<?php

namespace App\Services;

use App\Models\FarmerProfile;
use Illuminate\Database\Eloquent\Collection;

class FarmerProfileService
{
    public function getFarmers(): Collection
    {
        return FarmerProfile::query()
            ->with('user')
            ->latest('id')
            ->get();
    }
}