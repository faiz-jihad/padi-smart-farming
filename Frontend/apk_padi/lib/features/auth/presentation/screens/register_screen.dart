import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:padi/core/providers/app_providers.dart';
import 'package:padi/features/auth/data/services/face_capture_models.dart';
import 'package:padi/features/auth/data/services/face_capture_service.dart';
import 'package:padi/features/auth/presentation/controllers/auth_controller.dart';

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
  final _pinController = TextEditingController();
  final _pinConfirmationController = TextEditingController();
  final _faceService = createFaceCaptureService();

  final _nameFocus = FocusNode();
  final _emailFocus = FocusNode();
  final _phoneFocus = FocusNode();
  final _passwordFocus = FocusNode();
  final _confirmationFocus = FocusNode();
  final _pinFocus = FocusNode();
  final _pinConfirmationFocus = FocusNode();

  late String _accountType;
  bool _obscurePassword = true;
  bool _obscureConfirmation = true;
  bool _obscurePin = true;
  bool _obscurePinConfirmation = true;
  bool _showPasswordSetup = false;
  bool _isCapturingFace = false;
  List<List<double>>? _faceDescriptors;
  String? _faceLabel;

  @override
  void initState() {
    super.initState();
    _accountType = widget.initialRole == 'buyer' ? 'buyer' : 'farmer';

    // Auto-scroll saat input di bagian bawah layar menerima fokus
    _phoneFocus.addListener(_handleFocusChange);
    _passwordFocus.addListener(_handleFocusChange);
    _confirmationFocus.addListener(_handleFocusChange);
    _pinFocus.addListener(_handleFocusChange);
    _pinConfirmationFocus.addListener(_handleFocusChange);
  }

  void _handleFocusChange() {
    if (_phoneFocus.hasFocus ||
        _passwordFocus.hasFocus ||
        _confirmationFocus.hasFocus ||
        _pinFocus.hasFocus ||
        _pinConfirmationFocus.hasFocus) {
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
    _pinFocus.removeListener(_handleFocusChange);
    _pinConfirmationFocus.removeListener(_handleFocusChange);

    _nameController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    _passwordController.dispose();
    _confirmationController.dispose();
    _pinController.dispose();
    _pinConfirmationController.dispose();

    _nameFocus.dispose();
    _emailFocus.dispose();
    _phoneFocus.dispose();
    _passwordFocus.dispose();
    _confirmationFocus.dispose();
    _pinFocus.dispose();
    _pinConfirmationFocus.dispose();

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
    final pin = _pinController.text.trim();
    final pinConfirmation = _pinConfirmationController.text.trim();
    final isFarmer = _accountType == 'farmer';

    if (name.isEmpty || phone.isEmpty || (!isFarmer && email.isEmpty)) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Isi nama dan nomor WhatsApp dulu.'),
          backgroundColor: Color(0xFFDC2626),
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }

    if (isFarmer && _faceDescriptors == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Rekam wajah dulu agar nanti mudah masuk.'),
          backgroundColor: Color(0xFFDC2626),
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }

    if (isFarmer && pin.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Buat PIN 4 sampai 6 angka.'),
          backgroundColor: Color(0xFFDC2626),
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }

    if (password.isNotEmpty && password.length < 8) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Kata sandi minimal terdiri dari 8 karakter.'),
          backgroundColor: Color(0xFFDC2626),
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }

    if (password.isNotEmpty && password != confirmation) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Konfirmasi kata sandi tidak cocok.'),
          backgroundColor: Color(0xFFDC2626),
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }

    if (pin.isNotEmpty ||
        pinConfirmation.isNotEmpty ||
        _faceDescriptors != null) {
      if (pin.length < 4 || pin.length > 6 || int.tryParse(pin) == null) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('PIN harus 4 sampai 6 angka.'),
            backgroundColor: Color(0xFFDC2626),
            behavior: SnackBarBehavior.floating,
          ),
        );
        return;
      }

      if (pin != pinConfirmation) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Konfirmasi PIN tidak cocok.'),
            backgroundColor: Color(0xFFDC2626),
            behavior: SnackBarBehavior.floating,
          ),
        );
        return;
      }

      if (_faceDescriptors == null) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Rekam wajah dulu agar nanti bisa masuk mudah.'),
            backgroundColor: Color(0xFFDC2626),
            behavior: SnackBarBehavior.floating,
          ),
        );
        return;
      }
    }

    await ref
        .read(authControllerProvider)
        .register(
          name: name,
          email: email.isEmpty ? _fallbackEmail(phone) : email,
          phone: phone,
          accountType: _accountType,
          password: password.isEmpty ? _fallbackPassword(phone) : password,
          passwordConfirmation: password.isEmpty
              ? _fallbackPassword(phone)
              : confirmation,
          pin: pin.isEmpty ? null : pin,
          pinConfirmation: pinConfirmation.isEmpty ? null : pinConfirmation,
          faceDescriptors: _faceDescriptors,
        );
  }

  String _fallbackEmail(String phone) {
    final digits = phone.replaceAll(RegExp(r'[^0-9]'), '');
    return 'petani${digits.isEmpty ? DateTime.now().millisecondsSinceEpoch : digits}@padi.local';
  }

  String _fallbackPassword(String phone) {
    final digits = phone.replaceAll(RegExp(r'[^0-9]'), '');
    final tail = digits.length >= 4
        ? digits.substring(digits.length - 4)
        : '2026';
    return 'PadiPetani$tail!';
  }

  Future<void> _captureFace() async {
    FocusScope.of(context).unfocus();

    if (_isCapturingFace) {
      return;
    }

    setState(() {
      _isCapturingFace = true;
    });

    try {
      final result = await _faceService.captureEnrollment();
      setState(() {
        _faceDescriptors = result.descriptors;
        _faceLabel = result.sourceLabel;
      });
    } on FaceCaptureException catch (error) {
      _showError(error.message);
    } catch (_) {
      _showError('Kamera belum bisa dibuka. Coba lagi sebentar.');
    } finally {
      if (mounted) {
        setState(() {
          _isCapturingFace = false;
        });
      }
    }
  }

  void _showError(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: const Color(0xFFDC2626),
        behavior: SnackBarBehavior.floating,
      ),
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
        backgroundColor: const Color(0xFFF6FFF9),
        appBar: AppBar(
          backgroundColor: const Color(0xFFF6FFF9),
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
                keyboardDismissBehavior:
                    ScrollViewKeyboardDismissBehavior.onDrag,
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
                    Text(
                      isBuyer ? 'Daftar Pembeli' : 'Daftar Petani',
                      style: TextStyle(
                        fontSize: 30,
                        fontWeight: FontWeight.w900,
                        color: Color(0xFF0F172A),
                      ),
                    ),
                    const SizedBox(height: 6),
                    Text(
                      isBuyer
                          ? 'Lengkapi data akun untuk membeli hasil panen.'
                          : 'Cukup isi nama, nomor HP, wajah, dan PIN.',
                      style: TextStyle(
                        fontSize: 17,
                        color: Color(0xFF3F6F63),
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
                              color: isBuyer
                                  ? const Color(0xFFECFDF5)
                                  : const Color(0xFFF0FDF4),
                              borderRadius: BorderRadius.circular(12),
                            ),
                            padding: const EdgeInsets.all(4),
                            child: Image.asset(
                              isBuyer
                                  ? 'assets/images/role_buyer.png'
                                  : 'assets/images/role_farmer.png',
                              fit: BoxFit.contain,
                              errorBuilder: (_, _, _) => Icon(
                                isBuyer
                                    ? Icons.storefront_rounded
                                    : Icons.agriculture_rounded,
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
                                  isBuyer ? 'Akun Pembeli' : 'Akun Petani',
                                  style: const TextStyle(
                                    fontSize: 14,
                                    fontWeight: FontWeight.w800,
                                    color: Color(0xFF0F172A),
                                  ),
                                ),
                                const SizedBox(height: 2),
                                Text(
                                  isBuyer
                                      ? 'Beli panen langsung dari petani'
                                      : 'Nanti masuk cukup lihat kamera dan isi PIN',
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
                              padding: const EdgeInsets.symmetric(
                                horizontal: 10,
                                vertical: 6,
                              ),
                              textStyle: const TextStyle(
                                fontSize: 12.5,
                                fontWeight: FontWeight.w700,
                              ),
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
                        padding: const EdgeInsets.symmetric(
                          horizontal: 14,
                          vertical: 12,
                        ),
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
                          _stepLabel(number: '1', label: 'Nama Petani'),
                          const SizedBox(height: 6),
                          TextField(
                            controller: _nameController,
                            focusNode: _nameFocus,
                            scrollPadding: const EdgeInsets.only(bottom: 120),
                            textCapitalization: TextCapitalization.words,
                            textInputAction: TextInputAction.next,
                            onSubmitted: (_) => FocusScope.of(
                              context,
                            ).requestFocus(_emailFocus),
                            style: const TextStyle(
                              fontSize: 17,
                              color: Color(0xFF0F172A),
                              fontWeight: FontWeight.w500,
                            ),
                            decoration: _inputDecoration(
                              hintText: 'Nama lengkap Anda',
                              prefixIcon: Icons.person_outline_rounded,
                              errorText: state.fieldErrors['name']?.first,
                            ),
                          ),

                          const SizedBox(height: 16),

                          // Email Field
                          _buildFieldLabel(
                            isBuyer ? 'Email' : 'Email, boleh dikosongkan',
                          ),
                          const SizedBox(height: 6),
                          TextField(
                            controller: _emailController,
                            focusNode: _emailFocus,
                            scrollPadding: const EdgeInsets.only(bottom: 120),
                            keyboardType: TextInputType.emailAddress,
                            textInputAction: TextInputAction.next,
                            onSubmitted: (_) => FocusScope.of(
                              context,
                            ).requestFocus(_phoneFocus),
                            style: const TextStyle(
                              fontSize: 17,
                              color: Color(0xFF0F172A),
                              fontWeight: FontWeight.w500,
                            ),
                            decoration: _inputDecoration(
                              hintText: 'nama@email.com',
                              prefixIcon: Icons.mail_outline_rounded,
                              errorText: state.fieldErrors['email']?.first,
                            ),
                          ),

                          const SizedBox(height: 16),

                          // Phone Field
                          _stepLabel(number: '2', label: 'Nomor WhatsApp'),
                          const SizedBox(height: 6),
                          TextField(
                            controller: _phoneController,
                            focusNode: _phoneFocus,
                            scrollPadding: const EdgeInsets.only(bottom: 120),
                            keyboardType: TextInputType.phone,
                            textInputAction: TextInputAction.next,
                            onSubmitted: (_) =>
                                FocusScope.of(context).requestFocus(
                                  isBuyer ? _passwordFocus : _pinFocus,
                                ),
                            style: const TextStyle(
                              fontSize: 17,
                              color: Color(0xFF0F172A),
                              fontWeight: FontWeight.w500,
                            ),
                            decoration: _inputDecoration(
                              hintText: '081234567890',
                              prefixIcon: Icons.phone_outlined,
                              errorText: state.fieldErrors['phone']?.first,
                            ),
                          ),

                          const SizedBox(height: 16),

                          if (isBuyer) ...[
                            _passwordFields(state),
                          ] else ...[
                            _passwordSetupToggle(),
                            if (_showPasswordSetup) ...[
                              const SizedBox(height: 12),
                              _passwordFields(state),
                            ],
                          ],

                          const SizedBox(height: 18),

                          _facePinCard(state.isSubmitting),

                          const SizedBox(height: 24),

                          // Submit Button
                          SizedBox(
                            height: 60,
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
                                      mainAxisAlignment:
                                          MainAxisAlignment.center,
                                      children: [
                                        Text(
                                          isBuyer
                                              ? 'Daftar sebagai Pembeli'
                                              : 'Selesai Daftar',
                                          style: const TextStyle(
                                            fontSize: 18,
                                            fontWeight: FontWeight.w900,
                                          ),
                                        ),
                                        const SizedBox(width: 8),
                                        const Icon(
                                          Icons.arrow_forward_rounded,
                                          size: 18,
                                        ),
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

  Widget _passwordSetupToggle() {
    return OutlinedButton.icon(
      onPressed: () {
        setState(() {
          _showPasswordSetup = !_showPasswordSetup;
        });
      },
      icon: Icon(
        _showPasswordSetup
            ? Icons.expand_less_rounded
            : Icons.lock_outline_rounded,
      ),
      label: Text(
        _showPasswordSetup ? 'Sembunyikan kata sandi' : 'Buat kata sandi juga',
      ),
      style: OutlinedButton.styleFrom(
        foregroundColor: const Color(0xFF047857),
        side: const BorderSide(color: Color(0xFFBBF7D0)),
        minimumSize: const Size.fromHeight(56),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        textStyle: const TextStyle(fontWeight: FontWeight.w800),
      ),
    );
  }

  Widget _passwordFields(AuthState state) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        _buildFieldLabel('Kata Sandi'),
        const SizedBox(height: 6),
        TextField(
          controller: _passwordController,
          focusNode: _passwordFocus,
          scrollPadding: const EdgeInsets.only(bottom: 140),
          obscureText: _obscurePassword,
          textInputAction: TextInputAction.next,
          onSubmitted: (_) =>
              FocusScope.of(context).requestFocus(_confirmationFocus),
          style: const TextStyle(
            fontSize: 17,
            color: Color(0xFF0F172A),
            fontWeight: FontWeight.w500,
          ),
          decoration: _inputDecoration(
            hintText: 'Minimal 8 karakter',
            prefixIcon: Icons.lock_outline_rounded,
            errorText: state.fieldErrors['password']?.first,
            suffixIcon: IconButton(
              onPressed: () =>
                  setState(() => _obscurePassword = !_obscurePassword),
              tooltip: _obscurePassword
                  ? 'Tampilkan sandi'
                  : 'Sembunyikan sandi',
              icon: Icon(
                _obscurePassword
                    ? Icons.visibility_outlined
                    : Icons.visibility_off_outlined,
                color: const Color(0xFF94A3B8),
                size: 20,
              ),
            ),
          ),
        ),
        const SizedBox(height: 16),
        _buildFieldLabel('Ulangi Kata Sandi'),
        const SizedBox(height: 6),
        TextField(
          controller: _confirmationController,
          focusNode: _confirmationFocus,
          scrollPadding: const EdgeInsets.only(bottom: 180),
          obscureText: _obscureConfirmation,
          textInputAction: TextInputAction.done,
          onSubmitted: (_) => _submit(),
          style: const TextStyle(
            fontSize: 17,
            color: Color(0xFF0F172A),
            fontWeight: FontWeight.w500,
          ),
          decoration: _inputDecoration(
            hintText: 'Ulangi kata sandi',
            prefixIcon: Icons.lock_reset_rounded,
            suffixIcon: IconButton(
              onPressed: () =>
                  setState(() => _obscureConfirmation = !_obscureConfirmation),
              tooltip: _obscureConfirmation
                  ? 'Tampilkan sandi'
                  : 'Sembunyikan sandi',
              icon: Icon(
                _obscureConfirmation
                    ? Icons.visibility_outlined
                    : Icons.visibility_off_outlined,
                color: const Color(0xFF94A3B8),
                size: 20,
              ),
            ),
          ),
        ),
      ],
    );
  }

  Widget _facePinCard(bool isSubmitting) {
    final hasFace = _faceDescriptors != null;
    final isBusy = isSubmitting || _isCapturingFace;
    final poseCount = _faceDescriptors?.length ?? 0;

    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(22),
        border: Border.all(
          color: hasFace ? const Color(0xFF16A34A) : const Color(0xFFBBF7D0),
          width: hasFace ? 2 : 1.3,
        ),
        boxShadow: const [
          BoxShadow(
            color: Color(0x08059669),
            blurRadius: 18,
            offset: Offset(0, 6),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Row(
            children: [
              Container(
                width: 46,
                height: 46,
                decoration: BoxDecoration(
                  color: const Color(0xFFF0FDF4),
                  shape: BoxShape.circle,
                  border: Border.all(color: const Color(0xFFBBF7D0)),
                ),
                child: Icon(
                  hasFace
                      ? Icons.verified_user_rounded
                      : Icons.face_retouching_natural_rounded,
                  color: const Color(0xFF047857),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      hasFace ? 'Wajah Sudah Direkam' : '3. Rekam Wajah',
                      style: const TextStyle(
                        color: Color(0xFF052E25),
                        fontSize: 20,
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      hasFace
                          ? 'Tersimpan di HP ini. Nanti dipakai saat masuk.'
                          : 'Ikuti arahan di kamera pelan-pelan.',
                      style: const TextStyle(
                        color: Color(0xFF3F6F63),
                        fontSize: 15,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: const Color(0xFFF0FDF4),
              borderRadius: BorderRadius.circular(18),
              border: Border.all(color: const Color(0xFFBBF7D0)),
            ),
            child: Column(
              children: [
                Container(
                  width: 116,
                  height: 116,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    color: Colors.white,
                    border: Border.all(
                      color: hasFace
                          ? const Color(0xFF16A34A)
                          : const Color(0xFF86EFAC),
                      width: 4,
                    ),
                  ),
                  child: Icon(
                    hasFace
                        ? Icons.check_rounded
                        : Icons.face_retouching_natural_rounded,
                    color: const Color(0xFF059669),
                    size: 62,
                  ),
                ),
                const SizedBox(height: 14),
                Text(
                  hasFace
                      ? '$poseCount foto wajah sudah aman'
                      : 'Nanti diminta lihat depan, kanan, kiri, lalu depan lagi.',
                  textAlign: TextAlign.center,
                  style: const TextStyle(
                    color: Color(0xFF052E25),
                    fontSize: 17,
                    fontWeight: FontWeight.w900,
                    height: 1.35,
                  ),
                ),
                const SizedBox(height: 14),
                Row(
                  children: [
                    _faceStepChip('1', 'Depan', hasFace || poseCount >= 1),
                    const SizedBox(width: 6),
                    _faceStepChip('2', 'Kanan', hasFace || poseCount >= 2),
                    const SizedBox(width: 6),
                    _faceStepChip('3', 'Kiri', hasFace || poseCount >= 3),
                    const SizedBox(width: 6),
                    _faceStepChip('4', 'Cek', hasFace || poseCount >= 4),
                  ],
                ),
              ],
            ),
          ),
          const SizedBox(height: 14),
          FilledButton.icon(
            onPressed: isBusy ? null : _captureFace,
            icon: _isCapturingFace
                ? const SizedBox.square(
                    dimension: 20,
                    child: CircularProgressIndicator(
                      strokeWidth: 2.2,
                      color: Colors.white,
                    ),
                  )
                : Icon(
                    hasFace ? Icons.refresh_rounded : Icons.camera_alt_rounded,
                  ),
            label: Text(
              _isCapturingFace
                  ? 'Ikuti arahan di kamera...'
                  : hasFace
                  ? 'Ulangi Rekam Wajah'
                  : 'Mulai Rekam Wajah',
            ),
            style: FilledButton.styleFrom(
              backgroundColor: const Color(0xFF059669),
              foregroundColor: Colors.white,
              minimumSize: const Size.fromHeight(64),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(14),
              ),
              textStyle: const TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.w900,
              ),
            ),
          ),
          if (hasFace && _faceLabel != null) ...[
            const SizedBox(height: 10),
            Text(
              _faceLabel!,
              textAlign: TextAlign.center,
              style: const TextStyle(
                color: Color(0xFF047857),
                fontSize: 15,
                fontWeight: FontWeight.w800,
              ),
            ),
          ],
          const SizedBox(height: 18),
          const Text(
            '4. Buat PIN Masuk',
            style: TextStyle(
              color: Color(0xFF052E25),
              fontSize: 18,
              fontWeight: FontWeight.w900,
            ),
          ),
          const SizedBox(height: 4),
          const Text(
            'PIN dipakai kalau wajah sulit terbaca. Gunakan 4 sampai 6 angka.',
            style: TextStyle(
              color: Color(0xFF3F6F63),
              fontSize: 15,
              fontWeight: FontWeight.w700,
              height: 1.35,
            ),
          ),
          const SizedBox(height: 10),
          TextField(
            controller: _pinController,
            focusNode: _pinFocus,
            keyboardType: TextInputType.number,
            obscureText: _obscurePin,
            maxLength: 6,
            textInputAction: TextInputAction.next,
            onSubmitted: (_) =>
                FocusScope.of(context).requestFocus(_pinConfirmationFocus),
            style: const TextStyle(fontSize: 19, fontWeight: FontWeight.w800),
            decoration: _inputDecoration(
              hintText: 'Buat PIN',
              prefixIcon: Icons.pin_rounded,
              errorText: stateFieldError('pin'),
              counterText: '',
              suffixIcon: IconButton(
                onPressed: () => setState(() => _obscurePin = !_obscurePin),
                tooltip: _obscurePin ? 'Tampilkan PIN' : 'Sembunyikan PIN',
                icon: Icon(
                  _obscurePin
                      ? Icons.visibility_outlined
                      : Icons.visibility_off_outlined,
                  color: const Color(0xFF94A3B8),
                  size: 22,
                ),
              ),
            ),
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _pinConfirmationController,
            focusNode: _pinConfirmationFocus,
            keyboardType: TextInputType.number,
            obscureText: _obscurePinConfirmation,
            maxLength: 6,
            textInputAction: TextInputAction.done,
            onSubmitted: (_) => _submit(),
            style: const TextStyle(fontSize: 19, fontWeight: FontWeight.w800),
            decoration: _inputDecoration(
              hintText: 'Ulangi PIN',
              prefixIcon: Icons.lock_reset_rounded,
              counterText: '',
              suffixIcon: IconButton(
                onPressed: () => setState(
                  () => _obscurePinConfirmation = !_obscurePinConfirmation,
                ),
                tooltip: _obscurePinConfirmation
                    ? 'Tampilkan PIN'
                    : 'Sembunyikan PIN',
                icon: Icon(
                  _obscurePinConfirmation
                      ? Icons.visibility_outlined
                      : Icons.visibility_off_outlined,
                  color: const Color(0xFF94A3B8),
                  size: 22,
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _faceStepChip(String number, String label, bool complete) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 9),
        decoration: BoxDecoration(
          color: complete ? const Color(0xFF059669) : Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: complete ? const Color(0xFF059669) : const Color(0xFFBBF7D0),
            width: 1.5,
          ),
        ),
        child: Column(
          children: [
            Icon(
              complete
                  ? Icons.check_circle_rounded
                  : Icons.radio_button_unchecked_rounded,
              color: complete ? Colors.white : const Color(0xFF059669),
              size: 20,
            ),
            const SizedBox(height: 4),
            Text(
              '$number. $label',
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: TextStyle(
                color: complete ? Colors.white : const Color(0xFF047857),
                fontSize: 12,
                fontWeight: FontWeight.w900,
              ),
            ),
          ],
        ),
      ),
    );
  }

  String? stateFieldError(String field) {
    final auth = ref.read(authControllerProvider);
    return auth.state.fieldErrors[field]?.first;
  }

  Widget _stepLabel({required String number, required String label}) {
    return Row(
      children: [
        Container(
          width: 30,
          height: 30,
          alignment: Alignment.center,
          decoration: const BoxDecoration(
            color: Color(0xFF059669),
            shape: BoxShape.circle,
          ),
          child: Text(
            number,
            style: const TextStyle(
              color: Colors.white,
              fontSize: 16,
              fontWeight: FontWeight.w900,
            ),
          ),
        ),
        const SizedBox(width: 10),
        Expanded(
          child: Text(
            label,
            style: const TextStyle(
              fontSize: 17,
              fontWeight: FontWeight.w900,
              color: Color(0xFF052E25),
            ),
          ),
        ),
      ],
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
    String? counterText,
    Widget? suffixIcon,
  }) {
    return InputDecoration(
      hintText: hintText,
      counterText: counterText,
      hintStyle: const TextStyle(fontSize: 16, color: Color(0xFF94A3B8)),
      prefixIcon: Icon(prefixIcon, color: const Color(0xFF047857), size: 24),
      suffixIcon: suffixIcon,
      errorText: errorText,
      filled: true,
      fillColor: const Color(0xFFF8FAFC),
      contentPadding: const EdgeInsets.symmetric(horizontal: 18, vertical: 19),
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
