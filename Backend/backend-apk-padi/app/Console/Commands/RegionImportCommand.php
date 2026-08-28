<?php

namespace App\Console\Commands;

use App\Enums\RegencyType;
use App\Enums\VillageType;
use App\Models\District;
use App\Models\Province;
use App\Models\Regency;
use App\Models\Village;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class RegionImportCommand extends Command
{
    protected $signature = 'region:import
                            {--file= : Path ke file CSV wilayah Kemendagri/BPS}
                            {--province= : Kode provinsi tertentu (misal: 32)}';

    protected $description = 'Import data wilayah administratif Indonesia dari file CSV Kemendagri/BPS';

    public function handle(): int
    {
        $filePath = $this->option('file') ?: storage_path('app/data/regions.csv');
        $provinceFilter = $this->option('province');

        if (!File::exists($filePath)) {
            $this->error("File tidak ditemukan: {$filePath}");
            $this->line("Letakkan file CSV di storage/app/data/regions.csv");
            return self::FAILURE;
        }

        $this->info("Membaca file: {$filePath}");

        $handle = fopen($filePath, 'r');

        if (!$handle) {
            $this->error('Gagal membuka file CSV.');
            return self::FAILURE;
        }

        $header = fgetcsv($handle, 0, ',');

        if (!$header || count($header) < 9) {
            fclose($handle);
            $this->error('Format CSV tidak sesuai. File harus memiliki 9 kolom data Kemendagri.');
            return self::FAILURE;
        }

        $provinceFilter = $provinceFilter
            ? str_replace('.', '', trim($provinceFilter))
            : null;

        $provinceCache = [];
        $regencyCache = [];
        $districtCache = [];

        $countProvince = 0;
        $countRegency = 0;
        $countDistrict = 0;
        $countVillage = 0;

        DB::beginTransaction();

        try {
            while (($row = fgetcsv($handle, 0, ',')) !== false) {
                if (count($row) < 9) {
                    continue;
                }

                $villageCode  = $this->cleanCode($row[0]);
                $villageName  = trim($row[1]);
                $districtCode = $this->cleanCode($row[2]);
                $districtName = trim($row[3]);
                $regencyCode  = $this->cleanCode($row[4]);
                $regencyName  = trim($row[5]);
                $provinceCode = $this->cleanCode($row[6]);
                $provinceName = trim($row[7]);
                $villageType  = strtoupper(trim($row[8]));

                if (!$provinceCode || !$regencyCode || !$districtCode || !$villageCode) {
                    continue;
                }

                if ($provinceFilter && $provinceCode !== $provinceFilter) {
                    continue;
                }

                /*
                 * 1. Province
                 */
                if (!isset($provinceCache[$provinceCode])) {
                    $province = Province::updateOrCreate(
                        ['code' => $provinceCode],
                        [
                            'name' => $provinceName,
                            'latitude' => null,
                            'longitude' => null,
                        ]
                    );

                    $provinceCache[$provinceCode] = $province;
                    $countProvince++;
                } else {
                    $province = $provinceCache[$provinceCode];
                }

                /*
                 * 2. Regency
                 */
                if (!isset($regencyCache[$regencyCode])) {
                    $cleanRegencyName = preg_replace(
                        '/^(KABUPATEN|KAB\.|KOTA)\s+/i',
                        '',
                        $regencyName
                    );

                    $type = str_contains($regencyName, 'KOTA')
                        ? RegencyType::City
                        : RegencyType::Regency;

                    $regency = Regency::updateOrCreate(
                        ['code' => $regencyCode],
                        [
                            'province_id' => $province->id,
                            'name' => $cleanRegencyName,
                            'type' => $type,
                        ]
                    );

                    $regencyCache[$regencyCode] = $regency;
                    $countRegency++;
                } else {
                    $regency = $regencyCache[$regencyCode];
                }

                /*
                 * 3. District
                 */
                if (!isset($districtCache[$districtCode])) {
                    $cleanDistrictName = preg_replace(
                        '/^(KECAMATAN|KEC\.)\s+/i',
                        '',
                        $districtName
                    );

                    $district = District::updateOrCreate(
                        ['code' => $districtCode],
                        [
                            'regency_id' => $regency->id,
                            'name' => $cleanDistrictName,
                        ]
                    );

                    $districtCache[$districtCode] = $district;
                    $countDistrict++;
                } else {
                    $district = $districtCache[$districtCode];
                }

                /*
                 * 4. Village
                 */
                $cleanVillageName = preg_replace(
                    '/^(DESA|KELURAHAN|KEL\.|DESA ADAT)\s+/i',
                    '',
                    $villageName
                );

                $type = match (true) {
                    str_contains($villageType, 'KELURAHAN') => VillageType::UrbanVillage,
                    str_contains($villageType, 'DESA ADAT') => VillageType::Village,
                    default => VillageType::Village,
                };

                Village::updateOrCreate(
                    ['code' => $villageCode],
                    [
                        'district_id' => $district->id,
                        'name' => $cleanVillageName,
                        'type' => $type,
                        'latitude' => null,
                        'longitude' => null,
                    ]
                );

                $countVillage++;
            }

            fclose($handle);
            DB::commit();

            $this->newLine();
            $this->info('Import berhasil!');
            $this->line("Province : {$countProvince}");
            $this->line("Regency  : {$countRegency}");
            $this->line("District : {$countDistrict}");
            $this->line("Village  : {$countVillage}");

            return self::SUCCESS;

        } catch (\Throwable $e) {
            DB::rollBack();
            fclose($handle);

            $this->error('Error saat import: ' . $e->getMessage());

            return self::FAILURE;
        }
    }

    private function cleanCode(?string $code): string
    {
        return str_replace('.', '', trim((string) $code));
    }
}