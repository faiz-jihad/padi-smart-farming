import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_tts/flutter_tts.dart';
import 'package:padi/core/localization/app_language.dart';
import 'package:padi/core/providers/app_providers.dart';
import 'package:padi/features/home/presentation/tokens/home_tokens.dart';

final weatherAdvisoryFamilyProvider =
    FutureProvider.family<Map<String, dynamic>?, int?>((ref, farmId) async {
      if (farmId == null || farmId <= 0) return null;
      final apiClient = ref.read(apiClientProvider);
      try {
        final res = await apiClient.dio.get('/farms/$farmId/weather-advisory');
        return res.data?['data'] as Map<String, dynamic>?;
      } catch (_) {
        return null;
      }
    });

final weatherForecastFamilyProvider =
    FutureProvider.family<List<Map<String, dynamic>>, int?>((
      ref,
      farmId,
    ) async {
      if (farmId == null || farmId <= 0) return const [];
      final apiClient = ref.read(apiClientProvider);
      try {
        final res = await apiClient.dio.post(
          '/weather/forecast',
          data: {'farm_id': farmId, 'units': 'metric', 'lang': 'id'},
        );
        final raw = res.data?['data']?['forecasts'];
        if (raw is! List) return const [];
        return raw
            .whereType<Map>()
            .map((item) => Map<String, dynamic>.from(item))
            .toList();
      } catch (_) {
        return const [];
      }
    });

class WeatherCard extends StatefulWidget {
  const WeatherCard({
    super.key,
    required this.locationName,
    required this.onTapCalendar,
    this.farmId,
  });

  final String locationName;
  final VoidCallback onTapCalendar;
  final int? farmId;

  @override
  State<WeatherCard> createState() => _WeatherCardState();
}

