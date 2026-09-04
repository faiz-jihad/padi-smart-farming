<?php

namespace Tests\Feature;

use App\Models\Farm;
use App\Models\IrrigationSchedule;
use App\Models\SoilDetection;
use App\Models\User;
use App\Services\Irrigation\IrrigationComparisonService;
use App\Services\Irrigation\WrdcWaterResourceService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IrrigationComparisonTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $farmer;
    protected Farm $farm;

    protected function setUp(): void
    {
        parent::setUp();
        \Illuminate\Support\Facades\Cache::flush();

        $this->seed(RoleSeeder::class);

        $this->admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $this->admin->assignRole('admin');

        $this->farmer = User::factory()->create(['role' => 'farmer', 'status' => 'active']);
        $this->farmer->assignRole('farmer');

        $this->farm = Farm::create([
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

        \Illuminate\Support\Facades\Http::fake([
            'https://sigi.pu.go.id/geoapi/api/v1/daerah_irigasi_permukaan/data*' => \Illuminate\Support\Facades\Http::response([
                'type' => 'FeatureCollection',
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
                            'smbr_air' => 'Bendung Rentang',
                            'kd_inf' => 'DI-3212001',
                        ]
                    ]
                ]
            ], 200),
            'https://sigi.pu.go.id/geoapi/api/v1/bendung/data*' => \Illuminate\Support\Facades\Http::response([], 200),
            'https://sigi.pu.go.id/geoapi/api/v1/ketersediaan_air/data*' => \Illuminate\Support\Facades\Http::response([], 200),
            'https://sigi.pu.go.id/geoapi/api/v1/kebutuhan_air/data*' => \Illuminate\Support\Facades\Http::response([], 200),
            'https://sigi.pu.go.id/geoapi/api/v1/neraca_air/data*' => \Illuminate\Support\Facades\Http::response([], 200),
            '*' => \Illuminate\Support\Facades\Http::response([], 200),
        ]);
    }

    /**
     * 1. Test CASE A: SOURCE 1 (System) + SOURCE 2 (Field) + SOURCE 3 (PU/WRDC)
     */
    public function test_case_a_source_1_and_source_2_and_source_3_produces_aligned_schedule(): void
    {
        IrrigationSchedule::create([
            'farm_id' => $this->farm->id,
            'schedule_date' => now()->addDay()->toDateString(),
            'start_time' => '06:00',
            'end_time' => '08:30',
            'status' => 'scheduled',
            'source' => 'raksa_bumi',
            'officer_name' => 'Pak Raksa Subarkah',
            'irrigation_block' => 'Tersier Blok B-4',
            'water_source' => 'Saluran Sekunder Barat',
        ]);

        $comparisonService = app(IrrigationComparisonService::class);
        $result = $comparisonService->compareForFarm(
            $this->farm,
            null,
            30.0 // Kelembaban rendah butuh air
        );

        $this->assertTrue($result['comparison']['is_aligned']);
        $this->assertEquals('aligned', $result['comparison']['status']);
        $this->assertTrue($result['irrigation_schedule']['is_field_schedule']);
        $this->assertEquals('scheduled', $result['irrigation_schedule']['status']);
        $this->assertEquals('Pak Raksa Subarkah', $result['irrigation_schedule']['officer_name']);
        $this->assertTrue($result['official_context']['is_available']);
        $this->assertStringContainsString('Rentang', $result['official_context']['daerah_irigasi']);
    }

    /**
     * 2. Test CASE B: SOURCE 1 + SOURCE 2 ADA (SOURCE 3 TIDAK ADA)
     */
    public function test_case_b_source_1_and_source_2_without_source_3_produces_field_schedule(): void
    {
        IrrigationSchedule::create([
            'farm_id' => $this->farm->id,
            'schedule_date' => now()->addDay()->toDateString(),
            'start_time' => '07:00',
            'end_time' => '09:00',
            'status' => 'scheduled',
            'source' => 'manual',
            'officer_name' => 'Petani Mandiri',
        ]);

        $comparisonService = app(IrrigationComparisonService::class);
        $result = $comparisonService->compareForFarm(
            $this->farm,
            null,
            32.0,
            27.0,
            ['is_available' => false] // Simulasikan Source 3 tidak tersedia
        );

        $this->assertEquals('aligned', $result['comparison']['status']);
        $this->assertTrue($result['irrigation_schedule']['is_field_schedule']);
        $this->assertEquals('Petani Mandiri', $result['irrigation_schedule']['officer_name']);
        $this->assertEquals('Belum tersedia dari sumber resmi', $result['official_context']['daerah_irigasi']);
        $this->assertFalse($result['official_context']['is_available']);
    }

    /**
     * 3. Test CASE C: SOURCE 1 + SOURCE 3 ADA (SOURCE 2 TIDAK ADA)
     */
    public function test_case_c_source_1_and_source_3_without_source_2_produces_recommended_slot(): void
    {
        $comparisonService = app(IrrigationComparisonService::class);
        $result = $comparisonService->compareForFarm(
            $this->farm,
            null,
            28.0 // Butuh air
        );

        $this->assertEquals('needs_scheduling', $result['comparison']['status']);
        $this->assertFalse($result['irrigation_schedule']['is_field_schedule']);
        $this->assertEquals('recommended', $result['irrigation_schedule']['status']);
        $this->assertNotNull($result['irrigation_schedule']['date']);
        $this->assertNotNull($result['irrigation_schedule']['time_range']);
        $this->assertTrue($result['official_context']['is_available']);
    }

    /**
     * 4. Test CASE D: SOURCE 1 SAJA (SOURCE 2 & 3 TIDAK ADA)
     */
    public function test_case_d_source_1_only_produces_recommended_slot(): void
    {
        $comparisonService = app(IrrigationComparisonService::class);
        $result = $comparisonService->compareForFarm(
            $this->farm,
            null,
            34.0,
            28.0,
            ['is_available' => false]
        );

        $this->assertEquals('needs_scheduling', $result['comparison']['status']);
        $this->assertFalse($result['irrigation_schedule']['is_field_schedule']);
        $this->assertEquals('recommended', $result['irrigation_schedule']['status']);
        $this->assertEquals('Belum tersedia dari sumber resmi', $result['official_context']['daerah_irigasi']);
    }

    /**
     * 5. Test Tanah Membutuhkan Air + Ada Jadwal (ALIGNED)
     */
    public function test_soil_needs_water_and_schedule_exists_is_aligned(): void
    {
        IrrigationSchedule::create([
            'farm_id' => $this->farm->id,
            'schedule_date' => now()->addDay()->toDateString(),
            'start_time' => '06:00',
            'end_time' => '08:30',
            'status' => 'scheduled',
            'source' => 'raksa_bumi',
        ]);

        $comparisonService = app(IrrigationComparisonService::class);
        $result = $comparisonService->compareForFarm($this->farm, null, 25.0);

        $this->assertTrue($result['comparison']['is_aligned']);
        $this->assertEquals('aligned', $result['comparison']['status']);
    }

    /**
     * 6. Test Tanah Membutuhkan Air + Tidak Ada Jadwal (NEEDS SCHEDULING)
     */
    public function test_soil_needs_water_and_no_schedule_prompts_needs_scheduling(): void
    {
        $comparisonService = app(IrrigationComparisonService::class);
        $result = $comparisonService->compareForFarm($this->farm, null, 30.0);

        $this->assertFalse($result['comparison']['is_aligned']);
        $this->assertEquals('needs_scheduling', $result['comparison']['status']);
    }

    /**
     * 7. Test Tanah Optimal + Ada Jadwal (ADVISORY - Jadwal Tidak Dihapus)
     */
    public function test_soil_optimal_and_schedule_exists_advises_without_deleting_schedule(): void
    {
        $schedule = IrrigationSchedule::create([
            'farm_id' => $this->farm->id,
            'schedule_date' => now()->addDay()->toDateString(),
            'start_time' => '07:00',
            'end_time' => '09:00',
            'status' => 'scheduled',
            'source' => 'manual',
        ]);

        $comparisonService = app(IrrigationComparisonService::class);
        $result = $comparisonService->compareForFarm($this->farm, null, 58.0);

        $this->assertEquals('moisture_sufficient', $result['comparison']['status']);
        $this->assertTrue($result['field_schedule']['has_schedule']);
        $this->assertDatabaseHas('irrigation_schedules', ['id' => $schedule->id]);
    }

    /**
     * 8. Test Tanah Optimal + Tidak Ada Jadwal (OPTIMAL)
     */
    public function test_soil_optimal_and_no_schedule_shows_routine_monitoring(): void
    {
        $comparisonService = app(IrrigationComparisonService::class);
        $result = $comparisonService->compareForFarm($this->farm, null, 60.0);

        $this->assertEquals('optimal', $result['comparison']['status']);
        $this->assertTrue($result['comparison']['is_aligned']);
    }

    /**
     * 9. Test Tanah Terlalu Basah / Jenuh (DRAINAGE NEEDED)
     */
    public function test_soil_flooded_advises_drainage_first(): void
    {
        $comparisonService = app(IrrigationComparisonService::class);
        $result = $comparisonService->compareForFarm($this->farm, null, 85.0);

        $this->assertEquals('drainage_needed', $result['comparison']['status']);
        $this->assertStringContainsString('pengeringan', strtolower($result['comparison']['explanation']));
    }

    /**
     * 10. Test Resolusi Data Resmi GEOAPI untuk Indramayu (DI Rentang & BBWS Cimanuk Cisanggarung)
     */
    public function test_wrdc_context_resolves_indramayu_as_di_rentang_and_bbws_cimanuk(): void
    {
        $wrdcService = app(WrdcWaterResourceService::class);
        $context = $wrdcService->getOfficialContextForFarm($this->farm);

        $this->assertTrue($context['is_live_api']);
        $this->assertStringContainsString('Rentang', $context['daerah_irigasi']);
        $this->assertStringContainsString('Cimanuk', $context['bbws_bws']);
        $this->assertStringContainsString('Pusat', $context['authority']);
        $this->assertEquals('pu_geoapi', $context['integration_status']);
    }

    /**
     * 11. Test Admin Web Store Manual Schedule
     */
    public function test_admin_web_can_store_manual_irrigation_schedule(): void
    {
        $soil = SoilDetection::create([
            'farm_id' => $this->farm->id,
            'tested_by_user_id' => $this->admin->id,
            'sample_code' => 'SOIL-TEST-001',
            'ph_level' => 6.5,
            'nitrogen_ppm' => 100,
            'phosphorus_ppm' => 20,
            'potassium_ppm' => 120,
            'moisture_percentage' => 30.0,
            'organic_matter_percentage' => 2.0,
            'tested_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.irrigation-schedules.store'), [
                'farm_id' => $this->farm->id,
                'soil_detection_id' => $soil->sample_code,
                'schedule_date' => now()->addDays(2)->toDateString(),
                'start_time' => '06:00',
                'end_time' => '08:30',
                'source' => 'raksa_bumi',
                'officer_name' => 'Pak Subarkah',
                'irrigation_block' => 'Tersier 02',
                'water_source' => 'Saluran Sekunder Barat',
                'notes' => 'Jadwal gilir air resmi desa',
            ]);

        $response->assertRedirect(route('admin.soil.show', $soil));
        $this->assertStringContainsString($soil->sample_code, $response->headers->get('Location'));
        $response->assertSessionHas('status');

        $this->assertDatabaseHas('irrigation_schedules', [
            'farm_id' => $this->farm->id,
            'source' => 'raksa_bumi',
            'officer_name' => 'Pak Subarkah',
            'irrigation_block' => 'Tersier 02',
        ]);
    }

    /**
     * 12. Test Admin Web Update Manual Schedule
     */
    public function test_admin_web_can_update_manual_irrigation_schedule(): void
    {
        $soil = SoilDetection::create([
            'farm_id' => $this->farm->id,
            'tested_by_user_id' => $this->admin->id,
            'sample_code' => 'SOIL-TEST-002',
            'ph_level' => 6.5,
            'nitrogen_ppm' => 100,
            'phosphorus_ppm' => 20,
            'potassium_ppm' => 120,
            'moisture_percentage' => 30.0,
            'organic_matter_percentage' => 2.0,
            'tested_at' => now(),
        ]);

        $schedule = IrrigationSchedule::create([
            'farm_id' => $this->farm->id,
            'schedule_date' => now()->addDay()->toDateString(),
            'start_time' => '06:00',
            'end_time' => '08:00',
            'status' => 'scheduled',
            'source' => 'manual',
            'officer_name' => 'Petani A',
        ]);

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.irrigation-schedules.update', $schedule->id), [
                'soil_detection_id' => $soil->sample_code,
                'start_time' => '06:30',
                'end_time' => '09:00',
                'officer_name' => 'Petani B (Revisi)',
            ]);

        $response->assertRedirect(route('admin.soil.show', $soil));
        $this->assertStringContainsString($soil->sample_code, $response->headers->get('Location'));
        $this->assertDatabaseHas('irrigation_schedules', [
            'id' => $schedule->id,
            'start_time' => '06:30',
            'officer_name' => 'Petani B (Revisi)',
        ]);
    }

    /**
     * 13. Test Admin Web Delete Manual Schedule
     */
    public function test_admin_web_can_delete_manual_irrigation_schedule(): void
    {
        $soil = SoilDetection::create([
            'farm_id' => $this->farm->id,
            'tested_by_user_id' => $this->admin->id,
            'sample_code' => 'SOIL-TEST-003',
            'ph_level' => 6.5,
            'nitrogen_ppm' => 100,
            'phosphorus_ppm' => 20,
            'potassium_ppm' => 120,
            'moisture_percentage' => 30.0,
            'organic_matter_percentage' => 2.0,
            'tested_at' => now(),
        ]);

        $schedule = IrrigationSchedule::create([
            'farm_id' => $this->farm->id,
            'schedule_date' => now()->addDay()->toDateString(),
            'status' => 'scheduled',
            'source' => 'manual',
        ]);

        $response = $this->actingAs($this->admin)
            ->delete(route('admin.irrigation-schedules.destroy', $schedule->id), [
                'soil_detection_id' => $soil->sample_code,
            ]);

        $response->assertRedirect(route('admin.soil.show', $soil));
        $this->assertStringContainsString($soil->sample_code, $response->headers->get('Location'));
        $this->assertDatabaseMissing('irrigation_schedules', ['id' => $schedule->id]);
    }

    /**
     * 14. Test Backward Compatibility on Existing API Endpoints
     */
    public function test_api_endpoints_backward_compatibility_with_comparison(): void
    {
        $soil = SoilDetection::create([
            'farm_id' => $this->farm->id,
            'tested_by_user_id' => $this->farmer->id,
            'sample_code' => 'SOIL-CMP-002',
            'ph_level' => 6.2,
            'nitrogen_ppm' => 110,
            'phosphorus_ppm' => 22,
            'potassium_ppm' => 140,
            'moisture_percentage' => 38.0,
            'organic_matter_percentage' => 2.4,
            'soil_temp_celsius' => 27.5,
            'soil_health_score' => 78,
            'soil_status' => 'optimal',
            'tested_at' => now(),
        ]);

        // API 1: GET /farms/{farm}/irrigation-comparison
        $response1 = $this->actingAs($this->farmer, 'sanctum')
            ->getJson("/api/v1/farms/{$this->farm->id}/irrigation-comparison");

        $response1->assertStatus(200);
        $response1->assertJsonStructure([
            'success',
            'farm',
            'system_recommendation',
            'field_schedule',
            'official_context',
            'irrigation_schedule',
            'comparison',
        ]);

        // API 2: GET /soil-detections/{sample_code}/irrigation-schedule
        $response2 = $this->actingAs($this->farmer, 'sanctum')
            ->getJson("/api/v1/soil-detections/{$soil->sample_code}/irrigation-schedule");

        $response2->assertStatus(200);
        $response2->assertJsonStructure([
            'success',
            'message',
            'sample_code',
            'irrigation_schedule',
            'field_schedule',
            'official_context',
            'comparison',
        ]);

        // API 3: POST /farms/{farm}/irrigation-schedules
        $response3 = $this->actingAs($this->farmer, 'sanctum')
            ->postJson("/api/v1/farms/{$this->farm->id}/irrigation-schedules", [
                'schedule_date' => now()->addDays(3)->format('Y-m-d'),
                'start_time' => '06:00',
                'end_time' => '08:30',
                'source' => 'raksa_bumi',
                'officer_name' => 'Pak Raksa Asep',
                'irrigation_block' => 'Tersier Blok B-01',
                'water_source' => 'Saluran Induk Barat',
            ]);

        $response3->assertStatus(201);
        $response3->assertJsonPath('data.source', 'raksa_bumi');
    }
}
