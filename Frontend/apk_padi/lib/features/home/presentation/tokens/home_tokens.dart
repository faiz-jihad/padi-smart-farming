import 'package:flutter/material.dart';

/// P.A.D.I. Home Design Tokens & Theming System
/// Built following 8pt Spacing, Material 3, Apple HIG, and WCAG Accessibility Guidelines.
abstract final class HomeColors {
  // Brand Colors
  static const Color primaryGreen = Color(0xFF146B45);
  static const Color deepGreen = Color(0xFF075E3B);
  static const Color emerald = Color(0xFF0E7C53);
  static const Color lightGreen = Color(0xFFEAF5EF);
  static const Color mintAccent = Color(0xFF34D399);

  // Background & Surfaces
  static const Color background = Color(0xFFF7F9F6);
  static const Color surface = Color(0xFFFFFFFF);
  static const Color surfaceMuted = Color(0xFFF1F5F0);
  static const Color border = Color(0xFFE5ECE3);
  static const Color borderSubtle = Color(0xFFEEF3EC);

  // Typography
  static const Color textPrimary = Color(0xFF17251E);
  static const Color textSecondary = Color(0xFF68766E);
  static const Color textTertiary = Color(0xFF94A39B);
  static const Color textOnDark = Color(0xFFFFFFFF);
  static const Color textOnDarkMuted = Color(0xFFD1E7DD);

  // State & Accent Colors
  static const Color warning = Color(0xFFF4A825);
  static const Color warningBg = Color(0xFFFEF7E6);
  static const Color danger = Color(0xFFD94B45);
  static const Color dangerBg = Color(0xFFFDEEEC);
  static const Color skyBlue = Color(0xFF0284C7);
  static const Color skyBlueBg = Color(0xFFE0F2FE);
  static const Color purple = Color(0xFF7C3AED);
  static const Color purpleBg = Color(0xFFF3E8FF);
  static const Color harvestGold = Color(0xFFD97706);
  static const Color harvestGoldBg = Color(0xFFFEF3C7);
}

abstract final class HomeSpacing {
  static const double xxs = 4.0;
  static const double xs = 8.0;
  static const double sm = 12.0;
  static const double md = 16.0;
  static const double lg = 20.0;
  static const double xl = 24.0;
  static const double xxl = 32.0;
  static const double xxxl = 40.0;

  static const double screenHorizontal = 18.0;
  static const double cardPadding = 18.0;
  static const double sectionSpacing = 24.0;
}

abstract final class HomeRadius {
  static const double sm = 12.0;
  static const double md = 16.0;
  static const double lg = 22.0;
  static const double xl = 26.0;
  static const double xxl = 32.0;
  static const double pill = 99.0;
}

abstract final class HomeShadows {
  static List<BoxShadow> get subtle => [
        BoxShadow(
          color: Colors.black.withOpacity(0.03),
          blurRadius: 10,
          offset: const Offset(0, 3),
        ),
      ];

  static List<BoxShadow> get hero => [
        BoxShadow(
          color: HomeColors.deepGreen.withOpacity(0.22),
          blurRadius: 24,
          offset: const Offset(0, 10),
        ),
      ];

  static List<BoxShadow> get nav => [
        BoxShadow(
          color: Colors.black.withOpacity(0.06),
          blurRadius: 16,
          offset: const Offset(0, -4),
        ),
      ];
}

abstract final class HomeTypography {
  static const TextStyle greeting = TextStyle(
    color: HomeColors.textPrimary,
    fontSize: 22,
    fontWeight: FontWeight.w800,
    letterSpacing: -0.4,
    height: 1.2,
  );

  static const TextStyle sectionTitle = TextStyle(
    color: HomeColors.textPrimary,
    fontSize: 18,
    fontWeight: FontWeight.w800,
    letterSpacing: -0.3,
  );

  static const TextStyle cardTitle = TextStyle(
    color: HomeColors.textPrimary,
    fontSize: 16,
    fontWeight: FontWeight.w700,
    letterSpacing: -0.2,
  );

  static const TextStyle body = TextStyle(
    color: HomeColors.textPrimary,
    fontSize: 14,
    fontWeight: FontWeight.w500,
    height: 1.4,
  );

  static const TextStyle supporting = TextStyle(
    color: HomeColors.textSecondary,
    fontSize: 12.5,
    fontWeight: FontWeight.w500,
    height: 1.35,
  );

  static const TextStyle caption = TextStyle(
    color: HomeColors.textSecondary,
    fontSize: 11,
    fontWeight: FontWeight.w600,
    letterSpacing: 0.2,
  );
}
