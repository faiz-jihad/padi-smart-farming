import 'package:padi/core/router/app_router.dart';
import 'package:padi/features/auth/presentation/widgets/auth_fields.dart';
import 'package:padi/features/auth/presentation/widgets/padi_theme.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class ChangePasswordScreen extends ConsumerStatefulWidget {
  const ChangePasswordScreen({super.key});

  @override
  ConsumerState<ChangePasswordScreen> createState() => _ChangePasswordScreenState();
}

class _ChangePasswordScreenState extends ConsumerState<ChangePasswordScreen> {
  final _currentController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmationController = TextEditingController();
  bool _obscure = true;

  @override
  void dispose() {
    _currentController.dispose();
    _passwordController.dispose();
    _confirmationController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final auth = ref.watch(authControllerProvider);
    final state = auth.state;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Ubah Password'),
        leading: IconButton(
          tooltip: 'Kembali',
          onPressed: () => context.go('/profile'),
          icon: const Icon(Icons.arrow_back_rounded),
        ),
      ),
      body: SafeArea(
        child: ListView(
          padding: const EdgeInsets.all(22),
          children: [
            const Text(
              'Keamanan Akun',
              style: TextStyle(fontSize: 24, fontWeight: FontWeight.w900, color: padiInk),
            ),
            const SizedBox(height: 8),
            Text(
              'Setelah password berubah, sesi perangkat lain akan diakhiri.',
              style: Theme.of(context).textTheme.bodyMedium?.copyWith(color: padiMuted),
            ),
            const SizedBox(height: 22),
            ErrorBanner(message: state.message ?? ''),
            if (state.message != null) const SizedBox(height: 12),
            PadiTextField(
              controller: _currentController,
              label: 'Password saat ini',
              obscureText: _obscure,
              errorText: state.fieldErrors['current_password']?.first,
              prefixIcon: Icons.lock_open_rounded,
            ),
            const SizedBox(height: 14),
            PadiTextField(
              controller: _passwordController,
              label: 'Password baru',
              obscureText: _obscure,
              errorText: state.fieldErrors['password']?.first,
              prefixIcon: Icons.lock_outline_rounded,
              suffixIcon: IconButton(
                tooltip: _obscure ? 'Tampilkan password' : 'Sembunyikan password',
                onPressed: () => setState(() => _obscure = !_obscure),
                icon: Icon(_obscure ? Icons.visibility_rounded : Icons.visibility_off_rounded),
              ),
            ),
            const SizedBox(height: 14),
            PadiTextField(
              controller: _confirmationController,
              label: 'Konfirmasi password baru',
              obscureText: _obscure,
              prefixIcon: Icons.verified_user_outlined,
            ),
            const SizedBox(height: 22),
            FilledButton(
              onPressed: state.isSubmitting ? null : _submit,
              child: state.isSubmitting
                  ? const SizedBox.square(
                      dimension: 20,
                      child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                    )
                  : const Text('Ubah password'),
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _submit() {
    return ref.read(authControllerProvider).changePassword(
          currentPassword: _currentController.text,
          password: _passwordController.text,
          passwordConfirmation: _confirmationController.text,
        );
  }
}
