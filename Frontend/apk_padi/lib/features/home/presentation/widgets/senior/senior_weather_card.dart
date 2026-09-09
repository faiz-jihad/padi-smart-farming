import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_tts/flutter_tts.dart';
import 'package:padi/core/localization/app_language.dart';
import 'package:padi/features/home/presentation/tokens/senior_tokens.dart';
import 'package:padi/features/home/presentation/widgets/weather_card.dart'
    show weatherAdvisoryFamilyProvider;

/// Kartu Cuaca & Agroklimat Ramah Lansia (Senior Weather Card)
/// Menampilkan ikon cuaca besar (48px), suhu besar (32px),
/// kondisi ringkas, dan anjuran kerja tani yang to-the-point
/// ("Cocok untuk bertani" / "Potensi hujan, hati-hati"),
/// serta tombol suara untuk membacakan ramalan cuaca.
class SeniorWeatherCard extends ConsumerStatefulWidget {
  const SeniorWeatherCard({
    super.key,
    required this.locationName,
    required this.onTapCalendar,
    this.farmId,
  });

  final String locationName;
  final VoidCallback onTapCalendar;
  final int? farmId;

  @override
  ConsumerState<SeniorWeatherCard> createState() => _SeniorWeatherCardState();
}

class _SeniorWeatherCardState extends ConsumerState<SeniorWeatherCard> {
  final FlutterTts _flutterTts = FlutterTts();
  bool _isPlayingVoice = false;

  @override
  void initState() {
    super.initState();
    _initTts();
  }

  Future<void> _initTts() async {
    try {
      await _flutterTts.setLanguage('id-ID');
      await _flutterTts.setSpeechRate(0.48);
      await _flutterTts.setPitch(1.0);
      _flutterTts.setCompletionHandler(() {
        if (mounted) setState(() => _isPlayingVoice = false);
      });
      _flutterTts.setErrorHandler((_) {
        if (mounted) setState(() => _isPlayingVoice = false);
      });
    } catch (_) {}
  }

  @override
  void dispose() {
    _flutterTts.stop();
    super.dispose();
  }

