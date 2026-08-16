<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\AdminBroadcast;
use App\Models\AuditLog;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOverviewApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_admin_overview_requires_admin_role(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Farmer->value,
        ]);
        $user->assignRole(UserRole::Farmer->value);

        $this->withToken($user->createToken('Feature Test')->plainTextToken)
            ->getJson('/api/v1/admin')
            ->assertForbidden();
    }

    public function test_admin_overview_returns_connected_admin_data(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin->value,
        ]);
        $admin->assignRole(UserRole::Admin->value);

        $farmer = User::factory()->create([
            'role' => UserRole::Farmer->value,
        ]);
        $farmer->assignRole(UserRole::Farmer->value);

        AdminBroadcast::query()->create([
            'admin_id' => $admin->id,
            'title' => 'Peringatan cuaca',
            'message' => 'Curah hujan tinggi dalam 24 jam ke depan.',
            'type' => 'warning',
            'status' => 'published',
            'published_at' => now(),
        ]);

        AuditLog::query()->create([
            'user_id' => $admin->id,
            'action' => 'published_broadcast',
            'entity_type' => AdminBroadcast::class,
            'entity_id' => 1,
            'ip_address' => '127.0.0.1',
        ]);

        $this->withToken($admin->createToken('Feature Test')->plainTextToken)
            ->getJson('/api/v1/admin')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.summary.users_total', 2)
            ->assertJsonPath('data.summary.farmers_total', 1)
            ->assertJsonPath('data.summary.broadcasts_total', 1)
            ->assertJsonPath('data.summary.audit_logs_total', 1)
            ->assertJsonPath('data.broadcasts.0.title', 'Peringatan cuaca')
            ->assertJsonPath('data.audit_logs.0.action', 'published_broadcast');
    }

    public function test_admin_can_list_and_update_users(): void
    {
        $admin = $this->adminUser();
        $target = User::factory()->create([
            'role' => UserRole::Farmer->value,
            'status' => 'active',
        ]);
        $target->assignRole(UserRole::Farmer->value);

        $this->withToken($admin->createToken('Feature Test')->plainTextToken)
            ->getJson('/api/v1/admin/users')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonFragment(['email' => $target->email]);

        $this->withToken($admin->createToken('Feature Test 2')->plainTextToken)
            ->patchJson("/api/v1/admin/users/{$target->id}", [
                'role' => UserRole::Buyer->value,
                'status' => 'suspended',
            ])
            ->assertOk()
            ->assertJsonPath('data.user.role', UserRole::Buyer->value)
            ->assertJsonPath('data.user.status', 'suspended');

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'role' => 'partner',
            'status' => 'suspended',
        ]);
        $this->assertTrue($target->refresh()->hasRole(UserRole::Buyer->value));
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $admin->id,
            'action' => 'admin_user_updated',
            'entity_type' => User::class,
            'entity_id' => $target->id,
        ]);
    }

    public function test_admin_cannot_disable_own_account(): void
    {
        $admin = $this->adminUser();

        $this->withToken($admin->createToken('Feature Test')->plainTextToken)
            ->patchJson("/api/v1/admin/users/{$admin->id}", [
                'status' => 'suspended',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('success', false);
    }

    public function test_admin_can_manage_broadcasts_and_read_audit_logs(): void
    {
        $admin = $this->adminUser();
        $token = $admin->createToken('Feature Test')->plainTextToken;

        $createResponse = $this->withToken($token)
            ->postJson('/api/v1/admin/broadcasts', [
                'title' => 'Pengumuman panen',
                'message' => 'Harga gabah terbaru sudah diperbarui.',
                'type' => 'announcement',
                'status' => 'draft',
            ])
            ->assertCreated()
            ->assertJsonPath('data.broadcast.title', 'Pengumuman panen')
            ->assertJsonPath('data.broadcast.status', 'draft');

        $broadcastId = $createResponse->json('data.broadcast.id');

        $this->withToken($token)
            ->patchJson("/api/v1/admin/broadcasts/{$broadcastId}", [
                'status' => 'published',
            ])
            ->assertOk()
            ->assertJsonPath('data.broadcast.status', 'published');

        $this->assertNotNull(AdminBroadcast::query()->find($broadcastId)?->published_at);

        $this->withToken($token)
            ->getJson('/api/v1/admin/broadcasts')
            ->assertOk()
            ->assertJsonFragment(['title' => 'Pengumuman panen']);

        $this->withToken($token)
            ->deleteJson("/api/v1/admin/broadcasts/{$broadcastId}")
            ->assertOk();

        $this->assertDatabaseMissing('admin_broadcasts', [
            'id' => $broadcastId,
        ]);

        $this->withToken($token)
            ->getJson('/api/v1/admin/audit-logs')
            ->assertOk()
            ->assertJsonFragment(['action' => 'admin_broadcast_created'])
            ->assertJsonFragment(['action' => 'admin_broadcast_updated'])
            ->assertJsonFragment(['action' => 'admin_broadcast_deleted']);
    }

    private function adminUser(): User
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin->value,
        ]);
        $admin->assignRole(UserRole::Admin->value);

        return $admin;
    }
}
