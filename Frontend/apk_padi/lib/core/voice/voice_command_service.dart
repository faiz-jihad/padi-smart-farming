import 'dart:async';
import 'package:flutter/foundation.dart';
import 'package:flutter_tts/flutter_tts.dart';
import 'package:speech_to_text/speech_to_text.dart';

import 'intent_resolver.dart';
import 'voice_intent.dart';

/// Singleton wrapper untuk Speech-to-Text dan Text-to-Speech.
///
/// Dipisah dari UI agar mudah ditest dan di-mock.
/// Mic hanya aktif saat [startListening] dipanggil (push-to-talk).
class VoiceCommandService {
  VoiceCommandService._();
  static final VoiceCommandService instance = VoiceCommandService._();

  final SpeechToText _stt = SpeechToText();
  final FlutterTts _tts = FlutterTts();
  final IntentResolver _resolver = IntentResolver.instance;

  bool _sttInitialized = false;
  bool _sttAvailable = false;
  VoidCallback? _onDone;
  void Function(String transcript, double confidence)? _onResult;
  String? _lastTranscript;
  double _lastConfidence = 0.0;
  bool _hasDeliveredResult = false;
  Timer? _listenWatchdog;

  // ─── Initialization ───────────────────────────────────────────

  /// Inisialisasi STT. Dipanggil sekali saat app start.
  Future<bool> initialize() async {
    try {
      _sttAvailable = await _stt.initialize(
        onError: (error) => debugPrint('[VoiceCmd] STT error: $error'),
        onStatus: (status) {
          debugPrint('[VoiceCmd] STT status: $status');
          if (status == 'done' || status == 'notListening') {
            _finishListening();
          }
        },
      );
      _sttInitialized = true;

      // Konfigurasi TTS untuk Bahasa Indonesia
      await _tts.setLanguage('id-ID');
      await _tts.setSpeechRate(0.48); // Sedikit lebih lambat untuk petani
      await _tts.setVolume(1.0);
      await _tts.setPitch(1.0);

      debugPrint('[VoiceCmd] Initialized. STT available: $_sttAvailable');
      return _sttAvailable;
    } catch (e) {
      debugPrint('[VoiceCmd] Init failed: $e');
      return false;
    }
  }

  bool get isAvailable => _sttInitialized && _sttAvailable;
  bool get isListening => _stt.isListening;

  // ─── Speech-to-Text ───────────────────────────────────────────

  /// Mulai mendengarkan. [onResult] dipanggil saat ada hasil.
  /// [onDone] dipanggil saat sesi selesai.
  Future<void> startListening({
    required void Function(String transcript, double confidence) onResult,
    VoidCallback? onDone,
  }) async {
    if (!isAvailable) {
      debugPrint('[VoiceCmd] STT tidak tersedia.');
      onDone?.call();
      return;
    }

    if (_stt.isListening) {
      await stopListening();
    }

    // Hentikan TTS jika sedang berjalan
    await _tts.stop();
    _onDone = onDone;
    _onResult = onResult;
    _lastTranscript = null;
    _lastConfidence = 0.0;
    _hasDeliveredResult = false;
    _listenWatchdog?.cancel();
    _listenWatchdog = Timer(const Duration(seconds: 14), _finishListening);

    try {
      await _stt.listen(
        onResult: (result) {
          final transcript = result.recognizedWords.trim();
          if (transcript.isEmpty) {
            return;
          }

          _lastTranscript = transcript;
          _lastConfidence = result.confidence > 0 ? result.confidence : 0.72;

          if (result.finalResult) {
            _deliverResult();
          }
        },
        onSoundLevelChange: null,
        listenOptions: SpeechListenOptions(
          listenFor: const Duration(seconds: 12),
          pauseFor: const Duration(seconds: 4),
          localeId: 'id_ID',
          listenMode: ListenMode.confirmation,
          partialResults: true,
        ),
      );
    } catch (e) {
      debugPrint('[VoiceCmd] Listen failed: $e');
      final callback = _onDone;
      _clearCurrentSession();
      callback?.call();
    }
  }

  /// Hentikan STT.
  Future<void> stopListening() async {
    _clearCurrentSession();
    if (_stt.isListening) {
      await _stt.stop();
    }
  }

  // ─── Text-to-Speech ───────────────────────────────────────────

  /// Bacakan teks menggunakan TTS.
  Future<void> speak(String text) async {
    await _tts.stop();
    await _tts.speak(text);
  }

  /// Hentikan TTS.
  Future<void> stopSpeaking() async {
    await _tts.stop();
  }

