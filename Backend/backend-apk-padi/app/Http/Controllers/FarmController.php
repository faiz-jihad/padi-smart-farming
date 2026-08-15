<?php

namespace App\Http\Controllers;

use App\Models\Farm;

class FarmController extends Controller
{
    public function index()
    {
        $farms = Farm::with('farmer')->get();

        return response()->json([
            'success' => true,
            'data' => $farms,
        ]);
    }
}