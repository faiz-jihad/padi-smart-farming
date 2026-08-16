<?php

namespace App\Services;

use App\Models\CropSeason;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class CropSeasonService
{
    public function getCropSeasons(User $user): Collection
    {
        return CropSeason::query()
            ->whereHas('farm', function ($query) use ($user): void {
                $query->where('farmer_user_id', $user->id);
            })
            ->latest('id')
            ->get();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createCropSeason(User $user, array $data): CropSeason
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