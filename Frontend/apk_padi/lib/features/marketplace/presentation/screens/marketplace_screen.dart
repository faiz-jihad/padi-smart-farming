import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:padi/core/providers/app_providers.dart';
import 'package:padi/features/home/presentation/tokens/home_tokens.dart';
import 'package:padi/features/marketplace/data/models/market_listing_model.dart';
import 'package:padi/features/marketplace/data/services/marketplace_api_service.dart';
import 'package:padi/features/marketplace/presentation/widgets/market_listing_card.dart';
import 'package:padi/features/marketplace/presentation/widgets/marketplace_header.dart';
import 'package:padi/features/marketplace/presentation/widgets/marketplace_hero_banner.dart';
import 'package:padi/features/marketplace/presentation/widgets/marketplace_price_ticker.dart';
import 'package:padi/features/marketplace/presentation/widgets/marketplace_skeleton.dart';

final marketplaceApiServiceProvider = Provider<MarketplaceApiService>(
  (ref) => MarketplaceApiService(ref.read(apiClientProvider)),
);

final marketplaceListingsProvider =
    FutureProvider.autoDispose<List<MarketListingModel>>((ref) async {
  final service = ref.read(marketplaceApiServiceProvider);
  try {
    final listings = await service.fetchListings().timeout(
          const Duration(seconds: 8),
        );
    return listings.isNotEmpty ? listings : _seedListings;
  } catch (_) {
    return _seedListings;
  }
});

class MarketplaceScreen extends ConsumerStatefulWidget {
  const MarketplaceScreen({super.key});

  @override
  ConsumerState<MarketplaceScreen> createState() => _MarketplaceScreenState();
}

class _MarketplaceScreenState extends ConsumerState<MarketplaceScreen> {
  final TextEditingController _searchController = TextEditingController();
  String _selectedCategory = 'all';
  String _selectedSort = 'newest';

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _openCreateListing() async {
    final result = await context.push('/marketplace/create');

    if (!mounted) return;

    if (result == true) {
      setState(() {
        _selectedCategory = 'all';
        _searchController.clear();
      });
      ref.invalidate(marketplaceListingsProvider);
    }
  }

  List<MarketListingModel> _filterAndSortListings(
    List<MarketListingModel> raw,
  ) {
    var result = List<MarketListingModel>.from(raw);

    // Filter Category
    if (_selectedCategory != 'all') {
      result = result.where((listing) {
        final commodity = listing.commodity.toLowerCase();
        switch (_selectedCategory) {
          case 'gkp':
            return commodity.contains('gkp') || commodity.contains('panen');
          case 'gkg':
            return commodity.contains('gkg') || commodity.contains('giling');
          case 'beras':
            return commodity.contains('beras');
          case 'benih':
            return commodity.contains('benih') || commodity.contains('bibit');
          default:
            return true;
        }
      }).toList();
    }

    // Filter Keyword Search
    final keyword = _searchController.text.trim().toLowerCase();
    if (keyword.isNotEmpty) {
      result = result.where((listing) {
        final commodity = listing.commodity.toLowerCase();
        final description = (listing.description ?? '').toLowerCase();
        return commodity.contains(keyword) || description.contains(keyword);
      }).toList();
    }

    // Sorting
    switch (_selectedSort) {
      case 'price_asc':
        result.sort((a, b) => a.pricePerUnit.compareTo(b.pricePerUnit));
      case 'price_desc':
        result.sort((a, b) => b.pricePerUnit.compareTo(a.pricePerUnit));
      case 'qty_desc':
        result.sort((a, b) => b.quantity.compareTo(a.quantity));
      default:
        result.sort((a, b) => b.id.compareTo(a.id));
    }

    return result;
  }

