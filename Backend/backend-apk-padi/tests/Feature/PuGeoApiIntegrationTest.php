<?php

namespace Tests\Feature;

use App\Models\Farm;
use App\Models\User;
use App\Services\Irrigation\PuGeoApiService;
use App\Services\Irrigation\WrdcWaterResourceService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PuGeoApiIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $farmer;
    protected Farm $indramayuFarm;
    protected Farm $sidrapFarm;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();

        $this->seed(RoleSeeder::class);

        $this->farmer = User::factory()->create(['role' => 'farmer', 'status' => 'active']);
        $this->farmer->assignRole('farmer');

        // Farm di Indramayu (-6.3266, 108.3200)
        $this->indramayuFarm = Farm::create([
            'farmer_user_id' => $this->farmer->id,
            'name' => 'Sawah Sindang Indramayu',
            'latitude' => -6.3266,
            'longitude' => 108.3200,
            'area_ha' => 1.75,
            'irrigation_type' => 'technical',
            'province' => 'Jawa Barat',
            'regency' => 'Kabupaten Indramayu',
            'district' => 'Sindang',
            'village' => 'Dersan',
        ]);

        // Farm di Sidrap, Sulawesi Selatan (-3.9268, 119.7972)
        $this->sidrapFarm = Farm::create([
            'farmer_user_id' => $this->farmer->id,
            'name' => 'Sawah Maritengngae Sidrap',
            'latitude' => -3.9268,
            'longitude' => 119.7972,
            'area_ha' => 2.50,
            'irrigation_type' => 'technical',
            'province' => 'Sulawesi Selatan',
            'regency' => 'Kabupaten Sidenreng Rappang',
            'district' => 'Maritengngae',
            'village' => 'Pangkajene',
        ]);

        Config::set('services.pu_geoapi.base_url', 'https://sigi.pu.go.id/geoapi/api/v1');
        Config::set('services.pu_geoapi.email', 'test_padi@pu.go.id');
        Config::set('services.pu_geoapi.token', 'test_token_secret_123');
        Config::set('services.pu_geoapi.page_size', 100);
    }

    /**
     * Test Pagination Daerah Irigasi Permukaan (326 Records):
     * - Dataset 326 dibagi ke dalam 4 halaman (100, 100, 100, 26).
     * - Polygon yang cocok berada di Halaman 2 (offset 100).
     * - Memastikan seluruh 326 record terkumpul dan polygon di halaman 2 berhasil dicocokkan.
     */
    public function test_daerah_irigasi_pagination_collects_all_326_records_and_finds_target_on_page_2(): void
    {
        $totalPages = 4;
        $totalRecords = 326;

        Http::fake([
            'https://sigi.pu.go.id/geoapi/api/v1/daerah_irigasi_permukaan/data*' => function ($request) use ($totalRecords) {
                $url = $request->url();
                parse_str(parse_url($url, PHP_URL_QUERY) ?? '', $queryParams);
                $offset = (int) ($queryParams['offset'] ?? 0);
                $limit = (int) ($queryParams['limit'] ?? 100);

                $remaining = $totalRecords - $offset;
                $count = min($limit, max(0, $remaining));

                $features = [];
                for ($i = 0; $i < $count; $i++) {
                    $recordIndex = $offset + $i + 1;

                    // Buat polygon target Indramayu berada pada Page 2 (misal recordIndex = 145)
                    if ($recordIndex === 145) {
                        $features[] = [
                            'type' => 'Feature',
                            'geometry' => [
                                'type' => 'Polygon',
                                'coordinates' => [
                                    [
                                        [108.0, -6.5],
                                        [108.5, -6.5],
                                        [108.5, -6.0],
                                        [108.0, -6.0],
                                        [108.0, -6.5],
                                    ]
                                ]
                            ],
                            'properties' => [
                                'nm_inf' => 'Daerah Irigasi Rentang (Page 2)',
                                'kd_inf' => 'DI-3212001',
                                'nm_balai' => 'Balai Besar Wilayah Sungai Cimanuk Cisanggarung',
                                'kewenangan' => 'Pemerintah Pusat',
                                'luas_ha' => 87840.0,
                                'jenis_di' => 'Irigasi Teknis Gravitasi',
                                'kondisi' => 'Baik',
                                'smbr_air' => 'Bendung Rentang',
                            ]
                        ];
                    } else {
                        // Polygon dummy di luar koordinat target
                        $features[] = [
                            'type' => 'Feature',
                            'geometry' => [
                                'type' => 'Polygon',
                                'coordinates' => [
                                    [
                                        [95.0 + ($recordIndex * 0.01), 4.0],
                                        [95.05 + ($recordIndex * 0.01), 4.0],
                                        [95.05 + ($recordIndex * 0.01), 4.05],
                                        [95.0 + ($recordIndex * 0.01), 4.05],
                                        [95.0 + ($recordIndex * 0.01), 4.0],
                                    ]
                                ]
                            ],
                            'properties' => [
                                'nm_inf' => "Dummy DI {$recordIndex}",
                                'kd_inf' => "DI-DUMMY-{$recordIndex}",
                            ]
                        ];
                    }
                }

                return Http::response([
                    'type' => 'FeatureCollection',
                    'totalRecords' => $totalRecords,
                    'returnedCount' => $count,
                    'offset' => $offset,
                    'features' => $features,
                ], 200);
            },
            'https://sigi.pu.go.id/geoapi/api/v1/*' => Http::response([], 200),
        ]);

        $geoApiService = app(PuGeoApiService::class);
        $dataset = $geoApiService->fetchLayerDataset('daerah_irigasi_permukaan');

        // Pastikan seluruh 326 record terkumpul
        $this->assertCount(326, $dataset);

        // Pastikan cache tersimpan lengkap dengan 326 record
        $cachedDataset = Cache::get('pu_geoapi.dataset.daerah_irigasi_permukaan');
        $this->assertCount(326, $cachedDataset);

        // Pastikan pencarian titik Indramayu berhasil menemukan DI yang berada di Page 2
        $di = $geoApiService->getIrrigationAreasByPoint(-6.3266, 108.3200);
        $this->assertNotNull($di);
        $this->assertEquals('Daerah Irigasi Rentang (Page 2)', $di['nm_inf']);
        $this->assertEquals('DI-3212001', $di['kd_inf']);
        $this->assertEquals('Balai Besar Wilayah Sungai Cimanuk Cisanggarung', $di['nm_balai']);
    }

    /**
     * Test Pagination Bendung (948 Records):
     * - Dataset 948 dibagi ke dalam 10 halaman (9x100 + 1x48).
     * - Bendung terdekat berada di Halaman 3 (offset 200).
     * - Memastikan seluruh 948 record terkumpul dan bendung terdekat di halaman 3 dipilih.
     */
    public function test_bendung_pagination_collects_all_948_records_and_finds_nearest_on_page_3(): void
    {
        $totalRecords = 948;

        Http::fake([
            'https://sigi.pu.go.id/geoapi/api/v1/bendung/data*' => function ($request) use ($totalRecords) {
                $url = $request->url();
                parse_str(parse_url($url, PHP_URL_QUERY) ?? '', $queryParams);
                $offset = (int) ($queryParams['offset'] ?? 0);
                $limit = (int) ($queryParams['limit'] ?? 100);

                $remaining = $totalRecords - $offset;
                $count = min($limit, max(0, $remaining));

                $features = [];
                for ($i = 0; $i < $count; $i++) {
                    $recordIndex = $offset + $i + 1;

                    // Bendung terdekat dengan Indramayu ditaruh di Page 3 (misal recordIndex = 230)
                    if ($recordIndex === 230) {
                        $features[] = [
                            'type' => 'Feature',
                            'geometry' => ['type' => 'Point', 'coordinates' => [108.3210, -6.3250]], // ~0.2 km dari farm
                            'properties' => [
                                'id' => 230,
                                'nama_infrastruktur' => 'Bendung Bangkir Utama (Page 3)',
                                'teknis_debit_intake_musim_hujan_m3_detik' => 60.0,
                                'teknis_debit_intake_musim_kemarau_m3_detik' => 20.0,
                                'nm_balai' => 'BBWS Cimanuk Cisanggarung',
                                'kewenangan' => 'Pemerintah Pusat',
                            ]
                        ];
                    } else {
                        // Bendung dummy di lokasi lain yang jauh
                        $features[] = [
                            'type' => 'Feature',
                            'geometry' => ['type' => 'Point', 'coordinates' => [110.0 + ($recordIndex * 0.005), -7.5]],
                            'properties' => [
                                'id' => $recordIndex,
                                'nama_infrastruktur' => "Bendung Dummy {$recordIndex}",
                                'teknis_debit_intake_musim_hujan_m3_detik' => 10.0,
                            ]
                        ];
                    }
                }

                return Http::response([
                    'type' => 'FeatureCollection',
                    'totalRecords' => $totalRecords,
                    'returnedCount' => $count,
                    'offset' => $offset,
                    'features' => $features,
                ], 200);
            },
            'https://sigi.pu.go.id/geoapi/api/v1/*' => Http::response([], 200),
        ]);

        $geoApiService = app(PuGeoApiService::class);
        $dataset = $geoApiService->fetchLayerDataset('bendung');

        // Pastikan seluruh 948 record terkumpul
        $this->assertCount(948, $dataset);

        // Pastikan cache tersimpan lengkap dengan 948 record
        $cachedDataset = Cache::get('pu_geoapi.dataset.bendung');
        $this->assertCount(948, $cachedDataset);

        // Pastikan bendung terdekat yang dipilih adalah Bendung di Page 3
        $nearest = $geoApiService->getNearestDam(-6.3266, 108.3200);
        $this->assertNotNull($nearest);
        $this->assertEquals('Bendung Bangkir Utama (Page 3)', $nearest['nama_infrastruktur']);
        $this->assertEquals(230, $nearest['id']);
        $this->assertLessThan(1.0, $nearest['distance_km']);
        $this->assertEquals(60.0, $nearest['teknis_debit_intake_musim_hujan_m3_detik']);
    }

    /**
     * Test Kegagalan Parsial pada Pagination:
     * - Page 1 berhasil (100 records).
     * - Page 2 gagal (HTTP 500).
     * - Memastikan dataset parsial TIDAK di-cache sebagai dataset lengkap.
     * - Memastikan fetch mengembalikan array kosong dan context jatuh ke fallback aman.
     */
    public function test_partial_pagination_failure_aborts_and_triggers_graceful_fallback(): void
    {
        Http::fake([
            'https://sigi.pu.go.id/geoapi/api/v1/daerah_irigasi_permukaan/data*' => function ($request) {
                $url = $request->url();
                parse_str(parse_url($url, PHP_URL_QUERY) ?? '', $queryParams);
                $offset = (int) ($queryParams['offset'] ?? 0);

                if ($offset === 0) {
                    // Page 1 sukses
                    return Http::response([
                        'type' => 'FeatureCollection',
                        'totalRecords' => 326,
                        'returnedCount' => 100,
                        'offset' => 0,
                        'features' => array_fill(0, 100, [
                            'type' => 'Feature',
                            'geometry' => ['type' => 'Polygon', 'coordinates' => [[[0, 0], [1, 0], [1, 1], [0, 1], [0, 0]]]],
                            'properties' => ['nm_inf' => 'Dummy DI']
                        ])
                    ], 200);
                }

                // Page 2 gagal (HTTP 500)
                return Http::response(['error' => 'Database timeout on page 2'], 500);
            },
            'https://sigi.pu.go.id/geoapi/api/v1/*' => Http::response([], 200),
        ]);

        $geoApiService = app(PuGeoApiService::class);
        $dataset = $geoApiService->fetchLayerDataset('daerah_irigasi_permukaan');

        // Harus gagal total, tidak mengembalikan partial dataset
        $this->assertEmpty($dataset);

        // Cache tidak boleh menyimpan partial dataset
        $this->assertNull(Cache::get('pu_geoapi.dataset.daerah_irigasi_permukaan'));

        // WrdcWaterResourceService harus menggunakan fallback aman
        $wrdcService = app(WrdcWaterResourceService::class);
        $context = $wrdcService->getOfficialContextForFarm($this->indramayuFarm);

        $this->assertFalse($context['is_live_api']);
        $this->assertEquals('authoritative_wrdc_fallback', $context['integration_status']);
        $this->assertEquals('Data resmi DI belum tersedia', $context['daerah_irigasi']);
        $this->assertNull($context['di_code']);
    }

    /**
     * Skenario Regression Indramayu:
     * Menjamin DI dan balai dipetakan apa adanya dari API.
     */
    public function test_skenario_indramayu_resolves_official_di_and_balai_from_geoapi(): void
    {
        Http::fake([
            'https://sigi.pu.go.id/geoapi/api/v1/daerah_irigasi_permukaan/data*' => Http::response([
                'type' => 'FeatureCollection',
                'totalRecords' => 1,
                'returnedCount' => 1,
                'offset' => 0,
                'features' => [
                    [
                        'type' => 'Feature',
                        'geometry' => [
                            'type' => 'Polygon',
                            'coordinates' => [
                                [
                                    [108.0, -6.5],
                                    [108.5, -6.5],
                                    [108.5, -6.0],
                                    [108.0, -6.0],
                                    [108.0, -6.5],
                                ]
                            ]
                        ],
                        'properties' => [
                            'nm_balai' => 'Balai Besar Wilayah Sungai Cimanuk Cisanggarung',
                            'nm_inf' => 'Daerah Irigasi Rentang',
                            'kewenangan' => 'Pemerintah Pusat',
                            'luas_ha' => 87840.0,
                            'jenis_di' => 'Irigasi Teknis Gravitasi',
                            'kondisi' => 'Baik',
                            'nm_ws' => 'Cimanuk Cisanggarung',
                            'nm_das' => 'Cimanuk',
                            'provinsi' => 'Jawa Barat',
                            'kab_kota' => 'Indramayu',
                            'kecamatan' => 'Sindang',
                            'kel_desa' => 'Dersan',
                            'smbr_air' => 'Bendung Rentang',
                            'luas_fung' => 85000.0,
                            'kd_inf' => 'DI-3212001',
                            'status' => 'Operasional',
                            'update_date' => '2025-01-15',
                        ]
                    ]
                ]
            ], 200),
            'https://sigi.pu.go.id/geoapi/api/v1/bendung/data*' => Http::response([
                'type' => 'FeatureCollection',
                'totalRecords' => 1,
                'returnedCount' => 1,
                'offset' => 0,
                'features' => [
                    [
                        'type' => 'Feature',
                        'geometry' => [
                            'type' => 'Point',
                            'coordinates' => [108.2800, -6.3400]
                        ],
                        'properties' => [
                            'id' => 101,
                            'nama_infrastruktur' => 'Bendung Rentang',
                            'teknis_debit_intake_musim_hujan_m3_detik' => 120.5,
                            'teknis_debit_intake_musim_kemarau_m3_detik' => 45.2,
                            'kewenangan' => 'Pemerintah Pusat',
                            'wilayah_sungai' => 'Cimanuk Cisanggarung',
                            'nm_balai' => 'Balai Besar Wilayah Sungai Cimanuk Cisanggarung',
                        ]
                    ]
                ]
            ], 200),
            'https://sigi.pu.go.id/geoapi/api/v1/*' => Http::response([], 200),
        ]);

        $wrdcService = app(WrdcWaterResourceService::class);
        $context = $wrdcService->getOfficialContextForFarm($this->indramayuFarm);

        $this->assertTrue($context['is_live_api']);
        $this->assertEquals('pu_geoapi', $context['integration_status']);
        $this->assertEquals('Kementerian Pekerjaan Umum / PUSDATIN GEOAPI', $context['provider']);
        $this->assertEquals('Daerah Irigasi Rentang', $context['daerah_irigasi']);
        $this->assertEquals('DI-3212001', $context['di_code']);
        $this->assertEquals('Balai Besar Wilayah Sungai Cimanuk Cisanggarung', $context['bbws_bws']);
        $this->assertEquals('Pemerintah Pusat', $context['authority']);
        $this->assertEquals('Bendung Rentang', $context['primary_source']);
        $this->assertEquals(87840.0, $context['service_area_ha']);
        $this->assertNotNull($context['nearest_dam']);
        $this->assertEquals('Bendung Rentang', $context['nearest_dam']['nama_infrastruktur']);
        $this->assertEquals(120.5, $context['nearest_dam']['teknis_debit_intake_musim_hujan_m3_detik']);
    }

    /**
     * Skenario Wilayah Lain (Sidrap Sulawesi Selatan):
     * Memastikan data murni berasal dari API tanpa rule kabupaten.
     */
    public function test_skenario_other_region_resolves_purely_from_geoapi(): void
    {
        Http::fake([
            'https://sigi.pu.go.id/geoapi/api/v1/daerah_irigasi_permukaan/data*' => Http::response([
                'type' => 'FeatureCollection',
                'totalRecords' => 1,
                'returnedCount' => 1,
                'offset' => 0,
                'features' => [
                    [
                        'type' => 'Feature',
                        'geometry' => [
                            'type' => 'Polygon',
                            'coordinates' => [
                                [
                                    [119.5, -4.2],
                                    [120.0, -4.2],
                                    [120.0, -3.7],
                                    [119.5, -3.7],
                                    [119.5, -4.2],
                                ]
                            ]
                        ],
                        'properties' => [
                            'nm_balai' => 'BWS Pompengan Jeneberang',
                            'nm_inf' => 'Daerah Irigasi Saddang Sub-Sistem Maritengngae',
                            'kewenangan' => 'Pusat (Kementerian PU)',
                            'luas_ha' => 45000.0,
                            'jenis_di' => 'Irigasi Teknis Gravitasi',
                            'kondisi' => 'Sangat Baik',
                            'nm_ws' => 'Saddang',
                            'smbr_air' => 'Sungai Saddang / Bendung Benteng',
                            'kd_inf' => 'DI-7314002',
                        ]
                    ]
                ]
            ], 200),
            'https://sigi.pu.go.id/geoapi/api/v1/*' => Http::response([], 200),
        ]);

        $wrdcService = app(WrdcWaterResourceService::class);
        $context = $wrdcService->getOfficialContextForFarm($this->sidrapFarm);

        $this->assertTrue($context['is_live_api']);
        $this->assertEquals('pu_geoapi', $context['integration_status']);
        $this->assertEquals('Daerah Irigasi Saddang Sub-Sistem Maritengngae', $context['daerah_irigasi']);
        $this->assertEquals('BWS Pompengan Jeneberang', $context['bbws_bws']);
        $this->assertEquals('DI-7314002', $context['di_code']);
    }

    /**
     * Skenario Farm di Luar Polygon Daerah Irigasi:
     * Memastikan DI tidak ditebak dan bernilai 'Data resmi DI belum tersedia', kd_inf null.
     */
    public function test_farm_outside_irrigation_polygon_falls_back_gracefully(): void
    {
        Http::fake([
            'https://sigi.pu.go.id/geoapi/api/v1/daerah_irigasi_permukaan/data*' => Http::response([
                'type' => 'FeatureCollection',
                'totalRecords' => 1,
                'returnedCount' => 1,
                'offset' => 0,
                'features' => [
                    [
                        'type' => 'Feature',
                        'geometry' => [
                            'type' => 'Polygon',
                            'coordinates' => [
                                [
                                    [115.0, -8.5],
                                    [115.5, -8.5],
                                    [115.5, -8.0],
                                    [115.0, -8.0],
                                    [115.0, -8.5],
                                ]
                            ]
                        ],
                        'properties' => [
                            'nm_inf' => 'DI Bali Timur',
                        ]
                    ]
                ]
            ], 200),
            'https://sigi.pu.go.id/geoapi/api/v1/*' => Http::response([], 200),
        ]);

        $wrdcService = app(WrdcWaterResourceService::class);
        $context = $wrdcService->getOfficialContextForFarm($this->indramayuFarm);

        $this->assertFalse($context['is_live_api']);
        $this->assertEquals('authoritative_wrdc_fallback', $context['integration_status']);
        $this->assertEquals('Data resmi DI belum tersedia', $context['daerah_irigasi']);
        $this->assertNull($context['di_code']);
        $this->assertNull($context['service_area_ha']);
    }

    /**
     * Skenario Parsing Ketersediaan Air (128 records)
     */
    public function test_parsing_water_availability_dataset(): void
    {
        $geoApiService = app(PuGeoApiService::class);

        Http::fake([
            'https://sigi.pu.go.id/geoapi/api/v1/ketersediaan_air/data*' => Http::response([
                'type' => 'FeatureCollection',
                'totalRecords' => 128,
                'returnedCount' => 1,
                'offset' => 0,
                'features' => [
                    [
                        'type' => 'Feature',
                        'geometry' => [
                            'type' => 'Polygon',
                            'coordinates' => [
                                [
                                    [108.0, -6.5],
                                    [108.5, -6.5],
                                    [108.5, -6.0],
                                    [108.0, -6.0],
                                    [108.0, -6.5],
                                ]
                            ]
                        ],
                        'properties' => [
                            'nama_ws' => 'WS Cimanuk Cisanggarung',
                            'kode_ws' => '02.09.A3',
                            'ta_rerata' => 7420.5,
                            'thn_dat' => '2024',
                            'objectid' => 45,
                        ]
                    ]
                ]
            ], 200),
            'https://sigi.pu.go.id/geoapi/api/v1/*' => Http::response([], 200),
        ]);

        $availability = $geoApiService->getWaterAvailabilityByPoint(-6.3266, 108.3200);

        $this->assertNotNull($availability);
        $this->assertEquals('WS Cimanuk Cisanggarung', $availability['nama_ws']);
        $this->assertEquals('02.09.A3', $availability['kode_ws']);
        $this->assertEquals(7420.5, $availability['ta_rerata']);
        $this->assertEquals('2024', $availability['thn_dat']);
    }

    /**
     * Skenario Parsing Kebutuhan Air (128 records)
     */
    public function test_parsing_water_demand_dataset(): void
    {
        $geoApiService = app(PuGeoApiService::class);

        Http::fake([
            'https://sigi.pu.go.id/geoapi/api/v1/kebutuhan_air/data*' => Http::response([
                'type' => 'FeatureCollection',
                'totalRecords' => 128,
                'returnedCount' => 1,
                'offset' => 0,
                'features' => [
                    [
                        'type' => 'Feature',
                        'geometry' => [
                            'type' => 'Polygon',
                            'coordinates' => [
                                [
                                    [108.0, -6.5],
                                    [108.5, -6.5],
                                    [108.5, -6.0],
                                    [108.0, -6.0],
                                    [108.0, -6.5],
                                ]
                            ]
                        ],
                        'properties' => [
                            'nama_ws' => 'WS Cimanuk Cisanggarung',
                            'kode_ws' => '02.09.A3',
                            'irigasi' => 5200.0,
                            'perikanan' => 120.0,
                            'peternakan' => 45.0,
                            'rki' => 310.0,
                            'aliran_pem' => 200.0,
                            'thn_dat' => '2024',
                            'objectid' => 88,
                        ]
                    ]
                ]
            ], 200),
            'https://sigi.pu.go.id/geoapi/api/v1/*' => Http::response([], 200),
        ]);

        $demand = $geoApiService->getWaterDemandByPoint(-6.3266, 108.3200);

        $this->assertNotNull($demand);
        $this->assertEquals('WS Cimanuk Cisanggarung', $demand['nama_ws']);
        $this->assertEquals(5200.0, $demand['irigasi']);
        $this->assertEquals(120.0, $demand['perikanan']);
        $this->assertEquals(45.0, $demand['peternakan']);
        $this->assertEquals(310.0, $demand['rki']);
        $this->assertEquals(200.0, $demand['aliran_pem']);
        $this->assertEquals('2024', $demand['thn_dat']);
    }

    /**
     * Skenario Parsing Neraca Air & Raw Fields (128 records)
     */
    public function test_parsing_water_balance_dataset_with_raw_fields(): void
    {
        $geoApiService = app(PuGeoApiService::class);

        Http::fake([
            'https://sigi.pu.go.id/geoapi/api/v1/neraca_air/data*' => Http::response([
                'type' => 'FeatureCollection',
                'totalRecords' => 128,
                'returnedCount' => 1,
                'offset' => 0,
                'features' => [
                    [
                        'type' => 'Feature',
                        'geometry' => [
                            'type' => 'Polygon',
                            'coordinates' => [
                                [
                                    [108.0, -6.5],
                                    [108.5, -6.5],
                                    [108.5, -6.0],
                                    [108.0, -6.0],
                                    [108.0, -6.5],
                                ]
                            ]
                        ],
                        'properties' => [
                            'nama_ws' => 'WS Cimanuk Cisanggarung',
                            'kode_ws' => '02.09.A3',
                            'irigasi' => 5200.0,
                            'perikanan' => 120.0,
                            'peternakan' => 45.0,
                            'rki' => 310.0,
                            'industri' => 95.0,
                            'rk' => 20.0,
                            'thn_dat' => '2024',
                            'kebutuhan_' => 'K1_RAW',
                            'kebutuha_1' => 'K2_RAW',
                            'kebutuha_2' => 'K3_RAW',
                        ]
                    ]
                ]
            ], 200),
            'https://sigi.pu.go.id/geoapi/api/v1/*' => Http::response([], 200),
        ]);

        $balance = $geoApiService->getWaterBalanceByPoint(-6.3266, 108.3200);

        $this->assertNotNull($balance);
        $this->assertEquals('WS Cimanuk Cisanggarung', $balance['nama_ws']);
        $this->assertEquals(5200.0, $balance['irigasi']);
        $this->assertEquals(95.0, $balance['industri']);
        $this->assertEquals(20.0, $balance['rk']);
        $this->assertEquals('K1_RAW', $balance['kebutuhan_']);
        $this->assertEquals('K2_RAW', $balance['kebutuha_1']);
        $this->assertEquals('K3_RAW', $balance['kebutuha_2']);
    }

    /**
     * Skenario API Failure Total: HTTP 500, 401, Malformed JSON
     */
    public function test_api_failures_fallback_gracefully(): void
    {
        // 1. HTTP 500
        Http::fake([
            'https://sigi.pu.go.id/geoapi/api/v1/*' => Http::response(['error' => 'Internal Server Error'], 500),
        ]);

        $wrdcService = app(WrdcWaterResourceService::class);
        $context500 = $wrdcService->getOfficialContextForFarm($this->indramayuFarm);

        $this->assertFalse($context500['is_live_api']);
        $this->assertEquals('authoritative_wrdc_fallback', $context500['integration_status']);

        Cache::flush();

        // 2. HTTP 401 Unauthorized
        Http::fake([
            'https://sigi.pu.go.id/geoapi/api/v1/*' => Http::response(['error' => 'Unauthorized'], 401),
        ]);

        $context401 = $wrdcService->getOfficialContextForFarm($this->indramayuFarm);
        $this->assertFalse($context401['is_live_api']);
        $this->assertEquals('authoritative_wrdc_fallback', $context401['integration_status']);

        Cache::flush();

        // 3. Malformed JSON
        Http::fake([
            'https://sigi.pu.go.id/geoapi/api/v1/*' => Http::response('invalid-json', 200),
        ]);

        $contextMalformed = $wrdcService->getOfficialContextForFarm($this->indramayuFarm);
        $this->assertFalse($contextMalformed['is_live_api']);
        $this->assertEquals('authoritative_wrdc_fallback', $contextMalformed['integration_status']);
    }

    /**
     * Skenario Credential: Token & Email dari config
     */
    public function test_credentials_sent_from_config(): void
    {
        Http::fake([
            'https://sigi.pu.go.id/geoapi/api/v1/*' => Http::response([
                'type' => 'FeatureCollection',
                'totalRecords' => 0,
                'returnedCount' => 0,
                'features' => []
            ], 200),
        ]);

        $geoApiService = app(PuGeoApiService::class);
        $geoApiService->fetchLayerDataset('daerah_irigasi_permukaan');

        Http::assertSent(function ($request) {
            return $request->hasHeader('Authorization', 'Bearer test_token_secret_123')
                && $request->hasHeader('X-User-Email', 'test_padi@pu.go.id');
        });
    }
}
