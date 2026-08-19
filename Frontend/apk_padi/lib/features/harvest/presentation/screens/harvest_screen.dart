import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:padi/features/auth/presentation/widgets/padi_theme.dart';
import 'package:padi/features/harvest/presentation/widgets/harvest_record_card.dart';
import 'package:padi/features/harvest/presentation/widgets/harvest_summary_card.dart';

class HarvestScreen extends StatelessWidget {
  const HarvestScreen({super.key});

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
          'Catatan Panen',
          style: TextStyle(
            color: padiInk,
            fontSize: 20,
            fontWeight: FontWeight.w900,
          ),
        ),
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => context.push('/harvest/add'),
        backgroundColor: padiGreen,
        foregroundColor: Colors.white,
        icon: const Icon(Icons.add_rounded),
        label: const Text(
          'Catat Panen',
          style: TextStyle(
            fontWeight: FontWeight.w800,
          ),
        ),
      ),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(20, 8, 20, 100),
        children: [
          const HarvestSummaryCard(
            totalHarvest: 2450,
            totalRevenue: 14700000,
          ),
          const SizedBox(height: 22),
          const Text(
            'Riwayat Panen',
            style: TextStyle(
              color: padiInk,
              fontSize: 19,
              fontWeight: FontWeight.w900,
            ),
          ),
          const SizedBox(height: 12),
          const HarvestRecordCard(
            title: 'Sawah Blok A',
            date: '18 Agustus 2026',
            harvest: 1200,
            price: 6000,
          ),
          const SizedBox(height: 10),
          const HarvestRecordCard(
            title: 'Sawah Blok B',
            date: '10 Agustus 2026',
            harvest: 750,
            price: 6000,
          ),
          const SizedBox(height: 10),
          const HarvestRecordCard(
            title: 'Sawah Blok A',
            date: '25 Juli 2026',
            harvest: 500,
            price: 6000,
          ),
        ],
      ),
    );
  }
}