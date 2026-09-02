import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import 'package:padi/core/providers/app_providers.dart';
import 'package:padi/features/cart/presentation/providers/cart_providers.dart';
import 'package:padi/features/home/presentation/tokens/home_tokens.dart';
import 'package:padi/features/marketplace/data/models/market_listing_model.dart';
import 'package:padi/features/marketplace/data/services/marketplace_api_service.dart';
import 'package:padi/features/marketplace/presentation/widgets/market_listing_card.dart';
import 'package:padi/features/marketplace/presentation/widgets/marketplace_header.dart';
import 'package:padi/features/marketplace/presentation/widgets/marketplace_hero_banner.dart';
import 'package:padi/features/marketplace/presentation/widgets/marketplace_price_ticker.dart';
import 'package:padi/features/marketplace/presentation/widgets/marketplace_skeleton.dart';


// ============================================================
// MARKETPLACE API PROVIDER
// ============================================================

final marketplaceApiServiceProvider =
    Provider<MarketplaceApiService>((ref) {
  return MarketplaceApiService(
    ref.read(apiClientProvider),
  );
});


// ============================================================
// MARKETPLACE LISTINGS PROVIDER
//
// Semua produk Marketplace berasal dari API yang sama.
// Home juga menggunakan provider ini.
//
// Jadi data Home dan Marketplace tetap sinkron.
// ============================================================

final marketplaceListingsProvider =
    FutureProvider.autoDispose<List<MarketListingModel>>((ref) async {
  final service = ref.read(
    marketplaceApiServiceProvider,
  );

  try {
    final listings = await service
        .fetchListings()
        .timeout(
          const Duration(seconds: 8),
        );

    return listings;
  } catch (_) {
    return <MarketListingModel>[];
  }
});


// ============================================================
// MARKETPLACE SCREEN
// ============================================================

class MarketplaceScreen extends ConsumerStatefulWidget {
  const MarketplaceScreen({
    super.key,
    this.initialCategory = 'all',
  });

  /// Kategori yang dikirim dari Home.
  ///
  /// gkp
  /// gkg
  /// beras
  /// benih
  /// all
  final String initialCategory;

  @override
  ConsumerState<MarketplaceScreen> createState() =>
      _MarketplaceScreenState();
}


// ============================================================
// STATE
// ============================================================

