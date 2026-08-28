<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Farm;
use App\Services\Admin\AdminAgricultureService;
use App\Services\Admin\AdminAuditLogger;
use App\Services\Admin\AdminNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use App\Services\Geography\LocationService;


class AgricultureController extends Controller
{
    public function index(Request $request, AdminAgricultureService $agriculture): View
    {
        return view('admin.agriculture.index', $agriculture->indexData($request));
    }

    public function store(
        Request $request,
        AdminAgricultureService $agriculture,
        AdminAuditLogger $audit,
        AdminNotificationService $notifications,
        LocationService $locationService,
    ): RedirectResponse {
        $validated = $request->validate([
            'farmer_user_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'farmer')),
            ],
            'name' => ['required', 'string', 'max:255'],
            'area_ha' => ['required', 'numeric', 'min:0.01'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'boundary_coordinates' => ['nullable', 'json'],
            'irrigation_type' => ['required', 'string'],
            'irrigation_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $agriculture->store(
            $validated,
            $request,
            $audit,
            $notifications,
            $locationService
        );

        return back()->with('status', 'Data lahan pertanian baru berhasil ditambahkan.');
    }

    public function update(
        Request $request,
        Farm $farm,
        AdminAgricultureService $agriculture,
        AdminAuditLogger $audit,
        AdminNotificationService $notifications,
    ): RedirectResponse {
        $validated = $request->validate([
            'farmer_user_id' => [
                'required',
                Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', 'farmer')),
            ],
            'name' => ['required', 'string', 'max:255'],
            'area_ha' => ['required', 'numeric', 'min:0.01'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'boundary_coordinates' => ['nullable', 'json'],
            'irrigation_type' => ['required', 'string'],
            'irrigation_notes' => ['nullable', 'string', 'max:500'],
        ]);

        $agriculture->update($farm, $validated, $request, $audit, $notifications);

        return back()->with('status', 'Data lahan pertanian berhasil diperbarui.');
    }

    public function destroy(
        Request $request,
        Farm $farm,
        AdminAgricultureService $agriculture,
        AdminAuditLogger $audit,
        AdminNotificationService $notifications,
    ): RedirectResponse {
        $agriculture->destroy($farm, $request, $audit, $notifications);

        return back()->with('status', 'Data lahan berhasil dihapus.');
    }
}
