<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\AgricultureEvent;
use App\Models\EventRegistration;
use App\Models\Notification;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventCreationAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    private function createActor(string $role): User
    {
        $user = User::factory()->create([
            'role' => $role,
            'status' => UserStatus::Active->value,
        ]);
        $user->assignRole($role);

        return $user;
    }

    private function eventPayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Bimbingan Teknis Pengendalian Hama Terpadu',
            'description' => 'Pelatihan lapangan untuk petani mengenai teknik pengendalian OPT padi ramah lingkungan.',
            'category' => 'workshop',
            'event_date' => now()->addDays(7)->toDateString(),
            'event_time' => '08:30 - 12:00 WIB',
            'location_name' => 'Balai Penyuluhan Pertanian (BPP) Karawang',
            'location_address' => 'Jl. Raya Pertanian No. 12, Karawang Barat',
            'is_online' => false,
            'organizer' => 'Dinas Pertanian dan Ketahanan Pangan',
            'speaker' => 'Ir. Hendra Pratama, M.P.',
            'quota' => 50,
            'price_type' => 'free',
            'contact_person' => '081234567890',
        ], $overrides);
    }

    public function test_unauthenticated_user_cannot_create_event(): void
    {
        $response = $this->postJson('/api/v1/events', $this->eventPayload());

        $response->assertUnauthorized();
        $this->assertDatabaseEmpty('agriculture_events');
    }

    public function test_buyer_cannot_create_event(): void
    {
        $buyer = $this->createActor(UserRole::Buyer->value);
        $token = $buyer->createToken('Buyer Token')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson('/api/v1/events', $this->eventPayload([
                'title' => 'Acara Ilegal Dibuat oleh Pembeli',
            ]));

        $response->assertForbidden();
        $this->assertDatabaseMissing('agriculture_events', [
            'title' => 'Acara Ilegal Dibuat oleh Pembeli',
        ]);
        $this->assertDatabaseEmpty('agriculture_events');
    }

    public function test_extension_officer_can_create_official_event(): void
    {
        $officer = $this->createActor(UserRole::ExtensionOfficer->value);
        $token = $officer->createToken('Officer Token')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson('/api/v1/events', $this->eventPayload([
                'title' => 'Bimtek Resmi PPL Karawang',
                'category' => 'webinar',
            ]));

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', 'Bimtek Resmi PPL Karawang')
            ->assertJsonPath('data.source', 'official')
            ->assertJsonPath('data.approval_status', 'approved');

        $this->assertDatabaseHas('agriculture_events', [
            'title' => 'Bimtek Resmi PPL Karawang',
            'created_by' => $officer->id,
            'source' => 'official',
            'approval_status' => 'approved',
            'status' => 'upcoming',
        ]);
    }

    public function test_admin_can_create_official_event(): void
    {
        $admin = $this->createActor(UserRole::Admin->value);
        $token = $admin->createToken('Admin Token')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson('/api/v1/events', $this->eventPayload([
                'title' => 'Festival Panen Raya Nasional P.A.D.I.',
            ]));

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', 'Festival Panen Raya Nasional P.A.D.I.')
            ->assertJsonPath('data.source', 'official')
            ->assertJsonPath('data.approval_status', 'approved');

        $this->assertDatabaseHas('agriculture_events', [
            'title' => 'Festival Panen Raya Nasional P.A.D.I.',
            'created_by' => $admin->id,
            'source' => 'official',
            'approval_status' => 'approved',
        ]);
    }

    public function test_farmer_can_submit_event_proposal_as_pending(): void
    {
        $farmer = $this->createActor(UserRole::Farmer->value);
        $token = $farmer->createToken('Farmer Token')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson('/api/v1/events', $this->eventPayload([
                'title' => 'Musyawarah Petani Desa Sindang',
            ]));

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Pengajuan agenda berhasil dikirim dan menunggu persetujuan admin.')
            ->assertJsonPath('data.title', 'Musyawarah Petani Desa Sindang')
            ->assertJsonPath('data.source', 'farmer_submission')
            ->assertJsonPath('data.approval_status', 'pending');

        $this->assertDatabaseHas('agriculture_events', [
            'title' => 'Musyawarah Petani Desa Sindang',
            'created_by' => $farmer->id,
            'source' => 'farmer_submission',
            'approval_status' => 'pending',
            'status' => 'upcoming',
        ]);
    }

    public function test_farmer_pending_submission_not_in_public_events_list(): void
    {
        $farmer = $this->createActor(UserRole::Farmer->value);
        $admin = $this->createActor(UserRole::Admin->value);

        // Official approved event
        $approvedEvent = AgricultureEvent::create(array_merge($this->eventPayload([
            'title' => 'Pelatihan Irigasi Resmi',
        ]), [
            'created_by' => $admin->id,
            'source' => 'official',
            'approval_status' => 'approved',
            'status' => 'upcoming',
        ]));

        // Pending farmer submission
        $pendingEvent = AgricultureEvent::create(array_merge($this->eventPayload([
            'title' => 'Usulan Temu Tani Lokal',
        ]), [
            'created_by' => $farmer->id,
            'source' => 'farmer_submission',
            'approval_status' => 'pending',
            'status' => 'upcoming',
        ]));

        $token = $farmer->createToken('Farmer Token')->plainTextToken;

        $response = $this->withToken($token)->getJson('/api/v1/events');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $approvedEvent->id);
    }

    public function test_farmer_can_view_own_submissions_via_my_submissions(): void
    {
        $farmer1 = $this->createActor(UserRole::Farmer->value);
        $farmer2 = $this->createActor(UserRole::Farmer->value);

        $submission1 = AgricultureEvent::create(array_merge($this->eventPayload([
            'title' => 'Pengajuan Petani 1',
        ]), [
            'created_by' => $farmer1->id,
            'source' => 'farmer_submission',
            'approval_status' => 'pending',
        ]));

        $submission2 = AgricultureEvent::create(array_merge($this->eventPayload([
            'title' => 'Pengajuan Petani 2',
        ]), [
            'created_by' => $farmer2->id,
            'source' => 'farmer_submission',
            'approval_status' => 'pending',
        ]));

        $token1 = $farmer1->createToken('Farmer1 Token')->plainTextToken;

        $response = $this->withToken($token1)->getJson('/api/v1/events/my-submissions');

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $submission1->id)
            ->assertJsonPath('data.0.title', 'Pengajuan Petani 1');
    }

    public function test_unrelated_farmer_cannot_view_another_farmers_pending_or_rejected_submission(): void
    {
        $farmer1 = $this->createActor(UserRole::Farmer->value);
        $farmer2 = $this->createActor(UserRole::Farmer->value);

        $pendingSubmission = AgricultureEvent::create(array_merge($this->eventPayload([
            'title' => 'Pengajuan Rahasia Petani 1',
        ]), [
            'created_by' => $farmer1->id,
            'source' => 'farmer_submission',
            'approval_status' => 'pending',
        ]));

        $token2 = $farmer2->createToken('Farmer2 Token')->plainTextToken;

        $response = $this->withToken($token2)->getJson("/api/v1/events/{$pendingSubmission->id}");

        $response->assertNotFound();
    }

    public function test_creator_and_admin_can_view_pending_submission_detail(): void
    {
        $farmer = $this->createActor(UserRole::Farmer->value);
        $admin = $this->createActor(UserRole::Admin->value);

        $pendingSubmission = AgricultureEvent::create(array_merge($this->eventPayload([
            'title' => 'Pengajuan Menunggu Review',
        ]), [
            'created_by' => $farmer->id,
            'source' => 'farmer_submission',
            'approval_status' => 'pending',
        ]));

        $farmerToken = $farmer->createToken('Farmer Token')->plainTextToken;
        $adminToken = $admin->createToken('Admin Token')->plainTextToken;

        // Creator can view
        $this->withToken($farmerToken)
            ->getJson("/api/v1/events/{$pendingSubmission->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $pendingSubmission->id)
            ->assertJsonPath('data.approval_status', 'pending');

        // Admin can view
        $this->withToken($adminToken)
            ->getJson("/api/v1/events/{$pendingSubmission->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $pendingSubmission->id)
            ->assertJsonPath('data.approval_status', 'pending');
    }

    public function test_admin_can_approve_pending_farmer_submission(): void
    {
        $farmer = $this->createActor(UserRole::Farmer->value);
        $admin = $this->createActor(UserRole::Admin->value);

        $pendingSubmission = AgricultureEvent::create(array_merge($this->eventPayload([
            'title' => 'Pelatihan Pembuatan Kompos Organik',
        ]), [
            'created_by' => $farmer->id,
            'source' => 'farmer_submission',
            'approval_status' => 'pending',
            'status' => 'upcoming',
        ]));

        $adminToken = $admin->createToken('Admin Token')->plainTextToken;

        $response = $this->withToken($adminToken)
            ->postJson("/api/v1/admin/events/{$pendingSubmission->id}/approve");

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.approval_status', 'approved');

        $this->assertDatabaseHas('agriculture_events', [
            'id' => $pendingSubmission->id,
            'approval_status' => 'approved',
            'approved_by' => $admin->id,
            'status' => 'upcoming',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $farmer->id,
            'title' => 'Pengajuan Agenda Disetujui',
        ]);
    }

    public function test_admin_can_reject_pending_farmer_submission_with_reason(): void
    {
        $farmer = $this->createActor(UserRole::Farmer->value);
        $admin = $this->createActor(UserRole::Admin->value);

        $pendingSubmission = AgricultureEvent::create(array_merge($this->eventPayload([
            'title' => 'Acara Kurang Lengkap',
        ]), [
            'created_by' => $farmer->id,
            'source' => 'farmer_submission',
            'approval_status' => 'pending',
            'status' => 'upcoming',
        ]));

        $adminToken = $admin->createToken('Admin Token')->plainTextToken;

        $response = $this->withToken($adminToken)
            ->postJson("/api/v1/admin/events/{$pendingSubmission->id}/reject", [
                'rejection_reason' => 'Lokasi acara belum jelas dan kontak narasumber tidak aktif.',
            ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.approval_status', 'rejected')
            ->assertJsonPath('data.rejection_reason', 'Lokasi acara belum jelas dan kontak narasumber tidak aktif.');

        $this->assertDatabaseHas('agriculture_events', [
            'id' => $pendingSubmission->id,
            'approval_status' => 'rejected',
            'rejection_reason' => 'Lokasi acara belum jelas dan kontak narasumber tidak aktif.',
            'status' => 'upcoming',
        ]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $farmer->id,
            'title' => 'Pengajuan Agenda Ditolak',
        ]);
    }

    public function test_creator_can_see_rejection_reason_but_public_events_cannot_see_rejected_event(): void
    {
        $farmer = $this->createActor(UserRole::Farmer->value);
        $admin = $this->createActor(UserRole::Admin->value);

        $rejectedEvent = AgricultureEvent::create(array_merge($this->eventPayload([
            'title' => 'Acara Ditolak',
        ]), [
            'created_by' => $farmer->id,
            'source' => 'farmer_submission',
            'approval_status' => 'rejected',
            'rejection_reason' => 'Kuota tidak realistis.',
            'status' => 'upcoming',
        ]));

        $farmerToken = $farmer->createToken('Farmer Token')->plainTextToken;

        // Submitter can view rejected event with reason
        $detailResponse = $this->withToken($farmerToken)
            ->getJson("/api/v1/events/{$rejectedEvent->id}");

        $detailResponse->assertOk()
            ->assertJsonPath('data.approval_status', 'rejected')
            ->assertJsonPath('data.rejection_reason', 'Kuota tidak realistis.');

        // Rejected event is NOT in public events list
        $listResponse = $this->withToken($farmerToken)->getJson('/api/v1/events');
        $listResponse->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_farmer_can_register_only_to_approved_event(): void
    {
        $admin = $this->createActor(UserRole::Admin->value);
        $approvedEvent = AgricultureEvent::create(array_merge($this->eventPayload([
            'title' => 'Workshop Drone Pertanian',
        ]), [
            'created_by' => $admin->id,
            'source' => 'official',
            'approval_status' => 'approved',
            'status' => 'upcoming',
            'registered_count' => 0,
            'quota' => 50,
        ]));

        $farmer = $this->createActor(UserRole::Farmer->value);
        $pendingEvent = AgricultureEvent::create(array_merge($this->eventPayload([
            'title' => 'Acara Belum Disetujui',
        ]), [
            'created_by' => $farmer->id,
            'source' => 'farmer_submission',
            'approval_status' => 'pending',
            'status' => 'upcoming',
        ]));

        $farmerToken = $farmer->createToken('Farmer Token')->plainTextToken;

        // 1. Registering to approved event succeeds
        $registerResponse = $this->withToken($farmerToken)
            ->postJson("/api/v1/events/{$approvedEvent->id}/register", [
                'notes' => 'Petani Maju Jaya',
            ]);

        $registerResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('already_registered', false);

        $this->assertDatabaseHas('event_registrations', [
            'event_id' => $approvedEvent->id,
            'user_id' => $farmer->id,
        ]);
        $this->assertEquals(1, $approvedEvent->refresh()->registered_count);

        // 2. Registering to pending event fails
        $failResponse = $this->withToken($farmerToken)
            ->postJson("/api/v1/events/{$pendingEvent->id}/register");

        $failResponse->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_event_creator_cannot_register_to_own_submitted_event(): void
    {
        $farmerA = $this->createActor(UserRole::Farmer->value);
        $farmerB = $this->createActor(UserRole::Farmer->value);

        $eventX = AgricultureEvent::create(array_merge($this->eventPayload([
            'title' => 'Festival Panen Komunitas Petani',
        ]), [
            'created_by' => $farmerA->id,
            'source' => 'farmer_submission',
            'approval_status' => 'approved',
            'status' => 'upcoming',
            'registered_count' => 0,
            'quota' => 50,
        ]));

        $farmerAToken = $farmerA->createToken('FarmerA Token')->plainTextToken;
        $farmerBToken = $farmerB->createToken('FarmerB Token')->plainTextToken;

        // Farmer A (creator) CANNOT register
        $creatorResponse = $this->withToken($farmerAToken)
            ->postJson("/api/v1/events/{$eventX->id}/register");

        $creatorResponse->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Anda tidak dapat mendaftar sebagai peserta pada acara yang Anda ajukan sendiri.');

        $this->assertDatabaseMissing('event_registrations', [
            'event_id' => $eventX->id,
            'user_id' => $farmerA->id,
        ]);
        $this->assertEquals(0, $eventX->refresh()->registered_count);

        // Farmer B (other farmer) CAN register
        auth()->forgetGuards();
        $otherResponse = $this->withToken($farmerBToken)
            ->postJson("/api/v1/events/{$eventX->id}/register");

        $otherResponse->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('event_registrations', [
            'event_id' => $eventX->id,
            'user_id' => $farmerB->id,
        ]);
        $this->assertEquals(1, $eventX->refresh()->registered_count);
    }
}

