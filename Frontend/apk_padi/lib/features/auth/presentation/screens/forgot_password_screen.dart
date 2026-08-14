import 'package:padi/core/router/app_router.dart';
import 'package:padi/features/auth/presentation/widgets/auth_fields.dart';
import 'package:padi/features/auth/presentation/widgets/auth_header.dart';
import 'package:padi/features/auth/presentation/widgets/auth_scaffold.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class ForgotPasswordScreen extends ConsumerStatefulWidget {
  const ForgotPasswordScreen({super.key});

  @override
  ConsumerState<ForgotPasswordScreen> createState() => _ForgotPasswordScreenState();
}

class _ForgotPasswordScreenState extends ConsumerState<ForgotPasswordScreen> {
  final _emailController = TextEditingController();

  @override
  void dispose() {
    _emailController.dispose();
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
            title: 'Pulihkan password',
            subtitle: 'Masukkan email akun. Fitur aktif setelah mailer backend dikonfigurasi.',
          ),
          const SizedBox(height: 24),
          ErrorBanner(message: state.message ?? ''),
          if (state.message != null) const SizedBox(height: 12),
          PadiTextField(
            controller: _emailController,
            label: 'Email',
            keyboardType: TextInputType.emailAddress,
            errorText: state.fieldErrors['email']?.first,
            prefixIcon: Icons.alternate_email_rounded,
          ),
          const SizedBox(height: 22),
          FilledButton(
            onPressed: state.isSubmitting
                ? null
                : () => ref.read(authControllerProvider).forgotPassword(_emailController.text.trim()),
            child: state.isSubmitting
                ? const SizedBox.square(
                    dimension: 20,
                    child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                  )
                : const Text('Kirim instruksi'),
          ),
          const SizedBox(height: 14),
          TextButton(
            onPressed: state.isSubmitting ? null : () => context.go('/login'),
            child: const Text('Kembali masuk'),
          ),
        ],
      ),
    );
  }
}
