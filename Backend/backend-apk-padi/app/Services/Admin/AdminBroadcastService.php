<?php

namespace App\Services\Admin;

use App\Models\AdminBroadcast;
use Illuminate\Http\Request;

class AdminBroadcastService
{
    /**
     * @return array<string, mixed>
     */
    public function indexData(): array
    {
        return [
            'title' => 'Broadcast',
            'broadcasts' => AdminBroadcast::query()->with('admin')->latest('id')->paginate(10),
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
        $data = $this->prepareData($data);
        $data['admin_id'] = $adminId;

        $broadcast = AdminBroadcast::query()->create($data);

        $audit->write('admin_broadcast_created', $broadcast, null, $broadcast->toArray(), $request);
        $notifications->notifyAdmins('Broadcast dibuat', $broadcast->title, $broadcast->type);

        return $broadcast;
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
        $oldValues = $broadcast->toArray();
        $broadcast->update($this->prepareData($data, $broadcast));

        $audit->write('admin_broadcast_updated', $broadcast, $oldValues, $broadcast->toArray(), $request);
        $notifications->notifyAdmins('Broadcast diperbarui', $broadcast->title, $broadcast->type);
    }

    public function destroy(
        AdminBroadcast $broadcast,
        Request $request,
        AdminAuditLogger $audit,
        AdminNotificationService $notifications,
    ): void {
        $oldValues = $broadcast->toArray();
        $broadcast->delete();

        $audit->write('admin_broadcast_deleted', AdminBroadcast::class, $oldValues, null, $request, $broadcast->id);
        $notifications->notifyAdmins('Broadcast dihapus', $oldValues['title'] ?? 'Broadcast dihapus.');
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function prepareData(array $data, ?AdminBroadcast $broadcast = null): array
    {
        if ($data['status'] === 'published' && $broadcast?->published_at === null) {
            $data['published_at'] = now();
        }

        return $data;
    }
}
