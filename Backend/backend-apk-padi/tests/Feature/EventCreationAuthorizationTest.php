<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\AgricultureEvent;
use App\Models\EventRegistration;
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

    public function test_farmer_cannot_create_event(): void
    {
        $farmer = $this->createActor(UserRole::Farmer->value);
        $token = $farmer->createToken('Farmer Token')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson('/api/v1/events', $this->eventPayload([
                'title' => 'Acara Ilegal Dibuat oleh Petani',
            ]));

        $response->assertForbidden();
        $this->assertDatabaseMissing('agriculture_events', [
            'title' => 'Acara Ilegal Dibuat oleh Petani',
        ]);
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

    public function test_extension_officer_can_create_event(): void
    {
        $officer = $this->createActor(UserRole::ExtensionOfficer->value);
        $token = $officer->createToken('Officer Token')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson('/api/v1/events', $this->eventPayload([
                'title' => 'Bimtek Resmi PPL Karawang',
            ]));

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', 'Bimtek Resmi PPL Karawang');

        $this->assertDatabaseHas('agriculture_events', [
            'title' => 'Bimtek Resmi PPL Karawang',
            'created_by' => $officer->id,
            'status' => 'upcoming',
        ]);
    }

    public function test_admin_can_create_event(): void
    {
        $admin = $this->createActor(UserRole::Admin->value);
        $token = $admin->createToken('Admin Token')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson('/api/v1/events', $this->eventPayload([
                'title' => 'Festival Panen Raya Nasional P.A.D.I.',
            ]));

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.title', 'Festival Panen Raya Nasional P.A.D.I.');

        $this->assertDatabaseHas('agriculture_events', [
            'title' => 'Festival Panen Raya Nasional P.A.D.I.',
            'created_by' => $admin->id,
        ]);
    }

    public function test_farmer_can_still_list_and_register_events(): void
    {
        $admin = $this->createActor(UserRole::Admin->value);
        $event = AgricultureEvent::create(array_merge($this->eventPayload([
            'title' => 'Workshop Teknologi Drone Pertanian',
        ]), [
            'created_by' => $admin->id,
            'status' => 'upcoming',
            'registered_count' => 0,
        ]));

        $farmer = $this->createActor(UserRole::Farmer->value);
        $token = $farmer->createToken('Farmer Token')->plainTextToken;

        // 1. Farmer can view list of events
        $listResponse = $this->withToken($token)
            ->getJson('/api/v1/events');

        $listResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data');

        // 2. Farmer can view event detail
        $detailResponse = $this->withToken($token)
            ->getJson("/api/v1/events/{$event->id}");

        $detailResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.id', $event->id);

        // 3. Farmer can register to event
        $registerResponse = $this->withToken($token)
            ->postJson("/api/v1/events/{$event->id}/register", [
                'notes' => 'Petani kelompok tani Makmur Jaya',
            ]);

        $registerResponse->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('event_registrations', [
            'event_id' => $event->id,
            'user_id' => $farmer->id,
        ]);

        $this->assertEquals(1, $event->refresh()->registered_count);
    }
}
