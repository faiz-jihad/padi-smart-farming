<?php

namespace App\Console\Commands;

use App\Models\District;
use App\Models\DistrictBoundary;
use App\Models\Regency;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Import boundary polygon kecamatan dari file SQL cahyadsn/wilayah_boundaries.
 *
 * Format kode di SQL: "32.12.01" (provinsi.kabupaten.kecamatan)
 * Format kode di DB:  "321201" (6 digit tanpa titik)
 *
 * Usage:
 *   php artisan geo:import-district-boundaries --all
 *   php artisan geo:import-district-boundaries --province=32
 *   php artisan geo:import-district-boundaries --regency=3212
 */
class ImportDistrictBoundariesCommand extends Command
{
    protected $signature = 'geo:import-district-boundaries-sql
                            {--file= : Path spesifik ke file SQL}
                            {--province= : Kode provinsi (contoh: 32 untuk Jawa Barat)}
                            {--regency= : Filter kode kabupaten (contoh: 3212)}
                            {--all : Import semua file provinsi di database/data/kec/}';

    protected $description = 'Import polygon batas kecamatan dari file SQL wilayah_boundaries ke tabel districts & district_boundaries';

    public function handle(): int
    {
        $all = $this->option('all');
        $provCode = $this->option('province');
        $regencyFilter = $this->option('regency');
        $specFile = $this->option('file');

        $files = [];

        if ($specFile) {
            if (!file_exists($specFile)) {
                $this->error("File tidak ditemukan: {$specFile}");
                return self::FAILURE;
            }
            $files[] = $specFile;
        } elseif ($provCode) {
            $f = database_path("data/kec/wilayah_boundaries_kec_{$provCode}.sql");
            if (!file_exists($f)) {
                $f = database_path("data/wilayah_boundaries_kec_{$provCode}.sql");
            }
            if (!file_exists($f)) {
                $this->error("File untuk provinsi {$provCode} tidak ditemukan.");
                return self::FAILURE;
            }
            $files[] = $f;
        } elseif ($all) {
            $pattern = database_path('data/kec/wilayah_boundaries_kec_*.sql');
            $files = glob($pattern);
            if (empty($files)) {
                $files = glob(database_path('data/wilayah_boundaries_kec_*.sql'));
            }
        } else {
            // Default to West Java (32) if no flag passed
            $f = database_path('data/kec/wilayah_boundaries_kec_32.sql');
            if (!file_exists($f)) {
                $f = database_path('data/wilayah_boundaries_kec_32.sql');
            }
            if (file_exists($f)) {
                $files[] = $f;
            }
        }

        if (empty($files)) {
            $this->error('Tidak ada file SQL wilayah kecamatan yang ditemukan.');
            return self::FAILURE;
        }

        $this->info("Memulai import kecamatan dari " . count($files) . " file SQL...");

        // Preload regency lookup: [ '3212' => 1, ... ]
        $regencyMap = Regency::pluck('id', 'code')->toArray();

        $totalImported = 0;
        $totalCreated = 0;

        foreach ($files as $file) {
            $filename = basename($file);
            $this->line("<comment>-> Memproses {$filename}...</comment>");

            $content = file_get_contents($file);
            $content = preg_replace('/\/\*.*?\*\//s', '', $content);

            $pattern = "/\('(\d{2}\.\d{2}\.\d{2})','([^']+)',(-?[\d.]+),(-?[\d.]+),'(\[\[.*?\]\])'\)/s";
            preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

            if (empty($matches)) {
                $this->warn("   Tidak ada data INSERT di {$filename}");
                continue;
            }

            $count = count($matches);
            $this->info("   Ditemukan {$count} kecamatan.");

            $bar = $this->output->createProgressBar($count);
            $bar->start();

            DB::beginTransaction();
            try {
                foreach ($matches as $row) {
                    [, $kodeRaw, $nama, $lat, $lng, $pathJson] = $row;
                    $kode = str_replace('.', '', $kodeRaw);

                    if ($regencyFilter && !str_starts_with($kode, $regencyFilter)) {
                        $bar->advance();
                        continue;
                    }

                    $regencyCode = substr($kode, 0, 4);
                    $regencyId = $regencyMap[$regencyCode] ?? null;

                    if (!$regencyId) {
                        // Coba cari regency dengan prefix
                        $matchedReg = Regency::where('code', $regencyCode)
                            ->orWhere('code', substr($kodeRaw, 0, 5))
                            ->first();
                        if ($matchedReg) {
                            $regencyId = $matchedReg->id;
                            $regencyMap[$regencyCode] = $regencyId;
                        }
                    }

                    if (!$regencyId) {
                        $bar->advance();
                        continue;
                    }

                    $rawCoords = json_decode($pathJson, true);
                    if (!$rawCoords) {
                        $bar->advance();
                        continue;
                    }

                    // Convert [lat, lng] -> [lng, lat]
                    $converted = $this->convertCoordinates($rawCoords);
                    $geometry = $this->normalizeGeoJson($converted);
                    $bbox = $this->calculateBbox($geometry);

                    // Update or create District
                    $district = District::updateOrCreate(
                        ['code' => $kode],
                        [
                            'regency_id' => $regencyId,
                            'name'       => $nama,
                            'latitude'   => (float) $lat,
                            'longitude'  => (float) $lng,
                        ]
                    );

                    // Update or create DistrictBoundary
                    DistrictBoundary::updateOrCreate(
                        ['district_id' => $district->id],
                        [
                            'geometry' => json_encode($geometry),
                            'bbox'     => $bbox,
                        ]
                    );

                    $totalImported++;
                    $bar->advance();
                }

                DB::commit();
                $bar->finish();
                $this->newLine();
            } catch (\Throwable $e) {
                DB::rollBack();
                $this->error("   Gagal import {$filename}: " . $e->getMessage());
            }
        }

        $this->info("Import kecamatan selesai! Total {$totalImported} kecamatan & batas polygon berhasil diimpor.");

        // Clear cache
        \Illuminate\Support\Facades\Cache::flush();

        return self::SUCCESS;
    }

    private function convertCoordinates(array $coords): array
    {
        if (empty($coords)) {
            return [];
        }

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

    private function normalizeGeoJson(array $coords): array
    {
        $depth = $this->getDepth($coords);

        if ($depth === 2) {
            // [[lng, lat], [lng, lat]] -> [[[lng, lat], [lng, lat]]] (Polygon)
            $coords = [$coords];
            $type = 'Polygon';
        } elseif ($depth === 3) {
            $type = 'Polygon';
        } elseif ($depth >= 4) {
            $type = 'MultiPolygon';
        } else {
            $type = 'Polygon';
        }

        return [
            'type'        => $type,
            'coordinates' => $coords,
        ];
    }

    private function getDepth(array $arr): int
    {
        if (empty($arr)) return 0;
        if (isset($arr[0]) && is_numeric($arr[0])) return 1;
        if (isset($arr[0]) && is_array($arr[0])) return 1 + $this->getDepth($arr[0]);
        return 0;
    }

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
