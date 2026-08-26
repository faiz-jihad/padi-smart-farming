<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\AgricultureKnowledge;
use App\Models\Farm;
use App\Models\SoilDetection;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExtensionOfficerAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    private function extensionOfficer(): User
    {
        $user = User::factory()->create([
            'role' => UserRole::ExtensionOfficer->value,
            'status' => UserStatus::Active->value,
        ]);

        $user->assignRole(UserRole::ExtensionOfficer->value);

        return $user;
    }

    private function admin(): User
    {
        $user = User::factory()->create([
            'role' => UserRole::Admin->value,
            'status' => UserStatus::Active->value,
        ]);

        $user->assignRole(UserRole::Admin->value);

        return $user;
    }

    private function farmer(): User
    {
        $user = User::factory()->create([
            'role' => UserRole::Farmer->value,
            'status' => UserStatus::Active->value,
        ]);

        $user->assignRole(UserRole::Farmer->value);

        return $user;
    }

    private function buyer(): User
    {
        $user = User::factory()->create([
            'role' => UserRole::Buyer->value,
            'status' => UserStatus::Active->value,
        ]);

        $user->assignRole(UserRole::Buyer->value);

        return $user;
    }

    // ─── Weather Settings Authorization (Admin Only) ──────────────────────────

    public function test_admin_can_access_weather_settings(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('admin.weather.settings'))
            ->assertOk();
    }

    public function test_extension_officer_cannot_access_weather_settings(): void
    {
        $ppl = $this->extensionOfficer();

        $this->actingAs($ppl)
            ->get(route('admin.weather.settings'))
            ->assertForbidden();

        $this->actingAs($ppl)
            ->patch(route('admin.weather.settings.update'), [
                'weather_provider' => 'openweathermap',
            ])
            ->assertForbidden();

        $this->actingAs($ppl)
            ->post(route('admin.weather.test-connection'))
            ->assertForbidden();

        $this->actingAs($ppl)
            ->post(route('admin.weather.clear-cache'))
            ->assertForbidden();
    }

    public function test_farmer_and_buyer_cannot_access_weather_settings(): void
    {
        $farmer = $this->farmer();
        $buyer = $this->buyer();

        $this->actingAs($farmer)
            ->get(route('admin.weather.settings'))
            ->assertForbidden();

        $this->actingAs($buyer)
            ->get(route('admin.weather.settings'))
            ->assertForbidden();
    }

    // ─── Restricted Deletions Authorization (Admin Only) ─────────────────────

    public function test_admin_can_delete_agriculture_farm(): void
    {
        $admin = $this->admin();
        $farmer = $this->farmer();
        $farm = Farm::query()->create([
            'farmer_user_id' => $farmer->id,
            'name' => 'Petak Sawah Uji',
            'area_ha' => 1.5,
            'latitude' => -6.32,
            'longitude' => 108.32,
            'irrigation_type' => 'Irigasi Teknis',
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.agriculture.destroy', $farm))
            ->assertRedirect();

        $this->assertDatabaseMissing('farms', ['id' => $farm->id]);
    }

    public function test_extension_officer_farmer_buyer_cannot_delete_agriculture_farm(): void
    {
        $farmer = $this->farmer();
        $ppl = $this->extensionOfficer();
        $buyer = $this->buyer();

        $farm = Farm::query()->create([
            'farmer_user_id' => $farmer->id,
            'name' => 'Petak Sawah Lindung',
            'area_ha' => 2.0,
            'latitude' => -6.32,
            'longitude' => 108.32,
            'irrigation_type' => 'Tadah Hujan',
        ]);

        $this->actingAs($ppl)
            ->delete(route('admin.agriculture.destroy', $farm))
            ->assertForbidden();

        $this->actingAs($farmer)
            ->delete(route('admin.agriculture.destroy', $farm))
            ->assertForbidden();

        $this->actingAs($buyer)
            ->delete(route('admin.agriculture.destroy', $farm))
            ->assertForbidden();

        $this->assertDatabaseHas('farms', ['id' => $farm->id]);
    }

    public function test_admin_can_delete_soil_detection_record(): void
    {
        $admin = $this->admin();
        $farmer = $this->farmer();
        $farm = Farm::query()->create([
            'farmer_user_id' => $farmer->id,
            'name' => 'Petak Tanah Uji',
            'area_ha' => 1.0,
            'latitude' => -6.32,
            'longitude' => 108.32,
            'irrigation_type' => 'Teknis',
        ]);

        $soil = SoilDetection::query()->create([
            'farm_id' => $farm->id,
            'tested_by_user_id' => $admin->id,
            'sample_code' => 'SMP-TEST-001',
            'nitrogen' => 20,
            'phosphorus' => 15,
            'potassium' => 100,
            'ph' => 6.5,
            'tested_at' => now(),
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.soil.destroy', $soil))
            ->assertRedirect(route('admin.soil.index'));

        $this->assertDatabaseMissing('soil_detections', ['id' => $soil->id]);
    }

    public function test_extension_officer_cannot_delete_soil_detection_record(): void
    {
        $admin = $this->admin();
        $ppl = $this->extensionOfficer();
        $farmer = $this->farmer();

        $farm = Farm::query()->create([
            'farmer_user_id' => $farmer->id,
            'name' => 'Petak Tanah Aman',
            'area_ha' => 1.0,
            'latitude' => -6.32,
            'longitude' => 108.32,
            'irrigation_type' => 'Teknis',
        ]);

        $soil = SoilDetection::query()->create([
            'farm_id' => $farm->id,
            'tested_by_user_id' => $admin->id,
            'sample_code' => 'SMP-TEST-002',
            'nitrogen' => 20,
            'phosphorus' => 15,
            'potassium' => 100,
            'ph' => 6.5,
            'tested_at' => now(),
        ]);

        $this->actingAs($ppl)
            ->delete(route('admin.soil.destroy', $soil))
            ->assertForbidden();

        $this->actingAs($farmer)
            ->delete(route('admin.soil.destroy', $soil))
            ->assertForbidden();

        $this->assertDatabaseHas('soil_detections', ['id' => $soil->id]);
    }

    public function test_admin_can_delete_knowledge_article(): void
    {
        $admin = $this->admin();
        $article = AgricultureKnowledge::query()->create([
            'category' => 'budidaya',
            'title' => 'Panduan Hapus Uji',
            'slug' => 'panduan-hapus-uji',
            'summary' => 'Ringkasan panduan.',
            'content_markdown' => '# Konten panduan.',
            'tags' => ['padi'],
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.knowledge.destroy', $article))
            ->assertRedirect(route('admin.knowledge.index'));

        $this->assertDatabaseMissing('agriculture_knowledges', ['id' => $article->id]);
    }

    public function test_extension_officer_cannot_delete_knowledge_article(): void
    {
        $ppl = $this->extensionOfficer();
        $farmer = $this->farmer();
        $article = AgricultureKnowledge::query()->create([
            'category' => 'budidaya',
            'title' => 'Panduan Tetap Ada',
            'slug' => 'panduan-tetap-ada',
            'summary' => 'Ringkasan panduan.',
            'content_markdown' => '# Konten panduan.',
            'tags' => ['padi'],
        ]);

        $this->actingAs($ppl)
            ->delete(route('admin.knowledge.destroy', $article))
            ->assertForbidden();

        $this->actingAs($farmer)
            ->delete(route('admin.knowledge.destroy', $article))
            ->assertForbidden();

        $this->assertDatabaseHas('agriculture_knowledges', ['id' => $article->id]);
    }

    // ─── PPL Allowed Features (Must Remain Accessible) ───────────────────────

    public function test_extension_officer_can_view_operational_features(): void
    {
        $ppl = $this->extensionOfficer();

        $this->actingAs($ppl)->get(route('admin.agriculture.index'))->assertOk();
        $this->actingAs($ppl)->get(route('admin.weather.index'))->assertOk();
        $this->actingAs($ppl)->get(route('admin.soil.index'))->assertOk();
        $this->actingAs($ppl)->get(route('admin.knowledge.index'))->assertOk();
        $this->actingAs($ppl)->get(route('admin.disease.index'))->assertOk();
        $this->actingAs($ppl)->get(route('admin.early-warning.index'))->assertOk();
    }

    public function test_extension_officer_cannot_access_admin_only_management(): void
    {
        $ppl = $this->extensionOfficer();

        $this->actingAs($ppl)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($ppl)->get(route('admin.broadcast.index'))->assertForbidden();
        $this->actingAs($ppl)->get(route('admin.marketplace.index'))->assertForbidden();
        $this->actingAs($ppl)->get(route('admin.audit.index'))->assertForbidden();
        $this->actingAs($ppl)->get(route('admin.farmer-profiles.index'))->assertForbidden();
    }
}