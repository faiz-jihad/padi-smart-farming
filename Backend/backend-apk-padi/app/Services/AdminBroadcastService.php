<?php

namespace App\Services;

use App\Models\AdminBroadcast;
use Illuminate\Database\Eloquent\Collection;

class AdminBroadcastService
{
    public function getBroadcasts(): Collection
    {
        return AdminBroadcast::with('admin')
            ->latest()
            ->get();
    }
}