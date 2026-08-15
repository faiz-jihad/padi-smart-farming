<?php

namespace App\Http\Controllers;

use App\Models\AdminBroadcast;

class AdminBroadcastController extends Controller
{
    public function index()
    {
        $broadcasts = AdminBroadcast::with('admin')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $broadcasts,
        ]);
    }
}