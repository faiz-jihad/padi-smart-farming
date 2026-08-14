<?php

namespace App\Http\Controllers;

use App\Models\DeviceToken;

class DeviceTokenController extends Controller
{
    public function index()
    {
        $tokens = DeviceToken::with('user')->get();

        return response()->json([
            'success' => true,
            'data' => $tokens,
        ]);
    }
}