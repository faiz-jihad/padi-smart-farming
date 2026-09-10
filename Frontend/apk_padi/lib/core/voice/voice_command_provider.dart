import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:permission_handler/permission_handler.dart';

import 'voice_command_service.dart';
import 'voice_intent.dart';
import 'voice_state.dart';

/// Riverpod provider untuk state machine Voice Command.
final voiceCommandProvider =
    NotifierProvider<VoiceCommandNotifier, VoiceCommandState>(
      VoiceCommandNotifier.new,
    );

class VoiceCommandNotifier extends Notifier<VoiceCommandState> {
  final VoiceCommandService _service = VoiceCommandService.instance;
  bool _initialized = false;
  bool _isDisposed = false;
  bool _isStartingListening = false;

  @override
  VoiceCommandState build() {
    _isDisposed = false;
    ref.onDispose(() {
      _isDisposed = true;
    });
    return const VoiceCommandState();
  }

  // ─── Initialization ───────────────────────────────────────────

  Future<bool> ensureInitialized() async {
    if (_initialized && _service.isAvailable) return true;
    _initialized = true;
    return await _service.initialize();
  }

  // ─── Overlay Control ─────────────────────────────────────────

  void showOverlay() {
    state = state.copyWith(
      isOverlayVisible: true,
      uiState: VoiceUiState.idle,
      statusMessage: 'Bicara ke P.A.D.I.',
    );
  }

  void hideOverlay() {
    Future.microtask(() async {
      await _service.stopListening();
      await _service.stopSpeaking();
    });
    state = const VoiceCommandState(isOverlayVisible: false);
  }

  Future<void> resetAudioSession() async {
    _isStartingListening = false;
    _pendingResult = null;
    await _service.stopListening();
    await _service.stopSpeaking();
  }

  // ─── Main Flow ────────────────────────────────────────────────

  /// Push-to-talk: mulai sesi mendengarkan.
  Future<void> startListening() async {
    if (_isStartingListening) return;
    _isStartingListening = true;

    try {
      state = const VoiceCommandState(
        uiState: VoiceUiState.transcribing,
        isOverlayVisible: true,
        statusMessage: 'Menyiapkan mikrofon...',
      );

      // 1. Cek izin microphone. Di web, izin diminta oleh browser saat STT mulai.
      if (!kIsWeb) {
        final micStatus = await Permission.microphone.request();
        if (!micStatus.isGranted) {
          state = state.copyWith(
            uiState: VoiceUiState.error,
            errorMessage:
                'Izin mikrofon ditolak. Aktifkan izin di Pengaturan > P.A.D.I. > Mikrofon.',
          );
          return;
        }
      }

      // 2. Pastikan STT siap
      final ready = await ensureInitialized();
      if (!ready) {
        state = state.copyWith(
          uiState: VoiceUiState.error,
          errorMessage: 'Fitur suara tidak tersedia di perangkat ini.',
        );
        return;
      }

      // 3. Mulai listening
      state = const VoiceCommandState(
        uiState: VoiceUiState.listening,
        isOverlayVisible: true,
        statusMessage: 'Mendengarkan...',
      );

      await _service.startListening(
        onResult: _onSpeechResult,
        onPartialResult: _onSpeechPartialResult,
        onDone: _onSpeechDone,
      );
    } finally {
      _isStartingListening = false;
    }
  }

  void _onSpeechPartialResult(String transcript) {
    if (_isDisposed || transcript.trim().isEmpty) return;

    if (state.uiState == VoiceUiState.listening ||
        state.uiState == VoiceUiState.transcribing) {
      state = state.copyWith(
        uiState: VoiceUiState.listening,
        transcript: transcript.trim(),
        statusMessage: 'Suara terbaca...',
      );
    }
  }

  void _onSpeechResult(String transcript, double confidence) {
    if (_isDisposed) return;

    debugPrint('[VoiceCmd] transcript="$transcript" confidence=$confidence');

    // Transisi ke TRANSCRIBING dulu
    state = state.copyWith(
      uiState: VoiceUiState.transcribing,
      transcript: transcript,
      statusMessage: 'Memahami ucapan...',
    );

    // Resolve intent
    final result = _service.resolve(transcript, confidence: confidence);

    // Confidence sangat rendah → ERROR
    // Unknown intent → ERROR dengan contoh
    if (result.intent == VoiceIntent.unknown) {
      state = state.copyWith(
        uiState: VoiceUiState.error,
        voiceResult: result,
        errorMessage:
            'Saya belum memahami perintah itu.\nCoba katakan: "Periksa tanaman".',
      );
      return;
    }

    // Confidence sedang (0.55–0.79) → CONFIRMATION
    if (confidence < 0.80 || result.requiresConfirmation) {
      final intentLabel = _intentLabel(result.intent);
      state = state.copyWith(
        uiState: VoiceUiState.confirmation,
        voiceResult: result,
        statusMessage:
            'Saya mendengar:\n"$transcript"\n\nLanjut ke:\n$intentLabel?',
      );
      return;
    }

    // Confidence tinggi (≥ 0.80) → langsung EXECUTE
    _executeIntent(result);
  }

