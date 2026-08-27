import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import 'package:padi/features/cart/data/models/cart_item_model.dart';
import 'package:padi/features/cart/presentation/providers/cart_providers.dart';
import 'package:padi/features/home/presentation/tokens/home_tokens.dart';

class CartScreen extends ConsumerWidget {
  const CartScreen({super.key});

  String _formatPrice(double value) {
    final currencyFmt = NumberFormat.currency(
      locale: 'id_ID',
      symbol: 'Rp',
      decimalDigits: 0,
    );
    return currencyFmt.format(value.round());
  }

  String _formatQty(double value, String unit) {
    if (value == value.roundToDouble()) {
      return '${value.toInt()} $unit';
    }
    return '${value.toStringAsFixed(1)} $unit';
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final cartState = ref.watch(cartProvider);
    final cartNotifier = ref.read(cartProvider.notifier);

    return Scaffold(
      backgroundColor: const Color(0xFFF6F8F5),
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        scrolledUnderElevation: 0,
        centerTitle: false,
        leading: context.canPop()
            ? IconButton(
                icon: const Icon(
                  Icons.arrow_back_rounded,
                  color: Color(0xFF17251E),
                ),
                onPressed: () => context.pop(),
              )
            : null,
        title: Row(
          children: [
            const Text(
              'Keranjang Belanja',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.w900,
                color: Color(0xFF17251E),
              ),
            ),
            const SizedBox(width: 8),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
              decoration: BoxDecoration(
                color: HomeColors.lightGreen,
                borderRadius: BorderRadius.circular(12),
              ),
              child: Text(
                '${cartState.totalCount}',
                style: const TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w800,
                  color: HomeColors.primaryGreen,
                ),
              ),
            ),
          ],
        ),
        actions: [
          if (cartState.hasItems)
            IconButton(
              tooltip: 'Kosongkan Keranjang',
              icon: const Icon(
                Icons.delete_sweep_rounded,
                color: Color(0xFFDC2626),
              ),
              onPressed: () async {
                final confirm = await showDialog<bool>(
                  context: context,
                  builder: (ctx) => AlertDialog(
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(16),
                    ),
                    title: const Text(
                      'Kosongkan Keranjang?',
                      style: TextStyle(fontWeight: FontWeight.w800),
                    ),
                    content: const Text(
                      'Semua komoditas hasil panen di keranjang Anda akan dihapus.',
                    ),
                    actions: [
                      TextButton(
                        onPressed: () => Navigator.pop(ctx, false),
                        child: const Text('Batal'),
                      ),
                      FilledButton(
                        style: FilledButton.styleFrom(
                          backgroundColor: const Color(0xFFDC2626),
                        ),
                        onPressed: () => Navigator.pop(ctx, true),
                        child: const Text('Hapus Semua'),
                      ),
                    ],
                  ),
                );
                if (confirm == true) {
                  cartNotifier.clearCart();
                }
              },
            ),
        ],
      ),
      body: cartState.hasItems
          ? _buildCartList(context, ref, cartState, cartNotifier)
          : _buildEmptyState(context),
      bottomNavigationBar: cartState.hasItems
          ? _buildStickyCheckoutBar(context, ref, cartState, cartNotifier)
          : null,
    );
  }

  Widget _buildEmptyState(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              width: 100,
              height: 100,
              decoration: BoxDecoration(
                color: HomeColors.lightGreen.withOpacity(0.6),
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.remove_shopping_cart_rounded,
                size: 48,
                color: HomeColors.primaryGreen,
              ),
            ),
            const SizedBox(height: 20),
            const Text(
              'Keranjang Belanja Masih Kosong',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.w800,
                color: Color(0xFF17251E),
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 8),
            const Text(
              'Jelajahi bursa hasil panen raya P.A.D.I. Temukan Gabah Kering Panen (GKP), Gabah Kering Giling (GKG), beras organik, dan benih langsung dari petani binaan.',
              style: TextStyle(
                fontSize: 13,
                color: Color(0xFF68766E),
                height: 1.4,
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 24),
            FilledButton.icon(
              onPressed: () => context.go('/marketplace'),
              icon: const Icon(Icons.storefront_rounded, size: 18),
              label: const Text('Eksplor Bursa Panen Sekarang'),
              style: FilledButton.styleFrom(
                backgroundColor: HomeColors.primaryGreen,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(
                  horizontal: 24,
                  vertical: 14,
                ),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildCartList(
    BuildContext context,
    WidgetRef ref,
    CartState state,
    CartNotifier notifier,
  ) {
    return ListView(
      padding: const EdgeInsets.fromLTRB(14, 12, 14, 120),
      children: [
        // Jaminan Timbangan & Standar Mutu P.A.D.I.
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
          decoration: BoxDecoration(
            color: const Color(0xFFECFDF5),
            borderRadius: BorderRadius.circular(10),
            border: Border.all(color: const Color(0xFFA7F3D0)),
          ),
          child: const Row(
            children: [
              Icon(
                Icons.verified_rounded,
                color: Color(0xFF059669),
                size: 18,
              ),
              SizedBox(width: 8),
              Expanded(
                child: Text(
                  'Transaksi langsung dengan petani mitra. Bebas tengkulak liar & timbangan terstandarisasi.',
                  style: TextStyle(
                    fontSize: 11.5,
                    color: Color(0xFF065F46),
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),
            ],
          ),
        ),

        const SizedBox(height: 12),

        // List Cart Items
        ...state.items.map((item) => _buildCartItemCard(context, item, notifier)),
      ],
    );
  }

  Widget _buildCartItemCard(
    BuildContext context,
    CartItemModel item,
    CartNotifier notifier,
  ) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFE5ECE3)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.02),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header Toko / Petani
          Padding(
            padding: const EdgeInsets.fromLTRB(12, 12, 12, 8),
            child: Row(
              children: [
                SizedBox(
                  width: 24,
                  height: 24,
                  child: Checkbox(
                    value: item.isSelected,
                    activeColor: HomeColors.primaryGreen,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(4),
                    ),
                    onChanged: (_) => notifier.toggleItemSelection(item.listingId),
                  ),
                ),
                const SizedBox(width: 8),
                const Icon(
                  Icons.storefront_rounded,
                  size: 16,
                  color: HomeColors.primaryGreen,
                ),
                const SizedBox(width: 6),
                Expanded(
                  child: Text(
                    item.farmerName ?? 'Petani Mitra P.A.D.I.',
                    style: const TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w700,
                      color: Color(0xFF17251E),
                    ),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
                if (item.farmName != null && item.farmName!.isNotEmpty)
                  Container(
                    padding:
                        const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF1F5F9),
                      borderRadius: BorderRadius.circular(4),
                    ),
                    child: Text(
                      item.farmName!,
                      style: const TextStyle(
                        fontSize: 10,
                        color: Color(0xFF64748B),
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
              ],
            ),
          ),

          const Divider(color: Color(0xFFF1F5F0), height: 1),

          // Konten Barang
          Padding(
            padding: const EdgeInsets.all(12),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Gambar Komoditas
                ClipRRect(
                  borderRadius: BorderRadius.circular(10),
                  child: SizedBox(
                    width: 76,
                    height: 76,
                    child: _buildItemImage(item.imageUrl),
                  ),
                ),

                const SizedBox(width: 12),

                // Detail Komoditas
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        item.commodity,
                        style: const TextStyle(
                          fontSize: 13.5,
                          fontWeight: FontWeight.w800,
                          color: Color(0xFF17251E),
                          height: 1.25,
                        ),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                      const SizedBox(height: 4),
                      Row(
                        children: [
                          if (item.qualityGrade != null)
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 6,
                                vertical: 2,
                              ),
                              margin: const EdgeInsets.only(right: 6),
                              decoration: BoxDecoration(
                                color: HomeColors.lightGreen,
                                borderRadius: BorderRadius.circular(4),
                              ),
                              child: Text(
                                item.qualityGrade!,
                                style: const TextStyle(
                                  fontSize: 10,
                                  fontWeight: FontWeight.w700,
                                  color: HomeColors.primaryGreen,
                                ),
                              ),
                            ),
                          Text(
                            '${_formatPrice(item.pricePerUnit)} / ${item.unit}',
                            style: const TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w600,
                              color: Color(0xFF68766E),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),

                      // Stepper & Tombol Hapus
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          // Stepper Kuantitas
                          Container(
                            decoration: BoxDecoration(
                              border:
                                  Border.all(color: const Color(0xFFE2E8F0)),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                InkWell(
                                  onTap: () {
                                    final step = item.quantity >= 500 ? 100.0 : (item.quantity >= 50 ? 10.0 : 1.0);
                                    notifier.updateQuantity(
                                      item.listingId,
                                      item.quantity - step,
                                    );
                                  },
                                  borderRadius: const BorderRadius.horizontal(
                                    left: Radius.circular(7),
                                  ),
                                  child: Container(
                                    padding: const EdgeInsets.all(6),
                                    child: const Icon(
                                      Icons.remove_rounded,
                                      size: 16,
                                      color: Color(0xFF475569),
                                    ),
                                  ),
                                ),
                                Container(
                                  padding: const EdgeInsets.symmetric(
                                    horizontal: 10,
                                  ),
                                  child: Text(
                                    _formatQty(item.quantity, item.unit),
                                    style: const TextStyle(
                                      fontSize: 12,
                                      fontWeight: FontWeight.w800,
                                      color: Color(0xFF1E293B),
                                    ),
                                  ),
                                ),
                                InkWell(
                                  onTap: () {
                                    final step = item.quantity >= 500 ? 100.0 : (item.quantity >= 50 ? 10.0 : 1.0);
                                    notifier.updateQuantity(
                                      item.listingId,
                                      item.quantity + step,
                                    );
                                  },
                                  borderRadius: const BorderRadius.horizontal(
                                    right: Radius.circular(7),
                                  ),
                                  child: Container(
                                    padding: const EdgeInsets.all(6),
                                    child: const Icon(
                                      Icons.add_rounded,
                                      size: 16,
                                      color: Color(0xFF475569),
                                    ),
                                  ),
                                ),
                              ],
                            ),
                          ),

                          // Tombol Hapus Satuan
                          IconButton(
                            icon: const Icon(
                              Icons.delete_outline_rounded,
                              size: 20,
                              color: Color(0xFF94A3B8),
                            ),
                            onPressed: () =>
                                notifier.removeItem(item.listingId),
                            tooltip: 'Hapus barang',
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),

          // Subtotal Bar
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
            decoration: const BoxDecoration(
              color: Color(0xFFFAFCFA),
              borderRadius: BorderRadius.vertical(bottom: Radius.circular(13)),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text(
                  'Subtotal Komoditas:',
                  style: TextStyle(
                    fontSize: 11.5,
                    color: Color(0xFF68766E),
                    fontWeight: FontWeight.w500,
                  ),
                ),
                Text(
                  _formatPrice(item.subtotal),
                  style: const TextStyle(
                    fontSize: 13.5,
                    fontWeight: FontWeight.w900,
                    color: HomeColors.primaryGreen,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStickyCheckoutBar(
    BuildContext context,
    WidgetRef ref,
    CartState state,
    CartNotifier notifier,
  ) {
    final hasSelection = state.selectedCount > 0;

    return Container(
      padding: const EdgeInsets.fromLTRB(16, 10, 16, 16),
      decoration: BoxDecoration(
        color: Colors.white,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.08),
            blurRadius: 14,
            offset: const Offset(0, -3),
          ),
        ],
      ),
      child: SafeArea(
        top: false,
        child: Row(
          children: [
            // Checkbox Select All
            InkWell(
              onTap: () => notifier.toggleSelectAll(!state.isAllSelected),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  SizedBox(
                    width: 24,
                    height: 24,
                    child: Checkbox(
                      value: state.isAllSelected,
                      activeColor: HomeColors.primaryGreen,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(4),
                      ),
                      onChanged: (val) => notifier.toggleSelectAll(val ?? false),
                    ),
                  ),
                  const SizedBox(width: 4),
                  const Text(
                    'Semua',
                    style: TextStyle(
                      fontSize: 12.5,
                      fontWeight: FontWeight.w600,
                      color: Color(0xFF475569),
                    ),
                  ),
                ],
              ),
            ),

            const SizedBox(width: 12),

            // Ringkasan Total
            Expanded(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  const Text(
                    'Total Tagihan:',
                    style: TextStyle(
                      fontSize: 11,
                      color: Color(0xFF64748B),
                    ),
                  ),
                  Text(
                    _formatPrice(state.totalSelectedPrice),
                    style: const TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.w900,
                      color: HomeColors.primaryGreen,
                      letterSpacing: -0.3,
                    ),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                ],
              ),
            ),

            const SizedBox(width: 12),

            // Tombol Checkout
            FilledButton.icon(
              onPressed: hasSelection
                  ? () => context.push('/checkout')
                  : null,
              icon: const Icon(Icons.shopping_bag_rounded, size: 18),
              label: Text(
                'Checkout (${state.selectedCount})',
                style: const TextStyle(
                  fontSize: 13,
                  fontWeight: FontWeight.w800,
                ),
              ),
              style: FilledButton.styleFrom(
                backgroundColor: HomeColors.primaryGreen,
                disabledBackgroundColor: const Color(0xFFCBD5E1),
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(
                  horizontal: 16,
                  vertical: 14,
                ),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(10),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildItemImage(String? imageUrl) {
    final cleanUrl = imageUrl?.trim() ?? '';
    final isValidHttp =
        cleanUrl.startsWith('http://') || cleanUrl.startsWith('https://');

    if (!isValidHttp) {
      return Image.asset(
        'assets/images/onboarding_3.jpeg',
        fit: BoxFit.cover,
      );
    }

    return Image.network(
      cleanUrl,
      fit: BoxFit.cover,
      errorBuilder: (context, error, stackTrace) => Image.asset(
        'assets/images/onboarding_3.jpeg',
        fit: BoxFit.cover,
      ),
    );
  }
}
