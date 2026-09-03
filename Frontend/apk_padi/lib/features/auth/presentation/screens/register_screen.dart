import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:padi/core/providers/app_providers.dart';

class RegisterScreen extends ConsumerStatefulWidget {
  const RegisterScreen({super.key, this.initialRole});

  final String? initialRole;

  @override
  ConsumerState<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends ConsumerState<RegisterScreen> {
  final _scrollController = ScrollController();

  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  final _phoneController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmationController = TextEditingController();

  final _nameFocus = FocusNode();
  final _emailFocus = FocusNode();
  final _phoneFocus = FocusNode();
  final _passwordFocus = FocusNode();
  final _confirmationFocus = FocusNode();

  late String _accountType;
  bool _obscurePassword = true;
  bool _obscureConfirmation = true;

  @override
  void initState() {
    super.initState();
    _accountType = widget.initialRole == 'buyer' ? 'buyer' : 'farmer';

    // Auto-scroll saat input di bagian bawah layar menerima fokus
    _phoneFocus.addListener(_handleFocusChange);
    _passwordFocus.addListener(_handleFocusChange);
    _confirmationFocus.addListener(_handleFocusChange);
  }

  void _handleFocusChange() {
    if (_phoneFocus.hasFocus || _passwordFocus.hasFocus || _confirmationFocus.hasFocus) {
      Future.delayed(const Duration(milliseconds: 250), () {
        if (_scrollController.hasClients) {
          _scrollController.animateTo(
            _scrollController.position.maxScrollExtent,
            duration: const Duration(milliseconds: 300),
            curve: Curves.easeOutCubic,
          );
        }
      });
    }
  }

  @override
  void dispose() {
    _phoneFocus.removeListener(_handleFocusChange);
    _passwordFocus.removeListener(_handleFocusChange);
    _confirmationFocus.removeListener(_handleFocusChange);

    _nameController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    _passwordController.dispose();
    _confirmationController.dispose();

    _nameFocus.dispose();
    _emailFocus.dispose();
    _phoneFocus.dispose();
    _passwordFocus.dispose();
    _confirmationFocus.dispose();

    _scrollController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    // Tutup keyboard saat submit
    FocusScope.of(context).unfocus();

    final name = _nameController.text.trim();
    final email = _emailController.text.trim();
    final phone = _phoneController.text.trim();
    final password = _passwordController.text;
    final confirmation = _confirmationController.text;

    if (name.isEmpty || email.isEmpty || phone.isEmpty || password.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Silakan lengkapi semua data pendaftaran.'),
          backgroundColor: Color(0xFFDC2626),
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }

    if (password.length < 8) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Kata sandi minimal terdiri dari 8 karakter.'),
          backgroundColor: Color(0xFFDC2626),
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }

    if (password != confirmation) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Konfirmasi kata sandi tidak cocok.'),
          backgroundColor: Color(0xFFDC2626),
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }

