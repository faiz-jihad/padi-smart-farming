<?php

namespace App\Http\Controllers;

use App\Models\FertilizerRule;

class FertilizerRuleController extends Controller
{
    public function index()
    {
        $rules = FertilizerRule::with('variety')
            ->orderBy('phase')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $rules,
        ]);
    }
}