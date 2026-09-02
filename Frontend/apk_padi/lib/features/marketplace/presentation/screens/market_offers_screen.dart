import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import 'package:url_launcher/url_launcher.dart';

import 'package:padi/core/network/api_client.dart';
import 'package:padi/core/providers/app_providers.dart';
import 'package:padi/core/storage/token_storage.dart';
import 'package:padi/features/marketplace/data/models/market_offer_model.dart';
import 'package:padi/features/marketplace/data/services/marketplace_api_service.dart';
import 'package:padi/features/notifications/presentation/providers/notifications_provider.dart';

class MarketOffersScreen extends ConsumerStatefulWidget {
  const MarketOffersScreen({
    super.key,
    this.listingId,
  });

  final int? listingId;

  @override
  ConsumerState<MarketOffersScreen> createState() =>
      _MarketOffersScreenState();
}

class _MarketOffersScreenState
    extends ConsumerState<MarketOffersScreen> {
  late final MarketplaceApiService _service;

  List<MarketOfferModel> _offers = [];
  bool _isLoading = true;
  String? _error;
  int? _processingOfferId;

  static const Color primary = Color(0xFF059669);
  static const Color primaryDark = Color(0xFF047857);
  static const Color textDark = Color(0xFF0F172A);
  static const Color textMuted = Color(0xFF64748B);
  static const Color border = Color(0xFFE2E8F0);
  static const Color background = Color(0xFFF8FAFC);

  @override
  void initState() {
    super.initState();

    _service = MarketplaceApiService(
      ApiClient(
        const SecureTokenStorage(),
      ),
    );

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
      final List<MarketOfferModel> offers;

      if (widget.listingId == null) {
        offers = await _service.fetchMyOffers();
      } else {
        offers = await _service.fetchListingOffers(
          widget.listingId!,
        );
      }

      if (!mounted) return;

      setState(() {
        _offers = offers;
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

    return message.startsWith('Exception: ')
        ? message.substring(11)
        : message;
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

    setState(() {
      _processingOfferId = offer.id;
    });

    try {
      final result = await _service.updateOfferStatus(
        offerId: offer.id,
        status: status,
        counterPrice: counterPrice,
        counterQuantity: counterQuantity,
        counterNotes: counterNotes,
      );

      if (!mounted) return;

      ref.read(notificationsProvider.notifier).refresh();

      await _loadOffers();

      if (!mounted) return;

      if (status == 'accepted') {
        _showSnackBar(
          'Penawaran diterima! Kontrak resmi terbentuk.',
          icon: Icons.check_circle_rounded,
          color: primary,
        );

        await Future.delayed(
          const Duration(milliseconds: 500),
        );

        if (mounted) {
          await _openWhatsApp(result);
        }
      }

      if (status == 'countered') {
        final isBuyer = ref.read(isBuyerRoleProvider);

        _showSnackBar(
          isBuyer
              ? 'Offer berikutnya berhasil dikirim ke petani.'
              : 'Tawaran balik berhasil dikirim ke pembeli.',
          icon: Icons.handshake_rounded,
          color: primary,
        );
      }

      if (status == 'rejected') {
        _showSnackBar(
          'Penawaran berhasil ditolak.',
          color: const Color(0xFFDC2626),
        );
      }
    } catch (e) {
      if (!mounted) return;

      _showSnackBar(
        _cleanError(e),
        color: const Color(0xFFDC2626),
      );
    } finally {
      if (mounted) {
        setState(() {
          _processingOfferId = null;
        });
      }
    }
  }

  void _showSnackBar(
    String message, {
    IconData? icon,
    Color color = primary,
  }) {
    ScaffoldMessenger.of(context)
      ..hideCurrentSnackBar()
      ..showSnackBar(
        SnackBar(
          content: Row(
            children: [
              if (icon != null) ...[
                Icon(
                  icon,
                  color: Colors.white,
                  size: 18,
                ),
                const SizedBox(width: 8),
              ],
              Expanded(
                child: Text(
                  message,
                  style: const TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),
            ],
          ),
          backgroundColor: color,
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(10),
          ),
        ),
      );
  }

  Future<void> _confirmAction(
    MarketOfferModel offer,
    String status,
  ) async {
    final isAccept = status == 'accepted';

    final result = await showDialog<bool>(
      context: context,
      builder: (dialogContext) {
        return AlertDialog(
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(16),
          ),
          title: Text(
            isAccept
                ? 'Terima Penawaran?'
                : 'Tolak Penawaran?',
            style: const TextStyle(
              fontWeight: FontWeight.w900,
              fontSize: 16,
              color: textDark,
            ),
          ),
          content: Text(
            isAccept
                ? 'Apakah Anda yakin ingin menerima penawaran dari ${offer.partnerName ?? 'Pembeli'}? Kontrak pembelian resmi akan langsung terbentuk.'
                : 'Apakah Anda yakin ingin menolak penawaran ini?',
            style: const TextStyle(
              fontSize: 13,
              color: Color(0xFF475569),
              height: 1.4,
            ),
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
                backgroundColor: isAccept
                    ? primary
                    : const Color(0xFFDC2626),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(9),
                ),
              ),
              child: Text(
                isAccept ? 'Ya, Terima' : 'Ya, Tolak',
              ),
            ),
          ],
        );
      },
    );

    if (result != true) return;

    await _updateStatus(
      offer,
      status,
    );
  }

  void _showCounterOfferModal(
    MarketOfferModel offer,
  ) {
    final isBuyer = ref.read(isBuyerRoleProvider);

    final priceController = TextEditingController(
      text: offer.offeredPrice.round().toString(),
    );

    final quantityController = TextEditingController(
      text: offer.quantity.round().toString(),
    );

    final notesController = TextEditingController();

    showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (sheetContext) {
        return StatefulBuilder(
          builder: (modalContext, setModalState) {
            final parsedPrice =
                double.tryParse(
                  priceController.text
                      .replaceAll('.', '')
                      .replaceAll(',', '.'),
                ) ??
                offer.offeredPrice;

            final parsedQuantity =
                double.tryParse(
                  quantityController.text
                      .replaceAll('.', '')
                      .replaceAll(',', '.'),
                ) ??
                offer.quantity;

            final totalEstimation =
                parsedPrice * parsedQuantity;

            return Container(
              padding: EdgeInsets.fromLTRB(
                20,
                18,
                20,
                MediaQuery.of(modalContext)
                        .viewInsets
                        .bottom +
                    20,
              ),
              decoration: const BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.vertical(
                  top: Radius.circular(22),
                ),
              ),
              child: SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment:
                      CrossAxisAlignment.start,
                  children: [
                    Center(
                      child: Container(
                        width: 38,
                        height: 4,
                        decoration: BoxDecoration(
                          color: const Color(0xFFCBD5E1),
                          borderRadius:
                              BorderRadius.circular(4),
                        ),
                      ),
                    ),
                    const SizedBox(height: 14),
                    Row(
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment:
                                CrossAxisAlignment.start,
                            children: [
                              Text(
                                isBuyer
                                    ? 'Kirim Offer Berikutnya'
                                    : 'Nego Ulang Penawaran',
                                style: const TextStyle(
                                  fontSize: 17,
                                  fontWeight:
                                      FontWeight.w900,
                                  color: textDark,
                                ),
                              ),
                              const SizedBox(height: 3),
                              Text(
                                isBuyer
                                    ? 'Petani: ${offer.partnerName ?? 'Petani'}'
                                    : 'Mitra Pembeli: ${offer.partnerName ?? 'Mitra B2B'}',
                                style: const TextStyle(
                                  fontSize: 12,
                                  color: textMuted,
                                ),
                              ),
                            ],
                          ),
                        ),
                        Container(
                          padding:
                              const EdgeInsets.symmetric(
                            horizontal: 8,
                            vertical: 4,
                          ),
                          decoration: BoxDecoration(
                            color:
                                const Color(0xFFECFDF5),
                            borderRadius:
                                BorderRadius.circular(6),
                          ),
                          child: Text(
                            isBuyer
                                ? 'OFFER'
                                : 'FITUR NEGO',
                            style: const TextStyle(
                              fontSize: 9.5,
                              fontWeight:
                                  FontWeight.w900,
                              color: primaryDark,
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 14),
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: background,
                        borderRadius:
                            BorderRadius.circular(10),
                        border: Border.all(
                          color: border,
                        ),
                      ),
                      child: Column(
                        crossAxisAlignment:
                            CrossAxisAlignment.start,
                        children: [
                          Text(
                            isBuyer
                                ? 'Tawaran Petani'
                                : 'Tawaran Pembeli',
                            style: const TextStyle(
                              fontSize: 11,
                              fontWeight:
                                  FontWeight.w800,
                              color: textMuted,
                            ),
                          ),
                          const SizedBox(height: 5),
                          Text(
                            '${_formatCurrency(offer.offeredPrice)} / ${offer.unit ?? 'kg'}',
                            style: const TextStyle(
                              fontSize: 14,
                              fontWeight:
                                  FontWeight.w900,
                              color: textDark,
                            ),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            'Jumlah ${_formatNumber(offer.quantity)} ${offer.unit ?? 'kg'}',
                            style: const TextStyle(
                              fontSize: 11.5,
                              color: textMuted,
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),
                    Text(
                      isBuyer
                          ? 'Harga Offer Berikutnya'
                          : 'Harga Tawar Balik Petani',
                      style: const TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w800,
                        color: textDark,
                      ),
                    ),
                    const SizedBox(height: 6),
                    TextField(
                      controller: priceController,
                      keyboardType:
                          const TextInputType.numberWithOptions(
                        decimal: true,
                      ),
                      onChanged: (_) {
                        setModalState(() {});
                      },
                      decoration: InputDecoration(
                        prefixText: 'Rp ',
                        suffixText:
                            '/ ${offer.unit ?? 'kg'}',
                        filled: true,
                        fillColor: background,
                        contentPadding:
                            const EdgeInsets.symmetric(
                          horizontal: 14,
                          vertical: 12,
                        ),
                        border: OutlineInputBorder(
                          borderRadius:
                              BorderRadius.circular(12),
                          borderSide:
                              const BorderSide(
                            color: border,
                          ),
                        ),
                        enabledBorder:
                            OutlineInputBorder(
                          borderRadius:
                              BorderRadius.circular(12),
                          borderSide:
                              const BorderSide(
                            color: border,
                          ),
                        ),
                        focusedBorder:
                            OutlineInputBorder(
                          borderRadius:
                              BorderRadius.circular(12),
                          borderSide:
                              const BorderSide(
                            color: primary,
                            width: 1.6,
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(height: 7),
                    Wrap(
                      spacing: 6,
                      runSpacing: 6,
                      children: [
                        _buildPresetChip(
                          '+ Rp 200',
                          () {
                            priceController.text =
                                (parsedPrice + 200)
                                    .round()
                                    .toString();

                            setModalState(() {});
                          },
                        ),
                        _buildPresetChip(
                          '+ Rp 500',
                          () {
                            priceController.text =
                                (parsedPrice + 500)
                                    .round()
                                    .toString();

                            setModalState(() {});
                          },
                        ),
                        _buildPresetChip(
                          '+ Rp 1.000',
                          () {
                            priceController.text =
                                (parsedPrice + 1000)
                                    .round()
                                    .toString();

                            setModalState(() {});
                          },
                        ),
                      ],
                    ),
                    const SizedBox(height: 14),
                    Text(
                      isBuyer
                          ? 'Kuantitas yang Dibutuhkan'
                          : 'Kuantitas yang Disanggupi Petani',
                      style: const TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w800,
                        color: textDark,
                      ),
                    ),
                    const SizedBox(height: 6),
                    TextField(
                      controller: quantityController,
                      keyboardType:
                          const TextInputType.numberWithOptions(
                        decimal: true,
                      ),
                      onChanged: (_) {
                        setModalState(() {});
                      },
                      decoration: InputDecoration(
                        suffixText:
                            '${offer.unit ?? 'kg'}  ',
                        filled: true,
                        fillColor: background,
                        contentPadding:
                            const EdgeInsets.symmetric(
                          horizontal: 14,
                          vertical: 12,
                        ),
                        border: OutlineInputBorder(
                          borderRadius:
                              BorderRadius.circular(12),
                          borderSide:
                              const BorderSide(
                            color: border,
                          ),
                        ),
                        enabledBorder:
                            OutlineInputBorder(
                          borderRadius:
                              BorderRadius.circular(12),
                          borderSide:
                              const BorderSide(
                            color: border,
                          ),
                        ),
                        focusedBorder:
                            OutlineInputBorder(
                          borderRadius:
                              BorderRadius.circular(12),
                          borderSide:
                              const BorderSide(
                            color: primary,
                            width: 1.6,
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(height: 14),
                    Text(
                      isBuyer
                          ? 'Catatan / Pesan untuk Petani'
                          : 'Catatan / Alasan Nego',
                      style: const TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w800,
                        color: textDark,
                      ),
                    ),
                    const SizedBox(height: 6),
                    TextField(
                      controller: notesController,
                      maxLines: 3,
                      decoration: InputDecoration(
                        hintText: isBuyer
                            ? 'Contoh: Saya mengajukan harga sesuai kondisi pasar.'
                            : 'Contoh: Kadar air bagus, harga pas Rp 7.200.',
                        hintStyle: const TextStyle(
                          fontSize: 12,
                          color: Color(0xFF94A3B8),
                        ),
                        filled: true,
                        fillColor: background,
                        contentPadding:
                            const EdgeInsets.all(12),
                        border: OutlineInputBorder(
                          borderRadius:
                              BorderRadius.circular(12),
                          borderSide:
                              const BorderSide(
                            color: border,
                          ),
                        ),
                        enabledBorder:
                            OutlineInputBorder(
                          borderRadius:
                              BorderRadius.circular(12),
                          borderSide:
                              const BorderSide(
                            color: border,
                          ),
                        ),
                        focusedBorder:
                            OutlineInputBorder(
                          borderRadius:
                              BorderRadius.circular(12),
                          borderSide:
                              const BorderSide(
                            color: primary,
                            width: 1.6,
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(height: 14),
                    Container(
                      width: double.infinity,
                      padding:
                          const EdgeInsets.symmetric(
                        horizontal: 14,
                        vertical: 11,
                      ),
                      decoration: BoxDecoration(
                        color:
                            const Color(0xFFF0FDF4),
                        borderRadius:
                            BorderRadius.circular(10),
                        border: Border.all(
                          color:
                              const Color(0xFFA7F3D0),
                        ),
                      ),
                      child: Row(
                        children: [
                          const Expanded(
                            child: Text(
                              'Total Transaksi',
                              style: TextStyle(
                                fontSize: 12,
                                fontWeight:
                                    FontWeight.w700,
                                color: primaryDark,
                              ),
                            ),
                          ),
                          Text(
                            _formatCurrency(
                              totalEstimation > 0
                                  ? totalEstimation
                                      .round()
                                  : 0,
                            ),
                            style: const TextStyle(
                              fontSize: 15,
                              fontWeight:
                                  FontWeight.w900,
                              color:
                                  Color(0xFF0F5132),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 18),
                    SizedBox(
                      width: double.infinity,
                      height: 48,
                      child: FilledButton.icon(
                        onPressed: () {
                          if (parsedPrice <= 0 ||
                              parsedQuantity <= 0) {
                            ScaffoldMessenger.of(
                              modalContext,
                            ).showSnackBar(
                              const SnackBar(
                                content: Text(
                                  'Harga dan kuantitas harus lebih dari 0.',
                                ),
                              ),
                            );
                            return;
                          }

                          Navigator.of(
                            sheetContext,
                          ).pop();

                          _updateStatus(
                            offer,
                            'countered',
                            counterPrice: parsedPrice,
                            counterQuantity:
                                parsedQuantity,
                            counterNotes:
                                notesController.text
                                    .trim(),
                          );
                        },
                        style: FilledButton.styleFrom(
                          backgroundColor: primary,
                          shape:
                              RoundedRectangleBorder(
                            borderRadius:
                                BorderRadius.circular(12),
                          ),
                        ),
                        icon: const Icon(
                          Icons.send_rounded,
                          size: 16,
                        ),
                        label: Text(
                          isBuyer
                              ? 'Kirim Offer Berikutnya'
                              : 'Kirim Tawaran Balik',
                          style: const TextStyle(
                            fontWeight:
                                FontWeight.w900,
                            fontSize: 13.5,
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            );
          },
        );
      },
    );
  }

  Widget _buildPresetChip(
    String label,
    VoidCallback onTap,
  ) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(6),
      child: Container(
        padding:
            const EdgeInsets.symmetric(
          horizontal: 8,
          vertical: 4,
        ),
        decoration: BoxDecoration(
          color: const Color(0xFFF1F5F9),
          borderRadius:
              BorderRadius.circular(6),
          border: Border.all(
            color: border,
          ),
        ),
        child: Text(
          label,
          style: const TextStyle(
            fontSize: 11,
            fontWeight: FontWeight.w700,
            color: Color(0xFF475569),
          ),
        ),
      ),
    );
  }

  Future<void> _openWhatsApp(
    MarketOfferModel offer,
  ) async {
    var phone =
        offer.partnerPhone?.trim() ?? '';

    if (phone.isEmpty) {
      if (!mounted) return;

      _showSnackBar(
        'Nomor WhatsApp tidak tersedia.',
        color: const Color(0xFFDC2626),
      );

      return;
    }

    phone = phone.replaceAll(
      RegExp(r'[^0-9]'),
      '',
    );

    if (phone.startsWith('0')) {
      phone = '62${phone.substring(1)}';
    }

    if (phone.length < 10) {
      if (!mounted) return;

      _showSnackBar(
        'Nomor WhatsApp tidak valid.',
        color: const Color(0xFFDC2626),
      );

      return;
    }

    final message = '''
Halo ${offer.partnerName ?? 'Mitra P.A.D.I.'},

Penawaran hasil panen Anda telah saya terima melalui Bursa P.A.D.I.

• Komoditas: ${offer.commodity ?? 'Hasil Panen'}
• Jumlah: ${_formatNumber(offer.quantity)} ${offer.unit ?? 'kg'}
• Harga Kesepakatan: ${_formatCurrency(offer.offeredPrice)} / ${offer.unit ?? 'kg'}

Kontrak pembelian resmi telah terbentuk. Mari lanjutkan persiapan penimbangan dan pengiriman hasil panen.
''';

    final uri = Uri.parse(
      'https://wa.me/$phone?text=${Uri.encodeComponent(message)}',
    );

    try {
      final success = await launchUrl(
        uri,
        mode: LaunchMode.externalApplication,
      );

      if (!success && mounted) {
        _showSnackBar(
          'WhatsApp tidak dapat dibuka.',
          color: const Color(0xFFDC2626),
        );
      }
    } catch (_) {
      if (!mounted) return;

      _showSnackBar(
        'WhatsApp tidak dapat dibuka.',
        color: const Color(0xFFDC2626),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final isBuyer = ref.watch(isBuyerRoleProvider);

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
              context.go('/marketplace');
            }
          },
        ),
        title: Text(
          isBuyer
              ? 'Penawaran Saya'
              : widget.listingId == null
                  ? 'Daftar Penawaran Bursa'
                  : 'Penawaran Masuk Hasil Panen',
          style: const TextStyle(
            fontSize: 17,
            fontWeight: FontWeight.w900,
            color: textDark,
          ),
        ),
      ),
      body: _buildBody(isBuyer),
    );
  }

  Widget _buildBody(bool isBuyer) {
    if (_isLoading) {
      return const Center(
        child: CircularProgressIndicator(
          color: primary,
        ),
      );
    }

    if (_error != null) {
      return _buildError();
    }

    if (_offers.isEmpty) {
      return _buildEmpty(isBuyer);
    }

    return RefreshIndicator(
      onRefresh: _loadOffers,
      color: primary,
      child: Center(
        child: ConstrainedBox(
          constraints: const BoxConstraints(
            maxWidth: 540,
          ),
          child: ListView.separated(
            physics:
                const AlwaysScrollableScrollPhysics(
              parent: BouncingScrollPhysics(),
            ),
            padding:
                const EdgeInsets.symmetric(
              horizontal: 18,
              vertical: 16,
            ),
            itemCount: _offers.length,
            separatorBuilder: (_, __) {
              return const SizedBox(height: 14);
            },
            itemBuilder: (_, index) {
              return _buildOfferCard(
                _offers[index],
                isBuyer,
              );
            },
          ),
        ),
      ),
    );
  }

  Widget _buildOfferCard(
    MarketOfferModel offer,
    bool isBuyer,
  ) {
    final isAccepted = offer.isAccepted;
    final isRejected = offer.isRejected;
    final isProcessing =
        _processingOfferId == offer.id;

    final canFarmerAct =
        !isBuyer &&
        offer.canFarmerAct;

    final canBuyerAct =
        isBuyer &&
        offer.canBuyerAct;

    final isCurrentUserTurn =
        canFarmerAct || canBuyerAct;

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius:
            BorderRadius.circular(16),
        border: Border.all(
          color: offer.isCountered
              ? const Color(0xFF60A5FA)
              : border,
        ),
        boxShadow: [
          BoxShadow(
            color:
                Colors.black.withOpacity(0.02),
            blurRadius: 8,
            offset:
                const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment:
            CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 42,
                height: 42,
                decoration: BoxDecoration(
                  color: offer.isCountered
                      ? const Color(0xFFEFF6FF)
                      : const Color(0xFFECFDF5),
                  borderRadius:
                      BorderRadius.circular(10),
                ),
                child: Icon(
                  offer.isCountered
                      ? Icons.handshake_rounded
                      : Icons.store_rounded,
                  color: offer.isCountered
                      ? const Color(0xFF2563EB)
                      : primary,
                  size: 20,
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment:
                      CrossAxisAlignment.start,
                  children: [
                    Text(
                      offer.partnerName ??
                          (isBuyer
                              ? 'Petani'
                              : 'Mitra Pembeli B2B'),
                      maxLines: 1,
                      overflow:
                          TextOverflow.ellipsis,
                      style: const TextStyle(
                        fontSize: 14.5,
                        fontWeight:
                            FontWeight.w900,
                        color: textDark,
                      ),
                    ),
                    if ((offer.partnerEmail ?? '')
                        .trim()
                        .isNotEmpty)
                      Text(
                        offer.partnerEmail!,
                        maxLines: 1,
                        overflow:
                            TextOverflow.ellipsis,
                        style: const TextStyle(
                          fontSize: 11.5,
                          color: textMuted,
                        ),
                      ),
                  ],
                ),
              ),
              const SizedBox(width: 8),
              _buildStatusBadge(
                offer.status,
              ),
            ],
          ),
          const SizedBox(height: 14),
          const Divider(
            height: 1,
            color: Color(0xFFF1F5F9),
          ),
          const SizedBox(height: 12),
          _buildInfoRow(
            Icons.inventory_2_outlined,
            'Komoditas',
            offer.commodity ??
                'Hasil Panen',
          ),
          const SizedBox(height: 8),
          _buildInfoRow(
            Icons.scale_rounded,
            'Kuantitas',
            '${_formatNumber(offer.quantity)} ${offer.unit ?? 'kg'}',
          ),
          const SizedBox(height: 8),
          _buildInfoRow(
            Icons.payments_rounded,
            'Harga Satuan',
            '${_formatCurrency(offer.offeredPrice)} / ${offer.unit ?? 'kg'}',
            valueColor: primary,
            isBold: true,
          ),
          const SizedBox(height: 8),
          _buildInfoRow(
            Icons.calculate_rounded,
            'Total Penawaran',
            _formatCurrency(
              offer.offeredPrice *
                  offer.quantity,
            ),
            valueColor: textDark,
            isBold: true,
          ),
          if ((offer.message ?? '')
              .trim()
              .isNotEmpty) ...[
            const SizedBox(height: 12),
            Container(
              width: double.infinity,
              padding:
                  const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: offer.isCountered
                    ? const Color(0xFFF0F9FF)
                    : background,
                borderRadius:
                    BorderRadius.circular(10),
                border: Border.all(
                  color: offer.isCountered
                      ? const Color(0xFFBAE6FD)
                      : border,
                ),
              ),
              child: Column(
                crossAxisAlignment:
                    CrossAxisAlignment.start,
                children: [
                  Text(
                    offer.isCountered
                        ? 'Riwayat Negosiasi'
                        : 'Pesan',
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight:
                          FontWeight.w800,
                      color: offer.isCountered
                          ? const Color(
                              0xFF0369A1,
                            )
                          : textMuted,
                    ),
                  ),
                  const SizedBox(height: 5),
                  Text(
                    offer.message!,
                    style: TextStyle(
                      fontSize: 12.5,
                      color: offer.isCountered
                          ? const Color(
                              0xFF0C4A6E,
                            )
                          : textDark,
                      height: 1.35,
                    ),
                  ),
                ],
              ),
            ),
          ],
          if (offer.isActive) ...[
            const SizedBox(height: 14),
            _buildTurnInformation(
              offer: offer,
              isBuyer: isBuyer,
              isCurrentUserTurn:
                  isCurrentUserTurn,
            ),
          ],
          if (offer.isActive &&
              canFarmerAct) ...[
            const SizedBox(height: 16),
            Row(
              children: [
                OutlinedButton(
                  onPressed:
                      _processingOfferId == null
                          ? () =>
                              _confirmAction(
                                offer,
                                'rejected',
                              )
                          : null,
                  style:
                      OutlinedButton.styleFrom(
                    foregroundColor:
                        const Color(
                      0xFFDC2626,
                    ),
                    side:
                        const BorderSide(
                      color: Color(
                        0xFFDC2626,
                      ),
                    ),
                    padding:
                        const EdgeInsets.symmetric(
                      horizontal: 12,
                    ),
                    minimumSize:
                        const Size(0, 42),
                    shape:
                        RoundedRectangleBorder(
                      borderRadius:
                          BorderRadius.circular(
                        10,
                      ),
                    ),
                  ),
                  child: const Text(
                    'Tolak',
                    style: TextStyle(
                      fontWeight:
                          FontWeight.w800,
                      fontSize: 12,
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child:
                      OutlinedButton.icon(
                    onPressed:
                        _processingOfferId ==
                                null
                            ? () =>
                                _showCounterOfferModal(
                                  offer,
                                )
                            : null,
                    style:
                        OutlinedButton.styleFrom(
                      foregroundColor:
                          primary,
                      side:
                          const BorderSide(
                        color: primary,
                        width: 1.4,
                      ),
                      minimumSize:
                          const Size(0, 42),
                      shape:
                          RoundedRectangleBorder(
                        borderRadius:
                            BorderRadius.circular(
                          10,
                        ),
                      ),
                    ),
                    icon: const Icon(
                      Icons.handshake_outlined,
                      size: 16,
                    ),
                    label: const Text(
                      'Nego Ulang',
                      style: TextStyle(
                        fontWeight:
                            FontWeight.w800,
                        fontSize: 12,
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: FilledButton(
                    onPressed:
                        _processingOfferId ==
                                null
                            ? () =>
                                _confirmAction(
                                  offer,
                                  'accepted',
                                )
                            : null,
                    style:
                        FilledButton.styleFrom(
                      backgroundColor:
                          primary,
                      minimumSize:
                          const Size(0, 42),
                      shape:
                          RoundedRectangleBorder(
                        borderRadius:
                            BorderRadius.circular(
                          10,
                        ),
                      ),
                    ),
                    child: isProcessing
                        ? const SizedBox(
                            width: 16,
                            height: 16,
                            child:
                                CircularProgressIndicator(
                              strokeWidth: 2,
                              color:
                                  Colors.white,
                            ),
                          )
                        : const Text(
                            'Terima',
                            style: TextStyle(
                              fontWeight:
                                  FontWeight.w900,
                              fontSize: 12,
                            ),
                          ),
                  ),
                ),
              ],
            ),
          ],
          if (offer.isActive &&
              canBuyerAct) ...[
            const SizedBox(height: 16),
            SizedBox(
              width: double.infinity,
              height: 44,
              child: FilledButton.icon(
                onPressed:
                    _processingOfferId == null
                        ? () =>
                            _showCounterOfferModal(
                              offer,
                            )
                        : null,
                style: FilledButton.styleFrom(
                  backgroundColor: primary,
                  shape:
                      RoundedRectangleBorder(
                    borderRadius:
                        BorderRadius.circular(10),
                  ),
                ),
                icon: isProcessing
                    ? const SizedBox(
                        width: 16,
                        height: 16,
                        child:
                            CircularProgressIndicator(
                          strokeWidth: 2,
                          color:
                              Colors.white,
                        ),
                      )
                    : const Icon(
                        Icons
                            .local_offer_rounded,
                        size: 17,
                      ),
                label: const Text(
                  'Offer Berikutnya',
                  style: TextStyle(
                    fontSize: 12.5,
                    fontWeight:
                        FontWeight.w900,
                  ),
                ),
              ),
            ),
          ],
          if (isAccepted) ...[
            const SizedBox(height: 14),
            Container(
              width: double.infinity,
              padding:
                  const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color:
                    const Color(0xFFF0FDF4),
                borderRadius:
                    BorderRadius.circular(10),
                border: Border.all(
                  color:
                      const Color(0xFFA7F3D0),
                ),
              ),
              child: Column(
                crossAxisAlignment:
                    CrossAxisAlignment.start,
                children: [
                  const Row(
                    children: [
                      Icon(
                        Icons
                            .check_circle_rounded,
                        color: primary,
                        size: 16,
                      ),
                      SizedBox(width: 6),
                      Expanded(
                        child: Text(
                          'Penawaran Disetujui & Kontrak Sah',
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight:
                                FontWeight.w800,
                            color:
                                primaryDark,
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 10),
                  Row(
                    children: [
                      Expanded(
                        child:
                            OutlinedButton.icon(
                          onPressed: () {
                            context.push(
                              '/faktur/${offer.id}',
                            );
                          },
                          style:
                              OutlinedButton
                                  .styleFrom(
                            foregroundColor:
                                primary,
                            side:
                                const BorderSide(
                              color: primary,
                            ),
                            minimumSize:
                                const Size(
                              0,
                              36,
                            ),
                            shape:
                                RoundedRectangleBorder(
                              borderRadius:
                                  BorderRadius
                                      .circular(
                                8,
                              ),
                            ),
                          ),
                          icon: const Icon(
                            Icons
                                .receipt_rounded,
                            size: 15,
                          ),
                          label: const Text(
                            'Buka Faktur',
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight:
                                  FontWeight
                                      .w800,
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Expanded(
                        child:
                            FilledButton.icon(
                          onPressed: () =>
                              _openWhatsApp(
                            offer,
                          ),
                          style:
                              FilledButton
                                  .styleFrom(
                            backgroundColor:
                                primary,
                            minimumSize:
                                const Size(
                              0,
                              36,
                            ),
                            shape:
                                RoundedRectangleBorder(
                              borderRadius:
                                  BorderRadius
                                      .circular(
                                8,
                              ),
                            ),
                          ),
                          icon: const Icon(
                            Icons.chat_rounded,
                            size: 15,
                          ),
                          label: const Text(
                            'WhatsApp',
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight:
                                  FontWeight
                                      .w800,
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
          if (isRejected) ...[
            const SizedBox(height: 12),
            Container(
              width: double.infinity,
              padding:
                  const EdgeInsets.symmetric(
                horizontal: 12,
                vertical: 8,
              ),
              decoration: BoxDecoration(
                color:
                    const Color(0xFFFEF2F2),
                borderRadius:
                    BorderRadius.circular(8),
              ),
              child: const Row(
                children: [
                  Icon(
                    Icons.cancel_rounded,
                    color:
                        Color(0xFFDC2626),
                    size: 15,
                  ),
                  SizedBox(width: 6),
                  Expanded(
                    child: Text(
                      'Penawaran ini telah ditolak.',
                      style: TextStyle(
                        fontSize: 11.5,
                        color:
                            Color(0xFFDC2626),
                        fontWeight:
                            FontWeight.w700,
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

  Widget _buildTurnInformation({
    required MarketOfferModel offer,
    required bool isBuyer,
    required bool isCurrentUserTurn,
  }) {
    if (isCurrentUserTurn) {
      return Container(
        width: double.infinity,
        padding: const EdgeInsets.all(11),
        decoration: BoxDecoration(
          color: const Color(0xFFF0FDF4),
          borderRadius:
              BorderRadius.circular(10),
          border: Border.all(
            color: const Color(0xFFA7F3D0),
          ),
        ),
        child: Row(
          children: [
            const Icon(
              Icons.touch_app_rounded,
              size: 17,
              color: primary,
            ),
            const SizedBox(width: 8),
            Expanded(
              child: Text(
                isBuyer
                    ? 'Giliran Anda. Silakan kirim Offer Berikutnya kepada petani.'
                    : 'Giliran Anda. Silakan pilih Tolak, Nego Ulang, atau Terima.',
                style: const TextStyle(
                  fontSize: 11.5,
                  fontWeight: FontWeight.w700,
                  color: primaryDark,
                  height: 1.35,
                ),
              ),
            ),
          ],
        ),
      );
    }

    final waitingForCurrentUser =
        isBuyer
            ? offer.waitingForBuyer
            : offer.waitingForFarmer;

    if (waitingForCurrentUser) {
      return Container(
        width: double.infinity,
        padding: const EdgeInsets.all(11),
        decoration: BoxDecoration(
          color: const Color(0xFFEFF6FF),
          borderRadius:
              BorderRadius.circular(10),
          border: Border.all(
            color: const Color(0xFFBFDBFE),
          ),
        ),
        child: Row(
          children: [
            const Icon(
              Icons.hourglass_top_rounded,
              size: 17,
              color: Color(0xFF2563EB),
            ),
            const SizedBox(width: 8),
            Expanded(
              child: Text(
                isBuyer
                    ? 'Menunggu respons petani.'
                    : 'Menunggu respons pembeli.',
                style: const TextStyle(
                  fontSize: 11.5,
                  fontWeight: FontWeight.w700,
                  color: Color(0xFF1D4ED8),
                  height: 1.35,
                ),
              ),
            ),
          ],
        ),
      );
    }

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(11),
      decoration: BoxDecoration(
        color: const Color(0xFFFFFBEB),
        borderRadius:
            BorderRadius.circular(10),
        border: Border.all(
          color: const Color(0xFFFDE68A),
        ),
      ),
      child: Row(
        children: [
          const Icon(
            Icons.info_outline_rounded,
            size: 17,
            color: Color(0xFFD97706),
          ),
          const SizedBox(width: 8),
          Expanded(
            child: Text(
              isBuyer
                  ? 'Menunggu giliran offer berikutnya.'
                  : 'Menunggu giliran respons.',
              style: const TextStyle(
                fontSize: 11.5,
                fontWeight: FontWeight.w700,
                color: Color(0xFFB45309),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatusBadge(
    String status,
  ) {
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
      padding:
          const EdgeInsets.symmetric(
        horizontal: 8,
        vertical: 3,
      ),
      decoration: BoxDecoration(
        color: bg,
        borderRadius:
            BorderRadius.circular(6),
      ),
      child: Text(
        label,
        style: TextStyle(
          fontSize: 9.5,
          fontWeight:
              FontWeight.w900,
          color: fg,
        ),
      ),
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
      crossAxisAlignment:
          CrossAxisAlignment.start,
      children: [
        Icon(
          icon,
          size: 16,
          color: textMuted,
        ),
        const SizedBox(width: 8),
        Expanded(
          child: Text(
            label,
            style: const TextStyle(
              fontSize: 12,
              color: textMuted,
            ),
          ),
        ),
        const SizedBox(width: 10),
        Flexible(
          child: Text(
            value,
            textAlign: TextAlign.right,
            style: TextStyle(
              fontSize: 12.5,
              fontWeight: isBold
                  ? FontWeight.w900
                  : FontWeight.w700,
              color:
                  valueColor ?? textDark,
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildEmpty(bool isBuyer) {
    return Center(
      child: Padding(
        padding:
            const EdgeInsets.all(32),
        child: Column(
          mainAxisAlignment:
              MainAxisAlignment.center,
          children: [
            const Icon(
              Icons.handshake_outlined,
              size: 54,
              color: Color(0xFF94A3B8),
            ),
            const SizedBox(height: 14),
            Text(
              isBuyer
                  ? 'Belum Ada Penawaran'
                  : 'Belum Ada Penawaran Masuk',
              textAlign: TextAlign.center,
              style: const TextStyle(
                fontSize: 16,
                fontWeight:
                    FontWeight.w800,
                color: textDark,
              ),
            ),
            const SizedBox(height: 6),
            Text(
              isBuyer
                  ? 'Penawaran yang Anda kirim ke petani akan muncul di sini.'
                  : 'Penawaran harga dari pembeli bursa akan muncul di sini. Anda dapat langsung menyetujui atau mengajukan nego tawar-balik.',
              textAlign: TextAlign.center,
              style: const TextStyle(
                fontSize: 12.5,
                color: textMuted,
                height: 1.4,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildError() {
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
              _error ??
                  'Terjadi kesalahan',
              textAlign:
                  TextAlign.center,
              style: const TextStyle(
                fontSize: 13,
                color: textDark,
              ),
            ),
            const SizedBox(height: 16),
            FilledButton(
              onPressed: _loadOffers,
              style:
                  FilledButton.styleFrom(
                backgroundColor: primary,
                shape:
                    RoundedRectangleBorder(
                  borderRadius:
                      BorderRadius.circular(
                    10,
                  ),
                ),
              ),
              child:
                  const Text('Coba Lagi'),
            ),
          ],
        ),
      ),
    );
  }
}