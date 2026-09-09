import 'voice_intent.dart';

/// Rule-based intent classifier untuk Voice Command P.A.D.I.
///
/// Tidak menggunakan LLM — deterministik, tidak butuh internet,
/// cocok untuk MVP hackathon (spec section 60).
///
/// Cara kerja:
/// 1. Normalisasi teks (lowercase, trim, hapus karakter aneh)
/// 2. Cek setiap intent berdasarkan keyword list
/// 3. Return intent pertama yang cocok, atau [VoiceIntent.unknown]
class IntentResolver {
  const IntentResolver._();
  static const IntentResolver instance = IntentResolver._();

  // ─── Keyword Maps ────────────────────────────────────────────

  static const _startPlantCheck = [
    // Indonesia
    'periksa tanaman', 'periksa daun', 'cek tanaman', 'cek daun',
    'cek penyakit', 'foto daun', 'foto tanaman', 'mau cek',
    'saya mau cek', 'scan daun', 'scan tanaman', 'lihat daun',
    'tanaman saya sakit', 'daun saya sakit', 'daun bercak',
    'tolong cek', 'tolong periksa', 'cek padi', 'periksa padi',
    // Bahasa Jawa dasar
    'priksa godhong', 'priksa tanduran', 'cek godhong',
    'godhong lara', 'padi lara',
  ];

  static const _takePlantPhoto = [
    'ambil foto', 'foto sekarang', 'jepret', 'ambil gambar',
    'take photo', 'capture', 'potret',
    // Jawa
    'jupuk foto', 'foto saiki',
  ];

  static const _retakePhoto = [
    'foto ulang', 'ulangi foto', 'ambil ulang', 'foto lagi',
    'ulangi', 'ganti foto',
    // Jawa
    'foto maneh', 'jupuk maneh',
  ];

  static const _analyzePlantImage = [
    'periksa foto ini', 'analisis foto', 'analisis', 'cek penyakitnya',
    'analisa foto', 'pakai foto ini', 'gunakan foto',
    'lanjutkan', 'proses', 'kirim foto',
  ];

  static const _readDiagnosis = [
    'bacakan hasilnya', 'tanaman saya sakit apa', 'jelaskan hasilnya',
    'apa hasilnya', 'hasil apa', 'sakit apa', 'penyakit apa',
    'bacakan hasil', 'baca hasil',
    // Jawa
    'wacakno hasil', 'lara apa',
  ];

  static const _readRecommendation = [
    'apa yang harus saya lakukan', 'bagaimana cara mengatasinya',
    'bacakan sarannya', 'cara mengatasi', 'obatnya apa',
    'harus diapakan', 'saran apa', 'langkah apa', 'cara sembuhkan',
    'apa obatnya', 'solusinya', 'penanganan',
    // Jawa
    'kepiye carane', 'obate apa',
  ];

  static const _escalateToPpl = [
    'tanyakan ke penyuluh', 'hubungi ppl', 'saya mau tanya penyuluh',
    'konsultasi ppl', 'tanya ppl', 'lapor ppl',
    'kontak penyuluh', 'penyuluh', 'orang pertanian',
    // Jawa
    'takon penyuluh', 'hubungi ppl',
  ];

  static const _checkDiseaseWarning = [
    'ada penyakit di sekitar', 'cek penyakit sekitar', 'ada peringatan',
    'bagaimana kondisi sekitar sawah', 'radar penyakit', 'early warning',
    'kondisi sekitar', 'peringatan penyakit', 'penyakit tetangga',
    'wabah sekitar',
    // Jawa
    'ana penyakit sakjane', 'ana peringatan',
  ];

  static const _getDailyPriority = [
    'bagaimana kondisi sawah hari ini', 'apa yang harus saya lakukan hari ini',
    'prioritas hari ini', 'kegiatan hari ini', 'hari ini apa',
    'agenda hari ini', 'jadwal hari ini', 'kondisi sawah',
    // Jawa
    'kondisi sawah saiki', 'prioritas dina iki',
  ];

  static const _getFarmWeather = [
    'apakah hari ini hujan', 'cuaca sawah', 'cuaca hari ini',
    'kapan waktu yang bagus untuk menyemprot', 'prakiraan cuaca',
    'mau hujan', 'akan hujan', 'cuaca bagaimana',
    'bagus menyemprot kapan', 'info cuaca',
    // Jawa
    'cuaca saiki', 'udan ora',
  ];

