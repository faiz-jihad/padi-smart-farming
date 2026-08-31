import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import 'package:padi/core/localization/app_language.dart';
import 'package:padi/core/providers/app_providers.dart';
import 'package:padi/core/utils/debouncer.dart';
import 'package:padi/features/cart/presentation/providers/cart_providers.dart';
import 'package:padi/features/home/presentation/tokens/home_tokens.dart';
import 'package:padi/features/home/presentation/widgets/role_rights_card.dart';
import 'package:padi/features/home/presentation/widgets/upcoming_events_banner.dart';
import 'package:padi/features/marketplace/data/models/market_listing_model.dart';
import 'package:padi/features/marketplace/presentation/screens/buyer_orders_screen.dart';
import 'package:padi/features/marketplace/presentation/screens/marketplace_screen.dart';

class BuyerHomeScreen extends ConsumerStatefulWidget {
  const BuyerHomeScreen({super.key});

  @override
  ConsumerState<BuyerHomeScreen> createState() => _BuyerHomeScreenState();
}

class _BuyerHomeScreenState extends ConsumerState<BuyerHomeScreen> {
  final _searchController = TextEditingController();

  String _selectedTab = 'Rekomendasi';
  String _selectedCategory = 'Semua';
  String _deliveryLocation = 'Gudang Utama (Jawa Barat)';

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  String _formatPrice(double value) {
    final currencyFmt = NumberFormat.currency(
      locale: 'id_ID',
      symbol: 'Rp ',
      decimalDigits: 0,
    );
    return currencyFmt.format(value.round());
  }

