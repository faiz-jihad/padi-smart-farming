import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:padi/core/localization/app_language.dart';
import 'package:padi/core/providers/app_providers.dart';
import 'package:padi/features/cart/presentation/providers/cart_providers.dart';
import 'package:padi/features/home/presentation/tokens/home_tokens.dart';
import 'package:padi/features/marketplace/presentation/screens/buyer_orders_screen.dart';

class HomeBottomNavigation extends ConsumerWidget {
  const HomeBottomNavigation({
    super.key,
    required this.currentIndex,
    required this.onTap,
  });

  final int currentIndex;
  final ValueChanged<int> onTap;

  // ───────────────────── Color Palette ─────────────────────
  static const _white = Colors.white;
  static const _green = Color(0xFF059669);       // Emerald-600
  static const _lightGreen = Color(0xFFECFDF5);  // Emerald-50
  static const _border = Color(0xFFA7F3D0);      // Emerald-200
  static const _muted = Color(0xFF94A3B8);        // Slate-400

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final lang = ref.watch(languageProvider);
    final s = AppStrings(lang);
    final isBuyer = ref.watch(isBuyerRoleProvider);
    final cartState = ref.watch(cartProvider);

    final activeContractsCount = ref.watch(buyerContractsProvider).when(
          data: (contracts) =>
              contracts.where((c) => c.status.toLowerCase() == 'active').length,
          loading: () => 0,
          error: (_, __) => 0,
        );

    if (isBuyer) {
      return _buildBuyerNavigation(context, s, cartState.totalCount, activeContractsCount);
    }

