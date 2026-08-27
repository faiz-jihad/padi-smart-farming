import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import 'package:url_launcher/url_launcher.dart';
import 'package:padi/core/providers/app_providers.dart';
import 'package:padi/features/home/presentation/tokens/home_tokens.dart';
import 'package:padi/features/marketplace/data/models/market_offer_model.dart';
import 'package:padi/features/marketplace/data/models/purchase_contract_model.dart';
import 'package:padi/features/marketplace/data/services/marketplace_api_service.dart';

final buyerContractsProvider =
    FutureProvider.autoDispose<List<PurchaseContractModel>>((ref) async {
  final service = MarketplaceApiService(ref.read(apiClientProvider));
  try {
    return await service.fetchContracts();
  } catch (_) {
    return const [];
  }
});

final buyerOffersProvider =
    FutureProvider.autoDispose<List<MarketOfferModel>>((ref) async {
  final service = MarketplaceApiService(ref.read(apiClientProvider));
  try {
    return await service.fetchMyOffers();
  } catch (_) {
    return const [];
  }
});

class BuyerOrdersScreen extends ConsumerStatefulWidget {
  const BuyerOrdersScreen({super.key});

  @override
  ConsumerState<BuyerOrdersScreen> createState() => _BuyerOrdersScreenState();
}

