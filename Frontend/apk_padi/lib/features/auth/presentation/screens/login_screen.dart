import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:padi/core/providers/app_providers.dart';
import 'package:padi/features/auth/data/services/face_capture_models.dart';
import 'package:padi/features/auth/data/services/face_capture_service.dart';
import 'package:padi/features/auth/presentation/screens/role_selection_screen.dart';

class LoginScreen extends ConsumerStatefulWidget {
  const LoginScreen({super.key});

  @override
  ConsumerState<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends ConsumerState<LoginScreen> {
  final _phoneController = TextEditingController();
  final _pinController = TextEditingController();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  final _faceService = createFaceCaptureService();

  bool _showPasswordLogin = false;
  bool _showPhonePinHelp = false;
  bool _obscurePassword = true;
  bool _isCapturingFace = false;
  List<double>? _faceDescriptor;
  String? _faceLabel;

  @override
  void dispose() {
    _phoneController.dispose();
    _pinController.dispose();
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
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
      final result = await _faceService.captureDescriptor();
      setState(() {
        _faceDescriptor = result.descriptor;
        _faceLabel = result.sourceLabel;
      });
      await _submitFaceLogin(descriptorOverride: result.descriptor);
    } on FaceCaptureException catch (error) {
      _showSnack(error.message);
    } catch (_) {
      _showSnack('Kamera belum bisa dibuka. Coba lagi sebentar.');
    } finally {
      if (mounted) {
        setState(() {
          _isCapturingFace = false;
        });
      }
    }
  }

  Future<void> _submitFaceLogin({List<double>? descriptorOverride}) async {
    final phone = _phoneController.text.trim();
    final pin = _pinController.text.trim();
    final descriptor = descriptorOverride ?? _faceDescriptor;

    if (descriptor == null) {
      _showSnack('Lihat kamera dulu untuk masuk.');
      return;
    }

    await ref
        .read(authControllerProvider)
        .faceLogin(phone: phone, pin: pin, faceDescriptor: descriptor);
  }

  Future<void> _submitPasswordLogin() async {
    final email = _emailController.text.trim();
    final password = _passwordController.text;

    if (email.isEmpty || password.isEmpty) {
      _showSnack('Isi email dan kata sandi dulu.');
      return;
    }

    await ref
        .read(authControllerProvider)
        .login(email: email, password: password);
  }

