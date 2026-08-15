<?php

namespace App\Services\Admin;

use App\Models\AuditLog;

class AdminAuditLogService
{
    /**
     * @return array<string, mixed>
     */
    public function indexData(): array
    {
        return [
            'title' => 'Audit Log',
            'logs' => AuditLog::query()->with('user')->latest('id')->paginate(15),
            'stats' => [
                'total' => AuditLog::query()->count(),
                'today' => AuditLog::query()->whereDate('created_at', today())->count(),
                'users' => AuditLog::query()->distinct('user_id')->count('user_id'),
                'admin_actions' => AuditLog::query()->where('action', 'like', 'admin_%')->count(),
            ],
        ];
    }
}
