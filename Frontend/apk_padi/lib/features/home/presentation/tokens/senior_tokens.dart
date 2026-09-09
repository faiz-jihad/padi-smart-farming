import 'package:flutter/material.dart';

/// P.A.D.I. Senior Design Tokens (Ramah Lansia / 60+ Years Old)
/// Menggunakan palet hijau-putih yang tenang, kontras tinggi,
/// ukuran font minimum 16px, dan target sentuh lega.
abstract final class SeniorColors {
  // Teks keterbacaan tinggi
  static const Color textPrimary = Color(0xFF052E16);
  static const Color textSecondary = Color(0xFF166534);
  static const Color textOnDark = Color(0xFFFFFFFF);
  static const Color textOnDarkMuted = Color(0xFFE8FFF0);

  // Warna utama pertanian
  static const Color primaryGreen = Color(0xFF14532D);
  static const Color accentGreen = Color(0xFF15803D);
  static const Color lightGreenBg = Color(0xFFDCFCE7);
  static const Color paleGreenBg = Color(0xFFF0FDF4);
  static const Color greenBorder = Color(0xFF86EFAC);

  // Permukaan & latar belakang
  static const Color background = Color(0xFFFFFFFF);
  static const Color surface = Color(0xFFFFFFFF);
  static const Color surfaceMuted = Color(0xFFF0FDF4);
  static const Color border = Color(0xFFBBF7D0);
  static const Color borderStrong = Color(0xFF22C55E);

  // Alias status tetap hijau agar halaman konsisten.
  static const Color blueAccent = primaryGreen;
  static const Color blueBg = paleGreenBg;
  static const Color blueBorder = greenBorder;

  static const Color amberAccent = primaryGreen;
  static const Color amberBg = paleGreenBg;
  static const Color amberBorder = greenBorder;

  static const Color tealAccent = accentGreen;
  static const Color tealBg = paleGreenBg;
  static const Color tealBorder = greenBorder;

  static const Color warningText = primaryGreen;
  static const Color warningBg = lightGreenBg;
  static const Color warningBorder = accentGreen;

  static const Color dangerText = primaryGreen;
  static const Color dangerBg = lightGreenBg;
  static const Color dangerBorder = accentGreen;
}

abstract final class SeniorTypography {
  // Tampilan Utama / Angka Besar (28-32px)
  static const TextStyle display = TextStyle(
    color: SeniorColors.textPrimary,
    fontSize: 28,
    fontWeight: FontWeight.w900,
    letterSpacing: 0,
    height: 1.2,
  );

  // Judul Seksi (24px)
  static const TextStyle title = TextStyle(
    color: SeniorColors.textPrimary,
    fontSize: 24,
    fontWeight: FontWeight.w800,
    letterSpacing: 0,
    height: 1.25,
  );

  // Subjudul Kartu / Nama Elemen (18-20px)
  static const TextStyle subtitle = TextStyle(
    color: SeniorColors.textPrimary,
    fontSize: 20,
    fontWeight: FontWeight.w700,
    letterSpacing: 0,
    height: 1.3,
  );

  static const TextStyle subtitleSecondary = TextStyle(
    color: SeniorColors.textSecondary,
    fontSize: 18,
    fontWeight: FontWeight.w600,
    height: 1.35,
  );

  // Teks Isi Utama (Minimal 16px)
  static const TextStyle body = TextStyle(
    color: SeniorColors.textPrimary,
    fontSize: 16,
    fontWeight: FontWeight.w600,
    height: 1.45,
  );

  static const TextStyle bodySecondary = TextStyle(
    color: SeniorColors.textSecondary,
    fontSize: 16,
    fontWeight: FontWeight.w600,
    height: 1.45,
  );

  // Label Tombol (18px, tebal dan tegas)
  static const TextStyle button = TextStyle(
    color: Colors.white,
    fontSize: 18,
    fontWeight: FontWeight.w800,
    letterSpacing: 0,
  );

  // Label Sekunder / Keterangan Tambahan (15-16px, tidak di bawah 14px)
  static const TextStyle caption = TextStyle(
    color: SeniorColors.textSecondary,
    fontSize: 15,
    fontWeight: FontWeight.w600,
    letterSpacing: 0,
  );
}

abstract final class SeniorSpacing {
  static const double xs = 8.0;
  static const double sm = 12.0;
  static const double md = 16.0;
  static const double lg = 20.0;
  static const double xl = 24.0;
  static const double xxl = 32.0;

  static const double screenHorizontal = 18.0;
  static const double cardPadding = 20.0;
  static const double sectionGap = 24.0;
}

abstract final class SeniorDimensions {
  // Target Sentuh Nyaman Lansia (minimum 48dp)
  static const double minTouchTarget = 56.0;
  static const double buttonHeight = 60.0;
  static const double heroButtonHeight = 64.0;

  // Ukuran Ikon
  static const double iconSmall = 28.0;
  static const double iconMedium = 34.0;
  static const double iconLarge = 40.0;
  static const double iconHuge = 48.0;

  // Sudut Kartu & Tombol
  static const double cardRadius = 18.0;
  static const double buttonRadius = 16.0;
  static const double pillRadius = 99.0;
  static const double borderWidth = 1.5;
}
