import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:padi/core/localization/app_language.dart';
import 'package:padi/features/cultivation/data/models/crop_season_model.dart';
import 'package:padi/features/farm/data/models/farm_model.dart';
import 'package:padi/features/home/presentation/tokens/senior_tokens.dart';

/// Kartu Lahan Utama Ramah Lansia (Senior Farm Hero Card)
/// Menampilkan satu informasi utama secara fokus: nama lahan, luas,
/// status musim tanam ringkas dalam satu baris, dan satu tombol raksasa
/// "PERIKSA TANAMAN" (tinggi 60px) untuk kemudahan sentuhan lansia.
class SeniorFarmHeroCard extends ConsumerWidget {
  const SeniorFarmHeroCard({
    super.key,
    required this.farms,
    required this.seasons,
    required this.selectedIndex,
    required this.onFarmIndexChanged,
    required this.onFarmTap,
    required this.onAddFarmTap,
    required this.onScanTap,
  });

  final List<FarmModel> farms;
  final List<CropSeasonModel> seasons;
  final int selectedIndex;
  final ValueChanged<int> onFarmIndexChanged;
  final ValueChanged<FarmModel> onFarmTap;
  final VoidCallback onAddFarmTap;
  final VoidCallback onScanTap;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final lang = ref.watch(languageProvider);
    final s = AppStrings(lang);

    if (farms.isEmpty) {
      return _buildEmptyState(context, s, lang);
    }

    final farmIndex = selectedIndex.clamp(0, farms.length - 1);
    final currentFarm = farms[farmIndex];

    // Temukan musim tanam aktif untuk lahan ini
    final currentSeason = seasons.firstWhere(
      (sea) => sea.farmId == currentFarm.id && sea.status == 'active',
      orElse: () => seasons.firstWhere(
        (sea) => sea.farmId == currentFarm.id,
        orElse: () => seasons.isNotEmpty
            ? seasons.first
            : const CropSeasonModel(id: 0, farmId: 0),
      ),
    );

    final dayNumber = currentSeason.dayNumber ?? 45;
    final phase = s.mapGrowthPhase(dayNumber);

    final areaText = currentFarm.areaHa > 0
        ? '${currentFarm.areaHa.toStringAsFixed(currentFarm.areaHa == currentFarm.areaHa.roundToDouble() ? 0 : 1)} Ha'
        : '0 Ha';

    final locationText = _buildLocationText(currentFarm);

    final statusText = currentSeason.id > 0
        ? 'Hari ke-$dayNumber - $phase'
        : switch (lang) {
            AppLanguage.id => 'Belum ada musim aktif',
            AppLanguage.jv => 'Durung ana mangsa aktif',
            AppLanguage.en => 'No active season',
          };

