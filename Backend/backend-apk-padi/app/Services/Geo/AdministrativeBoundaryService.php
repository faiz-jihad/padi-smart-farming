<?php

namespace App\Services\Geo;

use App\Models\District;
use App\Models\Farm;
use App\Models\Province;
use App\Models\Regency;
use App\Models\User;
use App\Models\Village;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AdministrativeBoundaryService
{
    // ─────────────────────────────────────────────
    // GeoJSON: All Provinces in Indonesia
    // ─────────────────────────────────────────────

    /**
     * Get GeoJSON FeatureCollection of all provinces in Indonesia with their polygons.
     */
    public function getProvincesGeoJson(): array
    {
        $cacheKey = 'geo:provinces:all';

        return Cache::remember($cacheKey, now()->addHours(6), function () {
            $provinces = Province::whereNotNull('geometry')->get();

            $regencyCounts = Regency::select('province_id', DB::raw('COUNT(*) as count'))
                ->groupBy('province_id')
                ->get()
                ->keyBy('province_id');

            $features = [];

            foreach ($provinces as $province) {
                $rc = $regencyCounts->get($province->id);

                $geom = is_string($province->geometry) ? json_decode($province->geometry, true) : $province->geometry;
                $bbox = is_string($province->bbox) ? json_decode($province->bbox, true) : $province->bbox;

                if (!$geom) {
                    continue;
                }

                $geom = $this->normalizeGeometryStructure($geom);

                $features[] = [
                    'type'       => 'Feature',
                    'geometry'   => $geom,
                    'bbox'       => $bbox,
                    'properties' => [
                        'id'            => $province->id,
                        'name'          => $province->name,
                        'code'          => $province->code,
                        'regency_count' => $rc ? (int) $rc->count : 0,
                        'lat'           => (float) $province->latitude,
                        'lng'           => (float) $province->longitude,
                    ],
                ];
            }

            return [
                'type'     => 'FeatureCollection',
                'features' => $features,
            ];
        });
    }

    // ─────────────────────────────────────────────
    // GeoJSON: Regencies per Province
    // ─────────────────────────────────────────────

    /**
     * Get GeoJSON FeatureCollection of all regencies within a province with their polygons.
     */
    public function getRegenciesGeoJson(int $provinceId): array
    {
        $cacheKey = "geo:regencies:province:{$provinceId}";

        return Cache::remember($cacheKey, now()->addHours(6), function () use ($provinceId) {
            $regencies = Regency::where('province_id', $provinceId)
                ->whereNotNull('geometry')
                ->get();

            $districtCounts = District::whereIn('regency_id', $regencies->pluck('id'))
                ->select('regency_id', DB::raw('COUNT(*) as count'))
                ->groupBy('regency_id')
                ->get()
                ->keyBy('regency_id');

            $features = [];

            foreach ($regencies as $regency) {
                $dc = $districtCounts->get($regency->id);

                $geom = is_string($regency->geometry) ? json_decode($regency->geometry, true) : $regency->geometry;
                $bbox = is_string($regency->bbox) ? json_decode($regency->bbox, true) : $regency->bbox;

                if (!$geom) {
                    continue;
                }

                $geom = $this->normalizeGeometryStructure($geom);

                $features[] = [
                    'type'       => 'Feature',
                    'geometry'   => $geom,
                    'bbox'       => $bbox,
                    'properties' => [
                        'id'             => $regency->id,
                        'name'           => $regency->name,
                        'code'           => $regency->code,
                        'type'           => $regency->type?->value ?? 'regency',
                        'type_label'     => $regency->type?->label() ?? 'Kabupaten',
                        'district_count' => $dc ? (int) $dc->count : 0,
                        'lat'            => (float) $regency->latitude,
                        'lng'            => (float) $regency->longitude,
                    ],
                ];
            }

            return [
                'type'     => 'FeatureCollection',
                'features' => $features,
            ];
        });
    }

    /**
     * Get GeoJSON Feature for a single Regency.
     */
    public function getSingleRegencyGeoJson(int $regencyId): ?array
    {
        $regency = Regency::with('province')->find($regencyId);
        if (!$regency || !$regency->geometry) {
            return null;
        }

        $geom = is_string($regency->geometry) ? json_decode($regency->geometry, true) : $regency->geometry;
        $bbox = is_string($regency->bbox) ? json_decode($regency->bbox, true) : $regency->bbox;

        $geom = $this->normalizeGeometryStructure($geom);

        return [
            'type'       => 'Feature',
            'geometry'   => $geom,
            'bbox'       => $bbox,
            'properties' => [
                'id'            => $regency->id,
                'name'          => $regency->name,
                'code'          => $regency->code,
                'type'          => $regency->type?->value ?? 'regency',
                'type_label'    => $regency->type?->label() ?? 'Kabupaten',
                'province_id'   => $regency->province_id,
                'province_name' => $regency->province?->name ?? 'Provinsi',
                'lat'           => (float) $regency->latitude,
                'lng'           => (float) $regency->longitude,
            ],
        ];
    }

    /**
     * Get GeoJSON Feature for a single Province.
     */
    public function getSingleProvinceGeoJson(int $provinceId): ?array
    {
        $province = Province::find($provinceId);
        if (!$province || !$province->geometry) {
            return null;
        }

        $geom = is_string($province->geometry) ? json_decode($province->geometry, true) : $province->geometry;
        $bbox = is_string($province->bbox) ? json_decode($province->bbox, true) : $province->bbox;

        $geom = $this->normalizeGeometryStructure($geom);

        return [
            'type'       => 'Feature',
            'geometry'   => $geom,
            'bbox'       => $bbox,
            'properties' => [
                'id'            => $province->id,
                'name'          => $province->name,
                'code'          => $province->code,
                'lat'           => (float) $province->latitude,
                'lng'           => (float) $province->longitude,
            ],
        ];
    }

    // ─────────────────────────────────────────────
    // GeoJSON: Districts per Regency
    // ─────────────────────────────────────────────

    /**
     * Get GeoJSON FeatureCollection of all districts within a regency.
     * Each Feature carries pre-aggregated statistics in `properties`.
     */
    public function getDistrictsGeoJson(int $regencyId): array
    {
        $cacheKey = "geo:districts:regency:{$regencyId}";

        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($regencyId) {
            $districts = District::with(['boundary', 'villages'])
                ->where('regency_id', $regencyId)
                ->get();

            $farmCounts = Farm::whereIn('district_id', $districts->pluck('id'))
                ->select('district_id', DB::raw('COUNT(*) as farm_count'), DB::raw('SUM(area_ha) as total_area'))
                ->groupBy('district_id')
                ->get()
                ->keyBy('district_id');

            $features = [];

            foreach ($districts as $district) {
                $geometry = $this->resolveDistrictGeometry($district);
                if (!$geometry) {
                    continue;
                }

                $fc   = $farmCounts->get($district->id);
                $bbox = $district->boundary?->bbox ?? $this->calculateBbox($geometry);

                $features[] = [
                    'type'     => 'Feature',
                    'geometry' => $geometry,
                    'bbox'     => $bbox,
                    'properties' => [
                        'id'           => $district->id,
                        'name'         => $district->name,
                        'code'         => $district->code,
                        'village_count' => $district->villages->count(),
                        'farm_count'   => $fc ? (int) $fc->farm_count : 0,
                        'total_area_ha' => $fc ? round((float) $fc->total_area, 1) : 0,
                        'lat'          => $district->latitude,
                        'lng'          => $district->longitude,
                    ],
                ];
            }

            return [
                'type'     => 'FeatureCollection',
                'features' => $features,
            ];
        });
    }

    // ─────────────────────────────────────────────
    // GeoJSON: Villages per District
    // ─────────────────────────────────────────────

    /**
     * Get GeoJSON FeatureCollection of all villages within a district.
     */
    public function getVillagesGeoJson(int $districtId): array
    {
        $cacheKey = "geo:villages:district:{$districtId}";

        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($districtId) {
            $villages = Village::with('boundary')
                ->where('district_id', $districtId)
                ->get();

            $farmCounts = Farm::whereIn('village_id', $villages->pluck('id'))
                ->select('village_id', DB::raw('COUNT(*) as farm_count'), DB::raw('SUM(area_ha) as total_area'))
                ->groupBy('village_id')
                ->get()
                ->keyBy('village_id');

            $features = [];

            foreach ($villages as $village) {
                $geometry = $this->resolveVillageGeometry($village);
                if (!$geometry) {
                    continue;
                }

                $fc   = $farmCounts->get($village->id);
                $bbox = $village->boundary?->bbox ?? $this->calculateBbox($geometry);

                $features[] = [
                    'type'     => 'Feature',
                    'geometry' => $geometry,
                    'bbox'     => $bbox,
                    'properties' => [
                        'id'           => $village->id,
                        'name'         => $village->name,
                        'code'         => $village->code,
                        'type'         => $village->type?->value ?? 'village',
                        'farm_count'   => $fc ? (int) $fc->farm_count : 0,
                        'total_area_ha' => $fc ? round((float) $fc->total_area, 1) : 0,
                        'lat'          => $village->latitude,
                        'lng'          => $village->longitude,
                    ],
                ];
            }

            return [
                'type'     => 'FeatureCollection',
                'features' => $features,
            ];
        });
    }

    // ─────────────────────────────────────────────
    // GeoJSON: Farms per Village
    // ─────────────────────────────────────────────

    /**
     * Get GeoJSON FeatureCollection of all farms within a village.
     * Farms may have polygon boundaries or just a point marker.
     */
    public function getFarmsGeoJson(int $villageId): array
    {
        $farms = Farm::with(['farmer', 'cropSeasons', 'weatherSnapshots' => fn ($q) => $q->latest('observed_at')->limit(1)])
            ->where('village_id', $villageId)
            ->get();

        $features = [];

        foreach ($farms as $farm) {
            $geometry = $this->resolveFarmGeometry($farm);
            $latestSeason = $farm->cropSeasons->sortByDesc('created_at')->first();
            $latestWeather = $farm->weatherSnapshots->first();

            $features[] = [
                'type'     => 'Feature',
                'geometry' => $geometry,
                'properties' => [
                    'id'            => $farm->id,
                    'name'          => $farm->name,
                    'area_ha'       => $farm->area_ha,
                    'farmer_name'   => $farm->farmer?->name ?? '-',
                    'irrigation'    => $farm->irrigation_type ?? '-',
                    'soil_type'     => $farm->soil_type ?? '-',
                    'status'        => $farm->status ?? 'idle',
                    'crop_status'   => $latestSeason?->status ?? 'idle',
                    'lat'           => $farm->latitude,
                    'lng'           => $farm->longitude,
                    'weather'       => $latestWeather ? [
                        'temperature' => $latestWeather->temperature_celsius,
                        'humidity'    => $latestWeather->humidity_percentage,
                        'condition'   => $latestWeather->weather_condition,
                        'rain_rate'   => $latestWeather->rain_rate_mm,
                        'observed_at' => $latestWeather->observed_at?->format('d M Y H:i'),
                    ] : null,
                ],
            ];
        }

        return [
            'type'     => 'FeatureCollection',
            'features' => $features,
        ];
    }

    // ─────────────────────────────────────────────
    // Aggregated Summary: District
    // ─────────────────────────────────────────────

    public function getDistrictSummary(District $district): array
    {
        $district->load(['regency', 'villages', 'farms']);

        $farms   = Farm::where('district_id', $district->id)->get();
        $farmers = User::whereIn('id', $farms->pluck('farmer_user_id')->unique()->filter())->count();

        $totalArea    = round((float) $farms->sum('area_ha'), 2);
        $villageCount = $district->villages->count();

        $planting      = $farms->where('status', 'planting')->count();
        $harvestReady  = $farms->where('status', 'harvest_ready')->count();
        $idle          = $farms->where('status', 'idle')->count();
        $irrigated     = $farms->whereNotNull('irrigation_type')->count();
        $irrigationPct = $farms->count() > 0 ? round(($irrigated / $farms->count()) * 100) : 0;
        $waterStatus   = $farms->count() > 0 ? $this->resolveWaterStatus($irrigationPct) : 'Belum Ada Lahan';

        $lat = (float) ($district->latitude ?? -6.25);
        $lng = (float) ($district->longitude ?? 108.08);

        // Fetch weather data for district
        $weatherService = app(\App\Services\Weather\WeatherService::class);
        $weatherRes = $weatherService->getCurrentWeather($lat, $lng);
        $weather = $weatherRes['success'] ? $weatherService->parseWeatherData($weatherRes['data']) : null;
        $soilRes = $weatherService->getSoilData($lat, $lng);
        $soil = $soilRes['success'] ? $soilRes['data'] : null;
        $bmkgRes = $weatherService->getBMKGForecast($lat, $lng, 5);

        // Risk heuristics
        $risk = $this->calculateRisk($lat, $lng, $farms);

        return [
            'district' => [
                'id'        => $district->id,
                'name'      => $district->name,
                'code'      => $district->code,
                'regency'   => $district->regency?->name,
                'latitude'  => $lat,
                'longitude' => $lng,
            ],
            'statistics' => [
                'villages'          => $villageCount,
                'farmers'           => $farmers,
                'farms'             => $farms->count(),
                'farm_area_hectare' => $totalArea,
            ],
            'agriculture' => [
                'planting'      => $planting,
                'harvest_ready' => $harvestReady,
                'idle'          => $idle,
            ],
            'irrigation' => [
                'coverage_percentage' => $irrigationPct,
                'water_status'        => $waterStatus,
            ],
            'water' => [
                'status' => $waterStatus,
            ],
            'weather'          => $weather,
            'soil'             => $soil,
            'forecast'         => $bmkgRes['data']['forecast'] ?? [],
            'warning'          => $bmkgRes['data']['warning'] ?? null,
            'has_sub_villages' => $villageCount > 0,
            'risk'             => $risk,
        ];
    }

    // ─────────────────────────────────────────────
    // Aggregated Summary: Village
    // ─────────────────────────────────────────────

    public function getVillageSummary(Village $village): array
    {
        $village->load(['district', 'farms']);

        $farms   = Farm::where('village_id', $village->id)->get();
        $farmers = User::whereIn('id', $farms->pluck('farmer_user_id')->unique()->filter())->count();

        $totalArea = round((float) $farms->sum('area_ha'), 1);

        $planting     = $farms->where('status', 'planting')->count();
        $harvestReady = $farms->where('status', 'harvest_ready')->count();
        $idle         = $farms->where('status', 'idle')->count();

        $irrigated = $farms->whereNotNull('irrigation_type')->count();
        $irrigationPct = $farms->count() > 0
            ? round(($irrigated / $farms->count()) * 100)
            : 0;

        $lat = (float) ($village->latitude ?? -6.25);
        $lng = (float) ($village->longitude ?? 108.08);

        $weatherService = app(\App\Services\Weather\WeatherService::class);
        $weatherRes = $weatherService->getCurrentWeather($lat, $lng);
        $weather = $weatherRes['success'] ? $weatherService->parseWeatherData($weatherRes['data']) : null;
        $soilRes = $weatherService->getSoilData($lat, $lng);
        $soil = $soilRes['success'] ? $soilRes['data'] : null;
        $bmkgRes = $weatherService->getBMKGForecast($lat, $lng, 5);

        $risk = $this->calculateRisk($lat, $lng, $farms);

        return [
            'village' => [
                'id'        => $village->id,
                'name'      => $village->name,
                'code'      => $village->code,
                'type'      => $village->type?->value ?? 'village',
                'district'  => $village->district?->name,
                'latitude'  => $lat,
                'longitude' => $lng,
            ],
            'statistics' => [
                'farmers'           => $farmers,
                'farms'             => $farms->count(),
                'farm_area_hectare' => $totalArea,
            ],
            'agriculture' => [
                'planting'      => $planting,
                'harvest_ready' => $harvestReady,
                'idle'          => $idle,
            ],
            'irrigation' => [
                'coverage_percentage' => $irrigationPct,
            ],
            'water' => [
                'status' => $this->resolveWaterStatus($irrigationPct),
            ],
            'weather'  => $weather,
            'soil'     => $soil,
            'forecast' => $bmkgRes['data']['forecast'] ?? [],
            'warning'  => $bmkgRes['data']['warning'] ?? null,
            'risk'     => $risk,
        ];
    }

    // ─────────────────────────────────────────────
    // Geometry Resolvers
    // ─────────────────────────────────────────────

    private function resolveDistrictGeometry(District $district): ?array
    {
        if ($district->boundary && $district->boundary->geometry) {
            $geo = is_string($district->boundary->geometry)
                ? json_decode($district->boundary->geometry, true)
                : $district->boundary->geometry;
            if (!empty($geo)) {
                return $geo;
            }
        }

        // Fallback: generate a simple bounding box polygon from lat/lng centroid
        if ($district->latitude && $district->longitude) {
            return $this->centroidToPolygon($district->latitude, $district->longitude, 0.05);
        }

        return null;
    }

    private function resolveVillageGeometry(Village $village): ?array
    {
        if ($village->boundary && $village->boundary->geometry) {
            $geo = is_string($village->boundary->geometry)
                ? json_decode($village->boundary->geometry, true)
                : $village->boundary->geometry;
            if (!empty($geo)) {
                return $geo;
            }
        }

        if ($village->latitude && $village->longitude) {
            return $this->centroidToPolygon($village->latitude, $village->longitude, 0.015);
        }

        return null;
    }

    private function resolveFarmGeometry(Farm $farm): array
    {
        $boundary = $farm->boundary_coordinates;
        if ($boundary && is_array($boundary) && count($boundary) >= 3) {
            $rings = array_map(
                fn($p) => [(float) ($p['lng'] ?? $p[1] ?? 0), (float) ($p['lat'] ?? $p[0] ?? 0)],
                $boundary
            );
            // Close the ring
            if ($rings[0] !== end($rings)) {
                $rings[] = $rings[0];
            }
            return [
                'type'        => 'Polygon',
                'coordinates' => [$rings],
            ];
        }

        // Fallback: point geometry
        return [
            'type'        => 'Point',
            'coordinates' => [(float) $farm->longitude, (float) $farm->latitude],
        ];
    }

    // ─────────────────────────────────────────────
    // Utility
    // ─────────────────────────────────────────────

    /**
     * Build a simple rectangular polygon from a centroid point and delta.
     * Used as fallback when no real boundary polygon exists.
     */
    private function centroidToPolygon(float $lat, float $lng, float $delta): array
    {
        return [
            'type' => 'Polygon',
            'coordinates' => [[
                [$lng - $delta, $lat - $delta],
                [$lng + $delta, $lat - $delta],
                [$lng + $delta, $lat + $delta],
                [$lng - $delta, $lat + $delta],
                [$lng - $delta, $lat - $delta],
            ]],
        ];
    }

    private function calculateBbox(array $geometry): ?array
    {
        $allCoords = [];
        $this->flattenCoordinates($geometry['coordinates'] ?? [], $allCoords);

        if (count($allCoords) < 2) {
            return null;
        }

        $lngs = array_column($allCoords, 0);
        $lats = array_column($allCoords, 1);

        return [min($lngs), min($lats), max($lngs), max($lats)];
    }

    private function flattenCoordinates($coords, array &$out): void
    {
        if (empty($coords)) {
            return;
        }

        // Check if this is a leaf coordinate pair [lng, lat]
        if (is_numeric($coords[0] ?? null)) {
            $out[] = $coords;
            return;
        }

        foreach ($coords as $item) {
            $this->flattenCoordinates($item, $out);
        }
    }

    /**
     * Simple risk heuristic based on latitude, season, and farm data.
     */
    private function calculateRisk(float $lat, float $lng, Collection $farms): array
    {
        $month      = (int) now()->format('n');
        $isDrySeason = $month >= 4 && $month <= 9;

        return [
            'drought' => $isDrySeason ? 'MEDIUM' : 'LOW',
            'flood'   => !$isDrySeason ? 'MEDIUM' : 'LOW',
            'disease' => 'MEDIUM',
        ];
    }

    private function resolveWaterStatus(int $irrigationPct): string
    {
        if ($irrigationPct >= 75) {
            return 'NORMAL';
        }
        if ($irrigationPct >= 40) {
            return 'TERBATAS';
        }
        return 'KRITIS';
    }

    /**
     * Normalize GeoJSON geometry depth so Leaflet can parse it 100% reliably.
     */
    public function normalizeGeometryStructure(?array $geom): ?array
    {
        if (!$geom || !isset($geom['coordinates']) || !is_array($geom['coordinates'])) {
            return $geom;
        }

        $coords = $geom['coordinates'];
        $depth = $this->getCoordsDepth($coords);

        if ($depth === 2) {
            // [[lng, lat], [lng, lat]] -> [[[lng, lat], [lng, lat]]] (Polygon)
            $coords = [$coords];
            $type = 'Polygon';
        } elseif ($depth === 3) {
            $type = 'Polygon';
        } elseif ($depth >= 4) {
            $type = 'MultiPolygon';
        } else {
            $type = $geom['type'] ?? 'Polygon';
        }

        return [
            'type'        => $type,
            'coordinates' => $coords,
        ];
    }

    private function getCoordsDepth(array $arr): int
    {
        if (empty($arr)) return 0;
        if (isset($arr[0]) && is_numeric($arr[0])) return 1;
        if (isset($arr[0]) && is_array($arr[0])) return 1 + $this->getCoordsDepth($arr[0]);
        return 0;
    }
}
