class SoilTypeModel {
  final int id;
  final String name;
  final String code;
  final String? description;
  final bool isActive;

  const SoilTypeModel({
    required this.id,
    required this.name,
    required this.code,
    this.description,
    this.isActive = true,
  });

  factory SoilTypeModel.fromJson(Map<String, dynamic> json) {
    return SoilTypeModel(
      id: json['id'] is int
          ? json['id'] as int
          : int.tryParse(json['id']?.toString() ?? '0') ?? 0,
      name: json['name']?.toString() ?? '',
      code: json['code']?.toString() ?? '',
      description: json['description']?.toString(),
      isActive: json['is_active'] == true || json['is_active'] == 1 || json['is_active'] == '1',
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'code': code,
      'description': description,
      'is_active': isActive,
    };
  }

  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      other is SoilTypeModel &&
          runtimeType == other.runtimeType &&
          id == other.id &&
          code == other.code;

  @override
  int get hashCode => id.hashCode ^ code.hashCode;
}
