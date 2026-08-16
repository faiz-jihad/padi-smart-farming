<?php

namespace Tests\Feature\Admin;

use App\Models\Farm;
use App\Models\SoilDetection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSoilTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_soil_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.soil.index'));

        $response->assertStatus(200);
        $response->assertViewHas('detections');
        $response->assertViewHas('stats');
    }

    public function test_admin_can_create_soil_detection_sample(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $farmer = User::factory()->create(['role' => 'farmer']);

        $farm = Farm::create([
            'farmer_user_id' => $farmer->id,
            'name' => 'Sawah Subur Karawang',
            'area_ha' => 2.5,
            'latitude' => -6.30,
            'longitude' => 107.30,
            'irrigation_type' => 'irrigated',
        ]);

        $payload = [
            'farm_id' => $farm->id,
            'sample_code' => 'SOIL-TEST-001',
            'ph_level' => 6.2,
            'nitrogen_ppm' => 140,
            'phosphorus_ppm' => 25,
            'potassium_ppm' => 160,
            'moisture_percentage' => 55.0,
            'organic_matter_percentage' => 3.0,
            'soil_type' => 'loam',
            'tested_at' => now()->format('Y-m-d H:i:s'),
        ];

        $response = $this->actingAs($admin)->post(route('admin.soil.store'), $payload);

        $response->assertRedirect();
        $this->assertDatabaseHas('soil_detections', [
            'sample_code' => 'SOIL-TEST-001',
            'farm_id' => $farm->id,
            'soil_status' => 'optimal',
        ]);
    }

    public function test_admin_can_view_soil_detail_report(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $farmer = User::factory()->create(['role' => 'farmer']);

        $farm = Farm::create([
            'farmer_user_id' => $farmer->id,
            'name' => 'Sawah Subur Karawang',
            'area_ha' => 2.5,
            'latitude' => -6.30,
            'longitude' => 107.30,
            'irrigation_type' => 'irrigated',
        ]);

        $soil = SoilDetection::create([
            'farm_id' => $farm->id,
            'sample_code' => 'SOIL-TEST-002',
            'ph_level' => 4.8, // Acidic
            'nitrogen_ppm' => 70, // Deficient
            'phosphorus_ppm' => 10,
            'potassium_ppm' => 90,
            'moisture_percentage' => 30.0,
            'organic_matter_percentage' => 1.2,
            'soil_type' => 'clay',
            'soil_health_score' => 40,
            'soil_status' => 'critical',
            'tested_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.soil.show', $soil));

        $response->assertStatus(200);
        $response->assertSee('SOIL-TEST-002');
        $response->assertSee('Kritis');
    }

    public function test_admin_can_export_soil_data(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.soil.export'), [
            'format' => 'json',
        ]);

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/json');
    }
}
