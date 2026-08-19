<?php

namespace Database\Seeders;

use App\Enums\RegencyType;
use App\Enums\VillageType;
use App\Models\District;
use App\Models\DistrictBoundary;
use App\Models\Province;
use App\Models\Regency;
use App\Models\Village;
use Illuminate\Database\Seeder;

/**
 * Seeder data wilayah untuk lingkungan development.
 * Data ini bukan dari CSV resmi — hanya untuk keperluan demo/development.
 * Gunakan `php artisan region:import` untuk import data resmi dari Kemendagri.
 */
class RegionSeeder extends Seeder
{
    public function run(): void
    {
        // ─────────────────────────────────────────────
        // 1. Provinsi Jawa Barat
        // ─────────────────────────────────────────────
        $jabar = Province::updateOrCreate(
            ['code' => '32'],
            [
                'name'      => 'Jawa Barat',
                'latitude'  => -6.9174639,
                'longitude' => 107.6191228,
            ]
        );

        // ─────────────────────────────────────────────
        // 2. Kabupaten Indramayu
        // ─────────────────────────────────────────────
        $indramayu = Regency::updateOrCreate(
            ['code' => '3212'],
            [
                'province_id' => $jabar->id,
                'name'        => 'Indramayu',
                'type'        => RegencyType::Regency,
                'latitude'    => -6.3271,
                'longitude'   => 108.3254,
            ]
        );

        // ─────────────────────────────────────────────
        // 3. Kecamatan-kecamatan Indramayu
        // ─────────────────────────────────────────────
        $kecamatanData = [
            ['code' => '321201', 'name' => 'Haurgeulis',  'lat' => -6.2497, 'lng' => 107.9872],
            ['code' => '321202', 'name' => 'Gantar',      'lat' => -6.3158, 'lng' => 108.0511],
            ['code' => '321203', 'name' => 'Kroya',       'lat' => -6.3741, 'lng' => 108.1022],
            ['code' => '321205', 'name' => 'Losarang',    'lat' => -6.2780, 'lng' => 108.2134],
            ['code' => '321206', 'name' => 'Kandanghaur', 'lat' => -6.2500, 'lng' => 108.0800],
            ['code' => '321207', 'name' => 'Bongas',      'lat' => -6.2915, 'lng' => 108.1565],
            ['code' => '321208', 'name' => 'Arahan',      'lat' => -6.2543, 'lng' => 108.1841],
            ['code' => '321215', 'name' => 'Jatibarang',  'lat' => -6.4726, 'lng' => 108.3009],
            ['code' => '321219', 'name' => 'Sindang',     'lat' => -6.3284, 'lng' => 108.3129],
            ['code' => '321225', 'name' => 'Indramayu',   'lat' => -6.3263, 'lng' => 108.3228],
        ];

        $kecamatanModels = [];
        foreach ($kecamatanData as $kec) {
            $kecamatanModels[$kec['code']] = District::updateOrCreate(
                ['code' => $kec['code']],
                [
                    'regency_id' => $indramayu->id,
                    'name'       => $kec['name'],
                    'latitude'   => $kec['lat'],
                    'longitude'  => $kec['lng'],
                ]
            );
        }

        // ─────────────────────────────────────────────
        // 4. Desa-desa per kecamatan (sample)
        // ─────────────────────────────────────────────
        $desaData = [
            '321206' => [
                ['code' => '3212060001', 'name' => 'Kandanghaur', 'type' => 'village',       'lat' => -6.2499, 'lng' => 108.0795],
                ['code' => '3212060002', 'name' => 'Wirakanan',   'type' => 'village',       'lat' => -6.2612, 'lng' => 108.0852],
                ['code' => '3212060003', 'name' => 'Ilir',        'type' => 'village',       'lat' => -6.2380, 'lng' => 108.0702],
                ['code' => '3212060004', 'name' => 'Bulak',       'type' => 'village',       'lat' => -6.2451, 'lng' => 108.0910],
                ['code' => '3212060005', 'name' => 'Karanganyar', 'type' => 'village',       'lat' => -6.2698, 'lng' => 108.0763],
            ],
            '321205' => [
                ['code' => '3212050001', 'name' => 'Losarang',    'type' => 'village',       'lat' => -6.2780, 'lng' => 108.2134],
                ['code' => '3212050002', 'name' => 'Muntur',      'type' => 'village',       'lat' => -6.2849, 'lng' => 108.2198],
                ['code' => '3212050003', 'name' => 'Pabeanudik',  'type' => 'village',       'lat' => -6.2700, 'lng' => 108.2060],
                ['code' => '3212050004', 'name' => 'Tegal Agung', 'type' => 'village',       'lat' => -6.2931, 'lng' => 108.2251],
            ],
            '321219' => [
                ['code' => '3212190001', 'name' => 'Sindang',     'type' => 'village',       'lat' => -6.3284, 'lng' => 108.3129],
                ['code' => '3212190002', 'name' => 'Pabean Ilir', 'type' => 'village',       'lat' => -6.3200, 'lng' => 108.3050],
                ['code' => '3212190003', 'name' => 'Brondong',    'type' => 'village',       'lat' => -6.3351, 'lng' => 108.3210],
            ],
            '321215' => [
                ['code' => '3212150001', 'name' => 'Jatibarang',  'type' => 'urban_village', 'lat' => -6.4726, 'lng' => 108.3009],
                ['code' => '3212150002', 'name' => 'Krasak',      'type' => 'village',       'lat' => -6.4810, 'lng' => 108.2984],
                ['code' => '3212150003', 'name' => 'Pilangsari',  'type' => 'village',       'lat' => -6.4638, 'lng' => 108.3073],
                ['code' => '3212150004', 'name' => 'Bulak',       'type' => 'village',       'lat' => -6.4792, 'lng' => 108.3110],
            ],
            '321225' => [
                ['code' => '3212250001', 'name' => 'Karanganyar', 'type' => 'urban_village', 'lat' => -6.3263, 'lng' => 108.3228],
                ['code' => '3212250002', 'name' => 'Pekandangan', 'type' => 'village',       'lat' => -6.3321, 'lng' => 108.3301],
                ['code' => '3212250003', 'name' => 'Bojongsari',  'type' => 'village',       'lat' => -6.3182, 'lng' => 108.3155],
                ['code' => '3212250004', 'name' => 'Telukagung',  'type' => 'village',       'lat' => -6.3098, 'lng' => 108.3402],
            ],
        ];

        foreach ($desaData as $kecCode => $desas) {
            if (!isset($kecamatanModels[$kecCode])) {
                continue;
            }
            $districtId = $kecamatanModels[$kecCode]->id;
            foreach ($desas as $desa) {
                Village::updateOrCreate(
                    ['code' => $desa['code']],
                    [
                        'district_id' => $districtId,
                        'name'        => $desa['name'],
                        'type'        => VillageType::from($desa['type']),
                        'latitude'    => $desa['lat'],
                        'longitude'   => $desa['lng'],
                    ]
                );
            }
        }

        // ─────────────────────────────────────────────
        // 5. District Boundaries — semua kecamatan Indramayu
        //    Polygon approximate berdasarkan koordinat geografis BPS.
        //    Import data nyata: php artisan geo:import-district-boundaries
        // ─────────────────────────────────────────────
        $districtBoundaries = [
            '321201' => [
                'coords' => [[107.9200,-6.1900],[108.0200,-6.1900],[108.0600,-6.2400],[108.0200,-6.2900],[107.9200,-6.2900],[107.8800,-6.2400],[107.9200,-6.1900]],
                'bbox'   => [107.8800,-6.2900,108.0600,-6.1900],
            ],
            '321202' => [
                'coords' => [[108.0100,-6.2600],[108.1000,-6.2600],[108.1100,-6.3100],[108.1000,-6.3700],[108.0100,-6.3700],[107.9900,-6.3100],[108.0100,-6.2600]],
                'bbox'   => [107.9900,-6.3700,108.1100,-6.2600],
            ],
            '321203' => [
                'coords' => [[108.0700,-6.3400],[108.1500,-6.3400],[108.1600,-6.4100],[108.1500,-6.4200],[108.0700,-6.4200],[108.0500,-6.3800],[108.0700,-6.3400]],
                'bbox'   => [108.0500,-6.4200,108.1600,-6.3400],
            ],
            '321205' => [
                'coords' => [[108.1800,-6.2400],[108.2500,-6.2400],[108.2700,-6.3000],[108.2500,-6.3200],[108.1800,-6.3200],[108.1600,-6.3000],[108.1800,-6.2400]],
                'bbox'   => [108.1600,-6.3200,108.2700,-6.2400],
            ],
            '321206' => [
                'coords' => [[108.0400,-6.2000],[108.1300,-6.2000],[108.1400,-6.2600],[108.1300,-6.3000],[108.0400,-6.3000],[108.0300,-6.2600],[108.0400,-6.2000]],
                'bbox'   => [108.0300,-6.3000,108.1400,-6.2000],
            ],
            '321207' => [
                'coords' => [[108.1300,-6.2500],[108.1900,-6.2500],[108.2000,-6.3100],[108.1900,-6.3200],[108.1300,-6.3200],[108.1200,-6.3100],[108.1300,-6.2500]],
                'bbox'   => [108.1200,-6.3200,108.2000,-6.2500],
            ],
            '321208' => [
                'coords' => [[108.1600,-6.2100],[108.2200,-6.2100],[108.2300,-6.2600],[108.2200,-6.2900],[108.1600,-6.2900],[108.1500,-6.2600],[108.1600,-6.2100]],
                'bbox'   => [108.1500,-6.2900,108.2300,-6.2100],
            ],
            '321215' => [
                'coords' => [[108.2700,-6.4400],[108.3300,-6.4400],[108.3400,-6.5100],[108.3300,-6.5200],[108.2700,-6.5200],[108.2600,-6.5100],[108.2700,-6.4400]],
                'bbox'   => [108.2600,-6.5200,108.3400,-6.4400],
            ],
            '321219' => [
                'coords' => [[108.2800,-6.2900],[108.3400,-6.2900],[108.3500,-6.3500],[108.3400,-6.3700],[108.2800,-6.3700],[108.2700,-6.3500],[108.2800,-6.2900]],
                'bbox'   => [108.2700,-6.3700,108.3500,-6.2900],
            ],
            '321225' => [
                'coords' => [[108.3100,-6.3000],[108.3600,-6.3000],[108.3700,-6.3500],[108.3600,-6.3800],[108.3100,-6.3800],[108.3000,-6.3500],[108.3100,-6.3000]],
                'bbox'   => [108.3000,-6.3800,108.3700,-6.3000],
            ],
        ];

        foreach ($districtBoundaries as $code => $bound) {
            if (!isset($kecamatanModels[$code])) {
                continue;
            }
            DistrictBoundary::updateOrCreate(
                ['district_id' => $kecamatanModels[$code]->id],
                [
                    'geometry' => json_encode([
                        'type'        => 'Polygon',
                        'coordinates' => [$bound['coords']],
                    ]),
                    'bbox' => $bound['bbox'],
                ]
            );
        }

        // ─────────────────────────────────────────────
        // 6. Village Boundaries — Kandanghaur & Losarang
        // ─────────────────────────────────────────────
        if (class_exists(\App\Models\VillageBoundary::class)) {
            $villageBoundaries = [
                '3212060001' => [
                    'coords' => [[108.0700,-6.2350],[108.0950,-6.2350],[108.0950,-6.2600],[108.0700,-6.2600],[108.0700,-6.2350]],
                    'bbox'   => [108.0700,-6.2600,108.0950,-6.2350],
                ],
                '3212060002' => [
                    'coords' => [[108.0750,-6.2550],[108.1000,-6.2550],[108.1000,-6.2750],[108.0750,-6.2750],[108.0750,-6.2550]],
                    'bbox'   => [108.0750,-6.2750,108.1000,-6.2550],
                ],
                '3212060003' => [
                    'coords' => [[108.0550,-6.2200],[108.0800,-6.2200],[108.0800,-6.2500],[108.0550,-6.2500],[108.0550,-6.2200]],
                    'bbox'   => [108.0550,-6.2500,108.0800,-6.2200],
                ],
                '3212060004' => [
                    'coords' => [[108.0800,-6.2350],[108.1050,-6.2350],[108.1050,-6.2600],[108.0800,-6.2600],[108.0800,-6.2350]],
                    'bbox'   => [108.0800,-6.2600,108.1050,-6.2350],
                ],
                '3212060005' => [
                    'coords' => [[108.0600,-6.2600],[108.0900,-6.2600],[108.0900,-6.2850],[108.0600,-6.2850],[108.0600,-6.2600]],
                    'bbox'   => [108.0600,-6.2850,108.0900,-6.2600],
                ],
                '3212050001' => [
                    'coords' => [[108.2000,-6.2600],[108.2250,-6.2600],[108.2250,-6.2900],[108.2000,-6.2900],[108.2000,-6.2600]],
                    'bbox'   => [108.2000,-6.2900,108.2250,-6.2600],
                ],
                '3212050002' => [
                    'coords' => [[108.2100,-6.2700],[108.2350,-6.2700],[108.2350,-6.3000],[108.2100,-6.3000],[108.2100,-6.2700]],
                    'bbox'   => [108.2100,-6.3000,108.2350,-6.2700],
                ],
                '3212050003' => [
                    'coords' => [[108.1900,-6.2550],[108.2150,-6.2550],[108.2150,-6.2800],[108.1900,-6.2800],[108.1900,-6.2550]],
                    'bbox'   => [108.1900,-6.2800,108.2150,-6.2550],
                ],
                '3212050004' => [
                    'coords' => [[108.2200,-6.2800],[108.2450,-6.2800],[108.2450,-6.3100],[108.2200,-6.3100],[108.2200,-6.2800]],
                    'bbox'   => [108.2200,-6.3100,108.2450,-6.2800],
                ],
            ];

            foreach ($villageBoundaries as $villageCode => $bound) {
                $village = Village::where('code', $villageCode)->first();
                if (!$village) {
                    continue;
                }
                \App\Models\VillageBoundary::updateOrCreate(
                    ['village_id' => $village->id],
                    [
                        'geometry' => json_encode([
                            'type'        => 'Polygon',
                            'coordinates' => [$bound['coords']],
                        ]),
                        'bbox' => $bound['bbox'],
                    ]
                );
            }
        }

        $this->command->info('[RegionSeeder] Selesai. Data dev Jawa Barat + Indramayu berhasil diisi.');
        $this->command->warn('[RegionSeeder] CATATAN: Ini adalah data development. Gunakan `php artisan region:import` untuk data resmi Kemendagri.');
    }
}
