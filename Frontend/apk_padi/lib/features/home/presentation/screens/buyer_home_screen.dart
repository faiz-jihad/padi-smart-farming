import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';

import 'package:padi/features/cart/presentation/providers/cart_providers.dart';
import 'package:padi/features/marketplace/data/models/market_listing_model.dart';
import 'package:padi/features/marketplace/presentation/screens/marketplace_screen.dart';

// ============================================================
// GLOBAL COLORS
// ============================================================

const Color primaryGreen = Color(0xFF0F5132);
const Color lightGreen = Color(0xFFE8F8EF);
const Color background = Color(0xFFF8FAFC);

const Color textDark = Color(0xFF0F172A);
const Color textGrey = Color(0xFF64748B);
const Color borderColor = Color(0xFFE2E8F0);

// ============================================================
// ASSET
// ============================================================

const String padiLogo = 'assets/images/padi-logo.png';

// ============================================================
// BUYER HOME
// ============================================================

class BuyerHomeScreen extends ConsumerStatefulWidget {
  const BuyerHomeScreen({super.key});

  @override
  ConsumerState<BuyerHomeScreen> createState() =>
      _BuyerHomeScreenState();
}

class _BuyerHomeScreenState
    extends ConsumerState<BuyerHomeScreen> {
  String _selectedTab = 'Rekomendasi';

  // ============================================================
  // FORMAT PRICE
  // ============================================================

  String _formatPrice(num value) {
    final formatter = NumberFormat.currency(
      locale: 'id_ID',
      symbol: 'Rp ',
      decimalDigits: 0,
    );

    return formatter.format(value);
  }

  // ============================================================
  // OPEN MARKETPLACE WITH CATEGORY
  // ============================================================

  void _openMarketplaceCategory(
    BuildContext context,
    String category,
  ) {
    final uri = Uri(
      path: '/marketplace',
      queryParameters: {
        'category': category,
      },
    );

    context.push(uri.toString());
  }

  // ============================================================
  // BUILD
  // ============================================================

  @override
  Widget build(BuildContext context) {
    final cartState = ref.watch(cartProvider);

    final listingsAsync =
        ref.watch(marketplaceListingsProvider);

    return Scaffold(
      backgroundColor: background,

      body: SafeArea(
        child: RefreshIndicator(
          color: primaryGreen,
          backgroundColor: Colors.white,

          onRefresh: () async {
            ref.invalidate(
              marketplaceListingsProvider,
            );

            await Future<void>.delayed(
              const Duration(
                milliseconds: 300,
              ),
            );
          },

          child: CustomScrollView(
            physics:
                const AlwaysScrollableScrollPhysics(
              parent: BouncingScrollPhysics(),
            ),

            slivers: [
              // ==================================================
              // HEADER
              // ==================================================

              SliverPersistentHeader(
                pinned: true,

                delegate:
                    _BuyerHomeHeaderDelegate(
                  cartCount:
                      cartState.totalCount,

                  onCartTap: () {
                    context.push('/cart');
                  },

                  onNotificationTap: () {
                    context.push(
                      '/notifications',
                    );
                  },
                ),
              ),

              // ==================================================
              // CONTENT
              // ==================================================

              SliverToBoxAdapter(
                child: Padding(
                  padding:
                      const EdgeInsets.fromLTRB(
                    16,
                    10,
                    16,
                    110,
                  ),

                  child: Column(
                    crossAxisAlignment:
                        CrossAxisAlignment.start,

                    children: [
                      // =================================================
                      // CATEGORY
                      // =================================================

                      _buildQuickCategoryGrid(
                        context,
                      ),

                      const SizedBox(
                        height: 16,
                      ),

                      // =================================================
                      // BURSA PANEN KILAT
                      // =================================================

                      listingsAsync.when(
                        data: (listings) {
                          return _buildFlashSaleSection(
                            context,
                            listings,
                          );
                        },

                        loading: () {
                          return const SizedBox.shrink();
                        },

                        error: (_, __) {
                          return const SizedBox.shrink();
                        },
                      ),

                      const SizedBox(
                        height: 16,
                      ),

                      // =================================================
                      // FILTER FEED
                      // =================================================

                      _buildMarketplaceFeedTabs(),

                      const SizedBox(
                        height: 12,
                      ),

                      // =================================================
                      // PRODUCT
                      // =================================================

                      listingsAsync.when(
                        data: (listings) {
                          final filtered =
                              _filterListings(
                            listings,
                          );

                          if (filtered.isEmpty) {
                            return _buildEmptyState();
                          }

                          return GridView.builder(
                            shrinkWrap: true,

                            physics:
                                const NeverScrollableScrollPhysics(),

                            itemCount:
                                filtered.length,

                            gridDelegate:
                                const SliverGridDelegateWithFixedCrossAxisCount(
                              crossAxisCount: 2,
                              crossAxisSpacing: 10,
                              mainAxisSpacing: 10,
                              mainAxisExtent: 285,
                            ),

                            itemBuilder:
                                (context, index) {
                              return _buildProductCard(
                                context,
                                filtered[index],
                              );
                            },
                          );
                        },

                        loading: () {
                          return const Padding(
                            padding:
                                EdgeInsets.all(40),

                            child: Center(
                              child:
                                  CircularProgressIndicator(
                                color:
                                    primaryGreen,
                              ),
                            ),
                          );
                        },

                        error: (
                          error,
                          stackTrace,
                        ) {
                          return _buildErrorState();
                        },
                      ),
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

  // ============================================================
  // CATEGORY
  // ============================================================

  Widget _buildQuickCategoryGrid(
  BuildContext context,
) {
  final categories = [
    // =========================================================
    // GKP PANEN
    // =========================================================
    _BuyerCategory(
      title: 'GKP Panen',
      icon: Icons.grass_rounded,
      backgroundColor: const Color(0xFFE8F8EF),
      iconColor: primaryGreen,
      category: 'gkp',
      route: '/marketplace',
    ),

    // =========================================================
    // GKG GILING
    // =========================================================
    _BuyerCategory(
      title: 'GKG Giling',
      icon: Icons.grain_rounded,
      backgroundColor: const Color(0xFFE8F8EF),
      iconColor: primaryGreen,
      category: 'gkg',
      route: '/marketplace',
    ),

    // =========================================================
    // BERAS PREMIUM
    // =========================================================
    _BuyerCategory(
      title: 'Beras Premium',
      icon: Icons.rice_bowl_rounded,
      backgroundColor: const Color(0xFFE8F8EF),
      iconColor: primaryGreen,
      category: 'beras',
      route: '/marketplace',
    ),

    // =========================================================
    // BENIH BERSERTIFIKAT
    // =========================================================
    _BuyerCategory(
      title: 'Benih Bersertifikat',
      icon: Icons.spa_rounded,
      backgroundColor: const Color(0xFFE8F8EF),
      iconColor: primaryGreen,
      category: 'benih',
      route: '/marketplace',
    ),

    // =========================================================
    // BURSA LELANG
    // =========================================================
    _BuyerCategory(
      title: 'Bursa Lelang',
      icon: Icons.gavel_rounded,
      backgroundColor: const Color(0xFFE8F8EF),
      iconColor: primaryGreen,
      category: '',
      route: '/marketplace/offers',
    ),

    // =========================================================
    // KONTRAK SAYA
    // =========================================================
    _BuyerCategory(
      title: 'Kontrak Saya',
      icon: Icons.receipt_long_rounded,
      backgroundColor: const Color(0xFFE8F8EF),
      iconColor: primaryGreen,
      category: '',
      route: '/buyer/orders',
    ),
  ];

  return Container(
    width: double.infinity,
    padding: const EdgeInsets.all(16),
    decoration: BoxDecoration(
      color: Colors.white,
      borderRadius: BorderRadius.circular(20),
      border: Border.all(
        color: borderColor,
      ),
    ),
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Kategori Hasil Panen',
          style: TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.w900,
            color: textDark,
          ),
        ),

        const SizedBox(height: 14),

        GridView.builder(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          itemCount: categories.length,

          gridDelegate:
              const SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: 3,
            mainAxisExtent: 92,
            crossAxisSpacing: 4,
            mainAxisSpacing: 8,
          ),

          itemBuilder: (
            context,
            index,
          ) {
            final category = categories[index];

            return InkWell(
              borderRadius: BorderRadius.circular(12),

              onTap: () {
                // =================================================
                // LELANG
                // =================================================

                if (category.title == 'Bursa Lelang') {
                  context.push(
                    '/marketplace/offers',
                  );
                  return;
                }

                // =================================================
                // KONTRAK
                // =================================================

                if (category.title == 'Kontrak Saya') {
                  context.push(
                    '/buyer/orders',
                  );
                  return;
                }

                // =================================================
                // MARKETPLACE + CATEGORY
                // =================================================

                context.push(
                  Uri(
                    path: category.route,
                    queryParameters: {
                      'category': category.category,
                    },
                  ).toString(),
                );
              },

              child: Column(
                mainAxisAlignment:
                    MainAxisAlignment.center,

                children: [
                  Container(
                    width: 52,
                    height: 52,
                    decoration: const BoxDecoration(
                      color: lightGreen,
                      shape: BoxShape.circle,
                    ),

                    child: Icon(
                      category.icon,
                      size: 25,
                      color: category.iconColor,
                    ),
                  ),

                  const SizedBox(height: 6),

                  Text(
                    category.title,
                    textAlign: TextAlign.center,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,

                    style: const TextStyle(
                      fontSize: 10.5,
                      fontWeight: FontWeight.w700,
                      color: Color(0xFF334155),
                    ),
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

  // ============================================================
  // FLASH SALE
  // ============================================================

  Widget _buildFlashSaleSection(
    BuildContext context,
    List<MarketListingModel> listings,
  ) {
    if (listings.isEmpty) {
      return const SizedBox.shrink();
    }

    final flashListings =
        listings.take(4).toList();

    return Container(
      width: double.infinity,

      padding:
          const EdgeInsets.all(12),

      decoration: BoxDecoration(
        color:
            const Color(0xFFFFFBEB),

        borderRadius:
            BorderRadius.circular(16),

        border: Border.all(
          color:
              const Color(0xFFFDE68A),
        ),
      ),

      child: Column(
        crossAxisAlignment:
            CrossAxisAlignment.start,

        children: [
          Row(
            children: [
              const Icon(
                Icons.bolt_rounded,
                color:
                    Color(0xFFD97706),
                size: 21,
              ),

              const SizedBox(
                width: 6,
              ),

              const Expanded(
                child: Text(
                  'Bursa Panen Kilat',

                  style: TextStyle(
                    fontSize: 15,
                    fontWeight:
                        FontWeight.w900,
                    color:
                        Color(0xFF92400E),
                  ),
                ),
              ),

              TextButton(
                onPressed: () {
                  context.push(
                    '/marketplace',
                  );
                },

                child: const Text(
                  'Lihat Semua',
                ),
              ),
            ],
          ),

          const SizedBox(
            height: 8,
          ),

          SizedBox(
            height: 195,

            child:
                ListView.separated(
              scrollDirection:
                  Axis.horizontal,

              itemCount:
                  flashListings.length,

              separatorBuilder:
                  (context, index) {
                return const SizedBox(
                  width: 10,
                );
              },

              itemBuilder:
                  (context, index) {
                return _buildFlashCard(
                  context,
                  flashListings[index],
                );
              },
            ),
          ),
        ],
      ),
    );
  }

  // ============================================================
  // FLASH CARD
  // ============================================================

  Widget _buildFlashCard(
    BuildContext context,
    MarketListingModel listing,
  ) {
    return SizedBox(
      width: 145,

      child: InkWell(
        borderRadius:
            BorderRadius.circular(12),

        onTap: () {
          context.push(
            '/marketplace/${listing.id}',
          );
        },

        child: Container(
          decoration:
              BoxDecoration(
            color: Colors.white,

            borderRadius:
                BorderRadius.circular(12),

            border: Border.all(
              color:
                  const Color(0xFFFDE68A),
            ),
          ),

          clipBehavior:
              Clip.antiAlias,

          child: Column(
            crossAxisAlignment:
                CrossAxisAlignment.start,

            children: [
              SizedBox(
                width:
                    double.infinity,

                height: 85,

                child:
                    _buildItemImage(
                  listing.imageUrl,
                ),
              ),

              Padding(
                padding:
                    const EdgeInsets.all(
                  8,
                ),

                child: Column(
                  crossAxisAlignment:
                      CrossAxisAlignment
                          .start,

                  children: [
                    Text(
                      listing.commodity,

                      maxLines: 2,

                      overflow:
                          TextOverflow.ellipsis,

                      style:
                          const TextStyle(
                        fontSize: 11,
                        fontWeight:
                            FontWeight.w800,
                        color:
                            Color(0xFF1E293B),
                      ),
                    ),

                    const SizedBox(
                      height: 5,
                    ),

                    Text(
                      _formatPrice(
                        listing
                            .pricePerUnit,
                      ),

                      style:
                          const TextStyle(
                        fontSize: 13,
                        fontWeight:
                            FontWeight.w900,
                        color:
                            Color(0xFFEA580C),
                      ),
                    ),

                    const SizedBox(
                      height: 3,
                    ),

                    Text(
                      '/ ${listing.unit}',

                      style:
                          const TextStyle(
                        fontSize: 9.5,
                        color:
                            Color(0xFF64748B),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  // ============================================================
  // FEED TABS
  // ============================================================

  Widget _buildMarketplaceFeedTabs() {
    const tabs = [
      'Rekomendasi',
      'Terbaru',
      'Harga Terendah',
    ];

    return SizedBox(
      height: 42,

      child:
          ListView.separated(
        scrollDirection:
            Axis.horizontal,

        itemCount:
            tabs.length,

        separatorBuilder:
            (context, index) {
          return const SizedBox(
            width: 8,
          );
        },

        itemBuilder:
            (context, index) {
          final tab =
              tabs[index];

          final selected =
              _selectedTab == tab;

          return ChoiceChip(
            label: Text(tab),

            selected: selected,

            onSelected: (_) {
              setState(() {
                _selectedTab = tab;
              });
            },

            selectedColor:
                primaryGreen,

            backgroundColor:
                Colors.white,

            labelStyle:
                TextStyle(
              color: selected
                  ? Colors.white
                  : const Color(
                      0xFF475569,
                    ),

              fontWeight:
                  FontWeight.w700,

              fontSize: 11,
            ),

            side: BorderSide(
              color: selected
                  ? primaryGreen
                  : borderColor,
            ),
          );
        },
      ),
    );
  }

  // ============================================================
  // FILTER LISTINGS
  // ============================================================

  List<MarketListingModel>
      _filterListings(
    List<MarketListingModel>
        listings,
  ) {
    final result =
        List<MarketListingModel>.from(
      listings,
    );

    switch (_selectedTab) {
      case 'Terbaru':
        result.sort(
          (a, b) =>
              b.id.compareTo(a.id),
        );
        break;

      case 'Harga Terendah':
        result.sort(
          (a, b) =>
              a.pricePerUnit.compareTo(
            b.pricePerUnit,
          ),
        );
        break;

      case 'Rekomendasi':
      default:
        break;
    }

    return result;
  }

  // ============================================================
  // PRODUCT CARD
  // ============================================================

  Widget _buildProductCard(
    BuildContext context,
    MarketListingModel listing,
  ) {
    return InkWell(
      borderRadius:
          BorderRadius.circular(14),

      onTap: () {
        context.push(
          '/marketplace/${listing.id}',
        );
      },

      child: Container(
        decoration:
            BoxDecoration(
          color: Colors.white,

          borderRadius:
              BorderRadius.circular(14),

          border: Border.all(
            color: borderColor,
          ),
        ),

        clipBehavior:
            Clip.antiAlias,

        child: Column(
          crossAxisAlignment:
              CrossAxisAlignment.start,

          children: [
            // ==================================================
            // IMAGE
            // ==================================================

            SizedBox(
              width:
                  double.infinity,

              height: 145,

              child: Stack(
                children: [
                  Positioned.fill(
                    child:
                        _buildItemImage(
                      listing.imageUrl,
                    ),
                  ),

                  Positioned(
                    top: 8,
                    left: 8,

                    child: Container(
                      padding:
                          const EdgeInsets
                              .symmetric(
                        horizontal: 7,
                        vertical: 4,
                      ),

                      decoration:
                          BoxDecoration(
                        color:
                            primaryGreen,

                        borderRadius:
                            BorderRadius
                                .circular(
                          6,
                        ),
                      ),

                      child:
                          const Text(
                        'MITRA RESMI',

                        style:
                            TextStyle(
                          color:
                              Colors.white,
                          fontSize: 8,
                          fontWeight:
                              FontWeight.w800,
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),

            // ==================================================
            // INFORMATION
            // ==================================================

            Expanded(
              child: Padding(
                padding:
                    const EdgeInsets.all(
                  10,
                ),

                child: Column(
                  crossAxisAlignment:
                      CrossAxisAlignment
                          .start,

                  children: [
                    Text(
                      listing.commodity,

                      maxLines: 2,

                      overflow:
                          TextOverflow.ellipsis,

                      style:
                          const TextStyle(
                        fontSize: 13,
                        fontWeight:
                            FontWeight.w800,
                        color:
                            Color(0xFF1E293B),
                      ),
                    ),

                    const SizedBox(
                      height: 7,
                    ),

                    Text(
                      _formatPrice(
                        listing
                            .pricePerUnit,
                      ),

                      style:
                          const TextStyle(
                        fontSize: 16,
                        fontWeight:
                            FontWeight.w900,
                        color:
                            primaryGreen,
                      ),
                    ),

                    const SizedBox(
                      height: 3,
                    ),

                    Text(
                      '/ ${listing.unit}',

                      style:
                          const TextStyle(
                        fontSize: 10,
                        color:
                            Color(0xFF64748B),
                      ),
                    ),

                    const Spacer(),

                    Row(
                      children: [
                        const Icon(
                          Icons
                              .inventory_2_outlined,
                          size: 14,
                          color:
                              Color(0xFF64748B),
                        ),

                        const SizedBox(
                          width: 4,
                        ),

                        Expanded(
                          child: Text(
                            'Stok ${_formatStock(listing)}',

                            maxLines: 1,

                            overflow:
                                TextOverflow
                                    .ellipsis,

                            style:
                                const TextStyle(
                              fontSize: 10,
                              fontWeight:
                                  FontWeight.w600,
                              color:
                                  Color(
                                0xFF64748B,
                              ),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ============================================================
  // EMPTY STATE
  // ============================================================

  Widget _buildEmptyState() {
    return Container(
      width: double.infinity,

      padding:
          const EdgeInsets.all(30),

      decoration:
          BoxDecoration(
        color: Colors.white,

        borderRadius:
            BorderRadius.circular(14),

        border: Border.all(
          color: borderColor,
        ),
      ),

      child: Column(
        children: [
          const Icon(
            Icons.inventory_2_outlined,
            size: 42,
            color:
                Color(0xFF94A3B8),
          ),

          const SizedBox(
            height: 12,
          ),

          const Text(
            'Belum Ada Hasil Panen',

            style: TextStyle(
              fontSize: 15,
              fontWeight:
                  FontWeight.w800,
              color:
                  Color(0xFF334155),
            ),
          ),

          const SizedBox(
            height: 5,
          ),

          const Text(
            'Belum ada produk yang tersedia di marketplace.',

            textAlign:
                TextAlign.center,

            style: TextStyle(
              fontSize: 11,
              color:
                  Color(0xFF64748B),
            ),
          ),
        ],
      ),
    );
  }

  // ============================================================
  // ERROR STATE
  // ============================================================

  Widget _buildErrorState() {
    return Container(
      width: double.infinity,

      padding:
          const EdgeInsets.all(24),

      decoration:
          BoxDecoration(
        color: Colors.white,

        borderRadius:
            BorderRadius.circular(14),

        border: Border.all(
          color: borderColor,
        ),
      ),

      child: Column(
        children: [
          const Icon(
            Icons.cloud_off_rounded,
            size: 40,
            color:
                Color(0xFF94A3B8),
          ),

          const SizedBox(
            height: 10,
          ),

          const Text(
            'Gagal Memuat Marketplace',

            style: TextStyle(
              fontSize: 14,
              fontWeight:
                  FontWeight.w800,
              color:
                  Color(0xFF334155),
            ),
          ),

          const SizedBox(
            height: 10,
          ),

          OutlinedButton.icon(
            onPressed: () {
              ref.invalidate(
                marketplaceListingsProvider,
              );
            },

            icon: const Icon(
              Icons.refresh_rounded,
            ),

            label: const Text(
              'Coba Lagi',
            ),

            style:
                OutlinedButton.styleFrom(
              foregroundColor:
                  primaryGreen,

              side:
                  const BorderSide(
                color:
                    primaryGreen,
              ),
            ),
          ),
        ],
      ),
    );
  }

  // ============================================================
  // ITEM IMAGE
  // ============================================================

  Widget _buildItemImage(
    String? imageUrl,
  ) {
    if (imageUrl == null ||
        imageUrl.trim().isEmpty) {
      return Container(
        color: lightGreen,

        child: const Center(
          child: Icon(
            Icons.agriculture_rounded,
            size: 42,
            color:
                primaryGreen,
          ),
        ),
      );
    }

    return Image.network(
      imageUrl,

      fit: BoxFit.cover,

      errorBuilder: (
        context,
        error,
        stackTrace,
      ) {
        return Container(
          color: lightGreen,

          child: const Center(
            child: Icon(
              Icons.broken_image_outlined,
              size: 36,
              color:
                  primaryGreen,
            ),
          ),
        );
      },
    );
  }

  // ============================================================
  // STOCK
  // ============================================================

  String _formatStock(
    MarketListingModel listing,
  ) {
    final quantity =
        listing.quantity;

    if (quantity % 1 == 0) {
      return '${quantity.toInt()} ${listing.unit}';
    }

    return '$quantity ${listing.unit}';
  }
}

// ================================================================
// CATEGORY MODEL
// ================================================================

class _BuyerCategory {
  const _BuyerCategory({
    required this.title,
    required this.icon,
    required this.backgroundColor,
    required this.iconColor,
    required this.category,
    required this.route,
  });

  final String title;
  final IconData icon;
  final Color backgroundColor;
  final Color iconColor;

  // Nilai yang dikirim ke MarketplaceScreen
  final String category;

  // Route khusus untuk lelang / kontrak
  final String route;
}

// ================================================================
// BUYER HOME HEADER
// ================================================================

class _BuyerHomeHeaderDelegate
    extends SliverPersistentHeaderDelegate {
  const _BuyerHomeHeaderDelegate({
    required this.cartCount,
    required this.onCartTap,
    required this.onNotificationTap,
  });

  final int cartCount;

  final VoidCallback onCartTap;

  final VoidCallback onNotificationTap;

  @override
  double get minExtent => 72;

  @override
  double get maxExtent => 72;

  @override
  Widget build(
    BuildContext context,
    double shrinkOffset,
    bool overlapsContent,
  ) {
    return Material(
      color: Colors.white,

      elevation:
          overlapsContent ? 2 : 0,

      child: Container(
        height: 72,

        padding:
            const EdgeInsets.symmetric(
          horizontal: 16,
        ),

        decoration:
            const BoxDecoration(
          color: Colors.white,

          border: Border(
            bottom: BorderSide(
              color:
                  Color(0xFFE5E7EB),
              width: 1,
            ),
          ),
        ),

        child: Row(
          children: [
            // ==================================================
            // PADI LOGO
            // ==================================================

            SizedBox(
              width: 64,
              height: 54,

              child: Image.asset(
                padiLogo,

                fit: BoxFit.contain,

                errorBuilder: (
                  context,
                  error,
                  stackTrace,
                ) {
                  return const Icon(
                    Icons
                        .image_not_supported_outlined,
                    color:
                        Color(0xFF94A3B8),
                    size: 28,
                  );
                },
              ),
            ),

            const SizedBox(
              width: 8,
            ),

            // ==================================================
            // BRAND
            // ==================================================

            const Expanded(
              child: Column(
                mainAxisAlignment:
                    MainAxisAlignment.center,

                crossAxisAlignment:
                    CrossAxisAlignment.start,

                children: [
                  Text(
                    'PADI',

                    style: TextStyle(
                      color: textDark,

                      fontSize: 18,

                      fontWeight:
                          FontWeight.w900,

                      letterSpacing:
                          0.5,
                    ),
                  ),

                  Text(
                    'Marketplace',

                    style: TextStyle(
                      color: textGrey,

                      fontSize: 10,

                      fontWeight:
                          FontWeight.w600,
                    ),
                  ),
                ],
              ),
            ),

            // ==================================================
            // NOTIFICATION
            // ==================================================

            IconButton(
              onPressed:
                  onNotificationTap,

              tooltip:
                  'Notifikasi',

              splashRadius: 24,

              icon: const Icon(
                Icons
                    .notifications_none_rounded,

                color:
                    primaryGreen,

                size: 26,
              ),
            ),

            // ==================================================
            // CART
            // ==================================================

            Stack(
              clipBehavior:
                  Clip.none,

              children: [
                IconButton(
                  onPressed:
                      onCartTap,

                  tooltip:
                      'Keranjang',

                  splashRadius: 24,

                  icon: const Icon(
                    Icons
                        .shopping_cart_outlined,

                    color:
                        primaryGreen,

                    size: 27,
                  ),
                ),

                // ==================================================
                // CART BADGE
                // ==================================================

                if (cartCount > 0)
                  Positioned(
                    top: 2,
                    right: 0,

                    child: Container(
                      constraints:
                          const BoxConstraints(
                        minWidth: 18,
                        minHeight: 18,
                      ),

                      padding:
                          const EdgeInsets
                              .symmetric(
                        horizontal: 4,
                      ),

                      decoration:
                          const BoxDecoration(
                        color:
                            Color(0xFFEF4444),

                        shape:
                            BoxShape.circle,
                      ),

                      child: Text(
                        cartCount > 99
                            ? '99+'
                            : '$cartCount',

                        textAlign:
                            TextAlign.center,

                        style:
                            const TextStyle(
                          color:
                              Colors.white,

                          fontSize: 8,

                          fontWeight:
                              FontWeight.w900,
                        ),
                      ),
                    ),
                  ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  @override
  bool shouldRebuild(
    covariant
        _BuyerHomeHeaderDelegate
            oldDelegate,
  ) {
    return oldDelegate.cartCount !=
            cartCount ||
        oldDelegate.onCartTap !=
            onCartTap ||
        oldDelegate.onNotificationTap !=
            onNotificationTap;
  }
}