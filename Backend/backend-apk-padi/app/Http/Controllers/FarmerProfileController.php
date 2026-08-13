<?php

namespace App\Http\Controllers;

use App\Models\FarmerProfile;

class FarmerProfileController extends Controller
{
    public function index()
    {
        $farmers = FarmerProfile::with('user')->get();

        return response()->json($farmers);
    }
}