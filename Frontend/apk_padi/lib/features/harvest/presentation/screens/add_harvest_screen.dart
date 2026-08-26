import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import 'package:padi/core/network/api_client.dart';
import 'package:padi/core/storage/token_storage.dart';
import 'package:padi/features/auth/presentation/widgets/padi_theme.dart';
import 'package:padi/features/cultivation/data/models/crop_season_model.dart';
import 'package:padi/features/cultivation/data/services/crop_season_api_service.dart';
import 'package:padi/features/harvest/data/services/harvest_api_service.dart';

class AddHarvestScreen extends StatefulWidget {
  const AddHarvestScreen({
    super.key,
    this.farmId,
    this.cropSeasonId,
    this.setupFlow = false,
  });

  final int? farmId;
  final int? cropSeasonId;
  final bool setupFlow;

  @override
  State<AddHarvestScreen> createState() => _AddHarvestScreenState();
}

class _AddHarvestScreenState extends State<AddHarvestScreen> {
  late final HarvestApiService _harvestApiService;
  late final CropSeasonApiService _cropSeasonApiService;

  final TextEditingController _quantityController =
      TextEditingController();

  final TextEditingController _qualityController =
      TextEditingController();

  final TextEditingController _moistureController =
      TextEditingController();

  List<CropSeasonModel> _cropSeasons = [];

  CropSeasonModel? _selectedCropSeason;

  DateTime _selectedDate = DateTime.now();

  String _selectedUnit = 'kg';

  bool _isLoadingSeasons = true;
  bool _isSaving = false;

  @override
  void initState() {
    super.initState();

    final apiClient = ApiClient(
      const SecureTokenStorage(),
    );

    _harvestApiService = HarvestApiService(
      apiClient,
    );

    _cropSeasonApiService = CropSeasonApiService(
      apiClient,
    );

    _loadCropSeasons();
  }

  @override
  void dispose() {
    _quantityController.dispose();
    _qualityController.dispose();
    _moistureController.dispose();
    super.dispose();
  }

  Future<void> _loadCropSeasons() async {
    try {
      final seasons =
          await _cropSeasonApiService.fetchCropSeasons();

      if (!mounted) return;

      setState(() {
        _cropSeasons = seasons;
        _selectedCropSeason = seasons.isNotEmpty
            ? _preferredCropSeason(seasons)
            : null;
        _isLoadingSeasons = false;
      });
    } catch (e) {
      if (!mounted) return;

      setState(() {
        _isLoadingSeasons = false;
      });

      _showMessage(
        'Gagal mengambil data musim tanam.',
      );
    }
  }

  Future<void> _selectDate() async {
    final date = await showDatePicker(
      context: context,
      initialDate: _selectedDate,
      firstDate: DateTime(2020),
      lastDate: DateTime(2100),
      helpText: 'Pilih tanggal panen',
      cancelText: 'Batal',
      confirmText: 'Pilih',
    );

    if (date == null) return;

    setState(() {
      _selectedDate = date;
    });
  }

  String _formatDate(DateTime date) {
    const months = [
      'Januari',
      'Februari',
      'Maret',
      'April',
      'Mei',
      'Juni',
      'Juli',
      'Agustus',
      'September',
      'Oktober',
      'November',
      'Desember',
    ];

    return '${date.day} ${months[date.month - 1]} ${date.year}';
  }

  String _formatApiDate(DateTime date) {
    return '${date.year.toString().padLeft(4, '0')}-'
        '${date.month.toString().padLeft(2, '0')}-'
        '${date.day.toString().padLeft(2, '0')}';
  }

  String _seasonLabel(CropSeasonModel season) {
    return 'Musim Tanam #${season.id}';
  }

