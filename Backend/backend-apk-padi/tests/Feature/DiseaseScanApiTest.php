<?php

namespace Tests\Feature;

use App\Models\DiseaseScan;
use App\Models\Farm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DiseaseScanApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_farmer_can_upload_leaf_photo_and_store_ai_result(): void
    {
        Storage::fake('public');
        config(['services.ai.base_url' => 'http://ai.test/api/v1']);

        Http::fake([
            'http://ai.test/api/v1/diseases/detect' => Http::response([
                'success' => true,
                'data' => [
                    'disease_code' => 'blast',
                    'disease_name' => 'Blast',
                    'confidence' => 0.8732,
                    'confidence_level' => 'high',
                    'image_quality' => [
                        'is_acceptable' => true,
                        'blur_score' => 91.4,
                        'brightness_score' => 72.2,
                        'warnings' => [],
                    ],
                    'needs_expert_review' => false,
                    'model_version' => 'padi-disease-v2',
                    'processing_time_ms' => 214,
                ],
            ], 200),
        ]);

        $farmer = User::factory()->create(['role' => 'farmer', 'status' => 'active']);
        $farm = Farm::create([
            'farmer_user_id' => $farmer->id,
            'name' => 'Sawah Uji',
            'area_ha' => 1.2,
            'latitude' => -6.25,
            'longitude' => 108.08,
            'irrigation_type' => 'irrigated',
        ]);

        Sanctum::actingAs($farmer);

        $response = $this->post('/api/v1/disease-scans', [
            'farm_id' => $farm->id,
            'image' => UploadedFile::fake()->image('daun-padi.jpg', 900, 900),
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.scan.predicted_class', 'Blast')
            ->assertJsonPath('data.scan.confidence', 0.8732);

        $this->assertDatabaseHas('disease_scans', [
            'farmer_id' => $farmer->id,
            'farm_id' => $farm->id,
            'predicted_class' => 'Blast',
            'quality_status' => 'high',
            'model_version' => 'padi-disease-v2',
        ]);
    }

    public function test_farmer_can_only_report_their_own_scan(): void
    {
        $owner = User::factory()->create(['role' => 'farmer', 'status' => 'active']);
        $other = User::factory()->create(['role' => 'farmer', 'status' => 'active']);

        $farm = Farm::create([
            'farmer_user_id' => $owner->id,
            'name' => 'Sawah Pemilik',
            'area_ha' => 1.2,
            'latitude' => -6.25,
            'longitude' => 108.08,
            'irrigation_type' => 'irrigated',
        ]);

        $scan = DiseaseScan::create([
            'farmer_id' => $owner->id,
            'farm_id' => $farm->id,
            'image_url' => '/storage/disease-scans/example.jpg',
            'image_hash' => str_repeat('a', 64),
            'quality_status' => 'high',
            'predicted_class' => 'Blast',
            'confidence' => 0.91,
            'model_version' => 'padi-disease-v2',
            'scanned_at' => now(),
        ]);

        Sanctum::actingAs($other);

        $this->postJson('/api/v1/community-reports', [
            'scan_id' => $scan->id,
            'latitude' => -6.25,
            'longitude' => 108.08,
            'radius_km' => 5,
            'consent_given' => true,
        ])->assertUnprocessable();

        Sanctum::actingAs($owner);

        $this->postJson('/api/v1/community-reports', [
            'scan_id' => $scan->id,
            'latitude' => -6.25,
            'longitude' => 108.08,
            'radius_km' => 5,
            'consent_given' => true,
        ])->assertCreated()
            ->assertJsonPath('success', true);
    }
}
