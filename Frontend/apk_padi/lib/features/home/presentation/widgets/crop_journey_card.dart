import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:padi/core/localization/app_language.dart';
import 'package:padi/features/cultivation/data/models/crop_season_model.dart';
import 'package:padi/features/farm/data/models/farm_model.dart';
import 'package:padi/features/home/presentation/tokens/home_tokens.dart';

class CropJourneyCard extends StatelessWidget {
  const CropJourneyCard({
    super.key,
    required this.season,
    this.farms,
    this.selectedFarm,
    this.onSelectFarm,
    required this.onTapTimeline,
  });

  final CropSeasonModel? season;
  final List<FarmModel>? farms;
  final FarmModel? selectedFarm;
  final ValueChanged<FarmModel>? onSelectFarm;
  final VoidCallback onTapTimeline;

  @override
  Widget build(BuildContext context) {
    return Consumer(
      builder: (context, ref, _) {
        final lang = ref.watch(languageProvider);
        final s = AppStrings(lang);
        final metrics = _SeasonJourneyMetrics.fromSeason(season);

    return Container(
      decoration: BoxDecoration(
        color: HomeColors.surface,
        borderRadius: BorderRadius.circular(HomeRadius.xl),
        border: Border.all(color: HomeColors.border),
        boxShadow: HomeShadows.subtle,
      ),
      child: Material(
        color: Colors.transparent,
        borderRadius: BorderRadius.circular(HomeRadius.xl),
        child: InkWell(
          onTap: onTapTimeline,
          borderRadius: BorderRadius.circular(HomeRadius.xl),
          child: Padding(
            padding: const EdgeInsets.all(HomeSpacing.cardPadding),
            child: metrics == null
                ? _buildNoSeasonState(context, s)
                : _buildMeasuredJourney(context, metrics, s, lang),
          ),
        ),
      ),
    );
      },
    );
  }

