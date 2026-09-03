<?php

namespace App\Services\Admin;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class AdminUserService
{
    /**
     * @return array<string, mixed>
     */
    public function indexData(string $search): array
    {
        return [
            'title' => 'Pengguna',
            'users' => $this->usersQuery($search)->latest('id')->paginate(12),
            'stats' => [
                'total' => User::query()->count(),
                'active' => User::query()->where('status', UserStatus::Active->value)->count(),
                'admins' => User::query()->where('role', UserRole::Admin->value)->count(),
                'suspended' => User::query()->where('status', UserStatus::Suspended->value)->count(),
            ],
        ];
    }

    /**
     * @param  array{name: string, email: string, phone: string, password: string, role: string, status: string, verification_status: string}  $data
     */
    public function store(
        array $data,
        Request $request,
        AdminAuditLogger $audit,
        AdminNotificationService $notifications,
    ): User {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($data, $request, $audit, $notifications) {
            $user = User::query()->create($data);
            $user->syncRoles([$this->spatieRole($data['role'])]);

            $audit->write('admin_user_created', $user, null, $this->auditValues($user), $request);
            $notifications->notifyAdmins('Pengguna dibuat', "{$user->name} ditambahkan sebagai {$user->role}.");

            return $user;
        });
    }

    /**
     * @param  array{name: string, email: string, phone: string, password?: string|null, role: string, status: string, verification_status: string}  $data
     */
    public function update(
        User $target,
        User $actor,
        array $data,
        Request $request,
        AdminAuditLogger $audit,
        AdminNotificationService $notifications,
    ): bool {
        if ($target->is($actor) && $data['status'] !== UserStatus::Active->value) {
            return false;
        }

        return \Illuminate\Support\Facades\DB::transaction(function () use ($target, $actor, $data, $request, $audit, $notifications) {
            $oldValues = $this->auditValues($target);

            if (empty($data['password'])) {
                unset($data['password']);
            }

            $target->update($data);
            $target->syncRoles([$this->spatieRole($data['role'])]);

            $audit->write(
                'admin_user_updated',
                $target,
                $oldValues,
                $this->auditValues($target),
                $request,
            );
            $notifications->notifyAdmins('Pengguna diperbarui', "{$target->name} diperbarui oleh {$actor->name}.");

            return true;
        });
    }

    public function destroy(
        User $target,
        User $actor,
        Request $request,
        AdminAuditLogger $audit,
        AdminNotificationService $notifications,
    ): bool {
        if ($target->is($actor)) {
            return false;
        }

        return \Illuminate\Support\Facades\DB::transaction(function () use ($target, $actor, $request, $audit, $notifications) {
            $oldValues = $this->auditValues($target);
            $target->delete();

            $audit->write('admin_user_deleted', User::class, $oldValues, null, $request, $target->id);
            $notifications->notifyAdmins('Pengguna dihapus', "{$oldValues['name']} dihapus oleh {$actor->name}.");

            return true;
        });
    }

    private function usersQuery(string $search): Builder
    {
        return User::query()
            ->when($search !== '', fn (Builder $query) => $query->where(function (Builder $query) use ($search): void {
                $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            }));
    }

    private function spatieRole(string $databaseRole): string
    {
        return match ($databaseRole) {
            'ppl' => UserRole::ExtensionOfficer->value,
            'partner' => UserRole::Buyer->value,
            default => $databaseRole,
        };
    }

    /**
     * @return array<string, mixed>
     */
    private function auditValues(User $user): array
    {
        return $user->only(['id', 'name', 'email', 'phone', 'role', 'status', 'verification_status']);
    }
}
