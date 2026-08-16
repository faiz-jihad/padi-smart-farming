<?php

namespace App\Services;

use App\Models\Farm;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class FarmService
{
    /**
     * Get farms owned by the authenticated farmer.
     */
    public function getFarms(User $user): Collection
    {
        return Farm::query()
            ->where('farmer_user_id', $user->id)
            ->latest('id')
            ->get();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createFarm(User $user, array $data): Farm
    {
        return Farm::query()->create([
            'farmer_user_id' => $user->id,
            'name' => $data['name'],
            'area_ha' => $data['area_ha'],
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'irrigation_type' => $data['irrigation_type'],
            'irrigation_notes' => $data['irrigation_notes'] ?? null,
        ]);
    }
}