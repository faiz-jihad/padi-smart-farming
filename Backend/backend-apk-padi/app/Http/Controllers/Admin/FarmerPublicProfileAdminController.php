<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FarmerPublicProfile;
use App\Models\ProfileTemplate;
use App\Services\Public\PublishFarmerProfileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FarmerPublicProfileAdminController extends Controller
{
    // ─── Profile Monitoring ───────────────────────────────────────────────

    public function index(Request $request): View
    {
        $query = FarmerPublicProfile::with(['farmer', 'template'])
            ->latest();

        if ($request->filled('status')) {
            $query->where('website_status', $request->string('status'));
        }

        if ($request->filled('verification')) {
            $query->where('verification_status', $request->string('verification'));
        }

        if ($request->filled('search')) {
            $search = $request->string('search');
            $query->where(function ($q) use ($search) {
                $q->where('business_name', 'like', "%{$search}%")
                    ->orWhere('subdomain', 'like', "%{$search}%")
                    ->orWhereHas('farmer', fn ($fq) => $fq->where('name', 'like', "%{$search}%"));
            });
        }

        $profiles = $query->paginate(20)->withQueryString();

        return view('admin.farmer-profiles.index', [
            'profiles' => $profiles,
        ]);
    }

    public function verify(FarmerPublicProfile $farmerProfile): RedirectResponse
    {
        $farmerProfile->update(['verification_status' => 'verified']);

        return back()->with('status', "Profil \"{$farmerProfile->business_name}\" berhasil diverifikasi.");
    }

    public function reject(Request $request, FarmerPublicProfile $farmerProfile): RedirectResponse
    {
        $farmerProfile->update(['verification_status' => 'rejected']);

        return back()->with('status', "Profil \"{$farmerProfile->business_name}\" ditolak.");
    }

    public function suspend(
        FarmerPublicProfile $farmerProfile,
        PublishFarmerProfileService $publisher,
    ): RedirectResponse {
        $publisher->suspend($farmerProfile);

        return back()->with('status', "Profil \"{$farmerProfile->business_name}\" ditangguhkan.");
    }

    public function restore(
        FarmerPublicProfile $farmerProfile,
        PublishFarmerProfileService $publisher,
    ): RedirectResponse {
        $publisher->restore($farmerProfile);

        return back()->with('status', "Profil \"{$farmerProfile->business_name}\" dipulihkan ke Draft.");
    }
}
