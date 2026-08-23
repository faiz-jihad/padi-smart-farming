class PurchaseContractModel {
  const PurchaseContractModel({
    required this.id,
    required this.listingId,
    required this.farmerId,
    required this.partnerId,
    this.offerId,
    required this.quantity,
    required this.agreedPrice,
    required this.totalAmount,
    required this.status,
    this.contractedAt,
    this.commodity,
    this.unit,
    this.farmerName,
    this.farmerEmail,
    this.partnerName,
    this.partnerEmail,
  });

  factory PurchaseContractModel.fromJson(
    Map<String, dynamic> json,
  ) {
    final listing = json['listing'] is Map
        ? Map<String, dynamic>.from(
            json['listing'] as Map,
          )
        : null;

    final farmer = json['farmer'] is Map
        ? Map<String, dynamic>.from(
            json['farmer'] as Map,
          )
        : null;

    final partner = json['partner'] is Map
        ? Map<String, dynamic>.from(
            json['partner'] as Map,
          )
        : null;

    return PurchaseContractModel(
      id: _toInt(json['id']),
      listingId: _toInt(json['listing_id']),
      farmerId: _toInt(json['farmer_id']),
      partnerId: _toInt(json['partner_id']),
      offerId: _toNullableInt(json['offer_id']),
      quantity: _toDouble(json['quantity']),
      agreedPrice: _toDouble(json['agreed_price']),
      totalAmount: _toDouble(json['total_amount']),
      status: json['status']?.toString() ?? '',
      contractedAt: json['contracted_at']?.toString(),
      commodity: listing?['commodity']?.toString(),
      unit: listing?['unit']?.toString(),
      farmerName: farmer?['name']?.toString(),
      farmerEmail: farmer?['email']?.toString(),
      partnerName: partner?['name']?.toString(),
      partnerEmail: partner?['email']?.toString(),
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

  static int? _toNullableInt(dynamic value) {
    if (value == null) {
      return null;
    }

    if (value is num) {
      return value.toInt();
    }

    return int.tryParse(
      value.toString(),
    );
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
  final int listingId;
  final int farmerId;
  final int partnerId;
  final int? offerId;
  final double quantity;
  final double agreedPrice;
  final double totalAmount;
  final String status;
  final String? contractedAt;
  final String? commodity;
  final String? unit;
  final String? farmerName;
  final String? farmerEmail;
  final String? partnerName;
  final String? partnerEmail;
}