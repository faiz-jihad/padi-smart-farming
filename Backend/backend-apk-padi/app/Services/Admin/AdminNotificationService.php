<?php

namespace App\Services\Admin;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Events\AdminNotificationCreated;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Schema;

class AdminNotificationService
{
    public function notifyAdmins(string $title, string $body, string $type = 'system'): void
    {
        if (! Schema::hasTable('notifications')) {
            return;
        }

        User::query()
            ->where('role', UserRole::Admin->value)
            ->where('status', UserStatus::Active->value)
            ->get()
            ->each(function (User $admin) use ($title, $body, $type): void {
                $notification = Notification::query()->create([
                    'user_id' => $admin->id,
                    'type' => $type,
                    'title' => $title,
                    'body' => $body,
                ]);

                event(new AdminNotificationCreated($notification));
            });
    }
}
