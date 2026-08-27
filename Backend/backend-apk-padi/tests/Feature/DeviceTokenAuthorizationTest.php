<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\DeviceToken;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeviceTokenAuthorizationTest extends TestCase
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

    public function test_unauthenticated_user_cannot_view_device_tokens(): void
    {
        $response = $this->getJson('/api/v1/device-tokens');

        $response->assertUnauthorized();
    }

    public function test_farmer_cannot_view_device_tokens(): void
    {
        $farmer = $this->createActor(UserRole::Farmer->value);
        $token = $farmer->createToken('Farmer Token')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/v1/device-tokens');

        $response->assertForbidden();
    }

    public function test_buyer_cannot_view_device_tokens(): void
    {
        $buyer = $this->createActor(UserRole::Buyer->value);
        $token = $buyer->createToken('Buyer Token')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/v1/device-tokens');

        $response->assertForbidden();
    }

    public function test_extension_officer_cannot_view_device_tokens(): void
    {
        $officer = $this->createActor(UserRole::ExtensionOfficer->value);
        $token = $officer->createToken('Officer Token')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/v1/device-tokens');

        $response->assertForbidden();
    }

    public function test_admin_can_view_device_tokens(): void
    {
        $farmer = $this->createActor(UserRole::Farmer->value);
        DeviceToken::create([
            'user_id' => $farmer->id,
            'token' => 'fcm_device_token_secret_sample_123',
            'platform' => 'android',
            'last_used_at' => now(),
        ]);

        $admin = $this->createActor(UserRole::Admin->value);
        $token = $admin->createToken('Admin Token')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/v1/device-tokens');

        $response->assertOk()
            ->assertJsonPath('data.0.token', 'fcm_device_token_secret_sample_123')
            ->assertJsonPath('data.0.platform', 'android')
            ->assertJsonPath('data.0.user_id', $farmer->id);
    }
}
