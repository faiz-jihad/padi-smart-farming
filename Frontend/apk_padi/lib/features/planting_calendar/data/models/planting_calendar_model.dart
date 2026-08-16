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

  factory PlantingCalendarModel.fromJson(Map<String, dynamic> json) {
    return PlantingCalendarModel(
      id: json['id'] as int,
      season: json['season'] as String? ?? 'rainy',
      seasonLabel: json['season_label'] as String? ?? 'Musim Hujan',
      seasonCode: json['season_code'] as String?,
      year: json['year'] as int? ?? DateTime.now().year,
      plantingStart: json['planting_start'] as String? ?? '',
      plantingEnd: json['planting_end'] as String? ?? '',
      plantingPattern: json['planting_pattern'] as String?,
      riceVariety: json['rice_variety'] as String?,
      recommendedArea: (json['recommended_area'] as num?)?.toDouble(),
      status: json['status'] as String? ?? 'active',
      source: json['source'] as String?,
      notes: json['notes'] as String?,
      resolvedLevel: json['resolved_level'] as String?,
      isFallback: json['is_fallback'] as bool? ?? false,
      isPlantingWindow: json['is_planting_window'] as bool? ?? false,
      daysUntilStart: json['days_until_start'] as int? ?? 0,
      daysUntilEnd: json['days_until_end'] as int? ?? 0,
      region: json['region'] as Map<String, dynamic>?,
    );
  }

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

  String get regionTitle {
    if (region != null) {
      final district = region!['district'] as String?;
      final regency = region!['regency'] as String?;
      if (district != null && regency != null) {
        return 'Kec. $district, $regency';
      }
      if (regency != null) return regency;
    }
    return 'Wilayah';
  }
}
