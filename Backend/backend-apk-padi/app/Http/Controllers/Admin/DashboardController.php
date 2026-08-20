<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminDashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

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
}

