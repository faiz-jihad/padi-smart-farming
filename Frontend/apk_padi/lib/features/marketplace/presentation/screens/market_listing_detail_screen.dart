import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import 'package:padi/core/network/api_client.dart';
import 'package:padi/core/storage/token_storage.dart';
import 'package:padi/features/auth/presentation/widgets/padi_theme.dart';
import 'package:padi/features/marketplace/data/models/market_listing_model.dart';
import 'package:padi/features/marketplace/data/services/marketplace_api_service.dart';

class MarketListingDetailScreen extends StatefulWidget {
  const MarketListingDetailScreen({
    super.key,
    required this.listingId,
  });

  final int listingId;

  @override
  State<MarketListingDetailScreen> createState() =>
      _MarketListingDetailScreenState();
}

class _MarketListingDetailScreenState
    extends State<MarketListingDetailScreen> {
  late final MarketplaceApiService _service;

  MarketListingModel? _listing;

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

    _loadDetail();
  }

  Future<void> _loadDetail() async {
    if (mounted) {
      setState(() {
        _isLoading = true;
        _error = null;
      });
    }

    try {
      final listing = await _service.getListing(
        widget.listingId,
      );

      if (!mounted) {
        return;
      }

      setState(() {
        _listing = listing;
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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: padiField,
      appBar: AppBar(
        backgroundColor: padiField,
        elevation: 0,
        scrolledUnderElevation: 0,
        leading: IconButton(
          onPressed: () => context.pop(),
          icon: const Icon(
            Icons.arrow_back_rounded,
            color: padiInk,
          ),
        ),
        title: const Text(
          'Detail Hasil Panen',
          style: TextStyle(
            color: padiInk,
            fontSize: 20,
            fontWeight: FontWeight.w900,
          ),
        ),
      ),
      body: _buildBody(),
    );
  }

  Widget _buildBody() {
    if (_isLoading) {
      return const Center(
        child: CircularProgressIndicator(
          color: padiGreen,
        ),
      );
    }

    if (_error != null) {
      return _buildError();
    }

    if (_listing == null) {
      return _buildError();
    }

    final listing = _listing!;

    return RefreshIndicator(
      onRefresh: _loadDetail,
      color: padiGreen,
      child: ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.fromLTRB(
          20,
          10,
          20,
          30,
        ),
        children: [
          _buildImage(listing),
          const SizedBox(height: 24),
          Text(
            listing.commodity,
            style: const TextStyle(
              color: padiInk,
              fontSize: 28,
              fontWeight: FontWeight.w900,
            ),
          ),
          const SizedBox(height: 8),
          if (listing.description != null &&
              listing.description!.isNotEmpty)
            Text(
              listing.description!,
              style: const TextStyle(
                color: padiMuted,
                fontSize: 16,
                height: 1.5,
              ),
            ),
          const SizedBox(height: 24),
          _buildInfoCard(
            icon: Icons.scale_rounded,
            label: 'Jumlah',
            value:
                '${_formatNumber(listing.quantity)} ${listing.unit}',
          ),
          const SizedBox(height: 12),
          _buildInfoCard(
            icon: Icons.payments_rounded,
            label: 'Harga Patokan',
            value:
                'Rp${_formatNumber(listing.pricePerUnit)} / ${listing.unit}',
            valueColor: padiGreen,
          ),
          const SizedBox(height: 12),
          _buildInfoCard(
            icon: Icons.info_outline_rounded,
            label: 'Status',
            value: listing.status,
          ),
          const SizedBox(height: 30),
          _buildActionButton(listing),
        ],
      ),
    );
  }

  Widget _buildActionButton(
    MarketListingModel listing,
  ) {
    if (listing.isOwner) {
      return SizedBox(
        width: double.infinity,
        height: 58,
        child: FilledButton(
          onPressed: () {
            context.push(
              '/marketplace/${listing.id}/offers',
            );
          },
          style: FilledButton.styleFrom(
            backgroundColor: padiGreen,
            foregroundColor: Colors.white,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(18),
            ),
          ),
          child: const Text(
            'Penawaran Masuk',
            style: TextStyle(
              fontSize: 16,
              fontWeight: FontWeight.w900,
            ),
          ),
        ),
      );
    }

    if (listing.status != 'published') {
      return const SizedBox.shrink();
    }

    return SizedBox(
      width: double.infinity,
      height: 58,
      child: FilledButton(
        onPressed: () {
          context.push(
            '/marketplace/${listing.id}/offer',
            extra: {
              'commodity': listing.commodity,
              'unit': listing.unit,
              'quantity': listing.quantity,
              'pricePerUnit': listing.pricePerUnit,
            },
          );
        },
        style: FilledButton.styleFrom(
          backgroundColor: padiGreen,
          foregroundColor: Colors.white,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(18),
          ),
        ),
        child: const Text(
          'Berikan Penawaran',
          style: TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.w900,
          ),
        ),
      ),
    );
  }

  Widget _buildImage(
    MarketListingModel listing,
  ) {
    final imageUrl = listing.imageUrl;

    if (imageUrl == null || imageUrl.isEmpty) {
      return Container(
        height: 300,
        width: double.infinity,
        decoration: BoxDecoration(
          color: padiField,
          borderRadius: BorderRadius.circular(24),
        ),
        child: const Center(
          child: Icon(
            Icons.image_not_supported_outlined,
            color: padiGreen,
            size: 70,
          ),
        ),
      );
    }

    return ClipRRect(
      borderRadius: BorderRadius.circular(24),
      child: Image.network(
        imageUrl,
        height: 300,
        width: double.infinity,
        fit: BoxFit.cover,
        errorBuilder: (
          context,
          error,
          stackTrace,
        ) {
          return Container(
            height: 300,
            width: double.infinity,
            color: padiField,
            child: const Center(
              child: Icon(
                Icons.broken_image_outlined,
                color: padiGreen,
                size: 70,
              ),
            ),
          );
        },
        loadingBuilder: (
          context,
          child,
          loadingProgress,
        ) {
          if (loadingProgress == null) {
            return child;
          }

          return Container(
            height: 300,
            width: double.infinity,
            color: padiField,
            child: const Center(
              child: CircularProgressIndicator(
                color: padiGreen,
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _buildInfoCard({
    required IconData icon,
    required String label,
    required String value,
    Color? valueColor,
  }) {
    return Container(
      padding: const EdgeInsets.symmetric(
        horizontal: 20,
        vertical: 18,
      ),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
      ),
      child: Row(
        children: [
          Icon(
            icon,
            color: padiGreen,
            size: 30,
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Text(
              label,
              style: const TextStyle(
                color: padiMuted,
                fontSize: 16,
              ),
            ),
          ),
          Text(
            value,
            textAlign: TextAlign.right,
            style: TextStyle(
              color: valueColor ?? padiInk,
              fontSize: 16,
              fontWeight: FontWeight.w900,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildError() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(30),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              Icons.cloud_off_rounded,
              size: 60,
              color: Colors.grey.shade500,
            ),
            const SizedBox(height: 16),
            const Text(
              'Gagal mengambil detail hasil panen',
              textAlign: TextAlign.center,
              style: TextStyle(
                color: padiInk,
                fontSize: 18,
                fontWeight: FontWeight.w900,
              ),
            ),
            const SizedBox(height: 16),
            FilledButton(
              onPressed: _loadDetail,
              style: FilledButton.styleFrom(
                backgroundColor: padiGreen,
                foregroundColor: Colors.white,
              ),
              child: const Text('Coba Lagi'),
            ),
          ],
        ),
      ),
    );
  }

  String _formatNumber(
    double value,
  ) {
    if (value == value.roundToDouble()) {
      return value.toInt().toString();
    }

    return value.toStringAsFixed(2);
  }
}