  Future<void> _saveHarvest() async {
    if (_isSaving) return;

    if (_selectedCropSeason == null) {
      _showMessage(
        'Pilih musim tanam terlebih dahulu.',
      );
      return;
    }

    final quantity = double.tryParse(
      _quantityController.text.trim().replaceAll(',', '.'),
    );

    final moisture = double.tryParse(
      _moistureController.text.trim().replaceAll(',', '.'),
    );

    if (quantity == null || quantity <= 0) {
      _showMessage(
        'Masukkan jumlah hasil panen yang valid.',
      );
      return;
    }

    if (moisture != null &&
        (moisture < 0 || moisture > 100)) {
      _showMessage(
        'Kadar air harus antara 0 sampai 100%.',
      );
      return;
    }

    setState(() {
      _isSaving = true;
    });

    try {
      await _harvestApiService.createHarvest(
        cropSeasonId: _selectedCropSeason!.id,
        harvestDate: _formatApiDate(_selectedDate),
        quantity: quantity,
        unit: _selectedUnit,
        qualityGrade:
            _qualityController.text.trim().isEmpty
                ? null
                : _qualityController.text.trim(),
        moisturePercent: moisture,
      );

      if (!mounted) return;

      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text(
            'Catatan panen berhasil disimpan.',
          ),
          behavior: SnackBarBehavior.floating,
        ),
      );

