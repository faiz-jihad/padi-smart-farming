import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:intl/intl.dart';

enum AppLanguage {
  id(
    code: 'id',
    name: 'Bahasa Indonesia',
    nativeName: 'Bahasa Indonesia',
    subtitle: 'Mudah dipahami untuk penggunaan sehari-hari',
    flagEmoji: '🇮🇩',
  ),
  jv(
    code: 'jv',
    name: 'Basa Jawa',
    nativeName: 'Basa Jawa',
    subtitle: 'Basa Jawa sing gampang dingerteni',
    flagEmoji: '🌾',
  ),
  en(
    code: 'en',
    name: 'English',
    nativeName: 'English',
    subtitle: 'For international users',
    flagEmoji: '🇬🇧',
  );

  final String code;
  final String name;
  final String nativeName;
  final String subtitle;
  final String flagEmoji;

  const AppLanguage({
    required this.code,
    required this.name,
    required this.nativeName,
    required this.subtitle,
    required this.flagEmoji,
  });

  Locale get locale => Locale(code);

  static AppLanguage fromCode(String? code) {
    return AppLanguage.values.firstWhere(
      (l) => l.code == code,
      orElse: () => AppLanguage.id,
    );
  }
}

class LanguageNotifier extends Notifier<AppLanguage> {
  static const _storageKey = 'padi_user_language';
  static const _storage = FlutterSecureStorage();

  @override
  AppLanguage build() {
    _loadSavedLanguage();
    return AppLanguage.id;
  }

  Future<void> _loadSavedLanguage() async {
    try {
      final code = await _storage.read(key: _storageKey);
      if (code != null) {
        state = AppLanguage.fromCode(code);
      }
    } catch (_) {}
  }

  Future<void> setLanguage(AppLanguage language) async {
    state = language;
    try {
      await _storage.write(key: _storageKey, value: language.code);
    } catch (_) {}
  }
}

final languageProvider = NotifierProvider<LanguageNotifier, AppLanguage>(() {
  return LanguageNotifier();
});

/// Kamus Terjemahan Lengkap (Bahasa Indonesia, Basa Jawa, English)
class AppStrings {
  final AppLanguage lang;
  const AppStrings(this.lang);

  // --- Bottom Navigation ---
  String get navHome => switch (lang) {
        AppLanguage.id => 'Beranda',
        AppLanguage.jv => 'Beranda',
        AppLanguage.en => 'Home',
      };

  String get navFarms => switch (lang) {
        AppLanguage.id => 'Lahan',
        AppLanguage.jv => 'Sawah',
        AppLanguage.en => 'Farm',
      };

  String get navScan => switch (lang) {
        AppLanguage.id => 'Periksa',
        AppLanguage.jv => 'Priksa',
        AppLanguage.en => 'Check',
      };

  String get navMarket => switch (lang) {
        AppLanguage.id => 'Toko',
        AppLanguage.jv => 'Toko',
        AppLanguage.en => 'Store',
      };

  String get navProfile => switch (lang) {
        AppLanguage.id => 'Profil',
        AppLanguage.jv => 'Profil',
        AppLanguage.en => 'Profile',
      };

  // --- Home Screen Headers & Greet ---
  String helloUser(String name) => switch (lang) {
        AppLanguage.id => 'Halo, $name 👋',
        AppLanguage.jv => 'Halo, $name 👋',
        AppLanguage.en => 'Hello, $name 👋',
      };

  String get defaultUserName => switch (lang) {
        AppLanguage.id => 'Faiz',
        AppLanguage.jv => 'Faiz',
        AppLanguage.en => 'Faiz',
      };

  String get farmConditionQuestion => switch (lang) {
        AppLanguage.id => 'Bagaimana kondisi sawah hari ini?',
        AppLanguage.jv => 'Kepiye kondisi sawahe dina iki?',
        AppLanguage.en => 'How is your farm today?',
      };

  String get needAttentionToday => switch (lang) {
        AppLanguage.id => 'Perlu perhatian hari ini',
        AppLanguage.jv => 'Sing perlu digatekake dina iki',
        AppLanguage.en => 'Needs your attention',
      };

  String get farmWeather => switch (lang) {
        AppLanguage.id => 'Cuaca lahan',
        AppLanguage.jv => 'Cuaca ing sawah',
        AppLanguage.en => 'Farm Weather',
      };

