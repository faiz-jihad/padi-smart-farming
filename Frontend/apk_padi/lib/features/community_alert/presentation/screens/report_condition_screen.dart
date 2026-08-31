import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:latlong2/latlong.dart' as latlng;
import 'package:padi/core/location/location_service.dart';
import 'package:padi/core/providers/app_providers.dart';
import 'package:padi/features/auth/presentation/widgets/padi_theme.dart';
import 'package:padi/features/community_alert/data/services/community_report_api_service.dart';
import 'package:padi/features/farm/data/models/farm_model.dart';
import 'package:padi/features/farm/data/services/farm_api_service.dart';
import 'package:padi/features/plant_check/data/services/plant_check_api_service.dart';

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

class _ReportConditionScreenState extends ConsumerState<ReportConditionScreen> {
  final _formKey = GlobalKey<FormState>();

  final _latitudeController = TextEditingController(text: '-6.3279000');
  final _longitudeController = TextEditingController(text: '108.3245000');
  final _radiusController = TextEditingController(text: '5.0');
  final _noteController = TextEditingController();

  double _radiusKm = 5.0;
  latlng.LatLng _currentPoint = const latlng.LatLng(-6.3279000, 108.3245000);
  late final MapController _mapController;

  bool _consentGiven = true;
  bool _isLoading = false;
  bool _isLocatingGps = false;
  bool _isLoadingScans = false;

  int? _activeScanId;
  PlantCheckResult? _selectedScan;
  List<PlantCheckResult> _recentScans = [];
  List<FarmModel> _farms = [];

  late final CommunityReportApiService _reportService;
  late final FarmApiService _farmService;
  late final PlantCheckApiService _plantCheckService;
  final LocationService _locationService = const LocationService();

  @override
  void initState() {
    super.initState();
    _mapController = MapController();
    _activeScanId = widget.scanId;

    final apiClient = ref.read(apiClientProvider);
    _reportService = CommunityReportApiService(apiClient);
    _farmService = FarmApiService(apiClient);
    _plantCheckService = PlantCheckApiService(apiClient);

    _loadInitialData();
  }

  @override
  void dispose() {
    _latitudeController.dispose();
    _longitudeController.dispose();
    _radiusController.dispose();
    _noteController.dispose();
    super.dispose();
  }

  Future<void> _loadInitialData() async {
    // 1. Load farms
    try {
      final farms = await _farmService.fetchFarms();
      if (mounted) {
        setState(() {
          _farms = farms;
          if (farms.isNotEmpty) {
            final first = farms.first;
            if (first.latitude != 0 && first.longitude != 0) {
              _updateCoordinates(first.latitude, first.longitude);
            }
          }
        });
      }
    } catch (_) {}

    // 2. Load recent scans if scanId is null or to get scan details
    setState(() => _isLoadingScans = true);
    try {
      final scans = await _plantCheckService.fetchScans();
      if (mounted) {
        setState(() {
          _recentScans = scans;
          if (_activeScanId != null) {
            final found = scans.where((s) => s.id == _activeScanId).toList();
            if (found.isNotEmpty) {
              _selectedScan = found.first;
            } else if (scans.isNotEmpty) {
              _selectedScan = scans.first;
              _activeScanId = scans.first.id;
            } else {
              _selectedScan = PlantCheckResult(
                id: _activeScanId!,
                farmId: 0,
                predictedClass: 'Gejala Penyakit Padi',
                qualityStatus: 'valid',
              );
            }
          } else if (scans.isNotEmpty) {
            _selectedScan = scans.first;
            _activeScanId = scans.first.id;
          }
          _isLoadingScans = false;
        });
      }
    } catch (_) {
      if (mounted) setState(() => _isLoadingScans = false);
    }


    // 3. Try to get GPS current position
    _fetchGpsLocation(silently: true);
  }

