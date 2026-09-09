import 'voice_intent.dart';

/// Status state machine Voice Command.
enum VoiceUiState {
  /// Idle — menunggu pengguna menekan tombol mic.
  idle,

  /// Mikrofon aktif, mendengarkan suara pengguna.
  listening,

  /// Audio diterima, sedang diproses menjadi teks (STT).
  transcribing,

  /// Intent terdeteksi, meminta konfirmasi dari pengguna.
  confirmation,

  /// Menjalankan aksi (navigasi, TTS, dsb).
  executing,

  /// Gagal memahami atau terjadi error.
  error,
}

/// State lengkap Voice Command UI.
class VoiceCommandState {
  const VoiceCommandState({
    this.uiState = VoiceUiState.idle,
    this.transcript,
    this.voiceResult,
    this.statusMessage,
    this.errorMessage,
    this.isOverlayVisible = false,
  });

  final VoiceUiState uiState;

  /// Teks asli hasil STT.
  final String? transcript;

  /// Hasil intent resolution.
  final VoiceResult? voiceResult;

  /// Pesan yang ditampilkan di overlay (contoh: "Mendengarkan...").
  final String? statusMessage;

  /// Pesan error jika uiState == error.
  final String? errorMessage;

  /// Apakah overlay modal sedang terbuka.
  final bool isOverlayVisible;

  VoiceCommandState copyWith({
    VoiceUiState? uiState,
    String? transcript,
    VoiceResult? voiceResult,
    String? statusMessage,
    String? errorMessage,
    bool? isOverlayVisible,
  }) {
    return VoiceCommandState(
      uiState: uiState ?? this.uiState,
      transcript: transcript ?? this.transcript,
      voiceResult: voiceResult ?? this.voiceResult,
      statusMessage: statusMessage ?? this.statusMessage,
      errorMessage: errorMessage ?? this.errorMessage,
      isOverlayVisible: isOverlayVisible ?? this.isOverlayVisible,
    );
  }

  @override
  String toString() => 'VoiceCommandState(uiState: $uiState, transcript: "$transcript")';
}
