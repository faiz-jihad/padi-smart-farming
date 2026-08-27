<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\Notification;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationReadAuthorizationTest extends TestCase
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

    public function test_unauthenticated_cannot_mark_notification_as_read(): void
    {
        $owner = $this->createActor(UserRole::Farmer->value);
        $notification = Notification::query()->create([
            'user_id' => $owner->id,
            'type' => 'crop_alert',
            'title' => 'Peringatan Hama',
            'body' => 'Hama wereng terdeteksi',
        ]);

        $response = $this->patchJson("/api/v1/notifications/{$notification->id}/read");

        $response->assertUnauthorized();
        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_owner_can_mark_notification_as_read(): void
    {
        $owner = $this->createActor(UserRole::Farmer->value);
        $notification = Notification::query()->create([
            'user_id' => $owner->id,
            'type' => 'crop_alert',
            'title' => 'Peringatan Hama',
            'body' => 'Hama wereng terdeteksi',
        ]);

        $token = $owner->createToken('Owner Token')->plainTextToken;

        $response = $this->withToken($token)
            ->patchJson("/api/v1/notifications/{$notification->id}/read");

        $response->assertOk()
            ->assertJsonPath('status', 'success');

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_another_user_cannot_mark_notification_as_read(): void
    {
        $owner = $this->createActor(UserRole::Farmer->value);
        $attacker = $this->createActor(UserRole::Farmer->value);

        $notification = Notification::query()->create([
            'user_id' => $owner->id,
            'type' => 'crop_alert',
            'title' => 'Peringatan Hama Rahasia',
            'body' => 'Hama wereng terdeteksi',
        ]);

        $tokenAttacker = $attacker->createToken('Attacker Token')->plainTextToken;

        $response = $this->withToken($tokenAttacker)
            ->patchJson("/api/v1/notifications/{$notification->id}/read");

        $response->assertForbidden();
        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_buyer_cannot_mark_farmer_notification_as_read(): void
    {
        $owner = $this->createActor(UserRole::Farmer->value);
        $buyer = $this->createActor(UserRole::Buyer->value);

        $notification = Notification::query()->create([
            'user_id' => $owner->id,
            'type' => 'crop_alert',
            'title' => 'Peringatan Hama',
            'body' => 'Hama wereng terdeteksi',
        ]);

        $tokenBuyer = $buyer->createToken('Buyer Token')->plainTextToken;

        $response = $this->withToken($tokenBuyer)
            ->patchJson("/api/v1/notifications/{$notification->id}/read");

        $response->assertForbidden();
        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_admin_can_mark_user_notification_as_read(): void
    {
        $owner = $this->createActor(UserRole::Farmer->value);
        $admin = $this->createActor(UserRole::Admin->value);

        $notification = Notification::query()->create([
            'user_id' => $owner->id,
            'type' => 'system',
            'title' => 'System Update',
            'body' => 'Maintenance info',
        ]);

        $tokenAdmin = $admin->createToken('Admin Token')->plainTextToken;

        $response = $this->withToken($tokenAdmin)
            ->patchJson("/api/v1/notifications/{$notification->id}/read");

        $response->assertOk()
            ->assertJsonPath('status', 'success');

        $this->assertNotNull($notification->fresh()->read_at);
    }
}
