<?php

namespace App\Services\Admin;

use App\Models\AdminBroadcast;
use App\Models\AuditLog;
use App\Models\CommunityReport;
use App\Models\Farm;
use App\Models\Harvest;
use App\Models\MarketListing;
use App\Models\MarketOffer;
use App\Models\Notification;
use App\Models\PurchaseContract;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class AdminDashboardService
{
    /**
     * @return array<string, mixed>
     */
    public function viewData(?int $adminId): array
    {
        return [
            'title' => 'Dashboard',
            'metrics' => $this->metrics(),
            'recentActivities' => $this->recentActivities(),
            'systemNotifications' => $this->systemNotifications($adminId),
            'marketplaceStats' => $this->marketplaceStats(),
            'userStats' => $this->userStats(),
        ];
    }

    public function markNotificationsRead(?int $adminId): bool
    {
        if (! $adminId || ! Schema::hasTable('notifications')) {
            return false;
        }

        Notification::query()
            ->where('user_id', $adminId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return true;
    }

    /**
     * @return list<array{label: string, value: int, helper: string, tone: string, icon: string}>
     */
    private function metrics(): array
    {
        return [
            [
                'label' => 'Total Pengguna',
                'value' => $this->count(User::class, 'users'),
                'helper' => 'Akun terdaftar di sistem',
                'tone' => 'green',
                'icon' => 'users',
            ],
            [
                'label' => 'Lahan Terdaftar',
                'value' => $this->count(Farm::class, 'farms'),
                'helper' => 'Lahan petani yang tercatat',
                'tone' => 'green',
                'icon' => 'farm',
            ],
            [
                'label' => 'Laporan Komunitas',
                'value' => $this->count(CommunityReport::class, 'community_reports'),
                'helper' => 'Laporan yang masuk dari pengguna',
                'tone' => 'green',
                'icon' => 'warning',
            ],
            [
                'label' => 'Marketplace',
                'value' => $this->count(MarketListing::class, 'market_listings'),
                'helper' => 'Listing hasil panen',
                'tone' => 'green',
                'icon' => 'market',
            ],
        ];
    }

    /**
     * @return list<array{title: string, description: string, time: string, tone: string, icon: string}>
     */
    private function recentActivities(): array
    {
        if (! Schema::hasTable('audit_logs')) {
            return [];
        }

        return AuditLog::query()
            ->with('user')
            ->latest('id')
            ->limit(6)
            ->get()
            ->map(fn (AuditLog $log): array => [
                'title' => $this->activityTitle($log->action),
                'description' => ($log->user?->name ?? 'Sistem').' menjalankan aksi pada '.class_basename((string) $log->entity_type),
                'time' => $log->created_at?->diffForHumans() ?? '-',
                'tone' => 'green',
                'icon' => str_contains($log->action, 'broadcast') ? 'broadcast' : 'audit',
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{title: string, body: string, time: string, type: string}>
     */
    private function systemNotifications(?int $adminId): array
    {
        if (! $adminId || ! Schema::hasTable('notifications')) {
            return [];
        }

        return Notification::query()
            ->where('user_id', $adminId)
            ->latest('id')
            ->limit(5)
            ->get()
            ->map(fn (Notification $notification): array => [
                'title' => $notification->title,
                'body' => $notification->body,
                'time' => $notification->created_at?->diffForHumans() ?? '-',
                'type' => $notification->type,
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, int>
     */
    private function marketplaceStats(): array
    {
        return [
            'active_listings' => $this->countWhere(MarketListing::class, 'market_listings', 'status', 'published'),
            'offers' => $this->count(MarketOffer::class, 'market_offers'),
            'contracts' => $this->countWhere(PurchaseContract::class, 'purchase_contracts', 'status', 'active'),
            'pending_moderation' => $this->countWhere(MarketListing::class, 'market_listings', 'status', 'draft'),
        ];
    }

    /**
     * @return array<string, int>
     */
    private function userStats(): array
    {
        return [
            'active' => $this->countWhere(User::class, 'users', 'status', 'active'),
            'inactive' => $this->countWhere(User::class, 'users', 'status', 'inactive'),
            'suspended' => $this->countWhere(User::class, 'users', 'status', 'suspended'),
            'broadcasts' => $this->count(AdminBroadcast::class, 'admin_broadcasts'),
            'harvests' => $this->count(Harvest::class, 'harvests'),
        ];
    }

    private function activityTitle(string $action): string
    {
        return match ($action) {
            'admin_user_updated' => 'Data pengguna diperbarui',
            'admin_broadcast_created' => 'Broadcast baru dibuat',
            'admin_broadcast_updated' => 'Broadcast diperbarui',
            'admin_broadcast_deleted' => 'Broadcast dihapus',
            default => str_replace('_', ' ', $action),
        };
    }

    /**
     * @param  class-string<Model>  $model
     */
    private function count(string $model, string $table): int
    {
        if (! Schema::hasTable($table)) {
            return 0;
        }

        return $model::query()->count();
    }

    /**
     * @param  class-string<Model>  $model
     */
    private function countWhere(string $model, string $table, string $column, string $value): int
    {
        if (! Schema::hasTable($table)) {
            return 0;
        }

        return $model::query()->where($column, $value)->count();
    }
}
