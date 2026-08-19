<?php

namespace App\Services;

use App\Models\DeviceToken;
use Illuminate\Database\Eloquent\Collection;

class DeviceTokenService
{
    public function getTokens(): Collection
    {
        return DeviceToken::with('user')->get();
    }
}