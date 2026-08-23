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
    this.publishedAt,
    this.expiresAt,
    this.isOwner = false,
  });

  factory MarketListingModel.fromJson(
    Map<String, dynamic> json,
  ) {
    return MarketListingModel(
      id: _toInt(json['id']),
      farmerId: _toInt(json['farmer_id']),
      farmId: _toInt(json['farm_id']),
      cropSeasonId: _toInt(
        json['crop_season_id'],
      ),
      harvestId: _toInt(
        json['harvest_id'],
      ),
      commodity:
          json['commodity']?.toString() ?? '',
      quantity: _toDouble(
        json['quantity'],
      ),
      unit: json['unit']?.toString() ?? 'kg',
      pricePerUnit: _toDouble(
        json['price_per_unit'],
      ),
      description:
          json['description']?.toString(),
      salesLink:
          json['sales_link']?.toString(),
      imageUrl:
          json['image_url']?.toString(),
      status:
          json['status']?.toString() ?? '',
      publishedAt:
          json['published_at']?.toString(),
      expiresAt:
          json['expires_at']?.toString(),
      isOwner:
          json['is_owner'] == true,
    );
  }

  static int _toInt(dynamic value) {
    if (value is num) {
      return value.toInt();
    }

    return int.tryParse(
          value?.toString() ?? '',
        ) ??
        0;
  }

  static double _toDouble(dynamic value) {
    if (value is num) {
      return value.toDouble();
    }

    return double.tryParse(
          value?.toString() ?? '',
        ) ??
        0;
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
  final String? publishedAt;
  final String? expiresAt;
  final bool isOwner;
}