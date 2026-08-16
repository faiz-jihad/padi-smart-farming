<?php

namespace App\Services;

use App\Models\RiceVariety;
use Illuminate\Database\Eloquent\Collection;

class RiceVarietyService
{
    public function getActiveVarieties(): Collection
    {
        return RiceVariety::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }
}