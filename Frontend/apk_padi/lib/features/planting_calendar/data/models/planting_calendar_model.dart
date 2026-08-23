class PlantingCalendarModel {
  const PlantingCalendarModel({
    required this.id,
    required this.season,
    required this.seasonLabel,
    this.seasonCode,
    required this.year,
    required this.plantingStart,
    required this.plantingEnd,
    this.plantingPattern,
    this.riceVariety,
    this.recommendedArea,
    required this.status,
    this.source,
    this.notes,
    this.resolvedLevel,
    this.isFallback = false,
    this.isPlantingWindow = false,
    this.daysUntilStart = 0,
    this.daysUntilEnd = 0,
    this.region,
  });

  final int id;
  final String season;
  final String seasonLabel;
  final String? seasonCode;
  final int year;
  final String plantingStart;
  final String plantingEnd;
  final String? plantingPattern;
  final String? riceVariety;
  final double? recommendedArea;
  final String status;
  final String? source;
  final String? notes;
  final String? resolvedLevel;
  final bool isFallback;
  final bool isPlantingWindow;
  final int daysUntilStart;
  final int daysUntilEnd;
  final Map<String, dynamic>? region;

  factory PlantingCalendarModel.fromJson(
    Map<String, dynamic> json,
  ) {
    return PlantingCalendarModel(
      id: (json['id'] as num).toInt(),
      season: json['season']?.toString() ?? 'rainy',
      seasonLabel:
          json['season_label']?.toString() ?? 'Musim Hujan',
      seasonCode: json['season_code']?.toString(),
      year: (json['year'] as num?)?.toInt() ??
          DateTime.now().year,
      plantingStart:
          json['planting_start']?.toString() ?? '',
      plantingEnd:
          json['planting_end']?.toString() ?? '',
      plantingPattern:
          json['planting_pattern']?.toString(),
      riceVariety:
          json['rice_variety']?.toString(),
      recommendedArea:
          (json['recommended_area'] as num?)?.toDouble(),
      status:
          json['status']?.toString() ?? 'active',
      source:
          json['source']?.toString(),
      notes:
          json['notes']?.toString(),
      resolvedLevel:
          json['resolved_level']?.toString(),
      isFallback:
          json['is_fallback'] as bool? ?? false,
      isPlantingWindow:
          json['is_planting_window'] as bool? ?? false,
      daysUntilStart:
          (json['days_until_start'] as num?)?.toInt() ?? 0,
      daysUntilEnd:
          (json['days_until_end'] as num?)?.toInt() ?? 0,
      region:
          json['region'] is Map
              ? Map<String, dynamic>.from(
                  json['region'] as Map,
                )
              : null,
    );
  }

  String get regionTitle {
    if (region != null) {
      final district = region!['district']?.toString();
      final regency = region!['regency']?.toString();

      if (district != null &&
          district.isNotEmpty &&
          regency != null &&
          regency.isNotEmpty) {
        return 'Kec. $district, $regency';
      }

      if (regency != null && regency.isNotEmpty) {
        return regency;
      }
    }

    return 'Wilayah Anda';
  }
}