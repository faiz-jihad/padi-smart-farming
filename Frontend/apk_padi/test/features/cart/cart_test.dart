import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:padi/features/cart/data/models/cart_item_model.dart';
import 'package:padi/features/cart/presentation/providers/cart_providers.dart';
import 'package:padi/features/marketplace/data/models/market_listing_model.dart';

void main() {
  group('CartItemModel Tests', () {
    test('subtotal calculates correctly', () {
      const item = CartItemModel(
        listingId: 1,
        farmerId: 10,
        commodity: 'Gabah Kering Panen (GKP)',
        unit: 'kg',
        pricePerUnit: 6850,
        quantity: 500,
        maxQuantity: 2000,
        farmerName: 'Pak Subandi',
        farmerPhone: '+6281234567890',
        farmName: 'Sawah Subang Blok A',
      );

      expect(item.subtotal, 3425000.0);
    });

    test('toJson and fromJson work symmetrically', () {
      const item = CartItemModel(
        listingId: 2,
        farmerId: 11,
        commodity: 'Beras Pandan Wangi',
        unit: 'kg',
        pricePerUnit: 15000,
        quantity: 100,
        maxQuantity: 1000,
        farmerName: 'Bu Siti',
        farmerPhone: '081298765432',
        farmName: 'Lahan Cianjur',
        isSelected: false,
      );

      final json = item.toJson();
      final reconstructed = CartItemModel.fromJson(json);

      expect(reconstructed.listingId, 2);
      expect(reconstructed.farmerId, 11);
      expect(reconstructed.commodity, 'Beras Pandan Wangi');
      expect(reconstructed.pricePerUnit, 15000);
      expect(reconstructed.quantity, 100);
      expect(reconstructed.isSelected, false);
    });
  });

  group('CartNotifier & CartState Tests', () {
    late ProviderContainer container;

    setUp(() {
      container = ProviderContainer();
    });

    tearDown(() {
      container.dispose();
    });

    final testListing1 = MarketListingModel(
      id: 101,
      farmerId: 20,
      farmId: 5,
      cropSeasonId: 1,
      harvestId: 1,
      commodity: 'Gabah Kering Panen',
      varietyName: 'Ciherang',
      quantity: 1000,
      unit: 'kg',
      pricePerUnit: 7000,
      qualityGrade: 'A',
      status: 'active',
      farmerName: 'Pak Joko',
      farmerPhone: '081300001111',
      farmName: 'Sawah Indramayu',
    );

    final testListing2 = MarketListingModel(
      id: 102,
      farmerId: 20, // Same farmer
      farmId: 5,
      cropSeasonId: 1,
      harvestId: 2,
      commodity: 'Gabah Kering Giling',
      varietyName: 'Inpari 32',
      quantity: 800,
      unit: 'kg',
      pricePerUnit: 8000,
      qualityGrade: 'Premium',
      status: 'active',
      farmerName: 'Pak Joko',
      farmerPhone: '081300001111',
      farmName: 'Sawah Indramayu',
    );

    test('Initial cart is empty', () {
      final cart = container.read(cartProvider);
      expect(cart.hasItems, false);
      expect(cart.totalCount, 0);
      expect(cart.totalSelectedPrice, 0.0);
    });

    test('Adding item updates cart state', () {
      container.read(cartProvider.notifier).addItem(testListing1, quantity: 200);

      final cart = container.read(cartProvider);
      expect(cart.hasItems, true);
      expect(cart.totalCount, 1);
      expect(cart.items.first.listingId, 101);
      expect(cart.items.first.quantity, 200);
      expect(cart.totalSelectedPrice, 1400000.0);
    });

    test('Adding same item increments quantity rather than adding duplicate', () {
      container.read(cartProvider.notifier).addItem(testListing1, quantity: 200);
      container.read(cartProvider.notifier).addItem(testListing1, quantity: 150);

      final cart = container.read(cartProvider);
      expect(cart.totalCount, 1);
      expect(cart.items.first.quantity, 350);
      expect(cart.totalSelectedPrice, 350 * 7000.0);
    });

    test('updateQuantity modifies item and clamps to max', () {
      container.read(cartProvider.notifier).addItem(testListing1, quantity: 200);
      container.read(cartProvider.notifier).updateQuantity(101, 500);

      expect(container.read(cartProvider).items.first.quantity, 500);

      // Clamping to maxQuantity (1000)
      container.read(cartProvider.notifier).updateQuantity(101, 5000);
      expect(container.read(cartProvider).items.first.quantity, 1000);
    });

    test('Toggle selection and groupedByFarmer grouping', () {
      container.read(cartProvider.notifier).addItem(testListing1, quantity: 100);
      container.read(cartProvider.notifier).addItem(testListing2, quantity: 100);

      var cart = container.read(cartProvider);
      expect(cart.totalCount, 2);
      expect(cart.groupedByFarmer.containsKey(20), true);
      expect(cart.groupedByFarmer[20]!.length, 2);
      expect(cart.totalSelectedPrice, (100 * 7000) + (100 * 8000));

      // Unselect item 1
      container.read(cartProvider.notifier).toggleItemSelection(101);
      cart = container.read(cartProvider);
      expect(cart.selectedCount, 1);
      expect(cart.totalSelectedPrice, 100 * 8000.0);
    });

    test('removeItem and clearCart work properly', () {
      container.read(cartProvider.notifier).addItem(testListing1, quantity: 100);
      container.read(cartProvider.notifier).addItem(testListing2, quantity: 100);

      container.read(cartProvider.notifier).removeItem(101);
      expect(container.read(cartProvider).totalCount, 1);
      expect(container.read(cartProvider).items.first.listingId, 102);

      container.read(cartProvider.notifier).clearCart();
      expect(container.read(cartProvider).hasItems, false);
      expect(container.read(cartProvider).totalCount, 0);
    });
  });
}
