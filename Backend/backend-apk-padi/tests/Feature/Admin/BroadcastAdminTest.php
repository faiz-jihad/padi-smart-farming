<?php

namespace Tests\Feature\Admin;

use App\Models\AdminBroadcast;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BroadcastAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    public function test_admin_can_create_broadcast_and_dispatch_user_notifications(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        $admin->assignRole('admin');
        $farmer = User::factory()->create(['role' => 'farmer', 'status' => 'active']);
        $partner = User::factory()->create(['role' => 'partner', 'status' => 'active']);

        $response = $this->actingAs($admin)->post('/admin/broadcast', [
            'title' => 'Peringatan Hama Wereng Karawang',
            'message' => 'Segera lakukan penyemprotan agens hayati pada lahan padi MT2.',
            'type' => 'warning',
            'target_role' => 'farmer',
            'status' => 'published',
        ]);

        $response->assertRedirect();
        $broadcast = AdminBroadcast::where('title', 'Peringatan Hama Wereng Karawang')->first();
        $this->assertNotNull($broadcast);
        $this->assertEquals('farmer', $broadcast->target_role);

        // Check Notification created for farmer but NOT partner
        $this->assertDatabaseHas('notifications', [
            'user_id' => $farmer->id,
            'title' => 'Peringatan Hama Wereng Karawang',
        ]);

        $this->assertDatabaseMissing('notifications', [
            'user_id' => $partner->id,
            'title' => 'Peringatan Hama Wereng Karawang',
        ]);
    }
}
