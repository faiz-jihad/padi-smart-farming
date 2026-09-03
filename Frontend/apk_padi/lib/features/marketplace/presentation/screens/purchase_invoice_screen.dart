import 'dart:typed_data';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import 'package:pdf/pdf.dart';
import 'package:pdf/widgets.dart' as pw;
import 'package:printing/printing.dart';
import 'package:url_launcher/url_launcher.dart';

import 'package:padi/core/network/api_client.dart';
import 'package:padi/core/providers/app_providers.dart';
import 'package:padi/core/storage/token_storage.dart';
import 'package:padi/features/marketplace/data/models/purchase_contract_model.dart';
import 'package:padi/features/marketplace/data/services/marketplace_api_service.dart';

class PurchaseInvoiceScreen extends ConsumerStatefulWidget {
  const PurchaseInvoiceScreen({
    super.key,
    required this.contractId,
    this.initialContract,
  });

  final int contractId;
  final PurchaseContractModel? initialContract;

  @override
  ConsumerState<PurchaseInvoiceScreen> createState() =>
      _PurchaseInvoiceScreenState();
}

class _PurchaseInvoiceScreenState
    extends ConsumerState<PurchaseInvoiceScreen> {
  late final MarketplaceApiService _service;

  PurchaseContractModel? _contract;

  bool _isLoading = true;
  bool _isPrinting = false;
  bool _isWhatsAppLoading = false;

  String? _error;

  static const Color primary = Color(0xFF059669);
  static const Color primaryDark = Color(0xFF047857);
  static const Color textDark = Color(0xFF0F172A);
  static const Color textMuted = Color(0xFF64748B);
  static const Color background = Color(0xFFF8FAFC);
  static const Color lightGreen = Color(0xFFECFDF5);
  static const Color borderGreen = Color(0xFFA7F3D0);

  @override
  void initState() {
    super.initState();

    _service = MarketplaceApiService(
      ApiClient(
        const SecureTokenStorage(),
      ),
    );

    _loadContract();
  }

  // ============================================================
  // LOAD CONTRACT
  // ============================================================

  Future<void> _loadContract() async {
    if (mounted) {
      setState(() {
        _isLoading = true;
        _error = null;
      });
    }

    try {
      final contract = await _service.getContract(
        widget.contractId,
      );

      if (!mounted) return;

      setState(() {
        _contract = contract;
        _isLoading = false;
      });
    } catch (e) {
      if (!mounted) return;

      setState(() {
        _isLoading = false;
        _error = _cleanError(e);
      });
    }
  }

  String _cleanError(Object error) {
    final message = error.toString();

    if (message.startsWith('Exception: ')) {
      return message.substring(11);
    }

    return message;
  }

  // ============================================================
  // FORMAT
  // ============================================================

  String _formatCurrency(num value) {
    return NumberFormat.currency(
      locale: 'id_ID',
      symbol: 'Rp ',
      decimalDigits: 0,
    ).format(value);
  }

  String _formatNumber(num value) {
    return NumberFormat.decimalPattern(
      'id_ID',
    ).format(value);
  }

  String _formatDate(String? value) {
    if (value == null || value.trim().isEmpty) {
      return DateFormat(
        'dd MMMM yyyy, HH:mm',
        'id_ID',
      ).format(DateTime.now());
    }

    final parsed = DateTime.tryParse(value);

    if (parsed == null) {
      return value;
    }

    return DateFormat(
      'dd MMMM yyyy, HH:mm',
      'id_ID',
    ).format(parsed.toLocal());
  }

  String _invoiceNumber(PurchaseContractModel c) {
    return 'INV/PAD/${c.id.toString().padLeft(6, '0')}';
  }

  // ============================================================
  // WHATSAPP
  // ============================================================

  String _normalizePhone(String? rawPhone) {
    if (rawPhone == null) {
      return '';
    }

    var phone = rawPhone.trim();

    if (phone.isEmpty) {
      return '';
    }

    phone = phone.replaceAll(
      RegExp(r'[^0-9]'),
      '',
    );

    if (phone.startsWith('620')) {
      phone = '62${phone.substring(3)}';
    } else if (phone.startsWith('0')) {
      phone = '62${phone.substring(1)}';
    } else if (phone.startsWith('8')) {
      phone = '62$phone';
    }

    if (!phone.startsWith('62')) {
      return '';
    }

    return phone;
  }

  Future<void> _shareToWhatsApp() async {
    final contract = _contract;

    if (contract == null || _isWhatsAppLoading) {
      return;
    }

    setState(() {
      _isWhatsAppLoading = true;
    });

    try {
      final isBuyer = ref.read(
        isBuyerRoleProvider,
      );

      final String? targetPhone;
      final String targetName;

      if (isBuyer) {
        targetPhone = contract.farmerPhone;
        targetName =
            contract.farmerName ??
            'Petani Mitra P.A.D.I.';
      } else {
        targetPhone = contract.partnerPhone;
        targetName =
            contract.partnerName ??
            'Mitra Pembeli P.A.D.I.';
      }

      final phone = _normalizePhone(
        targetPhone,
      );

      if (phone.isEmpty) {
        if (!mounted) return;

        _showSnackBar(
          isBuyer
              ? 'Nomor WhatsApp petani tidak tersedia.'
              : 'Nomor WhatsApp pembeli tidak tersedia.',
          color: const Color(0xFFDC2626),
        );

        return;
      }

      if (phone.length < 10) {
        if (!mounted) return;

        _showSnackBar(
          'Nomor WhatsApp tidak valid.',
          color: const Color(0xFFDC2626),
        );

        return;
      }

      final invoiceNo = _invoiceNumber(
        contract,
      );

      final quantity = _formatNumber(
        contract.quantity,
      );

      final agreedPrice = _formatCurrency(
        contract.agreedPrice,
      );

      final total = _formatCurrency(
        contract.totalAmount,
      );

      final commodity =
          contract.commodity ??
          'Gabah Padi';

      final unit =
          contract.unit ??
          'kg';

      final message = '''
Halo $targetName,

Saya menghubungi terkait transaksi resmi P.A.D.I.

No. Faktur: $invoiceNo
Tanggal: ${_formatDate(contract.contractedAt)} WIB
Status: ${contract.status.toUpperCase()}

Komoditas: $commodity
Volume: $quantity $unit
Harga Kesepakatan: $agreedPrice / $unit
Total Pembayaran: $total

Transaksi ini telah tercatat sebagai kontrak pembelian resmi melalui P.A.D.I. Smart Farming.

Mohon dapat melanjutkan koordinasi terkait proses penimbangan, pengiriman, dan penyelesaian transaksi.

Terima kasih.
''';

      final encodedMessage =
          Uri.encodeComponent(message);

      final whatsappUri = Uri.parse(
        'whatsapp://send?phone=$phone&text=$encodedMessage',
      );

      final webUri = Uri.parse(
        'https://wa.me/$phone?text=$encodedMessage',
      );

      bool launched = false;

      try {
        launched = await launchUrl(
          whatsappUri,
          mode: LaunchMode.externalApplication,
        );
      } catch (_) {
        launched = false;
      }

      if (!launched) {
        try {
          launched = await launchUrl(
            webUri,
            mode: LaunchMode.externalApplication,
          );
        } catch (_) {
          launched = false;
        }
      }

      if (!launched && mounted) {
        _showSnackBar(
          'WhatsApp tidak dapat dibuka. Pastikan aplikasi WhatsApp terpasang.',
          color: const Color(0xFFDC2626),
        );
      }
    } catch (e) {
      if (!mounted) return;

      _showSnackBar(
        'Gagal membuka WhatsApp: ${_cleanError(e)}',
        color: const Color(0xFFDC2626),
      );
    } finally {
      if (mounted) {
        setState(() {
          _isWhatsAppLoading = false;
        });
      }
    }
  }

  // ============================================================
  // CETAK / SIMPAN PDF
  // ============================================================

  Future<void> _showPrintDialog() async {
    final contract = _contract;

    if (contract == null || _isPrinting) {
      return;
    }

    setState(() {
      _isPrinting = true;
    });

    try {
      final pdfBytes = await _buildPdf(
        contract,
      );

      await Printing.layoutPdf(
        name: '${_invoiceNumber(contract)}.pdf',
        onLayout: (PdfPageFormat format) async {
          return pdfBytes;
        },
      );
    } catch (e) {
      if (!mounted) return;

      _showSnackBar(
        'Gagal membuat PDF: ${_cleanError(e)}',
        color: const Color(0xFFDC2626),
      );
    } finally {
      if (mounted) {
        setState(() {
          _isPrinting = false;
        });
      }
    }
  }

  // ============================================================
  // BUILD PDF
  // ============================================================

  Future<Uint8List> _buildPdf(
    PurchaseContractModel c,
  ) async {
    final document = pw.Document();

    pw.MemoryImage? logo;

    try {
      final logoData = await rootBundle.load(
        'assets/images/padi-logo.png',
      );

      logo = pw.MemoryImage(
        logoData.buffer.asUint8List(),
      );
    } catch (_) {
      logo = null;
    }

    final green = PdfColor.fromHex(
      '#059669',
    );

    final darkGreen = PdfColor.fromHex(
      '#047857',
    );

    final dark = PdfColor.fromHex(
      '#0F172A',
    );

    final muted = PdfColor.fromHex(
      '#64748B',
    );

    final light = PdfColor.fromHex(
      '#ECFDF5',
    );

    final softGrey = PdfColor.fromHex(
      '#F8FAFC',
    );

    final border = PdfColor.fromHex(
      '#A7F3D0',
    );

    document.addPage(
      pw.Page(
        pageFormat: PdfPageFormat.a4,
        margin: const pw.EdgeInsets.all(32),
        build: (context) {
          return pw.Column(
            crossAxisAlignment:
                pw.CrossAxisAlignment.stretch,
            children: [
              pw.Container(
                padding:
                    const pw.EdgeInsets.all(20),
                decoration:
                    pw.BoxDecoration(
                  color: light,
                  borderRadius:
                      pw.BorderRadius.circular(14),
                  border: pw.Border.all(
                    color: border,
                    width: 1,
                  ),
                ),
                child: pw.Row(
                  crossAxisAlignment:
                      pw.CrossAxisAlignment.start,
                  children: [
                    if (logo != null)
                      pw.Container(
                        width: 58,
                        height: 58,
                        margin:
                            const pw.EdgeInsets.only(
                          right: 14,
                        ),
                        child: pw.Image(
                          logo,
                          fit: pw.BoxFit.contain,
                        ),
                      ),
                    pw.Expanded(
                      child: pw.Column(
                        crossAxisAlignment:
                            pw.CrossAxisAlignment.start,
                        children: [
                          pw.Text(
                            'P.A.D.I. SMART FARMING',
                            style: pw.TextStyle(
                              fontSize: 17,
                              fontWeight:
                                  pw.FontWeight.bold,
                              color: darkGreen,
                            ),
                          ),
                          pw.SizedBox(height: 10),
                          pw.Text(
                            'FAKTUR PEMBELIAN RESMI',
                            style: pw.TextStyle(
                              fontSize: 13,
                              fontWeight:
                                  pw.FontWeight.bold,
                              color: dark,
                            ),
                          ),
                          pw.SizedBox(height: 4),
                          pw.Text(
                            _invoiceNumber(c),
                            style: pw.TextStyle(
                              fontSize: 11,
                              fontWeight:
                                  pw.FontWeight.bold,
                              color: green,
                            ),
                          ),
                          pw.SizedBox(height: 3),
                          pw.Text(
                            'Waktu Kontrak: ${_formatDate(c.contractedAt)} WIB',
                            style: pw.TextStyle(
                              fontSize: 9,
                              color: muted,
                            ),
                          ),
                        ],
                      ),
                    ),
                    pw.Container(
                      padding:
                          const pw.EdgeInsets.symmetric(
                        horizontal: 9,
                        vertical: 5,
                      ),
                      decoration:
                          pw.BoxDecoration(
                        color: PdfColors.white,
                        borderRadius:
                            pw.BorderRadius.circular(6),
                        border: pw.Border.all(
                          color: border,
                        ),
                      ),
                      child: pw.Text(
                        c.status.toUpperCase(),
                        style: pw.TextStyle(
                          fontSize: 8,
                          fontWeight:
                              pw.FontWeight.bold,
                          color: darkGreen,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              pw.SizedBox(height: 20),
              pw.Text(
                'PIHAK TRANSAKSI',
                style: pw.TextStyle(
                  fontSize: 10,
                  fontWeight:
                      pw.FontWeight.bold,
                  color: muted,
                ),
              ),
              pw.SizedBox(height: 8),
              pw.Row(
                children: [
                  pw.Expanded(
                    child: _pdfPartyBox(
                      title: 'PIHAK PENJUAL',
                      name:
                          c.farmerName ??
                          'Petani Mitra P.A.D.I.',
                      phone:
                          c.farmerPhone ??
                          '-',
                      green: green,
                      dark: dark,
                      muted: muted,
                      softGrey: softGrey,
                    ),
                  ),
                  pw.SizedBox(width: 12),
                  pw.Expanded(
                    child: _pdfPartyBox(
                      title: 'PIHAK PEMBELI',
                      name:
                          c.partnerName ??
                          'Mitra Pembeli P.A.D.I.',
                      phone:
                          c.partnerPhone ??
                          '-',
                      green: green,
                      dark: dark,
                      muted: muted,
                      softGrey: softGrey,
                    ),
                  ),
                ],
              ),
              pw.SizedBox(height: 20),
              pw.Text(
                'RINCIAN TRANSAKSI KOMODITAS',
                style: pw.TextStyle(
                  fontSize: 10,
                  fontWeight:
                      pw.FontWeight.bold,
                  color: muted,
                ),
              ),
              pw.SizedBox(height: 8),
              pw.Container(
                padding:
                    const pw.EdgeInsets.all(14),
                decoration:
                    pw.BoxDecoration(
                  color: softGrey,
                  borderRadius:
                      pw.BorderRadius.circular(10),
                  border: pw.Border.all(
                    color: PdfColor.fromHex(
                      '#E2E8F0',
                    ),
                  ),
                ),
                child: pw.Column(
                  children: [
                    pw.Row(
                      mainAxisAlignment:
                          pw.MainAxisAlignment
                              .spaceBetween,
                      children: [
                        pw.Expanded(
                          child: pw.Text(
                            c.commodity ??
                                'Gabah Padi',
                            style:
                                pw.TextStyle(
                              fontSize: 13,
                              fontWeight:
                                  pw.FontWeight.bold,
                              color: dark,
                            ),
                          ),
                        ),
                        pw.Text(
                          _formatCurrency(
                            c.totalAmount,
                          ),
                          style:
                              pw.TextStyle(
                            fontSize: 13,
                            fontWeight:
                                pw.FontWeight.bold,
                            color: green,
                          ),
                        ),
                      ],
                    ),
                    pw.SizedBox(height: 8),
                    pw.Row(
                      children: [
                        pw.Text(
                          'Volume: ',
                          style:
                              pw.TextStyle(
                            fontSize: 9,
                            color: muted,
                          ),
                        ),
                        pw.Text(
                          '${_formatNumber(c.quantity)} ${c.unit ?? 'kg'}',
                          style:
                              pw.TextStyle(
                            fontSize: 9,
                            fontWeight:
                                pw.FontWeight.bold,
                            color: dark,
                          ),
                        ),
                        pw.SizedBox(width: 12),
                        pw.Text(
                          'Harga: ',
                          style:
                              pw.TextStyle(
                            fontSize: 9,
                            color: muted,
                          ),
                        ),
                        pw.Text(
                          '${_formatCurrency(c.agreedPrice)} / ${c.unit ?? 'kg'}',
                          style:
                              pw.TextStyle(
                            fontSize: 9,
                            fontWeight:
                                pw.FontWeight.bold,
                            color: dark,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
              pw.SizedBox(height: 14),
              _pdfTotalRow(
                'Subtotal Komoditas',
                _formatCurrency(
                  c.totalAmount,
                ),
                dark: dark,
                muted: muted,
                green: green,
              ),
              pw.SizedBox(height: 7),
              _pdfTotalRow(
                'Biaya Layanan Bursa P.A.D.I.',
                'Rp 0 (Subsidi Program)',
                dark: dark,
                muted: muted,
                green: green,
              ),
              pw.SizedBox(height: 7),
              _pdfTotalRow(
                'Pajak & Bea Tera Sawah',
                'Rp 0 (Bebas Potongan)',
                dark: dark,
                muted: muted,
                green: green,
              ),
              pw.SizedBox(height: 12),
              pw.Divider(
                color: PdfColor.fromHex(
                  '#E2E8F0',
                ),
              ),
              pw.SizedBox(height: 10),
              pw.Row(
                mainAxisAlignment:
                    pw.MainAxisAlignment
                        .spaceBetween,
                children: [
                  pw.Text(
                    'TOTAL PEMBAYARAN',
                    style: pw.TextStyle(
                      fontSize: 13,
                      fontWeight:
                          pw.FontWeight.bold,
                      color: dark,
                    ),
                  ),
                  pw.Text(
                    _formatCurrency(
                      c.totalAmount,
                    ),
                    style: pw.TextStyle(
                      fontSize: 18,
                      fontWeight:
                          pw.FontWeight.bold,
                      color: green,
                    ),
                  ),
                ],
              ),
              pw.SizedBox(height: 20),
              pw.Container(
                padding:
                    const pw.EdgeInsets.all(14),
                decoration:
                    pw.BoxDecoration(
                  color: light,
                  borderRadius:
                      pw.BorderRadius.circular(10),
                  border: pw.Border.all(
                    color: border,
                  ),
                ),
                child: pw.Row(
                  crossAxisAlignment:
                      pw.CrossAxisAlignment.start,
                  children: [
                    pw.Container(
                      width: 24,
                      height: 24,
                      alignment:
                          pw.Alignment.center,
                      decoration:
                          pw.BoxDecoration(
                        color: green,
                        borderRadius:
                            pw.BorderRadius.circular(5),
                      ),
                      child: pw.Text(
                        '✓',
                        style: pw.TextStyle(
                          fontSize: 12,
                          fontWeight:
                              pw.FontWeight.bold,
                          color: PdfColors.white,
                        ),
                      ),
                    ),
                    pw.SizedBox(width: 10),
                    pw.Expanded(
                      child: pw.Text(
                        'Transaksi ini sah mengikat dan dilindungi UU Metrologi Legal No. 2/1981 dengan verifikasi tera timbangan dan standar kadar air SNI.',
                        style: pw.TextStyle(
                          fontSize: 8.5,
                          color: darkGreen,
                          lineSpacing: 2,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
              pw.Spacer(),
              pw.Divider(
                color: PdfColor.fromHex(
                  '#E2E8F0',
                ),
              ),
              pw.SizedBox(height: 6),
              pw.Center(
                child: pw.Text(
                  'Dokumen elektronik resmi P.A.D.I. Smart Farming',
                  style: pw.TextStyle(
                    fontSize: 8,
                    color: muted,
                  ),
                ),
              ),
            ],
          );
        },
      ),
    );

    return document.save();
  }

  // ============================================================
  // PDF PARTY BOX
  // ============================================================

  pw.Widget _pdfPartyBox({
    required String title,
    required String name,
    required String phone,
    required PdfColor green,
    required PdfColor dark,
    required PdfColor muted,
    required PdfColor softGrey,
  }) {
    return pw.Container(
      padding:
          const pw.EdgeInsets.all(12),
      decoration:
          pw.BoxDecoration(
        color: softGrey,
        borderRadius:
            pw.BorderRadius.circular(9),
      ),
      child: pw.Column(
        crossAxisAlignment:
            pw.CrossAxisAlignment.start,
        children: [
          pw.Text(
            title,
            style: pw.TextStyle(
              fontSize: 8,
              fontWeight:
                  pw.FontWeight.bold,
              color: muted,
            ),
          ),
          pw.SizedBox(height: 5),
          pw.Text(
            name,
            maxLines: 2,
            style: pw.TextStyle(
              fontSize: 11,
              fontWeight:
                  pw.FontWeight.bold,
              color: dark,
            ),
          ),
          pw.SizedBox(height: 4),
          pw.Text(
            phone,
            style: pw.TextStyle(
              fontSize: 8,
              color: muted,
            ),
          ),
          pw.SizedBox(height: 4),
          pw.Text(
            'Terverifikasi P.A.D.I.',
            style: pw.TextStyle(
              fontSize: 8,
              fontWeight:
                  pw.FontWeight.bold,
              color: green,
            ),
          ),
        ],
      ),
    );
  }

  // ============================================================
  // PDF TOTAL ROW
  // ============================================================

  pw.Widget _pdfTotalRow(
    String label,
    String value, {
    required PdfColor dark,
    required PdfColor muted,
    required PdfColor green,
  }) {
    return pw.Row(
      mainAxisAlignment:
          pw.MainAxisAlignment.spaceBetween,
      children: [
        pw.Expanded(
          child: pw.Text(
            label,
            style: pw.TextStyle(
              fontSize: 9,
              color: muted,
            ),
          ),
        ),
        pw.SizedBox(width: 10),
        pw.Text(
          value,
          textAlign: pw.TextAlign.right,
          style: pw.TextStyle(
            fontSize: 9,
            fontWeight:
                pw.FontWeight.bold,
            color: green,
          ),
        ),
      ],
    );
  }

  // ============================================================
  // SNACKBAR
  // ============================================================

  void _showSnackBar(
    String message, {
    Color color = primary,
  }) {
    if (!mounted) return;

    ScaffoldMessenger.of(context)
      ..hideCurrentSnackBar()
      ..showSnackBar(
        SnackBar(
          content: Text(
            message,
            style: const TextStyle(
              color: Colors.white,
              fontWeight:
                  FontWeight.w600,
            ),
          ),
          backgroundColor: color,
          behavior:
              SnackBarBehavior.floating,
          shape:
              RoundedRectangleBorder(
            borderRadius:
                BorderRadius.circular(10),
          ),
        ),
      );
  }

  // ============================================================
  // BUILD
  // ============================================================

  @override
  Widget build(BuildContext context) {
    final contract = _contract;

    return Scaffold(
      backgroundColor: background,
      appBar: AppBar(
        backgroundColor: Colors.white,
        foregroundColor: textDark,
        elevation: 0,
        scrolledUnderElevation: 1,
        leading: IconButton(
          tooltip: 'Kembali',
          icon: const Icon(
            Icons.arrow_back_ios_new_rounded,
            size: 18,
            color: textDark,
          ),
          onPressed: () {
            if (context.canPop()) {
              context.pop();
            } else {
              context.go('/buyer/orders');
            }
          },
        ),
        title: const Text(
          'Faktur Pembelian Resmi',
          style: TextStyle(
            fontSize: 17,
            fontWeight:
                FontWeight.w900,
            color: textDark,
          ),
        ),
        actions: [
          IconButton(
            tooltip: 'Bagikan ke WhatsApp',
            icon: _isWhatsAppLoading
                ? const SizedBox(
                    width: 18,
                    height: 18,
                    child:
                        CircularProgressIndicator(
                      strokeWidth: 2,
                      color: primary,
                    ),
                  )
                : const Icon(
                    Icons.share_outlined,
                    color: primary,
                  ),
            onPressed:
                contract == null ||
                        _isWhatsAppLoading
                    ? null
                    : _shareToWhatsApp,
          ),
        ],
      ),
      body: _buildBody(contract),
    );
  }

  // ============================================================
  // BODY
  // ============================================================

  Widget _buildBody(
    PurchaseContractModel? contract,
  ) {
    if (_isLoading) {
      return const Center(
        child: CircularProgressIndicator(
          color: primary,
        ),
      );
    }

    if (_error != null) {
      return Center(
        child: Padding(
          padding:
              const EdgeInsets.all(24),
          child: Column(
            mainAxisAlignment:
                MainAxisAlignment.center,
            children: [
              const Icon(
                Icons.error_outline_rounded,
                size: 48,
                color: Color(0xFFDC2626),
              ),
              const SizedBox(height: 12),
              Text(
                _error!,
                textAlign: TextAlign.center,
                style: const TextStyle(
                  color: textDark,
                ),
              ),
              const SizedBox(height: 16),
              FilledButton(
                onPressed: _loadContract,
                style:
                    FilledButton.styleFrom(
                  backgroundColor: primary,
                ),
                child:
                    const Text('Coba Lagi'),
              ),
            ],
          ),
        ),
      );
    }

    if (contract == null) {
      return const Center(
        child: Text(
          'Faktur tidak ditemukan.',
        ),
      );
    }

    return _buildInvoiceContent(
      contract,
    );
  }

  // ============================================================
  // INVOICE CONTENT
  // ============================================================

  Widget _buildInvoiceContent(
    PurchaseContractModel c,
  ) {
    final invoiceNo = _invoiceNumber(c);

    return SafeArea(
      child: Center(
        child: ConstrainedBox(
          constraints:
              const BoxConstraints(
            maxWidth: 540,
          ),
          child: ListView(
            padding:
                const EdgeInsets.symmetric(
              horizontal: 18,
              vertical: 16,
            ),
            physics:
                const BouncingScrollPhysics(),
            children: [
              Container(
                decoration:
                    BoxDecoration(
                  color: Colors.white,
                  borderRadius:
                      BorderRadius.circular(18),
                  border: Border.all(
                    color: borderGreen,
                  ),
                  boxShadow: [
                    BoxShadow(
                      color:
                          primary.withOpacity(
                        0.06,
                      ),
                      blurRadius: 16,
                      offset:
                          const Offset(0, 4),
                    ),
                  ],
                ),
                child: Column(
                  crossAxisAlignment:
                      CrossAxisAlignment.start,
                  children: [
                    Container(
                      padding:
                          const EdgeInsets.all(18),
                      decoration:
                          const BoxDecoration(
                        color: lightGreen,
                        borderRadius:
                            BorderRadius.vertical(
                          top:
                              Radius.circular(17),
                        ),
                      ),
                      child: Column(
                        crossAxisAlignment:
                            CrossAxisAlignment.start,
                        children: [
                          Row(
                            mainAxisAlignment:
                                MainAxisAlignment
                                    .spaceBetween,
                            children: [
                              Row(
                                children: [
                                  Container(
                                    padding:
                                        const EdgeInsets.all(
                                      6,
                                    ),
                                    decoration:
                                        BoxDecoration(
                                      color: primary,
                                      borderRadius:
                                          BorderRadius
                                              .circular(
                                        8,
                                      ),
                                    ),
                                    child:
                                        const Icon(
                                      Icons
                                          .verified_rounded,
                                      color:
                                          Colors.white,
                                      size: 16,
                                    ),
                                  ),
                                  const SizedBox(
                                    width: 8,
                                  ),
                                  const Text(
                                    'P.A.D.I. SMART FARMING',
                                    style:
                                        TextStyle(
                                      fontSize: 12,
                                      fontWeight:
                                          FontWeight.w900,
                                      color:
                                          primaryDark,
                                      letterSpacing:
                                          0.5,
                                    ),
                                  ),
                                ],
                              ),
                              Container(
                                padding:
                                    const EdgeInsets
                                        .symmetric(
                                  horizontal: 8,
                                  vertical: 3,
                                ),
                                decoration:
                                    BoxDecoration(
                                  color:
                                      Colors.white,
                                  borderRadius:
                                      BorderRadius
                                          .circular(
                                    6,
                                  ),
                                  border:
                                      Border.all(
                                    color:
                                        borderGreen,
                                  ),
                                ),
                                child: Text(
                                  c.status
                                      .toUpperCase(),
                                  style:
                                      const TextStyle(
                                    fontSize: 10,
                                    fontWeight:
                                        FontWeight.w900,
                                    color:
                                        primaryDark,
                                  ),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 12),
                          Text(
                            invoiceNo,
                            style:
                                const TextStyle(
                              fontSize: 18,
                              fontWeight:
                                  FontWeight.w900,
                              color: textDark,
                              letterSpacing:
                                  -0.3,
                            ),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            'Waktu Kontrak: ${_formatDate(c.contractedAt)} WIB',
                            style:
                                const TextStyle(
                              fontSize: 11.5,
                              color: textMuted,
                            ),
                          ),
                        ],
                      ),
                    ),
                    const Divider(
                      height: 1,
                      color:
                          Color(0xFFE2E8F0),
                    ),
                    Padding(
                      padding:
                          const EdgeInsets.all(
                        18,
                      ),
                      child: Column(
                        crossAxisAlignment:
                            CrossAxisAlignment
                                .start,
                        children: [
                          Row(
                            crossAxisAlignment:
                                CrossAxisAlignment
                                    .start,
                            children: [
                              Expanded(
                                child: Column(
                                  crossAxisAlignment:
                                      CrossAxisAlignment
                                          .start,
                                  children: [
                                    const Text(
                                      'PIHAK PENJUAL',
                                      style:
                                          TextStyle(
                                        fontSize: 10,
                                        fontWeight:
                                            FontWeight.w800,
                                        color:
                                            textMuted,
                                        letterSpacing:
                                            0.4,
                                      ),
                                    ),
                                    const SizedBox(
                                      height: 4,
                                    ),
                                    Text(
                                      c.farmerName ??
                                          'Petani Mitra P.A.D.I.',
                                      style:
                                          const TextStyle(
                                        fontSize: 13.5,
                                        fontWeight:
                                            FontWeight.w900,
                                        color:
                                            textDark,
                                      ),
                                    ),
                                    const SizedBox(
                                      height: 2,
                                    ),
                                    Text(
                                      c.farmerPhone ??
                                          '-',
                                      style:
                                          const TextStyle(
                                        fontSize: 11.5,
                                        color:
                                            textMuted,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                              Container(
                                height: 40,
                                width: 1,
                                color:
                                    const Color(
                                  0xFFE2E8F0,
                                ),
                              ),
                              const SizedBox(
                                width: 14,
                              ),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment:
                                      CrossAxisAlignment
                                          .start,
                                  children: [
                                    const Text(
                                      'PIHAK PEMBELI',
                                      style:
                                          TextStyle(
                                        fontSize: 10,
                                        fontWeight:
                                            FontWeight.w800,
                                        color:
                                            textMuted,
                                        letterSpacing:
                                            0.4,
                                      ),
                                    ),
                                    const SizedBox(
                                      height: 4,
                                    ),
                                    Text(
                                      c.partnerName ??
                                          'Mitra Pembeli P.A.D.I.',
                                      style:
                                          const TextStyle(
                                        fontSize: 13.5,
                                        fontWeight:
                                            FontWeight.w900,
                                        color:
                                            textDark,
                                      ),
                                    ),
                                    const SizedBox(
                                      height: 2,
                                    ),
                                    const Text(
                                      'Akun Terverifikasi B2B',
                                      style:
                                          TextStyle(
                                        fontSize: 11.5,
                                        color:
                                            primary,
                                        fontWeight:
                                            FontWeight.w700,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(
                            height: 18,
                          ),
                          const Divider(
                            height: 1,
                            color:
                                Color(0xFFF1F5F9),
                          ),
                          const SizedBox(
                            height: 14,
                          ),
                          const Text(
                            'RINCIAN TRANSAKSI KOMODITAS',
                            style:
                                TextStyle(
                              fontSize: 10.5,
                              fontWeight:
                                  FontWeight.w800,
                              color:
                                  textMuted,
                              letterSpacing:
                                  0.4,
                            ),
                          ),
                          const SizedBox(
                            height: 10,
                          ),
                          Container(
                            padding:
                                const EdgeInsets.all(
                              12,
                            ),
                            decoration:
                                BoxDecoration(
                              color:
                                  const Color(
                                0xFFF8FAFC,
                              ),
                              borderRadius:
                                  BorderRadius
                                      .circular(
                                10,
                              ),
                              border:
                                  Border.all(
                                color:
                                    const Color(
                                  0xFFE2E8F0,
                                ),
                              ),
                            ),
                            child: Column(
                              children: [
                                Row(
                                  mainAxisAlignment:
                                      MainAxisAlignment
                                          .spaceBetween,
                                  children: [
                                    Expanded(
                                      child: Text(
                                        c.commodity ??
                                            'Gabah Padi',
                                        style:
                                            const TextStyle(
                                          fontSize: 14,
                                          fontWeight:
                                              FontWeight.w900,
                                          color:
                                              textDark,
                                        ),
                                      ),
                                    ),
                                    Text(
                                      _formatCurrency(
                                        c.totalAmount,
                                      ),
                                      style:
                                          const TextStyle(
                                        fontSize: 14,
                                        fontWeight:
                                            FontWeight.w900,
                                        color:
                                            primary,
                                      ),
                                    ),
                                  ],
                                ),
                                const SizedBox(
                                  height: 6,
                                ),
                                Row(
                                  children: [
                                    Text(
                                      'Volume: ${_formatNumber(c.quantity)} ${c.unit ?? 'kg'}',
                                      style:
                                          const TextStyle(
                                        fontSize: 11,
                                        color:
                                            textMuted,
                                      ),
                                    ),
                                    const SizedBox(
                                      width: 5,
                                    ),
                                    const Text(
                                      '•',
                                      style:
                                          TextStyle(
                                        color:
                                            textMuted,
                                      ),
                                    ),
                                    const SizedBox(
                                      width: 5,
                                    ),
                                    Expanded(
                                      child: Text(
                                        'Harga: ${_formatCurrency(c.agreedPrice)} / ${c.unit ?? 'kg'}',
                                        style:
                                            const TextStyle(
                                          fontSize: 11,
                                          color:
                                              textMuted,
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(
                            height: 14,
                          ),
                          _buildCostRow(
                            'Subtotal Komoditas',
                            _formatCurrency(
                              c.totalAmount,
                            ),
                          ),
                          const SizedBox(
                            height: 7,
                          ),
                          _buildCostRow(
                            'Biaya Layanan Bursa P.A.D.I.',
                            'Rp 0 (Subsidi Program)',
                            isGreen: true,
                          ),
                          const SizedBox(
                            height: 7,
                          ),
                          _buildCostRow(
                            'Pajak & Bea Tera Sawah',
                            'Rp 0 (Bebas Potongan)',
                            isGreen: true,
                          ),
                          const SizedBox(
                            height: 12,
                          ),
                          const Divider(
                            height: 1,
                            color:
                                Color(0xFFE2E8F0),
                          ),
                          const SizedBox(
                            height: 14,
                          ),
                          Row(
                            mainAxisAlignment:
                                MainAxisAlignment
                                    .spaceBetween,
                            children: [
                              const Text(
                                'TOTAL PEMBAYARAN',
                                style:
                                    TextStyle(
                                  fontSize: 13,
                                  fontWeight:
                                      FontWeight.w900,
                                  color:
                                      textDark,
                                ),
                              ),
                              Text(
                                _formatCurrency(
                                  c.totalAmount,
                                ),
                                style:
                                    const TextStyle(
                                  fontSize: 20,
                                  fontWeight:
                                      FontWeight.w900,
                                  color:
                                      primaryDark,
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                    Container(
                      padding:
                          const EdgeInsets.all(
                        14,
                      ),
                      decoration:
                          const BoxDecoration(
                        color: lightGreen,
                        borderRadius:
                            BorderRadius.vertical(
                          bottom:
                              Radius.circular(
                            17,
                          ),
                        ),
                      ),
                      child: const Row(
                        crossAxisAlignment:
                            CrossAxisAlignment
                                .start,
                        children: [
                          Icon(
                            Icons.gavel_rounded,
                            color: primary,
                            size: 20,
                          ),
                          SizedBox(
                            width: 10,
                          ),
                          Expanded(
                            child: Text(
                              'Transaksi ini sah mengikat dan dilindungi UU Metrologi Legal No. 2/1981 dengan verifikasi tera timbangan dan standar kadar air SNI.',
                              style:
                                  TextStyle(
                                fontSize: 10.5,
                                color:
                                    primaryDark,
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
              const SizedBox(
                height: 20,
              ),
              Row(
                children: [
                  Expanded(
                    child:
                        OutlinedButton.icon(
                      onPressed:
                          _isPrinting
                              ? null
                              : _showPrintDialog,
                      style:
                          OutlinedButton.styleFrom(
                        foregroundColor:
                            primary,
                        side:
                            const BorderSide(
                          color: primary,
                          width: 1.5,
                        ),
                        minimumSize:
                            const Size(
                          0,
                          48,
                        ),
                        shape:
                            RoundedRectangleBorder(
                          borderRadius:
                              BorderRadius
                                  .circular(
                            12,
                          ),
                        ),
                      ),
                      icon: _isPrinting
                          ? const SizedBox(
                              width: 18,
                              height: 18,
                              child:
                                  CircularProgressIndicator(
                                strokeWidth:
                                    2,
                                color:
                                    primary,
                              ),
                            )
                          : const Icon(
                              Icons
                                  .print_outlined,
                              size: 18,
                            ),
                      label: Text(
                        _isPrinting
                            ? 'Membuat PDF...'
                            : 'Cetak / Simpan',
                        style:
                            const TextStyle(
                          fontWeight:
                              FontWeight.w800,
                          fontSize: 13,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(
                    width: 12,
                  ),
                  Expanded(
                    child:
                        FilledButton.icon(
                      onPressed:
                          _isWhatsAppLoading
                              ? null
                              : _shareToWhatsApp,
                      style:
                          FilledButton.styleFrom(
                        backgroundColor:
                            primary,
                        foregroundColor:
                            Colors.white,
                        minimumSize:
                            const Size(
                          0,
                          48,
                        ),
                        shape:
                            RoundedRectangleBorder(
                          borderRadius:
                              BorderRadius
                                  .circular(
                            12,
                          ),
                        ),
                      ),
                      icon:
                          _isWhatsAppLoading
                              ? const SizedBox(
                                  width: 18,
                                  height: 18,
                                  child:
                                      CircularProgressIndicator(
                                    strokeWidth:
                                        2,
                                    color:
                                        Colors.white,
                                  ),
                                )
                              : const Icon(
                                  Icons
                                      .chat_rounded,
                                  size: 18,
                                ),
                      label: Text(
                        _isWhatsAppLoading
                            ? 'Membuka...'
                            : 'Kirim WhatsApp',
                        style:
                            const TextStyle(
                          fontWeight:
                              FontWeight.w800,
                          fontSize: 13,
                        ),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(
                height: 20,
              ),
            ],
          ),
        ),
      ),
    );
  }

  // ============================================================
  // COST ROW
  // ============================================================

  Widget _buildCostRow(
    String label,
    String value, {
    bool isGreen = false,
  }) {
    return Row(
      mainAxisAlignment:
          MainAxisAlignment.spaceBetween,
      children: [
        Expanded(
          child: Text(
            label,
            style:
                const TextStyle(
              fontSize: 12,
              color: textMuted,
            ),
          ),
        ),
        const SizedBox(
          width: 10,
        ),
        Text(
          value,
          textAlign: TextAlign.right,
          style: TextStyle(
            fontSize: 12,
            fontWeight:
                FontWeight.w700,
            color: isGreen
                ? primary
                : textDark,
          ),
        ),
      ],
    );
  }
}