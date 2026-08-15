<?php

namespace App\Domain\CropSeason\Actions;

use App\Models\CropSeason;
use App\Models\User;

class CreateCropSeasonAction
{
    /**
     * @param array<string, mixed> $data
     */
    public function execute(User $user, array $data): CropSeason
    {
        $farm = $user->farms()
            ->whereKey($data['farm_id'])
            ->firstOrFail();

        return CropSeason::query()->create([
            'farm_id' => $farm->id,
            'variety_id' => $data['variety_id'] ?? null,
            'planned_planting_date' => $data['planned_planting_date'] ?? null,
            'planting_date' => $data['planting_date'] ?? null,
            'estimated_harvest_date' => $data['estimated_harvest_date'] ?? null,
            'status' => $data['status'] ?? 'planned',
        ]);
    }
}