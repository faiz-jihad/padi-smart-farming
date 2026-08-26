class CropSeasonModel {
  const CropSeasonModel({
    required this.id,
    required this.farmId,
    this.varietyId,
    this.plannedPlantingDate,
    this.plantingDate,
    this.estimatedHarvestDate,
    this.status,
  });

  final int id;
  final int farmId;
  final int? varietyId;
  final String? plannedPlantingDate;
  final String? plantingDate;
  final String? estimatedHarvestDate;
  final String? status;

  factory CropSeasonModel.fromJson(Map<String, dynamic> json) {
    return CropSeasonModel(
      id: _toInt(json['id']),
      farmId: _toInt(json['farm_id']),
      varietyId: _toNullableInt(json['variety_id']),
      plannedPlantingDate:
          json['planned_planting_date']?.toString(),
      plantingDate: json['planting_date']?.toString(),
      estimatedHarvestDate:
          json['estimated_harvest_date']?.toString(),
      status: json['status']?.toString(),
    );
  }

  String get statusLabel {
    switch (status) {
      case 'planned':
        return 'Direncanakan';
      case 'active':
        return 'Aktif';
      case 'completed':
        return 'Selesai';
      case 'cancelled':
        return 'Dibatalkan';
      default:
        return status ?? '-';
    }
  }

  DateTime? get startDate {
    final value = plantingDate ?? plannedPlantingDate;

    if (value == null || value.isEmpty) {
      return null;
    }

    return DateTime.tryParse(value);
  }

  int? get dayNumber {
    final date = startDate;

    if (date == null) {
      return null;
    }

    final today = DateTime.now();

    final start = DateTime(
      date.year,
      date.month,
      date.day,
    );

    final current = DateTime(
      today.year,
      today.month,
      today.day,
    );

    final difference = current.difference(start).inDays;

    if (difference < 0) {
      return 0;
    }

    return difference + 1;
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