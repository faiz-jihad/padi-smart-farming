<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\IrrigationType;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IrrigationTypeAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_admin_can_store_new_irrigation_type(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin->value,
            'status' => UserStatus::Active->value,
        ]);
        $admin->assignRole(UserRole::Admin->value);

        $response = $this->actingAs($admin)
            ->postJson(route('admin.agriculture.irrigation-types.store'), [
                'name' => 'Irigasi Tetes Modern',
                'code' => 'drip_modern',
                'description' => 'Sistem fertigasi dan irigasi tetes efisiensi tinggi',
            ]);

        $response->assertStatus(201);
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('irrigation_types', [
            'name' => 'Irigasi Tetes Modern',
            'code' => 'drip_modern',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_toggle_irrigation_type_status(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin->value,
            'status' => UserStatus::Active->value,
        ]);
        $admin->assignRole(UserRole::Admin->value);

        $type = IrrigationType::create([
            'name' => 'Irigasi Uji Coba',
            'code' => 'uji_coba',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)
            ->postJson(route('admin.agriculture.irrigation-types.toggle-status', $type));

        $response->assertStatus(200);
        $this->assertDatabaseHas('irrigation_types', [
            'id' => $type->id,
            'is_active' => false,
        ]);
    }
}
