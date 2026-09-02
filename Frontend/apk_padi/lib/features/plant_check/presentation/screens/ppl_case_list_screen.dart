import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:padi/core/providers/app_providers.dart';
import 'package:padi/features/plant_check/data/services/plant_check_api_service.dart';

final pplValidationsProvider = FutureProvider.autoDispose<List<Map<String, dynamic>>>((ref) async {
  final service = ref.read(plantCheckApiServiceProvider);
  return await service.fetchPplValidations();
});

class PplCaseListScreen extends ConsumerStatefulWidget {
  const PplCaseListScreen({super.key});

  @override
  ConsumerState<PplCaseListScreen> createState() => _PplCaseListScreenState();
}

class _PplCaseListScreenState extends ConsumerState<PplCaseListScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;

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

  @override
  Widget build(BuildContext context) {
    final validationsAsync = ref.watch(pplValidationsProvider);

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_rounded, color: Color(0xFF0F172A)),
          onPressed: () => context.pop(),
        ),
        title: const Text(
          'Validasi Kasus Lapangan PPL',
          style: TextStyle(
            color: Color(0xFF0F172A),
            fontSize: 16,
            fontWeight: FontWeight.w800,
          ),
        ),
        bottom: TabBar(
          controller: _tabController,
          indicatorColor: const Color(0xFF0284C7),
          indicatorWeight: 3,
          labelColor: const Color(0xFF0284C7),
          unselectedLabelColor: const Color(0xFF64748B),
          labelStyle: const TextStyle(fontSize: 13, fontWeight: FontWeight.w800),
          unselectedLabelStyle: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600),
          tabs: const [
            Tab(text: 'Menunggu Validasi'),
            Tab(text: 'Riwayat Selesai'),
          ],
        ),
      ),
      body: validationsAsync.when(
        data: (list) {
          final pendingList = list.where((item) => item['status'] == 'pending').toList();
          final historyList = list.where((item) => item['status'] != 'pending').toList();

          return TabBarView(
            controller: _tabController,
            children: [
              _buildCaseList(pendingList, isPending: true),
              _buildCaseList(historyList, isPending: false),
            ],
          );
        },
        loading: () => const Center(
          child: CircularProgressIndicator(color: Color(0xFF0284C7)),
        ),
        error: (error, _) => Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.error_outline_rounded, size: 40, color: Color(0xFF94A3B8)),
              const SizedBox(height: 10),
              Text(
                'Gagal memuat data kasus: $error',
                style: const TextStyle(color: Color(0xFF64748B), fontSize: 13),
              ),
              const SizedBox(height: 12),
              FilledButton(
                onPressed: () => ref.refresh(pplValidationsProvider),
                style: FilledButton.styleFrom(backgroundColor: const Color(0xFF0284C7)),
                child: const Text('Coba Lagi'),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildCaseList(List<Map<String, dynamic>> items, {required bool isPending}) {
    if (items.isEmpty) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(32),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(
                isPending ? Icons.task_alt_rounded : Icons.history_rounded,
                size: 54,
                color: const Color(0xFFCBD5E1),
              ),
              const SizedBox(height: 14),
              Text(
                isPending ? 'Tidak ada antrean kasus baru' : 'Belum ada riwayat validasi',
                style: const TextStyle(
                  fontSize: 15,
                  fontWeight: FontWeight.bold,
                  color: Color(0xFF334155),
                ),
              ),
              const SizedBox(height: 6),
              Text(
                isPending
                    ? 'Semua laporan diagnosa petani telah divalidasi oleh petugas.'
                    : 'Hasil verifikasi lapangan yang telah selesai akan tercatat di sini.',
                textAlign: TextAlign.center,
                style: const TextStyle(fontSize: 12, color: Color(0xFF64748B)),
              ),
            ],
          ),
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: () async => ref.refresh(pplValidationsProvider.future),
      color: const Color(0xFF0284C7),
      child: ListView.separated(
        padding: const EdgeInsets.all(16),
        itemCount: items.length,
        separatorBuilder: (_, _) => const SizedBox(height: 12),
        itemBuilder: (context, index) {
          final item = items[index];
          final scan = item['scan'] as Map<String, dynamic>? ?? {};
          final farmer = scan['farmer'] as Map<String, dynamic>? ?? {};
          final farm = scan['farm'] as Map<String, dynamic>? ?? {};
          final disease = scan['predicted_class']?.toString() ?? 'Penyakit Tanaman';
          final confidence = scan['confidence'] != null
              ? '${((double.tryParse(scan['confidence'].toString()) ?? 0) * 100).toStringAsFixed(1)}%'
              : '-';
          final imageUrl = scan['image_url']?.toString();
          final status = item['status']?.toString() ?? 'pending';

          return _buildCaseCard(
            context,
            item: item,
            farmerName: farmer['name']?.toString() ?? 'Petani',
            farmName: farm['name']?.toString() ?? 'Lahan Petani',
            disease: disease,
            confidence: confidence,
            imageUrl: imageUrl,
            status: status,
          );
        },
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
        statusColor = const Color(0xFF059669);
        statusBg = const Color(0xFFECFDF5);
        statusLabel = 'Divalidasi';
        break;
      case 'rejected':
        statusColor = const Color(0xFFDC2626);
        statusBg = const Color(0xFFFEF2F2);
        statusLabel = 'Tidak Terkonfirmasi';
        break;
      case 'needs_revisit':
        statusColor = const Color(0xFFD97706);
        statusBg = const Color(0xFFFFFBEB);
        statusLabel = 'Perlu Tinjauan Ulang';
        break;
      case 'pending':
      default:
        statusColor = const Color(0xFF0284C7);
        statusBg = const Color(0xFFF0F9FF);
        statusLabel = 'Menunggu Validasi';
        break;
    }

    return InkWell(
      onTap: () async {
        final updated = await context.push('/ppl-cases/detail', extra: item);
        if (updated == true) {
          ref.refresh(pplValidationsProvider);
        }
      },
      borderRadius: BorderRadius.circular(16),
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(color: const Color(0xFFE2E8F0)),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.03),
              blurRadius: 10,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Thumbnail Foto Daun
            ClipRRect(
              borderRadius: BorderRadius.circular(12),
              child: Container(
                width: 70,
                height: 70,
                color: const Color(0xFFF1F5F9),
                child: imageUrl != null && imageUrl.isNotEmpty
                    ? Image.network(
                        imageUrl,
                        fit: BoxFit.cover,
                        errorBuilder: (_, _, _) => const Icon(Icons.image_not_supported_rounded, color: Color(0xFF94A3B8)),
                      )
                    : const Icon(Icons.eco_rounded, color: Color(0xFF059669), size: 30),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 3),
                        decoration: BoxDecoration(
                          color: statusBg,
                          borderRadius: BorderRadius.circular(6),
                          border: Border.all(color: statusColor.withValues(alpha: 0.3)),
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
                      const Spacer(),
                      Text(
                        'AI: $confidence',
                        style: const TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w700,
                          color: Color(0xFF64748B),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 6),
                  Text(
                    disease,
                    style: const TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w800,
                      color: Color(0xFF0F172A),
                    ),
                  ),
                  const SizedBox(height: 3),
                  Text(
                    '$farmerName • $farmName',
                    style: const TextStyle(
                      fontSize: 11.5,
                      color: Color(0xFF475569),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(width: 6),
            const Icon(Icons.chevron_right_rounded, color: Color(0xFF94A3B8), size: 20),
          ],
        ),
      ),
    );
  }
}
