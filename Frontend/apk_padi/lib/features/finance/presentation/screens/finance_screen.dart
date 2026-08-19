import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:padi/features/auth/presentation/widgets/padi_theme.dart';
import 'package:padi/features/finance/presentation/widgets/finance_summary_card.dart';
import 'package:padi/features/finance/presentation/widgets/finance_transaction_card.dart';

class FinanceScreen extends StatelessWidget {
  const FinanceScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: padiField,
      appBar: AppBar(
        backgroundColor: padiField,
        elevation: 0,
        scrolledUnderElevation: 0,
        leading: IconButton(
          onPressed: () => context.go('/home'),
          icon: const Icon(
            Icons.arrow_back_rounded,
            color: padiInk,
          ),
        ),
        title: const Text(
          'Keuangan',
          style: TextStyle(
            color: padiInk,
            fontSize: 20,
            fontWeight: FontWeight.w900,
          ),
        ),
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => context.push('/finance/add'),
        backgroundColor: padiGreen,
        foregroundColor: Colors.white,
        icon: const Icon(Icons.add_rounded),
        label: const Text(
          'Tambah',
          style: TextStyle(
            fontWeight: FontWeight.w800,
          ),
        ),
      ),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(20, 8, 20, 100),
        children: [
          const FinanceSummaryCard(
            income: 3500000,
            expense: 1250000,
          ),
          const SizedBox(height: 22),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                'Transaksi Terbaru',
                style: TextStyle(
                  color: padiInk,
                  fontSize: 19,
                  fontWeight: FontWeight.w900,
                ),
              ),
              TextButton(
                onPressed: () {},
                child: const Text('Lihat Semua'),
              ),
            ],
          ),
          const SizedBox(height: 6),
          const FinanceTransactionCard(
            title: 'Penjualan hasil panen',
            date: '18 Agustus 2026',
            amount: 3500000,
            isIncome: true,
            icon: Icons.agriculture_rounded,
          ),
          const SizedBox(height: 10),
          const FinanceTransactionCard(
            title: 'Pembelian pupuk Urea',
            date: '15 Agustus 2026',
            amount: 450000,
            isIncome: false,
            icon: Icons.grass_rounded,
          ),
          const SizedBox(height: 10),
          const FinanceTransactionCard(
            title: 'Pembelian benih padi',
            date: '12 Agustus 2026',
            amount: 800000,
            isIncome: false,
            icon: Icons.spa_rounded,
          ),
        ],
      ),
    );
  }
}