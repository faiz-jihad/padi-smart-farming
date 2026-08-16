<?php

namespace App\Services;

use App\Models\FertilizerRule;
use Illuminate\Database\Eloquent\Collection;

class FertilizerRuleService
{
    public function getRules(): Collection
    {
        return FertilizerRule::with('variety')
            ->orderBy('phase')
            ->get();
    }
}