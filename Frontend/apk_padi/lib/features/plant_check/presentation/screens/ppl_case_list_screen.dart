import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:padi/core/utils/debouncer.dart';
import 'package:padi/features/plant_check/data/services/plant_check_api_service.dart';

final pplValidationsProvider =
    FutureProvider.autoDispose<List<Map<String, dynamic>>>((ref) async {
      final service = ref.read(plantCheckApiServiceProvider);
      return await service.fetchPplValidations();
    });

class _PplPalette {
  static const field = Color(0xFFF6FBF7);
  static const surface = Colors.white;
  static const ink = Color(0xFF102017);
  static const muted = Color(0xFF607267);
  static const green = Color(0xFF047857);
  static const greenStrong = Color(0xFF065F46);
  static const greenSoft = Color(0xFFEAF7EF);
  static const border = Color(0xFFDCEBE2);
}

class PplCaseListScreen extends ConsumerStatefulWidget {
  const PplCaseListScreen({super.key});

  @override
  ConsumerState<PplCaseListScreen> createState() => _PplCaseListScreenState();
}

class _PplCaseListScreenState extends ConsumerState<PplCaseListScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;
  final TextEditingController _searchController = TextEditingController();
  final Debouncer _searchDebouncer = Debouncer(milliseconds: 300);

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
  }

  @override
  void dispose() {
    _searchDebouncer.dispose();
    _searchController.dispose();
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final validationsAsync = ref.watch(pplValidationsProvider);

    return Scaffold(
      backgroundColor: _PplPalette.field,
      appBar: AppBar(
        backgroundColor: _PplPalette.surface,
        surfaceTintColor: _PplPalette.surface,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_rounded, color: _PplPalette.ink),
          onPressed: () => context.pop(),
        ),
        title: const Text(
          'Validasi Lapangan PPL',
          style: TextStyle(
            color: _PplPalette.ink,
            fontSize: 16,
            fontWeight: FontWeight.w900,
          ),
        ),
        bottom: TabBar(
          controller: _tabController,
          indicatorColor: _PplPalette.green,
          indicatorWeight: 3,
          labelColor: _PplPalette.green,
          unselectedLabelColor: _PplPalette.muted,
          dividerColor: _PplPalette.border,
          labelStyle: const TextStyle(
            fontSize: 13,
            fontWeight: FontWeight.w900,
          ),
          unselectedLabelStyle: const TextStyle(
            fontSize: 13,
            fontWeight: FontWeight.w700,
          ),
          tabs: const [
            Tab(text: 'Menunggu Validasi'),
            Tab(text: 'Riwayat Selesai'),
          ],
        ),
      ),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 14, 16, 10),
            child: _buildSearchField(),
          ),
          Expanded(
            child: validationsAsync.when(
              data: _buildTabs,
              loading: () => const Center(
                child: CircularProgressIndicator(color: _PplPalette.green),
              ),
              error: (error, _) => _buildErrorState(error),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSearchField() {
    return Container(
      decoration: BoxDecoration(
        color: _PplPalette.surface,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: _PplPalette.border),
        boxShadow: [
          BoxShadow(
            color: _PplPalette.green.withValues(alpha: 0.05),
            blurRadius: 16,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: TextField(
        controller: _searchController,
        onChanged: (_) => _searchDebouncer.run(() => setState(() {})),
        style: const TextStyle(
          fontSize: 13,
          fontWeight: FontWeight.w700,
          color: _PplPalette.ink,
        ),
        decoration: InputDecoration(
          filled: true,
          fillColor: _PplPalette.surface,
          hintText: 'Cari petani, sawah, atau penyakit',
          hintStyle: const TextStyle(
            fontSize: 13,
            color: _PplPalette.muted,
            fontWeight: FontWeight.w600,
          ),
          prefixIcon: const Icon(
            Icons.search_rounded,
            size: 22,
            color: _PplPalette.green,
          ),
          suffixIcon: _searchController.text.isNotEmpty
              ? IconButton(
                  icon: const Icon(
                    Icons.cancel_rounded,
                    size: 18,
                    color: _PplPalette.muted,
                  ),
                  onPressed: () {
                    _searchController.clear();
                    setState(() {});
                  },
                )
              : null,
          border: InputBorder.none,
          enabledBorder: InputBorder.none,
          focusedBorder: InputBorder.none,
          contentPadding: const EdgeInsets.symmetric(
            horizontal: 12,
            vertical: 13,
          ),
        ),
      ),
    );
  }

  Widget _buildTabs(List<Map<String, dynamic>> list) {
    final keyword = _searchController.text.trim().toLowerCase();
    var filtered = list;

    if (keyword.isNotEmpty) {
      filtered = list.where((item) {
        final scan = item['scan'] as Map<String, dynamic>? ?? {};
        final farmer = scan['farmer'] as Map<String, dynamic>? ?? {};
        final farm = scan['farm'] as Map<String, dynamic>? ?? {};
        final disease = (scan['predicted_class']?.toString() ?? '')
            .toLowerCase();
        final farmerName = (farmer['name']?.toString() ?? '').toLowerCase();
        final farmName = (farm['name']?.toString() ?? '').toLowerCase();
        final notes = (item['notes']?.toString() ?? '').toLowerCase();

        return disease.contains(keyword) ||
            farmerName.contains(keyword) ||
            farmName.contains(keyword) ||
            notes.contains(keyword);
      }).toList();
    }

    final pendingList = filtered
        .where((item) => item['status'] == 'pending')
        .toList();
    final historyList = filtered
        .where((item) => item['status'] != 'pending')
        .toList();

    return TabBarView(
      controller: _tabController,
      children: [
        _buildCaseList(pendingList, isPending: true),
        _buildCaseList(historyList, isPending: false),
      ],
    );
  }

  Widget _buildErrorState(Object error) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(
              Icons.wifi_off_rounded,
              size: 42,
              color: _PplPalette.green,
            ),
            const SizedBox(height: 10),
            Text(
              'Gagal memuat data kasus: $error',
              textAlign: TextAlign.center,
              style: const TextStyle(
                color: _PplPalette.muted,
                fontSize: 13,
                height: 1.35,
              ),
            ),
            const SizedBox(height: 12),
            FilledButton(
              onPressed: () => ref.refresh(pplValidationsProvider),
              style: FilledButton.styleFrom(backgroundColor: _PplPalette.green),
              child: const Text('Coba Lagi'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildCaseList(
    List<Map<String, dynamic>> items, {
    required bool isPending,
  }) {
    if (items.isEmpty) {
      return _buildEmptyState(isPending);
    }

    return RefreshIndicator(
      onRefresh: () async => ref.refresh(pplValidationsProvider.future),
      color: _PplPalette.green,
      child: ListView(
        padding: const EdgeInsets.fromLTRB(16, 2, 16, 20),
        children: [
          _buildListSummary(isPending: isPending, totalCount: items.length),
          const SizedBox(height: 12),
          ...List.generate(items.length, (index) {
            final item = items[index];
            final scan = item['scan'] as Map<String, dynamic>? ?? {};
            final farmer = scan['farmer'] as Map<String, dynamic>? ?? {};
            final farm = scan['farm'] as Map<String, dynamic>? ?? {};
            final disease =
                scan['predicted_class']?.toString() ?? 'Penyakit Tanaman';
            final confidence = scan['confidence'] != null
                ? '${((double.tryParse(scan['confidence'].toString()) ?? 0) * 100).toStringAsFixed(1)}%'
                : '-';
            final imageUrl = scan['image_url']?.toString();
            final status = item['status']?.toString() ?? 'pending';

            return Padding(
              padding: EdgeInsets.only(
                bottom: index == items.length - 1 ? 0 : 12,
              ),
              child: _buildCaseCard(
                context,
                item: item,
                farmerName: farmer['name']?.toString() ?? 'Petani',
                farmName: farm['name']?.toString() ?? 'Lahan Petani',
                disease: disease,
                confidence: confidence,
                imageUrl: imageUrl,
                status: status,
              ),
            );
          }),
        ],
      ),
    );
  }

  Widget _buildEmptyState(bool isPending) {
    return RefreshIndicator(
      onRefresh: () async => ref.refresh(pplValidationsProvider.future),
      color: _PplPalette.green,
      child: LayoutBuilder(
        builder: (context, constraints) => SingleChildScrollView(
          physics: const AlwaysScrollableScrollPhysics(),
          child: ConstrainedBox(
            constraints: BoxConstraints(minHeight: constraints.maxHeight),
            child: Center(
              child: Padding(
                padding: const EdgeInsets.all(32),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      isPending
                          ? Icons.task_alt_rounded
                          : Icons.history_rounded,
                      size: 54,
                      color: _PplPalette.green.withValues(alpha: 0.55),
                    ),
                    const SizedBox(height: 14),
                    Text(
                      isPending
                          ? 'Tidak ada antrean kasus baru'
                          : 'Belum ada riwayat validasi',
                      textAlign: TextAlign.center,
                      style: const TextStyle(
                        fontSize: 15,
                        fontWeight: FontWeight.w900,
                        color: _PplPalette.ink,
                      ),
                    ),
                    const SizedBox(height: 6),
                    Text(
                      isPending
                          ? 'Semua laporan diagnosa petani telah divalidasi oleh petugas.'
                          : 'Hasil verifikasi lapangan yang telah selesai akan tercatat di sini.',
                      textAlign: TextAlign.center,
                      style: const TextStyle(
                        fontSize: 12,
                        color: _PplPalette.muted,
                        height: 1.35,
                      ),
                    ),
                    const SizedBox(height: 16),
                    TextButton.icon(
                      onPressed: () => ref.refresh(pplValidationsProvider),
                      icon: const Icon(Icons.refresh_rounded, size: 16),
                      label: const Text('Muat Ulang'),
                      style: TextButton.styleFrom(
                        foregroundColor: _PplPalette.green,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildListSummary({required bool isPending, required int totalCount}) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: BoxDecoration(
        color: _PplPalette.greenSoft,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: _PplPalette.border),
      ),
      child: Row(
        children: [
          Container(
            width: 36,
            height: 36,
            decoration: const BoxDecoration(
              color: _PplPalette.surface,
              shape: BoxShape.circle,
            ),
            child: Icon(
              isPending ? Icons.fact_check_outlined : Icons.verified_outlined,
              color: _PplPalette.green,
              size: 20,
            ),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              isPending
                  ? '$totalCount kasus menunggu verifikasi lapangan'
                  : '$totalCount kasus sudah selesai diverifikasi',
              style: const TextStyle(
                color: _PplPalette.ink,
                fontSize: 13,
                fontWeight: FontWeight.w800,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildCaseCard(
    BuildContext context, {
    required Map<String, dynamic> item,
    required String farmerName,
    required String farmName,
    required String disease,
    required String confidence,
    required String? imageUrl,
    required String status,
  }) {
    final Color statusColor;
    final Color statusBg;
    final String statusLabel;

    switch (status) {
      case 'validated':
        statusColor = const Color(0xFF047857);
        statusBg = const Color(0xFFEAF7EF);
        statusLabel = 'Divalidasi';
        break;
      case 'rejected':
        statusColor = const Color(0xFF065F46);
        statusBg = const Color(0xFFEAF7EF);
        statusLabel = 'Tidak Sesuai';
        break;
      case 'needs_revisit':
        statusColor = const Color(0xFF065F46);
        statusBg = const Color(0xFFEAF7EF);
        statusLabel = 'Tinjauan Ulang';
        break;
      case 'pending':
      default:
        statusColor = const Color(0xFF047857);
        statusBg = const Color(0xFFEAF7EF);
        statusLabel = 'Menunggu Petugas';
        break;
    }

    return InkWell(
      onTap: () async {
        final updated = await context.push('/ppl-cases/detail', extra: item);
        if (updated == true) {
          ref.invalidate(pplValidationsProvider);
        }
      },
      borderRadius: BorderRadius.circular(14),
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: _PplPalette.surface,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: _PplPalette.border),
          boxShadow: [
            BoxShadow(
              color: _PplPalette.green.withValues(alpha: 0.06),
              blurRadius: 18,
              offset: const Offset(0, 8),
            ),
          ],
        ),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            ClipRRect(
              borderRadius: BorderRadius.circular(12),
              child: Container(
                width: 70,
                height: 70,
                color: _PplPalette.greenSoft,
                child: imageUrl != null && imageUrl.isNotEmpty
                    ? Image.network(
                        imageUrl,
                        fit: BoxFit.cover,
                        errorBuilder: (_, _, _) => const Icon(
                          Icons.eco_outlined,
                          color: _PplPalette.green,
                        ),
                      )
                    : const Icon(
                        Icons.eco_rounded,
                        color: _PplPalette.green,
                        size: 30,
                      ),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Flexible(
                        child: Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 8,
                            vertical: 4,
                          ),
                          decoration: BoxDecoration(
                            color: statusBg,
                            borderRadius: BorderRadius.circular(8),
                            border: Border.all(
                              color: statusColor.withValues(alpha: 0.25),
                            ),
                          ),
                          child: Text(
                            statusLabel,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: TextStyle(
                              fontSize: 10.5,
                              fontWeight: FontWeight.w900,
                              color: statusColor,
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Text(
                        'Keyakinan $confidence',
                        style: const TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w800,
                          color: _PplPalette.muted,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Text(
                    disease,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w900,
                      color: _PplPalette.ink,
                    ),
                  ),
                  const SizedBox(height: 5),
                  _buildMetaLine(
                    icon: Icons.person_outline_rounded,
                    text: farmerName,
                  ),
                  const SizedBox(height: 3),
                  _buildMetaLine(
                    icon: Icons.grass_rounded,
                    text: farmName,
                    iconColor: _PplPalette.green,
                  ),
                  if (item['ppl'] is Map &&
                      (item['ppl'] as Map)['name'] != null &&
                      (item['ppl'] as Map)['name'].toString().isNotEmpty) ...[
                    const SizedBox(height: 3),
                    _buildMetaLine(
                      icon: Icons.badge_outlined,
                      text: 'PPL/Admin: ${(item['ppl'] as Map)['name']}',
                      iconColor: _PplPalette.green,
                    ),
                  ],
                  if (item['notes'] != null &&
                      item['notes'].toString().trim().isNotEmpty) ...[
                    const SizedBox(height: 6),
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 8,
                        vertical: 4,
                      ),
                      decoration: BoxDecoration(
                        color: const Color(0xFFF1F5F9),
                        borderRadius: BorderRadius.circular(6),
                        border: Border.all(color: const Color(0xFFE2E8F0)),
                      ),
                      child: Row(
                        children: [
                          const Icon(
                            Icons.rate_review_outlined,
                            size: 12,
                            color: Color(0xFF64748B),
                          ),
                          const SizedBox(width: 5),
                          Expanded(
                            child: Text(
                              item['notes'].toString().trim(),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: const TextStyle(
                                fontSize: 11,
                                color: Color(0xFF475569),
                                fontStyle: FontStyle.italic,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ],
              ),
            ),
            const SizedBox(width: 6),
            const Icon(
              Icons.chevron_right_rounded,
              color: _PplPalette.green,
              size: 20,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildMetaLine({
    required IconData icon,
    required String text,
    Color iconColor = _PplPalette.muted,
  }) {
    return Row(
      children: [
        Icon(icon, size: 14, color: iconColor),
        const SizedBox(width: 4),
        Expanded(
          child: Text(
            text,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: const TextStyle(
              fontSize: 11.5,
              color: _PplPalette.muted,
              fontWeight: FontWeight.w700,
            ),
          ),
        ),
      ],
    );
  }
}
