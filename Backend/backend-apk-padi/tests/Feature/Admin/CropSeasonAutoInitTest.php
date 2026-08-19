<?php

namespace Tests\Feature\Admin;

use App\Models\CropSeason;
use App\Models\Farm;
use App\Models\User;
use App\Services\Agriculture\CropSeasonService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CropSeasonAutoInitTest extends TestCase
{
    use RefreshDatabase;

    public function test_auto_generates_regional_crop_seasons_when_farm_created(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $farmer = User::factory()->create(['role' => 'farmer', 'status' => 'active']);

        $response = $this->actingAs($admin)
            ->from(route('admin.agriculture.index'))
            ->post(route('admin.agriculture.store'), [
                'farmer_user_id' => $farmer->id,
                'name' => 'Lahan Sentra Karawang IP300',
                'area_ha' => 3.5,
                'latitude' => -6.3031,
                'longitude' => 107.3009,
                'irrigation_type' => 'technical',
            ]);

        $farm = Farm::where('name', 'Lahan Sentra Karawang IP300')->first();
        $this->assertNotNull($farm);
        $response->assertRedirect(route('admin.agriculture.index'));

        // Verify that 3 crop seasons (MT1, MT2, MT3) were automatically generated
        $this->assertEquals(3, $farm->cropSeasons()->count());
        $activeSeason = $farm->cropSeasons()->where('status', 'active')->first();
        $this->assertNotNull($activeSeason);
    }

    public function test_auto_generates_all_farms_crop_seasons_if_database_empty(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $farmer = User::factory()->create(['role' => 'farmer', 'status' => 'active']);

        // Create farm without calling service
        $farm = Farm::create([
            'farmer_user_id' => $farmer->id,
            'name' => 'Lahan Tanpa Season',
            'area_ha' => 2.0,
            'latitude' => -7.25,
            'longitude' => 112.75,
            'irrigation_type' => 'technical',
        ]);

        $this->assertEquals(0, CropSeason::count());

        // Accessing agriculture dashboard auto-generates crop seasons
        $response = $this->actingAs($admin)->get(route('admin.agriculture.index'));
        $response->assertStatus(200);

        $this->assertGreaterThan(0, CropSeason::count());
        $this->assertEquals(3, $farm->cropSeasons()->count());
    }
}
