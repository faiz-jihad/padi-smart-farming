<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Notification;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index(
        Request $request,
        AdminDashboardService $dashboard,
        \App\Services\Admin\AdminWeatherService $weatherAdminService
    ): View|JsonResponse {
        $farmId = $request->filled('farm_id') ? (int) $request->input('farm_id') : null;

        if ($request->boolean('force_sync')) {
            try {
                if ($farmId) {
                    $weatherAdminService->refreshWeatherData($farmId);
                } else {
                    $weatherAdminService->refreshAllFarmsWeatherData();
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $data = $dashboard->viewData(Auth::id(), $farmId);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        }

        return view('admin.dashboard', $data);
    }

    public function report(
        Request $request,
        AdminDashboardService $dashboard
    )
    {
        $farmId = $request->filled('farm_id')
            ? (int) $request->input('farm_id')
            : null;

        $data = $dashboard->viewData(Auth::id(), $farmId);

        $pdf = Pdf::loadView(
            'admin.report.dashboard-report',
            $data
        );

        $pdf->setPaper('a4', 'portrait');

        return $pdf->stream(
            'Laporan_Monitoring_Agroklimat_' . now()->format('d-m-Y') . '.pdf'
        );
    }

    public function markNotificationsRead(Request $request, AdminDashboardService $dashboard): RedirectResponse|JsonResponse
    {
        $success = $dashboard->markNotificationsRead(Auth::id());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => $success,
                'message' => $success ? 'Notifikasi sudah ditandai dibaca.' : 'Belum ada notifikasi.',
            ]);
        }

        if (! $success) {
            return back()->with('status', 'Belum ada tabel notifikasi.');
        }

        return back()->with('status', 'Notifikasi sudah ditandai dibaca.');
    }

    public function notifications(): View
    {
        $adminId = Auth::id();

        $notifications = collect();
        $unreadCount = 0;
        $totalCount = 0;
        $alertCount = 0;
        $infoCount = 0;

        if ($adminId && Schema::hasTable('notifications')) {
            $notifications = Notification::query()
                ->where('user_id', $adminId)
                ->latest('id')
                ->paginate(15);

            $unreadCount = Notification::query()
                ->where('user_id', $adminId)
                ->whereNull('read_at')
                ->count();

            $totalCount = Notification::query()
                ->where('user_id', $adminId)
                ->count();

            $alertCount = Notification::query()
                ->where('user_id', $adminId)
                ->where(function ($q) {
                    $q->where('type', 'like', '%alert%')
                      ->orWhere('type', 'like', '%warning%')
                      ->orWhere('type', 'like', '%disease%')
                      ->orWhere('type', 'like', '%weather%')
                      ->orWhere('type', 'like', '%bencana%');
                })
                ->count();

            $infoCount = max(0, $totalCount - $alertCount);
        }

        return view('admin.notifications.index', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
            'totalCount' => $totalCount,
            'alertCount' => $alertCount,
            'infoCount' => $infoCount,
        ]);
    }

    public function latestNotifications(): JsonResponse
    {
        $adminId = Auth::id();
        $unreadCount = 0;
        $items = [];

        if ($adminId && Schema::hasTable('notifications')) {
            $unreadCount = Notification::query()
                ->where('user_id', $adminId)
                ->whereNull('read_at')
                ->count();

            $items = Notification::query()
                ->where('user_id', $adminId)
                ->latest('id')
                ->limit(6)
                ->get()
                ->map(fn ($n) => [
                    'id' => $n->id,
                    'type' => $n->type,
                    'title' => $n->title,
                    'body' => $n->body,
                    'is_read' => $n->read_at !== null,
                    'created_at_human' => $n->created_at?->diffForHumans(),
                ]);
        }

        return response()->json([
            'status' => 'success',
            'unread_count' => $unreadCount,
            'items' => $items,
        ]);
    }
}

