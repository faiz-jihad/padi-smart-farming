<?php

namespace App\Domain\Harvest\Actions;

use App\Models\Harvest;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CreateHarvestAction
{
    public function execute(User $user, array $data): Harvest
    {
        $cropSeason = $user->farms()
            ->whereHas('cropSeasons', function ($query) use ($data): void {
                $query->whereKey($data['crop_season_id']);
            })
            ->first();

        if (!$cropSeason) {
            throw ValidationException::withMessages([
                'crop_season_id' => [
                    'Musim tanam tidak ditemukan atau bukan milik pengguna.',
                ],
            ]);
        }

        return Harvest::create([
            'crop_season_id' => $data['crop_season_id'],
            'harvest_date' => $data['harvest_date'],
            'quantity' => $data['quantity'],
            'unit' => $data['unit'],
            'quality_grade' => $data['quality_grade'] ?? null,
            'moisture_percent' => $data['moisture_percent'] ?? null,
            'verification_status' => 'unverified',
        ]);
    }
}
