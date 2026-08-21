<?php

namespace App\Services;

use App\Models\CropSeason;
use App\Models\Harvest;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class HarvestService
{
    public function getHarvests(User $user): Collection
    {
        return Harvest::query()
            ->whereHas('cropSeason.farm', function ($query) use ($user): void {
                if (! $user->hasRole('admin')) {
                    $query->where('farmer_user_id', $user->id);
                }
            })
            ->with(['cropSeason.farm'])
            ->latest('harvest_date')
            ->get();
    }

    public function createHarvest(
        User $user,
        array $data
    ): Harvest {
        $this->getAuthorizedCropSeason(
            $user,
            (int) $data['crop_season_id']
        );

        $harvest = Harvest::query()->create($data);

        return $harvest->load(['cropSeason.farm']);
    }

    public function getHarvest(
        User $user,
        Harvest $harvest
    ): Harvest {
        $this->authorizeHarvest($user, $harvest);

        return $harvest->load(['cropSeason.farm']);
    }

    public function updateHarvest(
        User $user,
        Harvest $harvest,
        array $data
    ): Harvest {
        $this->authorizeHarvest($user, $harvest);

        if (
            isset($data['crop_season_id'])
            && (int) $data['crop_season_id'] !== $harvest->crop_season_id
        ) {
            $this->getAuthorizedCropSeason(
                $user,
                (int) $data['crop_season_id']
            );
        }

        $harvest->update($data);

        return $harvest->load(['cropSeason.farm']);
    }

    public function deleteHarvest(
        User $user,
        Harvest $harvest
    ): void {
        $this->authorizeHarvest($user, $harvest);

        $harvest->delete();
    }

    private function getAuthorizedCropSeason(
        User $user,
        int $cropSeasonId
    ): CropSeason {
        $cropSeason = CropSeason::query()
            ->with('farm')
            ->findOrFail($cropSeasonId);

        if (
            $user->hasRole('admin')
            || $cropSeason->farm?->farmer_user_id === $user->id
        ) {
            return $cropSeason;
        }

        abort(403, 'Anda tidak memiliki akses ke musim tanam ini');
    }

    private function authorizeHarvest(
        User $user,
        Harvest $harvest
    ): void {
        $harvest->loadMissing('cropSeason.farm');

        if (
            $user->hasRole('admin')
            || $harvest->cropSeason?->farm?->farmer_user_id === $user->id
        ) {
            return;
        }

        abort(403, 'Anda tidak memiliki akses ke data panen ini');
    }
}