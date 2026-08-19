<?php

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExtensionOfficerAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    private function extensionOfficer(): User
    {
        $user = User::factory()->create([
            'role' => UserRole::ExtensionOfficer->value,
            'status' => UserStatus::Active->value,
        ]);

        $user->assignRole(UserRole::ExtensionOfficer->value);

        return $user;
    }

    private function admin(): User
    {
        $user = User::factory()->create([
            'role' => UserRole::Admin->value,
            'status' => UserStatus::Active->value,
        ]);

        $user->assignRole(UserRole::Admin->value);

        return $user;
    }

    private function farmer(): User
    {
        $user = User::factory()->create([
            'role' => UserRole::Farmer->value,
            'status' => UserStatus::Active->value,
        ]);

        $user->assignRole(UserRole::Farmer->value);

        return $user;
    }

    public function test_extension_officer_can_access_internal_panel(): void
    {
        $ppl = $this->extensionOfficer();

        $this->actingAs($ppl)
            ->get(route('admin.agriculture.index'))
            ->assertOk();
    }

    public function test_extension_officer_cannot_access_admin_users(): void
    {
        $ppl = $this->extensionOfficer();

        $this->actingAs($ppl)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_extension_officer_cannot_access_admin_broadcast(): void
    {
        $ppl = $this->extensionOfficer();

        $this->actingAs($ppl)
            ->get(route('admin.broadcast.index'))
            ->assertForbidden();
    }

    public function test_farmer_cannot_access_internal_agriculture_panel(): void
    {
        $farmer = $this->farmer();

        $response = $this->actingAs($farmer)
            ->get(route('admin.agriculture.index'));

        $this->assertEquals(403, $response->status());
    }

    public function test_admin_can_access_admin_users(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk();
    }
}