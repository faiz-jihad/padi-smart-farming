<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\AuditLog;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminBladeDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));
    }

    public function test_admin_login_page_renders(): void
    {
        $this->get(route('admin.login'))
            ->assertOk()
            ->assertSee('Admin Console')
            ->assertSee('Email admin')
            ->assertSee('css/admin/auth.css', false)
            ->assertDontSee('admin-auth__showcase')
            ->assertDontSee('Pusat Kendali Operasional');
    }

    public function test_active_admin_can_login_to_blade_dashboard(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin Demo',
            'email' => 'admin@example.test',
            'password' => Hash::make('secret-password'),
            'role' => UserRole::Admin->value,
            'status' => UserStatus::Active->value,
        ]);

        $this->from(route('admin.login'))
            ->post(route('admin.login.submit'), [
                'email' => 'admin@example.test',
                'password' => 'secret-password',
            ])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
        $this->assertNotNull($admin->refresh()->last_login_at);

        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Admin Demo');
    }

    public function test_non_admin_cannot_login_to_blade_dashboard(): void
    {
        User::factory()->create([
            'email' => 'farmer@example.test',
            'password' => Hash::make('secret-password'),
            'role' => UserRole::Farmer->value,
            'status' => UserStatus::Active->value,
        ]);

        $this->from(route('admin.login'))
            ->post(route('admin.login.submit'), [
                'email' => 'farmer@example.test',
                'password' => 'secret-password',
            ])
            ->assertRedirect(route('admin.login'));

        $this->assertGuest();
    }

    public function test_inactive_admin_cannot_access_blade_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin->value,
            'status' => UserStatus::Suspended->value,
        ]);

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('admin.login'));

        $this->assertGuest();
    }

    public function test_admin_can_logout_from_blade_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin->value,
            'status' => UserStatus::Active->value,
        ]);

        $this->actingAs($admin)
            ->post(route('admin.logout'))
            ->assertRedirect(route('admin.login'));

        $this->assertGuest();
    }

    public function test_admin_dashboard_renders_dynamic_metrics_and_notifications(): void
    {
        $user = User::factory()->create([
            'name' => 'Admin Demo',
            'role' => UserRole::Admin->value,
            'status' => UserStatus::Active->value,
        ]);

        Notification::query()->create([
            'user_id' => $user->id,
            'type' => 'system',
            'title' => 'Broadcast perlu ditinjau',
            'body' => 'Ada draft broadcast yang belum diterbitkan.',
        ]);

        AuditLog::query()->create([
            'user_id' => $user->id,
            'action' => 'admin_user_updated',
            'entity_type' => User::class,
            'entity_id' => $user->id,
            'ip_address' => '127.0.0.1',
        ]);

        $this->actingAs($user)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('Pantau kondisi operasional dan aktivitas terbaru P.A.D.I.')
            ->assertDontSee('Dashboard Admin P.A.D.I.')
            ->assertSee('Admin Demo')
            ->assertSee('Total Pengguna')
            ->assertSee('Broadcast perlu ditinjau')
            ->assertSee('Data pengguna diperbarui');
    }

    public function test_admin_can_mark_notifications_as_read_from_blade(): void
    {
        $user = User::factory()->create([
            'role' => UserRole::Admin->value,
            'status' => UserStatus::Active->value,
        ]);

        $notification = Notification::query()->create([
            'user_id' => $user->id,
            'type' => 'system',
            'title' => 'Notifikasi baru',
            'body' => 'Perlu dibaca admin.',
        ]);

        $this->actingAs($user)
            ->post(route('admin.notifications.read'))
            ->assertRedirect();

        $this->assertNotNull($notification->refresh()->read_at);
    }
}
