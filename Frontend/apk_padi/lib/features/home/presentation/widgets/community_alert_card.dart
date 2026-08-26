import 'package:flutter/material.dart';
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
    final colors = _getSeverityColors();

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
                              'RADAR SEKITAR • ${distanceKm.toStringAsFixed(1)} KM',
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
                            'Lihat laporan sekitar',
                            style: TextStyle(
                              color: colors.icon,
                              fontSize: 12,
                              fontWeight: FontWeight.w800,
                            ),
                          ),
                          const SizedBox(width: 4),
                          Icon(
                            Icons.arrow_forward_rounded,
                            color: colors.icon,
                            size: 14,
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
  }

  _SeverityColorInfo _getSeverityColors() {
    switch (severity) {
      case AlertSeverity.high:
        return const _SeverityColorInfo(
          icon: HomeColors.danger,
          bg: HomeColors.dangerBg,
          border: Color(0xFFFECACA),
        );
      case AlertSeverity.medium:
        return const _SeverityColorInfo(
          icon: HomeColors.warning,
          bg: HomeColors.warningBg,
          border: Color(0xFFFDE68A),
        );
      case AlertSeverity.low:
        return const _SeverityColorInfo(
          icon: HomeColors.primaryGreen,
          bg: HomeColors.lightGreen,
          border: HomeColors.border,
        );
    }
  }
}

class _SeverityColorInfo {
  const _SeverityColorInfo({
    required this.icon,
    required this.bg,
    required this.border,
  });

  final Color icon;
  final Color bg;
  final Color border;
}
