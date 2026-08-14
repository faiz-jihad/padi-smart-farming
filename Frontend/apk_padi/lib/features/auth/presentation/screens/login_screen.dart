import 'package:padi/core/router/app_router.dart';
import 'package:padi/features/auth/presentation/widgets/auth_fields.dart';
import 'package:padi/features/auth/presentation/widgets/auth_header.dart';
import 'package:padi/features/auth/presentation/widgets/auth_scaffold.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class LoginScreen extends ConsumerStatefulWidget {
  const LoginScreen({super.key});

  @override
  ConsumerState<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends ConsumerState<LoginScreen> {
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _obscurePassword = true;

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
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
            title: 'Masuk ke P.A.D.I.',
            subtitle: 'Kelola akun pertanian dan lanjutkan sesi dengan aman.',
          ),
          const SizedBox(height: 24),
          ErrorBanner(message: state.message ?? ''),
          if (state.message != null) const SizedBox(height: 12),
          PadiTextField(
            controller: _emailController,
            label: 'Email',
            keyboardType: TextInputType.emailAddress,
            textInputAction: TextInputAction.next,
            errorText: state.fieldErrors['email']?.first,
            prefixIcon: Icons.alternate_email_rounded,
          ),
          const SizedBox(height: 14),
          PadiTextField(
            controller: _passwordController,
            label: 'Password',
            obscureText: _obscurePassword,
            errorText: state.fieldErrors['password']?.first,
            prefixIcon: Icons.lock_outline_rounded,
            suffixIcon: IconButton(
              tooltip: _obscurePassword ? 'Tampilkan password' : 'Sembunyikan password',
              onPressed: () => setState(() => _obscurePassword = !_obscurePassword),
              icon: Icon(_obscurePassword ? Icons.visibility_rounded : Icons.visibility_off_rounded),
            ),
          ),
          Align(
            alignment: Alignment.centerRight,
            child: TextButton(
              onPressed: state.isSubmitting ? null : () => context.go('/forgot-password'),
              child: const Text('Lupa password?'),
            ),
          ),
          const SizedBox(height: 8),
          FilledButton(
            onPressed: state.isSubmitting ? null : _submit,
            child: state.isSubmitting
                ? const SizedBox.square(
                    dimension: 20,
                    child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                  )
                : const Text('Masuk'),
          ),
          const SizedBox(height: 14),
          TextButton(
            onPressed: state.isSubmitting ? null : () => context.go('/register'),
            child: const Text('Belum punya akun? Daftar'),
          ),
        ],
      ),
    );
  }

  Future<void> _submit() async {
    await ref.read(authControllerProvider).login(
          email: _emailController.text.trim(),
          password: _passwordController.text,
        );
  }
}
