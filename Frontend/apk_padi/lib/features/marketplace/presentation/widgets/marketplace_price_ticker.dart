import 'package:flutter/material.dart';
import 'package:padi/features/home/presentation/tokens/home_tokens.dart';
import 'package:padi/features/marketplace/data/models/market_listing_model.dart';

class MarketplacePriceTicker extends StatelessWidget {
  const MarketplacePriceTicker({
    super.key,
    this.listings,
  });

  final List<MarketListingModel>? listings;

  String _formatCurrency(double val) {
    final formatted = val
        .round()
        .toString()
        .replaceAllMapped(RegExp(r'\B(?=(\d{3})+(?!\d))'), (match) => '.');
    return 'Rp$formatted';
  }

  (String, String, bool) _calculateIndex({
    required List<MarketListingModel> all,
    required List<String> keywords,
    required double benchmarkPrice,
    required String defaultChange,
  }) {
    if (all.isEmpty) {
      return (_formatCurrency(benchmarkPrice), defaultChange, true);
    }

    final matching = all.where((l) {
      final c = l.commodity.toLowerCase();
      final d = (l.description ?? '').toLowerCase();
      return keywords.any((k) => c.contains(k) || d.contains(k));
    }).toList();

    if (matching.isEmpty) {
      return (_formatCurrency(benchmarkPrice), defaultChange, true);
    }

    double totalPrice = 0;
    for (final item in matching) {
      totalPrice += item.pricePerUnit;
    }
    final avgPrice = totalPrice / matching.length;
    final diff = avgPrice - benchmarkPrice;
    final pct = (diff / benchmarkPrice) * 100;
    final isPos = pct >= 0;
    final sign = isPos ? '+' : '';
    final changeText =
        pct.abs() < 0.2 ? 'Stabil' : '$sign${pct.toStringAsFixed(1)}%';

    return (_formatCurrency(avgPrice), changeText, isPos);
  }

  @override
  Widget build(BuildContext context) {
    final allListings = listings ?? const [];

    final (gkpPrice, gkpChange, gkpIsPos) = _calculateIndex(
      all: allListings,
      keywords: ['gkp', 'panen'],
      benchmarkPrice: 6800,
      defaultChange: '+2.3%',
    );

    final (gkgPrice, gkgChange, gkgIsPos) = _calculateIndex(
      all: allListings,
      keywords: ['gkg', 'giling'],
      benchmarkPrice: 7400,
      defaultChange: '+1.4%',
    );

    final (mediumPrice, mediumChange, mediumIsPos) = _calculateIndex(
      all: allListings,
      keywords: ['medium', 'ir64', 'ciherang'],
      benchmarkPrice: 13200,
      defaultChange: 'Stabil',
    );

    final (premPrice, premChange, premIsPos) = _calculateIndex(
      all: allListings,
      keywords: ['premium', 'pandan wangi', 'mentik', 'organik', 'beras'],
      benchmarkPrice: 15000,
      defaultChange: '+0.8%',
    );

    return Container(
      color: Colors.white,
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Bursa Panen Hari Ini Bar (Tema Hijau Zamrud P.A.D.I.)
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(4),
                    decoration: BoxDecoration(
                      color: HomeColors.deepGreen,
                      borderRadius: BorderRadius.circular(4),
                    ),
                    child: const Icon(
                      Icons.trending_up_rounded,
                      color: Colors.white,
                      size: 15,
                    ),
                  ),
                  const SizedBox(width: 7),
                  const Text(
                    'BURSA PANEN HARI INI',
                    style: TextStyle(
                      color: HomeColors.deepGreen,
                      fontSize: 12.5,
                      fontWeight: FontWeight.w900,
                      letterSpacing: -0.2,
                    ),
                  ),
                ],
              ),
              Row(
                children: [
                  _buildTimerBlock('08'),
                  const Text(' : ', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 11)),
                  _buildTimerBlock('30'),
                  const Text(' : ', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 11)),
                  _buildTimerBlock('45'),
                ],
              ),
            ],
          ),

          const SizedBox(height: 8),

          // 4 Item Harga Acuan
          Row(
            children: [
              Expanded(
                child: _buildPriceItem('GKP Panen', gkpPrice, gkpChange, gkpIsPos),
              ),
              const SizedBox(width: 6),
              Expanded(
                child: _buildPriceItem('GKG Giling', gkgPrice, gkgChange, gkgIsPos),
              ),
              const SizedBox(width: 6),
              Expanded(
                child: _buildPriceItem('Beras Med', mediumPrice, mediumChange, mediumIsPos),
              ),
              const SizedBox(width: 6),
              Expanded(
                child: _buildPriceItem('Beras Prem', premPrice, premChange, premIsPos),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildTimerBlock(String value) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 2),
      decoration: BoxDecoration(
        color: const Color(0xFF042F1E),
        borderRadius: BorderRadius.circular(3),
      ),
      child: Text(
        value,
        style: const TextStyle(
          color: Colors.white,
          fontSize: 10,
          fontWeight: FontWeight.w800,
        ),
      ),
    );
  }

  Widget _buildPriceItem(
    String label,
    String price,
    String change,
    bool isPositive,
  ) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 6),
      decoration: BoxDecoration(
        color: const Color(0xFFF9FAF8),
        borderRadius: BorderRadius.circular(6),
        border: Border.all(color: const Color(0xFFE8ECE7), width: 0.8),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            label,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: const TextStyle(
              color: Color(0xFF666666),
              fontSize: 9.5,
              fontWeight: FontWeight.w600,
            ),
          ),
          const SizedBox(height: 2),
          Text(
            price,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: const TextStyle(
              color: HomeColors.primaryGreen,
              fontSize: 11.5,
              fontWeight: FontWeight.w900,
            ),
          ),
        ],
      ),
    );
  }
}