      if (widget.setupFlow) {
        _goToCalendar();
      } else {
        context.pop(true);
      }
    } on DioException catch (e) {
      if (!mounted) return;

      final responseData = e.response?.data;

      String message =
          'Gagal menyimpan catatan panen.';

      if (responseData is Map<String, dynamic>) {
        final serverMessage =
            responseData['message'];

        if (serverMessage is String &&
            serverMessage.isNotEmpty) {
          message = serverMessage;
        }

        final errors = responseData['errors'];

        if (errors is Map<String, dynamic>) {
          final messages = <String>[];

          for (final value in errors.values) {
            if (value is List) {
              messages.addAll(
                value.map(
                  (item) => item.toString(),
                ),
              );
            } else {
              messages.add(value.toString());
            }
          }

          if (messages.isNotEmpty) {
            message = messages.join('\n');
          }
        }
      }

      debugPrint(
        'HARVEST STATUS: ${e.response?.statusCode}',
      );

      debugPrint(
        'HARVEST RESPONSE: ${e.response?.data}',
      );

      debugPrint(
        'HARVEST REQUEST: ${e.requestOptions.data}',
      );

      setState(() {
        _isSaving = false;
      });

      _showMessage(
        '$message'
        '${e.response?.statusCode != null ? ' (${e.response?.statusCode})' : ''}',
      );
    } catch (e) {
      if (!mounted) return;

      setState(() {
        _isSaving = false;
      });

      debugPrint(
        'HARVEST ERROR: $e',
      );

      _showMessage(
        'Gagal menyimpan: $e',
      );
    }
  }

  void _showMessage(String message) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        behavior: SnackBarBehavior.floating,
        duration: const Duration(seconds: 5),
      ),
    );
  }

  void _goToCalendar() {
    final farmId = widget.farmId ?? _selectedCropSeason?.farmId;

    if (farmId == null) {
      context.go('/planting-calendar?flow=setup');
      return;
    }

    context.go('/planting-calendar/$farmId?flow=setup');
  }

  CropSeasonModel _preferredCropSeason(List<CropSeasonModel> seasons) {
    if (widget.cropSeasonId == null) {
      return seasons.first;
    }

    return seasons.firstWhere(
      (season) => season.id == widget.cropSeasonId,
      orElse: () => seasons.first,
    );
  }

  InputDecoration _inputDecoration({
    required String label,
    required IconData icon,
    String? suffixText,
  }) {
    return InputDecoration(
      labelText: label,
      prefixIcon: Icon(
        icon,
        color: padiGreen,
      ),
      suffixText: suffixText,
      filled: true,
      fillColor: Colors.white,
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(18),
        borderSide: BorderSide.none,
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(18),
        borderSide: const BorderSide(
          color: Color(0xFFE1E7E2),
        ),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(18),
        borderSide: const BorderSide(
          color: padiGreen,
          width: 2,
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: padiField,
      appBar: AppBar(
        backgroundColor: padiField,
        elevation: 0,
        scrolledUnderElevation: 0,
        leading: IconButton(
          onPressed: _isSaving
              ? null
              : () => context.pop(),
          icon: const Icon(
            Icons.arrow_back_rounded,
            color: padiInk,
          ),
        ),
        title: const Text(
          'Catat Panen',
          style: TextStyle(
            color: padiInk,
            fontSize: 20,
            fontWeight: FontWeight.w900,
          ),
        ),
      ),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(
          20,
          8,
          20,
          30,
        ),
        children: [
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: const Color(0xFFEAF5EF),
              borderRadius: BorderRadius.circular(22),
            ),
            child: const Row(
              children: [
                Icon(
                  Icons.agriculture_rounded,
                  color: padiGreen,
                  size: 42,
                ),
                SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment:
                        CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Catat hasil panen',
                        style: TextStyle(
                          color: padiInk,
                          fontSize: 19,
                          fontWeight: FontWeight.w900,
                        ),
                      ),
                      SizedBox(height: 5),
                      Text(
                        'Simpan hasil panen agar produksi sawah mudah dipantau.',
                        style: TextStyle(
                          color: padiMuted,
                          fontSize: 13,
                          height: 1.4,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 24),
          const Text(
            'Musim Tanam',
            style: TextStyle(
              color: padiInk,
              fontSize: 17,
              fontWeight: FontWeight.w900,
            ),
          ),
          const SizedBox(height: 8),
          _buildCropSeasonDropdown(),
          const SizedBox(height: 20),
          const Text(
            'Tanggal Panen',
            style: TextStyle(
              color: padiInk,
              fontSize: 17,
              fontWeight: FontWeight.w900,
            ),
          ),
          const SizedBox(height: 8),
          Material(
            color: Colors.white,
            borderRadius: BorderRadius.circular(18),
            child: InkWell(
              onTap: _isSaving
                  ? null
                  : _selectDate,
              borderRadius: BorderRadius.circular(18),
              child: Padding(
                padding: const EdgeInsets.symmetric(
                  horizontal: 16,
                  vertical: 19,
                ),
                child: Row(
                  children: [
                    const Icon(
                      Icons.calendar_month_rounded,
                      color: padiGreen,
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Text(
                        _formatDate(_selectedDate),
                        style: const TextStyle(
                          color: padiInk,
                          fontSize: 16,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                    ),
                    const Icon(
                      Icons.edit_calendar_rounded,
                      color: padiGreen,
                    ),
                  ],
                ),
              ),
            ),
          ),
          const SizedBox(height: 20),
          TextField(
            controller: _quantityController,
            enabled: !_isSaving,
            keyboardType:
                const TextInputType.numberWithOptions(
              decimal: true,
            ),
            decoration: _inputDecoration(
              label: 'Jumlah hasil panen',
              icon: Icons.scale_rounded,
              suffixText: _selectedUnit,
            ),
          ),
          const SizedBox(height: 16),
          _buildUnitDropdown(),
          const SizedBox(height: 16),
          TextField(
            controller: _qualityController,
            enabled: !_isSaving,
            textInputAction:
                TextInputAction.next,
            decoration: _inputDecoration(
              label: 'Kualitas / grade',
              icon: Icons.verified_rounded,
            ),
          ),
          const SizedBox(height: 16),
          TextField(
            controller: _moistureController,
            enabled: !_isSaving,
            keyboardType:
                const TextInputType.numberWithOptions(
              decimal: true,
            ),
            decoration: _inputDecoration(
              label: 'Kadar air',
              icon: Icons.water_drop_rounded,
              suffixText: '%',
            ),
          ),
          const SizedBox(height: 26),
          SizedBox(
            width: double.infinity,
            height: 56,
            child: FilledButton.icon(
              onPressed:
                  _isSaving || _isLoadingSeasons
                      ? null
                      : _saveHarvest,
              icon: _isSaving
                  ? const SizedBox(
                      width: 20,
                      height: 20,
                      child:
                          CircularProgressIndicator(
                        strokeWidth: 2,
                        color: Colors.white,
                      ),
                    )
                  : const Icon(
                      Icons.save_rounded,
                    ),
              label: Text(
                _isSaving
                    ? 'Menyimpan...'
                    : 'Simpan Catatan Panen',
                style: const TextStyle(
                  fontWeight: FontWeight.w900,
                ),
              ),
            ),
          ),
          if (widget.setupFlow) ...[
            const SizedBox(height: 12),
            SizedBox(
              width: double.infinity,
              height: 50,
              child: OutlinedButton.icon(
                onPressed: _isSaving ? null : _goToCalendar,
                icon: const Icon(Icons.calendar_month_outlined),
                label: const Text(
                  'Lewati ke Kalender',
                  style: TextStyle(fontWeight: FontWeight.w800),
                ),
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildCropSeasonDropdown() {
    if (_isLoadingSeasons) {
      return Container(
        height: 58,
        alignment: Alignment.center,
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(18),
        ),
        child: const SizedBox(
          width: 22,
          height: 22,
          child: CircularProgressIndicator(
            strokeWidth: 2,
            color: padiGreen,
          ),
        ),
      );
    }

    if (_cropSeasons.isEmpty) {
      return Container(
        padding: const EdgeInsets.all(18),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(18),
          border: Border.all(
            color: const Color(0xFFE1E7E2),
          ),
        ),
        child: const Row(
          children: [
            Icon(
              Icons.info_outline_rounded,
              color: padiGreen,
            ),
            SizedBox(width: 10),
            Expanded(
              child: Text(
                'Belum ada musim tanam yang tersedia.',
                style: TextStyle(
                  color: padiMuted,
                  fontSize: 14,
                ),
              ),
            ),
          ],
        ),
      );
    }

    return Container(
      padding: const EdgeInsets.symmetric(
        horizontal: 16,
      ),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(
          color: const Color(0xFFE1E7E2),
        ),
      ),
      child: DropdownButtonHideUnderline(
        child: DropdownButton<CropSeasonModel>(
          value: _selectedCropSeason,
          isExpanded: true,
          icon: const Icon(
            Icons.keyboard_arrow_down_rounded,
            color: padiGreen,
          ),
          items: _cropSeasons.map(
            (season) {
              return DropdownMenuItem<CropSeasonModel>(
                value: season,
                child: Text(
                  _seasonLabel(season),
                ),
              );
            },
          ).toList(),
          onChanged: _isSaving
              ? null
              : (value) {
                  if (value == null) return;

                  setState(() {
                    _selectedCropSeason = value;
                  });
                },
        ),
      ),
    );
  }

  Widget _buildUnitDropdown() {
    return Container(
      padding: const EdgeInsets.symmetric(
        horizontal: 16,
      ),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(
          color: const Color(0xFFE1E7E2),
        ),
      ),
      child: DropdownButtonHideUnderline(
        child: DropdownButton<String>(
          value: _selectedUnit,
          isExpanded: true,
          icon: const Icon(
            Icons.keyboard_arrow_down_rounded,
            color: padiGreen,
          ),
          items: const [
            DropdownMenuItem(
              value: 'kg',
              child: Text('Kilogram (kg)'),
            ),
            DropdownMenuItem(
              value: 'ton',
              child: Text('Ton'),
            ),
          ],
          onChanged: _isSaving
              ? null
              : (value) {
                  if (value == null) return;

                  setState(() {
                    _selectedUnit = value;
                  });
                },
        ),
      ),
    );
  }
}
