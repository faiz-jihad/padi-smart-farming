import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:padi/core/localization/app_language.dart';
import 'package:padi/features/home/presentation/tokens/home_tokens.dart';

class QuickActionGrid extends ConsumerWidget {
  const QuickActionGrid({
    super.key,
    required this.onScanTap,
    required this.onActivityTap,
    required this.onFarmTap,
    required this.onMarketTap,
    required this.onFertilizerTap,
    required this.onCalendarTap,
    required this.onAlertTap,
    required this.onTimelineTap,
  });

  final VoidCallback onScanTap;
  final VoidCallback onActivityTap;
  final VoidCallback onFarmTap;
  final VoidCallback onMarketTap;
  final VoidCallback onFertilizerTap;
  final VoidCallback onCalendarTap;
  final VoidCallback onAlertTap;
  final VoidCallback onTimelineTap;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final lang = ref.watch(languageProvider);
    final s = AppStrings(lang);

    final featuresCountText = switch (lang) {
      AppLanguage.id => '8 Fitur',
      AppLanguage.jv => '8 Fitur Tani',
      AppLanguage.en => '8 Tools',
    };

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
      decoration: BoxDecoration(
        color: HomeColors.surface,
        borderRadius: BorderRadius.circular(HomeRadius.xl),
        border: Border.all(color: HomeColors.border),
        boxShadow: HomeShadows.subtle,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  const Icon(Icons.grid_view_rounded, color: HomeColors.primaryGreen, size: 18),
                  const SizedBox(width: 6),
                  Text(
                    s.quickActions,
                    style: HomeTypography.cardTitle,
                  ),
                ],
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                decoration: BoxDecoration(
                  color: HomeColors.lightGreen,
                  borderRadius: BorderRadius.circular(HomeRadius.pill),
                ),
                child: Text(
                  featuresCountText,
                  style: const TextStyle(
                    color: HomeColors.primaryGreen,
                    fontSize: 10,
                    fontWeight: FontWeight.w800,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: HomeSpacing.md),

          // 8 Action Buttons Grid
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceAround,
            children: [
              _buildAppMenuItem(
                icon: Icons.camera_alt_rounded,
                label: s.checkCrops,
                color: const Color(0xFF047857),
                bgColor: const Color(0xFFDCFCE7),
                onTap: onScanTap,
                isHighlighted: true,
              ),
              _buildAppMenuItem(
                icon: Icons.grass_rounded,
                label: s.addFarm,
                color: const Color(0xFF059669),
                bgColor: const Color(0xFFECFDF5),
                onTap: onFarmTap,
              ),
              _buildAppMenuItem(
                icon: Icons.science_rounded,
                label: s.fertilizerDose,
                color: const Color(0xFF0F5132),
                bgColor: const Color(0xFFEAF5EF),
                onTap: onFertilizerTap,
              ),
              _buildAppMenuItem(
                icon: Icons.calendar_month_rounded,
                label: s.plantingCalendar,
                color: const Color(0xFF065F46),
                bgColor: const Color(0xFFF0FDF4),
                onTap: onCalendarTap,
              ),
            ],
          ),

          const SizedBox(height: 14),

          Row(
            mainAxisAlignment: MainAxisAlignment.spaceAround,
            children: [
              _buildAppMenuItem(
                icon: Icons.edit_calendar_rounded,
                label: s.logActivity,
                color: const Color(0xFF047857),
                bgColor: const Color(0xFFDCFCE7),
                onTap: onActivityTap,
              ),
              _buildAppMenuItem(
                icon: Icons.storefront_rounded,
                label: s.sellHarvest,
                color: const Color(0xFF0F5132),
                bgColor: const Color(0xFFECFDF5),
                onTap: onMarketTap,
              ),
              _buildAppMenuItem(
                icon: Icons.shield_outlined,
                label: s.pestRadar,
                color: const Color(0xFF065F46),
                bgColor: const Color(0xFFEAF5EF),
                onTap: onAlertTap,
              ),
              _buildAppMenuItem(
                icon: Icons.timeline_rounded,
                label: s.cropTimeline,
                color: const Color(0xFF059669),
                bgColor: const Color(0xFFF0FDF4),
                onTap: onTimelineTap,
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildAppMenuItem({
    required IconData icon,
    required String label,
    required Color color,
    required Color bgColor,
    required VoidCallback onTap,
    bool isHighlighted = false,
  }) {
    return SizedBox(
      width: 72,
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: onTap,
          borderRadius: BorderRadius.circular(HomeRadius.md),
          child: Padding(
            padding: const EdgeInsets.symmetric(vertical: 4),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Container(
                  width: 48,
                  height: 48,
                  decoration: BoxDecoration(
                    color: bgColor,
                    borderRadius: BorderRadius.circular(HomeRadius.lg),
                    boxShadow: [
                      BoxShadow(
                        color: color.withOpacity(0.12),
                        blurRadius: 6,
                        offset: const Offset(0, 2),
                      ),
                    ],
                  ),
                  child: Center(
                    child: Icon(
                      icon,
                      color: color,
                      size: 22,
                    ),
                  ),
                ),
                const SizedBox(height: 6),
                Text(
                  label,
                  textAlign: TextAlign.center,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(
                    color: HomeColors.textPrimary,
                    fontSize: 10.5,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
