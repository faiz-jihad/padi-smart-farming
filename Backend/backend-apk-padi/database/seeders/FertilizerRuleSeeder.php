<?php

namespace Database\Seeders;

use App\Models\FertilizerRule;
use App\Models\RiceVariety;
use App\Services\Agriculture\CropSeasonService;
use Illuminate\Database\Seeder;

class FertilizerRuleSeeder extends Seeder
{
    /**
     * Run the database seeds for Indonesian rice fertilization rules.
     */
    public function run(): void
    {
        $cropService = app(CropSeasonService::class);
        $cropService->ensureDefaultVarietiesExist();

        $varieties = RiceVariety::all();

        if ($varieties->isEmpty()) {
            return;
        }

        // Standar Pemupukan Berimbang Padi Sawah Irigasi & Tadah Hujan (Balitbangtan Kementan)
        $rulesData = [
            // 1. Urea (Nitrogen 46%)
            [
                'nutrient' => 'Urea',
                'phase' => 'Pupuk Dasar (0-7 HST)',
                'kg_per_ha' => 75.00,
                'source' => 'Petunjuk Teknis Pemupukan Berimbang Kementan RI',
                'version' => '2026.1',
            ],
            [
                'nutrient' => 'Urea',
                'phase' => 'Susulan I (21-25 HST)',
                'kg_per_ha' => 100.00,
                'source' => 'Petunjuk Teknis Pemupukan Berimbang Kementan RI',
                'version' => '2026.1',
            ],
            [
                'nutrient' => 'Urea',
                'phase' => 'Susulan II (40-45 HST)',
                'kg_per_ha' => 75.00,
                'source' => 'Petunjuk Teknis Pemupukan Berimbang Kementan RI',
                'version' => '2026.1',
            ],
            [
                'nutrient' => 'Urea',
                'phase' => 'Total 1 Musim Tanam',
                'kg_per_ha' => 250.00,
                'source' => 'Rekomendasi Pemupukan Padi Sawah Balitbangtan',
                'version' => '2026.1',
            ],

            // 2. NPK Phonska (15-15-15)
            [
                'nutrient' => 'NPK Phonska',
                'phase' => 'Pupuk Dasar (0-7 HST)',
                'kg_per_ha' => 150.00,
                'source' => 'Petunjuk Teknis Pemupukan Berimbang Kementan RI',
                'version' => '2026.1',
            ],
            [
                'nutrient' => 'NPK Phonska',
                'phase' => 'Susulan I (21-25 HST)',
                'kg_per_ha' => 150.00,
                'source' => 'Petunjuk Teknis Pemupukan Berimbang Kementan RI',
                'version' => '2026.1',
            ],
            [
                'nutrient' => 'NPK Phonska',
                'phase' => 'Total 1 Musim Tanam',
                'kg_per_ha' => 300.00,
                'source' => 'Rekomendasi Pemupukan Padi Sawah Balitbangtan',
                'version' => '2026.1',
            ],

            // 3. SP-36 / Fosfat (P2O5 36%)
            [
                'nutrient' => 'SP-36',
                'phase' => 'Pupuk Dasar (0-7 HST)',
                'kg_per_ha' => 100.00,
                'source' => 'Petunjuk Teknis Pemupukan Berimbang Kementan RI',
                'version' => '2026.1',
            ],
            [
                'nutrient' => 'SP-36',
                'phase' => 'Total 1 Musim Tanam',
                'kg_per_ha' => 100.00,
                'source' => 'Rekomendasi Pemupukan Padi Sawah Balitbangtan',
                'version' => '2026.1',
            ],

            // 4. KCl / MOP (Kalium K2O 60%)
            [
                'nutrient' => 'KCl',
                'phase' => 'Susulan I (21-25 HST)',
                'kg_per_ha' => 50.00,
                'source' => 'Petunjuk Teknis Pemupukan Berimbang Kementan RI',
                'version' => '2026.1',
            ],
            [
                'nutrient' => 'KCl',
                'phase' => 'Susulan II (40-45 HST)',
                'kg_per_ha' => 50.00,
                'source' => 'Petunjuk Teknis Pemupukan Berimbang Kementan RI',
                'version' => '2026.1',
            ],
            [
                'nutrient' => 'KCl',
                'phase' => 'Total 1 Musim Tanam',
                'kg_per_ha' => 100.00,
                'source' => 'Rekomendasi Pemupukan Padi Sawah Balitbangtan',
                'version' => '2026.1',
            ],

            // 5. Pupuk Organik / Kompos
            [
                'nutrient' => 'Pupuk Organik',
                'phase' => 'Olah Tanah / Pra-Tanam',
                'kg_per_ha' => 1500.00,
                'source' => 'Pedoman Pertanian Organik & Presisi Kementan',
                'version' => '2026.1',
            ],
            [
                'nutrient' => 'Pupuk Organik',
                'phase' => 'Total 1 Musim Tanam',
                'kg_per_ha' => 1500.00,
                'source' => 'Pedoman Pertanian Organik & Presisi Kementan',
                'version' => '2026.1',
            ],
        ];

        foreach ($varieties as $variety) {
            foreach ($rulesData as $rule) {
                FertilizerRule::updateOrCreate(
                    [
                        'variety_id' => $variety->id,
                        'nutrient' => $rule['nutrient'],
                        'phase' => $rule['phase'],
                    ],
                    [
                        'kg_per_ha' => $rule['kg_per_ha'],
                        'source' => $rule['source'],
                        'version' => $rule['version'],
                    ]
                );
            }
        }
    }
}
