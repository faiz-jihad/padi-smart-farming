import 'package:padi/core/providers/app_providers.dart';
import 'package:padi/features/auth/presentation/widgets/auth_fields.dart';
import 'package:padi/features/auth/presentation/widgets/auth_header.dart';
import 'package:padi/features/auth/presentation/widgets/auth_scaffold.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class NewPasswordScreen extends ConsumerStatefulWidget {
  const NewPasswordScreen({super.key, required this.email, required this.code});

  final String email;
  final String code;

  @override
  ConsumerState<NewPasswordScreen> createState() => _NewPasswordScreenState();
}

class _NewPasswordScreenState extends ConsumerState<NewPasswordScreen> {
  final _passwordController = TextEditingController();
  final _confirmationController = TextEditingController();

  bool _obscurePassword = true;
  bool _obscureConfirmation = true;

  @override
  void dispose() {
    _passwordController.dispose();
    _confirmationController.dispose();
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
            title: 'Ganti password',
            subtitle:
                'Buat password baru untuk mengamankan akun P.A.D.I. kamu.',
          ),
          const SizedBox(height: 24),

          if (state.message != null)
            Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
              decoration: BoxDecoration(
                color: const Color(0xFFE8F8F0),
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: const Color(0xFFB7E7D0)),
              ),
              child: Row(
                children: [
                  const Icon(
                    Icons.check_circle_rounded,
                    color: Color(0xFF087443),
                    size: 22,
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Text(
                      state.message!,
                      style: const TextStyle(
                        color: Color(0xFF087443),
                        fontSize: 14,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                  ),
                ],
              ),
            ),

          if (state.message != null) const SizedBox(height: 14),

          PadiTextField(
            controller: _passwordController,
            label: 'Password baru',
            obscureText: _obscurePassword,
            errorText: state.fieldErrors['password']?.first,
            prefixIcon: Icons.lock_outline_rounded,
            suffixIcon: IconButton(
              onPressed: () {
                setState(() {
                  _obscurePassword = !_obscurePassword;
                });
              },
              icon: Icon(
                _obscurePassword
                    ? Icons.visibility_rounded
                    : Icons.visibility_off_rounded,
              ),
            ),
          ),

          const SizedBox(height: 14),

          PadiTextField(
            controller: _confirmationController,
            label: 'Konfirmasi password baru',
            obscureText: _obscureConfirmation,
            errorText: state.fieldErrors['password_confirmation']?.first,
            prefixIcon: Icons.verified_user_outlined,
            suffixIcon: IconButton(
              onPressed: () {
                setState(() {
                  _obscureConfirmation = !_obscureConfirmation;
                });
              },
              icon: Icon(
                _obscureConfirmation
                    ? Icons.visibility_rounded
                    : Icons.visibility_off_rounded,
              ),
            ),
          ),

          const SizedBox(height: 22),

          FilledButton(
            onPressed: state.isSubmitting ? null : _submit,
            child: state.isSubmitting
                ? const SizedBox.square(
                    dimension: 20,
                    child: CircularProgressIndicator(
                      strokeWidth: 2,
                      color: Colors.white,
                    ),
                  )
                : const Text('Simpan password'),
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

  Future<void> _submit() async {
    final success = await ref
        .read(authControllerProvider)
        .resetPassword(
          email: widget.email,
          code: widget.code,
          password: _passwordController.text,
          passwordConfirmation: _confirmationController.text,
        );

    if (!mounted || !success) {
      return;
    }

    context.go('/login?reset=success');
  }
}
