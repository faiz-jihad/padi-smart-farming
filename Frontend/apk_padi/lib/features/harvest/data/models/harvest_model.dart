class HarvestModel {
  final int id;
  final int cropSeasonId;
  final String harvestDate;
  final double quantity;
  final String unit;
  final String? qualityGrade;
  final double? moisturePercent;
  final String? verificationStatus;

  const HarvestModel({
    required this.id,
    required this.cropSeasonId,
    required this.harvestDate,
    required this.quantity,
    required this.unit,
    this.qualityGrade,
    this.moisturePercent,
    this.verificationStatus,
  });

  factory HarvestModel.fromJson(Map<String, dynamic> json) {
    return HarvestModel(
      id: _toInt(json['id']),
      cropSeasonId: _toInt(json['crop_season_id']),
      harvestDate: json['harvest_date']?.toString() ?? '',
      quantity: _toDouble(json['quantity']),
      unit: json['unit']?.toString() ?? 'kg',
      qualityGrade: json['quality_grade']?.toString(),
      moisturePercent: _toNullableDouble(json['moisture_percent']),
      verificationStatus: json['verification_status']?.toString(),
    );
  }
}

int _toInt(dynamic value) {
  if (value is num) return value.toInt();
  if (value is String) return int.tryParse(value.trim()) ?? 0;
  return 0;
}

double _toDouble(dynamic value) {
  if (value is num) return value.toDouble();
  if (value is String) return double.tryParse(value.trim().replaceAll(',', '.')) ?? 0.0;
  return 0.0;
}

double? _toNullableDouble(dynamic value) {
  if (value == null) return null;
  if (value is num) return value.toDouble();
  if (value is String) return double.tryParse(value.trim().replaceAll(',', '.'));
  return null;
}