  @override
  Widget build(BuildContext context) {
    final lang = ref.watch(languageProvider);
    final s = AppStrings(lang);
    final auth = ref.watch(authControllerProvider);
    final user = auth.state.user;
    final userName = user?.name.trim().isNotEmpty == true
        ? user!.name.trim()
        : s.roleBuyer;

    final cartState = ref.watch(cartProvider);
    final listingsAsync = ref.watch(marketplaceListingsProvider);
    final contractsAsync = ref.watch(buyerContractsProvider);

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC), // Pure Marketplace Canvas
      body: SafeArea(
        child: RefreshIndicator(
          color: const Color(0xFF0F5132),
          backgroundColor: Colors.white,
          onRefresh: () async {
            ref.invalidate(marketplaceListingsProvider);
            ref.invalidate(buyerContractsProvider);
          },
          child: CustomScrollView(
            physics: const AlwaysScrollableScrollPhysics(
              parent: BouncingScrollPhysics(),
            ),
            slivers: [
              // 1. Tokopedia/Shopee Sticky Search Bar Header
              SliverPersistentHeader(
                pinned: true,
                delegate: _MarketplaceHeaderDelegate(
                  cartCount: cartState.totalCount,
                  onSearchSubmitted: (q) => context.go('/marketplace'),
                  onCartTap: () => context.push('/cart'),
                  onNotificationTap: () => context.push('/notifications'),
                  s: s,
                ),
              ),

              // 2. Main Marketplace Body
              SliverToBoxAdapter(
                child: Padding(
                  padding: const EdgeInsets.fromLTRB(16, 8, 16, 120),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const SizedBox(height: 6),

                      // Berita & Agenda Acara Tani (Sama seperti dashboard Petani)
                      UpcomingEventsBanner(
                        onEventTap: (event) => context.push('/events/detail', extra: event),
                        onCreateEventTap: () => context.push('/events/create'),
                        onViewAllTap: () => context.push('/events'),
                      ),

                      const SizedBox(height: 16),

                      // 8 Kategori Cepat Tokopedia-Style
                      _buildQuickCategoryGrid(context),

                      const SizedBox(height: 16),

                      // Flash Sale / Panen Kilat Terhangat (Shopee Flash Sale Style)
                      listingsAsync.when(
                        data: (listings) => _buildFlashSaleSection(context, ref, listings),
                        loading: () => const SizedBox.shrink(),
                        error: (_, __) => const SizedBox.shrink(),
                      ),

                      const SizedBox(height: 16),

                      // Active Shipment Tracker (Jika ada kontrak berjalan)
                      contractsAsync.when(
                        data: (contracts) {
                          final activeList = contracts
                              .where((c) => c.status.toLowerCase() == 'active')
                              .toList();
                          if (activeList.isEmpty) return const SizedBox.shrink();
                          return Padding(
                            padding: const EdgeInsets.only(bottom: 16),
                            child: _buildShipmentTracker(context, activeList.first),
                          );
                        },
                        loading: () => const SizedBox.shrink(),
                        error: (_, __) => const SizedBox.shrink(),
                      ),

                      // Feed Filter Tabs (Rekomendasi, Paling Laris, Dekat Anda)
                      _buildMarketplaceFeedTabs(s),

                      const SizedBox(height: 12),

                      // 2-Column Marketplace Product Grid
                      listingsAsync.when(
                        data: (listings) {
                          final filtered = _filterListings(listings);
                          if (filtered.isEmpty) {
                            return _buildEmptyState();
                          }
                          return GridView.builder(
                            shrinkWrap: true,
                            physics: const NeverScrollableScrollPhysics(),
                            itemCount: filtered.length,
                            gridDelegate:
                                const SliverGridDelegateWithFixedCrossAxisCount(
                              crossAxisCount: 2,
                              crossAxisSpacing: 10,
                              mainAxisSpacing: 10,
                              mainAxisExtent: 295,
                            ),
                            itemBuilder: (context, index) {
                              return _buildProductCard(
                                context,
                                ref,
                                filtered[index],
                              );
                            },
                          );
                        },
                        loading: () => const Center(
                          child: Padding(
                            padding: EdgeInsets.all(40),
                            child: CircularProgressIndicator(
                              color: Color(0xFF0F5132),
                            ),
                          ),
                        ),
                        error: (_, __) => _buildEmptyState(),
                      ),

                      const SizedBox(height: 20),

                      // Jaminan Perlindungan Belanja P.A.D.I.
                      _buildTrustBadges(),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  // --- Location & Role Switcher Bar ---
  Widget _buildTopActionBar(BuildContext context, WidgetRef ref, String userName) {
    return Column(
      children: [
        // Role Status Pill
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
          decoration: BoxDecoration(
            color: const Color(0xFF0A251B),
            borderRadius: BorderRadius.circular(10),
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  const Icon(Icons.verified_rounded, size: 15, color: Color(0xFFF59E0B)),
                  const SizedBox(width: 6),
                  Text(
                    'Akun Pembeli B2B: $userName',
                    style: const TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w800,
                      color: Colors.white,
                    ),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                ],
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: const Color(0xFF10B981).withOpacity(0.25),
                  borderRadius: BorderRadius.circular(6),
                  border: Border.all(color: const Color(0xFF34D399), width: 0.8),
                ),
                child: const Text(
                  'TERVERIFIKASI',
                  style: TextStyle(
                    fontSize: 9,
                    fontWeight: FontWeight.w900,
                    color: Color(0xFF34D399),
                    letterSpacing: 0.4,
                  ),
                ),
              ),
            ],
          ),
        ),

        const SizedBox(height: 6),

        // Delivery Location Picker (Tokopedia-style)
        InkWell(
          onTap: () => _showLocationSelectorModal(context),
          borderRadius: BorderRadius.circular(8),
          child: Padding(
            padding: const EdgeInsets.symmetric(vertical: 4, horizontal: 2),
            child: Row(
              children: [
                const Icon(Icons.location_on_rounded, size: 14, color: Color(0xFF0F5132)),
                const SizedBox(width: 4),
                const Text(
                  'Lokasi Penjemputan / Kirim: ',
                  style: TextStyle(fontSize: 11, color: Color(0xFF64748B)),
                ),
                Text(
                  _deliveryLocation,
                  style: const TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w800,
                    color: Color(0xFF0F172A),
                  ),
                ),
                const Icon(Icons.keyboard_arrow_down_rounded, size: 16, color: Color(0xFF64748B)),
              ],
            ),
          ),
        ),
      ],
    );
  }



