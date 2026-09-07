import 'dart:io';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:padi/core/config/app_config.dart';
import 'package:padi/core/errors/api_exception.dart';
import 'package:padi/core/providers/app_providers.dart';
import 'package:padi/features/auth/presentation/widgets/padi_theme.dart';
import 'package:padi/features/plant_check/data/services/plant_check_api_service.dart';
import 'package:padi/features/plant_check/presentation/screens/ppl_case_list_screen.dart';

class PplCaseDetailScreen extends ConsumerStatefulWidget {
  const PplCaseDetailScreen({super.key, required this.caseData});

  final Map<String, dynamic> caseData;

  @override
  ConsumerState<PplCaseDetailScreen> createState() =>
      _PplCaseDetailScreenState();
}

class _PplCaseDetailScreenState extends ConsumerState<PplCaseDetailScreen> {
  late Map<String, dynamic> _case;
  String? _selectedStatus;
  final TextEditingController _notesController = TextEditingController();
  bool _isSubmitting = false;
  bool _isLoadingDetail = false;

  int? get _validationId {
    final raw = _case['id'] ??
        _case['validation_id'] ??
        _case['ppl_validation_id'] ??
        (_case['ppl_validation'] is Map
            ? (_case['ppl_validation'] as Map)['id']
            : null) ??
        (_case['validation'] is Map
            ? (_case['validation'] as Map)['id']
            : null);
    if (raw is num) return raw.toInt();
    if (raw is String) return int.tryParse(raw.trim());
    return null;
  }

  @override
  void initState() {
    super.initState();
    _case = Map<String, dynamic>.from(widget.caseData);
    final currentStatus = _case['status']?.toString() ?? 'pending';
    _selectedStatus = currentStatus == 'pending' ? null : currentStatus;
    _notesController.text = _case['notes']?.toString() ?? '';

    // Muat detail lengkap jika payload awal tidak memuat data scan
    final valId = _validationId;
    if (_case['scan'] == null && valId != null) {
      _loadDetail(valId);
    }
  }

  @override
  void dispose() {
    _notesController.dispose();
    super.dispose();
  }

  Future<void> _loadDetail(int id) async {
    setState(() => _isLoadingDetail = true);
    try {
      final service = ref.read(plantCheckApiServiceProvider);
      final detail = await service.fetchPplValidationDetail(id);
      if (detail != null && mounted) {
        setState(() {
          _case = detail;
          final currentStatus = _case['status']?.toString() ?? 'pending';
          _selectedStatus = currentStatus == 'pending' ? null : currentStatus;
          if (_notesController.text.isEmpty) {
            _notesController.text = _case['notes']?.toString() ?? '';
          }
        });
      }
    } catch (_) {
    } finally {
      if (mounted) {
        setState(() => _isLoadingDetail = false);
      }
    }
  }

  Future<void> _handleSubmit() async {
    final validationId = _validationId;
    final selectedStatus = _selectedStatus;
    if (validationId == null) {
      _showSnack('ID validasi kasus tidak ditemukan.');
      return;
    }
    if (selectedStatus == null) {
      _showSnack('Pilih keputusan validasi terlebih dahulu.');
      return;
    }

    setState(() => _isSubmitting = true);

    try {
      final service = ref.read(plantCheckApiServiceProvider);
      final success = await service.updatePplValidation(
        validationId: validationId,
        status: selectedStatus,
        notes: _notesController.text.trim().isNotEmpty
            ? _notesController.text.trim()
            : null,
      );

      if (!mounted) return;
      setState(() => _isSubmitting = false);

      if (success) {
        ref.invalidate(pplValidationsProvider);
        _showSnack('Validasi disimpan dan status berhasil dikirim ke petani.');
        context.pop(true);
      } else {
        _showSnack('Gagal menyimpan validasi.');
      }
    } catch (e) {
      if (!mounted) return;
      setState(() => _isSubmitting = false);
      final msg = e is ApiException ? e.message : 'Terjadi kesalahan: $e';
      _showSnack(msg);
    }
  }

