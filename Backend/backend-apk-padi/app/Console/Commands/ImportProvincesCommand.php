<?php

namespace App\Console\Commands;

use App\Enums\RegencyType;
use App\Models\Province;
use App\Models\Regency;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Command untuk mengimpor seluruh 38 Provinsi Indonesia (dan 514 Kabupaten/Kota)
 * BESERTA POLYGON GEOMETRI LENGKAP dari data resmi Kepmendagri No 300.2.2-2430 Tahun 2025.
 *
 * Usage:
 *   php artisan geo:import-provinces
 *   php artisan geo:import-provinces --with-regencies
 */
class ImportProvincesCommand extends Command
{
    protected $signature = 'geo:import-provinces
                            {--file= : Path ke file SQL (default: database/data/wilayah_level_1_2.sql)}
                            {--with-regencies : Sekaligus import seluruh 514 Kabupaten/Kota beserta polygon}';

    protected $description = 'Import seluruh 38 Provinsi Indonesia (dan 514 Kab/Kota) beserta batas polygon GeoJSON';

    public function handle(): int
    {
        $file = $this->option('file') ?? database_path('data/wilayah_level_1_2.sql');
        $withRegencies = $this->option('with-regencies');

        if (!file_exists($file)) {
            $this->error("File tidak ditemukan: {$file}");
            return self::FAILURE;
        }

        $this->info("Membaca file data wilayah: {$file}");

        $handle = fopen($file, 'r');
        if (!$handle) {
            $this->error("Gagal membuka file: {$file}");
            return self::FAILURE;
        }

        $provinceCount = 0;
        $regencyCount  = 0;
        $provincesMap  = [];

        DB::beginTransaction();

        try {
            while (($line = fgets($handle)) !== false) {
                $line = trim($line);

                if (!str_starts_with($line, "('")) {
                    continue;
                }

                // Parse header: ('kode', 'nama', 'ibukota', lat, lng, ...
                if (!preg_match("/^\('([^']+)',\s*'([^']+)',\s*'([^']*)',\s*(-?[\d.]+),\s*(-?[\d.]+)/", $line, $mHeader)) {
                    continue;
                }

                $rawCode = $mHeader[1];
                $name    = $mHeader[2];
                $lat     = (float) $mHeader[4];
                $lng     = (float) $mHeader[5];

                // Parse path: '[[...]]' using fast string functions (avoids PCRE backtrack limits on huge polygons)
                $pathRaw = null;
                $firstBracket = strpos($line, "'[[");
                if ($firstBracket !== false) {
                    $lastBracket = strrpos($line, "]]'");
                    if ($lastBracket !== false) {
                        $pathRaw = substr($line, $firstBracket + 1, ($lastBracket + 2) - ($firstBracket + 1));
                    }
                }

                // Parse GeoJSON geometry & calculate bounding box
                $geometry = $pathRaw ? $this->parseGeometry($pathRaw) : null;
                $bbox     = $geometry ? $this->calculateBbox($geometry) : null;

                // Level 1: Provinsi (2 digit kode, misal: '11', '32')
                if (strlen($rawCode) === 2 && is_numeric($rawCode)) {
                    $province = Province::updateOrCreate(
                        ['code' => $rawCode],
                        [
                            'name'      => $name,
                            'latitude'  => $lat,
                            'longitude' => $lng,
                            'geometry'  => $geometry,
                            'bbox'      => $bbox,
                        ]
                    );

                    $provincesMap[$rawCode] = $province->id;
                    $provinceCount++;
                }
                // Level 2: Kabupaten / Kota (format: '11.01' atau '32.12')
                elseif ($withRegencies && str_contains($rawCode, '.') && strlen($rawCode) === 5) {
                    $cleanCode = str_replace('.', '', $rawCode);
                    $provCode  = substr($cleanCode, 0, 2);

                    $provinceId = $provincesMap[$provCode] ?? Province::where('code', $provCode)->value('id');

                    if ($provinceId) {
                        $isCity = str_starts_with(strtoupper($name), 'KOTA ');
                        $cleanName = preg_replace('/^(KABUPATEN|KOTA)\s+/i', '', $name);

                        Regency::updateOrCreate(
                            ['code' => $cleanCode],
                            [
                                'province_id' => $provinceId,
                                'name'        => $cleanName,
                                'type'        => $isCity ? RegencyType::City : RegencyType::Regency,
                                'latitude'    => $lat,
                                'longitude'   => $lng,
                                'geometry'    => $geometry,
                                'bbox'        => $bbox,
                            ]
                        );

                        $regencyCount++;
                    }
                }
            }

            fclose($handle);
            DB::commit();

            $this->newLine();
            $this->info("==========================================");
            $this->info(" Import Wilayah & Polygon Berhasil!");
            $this->info(" Total Provinsi dengan Polygon: {$provinceCount}");
            if ($withRegencies) {
                $this->info(" Total Kab/Kota dengan Polygon: {$regencyCount}");
            }
            $this->info("==========================================");

            return self::SUCCESS;
        } catch (\Throwable $e) {
            DB::rollBack();
            if (is_resource($handle)) {
                fclose($handle);
            }
            $this->error("Error saat import: " . $e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * Konversi string koordinat raw [lat, lng] dari wilayah_level_1_2 ke GeoJSON [lng, lat].
     */
    private function parseGeometry(string $pathRaw): ?array
    {
        $openCount  = substr_count($pathRaw, '[');
        $closeCount = substr_count($pathRaw, ']');
        if ($openCount > $closeCount) {
            $pathRaw .= str_repeat(']', $openCount - $closeCount);
        }

        $rawCoords = json_decode($pathRaw, true);
        if (!$rawCoords || !is_array($rawCoords)) {
            return null;
        }

        $converted = $this->convertCoordinates($rawCoords);
        if (empty($converted)) {
            return null;
        }

        // Tentukan tipe Polygon vs MultiPolygon
        $isMultiPolygon = isset($converted[0][0][0]) && is_array($converted[0][0][0]);

        return [
            'type'        => $isMultiPolygon ? 'MultiPolygon' : 'Polygon',
            'coordinates' => $converted,
        ];
    }

    /**
     * Rekursif membalik titik [lat, lng] -> [lng, lat]
     */
    private function convertCoordinates(array $coords): array
    {
        if (empty($coords)) {
            return [];
        }

        // Basis: [lat, lng]
        if (count($coords) === 2 && is_numeric($coords[0]) && is_numeric($coords[1])) {
            return [(float) $coords[1], (float) $coords[0]];
        }

        $result = [];
        foreach ($coords as $item) {
            if (is_array($item)) {
                $result[] = $this->convertCoordinates($item);
            }
        }
        return $result;
    }

    /**
     * Hitung bounding box [minLng, minLat, maxLng, maxLat] dari geometry GeoJSON.
     */
    private function calculateBbox(array $geometry): ?array
    {
        $points = [];
        $this->extractPoints($geometry['coordinates'], $points);

        if (empty($points)) {
            return null;
        }

        $lngs = array_column($points, 0);
        $lats = array_column($points, 1);

        return [
            round(min($lngs), 6),
            round(min($lats), 6),
            round(max($lngs), 6),
            round(max($lats), 6),
        ];
    }

    /**
     * Ekstrak semua titik [lng, lat] dari struktur koordinat bertingkat.
     */
    private function extractPoints(array $coords, array &$points): void
    {
        if (count($coords) === 2 && is_numeric($coords[0]) && is_numeric($coords[1])) {
            $points[] = $coords;
            return;
        }

        foreach ($coords as $item) {
            if (is_array($item)) {
                $this->extractPoints($item, $points);
            }
        }
    }
}
