import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:padi/core/localization/app_language.dart';
import 'package:padi/features/home/presentation/tokens/home_tokens.dart';

enum AlertSeverity { low, medium, high }

class CommunityAlertCard extends StatelessWidget {
  const CommunityAlertCard({
    super.key,
    required this.title,
    required this.subtitle,
    required this.onTapAlerts,
    this.severity = AlertSeverity.medium,
    this.distanceKm = 3.2,
  });

  final String title;
  final String subtitle;
  final VoidCallback onTapAlerts;
  final AlertSeverity severity;
  final double distanceKm;

  @override
  Widget build(BuildContext context) {
    return Consumer(
      builder: (context, ref, _) {
        final lang = ref.watch(languageProvider);
        final colors = _getSeverityColors();

    final radarBadge = switch (lang) {
      AppLanguage.id => 'RADAR SEKITAR • ${distanceKm.toStringAsFixed(1)} KM',
      AppLanguage.jv => 'RADAR SEKITAR • ${distanceKm.toStringAsFixed(1)} KM',
      AppLanguage.en => 'NEARBY RADAR • ${distanceKm.toStringAsFixed(1)} KM',
    };

    final viewReportLabel = switch (lang) {
      AppLanguage.id => 'Lihat laporan sekitar',
      AppLanguage.jv => 'Delok lapuran sekitar',
      AppLanguage.en => 'View nearby reports',
    };

    return Container(
      decoration: BoxDecoration(
        color: HomeColors.surface,
        borderRadius: BorderRadius.circular(HomeRadius.xl),
        border: Border.all(color: colors.border),
        boxShadow: HomeShadows.subtle,
      ),
      child: Material(
        color: Colors.transparent,
        borderRadius: BorderRadius.circular(HomeRadius.xl),
        child: InkWell(
          onTap: onTapAlerts,
          borderRadius: BorderRadius.circular(HomeRadius.xl),
          child: Padding(
            padding: const EdgeInsets.all(HomeSpacing.cardPadding),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Severity Icon Box
                Container(
                  width: 44,
                  height: 44,
                  decoration: BoxDecoration(
                    color: colors.bg,
                    borderRadius: BorderRadius.circular(HomeRadius.md),
                  ),
                  child: Icon(
                    Icons.shield_outlined,
                    color: colors.icon,
                    size: 22,
                  ),
                ),
                const SizedBox(width: HomeSpacing.sm),

                // Content
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Badge
                      Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                            decoration: BoxDecoration(
                              color: colors.bg,
                              borderRadius: BorderRadius.circular(HomeRadius.pill),
                            ),
                            child: Text(
                              radarBadge,
                              style: TextStyle(
                                color: colors.icon,
                                fontSize: 9.5,
                                fontWeight: FontWeight.w800,
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 5),

                      Text(
                        title,
                        style: const TextStyle(
                          color: HomeColors.textPrimary,
                          fontSize: 13.5,
                          fontWeight: FontWeight.w800,
                          letterSpacing: -0.2,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        subtitle,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: HomeTypography.supporting,
                      ),
                      const SizedBox(height: 8),

                      // CTA
                      Row(
                        children: [
                          Text(
                            viewReportLabel,
                            style: TextStyle(
                              color: colors.icon,
                              fontSize: 12,
                              fontWeight: FontWeight.w800,
                            ),
                          ),
                          const SizedBox(width: 3),
                          Icon(
                            Icons.chevron_right_rounded,
                            size: 16,
                            color: colors.icon,
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
      ),
    );
      },
    );
  }

  ({Color bg, Color border, Color icon}) _getSeverityColors() {
    switch (severity) {
      case AlertSeverity.high:
        return (
          bg: HomeColors.warningBg,
          border: const Color(0xFFFED7AA),
          icon: HomeColors.warning,
        );
      case AlertSeverity.medium:
        return (
          bg: HomeColors.skyBlueBg,
          border: const Color(0xFFBAE6FD),
          icon: HomeColors.skyBlue,
        );
      case AlertSeverity.low:
        return (
          bg: HomeColors.lightGreen,
          border: const Color(0xFFBBF7D0),
          icon: HomeColors.primaryGreen,
        );
    }
  }
}
