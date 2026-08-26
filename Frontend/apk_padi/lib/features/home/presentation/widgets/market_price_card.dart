import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:padi/core/localization/app_language.dart';
import 'package:padi/features/home/presentation/tokens/home_tokens.dart';

class MarketPriceCard extends ConsumerWidget {
  const MarketPriceCard({
    super.key,
    required this.onTapMarket,
    this.gkpPrice = 'Rp 6.800',
    this.gkgPrice = 'Rp 7.400',
  });

  final VoidCallback onTapMarket;
  final String gkpPrice;
  final String gkgPrice;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final lang = ref.watch(languageProvider);
    final s = AppStrings(lang);

    final gkpLabel = switch (lang) {
      AppLanguage.id => 'Gabah Kering Panen (GKP)',
      AppLanguage.jv => 'Gabah Teles Panen (GKP)',
      AppLanguage.en => 'Harvested Dry Paddy (GKP)',
    };

    final gkgLabel = switch (lang) {
      AppLanguage.id => 'Gabah Kering Giling (GKG)',
      AppLanguage.jv => 'Gabah Garing Giling (GKG)',
      AppLanguage.en => 'Milled Dry Paddy (GKG)',
    };

    final marketDetailText = switch (lang) {
      AppLanguage.id => 'Buka Pasar & Transaksi Langsung',
      AppLanguage.jv => 'Bukak Pasar & Adol Langsung',
      AppLanguage.en => 'Open Market & Trade Directly',
    };

    return Container(
      decoration: BoxDecoration(
        color: HomeColors.surface,
        borderRadius: BorderRadius.circular(HomeRadius.xl),
        border: Border.all(color: HomeColors.border),
        boxShadow: HomeShadows.subtle,
      ),
      child: Material(
        color: Colors.transparent,
        borderRadius: BorderRadius.circular(HomeRadius.xl),
        child: InkWell(
          onTap: onTapMarket,
          borderRadius: BorderRadius.circular(HomeRadius.xl),
          child: Padding(
            padding: const EdgeInsets.all(HomeSpacing.cardPadding),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Header
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(6),
                          decoration: BoxDecoration(
                            color: HomeColors.harvestGoldBg,
                            borderRadius: BorderRadius.circular(HomeRadius.sm),
                          ),
                          child: const Icon(
                            Icons.trending_up_rounded,
                            color: HomeColors.harvestGold,
                            size: 18,
                          ),
                        ),
                        const SizedBox(width: HomeSpacing.xs),
                        Text(
                          s.todayPaddyPrices,
                          style: HomeTypography.cardTitle,
                        ),
                      ],
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                      decoration: BoxDecoration(
                        color: const Color(0xFFDCFCE7),
                        borderRadius: BorderRadius.circular(HomeRadius.pill),
                      ),
                      child: const Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(Icons.arrow_upward_rounded, color: Color(0xFF16A34A), size: 11),
                          SizedBox(width: 2),
                          Text(
                            '+2.4%',
                            style: TextStyle(
                              color: Color(0xFF16A34A),
                              fontSize: 10,
                              fontWeight: FontWeight.w800,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),

                const SizedBox(height: HomeSpacing.md),

                // 2 Price columns
                Row(
                  children: [
                    Expanded(
                      child: Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: HomeColors.background,
                          borderRadius: BorderRadius.circular(HomeRadius.md),
                          border: Border.all(color: HomeColors.borderSubtle),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              gkpLabel,
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: const TextStyle(
                                color: HomeColors.textSecondary,
                                fontSize: 11,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              '$gkpPrice/kg',
                              style: const TextStyle(
                                color: HomeColors.textPrimary,
                                fontSize: 16,
                                fontWeight: FontWeight.w900,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(width: HomeSpacing.sm),
                    Expanded(
                      child: Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: HomeColors.background,
                          borderRadius: BorderRadius.circular(HomeRadius.md),
                          border: Border.all(color: HomeColors.borderSubtle),
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              gkgLabel,
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: const TextStyle(
                                color: HomeColors.textSecondary,
                                fontSize: 11,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              '$gkgPrice/kg',
                              style: const TextStyle(
                                color: HomeColors.textPrimary,
                                fontSize: 16,
                                fontWeight: FontWeight.w900,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ],
                ),

                const SizedBox(height: HomeSpacing.sm),

                Row(
                  mainAxisAlignment: MainAxisAlignment.end,
                  children: [
                    Text(
                      marketDetailText,
                      style: const TextStyle(
                        color: HomeColors.primaryGreen,
                        fontSize: 11.5,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                    const SizedBox(width: 4),
                    const Icon(
                      Icons.arrow_forward_rounded,
                      color: HomeColors.primaryGreen,
                      size: 14,
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
