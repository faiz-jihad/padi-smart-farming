<?php

namespace Tests\Feature;

use App\Models\DiseaseScan;
use App\Models\Farm;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DiseaseScanApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

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
            'http://ai.test/api/v1/treatments/recommend' => Http::response([
                'success' => true,
                'data' => [],
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

        Http::assertNotSent(fn (Request $request) => str_contains($request->url(), '/diseases/learn'));
    }

    public function test_scan_uses_labelled_fallback_when_ai_service_is_unavailable(): void
    {
        Storage::fake('public');
        config(['services.ai.base_url' => 'http://ai.test/api/v1']);

        Http::fake([
            'http://ai.test/api/v1/diseases/detect' => Http::response([
                'success' => false,
                'error' => [
                    'code' => 'MODEL_UNAVAILABLE',
                    'message' => 'Model belum tersedia.',
                ],
            ], 503),
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
            'image' => UploadedFile::fake()->image('leaf.jpg', 900, 900),
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.scan.quality_status', 'fallback_demo')
            ->assertJsonPath('data.scan.model_version', 'local-demo-fallback-v1');

        $this->assertDatabaseCount('disease_scans', 1);
        $this->assertDatabaseHas('disease_scans', [
            'farmer_id' => $farmer->id,
            'farm_id' => $farm->id,
            'quality_status' => 'fallback_demo',
            'model_version' => 'local-demo-fallback-v1',
        ]);
    }

    public function test_farmer_upload_non_leaf_photo_is_rejected(): void
    {
        Storage::fake('public');
        config(['services.ai.base_url' => 'http://ai.test/api/v1']);

        Http::fake([
            'http://ai.test/api/v1/diseases/detect' => Http::response([
                'success' => false,
                'error' => [
                    'code' => 'IMAGE_NOT_LEAF',
                    'message' => 'Objek pada gambar bukan daun padi. Silakan ambil foto daun padi dengan jelas.',
                ],
            ], 422),
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

        $response = $this->postJson('/api/v1/disease-scans', [
            'farm_id' => $farm->id,
            'image' => UploadedFile::fake()->image('bukan-daun.jpg', 900, 900),
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Objek pada gambar bukan daun padi. Silakan ambil foto daun padi dengan jelas.');

        $this->assertDatabaseCount('disease_scans', 0);
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

    public function test_farmer_can_submit_feedback_to_help_ai_learn(): void
    {
        Storage::fake('public');
        $farmer = User::factory()->create(['role' => 'farmer', 'status' => 'active']);
        $farm = Farm::create([
            'farmer_user_id' => $farmer->id,
            'name' => 'Sawah Uji Belajar',
            'area_ha' => 1.0,
            'latitude' => -6.25,
            'longitude' => 108.08,
            'irrigation_type' => 'irrigated',
        ]);

        $scan = DiseaseScan::create([
            'farmer_id' => $farmer->id,
            'farm_id' => $farm->id,
            'image_url' => '/storage/disease-scans/leaf.jpg',
            'image_hash' => hash('sha256', 'leaf_sample'),
            'quality_status' => 'high',
            'predicted_class' => 'Blast',
            'confidence' => 0.88,
            'model_version' => 'padi-disease-v2',
            'scanned_at' => now(),
        ]);

        Sanctum::actingAs($farmer);

        $response = $this->postJson("/api/v1/disease-scans/{$scan->id}/feedback", [
            'status' => 'confirmed',
            'notes' => 'Diagnosa sangat akurat di sawah',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.is_learned', true)
            ->assertJsonPath('data.scan.user_feedback', 'confirmed');

        $this->assertDatabaseHas('disease_scans', [
            'id' => $scan->id,
            'user_feedback' => 'confirmed',
            'is_learned' => true,
        ]);
    }
}
