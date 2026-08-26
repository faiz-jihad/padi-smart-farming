<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // Buat semua role
        foreach (UserRole::cases() as $role) {
            Role::findOrCreate($role->value);
        }

        // Semua permission yang digunakan sistem
        $permissions = [
            // Pertanian / PPL
            'view_planting_recommendations',
            'manage_planting_recommendations',
            'view_agriculture_data',
            'access_internal_panel',
            'view_weather',
            'view_soil',
            'view_knowledge',
            'view_disease',
            'view_early_warning',

            // Admin only
            'view_users',
            'view_marketplace',
            'view_farmer_profiles',
            'view_broadcast',
            'view_audit_log',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // =========================
        // ADMIN
        // =========================
        $adminRole = Role::findByName(UserRole::Admin->value);

        $adminRole->syncPermissions($permissions);

        // =========================
        // EXTENSION OFFICER / PPL
        // =========================
        $extensionOfficerRole = Role::findByName(
            UserRole::ExtensionOfficer->value
        );

        $extensionOfficerRole->syncPermissions([
            'view_planting_recommendations',
            'manage_planting_recommendations',
            'view_agriculture_data',
            'access_internal_panel',
            'view_weather',
            'view_soil',
            'view_knowledge',
            'view_disease',
            'view_early_warning',
        ]);

        // =========================
        // FARMER
        // =========================
        $farmerRole = Role::findByName(UserRole::Farmer->value);

        $farmerRole->syncPermissions([
            'view_planting_recommendations',
            'view_agriculture_data',
        ]);

        // =========================
        // BUYER
        // =========================
        $buyerRole = Role::findByName(UserRole::Buyer->value);

        $buyerRole->syncPermissions([]);
        
        // Bersihkan cache permission setelah seeding
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}