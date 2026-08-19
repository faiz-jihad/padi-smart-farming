<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Events\AdminNotificationCreated;
use App\Models\AdminBroadcast;
use App\Models\AlertSubscription;
use App\Models\CommunityReport;
use App\Models\CropSeason;
use App\Models\DiseaseScan;
use App\Models\Farm;
use App\Models\MarketListing;
use App\Models\MarketOffer;
use App\Models\RiceVariety;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AdminOperationalFeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_admin_pages_render_real_database_records(): void
    {
        $admin = $this->adminUser();
        $farmer = User::factory()->create(['name' => 'Petani Real', 'role' => UserRole::Farmer->value]);
        $partner = User::factory()->create(['name' => 'Partner Real', 'role' => 'partner']);

        $farm = Farm::query()->create([
            'farmer_user_id' => $farmer->id,
            'name' => 'Sawah DB Real',
            'area_ha' => 1.25,
            'latitude' => -7.2500000,
            'longitude' => 112.7500000,
            'irrigation_type' => 'teknis',
        ]);

        $variety = RiceVariety::query()->create(['name' => 'IR Test', 'duration_days' => 110]);
        $season = CropSeason::query()->create([
            'farm_id' => $farm->id,
            'variety_id' => $variety->id,
            'status' => 'active',
        ]);

        DiseaseScan::query()->create([
            'farmer_id' => $farmer->id,
            'farm_id' => $farm->id,
            'image_url' => 'https://example.test/scan.jpg',
            'quality_status' => 'valid',
            'predicted_class' => 'blast',
            'confidence' => 0.9123,
            'scanned_at' => now(),
        ]);

        AlertSubscription::query()->create([
            'farmer_id' => $farmer->id,
            'farm_id' => $farm->id,
            'radius_km' => 5,
        ]);

        $listing = MarketListing::query()->create([
            'farmer_id' => $farmer->id,
            'farm_id' => $farm->id,
            'crop_season_id' => $season->id,
            'commodity' => 'Gabah Real DB',
            'quantity' => 100,
            'unit' => 'kg',
            'price_per_unit' => 6000,
            'status' => 'draft',
        ]);

        MarketOffer::query()->create([
            'listing_id' => $listing->id,
            'partner_id' => $partner->id,
            'offered_price' => 6200,
            'quantity' => 50,
            'status' => 'pending',
        ]);

        $this->actingAs($admin)->get(route('admin.users.index'))->assertOk()->assertSee('Petani Real');
        $this->actingAs($admin)->get(route('admin.agriculture.index'))->assertOk()->assertSee('Sawah DB Real');
        $this->actingAs($admin)->get(route('admin.disease.index'))->assertOk()->assertSee('blast');
        $this->actingAs($admin)->get(route('admin.early-warning.index'))->assertOk()->assertSee('Sawah DB Real');
        $this->actingAs($admin)->get(route('admin.marketplace.index'))->assertOk()->assertSee('Gabah Real DB');
    }

    public function test_admin_broadcast_creates_database_notification_and_realtime_event(): void
    {
        Event::fake([AdminNotificationCreated::class]);

        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->post(route('admin.broadcast.store'), [
                'title' => 'Peringatan Real',
                'message' => 'Curah hujan meningkat.',
                'type' => 'warning',
                'status' => 'published',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('admin_broadcasts', [
            'title' => 'Peringatan Real',
            'status' => 'published',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin->id,
            'title' => 'Broadcast dibuat',
            'type' => 'warning',
        ]);

        Event::assertDispatched(AdminNotificationCreated::class);
    }

    public function test_admin_actions_update_real_database_rows(): void
    {
        $admin = $this->adminUser();
        $farmer = User::factory()->create(['role' => UserRole::Farmer->value]);

        $farm = Farm::query()->create([
            'farmer_user_id' => $farmer->id,
            'name' => 'Sawah Action',
            'area_ha' => 2,
            'latitude' => -7.1,
            'longitude' => 112.1,
            'irrigation_type' => 'teknis',
        ]);

        $season = CropSeason::query()->create(['farm_id' => $farm->id, 'status' => 'active']);
        $listing = MarketListing::query()->create([
            'farmer_id' => $farmer->id,
            'farm_id' => $farm->id,
            'crop_season_id' => $season->id,
            'commodity' => 'Gabah Action',
            'quantity' => 10,
            'unit' => 'kg',
            'price_per_unit' => 5000,
            'status' => 'draft',
        ]);
        $scan = DiseaseScan::query()->create([
            'farmer_id' => $farmer->id,
            'farm_id' => $farm->id,
            'image_url' => 'https://example.test/action.jpg',
            'quality_status' => 'valid',
            'scanned_at' => now(),
        ]);
        $report = CommunityReport::query()->create([
            'scan_id' => $scan->id,
            'farmer_id' => $farmer->id,
            'latitude' => -7.1,
            'longitude' => 112.1,
            'radius_km' => 3,
            'consent_given' => true,
            'status' => 'pending',
            'reported_at' => now(),
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.marketplace.listings.update', $listing), ['status' => 'published'])
            ->assertRedirect();

        $this->actingAs($admin)
            ->patch(route('admin.disease.reports.update', $report), ['status' => 'verified'])
            ->assertRedirect();

        $this->assertDatabaseHas('market_listings', ['id' => $listing->id, 'status' => 'published']);
        $this->assertDatabaseHas('community_reports', ['id' => $report->id, 'status' => 'verified']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'admin_listing_updated']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'admin_report_updated']);
    }

    public function test_admin_can_run_core_operational_actions_from_blade_routes(): void
    {
        $admin = $this->adminUser();
        $farmer = User::factory()->create([
            'name' => 'Petani Update',
            'role' => UserRole::Farmer->value,
            'status' => UserStatus::Active->value,
            'verification_status' => 'pending',
        ]);
        $partner = User::factory()->create(['role' => 'partner']);

        $farm = Farm::query()->create([
            'farmer_user_id' => $farmer->id,
            'name' => 'Sawah Operasional',
            'area_ha' => 2.5,
            'latitude' => -7.2,
            'longitude' => 112.2,
            'irrigation_type' => 'teknis',
        ]);
        $season = CropSeason::query()->create(['farm_id' => $farm->id, 'status' => 'active']);
        $listing = MarketListing::query()->create([
            'farmer_id' => $farmer->id,
            'farm_id' => $farm->id,
            'crop_season_id' => $season->id,
            'commodity' => 'Gabah Offer',
            'quantity' => 25,
            'unit' => 'kg',
            'price_per_unit' => 5500,
            'status' => 'published',
        ]);
        $offer = MarketOffer::query()->create([
            'listing_id' => $listing->id,
            'partner_id' => $partner->id,
            'offered_price' => 5700,
            'quantity' => 15,
            'status' => 'pending',
        ]);
        $broadcast = AdminBroadcast::query()->create([
            'admin_id' => $admin->id,
            'title' => 'Draft Admin',
            'message' => 'Masih draft.',
            'type' => 'info',
            'status' => 'draft',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.users.update', $farmer), [
                'name' => 'Petani Update Final',
                'email' => $farmer->email,
                'phone' => $farmer->phone,
                'role' => 'partner',
                'status' => 'active',
                'verification_status' => 'verified',
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->patch(route('admin.marketplace.offers.update', $offer), ['status' => 'accepted'])
            ->assertRedirect();

        $this->actingAs($admin)
            ->post(route('admin.early-warning.store'), [
                'title' => 'Peringatan Hama',
                'body' => 'Pantau serangan hama di area aktif.',
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->patch(route('admin.broadcast.update', $broadcast), [
                'title' => 'Broadcast Dipublish',
                'message' => 'Pesan operasional.',
                'type' => 'announcement',
                'status' => 'published',
            ])
            ->assertRedirect();

        $this->actingAs($admin)
            ->delete(route('admin.broadcast.destroy', $broadcast))
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $farmer->id,
            'name' => 'Petani Update Final',
            'role' => 'partner',
            'status' => 'active',
            'verification_status' => 'verified',
        ]);
        $this->assertTrue($farmer->refresh()->hasRole(UserRole::Buyer->value));
        $this->assertDatabaseHas('market_offers', ['id' => $offer->id, 'status' => 'accepted']);
        $this->assertDatabaseHas('admin_broadcasts', [
            'title' => 'Peringatan Hama',
            'status' => 'published',
            'type' => 'warning',
        ]);
        $this->assertDatabaseHas('notifications', [
            'user_id' => $admin->id,
            'title' => 'Peringatan Hama',
            'type' => 'early_warning',
        ]);
        $this->assertDatabaseMissing('admin_broadcasts', ['id' => $broadcast->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'admin_user_updated']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'admin_offer_updated']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'admin_warning_created']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'admin_broadcast_updated']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'admin_broadcast_deleted']);

        $this->actingAs($admin)
            ->get(route('admin.audit.index'))
            ->assertOk()
            ->assertSee('admin_user_updated')
            ->assertSee('admin_broadcast_deleted');
    }

    public function test_admin_can_create_update_and_delete_users_from_blade_routes(): void
    {
        $admin = $this->adminUser();

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'User CRUD Real',
                'email' => 'crud-user@example.test',
                'phone' => '081234567890',
                'password' => 'password-crud',
                'role' => 'farmer',
                'status' => 'active',
                'verification_status' => 'verified',
            ])
            ->assertRedirect();

        $createdUser = User::query()->where('email', 'crud-user@example.test')->firstOrFail();

        $this->assertDatabaseHas('users', [
            'id' => $createdUser->id,
            'name' => 'User CRUD Real',
            'phone' => '081234567890',
            'role' => 'farmer',
            'status' => 'active',
            'verification_status' => 'verified',
        ]);
        $this->assertTrue($createdUser->hasRole(UserRole::Farmer->value));

        $this->actingAs($admin)
            ->patch(route('admin.users.update', $createdUser), [
                'name' => 'User CRUD Updated',
                'email' => 'crud-updated@example.test',
                'phone' => '081234567891',
                'password' => '',
                'role' => 'ppl',
                'status' => 'inactive',
                'verification_status' => 'pending',
            ])
            ->assertRedirect();

        $createdUser->refresh();

        $this->assertDatabaseHas('users', [
            'id' => $createdUser->id,
            'name' => 'User CRUD Updated',
            'email' => 'crud-updated@example.test',
            'phone' => '081234567891',
            'role' => 'ppl',
            'status' => 'inactive',
            'verification_status' => 'pending',
        ]);
        $this->assertTrue($createdUser->hasRole(UserRole::ExtensionOfficer->value));

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $createdUser))
            ->assertRedirect();

        $this->assertSoftDeleted('users', ['id' => $createdUser->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'admin_user_created']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'admin_user_updated']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'admin_user_deleted']);
    }

    private function adminUser(): User
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin->value,
            'status' => UserStatus::Active->value,
        ]);

        $admin->assignRole(UserRole::Admin->value);

        return $admin;
    }
}
