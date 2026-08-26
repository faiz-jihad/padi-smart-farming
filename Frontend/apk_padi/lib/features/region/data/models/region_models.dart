class ProvinceModel {
  const ProvinceModel({
    required this.id,
    required this.code,
    required this.name,
    this.latitude,
    this.longitude,
  });

  factory ProvinceModel.fromJson(Map<String, dynamic> json) {
    return ProvinceModel(
      id: _toInt(json['id']),
      code: json['code']?.toString() ?? '',
      name: json['name']?.toString() ?? '',
      latitude: _toNullableDouble(json['latitude']),
      longitude: _toNullableDouble(json['longitude']),
    );
  }

  final int id;
  final String code;
  final String name;
  final double? latitude;
  final double? longitude;
}

class RegencyModel {
  const RegencyModel({
    required this.id,
    required this.provinceId,
    required this.code,
    required this.name,
    this.type,
    this.typeLabel,
    this.latitude,
    this.longitude,
  });

  factory RegencyModel.fromJson(Map<String, dynamic> json) {
    return RegencyModel(
      id: _toInt(json['id']),
      provinceId: _toInt(json['province_id']),
      code: json['code']?.toString() ?? '',
      name: json['name']?.toString() ?? '',
      type: json['type']?.toString(),
      typeLabel: json['type_label']?.toString(),
      latitude: _toNullableDouble(json['latitude']),
      longitude: _toNullableDouble(json['longitude']),
    );
  }

  final int id;
  final int provinceId;
  final String code;
  final String name;
  final String? type;
  final String? typeLabel;
  final double? latitude;
  final double? longitude;
}

class DistrictModel {
  const DistrictModel({
    required this.id,
    required this.regencyId,
    required this.code,
    required this.name,
    this.latitude,
    this.longitude,
    this.hasBoundary = false,
  });

  factory DistrictModel.fromJson(Map<String, dynamic> json) {
    return DistrictModel(
      id: _toInt(json['id']),
      regencyId: _toInt(json['regency_id']),
      code: json['code']?.toString() ?? '',
      name: json['name']?.toString() ?? '',
      latitude: _toNullableDouble(json['latitude']),
      longitude: _toNullableDouble(json['longitude']),
      hasBoundary: json['has_boundary'] == true,
    );
  }

  final int id;
  final int regencyId;
  final String code;
  final String name;
  final double? latitude;
  final double? longitude;
  final bool hasBoundary;
}

class VillageModel {
  const VillageModel({
    required this.id,
    required this.districtId,
    required this.code,
    required this.name,
    this.type,
    this.typeLabel,
    this.latitude,
    this.longitude,
  });

  factory VillageModel.fromJson(Map<String, dynamic> json) {
    return VillageModel(
      id: _toInt(json['id']),
      districtId: _toInt(json['district_id']),
      code: json['code']?.toString() ?? '',
      name: json['name']?.toString() ?? '',
      type: json['type']?.toString(),
      typeLabel: json['type_label']?.toString(),
      latitude: _toNullableDouble(json['latitude']),
      longitude: _toNullableDouble(json['longitude']),
    );
  }

  final int id;
  final int districtId;
  final String code;
  final String name;
  final String? type;
  final String? typeLabel;
  final double? latitude;
  final double? longitude;
}

class ResolvedLocationModel {
  const ResolvedLocationModel({
    this.province,
    this.regency,
    this.district,
    this.village,
    required this.formattedAddress,
    required this.resolutionMethod,
  });

  factory ResolvedLocationModel.fromJson(Map<String, dynamic> json) {
    return ResolvedLocationModel(
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
      formattedAddress: json['formatted_address']?.toString() ?? '',
      resolutionMethod: json['resolution_method']?.toString() ?? '',
    );
  }

  final ProvinceModel? province;
  final RegencyModel? regency;
  final DistrictModel? district;
  final VillageModel? village;
  final String formattedAddress;
  final String resolutionMethod;
}

int _toInt(dynamic value) {
  if (value is num) return value.toInt();
  if (value is String) return int.tryParse(value.trim()) ?? 0;
  return 0;
}

double? _toNullableDouble(dynamic value) {
  if (value == null) return null;
  if (value is num) return value.toDouble();
  if (value is String) return double.tryParse(value.trim().replaceAll(',', '.'));
  return null;
}
