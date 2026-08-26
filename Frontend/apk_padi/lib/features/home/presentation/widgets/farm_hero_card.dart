import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:padi/core/localization/app_language.dart';
import 'package:padi/features/cultivation/data/models/crop_season_model.dart';
import 'package:padi/features/farm/data/models/farm_model.dart';
import 'package:padi/features/home/presentation/tokens/home_tokens.dart';

class FarmHeroCard extends ConsumerWidget {
  const FarmHeroCard({
    super.key,
    required this.farms,
    required this.seasons,
    required this.selectedIndex,
    required this.onFarmIndexChanged,
    required this.onFarmTap,
    required this.onAddFarmTap,
  });

  final List<FarmModel> farms;
  final List<CropSeasonModel> seasons;
  final int selectedIndex;
  final ValueChanged<int> onFarmIndexChanged;
  final ValueChanged<FarmModel> onFarmTap;
  final VoidCallback onAddFarmTap;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final lang = ref.watch(languageProvider);
    final s = AppStrings(lang);

    if (farms.isEmpty) {
      return _buildEmptyFarmCard(context, s);
    }

    final farmIndex = selectedIndex.clamp(0, farms.length - 1);
    final currentFarm = farms[farmIndex];

    // Find active season for this farm
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
    final healthText = s.mapCropHealth('healthy');
    final locationText = _buildLocationText(currentFarm);

    final areaLabel = switch (lang) {
      AppLanguage.id => 'Luas Lahan',
      AppLanguage.jv => 'Jembar Sawah',
      AppLanguage.en => 'Farm Area',
    };

    return Container(
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(HomeRadius.xxl),
        boxShadow: [
          BoxShadow(
            color: HomeColors.deepGreen.withOpacity(0.28),
            blurRadius: 22,
            offset: const Offset(0, 8),
          ),
        ],
        image: const DecorationImage(
          image: AssetImage('assets/images/onboarding_1.jpeg'),
          fit: BoxFit.cover,
        ),
      ),
      child: Material(
        color: Colors.transparent,
        borderRadius: BorderRadius.circular(HomeRadius.xxl),
        child: InkWell(
          onTap: () => onFarmTap(currentFarm),
          borderRadius: BorderRadius.circular(HomeRadius.xxl),
          child: Container(
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(HomeRadius.xxl),
              gradient: LinearGradient(
                begin: Alignment.topRight,
                end: Alignment.bottomLeft,
                colors: [
                  const Color(0xFF042F1E).withOpacity(0.85),
                  const Color(0xFF075E3B).withOpacity(0.92),
                  const Color(0xFF0E4B31).withOpacity(0.96),
                ],
              ),
            ),
            padding: const EdgeInsets.all(HomeSpacing.cardPadding),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                // Top Row: Location Badge + Multi-farm Switcher
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: Colors.white.withOpacity(0.20),
                        borderRadius: BorderRadius.circular(HomeRadius.pill),
                        border: Border.all(
                          color: Colors.white.withOpacity(0.25),
                        ),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Icon(
                            Icons.location_on_rounded,
                            color: Color(0xFFFDE68A),
                            size: 13,
                          ),
                          const SizedBox(width: 4),
                          ConstrainedBox(
                            constraints: const BoxConstraints(maxWidth: 140),
                            child: Text(
                              locationText,
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: const TextStyle(
                                color: Colors.white,
                                fontSize: 11,
                                fontWeight: FontWeight.w700,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),

                    // Multi-farm Selector Pill
                    if (farms.length > 1)
                      InkWell(
                        onTap: () => _showFarmPickerModal(context, farmIndex),
                        borderRadius: BorderRadius.circular(HomeRadius.pill),
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
                          decoration: BoxDecoration(
                            color: const Color(0xFFFBBF24),
                            borderRadius: BorderRadius.circular(HomeRadius.pill),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Text(
                                '${farmIndex + 1}/${farms.length} Lahan',
                                style: const TextStyle(
                                  color: Color(0xFF042F1E),
                                  fontSize: 11,
                                  fontWeight: FontWeight.w800,
                                ),
                              ),
                              const SizedBox(width: 3),
                              const Icon(
                                Icons.keyboard_arrow_down_rounded,
                                color: Color(0xFF042F1E),
                                size: 16,
                              ),
                            ],
                          ),
                        ),
                      ),
                  ],
                ),

