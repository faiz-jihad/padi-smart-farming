<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\AdminDashboardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(AdminDashboardService $dashboard): View
    {
        $farmId = request()->filled('farm_id') ? (int) request('farm_id') : null;
        return view('admin.dashboard', $dashboard->viewData(Auth::id(), $farmId));
    }

    public function markNotificationsRead(AdminDashboardService $dashboard): RedirectResponse
    {
        if (! $dashboard->markNotificationsRead(Auth::id())) {
            return back()->with('status', 'Belum ada tabel notifikasi.');
        }

        return back()->with('status', 'Notifikasi sudah ditandai dibaca.');
    }
}
