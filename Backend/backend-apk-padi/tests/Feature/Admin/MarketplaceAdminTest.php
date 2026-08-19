<?php

namespace Tests\Feature\Admin;

use App\Models\CropSeason;
use App\Models\Farm;
use App\Models\MarketListing;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    public function test_admin_can_create_update_and_delete_marketplace_listing_with_sales_link_and_image(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $admin->assignRole('admin');
        $farmer = User::factory()->create(['role' => 'farmer', 'status' => 'active']);

        $farm = Farm::create([
            'farmer_user_id' => $farmer->id,
            'name' => 'Sawah Panen Unggul',
            'latitude' => -6.3031,
            'longitude' => 107.3009,
            'area_ha' => 2.0,
            'irrigation_type' => 'technical',
        ]);

        $season = CropSeason::create([
            'farm_id' => $farm->id,
            'season_name' => 'MT2 2026',
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->addMonths(4),
            'status' => 'active',
        ]);

        // 1. Create Listing
        $response = $this->actingAs($admin)->post('/admin/marketplace', [
            'farmer_id' => $farmer->id,
            'farm_id' => $farm->id,
            'commodity' => 'Beras Organik Pandanwangi',
            'quantity' => 15.5,
            'unit' => 'ton',
            'price_per_unit' => 14000,
            'description' => 'Beras organik kualitas super',
            'sales_link' => 'https://tokopedia.com/sawah-pandanwangi',
            'image_url' => 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=500',
            'status' => 'published',
        ]);

        $response->assertRedirect(route('admin.marketplace.index'));

        $listing = MarketListing::where('commodity', 'Beras Organik Pandanwangi')->first();
        $this->assertNotNull($listing);
        $this->assertEquals('https://tokopedia.com/sawah-pandanwangi', $listing->sales_link);
        $this->assertEquals('https://images.unsplash.com/photo-1586201375761-83865001e31c?w=500', $listing->image_url);

        // 2. Edit Listing
        $updateResponse = $this->actingAs($admin)->patch("/admin/marketplace/listings/{$listing->id}", [
            'commodity' => 'Beras Organik Pandanwangi Super',
            'quantity' => 20.0,
            'unit' => 'ton',
            'price_per_unit' => 15000,
            'description' => 'Beras organik kemasan vakum',
            'sales_link' => 'https://wa.me/6281234567890',
            'image_url' => 'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=500',
            'status' => 'published',
        ]);

        $updateResponse->assertRedirect(route('admin.marketplace.index'));
        $listing->refresh();
        $this->assertEquals('Beras Organik Pandanwangi Super', $listing->commodity);
        $this->assertEquals('https://wa.me/6281234567890', $listing->sales_link);

        // 3. Delete Listing
        $deleteResponse = $this->actingAs($admin)->delete("/admin/marketplace/listings/{$listing->id}");
        $deleteResponse->assertRedirect(route('admin.marketplace.index'));
        $this->assertDatabaseMissing('market_listings', ['id' => $listing->id]);
    }
}
