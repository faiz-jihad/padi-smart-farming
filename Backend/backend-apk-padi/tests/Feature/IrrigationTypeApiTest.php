<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Farm;
use App\Models\IrrigationType;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IrrigationTypeApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_authenticated_farmer_can_fetch_active_irrigation_types(): void
    {
        $farmer = User::factory()->create([
            'role' => UserRole::Farmer->value,
            'status' => UserStatus::Active->value,
        ]);
        $farmer->assignRole(UserRole::Farmer->value);

        IrrigationType::create([
            'name' => 'Irigasi Pompa Tenaga Surya',
            'code' => 'pompa_solar',
            'is_active' => true,
        ]);

        IrrigationType::create([
            'name' => 'Irigasi Nonaktif',
            'code' => 'inactive_irrigation',
            'is_active' => false,
        ]);

        $token = $farmer->createToken('Farmer Token')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/v1/irrigation-types');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Daftar tipe irigasi berhasil diambil',
        ]);

        $data = $response->json('data');
        $this->assertNotEmpty($data);

        $names = array_column($data, 'name');
        $this->assertContains('Irigasi Pompa Tenaga Surya', $names);
        $this->assertNotContains('Irigasi Nonaktif', $names);
    }

    public function test_farmer_can_create_farm_with_irrigation_type(): void
    {
        $farmer = User::factory()->create([
            'role' => UserRole::Farmer->value,
            'status' => UserStatus::Active->value,
        ]);
        $farmer->assignRole(UserRole::Farmer->value);

        $type = IrrigationType::create([
            'name' => 'Irigasi Pompa Diesel',
            'code' => 'pompa_diesel',
            'is_active' => true,
        ]);

        $token = $farmer->createToken('Farmer Token')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson('/api/v1/farms', [
                'name' => 'Sawah Blok Baru',
                'area_ha' => 1.5,
                'latitude' => -7.250000,
                'longitude' => 112.750000,
                'irrigation_type' => 'pompa_diesel',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('farms', [
            'name' => 'Sawah Blok Baru',
            'irrigation_type' => 'pompa_diesel',
            'irrigation_type_id' => $type->id,
        ]);
    }
}
