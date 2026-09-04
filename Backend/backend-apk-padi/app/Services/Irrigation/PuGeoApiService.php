<?php

namespace App\Services\Irrigation;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Service Adapter untuk GEOAPI PUSDATIN Kementerian Pekerjaan Umum (PU).
 * 
 * Mengakses layer data spasial resmi dengan dukungan pagination penuh:
 * - Daerah Irigasi Permukaan (Polygon - 326 records)
 * - Ketersediaan Air (Polygon - 128 records)
 * - Kebutuhan Air (Polygon - 128 records)
 * - Neraca Air (Polygon - 128 records)
 * - Bendung (Point - 948 records)
 */
class PuGeoApiService
{
    protected string $baseUrl;
    protected ?string $email;
    protected ?string $token;
    protected int $timeout;
    protected int $cacheTtl;
    protected int $pageSize;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.pu_geoapi.base_url', 'https://sigi.pu.go.id/geoapi/api/v1'), '/');
        $this->email = config('services.pu_geoapi.email');
        $this->token = config('services.pu_geoapi.token');
        $this->timeout = (int) config('services.pu_geoapi.timeout', 30);
        $this->cacheTtl = (int) config('services.pu_geoapi.cache_ttl', 86400);
        $this->pageSize = (int) config('services.pu_geoapi.page_size', 20);
    }

    /**
     * Mengambil data Daerah Irigasi Permukaan berdasarkan titik koordinat (Polygon Match)
     *
     * @return array<string, mixed>|null
     */
    public function getIrrigationAreasByPoint(float $latitude, float $longitude): ?array
    {
        $cacheKey = "pu_geoapi.di." . $this->generateCoordKey($latitude, $longitude);

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($latitude, $longitude) {
            $feature = $this->queryPolygonLayer('daerah_irigasi_permukaan', $latitude, $longitude);

            if (! $feature) {
                return null;
            }

            $props = $feature['properties'] ?? $feature;

            return [
                'nm_balai' => $props['nm_balai'] ?? null,
                'nm_inf' => $props['nm_inf'] ?? null,
                'kewenangan' => $props['kewenangan'] ?? null,
                'luas_ha' => isset($props['luas_ha']) ? (float) $props['luas_ha'] : null,
                'jenis_di' => $props['jenis_di'] ?? null,
                'kondisi' => $props['kondisi'] ?? null,
                'nm_ws' => $props['nm_ws'] ?? null,
                'nm_das' => $props['nm_das'] ?? null,
                'provinsi' => $props['provinsi'] ?? null,
                'kab_kota' => $props['kab_kota'] ?? null,
                'kecamatan' => $props['kecamatan'] ?? null,
                'kel_desa' => $props['kel_desa'] ?? null,
                'smbr_air' => $props['smbr_air'] ?? null,
                'luas_fung' => isset($props['luas_fung']) ? (float) $props['luas_fung'] : null,
                'ip_rencana' => isset($props['ip_rencana']) ? (float) $props['ip_rencana'] : null,
                'pola_tnm' => $props['pola_tnm'] ?? null,
                'kd_inf' => $props['kd_inf'] ?? null,
                'status' => $props['status'] ?? null,
                'update_date' => $props['update_date'] ?? null,
                'properties' => $props,
            ];
        });
    }

    /**
     * Mengambil data Ketersediaan Air berdasarkan titik koordinat
     *
     * @return array<string, mixed>|null
     */
    public function getWaterAvailabilityByPoint(float $latitude, float $longitude): ?array
    {
        $cacheKey = "pu_geoapi.availability." . $this->generateCoordKey($latitude, $longitude);

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($latitude, $longitude) {
            $feature = $this->queryPolygonLayer('ketersediaan_air', $latitude, $longitude);

            if (! $feature) {
                return null;
            }

            $props = $feature['properties'] ?? $feature;

            return [
                'nama_ws' => $props['nama_ws'] ?? null,
                'kode_ws' => $props['kode_ws'] ?? null,
                'ta_rerata' => isset($props['ta_rerata']) ? (float) $props['ta_rerata'] : ($props['ta_rerata'] ?? null),
                'thn_dat' => $props['thn_dat'] ?? null,
                'properties' => $props,
            ];
        });
    }

    /**
     * Mengambil data Kebutuhan Air berdasarkan titik koordinat
     *
     * @return array<string, mixed>|null
     */
    public function getWaterDemandByPoint(float $latitude, float $longitude): ?array
    {
        $cacheKey = "pu_geoapi.demand." . $this->generateCoordKey($latitude, $longitude);

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($latitude, $longitude) {
            $feature = $this->queryPolygonLayer('kebutuhan_air', $latitude, $longitude);

            if (! $feature) {
                return null;
            }

            $props = $feature['properties'] ?? $feature;

            return [
                'nama_ws' => $props['nama_ws'] ?? null,
                'kode_ws' => $props['kode_ws'] ?? null,
                'irigasi' => isset($props['irigasi']) ? (float) $props['irigasi'] : ($props['irigasi'] ?? null),
                'perikanan' => isset($props['perikanan']) ? (float) $props['perikanan'] : ($props['perikanan'] ?? null),
                'peternakan' => isset($props['peternakan']) ? (float) $props['peternakan'] : ($props['peternakan'] ?? null),
                'rki' => isset($props['rki']) ? (float) $props['rki'] : ($props['rki'] ?? null),
                'aliran_pem' => isset($props['aliran_pem']) ? (float) $props['aliran_pem'] : ($props['aliran_pem'] ?? null),
                'thn_dat' => $props['thn_dat'] ?? null,
                'properties' => $props,
            ];
        });
    }

    /**
     * Mengambil data Neraca Air berdasarkan titik koordinat
     *
     * @return array<string, mixed>|null
     */
    public function getWaterBalanceByPoint(float $latitude, float $longitude): ?array
    {
        $cacheKey = "pu_geoapi.balance." . $this->generateCoordKey($latitude, $longitude);

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($latitude, $longitude) {
            $feature = $this->queryPolygonLayer('neraca_air', $latitude, $longitude);

            if (! $feature) {
                return null;
            }

            $props = $feature['properties'] ?? $feature;

            return [
                'nama_ws' => $props['nama_ws'] ?? null,
                'kode_ws' => $props['kode_ws'] ?? null,
                'irigasi' => isset($props['irigasi']) ? (float) $props['irigasi'] : ($props['irigasi'] ?? null),
                'perikanan' => isset($props['perikanan']) ? (float) $props['perikanan'] : ($props['perikanan'] ?? null),
                'peternakan' => isset($props['peternakan']) ? (float) $props['peternakan'] : ($props['peternakan'] ?? null),
                'rki' => isset($props['rki']) ? (float) $props['rki'] : ($props['rki'] ?? null),
                'industri' => isset($props['industri']) ? (float) $props['industri'] : ($props['industri'] ?? null),
                'rk' => isset($props['rk']) ? (float) $props['rk'] : ($props['rk'] ?? null),
                'thn_dat' => $props['thn_dat'] ?? null,
                'kebutuhan_' => $props['kebutuhan_'] ?? null,
                'kebutuha_1' => $props['kebutuha_1'] ?? null,
                'kebutuha_2' => $props['kebutuha_2'] ?? null,
                'properties' => $props,
            ];
        });
    }

    /**
     * Mengambil Bendung terdekat berdasarkan koordinat farm (Point Layer + Haversine)
     *
     * @return array<string, mixed>|null
     */
    public function getNearestDam(float $latitude, float $longitude): ?array
    {
        $cacheKey = "pu_geoapi.nearest_dam." . $this->generateCoordKey($latitude, $longitude);

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($latitude, $longitude) {
            $dams = $this->fetchLayerDataset('bendung');

            if (empty($dams)) {
                return null;
            }

            $nearest = null;
            $minDistance = PHP_FLOAT_MAX;

            foreach ($dams as $dam) {
                $props = $dam['properties'] ?? $dam;
                $damLat = null;
                $damLon = null;

                if (isset($props['latitude']) && isset($props['longitude'])) {
                    $damLat = (float) $props['latitude'];
                    $damLon = (float) $props['longitude'];
                } elseif (isset($dam['geometry']['coordinates']) && is_array($dam['geometry']['coordinates'])) {
                    // GeoJSON Point: [lon, lat]
                    $damLon = (float) $dam['geometry']['coordinates'][0];
                    $damLat = (float) $dam['geometry']['coordinates'][1];
                }

                if ($damLat === null || $damLon === null || ($damLat === 0.0 && $damLon === 0.0)) {
                    continue;
                }

                $distance = $this->calculateHaversineDistance($latitude, $longitude, $damLat, $damLon);

                if ($distance < $minDistance) {
                    $minDistance = $distance;
                    $nearest = [
                        'id' => $props['id'] ?? ($props['objectid'] ?? null),
                        'nama_infrastruktur' => $props['nama_infrastruktur'] ?? ($props['nm_inf'] ?? 'Bendung PU'),
                        'latitude' => $damLat,
                        'longitude' => $damLon,
                        'distance_km' => round($distance, 2),
                        'wilayah_sungai' => $props['wilayah_sungai'] ?? ($props['nm_ws'] ?? null),
                        'daerah_aliran_sungai' => $props['daerah_aliran_sungai'] ?? ($props['nm_das'] ?? null),
                        'provinsi' => $props['provinsi'] ?? null,
                        'kota_kabupaten' => $props['kota_kabupaten'] ?? ($props['kab_kota'] ?? null),
                        'kecamatan' => $props['kecamatan'] ?? null,
                        'kelurahan' => $props['kelurahan'] ?? ($props['kel_desa'] ?? null),
                        'kewenangan' => $props['kewenangan'] ?? null,
                        'kode' => $props['kode'] ?? null,
                        'kode_balai' => $props['kode_balai'] ?? null,
                        'nm_balai' => $props['nm_balai'] ?? null,
                        'kondisi_bangunan' => $props['kondisi_bangunan'] ?? null,
                        'pengelola' => $props['pengelola'] ?? null,
                        'status_infrastruktur' => $props['status_infrastruktur'] ?? null,
                        'status_pemeliharaan' => $props['status_pemeliharaan'] ?? null,
                        'tahun_pembangunan' => $props['tahun_pembangunan'] ?? null,
                        'manfaat_irigasi_layanan_baku' => isset($props['manfaat_irigasi_layanan_baku']) ? (float) $props['manfaat_irigasi_layanan_baku'] : null,
                        'manfaat_irigasi_layanan_potensial' => isset($props['manfaat_irigasi_layanan_potensial']) ? (float) $props['manfaat_irigasi_layanan_potensial'] : null,
                        'manfaat_irigasi_layanan_fungsional' => isset($props['manfaat_irigasi_layanan_fungsional']) ? (float) $props['manfaat_irigasi_layanan_fungsional'] : null,
                        'manfaat_sumber_penyediaan_air_baku_liter_detik' => isset($props['manfaat_sumber_penyediaan_air_baku_liter_detik']) ? (float) $props['manfaat_sumber_penyediaan_air_baku_liter_detik'] : null,
                        'manfaat_air_baku' => $props['manfaat_air_baku'] ?? null,
                        'teknis_debit_intake_musim_hujan_m3_detik' => isset($props['teknis_debit_intake_musim_hujan_m3_detik']) ? (float) $props['teknis_debit_intake_musim_hujan_m3_detik'] : null,
                        'teknis_debit_intake_musim_kemarau_m3_detik' => isset($props['teknis_debit_intake_musim_kemarau_m3_detik']) ? (float) $props['teknis_debit_intake_musim_kemarau_m3_detik'] : null,
                        'teknis_jenis' => $props['teknis_jenis'] ?? null,
                        'teknis_keterangan' => $props['teknis_keterangan'] ?? null,
                        'teknis_kondisi_infrastruktur' => $props['teknis_kondisi_infrastruktur'] ?? null,
                        'teknis_sungai' => $props['teknis_sungai'] ?? null,
                        'properties' => $props,
                    ];
                }
            }

            return $nearest;
        });
    }

 /**
 * Query layer polygon dengan local spatial matching.
 *
 * Dataset diproses per halaman agar tidak memuat seluruh GeoJSON
 * polygon ke memory sekaligus.
 *
 * @return array<string, mixed>|null
 */