  Future<void> _toggleVoice(String textToSpeak) async {
    if (_isPlayingVoice) {
      await _flutterTts.stop();
      if (mounted) setState(() => _isPlayingVoice = false);
      return;
    }

    try {
      if (mounted) setState(() => _isPlayingVoice = true);
      await _flutterTts.speak(textToSpeak);
    } catch (_) {
      if (mounted) setState(() => _isPlayingVoice = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final lang = ref.watch(languageProvider);
    final advisoryAsync = ref.watch(
      weatherAdvisoryFamilyProvider(widget.farmId),
    );
    final advisoryData = advisoryAsync.value;

    final weather = advisoryData?['weather'] as Map<String, dynamic>?;
    final advisories = advisoryData?['advisories'] as List?;

    Map<dynamic, dynamic>? firstAdvice;
    if (advisories != null) {
      for (final item in advisories) {
        if (item is Map) {
          firstAdvice = item;
          break;
        }
      }
    }

    final rawTemp = weather?['temp'];
    final tempVal = rawTemp is num ? '${rawTemp.round()}°C' : '30°C';
    final conditionDesc =
        weather?['description']?.toString() ?? 'Cerah Berawan';
    final voiceText =
        advisoryData?['voice_text']?.toString() ??
        'Cuaca di ${widget.locationName} $tempVal, $conditionDesc.';

    // Menentukan apakah cuaca cocok untuk bertani
    final isRainy =
        conditionDesc.toLowerCase().contains('hujan') ||
        conditionDesc.toLowerCase().contains('gerimis') ||
        conditionDesc.toLowerCase().contains('badai');

    final adviceTitle = isRainy
        ? switch (lang) {
            AppLanguage.id => 'Potensi Hujan: Tunda Semprot / Pupuk',
            AppLanguage.jv => 'Bakal Udan: Tunda Nyemprot / Ngrabuk',
            AppLanguage.en => 'Rain Expected: Delay Spraying / Fertilizer',
          }
        : switch (lang) {
            AppLanguage.id => 'Cuaca Baik: Cocok untuk Bertani Hari Ini',
            AppLanguage.jv => 'Hawa Sae: Cocok Kanggo Makarya Ing Sawah',
            AppLanguage.en => 'Good Weather: Suitable for Field Work',
          };

    final adviceDesc =
        firstAdvice?['action']?.toString() ??
        (isRainy
            ? switch (lang) {
                AppLanguage.id =>
                  'Hujan berpotensi membilas pupuk dan pestisida. Pastikan saluran air sawah lancar.',
                AppLanguage.jv =>
                  'Udan saged ngelunturake rabuk. Pesthekake kalenan sawah mili lancar.',
                AppLanguage.en =>
                  'Rain may wash away inputs. Ensure field drainage is clear.',
              }
            : switch (lang) {
                AppLanguage.id =>
                  'Kondisi cuaca mendukung penyemprotan hama atau pemupukan tanaman padi.',
                AppLanguage.jv =>
                  'Hawa padhang sae kanggo nyemprot omo utawa ngrabuk pari.',
                AppLanguage.en =>
                  'Favorable weather for fertilization and crop spraying.',
              });

    final displayLoc = widget.locationName.isNotEmpty
        ? widget.locationName
        : switch (lang) {
            AppLanguage.id => 'Lokasi Sawah Anda',
            AppLanguage.jv => 'Lokasi Sawah Panjenengan',
            AppLanguage.en => 'Your Farm Location',
          };

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
          // Baris Atas: Judul Seksi + Tombol Suara (TTS)
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Expanded(
                child: Row(
                  children: [
                    const Icon(
                      Icons.wb_sunny_rounded,
                      color: SeniorColors.primaryGreen,
                      size: 26,
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        switch (lang) {
                          AppLanguage.id => 'Cuaca Sawah',
                          AppLanguage.jv => 'Hawa Sawah',
                          AppLanguage.en => 'Farm Weather',
                        },
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: SeniorTypography.title.copyWith(fontSize: 22),
                      ),
                    ),
                  ],
                ),
              ),

              // Tombol Baca Bersuara (TTS)
              const SizedBox(width: 8),
              Material(
                color: Colors.transparent,
                child: InkWell(
                  onTap: () => _toggleVoice(
                    '$displayLoc. $voiceText. $adviceTitle. $adviceDesc',
                  ),
                  borderRadius: BorderRadius.circular(
                    SeniorDimensions.pillRadius,
                  ),
                  child: Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 10,
                      vertical: 6,
                    ),
                    decoration: BoxDecoration(
                      color: _isPlayingVoice
                          ? SeniorColors.lightGreenBg
                          : SeniorColors.surfaceMuted,
                      borderRadius: BorderRadius.circular(
                        SeniorDimensions.pillRadius,
                      ),
                      border: Border.all(
                        color: _isPlayingVoice
                            ? SeniorColors.primaryGreen
                            : SeniorColors.border,
                        width: 1.5,
                      ),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(
                          _isPlayingVoice
                              ? Icons.volume_up_rounded
                              : Icons.volume_down_rounded,
                          color: _isPlayingVoice
                              ? SeniorColors.primaryGreen
                              : SeniorColors.textSecondary,
                          size: 22,
                        ),
                        const SizedBox(width: 6),
                        Text(
                          switch (lang) {
                            AppLanguage.id =>
                              _isPlayingVoice ? 'Berhenti' : 'Dengarkan',
                            AppLanguage.jv =>
                              _isPlayingVoice ? 'Mandheg' : 'Mirengake',
                            AppLanguage.en =>
                              _isPlayingVoice ? 'Stop' : 'Listen',
                          },
                          style: TextStyle(
                            color: _isPlayingVoice
                                ? SeniorColors.primaryGreen
                                : SeniorColors.textPrimary,
                            fontSize: 14,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ],
          ),

          const SizedBox(height: 4),

          // Lokasi Sawah (16px w600)
          Row(
            children: [
              const Icon(
                Icons.location_on_rounded,
                color: SeniorColors.textSecondary,
                size: 18,
              ),
              const SizedBox(width: 4),
              Expanded(
                child: Text(
                  displayLoc,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: SeniorTypography.caption.copyWith(
                    fontSize: 16,
                    color: SeniorColors.textSecondary,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),
            ],
          ),

          const SizedBox(height: SeniorSpacing.md),

          // Baris Cuaca Utama: Ikon Besar 48px + Suhu 32px + Kondisi 20px
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
            decoration: BoxDecoration(
              color: SeniorColors.paleGreenBg,
              borderRadius: BorderRadius.circular(
                SeniorDimensions.buttonRadius,
              ),
              border: Border.all(
                color: isRainy
                    ? SeniorColors.borderStrong
                    : SeniorColors.greenBorder,
                width: 1.5,
              ),
            ),
            child: Row(
              children: [
                Icon(
                  isRainy ? Icons.grain_rounded : Icons.wb_cloudy_rounded,
                  color: SeniorColors.primaryGreen,
                  size: SeniorDimensions.iconHuge,
                ),
                const SizedBox(width: 16),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      tempVal,
                      style: SeniorTypography.display.copyWith(
                        color: SeniorColors.textPrimary,
                        fontSize: 32,
                      ),
                    ),
                    Text(
                      conditionDesc,
                      style: SeniorTypography.subtitle.copyWith(
                        color: SeniorColors.textPrimary,
                        fontSize: 18,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),

          const SizedBox(height: SeniorSpacing.md),

          // Kotak Rekomendasi Kerja Tani
          Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: isRainy
                  ? SeniorColors.lightGreenBg
                  : SeniorColors.paleGreenBg,
              borderRadius: BorderRadius.circular(
                SeniorDimensions.buttonRadius,
              ),
              border: Border.all(
                color: isRainy
                    ? SeniorColors.borderStrong
                    : SeniorColors.greenBorder,
                width: 1.5,
              ),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Icon(
                      isRainy
                          ? Icons.info_outline_rounded
                          : Icons.check_circle_outline_rounded,
                      color: SeniorColors.primaryGreen,
                      size: 22,
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        adviceTitle,
                        style: SeniorTypography.subtitle.copyWith(
                          color: SeniorColors.primaryGreen,
                          fontSize: 17,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 6),
                Text(
                  adviceDesc,
                  style: SeniorTypography.body.copyWith(
                    fontSize: 16,
                    color: SeniorColors.textPrimary,
                  ),
                ),
              ],
            ),
          ),

          const SizedBox(height: SeniorSpacing.md),

          // Tombol Kalender Tanam Lengkap (56px)
          SizedBox(
            height: SeniorDimensions.buttonHeight,
            child: OutlinedButton.icon(
              onPressed: widget.onTapCalendar,
              icon: const Icon(Icons.calendar_month_rounded, size: 26),
              label: Text(
                switch (lang) {
                  AppLanguage.id => 'Buka Jadwal & Kalender Tanam',
                  AppLanguage.jv => 'Mirsani Tanggalan Tanam',
                  AppLanguage.en => 'Open Planting Calendar',
                },
                style: SeniorTypography.button.copyWith(
                  color: SeniorColors.primaryGreen,
                  fontSize: 17,
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
    );
  }
}
