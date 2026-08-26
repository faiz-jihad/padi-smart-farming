class FertilizerRuleModel {
  final int id;
  final int varietyId;
  final String phase;
  final String nutrient;
  final double kgPerHa;
  final String source;
  final String version;

  const FertilizerRuleModel({
    required this.id,
    required this.varietyId,
    required this.phase,
    required this.nutrient,
    required this.kgPerHa,
    required this.source,
    required this.version,
  });

  factory FertilizerRuleModel.fromJson(Map<String, dynamic> json) {
    return FertilizerRuleModel(
      id: _toInt(json['id']),
      varietyId: _toInt(json['variety_id']),
      phase: json['phase']?.toString() ?? '',
      nutrient: json['nutrient']?.toString() ?? '',
      kgPerHa: _toDouble(json['kg_per_ha']),
      source: json['source']?.toString() ?? '',
      version: json['version']?.toString() ?? '',
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