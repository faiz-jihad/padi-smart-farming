import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:padi/core/localization/app_language.dart';
import 'package:padi/features/home/presentation/tokens/home_tokens.dart';
import 'package:padi/features/home/presentation/widgets/weather_forecast_item.dart';

class WeatherCard extends StatefulWidget {
  const WeatherCard({
    super.key,
    required this.locationName,
    required this.onTapCalendar,
    this.currentTemp = '28°C',
    this.currentCondition,
    this.humidity = '78%',
    this.windSpeed = '12 km/j',
    this.rainNotice,
  });

  final String locationName;
  final VoidCallback onTapCalendar;
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

  @override
  Widget build(BuildContext context) {
    return Consumer(
      builder: (context, ref, _) {
        final lang = ref.watch(languageProvider);
        final s = AppStrings(lang);

    final title = switch (lang) {
      AppLanguage.id => 'Cuaca & Agroklimat',
      AppLanguage.jv => 'Hawa & Agroklimat',
      AppLanguage.en => 'Weather & Agroclimate',
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

    final condition = widget.currentCondition ??
        switch (lang) {
          AppLanguage.id => 'Cerah Berawan',
          AppLanguage.jv => 'Padhang Mendhung',
          AppLanguage.en => 'Partly Cloudy',
        };

    final notice = widget.rainNotice ??
        switch (lang) {
          AppLanguage.id => 'Peluang hujan 40% sore ini. Aman untuk pemupukan pagi.',
          AppLanguage.jv => 'Peluang udan 40% sore iki. Aman kanggo mupuk esuk.',
          AppLanguage.en => '40% chance of rain this afternoon. Safe for morning fertilizing.',
        };

    final nowLabel = s.weatherNow;
    final tomorrowLabel = switch (lang) {
      AppLanguage.id => 'Besok',
      AppLanguage.jv => 'Sesuk',
      AppLanguage.en => 'Tomorrow',
    };
    final dayAfterTomorrowLabel = switch (lang) {
      AppLanguage.id => 'Lusa',
      AppLanguage.jv => 'Emben',
      AppLanguage.en => 'In 2 Days',
    };

    final forecastList = [
      {
        'time': nowLabel,
        'temp': '28°C',
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
        'icon': Icons.wb_sunny_outlined,
        'rain': '10%',
      },
      {
        'time': '14:00',
        'temp': '30°C',
        'icon': Icons.grain_rounded,
        'rain': '40%',
      },
      {
        'time': '16:00',
        'temp': '28°C',
        'icon': Icons.thunderstorm_rounded,
        'rain': '65%',
      },
      {
        'time': tomorrowLabel,
        'temp': '27°C',
        'icon': Icons.water_drop_rounded,
        'rain': '70%',
      },
      {
        'time': dayAfterTomorrowLabel,
        'temp': '30°C',
        'icon': Icons.wb_sunny_rounded,
        'rain': '20%',
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
          mainAxisSize: MainAxisSize.min,
          children: [
            // Header: Title & Calendar Link
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(6),
                      decoration: BoxDecoration(
                        color: HomeColors.skyBlueBg,
                        borderRadius: BorderRadius.circular(HomeRadius.sm),
                      ),
                      child: const Icon(
                        Icons.cloud_queue_rounded,
                        color: HomeColors.skyBlue,
                        size: 18,
                      ),
                    ),
                    const SizedBox(width: HomeSpacing.xs),
                    Column(
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
                        ),
                      ],
                    ),
                  ],
                ),
                TextButton(
                  onPressed: widget.onTapCalendar,
                  style: TextButton.styleFrom(
                    visualDensity: VisualDensity.compact,
                    foregroundColor: HomeColors.primaryGreen,
                    padding: const EdgeInsets.symmetric(horizontal: 8),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(
                        calendarLabel,
                        style: const TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                      const SizedBox(width: 2),
                      const Icon(Icons.chevron_right_rounded, size: 16),
                    ],
                  ),
                ),
              ],
            ),

            const SizedBox(height: HomeSpacing.md),

            // Main Weather Info Banner
            Container(
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  colors: [
                    const Color(0xFFF0FDF4),
                    HomeColors.skyBlueBg.withValues(alpha: 0.5),
                  ],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                ),
                borderRadius: BorderRadius.circular(HomeRadius.md),
                border: Border.all(
                  color: const Color(0xFFBAE6FD).withValues(alpha: 0.6),
                ),
              ),
              child: Row(
                children: [
                  // Temperature & Condition
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          crossAxisAlignment: CrossAxisAlignment.center,
                          children: [
                            Text(
                              widget.currentTemp,
                              style: const TextStyle(
                                color: HomeColors.textPrimary,
                                fontSize: 26,
                                fontWeight: FontWeight.w900,
                                height: 1,
                              ),
                            ),
                            const SizedBox(width: 8),
                            Flexible(
                              child: Text(
                                condition,
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: const TextStyle(
                                  color: HomeColors.textSecondary,
                                  fontSize: 13,
                                  fontWeight: FontWeight.w700,
                                ),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 6),
                        Text(
                          notice,
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(
                            color: HomeColors.textPrimary,
                            fontSize: 11.5,
                            fontWeight: FontWeight.w500,
                            height: 1.3,
                          ),
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(width: HomeSpacing.sm),

                  // Quick Humidity & Wind Chips
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      _buildMetricChip(
                        icon: Icons.water_drop_outlined,
                        label: widget.humidity,
                        color: HomeColors.skyBlue,
                      ),
                      const SizedBox(height: 4),
                      _buildMetricChip(
                        icon: Icons.air_rounded,
                        label: widget.windSpeed,
                        color: HomeColors.textSecondary,
                      ),
                    ],
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
