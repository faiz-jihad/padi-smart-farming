import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:padi/core/localization/app_language.dart';
import 'package:padi/core/widgets/voice_mic_button.dart';
import 'package:padi/features/home/presentation/tokens/senior_tokens.dart';
import 'package:padi/features/home/presentation/widgets/senior/senior_help_dialog.dart';
import 'package:padi/features/notifications/presentation/providers/notifications_provider.dart';

/// Header Beranda Ramah Lansia (Senior-Friendly Header)
/// Memiliki ukuran teks besar, sapaan jelas, tombol bantuan "?",
/// tombol mikrofon suara, dan tombol notifikasi dengan target sentuh minimal 48dp.
class SeniorHomeHeader extends ConsumerWidget {
  const SeniorHomeHeader({
    super.key,
    required this.name,
    required this.onNotificationTap,
  });

  final String name;
  final VoidCallback onNotificationTap;

  String _getTimeGreeting(AppLanguage lang) {
    final hour = DateTime.now().hour;
    if (hour < 11) {
      return switch (lang) {
        AppLanguage.id => 'Selamat Pagi',
        AppLanguage.jv => 'Sugeng Enjang',
        AppLanguage.en => 'Good Morning',
      };
    }
    if (hour < 15) {
      return switch (lang) {
        AppLanguage.id => 'Selamat Siang',
        AppLanguage.jv => 'Sugeng Siang',
        AppLanguage.en => 'Good Afternoon',
      };
    }
    if (hour < 18) {
      return switch (lang) {
        AppLanguage.id => 'Selamat Sore',
        AppLanguage.jv => 'Sugeng Sonten',
        AppLanguage.en => 'Good Evening',
      };
    }
    return switch (lang) {
      AppLanguage.id => 'Selamat Malam',
      AppLanguage.jv => 'Sugeng Dalu',
      AppLanguage.en => 'Good Night',
    };
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final lang = ref.watch(languageProvider);
    final s = AppStrings(lang);
    final trimmedName = name.trim();
    final displayName = trimmedName.isNotEmpty ? trimmedName : s.roleFarmer;

    return Padding(
      padding: const EdgeInsets.symmetric(vertical: SeniorSpacing.xs),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          // Logo P.A.D.I.
          Container(
            width: 52,
            height: 52,
            padding: const EdgeInsets.all(4),
            decoration: BoxDecoration(
              color: SeniorColors.surface,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: SeniorColors.border, width: 1.5),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.05),
                  blurRadius: 8,
                  offset: const Offset(0, 2),
                ),
              ],
            ),
            child: ClipRRect(
              borderRadius: BorderRadius.circular(12),
              child: Image.asset(
                'assets/images/padi-logo.png',
                fit: BoxFit.contain,
                errorBuilder: (context, error, stackTrace) => Container(
                  color: SeniorColors.lightGreenBg,
                  child: const Icon(
                    Icons.eco_rounded,
                    color: SeniorColors.primaryGreen,
                    size: 28,
                  ),
                ),
              ),
            ),
          ),

          const SizedBox(width: SeniorSpacing.md),

          // Sapaan Waktu & Nama Petani
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  _getTimeGreeting(lang),
                  style: SeniorTypography.caption.copyWith(
                    fontSize: 16,
                    color: SeniorColors.textSecondary,
                    fontWeight: FontWeight.w700,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  displayName,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: SeniorTypography.title.copyWith(
                    fontSize: 22,
                    color: SeniorColors.textPrimary,
                  ),
                ),
              ],
            ),
          ),

          const SizedBox(width: SeniorSpacing.xs),

          // Tombol Bantuan "?" (Aksesibilitas Lansia)
          Tooltip(
            message: switch (lang) {
              AppLanguage.id => 'Bantuan Beranda',
              AppLanguage.jv => 'Pituduh Aplikasi',
              AppLanguage.en => 'Home Help',
            },
            child: Material(
              color: Colors.transparent,
              child: InkWell(
                onTap: () => SeniorHelpDialog.show(context, lang),
                borderRadius: BorderRadius.circular(
                  SeniorDimensions.pillRadius,
                ),
                child: Ink(
                  width: SeniorDimensions.minTouchTarget,
                  height: SeniorDimensions.minTouchTarget,
                  decoration: BoxDecoration(
                    color: SeniorColors.surface,
                    borderRadius: BorderRadius.circular(
                      SeniorDimensions.pillRadius,
                    ),
                    border: Border.all(color: SeniorColors.border, width: 1.5),
                  ),
                  child: const Icon(
                    Icons.help_outline_rounded,
                    color: SeniorColors.primaryGreen,
                    size: 28,
                  ),
                ),
              ),
            ),
          ),

          const SizedBox(width: SeniorSpacing.xs),

          // Tombol Mikrofon Perintah Suara
          const VoiceMicButton(mini: true),

          const SizedBox(width: SeniorSpacing.xs),

          // Tombol Notifikasi (Target sentuh min 48x48dp)
          Material(
            color: Colors.transparent,
            child: InkWell(
              onTap: onNotificationTap,
              borderRadius: BorderRadius.circular(SeniorDimensions.pillRadius),
              child: Ink(
                width: SeniorDimensions.minTouchTarget,
                height: SeniorDimensions.minTouchTarget,
                decoration: BoxDecoration(
                  color: SeniorColors.surface,
                  borderRadius: BorderRadius.circular(
                    SeniorDimensions.pillRadius,
                  ),
                  border: Border.all(color: SeniorColors.border, width: 1.5),
                ),
                child: Stack(
                  alignment: Alignment.center,
                  children: [
                    const Icon(
                      Icons.notifications_outlined,
                      color: SeniorColors.textPrimary,
                      size: 28,
                    ),
                    Consumer(
                      builder: (context, ref, _) {
                        final count = ref.watch(
                          unreadNotificationCountProvider,
                        );
                        if (count == 0) return const SizedBox.shrink();
                        return Positioned(
                          top: 4,
                          right: 4,
                          child: Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 5,
                              vertical: 2,
                            ),
                            constraints: const BoxConstraints(
                              minWidth: 20,
                              minHeight: 20,
                            ),
                            decoration: const BoxDecoration(
                              color: SeniorColors.primaryGreen,
                              shape: BoxShape.circle,
                            ),
                            child: Text(
                              count > 9 ? '9+' : '$count',
                              style: const TextStyle(
                                color: Colors.white,
                                fontSize: 11,
                                fontWeight: FontWeight.w900,
                              ),
                              textAlign: TextAlign.center,
                            ),
                          ),
                        );
                      },
                    ),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