protected function queryPolygonLayer(
    string $layerName,
    float $latitude,
    float $longitude
): ?array {
    $url = "{$this->baseUrl}/{$layerName}/data";

    $offset = 0;
    $limit = $this->pageSize;
    $maxPages = 100;
    $page = 0;

    while ($page < $maxPages) {
        $page++;

        $requestParams = [
            'offset' => $offset,
            'limit' => $limit,
            'filter' => '1=1',
            'fields' => '*',
            'format' => 'geojson',
            'geometry' => 'true',
        ];

        if (! empty($this->email)) {
            $requestParams['email'] = $this->email;
        }

        if (! empty($this->token)) {
            $requestParams['token'] = $this->token;
        }

        try {
            $response = Http::timeout($this->timeout)
                ->acceptJson()
                ->get($url, $requestParams);

            if (! $response->successful()) {
                Log::warning(
                    "PuGeoApiService: HTTP {$response->status()} " .
                    "saat query {$layerName} offset {$offset}"
                );

                return null;
            }

            $data = $response->json();

            if (! is_array($data)) {
                return null;
            }

            $features = $this->extractFeaturesFromResponse($data);
            $pageCount = count($features);

            foreach ($features as $feature) {
                if (
                    isset($feature['geometry']) &&
                    is_array($feature['geometry']) &&
                    $this->isPointInGeometry(
                        $latitude,
                        $longitude,
                        $feature['geometry']
                    )
                ) {
                    return $feature;
                }
            }

            if ($pageCount === 0) {
                break;
            }

            $totalRecords = $data['totalRecords']
                ?? ($data['total'] ?? null);

            $returnedCount = (int) (
                $data['returnedCount'] ?? $pageCount
            );

            if ($totalRecords !== null) {
                if (($offset + $returnedCount) >= (int) $totalRecords) {
                    break;
                }
            } elseif ($pageCount < $limit) {
                break;
            }

            $offset += $returnedCount > 0
                ? $returnedCount
                : $pageCount;

            unset($data, $features, $response);

        } catch (\Throwable $e) {
            Log::warning(
                "PuGeoApiService: Exception saat query {$layerName} " .
                "offset {$offset}: {$e->getMessage()}"
            );

            return null;
        }
    }

    return null;
}

    /**
 * Mengambil dataset layer dengan pagination.
 *
 * Setiap halaman disimpan secara atomic ke cache agar dataset besar
 * tidak menyebabkan FileStore melakukan unserialize terhadap satu
 * object raksasa.
 *
 * @return array<int, mixed>
 */
