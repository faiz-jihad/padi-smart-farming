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
      id: json['id'] as int,
      varietyId: json['variety_id'] as int,
      phase: json['phase']?.toString() ?? '',
      nutrient: json['nutrient']?.toString() ?? '',
      kgPerHa:
          double.tryParse(json['kg_per_ha']?.toString() ?? '0') ?? 0,
      source: json['source']?.toString() ?? '',
      version: json['version']?.toString() ?? '',
    );
  }
}