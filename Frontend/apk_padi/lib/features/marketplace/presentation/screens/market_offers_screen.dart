import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import 'package:url_launcher/url_launcher.dart';

import 'package:padi/core/network/api_client.dart';
import 'package:padi/core/storage/token_storage.dart';
import 'package:padi/features/marketplace/data/models/market_offer_model.dart';
import 'package:padi/features/marketplace/data/services/marketplace_api_service.dart';
import 'package:padi/features/notifications/presentation/providers/notifications_provider.dart';

class MarketOffersScreen extends ConsumerStatefulWidget {
  const MarketOffersScreen({super.key, this.listingId});

  final int? listingId;

  @override
  ConsumerState<MarketOffersScreen> createState() => _MarketOffersScreenState();
}

class _MarketOffersScreenState extends ConsumerState<MarketOffersScreen> {
  late final MarketplaceApiService _service;

  List<MarketOfferModel> _offers = [];
  bool _isLoading = true;
  String? _error;
  int? _processingOfferId;

  @override
  void initState() {
    super.initState();
    _service = MarketplaceApiService(ApiClient(const SecureTokenStorage()));
    _loadOffers();
  }

  Future<void> _loadOffers() async {
    if (mounted) {
      setState(() {
        _isLoading = true;
        _error = null;
      });
    }

    try {
      final listingId = widget.listingId;
      final offers = listingId == null
          ? await _service.fetchMyOffers()
          : await _service.fetchListingOffers(listingId);

      if (!mounted) return;

      setState(() {
        _offers = offers;
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

  String _formatNumber(num value) {
    return NumberFormat.decimalPattern('id_ID').format(value);
  }

  String _formatCurrency(num value) {
    return NumberFormat.currency(
      locale: 'id_ID',
      symbol: 'Rp ',
      decimalDigits: 0,
    ).format(value);
  }

  Future<void> _updateStatus(
    MarketOfferModel offer,
    String status, {
    double? counterPrice,
    double? counterQuantity,
    String? counterNotes,
  }) async {
    if (_processingOfferId != null) return;

    setState(() => _processingOfferId = offer.id);

    try {
      final result = await _service.updateOfferStatus(
        offerId: offer.id,
        status: status,
        counterPrice: counterPrice,
        counterQuantity: counterQuantity,
        counterNotes: counterNotes,
      );

      if (!mounted) return;

      // Refresh notifications so badge updates immediately
      ref.read(notificationsProvider.notifier).refresh();

      await _loadOffers();

      if (!mounted) return;

      if (status == 'accepted') {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: const Row(
              children: [
                Icon(Icons.check_circle_rounded, color: Colors.white, size: 18),
                SizedBox(width: 8),
                Text('Penawaran diterima! Kontrak resmi terbentuk.'),
              ],
            ),
            backgroundColor: const Color(0xFF059669),
            behavior: SnackBarBehavior.floating,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
          ),
        );
        await Future.delayed(const Duration(milliseconds: 500));
        if (mounted) {
          _openWhatsApp(result);
        }
      } else if (status == 'countered') {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: const Row(
              children: [
                Icon(Icons.handshake_rounded, color: Colors.white, size: 18),
                SizedBox(width: 8),
                Text('Tawaran balik berhasil dikirim ke pembeli!'),
              ],
            ),
            backgroundColor: const Color(0xFF059669),
            behavior: SnackBarBehavior.floating,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
          ),
        );
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Penawaran berhasil ditolak.'),
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    } catch (e) {
      if (!mounted) return;

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(e.toString().replaceFirst('Exception: ', '')),
          backgroundColor: const Color(0xFFDC2626),
          behavior: SnackBarBehavior.floating,
        ),
      );
    } finally {
      if (mounted) {
        setState(() => _processingOfferId = null);
      }
    }
  }

  Future<void> _confirmAction(MarketOfferModel offer, String status) async {
    final isAccept = status == 'accepted';

    final result = await showDialog<bool>(
      context: context,
      builder: (dialogContext) {
        return AlertDialog(
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          title: Text(
            isAccept ? 'Terima Penawaran?' : 'Tolak Penawaran?',
            style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 16),
          ),
          content: Text(
            isAccept
                ? 'Apakah Anda yakin ingin menerima penawaran dari ${offer.partnerName ?? 'Pembeli'}? Kontrak pembelian resmi dan faktur akan langsung diterbitkan.'
                : 'Apakah Anda yakin ingin menolak penawaran ini?',
            style: const TextStyle(fontSize: 13, color: Color(0xFF475569)),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(dialogContext).pop(false),
              child: const Text('Batal'),
            ),
            FilledButton(
              onPressed: () => Navigator.of(dialogContext).pop(true),
              style: FilledButton.styleFrom(
                backgroundColor: isAccept ? const Color(0xFF059669) : const Color(0xFFDC2626),
              ),
              child: Text(isAccept ? 'Ya, Terima' : 'Ya, Tolak'),
            ),
          ],
        );
      },
    );

    if (result != true) return;

    await _updateStatus(offer, status);
  }

  void _showCounterOfferModal(MarketOfferModel offer) {
    final priceController = TextEditingController(
      text: offer.offeredPrice.round().toString(),
    );
    final quantityController = TextEditingController(
      text: offer.quantity.round().toString(),
    );
    final notesController = TextEditingController();

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setModalState) {
          final p = double.tryParse(priceController.text.replaceAll(',', '.')) ?? offer.offeredPrice;
          final q = double.tryParse(quantityController.text.replaceAll(',', '.')) ?? offer.quantity;
          final totalEst = p * q;

          return Container(
            padding: EdgeInsets.fromLTRB(
              20,
              18,
              20,
              MediaQuery.of(context).viewInsets.bottom + 20,
            ),
            decoration: const BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.vertical(top: Radius.circular(22)),
            ),
            child: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Handle bar
                  Center(
                    child: Container(
                      width: 38,
                      height: 4,
                      decoration: BoxDecoration(
                        color: const Color(0xFFCBD5E1),
                        borderRadius: BorderRadius.circular(4),
                      ),
                    ),
                  ),
                  const SizedBox(height: 14),

                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text(
                            'Nego Ulang Penawaran',
                            style: TextStyle(
                              fontSize: 17,
                              fontWeight: FontWeight.w900,
                              color: Color(0xFF0F172A),
                            ),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            'Mitra Pembeli: ${offer.partnerName ?? 'Mitra B2B'}',
                            style: const TextStyle(fontSize: 12, color: Color(0xFF64748B)),
                          ),
                        ],
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                        decoration: BoxDecoration(
                          color: const Color(0xFFECFDF5),
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: const Text(
                          'FITUR NEGO',
                          style: TextStyle(
                            fontSize: 10,
                            fontWeight: FontWeight.w900,
                            color: Color(0xFF047857),
                          ),
                        ),
                      ),
                    ],
                  ),

                  const SizedBox(height: 14),

                  // Tawaran awal buyer
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF8FAFC),
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(color: const Color(0xFFE2E8F0)),
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text(
                          'Tawaran Masuk Awal:',
                          style: TextStyle(fontSize: 12, color: Color(0xFF64748B)),
                        ),
                        Text(
                          'Rp ${_formatNumber(offer.offeredPrice)} / ${offer.unit ?? 'kg'} (${_formatNumber(offer.quantity)} ${offer.unit ?? 'kg'})',
                          style: const TextStyle(
                            fontSize: 12.5,
                            fontWeight: FontWeight.w800,
                            color: Color(0xFF0F172A),
                          ),
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 16),

                  // 1. Input Harga Tawar Balik
                  const Text(
                    'Harga Tawar Balik Petani (per kg)',
                    style: TextStyle(fontSize: 13, fontWeight: FontWeight.w800, color: Color(0xFF0F172A)),
                  ),
                  const SizedBox(height: 6),
                  TextFormField(
                    controller: priceController,
                    keyboardType: TextInputType.number,
                    onChanged: (_) => setModalState(() {}),
                    style: const TextStyle(fontSize: 14.5, fontWeight: FontWeight.w800),
                    decoration: InputDecoration(
                      prefixIcon: const Padding(
                        padding: EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                        child: Text(
                          'Rp',
                          style: TextStyle(fontSize: 14, fontWeight: FontWeight.w900, color: Color(0xFF059669)),
                        ),
                      ),
                      prefixIconConstraints: const BoxConstraints(minWidth: 0, minHeight: 0),
                      suffixText: '/ ${offer.unit ?? 'kg'}',
                      filled: true,
                      fillColor: const Color(0xFFF8FAFC),
                      contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                        borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
                      ),
                      focusedBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                        borderSide: const BorderSide(color: Color(0xFF059669), width: 1.6),
                      ),
                    ),
                  ),

                  const SizedBox(height: 6),

                  // Quick price chips
                  Wrap(
                    spacing: 6,
                    children: [
                      _buildPresetChip(
                        '+ Rp 200',
                        () {
                          final cur = double.tryParse(priceController.text) ?? offer.offeredPrice;
                          priceController.text = (cur + 200).round().toString();
                          setModalState(() {});
                        },
                      ),
                      _buildPresetChip(
                        '+ Rp 500',
                        () {
                          final cur = double.tryParse(priceController.text) ?? offer.offeredPrice;
                          priceController.text = (cur + 500).round().toString();
                          setModalState(() {});
                        },
                      ),
                      _buildPresetChip(
                        '+ Rp 1.000',
                        () {
                          final cur = double.tryParse(priceController.text) ?? offer.offeredPrice;
                          priceController.text = (cur + 1000).round().toString();
                          setModalState(() {});
                        },
                      ),
                    ],
                  ),

                  const SizedBox(height: 14),

                  // 2. Input Kuantitas yang Disanggupi
                  const Text(
                    'Kuantitas yang Disanggupi Petani',
                    style: TextStyle(fontSize: 13, fontWeight: FontWeight.w800, color: Color(0xFF0F172A)),
                  ),
                  const SizedBox(height: 6),
                  TextFormField(
                    controller: quantityController,
                    keyboardType: TextInputType.number,
                    onChanged: (_) => setModalState(() {}),
                    style: const TextStyle(fontSize: 14.5, fontWeight: FontWeight.w800),
                    decoration: InputDecoration(
                      suffixText: '${offer.unit ?? 'kg'}  ',
                      filled: true,
                      fillColor: const Color(0xFFF8FAFC),
                      contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                        borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
                      ),
                      focusedBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                        borderSide: const BorderSide(color: Color(0xFF059669), width: 1.6),
                      ),
                    ),
                  ),

                  const SizedBox(height: 14),

                  // 3. Catatan Nego
                  const Text(
                    'Catatan / Alasan Nego (Opsional)',
                    style: TextStyle(fontSize: 13, fontWeight: FontWeight.w800, color: Color(0xFF0F172A)),
                  ),
                  const SizedBox(height: 6),
                  TextFormField(
                    controller: notesController,
                    maxLines: 2,
                    style: const TextStyle(fontSize: 13),
                    decoration: InputDecoration(
                      hintText: 'Contoh: Kadar air 13.5% super kering, harga pas Rp 7.200 sudah termasuk muat truk.',
                      hintStyle: const TextStyle(fontSize: 12, color: Color(0xFF94A3B8)),
                      filled: true,
                      fillColor: const Color(0xFFF8FAFC),
                      contentPadding: const EdgeInsets.all(12),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                        borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
                      ),
                      focusedBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                        borderSide: const BorderSide(color: Color(0xFF059669), width: 1.6),
                      ),
                    ),
                  ),

                  const SizedBox(height: 14),

                  // 4. Live Total Estimasi
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF0FDF4),
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(color: const Color(0xFFA7F3D0)),
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text(
                          'Total Transaksi Nego:',
                          style: TextStyle(fontSize: 12, fontWeight: FontWeight.w700, color: Color(0xFF047857)),
                        ),
                        Text(
                          _formatCurrency(totalEst > 0 ? totalEst.round() : 0),
                          style: const TextStyle(
                            fontSize: 15,
                            fontWeight: FontWeight.w900,
                            color: Color(0xFF0F5132),
                          ),
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 18),

                  // 5. Submit Nego Button
                  SizedBox(
                    width: double.infinity,
                    height: 48,
                    child: FilledButton.icon(
                      onPressed: () {
                        Navigator.pop(ctx);
                        _updateStatus(
                          offer,
                          'countered',
                          counterPrice: p,
                          counterQuantity: q,
                          counterNotes: notesController.text.trim(),
                        );
                      },
                      style: FilledButton.styleFrom(
                        backgroundColor: const Color(0xFF059669),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                      icon: const Icon(Icons.send_rounded, size: 16),
                      label: const Text(
                        'Kirim Tawaran Balik ke Pembeli',
                        style: TextStyle(fontWeight: FontWeight.w900, fontSize: 13.5),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _buildPresetChip(String label, VoidCallback onTap) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(6),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3.5),
        decoration: BoxDecoration(
          color: const Color(0xFFF1F5F9),
          borderRadius: BorderRadius.circular(6),
          border: Border.all(color: const Color(0xFFE2E8F0)),
        ),
        child: Text(
          label,
          style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w700, color: Color(0xFF475569)),
        ),
      ),
    );
  }

  Future<void> _openWhatsApp(MarketOfferModel offer) async {
    var phone = offer.partnerPhone?.trim() ?? '';
    if (phone.isEmpty) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Nomor WhatsApp pembeli tidak tersedia.')),
      );
      return;
    }

    phone = phone.replaceAll(RegExp(r'[^0-9]'), '');
    if (phone.startsWith('0')) {
      phone = '62${phone.substring(1)}';
    }

    final message = '''
Halo ${offer.partnerName ?? 'Pembeli'},

Penawaran hasil panen Anda telah saya terima melalui Bursa P.A.D.I.

• Komoditas: ${offer.commodity ?? 'Hasil Panen'}
• Jumlah: ${_formatNumber(offer.quantity)} ${offer.unit ?? 'kg'}
• Harga Kesepakatan: Rp${_formatNumber(offer.offeredPrice)} / ${offer.unit ?? 'kg'}

Faktur pembelian resmi telah diterbitkan. Mari lanjutkan persiapan penimbangan tera sawah dan pengiriman armada truk.
''';

    final uri = Uri.parse('https://wa.me/$phone?text=${Uri.encodeComponent(message)}');
    final success = await launchUrl(uri, mode: LaunchMode.externalApplication);

    if (!success && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('WhatsApp tidak dapat dibuka.')),
      );
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
              context.go('/marketplace');
            }
          },
        ),
        title: Text(
          widget.listingId == null ? 'Daftar Penawaran Bursa' : 'Penawaran Masuk Hasil Panen',
          style: const TextStyle(
            fontSize: 17,
            fontWeight: FontWeight.w900,
            color: Color(0xFF0F172A),
          ),
        ),
      ),
      body: _buildBody(),
    );
  }

  Widget _buildBody() {
    if (_isLoading) {
      return const Center(child: CircularProgressIndicator(color: Color(0xFF059669)));
    }

    if (_error != null) {
      return _buildError();
    }

    if (_offers.isEmpty) {
      return _buildEmpty();
    }

    return RefreshIndicator(
      onRefresh: _loadOffers,
      color: const Color(0xFF059669),
      child: Center(
        child: ConstrainedBox(
          constraints: const BoxConstraints(maxWidth: 540),
          child: ListView.separated(
            physics: const AlwaysScrollableScrollPhysics(parent: BouncingScrollPhysics()),
            padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 16),
            itemCount: _offers.length,
            separatorBuilder: (context, index) => const SizedBox(height: 14),
            itemBuilder: (context, index) => _buildOfferCard(_offers[index]),
          ),
        ),
      ),
    );
  }

  Widget _buildOfferCard(MarketOfferModel offer) {
    final isPending = offer.isPending;
    final isCountered = offer.isCountered;
    final isAccepted = offer.isAccepted;
    final isRejected = offer.isRejected;
    final isProcessing = _processingOfferId == offer.id;

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(
          color: isCountered ? const Color(0xFF60A5FA) : const Color(0xFFE2E8F0),
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.02),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header Card
          Row(
            children: [
              Container(
                width: 42,
                height: 42,
                decoration: BoxDecoration(
                  color: isCountered ? const Color(0xFFEFF6FF) : const Color(0xFFECFDF5),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Icon(
                  isCountered ? Icons.handshake_rounded : Icons.store_rounded,
                  color: isCountered ? const Color(0xFF2563EB) : const Color(0xFF059669),
                  size: 20,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      offer.partnerName ?? 'Mitra Pembeli B2B',
                      style: const TextStyle(
                        fontSize: 14.5,
                        fontWeight: FontWeight.w900,
                        color: Color(0xFF0F172A),
                      ),
                    ),
                    if ((offer.partnerEmail ?? '').trim().isNotEmpty)
                      Text(
                        offer.partnerEmail ?? '',
                        style: const TextStyle(fontSize: 11.5, color: Color(0xFF64748B)),
                      ),
                  ],
                ),
              ),
              _buildStatusBadge(offer.status),
            ],
          ),

          const SizedBox(height: 14),
          const Divider(height: 1, color: Color(0xFFF1F5F9)),
          const SizedBox(height: 12),

          // Detail Penawaran
          _buildInfoRow(Icons.inventory_2_outlined, 'Komoditas', offer.commodity ?? 'Hasil Panen'),
          const SizedBox(height: 8),
          _buildInfoRow(Icons.scale_rounded, 'Kuantitas Ditawarkan', '${_formatNumber(offer.quantity)} ${offer.unit ?? 'kg'}'),
          const SizedBox(height: 8),
          _buildInfoRow(
            Icons.payments_rounded,
            'Harga Satuan',
            'Rp ${_formatNumber(offer.offeredPrice)} / ${offer.unit ?? 'kg'}',
            valueColor: const Color(0xFF059669),
            isBold: true,
          ),

          // Catatan / Pesan
          if ((offer.message ?? '').trim().isNotEmpty) ...[
            const SizedBox(height: 12),
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: isCountered ? const Color(0xFFF0F9FF) : const Color(0xFFF8FAFC),
                borderRadius: BorderRadius.circular(10),
                border: Border.all(
                  color: isCountered ? const Color(0xFFBAE6FD) : const Color(0xFFE2E8F0),
                ),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    isCountered ? 'Riwayat Negosiasi / Tawar Balik:' : 'Pesan dari Pembeli:',
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w800,
                      color: isCountered ? const Color(0xFF0369A1) : const Color(0xFF64748B),
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    offer.message ?? '',
                    style: TextStyle(
                      fontSize: 12.5,
                      color: isCountered ? const Color(0xFF0C4A6E) : const Color(0xFF0F172A),
                      height: 1.3,
                    ),
                  ),
                ],
              ),
            ),
          ],

          // Action Buttons: If Pending or Countered (Negotiation in Progress)
          if (isPending || isCountered) ...[
            const SizedBox(height: 16),
            Row(
              children: [
                // Tolak
                OutlinedButton(
                  onPressed: _processingOfferId == null ? () => _confirmAction(offer, 'rejected') : null,
                  style: OutlinedButton.styleFrom(
                    foregroundColor: const Color(0xFFDC2626),
                    side: const BorderSide(color: Color(0xFFDC2626)),
                    padding: const EdgeInsets.symmetric(horizontal: 12),
                    minimumSize: const Size(0, 42),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                  ),
                  child: const Text('Tolak', style: TextStyle(fontWeight: FontWeight.w800, fontSize: 12)),
                ),
                const SizedBox(width: 8),

                // Nego Ulang (Counter-Offer)
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: _processingOfferId == null ? () => _showCounterOfferModal(offer) : null,
                    style: OutlinedButton.styleFrom(
                      foregroundColor: const Color(0xFF059669),
                      side: const BorderSide(color: Color(0xFF059669), width: 1.4),
                      minimumSize: const Size(0, 42),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    ),
                    icon: const Icon(Icons.handshake_outlined, size: 16),
                    label: const Text(
                      'Nego Ulang',
                      style: TextStyle(fontWeight: FontWeight.w800, fontSize: 12),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                ),
                const SizedBox(width: 8),

                // Terima Penawaran
                Expanded(
                  child: FilledButton(
                    onPressed: _processingOfferId == null ? () => _confirmAction(offer, 'accepted') : null,
                    style: FilledButton.styleFrom(
                      backgroundColor: const Color(0xFF059669),
                      minimumSize: const Size(0, 42),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    ),
                    child: isProcessing
                        ? const SizedBox(
                            width: 16,
                            height: 16,
                            child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                          )
                        : const Text(
                            'Terima',
                            style: TextStyle(fontWeight: FontWeight.w900, fontSize: 12),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                  ),
                ),
              ],
            ),
          ],

          // If Accepted: Show Contract & Invoice Actions
          if (isAccepted) ...[
            const SizedBox(height: 14),
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: const Color(0xFFF0FDF4),
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: const Color(0xFFA7F3D0)),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Row(
                    children: [
                      Icon(Icons.check_circle_rounded, color: Color(0xFF059669), size: 16),
                      SizedBox(width: 6),
                      Text(
                        'Penawaran Disetujui & Kontrak Sah',
                        style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: Color(0xFF047857)),
                      ),
                    ],
                  ),
                  const SizedBox(height: 10),
                  Row(
                    children: [
                      Expanded(
                        child: OutlinedButton.icon(
                          onPressed: () {
                            context.push('/faktur/${offer.id}');
                          },
                          style: OutlinedButton.styleFrom(
                            foregroundColor: const Color(0xFF059669),
                            side: const BorderSide(color: Color(0xFF059669)),
                            minimumSize: const Size(0, 36),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                          ),
                          icon: const Icon(Icons.receipt_rounded, size: 15),
                          label: const Text('Buka Faktur', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800)),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child: FilledButton.icon(
                          onPressed: () => _openWhatsApp(offer),
                          style: FilledButton.styleFrom(
                            backgroundColor: const Color(0xFF059669),
                            minimumSize: const Size(0, 36),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                          ),
                          icon: const Icon(Icons.chat_rounded, size: 15),
                          label: const Text('WhatsApp', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800)),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],

          // If Rejected
          if (isRejected) ...[
            const SizedBox(height: 12),
            Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
              decoration: BoxDecoration(
                color: const Color(0xFFFEF2F2),
                borderRadius: BorderRadius.circular(8),
              ),
              child: const Row(
                children: [
                  Icon(Icons.cancel_rounded, color: Color(0xFFDC2626), size: 15),
                  SizedBox(width: 6),
                  Text('Penawaran ini telah ditolak.', style: TextStyle(fontSize: 11.5, color: Color(0xFFDC2626), fontWeight: FontWeight.w700)),
                ],
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildStatusBadge(String status) {
    Color bg;
    Color fg;
    String label;

    switch (status.toLowerCase()) {
      case 'accepted':
        bg = const Color(0xFFDCFCE7);
        fg = const Color(0xFF047857);
        label = 'DISETUJUI';
        break;
      case 'rejected':
        bg = const Color(0xFFFEE2E2);
        fg = const Color(0xFFDC2626);
        label = 'DITOLAK';
        break;
      case 'countered':
        bg = const Color(0xFFDBEAFE);
        fg = const Color(0xFF1D4ED8);
        label = 'DINEGOSIASI';
        break;
      case 'pending':
      default:
        bg = const Color(0xFFFEF3C7);
        fg = const Color(0xFFD97706);
        label = 'MENUNGGU';
        break;
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(color: bg, borderRadius: BorderRadius.circular(6)),
      child: Text(label, style: TextStyle(fontSize: 9.5, fontWeight: FontWeight.w900, color: fg)),
    );
  }

  Widget _buildInfoRow(
    IconData icon,
    String label,
    String value, {
    Color? valueColor,
    bool isBold = false,
  }) {
    return Row(
      children: [
        Icon(icon, size: 16, color: const Color(0xFF64748B)),
        const SizedBox(width: 8),
        Text(label, style: const TextStyle(fontSize: 12, color: Color(0xFF64748B))),
        const Spacer(),
        Text(
          value,
          style: TextStyle(
            fontSize: 12.5,
            fontWeight: isBold ? FontWeight.w900 : FontWeight.w700,
            color: valueColor ?? const Color(0xFF0F172A),
          ),
        ),
      ],
    );
  }

  Widget _buildEmpty() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.handshake_outlined, size: 54, color: Color(0xFF94A3B8)),
            const SizedBox(height: 14),
            const Text(
              'Belum Ada Penawaran Masuk',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800, color: Color(0xFF0F172A)),
            ),
            const SizedBox(height: 6),
            const Text(
              'Penawaran harga dari pembeli bursa akan muncul di sini. Anda dapat langsung menyetujui atau mengajukan nego tawar-balik.',
              textAlign: TextAlign.center,
              style: TextStyle(fontSize: 12.5, color: Color(0xFF64748B)),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildError() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.error_outline_rounded, size: 48, color: Color(0xFFDC2626)),
            const SizedBox(height: 12),
            Text(_error ?? 'Terjadi kesalahan', textAlign: TextAlign.center),
            const SizedBox(height: 16),
            FilledButton(
              onPressed: _loadOffers,
              style: FilledButton.styleFrom(backgroundColor: const Color(0xFF059669)),
              child: const Text('Coba Lagi'),
            ),
          ],
        ),
      ),
    );
  }
}
