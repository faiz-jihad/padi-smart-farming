import 'package:flutter/material.dart';
import 'package:padi/features/auth/presentation/widgets/padi_theme.dart';
import 'package:padi/features/marketplace/data/models/market_listing_model.dart';

class MarketListingCard extends StatelessWidget {
  const MarketListingCard({
    super.key,
    required this.listing,
    required this.onTap,
  });

  final MarketListingModel listing;
  final VoidCallback onTap;

  String _formatPrice(double value) {
    final formatted = value
        .toStringAsFixed(0)
        .replaceAllMapped(
          RegExp(r'\B(?=(\d{3})+(?!\d))'),
          (match) => '.',
        );

    return 'Rp$formatted';
  }

  String _formatQuantity(double value) {
    if (value == value.roundToDouble()) {
      return value.toInt().toString();
    }

    return value.toString();
  }

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(22),
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(22),
          border: Border.all(
            color: Colors.black.withValues(alpha: 0.05),
          ),
        ),
        clipBehavior: Clip.antiAlias,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _buildImage(),
            Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    listing.commodity,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      color: padiInk,
                      fontSize: 18,
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      const Icon(
                        Icons.scale_outlined,
                        size: 17,
                        color: padiMuted,
                      ),
                      const SizedBox(width: 6),
                      Text(
                        '${_formatQuantity(listing.quantity)} ${listing.unit}',
                        style: const TextStyle(
                          color: padiMuted,
                          fontSize: 14,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ],
                  ),
                  if (listing.description != null &&
                      listing.description!.isNotEmpty) ...[
                    const SizedBox(height: 10),
                    Text(
                      listing.description!,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        color: padiMuted,
                        fontSize: 13,
                        height: 1.4,
                      ),
                    ),
                  ],
                  const SizedBox(height: 14),
                  const Text(
                    'Harga patokan',
                    style: TextStyle(
                      color: padiMuted,
                      fontSize: 12,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    '${_formatPrice(listing.pricePerUnit)} / ${listing.unit}',
                    style: const TextStyle(
                      color: padiGreen,
                      fontSize: 17,
                      fontWeight: FontWeight.w900,
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

  Widget _buildImage() {
    final imageUrl = listing.imageUrl;

    if (imageUrl == null || imageUrl.isEmpty) {
      return Container(
        height: 150,
        width: double.infinity,
        color: const Color(0xFFEAF6EC),
        child: const Icon(
          Icons.grass_rounded,
          color: padiGreen,
          size: 54,
        ),
      );
    }

    return Image.network(
      imageUrl,
      height: 150,
      width: double.infinity,
      fit: BoxFit.cover,
      errorBuilder: (
        context,
        error,
        stackTrace,
      ) {
        return Container(
          height: 150,
          width: double.infinity,
          color: const Color(0xFFEAF6EC),
          child: const Icon(
            Icons.image_not_supported_outlined,
            color: padiGreen,
            size: 45,
          ),
        );
      },
    );
  }
}