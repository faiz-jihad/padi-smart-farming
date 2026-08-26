import 'package:flutter/material.dart';
import 'package:padi/features/farm/data/models/farm_model.dart';
import 'package:padi/features/home/presentation/tokens/home_tokens.dart';

class FarmStatsCard extends StatelessWidget {
  const FarmStatsCard({
    super.key,
    required this.farms,
    required this.onTapMap,
  });

  final List<FarmModel> farms;
  final VoidCallback onTapMap;

  @override
  Widget build(BuildContext context) {
    final totalAreaHa = farms.fold<double>(0, (sum, f) => sum + f.areaHa);
    final totalAreaM2 = (totalAreaHa * 10000).toInt();
    final activeCount = farms.where((f) => f.status.toLowerCase() == 'active').length;

    return Container(
      width: double.infinity,
      decoration: BoxDecoration(
        color: HomeColors.surface,
        borderRadius: BorderRadius.circular(HomeRadius.xl),
        border: Border.all(color: HomeColors.border),
        boxShadow: HomeShadows.subtle,
      ),
      child: Padding(
        padding: const EdgeInsets.all(HomeSpacing.cardPadding),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(6),
                      decoration: BoxDecoration(
                        color: HomeColors.lightGreen,
                        borderRadius: BorderRadius.circular(HomeRadius.sm),
                      ),
                      child: const Icon(
                        Icons.landscape_rounded,
                        color: HomeColors.primaryGreen,
                        size: 18,
                      ),
                    ),
                    const SizedBox(width: HomeSpacing.xs),
                    const Text(
                      'Ringkasan Lahan',
                      style: HomeTypography.cardTitle,
                    ),
                  ],
                ),
                InkWell(
                  onTap: onTapMap,
                  borderRadius: BorderRadius.circular(HomeRadius.pill),
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: HomeColors.surfaceMuted,
                      borderRadius: BorderRadius.circular(HomeRadius.pill),
                      border: Border.all(color: HomeColors.borderSubtle),
                    ),
                    child: const Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(
                          Icons.map_rounded,
                          color: HomeColors.primaryGreen,
                          size: 14,
                        ),
                        SizedBox(width: 4),
                        Text(
                          'Peta GIS',
                          style: TextStyle(
                            color: HomeColors.primaryGreen,
                            fontSize: 11,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: HomeSpacing.md),

            // 3 Key Stats
            Row(
              children: [
                Expanded(
                  child: _buildStatItem(
                    label: 'Total Petak',
                    value: '${farms.length}',
                    unit: 'lahan',
                    icon: Icons.grass_rounded,
                    color: HomeColors.primaryGreen,
                  ),
                ),
                Container(width: 1, height: 38, color: HomeColors.borderSubtle),
                Expanded(
                  child: _buildStatItem(
                    label: 'Total Luas',
                    value: totalAreaHa.toStringAsFixed(totalAreaHa == totalAreaHa.roundToDouble() ? 0 : 1),
                    unit: 'Ha',
                    subUnit: '≈ $totalAreaM2 m²',
                    icon: Icons.aspect_ratio_rounded,
                    color: HomeColors.harvestGold,
                  ),
                ),
                Container(width: 1, height: 38, color: HomeColors.borderSubtle),
                Expanded(
                  child: _buildStatItem(
                    label: 'Status Aktif',
                    value: '$activeCount / ${farms.length}',
                    unit: 'tanam',
                    icon: Icons.check_circle_outline_rounded,
                    color: HomeColors.emerald,
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStatItem({
    required String label,
    required String value,
    required String unit,
    String? subUnit,
    required IconData icon,
    required Color color,
  }) {
    return Column(
      children: [
        Text(
          label,
          style: const TextStyle(
            color: HomeColors.textSecondary,
            fontSize: 10.5,
            fontWeight: FontWeight.w600,
          ),
        ),
        RichText(
          textAlign: TextAlign.center,
          text: TextSpan(
            children: [
              TextSpan(
                text: value,
                style: const TextStyle(
                  color: HomeColors.textPrimary,
                  fontSize: 16,
                  fontWeight: FontWeight.w900,
                  letterSpacing: -0.3,
                ),
              ),
              if (unit.isNotEmpty) ...[
                const TextSpan(text: ' '),
                TextSpan(
                  text: unit,
                  style: const TextStyle(
                    color: HomeColors.textSecondary,
                    fontSize: 10.5,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ],
            ],
          ),
        ),
        if (subUnit != null) ...[
          const SizedBox(height: 1),
          Text(
            subUnit,
            style: const TextStyle(
              color: HomeColors.textSecondary,
              fontSize: 9,
              fontWeight: FontWeight.w500,
            ),
          ),
        ],
      ],
    );
  }
}
