<?php

namespace App\Console\Commands;

use App\Models\District;
use App\Models\DistrictBoundary;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class GeoImportDistrictBoundariesCommand extends Command
{
    protected $signature = 'geo:import-district-boundaries 
                            {--file= : Path ke file GeoJSON batas kecamatan}';

    protected $description = 'Import data GeoJSON polygon batas wilayah kecamatan dari Badan Informasi Geospasial (BIG) / OSM';

    public function handle(): int
    {
        $filePath = $this->option('file') ?: storage_path('app/data/district_boundaries.geojson');

        if (!File::exists($filePath)) {
            $this->warn("File tidak ditemukan di: {$filePath}");
            $this->line("Silakan letakkan file GeoJSON batas kecamatan di 'storage/app/data/district_boundaries.geojson'");
            $this->line("Struktur GeoJSON yang diharapkan:");
            $this->line("- FeatureCollection");
            $this->line("- properties: code / KDPKAB / KDDIST (kode kecamatan BPS/Kemendagri)");
            $this->line("- geometry: Polygon atau MultiPolygon");
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
        $this->info("Memproses " . count($features) . " feature polygon...");

        foreach ($features as $feature) {
            $properties = $feature['properties'] ?? [];
            $geometry = $feature['geometry'] ?? null;

            if (!$geometry) {
                continue;
            }

            // Cari kode kecamatan dari berbagai kemungkinan key standar (Kemendagri / BPS / BIG)
            $code = $properties['code'] 
                ?? $properties['KD_KEC'] 
                ?? $properties['KODE_KEC'] 
                ?? $properties['id'] 
                ?? null;

            $name = $properties['name'] 
                ?? $properties['WADMKC'] 
                ?? $properties['KECAMATAN'] 
                ?? null;

            $district = null;
            if ($code) {
                $cleanCode = str_replace('.', '', (string) $code);
                $district = District::where('code', $cleanCode)->first();
            }

            if (!$district && $name) {
                $district = District::where('name', 'like', trim($name))->first();
            }

            if ($district) {
                // Hitung Bounding Box sederhana [minLng, minLat, maxLng, maxLat]
                $bbox = $feature['bbox'] ?? $this->calculateBbox($geometry);

                DistrictBoundary::updateOrCreate(
                    ['district_id' => $district->id],
                    [
                        'geometry' => json_encode($geometry),
                        'bbox'     => $bbox,
                    ]
                );

                // Invalidate cache
                Cache::forget("map:districts:regency:{$district->regency_id}");
                $count++;
            }
        }

        $this->info("Import batas kecamatan selesai! Total {$count} batas kecamatan berhasil disimpan.");
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
