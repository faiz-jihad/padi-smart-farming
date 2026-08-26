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
      id: _toInt(json['id']),
      season: json['season']?.toString() ?? 'rainy',
      seasonLabel:
          json['season_label']?.toString() ?? 'Musim Hujan',
      seasonCode: json['season_code']?.toString(),
      year: _toNullableInt(json['year']) ??
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
          _toNullableDouble(json['recommended_area']),
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
          _toInt(json['days_until_start']),
      daysUntilEnd:
          _toInt(json['days_until_end']),
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

int _toInt(dynamic value) {
  if (value is num) return value.toInt();
  if (value is String) return int.tryParse(value.trim()) ?? 0;
  return 0;
}

int? _toNullableInt(dynamic value) {
  if (value == null) return null;
  if (value is num) return value.toInt();
  if (value is String) return int.tryParse(value.trim());
  return null;
}

double? _toNullableDouble(dynamic value) {
  if (value == null) return null;
  if (value is num) return value.toDouble();
  if (value is String) return double.tryParse(value.trim().replaceAll(',', '.'));
  return null;
}