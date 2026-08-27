<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Farm;
use App\Models\SoilDetection;
use App\Models\User;
use App\Services\Weather\WeatherService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class SoilDetectionDetailAuthorizationTest extends TestCase
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

    private function createFarm(User $farmer, string $name = 'Sawah Karawang'): Farm
    {
        return Farm::query()->create([
            'farmer_user_id' => $farmer->id,
            'name' => $name,
            'area_ha' => 2.0,
            'latitude' => -6.3031,
            'longitude' => 107.3009,
            'irrigation_type' => 'technical',
        ]);
    }

    private function createSoilDetection(Farm $farm, string $sampleCode = 'SOIL-TEST-001'): SoilDetection
    {
        return SoilDetection::query()->create([
            'farm_id' => $farm->id,
            'sample_code' => $sampleCode,
            'ph_level' => 6.5,
            'nitrogen_ppm' => 120,
            'phosphorus_ppm' => 25,
            'potassium_ppm' => 150,
            'moisture_percentage' => 50.0,
            'organic_matter_percentage' => 2.5,
            'soil_temp_celsius' => 27.0,
            'soil_type' => 'loam',
            'soil_health_score' => 90,
            'soil_status' => 'optimal',
            'tested_at' => now(),
            'created_by' => $farm->farmer_user_id,
        ]);
    }

    public function test_unauthenticated_user_cannot_view_soil_detection_detail(): void
    {
        $farmer = $this->createActor(UserRole::Farmer->value);
        $farm = $this->createFarm($farmer);
        $soil = $this->createSoilDetection($farm, 'SOIL-UNAUTH-001');

        $response = $this->getJson("/api/v1/soil-detections/{$soil->sample_code}");

        $response->assertUnauthorized();
    }

    public function test_buyer_cannot_view_soil_detection_detail(): void
    {
        $farmer = $this->createActor(UserRole::Farmer->value);
        $farm = $this->createFarm($farmer);
        $soil = $this->createSoilDetection($farm, 'SOIL-BUYER-001');

        $buyer = $this->createActor(UserRole::Buyer->value);
        $token = $buyer->createToken('Buyer Token')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson("/api/v1/soil-detections/{$soil->sample_code}");

        $response->assertForbidden();
    }

    public function test_farmer_can_view_own_soil_detection_detail_by_code_and_id(): void
    {
        $farmer = $this->createActor(UserRole::Farmer->value);
        $farm = $this->createFarm($farmer);
        $soil = $this->createSoilDetection($farm, 'SOIL-OWN-001');

        $token = $farmer->createToken('Farmer Token')->plainTextToken;

        // By sample code
        $responseCode = $this->withToken($token)
            ->getJson("/api/v1/soil-detections/{$soil->sample_code}");

        $responseCode->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.sample_code', 'SOIL-OWN-001');

        // By ID
        $responseId = $this->withToken($token)
            ->getJson("/api/v1/soil-detections/{$soil->id}");

        $responseId->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $soil->id);
    }

    public function test_farmer_cannot_view_another_farmers_soil_detection_detail(): void
    {
        $farmerVictim = $this->createActor(UserRole::Farmer->value);
        $farmVictim = $this->createFarm($farmerVictim, 'Sawah Korban');
        $soilVictim = $this->createSoilDetection($farmVictim, 'SOIL-VICTIM-001');

        $farmerAttacker = $this->createActor(UserRole::Farmer->value);
        $tokenAttacker = $farmerAttacker->createToken('Attacker Token')->plainTextToken;

        $response = $this->withToken($tokenAttacker)
            ->getJson("/api/v1/soil-detections/{$soilVictim->sample_code}");

        $response->assertForbidden();
    }

    public function test_farmer_can_view_own_irrigation_schedule(): void
    {
        $farmer = $this->createActor(UserRole::Farmer->value);
        $farm = $this->createFarm($farmer);
        $soil = $this->createSoilDetection($farm, 'SOIL-IRR-OWN-001');

        $token = $farmer->createToken('Farmer Token')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson("/api/v1/soil-detections/{$soil->sample_code}/irrigation-schedule");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('sample_code', 'SOIL-IRR-OWN-001');
    }

    public function test_farmer_cannot_view_another_farmers_irrigation_schedule(): void
    {
        $farmerVictim = $this->createActor(UserRole::Farmer->value);
        $farmVictim = $this->createFarm($farmerVictim, 'Sawah Korban');
        $soilVictim = $this->createSoilDetection($farmVictim, 'SOIL-IRR-VICTIM-001');

        $farmerAttacker = $this->createActor(UserRole::Farmer->value);
        $tokenAttacker = $farmerAttacker->createToken('Attacker Token')->plainTextToken;

        $response = $this->withToken($tokenAttacker)
            ->getJson("/api/v1/soil-detections/{$soilVictim->sample_code}/irrigation-schedule");

        $response->assertForbidden();
    }

    public function test_extension_officer_can_view_soil_detection_detail(): void
    {
        $farmer = $this->createActor(UserRole::Farmer->value);
        $farm = $this->createFarm($farmer);
        $soil = $this->createSoilDetection($farm, 'SOIL-PPL-VIEW-001');

        $officer = $this->createActor(UserRole::ExtensionOfficer->value);
        $token = $officer->createToken('Officer Token')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson("/api/v1/soil-detections/{$soil->sample_code}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.sample_code', 'SOIL-PPL-VIEW-001');
    }

    public function test_admin_can_view_soil_detection_detail(): void
    {
        $farmer = $this->createActor(UserRole::Farmer->value);
        $farm = $this->createFarm($farmer);
        $soil = $this->createSoilDetection($farm, 'SOIL-ADMIN-VIEW-001');

        $admin = $this->createActor(UserRole::Admin->value);
        $token = $admin->createToken('Admin Token')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson("/api/v1/soil-detections/{$soil->sample_code}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.sample_code', 'SOIL-ADMIN-VIEW-001');
    }

    public function test_farmer_can_fetch_api_data_for_own_farm(): void
    {
        $farmer = $this->createActor(UserRole::Farmer->value);
        $farm = $this->createFarm($farmer);
        $token = $farmer->createToken('Farmer Token')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson("/api/v1/soil-detections/fetch-api-data?farm_id={$farm->id}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.farm_id', $farm->id);
    }

    public function test_farmer_cannot_fetch_api_data_for_another_farmers_farm_and_does_not_call_external_api(): void
    {
        $farmerVictim = $this->createActor(UserRole::Farmer->value);
        $farmVictim = $this->createFarm($farmerVictim, 'Sawah Korban');

        $farmerAttacker = $this->createActor(UserRole::Farmer->value);
        $tokenAttacker = $farmerAttacker->createToken('Attacker Token')->plainTextToken;

        $this->mock(WeatherService::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('getSoilData');
        });

        $response = $this->withToken($tokenAttacker)
            ->getJson("/api/v1/soil-detections/fetch-api-data?farm_id={$farmVictim->id}");

        $response->assertForbidden();
    }

    public function test_buyer_cannot_fetch_api_data_and_does_not_call_external_api(): void
    {
        $farmer = $this->createActor(UserRole::Farmer->value);
        $farm = $this->createFarm($farmer);

        $buyer = $this->createActor(UserRole::Buyer->value);
        $token = $buyer->createToken('Buyer Token')->plainTextToken;

        $this->mock(WeatherService::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('getSoilData');
        });

        $response = $this->withToken($token)
            ->getJson("/api/v1/soil-detections/fetch-api-data?farm_id={$farm->id}");

        $response->assertForbidden();
    }

    public function test_extension_officer_can_fetch_api_data_for_farmer_farm(): void
    {
        $farmer = $this->createActor(UserRole::Farmer->value);
        $farm = $this->createFarm($farmer);

        $officer = $this->createActor(UserRole::ExtensionOfficer->value);
        $token = $officer->createToken('Officer Token')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson("/api/v1/soil-detections/fetch-api-data?farm_id={$farm->id}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.farm_id', $farm->id);
    }

    public function test_admin_can_fetch_api_data_for_farmer_farm(): void
    {
        $farmer = $this->createActor(UserRole::Farmer->value);
        $farm = $this->createFarm($farmer);

        $admin = $this->createActor(UserRole::Admin->value);
        $token = $admin->createToken('Admin Token')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson("/api/v1/soil-detections/fetch-api-data?farm_id={$farm->id}");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.farm_id', $farm->id);
    }
}
