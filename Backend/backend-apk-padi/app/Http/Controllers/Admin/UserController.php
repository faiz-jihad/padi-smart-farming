<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use App\Services\Admin\AdminAuditLogger;
use App\Services\Admin\AdminNotificationService;
use App\Services\Admin\AdminUserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request, AdminUserService $users): View
    {
        return view('admin.users.index', $users->indexData((string) $request->query('search', '')));
    }

    public function store(
        StoreUserRequest $request,
        AdminUserService $users,
        AdminAuditLogger $audit,
        AdminNotificationService $notifications,
    ): RedirectResponse {
        $users->store($request->validated(), $request, $audit, $notifications);

        return back()->with('status', 'Pengguna baru berhasil dibuat.');
    }

    public function update(
        UpdateUserRequest $request,
        User $user,
        AdminUserService $users,
        AdminAuditLogger $audit,
        AdminNotificationService $notifications,
    ): RedirectResponse {
        if (! $users->update($user, Auth::user(), $request->validated(), $request, $audit, $notifications)) {
            return back()->withErrors(['status' => 'Admin tidak bisa menonaktifkan akunnya sendiri.']);
        }

        return back()->with('status', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(
        Request $request,
        User $user,
        AdminUserService $users,
        AdminAuditLogger $audit,
        AdminNotificationService $notifications,
    ): RedirectResponse {
        if (! $users->destroy($user, Auth::user(), $request, $audit, $notifications)) {
            return back()->withErrors(['user' => 'Admin tidak bisa menghapus akunnya sendiri.']);
        }

        return back()->with('status', 'Pengguna berhasil dihapus.');
    }
}
