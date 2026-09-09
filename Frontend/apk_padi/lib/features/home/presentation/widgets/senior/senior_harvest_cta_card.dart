import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:padi/core/localization/app_language.dart';
import 'package:padi/features/home/presentation/tokens/senior_tokens.dart';

/// Kartu Ajakan Jual Panen Ramah Lansia (Senior Harvest & Marketplace CTA)
/// Hanya ditampilkan jika mendekati panen (<= 14 hari) atau belum ada lahan.
/// Menampilkan info harga gabah ringkas dan 1 tombol besar yang mudah ditekan.
class SeniorHarvestCtaCard extends ConsumerWidget {
  const SeniorHarvestCtaCard({
    super.key,
    required this.onTapMarketplace,
    this.gkpPrice,
    this.gkgPrice,
  });

  final VoidCallback onTapMarketplace;
  final String? gkpPrice;
  final String? gkgPrice;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final lang = ref.watch(languageProvider);

    return Container(
      decoration: BoxDecoration(
        color: SeniorColors.paleGreenBg,
        borderRadius: BorderRadius.circular(SeniorDimensions.cardRadius),
        border: Border.all(color: SeniorColors.greenBorder, width: 2),
        boxShadow: [
          BoxShadow(
            color: SeniorColors.primaryGreen.withValues(alpha: 0.08),
            blurRadius: 10,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      padding: const EdgeInsets.all(SeniorSpacing.cardPadding),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Baris Atas: Ikon Panen + Judul
          Row(
            children: [
              Container(
                width: 48,
                height: 48,
                decoration: const BoxDecoration(
                  color: SeniorColors.lightGreenBg,
                  shape: BoxShape.circle,
                ),
                child: const Icon(
                  Icons.agriculture_rounded,
                  color: SeniorColors.primaryGreen,
                  size: SeniorDimensions.iconMedium,
                ),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Text(
                  switch (lang) {
                    AppLanguage.id => 'Musim Panen Mendekat!',
                    AppLanguage.jv => 'Mangsa Panen Sampun Celak!',
                    AppLanguage.en => 'Harvest Season is Near!',
                  },
                  style: SeniorTypography.subtitle.copyWith(
                    color: SeniorColors.primaryGreen,
                    fontSize: 20,
                    fontWeight: FontWeight.w900,
                  ),
                ),
              ),
            ],
          ),

          const SizedBox(height: SeniorSpacing.md),

          // Teks Ajakan
          Text(
            switch (lang) {
              AppLanguage.id =>
                'Tawarkan hasil gabah Anda langsung ke bursa pasar agar mendapatkan harga terbaik tanpa perantara.',
              AppLanguage.jv =>
                'Sadean gabah panjenengan langsung ing bursa pasar supados angsal rega ingkang sae.',
              AppLanguage.en =>
                'List your paddy harvest directly on the marketplace for transparent pricing.',
            },
            style: SeniorTypography.body.copyWith(
              color: SeniorColors.textPrimary,
              fontSize: 16,
            ),
          ),

          // Info Harga Gabah Ringkas (Jika Ada)
          if (gkpPrice != null && gkpPrice!.isNotEmpty) ...[
            const SizedBox(height: SeniorSpacing.md),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: SeniorColors.greenBorder, width: 1.5),
              ),
              child: Row(
                children: [
                  const Icon(
                    Icons.trending_up_rounded,
                    color: SeniorColors.primaryGreen,
                    size: 24,
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Text(
                      'Harga Rata-rata GKP: $gkpPrice / kg',
                      style: const TextStyle(
                        color: SeniorColors.primaryGreen,
                        fontSize: 16,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],

          const SizedBox(height: SeniorSpacing.lg),

          // Tombol Besar Jual Panen (56px)
          SizedBox(
            height: SeniorDimensions.buttonHeight,
            child: ElevatedButton.icon(
              onPressed: onTapMarketplace,
              icon: const Icon(Icons.storefront_rounded, size: 28),
              label: Text(
                switch (lang) {
                  AppLanguage.id => 'Jual Hasil Panen Sekarang',
                  AppLanguage.jv => 'Sadean Panen Saiki',
                  AppLanguage.en => 'Sell Harvest Now',
                },
                style: SeniorTypography.button.copyWith(
                  color: Colors.white,
                  fontSize: 17,
                ),
              ),
              style: ElevatedButton.styleFrom(
                backgroundColor: SeniorColors.primaryGreen,
                foregroundColor: Colors.white,
                elevation: 2,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(
                    SeniorDimensions.buttonRadius,
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
