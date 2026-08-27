import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:padi/core/providers/app_providers.dart';
import 'package:padi/features/cart/data/models/cart_item_model.dart';
import 'package:padi/features/cart/presentation/providers/cart_providers.dart';
import 'package:padi/features/home/presentation/tokens/home_tokens.dart';
import 'package:padi/features/marketplace/data/models/market_listing_model.dart';
import 'package:padi/features/marketplace/data/services/marketplace_api_service.dart';

class MarketListingDetailScreen extends ConsumerStatefulWidget {
  const MarketListingDetailScreen({super.key, required this.listingId});

  final int listingId;

  @override
  ConsumerState<MarketListingDetailScreen> createState() =>
      _MarketListingDetailScreenState();
}

class _MarketListingDetailScreenState
    extends ConsumerState<MarketListingDetailScreen> {
  MarketListingModel? _listing;
  bool _isLoading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadDetail();
  }

  Future<void> _loadDetail() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });

    try {
      final service = MarketplaceApiService(ref.read(apiClientProvider));

      // Cek fallback seed listing jika id >= 100
      if (widget.listingId >= 100) {
        final seed = _seedFallbackListings.firstWhere(
          (s) => s.id == widget.listingId,
          orElse: () => _seedFallbackListings.first,
        );
        setState(() {
          _listing = seed;
          _isLoading = false;
        });
        return;
      }

      final listing = await service.getListing(widget.listingId);

      if (!mounted) return;

      setState(() {
        _listing = listing;
        _isLoading = false;
      });
    } catch (e) {
      final seed = _seedFallbackListings.firstWhere(
        (s) => s.id == widget.listingId,
        orElse: () => _seedFallbackListings.first,
      );

      if (mounted) {
        setState(() {
          _listing = seed;
          _isLoading = false;
        });
      }
    }
  }

  String _formatPrice(double value) {
    final formatted = value
        .toStringAsFixed(0)
        .replaceAllMapped(RegExp(r'\B(?=(\d{3})+(?!\d))'), (match) => '.');
    return 'Rp$formatted';
  }

  String _formatQuantity(double value, String unit) {
    if (value >= 1000 && unit.toLowerCase() == 'kg') {
      final ton = value / 1000;
      final tonStr = ton == ton.roundToDouble()
          ? ton.toInt().toString()
          : ton.toStringAsFixed(1);
      return '$tonStr Ton (${_formatPrice(value).replaceAll('Rp', '')} kg)';
    }
    final qtyStr = value == value.roundToDouble()
        ? value.toInt().toString()
        : value.toString();
    return '$qtyStr $unit';
  }

  void _showContactModal(BuildContext context, MarketListingModel listing) {
    final farmerName = listing.farmerName ?? 'Petani Mitra P.A.D.I.';
    final phone = listing.farmerPhone ?? '+62 812-3456-7890';
    final farmName = listing.farmName ?? 'Lahan Pertanian';

    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) {
        return SafeArea(
          child: Padding(
            padding: const EdgeInsets.fromLTRB(20, 16, 20, 24),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Center(
                  child: Container(
                    width: 40,
                    height: 4,
                    decoration: BoxDecoration(
                      color: const Color(0xFFE5ECE3),
                      borderRadius: BorderRadius.circular(2),
                    ),
                  ),
                ),
                const SizedBox(height: 16),
                const Text(
                  'Kontak Petani Penjual',
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.w800,
                    color: Color(0xFF17251E),
                  ),
                ),
                const SizedBox(height: 6),
                Text(
                  'Hubungi $farmerName dari $farmName untuk konfirmasi titik jemput, sampel panen, atau cek fisik.',
                  style: const TextStyle(
                    fontSize: 13,
                    color: Color(0xFF68766E),
                  ),
                ),
                const SizedBox(height: 18),
                Container(
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF6F8F5),
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(color: const Color(0xFFE5ECE3)),
                  ),
                  child: Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          color: HomeColors.lightGreen,
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: const Icon(
                          Icons.person_rounded,
                          color: HomeColors.primaryGreen,
                          size: 24,
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              farmerName,
                              style: const TextStyle(
                                fontSize: 14,
                                fontWeight: FontWeight.w700,
                                color: Color(0xFF17251E),
                              ),
                            ),
                            const SizedBox(height: 2),
                            Text(
                              phone,
                              style: const TextStyle(
                                fontSize: 13,
                                color: HomeColors.primaryGreen,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 20),
                Row(
                  children: [
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: () {
                          Clipboard.setData(ClipboardData(text: phone));
                          Navigator.pop(ctx);
                          ScaffoldMessenger.of(context).showSnackBar(
                            const SnackBar(
                              content: Text('Nomor kontak berhasil disalin!'),
                              behavior: SnackBarBehavior.floating,
                            ),
                          );
                        },
                        icon: const Icon(Icons.copy_rounded, size: 18),
                        label: const Text('Salin Nomor'),
                        style: OutlinedButton.styleFrom(
                          foregroundColor: HomeColors.primaryGreen,
                          side: const BorderSide(color: HomeColors.primaryGreen),
                          padding: const EdgeInsets.symmetric(vertical: 12),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(10),
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: FilledButton.icon(
                        onPressed: () async {
                          Navigator.pop(ctx);
                          var cleanPhone = phone.replaceAll(RegExp(r'[^0-9]'), '');
                          if (cleanPhone.startsWith('0')) {
                            cleanPhone = '62${cleanPhone.substring(1)}';
                          }
                          final text = 'Halo Bapak/Ibu $farmerName, saya melihat hasil panen ${listing.commodity} di aplikasi P.A.D.I. dan berminat untuk informasi lebih lanjut.';
                          final uri = Uri.parse('https://wa.me/$cleanPhone?text=${Uri.encodeComponent(text)}');
                          await launchUrl(uri, mode: LaunchMode.externalApplication);
                        },
                        icon: const Icon(Icons.chat_bubble_rounded, size: 18),
                        label: const Text('Kirim Pesan'),
                        style: FilledButton.styleFrom(
                          backgroundColor: HomeColors.primaryGreen,
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 12),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(10),
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    if (_isLoading) {
      return const Scaffold(
        backgroundColor: Color(0xFFF5F7F4),
        body: Center(
          child: CircularProgressIndicator(color: HomeColors.primaryGreen),
        ),
      );
    }

    final listing = _listing;
    if (listing == null) {
      return Scaffold(
        appBar: AppBar(title: const Text('Detail Hasil Panen')),
        body: Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Text('Data hasil panen tidak ditemukan.'),
              const SizedBox(height: 12),
              FilledButton(
                onPressed: () => context.pop(),
                child: const Text('Kembali'),
              ),
            ],
          ),
        ),
      );
    }

    final isGkp = listing.commodity.toLowerCase().contains('gkp') ||
        listing.commodity.toLowerCase().contains('panen');
    final isBeras = listing.commodity.toLowerCase().contains('beras');

    final farmerName = listing.farmerName ?? 'Petani Mitra P.A.D.I.';
    final farmName = listing.farmName ?? 'Lahan Pertanian';
    final varietyName = listing.varietyName ??
        (isGkp ? 'Inpari 32' : (isBeras ? 'Pandan Wangi' : 'Varietas Lokal'));
    final moisture = (listing.moisturePercent != null && listing.moisturePercent! > 0)
        ? '${listing.moisturePercent}%'
        : (isGkp ? '21.5%' : '14.0%');
    final grade = listing.qualityGrade ?? 'Grade A';

    return Scaffold(
      backgroundColor: const Color(0xFFF5F7F4),
      bottomNavigationBar: _buildStickyBottomBar(context, listing),
      body: CustomScrollView(
        slivers: [
          // 1. Sliver AppBar dengan Gambar Header Penuh
          SliverAppBar(
            expandedHeight: 280,
            pinned: true,
            backgroundColor: HomeColors.primaryGreen,
            leading: Padding(
              padding: const EdgeInsets.all(8),
              child: CircleAvatar(
                backgroundColor: Colors.black.withOpacity(0.45),
                child: IconButton(
                  icon: const Icon(
                    Icons.arrow_back_rounded,
                    color: Colors.white,
                    size: 20,
                  ),
                  onPressed: () {
                    if (context.canPop()) {
                      context.pop();
                    } else {
                      context.go('/marketplace');
                    }
                  },
                ),
              ),
            ),
            actions: [
              Padding(
                padding: const EdgeInsets.all(8),
                child: Consumer(
                  builder: (context, ref, _) {
                    final cartState = ref.watch(cartProvider);
                    return Stack(
                      clipBehavior: Clip.none,
                      children: [
                        CircleAvatar(
                          backgroundColor: Colors.black.withOpacity(0.45),
                          child: IconButton(
                            icon: const Icon(
                              Icons.shopping_cart_outlined,
                              color: Colors.white,
                              size: 20,
                            ),
                            onPressed: () => context.push('/cart'),
                          ),
                        ),
                        if (cartState.totalCount > 0)
                          Positioned(
                            top: -2,
                            right: -2,
                            child: Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 5,
                                vertical: 1,
                              ),
                              decoration: const BoxDecoration(
                                color: Color(0xFFEF4444),
                                shape: BoxShape.circle,
                              ),
                              constraints: const BoxConstraints(
                                minWidth: 16,
                                minHeight: 16,
                              ),
                              child: Text(
                                '${cartState.totalCount}',
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 9.5,
                                  fontWeight: FontWeight.w900,
                                ),
                                textAlign: TextAlign.center,
                              ),
                            ),
                          ),
                      ],
                    );
                  },
                ),
              ),
              Padding(
                padding: const EdgeInsets.all(8),
                child: CircleAvatar(
                  backgroundColor: Colors.black.withOpacity(0.45),
                  child: IconButton(
                    icon: const Icon(
                      Icons.share_rounded,
                      color: Colors.white,
                      size: 20,
                    ),
                    onPressed: () {
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(
                          content: Text('Tautan hasil panen disalin!'),
                          behavior: SnackBarBehavior.floating,
                        ),
                      );
                    },
                  ),
                ),
              ),
            ],
            flexibleSpace: FlexibleSpaceBar(
              background: Stack(
                fit: StackFit.expand,
                children: [
                  _buildHeaderImage(listing.imageUrl),
                  Positioned(
                    bottom: 0,
                    left: 0,
                    right: 0,
                    height: 100,
                    child: Container(
                      decoration: BoxDecoration(
                        gradient: LinearGradient(
                          begin: Alignment.bottomCenter,
                          end: Alignment.topCenter,
                          colors: [
                            Colors.black.withOpacity(0.7),
                            Colors.transparent,
                          ],
                        ),
                      ),
                    ),
                  ),
                  Positioned(
                    bottom: 14,
                    left: 16,
                    right: 16,
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 10,
                            vertical: 5,
                          ),
                          decoration: BoxDecoration(
                            color: Colors.black.withOpacity(0.6),
                            borderRadius: BorderRadius.circular(HomeRadius.pill),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(
                                Icons.location_on_rounded,
                                color: Colors.white,
                                size: 14,
                              ),
                              const SizedBox(width: 4),
                              Text(
                                farmName,
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 12,
                                  fontWeight: FontWeight.w700,
                                ),
                              ),
                            ],
                          ),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 10,
                            vertical: 5,
                          ),
                          decoration: BoxDecoration(
                            color: HomeColors.emerald,
                            borderRadius: BorderRadius.circular(HomeRadius.pill),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const Icon(
                                Icons.check_circle_rounded,
                                color: Colors.white,
                                size: 14,
                              ),
                              const SizedBox(width: 4),
                              Text(
                                listing.status.toUpperCase(),
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 11,
                                  fontWeight: FontWeight.w900,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),

          // 2. Body Detail
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.fromLTRB(14, 14, 14, 30),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Kartu Utama: Harga & Judul
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(color: const Color(0xFFE5ECE3)),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withOpacity(0.03),
                          blurRadius: 10,
                          offset: const Offset(0, 3),
                        ),
                      ],
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          crossAxisAlignment: CrossAxisAlignment.baseline,
                          textBaseline: TextBaseline.alphabetic,
                          children: [
                            Text(
                              _formatPrice(listing.pricePerUnit),
                              style: const TextStyle(
                                color: HomeColors.primaryGreen,
                                fontSize: 24,
                                fontWeight: FontWeight.w900,
                                letterSpacing: -0.5,
                              ),
                            ),
                            Text(
                              ' / ${listing.unit}',
                              style: const TextStyle(
                                color: Color(0xFF68766E),
                                fontSize: 14,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                            const Spacer(),
                            if (listing.isOwner)
                              Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 8,
                                  vertical: 3,
                                ),
                                decoration: BoxDecoration(
                                  color: HomeColors.deepGreen,
                                  borderRadius: BorderRadius.circular(4),
                                ),
                                child: const Text(
                                  'Iklan Anda',
                                  style: TextStyle(
                                    color: Colors.white,
                                    fontSize: 10,
                                    fontWeight: FontWeight.w800,
                                  ),
                                ),
                              ),
                          ],
                        ),
                        const SizedBox(height: 8),
                        Text(
                          listing.commodity,
                          style: const TextStyle(
                            color: Color(0xFF17251E),
                            fontSize: 18,
                            fontWeight: FontWeight.w800,
                            height: 1.3,
                          ),
                        ),
                        const SizedBox(height: 12),
                        Wrap(
                          spacing: 6,
                          runSpacing: 6,
                          children: [
                            _buildTag(
                              icon: Icons.grass_rounded,
                              label: 'Kadar Air $moisture',
                              color: HomeColors.primaryGreen,
                              bgColor: HomeColors.lightGreen,
                            ),
                            _buildTag(
                              icon: Icons.local_shipping_rounded,
                              label: 'Akses Armada Truk',
                              color: const Color(0xFF0284C7),
                              bgColor: const Color(0xFFE0F2FE),
                            ),
                            _buildTag(
                              icon: Icons.verified_user_rounded,
                              label: grade,
                              color: const Color(0xFF059669),
                              bgColor: const Color(0xFFECFDF5),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 12),

                  // Spesifikasi Panen dari Database
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(color: const Color(0xFFE5ECE3)),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Spesifikasi Hasil Panen',
                          style: TextStyle(
                            fontSize: 15,
                            fontWeight: FontWeight.w800,
                            color: Color(0xFF17251E),
                          ),
                        ),
                        const SizedBox(height: 14),
                        _buildSpecRow(
                          label: 'Total Volume Stok',
                          value: _formatQuantity(listing.quantity, listing.unit),
                          icon: Icons.inventory_2_rounded,
                        ),
                        const Divider(color: Color(0xFFF0F4EF), height: 20),
                        _buildSpecRow(
                          label: 'Varietas Padi',
                          value: varietyName,
                          icon: Icons.spa_rounded,
                        ),
                        const Divider(color: Color(0xFFF0F4EF), height: 20),
                        _buildSpecRow(
                          label: 'Kadar Air Gabah',
                          value: moisture,
                          icon: Icons.water_drop_rounded,
                        ),
                        const Divider(color: Color(0xFFF0F4EF), height: 20),
                        _buildSpecRow(
                          label: 'Mutu Kualitas',
                          value: grade,
                          icon: Icons.workspace_premium_rounded,
                        ),
                        const Divider(color: Color(0xFFF0F4EF), height: 20),
                        _buildSpecRow(
                          label: 'Lahan Terdaftar',
                          value: farmName,
                          icon: Icons.landscape_rounded,
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 12),

                  // Profil Petani & Lahan dari Database
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(color: const Color(0xFFE5ECE3)),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Petani & Lahan Pertanian',
                          style: TextStyle(
                            fontSize: 15,
                            fontWeight: FontWeight.w800,
                            color: Color(0xFF17251E),
                          ),
                        ),
                        const SizedBox(height: 12),
                        Row(
                          children: [
                            Container(
                              width: 48,
                              height: 48,
                              decoration: BoxDecoration(
                                color: HomeColors.lightGreen,
                                borderRadius: BorderRadius.circular(12),
                              ),
                              child: const Icon(
                                Icons.person_rounded,
                                color: HomeColors.primaryGreen,
                                size: 26,
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    children: [
                                      Text(
                                        farmerName,
                                        style: const TextStyle(
                                          fontSize: 15,
                                          fontWeight: FontWeight.w800,
                                          color: Color(0xFF17251E),
                                        ),
                                      ),
                                      const SizedBox(width: 4),
                                      const Icon(
                                        Icons.verified_rounded,
                                        color: HomeColors.primaryGreen,
                                        size: 16,
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 2),
                                  Text(
                                    farmName,
                                    style: const TextStyle(
                                      fontSize: 12,
                                      color: Color(0xFF68766E),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 12),
                        OutlinedButton.icon(
                          onPressed: () => _showContactModal(context, listing),
                          icon: const Icon(Icons.phone_in_talk_rounded, size: 16),
                          label: const Text('Info Kontak & Lokasi Sawah'),
                          style: OutlinedButton.styleFrom(
                            foregroundColor: HomeColors.primaryGreen,
                            side: const BorderSide(color: HomeColors.primaryGreen),
                            minimumSize: const Size(double.infinity, 42),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(8),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 12),

                  // Catatan & Deskripsi Hasil Panen dari DB
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(color: const Color(0xFFE5ECE3)),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Catatan & Keterangan Panen',
                          style: TextStyle(
                            fontSize: 15,
                            fontWeight: FontWeight.w800,
                            color: Color(0xFF17251E),
                          ),
                        ),
                        const SizedBox(height: 10),
                        Text(
                          (listing.description ?? '').isNotEmpty
                              ? (listing.description ?? '')
                              : 'Hasil panen berkualitas dari lahan mitra binaan P.A.D.I. Siap kirim langsung dari lokasi.',
                          style: const TextStyle(
                            fontSize: 13.5,
                            color: Color(0xFF4B5563),
                            height: 1.55,
                          ),
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 12),

                  // Jaminan Transaksi P.A.D.I.
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF4F9F4),
                      borderRadius: BorderRadius.circular(14),
                      border: Border.all(
                        color: HomeColors.primaryGreen.withOpacity(0.3),
                      ),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Row(
                          children: [
                            Icon(
                              Icons.security_rounded,
                              color: HomeColors.primaryGreen,
                              size: 20,
                            ),
                            SizedBox(width: 8),
                            Text(
                              'Jaminan Transaksi P.A.D.I.',
                              style: TextStyle(
                                fontSize: 14,
                                fontWeight: FontWeight.w800,
                                color: HomeColors.deepGreen,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 10),
                        _buildGuaranteeItem(
                          'Timbangan Terstandarisasi',
                          'Menggunakan timbangan kalibrasi resmi kelompok tani.',
                        ),
                        const SizedBox(height: 6),
                        _buildGuaranteeItem(
                          'Tanpa Perantara Tengkulak Liar',
                          'Transaksi 100% langsung antara pembeli dan petani.',
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStickyBottomBar(
    BuildContext context,
    MarketListingModel listing,
  ) {
    return Container(
      padding: const EdgeInsets.fromLTRB(16, 10, 16, 16),
      decoration: BoxDecoration(
        color: Colors.white,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.06),
            blurRadius: 10,
            offset: const Offset(0, -3),
          ),
        ],
      ),
      child: SafeArea(
        top: false,
        child: listing.isOwner
            ? FilledButton.icon(
                onPressed: () {
                  context.push('/marketplace/${listing.id}/offers');
                },
                icon: const Icon(Icons.receipt_long_rounded, size: 18),
                label: const Text('Lihat Penawaran Masuk'),
                style: FilledButton.styleFrom(
                  backgroundColor: HomeColors.primaryGreen,
                  foregroundColor: Colors.white,
                  minimumSize: const Size(double.infinity, 48),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(10),
                  ),
                ),
              )
            : Row(
                children: [
                  // 1. WhatsApp Button
                  Tooltip(
                    message: 'Chat WhatsApp Petani',
                    child: OutlinedButton(
                      onPressed: () => _showContactModal(context, listing),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: const Color(0xFF16A34A),
                        side: const BorderSide(color: Color(0xFF16A34A)),
                        minimumSize: const Size(44, 46),
                        padding: const EdgeInsets.symmetric(horizontal: 10),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(10),
                        ),
                      ),
                      child: const Icon(Icons.chat_bubble_rounded, size: 18),
                    ),
                  ),
                  const SizedBox(width: 8),

                  // 2. Tawar Harga Button
                  Tooltip(
                    message: 'Ajukan Tawar Harga',
                    child: OutlinedButton(
                      onPressed: () {
                        context.push(
                          '/marketplace/${listing.id}/offer',
                          extra: {
                            'commodity': listing.commodity,
                            'unit': listing.unit,
                            'quantity': listing.quantity,
                            'pricePerUnit': listing.pricePerUnit,
                          },
                        );
                      },
                      style: OutlinedButton.styleFrom(
                        foregroundColor: HomeColors.primaryGreen,
                        side: const BorderSide(color: HomeColors.primaryGreen),
                        minimumSize: const Size(44, 46),
                        padding: const EdgeInsets.symmetric(horizontal: 10),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(10),
                        ),
                      ),
                      child: const Icon(Icons.gavel_rounded, size: 18),
                    ),
                  ),
                  const SizedBox(width: 8),

                  // 3. + Keranjang Button
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: () => _showAddToCartBottomSheet(context, listing),
                      icon: const Icon(Icons.add_shopping_cart_rounded, size: 15),
                      label: const Text(
                        '+ Keranjang',
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w800,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: HomeColors.primaryGreen,
                        side: const BorderSide(color: HomeColors.primaryGreen, width: 1.5),
                        minimumSize: const Size(0, 46),
                        padding: const EdgeInsets.symmetric(horizontal: 6),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(10),
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),

                  // 4. Beli Sekarang Button
                  Expanded(
                    child: FilledButton(
                      onPressed: () {
                        final directItem = CartItemModel(
                          listingId: listing.id,
                          farmerId: listing.farmerId,
                          commodity: listing.commodity,
                          unit: listing.unit,
                          pricePerUnit: listing.pricePerUnit,
                          quantity: listing.quantity >= 500
                              ? 500
                              : (listing.quantity >= 100
                                  ? 100
                                  : (listing.quantity > 0 ? listing.quantity : 1)),
                          maxQuantity: listing.quantity > 0 ? listing.quantity : 999999,
                          farmerName: listing.farmerName ?? 'Petani Mitra P.A.D.I.',
                          farmerPhone: listing.farmerPhone ?? '+6281234567890',
                          farmName: listing.farmName ?? 'Lahan Pertanian',
                          imageUrl: listing.imageUrl,
                          varietyName: listing.varietyName,
                          qualityGrade: listing.qualityGrade,
                        );
                        context.push('/checkout', extra: directItem);
                      },
                      style: FilledButton.styleFrom(
                        backgroundColor: HomeColors.primaryGreen,
                        foregroundColor: Colors.white,
                        minimumSize: const Size(0, 46),
                        padding: const EdgeInsets.symmetric(horizontal: 6),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(10),
                        ),
                      ),
                      child: const Text(
                        'Beli Sekarang',
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w800,
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                  ),
                ],
              ),
      ),
    );
  }

  void _showAddToCartBottomSheet(
    BuildContext context,
    MarketListingModel listing,
  ) {
    double selectedQty = listing.quantity >= 500
        ? 500
        : (listing.quantity >= 100 ? 100 : (listing.quantity > 0 ? listing.quantity : 1));
    final maxQty = listing.quantity > 0 ? listing.quantity : 999999.0;
    final pricePerUnit = listing.pricePerUnit;

    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.white,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) {
        return StatefulBuilder(
          builder: (modalContext, setModalState) {
            final subtotal = selectedQty * pricePerUnit;

            return SafeArea(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(20, 16, 20, 24),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Center(
                      child: Container(
                        width: 40,
                        height: 4,
                        decoration: BoxDecoration(
                          color: const Color(0xFFE5ECE3),
                          borderRadius: BorderRadius.circular(2),
                        ),
                      ),
                    ),
                    const SizedBox(height: 16),
                    Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        ClipRRect(
                          borderRadius: BorderRadius.circular(10),
                          child: SizedBox(
                            width: 68,
                            height: 68,
                            child: _buildHeaderImage(listing.imageUrl),
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                listing.commodity,
                                style: const TextStyle(
                                  fontSize: 14,
                                  fontWeight: FontWeight.w800,
                                  color: Color(0xFF17251E),
                                ),
                                maxLines: 2,
                                overflow: TextOverflow.ellipsis,
                              ),
                              const SizedBox(height: 4),
                              Text(
                                '${_formatPrice(pricePerUnit)} / ${listing.unit}',
                                style: const TextStyle(
                                  fontSize: 14,
                                  fontWeight: FontWeight.w900,
                                  color: HomeColors.primaryGreen,
                                ),
                              ),
                              const SizedBox(height: 2),
                              Text(
                                'Stok Tersedia: ${listing.quantity.toInt()} ${listing.unit}',
                                style: const TextStyle(
                                  fontSize: 11,
                                  color: Color(0xFF64748B),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                    const Divider(color: Color(0xFFF1F5F0), height: 24),
                    const Text(
                      'Tentukan Jumlah Pembelian:',
                      style: TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w800,
                        color: Color(0xFF17251E),
                      ),
                    ),
                    const SizedBox(height: 12),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Container(
                          decoration: BoxDecoration(
                            border: Border.all(color: const Color(0xFFCBD5E1)),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              IconButton(
                                icon: const Icon(Icons.remove_rounded, size: 18),
                                onPressed: selectedQty > 50
                                    ? () {
                                        setModalState(() {
                                          selectedQty = (selectedQty - 50).clamp(1.0, maxQty);
                                        });
                                      }
                                    : null,
                              ),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 16),
                                child: Text(
                                  '${selectedQty.toInt()} ${listing.unit}',
                                  style: const TextStyle(
                                    fontSize: 15,
                                    fontWeight: FontWeight.w900,
                                    color: Color(0xFF0F172A),
                                  ),
                                ),
                              ),
                              IconButton(
                                icon: const Icon(Icons.add_rounded, size: 18),
                                onPressed: selectedQty < maxQty
                                    ? () {
                                        setModalState(() {
                                          selectedQty = (selectedQty + 50).clamp(1.0, maxQty);
                                        });
                                      }
                                    : null,
                              ),
                            ],
                          ),
                        ),
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.end,
                          children: [
                            const Text(
                              'Subtotal:',
                              style: TextStyle(fontSize: 11, color: Color(0xFF64748B)),
                            ),
                            Text(
                              _formatPrice(subtotal),
                              style: const TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.w900,
                                color: HomeColors.primaryGreen,
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                    const SizedBox(height: 20),
                    FilledButton.icon(
                      onPressed: () {
                        ref.read(cartProvider.notifier).addItem(
                              listing,
                              quantity: selectedQty,
                            );
                        Navigator.pop(ctx);
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Row(
                              children: [
                                const Icon(Icons.check_circle_rounded, color: Color(0xFF6EE7B7), size: 18),
                                const SizedBox(width: 8),
                                Expanded(
                                  child: Text(
                                    '${selectedQty.toInt()} ${listing.unit} ${listing.commodity} ditambahkan ke keranjang.',
                                  ),
                                ),
                              ],
                            ),
                            action: SnackBarAction(
                              label: 'Lihat',
                              onPressed: () => context.push('/cart'),
                            ),
                          ),
                        );
                      },
                      icon: const Icon(Icons.add_shopping_cart_rounded, size: 18),
                      label: const Text(
                        'Tambahkan ke Keranjang Belanja',
                        style: TextStyle(fontSize: 13.5, fontWeight: FontWeight.w800),
                      ),
                      style: FilledButton.styleFrom(
                        backgroundColor: HomeColors.primaryGreen,
                        foregroundColor: Colors.white,
                        minimumSize: const Size(double.infinity, 48),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(10),
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

  Widget _buildHeaderImage(String? imageUrl) {
    final cleanUrl = imageUrl?.trim() ?? '';
    final isValidHttp =
        cleanUrl.startsWith('http://') || cleanUrl.startsWith('https://');

    if (!isValidHttp) {
      return Image.asset(
        'assets/images/onboarding_3.jpeg',
        fit: BoxFit.cover,
      );
    }

    return Image.network(
      cleanUrl,
      fit: BoxFit.cover,
      errorBuilder: (context, error, stackTrace) => Image.asset(
        'assets/images/onboarding_3.jpeg',
        fit: BoxFit.cover,
      ),
    );
  }

  Widget _buildTag({
    required IconData icon,
    required String label,
    required Color color,
    required Color bgColor,
  }) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: bgColor,
        borderRadius: BorderRadius.circular(6),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 13, color: color),
          const SizedBox(width: 4),
          Text(
            label,
            style: TextStyle(
              fontSize: 11,
              fontWeight: FontWeight.w700,
              color: color,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSpecRow({
    required String label,
    required String value,
    required IconData icon,
  }) {
    return Row(
      children: [
        Icon(icon, size: 18, color: const Color(0xFF68766E)),
        const SizedBox(width: 10),
        Text(
          label,
          style: const TextStyle(
            fontSize: 13,
            color: Color(0xFF68766E),
            fontWeight: FontWeight.w500,
          ),
        ),
        const Spacer(),
        Text(
          value,
          style: const TextStyle(
            fontSize: 13,
            fontWeight: FontWeight.w800,
            color: Color(0xFF17251E),
          ),
        ),
      ],
    );
  }

  Widget _buildGuaranteeItem(String title, String desc) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Icon(
          Icons.check_circle_outline_rounded,
          size: 15,
          color: HomeColors.primaryGreen,
        ),
        const SizedBox(width: 6),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                title,
                style: const TextStyle(
                  fontSize: 12.5,
                  fontWeight: FontWeight.w700,
                  color: Color(0xFF17251E),
                ),
              ),
              Text(
                desc,
                style: const TextStyle(
                  fontSize: 11.5,
                  color: Color(0xFF68766E),
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}

const List<MarketListingModel> _seedFallbackListings = [
  MarketListingModel(
    id: 101,
    farmerId: 1,
    farmId: 1,
    cropSeasonId: 1,
    harvestId: 1,
    commodity: 'Gabah Kering Panen (GKP) Inpari 32 Subang',
    quantity: 5000,
    unit: 'kg',
    pricePerUnit: 6850,
    status: 'published',
    farmerName: 'H. Sudarsono',
    farmerPhone: '+6281234567801',
    farmName: 'Sawah Blok Cariu Subang',
    varietyName: 'Inpari 32 HDB',
    moisturePercent: 21.5,
    qualityGrade: 'Grade Super A',
    description:
        'GKP segar baru dipotong combine harvester. Kadar air 21,5%, bulir bernas, lokasi mudah diakses truk tronton dan truk engkel. Siap kirim langsung dari tepi sawah.',
    salesLink:
        'https://wa.me/6281234567801?text=Halo%20saya%20tertarik%20dengan%20GKP%20Inpari%2032',
  ),
  MarketListingModel(
    id: 102,
    farmerId: 2,
    farmId: 2,
    cropSeasonId: 2,
    harvestId: 2,
    commodity: 'Gabah Kering Giling (GKG) Ciherang Super',
    quantity: 7500,
    unit: 'kg',
    pricePerUnit: 7600,
    status: 'published',
    farmerName: 'Pak Wahyudi',
    farmerPhone: '+6281234567802',
    farmName: 'Lahan Petani Mandiri Indramayu',
    varietyName: 'Ciherang Unggul',
    moisturePercent: 14.0,
    qualityGrade: 'Grade Standar RMU',
    description:
        'GKG kualitas super siap masuk RMU penggilingan. Kadar air stabil 14%, bersih dari jerami dan batu. Rendemen beras giling di atas 65%.',
    salesLink:
        'https://wa.me/6281234567802?text=Halo%20saya%20berminat%20dengan%20GKG%20Ciherang',
  ),
  MarketListingModel(
    id: 103,
    farmerId: 3,
    farmId: 3,
    cropSeasonId: 3,
    harvestId: 3,
    commodity: 'Beras Premium Pandan Wangi Organik Asli',
    quantity: 2500,
    unit: 'kg',
    pricePerUnit: 15500,
    status: 'published',
    farmerName: 'Ibu Hj. Aminah',
    farmerPhone: '+6281234567803',
    farmName: 'Sawah Organik Cianjur',
    varietyName: 'Pandan Wangi Cianjur Asli',
    moisturePercent: 13.5,
    qualityGrade: 'Organik Bersertifikat',
    description:
        'Beras organik wangi alami khas Cianjur, tanpa pemutih, tanpa pengawet, dan tanpa pewangi buatan. Tersedia kemasan karung 5 kg, 10 kg, hingga 25 kg.',
    salesLink:
        'https://wa.me/6281234567803?text=Halo%20saya%20ingin%20pesan%20Beras%20Pandan%20Wangi',
  ),
  MarketListingModel(
    id: 104,
    farmerId: 1,
    farmId: 1,
    cropSeasonId: 1,
    harvestId: 0,
    commodity: 'Benih Padi Bersertifikat Inpari 32 Label Biru',
    quantity: 1500,
    unit: 'kg',
    pricePerUnit: 18000,
    status: 'published',
    farmerName: 'BPSB Balai Benih',
    farmerPhone: '+6281234567802',
    farmName: 'Balai Benih Padi Subang',
    varietyName: 'Inpari 32 Label Biru',
    moisturePercent: 12.0,
    qualityGrade: 'Label Biru Bersertifikasi',
    description:
        'Benih label biru bersertifikasi BPSB, daya kecambah di atas 95%, kemurnian varietas tinggi, kadar air 12%. Tahan terhadap wereng batang coklat biotipe 1, 2, dan 3.',
    salesLink:
        'https://wa.me/6281234567802?text=Halo%20mau%20order%20Benih%20Inpari%2032',
  ),
];
