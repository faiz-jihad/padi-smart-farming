import 'package:flutter/material.dart';
import 'package:padi/features/home/presentation/tokens/home_tokens.dart';
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
        .replaceAllMapped(RegExp(r'\B(?=(\d{3})+(?!\d))'), (match) => '.');
    return 'Rp$formatted';
  }

  String _formatQuantity(double value, String unit) {
    if (value >= 1000 && unit.toLowerCase() == 'kg') {
      final ton = value / 1000;
      final tonStr = ton == ton.roundToDouble()
          ? ton.toInt().toString()
          : ton.toStringAsFixed(1);
      return '$tonStr Ton';
    }
    final qtyStr = value == value.roundToDouble()
        ? value.toInt().toString()
        : value.toString();
    return '$qtyStr $unit';
  }

  @override
  Widget build(BuildContext context) {
    final isGkp = listing.commodity.toLowerCase().contains('gkp') ||
        listing.commodity.toLowerCase().contains('panen');
    final isBeras = listing.commodity.toLowerCase().contains('beras');

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: const Color(0xFFE8ECE7), width: 0.8),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.04),
            blurRadius: 6,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      clipBehavior: Clip.antiAlias,
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(10),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // 1. Gambar Produk 1:1 dengan Badge Hijau Zamrud
              AspectRatio(
                aspectRatio: 1.05,
                child: Stack(
                  fit: StackFit.expand,
                  children: [
                    _buildImageThumbnail(listing.imageUrl),

                    // Badge Top Left: Petani Resmi / Beras Super
                    Positioned(
                      top: 6,
                      left: 6,
                      child: Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 5,
                          vertical: 2,
                        ),
                        decoration: BoxDecoration(
                          color: HomeColors.deepGreen,
                          borderRadius: BorderRadius.circular(3),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const Icon(
                              Icons.verified_rounded,
                              color: Colors.white,
                              size: 10,
                            ),
                            const SizedBox(width: 2),
                            Text(
                              isBeras ? 'Beras Super' : 'Petani Resmi',
                              style: const TextStyle(
                                color: Colors.white,
                                fontSize: 9,
                                fontWeight: FontWeight.w800,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),

                    // Badge Top Right: Iklan Anda
                    if (listing.isOwner)
                      Positioned(
                        top: 6,
                        right: 6,
                        child: Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 6,
                            vertical: 2,
                          ),
                          decoration: BoxDecoration(
                            color: const Color(0xFF042F1E),
                            borderRadius: BorderRadius.circular(3),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withOpacity(0.25),
                                blurRadius: 4,
                              ),
                            ],
                          ),
                          child: const Text(
                            'Iklan Anda',
                            style: TextStyle(
                              color: Color(0xFF34D399),
                              fontSize: 9,
                              fontWeight: FontWeight.w900,
                            ),
                          ),
                        ),
                      ),

                    // Bottom Image Gradient & Stock Banner
                    Positioned(
                      bottom: 0,
                      left: 0,
                      right: 0,
                      child: Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 6,
                          vertical: 3,
                        ),
                        decoration: BoxDecoration(
                          gradient: LinearGradient(
                            begin: Alignment.topCenter,
                            end: Alignment.bottomCenter,
                            colors: [
                              Colors.transparent,
                              Colors.black.withOpacity(0.65),
                            ],
                          ),
                        ),
                        child: Row(
                          children: [
                            const Icon(
                              Icons.inventory_2_outlined,
                              color: Colors.white,
                              size: 11,
                            ),
                            const SizedBox(width: 3),
                            Expanded(
                              child: Text(
                                'Stok: ${_formatQuantity(listing.quantity, listing.unit)}',
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 10,
                                  fontWeight: FontWeight.w700,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ],
                ),
              ),

              // 2. Info Detail Produk
              Padding(
                padding: const EdgeInsets.all(8),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Judul Produk 2 Baris
                    Text(
                      listing.commodity,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        color: Color(0xFF17251E),
                        fontSize: 12.5,
                        fontWeight: FontWeight.w600,
                        height: 1.25,
                      ),
                    ),
                    const SizedBox(height: 6),

                    // Mini Tag Kualitas
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 4,
                            vertical: 1.5,
                          ),
                          decoration: BoxDecoration(
                            color: HomeColors.lightGreen,
                            borderRadius: BorderRadius.circular(2),
                            border: Border.all(
                              color: HomeColors.primaryGreen.withOpacity(0.25),
                              width: 0.6,
                            ),
                          ),
                          child: Text(
                            isGkp ? 'Kadar Air Standar' : 'Siap Kirim Truk',
                            style: const TextStyle(
                              color: HomeColors.primaryGreen,
                              fontSize: 8.5,
                              fontWeight: FontWeight.w800,
                            ),
                          ),
                        ),
                        const SizedBox(width: 4),
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 4,
                            vertical: 1.5,
                          ),
                          decoration: BoxDecoration(
                            color: const Color(0xFFF1F5F0),
                            borderRadius: BorderRadius.circular(2),
                          ),
                          child: const Text(
                            'B2B',
                            style: TextStyle(
                              color: Color(0xFF4B5563),
                              fontSize: 8.5,
                              fontWeight: FontWeight.w800,
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 6),

                    // Harga Hijau Zamrud Bold
                    Row(
                      crossAxisAlignment: CrossAxisAlignment.baseline,
                      textBaseline: TextBaseline.alphabetic,
                      children: [
                        Text(
                          _formatPrice(listing.pricePerUnit),
                          style: const TextStyle(
                            color: HomeColors.primaryGreen,
                            fontSize: 14.5,
                            fontWeight: FontWeight.w900,
                            letterSpacing: -0.3,
                          ),
                        ),
                        Text(
                          ' /${listing.unit}',
                          style: const TextStyle(
                            color: Color(0xFF68766E),
                            fontSize: 10,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 6),

                    // Baris Lokasi & Rating Petani
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Row(
                          children: [
                            Icon(
                              Icons.location_on_rounded,
                              size: 11,
                              color: Color(0xFF888888),
                            ),
                            SizedBox(width: 2),
                            Text(
                              'Jawa Barat',
                              style: TextStyle(
                                color: Color(0xFF888888),
                                fontSize: 10,
                                fontWeight: FontWeight.w500,
                              ),
                            ),
                          ],
                        ),
                        Row(
                          children: [
                            const Icon(
                              Icons.star_rounded,
                              size: 11,
                              color: Color(0xFFF59E0B),
                            ),
                            const SizedBox(width: 1),
                            Text(
                              (4.8 + ((listing.id % 3) * 0.1))
                                  .toStringAsFixed(1),
                              style: const TextStyle(
                                color: Color(0xFF666666),
                                fontSize: 10,
                                fontWeight: FontWeight.w700,
                              ),
                            ),
                          ],
                        ),
                      ],
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

  Widget _buildImageThumbnail(String? imageUrl) {
    final cleanUrl = imageUrl?.trim() ?? '';
    final isValidHttp =
        cleanUrl.startsWith('http://') || cleanUrl.startsWith('https://');

    if (!isValidHttp) {
      return Container(
        color: const Color(0xFFF1F5F0),
        child: const Center(
          child: Icon(
            Icons.grass_rounded,
            color: HomeColors.primaryGreen,
            size: 42,
          ),
        ),
      );
    }

    return Image.network(
      cleanUrl,
      fit: BoxFit.cover,
      errorBuilder: (context, error, stackTrace) => Container(
        color: const Color(0xFFF1F5F0),
        child: const Center(
          child: Icon(
            Icons.image_not_supported_outlined,
            color: Color(0xFF888888),
            size: 32,
          ),
        ),
      ),
    );
  }
}
