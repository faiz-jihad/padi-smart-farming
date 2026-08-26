import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:padi/core/localization/app_language.dart';
import 'package:padi/features/home/presentation/tokens/home_tokens.dart';

class HomeBottomNavigation extends ConsumerWidget {
  const HomeBottomNavigation({
    super.key,
    required this.currentIndex,
    required this.onTap,
  });

  final int currentIndex;
  final ValueChanged<int> onTap;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final lang = ref.watch(languageProvider);
    final s = AppStrings(lang);

    final items = [
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
                Positioned(
                  left: 0,
                  right: 0,
                  bottom: 0,
                  child: Container(
                    height: 64,
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(24),
                      boxShadow: [
                        BoxShadow(
                          color: const Color(0xFF0B1F17).withOpacity(0.12),
                          blurRadius: 28,
                          offset: const Offset(0, 10),
                        ),
                      ],
                    ),
                  ),
                ),
                Positioned.fill(
                  top: 18,
                  child: Row(
                    children: [
                      Expanded(child: _buildIconItem(items[1])),
                      Expanded(child: _buildIconItem(items[2])),
                      const Spacer(),
                      Expanded(child: _buildIconItem(items[3])),
                      Expanded(child: _buildIconItem(items[4])),
                    ],
                  ),
                ),
                Positioned(
                  top: 0,
                  child: _buildPrimaryItem(items[0]),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildIconItem(_NavItem item) {
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
                color: isSelected ? HomeColors.lightGreen : Colors.transparent,
                shape: BoxShape.circle,
              ),
              child: Icon(
                item.icon,
                color: isSelected
                    ? HomeColors.primaryGreen
                    : HomeColors.textSecondary,
                size: 24,
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildPrimaryItem(_NavItem item) {
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
            gradient: LinearGradient(
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
              colors: isSelected
                  ? const [
                      Color(0xFF13B66B),
                      Color(0xFF0E7C53),
                      Color(0xFF042F1E),
                    ]
                  : const [
                      Color(0xFF25B77A),
                      Color(0xFF0E7C53),
                    ],
            ),
            boxShadow: [
              BoxShadow(
                color: const Color(0xFF0E7C53).withOpacity(0.28),
                blurRadius: 18,
                offset: const Offset(0, 8),
              ),
              BoxShadow(
                color: const Color(0xFFF59E0B).withOpacity(0.22),
                blurRadius: 22,
                offset: const Offset(-10, 10),
              ),
            ],
          ),
          child: Icon(
            item.icon,
            color: Colors.white,
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
  });

  final int index;
  final String label;
  final IconData icon;
}
