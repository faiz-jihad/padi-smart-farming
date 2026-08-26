import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:padi/core/localization/app_language.dart';
import 'package:padi/features/home/presentation/tokens/home_tokens.dart';

class TodayActivitySection extends ConsumerWidget {
  const TodayActivitySection({
    super.key,
    required this.activities,
    required this.onAddActivity,
    required this.onViewTimeline,
  });

  final List<dynamic> activities;
  final VoidCallback onAddActivity;
  final VoidCallback onViewTimeline;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final lang = ref.watch(languageProvider);
    final s = AppStrings(lang);

    final historyLabel = switch (lang) {
      AppLanguage.id => 'Riwayat',
      AppLanguage.jv => 'Riwayat',
      AppLanguage.en => 'History',
    };

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Section Header
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Text(
              s.todayActivities,
              style: HomeTypography.sectionTitle,
            ),
            if (activities.isNotEmpty)
              TextButton(
                onPressed: onViewTimeline,
                style: TextButton.styleFrom(
                  visualDensity: VisualDensity.compact,
                  foregroundColor: HomeColors.primaryGreen,
                  padding: const EdgeInsets.symmetric(horizontal: 6),
                ),
                child: Row(
                  children: [
                    Text(
                      historyLabel,
                      style: const TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                    const SizedBox(width: 2),
                    const Icon(Icons.chevron_right_rounded, size: 16),
                  ],
                ),
              )
            else
              const SizedBox.shrink(),
          ],
        ),

        const SizedBox(height: HomeSpacing.sm),

        // Main Unified Activity Container
        Container(
          decoration: BoxDecoration(
            color: HomeColors.surface,
            borderRadius: BorderRadius.circular(HomeRadius.xl),
            border: Border.all(color: HomeColors.border),
            boxShadow: HomeShadows.subtle,
          ),
          child: activities.isEmpty
              ? _buildEmptyState(s)
              : Column(
                  children: List.generate(
                    activities.length.clamp(0, 3),
                    (index) {
                      final item = activities[index];
                      final isLast = index == (activities.length.clamp(0, 3) - 1);
                      return _buildActivityTile(item, isLast: isLast);
                    },
                  ),
                ),
        ),
      ],
    );
  }

  Widget _buildEmptyState(AppStrings s) {
    final addButtonText = switch (s.lang) {
      AppLanguage.id => '+ Catat',
      AppLanguage.jv => '+ Cathet',
      AppLanguage.en => '+ Log',
    };

    final hintText = switch (s.lang) {
      AppLanguage.id => 'Catat jadwal pemupukan atau pengairan sawah hari ini.',
      AppLanguage.jv => 'Cathet jadwal mupuk utawa ngilekake banyu dina iki.',
      AppLanguage.en => 'Log your fertilizing or irrigation activities for today.',
    };

    return Padding(
      padding: const EdgeInsets.all(HomeSpacing.cardPadding),
      child: Row(
        children: [
          Container(
            width: 44,
            height: 44,
            decoration: BoxDecoration(
              color: HomeColors.surfaceMuted,
              borderRadius: BorderRadius.circular(HomeRadius.md),
            ),
            child: const Icon(
              Icons.checklist_rounded,
              color: HomeColors.textSecondary,
              size: 22,
            ),
          ),
          const SizedBox(width: HomeSpacing.sm),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  s.noActivitiesToday,
                  style: const TextStyle(
                    color: HomeColors.textPrimary,
                    fontSize: 13.5,
                    fontWeight: FontWeight.w700,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  hintText,
                  style: HomeTypography.supporting,
                ),
              ],
            ),
          ),
          const SizedBox(width: HomeSpacing.xs),
          FilledButton(
            onPressed: onAddActivity,
            style: FilledButton.styleFrom(
              backgroundColor: HomeColors.lightGreen,
              foregroundColor: HomeColors.primaryGreen,
              elevation: 0,
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
              minimumSize: const Size(60, 36),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(HomeRadius.sm),
              ),
            ),
            child: Text(
              addButtonText,
              style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w800),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildActivityTile(dynamic item, {required bool isLast}) {
    final title = (item is Map ? item['title'] ?? item['activity_type'] : null)?.toString() ?? 'Aktivitas Sawah';
    final desc = (item is Map ? item['notes'] ?? item['description'] : null)?.toString() ?? '';
    final timeStr = (item is Map ? item['activity_date'] ?? item['created_at'] : null)?.toString() ?? 'Hari ini';

    final iconInfo = _getActivityIcon(title);

    return Column(
      children: [
        Padding(
          padding: const EdgeInsets.all(HomeSpacing.cardPadding),
          child: Row(
            children: [
              Container(
                width: 42,
                height: 42,
                decoration: BoxDecoration(
                  color: iconInfo.bgColor,
                  borderRadius: BorderRadius.circular(HomeRadius.md),
                ),
                child: Icon(
                  iconInfo.icon,
                  color: iconInfo.color,
                  size: 20,
                ),
              ),
              const SizedBox(width: HomeSpacing.sm),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      title,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        color: HomeColors.textPrimary,
                        fontSize: 13.5,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                    if (desc.isNotEmpty) ...[
                      const SizedBox(height: 2),
                      Text(
                        desc,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: HomeTypography.supporting,
                      ),
                    ],
                  ],
                ),
              ),
              const SizedBox(width: HomeSpacing.xs),
              Text(
                timeStr.contains('T') ? timeStr.split('T').first : timeStr,
                style: const TextStyle(
                  color: HomeColors.textSecondary,
                  fontSize: 11,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ],
          ),
        ),
        if (!isLast)
          const Divider(
            height: 1,
            thickness: 1,
            color: HomeColors.borderSubtle,
            indent: 16,
            endIndent: 16,
          ),
      ],
    );
  }

  _ActivityIconInfo _getActivityIcon(String type) {
    final lower = type.toLowerCase();
    if (lower.contains('pupuk')) {
      return const _ActivityIconInfo(
        icon: Icons.grain_rounded,
        color: HomeColors.purple,
        bgColor: HomeColors.purpleBg,
      );
    }
    if (lower.contains('air') || lower.contains('irigasi')) {
      return const _ActivityIconInfo(
        icon: Icons.water_drop_rounded,
        color: HomeColors.skyBlue,
        bgColor: HomeColors.skyBlueBg,
      );
    }
    if (lower.contains('semprot') || lower.contains('hama')) {
      return const _ActivityIconInfo(
        icon: Icons.pest_control_rounded,
        color: HomeColors.danger,
        bgColor: HomeColors.dangerBg,
      );
    }
    if (lower.contains('panen')) {
      return const _ActivityIconInfo(
        icon: Icons.agriculture_rounded,
        color: HomeColors.harvestGold,
        bgColor: HomeColors.harvestGoldBg,
      );
    }
    return const _ActivityIconInfo(
      icon: Icons.task_alt_rounded,
      color: HomeColors.primaryGreen,
      bgColor: HomeColors.lightGreen,
    );
  }
}

class _ActivityIconInfo {
  const _ActivityIconInfo({
    required this.icon,
    required this.color,
    required this.bgColor,
  });

  final IconData icon;
  final Color color;
  final Color bgColor;
}
