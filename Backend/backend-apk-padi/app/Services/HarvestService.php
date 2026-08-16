<?php

namespace App\Services;

use App\Models\Harvest;
use Illuminate\Database\Eloquent\Collection;

class HarvestService
{
    public function getHarvests(): Collection
    {
        return Harvest::with('cropSeason')->get();
    }
}