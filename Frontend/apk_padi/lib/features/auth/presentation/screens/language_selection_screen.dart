import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:padi/core/localization/app_language.dart';
import 'package:padi/features/home/presentation/tokens/home_tokens.dart';

class LanguageSelectionScreen extends ConsumerWidget {
  const LanguageSelectionScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final currentLang = ref.watch(languageProvider);
    final s = AppStrings(currentLang);

    final options = [
      (
        lang: AppLanguage.id,
        title: 'Bahasa Indonesia',
        subtitle: 'Mudah dipahami untuk penggunaan sehari-hari',
        flag: '🇮🇩',
      ),
      (
        lang: AppLanguage.jv,
        title: 'Basa Jawa',
        subtitle: 'Basa Jawa sing gampang dingerteni',
        flag: '🌾',
      ),
      (
        lang: AppLanguage.en,
        title: 'English',
        subtitle: 'For international users',
        flag: '🇬🇧',
      ),
    ];

    return Scaffold(
      backgroundColor: HomeColors.background,
      appBar: AppBar(
        backgroundColor: HomeColors.background,
        elevation: 0,
        scrolledUnderElevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_rounded, color: HomeColors.textPrimary),
          onPressed: () {
            if (context.canPop()) {
              context.pop();
            } else {
              context.go('/profile');
            }
          },
        ),
        title: Text(
          s.selectLanguageHeader,
          style: const TextStyle(
            color: HomeColors.textPrimary,
            fontSize: 18,
            fontWeight: FontWeight.w900,
            letterSpacing: -0.3,
          ),
        ),
      ),
      body: SafeArea(
        child: Center(
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 580),
            child: ListView(
              padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 12),
              children: [
                // Info Card
                Container(
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: HomeColors.lightGreen,
                    borderRadius: BorderRadius.circular(HomeRadius.lg),
                    border: Border.all(color: const Color(0xFFBBF7D0)),
                  ),
                  child: Row(
                    children: [
                      const Icon(
                        Icons.translate_rounded,
                        color: HomeColors.primaryGreen,
                        size: 22,
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Text(
                          s.languageSubtitle,
                          style: const TextStyle(
                            color: HomeColors.deepGreen,
                            fontSize: 12.5,
                            fontWeight: FontWeight.w700,
                            height: 1.35,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),

                const SizedBox(height: 20),

                // Language List Options
                ...options.map((item) {
                  final isSelected = item.lang == currentLang;

                  return Padding(
                    padding: const EdgeInsets.only(bottom: 12),
                    child: Material(
                      color: Colors.transparent,
                      child: InkWell(
                        onTap: () {
                          ref.read(languageProvider.notifier).setLanguage(item.lang);

                          final feedback = switch (item.lang) {
                            AppLanguage.id => 'Bahasa berhasil diubah',
                            AppLanguage.jv => 'Basane wis diganti',
                            AppLanguage.en => 'Language changed',
                          };

                          ScaffoldMessenger.of(context).showSnackBar(
                            SnackBar(
                              content: Row(
                                children: [
                                  Text(item.flag, style: const TextStyle(fontSize: 18)),
                                  const SizedBox(width: 10),
                                  Expanded(
                                    child: Text(
                                      feedback,
                                      style: const TextStyle(fontWeight: FontWeight.w800),
                                    ),
                                  ),
                                ],
                              ),
                              backgroundColor: HomeColors.deepGreen,
                              duration: const Duration(seconds: 2),
                              behavior: SnackBarBehavior.floating,
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(HomeRadius.md),
                              ),
                            ),
                          );
                        },
                        borderRadius: BorderRadius.circular(HomeRadius.xl),
                        child: Container(
                          constraints: const BoxConstraints(minHeight: 64),
                          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                          decoration: BoxDecoration(
                            color: isSelected ? HomeColors.surface : HomeColors.surface,
                            borderRadius: BorderRadius.circular(HomeRadius.xl),
                            border: Border.all(
                              color: isSelected ? HomeColors.primaryGreen : HomeColors.border,
                              width: isSelected ? 2.0 : 1.0,
                            ),
                            boxShadow: isSelected
                                ? [
                                    BoxShadow(
                                      color: HomeColors.primaryGreen.withOpacity(0.12),
                                      blurRadius: 10,
                                      offset: const Offset(0, 4),
                                    ),
                                  ]
                                : HomeShadows.subtle,
                          ),
                          child: Row(
                            children: [
                              // Flag / Icon Badge
                              Container(
                                width: 44,
                                height: 44,
                                decoration: BoxDecoration(
                                  color: isSelected
                                      ? HomeColors.lightGreen
                                      : HomeColors.surfaceMuted,
                                  borderRadius: BorderRadius.circular(HomeRadius.md),
                                ),
                                child: Center(
                                  child: Text(
                                    item.flag,
                                    style: const TextStyle(fontSize: 22),
                                  ),
                                ),
                              ),
                              const SizedBox(width: 14),

                              // Text content
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      item.title,
                                      style: TextStyle(
                                        color: isSelected
                                            ? HomeColors.deepGreen
                                            : HomeColors.textPrimary,
                                        fontSize: 15,
                                        fontWeight:
                                            isSelected ? FontWeight.w900 : FontWeight.w700,
                                      ),
                                    ),
                                    const SizedBox(height: 2),
                                    Text(
                                      item.subtitle,
                                      style: TextStyle(
                                        color: isSelected
                                            ? HomeColors.primaryGreen
                                            : HomeColors.textSecondary,
                                        fontSize: 12,
                                        fontWeight:
                                            isSelected ? FontWeight.w600 : FontWeight.normal,
                                      ),
                                    ),
                                  ],
                                ),
                              ),

                              // Active Check Indicator
                              if (isSelected)
                                Container(
                                  padding: const EdgeInsets.all(4),
                                  decoration: const BoxDecoration(
                                    color: HomeColors.primaryGreen,
                                    shape: BoxShape.circle,
                                  ),
                                  child: const Icon(
                                    Icons.check_rounded,
                                    color: Colors.white,
                                    size: 16,
                                  ),
                                )
                              else
                                Container(
                                  width: 22,
                                  height: 22,
                                  decoration: BoxDecoration(
                                    shape: BoxShape.circle,
                                    border: Border.all(color: HomeColors.border, width: 1.5),
                                  ),
                                ),
                            ],
                          ),
                        ),
                      ),
                    ),
                  );
                }),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
