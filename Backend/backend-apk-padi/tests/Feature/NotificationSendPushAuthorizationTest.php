<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Notification;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationSendPushAuthorizationTest extends TestCase
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

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Peringatan Hama Wereng Batang Cokelat',
            'body' => 'Ditemukan populasi wereng cokelat di atas ambang batas ekonomi pada beberapa petak sawah.',
            'target_role' => UserRole::Farmer->value,
            'type' => 'warning',
        ], $overrides);
    }

    public function test_unauthenticated_user_cannot_send_push_notification(): void
    {
        $response = $this->postJson('/api/v1/notifications/send-push', $this->payload());

        $response->assertUnauthorized();
        $this->assertDatabaseEmpty('notifications');
    }

    public function test_farmer_cannot_send_push_notification(): void
    {
        $farmer = $this->createActor(UserRole::Farmer->value);
        $token = $farmer->createToken('Farmer Token')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson('/api/v1/notifications/send-push', $this->payload([
                'target_role' => 'all',
                'title' => 'Pesan Tidak Berwenang dari Petani',
            ]));

        $response->assertForbidden();
        $this->assertDatabaseMissing('notifications', [
            'title' => 'Pesan Tidak Berwenang dari Petani',
        ]);
        $this->assertDatabaseEmpty('notifications');
    }

    public function test_buyer_cannot_send_push_notification(): void
    {
        $buyer = $this->createActor(UserRole::Buyer->value);
        $token = $buyer->createToken('Buyer Token')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson('/api/v1/notifications/send-push', $this->payload([
                'target_role' => 'all',
                'title' => 'Pesan Promo Tidak Berwenang dari Pembeli',
            ]));

        $response->assertForbidden();
        $this->assertDatabaseMissing('notifications', [
            'title' => 'Pesan Promo Tidak Berwenang dari Pembeli',
        ]);
        $this->assertDatabaseEmpty('notifications');
    }

    public function test_extension_officer_can_send_push_notification(): void
    {
        $officer = $this->createActor(UserRole::ExtensionOfficer->value);
        $farmer = $this->createActor(UserRole::Farmer->value);
        $token = $officer->createToken('Officer Token')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson('/api/v1/notifications/send-push', $this->payload([
                'target_role' => UserRole::Farmer->value,
                'title' => 'Instruksi Pengendalian Hama PPL',
            ]));

        $response->assertCreated()
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('notifications', [
            'user_id' => $farmer->id,
            'title' => 'Instruksi Pengendalian Hama PPL',
        ]);
    }

    public function test_admin_can_send_push_notification(): void
    {
        $admin = $this->createActor(UserRole::Admin->value);
        $farmer = $this->createActor(UserRole::Farmer->value);
        $token = $admin->createToken('Admin Token')->plainTextToken;

        $response = $this->withToken($token)
            ->postJson('/api/v1/notifications/send-push', $this->payload([
                'target_role' => 'all',
                'title' => 'Pengumuman Penting Administrator Sistem',
            ]));

        $response->assertCreated()
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseHas('notifications', [
            'title' => 'Pengumuman Penting Administrator Sistem',
        ]);
    }
}
