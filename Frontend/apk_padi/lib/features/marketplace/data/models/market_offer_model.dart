class MarketOfferModel {
  const MarketOfferModel({
    required this.id,
    required this.listingId,
    required this.partnerId,
    required this.offeredPrice,
    required this.quantity,
    this.message,
    required this.status,
    this.partnerName,
    this.partnerEmail,
    this.partnerPhone,
    this.commodity,
    this.unit,
  });

  factory MarketOfferModel.fromJson(
    Map<String, dynamic> json,
  ) {
    final partner = json['partner'] is Map
        ? Map<String, dynamic>.from(json['partner'])
        : null;

    final listing = json['listing'] is Map
        ? Map<String, dynamic>.from(json['listing'])
        : null;

    return MarketOfferModel(
      id: _toInt(json['id']),
      listingId: _toInt(json['listing_id']),
      partnerId: _toInt(json['partner_id']),
      offeredPrice: _toDouble(json['offered_price']),
      quantity: _toDouble(json['quantity']),
      message: json['message']?.toString(),
      status: json['status']?.toString() ?? 'pending',
      partnerName: partner?['name']?.toString(),
      partnerEmail: partner?['email']?.toString(),
      partnerPhone: partner?['phone']?.toString(),
      commodity: listing?['commodity']?.toString(),
      unit: listing?['unit']?.toString(),
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
  final int listingId;
  final int partnerId;
  final double offeredPrice;
  final double quantity;
  final String? message;
  final String status;
  final String? partnerName;
  final String? partnerEmail;
  final String? partnerPhone;
  final String? commodity;
  final String? unit;

  bool get isPending => status == 'pending';

  bool get isAccepted => status == 'accepted';

  bool get isRejected => status == 'rejected';
}