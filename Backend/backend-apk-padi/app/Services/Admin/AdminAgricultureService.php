<?php

namespace App\Services\Admin;

use App\Models\CropSeason;
use App\Models\Farm;
use App\Models\Harvest;

class AdminAgricultureService
{
    /**
     * @return array<string, mixed>
     */
    public function indexData(): array
    {
        return [
            'title' => 'Pertanian',
            'farms' => Farm::query()->with(['farmer', 'cropSeasons.variety'])->latest('id')->paginate(12),
            'cropSeasons' => CropSeason::query()->with(['farm.farmer', 'variety'])->latest('id')->limit(10)->get(),
            'stats' => [
                'farms' => Farm::query()->count(),
                'area' => (float) Farm::query()->sum('area_ha'),
                'active_seasons' => CropSeason::query()->where('status', 'active')->count(),
                'harvests' => Harvest::query()->count(),
            ],
        ];
    }
}