  static const _recordActivity = [
    'catat hari ini saya memupuk', 'catat penyemprotan', 'catat pemupukan',
    'saya baru menyiram', 'saya baru memupuk', 'saya baru menyemprot',
    'catat kegiatan', 'tambah aktivitas', 'input aktivitas',
    'saya sudah', 'tadi saya',
    // Jawa
    'catat aku mupuk', 'catat nyemprot',
  ];

  static const _openMarketplace = [
    'buka pasar', 'saya mau jual', 'lihat harga gabah', 'harga gabah',
    'jual hasil panen', 'marketplace', 'pasar', 'jual panen',
    'harga padi', 'cek harga',
    // Jawa
    'buka pasar', 'dodolan panen',
  ];

  // ─── Public API ───────────────────────────────────────────────

  /// Resolve teks menjadi [VoiceResult].
  ///
  /// [transcript] — teks dari STT.
  /// [confidence] — skor kepercayaan dari STT (0.0–1.0).
  VoiceResult resolve(String transcript, {double confidence = 1.0}) {
    final normalized = _normalize(transcript);

    final intent = _matchIntent(normalized);

    // Ekstrak entitas untuk recordActivity
    String? entity;
    if (intent == VoiceIntent.recordActivity) {
      entity = _extractActivityEntity(normalized);
    }

    return VoiceResult(
      intent: intent,
      transcript: transcript,
      confidence: confidence,
      entityText: entity,
    );
  }

  // ─── Private Helpers ──────────────────────────────────────────

  String _normalize(String text) {
    return text
        .toLowerCase()
        .trim()
        .replaceAll(RegExp(r'[^\w\s]'), '') // hapus tanda baca
        .replaceAll(RegExp(r'\s+'), ' ');   // kolaps whitespace
  }

  VoiceIntent _matchIntent(String normalized) {
    // Konfirmasi cepat 1 kata
    if (normalized == 'ya' ||
        normalized == 'iya' ||
        normalized == 'benar' ||
        normalized == 'oke' ||
        normalized == 'lanjut') {
      return VoiceIntent.analyzePlantImage;
    }
    if (normalized == 'foto' || normalized == 'jepret') {
      return VoiceIntent.takePlantPhoto;
    }

    // Cek tiap category dengan urutan prioritas (spesifik & frasa panjang dulu)
    if (_containsAny(normalized, _retakePhoto)) return VoiceIntent.retakePhoto;
    if (_containsAny(normalized, _takePlantPhoto)) return VoiceIntent.takePlantPhoto;
    if (_containsAny(normalized, _checkDiseaseWarning)) return VoiceIntent.checkDiseaseWarning;
    if (_containsAny(normalized, _getDailyPriority)) return VoiceIntent.getDailyPriority;
    if (_containsAny(normalized, _readDiagnosis)) return VoiceIntent.readDiagnosis;
    if (_containsAny(normalized, _readRecommendation)) return VoiceIntent.readRecommendation;
    if (_containsAny(normalized, _escalateToPpl)) return VoiceIntent.escalateToPpl;
    if (_containsAny(normalized, _analyzePlantImage)) return VoiceIntent.analyzePlantImage;
    if (_containsAny(normalized, _startPlantCheck)) return VoiceIntent.startPlantCheck;
    if (_containsAny(normalized, _getFarmWeather)) return VoiceIntent.getFarmWeather;
    if (_containsAny(normalized, _recordActivity)) return VoiceIntent.recordActivity;
    if (_containsAny(normalized, _openMarketplace)) return VoiceIntent.openMarketplace;

    return VoiceIntent.unknown;
  }

  bool _containsKeyword(String text, String keyword) {
    if (text == keyword) return true;
    final pattern = RegExp('(^|\\s)${RegExp.escape(keyword)}(\\s|\$)');
    return pattern.hasMatch(text);
  }

  bool _containsAny(String normalized, List<String> keywords) {
    for (final kw in keywords) {
      if (_containsKeyword(normalized, kw)) return true;
    }
    return false;
  }

  /// Ekstrak tipe aktivitas dari ucapan untuk recordActivity.
  String? _extractActivityEntity(String normalized) {
    if (normalized.contains('memupuk') || normalized.contains('pupuk') || normalized.contains('mupuk')) {
      return 'Pemupukan';
    }
    if (normalized.contains('menyemprot') || normalized.contains('semprot') || normalized.contains('nyemprot')) {
      return 'Penyemprotan';
    }
    if (normalized.contains('menyiram') || normalized.contains('siram') || normalized.contains('ngairi')) {
      return 'Pengairan';
    }
    if (normalized.contains('menanam') || normalized.contains('tanam')) {
      return 'Penanaman';
    }
    if (normalized.contains('panen')) {
      return 'Panen';
    }
    return null;
  }
}
