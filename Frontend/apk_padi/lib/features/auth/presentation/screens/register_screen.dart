import 'package:padi/core/router/app_router.dart';
import 'package:padi/features/auth/presentation/widgets/auth_fields.dart';
import 'package:padi/features/auth/presentation/widgets/auth_header.dart';
import 'package:padi/features/auth/presentation/widgets/auth_scaffold.dart';
import 'package:padi/features/auth/presentation/widgets/padi_theme.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class RegisterScreen extends ConsumerStatefulWidget {
  const RegisterScreen({super.key});

  @override
  ConsumerState<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends ConsumerState<RegisterScreen> {
  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  final _phoneController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmationController = TextEditingController();
  String _accountType = 'farmer';
  bool _obscurePassword = true;

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
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
            title: 'Buat akun P.A.D.I.',
            subtitle: 'Registrasi publik tersedia untuk Petani dan Pembeli.',
          ),
          const SizedBox(height: 24),
          ErrorBanner(message: state.message ?? ''),
          if (state.message != null) const SizedBox(height: 12),
          PadiTextField(
            controller: _nameController,
            label: 'Nama lengkap',
            textInputAction: TextInputAction.next,
            errorText: state.fieldErrors['name']?.first,
            prefixIcon: Icons.person_outline_rounded,
          ),
          const SizedBox(height: 14),
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
            controller: _phoneController,
            label: 'Nomor telepon',
            keyboardType: TextInputType.phone,
            textInputAction: TextInputAction.next,
            errorText: state.fieldErrors['phone']?.first,
            prefixIcon: Icons.phone_rounded,
          ),
          const SizedBox(height: 14),
          SegmentedButton<String>(
            segments: const [
              ButtonSegment(value: 'farmer', label: Text('Petani'), icon: Icon(Icons.grass_rounded)),
              ButtonSegment(value: 'buyer', label: Text('Pembeli'), icon: Icon(Icons.shopping_bag_rounded)),
            ],
            selected: {_accountType},
            style: SegmentedButton.styleFrom(
              selectedBackgroundColor: padiGreen,
              selectedForegroundColor: Colors.white,
            ),
            onSelectionChanged: state.isSubmitting
                ? null
                : (values) => setState(() => _accountType = values.first),
          ),
          if (state.fieldErrors['account_type']?.isNotEmpty ?? false) ...[
            const SizedBox(height: 6),
            Text(
              state.fieldErrors['account_type']!.first,
              style: const TextStyle(color: Color(0xFFC2410C), fontSize: 12),
            ),
          ],
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
          const SizedBox(height: 14),
          PadiTextField(
            controller: _confirmationController,
            label: 'Konfirmasi password',
            obscureText: _obscurePassword,
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
                : const Text('Daftar'),
          ),
          const SizedBox(height: 14),
          TextButton(
            onPressed: state.isSubmitting ? null : () => context.go('/login'),
            child: const Text('Sudah punya akun? Masuk'),
          ),
        ],
      ),
    );
  }

  Future<void> _submit() async {
    await ref.read(authControllerProvider).register(
          name: _nameController.text.trim(),
          email: _emailController.text.trim(),
          phone: _phoneController.text.trim(),
          accountType: _accountType,
          password: _passwordController.text,
          passwordConfirmation: _confirmationController.text,
        );
  }
}
