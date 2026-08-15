<?php

namespace App\Http\Controllers;

use App\Http\Resources\FertilizerRuleResource;
use App\Models\FertilizerRule;

class FertilizerRuleController extends Controller
{
    public function index()
    {
        $rules = FertilizerRule::with('variety')
            ->orderBy('phase')
            ->get();

        return FertilizerRuleResource::collection($rules);
    }
}