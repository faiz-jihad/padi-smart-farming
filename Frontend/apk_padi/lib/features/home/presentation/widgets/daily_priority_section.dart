import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:padi/features/home/presentation/tokens/home_tokens.dart';

class DailyPriorityItem {
  const DailyPriorityItem({
    required this.id,
    required this.type,
    required this.urgency,
    required this.title,
    required this.subtitle,
    required this.actionLabel,
    required this.route,
    required this.icon,
  });

  factory DailyPriorityItem.fromJson(Map<String, dynamic> json) {
    return DailyPriorityItem(
      id: json['id']?.toString() ?? '',
      type: json['type']?.toString() ?? 'general',
      urgency: json['urgency']?.toString() ?? 'info',
      title: json['title']?.toString() ?? '',
      subtitle: json['subtitle']?.toString() ?? '',
      actionLabel: json['action_label']?.toString() ?? 'Lihat',
      route: json['route']?.toString() ?? '/home',
      icon: json['icon']?.toString() ?? 'info',
    );
  }

  final String id;
  final String type;
  final String urgency;
  final String title;
  final String subtitle;
  final String actionLabel;
  final String route;
  final String icon;
}

class DailyPrioritySection extends StatelessWidget {
  const DailyPrioritySection({
    super.key,
    required this.priorities,
    this.hst,
    this.farmName,
    this.isLoading = false,
  });

  final List<DailyPriorityItem> priorities;
  final int? hst;
  final String? farmName;
  final bool isLoading;

  @override
  Widget build(BuildContext context) {
    if (isLoading) {
      return Container(
        height: 120,
        decoration: BoxDecoration(
          color: HomeColors.surface,
          borderRadius: BorderRadius.circular(HomeRadius.xl),
          border: Border.all(color: HomeColors.border),
        ),
        child: const Center(
          child: SizedBox(
            width: 24,
            height: 24,
            child: CircularProgressIndicator(strokeWidth: 2, color: HomeColors.primaryGreen),
          ),
        ),
      );
    }

    if (priorities.isEmpty) {
      return const SizedBox.shrink();
    }

    return Container(
      decoration: BoxDecoration(
        color: HomeColors.surface,
        borderRadius: BorderRadius.circular(HomeRadius.xl),
        border: Border.all(color: HomeColors.border),
        boxShadow: HomeShadows.subtle,
      ),
      padding: const EdgeInsets.all(HomeSpacing.cardPadding),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
                decoration: BoxDecoration(
                  color: const Color(0xFF0E3E28).withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Icon(Icons.flash_on_rounded, size: 14, color: Color(0xFF0E3E28)),
                    const SizedBox(width: 4),
                    Text(
                      hst != null ? 'Prioritas Hari Ini - HST $hst' : 'Prioritas Tindakan Hari Ini',
                      style: const TextStyle(
                        fontSize: 11.5,
                        fontWeight: FontWeight.w800,
                        color: Color(0xFF0E3E28),
                        letterSpacing: 0.2,
                      ),
                    ),
                  ],
                ),
              ),
              const Spacer(),
              if (farmName != null && farmName!.isNotEmpty)
                Text(
                  farmName!,
                  style: const TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w600,
                    color: HomeColors.textSecondary,
                  ),
                ),
            ],
          ),
          const SizedBox(height: 12),
          ListView.separated(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: priorities.length,
            separatorBuilder: (context, index) => const SizedBox(height: 10),
            itemBuilder: (context, index) {
              final item = priorities[index];
              return _buildPriorityTile(context, item);
            },
          ),
        ],
      ),
    );
  }

  Widget _buildPriorityTile(BuildContext context, DailyPriorityItem item) {
    final Color badgeColor;
    final Color badgeBg;
    final IconData iconData;

    switch (item.urgency) {
      case 'urgent':
        badgeColor = const Color(0xFFDC2626);
        badgeBg = const Color(0xFFFEF2F2);
        iconData = Icons.warning_amber_rounded;
        break;
      case 'warning':
        badgeColor = const Color(0xFFD97706);
        badgeBg = const Color(0xFFFFFBEB);
        iconData = Icons.info_outline_rounded;
        break;
      case 'success':
        badgeColor = const Color(0xFF059669);
        badgeBg = const Color(0xFFECFDF5);
        iconData = Icons.check_circle_outline_rounded;
        break;
      case 'info':
      default:
        badgeColor = const Color(0xFF0284C7);
        badgeBg = const Color(0xFFF0F9FF);
        iconData = Icons.lightbulb_outline_rounded;
        break;
    }

    return InkWell(
      onTap: () => context.push(item.route),
      borderRadius: BorderRadius.circular(HomeRadius.md),
      child: Container(
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: badgeBg,
          borderRadius: BorderRadius.circular(HomeRadius.md),
          border: Border.all(color: badgeColor.withValues(alpha: 0.25)),
        ),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              padding: const EdgeInsets.all(7),
              decoration: BoxDecoration(
                color: Colors.white,
                shape: BoxShape.circle,
                border: Border.all(color: badgeColor.withValues(alpha: 0.3)),
              ),
              child: Icon(iconData, size: 17, color: badgeColor),
            ),
            const SizedBox(width: 11),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    item.title,
                    style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w800,
                      color: badgeColor,
                    ),
                  ),
                  const SizedBox(height: 3),
                  Text(
                    item.subtitle,
                    style: const TextStyle(
                      fontSize: 11.5,
                      height: 1.35,
                      color: Color(0xFF334155),
                    ),
                  ),
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      Text(
                        item.actionLabel,
                        style: TextStyle(
                          fontSize: 11.5,
                          fontWeight: FontWeight.w700,
                          color: badgeColor,
                        ),
                      ),
                      const SizedBox(width: 4),
                      Icon(Icons.arrow_forward_rounded, size: 13, color: badgeColor),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