    return _buildFarmerNavigation(context, s);
  }

  // ══════════════════════════════════════════════════
  // 1. BUYER BOTTOM NAVIGATION — Hijau & Putih
  // ══════════════════════════════════════════════════
  Widget _buildBuyerNavigation(BuildContext context, AppStrings s, int cartCount, int activeContractsCount) {
    final buyerItems = [
      _NavItem(index: 0, label: s.navHome, icon: Icons.storefront_rounded),
      _NavItem(index: 1, label: s.navMarket, icon: Icons.grid_view_rounded),
      _NavItem(index: 2, label: s.navCart, icon: Icons.shopping_cart_rounded, badgeCount: cartCount),
      _NavItem(index: 3, label: s.navOrders, icon: Icons.receipt_long_rounded, badgeCount: activeContractsCount),
      _NavItem(index: 4, label: s.navProfile, icon: Icons.person_rounded),
    ];

    return SafeArea(
      top: false,
      minimum: const EdgeInsets.fromLTRB(16, 0, 16, 12),
      child: Center(
        heightFactor: 1,
        child: ConstrainedBox(
          constraints: const BoxConstraints(maxWidth: 540),
          child: SizedBox(
            height: 88,
            child: Stack(
              alignment: Alignment.bottomCenter,
              clipBehavior: Clip.none,
              children: [
                // ── Bar background: white with green border ──
                Positioned(
                  left: 0,
                  right: 0,
                  bottom: 0,
                  child: Container(
                    height: 68,
                    decoration: BoxDecoration(
                      color: _white,
                      borderRadius: BorderRadius.circular(26),
                      border: Border.all(color: _border, width: 1.5),
                      boxShadow: [
                        BoxShadow(
                          color: _green.withOpacity(0.10),
                          blurRadius: 22,
                          offset: const Offset(0, 6),
                        ),
                      ],
                    ),
                  ),
                ),

                // ── Nav Items ──
                Positioned.fill(
                  top: 20,
                  child: Row(
                    children: [
                      Expanded(child: _buildBuyerNavItem(buyerItems[0])),
                      Expanded(child: _buildBuyerNavItem(buyerItems[1])),
                      const SizedBox(width: 72), // space for floating button
                      Expanded(child: _buildBuyerNavItem(buyerItems[3])),
                      Expanded(child: _buildBuyerNavItem(buyerItems[4])),
                    ],
                  ),
                ),

                // ── Floating Center Cart Button ──
                Positioned(
                  top: 0,
                  child: _buildBuyerCenterCartButton(buyerItems[2]),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildBuyerNavItem(_NavItem item) {
    final isSelected = currentIndex == item.index;

    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: () => onTap(item.index),
        borderRadius: BorderRadius.circular(16),
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 4),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Stack(
                clipBehavior: Clip.none,
                children: [
                  AnimatedContainer(
                    duration: const Duration(milliseconds: 180),
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 4),
                    decoration: BoxDecoration(
                      color: isSelected ? _lightGreen : Colors.transparent,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Icon(
                      item.icon,
                      color: isSelected ? _green : _muted,
                      size: 22,
                    ),
                  ),
                  if (item.badgeCount > 0)
                    Positioned(
                      top: -3,
                      right: 2,
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 1),
                        decoration: const BoxDecoration(
                          color: Color(0xFFDC2626),
                          shape: BoxShape.circle,
                        ),
                        constraints: const BoxConstraints(minWidth: 16, minHeight: 16),
                        child: Text(
                          '${item.badgeCount}',
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 9,
                            fontWeight: FontWeight.w900,
                          ),
                          textAlign: TextAlign.center,
                        ),
                      ),
                    ),
                ],
              ),
              const SizedBox(height: 3),
              Text(
                item.label,
                style: TextStyle(
                  fontSize: 10,
                  fontWeight: isSelected ? FontWeight.w800 : FontWeight.w500,
                  color: isSelected ? _green : _muted,
                  letterSpacing: -0.2,
                ),
                maxLines: 1,
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildBuyerCenterCartButton(_NavItem item) {
    final isSelected = currentIndex == item.index;

    return Tooltip(
      message: 'Keranjang Pengadaan Panen',
      child: GestureDetector(
        onTap: () => onTap(item.index),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Stack(
              clipBehavior: Clip.none,
              children: [
                AnimatedContainer(
                  duration: const Duration(milliseconds: 200),
                  width: 60,
                  height: 60,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    color: isSelected ? const Color(0xFF047857) : _green,
                    border: Border.all(
                      color: isSelected ? const Color(0xFF6EE7B7) : _border,
                      width: 2.5,
                    ),
                    boxShadow: [
                      BoxShadow(
                        color: _green.withOpacity(0.35),
                        blurRadius: 18,
                        offset: const Offset(0, 7),
                      ),
                    ],
                  ),
                  child: const Icon(
                    Icons.shopping_cart_rounded,
                    color: Colors.white,
                    size: 26,
                  ),
                ),
                if (item.badgeCount > 0)
                  Positioned(
                    top: -2,
                    right: -2,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1),
                      decoration: BoxDecoration(
                        color: const Color(0xFFDC2626),
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(color: Colors.white, width: 1.5),
                      ),
                      constraints: const BoxConstraints(minWidth: 19, minHeight: 19),
                      child: Text(
                        '${item.badgeCount}',
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 9.5,
                          fontWeight: FontWeight.w900,
                        ),
                        textAlign: TextAlign.center,
                      ),
                    ),
                  ),
              ],
            ),
            const SizedBox(height: 3),
            Text(
              item.label,
              style: TextStyle(
                fontSize: 10,
                fontWeight: isSelected ? FontWeight.w900 : FontWeight.w700,
                color: isSelected ? _green : _muted,
              ),
            ),
          ],
        ),
      ),
    );
  }

  // ══════════════════════════════════════════════════
  // 2. FARMER BOTTOM NAVIGATION — Hijau & Putih
  // ══════════════════════════════════════════════════
  Widget _buildFarmerNavigation(BuildContext context, AppStrings s) {
    final farmerItems = [
      _NavItem(index: 0, label: s.navHome, icon: Icons.home_rounded),
      _NavItem(index: 1, label: s.navFarms, icon: Icons.grass_rounded),
      _NavItem(index: 2, label: s.navScan, icon: Icons.camera_alt_rounded),
      _NavItem(index: 3, label: s.navMarket, icon: Icons.storefront_rounded),
      _NavItem(index: 4, label: s.navProfile, icon: Icons.person_rounded),
    ];

    return SafeArea(
      top: false,
      minimum: const EdgeInsets.fromLTRB(18, 0, 18, 12),
      child: Center(
        heightFactor: 1,
        child: ConstrainedBox(
          constraints: const BoxConstraints(maxWidth: 560),
          child: SizedBox(
            height: 82,
            child: Stack(
              alignment: Alignment.bottomCenter,
              clipBehavior: Clip.none,
              children: [
                // ── Bar background: white with green border ──
                Positioned(
                  left: 0,
                  right: 0,
                  bottom: 0,
                  child: Container(
                    height: 64,
                    decoration: BoxDecoration(
                      color: _white,
                      borderRadius: BorderRadius.circular(24),
                      border: Border.all(color: _border, width: 1.5),
                      boxShadow: [
                        BoxShadow(
                          color: _green.withOpacity(0.10),
                          blurRadius: 22,
                          offset: const Offset(0, 6),
                        ),
                      ],
                    ),
                  ),
                ),

                // ── Nav Items (excluding center home button) ──
                Positioned.fill(
                  top: 18,
                  child: Row(
                    children: [
                      Expanded(child: _buildFarmerIconItem(farmerItems[1])),
                      Expanded(child: _buildFarmerIconItem(farmerItems[2])),
                      const Spacer(),
                      Expanded(child: _buildFarmerIconItem(farmerItems[3])),
                      Expanded(child: _buildFarmerIconItem(farmerItems[4])),
                    ],
                  ),
                ),

                // ── Floating Center Home Button ──
                Positioned(
                  top: 0,
                  child: _buildFarmerPrimaryItem(farmerItems[0]),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildFarmerIconItem(_NavItem item) {
    final isSelected = currentIndex == item.index;

    return Tooltip(
      message: item.label,
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: () => onTap(item.index),
          customBorder: const CircleBorder(),
          child: Center(
            child: AnimatedContainer(
              duration: const Duration(milliseconds: 180),
              width: 42,
              height: 42,
              decoration: BoxDecoration(
                color: isSelected ? _lightGreen : Colors.transparent,
                shape: BoxShape.circle,
              ),
              child: Icon(
                item.icon,
                color: isSelected ? _green : _muted,
                size: 24,
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildFarmerPrimaryItem(_NavItem item) {
    final isSelected = currentIndex == item.index;

    return Tooltip(
      message: item.label,
      child: GestureDetector(
        onTap: () => onTap(item.index),
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 180),
          width: 64,
          height: 64,
          decoration: BoxDecoration(
            shape: BoxShape.circle,
            color: isSelected ? const Color(0xFF047857) : _green,
            border: Border.all(
              color: isSelected ? const Color(0xFF6EE7B7) : _border,
              width: 2.5,
            ),
            boxShadow: [
              BoxShadow(
                color: _green.withOpacity(0.30),
                blurRadius: 18,
                offset: const Offset(0, 7),
              ),
            ],
          ),
          child: Icon(
            item.icon,
            color: _white,
            size: 28,
          ),
        ),
      ),
    );
  }
}

class _NavItem {
  const _NavItem({
    required this.index,
    required this.label,
    required this.icon,
    this.badgeCount = 0,
  });

  final int index;
  final String label;
  final IconData icon;
  final int badgeCount;
}