  Future<void> _fetchGpsLocation({bool silently = false}) async {
    if (!silently) {
      setState(() => _isLocatingGps = true);
    }

    try {
      final pos = await _locationService.getCurrentPosition();
      if (pos != null && mounted) {
        _updateCoordinates(pos.latitude, pos.longitude);
        if (!silently) {
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(
              content: Text('📍 Koordinat GPS berhasil disesuaikan dengan posisi Anda.'),
              backgroundColor: Color(0xFF16A34A),
              behavior: SnackBarBehavior.floating,
            ),
          );
        }
      } else if (!silently && mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Gagal mendapatkan lokasi GPS. Pastikan GPS aktif.'),
            backgroundColor: Color(0xFFDC2626),
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
    } catch (_) {
    } finally {
      if (mounted && !silently) {
        setState(() => _isLocatingGps = false);
      }
    }
  }

  void _updateCoordinates(double lat, double lng) {
    setState(() {
      _currentPoint = latlng.LatLng(lat, lng);
      _latitudeController.text = lat.toStringAsFixed(7);
      _longitudeController.text = lng.toStringAsFixed(7);
    });

    try {
      _mapController.move(_currentPoint, _mapController.camera.zoom);
    } catch (_) {}
  }

  void _onRadiusChanged(double radius) {
    setState(() {
      _radiusKm = radius;
      _radiusController.text = radius.toStringAsFixed(1);
    });
  }