  Widget _buildNoSeasonState(BuildContext context, AppStrings s) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: HomeColors.lightGreen,
                borderRadius: BorderRadius.circular(HomeRadius.sm),
              ),
              child: const Icon(
                Icons.timeline_rounded,
                color: HomeColors.primaryGreen,
                size: 20,
              ),
            ),
            const SizedBox(width: HomeSpacing.sm),
            Expanded(
              child: Text(s.cropJourneyTitle, style: HomeTypography.cardTitle),
            ),
            if (farms != null && farms!.length > 1 && selectedFarm != null) ...[
              InkWell(
                onTap: () => _showFarmPickerModal(context),
                borderRadius: BorderRadius.circular(HomeRadius.pill),
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: HomeColors.lightGreen,
                    borderRadius: BorderRadius.circular(HomeRadius.pill),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(
                        selectedFarm?.name ?? s.navFarms,
                        style: const TextStyle(
                          color: HomeColors.primaryGreen,
                          fontSize: 11,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                      const Icon(Icons.arrow_drop_down_rounded, size: 16, color: HomeColors.primaryGreen),
                    ],
                  ),
                ),
              ),
            ],
          ],
        ),
        const SizedBox(height: HomeSpacing.xs),
        Text(
          s.noActiveSeasonDesc,
          maxLines: 3,
          overflow: TextOverflow.ellipsis,
          style: HomeTypography.supporting,
        ),
      ],
    );
  }

  Widget _buildMeasuredJourney(BuildContext context, _SeasonJourneyMetrics metrics, AppStrings s, AppLanguage lang) {
    final startLabel = switch (lang) {
      AppLanguage.id => 'Mulai',
      AppLanguage.jv => 'Wiwit',
      AppLanguage.en => 'Start',
    };
    final harvestLabel = switch (lang) {
      AppLanguage.id => 'Estimasi panen',
      AppLanguage.jv => 'Prakiraan panen',
      AppLanguage.en => 'Est. harvest',
    };

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
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
                Icons.timeline_rounded,
                color: HomeColors.primaryGreen,
                size: 18,
              ),
            ),
            const SizedBox(width: HomeSpacing.xs),
            Expanded(
              child: Text(
                s.cropJourneyTitle,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: HomeTypography.cardTitle,
              ),
            ),
            const SizedBox(width: HomeSpacing.xs),
            _buildBadge(metrics.dayLabel),
          ],
        ),
        if (farms != null && farms!.length > 1 && selectedFarm != null) ...[
          const SizedBox(height: 6),
          InkWell(
            onTap: () => _showFarmPickerModal(context),
            borderRadius: BorderRadius.circular(HomeRadius.pill),
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
              decoration: BoxDecoration(
                color: HomeColors.lightGreen,
                borderRadius: BorderRadius.circular(HomeRadius.pill),
                border: Border.all(color: HomeColors.primaryGreen.withOpacity(0.2)),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Icon(Icons.landscape_rounded, size: 13, color: HomeColors.primaryGreen),
                  const SizedBox(width: 5),
                  Flexible(
                    child: Text(
                      selectedFarm?.name ?? 'Lahan',
                      style: const TextStyle(
                        color: HomeColors.deepGreen,
                        fontSize: 11.5,
                        fontWeight: FontWeight.w800,
                      ),
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                  const SizedBox(width: 3),
                  const Icon(Icons.keyboard_arrow_down_rounded, size: 15, color: HomeColors.primaryGreen),
                ],
              ),
            ),
          ),
        ],
        const SizedBox(height: HomeSpacing.sm),
        Row(
          children: [
            Expanded(
              child: Text(
                metrics.stage.name,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(
                  color: HomeColors.textPrimary,
                  fontSize: 14,
                  fontWeight: FontWeight.w900,
                ),
              ),
            ),
            Text(
              '${metrics.progressPercent}%',
              style: const TextStyle(
                color: HomeColors.primaryGreen,
                fontSize: 13,
                fontWeight: FontWeight.w900,
              ),
            ),
          ],
        ),
        const SizedBox(height: 8),
        ClipRRect(
          borderRadius: BorderRadius.circular(HomeRadius.pill),
          child: LinearProgressIndicator(
            value: metrics.progress,
            minHeight: 8,
            backgroundColor: HomeColors.surfaceMuted,
            valueColor: const AlwaysStoppedAnimation<Color>(
              HomeColors.primaryGreen,
            ),
          ),
        ),
        const SizedBox(height: HomeSpacing.md),
        Row(
          children: [
            for (final stage in _GrowthStage.values) ...[
              _buildStageNode(
                stage: stage,
                currentStage: metrics.stage,
                progress: metrics.progress,
              ),
              if (stage != _GrowthStage.values.last)
                _buildStageLine(
                  isCompleted: metrics.progress >= 0.99 ||
                      metrics.stage.index > stage.index,
                ),
            ],
          ],
        ),
        const SizedBox(height: HomeSpacing.sm),
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 9),
          decoration: BoxDecoration(
            color: HomeColors.surfaceMuted,
            borderRadius: BorderRadius.circular(HomeRadius.sm),
          ),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Icon(
                Icons.insights_rounded,
                color: HomeColors.primaryGreen,
                size: 16,
              ),
              const SizedBox(width: 7),
              Expanded(
                child: Text(
                  metrics.recommendation,
                  maxLines: 3,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(
                    color: HomeColors.textSecondary,
                    fontSize: 11.5,
                    fontWeight: FontWeight.w600,
                    height: 1.35,
                  ),
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 8),
        Row(
          children: [
            Expanded(
              child: _buildMeasuredValue(
                label: startLabel,
                value: metrics.startLabel,
              ),
            ),
            const SizedBox(width: 8),
            Expanded(
              child: _buildMeasuredValue(
                label: harvestLabel,
                value: metrics.harvestLabel,
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildBadge(String label) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
      decoration: BoxDecoration(
        color: HomeColors.lightGreen,
        borderRadius: BorderRadius.circular(HomeRadius.pill),
      ),
      child: Text(
        label,
        style: const TextStyle(
          color: HomeColors.primaryGreen,
          fontSize: 11,
          fontWeight: FontWeight.w800,
        ),
      ),
    );
  }

  Widget _buildMeasuredValue({
    required String label,
    required String value,
  }) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(HomeRadius.sm),
        border: Border.all(color: HomeColors.borderSubtle),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            label,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: const TextStyle(
              color: HomeColors.textSecondary,
              fontSize: 10,
              fontWeight: FontWeight.w600,
            ),
          ),
          const SizedBox(height: 2),
          Text(
            value,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: const TextStyle(
              color: HomeColors.textPrimary,
              fontSize: 11.5,
              fontWeight: FontWeight.w800,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStageNode({
    required _GrowthStage stage,
    required _GrowthStage currentStage,
    required double progress,
  }) {
    final isSeasonFinished = progress >= 0.99;
    final isCompleted = isSeasonFinished || currentStage.index > stage.index;
    final isCurrent = !isSeasonFinished && currentStage == stage;

    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        Container(
          width: 28,
          height: 28,
          decoration: BoxDecoration(
            color: isCompleted
                ? HomeColors.primaryGreen
                : isCurrent
                    ? HomeColors.lightGreen
                    : HomeColors.surfaceMuted,
            shape: BoxShape.circle,
            border: Border.all(
              color: isCompleted || isCurrent
                  ? HomeColors.primaryGreen
                  : HomeColors.borderSubtle,
              width: isCurrent ? 2 : 1.2,
            ),
          ),
          child: Center(
            child: isCompleted
                ? const Icon(Icons.check_rounded, color: Colors.white, size: 16)
                : isCurrent
                    ? Container(
                        width: 8,
                        height: 8,
                        decoration: const BoxDecoration(
                          color: HomeColors.primaryGreen,
                          shape: BoxShape.circle,
                        ),
                      )
                    : null,
          ),
        ),
        const SizedBox(height: 4),
        Text(
          stage.shortName,
          style: TextStyle(
            color: isCurrent
                ? HomeColors.primaryGreen
                : isCompleted
                    ? HomeColors.textPrimary
                    : HomeColors.textTertiary,
            fontSize: 10.5,
            fontWeight:
                isCurrent || isCompleted ? FontWeight.w800 : FontWeight.w600,
          ),
        ),
      ],
    );
  }

  Widget _buildStageLine({required bool isCompleted}) {
    return Expanded(
      child: Container(
        height: 2.5,
        margin: const EdgeInsets.only(bottom: 16),
        color: isCompleted ? HomeColors.primaryGreen : HomeColors.border,
      ),
    );
  }

  void _showFarmPickerModal(BuildContext context) {
    if (farms == null || farms!.isEmpty || onSelectFarm == null) return;

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
                        fontSize: 16,
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
                const SizedBox(height: 8),
                Flexible(
                  child: ListView.separated(
                    shrinkWrap: true,
                    itemCount: farms!.length,
                    separatorBuilder: (_, __) => const SizedBox(height: 8),
                    itemBuilder: (c, idx) {
                      final farm = farms![idx];
                      final isSel = selectedFarm?.id == farm.id;
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
                          leading: Icon(
                            Icons.landscape_rounded,
                            color: isSel ? HomeColors.primaryGreen : const Color(0xFF68766E),
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
                            '${farm.areaHa.toStringAsFixed(2)} Ha',
                            style: const TextStyle(fontSize: 12, color: Color(0xFF68766E)),
                          ),
                          trailing: isSel
                              ? const Icon(Icons.check_circle_rounded, color: HomeColors.primaryGreen)
                              : const Icon(Icons.radio_button_off_rounded, color: Color(0xFFB0BDB5)),
                          onTap: () {
                            onSelectFarm!(farm);
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
}

class _SeasonJourneyMetrics {
  const _SeasonJourneyMetrics({
    required this.dayNumber,
    required this.totalDays,
    required this.remainingDays,
    required this.progress,
    required this.stage,
    required this.startLabel,
    required this.harvestLabel,
  });

  final int dayNumber;
  final int totalDays;
  final int remainingDays;
  final double progress;
  final _GrowthStage stage;
  final String startLabel;
  final String harvestLabel;

  int get progressPercent => (progress * 100).round().clamp(0, 100).toInt();

  String get dayLabel {
    if (remainingDays <= 0) {
      return 'Siap panen';
    }

    return 'H-$remainingDays panen';
  }

  String get recommendation {
    final stageTip = stage.tip;
    final measured =
        'Hari ke-$dayNumber dari $totalDays hari musim tanam terukur.';

    if (remainingDays <= 0) {
      return '$measured $stageTip Segera validasi kadar air dan catat hasil panen.';
    }

    return '$measured $stageTip';
  }

  static _SeasonJourneyMetrics? fromSeason(CropSeasonModel? season) {
    final startDate = _parseDate(season?.plantingDate) ??
        _parseDate(season?.plannedPlantingDate);

    if (startDate == null) {
      return null;
    }

    final today = _dateOnly(DateTime.now());
    final start = _dateOnly(startDate);
    final estimatedHarvest = _parseDate(season?.estimatedHarvestDate);
    final harvest = estimatedHarvest != null
        ? _dateOnly(estimatedHarvest)
        : start.add(const Duration(days: 109));

    final totalDays = harvest.difference(start).inDays + 1;
    final safeTotalDays = totalDays < 1 ? 110 : totalDays;
    final rawDay = today.difference(start).inDays + 1;
    final dayNumber = rawDay.clamp(0, safeTotalDays).toInt();
    final remainingDays =
        harvest.difference(today).inDays.clamp(0, safeTotalDays).toInt();

    final isCompleted = season?.status == 'completed' ||
        season?.status == 'harvested' ||
        season?.status == 'finished';

    final calculatedProgress =
        (dayNumber / safeTotalDays).clamp(0.0, 1.0).toDouble();

    final progress = isCompleted ? 1.0 : calculatedProgress;
    final effectiveDayNumber = isCompleted ? safeTotalDays : dayNumber;
    final effectiveRemainingDays = isCompleted ? 0 : remainingDays;

    return _SeasonJourneyMetrics(
      dayNumber: effectiveDayNumber,
      totalDays: safeTotalDays,
      remainingDays: effectiveRemainingDays,
      progress: progress,
      stage: _GrowthStage.fromProgress(progress),
      startLabel: _formatShortDate(start),
      harvestLabel: _formatShortDate(harvest),
    );
  }

  static DateTime? _parseDate(String? value) {
    if (value == null || value.trim().isEmpty) {
      return null;
    }

    return DateTime.tryParse(value.trim());
  }

  static DateTime _dateOnly(DateTime date) {
    return DateTime(date.year, date.month, date.day);
  }

  static String _formatShortDate(DateTime date) {
    const months = [
      'Jan',
      'Feb',
      'Mar',
      'Apr',
      'Mei',
      'Jun',
      'Jul',
      'Agu',
      'Sep',
      'Okt',
      'Nov',
      'Des',
    ];

    return '${date.day} ${months[date.month - 1]} ${date.year}';
  }
}

enum _GrowthStage {
  establishment,
  vegetative,
  reproductive,
  ripening;

  String get name {
    return switch (this) {
      _GrowthStage.establishment => 'Fase Tanam & Adaptasi',
      _GrowthStage.vegetative => 'Fase Vegetatif',
      _GrowthStage.reproductive => 'Fase Generatif',
      _GrowthStage.ripening => 'Fase Pematangan & Panen',
    };
  }

  String get shortName {
    return switch (this) {
      _GrowthStage.establishment => 'Tanam',
      _GrowthStage.vegetative => 'Vegetatif',
      _GrowthStage.reproductive => 'Generatif',
      _GrowthStage.ripening => 'Panen',
    };
  }

  String get tip {
    return switch (this) {
      _GrowthStage.establishment =>
        'Pastikan bibit adaptif, genangan stabil, dan sulaman dilakukan '
            'bila ada rumpun gagal tumbuh.',
      _GrowthStage.vegetative =>
        'Fokus pada anakan produktif, pemupukan susulan, dan pengendalian '
            'gulma berbasis pengamatan lahan.',
      _GrowthStage.reproductive =>
        'Jaga suplai air, pantau hama penggerek batang, dan hindari stres '
            'tanaman saat pembentukan malai.',
      _GrowthStage.ripening =>
        'Kurangi air bertahap, pantau bulir menguning, dan siapkan jadwal '
            'panen berdasarkan estimasi panen.',
    };
  }

  static _GrowthStage fromProgress(double progress) {
    if (progress < 0.15) return _GrowthStage.establishment;
    if (progress < 0.50) return _GrowthStage.vegetative;
    if (progress < 0.78) return _GrowthStage.reproductive;
    return _GrowthStage.ripening;
  }
}
