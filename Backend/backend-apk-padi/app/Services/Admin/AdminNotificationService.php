<?php

namespace App\Services\Admin;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Events\AdminNotificationCreated;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Throwable;

class AdminNotificationService
{
    /**
     * Send notification to all active administrators.
     */
    public function notifyAdmins(string $title, string $body, string $type = 'system', array $data = []): void
    {
        if (! Schema::hasTable('notifications')) {
            return;
        }

        User::query()
            ->where('role', UserRole::Admin->value)
            ->where('status', UserStatus::Active->value)
            ->get()
            ->each(function (User $admin) use ($title, $body, $type, $data): void {
                $notification = Notification::query()->create([
                    'user_id' => $admin->id,
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
