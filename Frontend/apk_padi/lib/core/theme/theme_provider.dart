import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

enum AppThemeMode {
  system(labelId: 'Ikuti Sistem', labelJv: 'Melu Sistem HP', labelEn: 'System Default'),
  light(labelId: 'Mode Terang', labelJv: 'Mode Padhang', labelEn: 'Light Mode'),
  dark(labelId: 'Mode Gelap', labelJv: 'Mode Peteng', labelEn: 'Dark Mode');

  final String labelId;
  final String labelJv;
  final String labelEn;

  const AppThemeMode({
    required this.labelId,
    required this.labelJv,
    required this.labelEn,
  });

  ThemeMode get toFlutterThemeMode {
    switch (this) {
      case AppThemeMode.system:
        return ThemeMode.system;
      case AppThemeMode.light:
        return ThemeMode.light;
      case AppThemeMode.dark:
        return ThemeMode.dark;
    }
  }

  static AppThemeMode fromString(String? val) {
    switch (val) {
      case 'light':
        return AppThemeMode.light;
      case 'dark':
        return AppThemeMode.dark;
      default:
        return AppThemeMode.system;
    }
  }
}

class ThemeModeNotifier extends Notifier<AppThemeMode> {
  static const _storageKey = 'padi_user_theme_mode';
  static const _storage = FlutterSecureStorage();

  @override
  AppThemeMode build() {
    _loadSavedTheme();
    return AppThemeMode.system;
  }

  Future<void> _loadSavedTheme() async {
    try {
      final val = await _storage.read(key: _storageKey);
      if (val != null) {
        state = AppThemeMode.fromString(val);
      }
    } catch (_) {}
  }

  Future<void> setTheme(AppThemeMode mode) async {
    state = mode;
    try {
      await _storage.write(key: _storageKey, value: mode.name);
    } catch (_) {}
  }
}

final themeModeProvider = NotifierProvider<ThemeModeNotifier, AppThemeMode>(() {
  return ThemeModeNotifier();
});
