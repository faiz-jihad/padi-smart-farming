<?php

namespace App\Services;

use App\Models\WeatherSnapshot;
use Illuminate\Database\Eloquent\Collection;

class WeatherSnapshotService
{
    public function getSnapshots(): Collection
    {
        return WeatherSnapshot::with('farm')
            ->latest('observed_at')
            ->get();
    }
}