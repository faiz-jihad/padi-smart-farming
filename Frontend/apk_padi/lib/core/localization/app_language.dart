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

  String get navCart => switch (lang) {
        AppLanguage.id => 'Keranjang',
        AppLanguage.jv => 'Kranjang',
        AppLanguage.en => 'Cart',
      };

  String get navOrders => switch (lang) {
        AppLanguage.id => 'Pesanan',
        AppLanguage.jv => 'Pesenan',
        AppLanguage.en => 'Orders',
      };

  String get back => switch (lang) {
        AppLanguage.id => 'Kembali',
        AppLanguage.jv => 'Mbalik',
        AppLanguage.en => 'Back',
      };

  String get roleFarmer => switch (lang) {
        AppLanguage.id => 'Petani',
        AppLanguage.jv => 'Wong Tani',
        AppLanguage.en => 'Farmer',
      };

  String get roleBuyer => switch (lang) {
        AppLanguage.id => 'Pembeli',
        AppLanguage.jv => 'Bakul',
        AppLanguage.en => 'Buyer',
      };

  String get buyerAccountTitle => switch (lang) {
        AppLanguage.id => 'Akun Pembeli / Mitra',
        AppLanguage.jv => 'Akun Bakul / Mitra',
        AppLanguage.en => 'Buyer / Partner Account',
      };

  String get buyerPurchasesTitle => switch (lang) {
        AppLanguage.id => 'Aktivitas Pembelian Panen',
        AppLanguage.jv => 'Aktivitas Tuku Panen',
        AppLanguage.en => 'Harvest Purchase Activity',
      };

  String get buyerOrders => switch (lang) {
        AppLanguage.id => 'Pesanan & Kontrak Saya',
        AppLanguage.jv => 'Pesenan & Kontrak Kula',
        AppLanguage.en => 'My Orders & Contracts',
      };

  String get buyerOrdersSubtitle => switch (lang) {
        AppLanguage.id => 'Pantau kontrak panen aktif dan status pengiriman',
        AppLanguage.jv => 'Pantau kontrak panen aktif lan status kiriman',
        AppLanguage.en => 'Track active crop contracts and shipping status',
      };

  String get buyerCart => switch (lang) {
        AppLanguage.id => 'Keranjang Belanja',
        AppLanguage.jv => 'Kranjang Blanja',
        AppLanguage.en => 'Shopping Cart',
      };

  String get buyerCartSubtitle => switch (lang) {
        AppLanguage.id => 'Lihat daftar komoditas yang siap dicheckout',
        AppLanguage.jv => 'Delok komoditas sing wis siyap dicheckout',
        AppLanguage.en => 'View commodities ready for checkout',
      };

  String get buyerOffers => switch (lang) {
        AppLanguage.id => 'Penawaran Harga Saya',
        AppLanguage.jv => 'Tawaran Rega Kula',
        AppLanguage.en => 'My Price Offers',
      };

  String get buyerOffersSubtitle => switch (lang) {
        AppLanguage.id => 'Status penawaran lelang hasil panen',
        AppLanguage.jv => 'Status tawaran lelang asil panen',
        AppLanguage.en => 'Status of auction crop bids',
      };

  String get statusActive => switch (lang) {
        AppLanguage.id => 'Aktif',
        AppLanguage.jv => 'Aktif',
        AppLanguage.en => 'Active',
      };

  String get themeSystemToast => switch (lang) {
        AppLanguage.id => 'Tema otomatis mengikuti pengaturan sistem HP',
        AppLanguage.jv => 'Tema otomatis manut setelan sistem HP',
        AppLanguage.en => 'Theme automatically follows system settings',
      };

  String get emailCopiedToast => switch (lang) {
        AppLanguage.id => 'Alamat email tersalin ke clipboard',
        AppLanguage.jv => 'Alamat email wis kasalin',
        AppLanguage.en => 'Email address copied to clipboard',
      };

  String get negoOffers => switch (lang) {
        AppLanguage.id => 'Nego Penawaran',
        AppLanguage.jv => 'Nego Tawaran',
        AppLanguage.en => 'Negotiate Offers',
      };

  String get manageCounterOffers => switch (lang) {
        AppLanguage.id => 'Kelola tawar balik',
        AppLanguage.jv => 'Atur tawar balik',
        AppLanguage.en => 'Manage counter offers',
      };

  String get salesReport => switch (lang) {
        AppLanguage.id => 'Laporan Penjualan',
        AppLanguage.jv => 'Laporan Penjualan',
        AppLanguage.en => 'Sales Report',
      };

  String get verifiedRevenue => switch (lang) {
        AppLanguage.id => 'Omzet & faktur sah',
        AppLanguage.jv => 'Omzet & faktur sah',
        AppLanguage.en => 'Revenue & verified invoices',
      };

  String get searchPaddyPlaceholder => switch (lang) {
        AppLanguage.id => 'Cari gabah panen, beras, varietas...',
        AppLanguage.jv => 'Golek gabah, beras, jinis pari...',
        AppLanguage.en => 'Search harvested paddy, rice, varieties...',
      };

  String get flashSaleTitle => switch (lang) {
        AppLanguage.id => 'Panen Kilat Hari Ini',
        AppLanguage.jv => 'Panen Kilat Dina Iki',
        AppLanguage.en => "Today's Flash Harvest",
      };

  String get endsIn => switch (lang) {
        AppLanguage.id => 'Berakhir dalam',
        AppLanguage.jv => 'Pungkasan sajrone',
        AppLanguage.en => 'Ends in',
      };

  String get tabRecommended => switch (lang) {
        AppLanguage.id => 'Rekomendasi',
        AppLanguage.jv => 'Rekomendasi',
        AppLanguage.en => 'Recommended',
      };

  String get tabBestSelling => switch (lang) {
        AppLanguage.id => 'Paling Laris',
        AppLanguage.jv => 'Paling Payu',
        AppLanguage.en => 'Best Selling',
      };

  String get tabNearYou => switch (lang) {
        AppLanguage.id => 'Dekat Anda',
        AppLanguage.jv => 'Cedhak Panjenengan',
        AppLanguage.en => 'Near You',
      };

  String get tabWholesale => switch (lang) {
        AppLanguage.id => 'Partai Besar',
        AppLanguage.jv => 'Partai Gedhe',
        AppLanguage.en => 'Wholesale',
      };

  String get officialPartner => switch (lang) {
        AppLanguage.id => 'MITRA RESMI',
        AppLanguage.jv => 'MITRA RESMI',
        AppLanguage.en => 'OFFICIAL PARTNER',
      };

  String get wholesaleBadge => switch (lang) {
        AppLanguage.id => 'PARTAI BESAR',
        AppLanguage.jv => 'PARTAI GEDHE',
        AppLanguage.en => 'WHOLESALE',
      };

  String get weatherNow => switch (lang) {
        AppLanguage.id => 'Sekarang',
        AppLanguage.jv => 'Saiki',
        AppLanguage.en => 'Now',
      };

  String get humidity => switch (lang) {
        AppLanguage.id => 'Kelembapan',
        AppLanguage.jv => 'Kelembapan',
        AppLanguage.en => 'Humidity',
      };

  String get windSpeed => switch (lang) {
        AppLanguage.id => 'Kecepatan Angin',
        AppLanguage.jv => 'Kacepetan Angin',
        AppLanguage.en => 'Wind Speed',
      };

  String get agroEventsTitle => switch (lang) {
        AppLanguage.id => 'Agenda & Acara Tani',
        AppLanguage.jv => 'Agenda & Acara Tani',
        AppLanguage.en => 'Agro Events & Agenda',
      };

  String get radarNearby => switch (lang) {
        AppLanguage.id => 'RADAR SEKITAR',
        AppLanguage.jv => 'RADAR SEKITAR',
        AppLanguage.en => 'NEARBY RADAR',
      };

  String get harvestMarketTitle => switch (lang) {
        AppLanguage.id => 'PASAR GABAH & BERAS',
        AppLanguage.jv => 'PASAR GABAH & BERAS',
        AppLanguage.en => 'PADDY & RICE MARKET',
      };

  String get harvestSeasonArrived => switch (lang) {
        AppLanguage.id => 'Musim Panen Tiba?',
        AppLanguage.jv => 'Mangsa Panen Rawuh?',
        AppLanguage.en => 'Harvest Season Arrived?',
      };

  String get sellHarvestDirectly => switch (lang) {
        AppLanguage.id => 'Jual gabah langsung ke pembeli terverifikasi tanpa tengkulak.',
        AppLanguage.jv => 'Adol gabah langsung marang bakul resmi tanpa tengkulak.',
        AppLanguage.en => 'Sell grain directly to verified buyers without middlemen.',
      };

  String get marketPaddyNow => switch (lang) {
        AppLanguage.id => 'Pasarkan Hasil Panen',
        AppLanguage.jv => 'Pasarake Asil Panen',
        AppLanguage.en => 'List Harvest Now',
      };

  String get cropJourneyTitle => switch (lang) {
        AppLanguage.id => 'Perjalanan Musim Tanam',
        AppLanguage.jv => 'Lampahan Mangsa Tanam',
        AppLanguage.en => 'Crop Season Journey',
      };

  String get noActiveSeasonDesc => switch (lang) {
        AppLanguage.id => 'Belum ada musim aktif pada lahan ini. Mulai musim tanam agar progres dihitung otomatis.',
        AppLanguage.jv => 'Durung ana mangsa aktif ing sawah iki. Mulai mangsa tanam supaya lampahan diitung otomatis.',
        AppLanguage.en => 'No active season for this farm. Start a season so progress is calculated automatically.',
      };

  // --- Farm / Lahan ---
  String get farmPlotsGis => switch (lang) {
        AppLanguage.id => 'Kelola petak sawah & GIS',
        AppLanguage.jv => 'Atur petak sawah & GIS',
        AppLanguage.en => 'Manage farm plots & GIS',
      };

  String get searchFarmsHint => switch (lang) {
        AppLanguage.id => 'Cari nama sawah atau desa...',
        AppLanguage.jv => 'Golek jeneng sawah utawa desa...',
        AppLanguage.en => 'Search farm name or village...',
      };

  String get noMatchingFarms => switch (lang) {
        AppLanguage.id => 'Tidak ada lahan yang cocok',
        AppLanguage.jv => 'Ora ana sawah sing cocog',
        AppLanguage.en => 'No matching farms found',
      };

  String get checkSearchKeyword => switch (lang) {
        AppLanguage.id => 'Coba periksa kata kunci pencarian Anda.',
        AppLanguage.jv => 'Coba delok maneh tembung panggolekan sampeyan.',
        AppLanguage.en => 'Please check your search keywords.',
      };

  String get noFarmsYet => switch (lang) {
        AppLanguage.id => 'Belum Ada Lahan Terdaftar',
        AppLanguage.jv => 'Durung Ana Sawah Kadhaptar',
        AppLanguage.en => 'No Farms Registered Yet',
      };

  String get noFarmsDesc => switch (lang) {
        AppLanguage.id => 'Daftarkan petak sawah Anda untuk mendapatkan rekomendasi kalender tanam, takaran pupuk, dan pemantauan satelit.',
        AppLanguage.jv => 'Daftarake sawah sampeyan kanggo nampa saran tanggalan tandur, takeran pupuk, lan pantauan satelit.',
        AppLanguage.en => 'Register your farm plot for planting calendar advice, fertilizer dosage, and satellite monitoring.',
      };

  String get addFirstFarm => switch (lang) {
        AppLanguage.id => 'Tambah Lahan Pertama',
        AppLanguage.jv => 'Tambah Sawah Sepisanan',
        AppLanguage.en => 'Add First Farm',
      };

  // --- Plant Check / Deteksi ---
  String get aiCameraPreparing => switch (lang) {
        AppLanguage.id => 'Menyiapkan kamera AI...',
        AppLanguage.jv => 'Nyiapake kamera AI...',
        AppLanguage.en => 'Preparing AI camera...',
      };

  String get positionLeafInFrame => switch (lang) {
        AppLanguage.id => 'Posisikan bercak daun di dalam kotak hijau',
        AppLanguage.jv => 'Pasno bercak godhong ing njero kothak ijo',
        AppLanguage.en => 'Position leaf symptoms inside the green frame',
      };

  String get photoGuideTitle => switch (lang) {
        AppLanguage.id => 'Panduan Foto Daun Padi',
        AppLanguage.jv => 'Pandhuan Foto Godhong Pari',
        AppLanguage.en => 'Paddy Leaf Photo Guide',
      };

  String get photoGuideGotIt => switch (lang) {
        AppLanguage.id => 'Mengerti',
        AppLanguage.jv => 'Ngerti',
        AppLanguage.en => 'Got It',
      };

  String get galleryLabel => switch (lang) {
        AppLanguage.id => 'Galeri',
        AppLanguage.jv => 'Galeri',
        AppLanguage.en => 'Gallery',
      };

  String get cameraNotReady => switch (lang) {
        AppLanguage.id => 'Kamera belum siap.',
        AppLanguage.jv => 'Kamera durung siyap.',
        AppLanguage.en => 'Camera not ready.',
      };

  String get registerFarmFirst => switch (lang) {
        AppLanguage.id => 'Daftarkan Lahan',
        AppLanguage.jv => 'Daftarake Sawah',
        AppLanguage.en => 'Register Farm',
      };

  // --- Marketplace ---
  String get searchMarketplaceHint => switch (lang) {
        AppLanguage.id => 'Cari gabah panen, beras pandan wangi, benih...',
        AppLanguage.jv => 'Golek gabah panen, beras pandan wangi, winih...',
        AppLanguage.en => 'Search harvested grain, rice, seeds...',
      };

  String get startSelling => switch (lang) {
        AppLanguage.id => 'Mulai Jual Panen',
        AppLanguage.jv => 'Mulai Adol Panen',
        AppLanguage.en => 'Start Selling Harvest',
      };

  String get categoryAll => switch (lang) {
        AppLanguage.id => 'Semua',
        AppLanguage.jv => 'Kabeh',
        AppLanguage.en => 'All',
      };

  String get categoryGkp => switch (lang) {
        AppLanguage.id => 'GKP Panen',
        AppLanguage.jv => 'GKP Panen',
        AppLanguage.en => 'Harvest Grain (GKP)',
      };

  String get categoryGkg => switch (lang) {
        AppLanguage.id => 'GKG Giling',
        AppLanguage.jv => 'GKG Giling',
        AppLanguage.en => 'Milled Grain (GKG)',
      };

  String get categoryRice => switch (lang) {
        AppLanguage.id => 'Beras Premium',
        AppLanguage.jv => 'Beras Apik',
        AppLanguage.en => 'Premium Rice',
      };

  String get categorySeed => switch (lang) {
        AppLanguage.id => 'Benih Bersertifikat',
        AppLanguage.jv => 'Winih Resmi',
        AppLanguage.en => 'Certified Seeds',
      };

  String get sortRelevance => switch (lang) {
        AppLanguage.id => 'Terkait',
        AppLanguage.jv => 'Cocog',
        AppLanguage.en => 'Relevant',
      };

  String get sortNewest => switch (lang) {
        AppLanguage.id => 'Terbaru',
        AppLanguage.jv => 'Paling Anyar',
        AppLanguage.en => 'Newest',
      };

  String get sortHighestStock => switch (lang) {
        AppLanguage.id => 'Stok Terbanyak',
        AppLanguage.jv => 'Stok Paling Akeh',
        AppLanguage.en => 'Highest Stock',
      };

  String get sortPrice => switch (lang) {
        AppLanguage.id => 'Harga',
        AppLanguage.jv => 'Rega',
        AppLanguage.en => 'Price',
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