class _WeatherCardState extends State<WeatherCard> {
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
    return Consumer(
      builder: (context, ref, _) {
        final lang = ref.watch(languageProvider);
        final advisoryAsync = ref.watch(
          weatherAdvisoryFamilyProvider(widget.farmId),
        );
        final forecastAsync = ref.watch(
          weatherForecastFamilyProvider(widget.farmId),
        );
        final advisoryData = advisoryAsync.value;
        final forecasts = forecastAsync.value ?? const <Map<String, dynamic>>[];

        final title = switch (lang) {
          AppLanguage.id => 'Cuaca & Agroklimat Lahan',
          AppLanguage.jv => 'Hawa & Agroklimat Sawah',
          AppLanguage.en => 'Farm Weather & Agroclimate',
        };

        final defaultLocation = switch (lang) {
          AppLanguage.id => 'Pilih atau tambahkan lahan',
          AppLanguage.jv => 'Pilih utawa tambah sawah',
          AppLanguage.en => 'Select or add a farm',
        };

        final calendarLabel = switch (lang) {
          AppLanguage.id => 'Kalender',
          AppLanguage.jv => 'Tanggalan',
          AppLanguage.en => 'Calendar',
        };

        final noFarmText = switch (lang) {
          AppLanguage.id =>
            'Data cuaca muncul setelah lahan memiliki lokasi tersimpan.',
          AppLanguage.jv =>
            'Data hawa metu yen sawah wis nduwe lokasi kesimpen.',
          AppLanguage.en =>
            'Weather data appears after the farm location is saved.',
        };

        final unavailableText = switch (lang) {
          AppLanguage.id =>
            'Saran cuaca belum tersedia. Tarik ke bawah untuk memuat ulang.',
          AppLanguage.jv =>
            'Saran hawa durung kasedhiya. Seret mudhun kanggo muat maneh.',
          AppLanguage.en =>
            'Weather advisory is unavailable. Pull down to refresh.',
        };

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
        final notice = firstAdvice?['action']?.toString();
        final adviceTitle = firstAdvice?['title']?.toString();
        final recommended = firstAdvice?['recommended']?.toString();
        final voiceText = advisoryData?['voice_text']?.toString() ?? notice;
        final phaseName = advisoryData?['phase_name']?.toString();
        final hst = advisoryData?['hst'];
        final tempVal = weather?['temp'] is num
            ? '${(weather!['temp'] as num).round()} C'
            : null;
        final humVal = weather?['humidity'] != null
            ? '${weather!['humidity']}%'
            : null;
        final windVal = weather?['wind_speed'] is num
            ? '${(weather!['wind_speed'] as num).toStringAsFixed(1)} m/d'
            : null;
        final condition = weather?['description']?.toString();
        final hasWeather = weather != null && tempVal != null;
        final isLoading = widget.farmId != null && advisoryAsync.isLoading;
        final earlyWarning = _WeatherEarlyWarning.from(
          weather: weather,
          advisories: advisories,
          forecasts: forecasts,
        );

        return Container(
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
                  children: [
                    Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: HomeColors.lightGreen,
                        borderRadius: BorderRadius.circular(HomeRadius.sm),
                      ),
                      child: const Icon(
                        Icons.wb_cloudy_outlined,
                        color: HomeColors.primaryGreen,
                        size: 18,
                      ),
                    ),
                    const SizedBox(width: HomeSpacing.sm),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(title, style: HomeTypography.cardTitle),
                          Text(
                            widget.locationName.isNotEmpty
                                ? widget.locationName
                                : defaultLocation,
                            style: HomeTypography.caption,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ],
                      ),
                    ),
                    if (voiceText != null && voiceText.isNotEmpty)
                      IconButton(
                        icon: Icon(
                          _isPlayingVoice
                              ? Icons.volume_up_rounded
                              : Icons.volume_up_outlined,
                          color: HomeColors.primaryGreen,
                          size: 20,
                        ),
                        tooltip: 'Dengarkan saran cuaca',
                        onPressed: () => _toggleVoice(voiceText),
                      ),
                    TextButton.icon(
                      onPressed: widget.onTapCalendar,
                      icon: const Icon(
                        Icons.calendar_month_outlined,
                        size: 15,
                        color: HomeColors.primaryGreen,
                      ),
                      label: Text(
                        calendarLabel,
                        style: const TextStyle(
                          color: HomeColors.primaryGreen,
                          fontSize: 12,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: HomeSpacing.md),
                if (isLoading)
                  _buildLoadingState()
                else if (!hasWeather)
                  _buildUnavailableState(
                    widget.farmId == null ? noFarmText : unavailableText,
                  )
                else
                  _buildWeatherState(
                    tempVal: tempVal,
                    condition: condition,
                    phaseName: phaseName,
                    hst: hst,
                    adviceTitle: adviceTitle,
                    notice: notice,
                    recommended: recommended,
                    humVal: humVal,
                    windVal: windVal,
                    forecasts: forecasts,
                    isForecastLoading: forecastAsync.isLoading,
                    earlyWarning: earlyWarning,
                  ),
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _buildUnavailableState(String text) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(HomeSpacing.md),
      decoration: BoxDecoration(
        color: HomeColors.lightGreen,
        borderRadius: BorderRadius.circular(HomeRadius.md),
        border: Border.all(color: HomeColors.border),
      ),
      child: Row(
        children: [
          const Icon(
            Icons.info_outline_rounded,
            color: HomeColors.primaryGreen,
          ),
          const SizedBox(width: HomeSpacing.sm),
          Expanded(
            child: Text(
              text,
              style: HomeTypography.supporting.copyWith(
                color: HomeColors.textPrimary,
                fontWeight: FontWeight.w700,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildLoadingState() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(HomeSpacing.md),
      decoration: BoxDecoration(
        color: HomeColors.lightGreen,
        borderRadius: BorderRadius.circular(HomeRadius.md),
        border: Border.all(color: HomeColors.border),
      ),
      child: Row(
        children: [
          const SizedBox(
            width: 24,
            height: 24,
            child: CircularProgressIndicator(
              strokeWidth: 2,
              color: HomeColors.primaryGreen,
            ),
          ),
          const SizedBox(width: HomeSpacing.sm),
          Expanded(
            child: Text(
              'Memuat data cuaca lahan...',
              style: HomeTypography.supporting.copyWith(
                color: HomeColors.textPrimary,
                fontWeight: FontWeight.w700,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildWeatherState({
    required String tempVal,
    required String? condition,
    required String? phaseName,
    required dynamic hst,
    required String? adviceTitle,
    required String? notice,
    required String? recommended,
    required String? humVal,
    required String? windVal,
    required List<Map<String, dynamic>> forecasts,
    required bool isForecastLoading,
    required _WeatherEarlyWarning earlyWarning,
  }) {
    return Container(
      padding: const EdgeInsets.all(HomeSpacing.md),
      decoration: BoxDecoration(
        color: HomeColors.lightGreen,
        borderRadius: BorderRadius.circular(HomeRadius.md),
        border: Border.all(color: HomeColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: 82,
                height: 82,
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(HomeRadius.md),
                  border: Border.all(color: HomeColors.border),
                ),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Icon(
                      Icons.thermostat_rounded,
                      color: HomeColors.primaryGreen,
                      size: 20,
                    ),
                    const SizedBox(height: 4),
                    Text(
                      tempVal,
                      style: const TextStyle(
                        fontSize: 22,
                        fontWeight: FontWeight.w900,
                        color: HomeColors.deepGreen,
                      ),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: HomeSpacing.sm),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    if (condition != null && condition.isNotEmpty) ...[
                      Text(
                        condition,
                        style: const TextStyle(
                          color: HomeColors.textPrimary,
                          fontSize: 16,
                          fontWeight: FontWeight.w900,
                        ),
                      ),
                      const SizedBox(height: 3),
                    ],
                    if (phaseName != null && phaseName.isNotEmpty)
                      Text(
                        phaseName,
                        style: const TextStyle(
                          color: HomeColors.primaryGreen,
                          fontSize: 11,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                    const SizedBox(height: HomeSpacing.xs),
                    Wrap(
                      spacing: 6,
                      runSpacing: 6,
                      children: [
                        if (humVal != null)
                          _buildMetricChip(
                            icon: Icons.water_drop_outlined,
                            label: humVal,
                          ),
                        if (windVal != null)
                          _buildMetricChip(
                            icon: Icons.air_rounded,
                            label: windVal,
                          ),
                        if (hst != null)
                          _buildMetricChip(
                            icon: Icons.eco_outlined,
                            label: 'HST $hst',
                          ),
                      ],
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: HomeSpacing.sm),
          _buildEarlyWarningCard(earlyWarning),
          if (notice != null && notice.isNotEmpty) ...[
            const SizedBox(height: HomeSpacing.sm),
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(HomeRadius.sm),
                border: Border.all(color: HomeColors.border),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Container(
                        padding: const EdgeInsets.all(6),
                        decoration: BoxDecoration(
                          color: HomeColors.lightGreen,
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: const Icon(
                          Icons.task_alt_rounded,
                          size: 15,
                          color: HomeColors.primaryGreen,
                        ),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          adviceTitle?.isNotEmpty == true
                              ? adviceTitle!
                              : 'Saran agroklimat hari ini',
                          style: const TextStyle(
                            color: HomeColors.deepGreen,
                            fontSize: 12.5,
                            fontWeight: FontWeight.w900,
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Text(
                    notice,
                    style: const TextStyle(
                      color: HomeColors.textPrimary,
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                      height: 1.4,
                    ),
                  ),
                  if (recommended != null && recommended.isNotEmpty) ...[
                    const SizedBox(height: 8),
                    Text(
                      recommended,
                      style: const TextStyle(
                        color: HomeColors.primaryGreen,
                        fontSize: 11.5,
                        fontWeight: FontWeight.w800,
                        height: 1.35,
                      ),
                    ),
                  ],
                ],
              ),
            ),
          ],
          const SizedBox(height: HomeSpacing.sm),
          _buildForecastSection(
            forecasts: forecasts,
            isLoading: isForecastLoading,
          ),
        ],
      ),
    );
  }

  Widget _buildEarlyWarningCard(_WeatherEarlyWarning warning) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: warning.isSafe ? Colors.white : HomeColors.deepGreen,
        borderRadius: BorderRadius.circular(HomeRadius.sm),
        border: Border.all(
          color: warning.isSafe ? HomeColors.border : HomeColors.deepGreen,
        ),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 36,
            height: 36,
            decoration: BoxDecoration(
              color: warning.isSafe
                  ? HomeColors.lightGreen
                  : Colors.white.withValues(alpha: 0.14),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(
              warning.icon,
              color: warning.isSafe ? HomeColors.primaryGreen : Colors.white,
              size: 20,
            ),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Wrap(
                  spacing: 8,
                  runSpacing: 6,
                  crossAxisAlignment: WrapCrossAlignment.center,
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 8,
                        vertical: 3,
                      ),
                      decoration: BoxDecoration(
                        color: warning.isSafe
                            ? HomeColors.lightGreen
                            : Colors.white.withValues(alpha: 0.16),
                        borderRadius: BorderRadius.circular(HomeRadius.pill),
                      ),
                      child: Text(
                        warning.level,
                        style: TextStyle(
                          color: warning.isSafe
                              ? HomeColors.primaryGreen
                              : Colors.white,
                          fontSize: 10,
                          fontWeight: FontWeight.w900,
                        ),
                      ),
                    ),
                    Text(
                      warning.title,
                      style: TextStyle(
                        color: warning.isSafe
                            ? HomeColors.deepGreen
                            : Colors.white,
                        fontSize: 13,
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 5),
                Text(
                  warning.message,
                  style: TextStyle(
                    color: warning.isSafe
                        ? HomeColors.textSecondary
                        : Colors.white.withValues(alpha: 0.86),
                    fontSize: 11.5,
                    fontWeight: FontWeight.w600,
                    height: 1.35,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildForecastSection({
    required List<Map<String, dynamic>> forecasts,
    required bool isLoading,
  }) {
    final items = forecasts.take(4).toList();

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(HomeRadius.sm),
        border: Border.all(color: HomeColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Icon(
                Icons.calendar_view_day_rounded,
                color: HomeColors.primaryGreen,
                size: 16,
              ),
              const SizedBox(width: 6),
              const Expanded(
                child: Text(
                  'Prediksi cuaca ke depan',
                  style: TextStyle(
                    color: HomeColors.deepGreen,
                    fontSize: 12.5,
                    fontWeight: FontWeight.w900,
                  ),
                ),
              ),
              if (items.isNotEmpty)
                Text(
                  '${items.length} data',
                  style: const TextStyle(
                    color: HomeColors.textSecondary,
                    fontSize: 10.5,
                    fontWeight: FontWeight.w700,
                  ),
                ),
            ],
          ),
          const SizedBox(height: 10),
          if (isLoading)
            const LinearProgressIndicator(
              minHeight: 3,
              color: HomeColors.primaryGreen,
              backgroundColor: HomeColors.lightGreen,
            )
          else if (items.isEmpty)
            Text(
              'Prediksi belum tersedia dari server cuaca.',
              style: HomeTypography.supporting.copyWith(
                color: HomeColors.textSecondary,
              ),
            )
          else
            Row(
              children: [
                for (var i = 0; i < items.length; i++) ...[
                  Expanded(child: _ForecastTile(data: items[i])),
                  if (i != items.length - 1) const SizedBox(width: 8),
                ],
              ],
            ),
        ],
      ),
    );
  }

  Widget _buildMetricChip({required IconData icon, required String label}) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(HomeRadius.pill),
        border: Border.all(color: HomeColors.border),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 12, color: HomeColors.primaryGreen),
          const SizedBox(width: 4),
          Text(
            label,
            style: const TextStyle(
              color: HomeColors.primaryGreen,
              fontSize: 10.5,
              fontWeight: FontWeight.w700,
            ),
          ),
        ],
      ),
    );
  }
}

class _ForecastTile extends StatelessWidget {
  const _ForecastTile({required this.data});

  final Map<String, dynamic> data;

  @override
  Widget build(BuildContext context) {
    final temp = _asNum(data['temperature']);
    final humidity = _asNum(data['humidity']);
    final rain = _asNum(data['rain']);
    final desc = data['description']?.toString() ?? data['weather']?.toString();

    return Container(
      constraints: const BoxConstraints(minHeight: 96),
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 10),
      decoration: BoxDecoration(
        color: HomeColors.lightGreen,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: HomeColors.border),
      ),
      child: Column(
        children: [
          Icon(_iconFor(desc, rain), color: HomeColors.primaryGreen, size: 20),
          const SizedBox(height: 6),
          Text(
            _timeLabel(data['timestamp']),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: const TextStyle(
              color: HomeColors.textSecondary,
              fontSize: 10,
              fontWeight: FontWeight.w800,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            temp != null ? '${temp.round()} C' : '-',
            style: const TextStyle(
              color: HomeColors.deepGreen,
              fontSize: 14,
              fontWeight: FontWeight.w900,
            ),
          ),
          const SizedBox(height: 3),
          Text(
            rain != null && rain > 0
                ? 'Hujan ${rain.toStringAsFixed(1)}'
                : humidity != null
                ? 'RH ${humidity.round()}%'
                : desc ?? '-',
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: const TextStyle(
              color: HomeColors.primaryGreen,
              fontSize: 10,
              fontWeight: FontWeight.w800,
            ),
          ),
        ],
      ),
    );
  }

  static num? _asNum(dynamic value) {
    if (value is num) return value;
    return num.tryParse(value?.toString() ?? '');
  }

  static String _timeLabel(dynamic timestamp) {
    final seconds = _asNum(timestamp)?.toInt();
    if (seconds == null || seconds <= 0) return 'Nanti';
    final dt = DateTime.fromMillisecondsSinceEpoch(seconds * 1000);
    final hour = dt.hour.toString().padLeft(2, '0');
    final minute = dt.minute.toString().padLeft(2, '0');
    return '$hour:$minute';
  }

  static IconData _iconFor(String? desc, num? rain) {
    final lower = desc?.toLowerCase() ?? '';
    if ((rain ?? 0) > 0 || lower.contains('rain') || lower.contains('hujan')) {
      return Icons.grain_rounded;
    }
    if (lower.contains('cloud') || lower.contains('awan')) {
      return Icons.cloud_outlined;
    }
    return Icons.wb_sunny_outlined;
  }
}

class _WeatherEarlyWarning {
  const _WeatherEarlyWarning({
    required this.level,
    required this.title,
    required this.message,
    required this.icon,
    required this.isSafe,
  });

  final String level;
  final String title;
  final String message;
  final IconData icon;
  final bool isSafe;

  factory _WeatherEarlyWarning.from({
    required Map<String, dynamic>? weather,
    required List? advisories,
    required List<Map<String, dynamic>> forecasts,
  }) {
    final currentTemp = _asNum(weather?['temp']);
    final currentHumidity = _asNum(weather?['humidity']);
    final currentWind = _asNum(weather?['wind_speed']);
    final currentDesc = weather?['description']?.toString().toLowerCase() ?? '';

    num maxTemp = currentTemp ?? 0;
    num maxHumidity = currentHumidity ?? 0;
    num maxWind = currentWind ?? 0;
    num maxRain = currentDesc.contains('hujan') || currentDesc.contains('rain')
        ? 1
        : 0;

    for (final forecast in forecasts.take(8)) {
      final temp = _asNum(forecast['temperature']);
      final humidity = _asNum(forecast['humidity']);
      final wind = _asNum(forecast['wind_speed']);
      final rain = _asNum(forecast['rain']);
      final desc =
          forecast['description']?.toString().toLowerCase() ??
          forecast['weather']?.toString().toLowerCase() ??
          '';

      if (temp != null && temp > maxTemp) maxTemp = temp;
      if (humidity != null && humidity > maxHumidity) maxHumidity = humidity;
      if (wind != null && wind > maxWind) maxWind = wind;
      if (rain != null && rain > maxRain) maxRain = rain;
      if (desc.contains('hujan') || desc.contains('rain')) {
        maxRain = maxRain == 0 ? 1 : maxRain;
      }
    }

    var hasUrgentAdvice = false;
    var hasWarningAdvice = false;
    if (advisories != null) {
      for (final advice in advisories) {
        if (advice is! Map) continue;
        final severity = advice['severity']?.toString().toLowerCase() ?? '';
        hasUrgentAdvice = hasUrgentAdvice || severity == 'urgent';
        hasWarningAdvice =
            hasWarningAdvice || severity == 'warning' || severity == 'urgent';
      }
    }

    if (hasUrgentAdvice ||
        maxRain >= 15 ||
        maxHumidity >= 90 ||
        maxWind >= 15) {
      return const _WeatherEarlyWarning(
        level: 'SIAGA CUACA',
        title: 'Risiko lapangan meningkat',
        message:
            'Pantau drainase, kelembaban rumpun, dan daun padi lebih sering pada periode prakiraan terdekat.',
        icon: Icons.warning_amber_rounded,
        isSafe: false,
      );
    }

    if (hasWarningAdvice ||
        maxRain > 0 ||
        maxHumidity >= 82 ||
        maxTemp >= 33 ||
        maxWind >= 8) {
      return const _WeatherEarlyWarning(
        level: 'WASPADA DINI',
        title: 'Ada faktor cuaca yang perlu dipantau',
        message:
            'Sesuaikan pemupukan, pengairan, dan inspeksi daun dengan kondisi prakiraan beberapa jam ke depan.',
        icon: Icons.notifications_active_outlined,
        isSafe: false,
      );
    }

    return const _WeatherEarlyWarning(
      level: 'KONDISI TERKENDALI',
      title: 'Belum ada sinyal cuaca berisiko',
      message:
          'Lanjutkan pemantauan rutin dan catat perubahan kondisi lahan bila cuaca berubah.',
      icon: Icons.check_circle_outline_rounded,
      isSafe: true,
    );
  }

  static num? _asNum(dynamic value) {
    if (value is num) return value;
    return num.tryParse(value?.toString() ?? '');
  }
}
