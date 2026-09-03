import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:padi/features/plant_check/data/services/plant_check_api_service.dart';

class PplCaseDetailScreen extends ConsumerStatefulWidget {
  const PplCaseDetailScreen({super.key, required this.caseData});

  final Map<String, dynamic> caseData;

  @override
  ConsumerState<PplCaseDetailScreen> createState() => _PplCaseDetailScreenState();
}

class _PplCaseDetailScreenState extends ConsumerState<PplCaseDetailScreen> {
  late String _selectedStatus;
  final TextEditingController _notesController = TextEditingController();
  bool _isSubmitting = false;

  @override
  void initState() {
    super.initState();
    _selectedStatus = widget.caseData['status']?.toString() ?? 'validated';
    if (_selectedStatus == 'pending') {
      _selectedStatus = 'validated';
    }
    _notesController.text = widget.caseData['notes']?.toString() ?? '';
  }

  @override
  void dispose() {
    _notesController.dispose();
    super.dispose();
  }

  Future<void> _handleSubmit() async {
    final validationId = (widget.caseData['id'] as num?)?.toInt();
    if (validationId == null) return;

    setState(() => _isSubmitting = true);

    try {
      final service = ref.read(plantCheckApiServiceProvider);
      final success = await service.updatePplValidation(
        validationId: validationId,
        status: _selectedStatus,
        notes: _notesController.text.trim().isNotEmpty ? _notesController.text.trim() : null,
      );

      if (!mounted) return;
      setState(() => _isSubmitting = false);

      if (success) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Row(
              children: [
                Icon(Icons.check_circle_rounded, color: Colors.white, size: 20),
                SizedBox(width: 10),
                Expanded(
                  child: Text(
                    'Validasi lapangan berhasil disimpan & dikirim ke petani.',
                    style: TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.w600),
                  ),
                ),
              ],
            ),
            backgroundColor: Color(0xFF059669),
            behavior: SnackBarBehavior.floating,
          ),
        );
        context.pop(true);
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Gagal menyimpan validasi.'),
            backgroundColor: Colors.red,
          ),
        );
      }
    } catch (e) {
      if (!mounted) return;
      setState(() => _isSubmitting = false);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Terjadi kesalahan: $e'),
          backgroundColor: Colors.red,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final scan = widget.caseData['scan'] as Map<String, dynamic>? ?? {};
    final farmer = scan['farmer'] as Map<String, dynamic>? ?? {};
    final farm = scan['farm'] as Map<String, dynamic>? ?? {};
    final rec = scan['recommendation'] as Map<String, dynamic>? ?? {};

    final disease = scan['predicted_class']?.toString() ?? 'Penyakit Tanaman';
    final confidence = scan['confidence'] != null
        ? '${((double.tryParse(scan['confidence'].toString()) ?? 0) * 100).toStringAsFixed(1)}%'
        : '-';
    final imageUrl = scan['image_url']?.toString();
    final farmerName = farmer['name']?.toString() ?? 'Petani';
    final farmName = farm['name']?.toString() ?? 'Lahan Petani';

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
          'Detail Kasus Petani',
          style: TextStyle(
            color: Color(0xFF0F172A),
            fontSize: 16,
            fontWeight: FontWeight.w800,
          ),
        ),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            // 1. Foto Daun Terlampir
            Container(
              height: 220,
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(16),
                color: const Color(0xFFF1F5F9),
                border: Border.all(color: const Color(0xFFE2E8F0)),
              ),
              child: ClipRRect(
                borderRadius: BorderRadius.circular(16),
                child: imageUrl != null && imageUrl.isNotEmpty
                    ? Image.network(
                        imageUrl,
                        fit: BoxFit.cover,
                        errorBuilder: (_, __, ___) => const Center(
                          child: Icon(Icons.broken_image_rounded, size: 48, color: Color(0xFF94A3B8)),
                        ),
                      )
                    : const Center(
                        child: Icon(Icons.eco_rounded, size: 64, color: Color(0xFF059669)),
                      ),
              ),
            ),
            const SizedBox(height: 16),

            // 2. Info Ringkasan Kasus
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: const Color(0xFFE2E8F0)),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                        decoration: BoxDecoration(
                          color: const Color(0xFFF0F9FF),
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: const Text(
                          'Diagnosa AI Gemini',
                          style: TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.w800,
                            color: Color(0xFF0284C7),
                          ),
                        ),
                      ),
                      const Spacer(),
                      Text(
                        'Keyakinan: $confidence',
                        style: const TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w800,
                          color: Color(0xFF059669),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Text(
                    disease,
                    style: const TextStyle(
                      fontSize: 17,
                      fontWeight: FontWeight.w900,
                      color: Color(0xFF0F172A),
                    ),
                  ),
                  const SizedBox(height: 6),
                  Row(
                    children: [
                      const Icon(Icons.person_outline_rounded, size: 16, color: Color(0xFF64748B)),
                      const SizedBox(width: 4),
                      Text(
                        farmerName,
                        style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w600, color: Color(0xFF475569)),
                      ),
                      const SizedBox(width: 12),
                      const Icon(Icons.landscape_outlined, size: 16, color: Color(0xFF64748B)),
                      const SizedBox(width: 4),
                      Text(
                        farmName,
                        style: const TextStyle(fontSize: 12.5, fontWeight: FontWeight.w600, color: Color(0xFF475569)),
                      ),
                    ],
                  ),
                ],
              ),
            ),

            if (rec['action'] != null) ...[
              const SizedBox(height: 12),
              Container(
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: const Color(0xFFF8FAFC),
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: const Color(0xFFE2E8F0)),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Saran Pengendalian AI Otomatis',
                      style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Color(0xFF334155)),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      rec['action'].toString(),
                      style: const TextStyle(fontSize: 12, color: Color(0xFF64748B)),
                    ),
                  ],
                ),
              ),
            ],

            const SizedBox(height: 20),

            // 3. Form Validasi Petugas PPL
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: const Color(0xFFBAE6FD)),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      const Icon(Icons.assignment_turned_in_rounded, size: 20, color: Color(0xFF0284C7)),
                      const SizedBox(width: 8),
                      const Text(
                        'Keputusan Validasi Lapangan',
                        style: TextStyle(fontSize: 14.5, fontWeight: FontWeight.w800, color: Color(0xFF0F172A)),
                      ),
                    ],
                  ),
                  const SizedBox(height: 14),

                  // Radio Status Options
                  _buildStatusRadio(
                    value: 'validated',
                    title: 'Divalidasi (Konfirmasi Benar)',
                    subtitle: 'Gejala dan penyakit sesuai dengan kondisi lapangan',
                    color: const Color(0xFF059669),
                  ),
                  const SizedBox(height: 8),
                  _buildStatusRadio(
                    value: 'needs_revisit',
                    title: 'Perlu Pemeriksaan Ulang',
                    subtitle: 'Sampel daun kurang jelas, butuh kunjungan/foto ulang',
                    color: const Color(0xFFD97706),
                  ),
                  const SizedBox(height: 8),
                  _buildStatusRadio(
                    value: 'rejected',
                    title: 'Tidak Terkonfirmasi (Diagnosa Keliru)',
                    subtitle: 'Kondisi tanaman bukan penyakit yang terindikasi',
                    color: const Color(0xFFDC2626),
                  ),

                  const SizedBox(height: 16),

                  // Input Catatan / Rekomendasi Khusus PPL
                  const Text(
                    'Catatan Petugas & Petunjuk Lapangan',
                    style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w700, color: Color(0xFF334155)),
                  ),
                  const SizedBox(height: 6),
                  TextField(
                    controller: _notesController,
                    maxLines: 3,
                    decoration: InputDecoration(
                      hintText: 'Tuliskan petunjuk teknis, dosis fungisida/insektisida yang direkomendasikan untuk petani...',
                      hintStyle: const TextStyle(fontSize: 12, color: Color(0xFF94A3B8)),
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                        borderSide: const BorderSide(color: Color(0xFFCBD5E1)),
                      ),
                      focusedBorder: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                        borderSide: const BorderSide(color: Color(0xFF0284C7), width: 1.5),
                      ),
                      filled: true,
                      fillColor: const Color(0xFFF8FAFC),
                      contentPadding: const EdgeInsets.all(12),
                    ),
                  ),

                  const SizedBox(height: 18),

                  FilledButton.icon(
                    onPressed: _isSubmitting ? null : _handleSubmit,
                    icon: _isSubmitting
                        ? const SizedBox(
                            width: 18,
                            height: 18,
                            child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                          )
                        : const Icon(Icons.send_rounded, size: 18),
                    label: Text(
                      _isSubmitting ? 'Menyimpan...' : 'Simpan & Beritahu Petani',
                      style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w800),
                    ),
                    style: FilledButton.styleFrom(
                      backgroundColor: const Color(0xFF0284C7),
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 24),
          ],
        ),
      ),
    );
  }

  Widget _buildStatusRadio({
    required String value,
    required String title,
    required String subtitle,
    required Color color,
  }) {
    final isSelected = _selectedStatus == value;

    return InkWell(
      onTap: () => setState(() => _selectedStatus = value),
      borderRadius: BorderRadius.circular(12),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
        decoration: BoxDecoration(
          color: isSelected ? color.withValues(alpha: 0.08) : const Color(0xFFF8FAFC),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(
            color: isSelected ? color : const Color(0xFFE2E8F0),
            width: isSelected ? 1.5 : 1,
          ),
        ),
        child: Row(
          children: [
            Icon(
              isSelected ? Icons.radio_button_checked_rounded : Icons.radio_button_off_rounded,
              color: isSelected ? color : const Color(0xFF94A3B8),
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
                      fontWeight: FontWeight.w800,
                      color: isSelected ? color : const Color(0xFF0F172A),
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    subtitle,
                    style: const TextStyle(fontSize: 11, color: Color(0xFF64748B)),
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
