import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import 'package:url_launcher/url_launcher.dart';

import 'package:padi/core/network/api_client.dart';
import 'package:padi/core/storage/token_storage.dart';
import 'package:padi/features/marketplace/data/models/purchase_contract_model.dart';
import 'package:padi/features/marketplace/data/services/marketplace_api_service.dart';

class FarmerSalesReportScreen extends StatefulWidget {
  const FarmerSalesReportScreen({super.key});

  @override
  State<FarmerSalesReportScreen> createState() =>
      _FarmerSalesReportScreenState();
}

class _FarmerSalesReportScreenState extends State<FarmerSalesReportScreen> {
  late final MarketplaceApiService _service;
  String _selectedPeriod = 'all'; // 'all', 'month', 'season'
  bool _isLoading = true;
  String? _error;

  double _totalRevenue = 0;
  double _totalVolume = 0;
  int _totalTransactions = 0;
  double _averagePrice = 0;
  List<PurchaseContractModel> _contracts = [];

  @override
  void initState() {
    super.initState();
    _service = MarketplaceApiService(ApiClient(const SecureTokenStorage()));
    _loadReport();
  }

  Future<void> _loadReport() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final res = await _service.fetchSalesReport(period: _selectedPeriod);
      final summary = res['summary'] as Map<String, dynamic>?;
      final rawContracts = res['contracts'] as List<dynamic>?;

      if (!mounted) return;

      if (summary != null) {
        _totalRevenue = (summary['total_revenue'] as num?)?.toDouble() ?? 0;
        _totalVolume = (summary['total_volume'] as num?)?.toDouble() ?? 0;
        _totalTransactions = (summary['total_transactions'] as num?)?.toInt() ?? 0;
        _averagePrice = (summary['average_price'] as num?)?.toDouble() ?? 0;
      }

      if (rawContracts != null) {
        _contracts = rawContracts
            .whereType<Map>()
            .map((c) => PurchaseContractModel.fromJson(Map<String, dynamic>.from(c)))
            .toList();
      } else {
        // Fallback to fetchContracts
        final all = await _service.fetchContracts();
        _contracts = all;
        _totalRevenue = all.fold(0, (sum, c) => sum + c.totalAmount);
        _totalVolume = all.fold(0, (sum, c) => sum + c.quantity);
        _totalTransactions = all.length;
        _averagePrice = _totalVolume > 0 ? _totalRevenue / _totalVolume : 0;
      }

