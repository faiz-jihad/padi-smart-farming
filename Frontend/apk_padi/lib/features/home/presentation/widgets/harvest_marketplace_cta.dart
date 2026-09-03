import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:padi/core/localization/app_language.dart';
import 'package:padi/features/home/presentation/tokens/home_tokens.dart';

class HarvestMarketplaceCta extends StatelessWidget {
  const HarvestMarketplaceCta({
    super.key,
    required this.onTapMarketplace,
    required this.onTapCreateListing,
  });

  final VoidCallback onTapMarketplace;
  final VoidCallback onTapCreateListing;

  @override
  Widget build(BuildContext context) {
    return Consumer(
      builder: (context, ref, _) {
        final lang = ref.watch(languageProvider);
        final s = AppStrings(lang);

    final title = switch (lang) {
      AppLanguage.id => 'Jual Hasil Panen Petani',
      AppLanguage.jv => 'Adol Asil Panen Petani',
      AppLanguage.en => 'Sell Farmer Crop Harvest',
    };

    final subtitle = switch (lang) {
      AppLanguage.id => 'Hubungkan gabah langsung ke mitra pembeli & penggilingan.',
      AppLanguage.jv => 'Sambungake gabah langsung marang bakul & panggilingan.',
      AppLanguage.en => 'Connect grain directly to verified buyers & mills.',
    };

    final createOfferLabel = switch (lang) {
      AppLanguage.id => '+ Buat Penawaran',
      AppLanguage.jv => '+ Gawe Tawaran',
      AppLanguage.en => '+ Create Offer',
    };

    final openMarketLabel = switch (lang) {
      AppLanguage.id => 'Buka Pasar',
      AppLanguage.jv => 'Bukak Pasar',
      AppLanguage.en => 'Open Market',
    };

    return Container(
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(HomeRadius.xl),
        boxShadow: [
          BoxShadow(
            color: HomeColors.harvestGold.withValues(alpha: 0.25),
            blurRadius: 18,
            offset: const Offset(0, 6),
          ),
        ],
        image: const DecorationImage(
          image: AssetImage('assets/images/onboarding_3.jpeg'),
          fit: BoxFit.cover,
        ),
      ),
      child: Container(
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(HomeRadius.xl),
          gradient: LinearGradient(
            begin: Alignment.centerLeft,
            end: Alignment.centerRight,
            colors: [
              const Color(0xFF78350F).withValues(alpha: 0.94),
              const Color(0xFF92400E).withValues(alpha: 0.88),
              const Color(0xFFB45309).withValues(alpha: 0.80),
            ],
          ),
        ),
        padding: const EdgeInsets.all(HomeSpacing.cardPadding),
        child: Row(
          children: [
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisSize: MainAxisSize.min,
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.22),
                      borderRadius: BorderRadius.circular(HomeRadius.pill),
                    ),
                    child: Text(
                      s.harvestMarketTitle,
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 9.5,
                        fontWeight: FontWeight.w900,
                        letterSpacing: 0.5,
                      ),
                    ),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    title,
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 16,
                      fontWeight: FontWeight.w900,
                      letterSpacing: -0.2,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    subtitle,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: TextStyle(
                      color: Colors.white.withValues(alpha: 0.90),
                      fontSize: 11.5,
                      height: 1.3,
                    ),
                  ),
                  const SizedBox(height: 10),

                  // Actions
                  Row(
                    children: [
                      FilledButton(
                        onPressed: onTapCreateListing,
                        style: FilledButton.styleFrom(
                          backgroundColor: const Color(0xFFFBBF24),
                          foregroundColor: const Color(0xFF78350F),
                          padding: const EdgeInsets.symmetric(
                            horizontal: 12,
                            vertical: 6,
                          ),
                          minimumSize: const Size(60, 32),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(HomeRadius.sm),
                          ),
                        ),
                        child: Text(
                          createOfferLabel,
                          style: const TextStyle(
                            fontSize: 11.5,
                            fontWeight: FontWeight.w900,
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      TextButton(
                        onPressed: onTapMarketplace,
                        style: TextButton.styleFrom(
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(
                            horizontal: 8,
                            vertical: 6,
                          ),
                          minimumSize: const Size(40, 32),
                        ),
                        child: Text(
                          openMarketLabel,
                          style: const TextStyle(
                            fontSize: 11.5,
                            fontWeight: FontWeight.w700,
                            decoration: TextDecoration.underline,
                            decorationColor: Colors.white,
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(width: HomeSpacing.sm),
            Container(
              width: 48,
              height: 48,
              decoration: BoxDecoration(
                color: Colors.white.withValues(alpha: 0.18),
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.storefront_rounded,
                color: Colors.white,
                size: 26,
              ),
            ),
          ],
        ),
      ),
    );
      },
    );
  }
}
