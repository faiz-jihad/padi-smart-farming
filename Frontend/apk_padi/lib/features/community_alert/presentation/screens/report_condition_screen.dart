import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:padi/core/network/api_client.dart';
import 'package:padi/core/storage/token_storage.dart';
import 'package:padi/features/auth/presentation/widgets/padi_theme.dart';
import 'package:padi/features/community_alert/data/services/community_report_api_service.dart';

class ReportConditionScreen extends ConsumerStatefulWidget {
  const ReportConditionScreen({
    super.key,
    this.scanId,
  });

  final int? scanId;

  @override
  ConsumerState<ReportConditionScreen> createState() =>
      _ReportConditionScreenState();
}

class _ReportConditionScreenState
    extends ConsumerState<ReportConditionScreen> {
  final _formKey = GlobalKey<FormState>();

  final _latitudeController = TextEditingController();
  final _longitudeController = TextEditingController();
  final _radiusController = TextEditingController(text: '5');

  bool _consentGiven = false;
  bool _isLoading = false;

  late final CommunityReportApiService _service;

  @override
  void initState() {
    super.initState();

    _service = CommunityReportApiService(
      ApiClient(
        const SecureTokenStorage(),
      ),
    );
  }

  @override
  void dispose() {
    _latitudeController.dispose();
    _longitudeController.dispose();
    _radiusController.dispose();
    super.dispose();
  }

  Future<void> _submitReport() async {
    if (widget.scanId == null) {
      _showMessage(
        'Laporan harus berasal dari hasil Periksa Padi.',
        isError: true,
      );
      return;
    }

    if (!_formKey.currentState!.validate()) {
      return;
    }

    if (!_consentGiven) {
      _showMessage(
        'Centang persetujuan terlebih dahulu.',
        isError: true,
      );
      return;
    }

    setState(() {
      _isLoading = true;
    });

    try {
      await _service.createReport(
        scanId: widget.scanId!,
        latitude: double.parse(
          _latitudeController.text.trim(),
        ),
        longitude: double.parse(
          _longitudeController.text.trim(),
        ),
        radiusKm: double.parse(
          _radiusController.text.trim(),
        ),
        consentGiven: _consentGiven,
      );

      if (!mounted) {
        return;
      }

      _showMessage(
        'Kondisi berhasil dilaporkan.',
      );

      await Future.delayed(
        const Duration(milliseconds: 500),
      );

      if (!mounted) {
        return;
      }

      context.pop();
    } catch (e) {
      if (!mounted) {
        return;
      }

      _showMessage(
        _extractErrorMessage(e),
        isError: true,
      );
    } finally {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }

  String _extractErrorMessage(Object error) {
    final message = error.toString();

    if (message.startsWith('Exception: ')) {
      return message.substring(11);
    }

    return 'Gagal mengirim laporan.';
  }

  void _showMessage(
    String message, {
    bool isError = false,
  }) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: isError
            ? Colors.red.shade700
            : padiGreen,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final hasScan = widget.scanId != null;

    return Scaffold(
      backgroundColor: padiField,
      appBar: AppBar(
        backgroundColor: padiField,
        elevation: 0,
        scrolledUnderElevation: 0,
        leading: IconButton(
          onPressed: () => context.pop(),
          icon: const Icon(
            Icons.arrow_back_rounded,
            color: padiInk,
          ),
        ),
        title: const Text(
          'Lapor Kondisi',
          style: TextStyle(
            color: padiInk,
            fontSize: 20,
            fontWeight: FontWeight.w900,
          ),
        ),
      ),
      body: SafeArea(
        child: Form(
          key: _formKey,
          child: ListView(
            padding: const EdgeInsets.fromLTRB(
              20,
              12,
              20,
              32,
            ),
            children: [
              _buildIntro(),
              const SizedBox(height: 20),
              if (!hasScan) _buildScanWarning(),
              if (!hasScan) const SizedBox(height: 20),
              _buildLocationSection(),
              const SizedBox(height: 20),
              _buildConsent(),
              const SizedBox(height: 24),
              _buildSubmitButton(),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildIntro() {
    return Container(
      padding: const EdgeInsets.all(22),
      decoration: BoxDecoration(
        color: padiField,
        borderRadius: BorderRadius.circular(24),
      ),
      child: Row(
        children: [
          Container(
            width: 52,
            height: 52,
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
            ),
            child: const Icon(
              Icons.campaign_rounded,
              color: padiGreen,
              size: 28,
            ),
          ),
          const SizedBox(width: 16),
          const Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Laporkan kondisi sawah',
                  style: TextStyle(
                    color: padiInk,
                    fontSize: 18,
                    fontWeight: FontWeight.w900,
                  ),
                ),
                SizedBox(height: 5),
                Text(
                  'Bantu petani sekitar mendapatkan informasi kondisi tanaman.',
                  style: TextStyle(
                    color: padiMuted,
                    fontSize: 14,
                    height: 1.4,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildScanWarning() {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: padiCream,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(
          color: const Color(0xFFF2D27A),
        ),
      ),
      child: const Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(
            Icons.info_outline_rounded,
            color: Color(0xFF946E00),
          ),
          SizedBox(width: 12),
          Expanded(
            child: Text(
              'Laporan kondisi harus dibuat dari hasil Periksa Padi agar laporan dapat dikaitkan dengan hasil pemeriksaan.',
              style: TextStyle(
                color: Color(0xFF6B5300),
                fontSize: 14,
                height: 1.4,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildLocationSection() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Lokasi',
          style: TextStyle(
            color: padiInk,
            fontSize: 19,
            fontWeight: FontWeight.w900,
          ),
        ),
        const SizedBox(height: 8),
        const Text(
          'Masukkan lokasi area yang ingin dilaporkan.',
          style: TextStyle(
            color: padiMuted,
            fontSize: 14,
          ),
        ),
        const SizedBox(height: 16),
        _buildTextField(
          controller: _latitudeController,
          label: 'Latitude',
          hint: 'Contoh: -6.3279000',
          icon: Icons.location_on_outlined,
          keyboardType: const TextInputType.numberWithOptions(
            decimal: true,
            signed: true,
          ),
          validator: (value) {
            final number = double.tryParse(
              value?.trim() ?? '',
            );

            if (number == null) {
              return 'Latitude tidak valid';
            }

            if (number < -90 || number > 90) {
              return 'Latitude harus -90 sampai 90';
            }

            return null;
          },
        ),
        const SizedBox(height: 14),
        _buildTextField(
          controller: _longitudeController,
          label: 'Longitude',
          hint: 'Contoh: 108.3245000',
          icon: Icons.explore_outlined,
          keyboardType: const TextInputType.numberWithOptions(
            decimal: true,
            signed: true,
          ),
          validator: (value) {
            final number = double.tryParse(
              value?.trim() ?? '',
            );

            if (number == null) {
              return 'Longitude tidak valid';
            }

            if (number < -180 || number > 180) {
              return 'Longitude harus -180 sampai 180';
            }

            return null;
          },
        ),
        const SizedBox(height: 14),
        _buildTextField(
          controller: _radiusController,
          label: 'Radius area',
          hint: 'Contoh: 5',
          suffix: 'km',
          icon: Icons.radar_rounded,
          keyboardType: const TextInputType.numberWithOptions(
            decimal: true,
          ),
          validator: (value) {
            final number = double.tryParse(
              value?.trim() ?? '',
            );

            if (number == null) {
              return 'Radius tidak valid';
            }

            if (number <= 0 || number > 100) {
              return 'Radius harus 0-100 km';
            }

            return null;
          },
        ),
      ],
    );
  }

  Widget _buildTextField({
    required TextEditingController controller,
    required String label,
    required String hint,
    required IconData icon,
    required TextInputType keyboardType,
    required String? Function(String?) validator,
    String? suffix,
  }) {
    return TextFormField(
      controller: controller,
      keyboardType: keyboardType,
      validator: validator,
      style: const TextStyle(
        color: padiInk,
        fontSize: 15,
        fontWeight: FontWeight.w600,
      ),
      decoration: InputDecoration(
        labelText: label,
        hintText: hint,
        prefixIcon: Icon(
          icon,
          color: padiGreen,
        ),
        suffixText: suffix,
        filled: true,
        fillColor: Colors.white,
        labelStyle: const TextStyle(
          color: padiMuted,
        ),
        hintStyle: const TextStyle(
          color: Color(0xFF9CA3AF),
        ),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(18),
          borderSide: BorderSide.none,
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(18),
          borderSide: const BorderSide(
            color: Color(0xFFE5E7EB),
          ),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(18),
          borderSide: const BorderSide(
            color: padiGreen,
            width: 1.5,
          ),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(18),
          borderSide: const BorderSide(
            color: Colors.red,
          ),
        ),
        focusedErrorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(18),
          borderSide: const BorderSide(
            color: Colors.red,
            width: 1.5,
          ),
        ),
      ),
    );
  }

  Widget _buildConsent() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(
          color: const Color(0xFFE5E7EB),
        ),
      ),
      child: CheckboxListTile(
        contentPadding: EdgeInsets.zero,
        value: _consentGiven,
        onChanged: _isLoading
            ? null
            : (value) {
                setState(() {
                  _consentGiven = value ?? false;
                });
              },
        activeColor: padiGreen,
        controlAffinity: ListTileControlAffinity.leading,
        title: const Text(
          'Saya menyetujui laporan ini dibagikan sebagai informasi kondisi pertanian di sekitar.',
          style: TextStyle(
            color: padiInk,
            fontSize: 13,
            height: 1.4,
            fontWeight: FontWeight.w600,
          ),
        ),
      ),
    );
  }

  Widget _buildSubmitButton() {
    return SizedBox(
      height: 58,
      child: ElevatedButton.icon(
        onPressed: _isLoading || widget.scanId == null
            ? null
            : _submitReport,
        icon: _isLoading
            ? const SizedBox(
                width: 20,
                height: 20,
                child: CircularProgressIndicator(
                  strokeWidth: 2.5,
                  color: Colors.white,
                ),
              )
            : const Icon(
                Icons.send_rounded,
              ),
        label: Text(
          _isLoading
              ? 'Mengirim...'
              : 'Kirim Laporan',
        ),
        style: ElevatedButton.styleFrom(
          backgroundColor: padiGreen,
          foregroundColor: Colors.white,
          disabledBackgroundColor: Colors.grey.shade300,
          disabledForegroundColor: Colors.grey.shade600,
          elevation: 0,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(18),
          ),
          textStyle: const TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.w900,
          ),
        ),
      ),
    );
  }
}