    await ref.read(authControllerProvider).register(
          name: name,
          email: email,
          phone: phone,
          accountType: _accountType,
          password: password,
          passwordConfirmation: confirmation,
        );
  }

  @override
  Widget build(BuildContext context) {
    final auth = ref.watch(authControllerProvider);
    final state = auth.state;
    final isBuyer = _accountType == 'buyer';
    final viewInsets = MediaQuery.of(context).viewInsets;
    final isKeyboardVisible = viewInsets.bottom > 0;

    return GestureDetector(
      // Tutup keyboard saat area kosong di luar textfield disentuh
      onTap: () => FocusScope.of(context).unfocus(),
      behavior: HitTestBehavior.opaque,
      child: Scaffold(
        resizeToAvoidBottomInset: true,
        backgroundColor: const Color(0xFFF9FAFB),
        appBar: AppBar(
          backgroundColor: const Color(0xFFF9FAFB),
          elevation: 0,
          scrolledUnderElevation: 0,
          leading: IconButton(
            onPressed: () {
              FocusScope.of(context).unfocus();
              if (Navigator.of(context).canPop()) {
                Navigator.of(context).pop();
              } else {
                context.go('/select-role');
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
          child: Center(
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 460),
              child: SingleChildScrollView(
                controller: _scrollController,
                keyboardDismissBehavior: ScrollViewKeyboardDismissBehavior.onDrag,
                physics: const AlwaysScrollableScrollPhysics(
                  parent: BouncingScrollPhysics(),
                ),
                padding: EdgeInsets.fromLTRB(
                  24,
                  8,
                  24,
                  isKeyboardVisible ? 120 : 32,
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    // Title & Subtitle
                    const Text(
                      'Daftar Akun Baru',
                      style: TextStyle(
                        fontSize: 26,
                        fontWeight: FontWeight.w800,
                        color: Color(0xFF0F172A),
                        letterSpacing: -0.5,
                      ),
                    ),
                    const SizedBox(height: 6),
                    const Text(
                      'Lengkapi profil Anda untuk mulai menggunakan layanan P.A.D.I.',
                      style: TextStyle(
                        fontSize: 13.5,
                        color: Color(0xFF64748B),
                        height: 1.4,
                      ),
                    ),

                    const SizedBox(height: 20),

                    // Selected Role Summary Banner with Illustration
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(color: const Color(0xFFE2E8F0)),
                        boxShadow: const [
                          BoxShadow(
                            color: Color(0x04000000),
                            blurRadius: 10,
                            offset: Offset(0, 2),
                          ),
                        ],
                      ),
                      child: Row(
                        children: [
                          // Role Avatar Illustration
                          Container(
                            width: 48,
                            height: 48,
                            decoration: BoxDecoration(
                              color: isBuyer ? const Color(0xFFECFDF5) : const Color(0xFFF0FDF4),
                              borderRadius: BorderRadius.circular(12),
                            ),
                            padding: const EdgeInsets.all(4),
                            child: Image.asset(
                              isBuyer ? 'assets/images/role_buyer.png' : 'assets/images/role_farmer.png',
                              fit: BoxFit.contain,
                              errorBuilder: (_, __, ___) => Icon(
                                isBuyer ? Icons.storefront_rounded : Icons.agriculture_rounded,
                                color: const Color(0xFF059669),
                                size: 24,
                              ),
                            ),
                          ),
                          const SizedBox(width: 12),

                          // Role Text Details
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  isBuyer ? 'Akun Pembeli & Industri' : 'Akun Petani Mitra',
                                  style: const TextStyle(
                                    fontSize: 14,
                                    fontWeight: FontWeight.w800,
                                    color: Color(0xFF0F172A),
                                  ),
                                ),
                                const SizedBox(height: 2),
                                Text(
                                  isBuyer ? 'Beli beras & pasokan panen langsung' : 'Kelola sawah & deteksi penyakit AI',
                                  style: const TextStyle(
                                    fontSize: 12,
                                    color: Color(0xFF64748B),
                                  ),
                                ),
                              ],
                            ),
                          ),

                          // Change Role Button
                          TextButton(
                            onPressed: state.isSubmitting
                                ? null
                                : () {
                                    FocusScope.of(context).unfocus();
                                    if (Navigator.of(context).canPop()) {
                                      Navigator.of(context).pop();
                                    } else {
                                      context.go('/select-role');
                                    }
                                  },
                            style: TextButton.styleFrom(
                              foregroundColor: const Color(0xFF059669),
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                              textStyle: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w700),
                            ),
                            child: const Text('Ganti'),
                          ),
                        ],
                      ),
                    ),

                    const SizedBox(height: 20),

                    // Error Banner
                    if (state.message != null && state.message!.isNotEmpty) ...[
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                        margin: const EdgeInsets.only(bottom: 20),
                        decoration: BoxDecoration(
                          color: const Color(0xFFFEF2F2),
                          borderRadius: BorderRadius.circular(14),
                          border: Border.all(color: const Color(0xFFFECACA)),
                        ),
                        child: Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Icon(
                              Icons.error_outline_rounded,
                              color: Color(0xFFDC2626),
                              size: 18,
                            ),
                            const SizedBox(width: 10),
                            Expanded(
                              child: Text(
                                state.message!,
                                style: const TextStyle(
                                  color: Color(0xFFDC2626),
                                  fontSize: 13,
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],

                    // Form Container Card
                    Container(
                      padding: const EdgeInsets.all(22),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(22),
                        border: Border.all(color: const Color(0xFFE2E8F0)),
                        boxShadow: const [
                          BoxShadow(
                            color: Color(0x04000000),
                            blurRadius: 16,
                            offset: Offset(0, 4),
                          ),
                        ],
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          // Name Field
                          _buildFieldLabel('Nama Lengkap'),
                          const SizedBox(height: 6),
                          TextField(
                            controller: _nameController,
                            focusNode: _nameFocus,
                            scrollPadding: const EdgeInsets.only(bottom: 120),
                            textCapitalization: TextCapitalization.words,
                            textInputAction: TextInputAction.next,
                            onSubmitted: (_) => FocusScope.of(context).requestFocus(_emailFocus),
                            style: const TextStyle(fontSize: 14, color: Color(0xFF0F172A), fontWeight: FontWeight.w500),
                            decoration: _inputDecoration(
                              hintText: 'Nama lengkap Anda',
                              prefixIcon: Icons.person_outline_rounded,
                              errorText: state.fieldErrors['name']?.first,
                            ),
                          ),

                          const SizedBox(height: 16),

                          // Email Field
                          _buildFieldLabel('Alamat Email'),
                          const SizedBox(height: 6),
                          TextField(
                            controller: _emailController,
                            focusNode: _emailFocus,
                            scrollPadding: const EdgeInsets.only(bottom: 120),
                            keyboardType: TextInputType.emailAddress,
                            textInputAction: TextInputAction.next,
                            onSubmitted: (_) => FocusScope.of(context).requestFocus(_phoneFocus),
                            style: const TextStyle(fontSize: 14, color: Color(0xFF0F172A), fontWeight: FontWeight.w500),
                            decoration: _inputDecoration(
                              hintText: 'nama@email.com',
                              prefixIcon: Icons.mail_outline_rounded,
                              errorText: state.fieldErrors['email']?.first,
                            ),
                          ),

                          const SizedBox(height: 16),

                          // Phone Field
                          _buildFieldLabel('Nomor WhatsApp / HP'),
                          const SizedBox(height: 6),
                          TextField(
                            controller: _phoneController,
                            focusNode: _phoneFocus,
                            scrollPadding: const EdgeInsets.only(bottom: 120),
                            keyboardType: TextInputType.phone,
                            textInputAction: TextInputAction.next,
                            onSubmitted: (_) => FocusScope.of(context).requestFocus(_passwordFocus),
                            style: const TextStyle(fontSize: 14, color: Color(0xFF0F172A), fontWeight: FontWeight.w500),
                            decoration: _inputDecoration(
                              hintText: '081234567890',
                              prefixIcon: Icons.phone_outlined,
                              errorText: state.fieldErrors['phone']?.first,
                            ),
                          ),

                          const SizedBox(height: 16),

                          // Password Field
                          _buildFieldLabel('Kata Sandi'),
                          const SizedBox(height: 6),
                          TextField(
                            controller: _passwordController,
                            focusNode: _passwordFocus,
                            scrollPadding: const EdgeInsets.only(bottom: 140),
                            obscureText: _obscurePassword,
                            textInputAction: TextInputAction.next,
                            onSubmitted: (_) => FocusScope.of(context).requestFocus(_confirmationFocus),
                            style: const TextStyle(fontSize: 14, color: Color(0xFF0F172A), fontWeight: FontWeight.w500),
                            decoration: _inputDecoration(
                              hintText: 'Minimal 8 karakter',
                              prefixIcon: Icons.lock_outline_rounded,
                              errorText: state.fieldErrors['password']?.first,
                              suffixIcon: IconButton(
                                onPressed: () => setState(() => _obscurePassword = !_obscurePassword),
                                tooltip: _obscurePassword ? 'Tampilkan sandi' : 'Sembunyikan sandi',
                                icon: Icon(
                                  _obscurePassword ? Icons.visibility_outlined : Icons.visibility_off_outlined,
                                  color: const Color(0xFF94A3B8),
                                  size: 20,
                                ),
                              ),
                            ),
                          ),

                          const SizedBox(height: 16),

                          // Confirm Password Field
                          _buildFieldLabel('Konfirmasi Kata Sandi'),
                          const SizedBox(height: 6),
                          TextField(
                            controller: _confirmationController,
                            focusNode: _confirmationFocus,
                            scrollPadding: const EdgeInsets.only(bottom: 180),
                            obscureText: _obscureConfirmation,
                            textInputAction: TextInputAction.done,
                            onSubmitted: (_) => _submit(),
                            style: const TextStyle(fontSize: 14, color: Color(0xFF0F172A), fontWeight: FontWeight.w500),
                            decoration: _inputDecoration(
                              hintText: 'Ulangi kata sandi',
                              prefixIcon: Icons.lock_reset_rounded,
                              suffixIcon: IconButton(
                                onPressed: () => setState(() => _obscureConfirmation = !_obscureConfirmation),
                                tooltip: _obscureConfirmation ? 'Tampilkan sandi' : 'Sembunyikan sandi',
                                icon: Icon(
                                  _obscureConfirmation ? Icons.visibility_outlined : Icons.visibility_off_outlined,
                                  color: const Color(0xFF94A3B8),
                                  size: 20,
                                ),
                              ),
                            ),
                          ),

                          const SizedBox(height: 24),

                          // Submit Button
                          SizedBox(
                            height: 52,
                            child: FilledButton(
                              onPressed: state.isSubmitting ? null : _submit,
                              style: FilledButton.styleFrom(
                                backgroundColor: const Color(0xFF059669),
                                foregroundColor: Colors.white,
                                elevation: 0,
                                shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(14),
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
                                  : Row(
                                      mainAxisAlignment: MainAxisAlignment.center,
                                      children: [
                                        Text(
                                          'Daftar sebagai ${isBuyer ? 'Pembeli' : 'Petani'}',
                                          style: const TextStyle(
                                            fontSize: 15,
                                            fontWeight: FontWeight.w700,
                                            letterSpacing: -0.2,
                                          ),
                                        ),
                                        const SizedBox(width: 8),
                                        const Icon(Icons.arrow_forward_rounded, size: 18),
                                      ],
                                    ),
                            ),
                          ),
                        ],
                      ),
                    ),

                    const SizedBox(height: 24),

                    // Login Link
                    Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Text(
                          'Sudah memiliki akun? ',
                          style: TextStyle(
                            color: Color(0xFF64748B),
                            fontSize: 13.5,
                          ),
                        ),
                        GestureDetector(
                          onTap: () {
                            FocusScope.of(context).unfocus();
                            if (Navigator.of(context).canPop()) {
                              Navigator.of(context).pop();
                            } else {
                              context.go('/login');
                            }
                          },
                          child: const Text(
                            'Masuk',
                            style: TextStyle(
                              color: Color(0xFF059669),
                              fontWeight: FontWeight.w800,
                              fontSize: 13.5,
                            ),
                          ),
                        ),
                      ],
                    ),

                    const SizedBox(height: 20),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildFieldLabel(String label) {
    return Text(
      label,
      style: const TextStyle(
        fontSize: 13,
        fontWeight: FontWeight.w700,
        color: Color(0xFF334155),
      ),
    );
  }

  InputDecoration _inputDecoration({
    required String hintText,
    required IconData prefixIcon,
    String? errorText,
    Widget? suffixIcon,
  }) {
    return InputDecoration(
      hintText: hintText,
      hintStyle: const TextStyle(fontSize: 13.5, color: Color(0xFF94A3B8)),
      prefixIcon: Icon(prefixIcon, color: const Color(0xFF64748B), size: 20),
      suffixIcon: suffixIcon,
      errorText: errorText,
      filled: true,
      fillColor: const Color(0xFFF8FAFC),
      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: Color(0xFF059669), width: 1.6),
      ),
      errorBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: Color(0xFFDC2626)),
      ),
      focusedErrorBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(12),
        borderSide: const BorderSide(color: Color(0xFFDC2626), width: 1.6),
      ),
    );
  }
}
