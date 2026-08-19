<?php

namespace Database\Seeders;

use App\Services\Agriculture\CropSeasonService;
use Illuminate\Database\Seeder;

class CropSeasonSeeder extends Seeder
{
    /**
     * Seed default crop seasons for all farms using regional cropping patterns
     */
    public function run(): void
    {
        $service = app(CropSeasonService::class);
        $service->ensureDefaultVarietiesExist();
        $service->autoGenerateAllFarmsCropSeasons();
    }
}
