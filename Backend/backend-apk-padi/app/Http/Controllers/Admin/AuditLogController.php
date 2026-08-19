<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminAuditLogService;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(AdminAuditLogService $auditLogs): View
    {
        return view('admin.audit.index', $auditLogs->indexData());
    }
}
