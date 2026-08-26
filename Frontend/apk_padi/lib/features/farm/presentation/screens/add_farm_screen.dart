import 'dart:math' as math;

import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:latlong2/latlong.dart' as latlng;
import 'package:padi/core/location/location_service.dart';
import 'package:padi/core/providers/app_providers.dart';
import 'package:padi/features/auth/presentation/widgets/padi_theme.dart';
import 'package:padi/features/farm/data/services/farm_api_service.dart';
import 'package:padi/features/region/data/models/region_models.dart';
import 'package:padi/features/region/data/services/region_api_service.dart';

class AddFarmScreen extends ConsumerStatefulWidget {
  const AddFarmScreen({super.key, this.setupFlow = false});

  final bool setupFlow;

  @override
  ConsumerState<AddFarmScreen> createState() => _AddFarmScreenState();
}

class _AddFarmScreenState extends ConsumerState<AddFarmScreen> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _areaController = TextEditingController();
  final _notesController = TextEditingController();

  final List<_FarmPoint> _polygonPoints = [];

  String _irrigationType = 'irrigated';
  String? _soilType;
  int _stepIndex = 0;
  bool _isLocating = false;
  bool _isSubmitting = false;
  ResolvedLocationModel? _resolvedLocation;

  @override
  void dispose() {
    _nameController.dispose();
    _areaController.dispose();
    _notesController.dispose();
    super.dispose();
  }

  Future<void> _detectGps() async {
    setState(() => _isLocating = true);

    try {
      const locService = LocationService();
      final position = await locService.getCurrentPosition();
      if (position == null) throw Exception('Lokasi tidak tersedia.');

      final regionApi = RegionApiService(ref.read(apiClientProvider));
      final resolved = await regionApi.resolveCoordinates(
        position.latitude,
        position.longitude,
      );

      if (!mounted) return;
      setState(() {
        _polygonPoints.add(
          _FarmPoint(lat: position.latitude, lng: position.longitude),
        );
        _resolvedLocation = resolved;
        _isLocating = false;
        _syncArea();
      });
    } catch (_) {
      if (!mounted) return;
      setState(() => _isLocating = false);
      _showMessage('Gagal mengambil GPS. Pastikan izin lokasi aktif.');
    }
  }

  Future<void> _submit() async {
    _syncArea();

    if (_polygonPoints.length < 3) {
      _showMessage('Tambahkan minimal 3 titik batas lahan.');
      return;
    }

    final formState = _formKey.currentState;
    if (formState == null || !formState.validate()) {
      return;
    }

    final areaHa = _parseDouble(_areaController.text);
    if (areaHa == null || areaHa <= 0) {
      _showMessage('Luas lahan tidak valid.');
      return;
    }

    setState(() => _isSubmitting = true);

    try {
      final center = _calculateCenter(_polygonPoints);
      final regionApi = RegionApiService(ref.read(apiClientProvider));
      final resolved =
          _resolvedLocation ??
          await regionApi.resolveCoordinates(center.lat, center.lng);

      final farmApi = FarmApiService(ref.read(apiClientProvider));
      final farm = await farmApi.createFarm(
        name: _nameController.text.trim(),
        areaHa: areaHa,
        latitude: center.lat,
        longitude: center.lng,
        boundaryCoordinates: _polygonPoints
            .map(
              (point) => {
                'lat': double.parse(point.lat.toStringAsFixed(7)),
                'lng': double.parse(point.lng.toStringAsFixed(7)),
              },
            )
            .toList(),
        irrigationType: _irrigationType,
        irrigationNotes: _notesController.text.trim().isEmpty
            ? null
            : _notesController.text.trim(),
        soilType: _soilType,
        provinceId: resolved?.province?.id,
        regencyId: resolved?.regency?.id,
        districtId: resolved?.district?.id,
        villageId: resolved?.village?.id,
      );

      if (!mounted) return;

      if (widget.setupFlow) {
        await showModalBottomSheet(
          context: context,
          isDismissible: false,
          enableDrag: false,
          backgroundColor: Colors.transparent,
          builder: (ctx) => Container(
            padding: const EdgeInsets.all(24),
            decoration: const BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
            ),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Container(
                  width: 60,
                  height: 60,
                  decoration: const BoxDecoration(
                    color: Color(0xFFDCFCE7),
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(
                    Icons.check_circle_rounded,
                    color: Color(0xFF16A34A),
                    size: 38,
                  ),
                ),
                const SizedBox(height: 16),
                Text(
                  'Lahan "${farm.name}" Berhasil Terdaftar!',
                  textAlign: TextAlign.center,
                  style: const TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.w900,
                    color: Color(0xFF14532D),
                  ),
                ),
                const SizedBox(height: 8),
                const Text(
                  'Data polygon dan karakteristik lahan telah tersimpan. Ingin langsung mulai musim tanam atau masuk ke Beranda?',
                  textAlign: TextAlign.center,
                  style: TextStyle(
                    fontSize: 13,
                    color: Color(0xFF4B5563),
                    height: 1.4,
                  ),
                ),
                const SizedBox(height: 20),
                SizedBox(
                  width: double.infinity,
                  child: FilledButton.icon(
                    onPressed: () {
                      Navigator.pop(ctx);
                      context.go('/land/season/start?farmId=${farm.id}&flow=setup');
                    },
                    icon: const Icon(Icons.play_arrow_rounded, size: 20),
                    label: const Text('Mulai Musim Tanam Sekarang'),
                    style: FilledButton.styleFrom(
                      backgroundColor: padiGreen,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                  ),
                ),
                const SizedBox(height: 10),
                SizedBox(
                  width: double.infinity,
                  child: OutlinedButton(
                    onPressed: () {
                      Navigator.pop(ctx);
                      context.go('/home');
                    },
                    style: OutlinedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                    child: const Text('Masuk ke Beranda'),
                  ),
                ),
              ],
            ),
          ),
        );
      } else if (context.canPop()) {
        context.pop(farm);
      } else {
        context.go('/farms');
      }
    } catch (error) {
      if (!mounted) return;
      _showMessage(
        'Gagal mendaftarkan lahan: ${error.toString().replaceFirst('Exception: ', '')}',
      );
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }
  }

  void _cancel() {
    if (widget.setupFlow) {
      context.go('/home');
      return;
    }

    context.canPop() ? context.pop() : context.go('/farms');
  }

  void _previousStep() {
    if (_stepIndex == 0) {
      _cancel();
      return;
    }

    setState(() => _stepIndex -= 1);
  }

  void _goToStep(int targetIndex) {
    if (targetIndex == _stepIndex) return;
    if (targetIndex > _stepIndex) {
      if (_stepIndex == 0) {
        final error = _requiredText(_nameController.text, 'Nama lahan wajib diisi.');
        if (error != null) {
          _showMessage(error);
          return;
        }
      }
      if (_stepIndex == 1 && _polygonPoints.length < 3) {
        _showMessage('Tambahkan minimal 3 titik batas lahan.');
        return;
      }
    }
    setState(() => _stepIndex = targetIndex);
  }

  Future<void> _primaryAction() async {
    if (_stepIndex == 0) {
      final error = _requiredText(
        _nameController.text,
        'Nama lahan wajib diisi.',
      );
      if (error != null) {
        _showMessage(error);
        return;
      }

      setState(() => _stepIndex = 1);
      return;
    }

    if (_stepIndex == 1) {
      _syncArea();
      if (_polygonPoints.length < 3) {
        _showMessage('Tambahkan minimal 3 titik batas lahan pada peta.');
        return;
      }

      setState(() => _stepIndex = 2);
      return;
    }

    if (_stepIndex == 2) {
      setState(() => _stepIndex = 3);
      return;
    }

    await _submit();
  }

  String get _primaryLabel {
    if (_isSubmitting) return 'Mendaftarkan Lahan...';
    switch (_stepIndex) {
      case 0:
        return 'Lanjut ke Peta Batas (Step 2)';
      case 1:
        return 'Lanjut ke Karakteristik (Step 3)';
      case 2:
        return 'Lanjut ke Ringkasan (Step 4)';
      case 3:
      default:
        return 'Simpan & Daftarkan Lahan';
    }
  }

  String get _secondaryLabel {
    if (_stepIndex > 0) return 'Kembali ke Step Sebelumnya';
    return widget.setupFlow ? 'Lewati' : 'Batal';
  }

  IconData get _primaryIcon {
    return _stepIndex == 3
        ? Icons.check_circle_outline_rounded
        : Icons.arrow_forward_rounded;
  }

  void _addMapPoint(latlng.LatLng point) {
    setState(() {
      _polygonPoints.add(_FarmPoint(lat: point.latitude, lng: point.longitude));
      _resolvedLocation = null;
      _syncArea();
    });
  }

  void _moveMapPoint(int index, latlng.LatLng point) {
    if (index < 0 || index >= _polygonPoints.length) {
      return;
    }

    setState(() {
      _polygonPoints[index] = _FarmPoint(
        lat: point.latitude,
        lng: point.longitude,
      );
      _resolvedLocation = null;
      _syncArea();
    });
  }

  void _removePoint(int index) {
    setState(() {
      _polygonPoints.removeAt(index);
      _syncArea();
    });
  }

  void _resetPoints() {
    setState(() {
      _polygonPoints.clear();
      _areaController.clear();
      _resolvedLocation = null;
    });
  }

  void _syncArea() {
    final areaHa = _calculateAreaHa(_polygonPoints);
    if (areaHa > 0) {
      _areaController.text = areaHa.toStringAsFixed(2);
    } else {
      _areaController.clear();
    }
  }

  double? _parseDouble(String value) {
    return double.tryParse(value.trim().replaceAll(',', '.'));
  }

  String? _requiredText(String? value, String message) {
    return value == null || value.trim().isEmpty ? message : null;
  }

  String? _validatePositiveNumber(String? value) {
    final parsed = _parseDouble(value ?? '');
    if (parsed == null) return 'Luas dihitung setelah minimal 3 titik.';
    return parsed <= 0 ? 'Nilai harus lebih dari 0.' : null;
  }

  _FarmPoint _calculateCenter(List<_FarmPoint> points) {
    final lat = points.fold<double>(0, (sum, point) => sum + point.lat);
    final lng = points.fold<double>(0, (sum, point) => sum + point.lng);
    return _FarmPoint(lat: lat / points.length, lng: lng / points.length);
  }

  double _calculateAreaHa(List<_FarmPoint> points) {
    if (points.length < 3) return 0;

    const earthRadius = 6378137.0;
    var area = 0.0;

    for (var i = 0; i < points.length; i++) {
      final p1 = points[i];
      final p2 = points[(i + 1) % points.length];
      final lat1 = p1.lat * math.pi / 180;
      final lat2 = p2.lat * math.pi / 180;
      final lng1 = p1.lng * math.pi / 180;
      final lng2 = p2.lng * math.pi / 180;
      area += (lng2 - lng1) * (2 + math.sin(lat1) + math.sin(lat2));
    }

    return (area * earthRadius * earthRadius / 2).abs() / 10000;
  }

  void _showMessage(String message) {
    ScaffoldMessenger.of(
      context,
    ).showSnackBar(SnackBar(content: Text(message)));
  }

  Widget _buildStepContent(_FarmPoint? center) {
    if (_stepIndex == 0) {
      return _FormPanel(
        title: 'Identitas Lahan Sawah',
        subtitle:
            'Beri nama petak sawah Anda. Batas & luas lahan akan dipetakan langsung via satelit pada langkah berikutnya.',
        child: Column(
          children: [
            TextFormField(
              controller: _nameController,
              textInputAction: TextInputAction.next,
              decoration: const InputDecoration(
                labelText: 'Nama Petak Sawah',
                hintText: 'Contoh: Sawah Blok Cariu / Petak Timur',
                prefixIcon: Icon(Icons.landscape_rounded),
              ),
              validator: (value) =>
                  _requiredText(value, 'Nama lahan wajib diisi.'),
            ),
            const SizedBox(height: 14),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: const Color(0xFFF0FDF4),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: const Color(0xFFBBF7D0)),
              ),
              child: const Row(
                children: [
                  Icon(Icons.info_outline_rounded, color: Color(0xFF16A34A), size: 20),
                  SizedBox(width: 10),
                  Expanded(
                    child: Text(
                      'Luas (Ha & m²) serta titik koordinat GPS akan dihitung secara otomatis saat Anda menandai batas lahan pada Step 2.',
                      style: TextStyle(color: Color(0xFF166534), fontSize: 12, height: 1.35),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      );
    }

    if (_stepIndex == 1) {
      final resolvedLocation = _resolvedLocation;

      return _FormPanel(
        title: 'Batas polygon',
        subtitle:
            'Tap peta untuk menandai batas sawah. Titik tersimpan seperti polygon admin.',
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            _MapPolygonPicker(
              points: _polygonPoints,
              center: center,
              isLocating: _isLocating,
              onTapPoint: _addMapPoint,
              onMovePoint: _moveMapPoint,
              onLocate: _detectGps,
              onUndo: _polygonPoints.isEmpty
                  ? null
                  : () => _removePoint(_polygonPoints.length - 1),
              onReset: _polygonPoints.isEmpty ? null : _resetPoints,
              onRemove: _removePoint,
            ),
            const SizedBox(height: 12),
            _PointSummary(
              points: _polygonPoints,
              center: center,
              areaHa: _calculateAreaHa(_polygonPoints),
              onRemove: _removePoint,
            ),
            if (resolvedLocation != null) ...[
              const SizedBox(height: 12),
              _DetectedLocation(location: resolvedLocation),
            ],
          ],
        ),
      );
    }

    if (_stepIndex == 2) {
      return _FormPanel(
        title: 'Karakteristik',
        subtitle: 'Lengkapi kondisi sawah sebelum disimpan.',
        child: Column(
          children: [
            DropdownButtonFormField<String>(
              initialValue: _irrigationType,
              decoration: const InputDecoration(
                labelText: 'Sistem irigasi',
                prefixIcon: Icon(Icons.water_drop_outlined),
              ),
              items: const [
                DropdownMenuItem(
                  value: 'irrigated',
                  child: Text('Irigasi teknis'),
                ),
                DropdownMenuItem(
                  value: 'semi_irrigated',
                  child: Text('Irigasi setengah teknis'),
                ),
                DropdownMenuItem(value: 'rainfed', child: Text('Tadah hujan')),
                DropdownMenuItem(value: 'tidal', child: Text('Pasang surut')),
              ],
              onChanged: _isSubmitting
                  ? null
                  : (value) {
                      if (value == null) {
                        return;
                      }

                      setState(() => _irrigationType = value);
                    },
            ),
            const SizedBox(height: 12),
            DropdownButtonFormField<String>(
              initialValue: _soilType,
              decoration: const InputDecoration(
                labelText: 'Tipe tanah',
                prefixIcon: Icon(Icons.terrain_outlined),
              ),
              hint: const Text('Opsional'),
              items: const [
                DropdownMenuItem(value: 'Alluvial', child: Text('Aluvial')),
                DropdownMenuItem(value: 'Latosol', child: Text('Latosol')),
                DropdownMenuItem(value: 'Grumusol', child: Text('Grumusol')),
                DropdownMenuItem(value: 'Regosol', child: Text('Regosol')),
              ],
              onChanged: _isSubmitting
                  ? null
                  : (value) => setState(() => _soilType = value),
            ),
            const SizedBox(height: 12),
            TextFormField(
              controller: _notesController,
              minLines: 3,
              maxLines: 4,
              decoration: const InputDecoration(
                labelText: 'Catatan',
                hintText: 'Kondisi irigasi, akses jalan, atau blok saluran',
                alignLabelWithHint: true,
              ),
            ),
          ],
        ),
      );
    }

    final resolvedLocation = _resolvedLocation;
    final areaHa = _calculateAreaHa(_polygonPoints);

    return _FormPanel(
      title: 'Ringkasan Lahan',
      subtitle: 'Periksa kembali data sawah Anda sebelum disimpan ke sistem.',
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: const Color(0xFFF6F8F5),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: const Color(0xFFE5ECE3)),
            ),
            child: Column(
              children: [
                _buildSummaryRow(
                  icon: Icons.badge_outlined,
                  label: 'Nama Lahan',
                  value: _nameController.text.trim(),
                ),
                const Divider(color: Color(0xFFE5ECE3), height: 16),
                _buildSummaryRow(
                  icon: Icons.straighten_rounded,
                  label: 'Luas Terhitung',
                  value: '${areaHa.toStringAsFixed(2)} Ha (${(areaHa * 10000).toStringAsFixed(0)} m²)',
                  valueColor: padiGreen,
                ),
                const Divider(color: Color(0xFFE5ECE3), height: 16),
                _buildSummaryRow(
                  icon: Icons.pin_drop_outlined,
                  label: 'Titik Polygon',
                  value: '${_polygonPoints.length} titik koordinat',
                ),
                if (resolvedLocation != null) ...[
                  const Divider(color: Color(0xFFE5ECE3), height: 16),
                  _buildSummaryRow(
                    icon: Icons.location_on_outlined,
                    label: 'Wilayah Desa / Kec',
                    value: '${resolvedLocation.village?.name ?? ''}, ${resolvedLocation.district?.name ?? ''}',
                  ),
                ],
                const Divider(color: Color(0xFFE5ECE3), height: 16),
                _buildSummaryRow(
                  icon: Icons.water_drop_outlined,
                  label: 'Sistem Irigasi',
                  value: _formatIrrigation(_irrigationType),
                ),
                if (_soilType != null) ...[
                  const Divider(color: Color(0xFFE5ECE3), height: 16),
                  _buildSummaryRow(
                    icon: Icons.terrain_outlined,
                    label: 'Tipe Tanah',
                    value: _soilType!,
                  ),
                ],
              ],
            ),
          ),
          const SizedBox(height: 14),
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: padiSoftGreen,
              borderRadius: BorderRadius.circular(10),
            ),
            child: const Row(
              children: [
                Icon(Icons.verified_rounded, color: padiGreen, size: 18),
                SizedBox(width: 8),
                Expanded(
                  child: Text(
                    'Data lahan lengkap. Siap disimpan & dipetakan ke citra satelit.',
                    style: TextStyle(
                      color: padiGreen,
                      fontSize: 12,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSummaryRow({
    required IconData icon,
    required String label,
    required String value,
    Color? valueColor,
  }) {
    return Row(
      children: [
        Icon(icon, size: 18, color: padiGreen),
        const SizedBox(width: 10),
        Expanded(
          child: Text(
            label,
            style: const TextStyle(color: padiMuted, fontSize: 13),
          ),
        ),
        Flexible(
          child: Text(
            value,
            textAlign: TextAlign.right,
            style: TextStyle(
              color: valueColor ?? padiInk,
              fontSize: 13,
              fontWeight: FontWeight.w800,
            ),
          ),
        ),
      ],
    );
  }

  String _formatIrrigation(String type) {
    switch (type) {
      case 'irrigated':
        return 'Irigasi Teknis';
      case 'semi_irrigated':
        return 'Setengah Teknis';
      case 'rainfed':
        return 'Tadah Hujan';
      case 'tidal':
        return 'Pasang Surut';
      default:
        return type;
    }
  }

  @override
  Widget build(BuildContext context) {
    final center = _polygonPoints.isEmpty
        ? null
        : _calculateCenter(_polygonPoints);

    return Scaffold(
      backgroundColor: padiField,
      body: SafeArea(
        child: Column(
          children: [
            _FarmSetupHeader(
              title: widget.setupFlow ? 'Daftarkan Lahan Sawah' : 'Tambah Lahan Sawah',
              subtitle: 'Lengkapi 4 langkah pendaftaran sawah & pemetaan polygon',
              onBack: _cancel,
            ),
            Expanded(
              child: Form(
                key: _formKey,
                child: SingleChildScrollView(
                  padding: const EdgeInsets.fromLTRB(18, 12, 18, 24),
                  child: Center(
                    child: ConstrainedBox(
                      constraints: const BoxConstraints(maxWidth: 720),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          _FarmStepIndicator(
                            currentStep: _stepIndex,
                            onStepTapped: _goToStep,
                          ),
                          AnimatedSwitcher(
                            duration: const Duration(milliseconds: 220),
                            switchInCurve: Curves.easeOutCubic,
                            switchOutCurve: Curves.easeOutCubic,
                            child: KeyedSubtree(
                              key: ValueKey(_stepIndex),
                              child: _buildStepContent(center),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              ),
            ),
            _BottomActions(
              isSubmitting: _isSubmitting,
              primaryLabel: _primaryLabel,
              secondaryLabel: _secondaryLabel,
              primaryIcon: _primaryIcon,
              onSecondary: _previousStep,
              onPrimary: _primaryAction,
            ),
          ],
        ),
      ),
    );
  }
}

class _FarmPoint {
  const _FarmPoint({required this.lat, required this.lng});

  final double lat;
  final double lng;
}

class _FarmSetupHeader extends StatelessWidget {
  const _FarmSetupHeader({
    required this.title,
    required this.subtitle,
    required this.onBack,
  });

  final String title;
  final String subtitle;
  final VoidCallback onBack;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.fromLTRB(12, 10, 18, 14),
      decoration: const BoxDecoration(
        color: padiSurface,
        border: Border(bottom: BorderSide(color: padiBorder)),
      ),
      child: Row(
        children: [
          IconButton(
            tooltip: 'Kembali',
            onPressed: onBack,
            icon: const Icon(Icons.arrow_back_rounded),
          ),
          const SizedBox(width: 4),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: const TextStyle(
                    color: padiInk,
                    fontSize: 19,
                    fontWeight: FontWeight.w900,
                  ),
                ),
                const SizedBox(height: 3),
                Text(
                  subtitle,
                  style: const TextStyle(
                    color: padiMuted,
                    fontSize: 12,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _SetupProgress extends StatelessWidget {
  const _SetupProgress();

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: padiGreen,
        borderRadius: BorderRadius.circular(padiControlRadius),
      ),
      child: const Row(
        children: [
          _SetupDot(label: '1', done: true),
          Expanded(child: _SetupLine(done: true)),
          _SetupDot(label: '2', active: true),
          Expanded(child: _SetupLine()),
          _SetupDot(label: '3'),
          Expanded(child: _SetupLine()),
          _SetupDot(label: '4'),
          Expanded(child: _SetupLine()),
          _SetupDot(label: '5'),
        ],
      ),
    );
  }
}

class _SetupDot extends StatelessWidget {
  const _SetupDot({
    required this.label,
    this.active = false,
    this.done = false,
  });

  final String label;
  final bool active;
  final bool done;

  @override
  Widget build(BuildContext context) {
    return SizedBox.square(
      dimension: 30,
      child: DecoratedBox(
        decoration: BoxDecoration(
          color: active || done
              ? Colors.white
              : Colors.white.withOpacity(0.24),
          shape: BoxShape.circle,
        ),
        child: Center(
          child: done
              ? const Icon(Icons.check_rounded, color: padiGreen, size: 18)
              : Text(
                  label,
                  style: TextStyle(
                    color: active ? padiGreen : Colors.white,
                    fontWeight: FontWeight.w900,
                    fontSize: 12,
                  ),
                ),
        ),
      ),
    );
  }
}

class _SetupLine extends StatelessWidget {
  const _SetupLine({this.done = false});

  final bool done;

  @override
  Widget build(BuildContext context) {
    return Container(
      height: 2,
      margin: const EdgeInsets.symmetric(horizontal: 5),
      color: done ? Colors.white : Colors.white.withOpacity(0.28),
    );
  }
}

class _ScreenIntro extends StatelessWidget {
  const _ScreenIntro({required this.setupFlow});

  final bool setupFlow;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: padiSurface,
        borderRadius: BorderRadius.circular(padiControlRadius),
        border: Border.all(color: padiBorder),
      ),
      child: Row(
        children: [
          const Icon(Icons.landscape_outlined, color: padiGreen),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              setupFlow
                  ? 'Langkah 2 dari 5: daftarkan batas lahan dengan polygon. Setelah tersimpan, lanjut ke musim tanam.'
                  : 'Input batas lahan memakai polygon seperti admin. Tambahkan minimal 3 titik koordinat.',
              style: const TextStyle(
                color: padiMuted,
                fontSize: 12,
                height: 1.4,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _FarmStepIndicator extends StatelessWidget {
  const _FarmStepIndicator({
    required this.currentStep,
    this.onStepTapped,
  });

  final int currentStep;
  final ValueChanged<int>? onStepTapped;

  @override
  Widget build(BuildContext context) {
    const steps = [
      ('Identitas', Icons.badge_outlined),
      ('Polygon', Icons.polyline_rounded),
      ('Detail', Icons.tune_rounded),
      ('Ringkasan', Icons.fact_check_outlined),
    ];

    return Row(
      children: [
        for (var i = 0; i < steps.length; i++) ...[
          Expanded(
            child: _FarmStepPill(
              label: steps[i].$1,
              icon: steps[i].$2,
              active: currentStep == i,
              done: currentStep > i,
              onTap: onStepTapped != null ? () => onStepTapped!(i) : null,
            ),
          ),
          if (i != steps.length - 1) const SizedBox(width: 6),
        ],
      ],
    );
  }
}

class _FarmStepPill extends StatelessWidget {
  const _FarmStepPill({
    required this.label,
    required this.icon,
    required this.active,
    required this.done,
    this.onTap,
  });

  final String label;
  final IconData icon;
  final bool active;
  final bool done;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    final selected = active || done;

    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Container(
          height: 42,
          padding: const EdgeInsets.symmetric(horizontal: 4),
          decoration: BoxDecoration(
            color: selected ? padiSoftGreen : padiSurface,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(
              color: selected ? const Color(0xFFCFE3D6) : padiBorder,
            ),
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(
                done ? Icons.check_rounded : icon,
                size: 15,
                color: selected ? padiGreen : padiMuted,
              ),
              const SizedBox(width: 4),
              Flexible(
                child: Text(
                  label,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    color: selected ? padiGreen : padiMuted,
                    fontSize: 11.5,
                    fontWeight: selected ? FontWeight.w800 : FontWeight.w600,
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _FormPanel extends StatelessWidget {
  const _FormPanel({required this.title, required this.child, this.subtitle});

  final String title;
  final Widget child;
  final String? subtitle;

  @override
  Widget build(BuildContext context) {
    final subtitle = this.subtitle;

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: padiSurface,
        borderRadius: BorderRadius.circular(padiControlRadius),
        border: Border.all(color: padiBorder),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Text(
            title,
            style: const TextStyle(
              color: padiInk,
              fontSize: 15,
              fontWeight: FontWeight.w800,
            ),
          ),
          if (subtitle != null) ...[
            const SizedBox(height: 6),
            Text(
              subtitle,
              style: const TextStyle(
                color: padiMuted,
                fontSize: 12,
                height: 1.35,
                fontWeight: FontWeight.w600,
              ),
            ),
          ],
          const SizedBox(height: 14),
          child,
        ],
      ),
    );
  }
}

class _MapPolygonPicker extends StatefulWidget {
  const _MapPolygonPicker({
    required this.points,
    required this.center,
    required this.isLocating,
    required this.onTapPoint,
    required this.onMovePoint,
    required this.onLocate,
    required this.onUndo,
    required this.onReset,
    required this.onRemove,
  });

  final List<_FarmPoint> points;
  final _FarmPoint? center;
  final bool isLocating;
  final ValueChanged<latlng.LatLng> onTapPoint;
  final void Function(int index, latlng.LatLng point) onMovePoint;
  final VoidCallback onLocate;
  final VoidCallback? onUndo;
  final VoidCallback? onReset;
  final ValueChanged<int> onRemove;

  @override
  State<_MapPolygonPicker> createState() => _MapPolygonPickerState();
}

class _MapPolygonPickerState extends State<_MapPolygonPicker> {
  final _mapKey = GlobalKey();
  int? _draggingIndex;

  @override
  Widget build(BuildContext context) {
    final mapPoints = widget.points
        .map((point) => latlng.LatLng(point.lat, point.lng))
        .toList(growable: false);
    final mapCenter = widget.center != null
        ? latlng.LatLng(widget.center!.lat, widget.center!.lng)
        : mapPoints.isNotEmpty
        ? mapPoints.last
        : const latlng.LatLng(-6.3265, 108.3242);

    return ClipRRect(
      borderRadius: BorderRadius.circular(padiControlRadius),
      child: Container(
        width: double.infinity,
        height: 320,
        decoration: BoxDecoration(
          color: const Color(0xFFEAF2E9),
          border: Border.all(color: padiBorder),
          borderRadius: BorderRadius.circular(padiControlRadius),
        ),
        child: Stack(
          children: [
            SizedBox.expand(
              key: _mapKey,
              child: FlutterMap(
                options: MapOptions(
                  initialCenter: mapCenter,
                  initialZoom: widget.points.isEmpty ? 15 : 17,
                  minZoom: 5,
                  maxZoom: 19,
                  interactionOptions: InteractionOptions(
                    flags: _draggingIndex == null
                        ? InteractiveFlag.all
                        : InteractiveFlag.all & ~InteractiveFlag.drag,
                  ),
                  onTap: (_, point) => widget.onTapPoint(point),
                ),
                children: [
                  TileLayer(
                    urlTemplate:
                        'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                    userAgentPackageName: 'com.padi.app',
                  ),
                  if (mapPoints.length >= 2)
                    PolylineLayer(
                      polylines: [
                        Polyline(
                          points: mapPoints,
                          color: padiGreen,
                          strokeWidth: 3,
                        ),
                      ],
                    ),
                  if (mapPoints.length >= 3)
                    PolygonLayer(
                      polygons: [
                        Polygon(
                          points: mapPoints,
                          color: padiGreen.withOpacity(0.18),
                          borderColor: padiGreen,
                          borderStrokeWidth: 2,
                        ),
                      ],
                    ),
                  MarkerLayer(
                    markers: widget.points
                        .asMap()
                        .entries
                        .map((entry) {
                          final number = entry.key + 1;
                          final point = entry.value;
                          final isDragging = _draggingIndex == entry.key;
                          return Marker(
                            point: latlng.LatLng(point.lat, point.lng),
                            width: 48,
                            height: 48,
                            child: Builder(
                              builder: (markerContext) {
                                return GestureDetector(
                                  onTap: () => widget.onRemove(entry.key),
                                  onPanStart: (_) {
                                    setState(() => _draggingIndex = entry.key);
                                  },
                                  onPanUpdate: (details) => _handleMarkerDrag(
                                    markerContext,
                                    entry.key,
                                    details.globalPosition,
                                  ),
                                  onPanEnd: (_) {
                                    setState(() => _draggingIndex = null);
                                  },
                                  onPanCancel: () {
                                    setState(() => _draggingIndex = null);
                                  },
                                  child: AnimatedScale(
                                    scale: isDragging ? 1.14 : 1,
                                    duration: const Duration(milliseconds: 120),
                                    child: Container(
                                      decoration: BoxDecoration(
                                        color: isDragging
                                            ? const Color(0xFFFF7A2F)
                                            : padiGreen,
                                        shape: BoxShape.circle,
                                        border: Border.all(
                                          color: Colors.white,
                                          width: 3,
                                        ),
                                        boxShadow: const [
                                          BoxShadow(
                                            color: Color(0x33000000),
                                            blurRadius: 10,
                                            offset: Offset(0, 4),
                                          ),
                                        ],
                                      ),
                                      child: Center(
                                        child: Text(
                                          '$number',
                                          style: const TextStyle(
                                            color: Colors.white,
                                            fontSize: 13,
                                            fontWeight: FontWeight.w900,
                                          ),
                                        ),
                                      ),
                                    ),
                                  ),
                                );
                              },
                            ),
                          );
                        })
                        .toList(growable: false),
                  ),
                ],
              ),
            ),
            Positioned(
              left: 12,
              top: 12,
              right: 12,
              child: Wrap(
                spacing: 8,
                runSpacing: 8,
                alignment: WrapAlignment.spaceBetween,
                children: [
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 12,
                      vertical: 9,
                    ),
                    decoration: BoxDecoration(
                      color: Colors.white.withOpacity(0.94),
                      borderRadius: BorderRadius.circular(999),
                      border: Border.all(color: padiBorder),
                    ),
                    child: const Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(
                          Icons.touch_app_outlined,
                          color: padiGreen,
                          size: 17,
                        ),
                        SizedBox(width: 7),
                        Text(
                          'Tap peta, tekan titik lalu geser',
                          style: TextStyle(
                            color: padiInk,
                            fontSize: 12,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                      ],
                    ),
                  ),
                  Wrap(
                    spacing: 6,
                    runSpacing: 6,
                    children: [
                      _MapToolButton(
                        tooltip: 'Ambil titik dari GPS',
                        onPressed: widget.isLocating ? null : widget.onLocate,
                        child: widget.isLocating
                            ? const SizedBox.square(
                                dimension: 16,
                                child: CircularProgressIndicator(
                                  strokeWidth: 2,
                                ),
                              )
                            : const Icon(Icons.my_location_rounded, size: 18),
                      ),
                      _MapToolButton(
                        tooltip: 'Hapus titik terakhir',
                        onPressed: widget.onUndo,
                        child: const Icon(Icons.undo_rounded, size: 18),
                      ),
                      _MapToolButton(
                        tooltip: 'Reset semua titik',
                        onPressed: widget.onReset,
                        child: const Icon(Icons.restart_alt_rounded, size: 18),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            if (widget.points.isEmpty)
              const Positioned(
                left: 18,
                right: 18,
                bottom: 16,
                child: _MapEmptyHint(),
              ),
          ],
        ),
      ),
    );
  }

  void _handleMarkerDrag(
    BuildContext markerContext,
    int index,
    Offset globalPosition,
  ) {
    final renderObject = _mapKey.currentContext?.findRenderObject();
    if (renderObject is! RenderBox) {
      return;
    }

    if (!renderObject.hasSize) {
      return;
    }

    final size = renderObject.size;
    if (size.isEmpty) {
      return;
    }

    final localPosition = renderObject.globalToLocal(globalPosition);
    if (localPosition.dx < 0 ||
        localPosition.dy < 0 ||
        localPosition.dx > size.width ||
        localPosition.dy > size.height) {
      return;
    }

    final camera = MapCamera.of(markerContext);
    widget.onMovePoint(index, camera.offsetToCrs(localPosition));
  }
}

class _MapToolButton extends StatelessWidget {
  const _MapToolButton({
    required this.tooltip,
    required this.onPressed,
    required this.child,
  });

  final String tooltip;
  final VoidCallback? onPressed;
  final Widget child;

  @override
  Widget build(BuildContext context) {
    return SizedBox.square(
      dimension: 40,
      child: Material(
        color: Colors.white.withOpacity(0.94),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(14),
          side: const BorderSide(color: padiBorder),
        ),
        child: IconButton(
          tooltip: tooltip,
          onPressed: onPressed,
          color: padiGreen,
          disabledColor: padiMuted.withOpacity(0.45),
          icon: child,
        ),
      ),
    );
  }
}

class _MapEmptyHint extends StatelessWidget {
  const _MapEmptyHint();

  @override
  Widget build(BuildContext context) {
    return DecoratedBox(
      decoration: BoxDecoration(
        color: Colors.white.withOpacity(0.94),
        borderRadius: BorderRadius.circular(padiControlRadius),
        border: Border.all(color: padiBorder),
      ),
      child: const Padding(
        padding: EdgeInsets.all(12),
        child: Text(
          'Mulai dari salah satu sudut sawah, lalu tap mengikuti batas lahan.',
          textAlign: TextAlign.center,
          style: TextStyle(
            color: padiMuted,
            fontSize: 12,
            height: 1.35,
            fontWeight: FontWeight.w700,
          ),
        ),
      ),
    );
  }
}

class _PointSummary extends StatelessWidget {
  const _PointSummary({
    required this.points,
    required this.center,
    required this.areaHa,
    required this.onRemove,
  });

  final List<_FarmPoint> points;
  final _FarmPoint? center;
  final double areaHa;
  final ValueChanged<int> onRemove;

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: padiField,
        borderRadius: BorderRadius.circular(padiControlRadius),
        border: Border.all(color: padiBorder),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Padding(
            padding: const EdgeInsets.all(12),
            child: Wrap(
              spacing: 8,
              runSpacing: 8,
              children: [
                _MetricChip(
                  icon: Icons.polyline_rounded,
                  label: 'Titik',
                  value: '${points.length}',
                ),
                _MetricChip(
                  icon: Icons.straighten_rounded,
                  label: 'Luas',
                  value: areaHa > 0 ? '${areaHa.toStringAsFixed(2)} ha' : '-',
                ),
                _MetricChip(
                  icon: Icons.place_outlined,
                  label: 'Pusat',
                  value: center == null
                      ? '-'
                      : '${center!.lat.toStringAsFixed(6)}, ${center!.lng.toStringAsFixed(6)}',
                  wide: true,
                ),
              ],
            ),
          ),
          const Divider(height: 1, color: padiBorder),
          if (points.isEmpty)
            const Padding(
              padding: EdgeInsets.all(14),
              child: Text(
                'Belum ada titik. Tap peta atau gunakan GPS untuk menambah titik pertama.',
                style: TextStyle(color: padiMuted, fontSize: 12, height: 1.4),
              ),
            )
          else
            ...points.asMap().entries.map(
              (entry) => ListTile(
                dense: true,
                leading: CircleAvatar(
                  radius: 14,
                  backgroundColor: padiGreen,
                  child: Text(
                    '${entry.key + 1}',
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 11,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                ),
                title: Text(
                  '${entry.value.lat.toStringAsFixed(7)}, ${entry.value.lng.toStringAsFixed(7)}',
                  style: const TextStyle(
                    color: padiInk,
                    fontSize: 12,
                    fontWeight: FontWeight.w700,
                  ),
                ),
                trailing: IconButton(
                  tooltip: 'Hapus titik',
                  onPressed: () => onRemove(entry.key),
                  icon: const Icon(Icons.close_rounded),
                ),
              ),
            ),
        ],
      ),
    );
  }
}

class _MetricChip extends StatelessWidget {
  const _MetricChip({
    required this.icon,
    required this.label,
    required this.value,
    this.wide = false,
  });

  final IconData icon;
  final String label;
  final String value;
  final bool wide;

  @override
  Widget build(BuildContext context) {
    return Container(
      constraints: BoxConstraints(minWidth: wide ? 220 : 110),
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
      decoration: BoxDecoration(
        color: padiSoftGreen,
        borderRadius: BorderRadius.circular(padiControlRadius),
        border: Border.all(color: const Color(0xFFCFE3D6)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, color: padiGreen, size: 18),
          const SizedBox(width: 8),
          Flexible(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(
                  label,
                  style: const TextStyle(
                    color: padiMuted,
                    fontSize: 10,
                    fontWeight: FontWeight.w700,
                  ),
                ),
                Text(
                  value,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(
                    color: padiInk,
                    fontSize: 12,
                    fontWeight: FontWeight.w800,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _DetectedLocation extends StatelessWidget {
  const _DetectedLocation({required this.location});

  final ResolvedLocationModel location;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: padiSoftGreen,
        borderRadius: BorderRadius.circular(padiControlRadius),
        border: Border.all(color: const Color(0xFFCFE3D6)),
      ),
      child: Row(
        children: [
          const Icon(Icons.verified_outlined, color: padiGreen, size: 20),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              location.formattedAddress,
              style: const TextStyle(
                color: padiInk,
                fontSize: 12,
                height: 1.35,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _BottomActions extends StatelessWidget {
  const _BottomActions({
    required this.isSubmitting,
    required this.primaryLabel,
    required this.secondaryLabel,
    required this.primaryIcon,
    required this.onSecondary,
    required this.onPrimary,
  });

  final bool isSubmitting;
  final String primaryLabel;
  final String secondaryLabel;
  final IconData primaryIcon;
  final VoidCallback onSecondary;
  final VoidCallback onPrimary;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: double.infinity,
      decoration: const BoxDecoration(
        color: padiSurface,
        border: Border(top: BorderSide(color: padiBorder)),
      ),
      child: SafeArea(
        top: false,
        child: Center(
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 720),
            child: Padding(
              padding: const EdgeInsets.fromLTRB(18, 12, 18, 12),
              child: Row(
                children: [
                  Expanded(
                    child: OutlinedButton(
                      onPressed: isSubmitting ? null : onSecondary,
                      style: OutlinedButton.styleFrom(
                        minimumSize: const Size.fromHeight(padiControlHeight),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(
                            padiControlRadius,
                          ),
                        ),
                      ),
                      child: Text(secondaryLabel),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    flex: 2,
                    child: FilledButton.icon(
                      onPressed: isSubmitting ? null : onPrimary,
                      style: FilledButton.styleFrom(
                        minimumSize: const Size.fromHeight(padiControlHeight),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(
                            padiControlRadius,
                          ),
                        ),
                      ),
                      icon: isSubmitting
                          ? const SizedBox.square(
                              dimension: 18,
                              child: CircularProgressIndicator(
                                strokeWidth: 2,
                                color: Colors.white,
                              ),
                            )
                          : Icon(primaryIcon),
                      label: Text(primaryLabel),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