class _MarketplaceScreenState
    extends ConsumerState<MarketplaceScreen> {

  // ==========================================================
  // SEARCH
  // ==========================================================

  final TextEditingController _searchController =
      TextEditingController();


  // ==========================================================
  // FILTER
  // ==========================================================

  late String _selectedCategory;

  String _selectedSort = 'newest';


  // ==========================================================
  // INIT
  // ==========================================================

  @override
  void initState() {
    super.initState();

    _selectedCategory = _normalizeCategory(
      widget.initialCategory,
    );
  }


  // ==========================================================
  // UPDATE KETIKA ROUTE BERUBAH
  // ==========================================================

  @override
  void didUpdateWidget(
    covariant MarketplaceScreen oldWidget,
  ) {
    super.didUpdateWidget(oldWidget);

    if (oldWidget.initialCategory !=
        widget.initialCategory) {
      final newCategory = _normalizeCategory(
        widget.initialCategory,
      );

      if (_selectedCategory != newCategory) {
        setState(() {
          _selectedCategory = newCategory;
        });
      }
    }
  }


  // ==========================================================
  // NORMALIZE CATEGORY
  // ==========================================================

  String _normalizeCategory(String category) {
    final value = category
        .trim()
        .toLowerCase();

    switch (value) {
      case 'gkp':
      case 'gkp_panen':
      case 'gabah_kering_panen':
        return 'gkp';

      case 'gkg':
      case 'gkg_giling':
      case 'gabah_kering_giling':
        return 'gkg';

      case 'beras':
      case 'beras_premium':
      case 'beras_super':
        return 'beras';

      case 'benih':
      case 'benih_bersertifikat':
        return 'benih';

      case 'all':
      default:
        return 'all';
    }
  }


  // ==========================================================
  // DISPOSE
  // ==========================================================

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }


  // ==========================================================
  // CREATE LISTING
  // ==========================================================

  Future<void> _openCreateListing() async {
    final result = await context.push(
      '/marketplace/create',
    );

    if (!mounted) {
      return;
    }

    if (result == true) {
      setState(() {
        _selectedCategory = 'all';
        _selectedSort = 'newest';
        _searchController.clear();
      });

      ref.invalidate(
        marketplaceListingsProvider,
      );
    }
  }


  // ==========================================================
  // CHANGE CATEGORY
  // ==========================================================

  void _changeCategory(String category) {
    final normalized = _normalizeCategory(
      category,
    );

    setState(() {
      _selectedCategory = normalized;
    });
  }


  // ==========================================================
  // FILTER LISTING
  // ==========================================================

  List<MarketListingModel> _filterListings(
    List<MarketListingModel> listings,
  ) {
    var result = List<MarketListingModel>.from(
      listings,
    );


    // ========================================================
    // CATEGORY
    // ========================================================

    if (_selectedCategory != 'all') {
      result = result.where((listing) {
        final commodity = listing.commodity
            .trim()
            .toLowerCase();

        switch (_selectedCategory) {

          // --------------------------------------------------
          // GKP PANEN
          // --------------------------------------------------

          case 'gkp':
            return commodity.contains('gkp') ||
                commodity.contains('gabah kering panen') ||
                commodity.contains('gkp panen');


          // --------------------------------------------------
          // GKG GILING
          // --------------------------------------------------

          case 'gkg':
            return commodity.contains('gkg') ||
                commodity.contains('gabah kering giling') ||
                commodity.contains('gkg giling');


          // --------------------------------------------------
          // BERAS PREMIUM
          // --------------------------------------------------

          case 'beras':
            return commodity.contains('beras');


          // --------------------------------------------------
          // BENIH BERSERTIFIKAT
          // --------------------------------------------------

          case 'benih':
            return commodity.contains('benih') ||
                commodity.contains('bibit');


          default:
            return true;
        }
      }).toList();
    }


    // ========================================================
    // SEARCH
    // ========================================================

    final keyword = _searchController.text
        .trim()
        .toLowerCase();

    if (keyword.isNotEmpty) {
      result = result.where((listing) {

        final commodity = listing.commodity
            .toLowerCase();

        final description =
            (listing.description ?? '')
                .toLowerCase();

        final variety =
            (listing.varietyName ?? '')
                .toLowerCase();

        final farmer =
            (listing.farmerName ?? '')
                .toLowerCase();

        return commodity.contains(keyword) ||
            description.contains(keyword) ||
            variety.contains(keyword) ||
            farmer.contains(keyword);
      }).toList();
    }


    // ========================================================
    // SORT
    // ========================================================

    switch (_selectedSort) {

      case 'price_asc':
        result.sort(
          (a, b) => a.pricePerUnit.compareTo(
            b.pricePerUnit,
          ),
        );
        break;


      case 'price_desc':
        result.sort(
          (a, b) => b.pricePerUnit.compareTo(
            a.pricePerUnit,
          ),
        );
        break;


      case 'qty_desc':
        result.sort(
          (a, b) => b.quantity.compareTo(
            a.quantity,
          ),
        );
        break;


      case 'newest':
      case 'relevance':
      default:
        result.sort(
          (a, b) => b.id.compareTo(
            a.id,
          ),
        );
        break;
    }

    return result;
  }


  // ==========================================================
  // BUILD
  // ==========================================================

  @override
  Widget build(BuildContext context) {

    final cartState = ref.watch(
      cartProvider,
    );

    final listingsAsync = ref.watch(
      marketplaceListingsProvider,
    );

    final isBuyer = ref.watch(
      isBuyerRoleProvider,
    );


    return Scaffold(
      backgroundColor:
          const Color(0xFFF5F7F4),


      // ======================================================
      // APP BAR
      // ======================================================

      appBar: AppBar(
        backgroundColor:
            HomeColors.primaryGreen,

        elevation: 0,

        scrolledUnderElevation: 0,

        leading: IconButton(
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


        // ====================================================
        // SEARCH
        // ====================================================

        title: Container(
          height: 38,

          margin: const EdgeInsets.only(
            right: 6,
          ),

          decoration: BoxDecoration(
            color: Colors.white,

            borderRadius:
                BorderRadius.circular(8),
          ),

          child: TextField(
            controller:
                _searchController,

            onChanged: (_) {
              setState(() {});
            },

            decoration:
                const InputDecoration(
              hintText:
                  'Cari gabah panen, beras, varietas...',

              prefixIcon:
                  Icon(
                Icons.search_rounded,

                color:
                    Color(0xFF777777),

                size: 22,
              ),

              border:
                  InputBorder.none,

              enabledBorder:
                  InputBorder.none,

              focusedBorder:
                  InputBorder.none,

              contentPadding:
                  EdgeInsets.symmetric(
                horizontal: 8,
                vertical: 8,
              ),
            ),
          ),
        ),


        // ====================================================
        // ACTION
        // ====================================================

        actions: [

          // --------------------------------------------------
          // CART
          // --------------------------------------------------

          Stack(
            clipBehavior:
                Clip.none,

            children: [

              IconButton(
                tooltip:
                    'Keranjang',

                icon:
                    const Icon(
                  Icons
                      .shopping_cart_outlined,

                  color:
                      Colors.white,

                  size: 22,
                ),

                onPressed: () {
                  context.push(
                    '/cart',
                  );
                },
              ),


              if (cartState.totalCount > 0)
                Positioned(
                  top: 4,
                  right: 4,

                  child:
                      Container(
                    constraints:
                        const BoxConstraints(
                      minWidth: 16,
                      minHeight: 16,
                    ),

                    decoration:
                        const BoxDecoration(
                      color:
                          Color(0xFFEF4444),

                      shape:
                          BoxShape.circle,
                    ),

                    alignment:
                        Alignment.center,

                    child:
                        Text(
                      '${cartState.totalCount}',

                      style:
                          const TextStyle(
                        color:
                            Colors.white,

                        fontSize: 9,

                        fontWeight:
                            FontWeight.bold,
                      ),
                    ),
                  ),
                ),
            ],
          ),


          // --------------------------------------------------
          // KONTRAK / PENAWARAN
          // --------------------------------------------------

          IconButton(
            tooltip:
                isBuyer
                    ? 'Kontrak Saya'
                    : 'Penawaran Saya',

            icon:
                Icon(
              isBuyer
                  ? Icons.receipt_long_rounded
                  : Icons.gavel_rounded,

              color:
                  Colors.white,

              size: 22,
            ),

            onPressed: () {

              if (isBuyer) {
                context.push(
                  '/buyer/orders',
                );
              } else {
                context.push(
                  '/marketplace/offers',
                );
              }
            },
          ),


          // --------------------------------------------------
          // REFRESH
          // --------------------------------------------------

          IconButton(
            tooltip:
                'Refresh',

            icon:
                const Icon(
              Icons.refresh_rounded,

              color:
                  Colors.white,

              size: 22,
            ),

            onPressed: () {
              ref.invalidate(
                marketplaceListingsProvider,
              );
            },
          ),

          const SizedBox(
            width: 4,
          ),
        ],
      ),


      // ======================================================
      // FLOATING BUTTON
      // ======================================================

      floatingActionButton:
          isBuyer
              ? (
                  cartState.hasItems
                      ? FloatingActionButton.extended(
                          onPressed: () {
                            context.push(
                              '/cart',
                            );
                          },

                          backgroundColor:
                              HomeColors
                                  .primaryGreen,

                          foregroundColor:
                              Colors.white,

                          icon:
                              const Icon(
                            Icons
                                .shopping_cart_rounded,

                            size: 20,
                          ),

                          label:
                              Text(
                            'Keranjang (${cartState.totalCount})',

                            style:
                                const TextStyle(
                              fontSize:
                                  13,

                              fontWeight:
                                  FontWeight.w800,
                            ),
                          ),
                        )
                      : null
                )
              : FloatingActionButton.extended(
                  onPressed:
                      _openCreateListing,

                  backgroundColor:
                      HomeColors.primaryGreen,

                  foregroundColor:
                      Colors.white,

                  icon:
                      const Icon(
                    Icons
                        .add_shopping_cart_rounded,

                    size: 20,
                  ),

                  label:
                      const Text(
                    'Mulai Jual Panen',

                    style:
                        TextStyle(
                      fontSize:
                          13,

                      fontWeight:
                          FontWeight.w800,
                    ),
                  ),
                ),


      // ======================================================
      // BODY
      // ======================================================

      body: RefreshIndicator(
        color:
            HomeColors.primaryGreen,

        backgroundColor:
            Colors.white,

        onRefresh: () async {
          ref.invalidate(
            marketplaceListingsProvider,
          );

          await ref.read(
            marketplaceListingsProvider.future,
          );
        },


        child: listingsAsync.when(

          // ==================================================
          // DATA
          // ==================================================

          data: (listings) {

            final filteredListings =
                _filterListings(
              listings,
            );


            return ListView(
              physics:
                  const AlwaysScrollableScrollPhysics(
                parent:
                    BouncingScrollPhysics(),
              ),

              padding:
                  const EdgeInsets.only(
                bottom: 120,
              ),

              children: [

                // ==========================================
                // HEADER MARKETPLACE
                // ==========================================

                MarketplaceHeader(
                  searchController:
                      _searchController,

                  selectedCategory:
                      _selectedCategory,

                  onCategorySelected:
                      _changeCategory,

                  selectedSort:
                      _selectedSort,

                  onSortSelected:
                      (sort) {
                    setState(() {
                      _selectedSort =
                          sort;
                    });
                  },

                  totalListings:
                      listings.length,

                  filteredListings:
                      filteredListings.length,

                  onSearchChanged:
                      () {
                    setState(() {});
                  },
                ),


                const SizedBox(
                  height: 6,
                ),


                // ==========================================
                // PRICE TICKER
                // ==========================================

                MarketplacePriceTicker(
                  listings:
                      listings,
                ),


                const SizedBox(
                  height: 8,
                ),


                // ==========================================
                // HERO
                // ==========================================

                if (!isBuyer) ...[
                  Padding(
                    padding:
                        const EdgeInsets
                            .symmetric(
                      horizontal: 8,
                    ),

                    child:
                        MarketplaceHeroBanner(
                      onTapCreateListing:
                          _openCreateListing,
                    ),
                  ),

                  const SizedBox(
                    height: 8,
                  ),
                ],


                // ==========================================
                // HASIL PRODUK
                // ==========================================

                if (filteredListings.isEmpty)
                  _buildEmptyState(
                    context,
                  )
                else
                  Padding(
                    padding:
                        const EdgeInsets
                            .symmetric(
                      horizontal: 8,
                    ),

                    child:
                        GridView.builder(
                      shrinkWrap:
                          true,

                      physics:
                          const NeverScrollableScrollPhysics(),

                      itemCount:
                          filteredListings.length,

                      gridDelegate:
                          const SliverGridDelegateWithFixedCrossAxisCount(
                        crossAxisCount:
                            2,

                        crossAxisSpacing:
                            8,

                        mainAxisSpacing:
                            8,

                        childAspectRatio:
                            0.62,
                      ),

                      itemBuilder:
                          (
                        context,
                        index,
                      ) {

                        final listing =
                            filteredListings[
                                index];


                        return MarketListingCard(
                          listing:
                              listing,

                          onTap: () {
                            context.push(
                              '/marketplace/${listing.id}',
                            );
                          },
                        );
                      },
                    ),
                  ),
              ],
            );
          },


          // ==================================================
          // LOADING
          // ==================================================

          loading: () {
            return ListView(
              physics:
                  const AlwaysScrollableScrollPhysics(
                parent:
                    BouncingScrollPhysics(),
              ),

              padding:
                  const EdgeInsets.fromLTRB(
                16,
                8,
                16,
                120,
              ),

              children:
                  const [
                MarketplaceSkeleton(),
              ],
            );
          },


          // ==================================================
          // ERROR
          // ==================================================

          error: (error, stack) {
            return ListView(
              physics:
                  const AlwaysScrollableScrollPhysics(
                parent:
                    BouncingScrollPhysics(),
              ),

              padding:
                  const EdgeInsets.fromLTRB(
                16,
                8,
                16,
                120,
              ),

              children: [
                _buildErrorState(
                  context,
                ),
              ],
            );
          },
        ),
      ),
    );
  }


  // ==========================================================
  // EMPTY STATE
  // ==========================================================

  Widget _buildEmptyState(
    BuildContext context,
  ) {

    final hasFilter =
        _selectedCategory != 'all' ||
        _searchController.text
            .trim()
            .isNotEmpty;


    return Container(
      margin:
          const EdgeInsets.all(12),

      padding:
          const EdgeInsets.all(30),

      decoration:
          BoxDecoration(
        color:
            Colors.white,

        borderRadius:
            BorderRadius.circular(12),

        border:
            Border.all(
          color:
              const Color(0xFFE2E8F0),
        ),
      ),

      child:
          Column(
        children: [

          Container(
            width: 68,
            height: 68,

            decoration:
                const BoxDecoration(
              color:
                  Color(0xFFF4F8F4),

              shape:
                  BoxShape.circle,
            ),

            child:
                const Icon(
              Icons
                  .inventory_2_outlined,

              size:
                  34,

              color:
                  HomeColors
                      .primaryGreen,
            ),
          ),


          const SizedBox(
            height: 14,
          ),


          Text(
            hasFilter
                ? 'Tidak Ada Hasil Panen Ditemukan'
                : 'Belum Ada Listing Panen',

            textAlign:
                TextAlign.center,

            style:
                const TextStyle(
              fontSize:
                  15,

              fontWeight:
                  FontWeight.w800,

              color:
                  Color(0xFF17251E),
            ),
          ),


          const SizedBox(
            height: 6,
          ),


          Text(
            hasFilter
                ? 'Tidak ada produk yang sesuai dengan kategori yang dipilih.'
                : 'Belum ada hasil panen yang tersedia.',

            textAlign:
                TextAlign.center,

            style:
                const TextStyle(
              fontSize:
                  12,

              color:
                  Color(0xFF68766E),
            ),
          ),


          if (hasFilter) ...[
            const SizedBox(
              height: 16,
            ),

            OutlinedButton.icon(
              onPressed: () {
                setState(() {
                  _selectedCategory =
                      'all';

                  _selectedSort =
                      'newest';

                  _searchController
                      .clear();
                });
              },

              icon:
                  const Icon(
                Icons.refresh_rounded,
                size: 16,
              ),

              label:
                  const Text(
                'Reset Filter',
              ),

              style:
                  OutlinedButton.styleFrom(
                foregroundColor:
                    HomeColors
                        .primaryGreen,

                side:
                    const BorderSide(
                  color:
                      HomeColors
                          .primaryGreen,
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }


  // ==========================================================
  // ERROR STATE
  // ==========================================================

  Widget _buildErrorState(
    BuildContext context,
  ) {

    return Container(
      margin:
          const EdgeInsets.all(12),

      padding:
          const EdgeInsets.all(28),

      decoration:
          BoxDecoration(
        color:
            Colors.white,

        borderRadius:
            BorderRadius.circular(12),

        border:
            Border.all(
          color:
              const Color(0xFFE2E8F0),
        ),
      ),

      child:
          Column(
        children: [

          const Icon(
            Icons.cloud_off_rounded,

            size:
                42,

            color:
                Color(0xFF94A3B8),
          ),


          const SizedBox(
            height: 12,
          ),


          const Text(
            'Gagal Memuat Data',

            style:
                TextStyle(
              fontSize:
                  15,

              fontWeight:
                  FontWeight.w800,

              color:
                  Color(0xFF17251E),
            ),
          ),


          const SizedBox(
            height: 6,
          ),


          const Text(
            'Periksa koneksi internet lalu coba lagi.',

            textAlign:
                TextAlign.center,

            style:
                TextStyle(
              fontSize:
                  12,

              color:
                  Color(0xFF68766E),
            ),
          ),


          const SizedBox(
            height: 16,
          ),


          FilledButton.icon(
            onPressed: () {
              ref.invalidate(
                marketplaceListingsProvider,
              );
            },

            icon:
                const Icon(
              Icons.refresh_rounded,
              size: 17,
            ),

            label:
                const Text(
              'Coba Lagi',
            ),

            style:
                FilledButton.styleFrom(
              backgroundColor:
                  HomeColors
                      .primaryGreen,

              foregroundColor:
                  Colors.white,
            ),
          ),
        ],
      ),
    );
  }
}