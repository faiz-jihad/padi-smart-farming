<?php

namespace Tests\Feature;

use App\Models\District;
use App\Models\Province;
use App\Models\Regency;
use App\Models\Village;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_provinces(): void
    {
        Province::create(['code' => '32', 'name' => 'Jawa Barat']);
        Province::create(['code' => '33', 'name' => 'Jawa Tengah']);

        $response = $this->getJson('/api/v1/regions/provinces');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data');
    }

    public function test_can_list_regencies_by_province(): void
    {
        $province = Province::create(['code' => '32', 'name' => 'Jawa Barat']);
        Regency::create(['province_id' => $province->id, 'code' => '3212', 'name' => 'Indramayu']);
        Regency::create(['province_id' => $province->id, 'code' => '3215', 'name' => 'Karawang']);

        $response = $this->getJson("/api/v1/regions/regencies?province_id={$province->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.name', 'Indramayu');
    }

    public function test_can_list_districts_by_regency(): void
    {
        $province = Province::create(['code' => '32', 'name' => 'Jawa Barat']);
        $regency = Regency::create(['province_id' => $province->id, 'code' => '3212', 'name' => 'Indramayu']);
        District::create(['regency_id' => $regency->id, 'code' => '321206', 'name' => 'Kandanghaur']);

        $response = $this->getJson("/api/v1/regions/districts?regency_id={$regency->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Kandanghaur');
    }

    public function test_can_list_villages_by_district(): void
    {
        $province = Province::create(['code' => '32', 'name' => 'Jawa Barat']);
        $regency = Regency::create(['province_id' => $province->id, 'code' => '3212', 'name' => 'Indramayu']);
        $district = District::create(['regency_id' => $regency->id, 'code' => '321206', 'name' => 'Kandanghaur']);
        Village::create(['district_id' => $district->id, 'code' => '3212060001', 'name' => 'Wirakanan']);

        $response = $this->getJson("/api/v1/regions/villages?district_id={$district->id}");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Wirakanan');
    }

    public function test_can_search_regions(): void
    {
        $province = Province::create(['code' => '32', 'name' => 'Jawa Barat']);
        $regency = Regency::create(['province_id' => $province->id, 'code' => '3212', 'name' => 'Indramayu']);
        District::create(['regency_id' => $regency->id, 'code' => '321206', 'name' => 'Kandanghaur']);

        $response = $this->getJson('/api/v1/regions/search?q=Kandang');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.name', 'Kandanghaur');
    }
}
