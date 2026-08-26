<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\CropSeason;
use App\Models\Farm;
use App\Models\MarketListing;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MarketplaceApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_farmer_can_publish_listing_with_uploaded_image_and_buyer_can_view_detail(): void
    {
        Storage::fake('public');

        [$farmer, $farm, $season] = $this->farmerWithActiveSeason();
        $buyer = $this->userWithRole(UserRole::Buyer->value);

        $response = $this
            ->actingAs($farmer, 'sanctum')
            ->post('/api/v1/market-listings', [
                'farm_id' => $farm->id,
                'crop_season_id' => $season->id,
                'commodity' => 'Gabah Kering Panen Inpari 32',
                'quantity' => 2500,
                'unit' => 'kg',
                'price_per_unit' => 6900,
                'description' => 'Kualitas bersih dan siap angkut.',
                'sales_link' => 'https://wa.me/6281234567890',
                'image' => UploadedFile::fake()->image('panen.jpg'),
            ], [
                'Accept' => 'application/json',
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'published')
            ->assertJsonPath('data.is_owner', true);

        $listing = MarketListing::query()->firstOrFail();

        $this->assertNotNull($listing->published_at);
        Storage::disk('public')->assertExists($listing->image_url);
        $this->assertStringStartsWith('marketplace/', $listing->image_url);

        $this
            ->actingAs($buyer, 'sanctum')
            ->getJson("/api/v1/market-listings/{$listing->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $listing->id)
            ->assertJsonPath('data.is_owner', false)
            ->assertJsonPath('data.status', 'published');
    }

    public function test_buyer_offer_acceptance_sells_listing_rejects_other_offers_and_creates_contract(): void
    {
        [$farmer, $farm, $season] = $this->farmerWithActiveSeason();
        $buyer = $this->userWithRole(UserRole::Buyer->value);
        $otherBuyer = $this->userWithRole(UserRole::Buyer->value);

        $listing = MarketListing::query()->create([
            'farmer_id' => $farmer->id,
            'farm_id' => $farm->id,
            'crop_season_id' => $season->id,
            'commodity' => 'Beras Premium',
            'quantity' => 1000,
            'unit' => 'kg',
            'price_per_unit' => 12500,
            'status' => 'published',
            'published_at' => now(),
        ]);

        $acceptedOffer = $this
            ->actingAs($buyer, 'sanctum')
            ->postJson('/api/v1/market-offers', [
                'listing_id' => $listing->id,
                'offered_price' => 12600,
                'quantity' => 800,
                'message' => 'Siap ambil minggu ini.',
            ])
            ->assertCreated()
            ->json('data.id');

        $rejectedOffer = $this
            ->actingAs($otherBuyer, 'sanctum')
            ->postJson('/api/v1/market-offers', [
                'listing_id' => $listing->id,
                'offered_price' => 12400,
                'quantity' => 500,
            ])
            ->assertCreated()
            ->json('data.id');

        $this
            ->actingAs($farmer, 'sanctum')
            ->putJson("/api/v1/market-offers/{$acceptedOffer}", [
                'status' => 'accepted',
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'accepted');

        $this->assertDatabaseHas('market_listings', [
            'id' => $listing->id,
            'status' => 'sold',
        ]);

        $this->assertDatabaseHas('market_offers', [
            'id' => $rejectedOffer,
            'status' => 'rejected',
        ]);

        $this->assertDatabaseHas('purchase_contracts', [
            'listing_id' => $listing->id,
            'partner_id' => $buyer->id,
            'offer_id' => $acceptedOffer,
            'status' => 'active',
        ]);
    }

    /**
     * @return array{User, Farm, CropSeason}
     */
    private function farmerWithActiveSeason(): array
    {
        $farmer = $this->userWithRole(UserRole::Farmer->value);

        $farm = Farm::query()->create([
            'farmer_user_id' => $farmer->id,
            'name' => 'Sawah Barat',
            'area_ha' => 1.5,
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

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create([
            'role' => $role,
            'status' => UserStatus::Active->value,
        ]);

        $user->assignRole($role);

        return $user;
    }
}
