import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:padi/core/localization/app_language.dart';
import 'package:padi/features/home/presentation/tokens/home_tokens.dart';
import 'package:padi/features/notifications/presentation/providers/notifications_provider.dart';

class HomeHeader extends ConsumerWidget {
  const HomeHeader({
    super.key,
    required this.name,
    required this.onNotificationTap,
    this.hasUnreadNotifications = true,
  });

  final String name;
  final VoidCallback onNotificationTap;
  final bool hasUnreadNotifications;

  String _getTimeGreeting(AppLanguage lang) {
    final hour = DateTime.now().hour;
    if (hour < 11) {
      return switch (lang) {
        AppLanguage.id => 'Selamat pagi',
        AppLanguage.jv => 'Sugeng enjang',
        AppLanguage.en => 'Good morning',
      };
    }
    if (hour < 15) {
      return switch (lang) {
        AppLanguage.id => 'Selamat siang',
        AppLanguage.jv => 'Sugeng siang',
        AppLanguage.en => 'Good afternoon',
      };
    }
    if (hour < 18) {
      return switch (lang) {
        AppLanguage.id => 'Selamat sore',
        AppLanguage.jv => 'Sugeng sonten',
        AppLanguage.en => 'Good evening',
      };
    }
    return switch (lang) {
      AppLanguage.id => 'Selamat malam',
      AppLanguage.jv => 'Sugeng dalu',
      AppLanguage.en => 'Good night',
    };
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final lang = ref.watch(languageProvider);
    final s = AppStrings(lang);
    final displayName = name.trim().isNotEmpty ? name.trim() : 'Petani';

    return Padding(
      padding: const EdgeInsets.symmetric(vertical: HomeSpacing.xs),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          // P.A.D.I. Smart Avatar Emblem
          Container(
            width: 48,
            height: 48,
            padding: const EdgeInsets.all(3),
            decoration: BoxDecoration(
              color: HomeColors.surface,
              borderRadius: BorderRadius.circular(HomeRadius.md),
              border: Border.all(color: HomeColors.border, width: 1.2),
              boxShadow: HomeShadows.subtle,
            ),
            child: ClipRRect(
              borderRadius: BorderRadius.circular(HomeRadius.sm),
              child: Image.asset(
                'assets/images/padi-logo.png',
                fit: BoxFit.contain,
                errorBuilder: (context, error, stackTrace) => Container(
                  color: HomeColors.lightGreen,
                  child: const Icon(
                    Icons.eco_rounded,
                    color: HomeColors.primaryGreen,
                    size: 24,
                  ),
                ),
              ),
            ),
          ),
          const SizedBox(width: HomeSpacing.sm),

          // Dynamic Greeting & Farmer Name
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  _getTimeGreeting(lang),
                  style: HomeTypography.caption.copyWith(
                    color: HomeColors.textSecondary,
                    fontWeight: FontWeight.w600,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  s.helloUser(displayName),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: HomeTypography.greeting.copyWith(
                    fontSize: 20,
                    color: HomeColors.textPrimary,
                  ),
                ),
              ],
            ),
          ),

          const SizedBox(width: HomeSpacing.xs),

          // Notification Button with Accessible Touch Target (44x44px minimum)
          Material(
            color: Colors.transparent,
            child: InkWell(
              onTap: onNotificationTap,
              borderRadius: BorderRadius.circular(HomeRadius.pill),
              child: Ink(
                width: 44,
                height: 44,
                decoration: BoxDecoration(
                  color: HomeColors.surface,
                  borderRadius: BorderRadius.circular(HomeRadius.pill),
                  border: Border.all(color: HomeColors.border),
                  boxShadow: HomeShadows.subtle,
                ),
                child: Stack(
                  alignment: Alignment.center,
                  children: [
                    Icon(
                      hasUnreadNotifications
                          ? Icons.notifications_rounded
                          : Icons.notifications_outlined,
                      color: HomeColors.textPrimary,
                      size: 22,
                    ),
                    Consumer(
                      builder: (context, ref, _) {
                        final count = ref.watch(unreadNotificationCountProvider);
                        if (count == 0) return const SizedBox.shrink();
                        return Positioned(
                          top: 6,
                          right: 7,
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 3, vertical: 1),
                            constraints: const BoxConstraints(minWidth: 16, minHeight: 16),
                            decoration: const BoxDecoration(
                              color: HomeColors.danger,
                              shape: BoxShape.circle,
                            ),
                            child: Text(
                              count > 9 ? '9+' : '$count',
                              style: const TextStyle(
                                color: Colors.white,
                                fontSize: 8.5,
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