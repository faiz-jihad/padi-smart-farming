import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:padi/features/auth/presentation/widgets/padi_theme.dart';

class MarketplaceScreen extends StatelessWidget {
  const MarketplaceScreen({super.key});

  static const products = [
    {
      'id': '1',
      'name': 'Pupuk Urea',
      'category': 'Pupuk',
      'price': 120000,
      'description': 'Pupuk nitrogen untuk membantu pertumbuhan tanaman padi.',
      'icon': Icons.grass_rounded,
    },
    {
      'id': '2',
      'name': 'Benih Padi Unggul',
      'category': 'Benih',
      'price': 85000,
      'description': 'Benih padi unggul untuk mendukung hasil panen yang optimal.',
      'icon': Icons.spa_rounded,
    },
    {
      'id': '3',
      'name': 'Pupuk NPK',
      'category': 'Pupuk',
      'price': 150000,
      'description': 'Pupuk NPK dengan kandungan nutrisi lengkap untuk tanaman.',
      'icon': Icons.eco_rounded,
    },
    {
      'id': '4',
      'name': 'Sprayer Manual',
      'category': 'Alat',
      'price': 250000,
      'description': 'Alat penyemprot manual untuk kebutuhan perawatan tanaman.',
      'icon': Icons.water_drop_rounded,
    },
    {
      'id': '5',
      'name': 'Pestisida Tanaman',
      'category': 'Pestisida',
      'price': 95000,
      'description': 'Produk perlindungan tanaman untuk membantu mengendalikan hama.',
      'icon': Icons.bug_report_rounded,
    },
    {
      'id': '6',
      'name': 'Pupuk Organik',
      'category': 'Pupuk',
      'price': 75000,
      'description': 'Pupuk organik untuk membantu menjaga kesuburan tanah.',
      'icon': Icons.compost_rounded,
    },
  ];

  String formatPrice(int price) {
    return 'Rp ${price.toString().replaceAllMapped(
          RegExp(r'\B(?=(\d{3})+(?!\d))'),
          (match) => '.',
        )}';
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: padiField,
      appBar: AppBar(
        backgroundColor: padiField,
        elevation: 0,
        scrolledUnderElevation: 0,
        title: const Text(
          'Marketplace',
          style: TextStyle(
            color: padiInk,
            fontSize: 20,
            fontWeight: FontWeight.w900,
          ),
        ),
        actions: [
          IconButton(
            onPressed: () {
              context.push('/marketplace/cart');
            },
            icon: const Icon(
              Icons.shopping_cart_outlined,
              color: padiInk,
            ),
          ),
        ],
      ),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(20, 8, 20, 30),
        children: [
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: padiGreen,
              borderRadius: BorderRadius.circular(24),
            ),
            child: const Row(
              children: [
                Icon(
                  Icons.storefront_rounded,
                  color: padiCream,
                  size: 36,
                ),
                SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Kebutuhan Pertanian',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 18,
                          fontWeight: FontWeight.w900,
                        ),
                      ),
                      SizedBox(height: 5),
                      Text(
                        'Temukan kebutuhan pertanian untuk sawah Anda.',
                        style: TextStyle(
                          color: Colors.white70,
                          fontSize: 12,
                          height: 1.4,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 18),
          TextField(
            decoration: InputDecoration(
              hintText: 'Cari produk...',
              prefixIcon: const Icon(
                Icons.search_rounded,
                color: padiGreen,
              ),
              suffixIcon: IconButton(
                onPressed: () {},
                icon: const Icon(Icons.tune_rounded),
              ),
            ),
          ),
          const SizedBox(height: 22),
          const Text(
            'Kategori',
            style: TextStyle(
              color: padiInk,
              fontSize: 18,
              fontWeight: FontWeight.w900,
            ),
          ),
          const SizedBox(height: 12),
          SizedBox(
            height: 42,
            child: ListView(
              scrollDirection: Axis.horizontal,
              children: const [
                _CategoryChip(
                  label: 'Semua',
                  selected: true,
                ),
                _CategoryChip(label: 'Pupuk'),
                _CategoryChip(label: 'Benih'),
                _CategoryChip(label: 'Pestisida'),
                _CategoryChip(label: 'Alat'),
              ],
            ),
          ),
          const SizedBox(height: 22),
          const Text(
            'Produk Pilihan',
            style: TextStyle(
              color: padiInk,
              fontSize: 18,
              fontWeight: FontWeight.w900,
            ),
          ),
          const SizedBox(height: 12),
          GridView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: products.length,
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 2,
              crossAxisSpacing: 12,
              mainAxisSpacing: 12,
              childAspectRatio: 0.68,
            ),
            itemBuilder: (context, index) {
              final product = products[index];

              return _ProductCard(
                name: product['name'] as String,
                category: product['category'] as String,
                price: formatPrice(product['price'] as int),
                icon: product['icon'] as IconData,
                onTap: () {
                  context.push(
                    '/marketplace/product/${product['id']}',
                  );
                },
              );
            },
          ),
        ],
      ),
    );
  }
}

class _CategoryChip extends StatelessWidget {
  const _CategoryChip({
    required this.label,
    this.selected = false,
  });

  final String label;
  final bool selected;

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(right: 8),
      child: ChoiceChip(
        label: Text(label),
        selected: selected,
        onSelected: (_) {},
        selectedColor: padiGreen,
        backgroundColor: Colors.white,
        labelStyle: TextStyle(
          color: selected ? Colors.white : padiInk,
          fontSize: 12,
          fontWeight: FontWeight.w700,
        ),
        side: BorderSide(
          color: selected
              ? padiGreen
              : Colors.black.withValues(alpha: 0.06),
        ),
      ),
    );
  }
}

class _ProductCard extends StatelessWidget {
  const _ProductCard({
    required this.name,
    required this.category,
    required this.price,
    required this.icon,
    required this.onTap,
  });

  final String name;
  final String category;
  final String price;
  final IconData icon;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.white,
      borderRadius: BorderRadius.circular(20),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(20),
        child: Padding(
          padding: const EdgeInsets.all(12),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(
                child: Container(
                  width: double.infinity,
                  decoration: BoxDecoration(
                    color: padiField,
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: Icon(
                    icon,
                    color: padiGreen,
                    size: 52,
                  ),
                ),
              ),
              const SizedBox(height: 10),
              Text(
                category,
                style: const TextStyle(
                  color: padiMuted,
                  fontSize: 10,
                ),
              ),
              const SizedBox(height: 3),
              Text(
                name,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(
                  color: padiInk,
                  fontSize: 14,
                  fontWeight: FontWeight.w900,
                ),
              ),
              const SizedBox(height: 5),
              Text(
                price,
                style: const TextStyle(
                  color: padiGreen,
                  fontSize: 13,
                  fontWeight: FontWeight.w900,
                ),
              ),
              const SizedBox(height: 8),
              SizedBox(
                width: double.infinity,
                height: 34,
                child: FilledButton(
                  onPressed: onTap,
                  style: FilledButton.styleFrom(
                    padding: EdgeInsets.zero,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(11),
                    ),
                  ),
                  child: const Text(
                    'Lihat Produk',
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}