  void _showSnack(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(
          message,
          style: const TextStyle(fontWeight: FontWeight.w600),
        ),
        backgroundColor: const Color(0xFF047857),
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final auth = ref.watch(authControllerProvider);
    final state = auth.state;
    final resetSuccess =
        GoRouterState.of(context).uri.queryParameters['reset'] == 'success';

    return GestureDetector(
      onTap: () => FocusScope.of(context).unfocus(),
      behavior: HitTestBehavior.opaque,
      child: Scaffold(
        resizeToAvoidBottomInset: true,
        backgroundColor: const Color(0xFFF8FAFC),
        body: SafeArea(
          child: Center(
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 440),
              child: SingleChildScrollView(
                keyboardDismissBehavior:
                    ScrollViewKeyboardDismissBehavior.onDrag,
                physics: const BouncingScrollPhysics(),
                padding: const EdgeInsets.symmetric(
                  horizontal: 24,
                  vertical: 28,
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    _brandHeader(),
                    const SizedBox(height: 34),
                    const Text(
                      'Masuk',
                      style: TextStyle(
                        fontSize: 28,
                        fontWeight: FontWeight.w900,
                        color: Color(0xFF0F172A),
                        letterSpacing: -0.5,
                      ),
                    ),
                    const SizedBox(height: 18),
                    if (resetSuccess || (state.message?.isNotEmpty ?? false))
                      _statusBanner(
                        resetSuccess
                            ? 'Kata sandi sudah diganti. Silakan masuk lagi.'
                            : state.message!,
                        isError: !resetSuccess && state.isError,
                      ),
                    _faceLoginCard(state.isSubmitting),
                    const SizedBox(height: 16),
                    _passwordToggle(),
                    if (_showPasswordLogin) ...[
                      const SizedBox(height: 14),
                      _passwordLoginCard(state.isSubmitting),
                    ],
                    const SizedBox(height: 28),
                    _registerLink(),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _brandHeader() {
    return Row(
      children: [
        Container(
          width: 44,
          height: 44,
          padding: const EdgeInsets.all(7),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(14),
            border: Border.all(color: const Color(0xFFE2E8F0)),
            boxShadow: const [
              BoxShadow(
                color: Color(0x06000000),
                blurRadius: 8,
                offset: Offset(0, 2),
              ),
            ],
          ),
          child: Image.asset(
            'assets/images/padi-logo.png',
            fit: BoxFit.contain,
            errorBuilder: (_, _, _) =>
                const Icon(Icons.eco_rounded, color: Color(0xFF059669)),
          ),
        ),
        const SizedBox(width: 12),
        const Text(
          'P.A.D.I.',
          style: TextStyle(
            fontSize: 20,
            fontWeight: FontWeight.w900,
            color: Color(0xFF0F172A),
            letterSpacing: 0.5,
          ),
        ),
      ],
    );
  }

  Widget _faceLoginCard(bool isSubmitting) {
    final hasFace = _faceDescriptor != null;
    final isBusy = isSubmitting || _isCapturingFace;

    return Container(
      padding: const EdgeInsets.all(22),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(24),
        border: Border.all(color: const Color(0xFFBBF7D0), width: 1.4),
        boxShadow: const [
          BoxShadow(
            color: Color(0x12059669),
            blurRadius: 28,
            offset: Offset(0, 10),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Container(
            padding: const EdgeInsets.fromLTRB(18, 20, 18, 18),
            decoration: BoxDecoration(
              color: const Color(0xFFEFFDF4),
              borderRadius: BorderRadius.circular(22),
              border: Border.all(color: const Color(0xFFBBF7D0)),
            ),
            child: Column(
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 14,
                    vertical: 7,
                  ),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(999),
                    border: Border.all(color: const Color(0xFF86EFAC)),
                  ),
                  child: const Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(
                        Icons.verified_user_rounded,
                        color: Color(0xFF047857),
                        size: 18,
                      ),
                      SizedBox(width: 7),
                      Text(
                        'Masuk dengan Wajah',
                        style: TextStyle(
                          color: Color(0xFF047857),
                          fontSize: 15,
                          fontWeight: FontWeight.w900,
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 18),
                Container(
                  width: 122,
                  height: 122,
                  decoration: BoxDecoration(
                    color: Colors.white,
                    shape: BoxShape.circle,
                    border: Border.all(
                      color: const Color(0xFF6EE7A0),
                      width: 4,
                    ),
                    boxShadow: const [
                      BoxShadow(
                        color: Color(0x22059669),
                        blurRadius: 22,
                        offset: Offset(0, 8),
                      ),
                    ],
                  ),
                  child: const Icon(
                    Icons.face_retouching_natural_rounded,
                    color: Color(0xFF059669),
                    size: 66,
                  ),
                ),
                const SizedBox(height: 18),
                const Text(
                  'Cukup lihat kamera',
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    color: Color(0xFF052E25),
                    fontSize: 24,
                    fontWeight: FontWeight.w900,
                    height: 1.2,
                  ),
                ),
                const SizedBox(height: 6),
                const Text(
                  'Pastikan wajah terlihat jelas dan cahaya cukup.',
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    color: Color(0xFF3F6F63),
                    fontSize: 16,
                    fontWeight: FontWeight.w700,
                    height: 1.35,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),
          FilledButton.icon(
            onPressed: isBusy ? null : _captureFace,
            icon: _isCapturingFace
                ? const SizedBox.square(
                    dimension: 20,
                    child: CircularProgressIndicator(
                      strokeWidth: 2,
                      color: Colors.white,
                    ),
                  )
                : Icon(
                    hasFace
                        ? Icons.check_circle_rounded
                        : Icons.camera_alt_outlined,
                    size: 22,
                  ),
            label: Text(
              _isCapturingFace
                  ? 'Membaca wajah...'
                  : hasFace
                  ? (_faceLabel ?? 'Wajah siap')
                  : 'Masuk dengan Wajah',
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
          const SizedBox(height: 14),
          const Row(
            children: [
              Icon(
                Icons.phone_android_rounded,
                color: Color(0xFF047857),
                size: 20,
              ),
              SizedBox(width: 8),
              Expanded(
                child: Text(
                  'Bisa dipakai dari HP ini meski sinyal sedang buruk.',
                  style: TextStyle(
                    color: Color(0xFF14532D),
                    fontSize: 14,
                    fontWeight: FontWeight.w700,
                    height: 1.35,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          TextButton.icon(
            onPressed: isBusy
                ? null
                : () {
                    setState(() => _showPhonePinHelp = !_showPhonePinHelp);
                  },
            icon: Icon(
              _showPhonePinHelp
                  ? Icons.expand_less_rounded
                  : Icons.help_outline_rounded,
              size: 20,
            ),
            label: Text(
              _showPhonePinHelp
                  ? 'Tutup bantuan masuk'
                  : 'Wajah sulit terbaca?',
            ),
            style: TextButton.styleFrom(
              foregroundColor: const Color(0xFF047857),
              textStyle: const TextStyle(
                fontSize: 15,
                fontWeight: FontWeight.w800,
              ),
            ),
          ),
          if (_showPhonePinHelp) ...[
            const SizedBox(height: 10),
            TextField(
              controller: _phoneController,
              keyboardType: TextInputType.phone,
              textInputAction: TextInputAction.next,
              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
              decoration: _inputDecoration(
                hintText: 'Nomor HP, boleh dikosongkan',
                prefixIcon: Icons.phone_outlined,
              ),
            ),
            const SizedBox(height: 12),
            TextField(
              controller: _pinController,
              keyboardType: TextInputType.number,
              obscureText: true,
              maxLength: 6,
              textInputAction: TextInputAction.done,
              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
              onSubmitted: (_) => _captureFace(),
              decoration: _inputDecoration(
                hintText: 'PIN, boleh dikosongkan',
                prefixIcon: Icons.pin_outlined,
                counterText: '',
              ),
            ),
          ],
          const SizedBox(height: 8),
          Center(
            child: TextButton(
              onPressed: isSubmitting
                  ? null
                  : () => context.go('/forgot-password'),
              style: TextButton.styleFrom(
                foregroundColor: const Color(0xFF475569),
                textStyle: const TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w600,
                ),
              ),
              child: const Text('Lupa PIN atau kata sandi?'),
            ),
          ),
        ],
      ),
    );
  }

  Widget _passwordToggle() {
    return OutlinedButton.icon(
      onPressed: () => setState(() => _showPasswordLogin = !_showPasswordLogin),
      icon: Icon(
        _showPasswordLogin
            ? Icons.expand_less_rounded
            : Icons.lock_outline_rounded,
        size: 20,
      ),
      label: Text(
        _showPasswordLogin ? 'Tutup form email' : 'Masuk pakai email',
      ),
      style: OutlinedButton.styleFrom(
        foregroundColor: const Color(0xFF14532D),
        side: const BorderSide(color: Color(0xFFBBF7D0)),
        backgroundColor: Colors.white,
        minimumSize: const Size.fromHeight(56),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        textStyle: const TextStyle(fontSize: 15, fontWeight: FontWeight.w800),
      ),
    );
  }

  Widget _passwordLoginCard(bool isSubmitting) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: const [
          BoxShadow(
            color: Color(0x08000000),
            blurRadius: 18,
            offset: Offset(0, 6),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          TextField(
            controller: _emailController,
            keyboardType: TextInputType.emailAddress,
            textInputAction: TextInputAction.next,
            style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
            decoration: _inputDecoration(
              hintText: 'nama@email.com',
              prefixIcon: Icons.mail_outline_rounded,
            ),
          ),
          const SizedBox(height: 14),
          TextField(
            controller: _passwordController,
            obscureText: _obscurePassword,
            textInputAction: TextInputAction.done,
            style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
            onSubmitted: (_) => _submitPasswordLogin(),
            decoration: _inputDecoration(
              hintText: 'Kata sandi',
              prefixIcon: Icons.lock_outline_rounded,
              suffixIcon: IconButton(
                onPressed: () =>
                    setState(() => _obscurePassword = !_obscurePassword),
                icon: Icon(
                  _obscurePassword
                      ? Icons.visibility_outlined
                      : Icons.visibility_off_outlined,
                  color: const Color(0xFF64748B),
                ),
              ),
            ),
          ),
          const SizedBox(height: 16),
          FilledButton(
            onPressed: isSubmitting ? null : _submitPasswordLogin,
            style: FilledButton.styleFrom(
              backgroundColor: const Color(0xFF059669),
              foregroundColor: Colors.white,
              minimumSize: const Size.fromHeight(52),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(14),
              ),
              textStyle: const TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.w800,
              ),
            ),
            child: const Text('Masuk'),
          ),
        ],
      ),
    );
  }

  Widget _registerLink() {
    return Row(
      mainAxisAlignment: MainAxisAlignment.center,
      children: [
        const Text(
          'Belum punya akun? ',
          style: TextStyle(
            color: Color(0xFF64748B),
            fontSize: 15,
            fontWeight: FontWeight.w600,
          ),
        ),
        GestureDetector(
          onTap: () {
            Navigator.of(context).push(
              MaterialPageRoute(
                builder: (context) => const RoleSelectionScreen(),
              ),
            );
          },
          child: const Text(
            'Daftar',
            style: TextStyle(
              color: Color(0xFF059669),
              fontSize: 15,
              fontWeight: FontWeight.w900,
              decoration: TextDecoration.underline,
            ),
          ),
        ),
      ],
    );
  }

  Widget _statusBanner(String message, {required bool isError}) {
    final color = isError ? const Color(0xFFDC2626) : const Color(0xFF047857);

    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      padding: const EdgeInsets.all(13),
      decoration: BoxDecoration(
        color: isError ? const Color(0xFFFEF2F2) : const Color(0xFFE8F8F0),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: color.withValues(alpha: 0.22)),
      ),
      child: Row(
        children: [
          Icon(
            isError
                ? Icons.error_outline_rounded
                : Icons.check_circle_outline_rounded,
            color: color,
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              message,
              style: TextStyle(color: color, fontWeight: FontWeight.w700),
            ),
          ),
        ],
      ),
    );
  }

  InputDecoration _inputDecoration({
    required String hintText,
    required IconData prefixIcon,
    String? counterText,
    Widget? suffixIcon,
  }) {
    return InputDecoration(
      hintText: hintText,
      hintStyle: const TextStyle(
        color: Color(0xFF94A3B8),
        fontSize: 15,
        fontWeight: FontWeight.w500,
      ),
      counterText: counterText,
      prefixIcon: Icon(prefixIcon, color: const Color(0xFF059669), size: 22),
      suffixIcon: suffixIcon,
      filled: true,
      fillColor: const Color(0xFFF8FAFC),
      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(14),
        borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(14),
        borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(14),
        borderSide: const BorderSide(color: Color(0xFF059669), width: 1.8),
      ),
    );
  }
}
