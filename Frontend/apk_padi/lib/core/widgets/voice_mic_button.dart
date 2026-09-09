import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../voice/voice_command_provider.dart';
import '../voice/voice_intent.dart';
import '../voice/voice_state.dart';
import 'voice_command_overlay.dart';

/// Tombol mic reusable yang bisa ditempatkan di mana saja.
///
/// Ukuran besar (56px default) agar mudah diklik petani.
/// Menekan tombol ini membuka [VoiceCommandOverlay].
class VoiceMicButton extends ConsumerWidget {
  const VoiceMicButton({
    super.key,
    this.size = 56.0,
    this.mini = false,
    this.onIntentExecuted,
    this.tooltip = 'Bicara ke P.A.D.I.',
  });

  /// Ukuran tombol (diameter).
  final double size;

  /// Jika true, tampilkan versi lebih kecil (38px) untuk AppBar dsb.
  final bool mini;

  /// Callback setelah intent berhasil dieksekusi.
  final void Function(VoiceIntent intent)? onIntentExecuted;

  final String tooltip;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final voiceState = ref.watch(voiceCommandProvider);
    final isListening = voiceState.uiState == VoiceUiState.listening;

    final effectiveSize = mini ? 38.0 : size;
    final iconSize = mini ? 18.0 : 26.0;

    return Tooltip(
      message: tooltip,
      child: GestureDetector(
        onTap: () => _openOverlay(context, ref),
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 200),
          width: effectiveSize,
          height: effectiveSize,
          decoration: BoxDecoration(
            shape: BoxShape.circle,
            color: isListening
                ? const Color(0xFFEF4444)   // merah saat aktif
                : const Color(0xFF16A34A),   // hijau default
            boxShadow: [
              BoxShadow(
                color: (isListening
                    ? const Color(0xFFEF4444)
                    : const Color(0xFF16A34A)).withValues(alpha: 0.4),
                blurRadius: isListening ? 18 : 10,
                spreadRadius: isListening ? 3 : 0,
                offset: const Offset(0, 3),
              ),
            ],
          ),
          child: Center(
            child: Icon(
              isListening ? Icons.mic_rounded : Icons.mic_none_rounded,
              color: Colors.white,
              size: iconSize,
            ),
          ),
        ),
      ),
    );
  }

  void _openOverlay(BuildContext context, WidgetRef ref) {
    showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      barrierColor: Colors.black.withValues(alpha: 0.5),
      builder: (ctx) => VoiceCommandOverlay(
        onIntentExecuted: onIntentExecuted,
      ),
    );
  }
}
