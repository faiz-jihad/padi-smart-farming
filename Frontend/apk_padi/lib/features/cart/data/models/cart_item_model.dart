class CartItemModel {
  const CartItemModel({
    required this.listingId,
    required this.farmerId,
    required this.commodity,
    required this.unit,
    required this.pricePerUnit,
    required this.quantity,
    required this.maxQuantity,
    this.farmerName,
    this.farmerPhone,
    this.farmName,
    this.imageUrl,
    this.varietyName,
    this.qualityGrade,
    this.isSelected = true,
  });

  final int listingId;
  final int farmerId;
  final String commodity;
  final String unit;
  final double pricePerUnit;
  final double quantity;
  final double maxQuantity;
  final String? farmerName;
  final String? farmerPhone;
  final String? farmName;
  final String? imageUrl;
  final String? varietyName;
  final String? qualityGrade;
  final bool isSelected;

  double get subtotal => pricePerUnit * quantity;

  CartItemModel copyWith({
    int? listingId,
    int? farmerId,
    String? commodity,
    String? unit,
    double? pricePerUnit,
    double? quantity,
    double? maxQuantity,
    String? farmerName,
    String? farmerPhone,
    String? farmName,
    String? imageUrl,
    String? varietyName,
    String? qualityGrade,
    bool? isSelected,
  }) {
    return CartItemModel(
      listingId: listingId ?? this.listingId,
      farmerId: farmerId ?? this.farmerId,
      commodity: commodity ?? this.commodity,
      unit: unit ?? this.unit,
      pricePerUnit: pricePerUnit ?? this.pricePerUnit,
      quantity: quantity ?? this.quantity,
      maxQuantity: maxQuantity ?? this.maxQuantity,
      farmerName: farmerName ?? this.farmerName,
      farmerPhone: farmerPhone ?? this.farmerPhone,
      farmName: farmName ?? this.farmName,
      imageUrl: imageUrl ?? this.imageUrl,
      varietyName: varietyName ?? this.varietyName,
      qualityGrade: qualityGrade ?? this.qualityGrade,
      isSelected: isSelected ?? this.isSelected,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'listingId': listingId,
      'farmerId': farmerId,
      'commodity': commodity,
      'unit': unit,
      'pricePerUnit': pricePerUnit,
      'quantity': quantity,
      'maxQuantity': maxQuantity,
      'farmerName': farmerName,
      'farmerPhone': farmerPhone,
      'farmName': farmName,
      'imageUrl': imageUrl,
      'varietyName': varietyName,
      'qualityGrade': qualityGrade,
      'isSelected': isSelected,
    };
  }

  factory CartItemModel.fromJson(Map<String, dynamic> json) {
    return CartItemModel(
      listingId: (json['listingId'] as num?)?.toInt() ?? 0,
      farmerId: (json['farmerId'] as num?)?.toInt() ?? 0,
      commodity: json['commodity']?.toString() ?? '',
      unit: json['unit']?.toString() ?? 'kg',
      pricePerUnit: (json['pricePerUnit'] as num?)?.toDouble() ?? 0.0,
      quantity: (json['quantity'] as num?)?.toDouble() ?? 1.0,
      maxQuantity: (json['maxQuantity'] as num?)?.toDouble() ?? 1000.0,
      farmerName: json['farmerName']?.toString(),
      farmerPhone: json['farmerPhone']?.toString(),
      farmName: json['farmName']?.toString(),
      imageUrl: json['imageUrl']?.toString(),
      varietyName: json['varietyName']?.toString(),
      qualityGrade: json['qualityGrade']?.toString(),
      isSelected: json['isSelected'] as bool? ?? true,
    );
  }
}
