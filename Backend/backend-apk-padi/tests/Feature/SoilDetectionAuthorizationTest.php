<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Farm;
use App\Models\SoilDetection;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SoilDetectionAuthorizationTest extends TestCase
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

    private function createFarm(User $farmer, string $name = 'Sawah Subur Karawang'): Farm
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

    private function soilPayload(int $farmId, array $overrides = []): array
    {
        return array_merge([
            'farm_id' => $farmId,
            'sample_code' => 'SOIL-' . uniqid(),
            'ph_level' => 6.2,
            'nitrogen_ppm' => 135,
            'phosphorus_ppm' => 28,
            'potassium_ppm' => 165,
            'moisture_percentage' => 52.5,
            'organic_matter_percentage' => 3.2,
            'soil_temp_celsius' => 27.0,
            'soil_type' => 'loam',
            'tested_at' => now()->format('Y-m-d H:i:s'),
            'notes' => 'Uji tanah berkala pra-tanam',
        ], $overrides);
    }

    public function test_unauthenticated_user_cannot_create_soil_detection(): void
    {
        $farmer = $this->createActor(UserRole::Farmer->value);
        $farm = $this->createFarm($farmer);

        $response = $this->postJson('/api/v1/soil-detections', $this->soilPayload($farm->id));

        $response->assertUnauthorized();
        $this->assertDatabaseEmpty('soil_detections');
    }

    public function test_buyer_cannot_create_soil_detection(): void
    {
        $farmer = $this->createActor(UserRole::Farmer->value);
        $farm = $this->createFarm($farmer);

        $buyer = $this->createActor(UserRole::Buyer->value);
        $token = $buyer->createToken('Buyer Token')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson('/api/v1/soil-detections', $this->soilPayload($farm->id));

        $response->assertForbidden();
        $this->assertDatabaseEmpty('soil_detections');
    }

    public function test_farmer_can_create_soil_detection_for_own_farm(): void
    {
        $farmer = $this->createActor(UserRole::Farmer->value);
        $farm = $this->createFarm($farmer);
        $token = $farmer->createToken('Farmer Token')->plainTextToken;

        $payload = $this->soilPayload($farm->id, [
            'sample_code' => 'SOIL-FARMER-001',
        ]);

        $response = $this->withToken($token)
            ->postJson('/api/v1/soil-detections', $payload);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.sample_code', 'SOIL-FARMER-001')
            ->assertJsonPath('data.farm_id', $farm->id);

        $this->assertDatabaseHas('soil_detections', [
            'sample_code' => 'SOIL-FARMER-001',
            'farm_id' => $farm->id,
            'created_by' => $farmer->id,
        ]);
    }

    public function test_farmer_cannot_create_soil_detection_for_another_farmer_farm(): void
    {
        $victimFarmer = $this->createActor(UserRole::Farmer->value);
        $victimFarm = $this->createFarm($victimFarmer, 'Sawah Korban');

        $attackerFarmer = $this->createActor(UserRole::Farmer->value);
        $token = $attackerFarmer->createToken('Attacker Token')->plainTextToken;

        $payload = $this->soilPayload($victimFarm->id, [
            'sample_code' => 'SOIL-ILLEGAL-001',
        ]);

        $response = $this->withToken($token)
            ->postJson('/api/v1/soil-detections', $payload);

        $response->assertForbidden();
        $this->assertDatabaseMissing('soil_detections', [
            'sample_code' => 'SOIL-ILLEGAL-001',
        ]);
        $this->assertDatabaseEmpty('soil_detections');
    }

    public function test_extension_officer_can_create_soil_detection_for_farmer_farm(): void
    {
        $farmer = $this->createActor(UserRole::Farmer->value);
        $farm = $this->createFarm($farmer);

        $officer = $this->createActor(UserRole::ExtensionOfficer->value);
        $token = $officer->createToken('Officer Token')->plainTextToken;

        $payload = $this->soilPayload($farm->id, [
            'sample_code' => 'SOIL-PPL-001',
        ]);

        $response = $this->withToken($token)
            ->postJson('/api/v1/soil-detections', $payload);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.sample_code', 'SOIL-PPL-001')
            ->assertJsonPath('data.farm_id', $farm->id);

        $this->assertDatabaseHas('soil_detections', [
            'sample_code' => 'SOIL-PPL-001',
            'farm_id' => $farm->id,
            'created_by' => $officer->id,
        ]);
    }

    public function test_admin_can_create_soil_detection_for_farmer_farm(): void
    {
        $farmer = $this->createActor(UserRole::Farmer->value);
        $farm = $this->createFarm($farmer);

        $admin = $this->createActor(UserRole::Admin->value);
        $token = $admin->createToken('Admin Token')->plainTextToken;

        $payload = $this->soilPayload($farm->id, [
            'sample_code' => 'SOIL-ADMIN-001',
        ]);

        $response = $this->withToken($token)
            ->postJson('/api/v1/soil-detections', $payload);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.sample_code', 'SOIL-ADMIN-001')
            ->assertJsonPath('data.farm_id', $farm->id);

        $this->assertDatabaseHas('soil_detections', [
            'sample_code' => 'SOIL-ADMIN-001',
            'farm_id' => $farm->id,
            'created_by' => $admin->id,
        ]);
    }
}
