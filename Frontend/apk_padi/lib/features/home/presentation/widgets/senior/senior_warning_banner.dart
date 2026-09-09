import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:padi/core/localization/app_language.dart';
import 'package:padi/features/home/presentation/tokens/senior_tokens.dart';

/// Banner Peringatan Hama & Penyakit Ramah Lansia (Senior Warning Banner)
/// Menggantikan CommunityAlertCard yang rumit menjadi satu banner peringatan
/// dengan kontras tinggi, teks to-the-point, dan tombol aksi periksa daun yang besar.
class SeniorWarningBanner extends ConsumerWidget {
  const SeniorWarningBanner({
    super.key,
    required this.diseaseName,
    this.distanceKm,
    this.locationName,
    this.farmerAdvice,
    required this.onScanTap,
  });

  final String diseaseName;
  final double? distanceKm;
  final String? locationName;
  final String? farmerAdvice;
  final VoidCallback onScanTap;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final lang = ref.watch(languageProvider);

    final headline = switch (lang) {
      AppLanguage.id => 'Peringatan Penyakit Dekat',
      AppLanguage.jv => 'WASPADA: $diseaseName Katon!',
      AppLanguage.en => 'ALERT: $diseaseName Detected!',
    };

    final distanceStr = distanceKm != null
        ? '${distanceKm!.toStringAsFixed(1)} km'
        : null;

    final desc = distanceStr != null
        ? switch (lang) {
            AppLanguage.id =>
              '$diseaseName dilaporkan dalam radius $distanceStr dari sawah Anda. Periksa daun padi hari ini.',
            AppLanguage.jv =>
              'Lapuran omo $diseaseName aktif jarak $distanceStr saking sawah panjenengan. Mangga enggal priksa godhong.',
            AppLanguage.en =>
              'Active $diseaseName report detected $distanceStr from your farm. Check leaves immediately.',
          }
        : switch (lang) {
            AppLanguage.id =>
              '$diseaseName dilaporkan di sekitar area sawah Anda. Periksa daun padi hari ini.',
            AppLanguage.jv =>
              'Lapuran omo $diseaseName katon ing sekitar sawah panjenengan. Mangga priksa godhong.',
            AppLanguage.en =>
              'Active $diseaseName report detected near your field. Check leaves immediately.',
          };

    return Container(
      decoration: BoxDecoration(
        color: SeniorColors.lightGreenBg,
        borderRadius: BorderRadius.circular(SeniorDimensions.cardRadius),
        border: Border.all(color: SeniorColors.borderStrong, width: 2),
        boxShadow: [
          BoxShadow(
            color: SeniorColors.primaryGreen.withValues(alpha: 0.10),
            blurRadius: 12,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      padding: const EdgeInsets.all(SeniorSpacing.cardPadding),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Header Baris: Ikon Peringatan 34px + Judul Waspada
          Row(
            crossAxisAlignment: CrossAxisAlignment.center,
            children: [
              Container(
                width: 48,
                height: 48,
                decoration: const BoxDecoration(
                  color: SeniorColors.primaryGreen,
                  shape: BoxShape.circle,
                ),
                child: const Icon(
                  Icons.warning_amber_rounded,
                  color: Colors.white,
                  size: SeniorDimensions.iconMedium,
                ),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Text(
                  headline,
                  style: SeniorTypography.subtitle.copyWith(
                    color: SeniorColors.primaryGreen,
                    fontSize: 20,
                    fontWeight: FontWeight.w900,
                  ),
                ),
              ),
            ],
          ),

          const SizedBox(height: SeniorSpacing.md),

          // Deskripsi Peringatan Sederhana (16px w600)
          Text(
            desc,
            style: SeniorTypography.body.copyWith(
              color: SeniorColors.textPrimary,
              fontSize: 17,
              height: 1.45,
            ),
          ),

          const SizedBox(height: SeniorSpacing.lg),

          // Tombol Periksa Sekarang (Tinggi 56px)
          SizedBox(
            height: SeniorDimensions.buttonHeight,
            child: ElevatedButton.icon(
              onPressed: onScanTap,
              icon: const Icon(Icons.camera_alt_rounded, size: 26),
              label: Text(switch (lang) {
                AppLanguage.id => 'Periksa Daun Sekarang',
                AppLanguage.jv => 'Priksa Godhong Saiki',
                AppLanguage.en => 'Check Leaves Now',
              }, style: SeniorTypography.button.copyWith(fontSize: 17)),
              style: ElevatedButton.styleFrom(
                backgroundColor: SeniorColors.primaryGreen,
                foregroundColor: Colors.white,
                elevation: 2,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(
                    SeniorDimensions.buttonRadius,
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