  void _showSnack(String message) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: padiGreen,
        behavior: SnackBarBehavior.floating,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final scan = _case['scan'] is Map
        ? Map<String, dynamic>.from(_case['scan'] as Map)
        : _case;
    final farmer = scan['farmer'] is Map
        ? Map<String, dynamic>.from(scan['farmer'] as Map)
        : (_case['farmer'] is Map
            ? Map<String, dynamic>.from(_case['farmer'] as Map)
            : <String, dynamic>{});
    final farm = scan['farm'] is Map
        ? Map<String, dynamic>.from(scan['farm'] as Map)
        : (_case['farm'] is Map
            ? Map<String, dynamic>.from(_case['farm'] as Map)
            : <String, dynamic>{});
    final rec = scan['recommendation'] is Map
        ? Map<String, dynamic>.from(scan['recommendation'] as Map)
        : (_case['recommendation'] is Map
            ? Map<String, dynamic>.from(_case['recommendation'] as Map)
            : <String, dynamic>{});
    final ppl = _case['ppl'] is Map
        ? Map<String, dynamic>.from(_case['ppl'] as Map)
        : (scan['ppl'] is Map
            ? Map<String, dynamic>.from(scan['ppl'] as Map)
            : null);

    final currentStatus = _case['status']?.toString() ??
        scan['ppl_validation']?['status']?.toString() ??
        'pending';
    final statusInfo = _statusInfo(_selectedStatus ?? currentStatus);
    final disease = scan['predicted_class']?.toString() ??
        _case['disease']?.toString() ??
        _case['predicted_class']?.toString() ??
        'Penyakit Tanaman';

    final rawConf = scan['confidence']?.toString() ??
        _case['confidence']?.toString() ??
        '';
    final cleanConf = rawConf.replaceAll('%', '').trim();
    final parsedConf = double.tryParse(cleanConf);
    final confidence = parsedConf != null
        ? (parsedConf <= 1.0
            ? '${(parsedConf * 100).toStringAsFixed(1)}%'
            : '${parsedConf.toStringAsFixed(1)}%')
        : (rawConf.isNotEmpty ? rawConf : '-');

    final imageUrl = scan['image_url']?.toString() ??
        scan['image_path']?.toString() ??
        _case['image_url']?.toString() ??
        _case['image_path']?.toString();
    final farmerName = farmer['name']?.toString() ??
        _case['farmer_name']?.toString() ??
        scan['farmer_name']?.toString() ??
        'Petani';
    final farmName = farm['name']?.toString() ??
        _case['farm_name']?.toString() ??
        scan['farm_name']?.toString() ??
        'Lahan Petani';
    final officerName = ppl?['name']?.toString() ??
        _case['ppl_name']?.toString() ??
        _case['officer_name']?.toString() ??
        scan['ppl_validation']?['ppl_name']?.toString();

    final recAction = rec['action']?.toString() ??
        rec['langkah_preventif']?.toString() ??
        rec['explanation']?.toString() ??
        rec['analisis']?.toString() ??
        _case['recommendation_action']?.toString() ??
        scan['action']?.toString();

    final isOfficer = ref.watch(isOfficerRoleProvider);

    return Scaffold(
      backgroundColor: const Color(0xFFF6FBF7),
      appBar: AppBar(
        backgroundColor: Colors.white,
        surfaceTintColor: Colors.white,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_rounded, color: padiInk),
          onPressed: () => context.pop(),
        ),
        title: const Text(
          'Validasi Laporan Penyakit',
          style: TextStyle(
            color: padiInk,
            fontSize: 16,
            fontWeight: FontWeight.w900,
          ),
        ),
        actions: [
          if (_isLoadingDetail)
            const Center(
              child: Padding(
                padding: EdgeInsets.only(right: 16),
                child: SizedBox(
                  width: 18,
                  height: 18,
                  child: CircularProgressIndicator(
                    strokeWidth: 2,
                    color: padiGreen,
                  ),
                ),
              ),
            ),
        ],
      ),
      body: _isLoadingDetail &&
              _case['scan'] == null &&
              _case['predicted_class'] == null
          ? const Center(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  CircularProgressIndicator(color: padiGreen),
                  SizedBox(height: 12),
                  Text(
                    'Memuat detail laporan...',
                    style: TextStyle(
                      color: padiMuted,
                      fontSize: 13,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ],
              ),
            )
          : RefreshIndicator(
              onRefresh: () async {
                final id = _validationId;
                if (id != null) await _loadDetail(id);
              },
              color: padiGreen,
              child: SingleChildScrollView(
                physics: const AlwaysScrollableScrollPhysics(),
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    _StatusHeaderCard(
                      status: statusInfo.label,
                      description: statusInfo.description,
                      currentStatus: currentStatus,
                    ),
                    const SizedBox(height: 14),
                    _ImageCard(imageUrl: imageUrl),
                    const SizedBox(height: 14),
                    _CaseSummaryCard(
                      disease: disease,
                      confidence: confidence,
                      farmerName: farmerName,
                      farmName: farmName,
                      officerName: officerName,
                    ),
                    if (recAction != null && recAction.isNotEmpty) ...[
                      const SizedBox(height: 12),
                      _RecommendationCard(action: recAction),
                    ],
                    const SizedBox(height: 16),
                    if (isOfficer) ...[
                      _DecisionCard(
                        selectedStatus: _selectedStatus,
                        notesController: _notesController,
                        isSubmitting: _isSubmitting,
                        onStatusChanged: (value) =>
                            setState(() => _selectedStatus = value),
                        onSubmit: _handleSubmit,
                      ),
                    ] else ...[
                      _FarmerResultCard(
                        status: currentStatus,
                        officerName: officerName,
                        notes: _case['notes']?.toString(),
                        validatedAt: _case['validated_at']?.toString(),
                      ),
                    ],
                    const SizedBox(height: 24),
                  ],
                ),
              ),
            ),
    );
  }
}

