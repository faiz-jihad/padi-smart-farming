import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:padi/core/providers/app_providers.dart';
import 'package:padi/features/auth/presentation/widgets/auth_fields.dart';
import 'package:padi/features/home/presentation/tokens/home_tokens.dart';

class RegisterScreen extends ConsumerStatefulWidget {
  const RegisterScreen({super.key, this.initialRole});

  final String? initialRole;

  @override
  ConsumerState<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends ConsumerState<RegisterScreen> {
  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  final _phoneController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmationController = TextEditingController();
  late String _accountType;
  bool _obscurePassword = true;

  @override
  void initState() {
    super.initState();
    _accountType = widget.initialRole == 'buyer' ? 'buyer' : 'farmer';
  }

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
                  padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
                  physics: const BouncingScrollPhysics(),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      // Back Button to Role Selection
                      Align(
                        alignment: Alignment.centerLeft,
                        child: InkWell(
                          onTap: () {
                            if (Navigator.of(context).canPop()) {
                              Navigator.of(context).pop();
                            } else {
                              context.go('/select-role');
                            }
                          },
                          borderRadius: BorderRadius.circular(20),
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                            decoration: BoxDecoration(
                              color: Colors.white.withOpacity(0.15),
                              borderRadius: BorderRadius.circular(20),
                              border: Border.all(color: Colors.white.withOpacity(0.2)),
                            ),
                            child: const Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Icon(Icons.arrow_back_rounded, size: 16, color: Colors.white),
                                SizedBox(width: 6),
                                Text(
                                  'Pilih Ulang Peran',
                                  style: TextStyle(
                                    fontSize: 12,
                                    fontWeight: FontWeight.w700,
                                    color: Colors.white,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ),

                      const SizedBox(height: 12),

                      // Logo and Brand Header
                      Center(
                        child: Column(
                          children: [
                            Container(
                              width: 64,
                              height: 64,
                              padding: const EdgeInsets.all(12),
                              decoration: BoxDecoration(
                                color: Colors.white,
                                shape: BoxShape.circle,
                                boxShadow: [
                                  BoxShadow(
                                    color: Colors.black.withOpacity(0.30),
                                    blurRadius: 20,
                                    offset: const Offset(0, 6),
                                  ),
                                ],
                              ),
                              child: Image.asset(
                                'assets/images/padi-logo.png',
                                fit: BoxFit.contain,
                                errorBuilder: (context, error, stackTrace) => const Icon(
                                  Icons.eco_rounded,
                                  color: HomeColors.primaryGreen,
                                  size: 32,
                                ),
                              ),
                            ),
                            const SizedBox(height: 10),
                            const Text(
                              'P.A.D.I.',
                              style: TextStyle(
                                fontSize: 24,
                                fontWeight: FontWeight.w900,
                                color: Colors.white,
                                letterSpacing: 2,
                              ),
                            ),
                            const SizedBox(height: 2),
                            Text(
                              _accountType == 'buyer'
                                  ? 'Registrasi Portal Pembeli & Industri'
                                  : 'Daftar Akun Baru & Kelola Lahan Pertanian',
                              style: TextStyle(
                                fontSize: 13,
                                color: const Color(0xFFFDE68A).withOpacity(0.92),
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ],
                        ),
                      ),

                      const SizedBox(height: 18),

                      // White Form Card
                      Container(
                        padding: const EdgeInsets.fromLTRB(20, 20, 20, 16),
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
                            // Role Indicator Header with "Ganti Peran"
                            Container(
                              margin: const EdgeInsets.only(bottom: 14),
                              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                              decoration: BoxDecoration(
                                color: _accountType == 'buyer'
                                    ? const Color(0xFFECFDF5)
                                    : const Color(0xFFF0FDF4),
                                borderRadius: BorderRadius.circular(10),
                                border: Border.all(
                                  color: _accountType == 'buyer'
                                      ? const Color(0xFF34D399)
                                      : const Color(0xFF86EFAC),
                                ),
                              ),
                              child: Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Expanded(
                                    child: Row(
                                      children: [
                                        Icon(
                                          _accountType == 'buyer'
                                              ? Icons.storefront_rounded
                                              : Icons.grass_rounded,
                                          size: 16,
                                          color: _accountType == 'buyer'
                                              ? const Color(0xFF0F5132)
                                              : HomeColors.primaryGreen,
                                        ),
                                        const SizedBox(width: 8),
                                        Expanded(
                                          child: Text(
                                            _accountType == 'buyer'
                                                ? 'Peran: Pembeli / Mitra B2B'
                                                : 'Peran: Petani Mitra P.A.D.I.',
                                            maxLines: 1,
                                            overflow: TextOverflow.ellipsis,
                                            style: TextStyle(
                                              fontSize: 12,
                                              fontWeight: FontWeight.w900,
                                              color: _accountType == 'buyer'
                                                  ? const Color(0xFF0F5132)
                                                  : HomeColors.primaryGreen,
                                            ),
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                  const SizedBox(width: 8),
                                  InkWell(
                                    onTap: state.isSubmitting
                                        ? null
                                        : () {
                                            if (Navigator.of(context).canPop()) {
                                              Navigator.of(context).pop();
                                            } else {
                                              context.go('/select-role');
                                            }
                                          },
                                    child: Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                      decoration: BoxDecoration(
                                        color: (_accountType == 'buyer'
                                                ? const Color(0xFF0F5132)
                                                : HomeColors.primaryGreen)
                                            .withOpacity(0.12),
                                        borderRadius: BorderRadius.circular(6),
                                      ),
                                      child: Row(
                                        children: [
                                          Text(
                                            'Ganti',
                                            style: TextStyle(
                                              fontSize: 11,
                                              fontWeight: FontWeight.w800,
                                              color: _accountType == 'buyer'
                                                  ? const Color(0xFF0F5132)
                                                  : HomeColors.primaryGreen,
                                            ),
                                          ),
                                          const SizedBox(width: 2),
                                          Icon(
                                            Icons.edit_rounded,
                                            size: 11,
                                            color: _accountType == 'buyer'
                                                ? const Color(0xFF0F5132)
                                                : HomeColors.primaryGreen,
                                          ),
                                        ],
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ),

                            Text(
                              _accountType == 'buyer'
                                  ? 'Pendaftaran Akun Pembeli'
                                  : 'Pendaftaran Akun Petani',
                              style: const TextStyle(
                                fontSize: 20,
                                fontWeight: FontWeight.w900,
                                color: HomeColors.textPrimary,
                                letterSpacing: -0.3,
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              _accountType == 'buyer'
                                  ? 'Lengkapi data untuk mulai berbelanja hasil panen raya'
                                  : 'Lengkapi data untuk memulai monitoring tanaman padi Anda',
                              style: const TextStyle(
                                fontSize: 12.5,
                                color: HomeColors.textSecondary,
                              ),
                            ),
                            const SizedBox(height: 16),

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
                              const SizedBox(height: 14),
                            ],

                            // Name Field
                            PadiTextField(
                              controller: _nameController,
                              label: 'Nama Lengkap',
                              textInputAction: TextInputAction.next,
                              errorText: state.fieldErrors['name']?.first,
                              decoration: _inputDecoration('Nama lengkap Anda', Icons.person_outline_rounded),
                            ),

                            const SizedBox(height: 12),

                            // Email Field
                            PadiTextField(
                              controller: _emailController,
                              label: 'Alamat Email',
                              keyboardType: TextInputType.emailAddress,
                              textInputAction: TextInputAction.next,
                              errorText: state.fieldErrors['email']?.first,
                              decoration: _inputDecoration('Email aktif Anda', Icons.mail_outline_rounded),
                            ),

                            const SizedBox(height: 12),

                            // Phone Field
                            PadiTextField(
                              controller: _phoneController,
                              label: 'Nomor WhatsApp / HP',
                              keyboardType: TextInputType.phone,
                              textInputAction: TextInputAction.next,
                              errorText: state.fieldErrors['phone']?.first,
                              decoration: _inputDecoration('081234567890', Icons.phone_android_rounded),
                            ),

                            const SizedBox(height: 12),

                            // Password Field
                            PadiTextField(
                              controller: _passwordController,
                              label: 'Password',
                              obscureText: _obscurePassword,
                              errorText: state.fieldErrors['password']?.first,
                              decoration: _inputDecoration('Minimal 8 karakter', Icons.lock_outline_rounded).copyWith(
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

                            const SizedBox(height: 12),

                            // Confirm Password Field
                            PadiTextField(
                              controller: _confirmationController,
                              label: 'Konfirmasi Password',
                              obscureText: _obscurePassword,
                              decoration: _inputDecoration('Ulangi password di atas', Icons.lock_reset_rounded),
                            ),

                            const SizedBox(height: 18),

                            // Submit Button
                            SizedBox(
                              height: 50,
                              child: FilledButton(
                                onPressed: state.isSubmitting ? null : _submit,
                                style: FilledButton.styleFrom(
                                  backgroundColor: _accountType == 'buyer'
                                      ? const Color(0xFF0F5132)
                                      : HomeColors.primaryGreen,
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
                                    : Text(
                                        _accountType == 'buyer'
                                            ? 'Daftar sebagai Pembeli B2B'
                                            : 'Daftar sebagai Petani',
                                        style: const TextStyle(
                                          fontSize: 15,
                                          fontWeight: FontWeight.w900,
                                        ),
                                      ),
                              ),
                            ),

                            const SizedBox(height: 16),

                            // Login Link
                            Wrap(
                              alignment: WrapAlignment.center,
                              crossAxisAlignment: WrapCrossAlignment.center,
                              children: [
                                const Text(
                                  'Sudah punya akun? ',
                                  style: TextStyle(color: HomeColors.textSecondary, fontSize: 13),
                                ),
                                InkWell(
                                  onTap: state.isSubmitting ? null : () => context.go('/login'),
                                  borderRadius: BorderRadius.circular(4),
                                  child: Padding(
                                    padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 2),
                                    child: Text(
                                      'Masuk di sini',
                                      style: TextStyle(
                                        color: _accountType == 'buyer'
                                            ? const Color(0xFF0F5132)
                                            : HomeColors.primaryGreen,
                                        fontWeight: FontWeight.w900,
                                        fontSize: 13,
                                      ),
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
      prefixIcon: Icon(icon, color: _accountType == 'buyer' ? const Color(0xFF0F5132) : HomeColors.primaryGreen, size: 20),
      filled: true,
      fillColor: HomeColors.surfaceMuted,
      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(HomeRadius.md),
        borderSide: const BorderSide(color: HomeColors.borderSubtle),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(HomeRadius.md),
        borderSide: BorderSide(
          color: _accountType == 'buyer' ? const Color(0xFF0F5132) : HomeColors.primaryGreen,
          width: 1.5,
        ),
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
