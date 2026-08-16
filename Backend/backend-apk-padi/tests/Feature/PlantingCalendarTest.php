<?php

namespace Tests\Feature;

use App\Enums\PlantingCalendarStatus;
use App\Enums\PlantingSeason;
use App\Models\District;
use App\Models\Farm;
use App\Models\PlantingCalendar;
use App\Models\Province;
use App\Models\Regency;
use App\Models\User;
use App\Models\Village;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PlantingCalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_get_planting_calendar_with_district_fallback(): void
    {
        $user = User::factory()->create(['role' => 'farmer', 'status' => 'active']);
        Sanctum::actingAs($user);

        $province = Province::create(['code' => '32', 'name' => 'Jawa Barat']);
        $regency = Regency::create(['province_id' => $province->id, 'code' => '3212', 'name' => 'Indramayu']);
        $district = District::create([
            'regency_id' => $regency->id,
            'code'       => '321206',
            'name'       => 'Kandanghaur',
        ]);

        PlantingCalendar::create([
            'district_id'      => $district->id,
            'regency_id'       => $regency->id,
            'season'           => PlantingSeason::Dry,
            'year'             => (int) date('Y'),
            'planting_start'   => date('Y') . '-04-01',
            'planting_end'     => date('Y') . '-04-30',
            'planting_pattern' => 'Padi-Bera-Padi',
            'rice_variety'     => 'Inpari 32',
            'status'           => PlantingCalendarStatus::Active,
        ]);

        $response = $this->getJson("/api/v1/districts/{$district->id}/planting-calendar?season=dry");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.resolved_level', 'district')
            ->assertJsonPath('data.rice_variety', 'Inpari 32');
    }

    public function test_can_get_planting_calendar_for_farm_with_regency_fallback(): void
    {
        $user = User::factory()->create(['role' => 'farmer', 'status' => 'active']);
        Sanctum::actingAs($user);

        $province = Province::create(['code' => '32', 'name' => 'Jawa Barat']);
        $regency = Regency::create(['province_id' => $province->id, 'code' => '3212', 'name' => 'Indramayu']);
        $district = District::create(['regency_id' => $regency->id, 'code' => '321206', 'name' => 'Kandanghaur']);

        $farm = Farm::create([
            'farmer_user_id'  => $user->id,
            'name'            => 'Lahan Sawah 1',
            'area_ha'         => 2.0,
            'latitude'        => -6.25,
            'longitude'       => 108.08,
            'irrigation_type' => 'irrigated',
            'province_id'     => $province->id,
            'regency_id'      => $regency->id,
            'district_id'     => $district->id,
        ]);

        // Calendar defined at Regency level only
        PlantingCalendar::create([
            'regency_id'       => $regency->id,
            'season'           => PlantingSeason::Rainy,
            'year'             => (int) date('Y'),
            'planting_start'   => date('Y') . '-11-01',
            'planting_end'     => date('Y') . '-11-30',
            'planting_pattern' => 'Padi-Palawija',
            'rice_variety'     => 'Ciherang',
            'status'           => PlantingCalendarStatus::Active,
        ]);

        $response = $this->getJson("/api/v1/farms/{$farm->id}/planting-calendar?season=rainy");

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.resolved_level', 'regency')
            ->assertJsonPath('data.is_fallback', true)
            ->assertJsonPath('data.rice_variety', 'Ciherang');
    }
}
