<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;

class AuditLogController extends Controller
{
    public function index()
    {
        $logs = AuditLog::with('user')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }
}