import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:url_launcher/url_launcher.dart';

import 'package:padi/core/network/api_client.dart';
import 'package:padi/core/storage/token_storage.dart';
import 'package:padi/features/auth/presentation/widgets/padi_theme.dart';
import 'package:padi/features/marketplace/data/models/market_offer_model.dart';
import 'package:padi/features/marketplace/data/services/marketplace_api_service.dart';

class MarketOffersScreen extends StatefulWidget {
  const MarketOffersScreen({super.key, this.listingId});

  final int? listingId;

  @override
  State<MarketOffersScreen> createState() => _MarketOffersScreenState();
}

class _MarketOffersScreenState extends State<MarketOffersScreen> {
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

      if (!mounted) {
        return;
      }

      setState(() {
        _offers = offers;
        _isLoading = false;
      });
    } catch (e) {
      if (!mounted) {
        return;
      }

      setState(() {
        _isLoading = false;
        _error = e.toString().replaceFirst('Exception: ', '');
      });
    }
  }

  Future<void> _updateStatus(MarketOfferModel offer, String status) async {
    if (_processingOfferId != null) {
      return;
    }

    setState(() {
      _processingOfferId = offer.id;
    });

    try {
      final result = await _service.updateOfferStatus(
        offerId: offer.id,
        status: status,
      );

      if (!mounted) {
        return;
      }

      if (status == 'accepted') {
        await _loadOffers();

        if (!mounted) {
          return;
        }

        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Penawaran diterima. Membuka WhatsApp pembeli...'),
          ),
        );

        await Future.delayed(const Duration(milliseconds: 700));

        if (!mounted) {
          return;
        }

        await _openWhatsApp(result);

        return;
      }

      await _loadOffers();

      if (!mounted) {
        return;
      }

      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Penawaran berhasil ditolak.')),
      );
    } catch (e) {
      if (!mounted) {
        return;
      }

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(e.toString().replaceFirst('Exception: ', ''))),
      );
    } finally {
      if (mounted) {
        setState(() {
          _processingOfferId = null;
        });
      }
    }
  }

  Future<void> _confirmAction(MarketOfferModel offer, String status) async {
    final isAccept = status == 'accepted';

    final result = await showDialog<bool>(
      context: context,
      builder: (dialogContext) {
        return AlertDialog(
          title: Text(
            isAccept ? 'Terima Penawaran?' : 'Tolak Penawaran?',
            style: const TextStyle(fontWeight: FontWeight.w900),
          ),
          content: Text(
            isAccept
                ? 'Apakah Anda yakin ingin menerima penawaran dari ${offer.partnerName ?? 'Pembeli'}? Penawaran lain pada hasil panen ini akan ditolak.'
                : 'Apakah Anda yakin ingin menolak penawaran ini?',
          ),
          actions: [
            TextButton(
              onPressed: () {
                Navigator.of(dialogContext).pop(false);
              },
              child: const Text('Batal'),
            ),
            FilledButton(
              onPressed: () {
                Navigator.of(dialogContext).pop(true);
              },
              style: FilledButton.styleFrom(
                backgroundColor: isAccept ? padiGreen : Colors.red,
              ),
              child: Text(isAccept ? 'Terima' : 'Tolak'),
            ),
          ],
        );
      },
    );

    if (result != true) {
      return;
    }

    await _updateStatus(offer, status);
  }

  Future<void> _openWhatsApp(MarketOfferModel offer) async {
    var phone = offer.partnerPhone?.trim() ?? '';

    if (phone.isEmpty) {
      if (!mounted) {
        return;
      }

      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Nomor WhatsApp pembeli tidak tersedia.')),
      );

      return;
    }

    phone = phone.replaceAll(RegExp(r'[^0-9]'), '');

    if (phone.startsWith('0')) {
      phone = '62${phone.substring(1)}';
    }

    final message =
        '''
Halo ${offer.partnerName ?? 'Pembeli'},

Penawaran Anda telah saya terima.

Hasil panen: ${offer.commodity ?? 'Hasil Panen'}
Jumlah: ${_formatNumber(offer.quantity)} ${offer.unit ?? ''}
Harga: Rp${_formatNumber(offer.offeredPrice)} / ${offer.unit ?? ''}

Hasil panen sudah terjual kepada Anda. Mari lanjutkan proses kontrak dan pembayaran melalui WhatsApp.
''';

    final uri = Uri.parse(
      'https://wa.me/$phone?text=${Uri.encodeComponent(message)}',
    );

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
      backgroundColor: padiField,
      appBar: AppBar(
        backgroundColor: padiGreen,
        foregroundColor: Colors.white,
        elevation: 0,
        leading: IconButton(
          tooltip: 'Kembali',
          onPressed: () {
            if (context.canPop()) {
              context.pop();
            } else {
              context.go('/marketplace');
            }
          },
          icon: const Icon(Icons.arrow_back_rounded),
        ),
        title: Text(
          widget.listingId == null ? 'Penawaran Saya' : 'Penawaran Masuk',
          style: TextStyle(fontSize: 20, fontWeight: FontWeight.w900),
        ),
      ),
      body: _buildBody(),
    );
  }

  Widget _buildBody() {
    if (_isLoading) {
      return const Center(child: CircularProgressIndicator(color: padiGreen));
    }

    if (_error != null) {
      return _buildError();
    }

    if (_offers.isEmpty) {
      return _buildEmpty();
    }

    return RefreshIndicator(
      onRefresh: _loadOffers,
      color: padiGreen,
      child: ListView.separated(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.fromLTRB(20, 20, 20, 30),
        itemCount: _offers.length,
        separatorBuilder: (context, index) {
          return const SizedBox(height: 16);
        },
        itemBuilder: (context, index) {
          return _buildOfferCard(_offers[index]);
        },
      ),
    );
  }

  Widget _buildOfferCard(MarketOfferModel offer) {
    final canProcessOffer = widget.listingId != null;
    final isPending = offer.isPending && canProcessOffer;
    final isAccepted = offer.isAccepted;
    final isRejected = offer.isRejected;

    final isProcessing = _processingOfferId == offer.id;

    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(22),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 48,
                height: 48,
                decoration: BoxDecoration(
                  color: padiGreen,
                  borderRadius: BorderRadius.circular(14),
                ),
                child: const Icon(Icons.person_rounded, color: Colors.white),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      offer.partnerName ?? 'Pembeli',
                      style: const TextStyle(
                        color: padiInk,
                        fontSize: 17,
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                    if ((offer.partnerEmail ?? '').trim().isNotEmpty)
                      Text(
                        offer.partnerEmail ?? '',
                        style: const TextStyle(color: padiMuted, fontSize: 13),
                      ),
                  ],
                ),
              ),
              _buildStatusBadge(offer.status),
            ],
          ),
          const SizedBox(height: 18),
          _buildInfoRow(
            Icons.grass_rounded,
            'Hasil Panen',
            offer.commodity ?? 'Hasil Panen',
          ),
          const SizedBox(height: 10),
          _buildInfoRow(
            Icons.scale_rounded,
            'Jumlah',
            '${_formatNumber(offer.quantity)} ${offer.unit ?? ''}',
          ),
          const SizedBox(height: 10),
          _buildInfoRow(
            Icons.payments_rounded,
            'Harga Penawaran',
            'Rp${_formatNumber(offer.offeredPrice)} / ${offer.unit ?? ''}',
            valueColor: padiGreen,
          ),
          if ((offer.message ?? '').trim().isNotEmpty) ...[
            const SizedBox(height: 16),
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: padiField,
                borderRadius: BorderRadius.circular(14),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Pesan Pembeli',
                    style: TextStyle(
                      color: padiMuted,
                      fontSize: 13,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                  const SizedBox(height: 6),
                  Text(
                    offer.message ?? '',
                    style: const TextStyle(color: padiInk, fontSize: 14),
                  ),
                ],
              ),
            ),
          ],
          if (isPending) ...[
            const SizedBox(height: 18),
            Row(
              children: [
                Expanded(
                  child: OutlinedButton(
                    onPressed: _processingOfferId == null
                        ? () => _confirmAction(offer, 'rejected')
                        : null,
                    style: OutlinedButton.styleFrom(
                      foregroundColor: Colors.red,
                      side: const BorderSide(color: Colors.red),
                      minimumSize: const Size(0, 50),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(14),
                      ),
                    ),
                    child: const Text(
                      'Tolak',
                      style: TextStyle(fontWeight: FontWeight.w900),
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: FilledButton(
                    onPressed: _processingOfferId == null
                        ? () => _confirmAction(offer, 'accepted')
                        : null,
                    style: FilledButton.styleFrom(
                      backgroundColor: padiGreen,
                      minimumSize: const Size(0, 50),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(14),
                      ),
                    ),
                    child: isProcessing
                        ? const SizedBox(
                            width: 20,
                            height: 20,
                            child: CircularProgressIndicator(
                              strokeWidth: 2.5,
                              color: Colors.white,
                            ),
                          )
                        : const Text(
                            'Terima',
                            style: TextStyle(fontWeight: FontWeight.w900),
                          ),
                  ),
                ),
              ],
            ),
          ],
          if (isAccepted) ...[
            const SizedBox(height: 18),
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: padiField,
                borderRadius: BorderRadius.circular(14),
              ),
              child: const Row(
                children: [
                  Icon(Icons.check_circle_rounded, color: padiGreen),
                  SizedBox(width: 10),
                  Expanded(
                    child: Text(
                      'Penawaran diterima. Silakan lanjutkan kontrak dan pembayaran melalui WhatsApp.',
                      style: TextStyle(
                        color: padiInk,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],
          if (isRejected) ...[
            const SizedBox(height: 18),
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: Colors.red.shade50,
                borderRadius: BorderRadius.circular(14),
              ),
              child: const Row(
                children: [
                  Icon(Icons.cancel_rounded, color: Colors.red),
                  SizedBox(width: 10),
                  Expanded(
                    child: Text(
                      'Penawaran ini ditolak.',
                      style: TextStyle(
                        color: Colors.red,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildInfoRow(
    IconData icon,
    String label,
    String value, {
    Color? valueColor,
  }) {
    return Row(
      children: [
        Icon(icon, color: padiGreen, size: 22),
        const SizedBox(width: 10),
        Expanded(
          child: Text(
            label,
            style: const TextStyle(color: padiMuted, fontSize: 14),
          ),
        ),
        Text(
          value,
          textAlign: TextAlign.right,
          style: TextStyle(
            color: valueColor ?? padiInk,
            fontSize: 14,
            fontWeight: FontWeight.w900,
          ),
        ),
      ],
    );
  }

  Widget _buildStatusBadge(String status) {
    String text;
    Color color;

    switch (status) {
      case 'accepted':
        text = 'Diterima';
        color = padiGreen;
        break;
      case 'rejected':
        text = 'Ditolak';
        color = Colors.red;
        break;
      default:
        text = 'Menunggu';
        color = Colors.orange;
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: color.withOpacity(0.12),
        borderRadius: BorderRadius.circular(20),
      ),
      child: Text(
        text,
        style: TextStyle(
          color: color,
          fontSize: 12,
          fontWeight: FontWeight.w900,
        ),
      ),
    );
  }

  Widget _buildEmpty() {
    return RefreshIndicator(
      onRefresh: _loadOffers,
      color: padiGreen,
      child: ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        children: [
          const SizedBox(height: 220),
          const Icon(Icons.inbox_outlined, size: 70, color: padiMuted),
          const SizedBox(height: 18),
          Center(
            child: Text(
              widget.listingId == null
                  ? 'Belum ada penawaran'
                  : 'Belum ada penawaran masuk',
              style: const TextStyle(
                color: padiInk,
                fontSize: 20,
                fontWeight: FontWeight.w900,
              ),
            ),
          ),
          const SizedBox(height: 8),
          Center(
            child: Text(
              widget.listingId == null
                  ? 'Riwayat penawaran marketplace akan muncul di sini.'
                  : 'Penawaran untuk hasil panen ini akan muncul di sini.',
              textAlign: TextAlign.center,
              style: const TextStyle(color: padiMuted, fontSize: 14),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildError() {
    return RefreshIndicator(
      onRefresh: _loadOffers,
      color: padiGreen,
      child: ListView(
        physics: const AlwaysScrollableScrollPhysics(),
        padding: const EdgeInsets.all(30),
        children: [
          const SizedBox(height: 180),
          const Icon(Icons.cloud_off_rounded, size: 70, color: padiMuted),
          const SizedBox(height: 18),
          const Text(
            'Gagal mengambil penawaran',
            textAlign: TextAlign.center,
            style: TextStyle(
              color: padiInk,
              fontSize: 20,
              fontWeight: FontWeight.w900,
            ),
          ),
          if ((_error ?? '').trim().isNotEmpty) ...[
            const SizedBox(height: 12),
            Text(
              _error ?? '',
              textAlign: TextAlign.center,
              style: const TextStyle(color: padiMuted, fontSize: 13),
            ),
          ],
          const SizedBox(height: 24),
          FilledButton(
            onPressed: _loadOffers,
            style: FilledButton.styleFrom(
              backgroundColor: padiGreen,
              minimumSize: const Size.fromHeight(54),
            ),
            child: const Text(
              'Coba Lagi',
              style: TextStyle(fontWeight: FontWeight.w900),
            ),
          ),
        ],
      ),
    );
  }

  String _formatNumber(double value) {
    if (value == value.roundToDouble()) {
      return value.toInt().toString();
    }

    return value.toStringAsFixed(2);
  }
}
