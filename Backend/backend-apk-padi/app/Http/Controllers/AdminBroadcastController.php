<?php

namespace App\Http\Controllers;

use App\Http\Resources\AdminBroadcastResource;
use App\Models\AdminBroadcast;

class AdminBroadcastController extends Controller
{
    public function index()
    {
        $broadcasts = AdminBroadcast::query()
            ->with('admin')
            ->where('status', 'published')
            ->where(function ($query) {
                $query
                    ->whereNull('target_role')
                    ->orWhere('target_role', 'farmer');
            })
            ->latest('id')
            ->get();

        return AdminBroadcastResource::collection($broadcasts);
    }
}
