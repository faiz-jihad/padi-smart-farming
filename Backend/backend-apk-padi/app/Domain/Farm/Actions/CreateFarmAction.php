<?php

namespace App\Domain\Farm\Actions;

use App\Models\Farm;
use App\Models\User;

class CreateFarmAction
{
    /**
     * @param array<string, mixed> $data
     */
    public function execute(User $user, array $data): Farm
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