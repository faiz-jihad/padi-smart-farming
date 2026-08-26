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
    this.boundaryCoordinates = const [],
    this.province,
    this.regency,
    this.district,
    this.village,
  });

  factory FarmModel.fromJson(Map<String, dynamic> json) {
    return FarmModel(
      id: _toInt(json['id']),
      farmerUserId: _toInt(json['farmer_user_id']),
      name: json['name']?.toString() ?? '',
      areaHa: _toDouble(json['area_ha']),
      latitude: _toDouble(json['latitude']),
      longitude: _toDouble(json['longitude']),
      irrigationType: json['irrigation_type']?.toString() ?? 'irrigated',
      irrigationNotes: json['irrigation_notes']?.toString(),
      soilType: json['soil_type']?.toString(),
      status: json['status']?.toString() ?? 'active',
      provinceId: _toNullableInt(json['province_id']),
      regencyId: _toNullableInt(json['regency_id']),
      districtId: _toNullableInt(json['district_id']),
      villageId: _toNullableInt(json['village_id']),
      boundaryCoordinates: _parseBoundaryCoordinates(
        json['boundary_coordinates'],
      ),
      province: json['province'] is Map
          ? ProvinceModel.fromJson(
              Map<String, dynamic>.from(json['province'] as Map),
            )
          : null,
      regency: json['regency'] is Map
          ? RegencyModel.fromJson(
              Map<String, dynamic>.from(json['regency'] as Map),
            )
          : null,
      district: json['district'] is Map
          ? DistrictModel.fromJson(
              Map<String, dynamic>.from(json['district'] as Map),
            )
          : null,
      village: json['village'] is Map
          ? VillageModel.fromJson(
              Map<String, dynamic>.from(json['village'] as Map),
            )
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
  final List<FarmBoundaryPoint> boundaryCoordinates;
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

  static List<FarmBoundaryPoint> _parseBoundaryCoordinates(Object? value) {
    if (value is! List) {
      return const [];
    }

    return value
        .whereType<Map>()
        .map(
          (e) => FarmBoundaryPoint.fromJson(
            Map<String, dynamic>.from(e),
          ),
        )
        .toList(growable: false);
  }

  static double _toDouble(Object? value) {
    if (value is num) {
      return value.toDouble();
    }

    if (value is String) {
      return double.tryParse(value.trim().replaceAll(',', '.')) ?? 0.0;
    }

    return 0.0;
  }

  static int _toInt(Object? value) {
    return _toNullableInt(value) ?? 0;
  }

  static int? _toNullableInt(Object? value) {
    if (value is int) {
      return value;
    }

    if (value is num) {
      return value.toInt();
    }

    if (value is String) {
      return int.tryParse(value.trim());
    }

    return null;
  }
}

class FarmBoundaryPoint {
  const FarmBoundaryPoint({
    required this.lat,
    required this.lng,
  });

  factory FarmBoundaryPoint.fromJson(Map<String, dynamic> json) {
    return FarmBoundaryPoint(
      lat: FarmModel._toDouble(json['lat'] ?? json['latitude']),
      lng: FarmModel._toDouble(json['lng'] ?? json['longitude']),
    );
  }

  final double lat;
  final double lng;
}
