import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:padi/core/providers/app_providers.dart';
import 'package:padi/features/auth/presentation/widgets/auth_fields.dart';
import 'package:padi/features/home/presentation/tokens/home_tokens.dart';

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

    return Scaffold(
      backgroundColor: const Color(0xFF042F1E),
      body: Stack(
        children: [
          // Background Image
          Positioned.fill(
            child: Image.asset(
              'assets/images/splash_background.jpeg',
              fit: BoxFit.cover,
              errorBuilder: (context, error, stackTrace) => Container(color: HomeColors.deepGreen),
            ),
          ),

          // Deep Agricultural Gradient Scrim
          Positioned.fill(
            child: Container(
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.topCenter,
                  end: Alignment.bottomCenter,
                  colors: [
                    Colors.black.withOpacity(0.35),
                    const Color(0xFF042F1E).withOpacity(0.82),
                    const Color(0xFF021B11).withOpacity(0.96),
                  ],
                  stops: const [0.0, 0.45, 1.0],
                ),
              ),
            ),
          ),

          // Main Scrollable Content
          SafeArea(
            child: Center(
              child: ConstrainedBox(
                constraints: const BoxConstraints(maxWidth: 480),
                child: SingleChildScrollView(
                  keyboardDismissBehavior: ScrollViewKeyboardDismissBehavior.onDrag,
                  padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      const SizedBox(height: 16),

                      // Logo & Branding Header
                      Center(
                        child: Container(
                          width: 56,
                          height: 56,
                          padding: const EdgeInsets.all(10),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(16),
                            boxShadow: [
                              BoxShadow(
                                color: Colors.black.withOpacity(0.15),
                                blurRadius: 16,
                                offset: const Offset(0, 4),
                              ),
                            ],
                          ),
                          child: Image.asset(
                            'assets/images/padi-logo.png',
                            errorBuilder: (context, error, stackTrace) => const Icon(
                              Icons.eco_rounded,
                              color: HomeColors.primaryGreen,
                              size: 32,
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(height: 12),

                      const Center(
                        child: Text(
                          'P.A.D.I.',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 24,
                            fontWeight: FontWeight.w900,
                            letterSpacing: 2.0,
                          ),
                        ),
                      ),
                      const Center(
                        child: Text(
                          'Predictive Agriculture & Disease Intelligence',
                          style: TextStyle(
                            color: Color(0xFFFDE68A),
                            fontSize: 12,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                      ),

                      const SizedBox(height: 24),

                      // Form Container Card
                      Container(
                        padding: const EdgeInsets.fromLTRB(22, 26, 22, 22),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(24),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withOpacity(0.20),
                              blurRadius: 24,
                              offset: const Offset(0, 8),
                            ),
                          ],
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.stretch,
                          children: [
                            const Text(
                              'Masuk ke Akun',
                              style: TextStyle(
                                fontSize: 20,
                                fontWeight: FontWeight.w900,
                                color: HomeColors.textPrimary,
                                letterSpacing: -0.3,
                              ),
                            ),
                            const SizedBox(height: 4),
                            const Text(
                              'Kelola lahan & pantau tanaman padi Anda',
                              style: TextStyle(
                                fontSize: 12.5,
                                color: HomeColors.textSecondary,
                              ),
                            ),
                            const SizedBox(height: 20),

                            if (state.message != null && state.message!.isNotEmpty) ...[
                              Container(
                                padding: const EdgeInsets.all(12),
                                decoration: BoxDecoration(
                                  color: HomeColors.dangerBg,
                                  borderRadius: BorderRadius.circular(HomeRadius.md),
                                  border: Border.all(color: const Color(0xFFFECDD3)),
                                ),
                                child: Row(
                                  children: [
                                    const Icon(Icons.error_outline_rounded, color: HomeColors.danger, size: 18),
                                    const SizedBox(width: 8),
                                    Expanded(
                                      child: Text(
                                        state.message!,
                                        style: const TextStyle(
                                          color: HomeColors.danger,
                                          fontSize: 12.5,
                                          fontWeight: FontWeight.w700,
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                              const SizedBox(height: 16),
                            ],

                            // Email Field
                            PadiTextField(
                              controller: _emailController,
                              label: 'Email',
                              keyboardType: TextInputType.emailAddress,
                              textInputAction: TextInputAction.next,
                              errorText: state.fieldErrors['email']?.first,
                              decoration: _inputDecoration('Email Anda', Icons.mail_outline_rounded),
                            ),

                            const SizedBox(height: 14),

                            // Password Field
                            PadiTextField(
                              controller: _passwordController,
                              label: 'Password',
                              obscureText: _obscurePassword,
                              errorText: state.fieldErrors['password']?.first,
                              decoration: _inputDecoration('Kata sandi', Icons.lock_outline_rounded).copyWith(
                                suffixIcon: IconButton(
                                  tooltip: _obscurePassword ? 'Tampilkan' : 'Sembunyikan',
                                  onPressed: () => setState(() => _obscurePassword = !_obscurePassword),
                                  icon: Icon(
                                    _obscurePassword ? Icons.visibility_outlined : Icons.visibility_off_outlined,
                                    color: HomeColors.textSecondary,
                                    size: 20,
                                  ),
                                ),
                              ),
                            ),

                            Align(
                              alignment: Alignment.centerRight,
                              child: TextButton(
                                onPressed: state.isSubmitting ? null : () => context.go('/forgot-password'),
                                child: const Text(
                                  'Lupa password?',
                                  style: TextStyle(
                                    color: HomeColors.primaryGreen,
                                    fontSize: 12.5,
                                    fontWeight: FontWeight.w700,
                                  ),
                                ),
                              ),
                            ),

                            const SizedBox(height: 8),

                            // Submit Button
                            SizedBox(
                              height: 50,
                              child: FilledButton(
                                onPressed: state.isSubmitting ? null : _submit,
                                style: FilledButton.styleFrom(
                                  backgroundColor: HomeColors.primaryGreen,
                                  foregroundColor: Colors.white,
                                  elevation: 0,
                                  shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(HomeRadius.md),
                                  ),
                                ),
                                child: state.isSubmitting
                                    ? const SizedBox.square(
                                        dimension: 20,
                                        child: CircularProgressIndicator(
                                          strokeWidth: 2.2,
                                          color: Colors.white,
                                        ),
                                      )
                                    : const Text(
                                        'Masuk Sekarang',
                                        style: TextStyle(
                                          fontSize: 15,
                                          fontWeight: FontWeight.w900,
                                        ),
                                      ),
                              ),
                            ),

                            const SizedBox(height: 18),

                            // Register link
                            Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                const Text(
                                  'Belum punya akun? ',
                                  style: TextStyle(color: HomeColors.textSecondary, fontSize: 13),
                                ),
                                GestureDetector(
                                  onTap: state.isSubmitting ? null : () => context.go('/register'),
                                  child: const Text(
                                    'Daftar di sini',
                                    style: TextStyle(
                                      color: HomeColors.primaryGreen,
                                      fontWeight: FontWeight.w900,
                                      fontSize: 13,
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),

                      const SizedBox(height: 20),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  InputDecoration _inputDecoration(String hint, IconData icon) {
    return InputDecoration(
      hintText: hint,
      prefixIcon: Icon(icon, color: HomeColors.primaryGreen, size: 20),
      filled: true,
      fillColor: HomeColors.surfaceMuted,
      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(HomeRadius.md),
        borderSide: const BorderSide(color: HomeColors.borderSubtle),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(HomeRadius.md),
        borderSide: const BorderSide(color: HomeColors.primaryGreen, width: 1.5),
      ),
      errorBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(HomeRadius.md),
        borderSide: const BorderSide(color: HomeColors.danger),
      ),
      focusedErrorBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(HomeRadius.md),
        borderSide: const BorderSide(color: HomeColors.danger, width: 1.5),
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
