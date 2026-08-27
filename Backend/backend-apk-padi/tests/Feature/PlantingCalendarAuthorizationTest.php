<?php

namespace Tests\Feature;

use App\Enums\PlantingCalendarStatus;
use App\Enums\PlantingSeason;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\District;
use App\Models\Farm;
use App\Models\PlantingCalendar;
use App\Models\Province;
use App\Models\Regency;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlantingCalendarAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    private function createActor(string $role): User
    {
        $user = User::factory()->create([
            'role' => $role,
            'status' => UserStatus::Active->value,
        ]);
        $user->assignRole($role);

        return $user;
    }

    private function createFarmWithCalendar(User $farmer): Farm
    {
        $province = Province::create(['code' => '32', 'name' => 'Jawa Barat']);
        $regency = Regency::create(['province_id' => $province->id, 'code' => '3212', 'name' => 'Indramayu']);
        $district = District::create(['regency_id' => $regency->id, 'code' => '321206', 'name' => 'Kandanghaur']);

        $farm = Farm::create([
            'farmer_user_id'  => $farmer->id,
            'name'            => 'Lahan Sawah ' . $farmer->name,
            'area_ha'         => 2.0,
            'latitude'        => -6.25,
            'longitude'       => 108.08,
            'irrigation_type' => 'irrigated',
            'province_id'     => $province->id,
            'regency_id'      => $regency->id,
            'district_id'     => $district->id,
        ]);

        PlantingCalendar::create([
            'regency_id'       => $regency->id,
            'district_id'      => $district->id,
            'season'           => PlantingSeason::Rainy,
            'year'             => (int) date('Y'),
            'planting_start'   => date('Y') . '-11-01',
            'planting_end'     => date('Y') . '-11-30',
            'planting_pattern' => 'Padi-Palawija',
            'rice_variety'     => 'Ciherang',
            'status'           => PlantingCalendarStatus::Active,
        ]);

        return $farm;
    }

    public function test_unauthenticated_user_cannot_view_farm_planting_calendar(): void
    {
        $farmer = $this->createActor(UserRole::Farmer->value);
        $farm = $this->createFarmWithCalendar($farmer);

        $response = $this->getJson("/api/v1/farms/{$farm->id}/planting-calendar?season=rainy");

        $response->assertUnauthorized();
    }

    public function test_buyer_cannot_view_farm_planting_calendar(): void
    {
        $farmer = $this->createActor(UserRole::Farmer->value);
        $farm = $this->createFarmWithCalendar($farmer);

        $buyer = $this->createActor(UserRole::Buyer->value);
        $token = $buyer->createToken('Buyer Token')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson("/api/v1/farms/{$farm->id}/planting-calendar?season=rainy");

        $response->assertForbidden();
    }

    public function test_farmer_cannot_view_planting_calendar_for_another_farmer_farm(): void
    {
        $farmerVictim = $this->createActor(UserRole::Farmer->value);
        $farmVictim = $this->createFarmWithCalendar($farmerVictim);

        $farmerAttacker = $this->createActor(UserRole::Farmer->value);
        $tokenAttacker = $farmerAttacker->createToken('Attacker Token')->plainTextToken;

        $response = $this->withToken($tokenAttacker)
            ->getJson("/api/v1/farms/{$farmVictim->id}/planting-calendar?season=rainy");

        $response->assertForbidden();
    }

    public function test_farmer_can_view_planting_calendar_for_own_farm(): void
    {
        $farmer = $this->createActor(UserRole::Farmer->value);
        $farm = $this->createFarmWithCalendar($farmer);
        $token = $farmer->createToken('Farmer Token')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson("/api/v1/farms/{$farm->id}/planting-calendar?season=rainy");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.rice_variety', 'Ciherang');
    }

    public function test_extension_officer_can_view_planting_calendar_for_farmer_farm(): void
    {
        $farmer = $this->createActor(UserRole::Farmer->value);
        $farm = $this->createFarmWithCalendar($farmer);

        $officer = $this->createActor(UserRole::ExtensionOfficer->value);
        $token = $officer->createToken('Officer Token')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson("/api/v1/farms/{$farm->id}/planting-calendar?season=rainy");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.rice_variety', 'Ciherang');
    }

    public function test_admin_can_view_planting_calendar_for_farmer_farm(): void
    {
        $farmer = $this->createActor(UserRole::Farmer->value);
        $farm = $this->createFarmWithCalendar($farmer);

        $admin = $this->createActor(UserRole::Admin->value);
        $token = $admin->createToken('Admin Token')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson("/api/v1/farms/{$farm->id}/planting-calendar?season=rainy");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.rice_variety', 'Ciherang');
    }
}
