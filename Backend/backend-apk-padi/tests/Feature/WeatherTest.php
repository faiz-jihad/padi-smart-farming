<?php

namespace Tests\Feature;

use App\Models\Farm;
use App\Models\User;
use App\Services\Weather\WeatherService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WeatherTest extends TestCase
{
    use RefreshDatabase;

    public function test_weather_service_returns_consistent_structure(): void
    {
        Http::fake([
            'api.openweathermap.org/*' => Http::response([
                'main' => [
                    'temp' => 29.5,
                    'feels_like' => 31.0,
                    'humidity' => 80,
                ],
                'weather' => [
                    ['main' => 'Rain', 'description' => 'hujan ringan', 'icon' => '10d'],
                ],
                'wind' => ['speed' => 3.5],
                'dt' => time(),
            ], 200),
        ]);

        $service = new WeatherService();
        $response = $service->getCurrentWeather(-6.2088, 106.8456, ['force_refresh' => true]);

        $this->assertTrue($response['success']);
        $this->assertEquals('openweathermap', $response['provider']);
        $this->assertEquals(29.5, $response['data']['main']['temp']);
    }

    public function test_weather_service_soil_data_fallback(): void
    {
        $service = new WeatherService();
        $soil = $service->getSoilData(-6.2088, 106.8456, ['force_refresh' => true]);

        $this->assertTrue($soil['success']);
        $this->assertArrayHasKey('soil_temp_celsius', $soil['data']);
        $this->assertArrayHasKey('moisture_percentage', $soil['data']);
    }

    public function test_admin_can_access_weather_settings_and_update(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($user)->get(route('admin.weather.settings'));
        $response->assertStatus(200);

        $postResponse = $this->actingAs($user)->patch(route('admin.weather.settings.update'), [
            'weather_provider' => 'agromonitoring',
            'weather_api_key' => '12345678901234567890',
        ]);

        $postResponse->assertRedirect();
        $postResponse->assertSessionHas('status');
    }

    public function test_admin_can_access_weather_map(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $farmer = User::factory()->create(['role' => 'farmer']);

        Farm::create([
            'farmer_user_id' => $farmer->id,
            'name' => 'Sawah Map Karawang',
            'area_ha' => 3.0,
            'latitude' => -6.30,
            'longitude' => 107.30,
            'irrigation_type' => 'irrigated',
        ]);

        $response = $this->actingAs($user)->get(route('admin.weather.map'));
        $response->assertStatus(200);
        $response->assertSee('Geolocation Tanah');
        $response->assertSee('Sawah Map Karawang');
    }
}
