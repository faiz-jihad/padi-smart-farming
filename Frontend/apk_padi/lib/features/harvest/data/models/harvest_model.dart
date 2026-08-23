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
      id: int.parse(json['id'].toString()),
      cropSeasonId: int.parse(
        json['crop_season_id'].toString(),
      ),
      harvestDate: json['harvest_date']?.toString() ?? '',
      quantity: double.parse(
        json['quantity'].toString(),
      ),
      unit: json['unit']?.toString() ?? 'kg',
      qualityGrade: json['quality_grade']?.toString(),
      moisturePercent:
          json['moisture_percent'] == null
              ? null
              : double.tryParse(
                  json['moisture_percent'].toString(),
                ),
      verificationStatus:
          json['verification_status']?.toString(),
    );
  }
}