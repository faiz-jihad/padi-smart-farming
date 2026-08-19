<?php

namespace Database\Seeders;

use App\Enums\PlantingCalendarStatus;
use App\Enums\PlantingSeason;
use App\Models\District;
use App\Models\PlantingCalendar;
use App\Models\Regency;
use Illuminate\Database\Seeder;

/**
 * Contoh data Kalender Tanam untuk development.
 * Berdasarkan pola tanam umum Kabupaten Indramayu.
 */
class PlantingCalendarSeeder extends Seeder
{
    public function run(): void
    {
        $indramayu = Regency::where('code', '3212')->first();

        if (!$indramayu) {
            $this->command->error('[PlantingCalendarSeeder] Regency Indramayu tidak ditemukan. Jalankan RegionSeeder terlebih dahulu.');
            return;
        }

        // ─────────────────────────────────────────────
        // Kalender Tanam tingkat Kabupaten Indramayu
        // ─────────────────────────────────────────────
        PlantingCalendar::updateOrCreate(
            [
                'regency_id' => $indramayu->id,
                'season'     => PlantingSeason::Rainy,
                'year'       => 2026,
            ],
            [
                'planting_start'   => '2026-11-01',
                'planting_end'     => '2026-11-30',
                'planting_pattern' => 'Padi-Palawija-Bera',
                'rice_variety'     => 'Ciherang, IR64, Inpari 32',
                'recommended_area' => 120000.00,
                'status'           => PlantingCalendarStatus::Active,
                'source'           => 'Dinas Pertanian Kabupaten Indramayu 2026',
                'notes'            => 'Anjuran tanam awal musim hujan. Waspadai serangan wereng coklat.',
            ]
        );

        PlantingCalendar::updateOrCreate(
            [
                'regency_id' => $indramayu->id,
                'season'     => PlantingSeason::Dry,
                'year'       => 2026,
            ],
            [
                'planting_start'   => '2026-04-01',
                'planting_end'     => '2026-04-30',
                'planting_pattern' => 'Padi-Bera-Padi',
                'rice_variety'     => 'Inpari 32, Inpari 42, Mekongga',
                'recommended_area' => 85000.00,
                'status'           => PlantingCalendarStatus::Active,
                'source'           => 'Dinas Pertanian Kabupaten Indramayu 2026',
                'notes'            => 'Musim kemarau — pastikan ketersediaan air irigasi. Prioritas varietas tahan kering.',
            ]
        );

        // ─────────────────────────────────────────────
        // Kalender Tanam tingkat Kecamatan Kandanghaur
        // ─────────────────────────────────────────────
        $kandanghaur = District::where('code', '321206')->first();

        if ($kandanghaur) {
            PlantingCalendar::updateOrCreate(
                [
                    'district_id' => $kandanghaur->id,
                    'season'      => PlantingSeason::Dry,
                    'year'        => 2026,
                ],
                [
                    'regency_id'       => $indramayu->id,
                    'planting_start'   => '2026-04-01',
                    'planting_end'     => '2026-04-20',
                    'planting_pattern' => 'Padi-Bera-Padi',
                    'rice_variety'     => 'Inpari 32, Ciherang',
                    'recommended_area' => 8500.00,
                    'status'           => PlantingCalendarStatus::Active,
                    'source'           => 'UPTD Pertanian Kandanghaur 2026',
                    'notes'            => 'Awal tanam lebih awal 10 hari dibanding kabupaten karena sistem irigasi teknis.',
                ]
            );
        }

        // ─────────────────────────────────────────────
        // Kalender Tanam tingkat Kecamatan Jatibarang
        // ─────────────────────────────────────────────
        $jatibarang = District::where('code', '321215')->first();

        if ($jatibarang) {
            PlantingCalendar::updateOrCreate(
                [
                    'district_id' => $jatibarang->id,
                    'season'      => PlantingSeason::Rainy,
                    'year'        => 2026,
                ],
                [
                    'regency_id'       => $indramayu->id,
                    'planting_start'   => '2026-11-01',
                    'planting_end'     => '2026-12-15',
                    'planting_pattern' => 'Padi-Palawija',
                    'rice_variety'     => 'IR64, Inpari 42',
                    'recommended_area' => 6200.00,
                    'status'           => PlantingCalendarStatus::Active,
                    'source'           => 'UPTD Pertanian Jatibarang 2026',
                    'notes'            => 'Sebagian lahan bergantung pada curah hujan. Rekomendasi varietas toleran rendaman.',
                ]
            );
        }

        $this->command->info('[PlantingCalendarSeeder] Selesai. Data kalender tanam Indramayu 2026 berhasil diisi.');
    }
}
