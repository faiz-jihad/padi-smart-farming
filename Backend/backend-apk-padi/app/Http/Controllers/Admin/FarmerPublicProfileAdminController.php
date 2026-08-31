<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CropSeason;
use App\Models\FarmerProfileGallery;
use App\Models\FarmerPublicProfile;
use App\Models\MarketListing;
use App\Models\ProfileTemplate;
use App\Models\User;
use App\Services\Admin\AdminMarketplaceService;
use App\Services\Admin\AdminNotificationService;
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
        }
        unset($data['logo']);

        if ($request->hasFile('cover_image')) {
            $data['cover_image_path'] = $request->file('cover_image')->store('farmer-profiles/covers', 'public');
        }
        unset($data['cover_image']);

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
        $farmerProfile->load(['farmer.farms', 'template', 'gallery']);
        $templates = ProfileTemplate::active()->orderBy('sort_order')->get();
        $listings = MarketListing::where('farmer_id', $farmerProfile->farmer_id)
            ->with('images')
            ->latest()
            ->get();
        $farms = $farmerProfile->farmer?->farms ?? collect();

        return view('admin.farmer-profiles.edit', [
            'profile'   => $farmerProfile,
            'templates' => $templates,
            'listings'  => $listings,
            'farms'     => $farms,
            'gallery'   => $farmerProfile->gallery ?? collect(),
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
        if ($request->has('section_settings')) {
            $keys = array_keys(FarmerPublicProfile::DEFAULT_SECTION_SETTINGS);
            $sectionSettings = [];

            foreach ($keys as $key) {
                $sectionSettings[$key] = $request->boolean("section_settings.{$key}", false);
            }

            $data['section_settings'] = $sectionSettings;
        } else {
            unset($data['section_settings']);
        }

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
        } else {
            unset($data['logo_path']);
        }
        unset($data['logo']);

        if ($request->hasFile('cover_image')) {
            if ($farmerProfile->cover_image_path) {
                Storage::disk('public')->delete($farmerProfile->cover_image_path);
            }
            $data['cover_image_path'] = $request->file('cover_image')->store('farmer-profiles/covers', 'public');
        } else {
            unset($data['cover_image_path']);
        }
        unset($data['cover_image']);

        if ($data['website_status'] === 'published' && ! $farmerProfile->published_at) {
            $data['published_at'] = now();
        }

        $farmerProfile->update($data);


        return redirect()->route('admin.farmer-profiles.index')
            ->with('status', "Profil \"{$farmerProfile->business_name}\" berhasil diperbarui.");
    }

    public function destroy(FarmerPublicProfile $farmerProfile): RedirectResponse
    {
        $name = $farmerProfile->business_name;

        \Illuminate\Support\Facades\DB::transaction(function () use ($farmerProfile) {
            foreach ($farmerProfile->gallery as $item) {
                if ($item->image_path) {
                    Storage::disk('public')->delete($item->image_path);
                }
                $item->delete();
            }

            if ($farmerProfile->logo_path) {
                Storage::disk('public')->delete($farmerProfile->logo_path);
            }
            if ($farmerProfile->cover_image_path) {
                Storage::disk('public')->delete($farmerProfile->cover_image_path);
            }

            $farmerProfile->delete();
        });

        return redirect()->route('admin.farmer-profiles.index')
            ->with('status', "Profil publik \"{$name}\" berhasil dihapus.");
    }


    // ─── Status Actions ────────────────────────────────────────────────────

    public function verify(FarmerPublicProfile $farmerProfile, AdminNotificationService $notifications): RedirectResponse
    {
        $farmerProfile->update(['verification_status' => 'verified']);

        if ($farmerProfile->farmer_id) {
            $notifications->notifyUser(
                $farmerProfile->farmer_id,
                '✅ Profil Website Anda Terverifikasi!',
                "Selamat, profil usaha tani \"{$farmerProfile->business_name}\" telah resmi diverifikasi oleh Administrator P.A.D.I.",
                'verification',
                ['url' => $farmerProfile->publicUrl()]
            );
        }

        $notifications->notifyAdmins(
            'Profil Petani Diverifikasi',
            "Administrator memverifikasi profil \"{$farmerProfile->business_name}\" ({$farmerProfile->subdomain}).",
            'verification'
        );

        return back()->with('status', "Profil \"{$farmerProfile->business_name}\" berhasil diverifikasi.");
    }

    public function reject(Request $request, FarmerPublicProfile $farmerProfile, AdminNotificationService $notifications): RedirectResponse
    {
        $farmerProfile->update(['verification_status' => 'rejected']);

        if ($farmerProfile->farmer_id) {
            $notifications->notifyUser(
                $farmerProfile->farmer_id,
                '⚠️ Pengajuan Verifikasi Profil Belum Disetujui',
                "Pengajuan verifikasi untuk \"{$farmerProfile->business_name}\" belum disetujui. Silakan lengkapi data profil dan kontak Anda.",
                'verification'
            );
        }

        return back()->with('status', "Profil \"{$farmerProfile->business_name}\" ditolak.");
    }

    public function suspend(
        FarmerPublicProfile $farmerProfile,
        PublishFarmerProfileService $publisher,
        AdminNotificationService $notifications
    ): RedirectResponse {
        $publisher->suspend($farmerProfile);

        if ($farmerProfile->farmer_id) {
            $notifications->notifyUser(
                $farmerProfile->farmer_id,
                '⛔ Website Usaha Tani Ditangguhkan',
                "Website publik \"{$farmerProfile->business_name}\" saat ini ditangguhkan oleh Administrator.",
                'system'
            );
        }

        return back()->with('status', "Profil \"{$farmerProfile->business_name}\" ditangguhkan.");
    }

    public function restore(
        FarmerPublicProfile $farmerProfile,
        PublishFarmerProfileService $publisher,
        AdminNotificationService $notifications
    ): RedirectResponse {
        $publisher->restore($farmerProfile);

        if ($farmerProfile->farmer_id) {
            $notifications->notifyUser(
                $farmerProfile->farmer_id,
                '🌟 Website Usaha Tani Kembali Aktif',
                "Website publik \"{$farmerProfile->business_name}\" telah dipulihkan dan dapat diakses kembali oleh publik.",
                'system',
                ['url' => $farmerProfile->publicUrl()]
            );
        }

        return back()->with('status', "Profil \"{$farmerProfile->business_name}\" berhasil dipulihkan dan dipublikasikan kembali.");
    }

    // ─── Direct Product/Listing Management for Farmer ────────────────────────

    public function storeListing(
        Request $request,
        FarmerPublicProfile $farmerProfile,
        AdminMarketplaceService $marketplace,
        AdminNotificationService $notifications
    ): RedirectResponse {
        $validated = $request->validate([
            'farm_id'        => 'required|integer|exists:farms,id',
            'commodity'      => 'required|string|max:100',
            'quantity'       => 'required|numeric|min:0.1',
            'unit'           => 'required|string|max:20',
            'price_per_unit' => 'required|numeric|min:0',
            'description'    => 'nullable|string',
            'sales_link'     => 'nullable|url|max:1000',
            'image_url'      => 'nullable|url|max:1000',
            'status'         => 'required|string|in:draft,published,closed,rejected,expired',
        ]);

        $validated['farmer_id'] = $farmerProfile->farmer_id;

        // Auto assign crop season
        $cropSeason = CropSeason::where('farm_id', $validated['farm_id'])->latest('id')->first();
        $validated['crop_season_id'] = $cropSeason?->id ?? CropSeason::firstOrCreate([
            'farm_id'     => $validated['farm_id'],
            'season_name' => 'MT2 2026',
        ], [
            'start_date' => now()->startOfMonth(),
            'end_date'   => now()->addMonths(4),
            'status'     => 'active',
        ])->id;

        $listing = $marketplace->storeListing($validated);

        if ($farmerProfile->farmer_id) {
            $notifications->notifyUser(
                $farmerProfile->farmer_id,
                '📦 Produk Baru Ditambahkan ke Etalase',
                "Produk \"{$listing->commodity}\" berhasil didaftarkan dan siap dipesan.",
                'marketplace',
                ['url' => $farmerProfile->publicUrl()]
            );
        }

        return back()->with('status', "Produk \"{$listing->commodity}\" berhasil ditambahkan ke etalase.");
    }

    public function updateListing(
        Request $request,
        FarmerPublicProfile $farmerProfile,
        MarketListing $listing,
        AdminMarketplaceService $marketplace,
        AdminNotificationService $notifications
    ): RedirectResponse {
        $validated = $request->validate([
            'commodity'      => 'required|string|max:100',
            'quantity'       => 'required|numeric|min:0.1',
            'unit'           => 'required|string|max:20',
            'price_per_unit' => 'required|numeric|min:0',
            'description'    => 'nullable|string',
            'sales_link'     => 'nullable|url|max:1000',
            'image_url'      => 'nullable|url|max:1000',
            'status'         => 'required|string|in:draft,published,closed,rejected,expired',
        ]);

        $marketplace->updateListing($listing, $validated);

        if ($farmerProfile->farmer_id) {
            $notifications->notifyUser(
                $farmerProfile->farmer_id,
                '✏️ Informasi Produk Diperbarui',
                "Detail dan harga untuk \"{$listing->commodity}\" telah diperbarui.",
                'marketplace'
            );
        }

        return back()->with('status', "Produk \"{$listing->commodity}\" berhasil diperbarui.");
    }

    public function destroyListing(
        FarmerPublicProfile $farmerProfile,
        MarketListing $listing,
        AdminMarketplaceService $marketplace,
        AdminNotificationService $notifications
    ): RedirectResponse {
        $name = $listing->commodity;
        $marketplace->deleteListing($listing);

        if ($farmerProfile->farmer_id) {
            $notifications->notifyUser(
                $farmerProfile->farmer_id,
                '🗑️ Produk Dihapus dari Etalase',
                "Produk \"{$name}\" telah dihapus dari daftar katalog.",
                'marketplace'
            );
        }

        return back()->with('status', "Produk \"{$name}\" berhasil dihapus dari etalase.");
    }

    // ─── Direct Gallery Photos Management for Farmer ─────────────────────────

    public function storeGallery(
        Request $request,
        FarmerPublicProfile $farmerProfile,
        AdminNotificationService $notifications
    ): RedirectResponse {
        $request->validate([
            'image'   => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'caption' => ['nullable', 'string', 'max:255'],
        ]);

        $path = $request->file('image')->store('farmer-profiles/gallery', 'public');

        $farmerProfile->gallery()->create([
            'image_path' => $path,
            'caption'    => $request->input('caption'),
            'sort_order' => ($farmerProfile->gallery()->max('sort_order') ?? 0) + 1,
            'status'     => 'active',
        ]);

        if ($farmerProfile->farmer_id) {
            $notifications->notifyUser(
                $farmerProfile->farmer_id,
                '📸 Foto Dokumentasi Ditambahkan',
                "Foto baru berhasil diunggah ke galeri website \"{$farmerProfile->business_name}\".",
                'gallery',
                ['url' => $farmerProfile->publicUrl()]
            );
        }

        return back()->with('status', 'Foto dokumentasi galeri berhasil ditambahkan.');
    }

    public function destroyGallery(
        FarmerPublicProfile $farmerProfile,
        FarmerProfileGallery $gallery
    ): RedirectResponse {
        if ($gallery->image_path) {
            Storage::disk('public')->delete($gallery->image_path);
        }

        $gallery->delete();

        return back()->with('status', 'Foto galeri berhasil dihapus.');
    }
}

