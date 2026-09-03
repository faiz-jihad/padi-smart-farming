import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:path_provider/path_provider.dart';
import 'package:padi/core/localization/app_language.dart';
import 'package:padi/core/providers/app_providers.dart';
import 'package:padi/features/home/presentation/tokens/home_tokens.dart';
import 'package:padi/features/notifications/presentation/providers/notification_settings_provider.dart';

class ProfileScreen extends ConsumerStatefulWidget {
  const ProfileScreen({super.key});

  @override
  ConsumerState<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends ConsumerState<ProfileScreen> {
  final _nameController = TextEditingController();
  final _phoneController = TextEditingController();
  int? _loadedUserId;
  String _cacheSize = '0.0 B';
  bool _isLoadingCache = false;
  bool _isClearingCache = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _loadRealCacheSize();
    });
  }

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
                answer: 'Ya! P.A.D.I. mendukung penuh Basa Jawa sehari-hari. Anda bisa mengganti bahasa di halaman Profil > Bahasa Aplikasi > pilih "Basa Jawa". Seluruh menu akan otomatis berubah seketika.',
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

  void _showNotificationSettingsSheet(BuildContext context, AppStrings s) {
    final lang = ref.read(languageProvider);
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (sheetContext) {
        return Consumer(
          builder: (context, ref, _) {
            final settings = ref.watch(notificationSettingsProvider);
            final notifier = ref.read(notificationSettingsProvider.notifier);

            final title = switch (lang) {
              AppLanguage.id => 'Pengaturan Notifikasi',
              AppLanguage.jv => 'Setelan Kabar Notifikasi',
              AppLanguage.en => 'Notification Settings',
            };
            final subtitle = switch (lang) {
              AppLanguage.id => 'Kelola pemberitahuan yang ingin Anda terima di aplikasi',
              AppLanguage.jv => 'Atur kabar sing pengin kok tampa neng aplikasi',
              AppLanguage.en => 'Manage which push alerts you want to receive',
            };

            return Container(
              padding: const EdgeInsets.fromLTRB(20, 12, 20, 28),
              decoration: const BoxDecoration(
                color: HomeColors.surface,
                borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
              ),
              child: SafeArea(
                top: false,
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
                    const SizedBox(height: 18),
                    Row(
                      children: [
                        Container(
                          padding: const EdgeInsets.all(10),
                          decoration: BoxDecoration(
                            color: HomeColors.lightGreen,
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: const Icon(Icons.notifications_active_rounded, color: HomeColors.primaryGreen, size: 22),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(title, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w900, color: HomeColors.textPrimary)),
                              const SizedBox(height: 2),
                              Text(subtitle, style: const TextStyle(fontSize: 11.5, color: HomeColors.textSecondary)),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 18),

                    // Master Push Switch
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: settings.pushEnabled ? const Color(0xFFF0FDF4) : HomeColors.surfaceMuted,
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(color: settings.pushEnabled ? const Color(0xFFBBF7D0) : HomeColors.border),
                      ),
                      child: Row(
                        children: [
                          Icon(
                            settings.pushEnabled ? Icons.notifications_on_rounded : Icons.notifications_off_rounded,
                            color: settings.pushEnabled ? HomeColors.primaryGreen : HomeColors.textSecondary,
                            size: 22,
                          ),
                          const SizedBox(width: 10),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  lang == AppLanguage.id
                                      ? 'Pemberitahuan Push'
                                      : (lang == AppLanguage.jv ? 'Notifikasi Push HP' : 'Push Notifications'),
                                  style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w800, color: HomeColors.textPrimary),
                                ),
                                Text(
                                  lang == AppLanguage.id
                                      ? 'Izinkan aplikasi memunculkan notifikasi di HP'
                                      : (lang == AppLanguage.jv ? 'Ulihke aplikasi ngetokke kabar neng HP' : 'Allow notifications on your device'),
                                  style: const TextStyle(fontSize: 11, color: HomeColors.textSecondary),
                                ),
                              ],
                            ),
                          ),
                          Switch.adaptive(
                            value: settings.pushEnabled,
                            activeColor: HomeColors.primaryGreen,
                            onChanged: (val) => notifier.togglePush(val),
                          ),
                        ],
                      ),
                    ),

                    const SizedBox(height: 14),

                    // Specific toggles
                    Opacity(
                      opacity: settings.pushEnabled ? 1.0 : 0.45,
                      child: IgnorePointer(
                        ignoring: !settings.pushEnabled,
                        child: Column(
                          children: [
                            _buildNotifToggleItem(
                              icon: Icons.bug_report_outlined,
                              title: lang == AppLanguage.id
                                  ? 'Peringatan Hama & Penyakit'
                                  : (lang == AppLanguage.jv ? 'Kabar Hama & Penyakit' : 'Pest & Disease Outbreaks'),
                              subtitle: lang == AppLanguage.id
                                  ? 'Dapat peringatan dini saat ada wabah di sekitar lahan Anda'
                                  : (lang == AppLanguage.jv ? 'Kabar wereng lan hama ing sekitar sawah' : 'Early alerts when threats detected nearby'),
                              value: settings.pestAlerts,
                              onChanged: (val) => notifier.togglePestAlerts(val),
                            ),
                            const Divider(height: 1, color: HomeColors.borderSubtle),
                            _buildNotifToggleItem(
                              icon: Icons.calendar_month_outlined,
                              title: lang == AppLanguage.id
                                  ? 'Jadwal Pemupukan & Perawatan'
                                  : (lang == AppLanguage.jv ? 'Jadwal Mupuk & Ngrumat' : 'Fertilizer & Farming Schedule'),
                              subtitle: lang == AppLanguage.id
                                  ? 'Pengingat otomatis hari pemupukan, pengairan, dan panen'
                                  : (lang == AppLanguage.jv ? 'Pelingat dina mupuk, mbanyu, lan panen' : 'Reminders for watering, fertilizing, & harvest'),
                              value: settings.plantingReminders,
                              onChanged: (val) => notifier.togglePlantingReminders(val),
                            ),
                            const Divider(height: 1, color: HomeColors.borderSubtle),
                            _buildNotifToggleItem(
                              icon: Icons.trending_up_rounded,
                              title: lang == AppLanguage.id
                                  ? 'Update Harga Pasar Gabah'
                                  : (lang == AppLanguage.jv ? 'Info Rego Gabah & Beras' : 'Grain Market Price Updates'),
                              subtitle: lang == AppLanguage.id
                                  ? 'Info pergerakan harga gabah basah/kering dan penawaran lelang'
                                  : (lang == AppLanguage.jv ? 'Owah-owahan rego gabah gkp/gkg saben dina' : 'Daily market prices and buyer auctions'),
                              value: settings.marketPriceUpdates,
                              onChanged: (val) => notifier.toggleMarketPrice(val),
                            ),
                            const Divider(height: 1, color: HomeColors.borderSubtle),
                            _buildNotifToggleItem(
                              icon: Icons.local_shipping_outlined,
                              title: lang == AppLanguage.id
                                  ? 'Status Pesanan & Kontrak'
                                  : (lang == AppLanguage.jv ? 'Status Pesenan & Kontrak' : 'Orders & Contract Updates'),
                              subtitle: lang == AppLanguage.id
                                  ? 'Pemberitahuan perubahan status pembayaran & penimbangan'
                                  : (lang == AppLanguage.jv ? 'Kabar ngenani pembayaran lan timbangan' : 'Updates on weighing, deals, and delivery'),
                              value: settings.orderUpdates,
                              onChanged: (val) => notifier.toggleOrderUpdates(val),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            );
          },
        );
      },
    );
  }

  Widget _buildNotifToggleItem({
    required IconData icon,
    required String title,
    required String subtitle,
    required bool value,
    required ValueChanged<bool> onChanged,
  }) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 10),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          Icon(icon, size: 20, color: HomeColors.primaryGreen),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w700, color: HomeColors.textPrimary),
                ),
                const SizedBox(height: 1),
                Text(
                  subtitle,
                  style: const TextStyle(fontSize: 10.5, color: HomeColors.textSecondary),
                ),
              ],
            ),
          ),
          const SizedBox(width: 8),
          Switch.adaptive(
            value: value,
            activeColor: HomeColors.primaryGreen,
            onChanged: onChanged,
          ),
        ],
      ),
    );
  }

  Future<void> _loadRealCacheSize() async {
    if (_isLoadingCache || _isClearingCache) return;
    if (mounted) {
      setState(() => _isLoadingCache = true);
    }

    try {
      int totalBytes = 0;

      // 1. Temporary directory (foto kamera, file sementara upload, dsb)
      try {
        final tempDir = await getTemporaryDirectory();
        totalBytes += await _computeDirectorySize(tempDir);
      } catch (_) {}

      // 2. Application cache directory (cache http, thumbnail, dsb)
      try {
        final appCacheDir = await getApplicationCacheDirectory();
        totalBytes += await _computeDirectorySize(appCacheDir);
      } catch (_) {}

      // 3. In-memory image cache
      try {
        totalBytes += PaintingBinding.instance.imageCache.currentSizeBytes;
      } catch (_) {}

      if (!mounted) return;
      setState(() {
        _cacheSize = _formatBytes(totalBytes);
        _isLoadingCache = false;
      });
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _cacheSize = '0.0 B';
        _isLoadingCache = false;
      });
    }
  }

  Future<int> _computeDirectorySize(Directory dir) async {
    int size = 0;
    try {
      if (await dir.exists()) {
        final entities = dir.listSync(recursive: true, followLinks: false);
        for (final entity in entities) {
          if (entity is File) {
            try {
              size += entity.lengthSync();
            } catch (_) {}
          }
        }
      }
    } catch (_) {}
    return size;
  }

  Future<void> _deleteDirectoryContents(Directory dir) async {
    try {
      if (await dir.exists()) {
        final entities = dir.listSync(followLinks: false);
        for (final entity in entities) {
          try {
            if (entity is Directory) {
              entity.deleteSync(recursive: true);
            } else if (entity is File) {
              entity.deleteSync();
            }
          } catch (_) {}
        }
      }
    } catch (_) {}
  }

  String _formatBytes(int bytes) {
    if (bytes <= 0) return '0.0 B';
    if (bytes < 1024) return '$bytes B';
    if (bytes < 1024 * 1024) {
      return '${(bytes / 1024).toStringAsFixed(1)} KB';
    }
    if (bytes < 1024 * 1024 * 1024) {
      return '${(bytes / (1024 * 1024)).toStringAsFixed(1)} MB';
    }
    return '${(bytes / (1024 * 1024 * 1024)).toStringAsFixed(2)} GB';
  }

  void _clearAppCache(BuildContext context, AppStrings s) {
    if (_isClearingCache) return;
    final lang = ref.read(languageProvider);

    showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (modalContext) {
        return Container(
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
          ),
          padding: const EdgeInsets.fromLTRB(20, 16, 20, 24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Center(
                child: Container(
                  width: 44,
                  height: 5,
                  decoration: BoxDecoration(
                    color: const Color(0xFFCBD5E1),
                    borderRadius: BorderRadius.circular(3),
                  ),
                ),
              ),
              const SizedBox(height: 18),
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: const Color(0xFFECFDF5),
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: const Color(0xFFA7F3D0)),
                    ),
                    child: const Icon(
                      Icons.cleaning_services_rounded,
                      color: Color(0xFF059669),
                      size: 26,
                    ),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          lang == AppLanguage.id
                              ? 'Bersihkan Cache Aplikasi'
                              : (lang == AppLanguage.jv ? 'Resiki Cache Aplikasi' : 'Clear App Cache'),
                          style: const TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.w900,
                            color: Color(0xFF0F172A),
                          ),
                        ),
                        const SizedBox(height: 3),
                        Text(
                          lang == AppLanguage.id
                              ? 'Kosongkan memori sementara untuk meringankan HP'
                              : (lang == AppLanguage.jv ? 'Kosongake memori sauntara ben entheng' : 'Free up temporary storage'),
                          style: const TextStyle(
                            fontSize: 12.5,
                            color: Color(0xFF64748B),
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 18),
              Container(
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: const Color(0xFFF0FDF4),
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: const Color(0xFFA7F3D0)),
                ),
                child: Column(
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Text(
                          lang == AppLanguage.id
                              ? 'Ukuran Cache Saat Ini:'
                              : (lang == AppLanguage.jv ? 'Ukuran Cache Saiki:' : 'Current Cache Size:'),
                          style: const TextStyle(fontSize: 13, color: Color(0xFF475569), fontWeight: FontWeight.w600),
                        ),
                        Text(
                          _isLoadingCache ? 'Menghitung...' : _cacheSize,
                          style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w900, color: Color(0xFF065F46)),
                        ),
                      ],
                    ),
                    const SizedBox(height: 10),
                    Text(
                      lang == AppLanguage.id
                          ? 'Membersihkan cache akan menghapus file sementara kamera, gambar pratinjau, dan data unduhan sementara untuk melegakan memori HP Anda.\n\nAkun, data lahan sawah, dan riwayat Anda tetap aman tersimpan.'
                          : (lang == AppLanguage.jv
                              ? 'Ngresiki cache bakal mbusak file sauntara kamera lan gambar pratinjau.\n\nAkun lan data sawah tetep aman.'
                              : 'Clearing cache removes temporary camera pictures and cached images.\n\nYour account and farm data remain safe.'),
                      style: const TextStyle(fontSize: 12.5, height: 1.45, color: Color(0xFF334155)),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 20),
              FilledButton.icon(
                onPressed: _isClearingCache
                    ? null
                    : () async {
                        Navigator.of(modalContext).pop();
                        await _executeCacheClear(context);
                      },
                icon: const Icon(Icons.delete_sweep_rounded, size: 20),
                label: Text(
                  lang == AppLanguage.id
                      ? 'Bersihkan Cache Sekarang'
                      : (lang == AppLanguage.jv ? 'Resiki Saiki' : 'Clear Cache Now'),
                  style: const TextStyle(fontSize: 15.5, fontWeight: FontWeight.w900),
                ),
                style: FilledButton.styleFrom(
                  backgroundColor: const Color(0xFF065F46),
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 16),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                  elevation: 1.5,
                ),
              ),
              const SizedBox(height: 10),
              TextButton(
                onPressed: () => Navigator.of(modalContext).pop(),
                child: Text(
                  lang == AppLanguage.id ? 'Batal' : (lang == AppLanguage.jv ? 'Batal' : 'Cancel'),
                  style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w700, color: Color(0xFF64748B)),
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  Future<void> _executeCacheClear(BuildContext context) async {
    final lang = ref.read(languageProvider);
    setState(() {
      _isClearingCache = true;
    });

    try {
      // 1. Bersihkan memory cache
      PaintingBinding.instance.imageCache.clear();
      PaintingBinding.instance.imageCache.clearLiveImages();

      // 2. Bersihkan temporary folder
      try {
        final tempDir = await getTemporaryDirectory();
        await _deleteDirectoryContents(tempDir);
      } catch (_) {}

      // 3. Bersihkan application cache directory
      try {
        final appCacheDir = await getApplicationCacheDirectory();
        await _deleteDirectoryContents(appCacheDir);
      } catch (_) {}

      // 4. Hitung ulang ukuran sebenarnya
      await _loadRealCacheSize();
    } finally {
      if (mounted) {
        setState(() {
          _isClearingCache = false;
        });
      }
    }

    if (!context.mounted) return;

    final msg = switch (lang) {
      AppLanguage.id => 'Cache aplikasi berhasil dibersihkan (Ukuran sekarang: $_cacheSize)',
      AppLanguage.jv => 'Cache aplikasi kasil dibersihake (Ukuran saiki: $_cacheSize)',
      AppLanguage.en => 'App cache cleared successfully (Current size: $_cacheSize)',
    };

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            const Icon(Icons.check_circle_outline_rounded, color: Colors.white, size: 20),
            const SizedBox(width: 10),
            Expanded(
              child: Text(
                msg,
                style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w700),
              ),
            ),
          ],
        ),
        backgroundColor: const Color(0xFF065F46),
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final auth = ref.watch(authControllerProvider);
    final currentLang = ref.watch(languageProvider);
    final notifSettings = ref.watch(notificationSettingsProvider);
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
                _buildUserHeaderCard(user, isBuyer, s),

                const SizedBox(height: 18),

                // 2. Section: Data Pengguna
                _buildSectionHeader(isBuyer ? s.buyerAccountTitle : s.userSectionTitle),
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
                  _buildSectionHeader(s.buyerPurchasesTitle),
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
                          title: s.buyerOrders,
                          subtitle: s.buyerOrdersSubtitle,
                          onTap: () => context.push('/buyer/orders'),
                        ),
                        const Divider(height: 1, color: HomeColors.borderSubtle, indent: 52),
                        _buildSettingsTile(
                          icon: Icons.shopping_cart_outlined,
                          title: s.buyerCart,
                          subtitle: s.buyerCartSubtitle,
                          onTap: () => context.push('/cart'),
                        ),
                        const Divider(height: 1, color: HomeColors.borderSubtle, indent: 52),
                        _buildSettingsTile(
                          icon: Icons.gavel_rounded,
                          title: s.buyerOffers,
                          subtitle: s.buyerOffersSubtitle,
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
                        valueText: currentLang.nativeName,
                        onTap: () => context.push('/profile/language'),
                      ),
                      const Divider(height: 1, color: HomeColors.borderSubtle, indent: 52),
                      // Notifikasi Setting (Interaktif dengan Modal Pengaturan)
                      _buildSettingsTile(
                        icon: Icons.notifications_none_rounded,
                        title: s.notifications,
                        subtitle: s.notificationsSubtitle,
                        valueText: notifSettings.pushEnabled
                            ? s.statusActive
                            : (currentLang == AppLanguage.id
                                ? 'Nonaktif'
                                : (currentLang == AppLanguage.jv ? 'Ora Aktif' : 'Disabled')),
                        onTap: () => _showNotificationSettingsSheet(context, s),
                      ),
                      const Divider(height: 1, color: HomeColors.borderSubtle, indent: 52),
                      // Bersihkan Cache & Penyimpanan (Real Disk & Memory Size)
                      _buildSettingsTile(
                        icon: Icons.cleaning_services_rounded,
                        title: currentLang == AppLanguage.id
                            ? 'Bersihkan Cache Aplikasi'
                            : (currentLang == AppLanguage.jv
                                ? 'Resiki Cache Aplikasi'
                                : 'Clear App Cache'),
                        subtitle: currentLang == AppLanguage.id
                            ? 'Kosongkan memori sementara untuk meringankan aplikasi'
                            : (currentLang == AppLanguage.jv
                                ? 'Kosongake memori sauntara ben entheng'
                                : 'Free up temporary storage for faster performance'),
                        valueText: _isLoadingCache
                            ? 'Menghitung...'
                            : (_isClearingCache ? 'Membersihkan...' : _cacheSize),
                        onTap: () => _clearAppCache(context, s),
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

  Widget _buildUserHeaderCard(dynamic user, bool isBuyer, AppStrings s) {
    final name = user?.name ?? s.roleFarmer;
    final email = user?.email ?? '';
    final phone = user?.phone ?? '';
    final rawRole = (user?.roleLabel ?? user?.role ?? '').toString().trim();
    String role;
    if (rawRole == 'true' || rawRole == 'false' || rawRole.isEmpty) {
      role = isBuyer ? s.roleBuyer : s.roleFarmer;
    } else if (rawRole.toLowerCase() == 'buyer' || rawRole.toLowerCase() == 'partner') {
      role = s.roleBuyer;
    } else if (rawRole.toLowerCase() == 'farmer') {
      role = s.roleFarmer;
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
