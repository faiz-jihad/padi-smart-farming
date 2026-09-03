import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:padi/core/widgets/error_state_view.dart';

/// Halaman penuh khusus untuk menangani kendala teknis, offline, maupun pemeliharaan.
class AppErrorScreen extends StatelessWidget {
  const AppErrorScreen({
    super.key,
    required this.type,
    this.customTitle,
    this.customMessage,
    this.onRetry,
    this.onBack,
  });

  final AppErrorType type;
  final String? customTitle;
  final String? customMessage;
  final Future<void> Function()? onRetry;
  final VoidCallback? onBack;

  factory AppErrorScreen.offline({
    Key? key,
    Future<void> Function()? onRetry,
    VoidCallback? onBack,
  }) {
    return AppErrorScreen(
      key: key,
      type: AppErrorType.noInternet,
      onRetry: onRetry,
      onBack: onBack,
    );
  }

  factory AppErrorScreen.technical({
    Key? key,
    String? details,
    Future<void> Function()? onRetry,
    VoidCallback? onBack,
  }) {
    return AppErrorScreen(
      key: key,
      type: AppErrorType.technicalError,
      customMessage: details,
      onRetry: onRetry,
      onBack: onBack,
    );
  }

  factory AppErrorScreen.timeout({
    Key? key,
    Future<void> Function()? onRetry,
    VoidCallback? onBack,
  }) {
    return AppErrorScreen(
      key: key,
      type: AppErrorType.serverTimeout,
      onRetry: onRetry,
      onBack: onBack,
    );
  }

  factory AppErrorScreen.maintenance({
    Key? key,
    VoidCallback? onBack,
  }) {
    return AppErrorScreen(
      key: key,
      type: AppErrorType.maintenance,
      onBack: onBack,
    );
  }

  factory AppErrorScreen.notFound({
    Key? key,
    VoidCallback? onBack,
  }) {
    return AppErrorScreen(
      key: key,
      type: AppErrorType.notFound,
      onBack: onBack,
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF9FAFB),
      appBar: AppBar(
        backgroundColor: const Color(0xFFF9FAFB),
        elevation: 0,
        scrolledUnderElevation: 0,
        leading: IconButton(
          onPressed: () {
            if (onBack != null) {
              onBack!();
            } else if (Navigator.of(context).canPop()) {
              Navigator.of(context).pop();
            } else {
              context.go('/home');
            }
          },
          icon: const Icon(
            Icons.arrow_back_rounded,
            color: Color(0xFF1E293B),
            size: 22,
          ),
          tooltip: 'Kembali',
        ),
      ),
      body: SafeArea(
        child: ErrorStateView(
          type: type,
          customTitle: customTitle,
          customMessage: customMessage,
          onRetry: onRetry,
          onSecondaryAction: () {
            if (onBack != null) {
              onBack!();
            } else if (Navigator.of(context).canPop()) {
              Navigator.of(context).pop();
            } else {
              context.go('/home');
            }
          },
        ),
      ),
    );
  }

  /// Helper untuk memunculkan modal bottom sheet ketika terjadi kendala di tengah aktivitas.
  static Future<void> showAsBottomSheet(
    BuildContext context, {
    required AppErrorType type,
    Future<void> Function()? onRetry,
  }) {
    return showModalBottomSheet<void>(
      context: context,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (sheetContext) {
        return SafeArea(
          child: Padding(
            padding: const EdgeInsets.symmetric(vertical: 12),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                // Top drag pill
                Container(
                  width: 36,
                  height: 4,
                  margin: const EdgeInsets.only(bottom: 8),
                  decoration: BoxDecoration(
                    color: const Color(0xFFE2E8F0),
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
                ErrorStateView(
                  type: type,
                  isCompact: true,
                  onRetry: onRetry != null
                      ? () async {
                          Navigator.of(sheetContext).pop();
                          await onRetry();
                        }
                      : null,
                  onSecondaryAction: () => Navigator.of(sheetContext).pop(),
                ),
              ],
            ),
          ),
        );
      },
    );
  }
}
