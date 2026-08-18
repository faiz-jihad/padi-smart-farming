<?php

namespace App\Http\Controllers\Farmer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Farmer\UpdatePublicProfileRequest;
use App\Http\Requests\Farmer\UpdateProfileSectionsRequest;
use App\Models\FarmerPublicProfile;
use App\Models\ProfileTemplate;
use App\Services\Public\FarmerPublicProfileDataService;
use App\Services\Public\PublishFarmerProfileService;
use App\Services\Public\SubdomainAvailabilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use RuntimeException;

class ProfileWebsiteController extends Controller
{
    // ─── Website Saya — Dashboard ──────────────────────────────────────────

    public function index(): View
    {
        $farmer = Auth::guard('farmer')->user();
        $profile = $farmer->publicProfile()->with('template')->first();

        return view('farmer.website.index', [
            'farmer'  => $farmer,
            'profile' => $profile,
        ]);
    }

    // ─── Edit Profile ──────────────────────────────────────────────────────

    public function edit(): View
    {
        $farmer = Auth::guard('farmer')->user();
        $profile = $farmer->publicProfile()->with('template')->firstOrNew([
            'farmer_id' => $farmer->id,
        ]);

        if (! $profile->exists) {
            $profile->section_settings = FarmerPublicProfile::DEFAULT_SECTION_SETTINGS;
        }

        return view('farmer.website.edit', [
            'farmer'  => $farmer,
            'profile' => $profile,
        ]);
    }

