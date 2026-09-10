import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../voice/voice_command_provider.dart';
import '../voice/voice_intent.dart';
import '../voice/voice_state.dart';

/// Modal bottom sheet utama Voice Command.
///
/// Menampilkan 6 state: idle → listening → transcribing → confirmation → executing → error.
/// Merespons pendingResult dari [VoiceCommandNotifier] untuk navigasi.
class VoiceCommandOverlay extends ConsumerStatefulWidget {
  const VoiceCommandOverlay({super.key, this.onIntentExecuted});

  final void Function(VoiceIntent intent)? onIntentExecuted;

  @override
  ConsumerState<VoiceCommandOverlay> createState() =>
      _VoiceCommandOverlayState();
}

class _VoiceCommandOverlayState extends ConsumerState<VoiceCommandOverlay>
    with SingleTickerProviderStateMixin {
  late AnimationController _pulseController;

  @override
  void initState() {
    super.initState();
    _pulseController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 900),
    )..repeat(reverse: true);

    // Auto-mulai mendengarkan saat overlay dibuka
    WidgetsBinding.instance.addPostFrameCallback((_) {
      ref.read(voiceCommandProvider.notifier).showOverlay();
      ref.read(voiceCommandProvider.notifier).startListening();
    });
  }

  @override
  void dispose() {
    _pulseController.dispose();
    ref.read(voiceCommandProvider.notifier).hideOverlay();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    // Dengarkan perubahan state dan handle navigasi
    ref.listen(voiceCommandProvider, (prev, next) {
      if (next.uiState == VoiceUiState.executing) {
        final result = ref.read(voiceCommandProvider.notifier).pendingResult;
        if (result != null) {
          ref.read(voiceCommandProvider.notifier).clearPendingResult();
          _handleExecution(result);
        }
      }
    });

    final state = ref.watch(voiceCommandProvider);

    return Container(
      margin: const EdgeInsets.fromLTRB(12, 0, 12, 12),
      decoration: BoxDecoration(
        color: const Color(0xFF0F2419),
        borderRadius: BorderRadius.circular(28),
        border: Border.all(
          color: const Color(0xFF22C55E).withValues(alpha: 0.3),
          width: 1.2,
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.5),
            blurRadius: 30,
            offset: const Offset(0, -4),
          ),
        ],
      ),
      padding: EdgeInsets.fromLTRB(
        24,
        20,
        24,
        24 + MediaQuery.of(context).padding.bottom,
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          // Handle bar
          Container(
            width: 40,
            height: 4,
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.25),
              borderRadius: BorderRadius.circular(2),
            ),
          ),
          const SizedBox(height: 20),

          // Content berganti sesuai state
          _buildContent(state),

          const SizedBox(height: 20),

          // Action buttons bawah
          _buildActionButtons(state),
        ],
      ),
    );
  }

  // ─── Content ─────────────────────────────────────────────────

  Widget _buildContent(VoiceCommandState state) {
    return switch (state.uiState) {
      VoiceUiState.idle => _buildIdleContent(),
      VoiceUiState.listening => _buildListeningContent(),
      VoiceUiState.transcribing => _buildTranscribingContent(
        state.transcript,
        state.statusMessage,
      ),
      VoiceUiState.confirmation => _buildConfirmationContent(state),
      VoiceUiState.executing => _buildExecutingContent(state.statusMessage),
      VoiceUiState.error => _buildErrorContent(state.errorMessage),
    };
  }

  // ── IDLE ──────────────────────────────────────────────────────
  Widget _buildIdleContent() {
    return Column(
      children: [
        _buildMicIcon(isListening: false, isError: false),
        const SizedBox(height: 16),
        const Text(
          'Bicara ke P.A.D.I.',
          style: TextStyle(
            color: Colors.white,
            fontSize: 20,
            fontWeight: FontWeight.w800,
          ),
        ),
        const SizedBox(height: 20),
        // Contoh command (max 4)
        _buildCommandExamples(),
      ],
    );
  }

  // ── LISTENING ─────────────────────────────────────────────────
  Widget _buildListeningContent() {
    return Column(
      children: [
        AnimatedBuilder(
          animation: _pulseController,
          builder: (context, child) {
            final scale = 1.0 + (_pulseController.value * 0.12);
            return Transform.scale(scale: scale, child: child);
          },
          child: _buildMicIcon(isListening: true, isError: false),
        ),
        const SizedBox(height: 16),
        const Text(
          'Mendengarkan...',
          style: TextStyle(
            color: Color(0xFF4ADE80),
            fontSize: 18,
            fontWeight: FontWeight.w700,
          ),
        ),
        const SizedBox(height: 8),
        Text(
          'Bicaralah sekarang',
          style: TextStyle(
            color: Colors.white.withValues(alpha: 0.55),
            fontSize: 13,
          ),
        ),
      ],
    );
  }

  // ── TRANSCRIBING ──────────────────────────────────────────────
  Widget _buildTranscribingContent(String? transcript, String? statusMessage) {
    return Column(
      children: [
        const CircularProgressIndicator(
          color: Color(0xFF4ADE80),
          strokeWidth: 2.5,
        ),
        const SizedBox(height: 16),
        Text(
          statusMessage ?? 'Memahami ucapan...',
          style: TextStyle(
            color: Colors.white,
            fontSize: 16,
            fontWeight: FontWeight.w700,
          ),
        ),
        if (transcript != null) ...[
          const SizedBox(height: 8),
          Text(
            '"$transcript"',
            textAlign: TextAlign.center,
            style: TextStyle(
              color: Colors.white.withValues(alpha: 0.6),
              fontSize: 13,
              fontStyle: FontStyle.italic,
            ),
          ),
        ],
      ],
    );
  }

  // ── CONFIRMATION ──────────────────────────────────────────────
  Widget _buildConfirmationContent(VoiceCommandState state) {
    return Column(
      children: [
        Container(
          padding: const EdgeInsets.all(14),
          decoration: BoxDecoration(
            color: const Color(0xFF16A34A).withValues(alpha: 0.15),
            borderRadius: BorderRadius.circular(16),
            border: Border.all(
              color: const Color(0xFF16A34A).withValues(alpha: 0.3),
            ),
          ),
          child: Row(
            children: [
              const Icon(
                Icons.hearing_rounded,
                color: Color(0xFF4ADE80),
                size: 22,
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Text(
                  state.statusMessage ?? '',
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 14,
                    height: 1.5,
                  ),
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 16),
        Row(
          children: [
            Expanded(
              child: _buildActionBtn(
                label: 'Ya, Lanjutkan',
                icon: Icons.check_rounded,
                color: const Color(0xFF16A34A),
                onTap: () =>
                    ref.read(voiceCommandProvider.notifier).confirmIntent(),
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: _buildActionBtn(
                label: 'Coba Lagi',
                icon: Icons.replay_rounded,
                color: const Color(0xFF475569),
                onTap: () =>
                    ref.read(voiceCommandProvider.notifier).retryListening(),
              ),
            ),
          ],
        ),
      ],
    );
  }

  // ── EXECUTING ─────────────────────────────────────────────────
  Widget _buildExecutingContent(String? message) {
    return Column(
      children: [
        const SizedBox(
          width: 52,
          height: 52,
          child: CircularProgressIndicator(
            color: Color(0xFF4ADE80),
            strokeWidth: 3,
          ),
        ),
        const SizedBox(height: 16),
        Text(
          message ?? 'Memproses...',
          style: const TextStyle(
            color: Color(0xFF4ADE80),
            fontSize: 16,
            fontWeight: FontWeight.w700,
          ),
        ),
      ],
    );
  }

  // ── ERROR ─────────────────────────────────────────────────────
  Widget _buildErrorContent(String? errorMessage) {
    return Column(
      children: [
        _buildMicIcon(isListening: false, isError: true),
        const SizedBox(height: 14),
        Text(
          errorMessage ?? 'Terjadi kesalahan.',
          textAlign: TextAlign.center,
          style: const TextStyle(
            color: Colors.white,
            fontSize: 15,
            height: 1.5,
          ),
        ),
      ],
    );
  }

  // ─── Action Buttons ───────────────────────────────────────────

  Widget _buildActionButtons(VoiceCommandState state) {
    // Confirmation punya button sendiri di content area
    if (state.uiState == VoiceUiState.confirmation) {
      return const SizedBox.shrink();
    }

    if (state.uiState == VoiceUiState.executing) {
      return const SizedBox.shrink();
    }

    if (state.uiState == VoiceUiState.error) {
      return Row(
        children: [
          Expanded(
            child: _buildActionBtn(
              label: 'Coba Lagi',
              icon: Icons.replay_rounded,
              color: const Color(0xFF16A34A),
              onTap: () =>
                  ref.read(voiceCommandProvider.notifier).startListening(),
            ),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: _buildActionBtn(
              label: 'Tutup',
              icon: Icons.close_rounded,
              color: const Color(0xFF475569),
              onTap: () => Navigator.of(context).pop(),
            ),
          ),
        ],
      );
    }

    // IDLE / LISTENING / TRANSCRIBING
    return SizedBox(
      width: double.infinity,
      child: _buildActionBtn(
        label: 'Tutup',
        icon: Icons.close_rounded,
        color: const Color(0xFF334155),
        onTap: () => Navigator.of(context).pop(),
      ),
    );
  }

  // ─── Helpers ─────────────────────────────────────────────────

  Widget _buildMicIcon({required bool isListening, required bool isError}) {
    return Container(
      width: 72,
      height: 72,
      decoration: BoxDecoration(
        shape: BoxShape.circle,
        color: isError
            ? const Color(0xFFEF4444).withValues(alpha: 0.15)
            : isListening
            ? const Color(0xFF16A34A).withValues(alpha: 0.2)
            : const Color(0xFF16A34A).withValues(alpha: 0.1),
        border: Border.all(
          color: isError
              ? const Color(0xFFEF4444).withValues(alpha: 0.5)
              : const Color(0xFF16A34A).withValues(alpha: 0.4),
          width: 1.5,
        ),
      ),
      child: Center(
        child: Icon(
          isError
              ? Icons.mic_off_rounded
              : isListening
              ? Icons.mic_rounded
              : Icons.mic_none_rounded,
          color: isError ? const Color(0xFFEF4444) : const Color(0xFF4ADE80),
          size: 32,
        ),
      ),
    );
  }

  Widget _buildCommandExamples() {
    const commands = [
      '"Periksa tanaman saya"',
      '"Apa prioritas hari ini?"',
      '"Ada penyakit di sekitar?"',
      '"Bacakan hasil diagnosa"',
    ];

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Anda bisa mengatakan:',
          style: TextStyle(
            color: Colors.white.withValues(alpha: 0.5),
            fontSize: 12,
            fontWeight: FontWeight.w600,
          ),
        ),
        const SizedBox(height: 10),
        ...commands.map(
          (cmd) => Padding(
            padding: const EdgeInsets.only(bottom: 7),
            child: Row(
              children: [
                Container(
                  width: 6,
                  height: 6,
                  decoration: const BoxDecoration(
                    shape: BoxShape.circle,
                    color: Color(0xFF4ADE80),
                  ),
                ),
                const SizedBox(width: 10),
                Text(
                  cmd,
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 14,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildActionBtn({
    required String label,
    required IconData icon,
    required Color color,
    required VoidCallback onTap,
  }) {
    return Material(
      color: color.withValues(alpha: 0.15),
      borderRadius: BorderRadius.circular(14),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(14),
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 13),
          decoration: BoxDecoration(
            border: Border.all(color: color.withValues(alpha: 0.3)),
            borderRadius: BorderRadius.circular(14),
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(icon, color: color, size: 18),
              const SizedBox(width: 8),
              Text(
                label,
                style: TextStyle(
                  color: color,
                  fontWeight: FontWeight.w700,
                  fontSize: 13,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  // ─── Navigation Execution ─────────────────────────────────────

  void _handleExecution(VoiceResult result) async {
    final notifier = ref.read(voiceCommandProvider.notifier);
    final router = GoRouter.of(context);

    // TTS acknowledgement dulu
    await notifier.speak(_acknowledgeText(result.intent));

    if (!mounted) return;

    switch (result.intent) {
      case VoiceIntent.startPlantCheck:
        if (mounted) Navigator.of(context).pop();
        router.push('/plant-check');
        break;

      case VoiceIntent.checkDiseaseWarning:
        if (mounted) Navigator.of(context).pop();
        router.push('/community-alert');
        break;

      case VoiceIntent.getDailyPriority:
        if (mounted) Navigator.of(context).pop();
        router.go('/home');
        break;

      case VoiceIntent.getFarmWeather:
        if (mounted) Navigator.of(context).pop();
        router.push('/planting-calendar');
        break;

      case VoiceIntent.openMarketplace:
        if (mounted) Navigator.of(context).pop();
        router.push('/marketplace');
        break;

      case VoiceIntent.recordActivity:
        if (mounted) Navigator.of(context).pop();
        router.push('/land/activity/add');
        break;

      case VoiceIntent.escalateToPpl:
        // Tunjukkan dialog konfirmasi PPL — jangan langsung kirim
        if (mounted) {
          await _showPplConfirmation();
        }
        break;

      case VoiceIntent.takePlantPhoto:
      case VoiceIntent.retakePhoto:
      case VoiceIntent.analyzePlantImage:
      case VoiceIntent.readDiagnosis:
      case VoiceIntent.readRecommendation:
        if (mounted) Navigator.of(context).pop();
        widget.onIntentExecuted?.call(result.intent);
        break;

      default:
        if (mounted) Navigator.of(context).pop();
        widget.onIntentExecuted?.call(result.intent);
    }
  }

  Future<void> _showPplConfirmation() async {
    final confirmed = await showDialog<bool>(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        backgroundColor: const Color(0xFF0F2419),
        title: const Text(
          'Konsultasi ke PPL?',
          style: TextStyle(color: Colors.white, fontWeight: FontWeight.w800),
        ),
        content: const Text(
          'Kirim hasil pemeriksaan ini ke Penyuluh Pertanian Lapangan?',
          style: TextStyle(color: Colors.white70),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Batal', style: TextStyle(color: Colors.white54)),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(ctx, true),
            style: FilledButton.styleFrom(
              backgroundColor: const Color(0xFF16A34A),
            ),
            child: const Text('Kirim ke PPL'),
          ),
        ],
      ),
    );
    if (confirmed == true && mounted) {
      Navigator.of(context).pop();
      GoRouter.of(context).push('/ppl-cases');
    } else if (mounted) {
      Navigator.of(context).pop();
    }
  }

  String _acknowledgeText(VoiceIntent intent) => switch (intent) {
    VoiceIntent.startPlantCheck => 'Baik, arahkan kamera ke satu daun padi.',
    VoiceIntent.takePlantPhoto => 'Mengambil foto daun.',
    VoiceIntent.retakePhoto => 'Mengulang foto daun.',
    VoiceIntent.analyzePlantImage => 'Memproses diagnosa tanaman.',
    VoiceIntent.readDiagnosis => 'Membacakan hasil diagnosa.',
    VoiceIntent.readRecommendation => 'Membacakan rekomendasi pengobatan.',
    VoiceIntent.checkDiseaseWarning =>
      'Membuka radar penyakit di sekitar lahan Anda.',
    VoiceIntent.getDailyPriority =>
      'Ini prioritas kegiatan sawah Anda hari ini.',
    VoiceIntent.getFarmWeather => 'Mengambil informasi cuaca untuk lahan Anda.',
    VoiceIntent.openMarketplace => 'Membuka halaman pasar gabah.',
    VoiceIntent.recordActivity => 'Baik, saya buka form catatan aktivitas.',
    VoiceIntent.escalateToPpl =>
      'Apakah Anda ingin mengirim hasil ini ke penyuluh?',
    _ => 'Baik.',
  };
}