  // --- 8 Kategori Cepat Tokopedia-Style ---
  Widget _buildQuickCategoryGrid(BuildContext context) {
    final categories = [
      ('Gabah GKP', Icons.grain_rounded, const Color(0xFFDCFCE7), const Color(0xFF047857)),
      ('Gabah GKG', Icons.agriculture_rounded, const Color(0xFFECFDF5), const Color(0xFF059669)),
      ('Beras Super', Icons.rice_bowl_rounded, const Color(0xFFD1FAE5), const Color(0xFF0F5132)),
      ('Beras Medium', Icons.inventory_2_rounded, const Color(0xFFF0FDF4), const Color(0xFF065F46)),
      ('Beras Ketan', Icons.eco_rounded, const Color(0xFFDCFCE7), const Color(0xFF047857)),
      ('Partai Truk', Icons.local_shipping_rounded, const Color(0xFFECFDF5), const Color(0xFF059669)),
      ('Bursa Lelang', Icons.gavel_rounded, const Color(0xFFD1FAE5), const Color(0xFF0F5132)),
      ('Kontrak Saya', Icons.receipt_long_rounded, const Color(0xFFF0FDF4), const Color(0xFF065F46)),
    ];

    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Kategori Hasil Panen',
            style: TextStyle(
              fontSize: 13,
              fontWeight: FontWeight.w900,
              color: Color(0xFF0F172A),
            ),
          ),
          const SizedBox(height: 10),
          GridView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: categories.length,
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 4,
              childAspectRatio: 0.92,
              crossAxisSpacing: 8,
              mainAxisSpacing: 8,
            ),
            itemBuilder: (context, idx) {
              final cat = categories[idx];
              return InkWell(
                onTap: () {
                  if (cat.$1 == 'Kontrak Saya') {
                    context.push('/buyer/orders');
                  } else if (cat.$1 == 'Bursa Lelang') {
                    context.push('/marketplace/offers');
                  } else {
                    context.go('/marketplace');
                  }
                },
                borderRadius: BorderRadius.circular(10),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: cat.$3,
                        shape: BoxShape.circle,
                      ),
                      child: Icon(cat.$2, size: 18, color: cat.$4),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      cat.$1,
                      style: const TextStyle(
                        fontSize: 10,
                        fontWeight: FontWeight.w700,
                        color: Color(0xFF334155),
                      ),
                      textAlign: TextAlign.center,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ],
                ),
              );
            },
          ),
        ],
      ),
    );
  }

  // --- Flash Sale / Panen Kilat Terhangat ---
  Widget _buildFlashSaleSection(
    BuildContext context,
    WidgetRef ref,
    List<MarketListingModel> listings,
  ) {
    if (listings.isEmpty) return const SizedBox.shrink();
    final flashListings = listings.take(4).toList();

    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: const Color(0xFFFFFBEB), // Warm Flash Sale Gold
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFFDE68A)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Icon(Icons.local_fire_department_rounded, color: Color(0xFFEA580C), size: 18),
              const SizedBox(width: 4),
              const Text(
                'BURSA PANEN KILAT',
                style: TextStyle(
                  fontSize: 13,
                  fontWeight: FontWeight.w900,
                  color: Color(0xFF9A3412),
                ),
              ),
              const SizedBox(width: 8),
              // Timer countdown badge
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 2),
                decoration: BoxDecoration(
                  color: const Color(0xFFEA580C),
                  borderRadius: BorderRadius.circular(4),
                ),
                child: const Text(
                  '02 : 14 : 35',
                  style: TextStyle(
                    fontSize: 9.5,
                    fontWeight: FontWeight.w900,
                    color: Colors.white,
                  ),
                ),
              ),
              const Spacer(),
              InkWell(
                onTap: () => context.go('/marketplace'),
                child: const Text(
                  'Lihat Semua >',
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w800,
                    color: Color(0xFFEA580C),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          SizedBox(
            height: 175,
            child: ListView.separated(
              scrollDirection: Axis.horizontal,
              itemCount: flashListings.length,
              separatorBuilder: (_, __) => const SizedBox(width: 10),
              itemBuilder: (context, idx) {
                final item = flashListings[idx];
                return _buildFlashItemCard(context, ref, item);
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFlashItemCard(
    BuildContext context,
    WidgetRef ref,
    MarketListingModel item,
  ) {
    return Container(
      width: 130,
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: const Color(0xFFFDE68A)),
      ),
      child: InkWell(
        onTap: () => context.push('/marketplace/${item.id}'),
        borderRadius: BorderRadius.circular(10),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            ClipRRect(
              borderRadius: const BorderRadius.vertical(top: Radius.circular(9)),
              child: SizedBox(
                height: 75,
                width: double.infinity,
                child: _buildItemImage(item.imageUrl),
              ),
            ),
            Padding(
              padding: const EdgeInsets.all(6),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    item.commodity,
                    style: const TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w800,
                      color: Color(0xFF0F172A),
                    ),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 2),
                  Text(
                    _formatPrice(item.pricePerUnit),
                    style: const TextStyle(
                      fontSize: 11.5,
                      fontWeight: FontWeight.w900,
                      color: Color(0xFFEA580C),
                    ),
                  ),
                  const SizedBox(height: 4),
                  // Progress stock bar
                  ClipRRect(
                    borderRadius: BorderRadius.circular(4),
                    child: LinearProgressIndicator(
                      value: 0.75,
                      minHeight: 4,
                      backgroundColor: const Color(0xFFF1F5F9),
                      color: const Color(0xFFEA580C),
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    'Stok ${(item.quantity / 1000).toStringAsFixed(1)} Ton',
                    style: const TextStyle(
                      fontSize: 9,
                      fontWeight: FontWeight.w700,
                      color: Color(0xFF64748B),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  // --- Live Shipment Tracker (Jika ada kontrak) ---
  Widget _buildShipmentTracker(BuildContext context, dynamic contract) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: const Color(0xFFECFDF5),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFA7F3D0)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Icon(Icons.local_shipping_rounded, color: Color(0xFF059669), size: 16),
              const SizedBox(width: 6),
              const Text(
                'Pesanan & Timbangan Sawah Berjalan',
                style: TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w900,
                  color: Color(0xFF065F46),
                ),
              ),
              const Spacer(),
              InkWell(
                onTap: () => context.push('/buyer/orders'),
                child: const Text(
                  'Lacak >',
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w800,
                    color: Color(0xFF059669),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 6),
          Text(
            '${contract.commodity ?? "Gabah Panen"} • ${contract.quantity} ${contract.unit ?? "kg"}',
            style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: Color(0xFF0F172A)),
          ),
          Text(
            'Petani: ${contract.farmerName ?? "Mitra P.A.D.I."} • Siap Timbang & Jemput',
            style: const TextStyle(fontSize: 10.5, color: Color(0xFF047857)),
          ),
        ],
      ),
    );
  }

  // --- Feed Filter Tabs ---
  Widget _buildMarketplaceFeedTabs(AppStrings s) {
    final tabs = [s.tabRecommended, s.tabBestSelling, s.tabNearYou, s.tabWholesale];

    return SizedBox(
      height: 34,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        itemCount: tabs.length,
        separatorBuilder: (_, __) => const SizedBox(width: 8),
        itemBuilder: (context, idx) {
          final t = tabs[idx];
          final isSelected = _selectedTab == t;

          return ChoiceChip(
            label: Text(t),
            selected: isSelected,
            onSelected: (val) {
              if (val) setState(() => _selectedTab = t);
            },
            selectedColor: const Color(0xFF0F5132),
            backgroundColor: Colors.white,
            labelStyle: TextStyle(
              fontSize: 11.5,
              fontWeight: FontWeight.w800,
              color: isSelected ? Colors.white : const Color(0xFF475569),
            ),
            side: BorderSide(
              color: isSelected ? const Color(0xFF0F5132) : const Color(0xFFE2E8F0),
            ),
          );
        },
      ),
    );
  }

  List<MarketListingModel> _filterListings(List<MarketListingModel> listings) {
    if (_selectedTab == 'Tonase Besar') {
      return listings.where((l) => l.quantity >= 1000).toList();
    }
    return listings;
  }

  // --- 2-Column Product Card (Tokopedia/Shopee Style) ---
  Widget _buildProductCard(
    BuildContext context,
    WidgetRef ref,
    MarketListingModel item,
  ) {
    final isWholesale = item.quantity >= 1000;

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.02),
            blurRadius: 6,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: InkWell(
        onTap: () => context.push('/marketplace/${item.id}'),
        borderRadius: BorderRadius.circular(12),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            // Product Image & Badges
            Stack(
              children: [
                ClipRRect(
                  borderRadius: const BorderRadius.vertical(top: Radius.circular(11)),
                  child: SizedBox(
                    height: 110,
                    width: double.infinity,
                    child: _buildItemImage(item.imageUrl),
                  ),
                ),
                Positioned(
                  top: 6,
                  left: 6,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 2),
                    decoration: BoxDecoration(
                      color: const Color(0xFF0F5132),
                      borderRadius: BorderRadius.circular(4),
                    ),
                    child: const Text(
                      'MITRA RESMI',
                      style: TextStyle(
                        fontSize: 8,
                        fontWeight: FontWeight.w900,
                        color: Colors.white,
                      ),
                    ),
                  ),
                ),
                if (isWholesale)
                  Positioned(
                    bottom: 6,
                    right: 6,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 2),
                      decoration: BoxDecoration(
                        color: const Color(0xFFF59E0B),
                        borderRadius: BorderRadius.circular(4),
                      ),
                      child: const Text(
                        'PARTAI BESAR',
                        style: TextStyle(
                          fontSize: 8,
                          fontWeight: FontWeight.w900,
                          color: Colors.black,
                        ),
                      ),
                    ),
                  ),
              ],
            ),

            // Product Details
            Padding(
              padding: const EdgeInsets.fromLTRB(8, 6, 8, 8),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(
                    item.commodity,
                    style: const TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w800,
                      color: Color(0xFF0F172A),
                      height: 1.25,
                    ),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 4),
                  Text(
                    '${_formatPrice(item.pricePerUnit)} / ${item.unit}',
                    style: const TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w900,
                      color: Color(0xFF0F5132),
                    ),
                  ),
                  const SizedBox(height: 3),
                  Row(
                    children: [
                      const Icon(Icons.star_rounded, size: 13, color: Color(0xFFF59E0B)),
                      const SizedBox(width: 2),
                      const Text(
                        '4.9',
                        style: TextStyle(fontSize: 10, fontWeight: FontWeight.w800, color: Color(0xFF0F172A)),
                      ),
                      const SizedBox(width: 4),
                      Text(
                        '• Terjual ${(item.quantity / 1000).toStringAsFixed(1)} Ton',
                        style: const TextStyle(fontSize: 10, color: Color(0xFF64748B)),
                      ),
                    ],
                  ),
                  const SizedBox(height: 3),
                  Row(
                    children: [
                      const Icon(Icons.location_on_outlined, size: 11, color: Color(0xFF64748B)),
                      const SizedBox(width: 2),
                      Expanded(
                        child: Text(
                          item.farmName ?? 'Sawah Jawa Barat',
                          style: const TextStyle(fontSize: 9.5, color: Color(0xFF64748B)),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 6),

                  // Quick "+ Keranjang" Button
                  SizedBox(
                    width: double.infinity,
                    height: 28,
                    child: OutlinedButton.icon(
                      onPressed: () {
                        ref.read(cartProvider.notifier).addItem(
                              item,
                              quantity: item.quantity >= 500 ? 500 : 100,
                            );
                      },
                      icon: const Icon(Icons.add_shopping_cart_rounded, size: 12),
                      label: const Text(
                        '+ Keranjang',
                        style: TextStyle(fontSize: 10, fontWeight: FontWeight.w800),
                      ),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: const Color(0xFF0F5132),
                        side: const BorderSide(color: Color(0xFF0F5132)),
                        padding: EdgeInsets.zero,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(6),
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }


  // --- Trust Badges Footer ---
  Widget _buildTrustBadges() {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: const Row(
        mainAxisAlignment: MainAxisAlignment.spaceAround,
        children: [
          _TrustItem(icon: Icons.scale_rounded, label: 'Tera Resmi'),
          _TrustItem(icon: Icons.security_rounded, label: 'Bebas Calo'),
          _TrustItem(icon: Icons.local_shipping_rounded, label: 'Armada Truk'),
          _TrustItem(icon: Icons.receipt_long_rounded, label: 'Nota Legal'),
        ],
      ),
    );
  }

  Widget _buildEmptyState() {
    return Container(
      padding: const EdgeInsets.all(32),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: const Center(
        child: Text(
          'Tidak ada hasil panen yang sesuai filter saat ini.',
          style: TextStyle(fontSize: 12.5, color: Color(0xFF64748B)),
        ),
      ),
    );
  }

  void _showLocationSelectorModal(BuildContext context) {
    showModalBottomSheet(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(18)),
      ),
      builder: (ctx) => SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'Pilih Lokasi Gudang / Pengambilan',
                style: TextStyle(fontSize: 15, fontWeight: FontWeight.w900),
              ),
              const SizedBox(height: 12),
              _locationTile('Gudang Utama Subang (Jawa Barat)'),
              _locationTile('Gudang Pengeringan Indramayu'),
              _locationTile('Pabrik Beras Karawang'),
              _locationTile('Gudang Sourcing Cianjur'),
            ],
          ),
        ),
      ),
    );
  }

  Widget _locationTile(String loc) {
    return ListTile(
      leading: const Icon(Icons.location_on_rounded, color: Color(0xFF0F5132)),
      title: Text(loc, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700)),
      onTap: () {
        setState(() => _deliveryLocation = loc);
        Navigator.pop(context);
      },
    );
  }

  Widget _buildItemImage(String? imageUrl) {
    final cleanUrl = imageUrl?.trim() ?? '';
    final isValidHttp =
        cleanUrl.startsWith('http://') || cleanUrl.startsWith('https://');

    if (!isValidHttp) {
      return Image.asset('assets/images/onboarding_3.jpeg', fit: BoxFit.cover);
    }

    return Image.network(
      cleanUrl,
      fit: BoxFit.cover,
      errorBuilder: (context, error, stackTrace) =>
          Image.asset('assets/images/onboarding_3.jpeg', fit: BoxFit.cover),
    );
  }
}

