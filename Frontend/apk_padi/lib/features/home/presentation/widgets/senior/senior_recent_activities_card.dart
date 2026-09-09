import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:padi/core/localization/app_language.dart';
import 'package:padi/features/home/presentation/tokens/senior_tokens.dart';

/// Catatan Kegiatan Terbaru Ramah Lansia (Senior Recent Activities Card)
/// Menampilkan maksimal 3 kegiatan sawah terbaru dengan font besar,
/// ikon jelas, dan tombol besar "+ Catat Kegiatan Baru" (56px).
class SeniorRecentActivitiesCard extends ConsumerWidget {
  const SeniorRecentActivitiesCard({
    super.key,
    required this.activities,
    required this.onAddActivity,
    required this.onViewAll,
  });

  final List<dynamic> activities;
  final VoidCallback onAddActivity;
  final VoidCallback onViewAll;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final lang = ref.watch(languageProvider);

    final displayList = activities.take(3).toList();

    return Container(
      decoration: BoxDecoration(
        color: SeniorColors.surface,
        borderRadius: BorderRadius.circular(SeniorDimensions.cardRadius),
        border: Border.all(color: SeniorColors.border, width: 2),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.04),
            blurRadius: 10,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      padding: const EdgeInsets.all(SeniorSpacing.cardPadding),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Header Seksi: Ikon Buku + Judul
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Expanded(
                child: Row(
                  children: [
                    const Icon(
                      Icons.event_note_rounded,
                      color: SeniorColors.primaryGreen,
                      size: 26,
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        switch (lang) {
                          AppLanguage.id => 'Catatan Sawah',
                          AppLanguage.jv => 'Cathetan Sawah',
                          AppLanguage.en => 'Farm Records',
                        },
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: SeniorTypography.title.copyWith(fontSize: 22),
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 8),
              if (activities.isNotEmpty)
                TextButton(
                  onPressed: onViewAll,
                  style: TextButton.styleFrom(
                    foregroundColor: SeniorColors.primaryGreen,
                    padding: const EdgeInsets.symmetric(
                      horizontal: 8,
                      vertical: 4,
                    ),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(
                        switch (lang) {
                          AppLanguage.id => 'Lihat Semua',
                          AppLanguage.jv => 'Tingali Kabeh',
                          AppLanguage.en => 'View All',
                        },
                        style: const TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                      const Icon(Icons.chevron_right_rounded, size: 20),
                    ],
                  ),
                ),
            ],
          ),

          const SizedBox(height: SeniorSpacing.md),

          // Daftar Kegiatan (Maksimal 3)
          if (displayList.isEmpty)
            _buildEmptyActivities(lang)
          else
            ...List.generate(displayList.length, (index) {
              final item = displayList[index];
              return _buildActivityTile(item, index < displayList.length - 1);
            }),

          const SizedBox(height: SeniorSpacing.md),

          // Tombol Besar Tambah Kegiatan (+ Catat Kegiatan Baru) (56px)
          SizedBox(
            height: SeniorDimensions.buttonHeight,
            child: ElevatedButton.icon(
              onPressed: onAddActivity,
              icon: const Icon(Icons.add_circle_outline_rounded, size: 26),
              label: Text(switch (lang) {
                AppLanguage.id => '+ Catat Kegiatan Baru',
                AppLanguage.jv => '+ Cathet Pakaryan Anyar',
                AppLanguage.en => '+ Log New Activity',
              }, style: SeniorTypography.button.copyWith(fontSize: 17)),
              style: ElevatedButton.styleFrom(
                backgroundColor: SeniorColors.primaryGreen,
                foregroundColor: Colors.white,
                elevation: 0,
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

  Widget _buildEmptyActivities(AppLanguage lang) {
    return Container(
      padding: const EdgeInsets.symmetric(vertical: 18, horizontal: 14),
      decoration: BoxDecoration(
        color: SeniorColors.surfaceMuted,
        borderRadius: BorderRadius.circular(SeniorDimensions.buttonRadius),
      ),
      child: Row(
        children: [
          Container(
            width: 44,
            height: 44,
            decoration: const BoxDecoration(
              color: Colors.white,
              shape: BoxShape.circle,
            ),
            child: const Icon(
              Icons.post_add_rounded,
              color: SeniorColors.textSecondary,
              size: 26,
            ),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Text(switch (lang) {
              AppLanguage.id =>
                'Belum ada catatan kegiatan hari ini. Catat pemupukan atau penyemprotan Anda.',
              AppLanguage.jv =>
                'Durung ana cathetan dina iki. Mangga cathet rabuk utawa semprotan.',
              AppLanguage.en =>
                'No activity logged yet today. Tap below to record field work.',
            }, style: SeniorTypography.bodySecondary),
          ),
        ],
      ),
    );
  }

  Widget _buildActivityTile(dynamic item, bool hasDivider) {
    final title =
        (item is Map ? item['title'] ?? item['activity_type'] : null)
            ?.toString() ??
        'Aktivitas Sawah';
    final notes =
        (item is Map ? item['notes'] ?? item['description'] : null)
            ?.toString() ??
        '';
    final dateStr =
        (item is Map ? item['activity_date'] ?? item['created_at'] : null)
            ?.toString() ??
        'Hari ini';

    final iconData = _getActivityIcon(title);

    return Column(
      children: [
        Padding(
          padding: const EdgeInsets.symmetric(vertical: 8),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.center,
            children: [
              Container(
                width: 46,
                height: 46,
                decoration: BoxDecoration(
                  color: iconData.bgColor,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(
                    color: iconData.color.withValues(alpha: 0.3),
                  ),
                ),
                child: Icon(
                  iconData.icon,
                  color: iconData.color,
                  size: SeniorDimensions.iconSmall,
                ),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      title,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: SeniorTypography.subtitle.copyWith(
                        fontSize: 18,
                        fontWeight: FontWeight.w700,
                        color: SeniorColors.textPrimary,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      notes.isNotEmpty ? notes : dateStr,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: SeniorTypography.bodySecondary.copyWith(
                        fontSize: 15,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
        if (hasDivider)
          const Divider(color: SeniorColors.border, height: 12, thickness: 1),
      ],
    );
  }

  _ActivityIconData _getActivityIcon(String title) {
    final lower = title.toLowerCase();
    if (lower.contains('pupuk') || lower.contains('rabuk')) {
      return const _ActivityIconData(
        icon: Icons.grain_rounded,
        color: Color(0xFF15803D),
        bgColor: Color(0xFFDCFCE7),
      );
    }
    if (lower.contains('semprot') ||
        lower.contains('pestisida') ||
        lower.contains('hama')) {
      return const _ActivityIconData(
        icon: Icons.sanitizer_rounded,
        color: SeniorColors.primaryGreen,
        bgColor: SeniorColors.paleGreenBg,
      );
    }
    if (lower.contains('air') ||
        lower.contains('irigasi') ||
        lower.contains('banyu')) {
      return const _ActivityIconData(
        icon: Icons.water_drop_rounded,
        color: SeniorColors.primaryGreen,
        bgColor: SeniorColors.paleGreenBg,
      );
    }
    if (lower.contains('panen')) {
      return const _ActivityIconData(
        icon: Icons.agriculture_rounded,
        color: SeniorColors.primaryGreen,
        bgColor: SeniorColors.paleGreenBg,
      );
    }
    return const _ActivityIconData(
      icon: Icons.check_circle_outline_rounded,
      color: SeniorColors.primaryGreen,
      bgColor: SeniorColors.lightGreenBg,
    );
  }
}

class _ActivityIconData {
  const _ActivityIconData({
    required this.icon,
    required this.color,
    required this.bgColor,
  });

  final IconData icon;
  final Color color;
  final Color bgColor;
}
