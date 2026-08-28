<?php

namespace App\Console\Commands;

use App\Enums\RegencyType;
use App\Models\Province;
use App\Models\Regency;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportWilayahLevel12Command extends Command
{
    protected $signature = 'wilayah:import-level12
                            {--file= : Path ke file wilayah_level_1_2.sql}';

    protected $description = 'Import provinsi dan kabupaten/kota dari wilayah_level_1_2.sql';

    public function handle(): int
    {
        $file = $this->option('file')
            ?: database_path('data/wilayah_level_1_2.sql');

        if (!file_exists($file)) {
            $this->error("File tidak ditemukan: {$file}");
            return self::FAILURE;
        }

        $this->info("Membaca: {$file}");

        $content = file_get_contents($file);

        if ($content === false) {
            $this->error('Gagal membaca file SQL.');
            return self::FAILURE;
        }

        /*
         * Format data:
         *
         * ('32','Jawa Barat','Bandung', -6.90, 107.61, ...)
         *
         * Kita hanya mengambil:
         * kode, nama, lat, lng
         */
        $pattern = "/\('([^']+)','([^']+)',\s*'([^']*)',\s*(-?[\d.]+),\s*(-?[\d.]+)/";

        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

        if (empty($matches)) {
            $this->error('Tidak ditemukan data wilayah pada file SQL.');
            return self::FAILURE;
        }

        $this->info('Ditemukan ' . count($matches) . ' data wilayah.');

        $provinceCount = 0;
        $regencyCount = 0;

        DB::beginTransaction();

        try {
            foreach ($matches as $row) {
                $code = trim($row[1]);
                $name = trim($row[2]);
                $lat = (float) $row[4];
                $lng = (float) $row[5];

                // Hilangkan titik dari kode.
                // Contoh: 32.12 -> 3212
                $cleanCode = str_replace('.', '', $code);

                /*
                 * =========================
                 * PROVINCE
                 * =========================
                 */
                if (strlen($cleanCode) === 2) {
                    Province::updateOrCreate(
                        ['code' => $cleanCode],
                        [
                            'name' => $name,
                            'latitude' => $lat,
                            'longitude' => $lng,
                        ]
                    );

                    $provinceCount++;
                    continue;
                }

                /*
                 * =========================
                 * REGENCY / CITY
                 * =========================
                 */
                if (strlen($cleanCode) === 4) {
                    $provinceCode = substr($cleanCode, 0, 2);

                    $province = Province::where(
                        'code',
                        $provinceCode
                    )->first();

                    if (!$province) {
                        $this->warn(
                            "Provinsi {$provinceCode} untuk {$cleanCode} tidak ditemukan."
                        );
                        continue;
                    }

                    $upperName = strtoupper($name);

                    $type = str_contains($upperName, 'KOTA')
                        ? RegencyType::City
                        : RegencyType::Regency;

                    $cleanName = preg_replace(
                        '/^(KABUPATEN|KAB\.|KOTA)\s+/i',
                        '',
                        $name
                    );

                    Regency::updateOrCreate(
                        ['code' => $cleanCode],
                        [
                            'province_id' => $province->id,
                            'name' => trim($cleanName),
                            'type' => $type,
                            'latitude' => $lat,
                            'longitude' => $lng,
                        ]
                    );

                    $regencyCount++;
                }
            }

            DB::commit();

            $this->newLine();
            $this->info('====================================');
            $this->info('IMPORT BERHASIL');
            $this->info('Province : ' . $provinceCount);
            $this->info('Regency  : ' . $regencyCount);
            $this->info('====================================');

            return self::SUCCESS;

        } catch (\Throwable $e) {
            DB::rollBack();

            $this->error('Import gagal: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}