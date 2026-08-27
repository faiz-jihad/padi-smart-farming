import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:padi/features/auth/presentation/screens/register_screen.dart';
import 'package:padi/features/home/presentation/tokens/home_tokens.dart';

class RoleSelectionScreen extends StatefulWidget {
  const RoleSelectionScreen({super.key});

  @override
  State<RoleSelectionScreen> createState() => _RoleSelectionScreenState();
}

class _RoleSelectionScreenState extends State<RoleSelectionScreen>
    with SingleTickerProviderStateMixin {
  String _selectedRole = 'farmer'; // 'farmer' or 'buyer'
  AnimationController? _entranceController;

  @override
  void initState() {
    super.initState();
    _initController();
  }

  void _initController() {
    _entranceController ??= AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 700),
    )..forward();
  }

  @override
  void dispose() {
    _entranceController?.dispose();
    super.dispose();
  }

  void _proceedToRegister() {
    Navigator.of(context).push(
      MaterialPageRoute(
        builder: (context) => RegisterScreen(initialRole: _selectedRole),
      ),
    );
  }

  Animation<Offset> _createSlide(double start, double end) {
    return Tween<Offset>(
      begin: const Offset(0, 0.14),
      end: Offset.zero,
    ).animate(
      CurvedAnimation(
        parent: _entranceController!,
        curve: Interval(start, end, curve: Curves.easeOutCubic),
      ),
    );
  }

  Animation<double> _createFade(double start, double end) {
    return Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(
        parent: _entranceController!,
        curve: Interval(start, end, curve: Curves.easeOut),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    _initController(); // Safe fallback for Hot Reload

    final isFarmer = _selectedRole == 'farmer';
    final isBuyer = _selectedRole == 'buyer';

    final headerSlide = _createSlide(0.0, 0.55);
    final headerFade = _createFade(0.0, 0.55);

    final card1Slide = _createSlide(0.20, 0.75);
    final card1Fade = _createFade(0.20, 0.75);

    final card2Slide = _createSlide(0.35, 0.90);
    final card2Fade = _createFade(0.35, 0.90);

    final buttonSlide = _createSlide(0.50, 1.0);
    final buttonFade = _createFade(0.50, 1.0);

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      body: Stack(
        children: [
          // Ambient soft radial gradient backdrop (Premium SaaS glow, no emojis)
          Positioned(
            top: -120,
            right: -100,
            child: Container(
              width: 320,
              height: 320,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                gradient: RadialGradient(
                  colors: [
                    const Color(0xFF10B981).withOpacity(0.08),
                    Colors.transparent,
                  ],
                ),
              ),
            ),
          ),
          Positioned(
            bottom: -80,
            left: -80,
            child: Container(
              width: 260,
              height: 260,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                gradient: RadialGradient(
                  colors: [
                    const Color(0xFF047857).withOpacity(0.06),
                    Colors.transparent,
                  ],
                ),
              ),
            ),
          ),

          SafeArea(
            child: Column(
              children: [
                // Top Custom Clean App Bar
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      IconButton(
                        onPressed: () {
                          if (Navigator.of(context).canPop()) {
                            Navigator.of(context).pop();
                          } else {
                            context.go('/login');
                          }
                        },
                        icon: const Icon(
                          Icons.arrow_back_ios_new_rounded,
                          size: 18,
                          color: Color(0xFF0F172A),
                        ),
                        style: IconButton.styleFrom(
                          backgroundColor: Colors.white,
                          elevation: 0,
                          side: const BorderSide(color: Color(0xFFE2E8F0)),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                        ),
                        tooltip: 'Kembali',
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                        decoration: BoxDecoration(
                          color: const Color(0xFFF1F5F9),
                          borderRadius: BorderRadius.circular(20),
                          border: Border.all(color: const Color(0xFFE2E8F0)),
                        ),
                        child: const Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(Icons.shield_outlined, size: 13, color: Color(0xFF059669)),
                            SizedBox(width: 5),
                            Text(
                              'Pendaftaran Resmi P.A.D.I.',
                              style: TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.w700,
                                color: Color(0xFF334155),
                              ),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(width: 40), // Balance the leading button
                    ],
                  ),
                ),

                // Scrollable Content
                Expanded(
                  child: Center(
                    child: ConstrainedBox(
                      constraints: const BoxConstraints(maxWidth: 480),
                      child: ListView(
                        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 4),
                        physics: const BouncingScrollPhysics(),
                        children: [
                          // 1. Header with Logo & Typography
                          SlideTransition(
                            position: headerSlide,
                            child: FadeTransition(
                              opacity: headerFade,
                              child: Column(
                                children: [
                                  Container(
                                    width: 52,
                                    height: 52,
                                    padding: const EdgeInsets.all(9),
                                    decoration: BoxDecoration(
                                      color: Colors.white,
                                      shape: BoxShape.circle,
                                      border: Border.all(
                                        color: const Color(0xFFE2E8F0),
                                        width: 1.5,
                                      ),
                                      boxShadow: [
                                        BoxShadow(
                                          color: Colors.black.withOpacity(0.04),
                                          blurRadius: 14,
                                          offset: const Offset(0, 4),
                                        ),
                                      ],
                                    ),
                                    child: Image.asset(
                                      'assets/images/padi-logo.png',
                                      fit: BoxFit.contain,
                                      errorBuilder: (_, __, ___) => const Icon(
                                        Icons.eco_rounded,
                                        color: Color(0xFF059669),
                                        size: 26,
                                      ),
                                    ),
                                  ),
                                  const SizedBox(height: 12),
                                  const Text(
                                    'Pilih Jenis Akun',
                                    style: TextStyle(
                                      fontSize: 23,
                                      fontWeight: FontWeight.w900,
                                      color: Color(0xFF0F172A),
                                      letterSpacing: -0.5,
                                    ),
                                  ),
                                  const SizedBox(height: 4),
                                  const Text(
                                    'Tentukan peran Anda untuk pengalaman terbaik di ekosistem P.A.D.I.',
                                    textAlign: TextAlign.center,
                                    style: TextStyle(
                                      fontSize: 13,
                                      color: Color(0xFF64748B),
                                      height: 1.35,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),

                          const SizedBox(height: 20),

                          // 2. Card 1: Petani Mitra (Animated Entrance & Interactive Scaling)
                          SlideTransition(
                            position: card1Slide,
                            child: FadeTransition(
                              opacity: card1Fade,
                              child: _AnimatedRoleCard(
                                isSelected: isFarmer,
                                badgeText: 'AKUN PETANI MITRA',
                                badgeIcon: Icons.agriculture_rounded,
                                badgeColor: const Color(0xFF047857),
                                badgeBg: const Color(0xFFDCFCE7),
                                icon: Icons.grass_rounded,
                                iconColor: const Color(0xFF059669),
                                iconBg: const Color(0xFFECFDF5),
                                title: 'Petani Mitra P.A.D.I.',
                                subtitle:
                                    'Kelola lahan sawah, pantau fase tanam, rekomendasi pupuk, deteksi hama AI & jual panen.',
                                tags: const [
                                  _TagItem(Icons.sensors_rounded, 'Monitoring Lahan & Cuaca'),
                                  _TagItem(Icons.biotech_rounded, 'Deteksi Hama AI'),
                                  _TagItem(Icons.calendar_month_rounded, 'Kalender Agronomi'),
                                  _TagItem(Icons.storefront_rounded, 'Bursa Hasil Panen'),
                                ],
                                onTap: () => setState(() => _selectedRole = 'farmer'),
                                accentColor: const Color(0xFF059669),
                              ),
                            ),
                          ),

                          const SizedBox(height: 14),

                          // 3. Card 2: Pembeli B2B (Animated Entrance & Interactive Scaling)
                          SlideTransition(
                            position: card2Slide,
                            child: FadeTransition(
                              opacity: card2Fade,
                              child: _AnimatedRoleCard(
                                isSelected: isBuyer,
                                badgeText: 'AKUN PEMBELI & INDUSTRI',
                                badgeIcon: Icons.business_center_rounded,
                                badgeColor: const Color(0xFF0F5132),
                                badgeBg: const Color(0xFFD1FAE5),
                                icon: Icons.storefront_rounded,
                                iconColor: const Color(0xFF0F5132),
                                iconBg: const Color(0xFFECFDF5),
                                title: 'Pembeli / Industri Beras',
                                subtitle:
                                    'Beli langsung gabah GKP/GKG dan beras premium dari petani dengan timbangan tera sah.',
                                tags: const [
                                  _TagItem(Icons.inventory_2_outlined, 'Bursa Gabah & Beras'),
                                  _TagItem(Icons.scale_rounded, 'Timbangan Tera Sah'),
                                  _TagItem(Icons.local_shipping_outlined, 'Logistik Partai Truk'),
                                  _TagItem(Icons.verified_outlined, 'Kontrak Jual Beli Sah'),
                                ],
                                onTap: () => setState(() => _selectedRole = 'buyer'),
                                accentColor: const Color(0xFF0F5132),
                              ),
                            ),
                          ),

                          const SizedBox(height: 22),

                          // 4. Action Button with Animated Transitions
                          SlideTransition(
                            position: buttonSlide,
                            child: FadeTransition(
                              opacity: buttonFade,
                              child: Column(
                                children: [
                                  SizedBox(
                                    width: double.infinity,
                                    height: 50,
                                    child: AnimatedContainer(
                                      duration: const Duration(milliseconds: 250),
                                      curve: Curves.easeInOut,
                                      decoration: BoxDecoration(
                                        borderRadius: BorderRadius.circular(14),
                                        boxShadow: [
                                          BoxShadow(
                                            color: (isBuyer
                                                    ? const Color(0xFF0F5132)
                                                    : HomeColors.primaryGreen)
                                                .withOpacity(0.28),
                                            blurRadius: 16,
                                            offset: const Offset(0, 4),
                                          ),
                                        ],
                                      ),
                                      child: FilledButton(
                                        onPressed: _proceedToRegister,
                                        style: FilledButton.styleFrom(
                                          backgroundColor: isBuyer
                                              ? const Color(0xFF0F5132)
                                              : HomeColors.primaryGreen,
                                          foregroundColor: Colors.white,
                                          elevation: 0,
                                          shape: RoundedRectangleBorder(
                                            borderRadius: BorderRadius.circular(14),
                                          ),
                                        ),
                                        child: AnimatedSwitcher(
                                          duration: const Duration(milliseconds: 220),
                                          transitionBuilder: (child, anim) =>
                                              FadeTransition(opacity: anim, child: child),
                                          child: Row(
                                            key: ValueKey<String>(_selectedRole),
                                            mainAxisAlignment: MainAxisAlignment.center,
                                            children: [
                                              Text(
                                                'Lanjutkan sebagai ${isBuyer ? 'Pembeli B2B' : 'Petani'}',
                                                style: const TextStyle(
                                                  fontSize: 14.5,
                                                  fontWeight: FontWeight.w900,
                                                  letterSpacing: -0.2,
                                                ),
                                              ),
                                              const SizedBox(width: 8),
                                              const Icon(Icons.arrow_forward_rounded, size: 18),
                                            ],
                                          ),
                                        ),
                                      ),
                                    ),
                                  ),

                                  const SizedBox(height: 14),

                                  // Clean Login Link Footer
                                  Wrap(
                                    alignment: WrapAlignment.center,
                                    crossAxisAlignment: WrapCrossAlignment.center,
                                    children: [
                                      const Text(
                                        'Sudah punya akun? ',
                                        style: TextStyle(
                                          color: Color(0xFF64748B),
                                          fontSize: 13,
                                        ),
                                      ),
                                      InkWell(
                                        onTap: () {
                                          if (Navigator.of(context).canPop()) {
                                            Navigator.of(context).pop();
                                          } else {
                                            context.go('/login');
                                          }
                                        },
                                        borderRadius: BorderRadius.circular(4),
                                        child: const Padding(
                                          padding: EdgeInsets.symmetric(horizontal: 4, vertical: 2),
                                          child: Text(
                                            'Masuk di sini',
                                            style: TextStyle(
                                              color: Color(0xFF059669),
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
                          ),

                          const SizedBox(height: 12),
                        ],
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _TagItem {
  const _TagItem(this.icon, this.label);
  final IconData icon;
  final String label;
}

class _AnimatedRoleCard extends StatelessWidget {
  const _AnimatedRoleCard({
    required this.isSelected,
    required this.badgeText,
    required this.badgeIcon,
    required this.badgeColor,
    required this.badgeBg,
    required this.icon,
    required this.iconColor,
    required this.iconBg,
    required this.title,
    required this.subtitle,
    required this.tags,
    required this.onTap,
    required this.accentColor,
  });

  final bool isSelected;
  final String badgeText;
  final IconData badgeIcon;
  final Color badgeColor;
  final Color badgeBg;
  final IconData icon;
  final Color iconColor;
  final Color iconBg;
  final String title;
  final String subtitle;
  final List<_TagItem> tags;
  final VoidCallback onTap;
  final Color accentColor;

  @override
  Widget build(BuildContext context) {
    return AnimatedScale(
      scale: isSelected ? 1.015 : 0.985,
      duration: const Duration(milliseconds: 220),
      curve: Curves.easeOutCubic,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 220),
        curve: Curves.easeOutCubic,
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(18),
          border: Border.all(
            color: isSelected ? accentColor : const Color(0xFFE2E8F0),
            width: isSelected ? 2.0 : 1.2,
          ),
          boxShadow: [
            if (isSelected)
              BoxShadow(
                color: accentColor.withOpacity(0.14),
                blurRadius: 18,
                offset: const Offset(0, 6),
              )
            else
              BoxShadow(
                color: Colors.black.withOpacity(0.02),
                blurRadius: 6,
                offset: const Offset(0, 2),
              ),
          ],
        ),
        child: Material(
          color: Colors.transparent,
          borderRadius: BorderRadius.circular(18),
          child: InkWell(
            onTap: onTap,
            borderRadius: BorderRadius.circular(18),
            splashColor: accentColor.withOpacity(0.08),
            highlightColor: accentColor.withOpacity(0.04),
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Top Row: Icon Container + Badge + Custom Animated Radio Check
                  Row(
                    children: [
                      // Circular Icon Container with smooth background transition
                      AnimatedContainer(
                        duration: const Duration(milliseconds: 200),
                        width: 44,
                        height: 44,
                        decoration: BoxDecoration(
                          color: isSelected ? iconBg : const Color(0xFFF1F5F9),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: Icon(
                          icon,
                          color: isSelected ? iconColor : const Color(0xFF64748B),
                          size: 22,
                        ),
                      ),
                      const SizedBox(width: 12),

                      // Text Badge (No emoji, clean icon + text)
                      Expanded(
                        child: Align(
                          alignment: Alignment.centerLeft,
                          child: AnimatedContainer(
                            duration: const Duration(milliseconds: 200),
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                            decoration: BoxDecoration(
                              color: isSelected ? badgeBg : const Color(0xFFF1F5F9),
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Icon(
                                  badgeIcon,
                                  size: 12,
                                  color: isSelected ? badgeColor : const Color(0xFF475569),
                                ),
                                const SizedBox(width: 4),
                                Text(
                                  badgeText,
                                  style: TextStyle(
                                    fontSize: 10,
                                    fontWeight: FontWeight.w800,
                                    color: isSelected ? badgeColor : const Color(0xFF475569),
                                    letterSpacing: 0.4,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ),

                      // Animated Radio Checkbox Circle
                      AnimatedContainer(
                        duration: const Duration(milliseconds: 200),
                        width: 22,
                        height: 22,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          color: isSelected ? accentColor : Colors.transparent,
                          border: Border.all(
                            color: isSelected ? accentColor : const Color(0xFFCBD5E1),
                            width: 1.8,
                          ),
                        ),
                        child: AnimatedSwitcher(
                          duration: const Duration(milliseconds: 180),
                          child: isSelected
                              ? const Icon(
                                  Icons.check_rounded,
                                  key: ValueKey('checked'),
                                  size: 14,
                                  color: Colors.white,
                                )
                              : const SizedBox.shrink(key: ValueKey('unchecked')),
                        ),
                      ),
                    ],
                  ),

                  const SizedBox(height: 12),

                  // Card Title
                  Text(
                    title,
                    style: const TextStyle(
                      fontSize: 16.5,
                      fontWeight: FontWeight.w900,
                      color: Color(0xFF0F172A),
                      letterSpacing: -0.3,
                    ),
                  ),

                  const SizedBox(height: 4),

                  // Card Subtitle
                  Text(
                    subtitle,
                    style: const TextStyle(
                      fontSize: 12.5,
                      color: Color(0xFF64748B),
                      height: 1.35,
                    ),
                  ),

                  const SizedBox(height: 12),

                  // Clean Tags with Micro-Icons (Zero Emojis)
                  Wrap(
                    spacing: 6,
                    runSpacing: 6,
                    children: tags.map((t) {
                      return AnimatedContainer(
                        duration: const Duration(milliseconds: 200),
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                        decoration: BoxDecoration(
                          color: isSelected
                              ? accentColor.withOpacity(0.07)
                              : const Color(0xFFF8FAFC),
                          borderRadius: BorderRadius.circular(6),
                          border: Border.all(
                            color: isSelected
                                ? accentColor.withOpacity(0.20)
                                : const Color(0xFFE2E8F0),
                          ),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(
                              t.icon,
                              size: 12,
                              color: isSelected ? accentColor : const Color(0xFF64748B),
                            ),
                            const SizedBox(width: 4),
                            Text(
                              t.label,
                              style: TextStyle(
                                fontSize: 11,
                                fontWeight: FontWeight.w700,
                                color: isSelected ? accentColor : const Color(0xFF334155),
                              ),
                            ),
                          ],
                        ),
                      );
                    }).toList(),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
