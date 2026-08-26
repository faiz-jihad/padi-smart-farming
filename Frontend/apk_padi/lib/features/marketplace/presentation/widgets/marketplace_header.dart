import 'package:flutter/material.dart';
import 'package:padi/features/home/presentation/tokens/home_tokens.dart';

class MarketplaceHeader extends StatelessWidget {
  const MarketplaceHeader({
    super.key,
    required this.searchController,
    required this.selectedCategory,
    required this.onCategorySelected,
    required this.selectedSort,
    required this.onSortSelected,
    required this.totalListings,
    required this.filteredListings,
    required this.onSearchChanged,
  });

  final TextEditingController searchController;
  final String selectedCategory;
  final ValueChanged<String> onCategorySelected;
  final String selectedSort;
  final ValueChanged<String> onSortSelected;
  final int totalListings;
  final int filteredListings;
  final VoidCallback onSearchChanged;

  static const List<Map<String, dynamic>> categories = [
    {
      'key': 'all',
      'label': 'Semua',
      'icon': Icons.apps_rounded,
      'color': Color(0xFF146B45),
    },
    {
      'key': 'gkp',
      'label': 'GKP Panen',
      'icon': Icons.grass_rounded,
      'color': Color(0xFF075E3B),
    },
    {
      'key': 'gkg',
      'label': 'GKG Giling',
      'icon': Icons.grain_rounded,
      'color': Color(0xFF0E7C53),
    },
    {
      'key': 'beras',
      'label': 'Beras Premium',
      'icon': Icons.rice_bowl_rounded,
      'color': Color(0xFF0284C7),
    },
    {
      'key': 'benih',
      'label': 'Benih Bersertifikat',
      'icon': Icons.spa_rounded,
      'color': Color(0xFF059669),
    },
  ];

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // 1. Grid Kategori Ikon P.A.D.I.
        Container(
          color: Colors.white,
          padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 8),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceAround,
            children: categories.map((cat) {
              final key = cat['key'] as String;
              final label = cat['label'] as String;
              final icon = cat['icon'] as IconData;
              final color = cat['color'] as Color;
              final isSelected = selectedCategory == key;

              return InkWell(
                onTap: () => onCategorySelected(key),
                borderRadius: BorderRadius.circular(8),
                child: Padding(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 4, vertical: 2),
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Container(
                        width: 44,
                        height: 44,
                        decoration: BoxDecoration(
                          color: isSelected
                              ? color.withOpacity(0.15)
                              : const Color(0xFFF6F8F5),
                          borderRadius: BorderRadius.circular(14),
                          border: Border.all(
                            color: isSelected
                                ? color
                                : const Color(0xFFE5ECE3),
                            width: isSelected ? 1.5 : 0.8,
                          ),
                        ),
                        child: Icon(
                          icon,
                          color: isSelected ? color : const Color(0xFF555555),
                          size: 22,
                        ),
                      ),
                      const SizedBox(height: 5),
                      Text(
                        label,
                        style: TextStyle(
                          color: isSelected ? color : const Color(0xFF333333),
                          fontSize: 10.5,
                          fontWeight:
                              isSelected ? FontWeight.w800 : FontWeight.w500,
                        ),
                      ),
                    ],
                  ),
                ),
              );
            }).toList(),
          ),
        ),

        const SizedBox(height: 6),

        // 2. Tab Sorting Bar (Hijau Zamrud Aktif)
        Container(
          color: Colors.white,
          padding: const EdgeInsets.symmetric(horizontal: 4),
          child: Row(
            children: [
              _buildSortTab(
                label: 'Terkait',
                sortKey: 'relevance',
                isSelected:
                    selectedSort == 'newest' || selectedSort == 'relevance',
              ),
              _buildSortTab(
                label: 'Terbaru',
                sortKey: 'newest',
                isSelected: selectedSort == 'newest',
              ),
              _buildSortTab(
                label: 'Stok Terbanyak',
                sortKey: 'qty_desc',
                isSelected: selectedSort == 'qty_desc',
              ),
              _buildPriceSortTab(),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildSortTab({
    required String label,
    required String sortKey,
    required bool isSelected,
  }) {
    return Expanded(
      child: InkWell(
        onTap: () => onSortSelected(sortKey),
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 12),
          decoration: BoxDecoration(
            border: Border(
              bottom: BorderSide(
                color: isSelected
                    ? HomeColors.primaryGreen
                    : Colors.transparent,
                width: 2.2,
              ),
            ),
          ),
          child: Center(
            child: Text(
              label,
              style: TextStyle(
                color: isSelected
                    ? HomeColors.primaryGreen
                    : const Color(0xFF555555),
                fontSize: 12,
                fontWeight: isSelected ? FontWeight.w800 : FontWeight.w500,
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildPriceSortTab() {
    final isPriceAsc = selectedSort == 'price_asc';
    final isPriceDesc = selectedSort == 'price_desc';
    final isPriceActive = isPriceAsc || isPriceDesc;

    return Expanded(
      child: InkWell(
        onTap: () {
          if (selectedSort == 'price_asc') {
            onSortSelected('price_desc');
          } else {
            onSortSelected('price_asc');
          }
        },
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 12),
          decoration: BoxDecoration(
            border: Border(
              bottom: BorderSide(
                color: isPriceActive
                    ? HomeColors.primaryGreen
                    : Colors.transparent,
                width: 2.2,
              ),
            ),
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Text(
                'Harga',
                style: TextStyle(
                  color: isPriceActive
                      ? HomeColors.primaryGreen
                      : const Color(0xFF555555),
                  fontSize: 12,
                  fontWeight: isPriceActive ? FontWeight.w800 : FontWeight.w500,
                ),
              ),
              const SizedBox(width: 2),
              Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(
                    Icons.arrow_drop_up_rounded,
                    size: 13,
                    color: isPriceAsc
                        ? HomeColors.primaryGreen
                        : const Color(0xFF888888),
                  ),
                  Icon(
                    Icons.arrow_drop_down_rounded,
                    size: 13,
                    color: isPriceDesc
                        ? HomeColors.primaryGreen
                        : const Color(0xFF888888),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}
