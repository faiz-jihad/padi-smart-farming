<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach (UserRole::cases() as $role) {
            Role::findOrCreate($role->value);
        }

        $permissions = [
            'view_planting_recommendations',
            'manage_planting_recommendations',
            'view_agriculture_data',
            'access_internal_panel',
        ];

        foreach ($permissions as $permission) {
            \Spatie\Permission\Models\Permission::findOrCreate($permission);
        }

        $adminRole = Role::findByName(UserRole::Admin->value);
        $adminRole->syncPermissions($permissions);

        $extensionOfficerRole = Role::findByName(UserRole::ExtensionOfficer->value);
        $extensionOfficerRole->syncPermissions([
            'view_planting_recommendations',
            'manage_planting_recommendations',
            'view_agriculture_data',
            'access_internal_panel',
        ]);

        $farmerRole = Role::findByName(UserRole::Farmer->value);
        $farmerRole->syncPermissions([
            'view_planting_recommendations',
            'view_agriculture_data',
        ]);
    }
}