  String get quickActions => switch (lang) {
        AppLanguage.id => 'Aksi cepat',
        AppLanguage.jv => 'Aksi cepet',
        AppLanguage.en => 'Quick Actions',
      };

  String get checkCrops => switch (lang) {
        AppLanguage.id => 'Periksa Tanaman',
        AppLanguage.jv => 'Priksa Tanduran',
        AppLanguage.en => 'Check Crops',
      };

  String get logActivity => switch (lang) {
        AppLanguage.id => 'Catat Aktivitas',
        AppLanguage.jv => 'Cathet Kegiatan',
        AppLanguage.en => 'Log Activity',
      };

  String get addFarm => switch (lang) {
        AppLanguage.id => 'Tambah Lahan',
        AppLanguage.jv => 'Tambah Sawah',
        AppLanguage.en => 'Add Farm',
      };

  String get sellHarvest => switch (lang) {
        AppLanguage.id => 'Jual Panen',
        AppLanguage.jv => 'Adol Panen',
        AppLanguage.en => 'Sell Harvest',
      };

  String get fertilizerDose => switch (lang) {
        AppLanguage.id => 'Dosis Pupuk',
        AppLanguage.jv => 'Takeran Pupuk',
        AppLanguage.en => 'Fertilizer Dose',
      };

  String get plantingCalendar => switch (lang) {
        AppLanguage.id => 'Kalender Tanam',
        AppLanguage.jv => 'Tanggalan Tanam',
        AppLanguage.en => 'Planting Calendar',
      };

  String get pestRadar => switch (lang) {
        AppLanguage.id => 'Radar Hama',
        AppLanguage.jv => 'Radar Ama',
        AppLanguage.en => 'Pest Alert',
      };

  String get cropTimeline => switch (lang) {
        AppLanguage.id => 'Timeline Tanam',
        AppLanguage.jv => 'Lampahan Tanam',
        AppLanguage.en => 'Crop Timeline',
      };

  String get todayActivities => switch (lang) {
        AppLanguage.id => 'Aktivitas hari ini',
        AppLanguage.jv => 'Kegiatan dina iki',
        AppLanguage.en => "Today's Activities",
      };

  String get nearbyConditions => switch (lang) {
        AppLanguage.id => 'Kondisi sekitar',
        AppLanguage.jv => 'Kondisi sekitar',
        AppLanguage.en => 'Nearby Conditions',
      };

  String get todayPaddyPrices => switch (lang) {
        AppLanguage.id => 'Harga gabah hari ini',
        AppLanguage.jv => 'Rega gabah dina iki',
        AppLanguage.en => "Today's Paddy Prices",
      };

  String get cropGrowthJourney => switch (lang) {
        AppLanguage.id => 'Perjalanan musim tanam',
        AppLanguage.jv => 'Lampahan mangsa tanam',
        AppLanguage.en => 'Crop Growth Journey',
      };

  String get seeFarmCondition => switch (lang) {
        AppLanguage.id => 'Lihat kondisi lahan',
        AppLanguage.jv => 'Delok kondisi sawah',
        AppLanguage.en => 'View farm condition',
      };

  String get noActivitiesToday => switch (lang) {
        AppLanguage.id => 'Belum ada aktivitas hari ini',
        AppLanguage.jv => 'Dina iki durung ana kegiatan',
        AppLanguage.en => 'No activities recorded today',
      };

  String get viewAll => switch (lang) {
        AppLanguage.id => 'Lihat Semua',
        AppLanguage.jv => 'Delok Kabeh',
        AppLanguage.en => 'View All',
      };

  String get tryAgain => switch (lang) {
        AppLanguage.id => 'Coba lagi',
        AppLanguage.jv => 'Coba maneh',
        AppLanguage.en => 'Try again',
      };

  // --- Profile Screen & Grouped Settings ---
  String get profile => switch (lang) {
        AppLanguage.id => 'Profil',
        AppLanguage.jv => 'Profil',
        AppLanguage.en => 'Profile',
      };

  String get profileSubtitle => switch (lang) {
        AppLanguage.id => 'Kelola akun, bahasa, dan pengaturan sawah',
        AppLanguage.jv => 'Setelan akun, basa, lan data sawah',
        AppLanguage.en => 'Manage account, language, and farm settings',
      };

  String get userSectionTitle => switch (lang) {
        AppLanguage.id => 'Akun Petani',
        AppLanguage.jv => 'Akun Petani',
        AppLanguage.en => 'Farmer Account',
      };

