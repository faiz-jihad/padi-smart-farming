<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Collection;

class AuditLogService
{
    public function getLogs(): Collection
    {
        return AuditLog::with('user')
            ->latest()
            ->get();
    }
}