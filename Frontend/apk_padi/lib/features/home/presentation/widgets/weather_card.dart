import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_tts/flutter_tts.dart';
import 'package:padi/core/localization/app_language.dart';
import 'package:padi/core/providers/app_providers.dart';
import 'package:padi/features/home/presentation/tokens/home_tokens.dart';
import 'package:padi/features/home/presentation/widgets/weather_forecast_item.dart';

final weatherAdvisoryFamilyProvider = FutureProvider.family<Map<String, dynamic>?, int?>((ref, farmId) async {
  if (farmId == null || farmId <= 0) return null;
  final apiClient = ref.read(apiClientProvider);
  try {
    final res = await apiClient.dio.get('/farms/$farmId/weather-advisory');
    return res.data?['data'] as Map<String, dynamic>?;
  } catch (_) {
    return null;
  }
});

class WeatherCard extends StatefulWidget {
  const WeatherCard({
    super.key,
    required this.locationName,
    required this.onTapCalendar,
    this.farmId,
    this.currentTemp = '28°C',
    this.currentCondition,
    this.humidity = '78%',
    this.windSpeed = '12 km/j',
    this.rainNotice,
  });

  final String locationName;
  final VoidCallback onTapCalendar;
  final int? farmId;
  final String currentTemp;
  final String? currentCondition;
  final String humidity;
  final String windSpeed;
  final String? rainNotice;

  @override
  State<WeatherCard> createState() => _WeatherCardState();
}

class _WeatherCardState extends State<WeatherCard> {
  int _selectedForecastIndex = 0;
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
        final s = AppStrings(lang);
        final advisoryAsync = ref.watch(weatherAdvisoryFamilyProvider(widget.farmId));
        final advisoryData = advisoryAsync.value;

        final title = switch (lang) {
          AppLanguage.id => 'Cuaca & Agroklimat Lahan',
          AppLanguage.jv => 'Hawa & Agroklimat Sawah',
          AppLanguage.en => 'Farm Weather & Agroclimate',
        };

        final defaultLocation = switch (lang) {
          AppLanguage.id => 'Wilayah Sawah Anda',
          AppLanguage.jv => 'Wewengkon Sawah Panjenengan',
          AppLanguage.en => 'Your Farm Area',
        };

        final calendarLabel = switch (lang) {
          AppLanguage.id => 'Kalender',
          AppLanguage.jv => 'Tanggalan',
          AppLanguage.en => 'Calendar',
        };

        final condition = advisoryData?['weather']?['description']?.toString() ??
            widget.currentCondition ??
            switch (lang) {
              AppLanguage.id => 'Cerah Berawan',
              AppLanguage.jv => 'Padhang Mendhung',
              AppLanguage.en => 'Partly Cloudy',
            };

        final notice = advisoryData?['advisories'] is List && (advisoryData!['advisories'] as List).isNotEmpty
            ? advisoryData['advisories'][0]['action']?.toString() ?? 'Kondisi cuaca terkendali.'
            : widget.rainNotice ??
                switch (lang) {
                  AppLanguage.id => 'Peluang hujan rendah. Waktu aman untuk pemupukan pagi.',
                  AppLanguage.jv => 'Peluang udan sithik. Aman kanggo mupuk esuk.',
                  AppLanguage.en => 'Low chance of rain. Safe for morning fertilizing.',
                };

        final voiceText = advisoryData?['voice_text']?.toString() ?? notice;
        final phaseName = advisoryData?['phase_name']?.toString();

        final tempVal = advisoryData?['weather']?['temp'] != null
            ? '${(advisoryData!['weather']['temp'] as num).round()}°C'
            : widget.currentTemp;
        final humVal = advisoryData?['weather']?['humidity'] != null
            ? '${advisoryData!['weather']['humidity']}%'
            : widget.humidity;
        final windVal = advisoryData?['weather']?['wind_speed'] != null
            ? '${(advisoryData!['weather']['wind_speed'] as num).toStringAsFixed(1)} m/d'
            : widget.windSpeed;

