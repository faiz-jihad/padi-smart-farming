import 'package:padi/features/region/data/models/region_models.dart';

class FarmModel {
  const FarmModel({
    required this.id,
    required this.farmerUserId,
    required this.name,
    required this.areaHa,
    required this.latitude,
    required this.longitude,
    required this.irrigationType,
    this.irrigationNotes,
    this.soilType,
    this.status = 'active',
    this.provinceId,
    this.regencyId,
    this.districtId,
    this.villageId,
    this.province,
    this.regency,
    this.district,
    this.village,
  });

  factory FarmModel.fromJson(Map<String, dynamic> json) {
    return FarmModel(
      id: json['id'] as int,
      farmerUserId: json['farmer_user_id'] as int? ?? 0,
      name: json['name'] as String? ?? '',
      areaHa: (json['area_ha'] as num?)?.toDouble() ?? 0.0,
      latitude: (json['latitude'] as num?)?.toDouble() ?? 0.0,
      longitude: (json['longitude'] as num?)?.toDouble() ?? 0.0,
      irrigationType: json['irrigation_type'] as String? ?? 'irrigated',
      irrigationNotes: json['irrigation_notes'] as String?,
      soilType: json['soil_type'] as String?,
      status: json['status'] as String? ?? 'active',
      provinceId: json['province_id'] as int?,
      regencyId: json['regency_id'] as int?,
      districtId: json['district_id'] as int?,
      villageId: json['village_id'] as int?,
      province: json['province'] != null
          ? ProvinceModel.fromJson(json['province'] as Map<String, dynamic>)
          : null,
      regency: json['regency'] != null
          ? RegencyModel.fromJson(json['regency'] as Map<String, dynamic>)
          : null,
      district: json['district'] != null
          ? DistrictModel.fromJson(json['district'] as Map<String, dynamic>)
          : null,
      village: json['village'] != null
          ? VillageModel.fromJson(json['village'] as Map<String, dynamic>)
          : null,
    );
  }

  final int id;
  final int farmerUserId;
  final String name;
  final double areaHa;
  final double latitude;
  final double longitude;
  final String irrigationType;
  final String? irrigationNotes;
  final String? soilType;
  final String status;
  final int? provinceId;
  final int? regencyId;
  final int? districtId;
  final int? villageId;
  final ProvinceModel? province;
  final RegencyModel? regency;
  final DistrictModel? district;
  final VillageModel? village;

  String get locationDescription {
    final parts = [
      if (village != null) village!.name,
      if (district != null) 'Kec. ${district!.name}',
      if (regency != null) regency!.name,
    ];
    return parts.isEmpty ? 'Lokasi belum terdata' : parts.join(', ');
  }
}
