<?php

namespace Tests\Feature\Admin;

use App\Models\Farm;
use App\Models\SoilDetection;
use App\Models\SoilType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FarmSoilTypeIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $farmer;
    protected SoilType $alluvial;
    protected SoilType $grumosol;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->farmer = User::factory()->create([
            'role' => 'farmer',
        ]);

        $this->alluvial = SoilType::firstOrCreate(
            ['code' => 'alluvial'],
            [
                'name' => 'Aluvial',
                'description' => 'Endapan sungai dataran rendah.',
                'is_active' => true,
            ]
        );

        $this->grumosol = SoilType::firstOrCreate(
            ['code' => 'grumosol'],
            [
                'name' => 'Grumosol',
                'description' => 'Tanah liat hitam / kelabu.',
                'is_active' => true,
            ]
        );
    }

    /**
     * TEST 1: Admin -> Tambah Jenis Tanah via AJAX endpoint
     */
    public function test_admin_can_create_new_master_soil_type(): void
    {
        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.soil.types.store'), [
                'name' => 'Regosol Vulkanik',
                'code' => 'regosol',
                'description' => 'Tanah berbutir kasar dari abu vulkanik.',
                'is_active' => true,
            ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Regosol Vulkanik',
                    'code' => 'regosol',
                ],
            ]);

        $this->assertDatabaseHas('soil_types', [
            'name' => 'Regosol Vulkanik',
            'code' => 'regosol',
        ]);
    }

    /**
     * TEST 2 & 3: Admin -> Tambah Lahan saves soil_type_id and shows on agriculture page
     */
    public function test_admin_can_create_farm_with_soil_type(): void
    {
        $response = $this->actingAs($this->admin)
            ->post(route('admin.agriculture.store'), [
                'farmer_user_id' => $this->farmer->id,
                'name' => 'Sawah Blok Selatan',
                'area_ha' => 1.5,
                'latitude' => -6.32,
                'longitude' => 108.20,
                'soil_type_id' => $this->alluvial->id,
                'irrigation_type' => 'teknis',
                'irrigation_notes' => 'Saluran irigasi lancar',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('farms', [
            'name' => 'Sawah Blok Selatan',
            'soil_type_id' => $this->alluvial->id,
            'soil_type' => 'alluvial',
            'farmer_user_id' => $this->farmer->id,
        ]);

        // Verify that index page renders farm with soil type
        $pageResponse = $this->actingAs($this->admin)
            ->get(route('admin.agriculture.index'));

        $pageResponse->assertStatus(200);
        $pageResponse->assertSee('Sawah Blok Selatan');
        $pageResponse->assertSee('Aluvial');
    }

    /**
     * TEST 4: Admin -> Edit Lahan updates soil_type_id
     */
    public function test_admin_can_edit_farm_soil_type(): void
    {
        $farm = Farm::create([
            'farmer_user_id' => $this->farmer->id,
            'name' => 'Sawah Blok Timur',
            'area_ha' => 2.0,
            'latitude' => -6.35,
            'longitude' => 108.22,
            'soil_type_id' => $this->alluvial->id,
            'soil_type' => 'alluvial',
            'irrigation_type' => 'teknis',
        ]);

        $response = $this->actingAs($this->admin)
            ->patch(route('admin.agriculture.update', $farm), [
                'farmer_user_id' => $this->farmer->id,
                'name' => 'Sawah Blok Timur Diperbarui',
                'area_ha' => 2.0,
                'latitude' => -6.35,
                'longitude' => 108.22,
                'soil_type_id' => $this->grumosol->id,
                'irrigation_type' => 'teknis',
            ]);

        $response->assertRedirect();

        $farm->refresh();
        $this->assertEquals($this->grumosol->id, $farm->soil_type_id);
        $this->assertEquals('grumosol', $farm->soil_type);
    }

    /**
     * TEST 5: Backend is Single Source of Truth for Soil Detection samples
     * Farm soil type must override any client-supplied soil_type
     */
    public function test_soil_sample_strictly_inherits_farm_soil_type(): void
    {
        $farm = Farm::create([
            'farmer_user_id' => $this->farmer->id,
            'name' => 'Sawah Aluvial Utama',
            'area_ha' => 1.2,
            'latitude' => -6.33,
            'longitude' => 108.21,
            'soil_type_id' => $this->alluvial->id,
            'soil_type' => 'alluvial',
            'irrigation_type' => 'teknis',
        ]);

        // Attempt to create soil detection for this farm while spoofing soil_type as 'peat' or different id
        $response = $this->actingAs($this->admin)
            ->post(route('admin.soil.store'), [
                'farm_id' => $farm->id,
                'ph_level' => 6.5,
                'nitrogen_ppm' => 120,
                'phosphorus_ppm' => 25,
                'potassium_ppm' => 150,
                'moisture_percentage' => 55,
                'organic_matter_percentage' => 2.5,
                'soil_type' => 'peat', // Client spoof
                'soil_type_id' => 9999, // Client spoof
                'tested_at' => now()->format('Y-m-d H:i:s'),
            ]);

        $response->assertRedirect();

        // Backend MUST have used $farm->soil_type_id and $farm->soil_type
        $this->assertDatabaseHas('soil_detections', [
            'farm_id' => $farm->id,
            'soil_type_id' => $this->alluvial->id,
            'soil_type' => 'alluvial',
        ]);
    }

    /**
     * TEST 6: Legacy Farm without soil type does not crash and displays 'Belum ditentukan'
     */
    public function test_legacy_farm_without_soil_type_renders_safely(): void
    {
        $farm = Farm::create([
            'farmer_user_id' => $this->farmer->id,
            'name' => 'Sawah Tanpa Jenis Tanah',
            'area_ha' => 0.8,
            'latitude' => -6.31,
            'longitude' => 108.19,
            'soil_type_id' => null,
            'soil_type' => null,
            'irrigation_type' => 'teknis',
        ]);

        $response = $this->actingAs($this->admin)
            ->get(route('admin.agriculture.index'));

        $response->assertStatus(200);
        $response->assertSee('Sawah Tanpa Jenis Tanah');
        $response->assertSee('Belum ditentukan');
    }

    /**
     * TEST 7: API / Mobile compatibility for Farm endpoints
     */
    public function test_api_farm_endpoints_support_soil_type(): void
    {
        // Test API store with soil_type_id
        $response = $this->actingAs($this->farmer, 'sanctum')
            ->postJson('/api/v1/farms', [
                'name' => 'Sawah Mobile API',
                'area_ha' => 1.0,
                'latitude' => -6.32,
                'longitude' => 108.20,
                'soil_type_id' => $this->alluvial->id,
                'irrigation_type' => 'teknis',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.soil_type_id', $this->alluvial->id)
            ->assertJsonPath('data.soil_type_name', 'Aluvial');

        $this->assertDatabaseHas('farms', [
            'name' => 'Sawah Mobile API',
            'soil_type_id' => $this->alluvial->id,
            'soil_type' => 'alluvial',
        ]);
    }
}
