import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import 'package:padi/core/network/api_client.dart';
import 'package:padi/core/storage/token_storage.dart';
import 'package:padi/features/auth/presentation/widgets/padi_theme.dart';
import 'package:padi/features/marketplace/data/models/market_listing_model.dart';
import 'package:padi/features/marketplace/data/services/marketplace_api_service.dart';
import 'package:padi/features/marketplace/presentation/widgets/market_listing_card.dart';

class MarketplaceScreen extends StatefulWidget {
  const MarketplaceScreen({
    super.key,
  });

  @override
  State<MarketplaceScreen> createState() => _MarketplaceScreenState();
}

class _MarketplaceScreenState extends State<MarketplaceScreen> {
  late final MarketplaceApiService _service;

  final TextEditingController _searchController =
      TextEditingController();

  List<MarketListingModel> _listings = [];

  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();

    _service = MarketplaceApiService(
      ApiClient(
        const SecureTokenStorage(),
      ),
    );

    _loadListings();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _loadListings() async {
    if (mounted) {
      setState(() {
        _isLoading = true;
        _error = null;
      });
    }

    try {
      final listings = await _service.fetchListings();

      if (!mounted) {
        return;
      }

      setState(() {
        _listings = listings;
        _isLoading = false;
      });
    } catch (e) {
      if (!mounted) {
        return;
      }

      setState(() {
        _isLoading = false;
        _error = e.toString().replaceFirst(
          'Exception: ',
          '',
        );
      });
    }
  }

  Future<void> _openCreateListing() async {
    final result = await context.push(
      '/marketplace/create',
    );

    if (!mounted) {
      return;
    }

    if (result == true) {
      await _loadListings();
    }
  }

  List<MarketListingModel> get _filteredListings {
    final keyword = _searchController.text
        .trim()
        .toLowerCase();

    if (keyword.isEmpty) {
      return _listings;
    }

    return _listings.where((listing) {
      final commodity = listing.commodity.toLowerCase();

      final description =
          (listing.description ?? '').toLowerCase();

      return commodity.contains(keyword) ||
          description.contains(keyword);
    }).toList();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: padiField,
      appBar: AppBar(
        backgroundColor: padiField,
        elevation: 0,
        scrolledUnderElevation: 0,
        leading: IconButton(
          onPressed: () {
            context.go('/home');
          },
          icon: const Icon(
            Icons.arrow_back_rounded,
            color: padiInk,
          ),
        ),
        title: const Text(
          'Toko',
          style: TextStyle(
            color: padiInk,
            fontSize: 20,
            fontWeight: FontWeight.w900,
          ),
        ),
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _isLoading
            ? null
            : _openCreateListing,
        backgroundColor: padiGreen,
        foregroundColor: Colors.white,
        icon: const Icon(
          Icons.add_rounded,
        ),
        label: const Text(
          'Jual Hasil Panen',
          style: TextStyle(
            fontWeight: FontWeight.w800,
          ),
        ),
      ),
      body: RefreshIndicator(
        onRefresh: _loadListings,
        child: CustomScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          slivers: [
            SliverToBoxAdapter(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(
                  20,
                  8,
                  20,
                  16,
                ),
                child: _buildHeader(),
              ),
            ),
            if (_isLoading)
              const SliverFillRemaining(
                hasScrollBody: false,
                child: Center(
                  child: CircularProgressIndicator(
                    color: padiGreen,
                  ),
                ),
              )
            else if (_error != null)
              SliverFillRemaining(
                hasScrollBody: false,
                child: _buildError(),
              )
            else if (_filteredListings.isEmpty)
              SliverFillRemaining(
                hasScrollBody: false,
                child: _buildEmpty(),
              )
            else
              SliverPadding(
                padding: const EdgeInsets.fromLTRB(
                  20,
                  0,
                  20,
                  100,
                ),
                sliver: SliverList(
                  delegate: SliverChildBuilderDelegate(
                    (context, index) {
                      final listing =
                          _filteredListings[index];

                      return Padding(
                        padding: const EdgeInsets.only(
                          bottom: 14,
                        ),
                        child: MarketListingCard(
                          listing: listing,
                          onTap: () {
                            context.push(
                              '/marketplace/${listing.id}',
                            );
                          },
                        ),
                      );
                    },
                    childCount: _filteredListings.length,
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildHeader() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Container(
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            color: padiGreen,
            borderRadius: BorderRadius.circular(24),
          ),
          child: const Row(
            children: [
              Icon(
                Icons.storefront_rounded,
                color: Colors.white,
                size: 34,
              ),
              SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment:
                      CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Pasar Hasil Panen',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 19,
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                    SizedBox(height: 4),
                    Text(
                      'Temukan hasil panen dari petani.',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 13,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 16),
        TextField(
          controller: _searchController,
          onChanged: (_) {
            setState(() {});
          },
          decoration: InputDecoration(
            hintText: 'Cari hasil panen...',
            prefixIcon: const Icon(
              Icons.search_rounded,
            ),
            suffixIcon:
                _searchController.text.isEmpty
                    ? null
                    : IconButton(
                        onPressed: () {
                          _searchController.clear();
                          setState(() {});
                        },
                        icon: const Icon(
                          Icons.close_rounded,
                        ),
                      ),
          ),
        ),
      ],
    );
  }

  Widget _buildEmpty() {
    return ListView(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.all(30),
      children: [
        const SizedBox(height: 80),
        Icon(
          Icons.storefront_outlined,
          size: 65,
          color: Colors.grey.shade400,
        ),
        const SizedBox(height: 18),
        const Text(
          'Belum ada hasil panen',
          textAlign: TextAlign.center,
          style: TextStyle(
            color: padiInk,
            fontSize: 18,
            fontWeight: FontWeight.w900,
          ),
        ),
        const SizedBox(height: 8),
        const Text(
          'Belum ada listing yang tersedia di toko.',
          textAlign: TextAlign.center,
          style: TextStyle(
            color: padiMuted,
            fontSize: 14,
          ),
        ),
      ],
    );
  }

  Widget _buildError() {
    return ListView(
      physics: const AlwaysScrollableScrollPhysics(),
      padding: const EdgeInsets.all(30),
      children: [
        const SizedBox(height: 70),
        Icon(
          Icons.cloud_off_rounded,
          size: 60,
          color: Colors.grey.shade500,
        ),
        const SizedBox(height: 18),
        const Text(
          'Gagal mengambil data toko',
          textAlign: TextAlign.center,
          style: TextStyle(
            color: padiInk,
            fontSize: 18,
            fontWeight: FontWeight.w900,
          ),
        ),
        if (_error != null) ...[
          const SizedBox(height: 10),
          Text(
            _error!,
            textAlign: TextAlign.center,
            style: const TextStyle(
              color: padiMuted,
              fontSize: 13,
            ),
          ),
        ],
        const SizedBox(height: 18),
        Center(
          child: FilledButton(
            onPressed: _loadListings,
            child: const Text(
              'Coba Lagi',
            ),
          ),
        ),
      ],
    );
  }
}