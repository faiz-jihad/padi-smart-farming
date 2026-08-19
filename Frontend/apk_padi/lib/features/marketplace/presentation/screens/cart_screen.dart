import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:padi/features/auth/presentation/widgets/padi_theme.dart';

class CartScreen extends StatefulWidget {
  const CartScreen({super.key});

  @override
  State<CartScreen> createState() => _CartScreenState();
}

class _CartScreenState extends State<CartScreen> {
  int quantity = 1;

  final int price = 120000;

  String formatPrice(int price) {
    return 'Rp ${price.toString().replaceAllMapped(
          RegExp(r'\B(?=(\d{3})+(?!\d))'),
          (match) => '.',
        )}';
  }

  @override
  Widget build(BuildContext context) {
    final subtotal = price * quantity;
    const shipping = 15000;
    final total = subtotal + shipping;

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
        title: const Text(
          'Keranjang',
          style: TextStyle(
            color: padiInk,
            fontSize: 20,
            fontWeight: FontWeight.w900,
          ),
        ),
      ),
      bottomNavigationBar: SafeArea(
        child: Container(
          padding: const EdgeInsets.fromLTRB(20, 12, 20, 12),
          color: Colors.white,
          child: SizedBox(
            height: 52,
            child: FilledButton(
              onPressed: () {
                context.push('/marketplace/order');
              },
              child: const Text(
                'Lanjutkan Pesanan',
                style: TextStyle(
                  fontWeight: FontWeight.w900,
                ),
              ),
            ),
          ),
        ),
      ),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(20, 8, 20, 100),
        children: [
          const Text(
            'Produk di Keranjang',
            style: TextStyle(
              color: padiInk,
              fontSize: 19,
              fontWeight: FontWeight.w900,
            ),
          ),
          const SizedBox(height: 12),
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(22),
            ),
            child: Row(
              children: [
                Container(
                  width: 76,
                  height: 76,
                  decoration: BoxDecoration(
                    color: padiField,
                    borderRadius: BorderRadius.circular(17),
                  ),
                  child: const Icon(
                    Icons.grass_rounded,
                    color: padiGreen,
                    size: 40,
                  ),
                ),
                const SizedBox(width: 13),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Pupuk Urea',
                        style: TextStyle(
                          color: padiInk,
                          fontSize: 15,
                          fontWeight: FontWeight.w900,
                        ),
                      ),
                      const SizedBox(height: 5),
                      Text(
                        formatPrice(price),
                        style: const TextStyle(
                          color: padiGreen,
                          fontSize: 13,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                      const SizedBox(height: 10),
                      Row(
                        children: [
                          IconButton(
                            visualDensity: VisualDensity.compact,
                            onPressed: quantity > 1
                                ? () {
                                    setState(() {
                                      quantity--;
                                    });
                                  }
                                : null,
                            icon: const Icon(
                              Icons.remove_circle_outline,
                              size: 21,
                            ),
                          ),
                          Text(
                            '$quantity',
                            style: const TextStyle(
                              fontWeight: FontWeight.w900,
                            ),
                          ),
                          IconButton(
                            visualDensity: VisualDensity.compact,
                            onPressed: () {
                              setState(() {
                                quantity++;
                              });
                            },
                            icon: const Icon(
                              Icons.add_circle_outline,
                              size: 21,
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
                IconButton(
                  onPressed: () {},
                  icon: const Icon(
                    Icons.delete_outline_rounded,
                    color: Colors.redAccent,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 22),
          Container(
            padding: const EdgeInsets.all(19),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(22),
            ),
            child: Column(
              children: [
                const Align(
                  alignment: Alignment.centerLeft,
                  child: Text(
                    'Ringkasan Belanja',
                    style: TextStyle(
                      color: padiInk,
                      fontSize: 17,
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                ),
                const SizedBox(height: 16),
                _PriceRow(
                  label: 'Subtotal',
                  value: formatPrice(subtotal),
                ),
                const SizedBox(height: 10),
                _PriceRow(
                  label: 'Ongkos kirim',
                  value: formatPrice(shipping),
                ),
                const Padding(
                  padding: EdgeInsets.symmetric(vertical: 14),
                  child: Divider(),
                ),
                _PriceRow(
                  label: 'Total',
                  value: formatPrice(total),
                  bold: true,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _PriceRow extends StatelessWidget {
  const _PriceRow({
    required this.label,
    required this.value,
    this.bold = false,
  });

  final String label;
  final String value;
  final bool bold;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Expanded(
          child: Text(
            label,
            style: TextStyle(
              color: padiMuted,
              fontSize: 13,
              fontWeight: bold ? FontWeight.w900 : FontWeight.w600,
            ),
          ),
        ),
        Text(
          value,
          style: TextStyle(
            color: bold ? padiGreen : padiInk,
            fontSize: bold ? 16 : 13,
            fontWeight: FontWeight.w900,
          ),
        ),
      ],
    );
  }
}