        final forecastList = [
          {
            'time': s.weatherNow,
            'temp': tempVal,
            'icon': Icons.wb_cloudy_rounded,
            'rain': '20%',
          },
          {
            'time': '10:00',
            'temp': '29°C',
            'icon': Icons.wb_sunny_rounded,
            'rain': '15%',
          },
          {
            'time': '12:00',
            'temp': '31°C',
            'icon': Icons.wb_sunny_rounded,
            'rain': '10%',
          },
          {
            'time': '14:00',
            'temp': '32°C',
            'icon': Icons.wb_cloudy_rounded,
            'rain': '35%',
          },
          {
            'time': '16:00',
            'temp': '29°C',
            'icon': Icons.grain_rounded,
            'rain': '45%',
          },
          {
            'time': '18:00',
            'temp': '27°C',
            'icon': Icons.nightlight_round,
            'rain': '25%',
          },
        ];

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
                // Top Header Row
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: HomeColors.skyBlue.withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(HomeRadius.sm),
                      ),
                      child: const Icon(
                        Icons.wb_sunny_rounded,
                        color: Color(0xFFD97706),
                        size: 18,
                      ),
                    ),
                    const SizedBox(width: HomeSpacing.sm),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            title,
                            style: HomeTypography.cardTitle,
                          ),
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
                    // TTS Voice Speaker Button
                    IconButton(
                      icon: Icon(
                        _isPlayingVoice ? Icons.volume_up_rounded : Icons.volume_up_outlined,
                        color: _isPlayingVoice ? const Color(0xFF059669) : const Color(0xFF64748B),
                        size: 20,
                      ),
                      tooltip: 'Dengarkan Saran Cuaca',
                      onPressed: () => _toggleVoice(voiceText),
                    ),
                    // Calendar Shortcut
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
                      style: TextButton.styleFrom(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 8,
                          vertical: 4,
                        ),
                        minimumSize: Size.zero,
                        tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                      ),
                    ),
                  ],
                ),

                const SizedBox(height: HomeSpacing.md),

                // Current Weather Hero + Advisory Banner
                Container(
                  padding: const EdgeInsets.all(HomeSpacing.md),
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                      colors: [
                        Color(0xFFF0FDF4),
                        Color(0xFFE0F2FE),
                      ],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                    borderRadius: BorderRadius.circular(HomeRadius.md),
                    border: Border.all(color: const Color(0xFFBAE6FD)),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Text(
                            tempVal,
                            style: const TextStyle(
                              fontSize: 28,
                              fontWeight: FontWeight.w900,
                              color: Color(0xFF0F172A),
                              letterSpacing: -1,
                            ),
                          ),
                          const SizedBox(width: 8),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  condition,
                                  style: const TextStyle(
                                    color: Color(0xFF0F172A),
                                    fontSize: 13,
                                    fontWeight: FontWeight.w700,
                                  ),
                                ),
                                if (phaseName != null)
                                  Text(
                                    phaseName,
                                    style: const TextStyle(
                                      color: Color(0xFF059669),
                                      fontSize: 11,
                                      fontWeight: FontWeight.w800,
                                    ),
                                  ),
                              ],
                            ),
                          ),
                          // Metric Chips
                          Column(
                            crossAxisAlignment: CrossAxisAlignment.end,
                            children: [
                              _buildMetricChip(
                                icon: Icons.water_drop_outlined,
                                label: humVal,
                                color: HomeColors.skyBlue,
                              ),
                              const SizedBox(height: 4),
                              _buildMetricChip(
                                icon: Icons.air_rounded,
                                label: windVal,
                                color: HomeColors.textSecondary,
                              ),
                            ],
                          ),
                        ],
                      ),
                      const SizedBox(height: 10),
                      Container(
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(color: const Color(0xFFE2E8F0)),
                        ),
                        child: Row(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Icon(Icons.psychology_rounded, size: 16, color: Color(0xFF059669)),
                            const SizedBox(width: 8),
                            Expanded(
                              child: Text(
                                notice,
                                style: const TextStyle(
                                  color: Color(0xFF334155),
                                  fontSize: 11.5,
                                  fontWeight: FontWeight.w600,
                                  height: 1.35,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),

                const SizedBox(height: HomeSpacing.sm),

                // Horizontal Scrollable Forecast
                SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  physics: const BouncingScrollPhysics(),
                  child: Row(
                    children: List.generate(
                      forecastList.length,
                      (index) {
                        final item = forecastList[index];
                        return Padding(
                          padding: EdgeInsets.only(
                            right: index == forecastList.length - 1 ? 0 : 8,
                          ),
                          child: WeatherForecastItem(
                            time: item['time']?.toString() ?? '',
                            temp: item['temp']?.toString() ?? '',
                            icon: (item['icon'] as IconData?) ?? Icons.wb_sunny_rounded,
                            rainProb: item['rain']?.toString(),
                            isSelected: _selectedForecastIndex == index,
                            onTap: () => setState(() => _selectedForecastIndex = index),
                          ),
                        );
                      },
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

  Widget _buildMetricChip({
    required IconData icon,
    required String label,
    required Color color,
  }) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(HomeRadius.pill),
        border: Border.all(color: HomeColors.borderSubtle),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 12, color: color),
          const SizedBox(width: 4),
          Text(
            label,
            style: TextStyle(
              color: color,
              fontSize: 10.5,
              fontWeight: FontWeight.w700,
            ),
          ),
        ],
      ),
    );
  }
}