                const SizedBox(height: HomeSpacing.md),

                // Farm Name with Fast Switch Arrow
                Row(
                  children: [
                    Expanded(
                      child: Text(
                        currentFarm.name,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 22,
                          fontWeight: FontWeight.w900,
                          letterSpacing: -0.4,
                          height: 1.15,
                        ),
                      ),
                    ),
                    if (farms.length > 1) ...[
                      IconButton(
                        visualDensity: VisualDensity.compact,
                        icon: const Icon(Icons.arrow_forward_ios_rounded, color: Colors.white70, size: 14),
                        onPressed: () {
                          final nextIndex = (farmIndex + 1) % farms.length;
                          onFarmIndexChanged(nextIndex);
                        },
                      ),
                    ],
                  ],
                ),
                const SizedBox(height: 4),

                // Area and Coordinates
                Text(
                  '$areaLabel: ${currentFarm.areaHa.toStringAsFixed(currentFarm.areaHa == currentFarm.areaHa.roundToDouble() ? 0 : 1)} Ha (≈ ${(currentFarm.areaHa * 10000).toInt()} m²)',
                  style: TextStyle(
                    color: Colors.white.withOpacity(0.88),
                    fontSize: 12.5,
                    fontWeight: FontWeight.w600,
                  ),
                ),

                const SizedBox(height: HomeSpacing.md),

                // Phase & Health Status Glass Chip
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                  decoration: BoxDecoration(
                    color: Colors.white.withOpacity(0.16),
                    borderRadius: BorderRadius.circular(HomeRadius.md),
                    border: Border.all(color: Colors.white.withOpacity(0.22)),
                  ),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      // Health Status
                      Row(
                        children: [
                          const Icon(
                            Icons.check_circle_rounded,
                            color: Color(0xFF4ADE80),
                            size: 15,
                          ),
                          const SizedBox(width: 6),
                          Text(
                            healthText,
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 12,
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                        ],
                      ),

                      // Phase
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                        decoration: BoxDecoration(
                          color: Colors.white.withOpacity(0.20),
                          borderRadius: BorderRadius.circular(HomeRadius.pill),
                        ),
                        child: Text(
                          phase,
                          style: const TextStyle(
                            color: Color(0xFFFDE68A),
                            fontSize: 11,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),

                const SizedBox(height: HomeSpacing.md),

                // Bottom Row: Add Farm CTA & Multi-farm dots indicator
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    // Add Farm Action
                    InkWell(
                      onTap: onAddFarmTap,
                      borderRadius: BorderRadius.circular(HomeRadius.pill),
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: Colors.white.withOpacity(0.18),
                          borderRadius: BorderRadius.circular(HomeRadius.pill),
                          border: Border.all(color: Colors.white.withOpacity(0.25)),
                        ),
                        child: const Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(Icons.add_rounded, color: Colors.white, size: 14),
                            SizedBox(width: 4),
                            Text(
                              'Tambah Lahan',
                              style: TextStyle(
                                color: Colors.white,
                                fontSize: 11,
                                fontWeight: FontWeight.w700,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),

                    // Multi-farm indicator dots
                    if (farms.length > 1)
                      Row(
                        mainAxisSize: MainAxisSize.min,
                        children: List.generate(farms.length, (index) {
                          final isSelected = index == farmIndex;
                          return GestureDetector(
                            onTap: () => onFarmIndexChanged(index),
                            child: AnimatedContainer(
                              duration: const Duration(milliseconds: 200),
                              margin: const EdgeInsets.symmetric(horizontal: 3),
                              width: isSelected ? 18 : 6,
                              height: 6,
                              decoration: BoxDecoration(
                                color: isSelected
                                    ? const Color(0xFFFBBF24)
                                    : Colors.white.withOpacity(0.4),
                                borderRadius: BorderRadius.circular(HomeRadius.pill),
                              ),
                            ),
                          );
                        }),
                      ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  void _showFarmPickerModal(BuildContext context, int currentIdx) {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) {
        return SafeArea(
          child: Padding(
            padding: const EdgeInsets.fromLTRB(18, 16, 18, 20),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text(
                      'Pilih Lahan Sawah',
                      style: TextStyle(
                        fontSize: 17,
                        fontWeight: FontWeight.w900,
                        color: Color(0xFF17251E),
                      ),
                    ),
                    IconButton(
                      icon: const Icon(Icons.close_rounded),
                      onPressed: () => Navigator.pop(ctx),
                    ),
                  ],
                ),
                const SizedBox(height: 6),
                const Text(
                  'Pilih sawah aktif untuk memantau siklus tanam, cuaca, dan kesehatan padi:',
                  style: TextStyle(fontSize: 12, color: Color(0xFF68766E)),
                ),
                const SizedBox(height: 14),
                Flexible(
                  child: ListView.separated(
                    shrinkWrap: true,
                    itemCount: farms.length,
                    separatorBuilder: (_, __) => const SizedBox(height: 8),
                    itemBuilder: (c, idx) {
                      final farm = farms[idx];
                      final isSel = idx == currentIdx;
                      return Container(
                        decoration: BoxDecoration(
                          color: isSel ? HomeColors.lightGreen : const Color(0xFFF9FAF8),
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(
                            color: isSel ? HomeColors.primaryGreen : const Color(0xFFE5ECE3),
                            width: isSel ? 1.5 : 1,
                          ),
                        ),
                        child: ListTile(
                          contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 2),
                          leading: Container(
                            width: 38,
                            height: 38,
                            decoration: BoxDecoration(
                              color: isSel ? HomeColors.primaryGreen : const Color(0xFFE5ECE3),
                              shape: BoxShape.circle,
                            ),
                            child: Icon(
                              Icons.landscape_rounded,
                              color: isSel ? Colors.white : HomeColors.primaryGreen,
                              size: 20,
                            ),
                          ),
                          title: Text(
                            farm.name,
                            style: TextStyle(
                              fontSize: 14,
                              fontWeight: FontWeight.w800,
                              color: isSel ? HomeColors.deepGreen : const Color(0xFF17251E),
                            ),
                          ),
                          subtitle: Text(
                            '${farm.areaHa.toStringAsFixed(2)} Ha • ${_buildLocationText(farm)}',
                            style: const TextStyle(fontSize: 11.5, color: Color(0xFF68766E)),
                          ),
                          trailing: isSel
                              ? const Icon(Icons.check_circle_rounded, color: HomeColors.primaryGreen)
                              : const Icon(Icons.radio_button_off_rounded, color: Color(0xFFB0BDB5)),
                          onTap: () {
                            onFarmIndexChanged(idx);
                            Navigator.pop(ctx);
                          },
                        ),
                      );
                    },
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  String _buildLocationText(FarmModel farm) {
    final village = farm.village?.name;
    final district = farm.district?.name;
    final regency = farm.regency?.name;

    if (village != null && village.isNotEmpty) {
      if (district != null && district.isNotEmpty) {
        return '$village, $district';
      }
      return village;
    }

    if (district != null && district.isNotEmpty) {
      if (regency != null && regency.isNotEmpty) {
        return '$district, $regency';
      }
      return district;
    }

    if (regency != null && regency.isNotEmpty) {
      return regency;
    }

    return 'Indonesia';
  }

  Widget _buildEmptyFarmCard(BuildContext context, AppStrings s) {
    return Container(
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(HomeRadius.xxl),
        color: HomeColors.surface,
        border: Border.all(color: HomeColors.border),
      ),
      padding: const EdgeInsets.all(HomeSpacing.cardPadding),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('Lahan Aktif', style: HomeTypography.cardTitle),
          const SizedBox(height: HomeSpacing.xs),
          const Text(
            'Belum ada data lahan yang terdaftar. Daftarkan petak sawah Anda untuk mulai memantau pertumbuhan dan kalender tanam.',
            style: HomeTypography.supporting,
          ),
          const SizedBox(height: HomeSpacing.md),
          FilledButton.icon(
            onPressed: onAddFarmTap,
            icon: const Icon(Icons.add_rounded, size: 18),
            label: Text(s.addFarm),
            style: FilledButton.styleFrom(
              backgroundColor: HomeColors.primaryGreen,
              foregroundColor: Colors.white,
            ),
          ),
        ],
      ),
    );
  }
}
