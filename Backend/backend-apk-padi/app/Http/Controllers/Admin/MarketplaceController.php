<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CropSeason;
use App\Rules\FarmBelongsToFarmer;
use App\Models\MarketListing;
use App\Models\MarketOffer;
use App\Services\Admin\AdminAuditLogger;
use App\Services\Admin\AdminMarketplaceService;
use App\Services\Admin\AdminNotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MarketplaceController extends Controller
{
    public function index(Request $request, AdminMarketplaceService $marketplace): View
    {
        return view('admin.marketplace.index', $marketplace->indexData($request));
    }

    public function create(AdminMarketplaceService $marketplace): View
    {
        $data = $marketplace->createData();

        $data['categories'] = Category::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'icon', 'is_active', 'sort_order']);

        $data['nextSortOrder'] = ((int) Category::query()->max('sort_order')) + 1;

        return view('admin.marketplace.create', $data);
    }

    public function store(Request $request, AdminMarketplaceService $marketplace): RedirectResponse
    {
        $validated = $request->validate([
            'farmer_id' => 'required|integer|exists:users,id',
            'farm_id' => [
                'required',
                'integer',
                'exists:farms,id',
                new FarmBelongsToFarmer((int) $request->farmer_id),
            ],
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where('is_active', true),
            ],
            'commodity' => 'required|string|max:100',
            'quantity' => 'required|numeric|min:0.1',
            'unit' => 'required|string|max:20',
            'price_per_unit' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'sales_link' => 'nullable|url|max:1000',
            'image_url' => 'nullable|string|max:1000',
            'status' => 'required|string|in:draft,published,closed,rejected,expired',
        ]);

        // Auto assign crop season
        $cropSeason = CropSeason::where('farm_id', $validated['farm_id'])
            ->latest('id')
            ->first();

        $validated['crop_season_id'] = $cropSeason?->id ?? CropSeason::firstOrCreate([
            'farm_id' => $validated['farm_id'],
            'season_name' => 'MT2 2026',
        ], [
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->addMonths(4),
            'status' => 'active',
        ])->id;

        $listing = $marketplace->storeListing($validated);

        return redirect()->route('admin.marketplace.index')
            ->with('status', "Listing {$listing->commodity} berhasil dibuat.");
    }

    /**
     * Simpan kategori baru dari modal tambah kategori.
     */
    public function storeCategory(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:100'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        // Gunakan slug dari input jika tersedia,
        // jika kosong otomatis dibuat dari nama kategori.
        $slug = ! empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : Str::slug($validated['name']);

        // Pastikan slug belum digunakan.
        if (Category::query()->where('slug', $slug)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Slug kategori sudah digunakan. Silakan gunakan slug lain.',
                'errors' => [
                    'slug' => [
                        'Slug kategori sudah digunakan.'
                    ],
                ],
            ], 422);
        }
        $nextSortOrder = ((int) Category::query()->max('sort_order')) + 1;
        $category = Category::query()->create([
            'name' => $validated['name'],
            'slug' => $slug,
            'icon' => $validated['icon'] ?? null,
            'sort_order' => $validated['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil ditambahkan.',
            'data' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'icon' => $category->icon,
                'sort_order' => $category->sort_order,
                'is_active' => $category->is_active,
            ],
        ]);
    }

    public function edit(MarketListing $listing, AdminMarketplaceService $marketplace): View
    {
        $data = $marketplace->createData();
        $data['listing'] = $listing;

        return view('admin.marketplace.edit', $data);
    }

    public function updateListing(
        Request $request,
        MarketListing $listing,
        AdminMarketplaceService $marketplace,
        AdminAuditLogger $audit,
        AdminNotificationService $notifications,
    ): RedirectResponse {
        $validated = $request->validate([
            'commodity' => 'sometimes|string|max:100',
            'quantity' => 'sometimes|numeric|min:0.1',
            'unit' => 'sometimes|string|max:20',
            'price_per_unit' => 'sometimes|numeric|min:0',
            'description' => 'nullable|string',
            'sales_link' => 'nullable|url|max:1000',
            'image_url' => 'nullable|string|max:1000',
            'status' => 'required|string|in:draft,published,closed,rejected,expired',
        ]);

        $marketplace->updateListing($listing, $validated, $request, $audit, $notifications);

        return redirect()->route('admin.marketplace.index')
            ->with('status', "Listing #{$listing->id} ({$listing->commodity}) berhasil diperbarui.");
    }

    public function destroy(MarketListing $listing, AdminMarketplaceService $marketplace): RedirectResponse
    {
        $commodity = $listing->commodity;
        $marketplace->deleteListing($listing);

        return redirect()->route('admin.marketplace.index')
            ->with('status', "Listing {$commodity} telah dihapus.");
    }

    public function updateOffer(
        Request $request,
        MarketOffer $offer,
        AdminMarketplaceService $marketplace,
        AdminAuditLogger $audit,
        AdminNotificationService $notifications,
    ): RedirectResponse {
        $validated = $request->validate([
            'status' => 'required|string|in:pending,accepted,rejected,cancelled',
        ]);

        $marketplace->updateOffer($offer, $validated, $request, $audit, $notifications);

        return back()->with('status', 'Status penawaran berhasil diperbarui.');
    }
}