class _BuyerOrdersScreenState extends ConsumerState<BuyerOrdersScreen>
    with SingleTickerProviderStateMixin {
  late final TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  String _formatPrice(double value) {
    final currencyFmt = NumberFormat.currency(
      locale: 'id_ID',
      symbol: 'Rp',
      decimalDigits: 0,
    );
    return currencyFmt.format(value.round());
  }

  Future<void> _chatFarmerWhatsApp(
    String? phone,
    String farmerName,
    String commodity,
    int contractId,
  ) async {
    final rawPhone = phone?.trim() ?? '';
    if (rawPhone.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Nomor WhatsApp petani tidak tersedia.')),
      );
      return;
    }

    var digits = rawPhone.replaceAll(RegExp(r'[^0-9]'), '');
    if (digits.startsWith('0')) {
      digits = '62${digits.substring(1)}';
    }

    final message = '''
Halo Bapak/Ibu $farmerName,

Saya ingin berkoordinasi terkait Kontrak Pesanan Panen #KTR-$contractId pada komoditas *$commodity*.

Mohon update informasi penimbangan dan jadwal armada angkut. Terima kasih!
''';

    final uri = Uri.parse(
      'https://wa.me/$digits?text=${Uri.encodeComponent(message)}',
    );

    final launched = await launchUrl(uri, mode: LaunchMode.externalApplication);
    if (!launched && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Tidak dapat membuka aplikasi WhatsApp.')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF6F8F5),
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        scrolledUnderElevation: 0,
        leading: context.canPop()
            ? IconButton(
                icon: const Icon(
                  Icons.arrow_back_rounded,
                  color: Color(0xFF17251E),
                ),
                onPressed: () => context.pop(),
              )
            : null,
        title: const Text(
          'Pesanan & Kontrak Saya',
          style: TextStyle(
            fontSize: 17,
            fontWeight: FontWeight.w800,
            color: Color(0xFF17251E),
          ),
        ),
        actions: [
          IconButton(
            tooltip: 'Segarkan',
            icon: const Icon(
              Icons.refresh_rounded,
              color: HomeColors.primaryGreen,
            ),
            onPressed: () {
              ref.invalidate(buyerContractsProvider);
              ref.invalidate(buyerOffersProvider);
            },
          ),
        ],
        bottom: TabBar(
          controller: _tabController,
          indicatorColor: HomeColors.primaryGreen,
          indicatorWeight: 3,
          labelColor: HomeColors.primaryGreen,
          unselectedLabelColor: const Color(0xFF64748B),
          labelStyle: const TextStyle(
            fontWeight: FontWeight.w800,
            fontSize: 13.5,
          ),
          tabs: const [
            Tab(text: 'Kontrak Pembelian'),
            Tab(text: 'Penawaran Harga'),
          ],
        ),
      ),
      body: TabBarView(
        controller: _tabController,
        children: [
          _buildContractsTab(),
          _buildOffersTab(),
        ],
      ),
    );
  }

  Widget _buildContractsTab() {
    final contractsAsync = ref.watch(buyerContractsProvider);

    return RefreshIndicator(
      color: HomeColors.primaryGreen,
      onRefresh: () async => ref.invalidate(buyerContractsProvider),
      child: contractsAsync.when(
        data: (contracts) {
          if (contracts.isEmpty) {
            return _buildEmptyTab(
              icon: Icons.receipt_long_rounded,
              title: 'Belum Ada Kontrak Pembelian',
              subtitle:
                  'Setelah Anda melakukan checkout atau pembelian di bursa panen, kontrak resmi akan muncul di sini.',
              actionLabel: 'Belanja di Bursa Panen',
              onAction: () => context.go('/marketplace'),
            );
          }

          return ListView.builder(
            padding: const EdgeInsets.fromLTRB(14, 14, 14, 120),
            itemCount: contracts.length,
            itemBuilder: (context, index) {
              final contract = contracts[index];
              return _buildContractCard(contract);
            },
          );
        },
        loading: () => const Center(
          child: CircularProgressIndicator(color: HomeColors.primaryGreen),
        ),
        error: (err, stack) => _buildEmptyTab(
          icon: Icons.error_outline_rounded,
          title: 'Gagal Memuat Kontrak',
          subtitle: 'Periksa koneksi jaringan atau coba beberapa saat lagi.',
          actionLabel: 'Coba Lagi',
          onAction: () => ref.invalidate(buyerContractsProvider),
        ),
      ),
    );
  }

  Widget _buildContractCard(PurchaseContractModel contract) {
    final status = contract.status.toLowerCase();
    final isActive = status == 'active';
    final isCompleted = status == 'completed';

    final statusColor = isActive
        ? const Color(0xFF059669)
        : (isCompleted ? const Color(0xFF0284C7) : const Color(0xFF64748B));
    final statusBg = isActive
        ? const Color(0xFFECFDF5)
        : (isCompleted ? const Color(0xFFE0F2FE) : const Color(0xFFF1F5F9));
    final statusLabel = isActive
        ? 'Aktif & Berjalan'
        : (isCompleted ? 'Selesai' : contract.status.toUpperCase());

    final commodity = contract.commodity ?? 'Hasil Panen P.A.D.I.';
    final unit = contract.unit ?? 'kg';
    final farmerName = contract.farmerName ?? 'Petani Mitra P.A.D.I.';

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFE5ECE3)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.02),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Header Kartu: No Kontrak & Status
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(6),
                      decoration: BoxDecoration(
                        color: HomeColors.lightGreen,
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: const Icon(
                        Icons.description_rounded,
                        size: 14,
                        color: HomeColors.primaryGreen,
                      ),
                    ),
                    const SizedBox(width: 8),
                    Text(
                      'Kontrak #KTR-${contract.id}',
                      style: const TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w800,
                        color: Color(0xFF17251E),
                      ),
                    ),
                  ],
                ),
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                  decoration: BoxDecoration(
                    color: statusBg,
                    borderRadius: BorderRadius.circular(6),
                  ),
                  child: Text(
                    statusLabel,
                    style: TextStyle(
                      fontSize: 10.5,
                      fontWeight: FontWeight.w800,
                      color: statusColor,
                    ),
                  ),
                ),
              ],
            ),

            const Divider(color: Color(0xFFF1F5F0), height: 20),

            // Info Komoditas & Nilai
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                ClipRRect(
                  borderRadius: BorderRadius.circular(8),
                  child: SizedBox(
                    width: 60,
                    height: 60,
                    child: _buildItemImage(contract.imageUrl),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        commodity,
                        style: const TextStyle(
                          fontSize: 13.5,
                          fontWeight: FontWeight.w800,
                          color: Color(0xFF17251E),
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                      const SizedBox(height: 3),
                      Text(
                        'Petani: $farmerName',
                        style: const TextStyle(
                          fontSize: 12,
                          color: Color(0xFF64748B),
                        ),
                      ),
                      const SizedBox(height: 3),
                      Text(
                        'Volume: ${contract.quantity} $unit @ ${_formatPrice(contract.agreedPrice)}',
                        style: const TextStyle(
                          fontSize: 12,
                          color: Color(0xFF475569),
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),

            const SizedBox(height: 12),

            // Bar Total & Tombol WhatsApp Petani
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                color: const Color(0xFFF8FAFC),
                borderRadius: BorderRadius.circular(10),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'Total Nilai Kontrak:',
                        style: TextStyle(
                          fontSize: 11,
                          color: Color(0xFF64748B),
                        ),
                      ),
                      Text(
                        _formatPrice(contract.totalAmount),
                        style: const TextStyle(
                          fontSize: 15,
                          fontWeight: FontWeight.w900,
                          color: HomeColors.primaryGreen,
                        ),
                      ),
                    ],
                  ),
                  FilledButton.icon(
                    onPressed: () => _chatFarmerWhatsApp(
                      contract.farmerPhone,
                      farmerName,
                      commodity,
                      contract.id,
                    ),
                    icon: const Icon(
                      Icons.chat_rounded,
                      size: 16,
                      color: Colors.white,
                    ),
                    label: const Text(
                      'Hubungi Petani (WA)',
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                    style: FilledButton.styleFrom(
                      backgroundColor: const Color(0xFF16A34A),
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(
                        horizontal: 12,
                        vertical: 8,
                      ),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(8),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildOffersTab() {
    final offersAsync = ref.watch(buyerOffersProvider);

    return RefreshIndicator(
      color: HomeColors.primaryGreen,
      onRefresh: () async => ref.invalidate(buyerOffersProvider),
      child: offersAsync.when(
        data: (offers) {
          if (offers.isEmpty) {
            return _buildEmptyTab(
              icon: Icons.gavel_rounded,
              title: 'Belum Ada Penawaran Harga',
              subtitle:
                  'Ajukan penawaran harga terbaik pada hasil panen yang sedang tayang di bursa pasar.',
              actionLabel: 'Buka Bursa Pasar',
              onAction: () => context.go('/marketplace'),
            );
          }

          return ListView.builder(
            padding: const EdgeInsets.fromLTRB(14, 14, 14, 120),
            itemCount: offers.length,
            itemBuilder: (context, index) {
              final offer = offers[index];
              return _buildOfferCard(offer);
            },
          );
        },
        loading: () => const Center(
          child: CircularProgressIndicator(color: HomeColors.primaryGreen),
        ),
        error: (err, stack) => _buildEmptyTab(
          icon: Icons.error_outline_rounded,
          title: 'Gagal Memuat Penawaran',
          subtitle: 'Periksa koneksi jaringan atau coba beberapa saat lagi.',
          actionLabel: 'Coba Lagi',
          onAction: () => ref.invalidate(buyerOffersProvider),
        ),
      ),
    );
  }

  Widget _buildOfferCard(MarketOfferModel offer) {
    final status = offer.status.toLowerCase();
    final isPending = status == 'pending';
    final isAccepted = status == 'accepted';

    final statusColor = isAccepted
        ? const Color(0xFF059669)
        : (isPending ? const Color(0xFFD97706) : const Color(0xFFDC2626));
    final statusBg = isAccepted
        ? const Color(0xFFECFDF5)
        : (isPending ? const Color(0xFFFEF3C7) : const Color(0xFFFEE2E2));
    final statusLabel = isAccepted
        ? 'Diterima Petani'
        : (isPending ? 'Menunggu Tanggapan' : 'Ditolak');

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFE5ECE3)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Expanded(
                child: Text(
                  offer.commodity ?? 'Komoditas Panen P.A.D.I.',
                  style: const TextStyle(
                    fontSize: 13.5,
                    fontWeight: FontWeight.w800,
                    color: Color(0xFF17251E),
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ),
              const SizedBox(width: 8),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: statusBg,
                  borderRadius: BorderRadius.circular(6),
                ),
                child: Text(
                  statusLabel,
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w800,
                    color: statusColor,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Row(
            children: [
              Text(
                'Volume Tawar: ${offer.quantity} ${offer.unit ?? 'kg'}',
                style: const TextStyle(fontSize: 12, color: Color(0xFF64748B)),
              ),
              const SizedBox(width: 12),
              Text(
                'Harga Tawar: ${_formatPrice(offer.offeredPrice)} / ${offer.unit ?? 'kg'}',
                style: const TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w700,
                  color: HomeColors.primaryGreen,
                ),
              ),
            ],
          ),
          if (offer.message != null && offer.message!.isNotEmpty) ...[
            const SizedBox(height: 6),
            Text(
              'Pesan: "${offer.message}"',
              style: const TextStyle(
                fontSize: 11.5,
                fontStyle: FontStyle.italic,
                color: Color(0xFF475569),
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildEmptyTab({
    required IconData icon,
    required String title,
    required String subtitle,
    required String actionLabel,
    required VoidCallback onAction,
  }) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 32),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              width: 80,
              height: 80,
              decoration: BoxDecoration(
                color: HomeColors.lightGreen.withOpacity(0.6),
                shape: BoxShape.circle,
              ),
              child: Icon(icon, size: 38, color: HomeColors.primaryGreen),
            ),
            const SizedBox(height: 16),
            Text(
              title,
              style: const TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.w800,
                color: Color(0xFF17251E),
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 6),
            Text(
              subtitle,
              style: const TextStyle(
                fontSize: 12.5,
                color: Color(0xFF68766E),
                height: 1.4,
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 20),
            FilledButton(
              onPressed: onAction,
              style: FilledButton.styleFrom(
                backgroundColor: HomeColors.primaryGreen,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(10),
                ),
              ),
              child: Text(actionLabel),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildItemImage(String? imageUrl) {
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
}
