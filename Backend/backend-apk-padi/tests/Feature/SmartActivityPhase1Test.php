<?php

namespace Tests\Feature;

use App\Models\CropSeason;
use App\Models\Farm;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmartActivityPhase1Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_farmer_can_store_structured_activity_with_source_and_sync_metadata(): void
    {
        $farmer = User::factory()->create();
        $farm = Farm::query()->create([
            'farmer_user_id' => $farmer->id,
            'name' => 'Sawah Smart',
            'area_ha' => 2,
            'latitude' => -7.123,
            'longitude' => 112.456,
            'irrigation_type' => 'teknis',
        ]);
        $season = CropSeason::query()->create([
            'farm_id' => $farm->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($farmer)
            ->postJson('/api/v1/farm-activities', [
                'crop_season_id' => $season->id,
                'type' => 'fertilizing',
                'occurred_at' => now()->toDateString(),
                'notes' => 'Pemupukan susulan di lahan satu',
                'source' => 'VOICE',
                'status' => 'COMPLETED',
                'sync_status' => 'pending',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.source', 'VOICE')
            ->assertJsonPath('data.status', 'COMPLETED')
            ->assertJsonPath('data.sync_status', 'pending');

        $this->assertDatabaseHas('farm_activities', [
            'crop_season_id' => $season->id,
            'source' => 'VOICE',
            'status' => 'COMPLETED',
            'sync_status' => 'pending',
        ]);
    }
}