  String get personalInfo => switch (lang) {
        AppLanguage.id => 'Data Pribadi',
        AppLanguage.jv => 'Data Dhiri',
        AppLanguage.en => 'Personal Information',
      };

  String get personalInfoSubtitle => switch (lang) {
        AppLanguage.id => 'Nama lengkap dan nomor kontak aktif',
        AppLanguage.jv => 'Asma jangkep lan nomer HP aktif',
        AppLanguage.en => 'Full name and active contact details',
      };

  String get accountSecurity => switch (lang) {
        AppLanguage.id => 'Keamanan Akun',
        AppLanguage.jv => 'Keamanan Akun',
        AppLanguage.en => 'Account Security',
      };

  String get accountSecuritySubtitle => switch (lang) {
        AppLanguage.id => 'Ubah kata sandi dan proteksi akun',
        AppLanguage.jv => 'Gantos tembung sandi lan pangreksa akun',
        AppLanguage.en => 'Change password and account security',
      };

  String get appSettings => switch (lang) {
        AppLanguage.id => 'Pengaturan Aplikasi',
        AppLanguage.jv => 'Setelan Aplikasi',
        AppLanguage.en => 'App Settings',
      };

  String get language => switch (lang) {
        AppLanguage.id => 'Bahasa',
        AppLanguage.jv => 'Basa',
        AppLanguage.en => 'Language',
      };

  String get languageSubtitle => switch (lang) {
        AppLanguage.id => 'Pilih bahasa aplikasi yang paling nyaman untuk Anda',
        AppLanguage.jv => 'Pilih basa aplikasi sing paling kepenak dingerteni',
        AppLanguage.en => 'Select the most comfortable application language for you',
      };

  String get notifications => switch (lang) {
        AppLanguage.id => 'Notifikasi',
        AppLanguage.jv => 'Notifikasi',
        AppLanguage.en => 'Notifications',
      };

  String get notificationsSubtitle => switch (lang) {
        AppLanguage.id => 'Peringatan hama dan jadwal pemupukan',
        AppLanguage.jv => 'Pangeling ama lan jadwal mupuk',
        AppLanguage.en => 'Pest alerts and fertilizer schedules',
      };

  String get theme => switch (lang) {
        AppLanguage.id => 'Tema',
        AppLanguage.jv => 'Tema',
        AppLanguage.en => 'Theme',
      };

  String get themeSystem => switch (lang) {
        AppLanguage.id => 'Ikuti sistem',
        AppLanguage.jv => 'Melu sistem HP',
        AppLanguage.en => 'System default',
      };

  String get helpSection => switch (lang) {
        AppLanguage.id => 'Bantuan & Informasi',
        AppLanguage.jv => 'Pitulungan & Katrangan',
        AppLanguage.en => 'Help & Information',
      };

  String get helpCenter => switch (lang) {
        AppLanguage.id => 'Pusat Bantuan',
        AppLanguage.jv => 'Pusat Pitulungan',
        AppLanguage.en => 'Help Center',
      };

  String get aboutApp => switch (lang) {
        AppLanguage.id => 'Tentang P.A.D.I.',
        AppLanguage.jv => 'Babagan P.A.D.I.',
        AppLanguage.en => 'About P.A.D.I.',
      };

  String get privacyPolicy => switch (lang) {
        AppLanguage.id => 'Kebijakan Privasi',
        AppLanguage.jv => 'Kawicaksanan Privasi',
        AppLanguage.en => 'Privacy Policy',
      };

  String get signOut => switch (lang) {
        AppLanguage.id => 'Keluar',
        AppLanguage.jv => 'Metu',
        AppLanguage.en => 'Sign Out',
      };

  String get signOutConfirm => switch (lang) {
        AppLanguage.id => 'Apakah Anda yakin ingin keluar dari akun ini?',
        AppLanguage.jv => 'Punapa panjenengan estu badhe medal saking akun?',
        AppLanguage.en => 'Are you sure you want to sign out?',
      };

  String get cancel => switch (lang) {
        AppLanguage.id => 'Batal',
        AppLanguage.jv => 'Batal',
        AppLanguage.en => 'Cancel',
      };

  String get yesSignOut => switch (lang) {
        AppLanguage.id => 'Ya, Keluar',
        AppLanguage.jv => 'Nggih, Metu',
        AppLanguage.en => 'Yes, Sign Out',
      };

  String get selectLanguageHeader => switch (lang) {
        AppLanguage.id => 'Pilih Bahasa',
        AppLanguage.jv => 'Pilih Basa',
        AppLanguage.en => 'Select Language',
      };

