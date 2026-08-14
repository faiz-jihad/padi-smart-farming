import 'package:flutter/material.dart';

const padiGreen = Color(0xFF37A447);
const padiLeaf = Color(0xFF76C043);
const padiInk = Color(0xFF18251E);
const padiMuted = Color(0xFF6D776F);
const padiField = Color(0xFFF2F6F0);
const padiCream = Color(0xFFFFF8E8);

ThemeData buildPadiTheme() {
  final scheme = ColorScheme.fromSeed(
    seedColor: padiGreen,
    brightness: Brightness.light,
  );

  return ThemeData(
    useMaterial3: true,
    colorScheme: scheme.copyWith(primary: padiGreen, secondary: padiLeaf),
    scaffoldBackgroundColor: Colors.white,
    fontFamily: 'Roboto',
    inputDecorationTheme: InputDecorationTheme(
      filled: true,
      fillColor: padiField,
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(18),
        borderSide: BorderSide.none,
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(18),
        borderSide: BorderSide(color: Colors.black.withValues(alpha: 0.05)),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(18),
        borderSide: const BorderSide(color: padiGreen, width: 1.4),
      ),
      errorBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(18),
        borderSide: const BorderSide(color: Color(0xFFC2410C)),
      ),
      contentPadding: const EdgeInsets.symmetric(horizontal: 18, vertical: 16),
    ),
    filledButtonTheme: FilledButtonThemeData(
      style: FilledButton.styleFrom(
        minimumSize: const Size.fromHeight(52),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
        textStyle: const TextStyle(fontWeight: FontWeight.w700),
      ),
    ),
    textButtonTheme: TextButtonThemeData(
      style: TextButton.styleFrom(foregroundColor: padiGreen),
    ),
  );
}
