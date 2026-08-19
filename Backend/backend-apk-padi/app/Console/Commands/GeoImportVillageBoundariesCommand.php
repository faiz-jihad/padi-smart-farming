<?php

namespace App\Console\Commands;

use App\Models\Village;
use App\Models\VillageBoundary;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class GeoImportVillageBoundariesCommand extends Command
{
    protected $signature = 'geo:import-village-boundaries 
                            {--file= : Path ke file GeoJSON batas desa}';

    protected $description = 'Import data GeoJSON polygon batas wilayah desa/kelurahan dari BIG / OSM';

    public function handle(): int
    {
        $filePath = $this->option('file') ?: storage_path('app/data/village_boundaries.geojson');

        if (!File::exists($filePath)) {
            $this->warn("File tidak ditemukan di: {$filePath}");
            $this->line("Silakan letakkan file GeoJSON batas desa di 'storage/app/data/village_boundaries.geojson'");
            $this->info("Untuk seeding data dev, jalankan: php artisan db:seed --class=RegionSeeder");
            return self::FAILURE;
        }

        $this->info("Membaca file GeoJSON dari {$filePath}...");
        $content = File::get($filePath);
        $geojson = json_decode($content, true);

        if (!$geojson || !isset($geojson['features'])) {
            $this->error("Format GeoJSON tidak valid atau tidak memiliki key 'features'.");
            return self::FAILURE;
        }

        $features = $geojson['features'];
        $count = 0;
        $this->info("Memproses " . count($features) . " feature polygon desa...");

        foreach ($features as $feature) {
            $properties = $feature['properties'] ?? [];
            $geometry = $feature['geometry'] ?? null;

            if (!$geometry) {
                continue;
            }

            $code = $properties['code'] 
                ?? $properties['KD_DESA'] 
                ?? $properties['KODE_DESA'] 
                ?? $properties['id'] 
                ?? null;

            $name = $properties['name'] 
                ?? $properties['NAMOBJ'] 
                ?? $properties['DESA'] 
                ?? null;

            $village = null;
            if ($code) {
                $cleanCode = str_replace('.', '', (string) $code);
                $village = Village::where('code', $cleanCode)->first();
            }

            if (!$village && $name) {
                $village = Village::where('name', 'like', trim($name))->first();
            }

            if ($village) {
                $bbox = $feature['bbox'] ?? $this->calculateBbox($geometry);

                VillageBoundary::updateOrCreate(
                    ['village_id' => $village->id],
                    [
                        'geometry' => json_encode($geometry),
                        'bbox'     => $bbox,
                    ]
                );

                // Invalidate cache
                Cache::forget("map:villages:district:{$village->district_id}");
                $count++;
            }
        }

        $this->info("Import batas desa selesai! Total {$count} batas desa berhasil disimpan.");
        return self::SUCCESS;
    }

    private function calculateBbox(array $geometry): ?array
    {
        $coordinates = $geometry['coordinates'] ?? [];
        if (empty($coordinates)) {
            return null;
        }

        $allPoints = [];
        array_walk_recursive($coordinates, function ($item) use (&$allPoints) {
            $allPoints[] = $item;
        });

        if (count($allPoints) < 2) {
            return null;
        }

        $lngs = [];
        $lats = [];

        for ($i = 0; $i < count($allPoints); $i += 2) {
            if (isset($allPoints[$i], $allPoints[$i + 1])) {
                $lngs[] = (float) $allPoints[$i];
                $lats[] = (float) $allPoints[$i + 1];
            }
        }

        if (empty($lngs) || empty($lats)) {
            return null;
        }

        return [
            min($lngs),
            min($lats),
            max($lngs),
            max($lats),
        ];
    }
}
