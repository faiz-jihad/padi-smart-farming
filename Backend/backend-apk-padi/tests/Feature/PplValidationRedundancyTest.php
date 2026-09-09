<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\DiseaseScan;
use App\Models\Farm;
use App\Models\PplValidation;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PplValidationRedundancyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    private function createFarm(int $farmerId, string $name = 'Sawah Blok A'): Farm
    {
        return Farm::create([
            'farmer_user_id' => $farmerId,
            'name' => $name,
            'area_ha' => 1.0,
            'latitude' => -6.25,
            'longitude' => 108.08,
            'irrigation_type' => 'irrigated',
        ]);
    }

    public function test_farmer_can_submit_disease_scan_to_ppl_successfully(): void
    {
        $farmer = User::factory()->create([
            'role' => UserRole::Farmer->value,
        ]);
        $farmer->assignRole('farmer');

        $ppl = User::factory()->create([
            'role' => UserRole::ExtensionOfficer->value,
        ]);
        $ppl->assignRole('extension_officer');

        $farm = $this->createFarm($farmer->id, 'Sawah Blok A');

        $scan = DiseaseScan::create([
            'farmer_id' => $farmer->id,
            'farm_id' => $farm->id,
            'image_url' => 'http://localhost/storage/scans/kresek.jpg',
            'quality_status' => 'passed',
            'predicted_class' => 'Hawar Daun Bakteri (Kresek)',
            'confidence' => 0.9450,
            'model_version' => 'yolo11-padi-v1',
            'scanned_at' => now(),
        ]);

        Sanctum::actingAs($farmer);

        $response = $this->postJson('/api/v1/ppl-validations', [
            'scan_id' => $scan->id,
            'notes' => 'Gejala mulai terlihat di petak barat.',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'validation' => [
                        'scan_id' => $scan->id,
                        'status' => 'pending',
                        'notes' => 'Gejala mulai terlihat di petak barat.',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('ppl_validations', [
            'scan_id' => $scan->id,
            'status' => 'pending',
        ]);
    }

    public function test_farmer_cannot_report_healthy_paddy_to_ppl(): void
    {
        $farmer = User::factory()->create([
            'role' => UserRole::Farmer->value,
        ]);
        $farmer->assignRole('farmer');

        $farm = $this->createFarm($farmer->id);

        $healthyScan = DiseaseScan::create([
            'farmer_id' => $farmer->id,
            'farm_id' => $farm->id,
            'image_url' => 'http://localhost/storage/scans/sehat.jpg',
            'quality_status' => 'passed',
            'predicted_class' => 'Normal / Tanaman Sehat',
            'confidence' => 0.9800,
            'model_version' => 'yolo11-padi-v1',
            'scanned_at' => now(),
        ]);

        Sanctum::actingAs($farmer);

        $response = $this->postJson('/api/v1/ppl-validations', [
            'scan_id' => $healthyScan->id,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'code' => 'PLANT_IS_HEALTHY',
            ]);

        $this->assertDatabaseMissing('ppl_validations', [
            'scan_id' => $healthyScan->id,
        ]);
    }

    public function test_farmer_cannot_report_exact_same_scan_twice(): void
    {
        $farmer = User::factory()->create([
            'role' => UserRole::Farmer->value,
        ]);
        $farmer->assignRole('farmer');

        $farm = $this->createFarm($farmer->id);

        $scan = DiseaseScan::create([
            'farmer_id' => $farmer->id,
            'farm_id' => $farm->id,
            'image_url' => 'http://localhost/storage/scans/scan1.jpg',
            'quality_status' => 'passed',
            'predicted_class' => 'Penyakit Blas (Blast)',
            'confidence' => 0.9100,
            'model_version' => 'yolo11-padi-v1',
            'scanned_at' => now(),
        ]);

        PplValidation::create([
            'scan_id' => $scan->id,
            'status' => 'pending',
            'notes' => 'Laporan pertama',
        ]);

        Sanctum::actingAs($farmer);

        $response = $this->postJson('/api/v1/ppl-validations', [
            'scan_id' => $scan->id,
            'notes' => 'Laporan kedua untuk scan sama',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'code' => 'DUPLICATE_SCAN_REPORT',
            ]);

        $this->assertEquals(1, PplValidation::where('scan_id', $scan->id)->count());
    }

    public function test_farmer_cannot_create_redundant_report_for_same_disease_on_same_farm_while_active(): void
    {
        $farmer = User::factory()->create([
            'role' => UserRole::Farmer->value,
        ]);
        $farmer->assignRole('farmer');

        $farm = $this->createFarm($farmer->id, 'Sawah Petak 1');

        // Scan 1 - sudah dilaporkan dan masih 'pending'
        $scan1 = DiseaseScan::create([
            'farmer_id' => $farmer->id,
            'farm_id' => $farm->id,
            'image_url' => 'http://localhost/storage/scans/scan1.jpg',
            'quality_status' => 'passed',
            'predicted_class' => 'Hawar Daun Bakteri (Kresek)',
            'confidence' => 0.9200,
            'model_version' => 'yolo11-padi-v1',
            'scanned_at' => now()->subHour(),
        ]);

        PplValidation::create([
            'scan_id' => $scan1->id,
            'status' => 'pending',
            'notes' => 'Laporan awal gejala kresek',
        ]);

        // Scan 2 - hasil deteksi baru pada hari yang sama untuk penyakit yang sama di lahan yang sama
        $scan2 = DiseaseScan::create([
            'farmer_id' => $farmer->id,
            'farm_id' => $farm->id,
            'image_url' => 'http://localhost/storage/scans/scan2.jpg',
            'quality_status' => 'passed',
            'predicted_class' => 'Hawar Daun Bakteri (Kresek)',
            'confidence' => 0.9500,
            'model_version' => 'yolo11-padi-v1',
            'scanned_at' => now(),
        ]);

        Sanctum::actingAs($farmer);

        // Petani mencoba melaporkan scan 2
        $response = $this->postJson('/api/v1/ppl-validations', [
            'scan_id' => $scan2->id,
            'notes' => 'Foto lain dari petak yang sama',
        ]);

        // Harus ditolak dengan status 422 DUPLICATE_ACTIVE_PROBLEM
        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'code' => 'DUPLICATE_ACTIVE_PROBLEM',
            ]);

        $this->assertStringContainsString('sudah pernah dilaporkan', $response->json('message'));
        $this->assertDatabaseMissing('ppl_validations', [
            'scan_id' => $scan2->id,
        ]);
    }

    public function test_farmer_can_report_different_disease_on_same_farm(): void
    {
        $farmer = User::factory()->create([
            'role' => UserRole::Farmer->value,
        ]);
        $farmer->assignRole('farmer');

        $farm = $this->createFarm($farmer->id, 'Sawah Petak 1');

        // Scan 1 - Kresek (sudah dilaporkan)
        $scan1 = DiseaseScan::create([
            'farmer_id' => $farmer->id,
            'farm_id' => $farm->id,
            'image_url' => 'http://localhost/storage/scans/scan1.jpg',
            'quality_status' => 'passed',
            'predicted_class' => 'Hawar Daun Bakteri (Kresek)',
            'confidence' => 0.9200,
            'model_version' => 'yolo11-padi-v1',
            'scanned_at' => now()->subHour(),
        ]);

        PplValidation::create([
            'scan_id' => $scan1->id,
            'status' => 'pending',
        ]);

        // Scan 2 - Penyakit yang berbeda (Blas) pada lahan yang sama
        $scan2 = DiseaseScan::create([
            'farmer_id' => $farmer->id,
            'farm_id' => $farm->id,
            'image_url' => 'http://localhost/storage/scans/scan2.jpg',
            'quality_status' => 'passed',
            'predicted_class' => 'Penyakit Blas (Blast)',
            'confidence' => 0.8800,
            'model_version' => 'yolo11-padi-v1',
            'scanned_at' => now(),
        ]);

        Sanctum::actingAs($farmer);

        $response = $this->postJson('/api/v1/ppl-validations', [
            'scan_id' => $scan2->id,
            'notes' => 'Ditemukan juga bercak belah ketupat di sudut utara',
        ]);

        // Karena penyakit berbeda, harus diizinkan (201)
        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'validation' => [
                        'scan_id' => $scan2->id,
                        'status' => 'pending',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('ppl_validations', [
            'scan_id' => $scan2->id,
            'status' => 'pending',
        ]);
    }

    public function test_farmer_cannot_submit_other_farmers_scan(): void
    {
        $farmer1 = User::factory()->create(['role' => UserRole::Farmer->value]);
        $farmer1->assignRole('farmer');

        $farmer2 = User::factory()->create(['role' => UserRole::Farmer->value]);
        $farmer2->assignRole('farmer');

        $farm2 = $this->createFarm($farmer2->id);

        $scanFarmer2 = DiseaseScan::create([
            'farmer_id' => $farmer2->id,
            'farm_id' => $farm2->id,
            'image_url' => 'http://localhost/storage/scans/f2.jpg',
            'quality_status' => 'passed',
            'predicted_class' => 'Penyakit Blas (Blast)',
            'confidence' => 0.9000,
            'model_version' => 'yolo11-padi-v1',
            'scanned_at' => now(),
        ]);

        Sanctum::actingAs($farmer1);

        $response = $this->postJson('/api/v1/ppl-validations', [
            'scan_id' => $scanFarmer2->id,
        ]);

        $response->assertStatus(403);
    }
}
