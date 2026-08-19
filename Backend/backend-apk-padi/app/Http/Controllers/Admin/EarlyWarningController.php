<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEarlyWarningRequest;
use App\Services\Admin\AdminAuditLogger;
use App\Services\Admin\AdminEarlyWarningService;
use App\Services\Admin\AdminNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class EarlyWarningController extends Controller
{
    public function index(AdminEarlyWarningService $warnings): View
    {
        return view('admin.early-warning.index', $warnings->indexData());
    }

    public function store(
        StoreEarlyWarningRequest $request,
        AdminEarlyWarningService $warnings,
        AdminAuditLogger $audit,
        AdminNotificationService $notifications,
    ): RedirectResponse {
        $warnings->store(Auth::id(), $request->validated(), $request, $audit, $notifications);

        return back()->with('status', 'Early warning berhasil dibuat dan dikirim realtime.');
    }
}
