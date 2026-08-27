<?php

namespace Tests\Feature\Admin;

use App\Models\Farm;
use App\Models\SoilDetection;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SoilIrrigationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_can_calculate_irrigation_schedule_and_exact_time(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $admin->assignRole('admin');
        $farmer = User::factory()->create(['role' => 'farmer', 'status' => 'active']);
        $farmer->assignRole('farmer');

        $farm = Farm::create([
            'farmer_user_id' => $farmer->id,
            'name' => 'Sawah Irigasi Test',
            'latitude' => -7.250000,
            'longitude' => 112.750000,
            'area_ha' => 2.50,
            'irrigation_type' => 'technical',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.soil.store'), [
            'farm_id' => $farm->id,
            'ph_level' => 6.5,
            'nitrogen_ppm' => 120,
            'phosphorus_ppm' => 25,
            'potassium_ppm' => 150,
            'moisture_percentage' => 40.0,
            'organic_matter_percentage' => 2.5,
            'soil_type' => 'loam',
            'tested_at' => now()->format('Y-m-d\TH:i'),
        ]);

        $soil = SoilDetection::latest('id')->first();
        $this->assertNotNull($soil);
        $response->assertRedirect(route('admin.soil.show', $soil));

        // Test API Endpoint for irrigation schedule
        $apiResponse = $this->actingAs($admin, 'sanctum')->getJson("/api/v1/soil-detections/{$soil->id}/irrigation-schedule");
        $apiResponse->assertStatus(200);
        $apiResponse->assertJsonStructure([
            'success',
            'message',
            'irrigation_schedule' => [
                'status',
                'status_label',
                'exact_date_time',
                'recommended_time_slot',
                'target_water_depth',
                'water_volume',
            ],
        ]);
    }
}
