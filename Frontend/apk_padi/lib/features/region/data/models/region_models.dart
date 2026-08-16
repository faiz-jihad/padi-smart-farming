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
      id: json['id'] as int,
      code: json['code'] as String,
      name: json['name'] as String,
      latitude: (json['latitude'] as num?)?.toDouble(),
      longitude: (json['longitude'] as num?)?.toDouble(),
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
      id: json['id'] as int,
      provinceId: json['province_id'] as int,
      code: json['code'] as String,
      name: json['name'] as String,
      type: json['type'] as String?,
      typeLabel: json['type_label'] as String?,
      latitude: (json['latitude'] as num?)?.toDouble(),
      longitude: (json['longitude'] as num?)?.toDouble(),
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
      id: json['id'] as int,
      regencyId: json['regency_id'] as int,
      code: json['code'] as String,
      name: json['name'] as String,
      latitude: (json['latitude'] as num?)?.toDouble(),
      longitude: (json['longitude'] as num?)?.toDouble(),
      hasBoundary: json['has_boundary'] as bool? ?? false,
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
      id: json['id'] as int,
      districtId: json['district_id'] as int,
      code: json['code'] as String,
      name: json['name'] as String,
      type: json['type'] as String?,
      typeLabel: json['type_label'] as String?,
      latitude: (json['latitude'] as num?)?.toDouble(),
      longitude: (json['longitude'] as num?)?.toDouble(),
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
      formattedAddress: json['formatted_address'] as String? ?? '',
      resolutionMethod: json['resolution_method'] as String? ?? '',
    );
  }

  final ProvinceModel? province;
  final RegencyModel? regency;
  final DistrictModel? district;
  final VillageModel? village;
  final String formattedAddress;
  final String resolutionMethod;
}
