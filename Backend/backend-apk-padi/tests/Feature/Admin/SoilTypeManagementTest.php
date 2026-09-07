<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Farm;
use App\Models\SoilDetection;
use App\Models\SoilType;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SoilTypeManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    private function createUser(string $role): User
    {
        $user = User::factory()->create([
            'role' => $role,
            'status' => UserStatus::Active->value,
        ]);
        $user->assignRole($role);

        return $user;
    }

    public function test_admin_can_view_soil_types_on_create_page(): void
    {
        $admin = $this->createUser(UserRole::Admin->value);
        SoilType::create([
            'name' => 'Lempung Berliat Khusus',
            'code' => 'clay_loam_special',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.soil.create'));

        $response->assertStatus(200);
        $response->assertSee('Lempung Berliat Khusus');
        $response->assertSee('Tambah Jenis Tanah');
    }

    public function test_admin_can_create_soil_type_via_ajax(): void
    {
        $admin = $this->createUser(UserRole::Admin->value);

        $payload = [
            'name' => 'Lempung Debu Organik',
            'code' => 'silt_loam_org',
            'description' => 'Tanah lempung debu dengan kandungan organik tinggi.',
        ];

        $response = $this->actingAs($admin)
            ->postJson(route('admin.soil.types.store'), $payload);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'data' => [
                'name' => 'Lempung Debu Organik',
                'code' => 'silt_loam_org',
                'created_by' => $admin->id,
            ],
        ]);

        $this->assertDatabaseHas('soil_types', [
            'name' => 'Lempung Debu Organik',
            'code' => 'silt_loam_org',
            'created_by' => $admin->id,
        ]);
    }

    public function test_extension_officer_can_create_soil_type_via_ajax(): void
    {
        $ppl = $this->createUser(UserRole::ExtensionOfficer->value);

        $payload = [
            'name' => 'Tanah Regosol Gunung',
            'description' => 'Tanah berbutir kasar dari material vulkanik.',
        ];

        $response = $this->actingAs($ppl)
            ->postJson(route('admin.soil.types.store'), $payload);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
            'data' => [
                'name' => 'Tanah Regosol Gunung',
                'code' => 'tanah_regosol_gunung',
            ],
        ]);

        $this->assertDatabaseHas('soil_types', [
            'name' => 'Tanah Regosol Gunung',
            'code' => 'tanah_regosol_gunung',
            'created_by' => $ppl->id,
        ]);
    }

    public function test_farmer_cannot_create_soil_type(): void
    {
        $farmer = $this->createUser(UserRole::Farmer->value);

        $payload = [
            'name' => 'Tanah Petani Ilegal',
        ];

        $response = $this->actingAs($farmer)
            ->postJson(route('admin.soil.types.store'), $payload);

        $response->assertForbidden();
        $this->assertDatabaseMissing('soil_types', [
            'name' => 'Tanah Petani Ilegal',
        ]);
    }

    public function test_guest_cannot_create_soil_type(): void
    {
        $payload = [
            'name' => 'Tanah Anonim',
        ];

        $response = $this->postJson(route('admin.soil.types.store'), $payload);

        $response->assertUnauthorized();
    }

    public function test_soil_type_name_is_required(): void
    {
        $admin = $this->createUser(UserRole::Admin->value);

        $response = $this->actingAs($admin)
            ->postJson(route('admin.soil.types.store'), [
                'name' => '',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }

    public function test_duplicate_soil_type_name_is_rejected(): void
    {
        $admin = $this->createUser(UserRole::Admin->value);

        SoilType::create([
            'name' => 'Aluvial Super',
            'code' => 'aluvial_super',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)
            ->postJson(route('admin.soil.types.store'), [
                'name' => 'Aluvial Super',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }

    public function test_admin_can_fetch_soil_types_list_json(): void
    {
        $admin = $this->createUser(UserRole::Admin->value);

        $response = $this->actingAs($admin)
            ->getJson(route('admin.soil.types.index'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                '*' => ['id', 'name', 'code', 'is_active'],
            ],
        ]);
    }

    public function test_can_create_soil_detection_with_custom_soil_type(): void
    {
        $admin = $this->createUser(UserRole::Admin->value);
        $farmer = $this->createUser(UserRole::Farmer->value);

        $farm = Farm::create([
            'farmer_user_id' => $farmer->id,
            'name' => 'Sawah Uji Kustom',
            'area_ha' => 1.5,
            'latitude' => -6.35,
            'longitude' => 107.25,
            'irrigation_type' => 'irrigated',
        ]);

        $customSoilType = SoilType::create([
            'name' => 'Lempung Berkerikil',
            'code' => 'gravelly_loam',
            'is_active' => true,
        ]);

        $payload = [
            'farm_id' => $farm->id,
            'sample_code' => 'SOIL-CUSTOM-001',
            'ph_level' => 6.5,
            'nitrogen_ppm' => 120,
            'phosphorus_ppm' => 30,
            'potassium_ppm' => 150,
            'moisture_percentage' => 60.0,
            'organic_matter_percentage' => 2.8,
            'soil_temp_celsius' => 26.0,
            'soil_type' => 'gravelly_loam',
            'tested_at' => now()->format('Y-m-d H:i:s'),
        ];

        $response = $this->actingAs($admin)
            ->post(route('admin.soil.store'), $payload);

        $response->assertRedirect();

        $detection = SoilDetection::where('sample_code', 'SOIL-CUSTOM-001')->first();
        $this->assertNotNull($detection);
        $this->assertEquals($customSoilType->id, $detection->soil_type_id);
        $this->assertEquals('gravelly_loam', $detection->soil_type);
        $this->assertEquals('Lempung Berkerikil', $detection->soilType->name);
    }
}
