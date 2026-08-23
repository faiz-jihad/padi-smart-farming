import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import 'package:padi/core/network/api_client.dart';
import 'package:padi/core/storage/token_storage.dart';
import 'package:padi/features/auth/presentation/widgets/padi_theme.dart';
import 'package:padi/features/harvest/data/models/harvest_model.dart';
import 'package:padi/features/harvest/data/services/harvest_api_service.dart';
import 'package:padi/features/harvest/presentation/widgets/harvest_record_card.dart';
import 'package:padi/features/harvest/presentation/widgets/harvest_summary_card.dart';

class HarvestScreen extends StatefulWidget {
  const HarvestScreen({super.key});

  @override
  State<HarvestScreen> createState() => _HarvestScreenState();
}

class _HarvestScreenState extends State<HarvestScreen> {
  late final HarvestApiService _harvestApiService;

  List<HarvestModel> _harvests = [];

  bool _isLoading = true;

  String? _errorMessage;

  @override
  void initState() {
    super.initState();

    _harvestApiService = HarvestApiService(
      ApiClient(
        const SecureTokenStorage(),
      ),
    );

    _loadHarvests();
  }

  Future<void> _loadHarvests() async {
    if (mounted) {
      setState(() {
        _isLoading = true;
        _errorMessage = null;
      });
    }

    try {
      final harvests =
          await _harvestApiService.fetchHarvests();

      if (!mounted) return;

      setState(() {
        _harvests = harvests;
        _isLoading = false;
      });
    } catch (e) {
      if (!mounted) return;

      setState(() {
        _isLoading = false;
        _errorMessage = 'Gagal mengambil data panen.';
      });
    }
  }

  double get _totalHarvest {
    return _harvests.fold(
      0,
      (total, harvest) => total + harvest.quantity,
    );
  }

  String _formatNumber(double value) {
    if (value == value.roundToDouble()) {
      return value.toInt().toString();
    }

    return value.toStringAsFixed(1);
  }

  String _formatDate(String date) {
    if (date.isEmpty) {
      return '-';
    }

    final parsed = DateTime.tryParse(date);

    if (parsed == null) {
      return date;
    }

    const months = [
      'Januari',
      'Februari',
      'Maret',
      'April',
      'Mei',
      'Juni',
      'Juli',
      'Agustus',
      'September',
      'Oktober',
      'November',
      'Desember',
    ];

    return '${parsed.day} ${months[parsed.month - 1]} ${parsed.year}';
  }

  Future<void> _openAddHarvest() async {
    await context.push('/harvest/add');

    if (!mounted) return;

    await _loadHarvests();
  }

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
        onPressed: _openAddHarvest,
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
      body: _buildBody(),
    );
  }

  Widget _buildBody() {
    if (_isLoading) {
      return const Center(
        child: CircularProgressIndicator(),
      );
    }

    if (_errorMessage != null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(20),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(
                Icons.error_outline_rounded,
                color: Colors.redAccent,
                size: 50,
              ),
              const SizedBox(height: 12),
              Text(
                _errorMessage!,
                textAlign: TextAlign.center,
                style: const TextStyle(
                  color: padiInk,
                  fontWeight: FontWeight.w700,
                ),
              ),
              const SizedBox(height: 15),
              FilledButton(
                onPressed: _loadHarvests,
                child: const Text('Coba Lagi'),
              ),
            ],
          ),
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: _loadHarvests,
      child: ListView(
        padding: const EdgeInsets.fromLTRB(
          20,
          8,
          20,
          100,
        ),
        children: [
          HarvestSummaryCard(
            totalHarvest: _totalHarvest,
            totalRevenue: 0,
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
          if (_harvests.isEmpty)
            Container(
              padding: const EdgeInsets.symmetric(
                horizontal: 25,
                vertical: 40,
              ),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(22),
              ),
              child: const Column(
                children: [
                  Icon(
                    Icons.agriculture_outlined,
                    color: padiGreen,
                    size: 55,
                  ),
                  SizedBox(height: 12),
                  Text(
                    'Belum ada catatan panen',
                    style: TextStyle(
                      color: padiInk,
                      fontSize: 17,
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                  SizedBox(height: 5),
                  Text(
                    'Tambahkan hasil panen pertama Anda.',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      color: padiMuted,
                      fontSize: 13,
                    ),
                  ),
                ],
              ),
            )
          else
            ..._harvests.map(
              (harvest) {
                return Padding(
                  padding: const EdgeInsets.only(bottom: 10),
                  child: HarvestRecordCard(
                    title:
                        'Musim Tanam #${harvest.cropSeasonId}',
                    date: _formatDate(
                      harvest.harvestDate,
                    ),
                    harvest: harvest.quantity,
                    price: 0,
                  ),
                );
              },
            ),
        ],
      ),
    );
  }
}