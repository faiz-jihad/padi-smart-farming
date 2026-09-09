/// Enum semua intent yang dikenali oleh Voice Command P.A.D.I.
enum VoiceIntent {
  /// Buka kamera plant check
  startPlantCheck,

  /// Ambil foto saat kamera terbuka
  takePlantPhoto,

  /// Ambil ulang foto
  retakePhoto,

  /// Kirim gambar ke pipeline AI untuk dianalisis
  analyzePlantImage,

  /// Bacakan hasil diagnosa menggunakan TTS
  readDiagnosis,

  /// Bacakan rekomendasi penanganan (max 3 langkah)
  readRecommendation,

  /// Eskalasi ke PPL (butuh konfirmasi)
  escalateToPpl,

  /// Buka Disease Radar / Early Warning
  checkDiseaseWarning,

  /// Bacakan prioritas kegiatan hari ini
  getDailyPriority,

  /// Informasi cuaca di lahan
  getFarmWeather,

  /// Catat aktivitas (butuh konfirmasi sebelum simpan)
  recordActivity,

  /// Navigasi ke marketplace
  openMarketplace,

  /// Tidak ada intent yang cocok
  unknown,
}

/// Metadata intent yang terdeteksi.
class VoiceResult {
  const VoiceResult({
    required this.intent,
    required this.transcript,
    this.confidence = 1.0,
    this.entityText,
  });

  final VoiceIntent intent;
  final String transcript;

  /// Skor keyakinan 0.0–1.0 dari speech_to_text.
  final double confidence;

  /// Teks entitas yang diekstrak (misal nama aktivitas untuk recordActivity).
  final String? entityText;

  bool get requiresConfirmation => confidence >= 0.55 && confidence < 0.80;
  bool get isHighConfidence => confidence >= 0.80;

  @override
  String toString() =>
      'VoiceResult(intent: $intent, transcript: "$transcript", confidence: $confidence)';
}
