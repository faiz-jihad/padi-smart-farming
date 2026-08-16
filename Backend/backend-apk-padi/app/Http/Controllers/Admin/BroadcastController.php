<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBroadcastRequest;
use App\Models\AdminBroadcast;
use App\Services\Admin\AdminAuditLogger;
use App\Services\Admin\AdminBroadcastService;
use App\Services\Admin\AdminNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BroadcastController extends Controller
{
    public function index(AdminBroadcastService $broadcasts): View
    {
        return view('admin.broadcast.index', $broadcasts->indexData());
    }

    public function store(
        StoreBroadcastRequest $request,
        AdminBroadcastService $broadcasts,
        AdminAuditLogger $audit,
        AdminNotificationService $notifications,
    ): RedirectResponse {
        $broadcasts->store($request->user()->id, $request->validated(), $request, $audit, $notifications);

        return back()->with('status', 'Broadcast berhasil dibuat.');
    }

    public function update(
        StoreBroadcastRequest $request,
        AdminBroadcast $broadcast,
        AdminBroadcastService $broadcasts,
        AdminAuditLogger $audit,
        AdminNotificationService $notifications,
    ): RedirectResponse {
        $broadcasts->update($broadcast, $request->validated(), $request, $audit, $notifications);

        return back()->with('status', 'Broadcast berhasil diperbarui.');
    }

    public function destroy(
        Request $request,
        AdminBroadcast $broadcast,
        AdminBroadcastService $broadcasts,
        AdminAuditLogger $audit,
        AdminNotificationService $notifications,
    ): RedirectResponse {
        $broadcasts->destroy($broadcast, $request, $audit, $notifications);

        return back()->with('status', 'Broadcast berhasil dihapus.');
    }
}