class _FarmerResultCard extends StatelessWidget {
  const _FarmerResultCard({
    required this.status,
    required this.officerName,
    required this.notes,
    required this.validatedAt,
  });

  final String status;
  final String? officerName;
  final String? notes;
  final String? validatedAt;

  @override
  Widget build(BuildContext context) {
    final Color badgeColor;
    final Color bgColor;
    final String titleText;
    final String descText;
    final IconData iconData;

    switch (status) {
      case 'validated':
        badgeColor = const Color(0xFF047857);
        bgColor = const Color(0xFFEAF7EF);
        titleText = 'Laporan Divalidasi oleh Petugas';
        descText =
            'Kondisi lapangan terkonfirmasi sesuai diagnosa. Ikuti arahan penanganan di bawah.';
        iconData = Icons.verified_rounded;
        break;
      case 'needs_revisit':
        badgeColor = const Color(0xFFD97706);
        bgColor = const Color(0xFFFEF3C7);
        titleText = 'Perlu Tinjauan Ulang';
        descText =
            'Petugas menjadwalkan kunjungan ulang atau meminta foto tambahan pada petak bergejala.';
        iconData = Icons.schedule_rounded;
        break;
      case 'rejected':
        badgeColor = const Color(0xFFDC2626);
        bgColor = const Color(0xFFFEE2E2);
        titleText = 'Hasil Verifikasi: Tidak Sesuai';
        descText =
            'Gejala visual di lapangan berbeda dari hasil diagnosa awal AI.';
        iconData = Icons.cancel_outlined;
        break;
      case 'pending':
      default:
        badgeColor = const Color(0xFF2563EB);
        bgColor = const Color(0xFFEFF6FF);
        titleText = 'Dalam Antrean Verifikasi';
        descText =
            'Laporan Anda sudah diterima dan menunggu verifikasi dari Petugas Penyuluh Lapangan (PPL).';
        iconData = Icons.hourglass_top_rounded;
        break;
    }

    return _SurfaceCard(
      borderColor: badgeColor.withValues(alpha: 0.28),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: bgColor,
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Icon(iconData, color: badgeColor, size: 22),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      titleText,
                      style: TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.w900,
                        color: badgeColor,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      descText,
                      style: const TextStyle(
                        fontSize: 11.5,
                        color: padiMuted,
                        height: 1.3,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          if (notes != null && notes!.trim().isNotEmpty) ...[
            const SizedBox(height: 14),
            const Divider(height: 1, color: Color(0xFFE2E8F0)),
            const SizedBox(height: 12),
            const Text(
              'Catatan & Arahan Petugas:',
              style: TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w800,
                color: padiInk,
              ),
            ),
            const SizedBox(height: 6),
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: const Color(0xFFF8FAFC),
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: const Color(0xFFE2E8F0)),
              ),
              child: Text(
                notes!.trim(),
                style: const TextStyle(
                  fontSize: 12.5,
                  color: padiInk,
                  height: 1.45,
                  fontWeight: FontWeight.w600,
                  fontStyle: FontStyle.italic,
                ),
              ),
            ),
          ],
          if (officerName != null && officerName!.isNotEmpty) ...[
            const SizedBox(height: 10),
            Row(
              children: [
                const Icon(Icons.badge_outlined, size: 14, color: padiMuted),
                const SizedBox(width: 4),
                Text(
                  'Petugas: $officerName',
                  style: const TextStyle(
                    fontSize: 11.5,
                    color: padiMuted,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ],
            ),
          ],
          const SizedBox(height: 16),
          Row(
            children: [
              Expanded(
                child: OutlinedButton.icon(
                  onPressed: () => context.push('/plant-check'),
                  icon: const Icon(Icons.camera_alt_outlined, size: 16),
                  label: const Text('Pindai Daun Baru'),
                  style: OutlinedButton.styleFrom(
                    foregroundColor: padiGreen,
                    side: BorderSide(color: padiGreen.withValues(alpha: 0.4)),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10),
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: FilledButton.icon(
                  onPressed: () => context.push('/community-alert'),
                  icon: const Icon(Icons.radar_rounded, size: 16),
                  label: const Text('Peta Radar'),
                  style: FilledButton.styleFrom(
                    backgroundColor: padiGreen,
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
    );
  }
}

class _StatusHeaderCard extends StatelessWidget {
  const _StatusHeaderCard({
    required this.status,
    required this.description,
    required this.currentStatus,
  });

  final String status;
  final String description;
  final String currentStatus;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: padiSoftGreen,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: padiGreen.withValues(alpha: 0.18)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Icon(Icons.verified_user_outlined, color: padiGreen),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  status,
                  style: const TextStyle(
                    color: padiInk,
                    fontSize: 15,
                    fontWeight: FontWeight.w900,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          _StatusFlow(status: currentStatus),
          const SizedBox(height: 10),
          Text(
            description,
            style: const TextStyle(
              color: padiMuted,
              fontSize: 12.5,
              fontWeight: FontWeight.w700,
              height: 1.35,
            ),
          ),
        ],
      ),
    );
  }
}

class _ImageCard extends StatelessWidget {
  const _ImageCard({required this.imageUrl});

  final String? imageUrl;

  @override
  Widget build(BuildContext context) {
    Widget imageWidget;
    final url = imageUrl?.trim();

    if (url != null && url.isNotEmpty) {
      if (url.startsWith('http://') || url.startsWith('https://')) {
        imageWidget = Image.network(
          url,
          fit: BoxFit.cover,
          errorBuilder: (_, __, ___) => _buildFallback(),
        );
      } else if (url.startsWith('/storage') ||
          url.startsWith('storage/') ||
          url.startsWith('/uploads') ||
          url.startsWith('uploads/')) {
        final baseUrl = AppConfig.baseUrl;
        final cleanPath = url.startsWith('/') ? url : '/$url';
        final fullUrl = '$baseUrl$cleanPath';
        imageWidget = Image.network(
          fullUrl,
          fit: BoxFit.cover,
          errorBuilder: (_, __, ___) => _buildFallback(),
        );
      } else if (url.startsWith('file://') ||
          url.contains(':\\') ||
          url.startsWith('/data/') ||
          url.startsWith('/storage/emulated/')) {
        final cleanPath = url.replaceFirst('file://', '');
        imageWidget = Image.file(
          File(cleanPath),
          fit: BoxFit.cover,
          errorBuilder: (_, __, ___) => _buildFallback(),
        );
      } else {
        final baseUrl = AppConfig.baseUrl;
        final fullUrl = '$baseUrl/${url.replaceFirst(RegExp(r'^/+'), '')}';
        imageWidget = Image.network(
          fullUrl,
          fit: BoxFit.cover,
          errorBuilder: (_, __, ___) => _buildFallback(),
        );
      }
    } else {
      imageWidget = _buildFallback();
    }

    return Container(
      height: 220,
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(14),
        color: Colors.white,
        border: Border.all(color: padiGreen.withValues(alpha: 0.14)),
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(14),
        child: imageWidget,
      ),
    );
  }

  Widget _buildFallback() {
    return const Center(
      child: Icon(Icons.eco_rounded, size: 64, color: padiGreen),
    );
  }
}

class _CaseSummaryCard extends StatelessWidget {
  const _CaseSummaryCard({
    required this.disease,
    required this.confidence,
    required this.farmerName,
    required this.farmName,
    required this.officerName,
  });

  final String disease;
  final String confidence;
  final String farmerName;
  final String farmName;
  final String? officerName;

  @override
  Widget build(BuildContext context) {
    return _SurfaceCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: padiSoftGreen,
                  borderRadius: BorderRadius.circular(8),
                ),
                child: const Text(
                  'Laporan Petani',
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w900,
                    color: padiGreen,
                  ),
                ),
              ),
              const Spacer(),
              Text(
                'Keyakinan $confidence',
                style: const TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w900,
                  color: padiGreen,
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Text(
            disease,
            style: const TextStyle(
              fontSize: 17,
              fontWeight: FontWeight.w900,
              color: padiInk,
            ),
          ),
          const SizedBox(height: 10),
          _MetaLine(
            icon: Icons.person_outline_rounded,
            label: 'Petani',
            value: farmerName,
          ),
          const SizedBox(height: 6),
          _MetaLine(icon: Icons.grass_rounded, label: 'Lahan', value: farmName),
          const SizedBox(height: 6),
          _MetaLine(
            icon: Icons.badge_outlined,
            label: 'PPL/Admin',
            value: officerName == null || officerName!.isEmpty
                ? 'Belum ditangani'
                : officerName!,
          ),
        ],
      ),
    );
  }
}