    public function update(UpdatePublicProfileRequest $request): RedirectResponse
    {
        $farmer = Auth::guard('farmer')->user();
        $profile = $farmer->publicProfile()->firstOrNew(['farmer_id' => $farmer->id]);

        $data = $request->validated();

        // Handle logo upload
        if ($request->hasFile('logo')) {
            if ($profile->logo_path) {
                Storage::disk('public')->delete($profile->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('farmer-profiles/logos', 'public');
            unset($data['logo']);
        }

        // Handle cover image upload
        if ($request->hasFile('cover_image')) {
            if ($profile->cover_image_path) {
                Storage::disk('public')->delete($profile->cover_image_path);
            }
            $data['cover_image_path'] = $request->file('cover_image')->store('farmer-profiles/covers', 'public');
            unset($data['cover_image']);
        }

        // Normalize WhatsApp
        if (! empty($data['whatsapp'])) {
            $data['whatsapp'] = preg_replace('/[^0-9]/', '', $data['whatsapp']);
            // Convert 08xx → 628xx
            if (str_starts_with($data['whatsapp'], '0')) {
                $data['whatsapp'] = '62' . substr($data['whatsapp'], 1);
            }
        }

        $profile->fill($data);

        if (! $profile->exists) {
            $profile->farmer_id = $farmer->id;
            $profile->section_settings = FarmerPublicProfile::DEFAULT_SECTION_SETTINGS;
        }

        $profile->save();

        return redirect()->route('farmer.website.index')
            ->with('status', 'Profil website berhasil disimpan.');
    }

    // ─── Privacy Sections ─────────────────────────────────────────────────

    public function sections(): View
    {
        $farmer = Auth::guard('farmer')->user();
        $profile = $farmer->publicProfile()->firstOrNew(['farmer_id' => $farmer->id]);

        return view('farmer.website.sections', [
            'farmer'   => $farmer,
            'profile'  => $profile,
            'settings' => $profile->resolvedSectionSettings(),
        ]);
    }

    public function updateSections(UpdateProfileSectionsRequest $request): RedirectResponse
    {
        $farmer = Auth::guard('farmer')->user();
        $profile = $farmer->publicProfile()->firstOrNew(['farmer_id' => $farmer->id]);

        if (! $profile->exists) {
            $profile->farmer_id = $farmer->id;
            $profile->business_name = $farmer->name . ' Farm';
        }

        $profile->section_settings = $request->validated()['section_settings'];
        $profile->save();

        return back()->with('status', 'Pengaturan privasi berhasil disimpan.');
    }

    // ─── Template Selection ───────────────────────────────────────────────

    public function template(): View
    {
        $farmer = Auth::guard('farmer')->user();
        $profile = $farmer->publicProfile()->with('template')->first();
        $templates = ProfileTemplate::active()->orderBy('sort_order')->get();

        return view('farmer.website.template', [
            'farmer'    => $farmer,
            'profile'   => $profile,
            'templates' => $templates,
        ]);
    }

    public function selectTemplate(Request $request): RedirectResponse
    {
        $request->validate([
            'template_id' => ['required', 'integer', 'exists:profile_templates,id'],
        ]);

        $farmer = Auth::guard('farmer')->user();
        $profile = $farmer->publicProfile()->firstOrNew(['farmer_id' => $farmer->id]);

        $template = ProfileTemplate::active()->findOrFail($request->integer('template_id'));

        if (! $profile->exists) {
            $profile->farmer_id = $farmer->id;
            $profile->business_name = $farmer->name . ' Farm';
            $profile->section_settings = FarmerPublicProfile::DEFAULT_SECTION_SETTINGS;
        }

        $profile->profile_template_id = $template->id;
        $profile->save();

        return redirect()->route('farmer.website.index')
            ->with('status', "Template \"{$template->name}\" berhasil dipilih.");
    }

    // ─── Subdomain ────────────────────────────────────────────────────────

    public function checkSubdomain(Request $request, SubdomainAvailabilityService $service): JsonResponse
    {
        $subdomain = strtolower(trim($request->query('subdomain', '')));

        if ($subdomain === '') {
            return response()->json(['available' => false, 'message' => 'Subdomain tidak boleh kosong.']);
        }

        $farmer = Auth::guard('farmer')->user();
        $profile = $farmer->publicProfile()->first();

        $available = $service->isAvailable($subdomain, $profile?->id);
        $baseDomain = config('domains.base', 'localhost');
        $scheme = app()->environment('production') ? 'https' : 'http';

        if ($available) {
            return response()->json([
                'available' => true,
                'subdomain' => $subdomain,
                'url'       => "{$scheme}://{$subdomain}.{$baseDomain}",
            ]);
        }

        return response()->json([
            'available' => false,
            'message'   => $service->unavailableReason($subdomain),
        ]);
    }

    public function updateSubdomain(Request $request, SubdomainAvailabilityService $service): RedirectResponse
    {
        $request->validate([
            'subdomain' => ['required', 'string', 'min:3', 'max:40', 'regex:/^[a-z0-9][a-z0-9\-]*[a-z0-9]$|^[a-z0-9]{3,}$/'],
        ]);

        $subdomain = strtolower(trim($request->string('subdomain')));

        $farmer = Auth::guard('farmer')->user();
        $profile = $farmer->publicProfile()->firstOrNew(['farmer_id' => $farmer->id]);

        if (! $service->isAvailable($subdomain, $profile?->id)) {
            return back()->withErrors(['subdomain' => $service->unavailableReason($subdomain)]);
        }

        if (! $profile->exists) {
            $profile->farmer_id = $farmer->id;
            $profile->business_name = $farmer->name . ' Farm';
            $profile->section_settings = FarmerPublicProfile::DEFAULT_SECTION_SETTINGS;
        }

        $profile->subdomain = $subdomain;
        $profile->save();

        return back()->with('status', "Subdomain {$subdomain} berhasil disimpan.");
    }

    // ─── Preview ──────────────────────────────────────────────────────────

    public function preview(
        FarmerPublicProfileDataService $dataService,
        FarmerProfileTemplateResolver $resolver,
    ): View {
        $farmer = Auth::guard('farmer')->user();
        $profile = $farmer->publicProfile()->with(['template', 'gallery'])->first();

        abort_if(! $profile, 404, 'Profil belum dibuat.');

        $data = $dataService->buildPublicData($profile);
        $view = $resolver->resolveFromProfile($profile);

        return view($view, array_merge($data, [
            'isPreview' => true,
        ]));
    }

    // ─── Publish / Unpublish ──────────────────────────────────────────────

    public function publish(PublishFarmerProfileService $publisher): RedirectResponse
    {
        $farmer = Auth::guard('farmer')->user();
        $profile = $farmer->publicProfile()->with('template')->first();

        abort_if(! $profile, 404);

        try {
            $publisher->publish($profile);
        } catch (RuntimeException $e) {
            return back()->withErrors(['publish' => $e->getMessage()]);
        }

        return redirect()->route('farmer.website.index')
            ->with('status', 'Website berhasil dipublikasikan! Sekarang dapat diakses publik.');
    }

    public function unpublish(PublishFarmerProfileService $publisher): RedirectResponse
    {
        $farmer = Auth::guard('farmer')->user();
        $profile = $farmer->publicProfile()->first();

        abort_if(! $profile, 404);

        $publisher->unpublish($profile);

        return redirect()->route('farmer.website.index')
            ->with('status', 'Website berhasil dinonaktifkan.');
    }
}
