import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:padi/core/localization/app_language.dart';
import 'package:padi/core/providers/app_providers.dart';
import 'package:padi/features/home/presentation/tokens/home_tokens.dart';

class ProfileScreen extends ConsumerStatefulWidget {
  const ProfileScreen({super.key});

  @override
  ConsumerState<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends ConsumerState<ProfileScreen> {
  final _nameController = TextEditingController();
  final _phoneController = TextEditingController();
  int? _loadedUserId;

  @override
  void dispose() {
    _nameController.dispose();
    _phoneController.dispose();
    super.dispose();
  }

  void _showEditProfileSheet(BuildContext context, dynamic user, dynamic state, AppStrings s) {
    _nameController.text = user?.name ?? '';
    _phoneController.text = user?.phone ?? '';

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) {
        return Padding(
          padding: EdgeInsets.only(
            bottom: MediaQuery.of(context).viewInsets.bottom,
          ),
          child: Container(
            decoration: const BoxDecoration(
              color: HomeColors.surface,
              borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
            ),
            padding: const EdgeInsets.fromLTRB(20, 12, 20, 28),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Center(
                  child: Container(
                    width: 40,
                    height: 4,
                    decoration: BoxDecoration(
                      color: HomeColors.border,
                      borderRadius: BorderRadius.circular(2),
                    ),
                  ),
                ),
                const SizedBox(height: 16),
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: HomeColors.lightGreen,
                        borderRadius: BorderRadius.circular(HomeRadius.sm),
                      ),
                      child: const Icon(
                        Icons.person_outline_rounded,
                        color: HomeColors.primaryGreen,
                        size: 20,
                      ),
                    ),
                    const SizedBox(width: 10),
                    Text(
                      s.personalInfo,
                      style: const TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w900,
                        color: HomeColors.textPrimary,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 16),

                // Name
                _buildInputLabel(s.fullName),
                _buildTextField(
                  controller: _nameController,
                  hint: s.fullName,
                  icon: Icons.badge_outlined,
                ),
                const SizedBox(height: 12),

                // Phone
                _buildInputLabel(s.phoneNumber),
                _buildTextField(
                  controller: _phoneController,
                  hint: s.phoneNumber,
                  icon: Icons.phone_outlined,
                  keyboardType: TextInputType.phone,
                ),
                const SizedBox(height: 20),

                // Action Buttons
                Row(
                  children: [
                    Expanded(
                      child: OutlinedButton(
                        onPressed: () => Navigator.pop(context),
                        style: OutlinedButton.styleFrom(
                          side: const BorderSide(color: HomeColors.border),
                          padding: const EdgeInsets.symmetric(vertical: 12),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(HomeRadius.md),
                          ),
                        ),
                        child: Text(s.cancel, style: const TextStyle(fontWeight: FontWeight.w700, color: HomeColors.textSecondary)),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: FilledButton(
                        onPressed: () async {
                          Navigator.pop(context);
                          await ref.read(authControllerProvider).updateProfile(
                            name: _nameController.text.trim(),
                            phone: _phoneController.text.trim(),
                          );
                          if (context.mounted) {
                            ScaffoldMessenger.of(context).showSnackBar(
                              SnackBar(
                                content: Text(
                                  s.lang == AppLanguage.jv
                                      ? 'Profil kasil dianyari'
                                      : s.lang == AppLanguage.en
                                          ? 'Profile updated successfully'
                                          : 'Profil berhasil diperbarui',
                                ),
                                backgroundColor: HomeColors.primaryGreen,
                                behavior: SnackBarBehavior.floating,
                              ),
                            );
                          }
                        },
                        style: FilledButton.styleFrom(
                          backgroundColor: HomeColors.primaryGreen,
                          padding: const EdgeInsets.symmetric(vertical: 12),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(HomeRadius.md),
                          ),
                        ),
                        child: Text(s.save, style: const TextStyle(fontWeight: FontWeight.w800)),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  void _showLogoutDialog(BuildContext context, AppStrings s) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(HomeRadius.xl)),
        backgroundColor: Colors.white,
        title: Row(
          children: [
            const Icon(Icons.logout_rounded, color: HomeColors.danger, size: 24),
            const SizedBox(width: 10),
            Text(s.signOut, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w900)),
          ],
        ),
        content: Text(
          s.signOutConfirm,
          style: const TextStyle(fontSize: 13.5, color: HomeColors.textPrimary),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text(s.cancel, style: const TextStyle(color: HomeColors.textSecondary, fontWeight: FontWeight.w700)),
          ),
          FilledButton(
            onPressed: () {
              Navigator.pop(context);
              ref.read(authControllerProvider).logout();
            },
            style: FilledButton.styleFrom(
              backgroundColor: HomeColors.danger,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(HomeRadius.md)),
            ),
            child: Text(s.yesSignOut, style: const TextStyle(fontWeight: FontWeight.w800)),
          ),
        ],
      ),
    );
  }

  void _showHelpCenterSheet(BuildContext context, AppStrings s) {
    final title = switch (s.lang) {
      AppLanguage.id => 'Pusat Bantuan & Konsultasi',
      AppLanguage.jv => 'Pusat Pitulungan & Layanan Tani',
      AppLanguage.en => 'Help Center & Support',
    };

    final subtitle = switch (s.lang) {
      AppLanguage.id => 'Dapatkan solusi kendala teknis aplikasi atau tanya pakar pertanian.',
      AppLanguage.jv => 'Nyuwun tulung babagan aplikasi utawa takon pakar tani.',
      AppLanguage.en => 'Get quick solutions for app issues or talk to agronomy experts.',
    };

    final faqTitle = switch (s.lang) {
      AppLanguage.id => 'Pertanyaan yang Sering Diajukan (FAQ)',
      AppLanguage.jv => 'Pitakon Sing Serep Ditakokake (FAQ)',
      AppLanguage.en => 'Frequently Asked Questions (FAQ)',
    };

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => DraggableScrollableSheet(
        initialChildSize: 0.75,
        maxChildSize: 0.95,
        minChildSize: 0.5,
        builder: (context, scrollController) => Container(
          decoration: const BoxDecoration(
            color: HomeColors.surface,
            borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
          ),
          padding: const EdgeInsets.fromLTRB(20, 12, 20, 24),
          child: ListView(
            controller: scrollController,
            children: [
              Center(
                child: Container(
                  width: 40,
                  height: 4,
                  decoration: BoxDecoration(
                    color: HomeColors.border,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: 16),
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: HomeColors.lightGreen,
                      borderRadius: BorderRadius.circular(HomeRadius.sm),
                    ),
                    child: const Icon(Icons.support_agent_rounded, color: HomeColors.primaryGreen, size: 24),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(title, style: const TextStyle(fontSize: 16.5, fontWeight: FontWeight.w900, color: HomeColors.textPrimary)),
                        const SizedBox(height: 2),
                        Text(subtitle, style: HomeTypography.supporting),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 20),

              // Contact Channels Card
              Container(
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: HomeColors.lightGreen,
                  borderRadius: BorderRadius.circular(HomeRadius.xl),
                  border: Border.all(color: HomeColors.primaryGreen.withOpacity(0.2)),
                ),
                child: Column(
                  children: [
                    _buildContactItem(
                      icon: Icons.chat_rounded,
                      color: const Color(0xFF16A34A),
                      title: 'WhatsApp Customer Service',
                      subtitle: '+62 812-3456-7890 (Aktif 08.00 - 17.00 WIB)',
                      onTap: () {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(
                            content: Text('Membuka WhatsApp Bantuan P.A.D.I...'),
                            backgroundColor: HomeColors.primaryGreen,
                            behavior: SnackBarBehavior.floating,
                          ),
                        );
                      },
                    ),
                    const Divider(height: 16, color: Color(0xFFDCFCE7)),
                    _buildContactItem(
                      icon: Icons.mail_outline_rounded,
                      color: HomeColors.deepGreen,
                      title: 'Email Dukungan Teknis',
                      subtitle: 'support@padi-smartfarming.id',
                      onTap: () {
                        ScaffoldMessenger.of(context).showSnackBar(
                          const SnackBar(
                            content: Text('Alamat email tersalin ke clipboard'),
                            backgroundColor: HomeColors.primaryGreen,
                            behavior: SnackBarBehavior.floating,
                          ),
                        );
                      },
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 22),
              Text(faqTitle, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w900, color: HomeColors.textPrimary)),
              const SizedBox(height: 12),

              _buildFaqTile(
                question: 'Bagaimana cara memeriksa kesehatan daun padi?',
                answer: 'Buka menu "Periksa" di navigasi bawah, izinkan akses kamera, lalu arahkan lensa ke daun padi yang bergejala (bercak, kuning, atau layu). AI akan langsung memberikan diagnosa penyakit dan dosis obat dalam hitungan detik.',
              ),
              _buildFaqTile(
                question: 'Bagaimana cara menghitung pupuk dengan kalkulator?',
                answer: 'Buka menu "Dosis Pupuk" dari Beranda. Pilih lahan aktif Anda atau masukkan luas petak sawah. Aplikasi akan menghitung otomatis takaran Urea, SP-36, dan NPK Phonska sesuai umur tanaman.',
              ),
              _buildFaqTile(
                question: 'Bagaimana cara menjual gabah/beras di Toko?',
                answer: 'Masuk ke tab "Toko", klik tombol "+ Jual Panen" di bagian atas. Masukkan nama komoditas (GKP/GKG/Beras), total kilogram, harga per kg, nomor kontak WhatsApp, dan unggah foto hasil panen.',
              ),
              _buildFaqTile(
                question: 'Apakah aplikasi bisa digunakan dalam Basa Jawa?',
                answer: 'Ya! P.A.D.I. mendukung penuh Basa Jawa sehari-hari. Anda bisa mengganti bahasa di halaman Profil > Bahasa Aplikasi > pilih "🌾 Basa Jawa". Seluruh menu akan otomatis berubah seketika.',
              ),
            ],
          ),
        ),
      ),
    );
  }

  void _showAboutAppSheet(BuildContext context, AppStrings s) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => Container(
        decoration: const BoxDecoration(
          color: HomeColors.surface,
          borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
        ),
        padding: const EdgeInsets.fromLTRB(20, 12, 20, 28),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Center(
              child: Container(
                width: 40,
                height: 4,
                decoration: BoxDecoration(
                  color: HomeColors.border,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
            ),
            const SizedBox(height: 16),

            // Header with Logo
            Row(
              children: [
                Container(
                  width: 52,
                  height: 52,
                  padding: const EdgeInsets.all(4),
                  decoration: BoxDecoration(
                    color: HomeColors.surface,
                    borderRadius: BorderRadius.circular(HomeRadius.md),
                    border: Border.all(color: HomeColors.border),
                  ),
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(HomeRadius.sm),
                    child: Image.asset(
                      'assets/images/padi-logo.png',
                      fit: BoxFit.contain,
                      errorBuilder: (context, error, stackTrace) => const Icon(
                        Icons.eco_rounded,
                        color: HomeColors.primaryGreen,
                        size: 28,
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 14),
                const Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'P.A.D.I. Smart Farming',
                        style: TextStyle(fontSize: 17, fontWeight: FontWeight.w900, color: HomeColors.textPrimary),
                      ),
                      SizedBox(height: 2),
                      Text(
                        'Precision Agriculture & Digital Intelligence',
                        style: TextStyle(fontSize: 11.5, color: HomeColors.textSecondary, fontWeight: FontWeight.w600),
                      ),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: HomeColors.lightGreen,
                    borderRadius: BorderRadius.circular(HomeRadius.pill),
                  ),
                  child: const Text(
                    'v1.0.0 Pro',
                    style: TextStyle(color: HomeColors.primaryGreen, fontSize: 10.5, fontWeight: FontWeight.w900),
                  ),
                ),
              ],
            ),

            const SizedBox(height: 16),
            const Text(
              'Platform pertanian pintar terintegrasi yang dirancang untuk memberdayakan petani padi Indonesia. Menggabungkan teknologi AI Computer Vision untuk deteksi dini penyakit, agroklimat presisi BMKG, kalkulator hara PUTS, serta pasar gabah digital tanpa perantara.',
              style: TextStyle(fontSize: 13, height: 1.5, color: HomeColors.textPrimary),
            ),
            const SizedBox(height: 16),

            // Feature Pills
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: [
                _buildAppFeatureBadge(Icons.camera_alt_rounded, 'AI Diagnosa Hama'),
                _buildAppFeatureBadge(Icons.wb_sunny_rounded, 'Agroklimat Presisi'),
                _buildAppFeatureBadge(Icons.calculate_rounded, 'Kalkulator Pupuk'),
                _buildAppFeatureBadge(Icons.storefront_rounded, 'Pasar Gabah Digital'),
                _buildAppFeatureBadge(Icons.translate_rounded, 'Multi-Bahasa (Basa Jawa)'),
              ],
            ),

            const SizedBox(height: 20),
            const Divider(height: 1, color: HomeColors.borderSubtle),
            const SizedBox(height: 12),
            const Center(
              child: Text(
                '© 2026 Tim P.A.D.I. • Hackathon KMIPN VI',
                style: TextStyle(fontSize: 11, color: HomeColors.textTertiary, fontWeight: FontWeight.w600),
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _showPrivacyPolicySheet(BuildContext context, AppStrings s) {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => DraggableScrollableSheet(
        initialChildSize: 0.7,
        maxChildSize: 0.9,
        minChildSize: 0.4,
        builder: (context, scrollController) => Container(
          decoration: const BoxDecoration(
            color: HomeColors.surface,
            borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
          ),
          padding: const EdgeInsets.fromLTRB(20, 12, 20, 24),
          child: ListView(
            controller: scrollController,
            children: [
              Center(
                child: Container(
                  width: 40,
                  height: 4,
                  decoration: BoxDecoration(
                    color: HomeColors.border,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: 16),
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: HomeColors.lightGreen,
                      borderRadius: BorderRadius.circular(HomeRadius.sm),
                    ),
                    child: const Icon(Icons.shield_outlined, color: HomeColors.primaryGreen, size: 24),
                  ),
                  const SizedBox(width: 12),
                  const Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Kebijakan Privasi & Keamanan',
                          style: TextStyle(fontSize: 16, fontWeight: FontWeight.w900, color: HomeColors.textPrimary),
                        ),
                        SizedBox(height: 2),
                        Text(
                          'Komitmen perlindungan data dan privasi petani.',
                          style: HomeTypography.supporting,
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 18),

              _buildPrivacyPoint(
                icon: Icons.location_on_outlined,
                title: 'Penggunaan Data Lokasi (GPS)',
                description: 'Koordinat lokasi hanya digunakan untuk menentukan prakiraan cuaca tingkat desa, rekomendasi kalender tanam, dan pemetaan polygon batas sawah Anda.',
              ),
              _buildPrivacyPoint(
                icon: Icons.camera_alt_outlined,
                title: 'Akses Kamera & Galeri Foto',
                description: 'Kamera hanya diaktifkan saat Anda mengambil sampel foto daun padi untuk dianalisis oleh AI model. Foto tidak disalahgunakan atau dijual ke pihak ketiga.',
              ),
              _buildPrivacyPoint(
                icon: Icons.lock_outline_rounded,
                title: 'Enkripsi & Keamanan Akun',
                description: 'Seluruh kata sandi, token autentikasi, dan transaksi terenkripsi menggunakan protokol keamanan standar industri (HTTPS, Sanctum AES-256).',
              ),
              _buildPrivacyPoint(
                icon: Icons.storefront_outlined,
                title: 'Data Transaksi Pasar Gabah',
                description: 'Informasi kontak dan listing komoditas hanya ditampilkan kepada pembeli terverifikasi saat Anda secara sukarela mempublikasikan panen Anda.',
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildContactItem({
    required IconData icon,
    required Color color,
    required String title,
    required String subtitle,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(HomeRadius.md),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: color.withOpacity(0.12),
              borderRadius: BorderRadius.circular(HomeRadius.sm),
            ),
            child: Icon(icon, color: color, size: 20),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(title, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w800, color: HomeColors.textPrimary)),
                const SizedBox(height: 1),
                Text(subtitle, style: const TextStyle(fontSize: 11.5, color: HomeColors.textSecondary)),
              ],
            ),
          ),
          const Icon(Icons.arrow_forward_ios_rounded, size: 14, color: HomeColors.textTertiary),
        ],
      ),
    );
  }

  Widget _buildFaqTile({required String question, required String answer}) {
    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      decoration: BoxDecoration(
        color: HomeColors.surface,
        borderRadius: BorderRadius.circular(HomeRadius.md),
        border: Border.all(color: HomeColors.border),
      ),
      child: ExpansionTile(
        tilePadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 2),
        childrenPadding: const EdgeInsets.fromLTRB(14, 0, 14, 14),
        shape: const Border(),
        collapsedShape: const Border(),
        title: Text(
          question,
          style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w800, color: HomeColors.textPrimary),
        ),
        children: [
          Text(
            answer,
            style: const TextStyle(fontSize: 12.5, height: 1.45, color: HomeColors.textSecondary),
          ),
        ],
      ),
    );
  }

  Widget _buildAppFeatureBadge(IconData icon, String label) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: HomeColors.surfaceMuted,
        borderRadius: BorderRadius.circular(HomeRadius.pill),
        border: Border.all(color: HomeColors.borderSubtle),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 14, color: HomeColors.primaryGreen),
          const SizedBox(width: 6),
          Text(label, style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w700, color: HomeColors.textPrimary)),
        ],
      ),
    );
  }

  Widget _buildPrivacyPoint({required IconData icon, required String title, required String description}) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 16),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.all(6),
            decoration: BoxDecoration(
              color: HomeColors.lightGreen,
              borderRadius: BorderRadius.circular(HomeRadius.sm),
            ),
            child: Icon(icon, size: 18, color: HomeColors.primaryGreen),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(title, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w800, color: HomeColors.textPrimary)),
                const SizedBox(height: 3),
                Text(description, style: const TextStyle(fontSize: 12, height: 1.4, color: HomeColors.textSecondary)),
              ],
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final auth = ref.watch(authControllerProvider);
    final currentLang = ref.watch(languageProvider);
    final s = AppStrings(currentLang);
    final user = auth.state.user;
    final isBuyer = ref.watch(isBuyerRoleProvider);

    if (user != null && user.id != _loadedUserId) {
      _loadedUserId = user.id;
      _nameController.text = user.name;
      _phoneController.text = user.phone ?? '';
    }

    return Scaffold(
      backgroundColor: HomeColors.background,
      appBar: AppBar(
        backgroundColor: HomeColors.background,
        elevation: 0,
        scrolledUnderElevation: 0,
        leading: IconButton(
          icon: Container(
            padding: const EdgeInsets.all(6),
            decoration: BoxDecoration(
              color: HomeColors.surface,
              shape: BoxShape.circle,
              border: Border.all(color: HomeColors.border),
            ),
            child: const Icon(Icons.arrow_back_rounded, color: HomeColors.textPrimary, size: 18),
          ),
          onPressed: () {
            if (Navigator.of(context).canPop()) {
              Navigator.of(context).pop();
            } else if (context.canPop()) {
              context.pop();
            } else {
              context.go('/home');
            }
          },
        ),
        title: Text(
          s.profile,
          style: const TextStyle(
            color: HomeColors.textPrimary,
            fontSize: 18,
            fontWeight: FontWeight.w900,
          ),
        ),
      ),
      body: SafeArea(
        child: Center(
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 580),
            child: ListView(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              children: [
                // 1. User Header Profile Card
                _buildUserHeaderCard(user, isBuyer),

                const SizedBox(height: 18),

                // 2. Section: Data Pengguna
                _buildSectionHeader(isBuyer ? 'AKUN PEMBELI / MITRA' : s.userSectionTitle),
                Container(
                  decoration: BoxDecoration(
                    color: HomeColors.surface,
                    borderRadius: BorderRadius.circular(HomeRadius.xl),
                    border: Border.all(color: HomeColors.border),
                    boxShadow: HomeShadows.subtle,
                  ),
                  child: Column(
                    children: [
                      _buildSettingsTile(
                        icon: Icons.person_outline_rounded,
                        title: s.personalInfo,
                        subtitle: s.personalInfoSubtitle,
                        onTap: () => _showEditProfileSheet(context, user, auth.state, s),
                      ),
                      const Divider(height: 1, color: HomeColors.borderSubtle, indent: 52),
                      _buildSettingsTile(
                        icon: Icons.lock_outline_rounded,
                        title: s.accountSecurity,
                        subtitle: s.accountSecuritySubtitle,
                        onTap: () => context.push('/profile/change-password'),
                      ),
                    ],
                  ),
                ),

                if (isBuyer) ...[
                  const SizedBox(height: 18),
                  _buildSectionHeader('Aktivitas Pembelian Panen'),
                  Container(
                    decoration: BoxDecoration(
                      color: HomeColors.surface,
                      borderRadius: BorderRadius.circular(HomeRadius.xl),
                      border: Border.all(color: HomeColors.border),
                      boxShadow: HomeShadows.subtle,
                    ),
                    child: Column(
                      children: [
                        _buildSettingsTile(
                          icon: Icons.receipt_long_rounded,
                          title: 'Pesanan & Kontrak Saya',
                          subtitle: 'Pantau kontrak panen aktif dan status pengiriman',
                          onTap: () => context.push('/buyer/orders'),
                        ),
                        const Divider(height: 1, color: HomeColors.borderSubtle, indent: 52),
                        _buildSettingsTile(
                          icon: Icons.shopping_cart_outlined,
                          title: 'Keranjang Belanja',
                          subtitle: 'Lihat daftar komoditas yang siap dicheckout',
                          onTap: () => context.push('/cart'),
                        ),
                        const Divider(height: 1, color: HomeColors.borderSubtle, indent: 52),
                        _buildSettingsTile(
                          icon: Icons.gavel_rounded,
                          title: 'Penawaran Harga Saya',
                          subtitle: 'Status penawaran lelang hasil panen',
                          onTap: () => context.push('/marketplace/offers'),
                        ),
                      ],
                    ),
                  ),
                ],

                const SizedBox(height: 18),

                // 3. Section: Pengaturan Aplikasi
                _buildSectionHeader(s.appSettings),
                Container(
                  decoration: BoxDecoration(
                    color: HomeColors.surface,
                    borderRadius: BorderRadius.circular(HomeRadius.xl),
                    border: Border.all(color: HomeColors.border),
                    boxShadow: HomeShadows.subtle,
                  ),
                  child: Column(
                    children: [
                      // Bahasa Setting
                      _buildSettingsTile(
                        icon: Icons.language_rounded,
                        title: s.language,
                        subtitle: s.languageSubtitle,
                        valueText: '${currentLang.flagEmoji} ${currentLang.nativeName}',
                        onTap: () => context.push('/profile/language'),
                      ),
                      const Divider(height: 1, color: HomeColors.borderSubtle, indent: 52),
                      // Notifikasi Setting
                      _buildSettingsTile(
                        icon: Icons.notifications_none_rounded,
                        title: s.notifications,
                        subtitle: s.notificationsSubtitle,
                        valueText: 'Aktif',
                        onTap: () => context.push('/notifications'),
                      ),
                      const Divider(height: 1, color: HomeColors.borderSubtle, indent: 52),
                      // Tema Setting
                      _buildSettingsTile(
                        icon: Icons.brightness_auto_rounded,
                        title: s.theme,
                        valueText: s.themeSystem,
                        onTap: () {
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(
                              content: Text('Tema otomatis mengikuti pengaturan sistem HP'),
                              backgroundColor: HomeColors.primaryGreen,
                              behavior: SnackBarBehavior.floating,
                            ),
                          );
                        },
                      ),
                    ],
                  ),
                ),

                const SizedBox(height: 18),

                // 4. Section: Bantuan & Informasi (Fully Interactive)
                _buildSectionHeader(s.helpSection),
                Container(
                  decoration: BoxDecoration(
                    color: HomeColors.surface,
                    borderRadius: BorderRadius.circular(HomeRadius.xl),
                    border: Border.all(color: HomeColors.border),
                    boxShadow: HomeShadows.subtle,
                  ),
                  child: Column(
                    children: [
                      _buildSettingsTile(
                        icon: Icons.help_outline_rounded,
                        title: s.helpCenter,
                        onTap: () => _showHelpCenterSheet(context, s),
                      ),
                      const Divider(height: 1, color: HomeColors.borderSubtle, indent: 52),
                      _buildSettingsTile(
                        icon: Icons.info_outline_rounded,
                        title: s.aboutApp,
                        valueText: 'v1.0.0',
                        onTap: () => _showAboutAppSheet(context, s),
                      ),
                      const Divider(height: 1, color: HomeColors.borderSubtle, indent: 52),
                      _buildSettingsTile(
                        icon: Icons.privacy_tip_outlined,
                        title: s.privacyPolicy,
                        onTap: () => _showPrivacyPolicySheet(context, s),
                      ),
                    ],
                  ),
                ),

                const SizedBox(height: 20),

                // 5. Sign Out Tile
                Container(
                  decoration: BoxDecoration(
                    color: HomeColors.surface,
                    borderRadius: BorderRadius.circular(HomeRadius.xl),
                    border: Border.all(color: const Color(0xFFFECDD3)),
                    boxShadow: HomeShadows.subtle,
                  ),
                  child: _buildSettingsTile(
                    icon: Icons.logout_rounded,
                    iconColor: HomeColors.danger,
                    title: s.signOut,
                    titleColor: HomeColors.danger,
                    hideChevron: true,
                    onTap: () => _showLogoutDialog(context, s),
                  ),
                ),

                const SizedBox(height: 40),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildSectionHeader(String title) {
    return Padding(
      padding: const EdgeInsets.only(left: 4, bottom: 8),
      child: Text(
        title.toUpperCase(),
        style: const TextStyle(
          color: HomeColors.textSecondary,
          fontSize: 11,
          fontWeight: FontWeight.w800,
          letterSpacing: 0.6,
        ),
      ),
    );
  }

  Widget _buildUserHeaderCard(dynamic user, bool isBuyer) {
    final name = user?.name ?? 'Pengguna';
    final email = user?.email ?? '';
    final phone = user?.phone ?? '';
    final rawRole = (user?.roleLabel ?? user?.role ?? '').toString().trim();
    String role;
    if (rawRole == 'true' || rawRole == 'false' || rawRole.isEmpty) {
      role = isBuyer ? 'Pembeli' : 'Petani';
    } else if (rawRole.toLowerCase() == 'buyer' || rawRole.toLowerCase() == 'partner') {
      role = 'Pembeli';
    } else if (rawRole.toLowerCase() == 'farmer') {
      role = 'Petani';
    } else {
      role = rawRole;
    }
    final initial = name.isNotEmpty ? name[0].toUpperCase() : 'P';

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: HomeColors.surface,
        borderRadius: BorderRadius.circular(HomeRadius.xl),
        border: Border.all(color: HomeColors.border),
        boxShadow: HomeShadows.subtle,
      ),
      child: Row(
        children: [
          Container(
            width: 58,
            height: 58,
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [HomeColors.deepGreen, HomeColors.primaryGreen],
              ),
              shape: BoxShape.circle,
              boxShadow: [
                BoxShadow(
                  color: HomeColors.primaryGreen.withOpacity(0.25),
                  blurRadius: 10,
                  offset: const Offset(0, 4),
                ),
              ],
            ),
            child: Center(
              child: Text(
                initial,
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 24,
                  fontWeight: FontWeight.w900,
                ),
              ),
            ),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Flexible(
                      child: Text(
                        name,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                          fontSize: 17,
                          fontWeight: FontWeight.w800,
                          color: HomeColors.textPrimary,
                        ),
                      ),
                    ),
                    const SizedBox(width: 6),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                      decoration: BoxDecoration(
                        color: HomeColors.lightGreen,
                        borderRadius: BorderRadius.circular(HomeRadius.pill),
                      ),
                      child: Text(
                        role,
                        style: const TextStyle(
                          color: HomeColors.primaryGreen,
                          fontSize: 10.5,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 3),
                Text(
                  email,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: HomeTypography.supporting,
                ),
                if (phone.isNotEmpty) ...[
                  const SizedBox(height: 2),
                  Text(
                    phone,
                    style: const TextStyle(
                      fontSize: 11.5,
                      fontWeight: FontWeight.w600,
                      color: HomeColors.textTertiary,
                    ),
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSettingsTile({
    required IconData icon,
    required String title,
    String? subtitle,
    String? valueText,
    Color iconColor = HomeColors.primaryGreen,
    Color titleColor = HomeColors.textPrimary,
    bool hideChevron = false,
    required VoidCallback onTap,
  }) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
          child: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: iconColor.withOpacity(0.12),
                  borderRadius: BorderRadius.circular(HomeRadius.sm),
                ),
                child: Icon(icon, color: iconColor, size: 20),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      title,
                      style: TextStyle(
                        color: titleColor,
                        fontSize: 14,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                    if (subtitle != null) ...[
                      const SizedBox(height: 2),
                      Text(
                        subtitle,
                        style: const TextStyle(
                          color: HomeColors.textSecondary,
                          fontSize: 11.5,
                        ),
                      ),
                    ],
                  ],
                ),
              ),
              if (valueText != null) ...[
                const SizedBox(width: 8),
                Text(
                  valueText,
                  style: const TextStyle(
                    color: HomeColors.textSecondary,
                    fontSize: 13,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ],
              if (!hideChevron) ...[
                const SizedBox(width: 6),
                const Icon(
                  Icons.chevron_right_rounded,
                  color: HomeColors.textTertiary,
                  size: 20,
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildInputLabel(String label) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 6, left: 2),
      child: Text(
        label,
        style: const TextStyle(
          color: HomeColors.textPrimary,
          fontSize: 12,
          fontWeight: FontWeight.w800,
        ),
      ),
    );
  }

  Widget _buildTextField({
    required TextEditingController controller,
    required String hint,
    required IconData icon,
    TextInputType keyboardType = TextInputType.text,
  }) {
    return Container(
      decoration: BoxDecoration(
        color: HomeColors.surface,
        borderRadius: BorderRadius.circular(HomeRadius.md),
        border: Border.all(color: HomeColors.border),
      ),
      child: TextField(
        controller: controller,
        keyboardType: keyboardType,
        style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w600),
        decoration: InputDecoration(
          hintText: hint,
          prefixIcon: Icon(icon, color: HomeColors.primaryGreen, size: 20),
          contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
          border: InputBorder.none,
          enabledBorder: InputBorder.none,
          focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(HomeRadius.md),
            borderSide: const BorderSide(color: HomeColors.primaryGreen, width: 1.5),
          ),
        ),
      ),
    );
  }
}
