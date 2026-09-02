import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';

import 'package:padi/features/cart/presentation/providers/cart_providers.dart';
import 'package:padi/features/marketplace/data/models/market_listing_model.dart';
import 'package:padi/features/marketplace/presentation/screens/marketplace_screen.dart';

class BuyerHomeScreen extends ConsumerStatefulWidget {
  const BuyerHomeScreen({super.key});

  @override
  ConsumerState<BuyerHomeScreen> createState() =>
      _BuyerHomeScreenState();
}

class _BuyerHomeScreenState
    extends ConsumerState<BuyerHomeScreen> {

  // ============================================================
  // WARNA
  // ============================================================

  static const Color primaryGreen =
      Color(0xFF0F5132);

  static const Color lightGreen =
      Color(0xFFE8F8EF);

  static const Color background =
      Color(0xFFF8FAFC);

  // ============================================================
  // FORMAT HARGA
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
  // BUILD
  // ============================================================

  @override
  Widget build(BuildContext context) {
    final cartState = ref.watch(cartProvider);

    // ==========================================================
    // DATA HOME MENGAMBIL DATA YANG SAMA DENGAN MARKETPLACE
    // ==========================================================

    final listingsAsync = ref.watch(
      marketplaceListingsProvider,
    );

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
          },

          child: CustomScrollView(
            physics: const AlwaysScrollableScrollPhysics(
              parent: BouncingScrollPhysics(),
            ),

            slivers: [

              // ==================================================
              // HEADER
              // LOGO PADI KIRI
              // CHAT + KERANJANG KANAN
              // ==================================================

              SliverToBoxAdapter(
                child: _buildHeader(
                  context,
                  cartState.totalCount,
                ),
              ),

              // ==================================================
              // CONTENT HOME
              // ==================================================

              SliverToBoxAdapter(
                child: Padding(
                  padding: const EdgeInsets.fromLTRB(
                    16,
                    10,
                    16,
                    110,
                  ),

                  child: Column(
                    crossAxisAlignment:
                        CrossAxisAlignment.start,

                    children: [

                      // ==========================================
                      // KATEGORI 3 X 2
                      // ==========================================

                      _buildCategorySection(
                        context,
                      ),

                      const SizedBox(
                        height: 16,
                      ),

                      // ==========================================
                      // BURSA PANEN KILAT
                      // ==========================================

                      listingsAsync.when(
                        data: (listings) {
                          return _buildFlashSection(
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

                      // ==========================================
                      // PESANAN & TIMBANGAN
                      // ==========================================

                      _buildOrderTracking(),

                      const SizedBox(
                        height: 18,
                      ),

                      // ==========================================
                      // REKOMENDASI
                      //
                      // 1 PRODUK GKP
                      // 1 PRODUK GKG
                      // 1 PRODUK BERAS
                      // 1 PRODUK BENIH
                      // ==========================================

                      listingsAsync.when(
                        data: (listings) {
                          return _buildRecommendationSection(
                            context,
                            listings,
                          );
                        },

                        loading: () {
                          return const Center(
                            child: Padding(
                              padding:
                                  EdgeInsets.all(30),

                              child:
                                  CircularProgressIndicator(
                                color:
                                    primaryGreen,
                              ),
                            ),
                          );
                        },

                        error: (_, __) {
                          return const SizedBox.shrink();
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
  // HEADER
  // ============================================================

  Widget _buildHeader(
    BuildContext context,
    int cartCount,
  ) {
    return Container(
      height: 72,

      padding: const EdgeInsets.symmetric(
        horizontal: 16,
        vertical: 8,
      ),

      decoration: BoxDecoration(
        color: Colors.white,

        border: Border(
          bottom: BorderSide(
            color: Colors.grey.withOpacity(0.15),
          ),
        ),

        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.035),
            blurRadius: 4,
            offset: const Offset(0, 2),
          ),
        ],
      ),

      child: Row(
        children: [

          // ==================================================
          // LOGO PADI
          // ==================================================

          Expanded(
            child: Align(
              alignment: Alignment.centerLeft,

              child: Image.asset(
                'assets/images/padi-logo.png',

                height: 48,

                fit: BoxFit.contain,

                errorBuilder: (
                  context,
                  error,
                  stackTrace,
                ) {
                  return const Text(
                    'P.A.D.I.',
                    style: TextStyle(
                      fontSize: 20,
                      fontWeight:
                          FontWeight.w900,
                      color:
                          primaryGreen,
                    ),
                  );
                },
              ),
            ),
          ),

          // ==================================================
          // CHAT
          // ==================================================

          IconButton(
            tooltip: 'Pesan',

            onPressed: () {
              context.push(
                '/notifications',
              );
            },

            icon: const Icon(
              Icons.chat_bubble_outline_rounded,
              color: Color(0xFF334155),
              size: 24,
            ),
          ),

          // ==================================================
          // KERANJANG
          // ==================================================

          Stack(
            clipBehavior: Clip.none,

            children: [

              IconButton(
                tooltip: 'Keranjang',

                onPressed: () {
                  context.push('/cart');
                },

                icon: const Icon(
                  Icons.shopping_cart_outlined,
                  color: primaryGreen,
                  size: 25,
                ),
              ),

              if (cartCount > 0)
                Positioned(
                  right: 1,
                  top: 1,

                  child: Container(
                    constraints:
                        const BoxConstraints(
                      minWidth: 18,
                      minHeight: 18,
                    ),

                    padding:
                        const EdgeInsets.symmetric(
                      horizontal: 4,
                    ),

                    decoration:
                        const BoxDecoration(
                      color: Color(0xFFDC2626),
                      shape: BoxShape.circle,
                    ),

                    child: Center(
                      child: Text(
                        cartCount > 99
                            ? '99+'
                            : '$cartCount',

                        style:
                            const TextStyle(
                          color: Colors.white,
                          fontSize: 8,
                          fontWeight:
                              FontWeight.w900,
                        ),
                      ),
                    ),
                  ),
                ),
            ],
          ),
        ],
      ),
    );
  }

  // ============================================================
  // KATEGORI
  //
  // 3 ATAS
  // GKP | GKG | BERAS
  //
  // 3 BAWAH
  // BENIH | LELANG | KONTRAK
  // ============================================================

  Widget _buildCategorySection(
    BuildContext context,
  ) {
    final categories = [
      _HomeCategory(
        label: 'GKP Panen',
        icon: Icons.grass_rounded,
        category: 'gkp',
      ),

      _HomeCategory(
        label: 'GKG Giling',
        icon: Icons.grain_rounded,
        category: 'gkg',
      ),

      _HomeCategory(
        label: 'Beras Premium',
        icon: Icons.rice_bowl_rounded,
        category: 'beras',
      ),

      _HomeCategory(
        label: 'Benih Bersertifikat',
        icon: Icons.spa_rounded,
        category: 'benih',
      ),

      _HomeCategory(
        label: 'Bursa Lelang',
        icon: Icons.gavel_rounded,
        category: 'lelang',
      ),

      _HomeCategory(
        label: 'Kontrak Saya',
        icon: Icons.receipt_long_rounded,
        category: 'kontrak',
      ),
    ];

    return Container(
      width: double.infinity,

      padding: const EdgeInsets.fromLTRB(
        14,
        14,
        14,
        12,
      ),

      decoration: BoxDecoration(
        color: Colors.white,

        borderRadius:
            BorderRadius.circular(16),

        border: Border.all(
          color: const Color(0xFFE2E8F0),
        ),
      ),

      child: Column(
        crossAxisAlignment:
            CrossAxisAlignment.start,

        children: [

          // ================================================
          // JUDUL
          // ================================================

          const Text(
            'Kategori Hasil Panen',

            style: TextStyle(
              fontSize: 18,
              fontWeight:
                  FontWeight.w900,
              color:
                  Color(0xFF0F172A),
            ),
          ),

          const SizedBox(
            height: 14,
          ),

          // ================================================
          // GRID 3 X 2
          // ================================================

          GridView.builder(
            shrinkWrap: true,

            physics:
                const NeverScrollableScrollPhysics(),

            itemCount:
                categories.length,

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
              final category =
                  categories[index];

              return InkWell(
                borderRadius:
                    BorderRadius.circular(12),

                onTap: () {
                  _handleCategoryTap(
                    context,
                    category.category,
                  );
                },

                child: Column(
                  mainAxisAlignment:
                      MainAxisAlignment.center,

                  children: [

                    // ======================================
                    // ICON
                    // ======================================

                    Container(
                      width: 52,
                      height: 52,

                      decoration:
                          const BoxDecoration(
                        color: lightGreen,
                        shape:
                            BoxShape.circle,
                      ),

                      child: Icon(
                        category.icon,

                        size: 25,

                        color:
                            primaryGreen,
                      ),
                    ),

                    const SizedBox(
                      height: 6,
                    ),

                    // ======================================
                    // TEXT
                    // ======================================

                    Text(
                      category.label,

                      textAlign:
                          TextAlign.center,

                      maxLines: 2,

                      overflow:
                          TextOverflow.ellipsis,

                      style:
                          const TextStyle(
                        fontSize: 10.5,
                        fontWeight:
                            FontWeight.w700,
                        color:
                            Color(0xFF334155),
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
  // HANDLE CATEGORY
  // ============================================================

  void _handleCategoryTap(
  BuildContext context,
  String category,
) {
  switch (category) {
    case 'lelang':
      context.push('/marketplace/offers');
      return;

    case 'kontrak':
      context.push('/buyer/orders');
      return;

    case 'gkp':
    case 'gkg':
    case 'beras':
    case 'benih':
      context.push(
        Uri(
          path: '/marketplace',
          queryParameters: {
            'category': category,
          },
        ).toString(),
      );
      return;

    default:
      context.push('/marketplace');
  }
}

  // ============================================================
  // BURSA PANEN KILAT
  // ============================================================

  Widget _buildFlashSection(
    BuildContext context,
    List<MarketListingModel> listings,
  ) {
    if (listings.isEmpty) {
      return const SizedBox.shrink();
    }

    final flashListings =
        listings.take(3).toList();

    return Container(
      width: double.infinity,

      padding:
          const EdgeInsets.all(12),

      decoration: BoxDecoration(
        color:
            const Color(0xFFFFFBEB),

        borderRadius:
            BorderRadius.circular(14),

        border: Border.all(
          color:
              const Color(0xFFFDE68A),
        ),
      ),

      child: Column(
        crossAxisAlignment:
            CrossAxisAlignment.start,

        children: [

          // ================================================
          // HEADER
          // ================================================

          Row(
            children: [

              const Icon(
                Icons.local_fire_department_rounded,
                color:
                    Color(0xFFEA580C),
                size: 19,
              ),

              const SizedBox(
                width: 5,
              ),

              const Text(
                'BURSA PANEN KILAT',

                style: TextStyle(
                  fontSize: 13,
                  fontWeight:
                      FontWeight.w900,
                  color:
                      Color(0xFF9A3412),
                ),
              ),

              const Spacer(),

              InkWell(
                onTap: () {
                  context.push(
                    '/marketplace',
                  );
                },

                child: const Text(
                  'Lihat Semua >',

                  style: TextStyle(
                    fontSize: 11,
                    fontWeight:
                        FontWeight.w900,
                    color:
                        Color(0xFFEA580C),
                  ),
                ),
              ),
            ],
          ),

          const SizedBox(
            height: 10,
          ),

          // ================================================
          // PRODUK
          // ================================================

          SizedBox(
            height: 185,

            child: ListView.separated(
              scrollDirection:
                  Axis.horizontal,

              itemCount:
                  flashListings.length,

              separatorBuilder:
                  (_, __) {
                return const SizedBox(
                  width: 10,
                );
              },

              itemBuilder:
                  (context, index) {

                final listing =
                    flashListings[index];

                return _buildFlashCard(
                  context,
                  listing,
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
    return InkWell(
      borderRadius:
          BorderRadius.circular(12),

      onTap: () {
        context.push(
          '/marketplace/${listing.id}',
        );
      },

      child: Container(
        width: 132,

        decoration: BoxDecoration(
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

            // ==============================================
            // GAMBAR
            // ==============================================

            SizedBox(
              width: double.infinity,
              height: 85,

              child: _buildItemImage(
                listing.imageUrl,
              ),
            ),

            // ==============================================
            // INFORMASI
            // ==============================================

            Padding(
              padding:
                  const EdgeInsets.all(8),

              child: Column(
                crossAxisAlignment:
                    CrossAxisAlignment.start,

                children: [

                  Text(
                    listing.commodity
                        .toString(),

                    maxLines: 1,

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
                      listing.pricePerUnit,
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
                    height: 5,
                  ),

                  Text(
                    'Stok ${_formatStock(listing)}',

                    style:
                        const TextStyle(
                      fontSize: 9.5,
                      fontWeight:
                          FontWeight.w600,
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
    );
  }

  // ============================================================
  // PESANAN & TIMBANGAN
  // ============================================================

  Widget _buildOrderTracking() {
    return Container(
      width: double.infinity,

      padding:
          const EdgeInsets.fromLTRB(
        14,
        14,
        14,
        16,
      ),

      decoration: BoxDecoration(
        color:
            const Color(0xFFECFDF5),

        borderRadius:
            BorderRadius.circular(16),

        border: Border.all(
          color:
              const Color(0xFFA7F3D0),
        ),
      ),

      child: Column(
        crossAxisAlignment:
            CrossAxisAlignment.start,

        children: [

          Row(
            children: [

              const Icon(
                Icons.local_shipping_rounded,
                color:
                    primaryGreen,
                size: 22,
              ),

              const SizedBox(
                width: 8,
              ),

              const Expanded(
                child: Text(
                  'Pesanan & Timbangan Sawah Berjalan',

                  style: TextStyle(
                    fontSize: 14,
                    fontWeight:
                        FontWeight.w900,
                    color:
                        primaryGreen,
                  ),
                ),
              ),

              const Text(
                'Lacak >',

                style: TextStyle(
                  fontSize: 12,
                  fontWeight:
                      FontWeight.w900,
                  color:
                      primaryGreen,
                ),
              ),
            ],
          ),

          const SizedBox(
            height: 12,
          ),

          const Text(
            'Gabah Padi • 100.0 kg',

            style: TextStyle(
              fontSize: 13,
              fontWeight:
                  FontWeight.w800,
              color:
                  Color(0xFF0F172A),
            ),
          ),

          const SizedBox(
            height: 4,
          ),

          const Text(
            'Petani: Petani Audi • Siap Timbang & Jemput',

            style: TextStyle(
              fontSize: 11,
              color:
                  Color(0xFF047857),
            ),
          ),
        ],
      ),
    );
  }

  // ============================================================
  // REKOMENDASI
  //
  // PENTING:
  // HANYA 1 PRODUK PER KATEGORI
  // ============================================================

  Widget _buildRecommendationSection(
    BuildContext context,
    List<MarketListingModel> listings,
  ) {
    final recommendations =
        _getOneProductPerCategory(
      listings,
    );

    if (recommendations.isEmpty) {
      return const SizedBox.shrink();
    }

    return Column(
      crossAxisAlignment:
          CrossAxisAlignment.start,

      children: [

        const Text(
          'Rekomendasi Untuk Anda',

          style: TextStyle(
            fontSize: 17,
            fontWeight:
                FontWeight.w900,
            color:
                Color(0xFF0F172A),
          ),
        ),

        const SizedBox(
          height: 10,
        ),

        GridView.builder(
          shrinkWrap: true,

          physics:
              const NeverScrollableScrollPhysics(),

          itemCount:
              recommendations.length,

          gridDelegate:
              const SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: 2,

            crossAxisSpacing: 10,

            mainAxisSpacing: 10,

            mainAxisExtent: 285,
          ),

          itemBuilder:
              (context, index) {

            final listing =
                recommendations[index];

            return _buildRecommendationCard(
              context,
              listing,
            );
          },
        ),
      ],
    );
  }

  // ============================================================
  // AMBIL 1 PRODUK PER KATEGORI
  // ============================================================

  List<MarketListingModel>
      _getOneProductPerCategory(
    List<MarketListingModel> listings,
  ) {
    final result =
        <MarketListingModel>[];

    // ==========================================================
    // KATEGORI YANG DIBUTUHKAN
    // ==========================================================

    const categories = [
      'gkp',
      'gkg',
      'beras',
      'benih',
    ];

    // ==========================================================
    // CARI 1 PRODUK PERTAMA DARI SETIAP KATEGORI
    // ==========================================================

    for (final category in categories) {
      for (final listing in listings) {

        if (_getListingCategory(listing) ==
            category) {

          result.add(listing);

          // Berhenti setelah menemukan
          // 1 produk kategori ini.
          break;
        }
      }
    }

    return result;
  }

  // ============================================================
  // IDENTIFIKASI KATEGORI PRODUK
  // ============================================================

  String _getListingCategory(
    MarketListingModel listing,
  ) {
    final commodity =
        listing.commodity
            .toString()
            .toLowerCase()
            .trim();

    // ==========================================================
    // GKP
    // ==========================================================

    if (commodity.contains('gkp') ||
        commodity.contains(
          'gabah kering panen',
        )) {
      return 'gkp';
    }

    // ==========================================================
    // GKG
    // ==========================================================

    if (commodity.contains('gkg') ||
        commodity.contains(
          'gabah kering giling',
        )) {
      return 'gkg';
    }

    // ==========================================================
    // BENIH
    // ==========================================================

    if (commodity.contains('benih')) {
      return 'benih';
    }

    // ==========================================================
    // BERAS
    // ==========================================================

    if (commodity.contains('beras')) {
      return 'beras';
    }

    return '';
  }

  // ============================================================
  // CARD REKOMENDASI
  // ============================================================

  Widget _buildRecommendationCard(
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
        decoration: BoxDecoration(
          color: Colors.white,

          borderRadius:
              BorderRadius.circular(14),

          border: Border.all(
            color:
                const Color(0xFFE2E8F0),
          ),
        ),

        clipBehavior:
            Clip.antiAlias,

        child: Column(
          crossAxisAlignment:
              CrossAxisAlignment.start,

          children: [

            // ==============================================
            // GAMBAR PRODUK
            // ==============================================

            SizedBox(
              width: double.infinity,
              height: 145,

              child: Stack(
                children: [

                  Positioned.fill(
                    child: _buildItemImage(
                      listing.imageUrl,
                    ),
                  ),

                  // ========================================
                  // LABEL MITRA
                  // ========================================

                  Positioned(
                    left: 8,
                    top: 8,

                    child: Container(
                      padding:
                          const EdgeInsets.symmetric(
                        horizontal: 8,
                        vertical: 5,
                      ),

                      decoration:
                          BoxDecoration(
                        color:
                            primaryGreen,

                        borderRadius:
                            BorderRadius.circular(
                          7,
                        ),
                      ),

                      child: const Text(
                        'MITRA RESMI',

                        style: TextStyle(
                          color:
                              Colors.white,
                          fontSize: 9,
                          fontWeight:
                              FontWeight.w800,
                        ),
                      ),
                    ),
                  ),
                ],
              ),
            ),

            // ==============================================
            // DETAIL
            // ==============================================

            Expanded(
              child: Padding(
                padding:
                    const EdgeInsets.all(10),

                child: Column(
                  crossAxisAlignment:
                      CrossAxisAlignment.start,

                  children: [

                    Text(
                      listing.commodity
                          .toString(),

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
                        listing.pricePerUnit,
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
                      height: 5,
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
                          Icons.inventory_2_outlined,
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
                                TextOverflow.ellipsis,

                            style:
                                const TextStyle(
                              fontSize: 10,
                              fontWeight:
                                  FontWeight.w600,
                              color:
                                  Color(0xFF64748B),
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
  // IMAGE PRODUK
  // ============================================================

  Widget _buildItemImage(
    String? imageUrl,
  ) {
    final url =
        imageUrl?.trim() ?? '';

    final validUrl =
        url.startsWith('http://') ||
        url.startsWith('https://');

    if (!validUrl) {
      return Container(
        color:
            const Color(0xFFF1F5F9),

        child: const Center(
          child: Icon(
            Icons.image_outlined,
            size: 42,
            color:
                Color(0xFF94A3B8),
          ),
        ),
      );
    }

    return Image.network(
      url,

      fit: BoxFit.cover,

      width: double.infinity,

      errorBuilder: (
        context,
        error,
        stackTrace,
      ) {
        return Container(
          color:
              const Color(0xFFF1F5F9),

          child: const Center(
            child: Icon(
              Icons.image_outlined,
              size: 42,
              color:
                  Color(0xFF94A3B8),
            ),
          ),
        );
      },
    );
  }

  // ============================================================
  // FORMAT STOCK
  // ============================================================

  String _formatStock(
    MarketListingModel listing,
  ) {
    final quantity =
        listing.quantity;

    if (quantity >= 1000) {
      return
          '${(quantity / 1000).toStringAsFixed(1)} Ton';
    }

    return
        '${quantity.toStringAsFixed(0)} ${listing.unit}';
  }
}


// ============================================================================
// MODEL KATEGORI
// ============================================================================

class _HomeCategory {
  const _HomeCategory({
    required this.label,
    required this.icon,
    required this.category,
  });

  final String label;
  final IconData icon;
  final String category;
}