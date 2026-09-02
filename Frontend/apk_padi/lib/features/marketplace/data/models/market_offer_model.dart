class MarketOfferModel {
  const MarketOfferModel({
    required this.id,
    required this.listingId,
    required this.partnerId,
    required this.offeredPrice,
    required this.quantity,
    required this.status,
    this.message,
    this.lastOfferBy,
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

    final rawLastOfferBy = json['last_offer_by']
        ?.toString()
        .trim()
        .toLowerCase();

    return MarketOfferModel(
      id: _toInt(json['id']),
      listingId: _toInt(json['listing_id']),
      partnerId: _toInt(json['partner_id']),
      offeredPrice: _toDouble(json['offered_price']),
      quantity: _toDouble(json['quantity']),
      status: json['status']?.toString().trim().toLowerCase() ?? 'pending',
      message: json['message']?.toString(),
      lastOfferBy: rawLastOfferBy?.isEmpty == true
          ? null
          : rawLastOfferBy,
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
  final String status;
  final String? message;
  final String? lastOfferBy;
  final String? partnerName;
  final String? partnerEmail;
  final String? partnerPhone;
  final String? commodity;
  final String? unit;

  bool get isPending {
    return status == 'pending';
  }

  bool get isAccepted {
    return status == 'accepted';
  }

  bool get isRejected {
    return status == 'rejected';
  }

  bool get isCountered {
    return status == 'countered';
  }

  bool get isActive {
    return !isAccepted && !isRejected;
  }

  bool get isLastOfferByFarmer {
    return lastOfferBy == 'farmer';
  }

  bool get isLastOfferByBuyer {
    return lastOfferBy == 'buyer';
  }

  bool get canFarmerAct {
    if (!isActive) {
      return false;
    }

    return isLastOfferByBuyer;
  }

  bool get canBuyerAct {
    if (!isActive) {
      return false;
    }

    return isLastOfferByFarmer;
  }

  bool get waitingForFarmer {
    if (!isActive) {
      return false;
    }

    return isLastOfferByBuyer;
  }

  bool get waitingForBuyer {
    if (!isActive) {
      return false;
    }

    return isLastOfferByFarmer;
  }

  String get lastOfferByLabel {
    if (isLastOfferByFarmer) {
      return 'Petani';
    }

    if (isLastOfferByBuyer) {
      return 'Pembeli';
    }

    return '-';
  }

  String get waitingForLabel {
    if (waitingForFarmer) {
      return 'Menunggu respons petani';
    }

    if (waitingForBuyer) {
      return 'Menunggu respons pembeli';
    }

    if (isAccepted) {
      return 'Penawaran disetujui';
    }

    if (isRejected) {
      return 'Penawaran ditolak';
    }

    return '-';
  }
}