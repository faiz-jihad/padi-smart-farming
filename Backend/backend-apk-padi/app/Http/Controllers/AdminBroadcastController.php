<?php

namespace App\Http\Controllers;

use App\Http\Resources\AdminBroadcastResource;
use App\Models\AdminBroadcast;

class AdminBroadcastController extends Controller
{
    public function index()
    {
        $broadcasts = AdminBroadcast::with('admin')
            ->latest()
            ->get();

        return AdminBroadcastResource::collection($broadcasts);
    }
}