<?php

namespace App\Domain\FarmActivity\Actions;

use App\Models\CropSeason;
use App\Models\FarmActivity;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CreateFarmActivityAction
{
    public function execute(User $user, array $data): FarmActivity
    {
        $cropSeason = CropSeason::query()
            ->whereKey($data['crop_season_id'])
            ->whereHas('farm', function ($query) use ($user): void {
                $query->where('farmer_user_id', $user->id);
            })
            ->first();

        if (!$cropSeason) {
            throw ValidationException::withMessages([
                'crop_season_id' => [
                    'Musim tanam tidak ditemukan atau bukan milik pengguna.',
                ],
            ]);
        }

        return FarmActivity::create([
            'crop_season_id' => $cropSeason->id,
            'type' => $data['type'],
            'occurred_at' => $data['occurred_at'],
            'notes' => $data['notes'] ?? null,
            'cost' => $data['cost'] ?? 0,
        ]);
    }
}
