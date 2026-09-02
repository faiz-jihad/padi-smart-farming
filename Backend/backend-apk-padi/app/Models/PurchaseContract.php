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
    this.farmerPhone,
    this.partnerName,
    this.partnerEmail,
    this.partnerPhone,
    this.imageUrl,
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

      listingId: _toInt(
        json['listing_id'],
      ),

      farmerId: _toInt(
        json['farmer_id'],
      ),

      partnerId: _toInt(
        json['partner_id'],
      ),

      offerId: _toNullableInt(
        json['offer_id'],
      ),

      quantity: _toDouble(
        json['quantity'],
      ),

      agreedPrice: _toDouble(
        json['agreed_price'],
      ),

      totalAmount: _toDouble(
        json['total_amount'],
      ),

      status: json['status']?.toString() ?? '',

      contractedAt:
          json['contracted_at']?.toString(),

      commodity:
          json['commodity']?.toString() ??
          listing?['commodity']?.toString(),

      unit:
          json['unit']?.toString() ??
          listing?['unit']?.toString(),

      // =========================
      // FARMER
      // =========================

      farmerName:
          json['farmer_name']?.toString() ??
          farmer?['name']?.toString(),

      farmerEmail:
          json['farmer_email']?.toString() ??
          farmer?['email']?.toString(),

      farmerPhone:
          json['farmer_phone']?.toString() ??
          farmer?['phone']?.toString(),

      // =========================
      // PARTNER / BUYER
      // =========================

      partnerName:
          json['partner_name']?.toString() ??
          partner?['name']?.toString(),

      partnerEmail:
          json['partner_email']?.toString() ??
          partner?['email']?.toString(),

      partnerPhone:
          json['partner_phone']?.toString() ??
          partner?['phone']?.toString(),

      // =========================
      // LISTING
      // =========================

      imageUrl:
          listing?['image_url']?.toString(),
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

  static int? _toNullableInt(
    dynamic value,
  ) {
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

  static double _toDouble(
    dynamic value,
  ) {
    if (value is num) {
      return value.toDouble();
    }

    return double.tryParse(
          value?.toString() ?? '',
        ) ??
        0;
  }

  // =========================
  // IDENTITAS KONTRAK
  // =========================

  final int id;

  final int listingId;

  final int farmerId;

  final int partnerId;

  final int? offerId;

  // =========================
  // TRANSAKSI
  // =========================

  final double quantity;

  final double agreedPrice;

  final double totalAmount;

  final String status;

  final String? contractedAt;

  // =========================
  // KOMODITAS
  // =========================

  final String? commodity;

  final String? unit;

  // =========================
  // FARMER / PENJUAL
  // =========================

  final String? farmerName;

  final String? farmerEmail;

  final String? farmerPhone;

  // =========================
  // PARTNER / PEMBELI
  // =========================

  final String? partnerName;

  final String? partnerEmail;

  final String? partnerPhone;

  // =========================
  // LISTING
  // =========================

  final String? imageUrl;
}
