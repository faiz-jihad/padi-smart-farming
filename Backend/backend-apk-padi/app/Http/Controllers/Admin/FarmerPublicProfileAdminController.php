<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FarmerPublicProfile;
use App\Models\ProfileTemplate;
use App\Models\User;
use App\Services\Public\PublishFarmerProfileService;
use App\Services\Public\SubdomainAvailabilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class FarmerPublicProfileAdminController extends Controller
{
    // ─── Profile Monitoring & Listing ──────────────────────────────────────

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

    // ─── Create (Admin makes website for farmer) ───────────────────────────

    public function create(Request $request): View
    {
        // Farmers without profile or specifically requested farmer_id
        $selectedFarmerId = $request->integer('farmer_id');

        $farmers = User::where('role', 'farmer')
            ->where('status', 'active')
            ->whereDoesntHave('publicProfile')
            ->orderBy('name')
            ->get();

        // If selected farmer already has a profile or is specified
        if ($selectedFarmerId && ! $farmers->contains('id', $selectedFarmerId)) {
            $specificFarmer = User::where('id', $selectedFarmerId)->where('role', 'farmer')->first();
            if ($specificFarmer) {
                $farmers->prepend($specificFarmer);
            }
        }

        $templates = ProfileTemplate::active()->orderBy('sort_order')->get();

        return view('admin.farmer-profiles.create', [
            'farmers'          => $farmers,
            'templates'        => $templates,
            'selectedFarmerId' => $selectedFarmerId,
            'defaults'         => FarmerPublicProfile::DEFAULT_SECTION_SETTINGS,
        ]);
    }

    public function store(Request $request, SubdomainAvailabilityService $subdomainService): RedirectResponse
    {
        $data = $request->validate([
            'farmer_id'           => ['required', 'integer', 'exists:users,id', 'unique:farmer_public_profiles,farmer_id'],
            'profile_template_id' => ['required', 'integer', 'exists:profile_templates,id'],
            'subdomain'           => ['required', 'string', 'min:3', 'max:40'],
            'business_name'       => ['required', 'string', 'max:150'],
            'headline'            => ['nullable', 'string', 'max:255'],
            'description'         => ['nullable', 'string', 'max:3000'],
            'whatsapp'            => ['nullable', 'string', 'max:30'],
            'public_email'        => ['nullable', 'email', 'max:150'],
            'public_phone'        => ['nullable', 'string', 'max:30'],
            'public_address'      => ['nullable', 'string', 'max:500'],
            'instagram_url'       => ['nullable', 'url', 'max:255'],
            'facebook_url'        => ['nullable', 'url', 'max:255'],
            'website_status'      => ['required', 'in:draft,review,published,suspended'],
            'verification_status' => ['required', 'in:unverified,verified,rejected'],
            'logo'                => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'cover_image'         => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $subdomain = strtolower(trim($data['subdomain']));
        if (! $subdomainService->isAvailable($subdomain)) {
            return back()->withErrors(['subdomain' => $subdomainService->unavailableReason($subdomain)])->withInput();
        }
        $data['subdomain'] = $subdomain;

        // Verify user is farmer
        $farmerUser = User::findOrFail($data['farmer_id']);
        if ($farmerUser->role !== 'farmer') {
            return back()->withErrors(['farmer_id' => 'Pengguna yang dipilih bukan berstatus Petani.'])->withInput();
        }

        // Section settings
        $keys = array_keys(FarmerPublicProfile::DEFAULT_SECTION_SETTINGS);
        $sectionSettings = [];
        foreach ($keys as $key) {
            $sectionSettings[$key] = $request->boolean("section_settings.{$key}", false);
        }
        $data['section_settings'] = $sectionSettings;

        // Normalize WhatsApp
        if (! empty($data['whatsapp'])) {
            $data['whatsapp'] = preg_replace('/[^0-9]/', '', $data['whatsapp']);
            if (str_starts_with($data['whatsapp'], '0')) {
                $data['whatsapp'] = '62' . substr($data['whatsapp'], 1);
            }
        }

        // Uploads
        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('farmer-profiles/logos', 'public');
            unset($data['logo']);
        }
        if ($request->hasFile('cover_image')) {
            $data['cover_image_path'] = $request->file('cover_image')->store('farmer-profiles/covers', 'public');
            unset($data['cover_image']);
        }

        if ($data['website_status'] === 'published') {
            $data['published_at'] = now();
        }

        $profile = FarmerPublicProfile::create($data);

        return redirect()->route('admin.farmer-profiles.index')
            ->with('status', "Profil publik untuk \"{$profile->business_name}\" ({$profile->subdomain}) berhasil dibuat.");
    }

    // ─── Edit ──────────────────────────────────────────────────────────────

    public function edit(FarmerPublicProfile $farmerProfile): View
    {
        $farmerProfile->load(['farmer', 'template']);
        $templates = ProfileTemplate::active()->orderBy('sort_order')->get();

        return view('admin.farmer-profiles.edit', [
            'profile'   => $farmerProfile,
            'templates' => $templates,
            'settings'  => $farmerProfile->resolvedSectionSettings(),
        ]);
    }

    public function update(
        Request $request,
        FarmerPublicProfile $farmerProfile,
        SubdomainAvailabilityService $subdomainService
    ): RedirectResponse {
        $data = $request->validate([
            'profile_template_id' => ['required', 'integer', 'exists:profile_templates,id'],
            'subdomain'           => ['required', 'string', 'min:3', 'max:40'],
            'business_name'       => ['required', 'string', 'max:150'],
            'headline'            => ['nullable', 'string', 'max:255'],
            'description'         => ['nullable', 'string', 'max:3000'],
            'whatsapp'            => ['nullable', 'string', 'max:30'],
            'public_email'        => ['nullable', 'email', 'max:150'],
            'public_phone'        => ['nullable', 'string', 'max:30'],
            'public_address'      => ['nullable', 'string', 'max:500'],
            'instagram_url'       => ['nullable', 'url', 'max:255'],
            'facebook_url'        => ['nullable', 'url', 'max:255'],
            'website_status'      => ['required', 'in:draft,review,published,suspended'],
            'verification_status' => ['required', 'in:unverified,verified,rejected'],
            'logo'                => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'cover_image'         => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $subdomain = strtolower(trim($data['subdomain']));
        if (! $subdomainService->isAvailable($subdomain, $farmerProfile->id)) {
            return back()->withErrors(['subdomain' => $subdomainService->unavailableReason($subdomain)])->withInput();
        }
        $data['subdomain'] = $subdomain;

        // Section settings
        $keys = array_keys(FarmerPublicProfile::DEFAULT_SECTION_SETTINGS);
        $sectionSettings = [];
        foreach ($keys as $key) {
            $sectionSettings[$key] = $request->boolean("section_settings.{$key}", false);
        }
        $data['section_settings'] = $sectionSettings;

        // Normalize WhatsApp
        if (! empty($data['whatsapp'])) {
            $data['whatsapp'] = preg_replace('/[^0-9]/', '', $data['whatsapp']);
            if (str_starts_with($data['whatsapp'], '0')) {
                $data['whatsapp'] = '62' . substr($data['whatsapp'], 1);
            }
        }

        // Uploads
        if ($request->hasFile('logo')) {
            if ($farmerProfile->logo_path) {
                Storage::disk('public')->delete($farmerProfile->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('farmer-profiles/logos', 'public');
            unset($data['logo']);
        }
        if ($request->hasFile('cover_image')) {
            if ($farmerProfile->cover_image_path) {
                Storage::disk('public')->delete($farmerProfile->cover_image_path);
            }
            $data['cover_image_path'] = $request->file('cover_image')->store('farmer-profiles/covers', 'public');
            unset($data['cover_image']);
        }

        if ($data['website_status'] === 'published' && ! $farmerProfile->published_at) {
            $data['published_at'] = now();
        }

        $farmerProfile->update($data);

        return redirect()->route('admin.farmer-profiles.index')
            ->with('status', "Profil \"{$farmerProfile->business_name}\" berhasil diperbarui.");
    }

    public function destroy(FarmerPublicProfile $farmerProfile): RedirectResponse
    {
        if ($farmerProfile->logo_path) {
            Storage::disk('public')->delete($farmerProfile->logo_path);
        }
        if ($farmerProfile->cover_image_path) {
            Storage::disk('public')->delete($farmerProfile->cover_image_path);
        }

        $name = $farmerProfile->business_name;
        $farmerProfile->delete();

        return redirect()->route('admin.farmer-profiles.index')
            ->with('status', "Profil publik \"{$name}\" berhasil dihapus.");
    }

    // ─── Status Actions ────────────────────────────────────────────────────

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