class _TrustItem extends StatelessWidget {
  const _TrustItem({required this.icon, required this.label});
  final IconData icon;
  final String label;

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Icon(icon, size: 18, color: const Color(0xFF0F5132)),
        const SizedBox(height: 3),
        Text(
          label,
          style: const TextStyle(fontSize: 9.5, fontWeight: FontWeight.w700, color: Color(0xFF475569)),
        ),
      ],
    );
  }
}

// --- Tokopedia / Shopee Sticky Header Delegate ---
class _MarketplaceHeaderDelegate extends SliverPersistentHeaderDelegate {
  _MarketplaceHeaderDelegate({
    required this.cartCount,
    required this.onSearchSubmitted,
    required this.onCartTap,
    required this.onNotificationTap,
    required this.s,
  });

  final int cartCount;
  final ValueChanged<String> onSearchSubmitted;
  final VoidCallback onCartTap;
  final VoidCallback onNotificationTap;
  final AppStrings s;
  final Debouncer _debouncer = Debouncer(milliseconds: 400);

  @override
  double get minExtent => 64.0;
  @override
  double get maxExtent => 64.0;

  @override
  Widget build(
    BuildContext context,
    double shrinkOffset,
    bool overlapsContent,
  ) {
    return Container(
      height: 64,
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      decoration: BoxDecoration(
        color: Colors.white,
        border: Border(bottom: BorderSide(color: Colors.grey.withOpacity(0.18))),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.04),
            blurRadius: 4,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Row(
        children: [
          // Tokopedia Search Input with Debounce
          Expanded(
            child: Container(
              height: 42,
              decoration: BoxDecoration(
                color: const Color(0xFFF1F5F9),
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: const Color(0xFFE2E8F0)),
              ),
              child: TextField(
                onChanged: (val) => _debouncer.run(() => onSearchSubmitted(val)),
                onSubmitted: (val) {
                  _debouncer.cancel();
                  onSearchSubmitted(val);
                },
                style: const TextStyle(fontSize: 12.5),
                decoration: InputDecoration(
                  hintText: s.searchPaddyPlaceholder,
                  hintStyle: const TextStyle(fontSize: 12, color: Color(0xFF94A3B8)),
                  prefixIcon: const Icon(Icons.search_rounded, size: 20, color: Color(0xFF0F5132)),
                  border: InputBorder.none,
                  contentPadding: const EdgeInsets.symmetric(vertical: 11),
                ),
              ),
            ),
          ),

          const SizedBox(width: 8),

          // Chat / WhatsApp Icon
          IconButton(
            tooltip: 'Pesan & Bantuan',
            icon: const Icon(Icons.chat_outlined, color: Color(0xFF334155), size: 22),
            onPressed: () => context.push('/notifications'),
          ),

          // Shopping Cart with Dynamic Badge
          Stack(
            clipBehavior: Clip.none,
            children: [
              IconButton(
                tooltip: 'Keranjang Belanja',
                icon: const Icon(Icons.shopping_cart_outlined, color: Color(0xFF0F5132), size: 23),
                onPressed: onCartTap,
              ),
              if (cartCount > 0)
                Positioned(
                  top: 4,
                  right: 4,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 1),
                    decoration: const BoxDecoration(
                      color: Color(0xFFEF4444),
                      shape: BoxShape.circle,
                    ),
                    constraints: const BoxConstraints(minWidth: 16, minHeight: 16),
                    child: Text(
                      '$cartCount',
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 9,
                        fontWeight: FontWeight.w900,
                      ),
                      textAlign: TextAlign.center,
                    ),
                  ),
                ),
            ],
          ),
        ],
      ),
    );
  }

  @override
  bool shouldRebuild(covariant _MarketplaceHeaderDelegate oldDelegate) {
    return oldDelegate.cartCount != cartCount;
  }
}