public function fetchLayerDataset(string $layerName): array
{
    $url = "{$this->baseUrl}/{$layerName}/data";

    $offset = 0;
    $limit = $this->pageSize;
    $maxPages = 100;
    $page = 0;
    $allFeatures = [];

    while ($page < $maxPages) {
        $page++;

        $pageCacheKey = "pu_geoapi.page.{$layerName}.{$offset}.{$limit}";

        $pageFeatures = Cache::store('file')->remember(
            $pageCacheKey,
            $this->cacheTtl,
            function () use ($url, $offset, $limit, $layerName) {
                $requestParams = [
                    'offset' => $offset,
                    'limit' => $limit,
                    'filter' => '1=1',
                    'fields' => '*',
                    'format' => 'geojson',
                    'geometry' => 'true',
                ];

                if (! empty($this->email)) {
                    $requestParams['email'] = $this->email;
                }

                if (! empty($this->token)) {
                    $requestParams['token'] = $this->token;
                }

                try {
                    $response = Http::timeout($this->timeout)
                        ->acceptJson()
                        ->get($url, $requestParams);

                    if (! $response->successful()) {
                        Log::warning(
                            "PuGeoApiService: HTTP {$response->status()} " .
                            "saat mengambil {$layerName} offset {$offset}"
                        );

                        return null;
                    }

                    $data = $response->json();

                    if (! is_array($data)) {
                        return null;
                    }

                    return [
                        'features' => $this->extractFeaturesFromResponse($data),
                        'totalRecords' => $data['totalRecords']
                            ?? ($data['total'] ?? null),
                        'returnedCount' => $data['returnedCount'] ?? null,
                    ];

                } catch (\Throwable $e) {
                    Log::warning(
                        "PuGeoApiService: Exception {$layerName} " .
                        "offset {$offset}: {$e->getMessage()}"
                    );

                    return null;
                }
            }
        );

        if (! is_array($pageFeatures)) {
            return [];
        }

        $features = $pageFeatures['features'] ?? [];

        if (! is_array($features) || empty($features)) {
            break;
        }

        $allFeatures = array_merge($allFeatures, $features);

        $pageCount = count($features);

        $totalRecords = $pageFeatures['totalRecords'] ?? null;

        $returnedCount = (int) (
            $pageFeatures['returnedCount'] ?? $pageCount
        );

        if ($totalRecords !== null) {
            if (count($allFeatures) >= (int) $totalRecords) {
                break;
            }
        } elseif ($pageCount < $limit) {
            break;
        }

        $offset += $returnedCount > 0
            ? $returnedCount
            : $pageCount;

        unset($pageFeatures, $features);
    }

    return $allFeatures;
}

    /**
     * Cache driver helper untuk menyimpan layer berukuran besar (Multi-MB)
     */
    public function getLayerCacheStore()
    {
        try {
            return Cache::store('file');
        } catch (\Throwable $e) {
            return Cache::store();
        }
    }

    /**
     * Ekstraksi array feature dari response GeoJSON atau array envelope
     *
     * @param array<string, mixed> $data
     * @return array<int, mixed>
     */
    protected function extractFeaturesFromResponse(array $data): array
    {
        if (isset($data['features']) && is_array($data['features'])) {
            return $data['features'];
        }

        if (isset($data['data']) && is_array($data['data'])) {
            return $data['data'];
        }

        if (array_is_list($data)) {
            return $data;
        }

        if (isset($data['type']) && $data['type'] === 'Feature') {
            return [$data];
        }

        return [];
    }

    /**
     * Memeriksa apakah titik berada di dalam GeoJSON Geometry (Polygon atau MultiPolygon).
     * Menggunakan Ray-Casting algorithm dengan Bounding Box pre-filtering.
     *
     * @param array<string, mixed> $geometry
     */
    public function isPointInGeometry(float $latitude, float $longitude, array $geometry): bool
    {
        $type = $geometry['type'] ?? '';
        $coords = $geometry['coordinates'] ?? [];

        if (empty($coords)) {
            return false;
        }

        if (strcasecmp($type, 'Polygon') === 0) {
            return $this->isPointInPolygonCoords($latitude, $longitude, $coords);
        }

        if (strcasecmp($type, 'MultiPolygon') === 0) {
            foreach ($coords as $polygonCoords) {
                if ($this->isPointInPolygonCoords($latitude, $longitude, $polygonCoords)) {
                    return true;
                }
            }
            return false;
        }

        return false;
    }

    /**
     * Algoritma Ray Casting untuk Polygon (Exterior Ring & Interior Holes)
     * Format koordinat GeoJSON: [longitude, latitude]
     *
     * @param array<int, mixed> $polygonCoords
     */
    protected function isPointInPolygonCoords(float $latitude, float $longitude, array $polygonCoords): bool
    {
        if (empty($polygonCoords) || ! is_array($polygonCoords[0])) {
            return false;
        }

        $exteriorRing = $polygonCoords[0];

        // 1. Pre-filtering Bounding Box
        if (! $this->isPointInBBox($latitude, $longitude, $exteriorRing)) {
            return false;
        }

        // 2. Ray-casting pada exterior ring
        $inside = $this->rayCasting($latitude, $longitude, $exteriorRing);

        if (! $inside) {
            return false;
        }

        // 3. Periksa interior holes (jika titik ada di dalam hole, maka di luar polygon)
        $ringCount = count($polygonCoords);
        for ($i = 1; $i < $ringCount; $i++) {
            if ($this->rayCasting($latitude, $longitude, $polygonCoords[$i])) {
                return false; // Berada di dalam lubang (hole)
            }
        }

        return true;
    }

    /**
     * Bounding Box pre-filter check
     *
     * @param array<int, mixed> $ring
     */
    protected function isPointInBBox(float $lat, float $lon, array $ring): bool
    {
        $minLat = PHP_FLOAT_MAX;
        $maxLat = -PHP_FLOAT_MAX;
        $minLon = PHP_FLOAT_MAX;
        $maxLon = -PHP_FLOAT_MAX;

        foreach ($ring as $pt) {
            if (! is_array($pt) || count($pt) < 2) {
                continue;
            }
            $pLon = (float) $pt[0];
            $pLat = (float) $pt[1];

            if ($pLat < $minLat) $minLat = $pLat;
            if ($pLat > $maxLat) $maxLat = $pLat;
            if ($pLon < $minLon) $minLon = $pLon;
            if ($pLon > $maxLon) $maxLon = $pLon;
        }

        return ($lat >= $minLat && $lat <= $maxLat && $lon >= $minLon && $lon <= $maxLon);
    }

    /**
     * Point-in-polygon Ray-Casting algorithm.
     * Koordinat titik pada ring: [lon, lat]
     *
     * @param array<int, mixed> $ring
     */
    protected function rayCasting(float $lat, float $lon, array $ring): bool
{
    $inside = false;
    $numPoints = count($ring);

    if ($numPoints < 3) {
        return false;
    }

    for ($i = 0, $j = $numPoints - 1; $i < $numPoints; $j = $i++) {
        if (
            !isset($ring[$i][0], $ring[$i][1]) ||
            !isset($ring[$j][0], $ring[$j][1])
        ) {
            continue;
        }

        // GeoJSON: [longitude, latitude]
        $xi = (float) $ring[$i][0];
        $yi = (float) $ring[$i][1];

        $xj = (float) $ring[$j][0];
        $yj = (float) $ring[$j][1];

        // Abaikan edge horizontal.
        if ($yi === $yj) {
            continue;
        }

        // Cek apakah garis horizontal dari titik melewati edge.
        $intersects = (($yi > $lat) !== ($yj > $lat));

        if (!$intersects) {
            continue;
        }

        $intersectionLon =
            (($xj - $xi) * ($lat - $yi) / ($yj - $yi)) + $xi;

        if ($lon < $intersectionLon) {
            $inside = !$inside;
        }
    }

    return $inside;
}

    /**
     * Menghitung jarak antara 2 titik koordinat (dalam kilometer) menggunakan Haversine Formula.
     */
    public function calculateHaversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadiusKm = 6371.0;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadiusKm * $c;
    }

    /**
     * Helper membuat cache key berdasarkan koordinat 4 desimal (~11m presisi)
     */
    protected function generateCoordKey(float $lat, float $lon): string
    {
        return sprintf('lat_%.4f_lon_%.4f', $lat, $lon);
    }
}
