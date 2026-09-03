<?php

namespace App\Console\Commands;

use App\Models\Village;
use App\Models\VillageBoundary;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportVillageBoundariesCommand extends Command
{
    protected $signature = 'app:import-village-boundaries
                            {--province= : Hanya import provinsi tertentu, contoh 11}
                            {--fresh : Hapus semua village boundaries sebelum import}';

    protected $description = 'Import polygon batas desa/kelurahan dari file SQL wilayah_boundaries ke village_boundaries';

    public function handle(): int
    {
        $basePath = database_path('data/kel');

        if (!File::isDirectory($basePath)) {
            $this->error("Folder tidak ditemukan: {$basePath}");
            return self::FAILURE;
        }

        if ($this->option('fresh')) {
            $this->warn('Menghapus seluruh village_boundaries...');
            VillageBoundary::truncate();
        }

        $files = collect(File::allFiles($basePath))
            ->filter(fn ($file) => $file->getExtension() === 'sql');

        if ($files->isEmpty()) {
            $this->error('Tidak ada file SQL kelurahan/desa.');
            return self::FAILURE;
        }

        if ($province = $this->option('province')) {
            $files = $files->filter(function ($file) use ($province) {
                return str_contains(
                    $file->getFilename(),
                    "wilayah_boundaries_kel_{$province}."
                );
            });
        }

        $this->info("Ditemukan {$files->count()} file SQL.");

        $inserted = 0;
        $updated = 0;
        $notFound = 0;
        $invalid = 0;

        foreach ($files as $file) {
            $this->line("Processing: {$file->getRelativePathname()}");

            $content = File::get($file->getPathname());

            /*
             * Format:
             * ('11.01.01.2001','Keude Bakongan',lat,lng,'[[[...]]]')
             */
            preg_match_all(
                "/\('([^']+)','([^']*)',(-?\d+(?:\.\d+)?),(-?\d+(?:\.\d+)?),'(.*?)'\)(?:,|;)/s",
                $content,
                $matches,
                PREG_SET_ORDER
            );

            foreach ($matches as $match) {
                $rawCode = trim($match[1]);
                $name = trim(str_replace("\\'", "'", $match[2]));
                $pathJson = trim(str_replace("\\'", "'", $match[5]));

                // 11.01.01.2001 -> 1101012001
                $villageCode = str_replace('.', '', $rawCode);

                $village = Village::where('code', $villageCode)->first();

                if (!$village) {
                    $notFound++;

                    if ($notFound <= 20) {
                        $this->warn(
                            "Village tidak ditemukan: {$villageCode} - {$name}"
                        );
                    }

                    continue;
                }

                $coordinates = json_decode($pathJson, true);

                if (!is_array($coordinates)) {
                    $invalid++;

                    if ($invalid <= 20) {
                        $this->warn(
                            "Geometry invalid: {$villageCode} - {$name}"
                        );
                    }

                    continue;
                }

                $geometry = $this->normalizeGeoJson($coordinates);

                if (!$geometry) {
                    $invalid++;

                    if ($invalid <= 20) {
                        $this->warn(
                            "Geometry tidak dapat dinormalisasi: {$villageCode} - {$name}"
                        );
                    }

                    continue;
                }

                $bbox = $this->calculateBbox($coordinates);

                $boundary = VillageBoundary::updateOrCreate(
                    [
                        'village_id' => $village->id,
                    ],
                    [
                        'geometry' => json_encode(
                            $geometry,
                            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                        ),
                        'bbox' => $bbox,
                    ]
                );

                if ($boundary->wasRecentlyCreated) {
                    $inserted++;
                } else {
                    $updated++;
                }
            }
        }

        $this->newLine();
        $this->info('=== IMPORT SELESAI ===');
        $this->info("Inserted : {$inserted}");
        $this->info("Updated  : {$updated}");
        $this->info("Not Found: {$notFound}");
        $this->info("Invalid  : {$invalid}");
        $this->info("Total DB : " . VillageBoundary::count());

        return self::SUCCESS;
    }

    /**
     * Menentukan apakah koordinat merupakan satu titik [lat,lng].
     */
    private function isPoint(array $value): bool
    {
        return count($value) === 2
            && is_numeric($value[0])
            && is_numeric($value[1]);
    }

    /**
     * Normalisasi path menjadi GeoJSON Polygon/MultiPolygon.
     *
     * Source menggunakan urutan [lat,lng],
     * sedangkan GeoJSON menggunakan [lng,lat].
     */
    private function normalizeGeoJson(array $coords): ?array
    {
        if (empty($coords)) {
            return null;
        }

        // Polygon:
        // [
        //   [
        //      [lat,lng],
        //      [lat,lng]
        //   ]
        // ]
        if (
            isset($coords[0][0])
            && is_array($coords[0][0])
            && $this->isPoint($coords[0][0])
        ) {
            $rings = [];

            foreach ($coords as $ring) {
                if (!is_array($ring)) {
                    continue;
                }

                $newRing = [];

                foreach ($ring as $point) {
                    if ($this->isPoint($point)) {
                        $newRing[] = [
                            (float) $point[1],
                            (float) $point[0],
                        ];
                    }
                }

                if (count($newRing) >= 4) {
                    $rings[] = $newRing;
                }
            }

            return empty($rings)
                ? null
                : [
                    'type' => 'Polygon',
                    'coordinates' => $rings,
                ];
        }

        // MultiPolygon:
        // [
        //   [
        //      [
        //         [lat,lng],
        //         ...
        //      ]
        //   ]
        // ]
        if (
            isset($coords[0][0][0])
            && is_array($coords[0][0][0])
            && $this->isPoint($coords[0][0][0])
        ) {
            $polygons = [];

            foreach ($coords as $polygon) {
                $rings = [];

                foreach ($polygon as $ring) {
                    $newRing = [];

                    foreach ($ring as $point) {
                        if ($this->isPoint($point)) {
                            $newRing[] = [
                                (float) $point[1],
                                (float) $point[0],
                            ];
                        }
                    }

                    if (count($newRing) >= 4) {
                        $rings[] = $newRing;
                    }
                }

                if (!empty($rings)) {
                    $polygons[] = $rings;
                }
            }

            return empty($polygons)
                ? null
                : [
                    'type' => 'MultiPolygon',
                    'coordinates' => $polygons,
                ];
        }

        return null;
    }

    /**
     * Hitung bbox dalam format:
     * [minLng, minLat, maxLng, maxLat]
     */
    private function calculateBbox(array $coords): array
    {
        $points = [];

        $walk = function ($value) use (&$walk, &$points) {
            if ($this->isPoint($value)) {
                $points[] = [
                    (float) $value[1], // lng
                    (float) $value[0], // lat
                ];

                return;
            }

            if (is_array($value)) {
                foreach ($value as $child) {
                    $walk($child);
                }
            }
        };

        $walk($coords);

        if (empty($points)) {
            return [null, null, null, null];
        }

        $lngs = array_column($points, 0);
        $lats = array_column($points, 1);

        return [
            min($lngs),
            min($lats),
            max($lngs),
            max($lats),
        ];
    }
}