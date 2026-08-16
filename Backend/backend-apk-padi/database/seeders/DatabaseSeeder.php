<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '081234567890',
            'role' => UserRole::Farmer->value,
            'status' => UserStatus::Active->value,
        ]);

        $user->assignRole(UserRole::Farmer->value);

        $admin = User::factory()->create([
            'name' => 'Admin P.A.D.I.',
            'email' => 'admin@padi.test',
            'phone' => '081234567891',
            'role' => UserRole::Admin->value,
            'status' => UserStatus::Active->value,
        ]);

        $admin->assignRole(UserRole::Admin->value);
    }
}
