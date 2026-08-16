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
        // 3. Kecamatan-kecamatan (5 sample utama)
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
            // Kecamatan Kandanghaur
            '321206' => [
                ['code' => '3212060001', 'name' => 'Kandanghaur',   'type' => 'village',       'lat' => -6.2499, 'lng' => 108.0795],
                ['code' => '3212060002', 'name' => 'Wirakanan',     'type' => 'village',       'lat' => -6.2612, 'lng' => 108.0852],
                ['code' => '3212060003', 'name' => 'Ilir',          'type' => 'village',       'lat' => -6.2380, 'lng' => 108.0702],
                ['code' => '3212060004', 'name' => 'Bulak',         'type' => 'village',       'lat' => -6.2451, 'lng' => 108.0910],
                ['code' => '3212060005', 'name' => 'Karanganyar',   'type' => 'village',       'lat' => -6.2698, 'lng' => 108.0763],
            ],
            // Kecamatan Losarang
            '321205' => [
                ['code' => '3212050001', 'name' => 'Losarang',      'type' => 'village',       'lat' => -6.2780, 'lng' => 108.2134],
                ['code' => '3212050002', 'name' => 'Muntur',        'type' => 'village',       'lat' => -6.2849, 'lng' => 108.2198],
                ['code' => '3212050003', 'name' => 'Pabeanudik',    'type' => 'village',       'lat' => -6.2700, 'lng' => 108.2060],
                ['code' => '3212050004', 'name' => 'Tegal Agung',   'type' => 'village',       'lat' => -6.2931, 'lng' => 108.2251],
            ],
            // Kecamatan Sindang
            '321219' => [
                ['code' => '3212190001', 'name' => 'Sindang',       'type' => 'village',       'lat' => -6.3284, 'lng' => 108.3129],
                ['code' => '3212190002', 'name' => 'Pabean Ilir',   'type' => 'village',       'lat' => -6.3200, 'lng' => 108.3050],
                ['code' => '3212190003', 'name' => 'Brondong',      'type' => 'village',       'lat' => -6.3351, 'lng' => 108.3210],
            ],
            // Kecamatan Jatibarang
            '321215' => [
                ['code' => '3212150001', 'name' => 'Jatibarang',    'type' => 'urban_village', 'lat' => -6.4726, 'lng' => 108.3009],
                ['code' => '3212150002', 'name' => 'Krasak',        'type' => 'village',       'lat' => -6.4810, 'lng' => 108.2984],
                ['code' => '3212150003', 'name' => 'Pilangsari',    'type' => 'village',       'lat' => -6.4638, 'lng' => 108.3073],
                ['code' => '3212150004', 'name' => 'Bulak',         'type' => 'village',       'lat' => -6.4792, 'lng' => 108.3110],
            ],
            // Kecamatan Indramayu (Kota)
            '321225' => [
                ['code' => '3212250001', 'name' => 'Karanganyar',   'type' => 'urban_village', 'lat' => -6.3263, 'lng' => 108.3228],
                ['code' => '3212250002', 'name' => 'Pekandangan',   'type' => 'village',       'lat' => -6.3321, 'lng' => 108.3301],
                ['code' => '3212250003', 'name' => 'Bojongsari',    'type' => 'village',       'lat' => -6.3182, 'lng' => 108.3155],
                ['code' => '3212250004', 'name' => 'Telukagung',    'type' => 'village',       'lat' => -6.3098, 'lng' => 108.3402],
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
        // 5. Boundary placeholder untuk Kandanghaur (polygon sederhana)
        //    Data nyata dapat diimport dengan: php artisan geo:import-district-boundaries
        // ─────────────────────────────────────────────
        $kandanghaur = $kecamatanModels['321206'];
        DistrictBoundary::updateOrCreate(
            ['district_id' => $kandanghaur->id],
            [
                'geometry' => json_encode([
                    'type' => 'Polygon',
                    'coordinates' => [[
                        [108.0600, -6.2300],
                        [108.1100, -6.2300],
                        [108.1100, -6.2800],
                        [108.0600, -6.2800],
                        [108.0600, -6.2300],
                    ]],
                ]),
                'bbox' => [108.0600, -6.2800, 108.1100, -6.2300],
            ]
        );

        $this->command->info('[RegionSeeder] Selesai. Data dev Jawa Barat + Indramayu berhasil diisi.');
        $this->command->warn('[RegionSeeder] CATATAN: Ini adalah data development. Gunakan `php artisan region:import` untuk data resmi Kemendagri.');
    }
}