  // ─── Intent Resolution ────────────────────────────────────────

  /// Resolve transcript menjadi VoiceResult.
  VoiceResult resolve(String transcript, {double confidence = 1.0}) {
    return _resolver.resolve(transcript, confidence: confidence);
  }

  // ─── TTS Templates ───────────────────────────────────────────

  /// Feedback setelah intent dikenali.
  Future<void> speakAcknowledgement(VoiceIntent intent) async {
    final text = switch (intent) {
      VoiceIntent.startPlantCheck =>
        'Baik, saya buka kamera untuk memeriksa daun padi.',
      VoiceIntent.takePlantPhoto => 'Mengambil foto sekarang.',
      VoiceIntent.retakePhoto => 'Baik, silakan ambil foto ulang.',
      VoiceIntent.analyzePlantImage => 'Foto sedang diperiksa oleh AI.',
      VoiceIntent.readDiagnosis => 'Baik, saya bacakan hasil pemeriksaan.',
      VoiceIntent.readRecommendation =>
        'Saya akan bacakan tiga langkah penanganan yang disarankan.',
      VoiceIntent.escalateToPpl =>
        'Apakah Anda ingin mengirim hasil ini ke penyuluh pertanian?',
      VoiceIntent.checkDiseaseWarning =>
        'Mengecek kondisi penyakit di sekitar lahan Anda.',
      VoiceIntent.getDailyPriority =>
        'Ini prioritas kegiatan sawah Anda hari ini.',
      VoiceIntent.getFarmWeather =>
        'Mengambil informasi cuaca untuk lahan Anda.',
      VoiceIntent.recordActivity =>
        'Baik, saya catat aktivitas tersebut. Mohon konfirmasi sebelum disimpan.',
      VoiceIntent.openMarketplace => 'Membuka halaman pasar gabah.',
      VoiceIntent.unknown =>
        'Saya belum memahami perintah itu. Coba katakan: Periksa tanaman.',
    };
    await speak(text);
  }

  /// Bacakan ringkasan diagnosa (maksimal kalimat pendek).
  Future<void> speakDiagnosisSummary({
    required String diseaseName,
    required String confidenceLabel,
  }) async {
    final text =
        'Hasil pemeriksaan menunjukkan kemungkinan $diseaseName '
        'dengan tingkat keyakinan $confidenceLabel. '
        'Katakan: Apa yang harus saya lakukan, untuk mendengar tindakan yang disarankan.';
    await speak(text);
  }

  /// Bacakan rekomendasi (maksimal 3 langkah).
  Future<void> speakRecommendation(List<String> steps) async {
    if (steps.isEmpty) {
      await speak('Tidak ada rekomendasi khusus saat ini.');
      return;
    }
    final limited = steps.take(3).toList();
    final buffer = StringBuffer(
      'Hari ini ada ${limited.length} hal yang disarankan. ',
    );
    for (int i = 0; i < limited.length; i++) {
      buffer.write('Langkah ${i + 1}: ${limited[i]}. ');
    }
    await speak(buffer.toString());
  }

  /// Dispose — panggil saat app ditutup.
  Future<void> dispose() async {
    await _stt.stop();
    await _tts.stop();
    _clearCurrentSession();
  }

  void _finishListening() {
    if (_stt.isListening) {
      unawaited(_stt.stop());
    }

    if (_hasDeliveredResult) {
      _clearCurrentSession();
      return;
    }

    final transcript = _lastTranscript;
    if (transcript != null && transcript.isNotEmpty) {
      _deliverResult();
      return;
    }

    final callback = _onDone;
    _clearCurrentSession();
    callback?.call();
  }

  void _deliverResult() {
    if (_hasDeliveredResult) {
      return;
    }

    final transcript = _lastTranscript;
    final callback = _onResult;
    if (transcript == null || transcript.isEmpty || callback == null) {
      return;
    }

    _hasDeliveredResult = true;
    if (_stt.isListening) {
      unawaited(_stt.stop());
    }
    final confidence = _lastConfidence > 0 ? _lastConfidence : 0.72;
    _clearCurrentSession(keepDeliveredFlag: true);
    callback(transcript, confidence);
  }

  void _clearCurrentSession({bool keepDeliveredFlag = false}) {
    _listenWatchdog?.cancel();
    _listenWatchdog = null;
    _onDone = null;
    _onResult = null;
    _lastTranscript = null;
    _lastConfidence = 0.0;
    if (!keepDeliveredFlag) {
      _hasDeliveredResult = false;
    }
  }
}
