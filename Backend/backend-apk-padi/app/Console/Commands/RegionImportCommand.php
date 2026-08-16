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

    protected $description = 'Import data wilayah administratif Indonesia dari file CSV';

    public function handle(): int
    {
        $filePath = $this->option('file') ?: storage_path('app/data/regions.csv');
        $provinceFilter = $this->option('province');

        if (!File::exists($filePath)) {
            $this->warn("File tidak ditemukan di: {$filePath}");
            $this->line("Silakan letakkan file data CSV wilayah di 'storage/app/data/regions.csv'");
            $this->line("Format CSV yang diharapkan (tanpa header / header: code,name,type,lat,lng):");
            $this->line("Contoh format kode:");
            $this->line("- Provinsi (2 digit) : 32,JAWA BARAT");
            $this->line("- Kab/Kota (4 digit)  : 3212,KABUPATEN INDRAMAYU");
            $this->line("- Kecamatan (6 digit) : 321206,KANDANGHAUR");
            $this->line("- Desa/Kel (10 digit) : 3212062001,KANDANGHAUR");
            $this->info("Untuk seeding data dev Jawa Barat + Indramayu, jalankan: php artisan db:seed --class=RegionSeeder");
            return self::FAILURE;
        }

        $this->info("Memulai import data wilayah dari {$filePath}...");

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            $this->error("Gagal membuka file CSV.");
            return self::FAILURE;
        }

        $count = 0;
        DB::beginTransaction();

        try {
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                if (empty($row[0]) || empty($row[1])) {
                    continue;
                }

                $code = trim($row[0]);
                $name = trim($row[1]);
                $lat = isset($row[3]) && is_numeric($row[3]) ? (float) $row[3] : null;
                $lng = isset($row[4]) && is_numeric($row[4]) ? (float) $row[4] : null;

                if ($provinceFilter && !str_starts_with($code, $provinceFilter)) {
                    continue;
                }

                $codeLength = strlen($code);

                // Province: 2 digits
                if ($codeLength === 2) {
                    Province::updateOrCreate(
                        ['code' => $code],
                        ['name' => $name, 'latitude' => $lat, 'longitude' => $lng]
                    );
                    $count++;
                }
                // Regency: 4-5 digits (e.g. 32.12 or 3212)
                elseif ($codeLength === 4 || ($codeLength === 5 && str_contains($code, '.'))) {
                    $cleanCode = str_replace('.', '', $code);
                    $provCode = substr($cleanCode, 0, 2);
                    $province = Province::where('code', $provCode)->first();
                    if ($province) {
                        $type = str_contains(strtoupper($name), 'KOTA') ? RegencyType::City : RegencyType::Regency;
                        $cleanName = preg_replace('/^(KABUPATEN|KAB\.|KOTA)\s+/i', '', $name);
                        Regency::updateOrCreate(
                            ['code' => $cleanCode],
                            [
                                'province_id' => $province->id,
                                'name'        => $cleanName,
                                'type'        => $type,
                                'latitude'    => $lat,
                                'longitude'   => $lng,
                            ]
                        );
                        $count++;
                    }
                }
                // District: 6-8 digits
                elseif ($codeLength === 6 || ($codeLength === 8 && str_contains($code, '.'))) {
                    $cleanCode = str_replace('.', '', $code);
                    $regCode = substr($cleanCode, 0, 4);
                    $regency = Regency::where('code', $regCode)->first();
                    if ($regency) {
                        $cleanName = preg_replace('/^(KECAMATAN|KEC\.)\s+/i', '', $name);
                        District::updateOrCreate(
                            ['code' => $cleanCode],
                            [
                                'regency_id' => $regency->id,
                                'name'       => $cleanName,
                                'latitude'   => $lat,
                                'longitude'  => $lng,
                            ]
                        );
                        $count++;
                    }
                }
                // Village: 10-13 digits
                elseif ($codeLength >= 10) {
                    $cleanCode = str_replace('.', '', $code);
                    $distCode = substr($cleanCode, 0, 6);
                    $district = District::where('code', $distCode)->first();
                    if ($district) {
                        $type = str_contains(strtoupper($name), 'KELURAHAN') ? VillageType::UrbanVillage : VillageType::Village;
                        $cleanName = preg_replace('/^(DESA|KELURAHAN|KEL\.)\s+/i', '', $name);
                        Village::updateOrCreate(
                            ['code' => $cleanCode],
                            [
                                'district_id' => $district->id,
                                'name'        => $cleanName,
                                'type'        => $type,
                                'latitude'    => $lat,
                                'longitude'   => $lng,
                            ]
                        );
                        $count++;
                    }
                }
            }

            fclose($handle);
            DB::commit();
            $this->info("Import berhasil! Total {$count} data wilayah diproses.");
            return self::SUCCESS;
        } catch (\Throwable $e) {
            DB::rollBack();
            fclose($handle);
            $this->error("Error saat import: " . $e->getMessage());
            return self::FAILURE;
        }
    }
}
