import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:padi/features/cart/data/models/cart_item_model.dart';
import 'package:padi/features/marketplace/data/models/market_listing_model.dart';

class CartState {
  const CartState({this.items = const []});

  final List<CartItemModel> items;

  int get totalCount => items.length;

  int get selectedCount => items.where((item) => item.isSelected).length;

  bool get hasItems => items.isNotEmpty;

  bool get isAllSelected =>
      items.isNotEmpty && items.every((item) => item.isSelected);

  List<CartItemModel> get selectedItems =>
      items.where((item) => item.isSelected).toList();

  double get totalSelectedPrice => selectedItems.fold<double>(
        0,
        (sum, item) => sum + item.subtotal,
      );

  double get totalSelectedQuantity => selectedItems.fold<double>(
        0,
        (sum, item) => sum + item.quantity,
      );

  Map<int, List<CartItemModel>> get groupedByFarmer {
    final map = <int, List<CartItemModel>>{};
    for (final item in items) {
      map.putIfAbsent(item.farmerId, () => []).add(item);
    }
    return map;
  }

  CartState copyWith({List<CartItemModel>? items}) {
    return CartState(items: items ?? this.items);
  }
}

class CartNotifier extends Notifier<CartState> {
  @override
  CartState build() {
    return const CartState();
  }

  void addItem(MarketListingModel listing, {double quantity = 100}) {
    final existingIndex =
        state.items.indexWhere((item) => item.listingId == listing.id);

    if (existingIndex >= 0) {
      final existing = state.items[existingIndex];
      final newQty = (existing.quantity + quantity).clamp(
        1.0,
        listing.quantity > 0 ? listing.quantity : 999999.0,
      );
      final updatedList = List<CartItemModel>.from(state.items);
      updatedList[existingIndex] = existing.copyWith(quantity: newQty);
      state = state.copyWith(items: updatedList);
    } else {
      final newItem = CartItemModel(
        listingId: listing.id,
        farmerId: listing.farmerId,
        commodity: listing.commodity,
        unit: listing.unit,
        pricePerUnit: listing.pricePerUnit,
        quantity: quantity.clamp(
          1.0,
          listing.quantity > 0 ? listing.quantity : 999999.0,
        ),
        maxQuantity: listing.quantity > 0 ? listing.quantity : 999999.0,
        farmerName: listing.farmerName ?? 'Petani Mitra P.A.D.I.',
        farmerPhone: listing.farmerPhone ?? '+6281234567890',
        farmName: listing.farmName ?? 'Lahan Mitra',
        imageUrl: listing.imageUrl,
        varietyName: listing.varietyName,
        qualityGrade: listing.qualityGrade,
        isSelected: true,
      );
      state = state.copyWith(items: [...state.items, newItem]);
    }
  }

  void updateQuantity(int listingId, double newQty) {
    if (newQty <= 0) {
      removeItem(listingId);
      return;
    }
    state = state.copyWith(
      items: state.items.map((item) {
        if (item.listingId == listingId) {
          final clamped = newQty.clamp(1.0, item.maxQuantity);
          return item.copyWith(quantity: clamped);
        }
        return item;
      }).toList(),
    );
  }

  void removeItem(int listingId) {
    state = state.copyWith(
      items: state.items.where((item) => item.listingId != listingId).toList(),
    );
  }

  void toggleItemSelection(int listingId) {
    state = state.copyWith(
      items: state.items.map((item) {
        if (item.listingId == listingId) {
          return item.copyWith(isSelected: !item.isSelected);
        }
        return item;
      }).toList(),
    );
  }

  void toggleSelectAll(bool select) {
    state = state.copyWith(
      items: state.items.map((item) => item.copyWith(isSelected: select)).toList(),
    );
  }

  void clearPurchased(List<int> purchasedListingIds) {
    state = state.copyWith(
      items: state.items
          .where((item) => !purchasedListingIds.contains(item.listingId))
          .toList(),
    );
  }

  void clearCart() {
    state = const CartState(items: []);
  }
}

final cartProvider = NotifierProvider<CartNotifier, CartState>(CartNotifier.new);
