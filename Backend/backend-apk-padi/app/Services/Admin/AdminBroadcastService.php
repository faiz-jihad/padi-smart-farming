<?php

namespace App\Services\Admin;

use App\Models\AdminBroadcast;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class AdminBroadcastService
{
    /**
     * @return array<string, mixed>
     */
    public function indexData(Request $request): array
    {
        $type = $request->query('type');
        $status = $request->query('status');
        $search = $request->query('search');

        $query = AdminBroadcast::query()->with('admin');

        if ($type) {
            $query->where('type', $type);
        }

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%");
            });
        }

        $broadcasts = $query->latest('id')->paginate(10);

        return [
            'title' => 'Broadcast',
            'broadcasts' => $broadcasts,
            'filters' => [
                'type' => $type,
                'status' => $status,
                'search' => $search,
            ],
            'stats' => [
                'total' => AdminBroadcast::query()->count(),
                'published' => AdminBroadcast::query()->where('status', 'published')->count(),
                'draft' => AdminBroadcast::query()->where('status', 'draft')->count(),
                'expired' => AdminBroadcast::query()->where('status', 'expired')->count(),
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function store(
        int $adminId,
        array $data,
        Request $request,
        AdminAuditLogger $audit,
        AdminNotificationService $notifications,
    ): AdminBroadcast {
        return \Illuminate\Support\Facades\DB::transaction(function () use ($adminId, $data, $request, $audit, $notifications) {
            $preparedData = $this->prepareData($data);
            $preparedData['admin_id'] = $adminId;

            $broadcast = AdminBroadcast::query()->create($preparedData);

            $this->dispatchUserNotifications($broadcast);

            $audit->write('admin_broadcast_created', $broadcast, null, $broadcast->toArray(), $request);
            $notifications->notifyAdmins('Broadcast dibuat', $broadcast->title, $broadcast->type);

            return $broadcast;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(
        AdminBroadcast $broadcast,
        array $data,
        Request $request,
        AdminAuditLogger $audit,
        AdminNotificationService $notifications,
    ): void {
        \Illuminate\Support\Facades\DB::transaction(function () use ($broadcast, $data, $request, $audit, $notifications) {
            $oldValues = $broadcast->toArray();
            $broadcast->update($this->prepareData($data, $broadcast));

            if ($broadcast->status === 'published' && ($oldValues['status'] ?? '') !== 'published') {
                $this->dispatchUserNotifications($broadcast);
            }

            $audit->write('admin_broadcast_updated', $broadcast, $oldValues, $broadcast->toArray(), $request);
            $notifications->notifyAdmins('Broadcast diperbarui', $broadcast->title, $broadcast->type);
        });
    }

    public function destroy(
        AdminBroadcast $broadcast,
        Request $request,
        AdminAuditLogger $audit,
        AdminNotificationService $notifications,
    ): void {
        \Illuminate\Support\Facades\DB::transaction(function () use ($broadcast, $request, $audit, $notifications) {
            $oldValues = $broadcast->toArray();
            $broadcastId = $broadcast->id;
            $broadcast->delete();

            $audit->write('admin_broadcast_deleted', AdminBroadcast::class, $oldValues, null, $request, $broadcastId);
            $notifications->notifyAdmins('Broadcast dihapus', $oldValues['title'] ?? 'Broadcast dihapus.');
        });
    }


    private function dispatchUserNotifications(AdminBroadcast $broadcast): void
    {
        if ($broadcast->status !== 'published') {
            return;
        }

        $usersQuery = User::query();

        if (($broadcast->target_role ?? 'all') === 'farmer') {
            $usersQuery->where('role', 'farmer');
        } elseif (($broadcast->target_role ?? 'all') === 'partner') {
            $usersQuery->where('role', 'partner');
        }

        $usersQuery->chunk(100, function ($users) use ($broadcast) {
            foreach ($users as $user) {
                Notification::create([
                    'user_id' => $user->id,
                    'type' => 'broadcast_' . $broadcast->type,
                    'title' => $broadcast->title,
                    'body' => $broadcast->message,
                    'data_json' => [
                        'broadcast_id' => $broadcast->id,
                        'type' => $broadcast->type,
                        'target_role' => $broadcast->target_role ?? 'all',
                    ],
                ]);
            }
        });
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function prepareData(array $data, ?AdminBroadcast $broadcast = null): array
    {
        if ($data['status'] === 'published' && ($broadcast?->published_at === null)) {
            $data['published_at'] = now();
        }

        return $data;
    }
}