  void _onSpeechDone() {
    if (_isDisposed) return;
    // Kalau sudah di state lain (confirmation/executing/error), jangan override
    if (state.uiState == VoiceUiState.listening ||
        state.uiState == VoiceUiState.transcribing) {
      final transcript = state.transcript?.trim();
      if (transcript != null && transcript.isNotEmpty) {
        _onSpeechResult(transcript, 0.72);
        return;
      }

      state = state.copyWith(
        uiState: VoiceUiState.error,
        errorMessage:
            'Suara belum terbaca. Dekatkan HP, bicara lebih jelas, lalu coba lagi.',
      );
    }
  }

  // ─── Confirmation ────────────────────────────────────────────

  /// Pengguna mengkonfirmasi intent yang ditampilkan.
  void confirmIntent() {
    final result = state.voiceResult;
    if (result == null) return;
    _executeIntent(result);
  }

  /// Pengguna menolak — kembali ke listening.
  Future<void> retryListening() async {
    await _service.stopListening();
    await Future<void>.delayed(const Duration(milliseconds: 350));
    await startListening();
  }

  // ─── Execution ───────────────────────────────────────────────

  /// Jalankan aksi sesuai intent. Callback diekspos lewat [onExecute].
  void _executeIntent(VoiceResult result) {
    _pendingResult = result;
    state = state.copyWith(
      uiState: VoiceUiState.executing,
      voiceResult: result,
      statusMessage: _executingMessage(result.intent),
    );
  }

  // ─── Pending Result ──────────────────────────────────────────

  /// Result yang siap dieksekusi oleh widget.
  VoiceResult? _pendingResult;

  VoiceResult? get pendingResult => _pendingResult;

  /// Dipanggil widget setelah mengambil dan menjalankan pendingResult.
  void clearPendingResult() {
    _pendingResult = null;
  }

  // ─── Stop ────────────────────────────────────────────────────

  Future<void> stopListening() async {
    await _service.stopListening();
    if (state.uiState == VoiceUiState.listening) {
      state = state.copyWith(
        uiState: VoiceUiState.idle,
        statusMessage: 'Bicara ke P.A.D.I.',
      );
    }
  }

  // ─── TTS Shortcuts ───────────────────────────────────────────

  Future<void> speak(String text) => _service.speak(text);

  Future<void> speakDiagnosisSummary({
    required String diseaseName,
    required String confidenceLabel,
  }) => _service.speakDiagnosisSummary(
    diseaseName: diseaseName,
    confidenceLabel: confidenceLabel,
  );

  Future<void> speakRecommendation(List<String> steps) =>
      _service.speakRecommendation(steps);

  // ─── Labels ──────────────────────────────────────────────────

  String _intentLabel(VoiceIntent intent) => switch (intent) {
    VoiceIntent.startPlantCheck => 'Periksa Tanaman',
    VoiceIntent.takePlantPhoto => 'Ambil Foto',
    VoiceIntent.retakePhoto => 'Foto Ulang',
    VoiceIntent.analyzePlantImage => 'Analisis Foto',
    VoiceIntent.readDiagnosis => 'Bacakan Hasil',
    VoiceIntent.readRecommendation => 'Bacakan Rekomendasi',
    VoiceIntent.escalateToPpl => 'Konsultasi ke PPL',
    VoiceIntent.checkDiseaseWarning => 'Cek Penyakit Sekitar',
    VoiceIntent.getDailyPriority => 'Prioritas Hari Ini',
    VoiceIntent.getFarmWeather => 'Info Cuaca',
    VoiceIntent.recordActivity => 'Catat Aktivitas',
    VoiceIntent.openMarketplace => 'Buka Pasar',
    VoiceIntent.unknown => 'Tidak Dikenal',
  };

  String _executingMessage(VoiceIntent intent) => switch (intent) {
    VoiceIntent.startPlantCheck => 'Membuka kamera...',
    VoiceIntent.takePlantPhoto => 'Mengambil foto...',
    VoiceIntent.retakePhoto => 'Kembali ke kamera...',
    VoiceIntent.analyzePlantImage => 'Memproses foto...',
    VoiceIntent.readDiagnosis => 'Membacakan hasil...',
    VoiceIntent.readRecommendation => 'Membacakan rekomendasi...',
    VoiceIntent.escalateToPpl => 'Menyiapkan eskalasi ke PPL...',
    VoiceIntent.checkDiseaseWarning => 'Membuka radar penyakit...',
    VoiceIntent.getDailyPriority => 'Mengambil prioritas hari ini...',
    VoiceIntent.getFarmWeather => 'Mengambil info cuaca...',
    VoiceIntent.recordActivity => 'Menyiapkan catatan aktivitas...',
    VoiceIntent.openMarketplace => 'Membuka pasar...',
    VoiceIntent.unknown => 'Memproses...',
  };
}
