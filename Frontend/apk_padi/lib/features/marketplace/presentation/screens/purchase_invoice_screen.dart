import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import 'package:url_launcher/url_launcher.dart';

import 'package:padi/core/network/api_client.dart';
import 'package:padi/core/storage/token_storage.dart';
import 'package:padi/features/marketplace/data/models/purchase_contract_model.dart';
import 'package:padi/features/marketplace/data/services/marketplace_api_service.dart';

class PurchaseInvoiceScreen extends StatefulWidget {
  const PurchaseInvoiceScreen({
    super.key,
    required this.contractId,
    this.initialContract,
  });

  final int contractId;
  final PurchaseContractModel? initialContract;

  @override
  State<PurchaseInvoiceScreen> createState() => _PurchaseInvoiceScreenState();
}

class _PurchaseInvoiceScreenState extends State<PurchaseInvoiceScreen> {
  late final MarketplaceApiService _service;
  PurchaseContractModel? _contract;
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _service = MarketplaceApiService(ApiClient(const SecureTokenStorage()));

    if (widget.initialContract != null) {
      _contract = widget.initialContract;
      _isLoading = false;
    } else {
      _loadContract();
    }
  }

  Future<void> _loadContract() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final contract = await _service.getContract(widget.contractId);
      if (!mounted) return;
      setState(() {
        _contract = contract;
        _isLoading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _isLoading = false;
        _error = e.toString().replaceFirst('Exception: ', '');
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
    return DateFormat('dd MMMM yyyy, HH:mm', 'id_ID').format(date);
  }

  Future<void> _shareToWhatsApp() async {
    final c = _contract;
    if (c == null) return;

    final invoiceNo = 'INV/PAD/${c.id.toString().padLeft(6, '0')}';
    final qtyStr = NumberFormat.decimalPattern('id_ID').format(c.quantity.round());

    final buffer = StringBuffer();
    buffer.writeln('🌾 *FAKTUR RESMI TRANSAKSI P.A.D.I.* 🌾');
    buffer.writeln('No. Faktur: *$invoiceNo*');
    buffer.writeln('Tanggal: ${_formatDate(c.contractedAt)} WIB');
    buffer.writeln('Status: *SAH & TERLINDUNGI HUKUM*');
    buffer.writeln('------------------------------------------');
    buffer.writeln('👤 *Petani (Penjual):* ${c.farmerName ?? 'Petani Mitra P.A.D.I.'}');
    buffer.writeln('🏢 *Pembeli (Mitra B2B):* ${c.partnerName ?? 'Mitra Industri Beras'}');
    buffer.writeln('------------------------------------------');
    buffer.writeln('📦 *Rincian Komoditas:*');
    buffer.writeln('• Komoditas: ${c.commodity ?? 'Gabah / Beras'}');
    buffer.writeln('• Kuantitas: $qtyStr ${c.unit}');
    buffer.writeln('• Harga Kesepakatan: ${_formatCurrency(c.agreedPrice)} / ${c.unit}');
    buffer.writeln('------------------------------------------');
    buffer.writeln('💰 *TOTAL TRANSAKSI: ${_formatCurrency(c.totalAmount)}*');
    buffer.writeln('------------------------------------------');
    buffer.writeln('⚖️ *Jaminan Transaksi:*');
    buffer.writeln('Wajib menggunakan timbangan tera legal berkalibrasi resmi dan uji kadar air SNI.');
    buffer.writeln('');
    buffer.writeln('_Dokumen ini diterbitkan secara elektronik oleh Sistem Bursa P.A.D.I. Smart Farming_');

    final uri = Uri.parse('https://wa.me/?text=${Uri.encodeComponent(buffer.toString())}');
    if (await canLaunchUrl(uri)) {
      await launchUrl(uri, mode: LaunchMode.externalApplication);
    }
  }

  void _showPrintDialog() {
    final c = _contract;
    if (c == null) return;

    final invoiceNo = 'INV/PAD/${c.id.toString().padLeft(6, '0')}';

    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Row(
          children: [
            Icon(Icons.check_circle_rounded, color: Color(0xFF059669), size: 22),
            SizedBox(width: 8),
            Text('Faktur Siap Dicetak', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w900)),
          ],
        ),
        content: Text(
          'Faktur resmi $invoiceNo telah siap. Anda dapat menyimpannya sebagai dokumen arsip pembukuan sah atau membagikannya ke mitra terkait.',
          style: const TextStyle(fontSize: 13, color: Color(0xFF475569)),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx),
            child: const Text('Tutup'),
          ),
          FilledButton.icon(
            onPressed: () {
              Navigator.pop(ctx);
              _shareToWhatsApp();
            },
            style: FilledButton.styleFrom(backgroundColor: const Color(0xFF059669)),
            icon: const Icon(Icons.share_rounded, size: 16),
            label: const Text('Bagikan Struk'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final c = _contract;

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
              context.go('/marketplace');
            }
          },
        ),
        title: const Text(
          'Faktur Pembelian Resmi',
          style: TextStyle(
            fontSize: 17,
            fontWeight: FontWeight.w900,
            color: Color(0xFF0F172A),
          ),
        ),
        actions: [
          IconButton(
            tooltip: 'Bagikan ke WhatsApp',
            icon: const Icon(Icons.share_outlined, color: Color(0xFF059669)),
            onPressed: c != null ? _shareToWhatsApp : null,
          ),
        ],
      ),
      body: _isLoading
          ? const Center(
              child: CircularProgressIndicator(color: Color(0xFF059669)),
            )
          : _error != null
              ? Center(
                  child: Padding(
                    padding: const EdgeInsets.all(24),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(Icons.error_outline_rounded, size: 48, color: Color(0xFFDC2626)),
                        const SizedBox(height: 12),
                        Text(_error!, textAlign: TextAlign.center),
                        const SizedBox(height: 16),
                        FilledButton(
                          onPressed: _loadContract,
                          child: const Text('Coba Lagi'),
                        ),
                      ],
                    ),
                  ),
                )
              : c == null
                  ? const Center(child: Text('Faktur tidak ditemukan.'))
                  : _buildInvoiceContent(c),
    );
  }

  Widget _buildInvoiceContent(PurchaseContractModel c) {
    final invoiceNo = 'INV/PAD/${c.id.toString().padLeft(6, '0')}';
    final qtyStr = NumberFormat.decimalPattern('id_ID').format(c.quantity.round());

    return SafeArea(
      child: Center(
        child: ConstrainedBox(
          constraints: const BoxConstraints(maxWidth: 540),
          child: ListView(
            padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 16),
            physics: const BouncingScrollPhysics(),
            children: [
              // 1. Invoice Card Container
              Container(
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(18),
                  border: Border.all(color: const Color(0xFFA7F3D0)),
                  boxShadow: [
                    BoxShadow(
                      color: const Color(0xFF059669).withOpacity(0.06),
                      blurRadius: 16,
                      offset: const Offset(0, 4),
                    ),
                  ],
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // A. Invoice Header
                    Container(
                      padding: const EdgeInsets.all(18),
                      decoration: const BoxDecoration(
                        color: Color(0xFFECFDF5),
                        borderRadius: BorderRadius.vertical(top: Radius.circular(17)),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Row(
                                children: [
                                  Container(
                                    padding: const EdgeInsets.all(6),
                                    decoration: BoxDecoration(
                                      color: const Color(0xFF059669),
                                      borderRadius: BorderRadius.circular(8),
                                    ),
                                    child: const Icon(
                                      Icons.verified_rounded,
                                      color: Colors.white,
                                      size: 16,
                                    ),
                                  ),
                                  const SizedBox(width: 8),
                                  const Text(
                                    'P.A.D.I. SMART FARMING',
                                    style: TextStyle(
                                      fontSize: 12,
                                      fontWeight: FontWeight.w900,
                                      color: Color(0xFF047857),
                                      letterSpacing: 0.5,
                                    ),
                                  ),
                                ],
                              ),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                                decoration: BoxDecoration(
                                  color: Colors.white,
                                  borderRadius: BorderRadius.circular(6),
                                  border: Border.all(color: const Color(0xFFA7F3D0)),
                                ),
                                child: Text(
                                  c.status.toUpperCase(),
                                  style: const TextStyle(
                                    fontSize: 10,
                                    fontWeight: FontWeight.w900,
                                    color: Color(0xFF047857),
                                  ),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 12),
                          Text(
                            invoiceNo,
                            style: const TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.w900,
                              color: Color(0xFF0F172A),
                              letterSpacing: -0.3,
                            ),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            'Waktu Kontrak: ${_formatDate(c.contractedAt)} WIB',
                            style: const TextStyle(fontSize: 11.5, color: Color(0xFF64748B)),
                          ),
                        ],
                      ),
                    ),

                    const Divider(height: 1, color: Color(0xFFE2E8F0)),

                    // B. Parties Information
                    Padding(
                      padding: const EdgeInsets.all(18),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              // Penjual (Petani)
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    const Text(
                                      'PIHAK PENJUAL',
                                      style: TextStyle(
                                        fontSize: 10,
                                        fontWeight: FontWeight.w800,
                                        color: Color(0xFF64748B),
                                        letterSpacing: 0.4,
                                      ),
                                    ),
                                    const SizedBox(height: 4),
                                    Text(
                                      c.farmerName ?? 'Petani Mitra P.A.D.I.',
                                      style: const TextStyle(
                                        fontSize: 13.5,
                                        fontWeight: FontWeight.w900,
                                        color: Color(0xFF0F172A),
                                      ),
                                    ),
                                    const SizedBox(height: 2),
                                    Text(
                                      c.farmerPhone ?? '+6281234567890',
                                      style: const TextStyle(fontSize: 11.5, color: Color(0xFF64748B)),
                                    ),
                                  ],
                                ),
                              ),
                              Container(height: 40, width: 1, color: const Color(0xFFE2E8F0)),
                              const SizedBox(width: 14),
                              // Pembeli
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    const Text(
                                      'PIHAK PEMBELI',
                                      style: TextStyle(
                                        fontSize: 10,
                                        fontWeight: FontWeight.w800,
                                        color: Color(0xFF64748B),
                                        letterSpacing: 0.4,
                                      ),
                                    ),
                                    const SizedBox(height: 4),
                                    Text(
                                      c.partnerName ?? 'Mitra Industri Beras',
                                      style: const TextStyle(
                                        fontSize: 13.5,
                                        fontWeight: FontWeight.w900,
                                        color: Color(0xFF0F172A),
                                      ),
                                    ),
                                    const SizedBox(height: 2),
                                    const Text(
                                      'Akun Terverifikasi B2B',
                                      style: TextStyle(fontSize: 11.5, color: Color(0xFF059669), fontWeight: FontWeight.w700),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),

                          const SizedBox(height: 18),
                          const Divider(height: 1, color: Color(0xFFF1F5F9)),
                          const SizedBox(height: 14),

                          // C. Item Details Table
                          const Text(
                            'RINCIAN TRANSAKSI KOMODITAS',
                            style: TextStyle(
                              fontSize: 10.5,
                              fontWeight: FontWeight.w800,
                              color: Color(0xFF64748B),
                              letterSpacing: 0.4,
                            ),
                          ),
                          const SizedBox(height: 10),

                          Container(
                            padding: const EdgeInsets.all(12),
                            decoration: BoxDecoration(
                              color: const Color(0xFFF8FAFC),
                              borderRadius: BorderRadius.circular(10),
                              border: Border.all(color: const Color(0xFFE2E8F0)),
                            ),
                            child: Column(
                              children: [
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    Expanded(
                                      child: Text(
                                        c.commodity ?? 'Gabah Panen Pilihan',
                                        style: const TextStyle(
                                          fontSize: 14,
                                          fontWeight: FontWeight.w900,
                                          color: Color(0xFF0F172A),
                                        ),
                                      ),
                                    ),
                                    Text(
                                      _formatCurrency(c.totalAmount),
                                      style: const TextStyle(
                                        fontSize: 14,
                                        fontWeight: FontWeight.w900,
                                        color: Color(0xFF059669),
                                      ),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 6),
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    Text(
                                      'Volume: $qtyStr ${c.unit}  •  Harga: ${_formatCurrency(c.agreedPrice)} / ${c.unit}',
                                      style: const TextStyle(fontSize: 11.5, color: Color(0xFF64748B)),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                          ),

                          const SizedBox(height: 14),

                          // D. Cost Breakdown
                          _buildCostRow('Subtotal Komoditas', _formatCurrency(c.totalAmount)),
                          const SizedBox(height: 6),
                          _buildCostRow('Biaya Layanan Bursa P.A.D.I.', 'Rp 0 (Subsidi Program)', isGreen: true),
                          const SizedBox(height: 6),
                          _buildCostRow('Pajak & Bea Tera Sawah', 'Rp 0 (Bebas Potongan)', isGreen: true),

                          const SizedBox(height: 12),
                          const Divider(height: 1, color: Color(0xFFE2E8F0)),
                          const SizedBox(height: 12),

                          // Total
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              const Text(
                                'TOTAL PEMBAYARAN',
                                style: TextStyle(
                                  fontSize: 13,
                                  fontWeight: FontWeight.w900,
                                  color: Color(0xFF0F172A),
                                ),
                              ),
                              Text(
                                _formatCurrency(c.totalAmount),
                                style: const TextStyle(
                                  fontSize: 18,
                                  fontWeight: FontWeight.w900,
                                  color: Color(0xFF0F5132),
                                  letterSpacing: -0.3,
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),

                    // E. Protection Guarantee Footer
                    Container(
                      padding: const EdgeInsets.all(14),
                      decoration: const BoxDecoration(
                        color: Color(0xFFF0FDF4),
                        borderRadius: BorderRadius.vertical(bottom: Radius.circular(17)),
                      ),
                      child: const Row(
                        children: [
                          Icon(Icons.gavel_rounded, color: Color(0xFF059669), size: 20),
                          SizedBox(width: 10),
                          Expanded(
                            child: Text(
                              'Transaksi ini sah mengikat dan dilindungi UU Metrologi Legal No. 2/1981 dengan verifikasi tera timbangan dan standar kadar air SNI.',
                              style: TextStyle(
                                fontSize: 10.5,
                                color: Color(0xFF047857),
                                height: 1.35,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 20),

              // 2. Action Buttons
              Row(
                children: [
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: _showPrintDialog,
                      style: OutlinedButton.styleFrom(
                        foregroundColor: const Color(0xFF059669),
                        side: const BorderSide(color: Color(0xFF059669), width: 1.5),
                        minimumSize: const Size(0, 48),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                      icon: const Icon(Icons.print_outlined, size: 18),
                      label: const Text(
                        'Cetak / Simpan',
                        style: TextStyle(fontWeight: FontWeight.w800, fontSize: 13),
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: FilledButton.icon(
                      onPressed: _shareToWhatsApp,
                      style: FilledButton.styleFrom(
                        backgroundColor: const Color(0xFF059669),
                        foregroundColor: Colors.white,
                        minimumSize: const Size(0, 48),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                      icon: const Icon(Icons.chat_rounded, size: 18),
                      label: const Text(
                        'Kirim WhatsApp',
                        style: TextStyle(fontWeight: FontWeight.w800, fontSize: 13),
                      ),
                    ),
                  ),
                ],
              ),

              const SizedBox(height: 16),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildCostRow(String label, String value, {bool isGreen = false}) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(
          label,
          style: const TextStyle(fontSize: 12, color: Color(0xFF64748B)),
        ),
        Text(
          value,
          style: TextStyle(
            fontSize: 12,
            fontWeight: FontWeight.w700,
            color: isGreen ? const Color(0xFF059669) : const Color(0xFF0F172A),
          ),
        ),
      ],
    );
  }
}
