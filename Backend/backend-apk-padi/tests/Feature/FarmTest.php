<?php

namespace Tests\Feature;

use App\Models\District;
use App\Models\Farm;
use App\Models\Province;
use App\Models\Regency;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FarmTest extends TestCase
{
    use RefreshDatabase;

    public function test_farmer_can_create_farm_with_auto_resolved_location(): void
    {
        $farmer = User::factory()->create(['role' => 'farmer', 'status' => 'active']);
        Sanctum::actingAs($farmer);

        $province = Province::create(['code' => '32', 'name' => 'Jawa Barat']);
        $regency = Regency::create(['province_id' => $province->id, 'code' => '3212', 'name' => 'Indramayu']);
        District::create([
            'regency_id' => $regency->id,
            'code'       => '321206',
            'name'       => 'Kandanghaur',
            'latitude'   => -6.2500,
            'longitude'  => 108.0800,
        ]);

        $response = $this->postJson('/api/v1/farms', [
            'name'            => 'Sawah Blok Ceplik',
            'area_ha'         => 2.5,
            'latitude'        => -6.251,
            'longitude'       => 108.081,
            'irrigation_type' => 'irrigated',
            'soil_type'       => 'Alluvial',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Sawah Blok Ceplik')
            ->assertJsonPath('data.district.name', 'Kandanghaur');

        $this->assertDatabaseHas('farms', [
            'name'           => 'Sawah Blok Ceplik',
            'farmer_user_id' => $farmer->id,
        ]);
    }

    public function test_farmer_can_list_only_their_own_farms(): void
    {
        $farmer1 = User::factory()->create(['role' => 'farmer', 'status' => 'active']);
        $farmer2 = User::factory()->create(['role' => 'farmer', 'status' => 'active']);

        Farm::create([
            'farmer_user_id'  => $farmer1->id,
            'name'            => 'Sawah Petani 1',
            'area_ha'         => 1.0,
            'latitude'        => -6.25,
            'longitude'       => 108.08,
            'irrigation_type' => 'irrigated',
        ]);

        Farm::create([
            'farmer_user_id'  => $farmer2->id,
            'name'            => 'Sawah Petani 2',
            'area_ha'         => 1.5,
            'latitude'        => -6.30,
            'longitude'       => 108.10,
            'irrigation_type' => 'rainfed',
        ]);

        Sanctum::actingAs($farmer1);

        $response = $this->getJson('/api/v1/farms');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Sawah Petani 1');
    }
}
