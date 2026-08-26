<?php

namespace App\Services\Api;

use App\Models\CropSeason;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class CropSeasonService
{
    public function listForUser(User $user): Collection
    {
        return CropSeason::query()
            ->whereHas('farm', function ($query) use ($user): void {
                if (! $user->hasRole('admin')) {
                    $query->where('farmer_user_id', $user->id);
                }
            })
            ->latest('id')
            ->get();
    }
}
