<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\SoilType;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SoilTypeApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_authenticated_farmer_can_fetch_soil_types(): void
    {
        $farmer = User::factory()->create([
            'role' => UserRole::Farmer->value,
            'status' => UserStatus::Active->value,
        ]);
        $farmer->assignRole(UserRole::Farmer->value);

        SoilType::create([
            'name' => 'Regosol Gunung',
            'code' => 'regosol',
            'is_active' => true,
        ]);

        SoilType::create([
            'name' => 'Tanah Nonaktif',
            'code' => 'inactive_soil',
            'is_active' => false,
        ]);

        $token = $farmer->createToken('Farmer Token')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/v1/soil-types');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Daftar jenis tanah berhasil diambil',
        ]);

        $data = $response->json('data');
        $this->assertNotEmpty($data);

        // Ensure active soil type is included
        $names = array_column($data, 'name');
        $this->assertContains('Regosol Gunung', $names);

        // Ensure inactive soil type is excluded
        $this->assertNotContains('Tanah Nonaktif', $names);
    }
}
