import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:padi/core/localization/app_language.dart';
import 'package:padi/features/home/presentation/tokens/senior_tokens.dart';
import 'package:padi/features/home/presentation/widgets/daily_priority_section.dart'
    show DailyPriorityItem;

/// Prioritas Harian Petani Ramah Lansia (Senior Daily Priority Card)
/// Menampilkan maksimal 2 tugas paling penting hari ini dalam ukuran teks besar,
/// nomor urut yang jelas, dan tombol aksi berukuran minimal 52-56px.
class SeniorDailyPriorityCard extends ConsumerWidget {
  const SeniorDailyPriorityCard({
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
  Widget build(BuildContext context, WidgetRef ref) {
    final lang = ref.watch(languageProvider);

    if (isLoading) {
      return Container(
        height: 120,
        decoration: BoxDecoration(
          color: SeniorColors.surface,
          borderRadius: BorderRadius.circular(SeniorDimensions.cardRadius),
          border: Border.all(color: SeniorColors.border, width: 1.5),
        ),
        child: const Center(
          child: SizedBox(
            width: 32,
            height: 32,
            child: CircularProgressIndicator(
              strokeWidth: 3,
              color: SeniorColors.primaryGreen,
            ),
          ),
        ),
      );
    }

    if (priorities.isEmpty) {
      return const SizedBox.shrink();
    }

    // Batasi maksimal 2 prioritas agar tidak membebani lansia
    final displayList = priorities.take(2).toList();

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        // Judul Seksi
        Row(
          children: [
            Container(
              padding: const EdgeInsets.all(6),
              decoration: const BoxDecoration(
                color: SeniorColors.lightGreenBg,
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.star_rounded,
                color: SeniorColors.primaryGreen,
                size: 24,
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: Text(switch (lang) {
                AppLanguage.id => 'Tugas Utama Hari Ini',
                AppLanguage.jv => 'Pakaryan Wigati Dina Iki',
                AppLanguage.en => 'Top Priorities Today',
              }, style: SeniorTypography.title.copyWith(fontSize: 22)),
            ),
          ],
        ),

        const SizedBox(height: SeniorSpacing.md),

        // Daftar 1-2 Prioritas
        ...List.generate(displayList.length, (index) {
          final item = displayList[index];
          final isUrgent = item.urgency == 'high' || item.urgency == 'urgent';

          return Container(
            margin: const EdgeInsets.only(bottom: 14),
            padding: const EdgeInsets.all(SeniorSpacing.cardPadding),
            decoration: BoxDecoration(
              color: SeniorColors.surface,
              borderRadius: BorderRadius.circular(SeniorDimensions.cardRadius),
              border: Border.all(
                color: isUrgent
                    ? SeniorColors.borderStrong
                    : SeniorColors.border,
                width: isUrgent ? 2 : 1.5,
              ),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.04),
                  blurRadius: 8,
                  offset: const Offset(0, 3),
                ),
              ],
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                // Nomor Urut + Judul
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Container(
                      width: 36,
                      height: 36,
                      decoration: BoxDecoration(
                        color: isUrgent
                            ? SeniorColors.warningBg
                            : SeniorColors.lightGreenBg,
                        shape: BoxShape.circle,
                        border: Border.all(
                          color: isUrgent
                              ? SeniorColors.warningBorder
                              : SeniorColors.greenBorder,
                          width: 1.5,
                        ),
                      ),
                      child: Center(
                        child: Text(
                          '${index + 1}',
                          style: TextStyle(
                            color: isUrgent
                                ? SeniorColors.warningText
                                : SeniorColors.primaryGreen,
                            fontSize: 18,
                            fontWeight: FontWeight.w900,
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            item.title,
                            style: SeniorTypography.subtitle.copyWith(
                              fontSize: 19,
                              fontWeight: FontWeight.w800,
                              color: SeniorColors.textPrimary,
                            ),
                          ),
                          if (item.subtitle.isNotEmpty) ...[
                            const SizedBox(height: 4),
                            Text(
                              item.subtitle,
                              style: SeniorTypography.bodySecondary.copyWith(
                                fontSize: 16,
                              ),
                            ),
                          ],
                        ],
                      ),
                    ),
                  ],
                ),

                const SizedBox(height: SeniorSpacing.md),

                // Tombol Aksi (Tinggi 52px)
                SizedBox(
                  height: 52,
                  child: ElevatedButton(
                    onPressed: () {
                      if (item.route.isNotEmpty && item.route != '/home') {
                        context.push(item.route);
                      }
                    },
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
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Text(
                          item.actionLabel.isNotEmpty
                              ? item.actionLabel
                              : switch (lang) {
                                  AppLanguage.id => 'Lakukan Sekarang',
                                  AppLanguage.jv => 'Tindakake Saiki',
                                  AppLanguage.en => 'Take Action',
                                },
                          style: SeniorTypography.button.copyWith(fontSize: 17),
                        ),
                        const SizedBox(width: 8),
                        const Icon(Icons.arrow_forward_rounded, size: 22),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          );
        }),
      ],
    );
  }
}