  String get languageChangedToast => switch (lang) {
        AppLanguage.id => 'Bahasa berhasil diubah',
        AppLanguage.jv => 'Basane wis diganti',
        AppLanguage.en => 'Language changed',
      };

  String get save => switch (lang) {
        AppLanguage.id => 'Simpan',
        AppLanguage.jv => 'Simpen',
        AppLanguage.en => 'Save',
      };

  String get fullName => switch (lang) {
        AppLanguage.id => 'Nama Lengkap',
        AppLanguage.jv => 'Asma Jangkep',
        AppLanguage.en => 'Full Name',
      };

  String get phoneNumber => switch (lang) {
        AppLanguage.id => 'Nomor WhatsApp / HP',
        AppLanguage.jv => 'Nomer WhatsApp / HP',
        AppLanguage.en => 'Phone Number',
      };

  // --- Dynamic Plurals & Counts ---
  String activeFarmsCount(int count) => switch (lang) {
        AppLanguage.id => '$count lahan aktif',
        AppLanguage.jv => '$count sawah aktif',
        AppLanguage.en => count == 1 ? '1 active farm' : '$count active farms',
      };

  String cropAgeDays(int day) => switch (lang) {
        AppLanguage.id => 'Hari ke-$day',
        AppLanguage.jv => 'Dina ke-$day',
        AppLanguage.en => 'Day $day',
      };

  // --- Backend Enums Mapping ---
  String mapCropHealth(String? status) {
    final s = status?.toLowerCase() ?? '';
    if (s.contains('healthy') || s.contains('subur') || s.contains('optimal')) {
      return switch (lang) {
        AppLanguage.id => 'Subur & Sehat',
        AppLanguage.jv => 'Subur & Waras',
        AppLanguage.en => 'Healthy & Fertile',
      };
    } else if (s.contains('warning') || s.contains('waspada')) {
      return switch (lang) {
        AppLanguage.id => 'Perlu Perhatian',
        AppLanguage.jv => 'Kudu Digatekake',
        AppLanguage.en => 'Needs Attention',
      };
    } else if (s.contains('critical') || s.contains('hama') || s.contains('danger')) {
      return switch (lang) {
        AppLanguage.id => 'Terindikasi Hama',
        AppLanguage.jv => 'Kena Ama',
        AppLanguage.en => 'Pest Detected',
      };
    }
    return switch (lang) {
      AppLanguage.id => 'Kondisi Baik',
      AppLanguage.jv => 'Kondisi Sae',
      AppLanguage.en => 'Good Condition',
    };
  }

  String mapGrowthPhase(int dayNumber) {
    if (dayNumber <= 15) {
      return switch (lang) {
        AppLanguage.id => 'Fase Tanam',
        AppLanguage.jv => 'Mangsa Tandur',
        AppLanguage.en => 'Planting Phase',
      };
    } else if (dayNumber <= 55) {
      return switch (lang) {
        AppLanguage.id => 'Fase Vegetatif',
        AppLanguage.jv => 'Mangsa Vegetatif',
        AppLanguage.en => 'Vegetative Phase',
      };
    } else if (dayNumber <= 90) {
      return switch (lang) {
        AppLanguage.id => 'Fase Generatif',
        AppLanguage.jv => 'Mangsa Generatif',
        AppLanguage.en => 'Generative Phase',
      };
    } else {
      return switch (lang) {
        AppLanguage.id => 'Siap Panen',
        AppLanguage.jv => 'Siyap Panen',
        AppLanguage.en => 'Ready for Harvest',
      };
    }
  }

  // --- Date Formatter ---
  String formatDate(DateTime date) {
    final localeName = switch (lang) {
      AppLanguage.id => 'id_ID',
      AppLanguage.jv => 'id_ID',
      AppLanguage.en => 'en_US',
    };
    try {
      return DateFormat('d MMMM yyyy', localeName).format(date);
    } catch (_) {
      return DateFormat('d MMMM yyyy').format(date);
    }
  }

  // --- Friendly API Errors ---
  String get friendlyErrorMessage => switch (lang) {
        AppLanguage.id => 'Data belum bisa dimuat. Coba lagi sebentar.',
        AppLanguage.jv => 'Datane durung isa dimuat. Coba maneh sedhela.',
        AppLanguage.en => 'Unable to load data. Please try again in a moment.',
      };
}
