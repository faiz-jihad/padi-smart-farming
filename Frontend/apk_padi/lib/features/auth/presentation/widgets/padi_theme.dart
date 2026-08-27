import 'package:flutter/material.dart';

const padiGreen = Color(0xFF075C3D);
const padiLeaf = Color(0xFF2F8C62);
const padiInk = Color(0xFF1A2F25);
const padiMuted = Color(0xFF66756D);
const padiField = Color(0xFFF5F7F2);
const padiSurface = Color(0xFFFFFFFF);
const padiBorder = Color(0xFFE1E8DF);
const padiSoftGreen = Color(0xFFEAF2EC);
const padiCream = Color(0xFFFFFAEA);
const padiWarning = Color(0xFF946E00);
const padiControlRadius = 18.0;
const padiControlHeight = 56.0;

ThemeData buildPadiTheme() {
  final scheme = ColorScheme.fromSeed(
    seedColor: padiGreen,
    brightness: Brightness.light,
  );

  return ThemeData(
    useMaterial3: true,
    colorScheme: scheme.copyWith(primary: padiGreen, secondary: padiLeaf),
    scaffoldBackgroundColor: padiField,
    appBarTheme: const AppBarTheme(
      backgroundColor: padiField,
      foregroundColor: padiInk,
      elevation: 0,
      scrolledUnderElevation: 0,
      centerTitle: false,
      titleTextStyle: TextStyle(
        color: padiInk,
        fontSize: 18,
        fontWeight: FontWeight.w800,
      ),
    ),
    cardTheme: CardThemeData(
      color: padiSurface,
      elevation: 0,
      margin: EdgeInsets.zero,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(8),
        side: const BorderSide(color: padiBorder),
      ),
    ),
    inputDecorationTheme: InputDecorationTheme(
      filled: true,
      fillColor: padiSurface,
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(padiControlRadius),
        borderSide: const BorderSide(color: padiBorder),
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(padiControlRadius),
        borderSide: const BorderSide(color: padiBorder),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(padiControlRadius),
        borderSide: const BorderSide(color: padiGreen, width: 1.4),
      ),
      errorBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(padiControlRadius),
        borderSide: const BorderSide(color: Color(0xFFC2410C)),
      ),
      focusedErrorBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(padiControlRadius),
        borderSide: const BorderSide(color: Color(0xFFC2410C), width: 1.4),
      ),
      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
    ),
    filledButtonTheme: FilledButtonThemeData(
      style: FilledButton.styleFrom(
        minimumSize: const Size(0, padiControlHeight),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(padiControlRadius),
        ),
        textStyle: const TextStyle(fontWeight: FontWeight.w700),
      ),
    ),
    outlinedButtonTheme: OutlinedButtonThemeData(
      style: OutlinedButton.styleFrom(
        foregroundColor: padiGreen,
        minimumSize: const Size(0, padiControlHeight),
        side: const BorderSide(color: padiBorder),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(padiControlRadius),
        ),
        textStyle: const TextStyle(fontWeight: FontWeight.w700),
      ),
    ),
    textButtonTheme: TextButtonThemeData(
      style: TextButton.styleFrom(foregroundColor: padiGreen),
    ),
    snackBarTheme: SnackBarThemeData(
      behavior: SnackBarBehavior.floating,
      backgroundColor: const Color(0xFF0F5132),
      contentTextStyle: const TextStyle(
        color: Colors.white,
        fontWeight: FontWeight.w700,
        fontSize: 13,
      ),
      actionTextColor: const Color(0xFF6EE7B7),
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(14),
        side: BorderSide(color: const Color(0xFF34D399).withOpacity(0.35)),
      ),
      elevation: 6,
      insetPadding: const EdgeInsets.fromLTRB(16, 0, 16, 100),
    ),
  );
}
