import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:padi/core/localization/app_language.dart';
import 'package:padi/features/home/presentation/tokens/senior_tokens.dart';

/// 4 Menu Aksi Utama Ramah Lansia (Senior Quick Actions)
/// Kartu dibuat besar, hijau-putih, dan memakai bahasa tindakan langsung.
class SeniorQuickActions extends ConsumerWidget {
  const SeniorQuickActions({
    super.key,
    required this.onScanTap,
    required this.onActivityTap,
    required this.onMarketTap,
    required this.onCalendarTap,
  });

  final VoidCallback onScanTap;
  final VoidCallback onActivityTap;
  final VoidCallback onMarketTap;
  final VoidCallback onCalendarTap;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final lang = ref.watch(languageProvider);

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Judul Seksi (24px w800)
        Text(switch (lang) {
          AppLanguage.id => 'Apa yang ingin dilakukan?',
          AppLanguage.jv => 'Menu Utama Tani',
          AppLanguage.en => 'Main Farming Menu',
        }, style: SeniorTypography.title),
        const SizedBox(height: SeniorSpacing.md),

        // Grid 2x2 Kartu Besar
        Row(
          children: [
            Expanded(
              child: _buildActionCard(
                icon: Icons.camera_alt_rounded,
                title: switch (lang) {
                  AppLanguage.id => 'Cek Daun',
                  AppLanguage.jv => 'Priksa Godhong',
                  AppLanguage.en => 'Scan Leaves',
                },
                subtitle: switch (lang) {
                  AppLanguage.id => 'Foto daun padi',
                  AppLanguage.jv => 'Cek Omo & Penyakit',
                  AppLanguage.en => 'Diagnose Pests',
                },
                accentColor: SeniorColors.primaryGreen,
                bgColor: SeniorColors.paleGreenBg,
                borderColor: SeniorColors.greenBorder,
                onTap: onScanTap,
              ),
            ),
            const SizedBox(width: SeniorSpacing.md),
            Expanded(
              child: _buildActionCard(
                icon: Icons.edit_calendar_rounded,
                title: switch (lang) {
                  AppLanguage.id => 'Catat Sawah',
                  AppLanguage.jv => 'Cathet Pakaryan',
                  AppLanguage.en => 'Log Activity',
                },
                subtitle: switch (lang) {
                  AppLanguage.id => 'Pupuk, air, semprot',
                  AppLanguage.jv => 'Rabuk & Semprot',
                  AppLanguage.en => 'Fertilize & Spray',
                },
                accentColor: SeniorColors.blueAccent,
                bgColor: SeniorColors.blueBg,
                borderColor: SeniorColors.blueBorder,
                onTap: onActivityTap,
              ),
            ),
          ],
        ),
        const SizedBox(height: SeniorSpacing.md),
        Row(
          children: [
            Expanded(
              child: _buildActionCard(
                icon: Icons.storefront_rounded,
                title: switch (lang) {
                  AppLanguage.id => 'Jual Panen',
                  AppLanguage.jv => 'Sadean Panen',
                  AppLanguage.en => 'Sell Harvest',
                },
                subtitle: switch (lang) {
                  AppLanguage.id => 'Lihat harga gabah',
                  AppLanguage.jv => 'Bursa & Rega Gabah',
                  AppLanguage.en => 'Market & Prices',
                },
                accentColor: SeniorColors.amberAccent,
                bgColor: SeniorColors.amberBg,
                borderColor: SeniorColors.amberBorder,
                onTap: onMarketTap,
              ),
            ),
            const SizedBox(width: SeniorSpacing.md),
            Expanded(
              child: _buildActionCard(
                icon: Icons.calendar_month_rounded,
                title: switch (lang) {
                  AppLanguage.id => 'Cuaca Sawah',
                  AppLanguage.jv => 'Jadwal & Hawa',
                  AppLanguage.en => 'Schedule & Weather',
                },
                subtitle: switch (lang) {
                  AppLanguage.id => 'Peringatan & jadwal',
                  AppLanguage.jv => 'Tanggalan Tandur',
                  AppLanguage.en => 'Crop Calendar',
                },
                accentColor: SeniorColors.tealAccent,
                bgColor: SeniorColors.tealBg,
                borderColor: SeniorColors.tealBorder,
                onTap: onCalendarTap,
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildActionCard({
    required IconData icon,
    required String title,
    required String subtitle,
    required Color accentColor,
    required Color bgColor,
    required Color borderColor,
    required VoidCallback onTap,
  }) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(SeniorDimensions.cardRadius),
        child: Ink(
          height: 156,
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
          decoration: BoxDecoration(
            color: bgColor,
            borderRadius: BorderRadius.circular(SeniorDimensions.cardRadius),
            border: Border.all(color: borderColor, width: 2),
            boxShadow: [
              BoxShadow(
                color: accentColor.withValues(alpha: 0.06),
                blurRadius: 8,
                offset: const Offset(0, 3),
              ),
            ],
          ),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              // Ikon Besar (40px) dengan wadah melingkar
              Container(
                width: 48,
                height: 48,
                decoration: BoxDecoration(
                  color: Colors.white,
                  shape: BoxShape.circle,
                  border: Border.all(color: borderColor, width: 1.5),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.04),
                      blurRadius: 4,
                      offset: const Offset(0, 2),
                    ),
                  ],
                ),
                child: Icon(
                  icon,
                  color: accentColor,
                  size: SeniorDimensions.iconLarge,
                ),
              ),

              // Teks Label 2 Baris (Judul 18px w800, Subjudul 15px w600)
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: SeniorTypography.subtitle.copyWith(
                      color: accentColor,
                      fontSize: 19,
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    subtitle,
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                    style: SeniorTypography.caption.copyWith(
                      color: SeniorColors.textSecondary,
                      fontSize: 15,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}
