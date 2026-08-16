<?php

namespace App\Services;

use App\Models\FarmActivity;
use Illuminate\Database\Eloquent\Collection;

class FarmActivityService
{
    public function getActivities(): Collection
    {
        return FarmActivity::query()
            ->latest('occurred_at')
            ->get();
    }
}