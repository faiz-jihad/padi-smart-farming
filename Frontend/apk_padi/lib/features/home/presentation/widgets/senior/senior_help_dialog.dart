import 'package:flutter/material.dart';
import 'package:padi/core/localization/app_language.dart';
import 'package:padi/features/home/presentation/tokens/senior_tokens.dart';

/// Dialog Panduan Penggunaan Beranda Khusus Lansia
/// Menggunakan teks besar, bahasa santun, dan ikon jelas.
class SeniorHelpDialog extends StatelessWidget {
  const SeniorHelpDialog({super.key, required this.lang});

  final AppLanguage lang;

  static Future<void> show(BuildContext context, AppLanguage lang) {
    return showDialog<void>(
      context: context,
      barrierDismissible: true,
      builder: (context) => SeniorHelpDialog(lang: lang),
    );
  }

  @override
  Widget build(BuildContext context) {
    final title = switch (lang) {
      AppLanguage.id => 'Bantuan & Petunjuk Beranda',
      AppLanguage.jv => 'Pituduh Migunakake Aplikasi',
      AppLanguage.en => 'Home Screen Guide',
    };

    final closeText = switch (lang) {
      AppLanguage.id => 'Saya Mengerti, Tutup',
      AppLanguage.jv => 'Kula Sampun Paham, Tutup',
      AppLanguage.en => 'Got it, Close',
    };

    return Dialog(
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(SeniorDimensions.cardRadius),
      ),
      backgroundColor: SeniorColors.surface,
      insetPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
      child: ConstrainedBox(
        constraints: const BoxConstraints(maxWidth: 480),
        child: Padding(
          padding: const EdgeInsets.all(SeniorSpacing.cardPadding),
          child: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                // Header Dialog
                Row(
                  children: [
                    Container(
                      width: 48,
                      height: 48,
                      decoration: BoxDecoration(
                        color: SeniorColors.lightGreenBg,
                        shape: BoxShape.circle,
                        border: Border.all(
                          color: SeniorColors.greenBorder,
                          width: 2,
                        ),
                      ),
                      child: const Icon(
                        Icons.help_outline_rounded,
                        color: SeniorColors.primaryGreen,
                        size: 30,
                      ),
                    ),
                    const SizedBox(width: 14),
                    Expanded(
                      child: Text(
                        title,
                        style: SeniorTypography.title.copyWith(fontSize: 22),
                      ),
                    ),
                  ],
                ),

                const SizedBox(height: SeniorSpacing.md),
                const Divider(color: SeniorColors.border, thickness: 1.5),
                const SizedBox(height: SeniorSpacing.md),

                // 4 Panduan Menu Utama
                _buildHelpItem(
                  icon: Icons.camera_alt_rounded,
                  iconColor: SeniorColors.primaryGreen,
                  iconBg: SeniorColors.lightGreenBg,
                  title: switch (lang) {
                    AppLanguage.id => '1. Periksa Daun Padi',
                    AppLanguage.jv => '1. Priksa Godhong Pari',
                    AppLanguage.en => '1. Scan Leaves',
                  },
                  description: switch (lang) {
                    AppLanguage.id =>
                      'Foto daun padi yang bercak atau menguning. Aplikasi akan mendeteksi penyakit dan memberi obat yang cocok.',
                    AppLanguage.jv =>
                      'Foto godhong pari sing ana bercak. Aplikasi bakal maringi pirsa penyakit lan obat sing trep.',
                    AppLanguage.en =>
                      'Take a photo of diseased leaves to get instant pest diagnosis and recommendations.',
                  },
                ),

                const SizedBox(height: SeniorSpacing.md),

                _buildHelpItem(
                  icon: Icons.edit_note_rounded,
                  iconColor: SeniorColors.blueAccent,
                  iconBg: SeniorColors.blueBg,
                  title: switch (lang) {
                    AppLanguage.id => '2. Catat Kegiatan Sawah',
                    AppLanguage.jv => '2. Cathet Pakaryan Sawah',
                    AppLanguage.en => '2. Log Farm Activity',
                  },
                  description: switch (lang) {
                    AppLanguage.id =>
                      'Catat tanggal pemupukan, penyemprotan, atau pengairan agar tidak lupa riwayat perawatan sawah.',
                    AppLanguage.jv =>
                      'Cathet tanggal rabuk, nyemprot, utawa ngilekake banyu supaya ora lali.',
                    AppLanguage.en =>
                      'Log fertilization, spraying, or irrigation to track field history.',
                  },
                ),

                const SizedBox(height: SeniorSpacing.md),

                _buildHelpItem(
                  icon: Icons.storefront_rounded,
                  iconColor: SeniorColors.amberAccent,
                  iconBg: SeniorColors.amberBg,
                  title: switch (lang) {
                    AppLanguage.id => '3. Pasar & Jual Gabah',
                    AppLanguage.jv => '3. Pasar & Sadean Gabah',
                    AppLanguage.en => '3. Marketplace & Grain Sale',
                  },
                  description: switch (lang) {
                    AppLanguage.id =>
                      'Lihat harga pasaran gabah hari ini atau pasang panen Anda agar langsung dibeli penebas/pembeli.',
                    AppLanguage.jv =>
                      'Mirsani rega gabah dina iki utawa sadean asil panen kanthi rega sing prayoga.',
                    AppLanguage.en =>
                      'Check current market grain prices and list your harvest for buyers.',
                  },
                ),

                const SizedBox(height: SeniorSpacing.md),

                _buildHelpItem(
                  icon: Icons.cloud_outlined,
                  iconColor: SeniorColors.tealAccent,
                  iconBg: SeniorColors.tealBg,
                  title: switch (lang) {
                    AppLanguage.id => '4. Cuaca & Jadwal Tanam',
                    AppLanguage.jv => '4. Hawa & Tanggalan Tanam',
                    AppLanguage.en => '4. Weather & Calendar',
                  },
                  description: switch (lang) {
                    AppLanguage.id =>
                      'Ketahui apakah cuaca hari ini aman untuk memupuk atau berpotensi hujan lebat.',
                    AppLanguage.jv =>
                      'Priksa hawa dina iki apa aman kanggo ngrabuk utawa arep udan deres.',
                    AppLanguage.en =>
                      'See weather forecasts and recommendations before field work.',
                  },
                ),

                const SizedBox(height: SeniorSpacing.md),

                // Panduan Suara
                Container(
                  padding: const EdgeInsets.all(SeniorSpacing.md),
                  decoration: BoxDecoration(
                    color: SeniorColors.surfaceMuted,
                    borderRadius: BorderRadius.circular(SeniorDimensions.buttonRadius),
                    border: Border.all(color: SeniorColors.borderStrong),
                  ),
                  child: Row(
                    children: [
                      const Icon(
                        Icons.mic_rounded,
                        color: SeniorColors.primaryGreen,
                        size: 32,
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              switch (lang) {
                                AppLanguage.id => 'Perintah Suara',
                                AppLanguage.jv => 'Prentah Swara',
                                AppLanguage.en => 'Voice Commands',
                              },
                              style: SeniorTypography.subtitle.copyWith(fontSize: 18),
                            ),
                            const SizedBox(height: 2),
                            Text(
                              switch (lang) {
                                AppLanguage.id =>
                                  'Tekan tombol mikrofon di pojok kanan atas, lalu katakan perintah seperti "Periksa daun" atau "Cek cuaca".',
                                AppLanguage.jv =>
                                  'Pencet tombol mic ing pojok dhuwur, lajeng ngendika "Priksa godhong" utawa "Cek hawa".',
                                AppLanguage.en =>
                                  'Tap the mic button and say "Scan leaves" or "Check weather".',
                              },
                              style: SeniorTypography.caption.copyWith(fontSize: 15),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),

                const SizedBox(height: SeniorSpacing.xl),

                // Tombol Tutup Besar (56px)
                SizedBox(
                  height: SeniorDimensions.buttonHeight,
                  child: ElevatedButton(
                    onPressed: () => Navigator.of(context).pop(),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: SeniorColors.primaryGreen,
                      foregroundColor: Colors.white,
                      elevation: 0,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(SeniorDimensions.buttonRadius),
                      ),
                    ),
                    child: Text(
                      closeText,
                      style: SeniorTypography.button,
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildHelpItem({
    required IconData icon,
    required Color iconColor,
    required Color iconBg,
    required String title,
    required String description,
  }) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Container(
          width: 44,
          height: 44,
          decoration: BoxDecoration(
            color: iconBg,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: iconColor.withValues(alpha: 0.3)),
          ),
          child: Icon(icon, color: iconColor, size: 26),
        ),
        const SizedBox(width: 14),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                title,
                style: SeniorTypography.subtitle.copyWith(fontSize: 18),
              ),
              const SizedBox(height: 2),
              Text(
                description,
                style: SeniorTypography.bodySecondary,
              ),
            ],
          ),
        ),
      ],
    );
  }
}
