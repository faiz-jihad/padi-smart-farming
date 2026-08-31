import 'package:padi/core/providers/app_providers.dart';
import 'package:padi/features/auth/presentation/widgets/auth_fields.dart';
import 'package:padi/features/auth/presentation/widgets/auth_header.dart';
import 'package:padi/features/auth/presentation/widgets/auth_scaffold.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class ResetPasswordScreen extends ConsumerStatefulWidget {
  const ResetPasswordScreen({
    super.key,
    required this.email,
  });

  final String email;

  @override
  ConsumerState<ResetPasswordScreen> createState() =>
      _ResetPasswordScreenState();
}

class _ResetPasswordScreenState
    extends ConsumerState<ResetPasswordScreen> {
  final _codeController = TextEditingController();

  @override
  void dispose() {
    _codeController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final auth = ref.watch(authControllerProvider);
    final state = auth.state;

    return AuthScaffold(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          const AuthHeader(
            title: 'Verifikasi kode',
            subtitle:
                'Masukkan kode 6 digit yang dikirim ke email kamu.',
          ),
          const SizedBox(height: 24),

          if (state.message != null)
            Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(
                horizontal: 16,
                vertical: 14,
              ),
              decoration: BoxDecoration(
                color: state.isError
                    ? const Color(0xFFFFEAEA)
                    : const Color(0xFFE8F8F0),
                borderRadius: BorderRadius.circular(16),
                border: Border.all(
                  color: state.isError
                      ? const Color(0xFFF5B5B5)
                      : const Color(0xFFB7E7D0),
                ),
              ),
              child: Row(
                children: [
                  Icon(
                    state.isError
                        ? Icons.error_rounded
                        : Icons.check_circle_rounded,
                    color: state.isError
                        ? const Color(0xFFDC2626)
                        : const Color(0xFF087443),
                    size: 22,
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Text(
                      state.message!,
                      style: TextStyle(
                        color: state.isError
                            ? const Color(0xFFB91C1C)
                            : const Color(0xFF087443),
                        fontSize: 14,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                  ),
                ],
              ),
            ),

          if (state.message != null)
            const SizedBox(height: 14),

          PadiTextField(
            controller: _codeController,
            label: 'Kode verifikasi',
            keyboardType: TextInputType.number,
            errorText: state.fieldErrors['code']?.first,
            prefixIcon: Icons.pin_rounded,
          ),

          const SizedBox(height: 22),

          FilledButton(
            onPressed: state.isSubmitting ? null : _verify,
            child: state.isSubmitting
                ? const SizedBox.square(
                    dimension: 20,
                    child: CircularProgressIndicator(
                      strokeWidth: 2,
                      color: Colors.white,
                    ),
                  )
                : const Text('Verifikasi kode'),
          ),

          const SizedBox(height: 14),

          TextButton(
            onPressed: state.isSubmitting
                ? null
                : () => context.go('/forgot-password'),
            child: const Text('Kirim ulang kode'),
          ),

          TextButton(
            onPressed: state.isSubmitting
                ? null
                : () => context.go('/login'),
            child: const Text('Kembali masuk'),
          ),
        ],
      ),
    );
  }

  Future<void> _verify() async {
    final code = _codeController.text.trim();

    if (code.isEmpty) {
      return;
    }

    final success = await ref
        .read(authControllerProvider)
        .verifyResetCode(
          email: widget.email,
          code: code,
        );

    if (!mounted || !success) {
      return;
    }

    context.go(
      '/reset-password/new',
      extra: {
        'email': widget.email,
        'code': code,
      },
    );
  }
}