    return Container(
      decoration: BoxDecoration(
        color: SeniorColors.primaryGreen,
        borderRadius: BorderRadius.circular(SeniorDimensions.cardRadius),
        gradient: const LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [
            SeniorColors.primaryGreen,
            SeniorColors.accentGreen,
            SeniorColors.primaryGreen,
          ],
        ),
        boxShadow: [
          BoxShadow(
            color: SeniorColors.primaryGreen.withValues(alpha: 0.25),
            blurRadius: 16,
            offset: const Offset(0, 6),
          ),
        ],
        border: Border.all(
          color: const Color(0xFF22C55E).withValues(alpha: 0.4),
          width: 1.5,
        ),
      ),
      padding: const EdgeInsets.all(SeniorSpacing.cardPadding),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Baris Atas: Pemilih Lahan (jika > 1) & Lokasi
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              // Badge Lokasi
              Flexible(
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Icon(
                      Icons.location_on_rounded,
                      color: Colors.white,
                      size: 20,
                    ),
                    const SizedBox(width: 6),
                    Flexible(
                      child: Text(
                        locationText,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 16,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                    ),
                  ],
                ),
              ),

              // Pemilih Sawah (Ganti Lahan)
              if (farms.length > 1)
                InkWell(
                  onTap: () => _showFarmPickerModal(context, farmIndex, lang),
                  borderRadius: BorderRadius.circular(
                    SeniorDimensions.pillRadius,
                  ),
                  child: Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 12,
                      vertical: 6,
                    ),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(
                        SeniorDimensions.pillRadius,
                      ),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text(
                          'Sawah ${farmIndex + 1}/${farms.length}',
                          style: const TextStyle(
                            color: SeniorColors.primaryGreen,
                            fontSize: 14,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                        const SizedBox(width: 4),
                        const Icon(
                          Icons.keyboard_arrow_down_rounded,
                          color: SeniorColors.primaryGreen,
                          size: 20,
                        ),
                      ],
                    ),
                  ),
                ),
            ],
          ),

          const SizedBox(height: SeniorSpacing.md),

          // Nama Lahan (24-26px w900)
          InkWell(
            onTap: () => onFarmTap(currentFarm),
            borderRadius: BorderRadius.circular(10),
            child: Row(
              children: [
                Expanded(
                  child: Text(
                    currentFarm.name,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 26,
                      fontWeight: FontWeight.w900,
                      letterSpacing: 0,
                    ),
                  ),
                ),
                const Icon(
                  Icons.arrow_forward_ios_rounded,
                  color: Colors.white70,
                  size: 20,
                ),
              ],
            ),
          ),

          const SizedBox(height: 6),

          // Info Luas Lahan (16px w600)
          Text(
            '${switch (lang) {
              AppLanguage.id => 'Luas Lahan',
              AppLanguage.jv => 'Jembar Sawah',
              AppLanguage.en => 'Field Area',
            }}: $areaText',
            style: const TextStyle(
              color: SeniorColors.textOnDarkMuted,
              fontSize: 16,
              fontWeight: FontWeight.w600,
            ),
          ),

          const SizedBox(height: SeniorSpacing.md),

          // Status Musim Tanam Ringkas (1 Baris Bersih)
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.14),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(
                color: Colors.white.withValues(alpha: 0.2),
                width: 1.2,
              ),
            ),
            child: Row(
              children: [
                const Icon(Icons.spa_rounded, color: Colors.white, size: 22),
                const SizedBox(width: 10),
                Expanded(
                  child: Text(
                    statusText,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 16,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                ),
              ],
            ),
          ),

          const SizedBox(height: SeniorSpacing.lg),

          // Tombol Raksasa Utama: PERIKSA TANAMAN (Tinggi 60px)
          SizedBox(
            height: SeniorDimensions.heroButtonHeight,
            child: ElevatedButton.icon(
              onPressed: onScanTap,
              icon: const Icon(Icons.camera_alt_rounded, size: 30),
              label: Text(
                switch (lang) {
                  AppLanguage.id => 'CEK DAUN PADI',
                  AppLanguage.jv => 'PRIKSA TANDURAN',
                  AppLanguage.en => 'SCAN CROPS NOW',
                },
                style: SeniorTypography.button.copyWith(
                  color: SeniorColors.primaryGreen,
                  fontSize: 18,
                  fontWeight: FontWeight.w900,
                  letterSpacing: 0,
                ),
              ),
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.white,
                foregroundColor: SeniorColors.primaryGreen,
                elevation: 4,
                shadowColor: Colors.black.withValues(alpha: 0.3),
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

  Widget _buildEmptyState(
    BuildContext context,
    AppStrings s,
    AppLanguage lang,
  ) {
    return Container(
      decoration: BoxDecoration(
        color: SeniorColors.surface,
        borderRadius: BorderRadius.circular(SeniorDimensions.cardRadius),
        border: Border.all(color: SeniorColors.border, width: 2),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.04),
            blurRadius: 10,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      padding: const EdgeInsets.all(SeniorSpacing.cardPadding),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Row(
            children: [
              Container(
                width: 52,
                height: 52,
                decoration: BoxDecoration(
                  color: SeniorColors.lightGreenBg,
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(
                    color: SeniorColors.greenBorder,
                    width: 1.5,
                  ),
                ),
                child: const Icon(
                  Icons.landscape_rounded,
                  color: SeniorColors.primaryGreen,
                  size: 32,
                ),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(switch (lang) {
                      AppLanguage.id => 'Belum Ada Data Lahan',
                      AppLanguage.jv => 'Durung Wonten Data Sawah',
                      AppLanguage.en => 'No Farm Added Yet',
                    }, style: SeniorTypography.title.copyWith(fontSize: 22)),
                    const SizedBox(height: 2),
                    Text(switch (lang) {
                      AppLanguage.id =>
                        'Daftarkan sawah Anda untuk pantau kesehatan',
                      AppLanguage.jv => 'Daftaraken sawah panjenengan',
                      AppLanguage.en =>
                        'Register your rice field to start monitoring',
                    }, style: SeniorTypography.bodySecondary),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: SeniorSpacing.lg),
          SizedBox(
            height: SeniorDimensions.heroButtonHeight,
            child: ElevatedButton.icon(
              onPressed: onAddFarmTap,
              icon: const Icon(Icons.add_location_alt_rounded, size: 28),
              label: Text(switch (lang) {
                AppLanguage.id => 'Tambah Sawah Saya',
                AppLanguage.jv => 'TAMBAH SAWAH KULA',
                AppLanguage.en => 'ADD MY FARM',
              }, style: SeniorTypography.button),
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

  String _buildLocationText(FarmModel farm) {
    final district = farm.district?.name;
    final regency = farm.regency?.name;
    if (district != null && district.isNotEmpty) {
      if (regency != null && regency.isNotEmpty) {
        return '$district, $regency';
      }
      return district;
    }
    return farm.name;
  }

  void _showFarmPickerModal(
    BuildContext context,
    int currentIndex,
    AppLanguage lang,
  ) {
    showModalBottomSheet<void>(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      backgroundColor: SeniorColors.surface,
      builder: (ctx) {
        return SafeArea(
          child: Padding(
            padding: const EdgeInsets.symmetric(
              horizontal: SeniorSpacing.screenHorizontal,
              vertical: SeniorSpacing.cardPadding,
            ),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Text(switch (lang) {
                  AppLanguage.id => 'Pilih Lahan Sawah',
                  AppLanguage.jv => 'Pilih Sawah Panjenengan',
                  AppLanguage.en => 'Select Rice Field',
                }, style: SeniorTypography.title),
                const SizedBox(height: SeniorSpacing.md),
                ...List.generate(farms.length, (i) {
                  final f = farms[i];
                  final isSelected = i == currentIndex;
                  return Padding(
                    padding: const EdgeInsets.only(bottom: 10),
                    child: InkWell(
                      onTap: () {
                        onFarmIndexChanged(i);
                        Navigator.of(ctx).pop();
                      },
                      borderRadius: BorderRadius.circular(
                        SeniorDimensions.buttonRadius,
                      ),
                      child: Container(
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(
                          color: isSelected
                              ? SeniorColors.lightGreenBg
                              : SeniorColors.surfaceMuted,
                          borderRadius: BorderRadius.circular(
                            SeniorDimensions.buttonRadius,
                          ),
                          border: Border.all(
                            color: isSelected
                                ? SeniorColors.primaryGreen
                                : SeniorColors.border,
                            width: isSelected ? 2 : 1.5,
                          ),
                        ),
                        child: Row(
                          children: [
                            Icon(
                              isSelected
                                  ? Icons.radio_button_checked_rounded
                                  : Icons.radio_button_off_rounded,
                              color: isSelected
                                  ? SeniorColors.primaryGreen
                                  : SeniorColors.textSecondary,
                              size: 26,
                            ),
                            const SizedBox(width: 14),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    f.name,
                                    style: SeniorTypography.subtitle.copyWith(
                                      color: isSelected
                                          ? SeniorColors.primaryGreen
                                          : SeniorColors.textPrimary,
                                    ),
                                  ),
                                  Text(
                                    '${f.areaHa} Ha - ${_buildLocationText(f)}',
                                    style: SeniorTypography.bodySecondary,
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                  );
                }),
                const SizedBox(height: 8),
                SizedBox(
                  height: SeniorDimensions.buttonHeight,
                  child: OutlinedButton.icon(
                    onPressed: () {
                      Navigator.of(ctx).pop();
                      onAddFarmTap();
                    },
                    icon: const Icon(Icons.add_rounded, size: 28),
                    label: Text(
                      switch (lang) {
                        AppLanguage.id => 'Tambah Lahan Baru',
                        AppLanguage.jv => '+ Tambah Sawah Anyar',
                        AppLanguage.en => '+ Add New Field',
                      },
                      style: SeniorTypography.subtitle.copyWith(
                        color: SeniorColors.primaryGreen,
                      ),
                    ),
                    style: OutlinedButton.styleFrom(
                      side: const BorderSide(
                        color: SeniorColors.primaryGreen,
                        width: 2,
                      ),
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
          ),
        );
      },
    );
  }
}
