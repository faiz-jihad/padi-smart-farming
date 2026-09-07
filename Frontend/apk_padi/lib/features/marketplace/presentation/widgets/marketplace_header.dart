import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import 'package:padi/core/localization/app_language.dart';
import 'package:padi/core/providers/app_providers.dart';
import 'package:padi/features/home/presentation/tokens/home_tokens.dart';
import 'package:padi/features/marketplace/data/models/category_model.dart';
import 'package:padi/features/marketplace/data/services/marketplace_api_service.dart';

// ============================================================
// MARKETPLACE CATEGORY PROVIDER
// ============================================================

final marketplaceCategoriesProvider =
    FutureProvider.autoDispose<List<CategoryModel>>(
  (ref) async {
    final service = MarketplaceApiService(
      ref.read(apiClientProvider),
    );

    return service.fetchCategories();
  },
);

// ============================================================
// MARKETPLACE HEADER
// ============================================================

class MarketplaceHeader extends ConsumerWidget {
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

  // ============================================================
  // CATEGORY ICON
  // ============================================================

  IconData _categoryIcon(String? icon) {
    switch (icon?.trim().toLowerCase()) {
      case 'grass':
        return Icons.grass_rounded;

      case 'grain':
        return Icons.grain_rounded;

      case 'rice_bowl':
      case 'rice-bowl':
      case 'ricebowl':
        return Icons.rice_bowl_rounded;

      case 'spa':
        return Icons.spa_rounded;

      case 'agriculture':
      case 'agriculture_rounded':
        return Icons.agriculture_rounded;

      case 'eco':
        return Icons.eco_rounded;

      case 'inventory':
      case 'inventory_2':
        return Icons.inventory_2_rounded;

      case 'category':
        return Icons.category_rounded;

      default:
        return Icons.category_rounded;
    }
  }

  // ============================================================
  // CATEGORY COLOR
  // ============================================================

  Color _categoryColor(
    String slug,
    int index,
  ) {
    switch (slug) {
      case 'gkp-panen':
        return const Color(0xFF075E3B);

      case 'gkg-giling':
        return const Color(0xFF0E7C53);

      case 'beras-premium':
        return const Color(0xFF0284C7);

      case 'benih-bersertifikat':
        return const Color(0xFF059669);

      default:
        const colors = [
          Color(0xFF146B45),
          Color(0xFF075E3B),
          Color(0xFF0E7C53),
          Color(0xFF0284C7),
          Color(0xFF059669),
        ];

        return colors[index % colors.length];
    }
  }

  // ============================================================
  // CATEGORY LABEL
  // ============================================================

  String _categoryLabel(
    CategoryModel category,
    AppStrings s,
  ) {
    final slug = category.slug.trim().toLowerCase();

    // Gunakan localization yang sudah tersedia
    // untuk kategori bawaan aplikasi.
    switch (slug) {
      case 'gkp-panen':
        return s.categoryGkp;

      case 'gkg-giling':
        return s.categoryGkg;

      case 'beras-premium':
        return s.categoryRice;

      case 'benih-bersertifikat':
        return s.categorySeed;

      default:
        // Kategori tambahan dari database
        // menggunakan nama database.
        return category.name;
    }
  }

  // ============================================================
  // BUILD CATEGORY ITEMS
  // ============================================================

  List<Map<String, dynamic>> _buildCategories(
    List<CategoryModel> serverCategories,
    AppStrings s,
  ) {
    final items = <Map<String, dynamic>>[
      {
        'key': 'all',
        'label': s.categoryAll,
        'icon': Icons.apps_rounded,
        'color': const Color(0xFF146B45),
      },
    ];

    for (var index = 0; index < serverCategories.length; index++) {
      final category = serverCategories[index];
      final slug = category.slug.trim().toLowerCase();

      if (slug.isEmpty) {
        continue;
      }

      items.add({
        'key': slug,
        'label': _categoryLabel(category, s),
        'icon': _categoryIcon(category.icon),
        'color': _categoryColor(slug, index),
      });
    }

    return items;
  }

  // ============================================================
  // BUILD
  // ============================================================

