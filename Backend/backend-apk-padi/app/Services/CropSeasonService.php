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

        $status = $data['status'] ?? 'planned';

        $plantingDate = $data['planting_date'] ?? $data['planned_planting_date'] ?? null;
        $harvestDate = $data['estimated_harvest_date'] ?? null;

        if ($plantingDate && $harvestDate) {
            $hasOverlap = $farm->cropSeasons()
                ->whereNotIn('status', ['cancelled'])
                ->where(function ($query) use ($plantingDate, $harvestDate): void {
                    $query
                        ->whereDate('planting_date', '<=', $harvestDate)
                        ->whereDate('estimated_harvest_date', '>=', $plantingDate);
                })
                ->exists();

            if ($hasOverlap) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'farm_id' => 'Periode crop season bertabrakan dengan crop season lain di lahan ini.',
                ]);
            }
        }

        if ($status === 'active') {
            $hasActiveSeason = $farm->cropSeasons()
                ->where('status', 'active')
                ->exists();

            if ($hasActiveSeason) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'farm_id' => 'Lahan ini sudah memiliki crop season yang aktif.',
                ]);
            }
        }

        return CropSeason::query()->create([
            'farm_id' => $farm->id,
            'variety_id' => $data['variety_id'] ?? null,
            'planned_planting_date' => $data['planned_planting_date'] ?? null,
            'planting_date' => $data['planting_date'] ?? null,
            'estimated_harvest_date' => $data['estimated_harvest_date'] ?? null,
            'status' => $status,
        ]);
    }
}