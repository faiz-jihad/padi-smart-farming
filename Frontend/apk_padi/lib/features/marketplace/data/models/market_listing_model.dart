class MarketListingModel {
  const MarketListingModel({
    required this.id,
    required this.farmerId,
    required this.farmId,
    required this.cropSeasonId,
    required this.harvestId,
    required this.commodity,
    required this.quantity,
    required this.unit,
    required this.pricePerUnit,
    required this.status,
    this.description,
    this.salesLink,
    this.imageUrl,
    this.images = const [],
    this.publishedAt,
    this.expiresAt,
    this.isOwner = false,
    this.farmerName,
    this.farmerPhone,
    this.farmName,
    this.farmAreaHa,
    this.varietyName,
    this.plantingDate,
    this.moisturePercent,
    this.qualityGrade,
  });

  factory MarketListingModel.fromJson(
    Map<String, dynamic> json,
  ) {
    final farmerMap = json['farmer'] is Map ? json['farmer'] as Map : null;
    final farmMap = json['farm'] is Map ? json['farm'] as Map : null;

    final imagesData = json['images'];

    final parsedImages = imagesData is List
        ? imagesData
            .whereType<Map>()
            .map((item) => Map<String, dynamic>.from(item))
            .toList()
        : <Map<String, dynamic>>[];

    String? resolvedImageUrl = json['image_url']?.toString();

    if ((resolvedImageUrl == null || resolvedImageUrl.isEmpty) &&
        parsedImages.isNotEmpty) {
      final firstImage = parsedImages.first['image_url'];

      if (firstImage != null && firstImage.toString().isNotEmpty) {
        resolvedImageUrl = firstImage.toString();
      }
    }

    return MarketListingModel(
      id: _toInt(json['id']),
      farmerId: _toInt(json['farmer_id']),
      farmId: _toInt(json['farm_id']),
      cropSeasonId: _toInt(json['crop_season_id']),
      harvestId: _toInt(json['harvest_id']),
      commodity: json['commodity']?.toString() ?? '',
      quantity: _toDouble(json['quantity']),
      unit: json['unit']?.toString() ?? 'kg',
      pricePerUnit: _toDouble(json['price_per_unit']),
      description: json['description']?.toString(),
      salesLink: json['sales_link']?.toString(),
      imageUrl: resolvedImageUrl,
      images: parsedImages,
      status: json['status']?.toString() ?? 'published',
      publishedAt: json['published_at']?.toString(),
      expiresAt: json['expires_at']?.toString(),
      isOwner: json['is_owner'] == true,
      farmerName: json['farmer_name']?.toString() ??
          farmerMap?['name']?.toString() ??
          'Petani P.A.D.I.',
      farmerPhone: json['farmer_phone']?.toString() ??
          farmerMap?['phone']?.toString() ??
          '+6281234567890',
      farmName: json['farm_name']?.toString() ??
          farmMap?['name']?.toString() ??
          'Lahan Pertanian',
      farmAreaHa: _toDouble(
        json['farm_area_ha'] ?? farmMap?['area_ha'],
      ),
      varietyName: json['variety_name']?.toString(),
      plantingDate: json['planting_date']?.toString(),
      moisturePercent: _toDouble(json['moisture_percent']),
      qualityGrade: json['quality_grade']?.toString() ?? 'Grade A',
    );
  }

  static int _toInt(dynamic value) {
    if (value is num) {
      return value.toInt();
    }

    return int.tryParse(value?.toString() ?? '') ?? 0;
  }

  static double _toDouble(dynamic value) {
    if (value is num) {
      return value.toDouble();
    }

    return double.tryParse(value?.toString() ?? '') ?? 0;
  }

  final int id;
  final int farmerId;
  final int farmId;
  final int cropSeasonId;
  final int harvestId;
  final String commodity;
  final double quantity;
  final String unit;
  final double pricePerUnit;
  final String status;
  final String? description;
  final String? salesLink;
  final String? imageUrl;
  final List<Map<String, dynamic>> images;
  final String? publishedAt;
  final String? expiresAt;
  final bool isOwner;
  final String? farmerName;
  final String? farmerPhone;
  final String? farmName;
  final double? farmAreaHa;
  final String? varietyName;
  final String? plantingDate;
  final double? moisturePercent;
  final String? qualityGrade;
}