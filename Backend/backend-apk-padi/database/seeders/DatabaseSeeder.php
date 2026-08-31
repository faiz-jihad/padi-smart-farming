<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        $user = User::updateOrCreate(
            ['phone' => '081234567890'],
            [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => Hash::make('password'),
                'role' => UserRole::Farmer->value,
                'status' => UserStatus::Active->value,
            ]
        );

        $user->assignRole(UserRole::Farmer->value);

        $admin = User::updateOrCreate(
            ['phone' => '081234567891'],
            [
                'name' => 'Admin P.A.D.I.',
                'email' => 'admin@padi.test',
                'password' => Hash::make('password'),
                'role' => UserRole::Admin->value,
                'status' => UserStatus::Active->value,
            ]
        );

        $admin->assignRole(UserRole::Admin->value);

        $this->call(CropSeasonSeeder::class);
        $this->call(FertilizerRuleSeeder::class);
        $this->call(MarketListingSeeder::class);
        $this->call(EventSeeder::class);
    }
}