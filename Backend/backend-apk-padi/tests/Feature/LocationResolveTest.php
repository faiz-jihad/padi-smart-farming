<?php

namespace Tests\Feature;

use App\Models\District;
use App\Models\DistrictBoundary;
use App\Models\Province;
use App\Models\Regency;
use App\Models\Village;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationResolveTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_resolve_location_via_polygon(): void
    {
        $province = Province::create(['code' => '32', 'name' => 'Jawa Barat']);
        $regency = Regency::create(['province_id' => $province->id, 'code' => '3212', 'name' => 'Indramayu']);
        $district = District::create([
            'regency_id' => $regency->id,
            'code'       => '321206',
            'name'       => 'Kandanghaur',
            'latitude'   => -6.2500,
            'longitude'  => 108.0800,
        ]);

        // Create boundary covering [108.00, -6.30] to [108.20, -6.20]
        DistrictBoundary::create([
            'district_id' => $district->id,
            'geometry'    => json_encode([
                'type'        => 'Polygon',
                'coordinates' => [[
                    [108.00, -6.20],
                    [108.20, -6.20],
                    [108.20, -6.30],
                    [108.00, -6.30],
                    [108.00, -6.20],
                ]],
            ]),
            'bbox'        => [108.00, -6.30, 108.20, -6.20],
        ]);

        // Query point inside the polygon
        $response = $this->getJson('/api/v1/location/resolve?latitude=-6.25&longitude=108.10');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.district.name', 'Kandanghaur')
            ->assertJsonPath('data.regency.name', 'Indramayu')
            ->assertJsonPath('data.province.name', 'Jawa Barat');
    }

    public function test_can_resolve_location_via_centroid_fallback(): void
    {
        $province = Province::create(['code' => '32', 'name' => 'Jawa Barat']);
        $regency = Regency::create(['province_id' => $province->id, 'code' => '3212', 'name' => 'Indramayu']);
        District::create([
            'regency_id' => $regency->id,
            'code'       => '321206',
            'name'       => 'Kandanghaur',
            'latitude'   => -6.2500,
            'longitude'  => 108.0800,
        ]);

        // Query point near Kandanghaur without polygon boundary
        $response = $this->getJson('/api/v1/location/resolve?latitude=-6.251&longitude=108.081');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.district.name', 'Kandanghaur')
            ->assertJsonPath('data.resolution_method', 'nearest_centroid');
    }
}