  @override
  Widget build(
    BuildContext context,
    WidgetRef ref,
  ) {
    final lang = ref.watch(languageProvider);
    final s = AppStrings(lang);

    final categoriesAsync =
        ref.watch(marketplaceCategoriesProvider);

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // ======================================================
        // 1. GRID KATEGORI
        // ======================================================

        categoriesAsync.when(
          data: (serverCategories) {
            final categories =
                _buildCategories(serverCategories, s);

            return Container(
              color: Colors.white,
              padding: const EdgeInsets.symmetric(
                vertical: 12,
                horizontal: 8,
              ),
              child: SingleChildScrollView(
                scrollDirection: Axis.horizontal,
                physics: const BouncingScrollPhysics(),
                child: Row(
                  children: categories.map((cat) {
                    final key = cat['key'] as String;
                    final label = cat['label'] as String;
                    final icon = cat['icon'] as IconData;
                    final color = cat['color'] as Color;
                    final isSelected =
                        selectedCategory == key;

                    return Padding(
                      padding:
                          const EdgeInsets.symmetric(horizontal: 4),
                      child: InkWell(
                        onTap: () =>
                            onCategorySelected(key),
                        borderRadius:
                            BorderRadius.circular(8),
                        child: Padding(
                          padding:
                              const EdgeInsets.symmetric(
                            horizontal: 4,
                            vertical: 2,
                          ),
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
                                  borderRadius:
                                      BorderRadius.circular(14),
                                  border: Border.all(
                                    color: isSelected
                                        ? color
                                        : const Color(0xFFE5ECE3),
                                    width:
                                        isSelected ? 1.5 : 0.8,
                                  ),
                                ),
                                child: Icon(
                                  icon,
                                  color: isSelected
                                      ? color
                                      : const Color(0xFF555555),
                                  size: 22,
                                ),
                              ),
                              const SizedBox(height: 5),
                              Text(
                                label,
                                maxLines: 1,
                                overflow:
                                    TextOverflow.ellipsis,
                                style: TextStyle(
                                  color: isSelected
                                      ? color
                                      : const Color(0xFF333333),
                                  fontSize: 10.5,
                                  fontWeight: isSelected
                                      ? FontWeight.w800
                                      : FontWeight.w500,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    );
                  }).toList(),
                ),
              ),
            );
          },

          // ====================================================
          // LOADING
          // ====================================================

          loading: () {
            return Container(
              color: Colors.white,
              height: 96,
              padding: const EdgeInsets.symmetric(
                vertical: 12,
                horizontal: 8,
              ),
              child: const Center(
                child: SizedBox(
                  width: 22,
                  height: 22,
                  child: CircularProgressIndicator(
                    strokeWidth: 2.2,
                    color: HomeColors.primaryGreen,
                  ),
                ),
              ),
            );
          },

          // ====================================================
          // ERROR
          // ====================================================

          error: (error, stack) {
            return Container(
              color: Colors.white,
              padding: const EdgeInsets.fromLTRB(
                12,
                12,
                12,
                10,
              ),
              child: Row(
                children: [
                  const Icon(
                    Icons.cloud_off_rounded,
                    size: 20,
                    color: Color(0xFF94A3B8),
                  ),
                  const SizedBox(width: 8),
                  const Expanded(
                    child: Text(
                      'Kategori gagal dimuat.',
                      style: TextStyle(
                        fontSize: 12,
                        color: Color(0xFF68766E),
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
                  TextButton(
                    onPressed: () {
                      ref.invalidate(
                        marketplaceCategoriesProvider,
                      );
                    },
                    child: const Text('Coba Lagi'),
                  ),
                ],
              ),
            );
          },
        ),

        const SizedBox(height: 6),

        // ======================================================
        // 2. TAB SORTING BAR
        // ======================================================

        Container(
          color: Colors.white,
          padding: const EdgeInsets.symmetric(
            horizontal: 4,
          ),
          child: Row(
            children: [
              _buildSortTab(
                label: s.sortRelevance,
                sortKey: 'relevance',
                isSelected:
                    selectedSort == 'newest' ||
                    selectedSort == 'relevance',
              ),
              _buildSortTab(
                label: s.sortNewest,
                sortKey: 'newest',
                isSelected:
                    selectedSort == 'newest',
              ),
              _buildSortTab(
                label: s.sortHighestStock,
                sortKey: 'qty_desc',
                isSelected:
                    selectedSort == 'qty_desc',
              ),
              _buildPriceSortTab(s.sortPrice),
            ],
          ),
        ),
      ],
    );
  }

  // ============================================================
  // SORT TAB
  // ============================================================

  Widget _buildSortTab({
    required String label,
    required String sortKey,
    required bool isSelected,
  }) {
    return Expanded(
      child: InkWell(
        onTap: () => onSortSelected(sortKey),
        child: Container(
          padding:
              const EdgeInsets.symmetric(vertical: 12),
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
                fontWeight: isSelected
                    ? FontWeight.w800
                    : FontWeight.w500,
              ),
            ),
          ),
        ),
      ),
    );
  }

  // ============================================================
  // PRICE SORT TAB
  // ============================================================

  Widget _buildPriceSortTab(String label) {
    final isPriceAsc =
        selectedSort == 'price_asc';
    final isPriceDesc =
        selectedSort == 'price_desc';
    final isPriceActive =
        isPriceAsc || isPriceDesc;

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
          padding:
              const EdgeInsets.symmetric(vertical: 12),
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
            mainAxisAlignment:
                MainAxisAlignment.center,
            children: [
              Text(
                label,
                style: TextStyle(
                  color: isPriceActive
                      ? HomeColors.primaryGreen
                      : const Color(0xFF555555),
                  fontSize: 12,
                  fontWeight: isPriceActive
                      ? FontWeight.w800
                      : FontWeight.w500,
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