      setState(() => _isLoading = false);
    } catch (e) {
      if (!mounted) return;
      // If backend reports empty, provide clean state
      setState(() {
        _isLoading = false;
        _error = null;
      });
    }
  }

  String _formatCurrency(num value) {
    return NumberFormat.currency(
      locale: 'id_ID',
      symbol: 'Rp ',
      decimalDigits: 0,
    ).format(value);
  }

  String _formatDate(dynamic dt) {
    DateTime date = DateTime.now();
    if (dt is DateTime) {
      date = dt;
    } else if (dt is String) {
      date = DateTime.tryParse(dt) ?? DateTime.now();
    }
    return DateFormat('dd MMM yyyy', 'id_ID').format(date);
  }

  Future<void> _shareSummaryToWhatsApp() async {
    final buffer = StringBuffer();
    buffer.writeln('🌾 *REKAPITULASI LAPORAN PENJUALAN PANEN P.A.D.I.* 🌾');
    buffer.writeln('Periode: ${_selectedPeriod == 'month' ? 'Bulan Ini' : (_selectedPeriod == 'season' ? 'Musim Tanam Ini' : 'Semua Waktu')}');
    buffer.writeln('Tanggal Cetak: ${DateFormat('dd MMMM yyyy, HH:mm', 'id_ID').format(DateTime.now())} WIB');
    buffer.writeln('------------------------------------------');
    buffer.writeln('💰 *Total Omzet Penjualan:* ${_formatCurrency(_totalRevenue)}');
    buffer.writeln('📦 *Total Volume Terjual:* ${NumberFormat.decimalPattern('id_ID').format(_totalVolume.round())} kg (${(_totalVolume / 1000).toStringAsFixed(1)} Ton)');
    buffer.writeln('🤝 *Total Transaksi Selesai:* $_totalTransactions Kontrak');
    buffer.writeln('📊 *Rata-rata Harga Jual:* ${_formatCurrency(_averagePrice)} / kg');
    buffer.writeln('------------------------------------------');
    buffer.writeln('Rincian Transaksi:');

    for (var i = 0; i < _contracts.length; i++) {
      final c = _contracts[i];
      buffer.writeln('${i + 1}. ${c.commodity ?? 'Gabah'} - ${NumberFormat.decimalPattern('id_ID').format(c.quantity.round())} ${c.unit} (${_formatCurrency(c.totalAmount)})');
      buffer.writeln('   Pembeli: ${c.partnerName ?? 'Mitra B2B'} • ${_formatDate(c.contractedAt)}');
    }

    buffer.writeln('');
    buffer.writeln('_Laporan resmi diterbitkan oleh Aplikasi P.A.D.I. Smart Farming_');

    final uri = Uri.parse('https://wa.me/?text=${Uri.encodeComponent(buffer.toString())}');
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        backgroundColor: Colors.white,
        foregroundColor: const Color(0xFF0F172A),
        elevation: 0,
        scrolledUnderElevation: 1,
        leading: IconButton(
          tooltip: 'Kembali',
          icon: const Icon(Icons.arrow_back_ios_new_rounded, size: 18, color: Color(0xFF0F172A)),
          onPressed: () {
            if (context.canPop()) {
              context.pop();
            } else {
              context.go('/home');
            }
          },
        ),
        title: const Text(
          'Laporan Penjualan Panen',
          style: TextStyle(
            fontSize: 17,
            fontWeight: FontWeight.w900,
            color: Color(0xFF0F172A),
          ),
        ),
        actions: [
          IconButton(
            tooltip: 'Bagikan Rekap Penjualan',
            icon: const Icon(Icons.share_outlined, color: Color(0xFF059669)),
            onPressed: _contracts.isNotEmpty ? _shareSummaryToWhatsApp : null,
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: Color(0xFF059669)))
          : SafeArea(
              child: RefreshIndicator(
                onRefresh: _loadReport,
                color: const Color(0xFF059669),
                child: Center(
                  child: ConstrainedBox(
                    constraints: const BoxConstraints(maxWidth: 540),
                    child: ListView(
                      padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 16),
                      physics: const AlwaysScrollableScrollPhysics(parent: BouncingScrollPhysics()),
                      children: [
                        // 1. Period Selector Chips
                        _buildPeriodSelector(),

                        const SizedBox(height: 16),

                        // 2. Financial & Volume Metric Grid
                        _buildMetricGrid(),

                        const SizedBox(height: 20),

                        // 3. Transactions Section Header
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Row(
                              children: [
                                Container(
                                  padding: const EdgeInsets.all(5),
                                  decoration: BoxDecoration(
                                    color: const Color(0xFFDCFCE7),
                                    borderRadius: BorderRadius.circular(6),
                                  ),
                                  child: const Icon(Icons.receipt_long_rounded, color: Color(0xFF047857), size: 16),
                                ),
                                const SizedBox(width: 8),
                                const Text(
                                  'Riwayat Transaksi Penjualan',
                                  style: TextStyle(
                                    fontSize: 14,
                                    fontWeight: FontWeight.w900,
                                    color: Color(0xFF0F172A),
                                  ),
                                ),
                              ],
                            ),
                            Text(
                              '${_contracts.length} Transaksi',
                              style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w700, color: Color(0xFF64748B)),
                            ),
                          ],
                        ),

                        const SizedBox(height: 12),

                        // 4. Contracts List
                        if (_contracts.isEmpty)
                          _buildEmptyState()
                        else
                          ..._contracts.map((c) => _buildContractItem(c)),

                        const SizedBox(height: 24),
                      ],
                    ),
                  ),
                ),
              ),
            ),
    );
  }

  Widget _buildPeriodSelector() {
    final periods = [
      ('all', 'Semua Waktu'),
      ('month', 'Bulan Ini'),
      ('season', 'Musim Tanam Ini'),
    ];

    return Container(
      padding: const EdgeInsets.all(4),
      decoration: BoxDecoration(
        color: const Color(0xFFE2E8F0),
        borderRadius: BorderRadius.circular(12),
      ),
      child: Row(
        children: periods.map((p) {
          final isSelected = _selectedPeriod == p.$1;
          return Expanded(
            child: InkWell(
              onTap: () {
                if (_selectedPeriod != p.$1) {
                  setState(() => _selectedPeriod = p.$1);
                  _loadReport();
                }
              },
              borderRadius: BorderRadius.circular(9),
              child: Container(
                padding: const EdgeInsets.symmetric(vertical: 8),
                decoration: BoxDecoration(
                  color: isSelected ? Colors.white : Colors.transparent,
                  borderRadius: BorderRadius.circular(9),
                  boxShadow: isSelected
                      ? [
                          BoxShadow(
                            color: Colors.black.withOpacity(0.06),
                            blurRadius: 4,
                            offset: const Offset(0, 1),
                          ),
                        ]
                      : null,
                ),
                child: Center(
                  child: Text(
                    p.$2,
                    style: TextStyle(
                      fontSize: 11.5,
                      fontWeight: isSelected ? FontWeight.w800 : FontWeight.w600,
                      color: isSelected ? const Color(0xFF0F5132) : const Color(0xFF64748B),
                    ),
                  ),
                ),
              ),
            ),
          );
        }).toList(),
      ),
    );
  }

  Widget _buildMetricGrid() {
    final tonVolume = (_totalVolume / 1000).toStringAsFixed(1);

    return Column(
      children: [
        // Big Revenue Banner
        Container(
          width: double.infinity,
          padding: const EdgeInsets.all(18),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(16),
            border: Border.all(color: const Color(0xFFA7F3D0)),
            boxShadow: [
              BoxShadow(
                color: const Color(0xFF059669).withOpacity(0.06),
                blurRadius: 12,
                offset: const Offset(0, 3),
              ),
            ],
          ),
          child: Row(
            children: [
              Container(
                width: 48,
                height: 48,
                decoration: BoxDecoration(
                  color: const Color(0xFFECFDF5),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: const Icon(Icons.payments_rounded, color: Color(0xFF059669), size: 24),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'TOTAL OMZET PENJUALAN PANEN',
                      style: TextStyle(
                        fontSize: 10,
                        fontWeight: FontWeight.w800,
                        color: Color(0xFF047857),
                        letterSpacing: 0.5,
                      ),
                    ),
                    const SizedBox(height: 3),
                    Text(
                      _formatCurrency(_totalRevenue),
                      style: const TextStyle(
                        fontSize: 22,
                        fontWeight: FontWeight.w900,
                        color: Color(0xFF0F5132),
                        letterSpacing: -0.5,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),

        const SizedBox(height: 10),

        // 3 Supporting Metric Cards
        Row(
          children: [
            Expanded(
              child: _buildSmallMetric(
                label: 'Volume Terjual',
                value: '$tonVolume Ton',
                subValue: '${NumberFormat.decimalPattern('id_ID').format(_totalVolume.round())} kg',
                icon: Icons.grain_rounded,
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: _buildSmallMetric(
                label: 'Transaksi Sukses',
                value: '$_totalTransactions Kontrak',
                subValue: 'Bursa Gabah',
                icon: Icons.check_circle_outline_rounded,
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: _buildSmallMetric(
                label: 'Rata-rata Harga',
                value: _formatCurrency(_averagePrice),
                subValue: 'per kg panen',
                icon: Icons.trending_up_rounded,
              ),
            ),
          ],
        ),
      ],
    );
  }

  Widget _buildSmallMetric({
    required String label,
    required String value,
    required String subValue,
    required IconData icon,
  }) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, size: 18, color: const Color(0xFF059669)),
          const SizedBox(height: 8),
          Text(
            value,
            style: const TextStyle(
              fontSize: 13,
              fontWeight: FontWeight.w900,
              color: Color(0xFF0F172A),
            ),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
          const SizedBox(height: 2),
          Text(
            label,
            style: const TextStyle(fontSize: 10, color: Color(0xFF64748B), fontWeight: FontWeight.w600),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
        ],
      ),
    );
  }

  Widget _buildContractItem(PurchaseContractModel c) {
    final qtyStr = NumberFormat.decimalPattern('id_ID').format(c.quantity.round());

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.02),
            blurRadius: 6,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Expanded(
                child: Text(
                  c.commodity ?? 'Gabah / Beras P.A.D.I.',
                  style: const TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.w900,
                    color: Color(0xFF0F172A),
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                decoration: BoxDecoration(
                  color: const Color(0xFFDCFCE7),
                  borderRadius: BorderRadius.circular(4),
                ),
                child: Text(
                  c.status.toUpperCase(),
                  style: const TextStyle(
                    fontSize: 9.5,
                    fontWeight: FontWeight.w900,
                    color: Color(0xFF047857),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 6),
          Row(
            children: [
              const Icon(Icons.business_rounded, size: 14, color: Color(0xFF64748B)),
              const SizedBox(width: 4),
              Text(
                'Pembeli: ${c.partnerName ?? 'Mitra B2B'}',
                style: const TextStyle(fontSize: 11.5, color: Color(0xFF64748B)),
              ),
              const Spacer(),
              Text(
                _formatDate(c.contractedAt),
                style: const TextStyle(fontSize: 11, color: Color(0xFF94A3B8)),
              ),
            ],
          ),
          const SizedBox(height: 10),
          const Divider(height: 1, color: Color(0xFFF1F5F9)),
          const SizedBox(height: 10),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Volume: $qtyStr ${c.unit}  •  ${_formatCurrency(c.agreedPrice)} / ${c.unit}',
                    style: const TextStyle(fontSize: 11, color: Color(0xFF64748B)),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    _formatCurrency(c.totalAmount),
                    style: const TextStyle(
                      fontSize: 14.5,
                      fontWeight: FontWeight.w900,
                      color: Color(0xFF0F5132),
                    ),
                  ),
                ],
              ),
              OutlinedButton.icon(
                onPressed: () {
                  context.push('/faktur/${c.id}', extra: c);
                },
                style: OutlinedButton.styleFrom(
                  foregroundColor: const Color(0xFF059669),
                  side: const BorderSide(color: Color(0xFF059669)),
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  minimumSize: const Size(0, 32),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                ),
                icon: const Icon(Icons.receipt_rounded, size: 14),
                label: const Text('Buka Faktur', style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.w800)),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildEmptyState() {
    return Container(
      padding: const EdgeInsets.all(32),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: const Column(
        children: [
          Icon(Icons.inventory_2_outlined, size: 48, color: Color(0xFF94A3B8)),
          SizedBox(height: 12),
          Text(
            'Belum Ada Riwayat Penjualan',
            style: TextStyle(fontSize: 15, fontWeight: FontWeight.w800, color: Color(0xFF0F172A)),
          ),
          SizedBox(height: 4),
          Text(
            'Penjualan hasil panen gabah & beras yang telah disetujui akan tercatat otomatis di laporan ini.',
            textAlign: TextAlign.center,
            style: TextStyle(fontSize: 12, color: Color(0xFF64748B)),
          ),
        ],
      ),
    );
  }
}
