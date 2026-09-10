import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

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

    final effectiveSize = mini ? 48.0 : size;
    final iconSize = mini ? 22.0 : 28.0;
    final buttonColor = isListening
        ? const Color(0xFF047857)
        : const Color(0xFF059669);

    return Semantics(
      button: true,
      label: 'Buka bantuan suara',
      child: Tooltip(
        message: tooltip,
        child: Material(
          color: Colors.transparent,
          shape: const CircleBorder(),
          child: InkWell(
            customBorder: const CircleBorder(),
            onTap: () => _openOverlay(context, ref),
            child: AnimatedContainer(
              duration: const Duration(milliseconds: 200),
              width: effectiveSize,
              height: effectiveSize,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: buttonColor,
                border: Border.all(
                  color: Colors.white.withValues(alpha: 0.82),
                  width: mini ? 1.4 : 2.4,
                ),
                boxShadow: [
                  BoxShadow(
                    color: const Color(
                      0xFF059669,
                    ).withValues(alpha: isListening ? 0.36 : 0.22),
                    blurRadius: isListening ? 22 : 14,
                    spreadRadius: isListening ? 2 : 0,
                    offset: const Offset(0, 6),
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
        ),
      ),
    );
  }

  Future<void> _openOverlay(BuildContext context, WidgetRef ref) async {
    ref.read(voiceCommandProvider.notifier).hideOverlay();
    await Future<void>.delayed(const Duration(milliseconds: 40));

    if (!context.mounted) return;

    final targetRoute = await showModalBottomSheet<String>(
      context: context,
      useRootNavigator: true,
      isScrollControlled: true,
      useSafeArea: true,
      backgroundColor: Colors.transparent,
      barrierColor: Colors.black.withValues(alpha: 0.5),
      builder: (ctx) => VoiceCommandOverlay(onIntentExecuted: onIntentExecuted),
    );

    if (context.mounted) {
      ref.read(voiceCommandProvider.notifier).hideOverlay();
      if (targetRoute != null && targetRoute.isNotEmpty) {
        WidgetsBinding.instance.addPostFrameCallback((_) {
          if (!context.mounted) return;
          if (targetRoute == '/home') {
            context.go(targetRoute);
          } else {
            context.push(targetRoute);
          }
        });
      }
    }
  }
}
