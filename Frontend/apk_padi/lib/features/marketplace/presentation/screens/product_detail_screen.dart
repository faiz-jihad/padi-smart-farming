import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:padi/features/auth/presentation/widgets/padi_theme.dart';

class ProductDetailScreen extends StatefulWidget {
  const ProductDetailScreen({
    super.key,
    required this.productId,
  });

  final String productId;

  @override
  State<ProductDetailScreen> createState() => _ProductDetailScreenState();
}

class _ProductDetailScreenState extends State<ProductDetailScreen> {
  int quantity = 1;

  final products = {
    '1': {
      'name': 'Pupuk Urea',
      'category': 'Pupuk',
      'price': 120000,
      'stock': 'Tersedia',
      'icon': Icons.grass_rounded,
      'description':
          'Pupuk Urea digunakan untuk membantu memenuhi kebutuhan nitrogen tanaman padi dan mendukung pertumbuhan tanaman.',
    },
    '2': {
      'name': 'Benih Padi Unggul',
      'category': 'Benih',
      'price': 85000,
      'stock': 'Tersedia',
      'icon': Icons.spa_rounded,
      'description':
          'Benih padi unggul yang dapat digunakan untuk membantu menghasilkan tanaman yang sehat dan produktif.',
    },
    '3': {
      'name': 'Pupuk NPK',
      'category': 'Pupuk',
      'price': 150000,
      'stock': 'Tersedia',
      'icon': Icons.eco_rounded,
      'description':
          'Pupuk NPK dengan kandungan nutrisi yang membantu memenuhi kebutuhan tanaman selama pertumbuhan.',
    },
    '4': {
      'name': 'Sprayer Manual',
      'category': 'Alat',
      'price': 250000,
      'stock': 'Tersedia',
      'icon': Icons.water_drop_rounded,
      'description':
          'Sprayer manual untuk membantu proses penyemprotan pupuk cair maupun produk perlindungan tanaman.',
    },
    '5': {
      'name': 'Pestisida Tanaman',
      'category': 'Pestisida',
      'price': 95000,
      'stock': 'Tersedia',
      'icon': Icons.bug_report_rounded,
      'description':
          'Produk perlindungan tanaman yang digunakan untuk membantu mengendalikan gangguan hama pada tanaman.',
    },
    '6': {
      'name': 'Pupuk Organik',
      'category': 'Pupuk',
      'price': 75000,
      'stock': 'Tersedia',
      'icon': Icons.compost_rounded,
      'description':
          'Pupuk organik untuk membantu menjaga kondisi dan kesuburan tanah pada lahan pertanian.',
    },
  };

  String formatPrice(int price) {
    return 'Rp ${price.toString().replaceAllMapped(
          RegExp(r'\B(?=(\d{3})+(?!\d))'),
          (match) => '.',
        )}';
  }

  @override
  Widget build(BuildContext context) {
    final product = products[widget.productId];

    if (product == null) {
      return Scaffold(
        appBar: AppBar(),
        body: const Center(
          child: Text('Produk tidak ditemukan'),
        ),
      );
    }

    final price = product['price'] as int;
    final total = price * quantity;

    return Scaffold(
      backgroundColor: padiField,
      appBar: AppBar(
        backgroundColor: padiField,
        elevation: 0,
        leading: IconButton(
          onPressed: () => context.pop(),
          icon: const Icon(
            Icons.arrow_back_rounded,
            color: padiInk,
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
      bottomNavigationBar: SafeArea(
        child: Container(
          padding: const EdgeInsets.fromLTRB(20, 12, 20, 12),
          color: Colors.white,
          child: Row(
            children: [
              Expanded(
                child: OutlinedButton(
                  onPressed: () {
                    context.push('/marketplace/cart');
                  },
                  child: const Text('Keranjang'),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: FilledButton(
                  onPressed: () {
                    context.push('/marketplace/cart');
                  },
                  child: const Text('Beli Sekarang'),
                ),
              ),
            ],
          ),
        ),
      ),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(20, 8, 20, 100),
        children: [
          Container(
            height: 260,
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(26),
            ),
            child: Icon(
              product['icon'] as IconData,
              color: padiGreen,
              size: 110,
            ),
          ),
          const SizedBox(height: 20),
          Text(
            product['category'] as String,
            style: const TextStyle(
              color: padiMuted,
              fontSize: 13,
            ),
          ),
          const SizedBox(height: 5),
          Text(
            product['name'] as String,
            style: const TextStyle(
              color: padiInk,
              fontSize: 25,
              fontWeight: FontWeight.w900,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            formatPrice(price),
            style: const TextStyle(
              color: padiGreen,
              fontSize: 20,
              fontWeight: FontWeight.w900,
            ),
          ),
          const SizedBox(height: 14),
          Row(
            children: [
              const Icon(
                Icons.check_circle_rounded,
                color: padiGreen,
                size: 18,
              ),
              const SizedBox(width: 6),
              Text(
                product['stock'] as String,
                style: const TextStyle(
                  color: padiGreen,
                  fontWeight: FontWeight.w700,
                ),
              ),
            ],
          ),
          const SizedBox(height: 22),
          Container(
            padding: const EdgeInsets.all(18),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(20),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Deskripsi Produk',
                  style: TextStyle(
                    color: padiInk,
                    fontSize: 17,
                    fontWeight: FontWeight.w900,
                  ),
                ),
                const SizedBox(height: 10),
                Text(
                  product['description'] as String,
                  style: const TextStyle(
                    color: padiMuted,
                    fontSize: 13,
                    height: 1.5,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 18),
          Container(
            padding: const EdgeInsets.all(18),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(20),
            ),
            child: Row(
              children: [
                const Expanded(
                  child: Text(
                    'Jumlah',
                    style: TextStyle(
                      color: padiInk,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                ),
                IconButton(
                  onPressed: quantity > 1
                      ? () {
                          setState(() {
                            quantity--;
                          });
                        }
                      : null,
                  icon: const Icon(Icons.remove_circle_outline),
                ),
                Text(
                  '$quantity',
                  style: const TextStyle(
                    color: padiInk,
                    fontSize: 17,
                    fontWeight: FontWeight.w900,
                  ),
                ),
                IconButton(
                  onPressed: () {
                    setState(() {
                      quantity++;
                    });
                  },
                  icon: const Icon(Icons.add_circle_outline),
                ),
              ],
            ),
          ),
          const SizedBox(height: 12),
          Text(
            'Total: ${formatPrice(total)}',
            textAlign: TextAlign.right,
            style: const TextStyle(
              color: padiGreen,
              fontSize: 14,
              fontWeight: FontWeight.w900,
            ),
          ),
        ],
      ),
    );
  }
}