  @override
  Widget build(BuildContext context) {
    final listingsAsync = ref.watch(marketplaceListingsProvider);

    return Scaffold(
      backgroundColor: const Color(0xFFF5F7F4), // Clean Agro Canvas
      appBar: AppBar(
        backgroundColor: HomeColors.primaryGreen,
        elevation: 0,
        scrolledUnderElevation: 0,
        leading: IconButton(
          tooltip: 'Kembali',
          icon: const Icon(
            Icons.arrow_back_rounded,
            color: Colors.white,
            size: 22,
          ),
          onPressed: () {
            if (context.canPop()) {
              context.pop();
            } else {
              context.go('/home');
            }
          },
        ),
        titleSpacing: 0,
        title: Container(
          height: 38,
          margin: const EdgeInsets.only(right: 8),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(6),
          ),
          child: TextField(
            controller: _searchController,
            onChanged: (_) => setState(() {}),
            style: const TextStyle(
              color: Color(0xFF17251E),
              fontSize: 13,
              fontWeight: FontWeight.w600,
            ),
            decoration: InputDecoration(
              isDense: true,
              filled: true,
              fillColor: Colors.white,
              hintText: 'Cari gabah panen, beras pandan wangi, benih...',
              hintStyle: const TextStyle(
                color: Color(0xFF999999),
                fontSize: 12,
              ),
              prefixIcon: const Icon(
                Icons.search_rounded,
                color: Color(0xFF888888),
                size: 19,
              ),
              suffixIcon: _searchController.text.isNotEmpty
                  ? IconButton(
                      onPressed: () {
                        _searchController.clear();
                        setState(() {});
                      },
                      icon: const Icon(
                        Icons.cancel_rounded,
                        color: Color(0xFF888888),
                        size: 16,
                      ),
                    )
                  : null,
              contentPadding: const EdgeInsets.symmetric(
                horizontal: 8,
                vertical: 8,
              ),
              border: InputBorder.none,
              enabledBorder: InputBorder.none,
              focusedBorder: InputBorder.none,
            ),
          ),
        ),
        actions: [
          IconButton(
            tooltip: 'Penawaran Saya',
            onPressed: () => context.push('/marketplace/offers'),
            icon: const Icon(
              Icons.receipt_long_rounded,
              color: Colors.white,
              size: 22,
            ),
          ),
          IconButton(
            tooltip: 'Segarkan',
            onPressed: () => ref.invalidate(marketplaceListingsProvider),
            icon: const Icon(
              Icons.refresh_rounded,
              color: Colors.white,
              size: 22,
            ),
          ),
          const SizedBox(width: 4),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _openCreateListing,
        backgroundColor: HomeColors.primaryGreen,
        foregroundColor: Colors.white,
        elevation: 4,
        icon: const Icon(Icons.add_shopping_cart_rounded, size: 20),
        label: const Text(
          'Mulai Jual Panen',
          style: TextStyle(
            fontWeight: FontWeight.w800,
            fontSize: 13.5,
          ),
        ),
      ),
      body: RefreshIndicator(
        color: HomeColors.primaryGreen,
        backgroundColor: Colors.white,
        onRefresh: () async => ref.invalidate(marketplaceListingsProvider),
        child: listingsAsync.when(
          data: (rawListings) {
            final filtered = _filterAndSortListings(rawListings);

            return ListView(
              physics: const AlwaysScrollableScrollPhysics(
                parent: BouncingScrollPhysics(),
              ),
              padding: const EdgeInsets.only(bottom: 120),
              children: [
                // 1. Kategori Ikon & Tab Sortir P.A.D.I.
                MarketplaceHeader(
                  searchController: _searchController,
                  selectedCategory: _selectedCategory,
                  onCategorySelected: (cat) {
                    setState(() => _selectedCategory = cat);
                  },
                  selectedSort: _selectedSort,
                  onSortSelected: (sort) {
                    setState(() => _selectedSort = sort);
                  },
                  totalListings: rawListings.length,
                  filteredListings: filtered.length,
                  onSearchChanged: () => setState(() {}),
                ),

                const SizedBox(height: 6),

                // 2. Bursa Panen Realtime Ticker
                MarketplacePriceTicker(listings: rawListings),

                const SizedBox(height: 6),

                // 3. Hero Banner dengan Foto Padi & Gradien Hijau Asli
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 8),
                  child: MarketplaceHeroBanner(
                    onTapCreateListing: _openCreateListing,
                  ),
                ),

                const SizedBox(height: 8),

                // 4. Grid Produk 2-Kolom E-Commerce
                if (filtered.isEmpty)
                  _buildEmptyState(context)
                else
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 8),
                    child: GridView.builder(
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      itemCount: filtered.length,
                      gridDelegate:
                          const SliverGridDelegateWithFixedCrossAxisCount(
                        crossAxisCount: 2,
                        childAspectRatio: 0.62,
                        crossAxisSpacing: 8,
                        mainAxisSpacing: 8,
                      ),
                      itemBuilder: (context, index) {
                        final listing = filtered[index];
                        return MarketListingCard(
                          listing: listing,
                          onTap: () {
                            context.push('/marketplace/${listing.id}');
                          },
                        );
                      },
                    ),
                  ),
              ],
            );
          },
          loading: () => ListView(
            padding: const EdgeInsets.fromLTRB(16, 8, 16, 120),
            children: const [
              MarketplaceSkeleton(),
            ],
          ),
          error: (err, stack) => ListView(
            padding: const EdgeInsets.fromLTRB(16, 8, 16, 120),
            children: [
              _buildEmptyState(context),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildEmptyState(BuildContext context) {
    final hasActiveFilter =
        _selectedCategory != 'all' || _searchController.text.trim().isNotEmpty;

    return Container(
      margin: const EdgeInsets.all(12),
      padding: const EdgeInsets.fromLTRB(20, 40, 20, 40),
      alignment: Alignment.center,
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(8),
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Container(
            width: 68,
            height: 68,
            decoration: BoxDecoration(
              color: const Color(0xFFF4F8F4),
              borderRadius: BorderRadius.circular(100),
            ),
            child: const Icon(
              Icons.shopping_bag_outlined,
              size: 34,
              color: HomeColors.primaryGreen,
            ),
          ),
          const SizedBox(height: 14),
          Text(
            hasActiveFilter
                ? 'Tidak Ada Hasil Panen Ditemukan'
                : 'Belum Ada Listing Panen',
            style: const TextStyle(
              fontSize: 15,
              fontWeight: FontWeight.w700,
              color: Color(0xFF17251E),
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 6),
          Text(
            hasActiveFilter
                ? 'Coba ganti kata kunci atau pilih tab kategori komoditas lainnya.'
                : 'Jadilah petani pertama yang membuka bursa penawaran panen.',
            style: const TextStyle(
              color: Color(0xFF68766E),
              fontSize: 12,
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 16),
          if (hasActiveFilter)
            OutlinedButton.icon(
              onPressed: () {
                setState(() {
                  _searchController.clear();
                  _selectedCategory = 'all';
                });
              },
              icon: const Icon(Icons.refresh_rounded, size: 16),
              label: const Text('Reset Filter'),
              style: OutlinedButton.styleFrom(
                foregroundColor: HomeColors.primaryGreen,
                side: const BorderSide(color: HomeColors.primaryGreen),
              ),
            )
          else
            FilledButton.icon(
              onPressed: _openCreateListing,
              icon: const Icon(Icons.add_shopping_cart_rounded, size: 16),
              label: const Text('Mulai Jual Panen Sekarang'),
              style: FilledButton.styleFrom(
                backgroundColor: HomeColors.primaryGreen,
                foregroundColor: Colors.white,
              ),
            ),
        ],
      ),
    );
  }
}

const List<MarketListingModel> _seedListings = [
  MarketListingModel(
    id: 101,
    farmerId: 1,
    farmId: 1,
    cropSeasonId: 1,
    harvestId: 1,
    commodity: 'Gabah Kering Panen (GKP) Inpari 32 Subang',
    quantity: 5000,
    unit: 'kg',
    pricePerUnit: 6850,
    status: 'published',
    description:
        'GKP segar baru dipotong combine harvester. Kadar air 21,5%, bulir bernas, lokasi mudah diakses truk.',
    salesLink:
        'https://wa.me/6281234567801?text=Halo%20saya%20tertarik%20dengan%20GKP%20Inpari%2032',
  ),
  MarketListingModel(
    id: 102,
    farmerId: 2,
    farmId: 2,
    cropSeasonId: 2,
    harvestId: 2,
    commodity: 'Gabah Kering Giling (GKG) Ciherang Super',
    quantity: 7500,
    unit: 'kg',
    pricePerUnit: 7600,
    status: 'published',
    description:
        'GKG kualitas super siap masuk RMU. Kadar air stabil 14%, bersih dari jerami dan batu.',
    salesLink:
        'https://wa.me/6281234567802?text=Halo%20saya%20berminat%20dengan%20GKG%20Ciherang',
  ),
  MarketListingModel(
    id: 103,
    farmerId: 3,
    farmId: 3,
    cropSeasonId: 3,
    harvestId: 3,
    commodity: 'Beras Premium Pandan Wangi Organik Asli',
    quantity: 2500,
    unit: 'kg',
    pricePerUnit: 15500,
    status: 'published',
    description:
        'Beras organik wangi alami, tanpa pemutih dan pengawet. Tersedia kemasan 5 kg sampai 25 kg.',
    salesLink:
        'https://wa.me/6281234567803?text=Halo%20saya%20ingin%20pesan%20Beras%20Pandan%20Wangi',
  ),
  MarketListingModel(
    id: 104,
    farmerId: 1,
    farmId: 1,
    cropSeasonId: 1,
    harvestId: 0,
    commodity: 'Benih Padi Bersertifikat Inpari 32 Label Biru',
    quantity: 1500,
    unit: 'kg',
    pricePerUnit: 18000,
    status: 'published',
    description:
        'Benih label biru, daya tumbuh di atas 95%, kemurnian varietas tinggi, kadar air 12%.',
    salesLink:
        'https://wa.me/6281234567802?text=Halo%20mau%20order%20Benih%20Inpari%2032',
  ),
];
