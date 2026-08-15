<?php

namespace App\Http\Controllers;

use App\Models\RiceVariety;

class RiceVarietyController extends Controller
{
    public function index()
    {
        $varieties = RiceVariety::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $varieties,
        ]);
    }
}