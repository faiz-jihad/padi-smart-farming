<?php

namespace App\Services\Admin;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Events\AdminNotificationCreated;
use App\Models\DeviceToken;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Throwable;

class AdminNotificationService
{
    /**
     * Send notification to a specific role with its dedicated rights & context.
     */
    public function notifyRole(UserRole|string $role, string $title, string $body, string $type = 'system', array $data = []): int
    {
        if (! Schema::hasTable('notifications')) {
            return 0;
        }

        $roleValue = $role instanceof UserRole ? $role->value : $role;

        $users = User::query()
            ->where('role', $roleValue)
            ->where('status', UserStatus::Active->value)
            ->get();

        $count = 0;
        foreach ($users as $user) {
            $notification = Notification::query()->create([
                'user_id' => $user->id,
                'type'    => $type,
                'title'   => $title,
                'body'    => $body,
                'data'    => array_merge($data, ['role_target' => $roleValue]),
            ]);

            $count++;

            try {
                event(new AdminNotificationCreated($notification));
            } catch (Throwable $e) {
                report($e);
            }
        }

        return $count;
    }

    /**
     * Send notification to all active Farmers (Petani).
     * Context: Cultivation Schedule, Early Warnings, Pest Radars, Crop Marketplace.
     */
    public function notifyFarmers(string $title, string $body, string $type = 'crop_alert', array $data = []): int
    {
        return $this->notifyRole(UserRole::Farmer, $title, $body, $type, array_merge([
            'category' => 'farmer_alert',
            'icon'     => '/images/icons/plant.png',
        ], $data));
    }

    /**
     * Send notification to all active Extension Officers (PPL / Penyuluh).
     * Context: Field validations, Farmer reports, Disease outbreaks.
     */
    public function notifyExtensionOfficers(string $title, string $body, string $type = 'ppl_validation', array $data = []): int
    {
        return $this->notifyRole(UserRole::ExtensionOfficer, $title, $body, $type, array_merge([
            'category' => 'ppl_verification',
            'icon'     => '/images/icons/badge-check.png',
        ], $data));
    }

    /**
     * Send notification to all active Buyers / Partners (Pembeli Gabah).
     * Context: Harvest listing offers, grain marketplace, price updates.
     */
    public function notifyBuyers(string $title, string $body, string $type = 'marketplace_deal', array $data = []): int
    {
        return $this->notifyRole(UserRole::Buyer, $title, $body, $type, array_merge([
            'category' => 'marketplace',
            'icon'     => '/images/icons/cart.png',
        ], $data));
    }

    /**
     * Send notification to all active administrators.
     * Context: System audits, User registrations, Platform operations.
     */
    public function notifyAdmins(string $title, string $body, string $type = 'system', array $data = []): void
    {
        $this->notifyRole(UserRole::Admin, $title, $body, $type, array_merge([
            'category' => 'admin_audit',
        ], $data));
    }

    /**
     * Send notification to a specific user (Farmer, Partner, PPL, or Admin).
     */
    public function notifyUser(User|int $user, string $title, string $body, string $type = 'system', array $data = []): ?Notification
    {
        if (! Schema::hasTable('notifications')) {
            return null;
        }

        $userId = $user instanceof User ? $user->id : $user;

        $notification = Notification::query()->create([
            'user_id' => $userId,
            'type'    => $type,
            'title'   => $title,
            'body'    => $body,
            'data'    => $data,
        ]);

        try {
            event(new AdminNotificationCreated($notification));
        } catch (Throwable $e) {
            report($e);
        }

        return $notification;
    }

    /**
     * Broadcast notification to all active users across the platform.
     */
    public function notifyAll(string $title, string $body, string $type = 'broadcast', array $data = []): void
    {
        if (! Schema::hasTable('notifications')) {
            return;
        }

        User::query()
            ->where('status', UserStatus::Active->value)
            ->get()
            ->each(function (User $user) use ($title, $body, $type, $data): void {
                $notification = Notification::query()->create([
                    'user_id' => $user->id,
                    'type'    => $type,
                    'title'   => $title,
                    'body'    => $body,
                    'data'    => $data,
                ]);

                try {
                    event(new AdminNotificationCreated($notification));
                } catch (Throwable $e) {
                    report($e);
                }
            });
    }
}
