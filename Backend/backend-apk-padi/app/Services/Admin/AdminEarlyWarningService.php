<?php

namespace App\Services\Admin;

use App\Models\AdminBroadcast;
use App\Models\AlertSubscription;
use App\Models\CommunityReport;
use App\Models\Notification;
use Illuminate\Http\Request;

class AdminEarlyWarningService
{
    /**
     * @return array<string, mixed>
     */
    public function indexData(): array
    {
        return [
            'title' => 'Early Warning',
            'subscriptions' => AlertSubscription::query()->with(['farmer', 'farm'])->latest('id')->paginate(10),
            'warnings' => Notification::query()
                ->whereIn('type', ['warning', 'early_warning'])
                ->with('user')
                ->latest('id')
                ->limit(10)
                ->get(),
            'stats' => [
                'subscriptions' => AlertSubscription::query()->count(),
                'active_subscriptions' => AlertSubscription::query()->where('is_active', true)->count(),
                'community_reports' => CommunityReport::query()->where('status', 'pending')->count(),
                'warnings' => Notification::query()->whereIn('type', ['warning', 'early_warning'])->count(),
            ],
        ];
    }

    /**
     * @param  array{title: string, body: string}  $data
     */
    public function store(
        int $adminId,
        array $data,
        Request $request,
        AdminAuditLogger $audit,
        AdminNotificationService $notifications,
    ): void {
        AdminBroadcast::query()->create([
            'admin_id' => $adminId,
            'title' => $data['title'],
            'message' => $data['body'],
            'type' => 'warning',
            'status' => 'published',
            'published_at' => now(),
        ]);

        $notifications->notifyAdmins($data['title'], $data['body'], 'early_warning');
        $audit->write('admin_warning_created', AdminBroadcast::class, null, $data, $request);
    }
}
