<?php

namespace App\Services\Agriculture;

use App\Models\CropSeason;
use App\Models\Farm;
use App\Models\RiceVariety;

class CropSeasonService
{
    /**
     * Ensure default Indonesian rice varieties exist in database
     */
    public function ensureDefaultVarietiesExist(): array
    {
        $varieties = [
            [
                'name' => 'Inpari 32 HDB',
                'description' => 'Tahan penyakit hawar daun bakteri, potensi hasil 10.5 ton/ha, tekstur nasi pulen.',
                'duration_days' => 115,
                'source_reference' => 'Balitbangtan Kementan RI',
                'is_active' => true,
            ],
            [
                'name' => 'Ciherang',
                'description' => 'Varietas populer tahan wereng coklat tipe 2, potensi 10 ton/ha.',
                'duration_days' => 116,
                'source_reference' => 'Balitbangtan Kementan RI',
                'is_active' => true,
            ],
            [
                'name' => 'Inpari 42 Agritan Green Super Rice',
                'description' => 'Tahan kekeringan dan perendaman, hemat pemupukan, potensi 10.5 ton/ha.',
                'duration_days' => 112,
                'source_reference' => 'Balitbangtan Kementan RI',
                'is_active' => true,
            ],
            [
                'name' => 'Mekongga',
                'description' => 'Tekstur nasi sedang-pulen, tahan penyakit blas dan wereng.',
                'duration_days' => 118,
                'source_reference' => 'Balitbangtan Kementan RI',
                'is_active' => true,
            ],
        ];

        $varietyModels = [];
        foreach ($varieties as $v) {
            $varietyModels[] = RiceVariety::firstOrCreate(
                ['name' => $v['name']],
                $v
            );
        }

        return $varietyModels;
    }

    /**
     * Auto-generate regional crop seasons for a specific farm
     */
    public function autoGenerateCropSeasonsForFarm(Farm $farm): array
    {
        if ($farm->cropSeasons()->count() > 0) {
            return $farm->cropSeasons()->get()->all();
        }

        $varieties = $this->ensureDefaultVarietiesExist();
        $v1 = $varieties[0]->id;
        $v2 = $varieties[1]->id;
        $v3 = $varieties[2]->id;

        $irrType = strtolower((string) ($farm->irrigation_type ?? 'technical'));

        // Pola Tanam Sentra Padi Indonesia (IP 300 vs IP 200 vs IP 100)
        if (str_contains($irrType, 'semi') || str_contains($irrType, 'sederhana')) {
            // Pola IP 200 (Padi - Padi - Palawija)
            $seasons = [
                [
                    'farm_id' => $farm->id,
                    'variety_id' => $v1,
                    'planned_planting_date' => '2025-12-01',
                    'planting_date' => '2025-12-01',
                    'estimated_harvest_date' => '2026-03-25',
                    'status' => 'completed',
                ],
                [
                    'farm_id' => $farm->id,
                    'variety_id' => $v2,
                    'planned_planting_date' => '2026-05-01',
                    'planting_date' => '2026-05-01',
                    'estimated_harvest_date' => '2026-08-25',
                    'status' => 'active',
                ],
                [
                    'farm_id' => $farm->id,
                    'variety_id' => $v3,
                    'planned_planting_date' => '2026-09-15',
                    'planting_date' => null,
                    'estimated_harvest_date' => '2026-12-15',
                    'status' => 'planned',
                ],
            ];
        } elseif (str_contains($irrType, 'tadah') || str_contains($irrType, 'rainfed')) {
            // Pola IP 100 (Padi Tadah Hujan - Palawija - Bera)
            $seasons = [
                [
                    'farm_id' => $farm->id,
                    'variety_id' => $v1,
                    'planned_planting_date' => '2025-12-15',
                    'planting_date' => '2025-12-15',
                    'estimated_harvest_date' => '2026-04-10',
                    'status' => 'completed',
                ],
                [
                    'farm_id' => $farm->id,
                    'variety_id' => $v2,
                    'planned_planting_date' => '2026-05-15',
                    'planting_date' => '2026-05-15',
                    'estimated_harvest_date' => '2026-08-15',
                    'status' => 'active',
                ],
                [
                    'farm_id' => $farm->id,
                    'variety_id' => $v3,
                    'planned_planting_date' => '2026-09-01',
                    'planting_date' => null,
                    'estimated_harvest_date' => '2026-11-30',
                    'status' => 'planned',
                ],
            ];
        } else {
            // Pola IP 300 (Irigasi Teknis Padi - Padi - Padi: Karawang, Subang, Ngawi, Sragen)
            $seasons = [
                [
                    'farm_id' => $farm->id,
                    'variety_id' => $v1,
                    'planned_planting_date' => '2025-11-15',
                    'planting_date' => '2025-11-15',
                    'estimated_harvest_date' => '2026-03-10',
                    'status' => 'completed',
                ],
                [
                    'farm_id' => $farm->id,
                    'variety_id' => $v2,
                    'planned_planting_date' => '2026-04-15',
                    'planting_date' => '2026-04-15',
                    'estimated_harvest_date' => '2026-08-10',
                    'status' => 'active',
                ],
                [
                    'farm_id' => $farm->id,
                    'variety_id' => $v3,
                    'planned_planting_date' => '2026-09-15',
                    'planting_date' => null,
                    'estimated_harvest_date' => '2027-01-10',
                    'status' => 'planned',
                ],
            ];
        }

        return \Illuminate\Support\Facades\DB::transaction(function () use ($seasons) {
            $created = [];
            foreach ($seasons as $s) {
                $created[] = CropSeason::create($s);
            }
            return $created;
        });
    }

    /**
     * Auto-generate crop seasons for all farms that do not have any
     */
    public function autoGenerateAllFarmsCropSeasons(): int
    {
        return \Illuminate\Support\Facades\DB::transaction(function () {
            $farms = Farm::doesntHave('cropSeasons')->get();
            $count = 0;

            foreach ($farms as $farm) {
                $this->autoGenerateCropSeasonsForFarm($farm);
                $count++;
            }

            return $count;
        });
    }
}