  Future<void> _submitReport() async {
    if (_activeScanId == null) {
      _showSnack('Pilih hasil diagnosa / scan terlebih dahulu.', isError: true);
      return;
    }

    if (!_formKey.currentState!.validate()) return;

    if (!_consentGiven) {
      _showSnack('Centang persetujuan siaran radar komunitas terlebih dahulu.', isError: true);
      return;
    }

    setState(() => _isLoading = true);

    try {
      await _reportService.createReport(
        scanId: _activeScanId!,
        latitude: _currentPoint.latitude,
        longitude: _currentPoint.longitude,
        radiusKm: _radiusKm,
        consentGiven: _consentGiven,
      );

      if (!mounted) return;

      _showSuccessDialog();
    } catch (e) {
      if (!mounted) return;
      _showSnack(_extractErrorMessage(e), isError: true);
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  void _showSuccessDialog() {
    showDialog<void>(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: const Row(
          children: [
            Icon(Icons.check_circle_rounded, color: Color(0xFF16A34A), size: 28),
            SizedBox(width: 8),
            Text('Siaran Berhasil!', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w900)),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Laporan kondisi "${_selectedScan?.predictedClass ?? 'Penyakit Padi'}" telah disiarkan ke radar petani dalam radius ${_radiusKm.toStringAsFixed(1)} km.',
              style: const TextStyle(fontSize: 13.5, height: 1.4, color: Color(0xFF374151)),
            ),
            const SizedBox(height: 12),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: const Color(0xFFF0FDF4),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: const Color(0xFFBBF7D0)),
              ),
              child: const Row(
                children: [
                  Icon(Icons.shield_outlined, color: Color(0xFF16A34A), size: 18),
                  SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      'Identitas pribadi Anda tetap aman dan terlindungi.',
                      style: TextStyle(fontSize: 11.5, color: Color(0xFF166534), fontWeight: FontWeight.w600),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
        actions: [
          FilledButton(
            onPressed: () {
              Navigator.of(ctx).pop();
              context.pop();
            },
            style: FilledButton.styleFrom(backgroundColor: padiGreen),
            child: const Text('Selesai'),
          ),
        ],
      ),
    );
  }

  String _extractErrorMessage(Object error) {
    final message = error.toString();
    if (message.startsWith('Exception: ')) {
      return message.substring(11);
    }
    return 'Gagal mengirim laporan radar.';
  }

  void _showSnack(String message, {bool isError = false}) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: isError ? const Color(0xFFDC2626) : const Color(0xFF16A34A),
        behavior: SnackBarBehavior.floating,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        scrolledUnderElevation: 1,
        leading: IconButton(
          onPressed: () => context.pop(),
          icon: const Icon(Icons.arrow_back_rounded, color: Color(0xFF1E293B)),
        ),
        title: const Text(
          'Lapor Radar Komunitas',
          style: TextStyle(
            color: Color(0xFF1E293B),
            fontSize: 18,
            fontWeight: FontWeight.w900,
          ),
        ),
      ),
      body: SafeArea(
        child: Form(
          key: _formKey,
          child: ListView(
            padding: const EdgeInsets.fromLTRB(18, 14, 18, 36),
            children: [
              // 1. Hero Header Banner
              _buildHeroHeader(),

              const SizedBox(height: 16),

              // 2. Scan / Disease Context Card
              _buildScanContextSection(),

              const SizedBox(height: 16),

              // 3. Interactive Map & Coordinates Section
              _buildMapAndLocationSection(),

              const SizedBox(height: 16),

              // 4. Radius Broadcast Selector
              _buildRadiusSection(),

              const SizedBox(height: 16),

              // 5. Notes & Symptoms
              _buildNotesSection(),

              const SizedBox(height: 16),

              // 6. Community Consent & Privacy
              _buildConsentSection(),

              const SizedBox(height: 24),

              // 7. Submit Broadcast Button
              _buildSubmitButton(),
            ],
          ),
        ),
      ),
    );
  }

  // ================= HERO HEADER =================
  Widget _buildHeroHeader() {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        gradient: const LinearGradient(
          colors: [Color(0xFF064E3B), Color(0xFF047857)],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(18),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF047857).withValues(alpha: 0.2),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Row(
        children: [
          Container(
            width: 48,
            height: 48,
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.18),
              borderRadius: BorderRadius.circular(14),
            ),

            child: const Icon(Icons.radar_rounded, color: Color(0xFFFDE68A), size: 28),
          ),
          const SizedBox(width: 14),
          const Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Peringatan Dini Hamparan',
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 16,
                    fontWeight: FontWeight.w900,
                  ),
                ),
                SizedBox(height: 4),
                Text(
                  'Siarkan temuan hama & penyakit ke petani sekitar agar penanganan serentak lebih cepat.',
                  style: TextStyle(
                    color: Colors.white70,
                    fontSize: 12,
                    height: 1.35,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  // ================= SCAN CONTEXT =================
  Widget _buildScanContextSection() {
    final scan = _selectedScan;

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Row(
                children: [
                  Icon(Icons.biotech_rounded, color: Color(0xFF059669), size: 18),
                  SizedBox(width: 6),
                  Text(
                    'Temuan Penyakit / Hama',
                    style: TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w800,
                      color: Color(0xFF1E293B),
                    ),
                  ),
                ],
              ),
              if (_recentScans.length > 1)
                TextButton(
                  onPressed: _showSelectScanModal,
                  style: TextButton.styleFrom(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    visualDensity: VisualDensity.compact,
                  ),
                  child: const Text('Ganti Scan', style: TextStyle(fontSize: 12)),
                ),
            ],
          ),
          const Divider(height: 16, color: Color(0xFFF1F5F9)),
          if (_isLoadingScans)
            const Center(
              child: Padding(
                padding: EdgeInsets.all(8.0),
                child: CircularProgressIndicator(strokeWidth: 2),
              ),
            )
          else if (scan != null) ...[
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    color: const Color(0xFFFEF3C7),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Icon(Icons.coronavirus_rounded, color: Color(0xFFD97706), size: 24),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        scan.predictedClass,
                        style: const TextStyle(
                          fontSize: 15,
                          fontWeight: FontWeight.w900,
                          color: Color(0xFF1E293B),
                        ),
                      ),
                      const SizedBox(height: 2),
                      Row(
                        children: [
                          if (scan.farmName != null) ...[
                            Text(
                              scan.farmName!,
                              style: const TextStyle(fontSize: 12, color: Color(0xFF64748B)),
                            ),
                            const Text(' • ', style: TextStyle(color: Color(0xFF94A3B8))),
                          ],
                          Text(
                            scan.confidence != null
                                ? '${(scan.confidence! * 100).toStringAsFixed(1)}% Akurat'
                                : 'Terverifikasi AI',
                            style: const TextStyle(
                              fontSize: 11.5,
                              fontWeight: FontWeight.w700,
                              color: Color(0xFF059669),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ] else ...[
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: const Color(0xFFFEF2F2),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: const Color(0xFFFECACA)),
              ),
              child: Row(
                children: [
                  const Icon(Icons.info_outline_rounded, color: Color(0xFFDC2626), size: 20),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Belum ada data scan daun',
                          style: TextStyle(fontSize: 12.5, fontWeight: FontWeight.w800, color: Color(0xFF991B1B)),
                        ),
                        const SizedBox(height: 2),
                        const Text(
                          'Periksa tanaman Anda terlebih dahulu sebelum membuat laporan.',
                          style: TextStyle(fontSize: 11.5, color: Color(0xFFB91C1C)),
                        ),
                        const SizedBox(height: 6),
                        InkWell(
                          onTap: () => context.push('/plant-check'),
                          child: const Text(
                            '🌱 Buka Periksa Tanaman Sekarang →',
                            style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: Color(0xFF166534)),
                          ),
                        ),
                      ],
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

  void _showSelectScanModal() {
    showModalBottomSheet<void>(
      context: context,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) => SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(18),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'Pilih Hasil Pemeriksaan Daun',
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.w900),
              ),
              const SizedBox(height: 12),
              Flexible(
                child: ListView.builder(
                  shrinkWrap: true,
                  itemCount: _recentScans.length,
                  itemBuilder: (ctx, i) {
                    final s = _recentScans[i];
                    return ListTile(
                      contentPadding: EdgeInsets.zero,
                      leading: const CircleAvatar(
                        backgroundColor: Color(0xFFECFDF5),
                        child: Icon(Icons.grass_rounded, color: Color(0xFF059669)),
                      ),
                      title: Text(s.predictedClass, style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13.5)),
                      subtitle: Text(s.farmName ?? 'Petak Sawah', style: const TextStyle(fontSize: 12)),
                      trailing: _activeScanId == s.id
                          ? const Icon(Icons.check_circle_rounded, color: Color(0xFF059669))
                          : null,
                      onTap: () {
                        setState(() {
                          _selectedScan = s;
                          _activeScanId = s.id;
                        });
                        Navigator.of(ctx).pop();
                      },
                    );
                  },
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  // ================= MAP & LOCATION =================
  Widget _buildMapAndLocationSection() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Row(
                children: [
                  Icon(Icons.location_on_rounded, color: Color(0xFF059669), size: 18),
                  SizedBox(width: 6),
                  Text(
                    'Peta Titik Peringatan',
                    style: TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w800,
                      color: Color(0xFF1E293B),
                    ),
                  ),
                ],
              ),
              ElevatedButton.icon(
                onPressed: _isLocatingGps ? null : () => _fetchGpsLocation(silently: false),
                icon: _isLocatingGps
                    ? const SizedBox(
                        width: 12,
                        height: 12,
                        child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                      )
                    : const Icon(Icons.my_location_rounded, size: 14),
                label: Text(_isLocatingGps ? 'Mencari...' : 'GPS Saya', style: const TextStyle(fontSize: 11.5)),
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF059669),
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                  visualDensity: VisualDensity.compact,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),

          // Mini Map
          ClipRRect(
            borderRadius: BorderRadius.circular(14),
            child: SizedBox(
              height: 200,
              child: FlutterMap(
                mapController: _mapController,
                options: MapOptions(
                  initialCenter: _currentPoint,
                  initialZoom: 13.0,
                  onTap: (tapPosition, point) {
                    _updateCoordinates(point.latitude, point.longitude);
                  },
                ),
                children: [
                  TileLayer(
                    urlTemplate: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                    userAgentPackageName: 'com.padi.smartfarming',
                  ),
                  // Radar Circle Layer
                  CircleLayer(
                    circles: [
                      CircleMarker(
                        point: _currentPoint,
                        radius: _radiusKm * 1000, // convert km to meters
                        useRadiusInMeter: true,
                        color: const Color(0xFFEF4444).withValues(alpha: 0.20),
                        borderColor: const Color(0xFFEF4444),

                        borderStrokeWidth: 2.0,
                      ),
                    ],
                  ),
                  // Center Marker
                  MarkerLayer(
                    markers: [
                      Marker(
                        point: _currentPoint,
                        width: 44,
                        height: 44,
                        child: const Icon(
                          Icons.location_pin,
                          color: Color(0xFFDC2626),
                          size: 40,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),

          const SizedBox(height: 10),
          const Row(
            children: [
              Icon(Icons.touch_app_outlined, size: 14, color: Color(0xFF64748B)),
              SizedBox(width: 4),
              Text(
                'Ketuk pada peta untuk menggeser titik peringatan',
                style: TextStyle(fontSize: 11, color: Color(0xFF64748B)),
              ),
            ],
          ),

          const SizedBox(height: 12),

          // Farm dropdown selector if farms available
          if (_farms.isNotEmpty) ...[
            DropdownButtonFormField<int>(
              decoration: InputDecoration(
                labelText: 'Pilih Titik Berdasarkan Sawah Terdaftar',
                labelStyle: const TextStyle(fontSize: 12, color: Color(0xFF64748B)),
                contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                filled: true,
                fillColor: const Color(0xFFF8FAFC),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
                ),
              ),
              items: _farms.map((f) {
                return DropdownMenuItem<int>(
                  value: f.id,
                  child: Text(f.name, style: const TextStyle(fontSize: 13)),
                );
              }).toList(),
              onChanged: (id) {
                if (id == null) return;
                final farm = _farms.firstWhere((f) => f.id == id);
                if (farm.latitude != 0 && farm.longitude != 0) {
                  _updateCoordinates(farm.latitude, farm.longitude);
                }
              },
            ),
            const SizedBox(height: 10),
          ],

          // Lat/Lng Row Display
          Row(
            children: [
              Expanded(
                child: TextFormField(
                  controller: _latitudeController,
                  keyboardType: const TextInputType.numberWithOptions(decimal: true, signed: true),
                  decoration: const InputDecoration(
                    labelText: 'Latitude',
                    labelStyle: TextStyle(fontSize: 12),
                    prefixIcon: Icon(Icons.map_outlined, size: 16),
                  ),
                  style: const TextStyle(fontSize: 12.5),
                  onChanged: (val) {
                    final lat = double.tryParse(val);
                    if (lat != null) _updateCoordinates(lat, _currentPoint.longitude);
                  },
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: TextFormField(
                  controller: _longitudeController,
                  keyboardType: const TextInputType.numberWithOptions(decimal: true, signed: true),
                  decoration: const InputDecoration(
                    labelText: 'Longitude',
                    labelStyle: TextStyle(fontSize: 12),
                    prefixIcon: Icon(Icons.explore_outlined, size: 16),
                  ),
                  style: const TextStyle(fontSize: 12.5),
                  onChanged: (val) {
                    final lng = double.tryParse(val);
                    if (lng != null) _updateCoordinates(_currentPoint.latitude, lng);
                  },
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  // ================= RADIUS =================
  Widget _buildRadiusSection() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Row(
                children: [
                  Icon(Icons.radar_rounded, color: Color(0xFF059669), size: 18),
                  SizedBox(width: 6),
                  Text(
                    'Jangkauan Radius Siaran',
                    style: TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w800,
                      color: Color(0xFF1E293B),
                    ),
                  ),
                ],
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: const Color(0xFFECFDF5),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: const Color(0xFFA7F3D0)),
                ),
                child: Text(
                  '${_radiusKm.toStringAsFixed(1)} km',
                  style: const TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w900,
                    color: Color(0xFF047857),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),

          // Quick Radius Chips
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Row(
              children: [
                _buildRadiusChip(1.0, '1 km (Sekitar Petak)'),
                _buildRadiusChip(3.0, '3 km (1 Desa)'),
                _buildRadiusChip(5.0, '5 km (Antar Desa)'),
                _buildRadiusChip(10.0, '10 km (1 Kecamatan)'),
              ],
            ),
          ),

          const SizedBox(height: 10),

          // Slider
          Slider(
            value: _radiusKm.clamp(1.0, 20.0),
            min: 1.0,
            max: 20.0,
            divisions: 19,
            activeColor: const Color(0xFF059669),
            label: '${_radiusKm.toStringAsFixed(1)} km',
            onChanged: _onRadiusChanged,
          ),
        ],
      ),
    );
  }

  Widget _buildRadiusChip(double value, String label) {
    final isSelected = (_radiusKm - value).abs() < 0.2;
    return Padding(
      padding: const EdgeInsets.only(right: 6),
      child: ChoiceChip(
        label: Text(
          label,
          style: TextStyle(
            fontSize: 11.5,
            fontWeight: FontWeight.w700,
            color: isSelected ? Colors.white : const Color(0xFF334155),
          ),
        ),
        selected: isSelected,
        selectedColor: const Color(0xFF059669),
        backgroundColor: const Color(0xFFF1F5F9),
        showCheckmark: false,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        onSelected: (_) => _onRadiusChanged(value),
      ),
    );
  }

  // ================= NOTES =================
  Widget _buildNotesSection() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Row(
            children: [
              Icon(Icons.edit_note_rounded, color: Color(0xFF059669), size: 20),
              SizedBox(width: 6),
              Text(
                'Catatan Tambahan Lapangan (Opsional)',
                style: TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w800,
                  color: Color(0xFF1E293B),
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          TextFormField(
            controller: _noteController,
            maxLines: 2,
            style: const TextStyle(fontSize: 13),
            decoration: InputDecoration(
              hintText: 'Contoh: Bercak mulai meluas ke 3 petak tetangga, disarankan semprot fungisida pagi hari.',
              hintStyle: const TextStyle(fontSize: 12, color: Color(0xFF94A3B8)),
              filled: true,
              fillColor: const Color(0xFFF8FAFC),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(12),
                borderSide: const BorderSide(color: Color(0xFFE2E8F0)),
              ),
            ),
          ),
        ],
      ),
    );
  }

  // ================= CONSENT =================
  Widget _buildConsentSection() {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Checkbox(
            value: _consentGiven,
            activeColor: const Color(0xFF059669),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(4)),
            onChanged: (val) => setState(() => _consentGiven = val ?? true),
          ),
          const SizedBox(width: 8),
          const Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Setujui Siaran Radar Komunitas',
                  style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w800,
                    color: Color(0xFF1E293B),
                  ),
                ),
                SizedBox(height: 2),
                Text(
                  'Data lokasi dan jenis penyakit akan dibagikan secara anonim untuk memperingatkan petani sekitar.',
                  style: TextStyle(fontSize: 11.5, color: Color(0xFF64748B), height: 1.35),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  // ================= SUBMIT BUTTON =================
  Widget _buildSubmitButton() {
    return SizedBox(
      height: 54,
      child: FilledButton.icon(
        onPressed: _isLoading || _activeScanId == null ? null : _submitReport,
        icon: _isLoading
            ? const SizedBox(
                width: 18,
                height: 18,
                child: CircularProgressIndicator(strokeWidth: 2.2, color: Colors.white),
              )
            : const Icon(Icons.cell_tower_rounded, size: 20),
        label: Text(
          _isLoading ? 'Menyiarkan Peringatan...' : 'Siarkan ke Radar Komunitas',
          style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w900),
        ),
        style: FilledButton.styleFrom(
          backgroundColor: const Color(0xFF059669),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
          elevation: 2,
        ),
      ),
    );
  }
}