class _RecommendationCard extends StatelessWidget {
  const _RecommendationCard({required this.action});

  final String action;

  @override
  Widget build(BuildContext context) {
    return _SurfaceCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Saran Awal Sistem',
            style: TextStyle(
              fontSize: 13,
              fontWeight: FontWeight.w900,
              color: padiInk,
            ),
          ),
          const SizedBox(height: 6),
          Text(
            action,
            style: const TextStyle(
              fontSize: 12.5,
              color: padiMuted,
              height: 1.4,
              fontWeight: FontWeight.w600,
            ),
          ),
        ],
      ),
    );
  }
}

class _DecisionCard extends StatelessWidget {
  const _DecisionCard({
    required this.selectedStatus,
    required this.notesController,
    required this.isSubmitting,
    required this.onStatusChanged,
    required this.onSubmit,
  });

  final String? selectedStatus;
  final TextEditingController notesController;
  final bool isSubmitting;
  final ValueChanged<String> onStatusChanged;
  final VoidCallback onSubmit;

  @override
  Widget build(BuildContext context) {
    return _SurfaceCard(
      borderColor: padiGreen.withValues(alpha: 0.28),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Row(
            children: [
              Icon(Icons.assignment_turned_in_rounded, color: padiGreen),
              SizedBox(width: 8),
              Expanded(
                child: Text(
                  'Keputusan Validasi Lapangan (PPL/Admin)',
                  style: TextStyle(
                    fontSize: 14.5,
                    fontWeight: FontWeight.w900,
                    color: padiInk,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          _buildStatusChoice(
            value: 'validated',
            title: 'Valid: penyakit sesuai laporan',
            subtitle: 'Petani menerima status valid dan arahan penanganan.',
          ),
          const SizedBox(height: 8),
          _buildStatusChoice(
            value: 'needs_revisit',
            title: 'Butuh tinjauan ulang',
            subtitle: 'Petani diminta foto ulang atau menunggu kunjungan.',
          ),
          const SizedBox(height: 8),
          _buildStatusChoice(
            value: 'rejected',
            title: 'Tidak sesuai kondisi lapangan',
            subtitle: 'Laporan ditutup sebagai anomali atau diagnosa keliru.',
          ),
          const SizedBox(height: 16),
          const Text(
            'Catatan Resmi Petugas untuk Petani',
            style: TextStyle(
              fontSize: 12.5,
              fontWeight: FontWeight.w800,
              color: padiInk,
            ),
          ),
          const SizedBox(height: 6),
          TextField(
            controller: notesController,
            maxLines: 4,
            decoration: InputDecoration(
              hintText:
                  'Tulis hasil pengamatan lapangan, rekomendasi obat, atau alasan keputusan.',
              hintStyle: const TextStyle(
                fontSize: 12,
                color: padiMuted,
                fontWeight: FontWeight.w500,
              ),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: BorderSide(
                  color: padiGreen.withValues(alpha: 0.18),
                ),
              ),
              focusedBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: const BorderSide(color: padiGreen, width: 1.5),
              ),
              filled: true,
              fillColor: const Color(0xFFF6FBF7),
              contentPadding: const EdgeInsets.all(12),
            ),
          ),
          const SizedBox(height: 18),
          FilledButton.icon(
            onPressed: isSubmitting ? null : onSubmit,
            icon: isSubmitting
                ? const SizedBox(
                    width: 18,
                    height: 18,
                    child: CircularProgressIndicator(
                      strokeWidth: 2,
                      color: Colors.white,
                    ),
                  )
                : const Icon(Icons.send_rounded, size: 18),
            label: Text(
              isSubmitting ? 'Menyimpan...' : 'Simpan & Beritahu Petani',
              style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w900),
            ),
            style: FilledButton.styleFrom(
              backgroundColor: padiGreen,
              foregroundColor: Colors.white,
              minimumSize: const Size.fromHeight(48),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatusChoice({
    required String value,
    required String title,
    required String subtitle,
  }) {
    final isSelected = selectedStatus == value;

    return InkWell(
      onTap: () => onStatusChanged(value),
      borderRadius: BorderRadius.circular(12),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
        decoration: BoxDecoration(
          color: isSelected ? padiSoftGreen : const Color(0xFFF6FBF7),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: isSelected ? padiGreen : padiGreen.withValues(alpha: 0.14),
            width: isSelected ? 1.5 : 1,
          ),
        ),
        child: Row(
          children: [
            Icon(
              isSelected
                  ? Icons.radio_button_checked_rounded
                  : Icons.radio_button_off_rounded,
              color: isSelected ? padiGreen : padiMuted,
              size: 20,
            ),
            const SizedBox(width: 10),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: TextStyle(
                      fontSize: 12.5,
                      fontWeight: FontWeight.w900,
                      color: isSelected ? padiGreen : padiInk,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    subtitle,
                    style: const TextStyle(fontSize: 11, color: padiMuted),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _SurfaceCard extends StatelessWidget {
  const _SurfaceCard({required this.child, this.borderColor});

  final Widget child;
  final Color? borderColor;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(
          color: borderColor ?? padiGreen.withValues(alpha: 0.14),
        ),
      ),
      child: child,
    );
  }
}

class _MetaLine extends StatelessWidget {
  const _MetaLine({
    required this.icon,
    required this.label,
    required this.value,
  });

  final IconData icon;
  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Icon(icon, size: 16, color: padiGreen),
        const SizedBox(width: 6),
        Text(
          '$label: ',
          style: const TextStyle(
            fontSize: 12.5,
            color: padiMuted,
            fontWeight: FontWeight.w700,
          ),
        ),
        Expanded(
          child: Text(
            value,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: const TextStyle(
              fontSize: 12.5,
              color: padiInk,
              fontWeight: FontWeight.w800,
            ),
          ),
        ),
      ],
    );
  }
}

class _StatusFlow extends StatelessWidget {
  const _StatusFlow({required this.status});

  final String status;

  @override
  Widget build(BuildContext context) {
    final step = switch (status) {
      'pending' => 1,
      'needs_revisit' => 2,
      'validated' || 'rejected' => 3,
      _ => 1,
    };

    return Row(
      children: [
        _FlowStep(label: 'Petani', active: step >= 1),
        _FlowLine(active: step >= 2),
        _FlowStep(label: 'PPL/Admin', active: step >= 2),
        _FlowLine(active: step >= 3),
        _FlowStep(label: 'Hasil', active: step >= 3),
      ],
    );
  }
}

class _FlowStep extends StatelessWidget {
  const _FlowStep({required this.label, required this.active});

  final String label;
  final bool active;

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Icon(
          active ? Icons.check_circle_rounded : Icons.circle_outlined,
          size: 18,
          color: active ? padiGreen : padiMuted,
        ),
        const SizedBox(height: 3),
        Text(
          label,
          style: TextStyle(
            color: active ? padiGreen : padiMuted,
            fontSize: 10,
            fontWeight: FontWeight.w800,
          ),
        ),
      ],
    );
  }
}

class _FlowLine extends StatelessWidget {
  const _FlowLine({required this.active});

  final bool active;

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: Container(
        height: 2,
        margin: const EdgeInsets.only(bottom: 18),
        color: active ? padiGreen : padiGreen.withValues(alpha: 0.16),
      ),
    );
  }
}

({String label, String description}) _statusInfo(String status) {
  return switch (status) {
    'validated' => (
      label: 'Status: Valid',
      description:
          'PPL/Admin sudah mengonfirmasi laporan penyakit dan petani menerima hasil validasi.',
    ),
    'rejected' => (
      label: 'Status: Tidak Sesuai',
      description:
          'PPL/Admin menutup laporan karena kondisi lapangan tidak sesuai dengan penyakit yang dilaporkan.',
    ),
    'needs_revisit' => (
      label: 'Status: Tinjauan Ulang',
      description:
          'Kasus belum final. Petani perlu foto ulang atau menunggu kunjungan lapangan.',
    ),
    _ => (
      label: 'Status: Menunggu Petugas',
      description:
          'Laporan sudah diterima dari petani dan menunggu keputusan PPL/Admin.',
    ),
  };
}
