<?php

namespace App\Services\Api;

use App\Models\Farm;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class FarmService
{
    public function listForUser(User $user): Collection
    {
        return Farm::query()
            ->where('farmer_user_id', $user->id)
            ->latest('id')
            ->get();
    }
}
