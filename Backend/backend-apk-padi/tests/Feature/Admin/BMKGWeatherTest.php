<?php

namespace Tests\Feature\Admin;

use App\Models\Farm;
use App\Models\User;
use App\Services\Weather\WeatherService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BMKGWeatherTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    public function test_can_fetch_bmkg_weather_forecast_via_service(): void
    {
        $service = app(WeatherService::class);
        $result = $service->getBMKGForecast(-6.3031, 107.3009, 7);

        $this->assertTrue($result['success']);
        $this->assertEquals('bmkg_official', $result['provider']);
        $this->assertCount(7, $result['data']['forecast']);
        $this->assertArrayHasKey('agri_recommendation', $result['data']['forecast'][0]);
    }

    public function test_can_fetch_bmkg_forecast_via_api_endpoint(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $admin->assignRole('admin');
        $farmer = User::factory()->create(['role' => 'farmer', 'status' => 'active']);
        $farmer->assignRole('farmer');

        $farm = Farm::create([
            'farmer_user_id' => $farmer->id,
            'name' => 'Sawah BMKG Karawang',
            'latitude' => -6.3031,
            'longitude' => 107.3009,
            'area_ha' => 4.0,
            'irrigation_type' => 'technical',
        ]);

        $response = $this->actingAs($admin, 'sanctum')->postJson('/api/v1/weather/bmkg-forecast', [
            'farm_id' => $farm->id,
            'days' => 5,
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'provider',
            'data' => [
                'source',
                'location',
                'forecast',
            ],
        ]);
    }

    public function test_can_update_weather_provider_setting_to_bmkg(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->patch('/admin/weather/settings', [
            'weather_provider' => 'bmkg',
            'weather_api_key' => 'bmkg_official_api_key_sample',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('status', 'Pengaturan cuaca berhasil diperbarui.');
        $this->assertEquals('bmkg', \Illuminate\Support\Facades\Cache::get('weather.provider'));
    }
}
