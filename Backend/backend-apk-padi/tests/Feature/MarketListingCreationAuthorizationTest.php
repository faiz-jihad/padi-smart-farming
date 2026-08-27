<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\CropSeason;
use App\Models\Farm;
use App\Models\MarketListing;
use App\Models\User;
use App\Services\Admin\AdminNotificationService;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery\MockInterface;
use Tests\TestCase;

class MarketListingCreationAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        Storage::fake('public');
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

    /**
     * @return array{User, Farm, CropSeason}
     */
    private function createFarmerWithActiveSeason(): array
    {
        $farmer = $this->createActor(UserRole::Farmer->value);

        $farm = Farm::query()->create([
            'farmer_user_id' => $farmer->id,
            'name' => 'Sawah Makmur Sentosa',
            'area_ha' => 2.0,
            'latitude' => -6.3031,
            'longitude' => 107.3009,
            'irrigation_type' => 'technical',
        ]);

        $season = CropSeason::query()->create([
            'farm_id' => $farm->id,
            'status' => 'active',
            'planting_date' => now()->subMonths(3)->toDateString(),
            'estimated_harvest_date' => now()->addWeek()->toDateString(),
        ]);

        return [$farmer, $farm, $season];
    }

    private function listingPayload(int $farmId, int $cropSeasonId, array $overrides = []): array
    {
        return array_merge([
            'farm_id' => $farmId,
            'crop_season_id' => $cropSeasonId,
            'commodity' => 'Gabah Kering Panen Inpari 32',
            'quantity' => 1500,
            'unit' => 'kg',
            'price_per_unit' => 7000,
            'description' => 'Gabah kualitas super siap panen.',
            'sales_link' => 'https://wa.me/6281234567890',
            'image' => UploadedFile::fake()->image('gabah.jpg'),
        ], $overrides);
    }

    public function test_unauthenticated_user_cannot_create_market_listing(): void
    {
        [, $farm, $season] = $this->createFarmerWithActiveSeason();

        $response = $this->postJson('/api/v1/market-listings', $this->listingPayload($farm->id, $season->id));

        $response->assertUnauthorized();
        $this->assertDatabaseEmpty('market_listings');
    }

    public function test_farmer_can_create_market_listing_for_own_farm(): void
    {
        [$farmer, $farm, $season] = $this->createFarmerWithActiveSeason();
        $token = $farmer->createToken('Farmer Token')->plainTextToken;

        $response = $this->withToken($token)
            ->post('/api/v1/market-listings', $this->listingPayload($farm->id, $season->id), [
                'Accept' => 'application/json',
            ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'published')
            ->assertJsonPath('data.is_owner', true);

        $this->assertDatabaseHas('market_listings', [
            'farmer_id' => $farmer->id,
            'farm_id' => $farm->id,
            'commodity' => 'Gabah Kering Panen Inpari 32',
            'status' => 'published',
        ]);
    }

    public function test_farmer_cannot_create_market_listing_for_another_farmer_farm(): void
    {
        [, $victimFarm, $victimSeason] = $this->createFarmerWithActiveSeason();
        $attackerFarmer = $this->createActor(UserRole::Farmer->value);
        $token = $attackerFarmer->createToken('Attacker Token')->plainTextToken;

        $response = $this->withToken($token)
            ->post('/api/v1/market-listings', $this->listingPayload($victimFarm->id, $victimSeason->id, [
                'commodity' => 'Gabah Curian Petani Lain',
            ]), [
                'Accept' => 'application/json',
            ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('market_listings', [
            'commodity' => 'Gabah Curian Petani Lain',
        ]);
        $this->assertDatabaseEmpty('market_listings');
    }

    public function test_buyer_cannot_create_market_listing(): void
    {
        [, $farm, $season] = $this->createFarmerWithActiveSeason();
        $buyer = $this->createActor(UserRole::Buyer->value);
        $token = $buyer->createToken('Buyer Token')->plainTextToken;

        $response = $this->withToken($token)
            ->post('/api/v1/market-listings', $this->listingPayload($farm->id, $season->id), [
                'Accept' => 'application/json',
            ]);

        $response->assertForbidden();
        $this->assertDatabaseEmpty('market_listings');
    }

    public function test_extension_officer_cannot_create_market_listing(): void
    {
        [, $farm, $season] = $this->createFarmerWithActiveSeason();
        $officer = $this->createActor(UserRole::ExtensionOfficer->value);
        $token = $officer->createToken('Officer Token')->plainTextToken;

        $response = $this->withToken($token)
            ->post('/api/v1/market-listings', $this->listingPayload($farm->id, $season->id), [
                'Accept' => 'application/json',
            ]);

        $response->assertForbidden();
        $this->assertDatabaseEmpty('market_listings');
    }

    public function test_admin_can_create_market_listing_for_any_farm(): void
    {
        [$farmer, $farm, $season] = $this->createFarmerWithActiveSeason();
        $admin = $this->createActor(UserRole::Admin->value);
        $token = $admin->createToken('Admin Token')->plainTextToken;

        $response = $this->withToken($token)
            ->post('/api/v1/market-listings', $this->listingPayload($farm->id, $season->id, [
                'commodity' => 'Gabah Hasil Binaan Dinas',
            ]), [
                'Accept' => 'application/json',
            ]);

        $response->assertCreated()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('market_listings', [
            'farmer_id' => $farmer->id,
            'farm_id' => $farm->id,
            'commodity' => 'Gabah Hasil Binaan Dinas',
        ]);
    }

    public function test_failed_authorization_does_not_broadcast_buyer_notifications(): void
    {
        [, $victimFarm, $victimSeason] = $this->createFarmerWithActiveSeason();
        $attackerFarmer = $this->createActor(UserRole::Farmer->value);
        $token = $attackerFarmer->createToken('Attacker Token')->plainTextToken;

        $mockNotification = $this->mock(AdminNotificationService::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('notifyBuyers');
        });

        $response = $this->withToken($token)
            ->post('/api/v1/market-listings', $this->listingPayload($victimFarm->id, $victimSeason->id), [
                'Accept' => 'application/json',
            ]);

        $response->assertForbidden();
        $this->assertDatabaseEmpty('market_listings');
    }
}
