<?php

namespace App\Services;

use App\Models\CropSeason;
use App\Models\FarmActivity;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;

class FarmActivityService
{
    public function getActivities(
        User $user,
        ?int $cropSeasonId = null
    ): Collection {
        $query = FarmActivity::query()
            ->whereHas('cropSeason.farm', function ($query) use ($user): void {
                if (! $user->hasRole('admin')) {
                    $query->where('farmer_user_id', $user->id);
                }
            })
            ->with(['cropSeason.farm'])
            ->latest('occurred_at');

        if ($cropSeasonId !== null) {
            $query->where('crop_season_id', $cropSeasonId);
        }

        return $query->get();
    }

    public function createActivity(
        User $user,
        array $data
    ): FarmActivity {
        $this->getAuthorizedCropSeason(
            $user,
            (int) $data['crop_season_id']
        );

        $activity = FarmActivity::query()->create(
            $this->filterWritableColumns(array_merge([
                'source' => 'MANUAL',
                'status' => 'COMPLETED',
                'sync_status' => 'pending',
            ], $data))
        );

        return $activity->load(['cropSeason.farm']);
    }

    public function getActivity(
        User $user,
        FarmActivity $activity
    ): FarmActivity {
        $this->authorizeActivity($user, $activity);

        return $activity->load(['cropSeason.farm']);
    }

    public function updateActivity(
        User $user,
        FarmActivity $activity,
        array $data
    ): FarmActivity {
        $this->authorizeActivity($user, $activity);

        if (
            isset($data['crop_season_id'])
            && (int) $data['crop_season_id'] !== $activity->crop_season_id
        ) {
            $this->getAuthorizedCropSeason(
                $user,
                (int) $data['crop_season_id']
            );
        }

        $activity->update($this->filterWritableColumns($data));

        return $activity->load(['cropSeason.farm']);
    }

    public function deleteActivity(
        User $user,
        FarmActivity $activity
    ): void {
        $this->authorizeActivity($user, $activity);

        $activity->delete();
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

    private function authorizeActivity(
        User $user,
        FarmActivity $activity
    ): void {
        $activity->loadMissing('cropSeason.farm');

        if (
            $user->hasRole('admin')
            || $activity->cropSeason?->farm?->farmer_user_id === $user->id
        ) {
            return;
        }

        abort(403, 'Anda tidak memiliki akses ke aktivitas pertanian ini');
    }

    private function filterWritableColumns(array $data): array
    {
        return collect($data)
            ->filter(
                fn ($value, string $key): bool => Schema::hasColumn(
                    'farm_activities',
                    $key
                )
            )
            ->all();
    }
}
