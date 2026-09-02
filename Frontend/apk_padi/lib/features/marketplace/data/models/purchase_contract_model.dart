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
    // ==============================
    // LISTING
    // ==============================
    final listing = json['listing'] is Map
        ? Map<String, dynamic>.from(
            json['listing'] as Map,
          )
        : null;

    // ==============================
    // FARMER / PETANI
    // ==============================
    final farmer = json['farmer'] is Map
        ? Map<String, dynamic>.from(
            json['farmer'] as Map,
          )
        : null;

    // ==============================
    // PARTNER / BUYER
    // ==============================
    final partner = json['partner'] is Map
        ? Map<String, dynamic>.from(
            json['partner'] as Map,
          )
        : null;

    return PurchaseContractModel(
      // ==============================
      // IDENTITAS KONTRAK
      // ==============================
      id: _toInt(
        json['id'],
      ),

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

      // ==============================
      // TRANSAKSI
      // ==============================
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

      contractedAt: json['contracted_at']?.toString(),

      // ==============================
      // KOMODITAS
      // ==============================
      commodity: json['commodity']?.toString() ??
          listing?['commodity']?.toString(),

      unit: json['unit']?.toString() ??
          listing?['unit']?.toString(),

      // ==============================
      // DATA PETANI
      // ==============================
      farmerName: json['farmer_name']?.toString() ??
          farmer?['name']?.toString(),

      farmerEmail: json['farmer_email']?.toString() ??
          farmer?['email']?.toString(),

      farmerPhone: json['farmer_phone']?.toString() ??
          farmer?['phone']?.toString() ??
          farmer?['phone_number']?.toString(),

      // ==============================
      // DATA BUYER / PARTNER
      // ==============================
      partnerName: json['partner_name']?.toString() ??
          partner?['name']?.toString(),

      partnerEmail: json['partner_email']?.toString() ??
          partner?['email']?.toString(),

      // TAMBAHAN PENTING:
      // Nomor WhatsApp pihak buyer/partner
      partnerPhone: json['partner_phone']?.toString() ??
          partner?['phone']?.toString() ??
          partner?['phone_number']?.toString(),

      // ==============================
      // GAMBAR PRODUK
      // ==============================
      imageUrl: listing?['image_url']?.toString(),
    );
  }

  // ============================================================
  // INTEGER PARSER
  // ============================================================
  static int _toInt(
    dynamic value,
  ) {
    if (value is num) {
      return value.toInt();
    }

    return int.tryParse(
          value?.toString() ?? '',
        ) ??
        0;
  }

  // ============================================================
  // NULLABLE INTEGER PARSER
  // ============================================================
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

  // ============================================================
  // DOUBLE PARSER
  // ============================================================
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

  // ============================================================
  // CONTRACT ID
  // ============================================================
  final int id;

  // ============================================================
  // LISTING ID
  // ============================================================
  final int listingId;

  // ============================================================
  // FARMER ID
  // ============================================================
  final int farmerId;

  // ============================================================
  // PARTNER / BUYER ID
  // ============================================================
  final int partnerId;

  // ============================================================
  // OFFER ID
  // ============================================================
  final int? offerId;

  // ============================================================
  // QUANTITY
  // ============================================================
  final double quantity;

  // ============================================================
  // HARGA YANG DISEPAKATI
  // ============================================================
  final double agreedPrice;

  // ============================================================
  // TOTAL TRANSAKSI
  // ============================================================
  final double totalAmount;

  // ============================================================
  // STATUS KONTRAK
  // ============================================================
  final String status;

  // ============================================================
  // WAKTU KONTRAK
  // ============================================================
  final String? contractedAt;

  // ============================================================
  // KOMODITAS
  // ============================================================
  final String? commodity;

  // ============================================================
  // SATUAN
  // ============================================================
  final String? unit;

  // ============================================================
  // DATA PETANI
  // ============================================================
  final String? farmerName;
  final String? farmerEmail;
  final String? farmerPhone;

  // ============================================================
  // DATA BUYER / PARTNER
  // ============================================================
  final String? partnerName;
  final String? partnerEmail;

  // NOMOR TELEPON / WHATSAPP BUYER
  final String? partnerPhone;

  // ============================================================
  // GAMBAR LISTING
  // ============================================================
  final String? imageUrl;
}