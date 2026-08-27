<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\AlertSubscription;
use App\Models\Farm;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlertSubscriptionAuthorizationTest extends TestCase
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

    private function createSubscription(User $farmer, Farm $farm, float $radiusKm = 10.0): AlertSubscription
    {
        return AlertSubscription::query()->create([
            'farmer_id' => $farmer->id,
            'farm_id' => $farm->id,
            'is_active' => true,
            'radius_km' => $radiusKm,
        ]);
    }

    public function test_unauthenticated_cannot_view_alert_subscriptions(): void
    {
        $farmer = $this->createActor(UserRole::Farmer->value);
        $farm = $this->createFarm($farmer);
        $this->createSubscription($farmer, $farm);

        $response = $this->getJson('/api/v1/alert-subscriptions');

        $response->assertUnauthorized();
    }

    public function test_buyer_cannot_view_alert_subscriptions(): void
    {
        $farmer = $this->createActor(UserRole::Farmer->value);
        $farm = $this->createFarm($farmer);
        $this->createSubscription($farmer, $farm);

        $buyer = $this->createActor(UserRole::Buyer->value);
        $token = $buyer->createToken('Buyer Token')->plainTextToken;

        $response = $this->withToken($token)
            ->getJson('/api/v1/alert-subscriptions');

        $response->assertForbidden();
    }

    public function test_farmer_only_sees_own_alert_subscriptions(): void
    {
        $farmer1 = $this->createActor(UserRole::Farmer->value);
        $farm1 = $this->createFarm($farmer1, 'Sawah Farmer 1');
        $sub1 = $this->createSubscription($farmer1, $farm1, 15.0);

        $farmer2 = $this->createActor(UserRole::Farmer->value);
        $farm2 = $this->createFarm($farmer2, 'Sawah Farmer 2');
        $this->createSubscription($farmer2, $farm2, 25.0);

        $token1 = $farmer1->createToken('Farmer 1 Token')->plainTextToken;

        $response = $this->withToken($token1)
            ->getJson('/api/v1/alert-subscriptions');

        $response->assertOk()
            ->assertJsonCount(1, 'data.alert_subscriptions')
            ->assertJsonPath('data.alert_subscriptions.0.id', $sub1->id)
            ->assertJsonPath('data.alert_subscriptions.0.farmer_id', $farmer1->id);
    }

    public function test_farmer_without_subscriptions_gets_empty_list(): void
    {
        $farmerVictim = $this->createActor(UserRole::Farmer->value);
        $farmVictim = $this->createFarm($farmerVictim, 'Sawah Korban');
        $this->createSubscription($farmerVictim, $farmVictim, 10.0);

        $farmerOther = $this->createActor(UserRole::Farmer->value);
        $tokenOther = $farmerOther->createToken('Other Token')->plainTextToken;

        $response = $this->withToken($tokenOther)
            ->getJson('/api/v1/alert-subscriptions');

        $response->assertOk()
            ->assertJsonCount(0, 'data.alert_subscriptions');
    }

    public function test_extension_officer_can_view_all_alert_subscriptions(): void
    {
        $farmer1 = $this->createActor(UserRole::Farmer->value);
        $farm1 = $this->createFarm($farmer1, 'Sawah Farmer 1');
        $this->createSubscription($farmer1, $farm1, 15.0);

        $farmer2 = $this->createActor(UserRole::Farmer->value);
        $farm2 = $this->createFarm($farmer2, 'Sawah Farmer 2');
        $this->createSubscription($farmer2, $farm2, 25.0);

        $officer = $this->createActor(UserRole::ExtensionOfficer->value);
        $tokenOfficer = $officer->createToken('Officer Token')->plainTextToken;

        $response = $this->withToken($tokenOfficer)
            ->getJson('/api/v1/alert-subscriptions');

        $response->assertOk()
            ->assertJsonCount(2, 'data.alert_subscriptions');
    }

    public function test_admin_can_view_all_alert_subscriptions(): void
    {
        $farmer1 = $this->createActor(UserRole::Farmer->value);
        $farm1 = $this->createFarm($farmer1, 'Sawah Farmer 1');
        $this->createSubscription($farmer1, $farm1, 15.0);

        $farmer2 = $this->createActor(UserRole::Farmer->value);
        $farm2 = $this->createFarm($farmer2, 'Sawah Farmer 2');
        $this->createSubscription($farmer2, $farm2, 25.0);

        $admin = $this->createActor(UserRole::Admin->value);
        $tokenAdmin = $admin->createToken('Admin Token')->plainTextToken;

        $response = $this->withToken($tokenAdmin)
            ->getJson('/api/v1/alert-subscriptions');

        $response->assertOk()
            ->assertJsonCount(2, 'data.alert_subscriptions');
    }
}
