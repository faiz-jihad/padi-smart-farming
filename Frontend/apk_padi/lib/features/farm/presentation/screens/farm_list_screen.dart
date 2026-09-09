import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:latlong2/latlong.dart' as latlng;
import 'package:padi/core/providers/app_providers.dart';
import 'package:padi/core/utils/debouncer.dart';
import 'package:padi/features/farm/data/models/farm_model.dart';
import 'package:padi/features/farm/data/services/farm_api_service.dart';
import 'package:padi/features/farm/presentation/widgets/farm_card.dart';
import 'package:padi/features/farm/presentation/widgets/farm_skeleton.dart';
import 'package:padi/features/farm/presentation/widgets/farm_stats_card.dart';
import 'package:padi/features/planting_calendar/data/services/planting_calendar_api_service.dart';
import 'package:padi/features/planting_calendar/presentation/widgets/planting_calendar_card.dart';

abstract final class _FarmPalette {
  static const white = Colors.white;
  static const green900 = Color(0xFF064E3B);
  static const green800 = Color(0xFF065F46);
  static const green700 = Color(0xFF047857);
  static const green600 = Color(0xFF059669);
  static const green200 = Color(0xFFBBF7D0);
  static const green50 = Color(0xFFF0FDF4);
  static const border = Color(0xFFDDF7E6);
}

final farmApiServiceProvider = Provider<FarmApiService>(
  (ref) => FarmApiService(ref.read(apiClientProvider)),
);

final userFarmsProvider = FutureProvider.autoDispose<List<FarmModel>>((ref) {
  return ref.read(farmApiServiceProvider).fetchFarms();
});

final farmCalendarApiServiceProvider = Provider<PlantingCalendarApiService>(
  (ref) => PlantingCalendarApiService(ref.read(apiClientProvider)),
);

class FarmListScreen extends ConsumerStatefulWidget {
  const FarmListScreen({super.key});

  @override
  ConsumerState<FarmListScreen> createState() => _FarmListScreenState();
}

class _FarmListScreenState extends ConsumerState<FarmListScreen> {
  final TextEditingController _searchController = TextEditingController();
  final Debouncer _searchDebouncer = Debouncer(milliseconds: 250);
  late final MapController _mapController;
  final DraggableScrollableController _sheetController =
      DraggableScrollableController();

  bool _isSatelliteLayer = true;
  FarmModel? _selectedFarm;

  @override
  void initState() {
    super.initState();
    _mapController = MapController();
  }

  @override
  void dispose() {
    _searchDebouncer.dispose();
    _searchController.dispose();
    _sheetController.dispose();
    super.dispose();
  }

  latlng.LatLng _calculateCenter(List<FarmModel> farms) {
    if (_selectedFarm != null) {
      if (_selectedFarm!.latitude != 0 || _selectedFarm!.longitude != 0) {
        return latlng.LatLng(_selectedFarm!.latitude, _selectedFarm!.longitude);
      }
      if (_selectedFarm!.boundaryCoordinates.isNotEmpty) {
        return _calculateBoundaryCenter(_selectedFarm!);
      }
    }

    if (farms.isEmpty) {
      return const latlng.LatLng(-6.3265, 108.3242); // Default Indramayu Center
    }

    final farm = farms.first;
    if (farm.latitude != 0 || farm.longitude != 0) {
      return latlng.LatLng(farm.latitude, farm.longitude);
    }
    return _calculateBoundaryCenter(farm);
  }

  latlng.LatLng _calculateBoundaryCenter(FarmModel farm) {
    if (farm.boundaryCoordinates.isEmpty) {
      return const latlng.LatLng(-6.3265, 108.3242);
    }
    final lat =
        farm.boundaryCoordinates.fold<double>(0, (sum, p) => sum + p.lat) /
        farm.boundaryCoordinates.length;
    final lng =
        farm.boundaryCoordinates.fold<double>(0, (sum, p) => sum + p.lng) /
        farm.boundaryCoordinates.length;
    return latlng.LatLng(lat, lng);
  }

  String _irrigationLabel(String value) {
    return switch (value.toLowerCase()) {
      'irrigated' => 'Irigasi Teknis',
      'semi_irrigated' => 'Setengah Teknis',
      'rainfed' => 'Tadah Hujan',
      'tidal' => 'Pasang Surut',
      _ => value,
    };
  }

  String _formatAreaHa(double areaHa) {
    return areaHa.toStringAsFixed(areaHa == areaHa.roundToDouble() ? 0 : 1);
  }

  String _getRegionTitle(List<FarmModel> farms) {
    if (_selectedFarm != null) {
      if (_selectedFarm!.regency != null) {
        return '${_selectedFarm!.regency!.name.toUpperCase()}, JAWA BARAT';
      }
      if (_selectedFarm!.district != null) {
        return 'KEC. ${_selectedFarm!.district!.name.toUpperCase()}';
      }
    }
    for (final farm in farms) {
      if (farm.regency != null) {
        return '${farm.regency!.name.toUpperCase()}, JAWA BARAT';
      }
    }
    return 'INDRAMAYU, JAWA BARAT';
  }

  void _recenterMap(List<FarmModel> farms) {
    final center = _calculateCenter(farms);
    _mapController.move(center, 15.5);
  }

  void _showStatsSheet(BuildContext context, List<FarmModel> farms) {
    showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (modalContext) {
        return Container(
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
          ),
          padding: const EdgeInsets.fromLTRB(20, 14, 20, 28),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Center(
                child: Container(
                  width: 44,
                  height: 5,
                  decoration: BoxDecoration(
                    color: _FarmPalette.green200,
                    borderRadius: BorderRadius.circular(3),
                  ),
                ),
              ),
              const SizedBox(height: 16),
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: const Color(0xFFECFDF5),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: const Color(0xFFA7F3D0)),
                    ),
                    child: const Icon(
                      Icons.bar_chart_rounded,
                      color: Color(0xFF059669),
                      size: 24,
                    ),
                  ),
                  const SizedBox(width: 12),
                  const Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Ringkasan Statistik Lahan',
                          style: TextStyle(
                            fontSize: 17,
                            fontWeight: FontWeight.w900,
                            color: _FarmPalette.green900,
                          ),
                        ),
                        SizedBox(height: 2),
                        Text(
                          'Total hamparan sawah dan sebaran irigasi',
                          style: TextStyle(
                            fontSize: 12,
                            color: _FarmPalette.green700,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 18),
              FarmStatsCard(
                farms: farms,
                onTapMap: () => Navigator.of(modalContext).pop(),
              ),
            ],
          ),
        );
      },
    );
  }

  void _showPlantingCalendar(FarmModel farm) async {
    final calendarApi = ref.read(farmCalendarApiServiceProvider);
    final calendar = await calendarApi
        .getCalendarForFarm(farm.id)
        .catchError((_) => null);

    if (!mounted) return;

    showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (modalContext) {
        return Container(
          padding: const EdgeInsets.fromLTRB(18, 12, 18, 24),
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
          ),
          child: SafeArea(
            top: false,
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Center(
                  child: Container(
                    width: 44,
                    height: 5,
                    decoration: BoxDecoration(
                      color: _FarmPalette.green200,
                      borderRadius: BorderRadius.circular(3),
                    ),
                  ),
                ),
                const SizedBox(height: 16),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            farm.name,
                            style: const TextStyle(
                              color: _FarmPalette.green900,
                              fontSize: 18,
                              fontWeight: FontWeight.w900,
                            ),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            farm.locationDescription,
                            style: const TextStyle(
                              color: _FarmPalette.green700,
                              fontSize: 12.5,
                            ),
                          ),
                        ],
                      ),
                    ),
                    IconButton(
                      onPressed: () => Navigator.pop(modalContext),
                      icon: const Icon(
                        Icons.close_rounded,
                        color: _FarmPalette.green700,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 16),
                if (calendar != null)
                  PlantingCalendarCard(calendar: calendar)
                else
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: _FarmPalette.green50,
                      borderRadius: BorderRadius.circular(16),
                      border: Border.all(color: _FarmPalette.border),
                    ),
                    child: const Row(
                      children: [
                        Icon(
                          Icons.event_busy_outlined,
                          color: _FarmPalette.green700,
                          size: 22,
                        ),
                        SizedBox(width: 10),
                        Expanded(
                          child: Text(
                            'Belum ada rekomendasi kalender tanam aktif untuk lahan ini.',
                            style: TextStyle(
                              color: _FarmPalette.green700,
                              fontSize: 13,
                              fontWeight: FontWeight.w600,
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
      },
    );
  }

  void _confirmDeleteFarm(FarmModel farm) {
    showDialog<void>(
      context: context,
      builder: (dialogContext) {
        return AlertDialog(
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(20),
          ),
          title: Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: _FarmPalette.green50,
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(
                  Icons.delete_outline_rounded,
                  color: _FarmPalette.green700,
                  size: 24,
                ),
              ),
              const SizedBox(width: 12),
              const Expanded(
                child: Text(
                  'Hapus Lahan?',
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.w900,
                    color: _FarmPalette.green900,
                  ),
                ),
              ),
            ],
          ),
          content: Text(
            'Apakah Anda yakin ingin menghapus "${farm.name}"? Seluruh data batas poligon dan riwayat aktivitas terkait lahan ini akan dihapus.',
            style: const TextStyle(
              fontSize: 13.5,
              color: _FarmPalette.green800,
              height: 1.4,
            ),
          ),
          actionsPadding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
          actions: [
            OutlinedButton(
              onPressed: () => Navigator.of(dialogContext).pop(),
              style: OutlinedButton.styleFrom(
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(10),
                ),
                side: const BorderSide(color: _FarmPalette.green200),
              ),
              child: const Text(
                'Batal',
                style: TextStyle(color: _FarmPalette.green800),
              ),
            ),
            FilledButton(
              onPressed: () async {
                Navigator.of(dialogContext).pop();
                try {
                  await ref.read(farmApiServiceProvider).deleteFarm(farm.id);
                  if (mounted) {
                    if (_selectedFarm?.id == farm.id) {
                      setState(() => _selectedFarm = null);
                    }
                    ref.invalidate(userFarmsProvider);
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(
                        content: Text('Lahan "${farm.name}" berhasil dihapus.'),
                        backgroundColor: const Color(0xFF059669),
                        behavior: SnackBarBehavior.floating,
                      ),
                    );
                  }
                } catch (_) {
                  if (mounted) {
                    ScaffoldMessenger.of(context).showSnackBar(
                      const SnackBar(
                        content: Text(
                          'Gagal menghapus lahan. Terjadi kendala di server, silakan coba lagi.',
                        ),
                        backgroundColor: _FarmPalette.green800,
                        behavior: SnackBarBehavior.floating,
                      ),
                    );
                  }
                }
              },
              style: FilledButton.styleFrom(
                backgroundColor: _FarmPalette.green800,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(10),
                ),
              ),
              child: const Text('Ya, Hapus'),
            ),
          ],
        );
      },
    );
  }

  List<Polygon> _buildPolygons(List<FarmModel> farms) {
    return farms.where((farm) => farm.boundaryCoordinates.length >= 3).map((
      farm,
    ) {
      final isSelected = _selectedFarm?.id == farm.id;
      final points = farm.boundaryCoordinates
          .map((p) => latlng.LatLng(p.lat, p.lng))
          .toList(growable: false);

      return Polygon(
        points: points,
        color: isSelected
            ? _FarmPalette.green600.withValues(alpha: 0.35)
            : _FarmPalette.green600.withValues(alpha: 0.20),
        borderColor: isSelected ? _FarmPalette.green900 : _FarmPalette.green600,
        borderStrokeWidth: isSelected ? 3.5 : 2.2,
      );
    }).toList();
  }

  List<Marker> _buildMarkers(List<FarmModel> farms) {
    return farms.asMap().entries.map((entry) {
      final farm = entry.value;
      final point = farm.latitude != 0 || farm.longitude != 0
          ? latlng.LatLng(farm.latitude, farm.longitude)
          : _calculateBoundaryCenter(farm);
      final isSelected = _selectedFarm?.id == farm.id;
      final nodeColor = isSelected
          ? _FarmPalette.green900
          : _FarmPalette.green700;
      final nodeBg = isSelected ? _FarmPalette.green200 : _FarmPalette.green50;

      return Marker(
        point: point,
        width: 44,
        height: 44,
        child: GestureDetector(
          onTap: () {
            setState(() => _selectedFarm = farm);
            _mapController.move(point, 16.5);
          },
          child: Container(
            decoration: BoxDecoration(
              color: Colors.white,
              shape: BoxShape.circle,
              border: Border.all(
                color: isSelected
                    ? _FarmPalette.green900
                    : _FarmPalette.green200,
                width: isSelected ? 2.8 : 1.5,
              ),
              boxShadow: const [
                BoxShadow(
                  color: Color(0x24064E3B),
                  blurRadius: 6,
                  offset: Offset(0, 3),
                ),
              ],
            ),
            child: Center(
              child: Container(
                width: 30,
                height: 30,
                decoration: BoxDecoration(
                  color: nodeBg,
                  shape: BoxShape.circle,
                ),
                child: Icon(Icons.memory_rounded, color: nodeColor, size: 18),
              ),
            ),
          ),
        ),
      );
    }).toList();
  }

  @override
  Widget build(BuildContext context) {
    final farmsAsync = ref.watch(userFarmsProvider);

    return Scaffold(
      body: farmsAsync.when(
        data: (farms) {
          final center = _calculateCenter(farms);
          final keyword = _searchController.text.trim().toLowerCase();
          final filteredFarms = keyword.isEmpty
              ? farms
              : farms.where((farm) {
                  final name = farm.name.toLowerCase();
                  final loc = farm.locationDescription.toLowerCase();
                  final soil = (farm.soilType ?? '').toLowerCase();
                  final irr = farm.irrigationType.toLowerCase();
                  return name.contains(keyword) ||
                      loc.contains(keyword) ||
                      soil.contains(keyword) ||
                      irr.contains(keyword);
                }).toList();

          return Stack(
            children: [
              // 1. Fullscreen Aerial Drone / Satellite Base Map
              Positioned.fill(
                child: FlutterMap(
                  mapController: _mapController,
                  options: MapOptions(
                    initialCenter: center,
                    initialZoom: 15.5,
                    minZoom: 4,
                    maxZoom: 19,
                  ),
                  children: [
                    TileLayer(
                      urlTemplate: _isSatelliteLayer
                          ? 'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}'
                          : 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                      userAgentPackageName: 'com.padi.app',
                      maxZoom: 19,
                    ),
                    PolygonLayer(polygons: _buildPolygons(farms)),
                    MarkerLayer(markers: _buildMarkers(farms)),
                  ],
                ),
              ),

              // 2. Top Floating Navigation Bar
              Positioned(
                top: MediaQuery.of(context).padding.top + 10,
                left: 16,
                right: 16,
                child: Row(
                  children: [
                    // Back / Menu Button
                    _buildFloatingButton(
                      icon: Icons.arrow_back_rounded,
                      tooltip: 'Kembali',
                      onTap: () {
                        if (context.canPop()) {
                          context.pop();
                        } else {
                          context.go('/home');
                        }
                      },
                    ),
                    const Spacer(),
                    // + Add Farm Action Button
                    Container(
                      width: 48,
                      height: 48,
                      decoration: BoxDecoration(
                        color: _FarmPalette.green700,
                        borderRadius: BorderRadius.circular(16),
                        boxShadow: const [
                          BoxShadow(
                            color: Color(0x24064E3B),
                            blurRadius: 10,
                            offset: Offset(0, 4),
                          ),
                        ],
                      ),
                      child: IconButton(
                        tooltip: 'Tambah Lahan Sawah',
                        icon: const Icon(
                          Icons.add_rounded,
                          color: Colors.white,
                          size: 28,
                        ),
                        onPressed: () async {
                          final result = await context.push('/farms/add');
                          if (result == true) {
                            ref.invalidate(userFarmsProvider);
                          }
                        },
                      ),
                    ),
                    const SizedBox(width: 10),
                    // Stats / Analytics Button
                    _buildFloatingButton(
                      icon: Icons.bar_chart_rounded,
                      tooltip: 'Statistik Lahan',
                      onTap: () => _showStatsSheet(context, farms),
                    ),
                  ],
                ),
              ),

              // 3. Right Floating GIS Tool Column
              Positioned(
                right: 16,
                bottom: MediaQuery.of(context).size.height * 0.36,
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    _buildFloatingButton(
                      icon: Icons.refresh_rounded,
                      tooltip: 'Segarkan Lahan',
                      size: 44,
                      iconSize: 20,
                      onTap: () => ref.invalidate(userFarmsProvider),
                    ),
                    const SizedBox(height: 10),
                    _buildFloatingButton(
                      icon: _isSatelliteLayer
                          ? Icons.map_outlined
                          : Icons.satellite_alt_rounded,
                      tooltip: _isSatelliteLayer
                          ? 'Peta Jalan (OSM)'
                          : 'Citra Satelit Drone',
                      size: 44,
                      iconSize: 20,
                      onTap: () => setState(
                        () => _isSatelliteLayer = !_isSatelliteLayer,
                      ),
                    ),
                    const SizedBox(height: 10),
                    _buildFloatingButton(
                      icon: Icons.my_location_rounded,
                      tooltip: 'Pusatkan Peta',
                      size: 44,
                      iconSize: 20,
                      onTap: () => _recenterMap(farms),
                    ),
                  ],
                ),
              ),

              // 4. Bottom Interactive Draggable Sheet
              DraggableScrollableSheet(
                controller: _sheetController,
                initialChildSize: 0.32,
                minChildSize: 0.22,
                maxChildSize: 0.85,
                builder: (sheetContext, scrollController) {
                  return Container(
                    decoration: const BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.vertical(
                        top: Radius.circular(28),
                      ),
                      boxShadow: [
                        BoxShadow(
                          color: Color(0x1A064E3B),
                          blurRadius: 18,
                          offset: Offset(0, -4),
                        ),
                      ],
                    ),
                    child: ListView(
                      controller: scrollController,
                      padding: const EdgeInsets.fromLTRB(18, 12, 18, 28),
                      children: [
                        // Top Drag Handle Bar
                        Center(
                          child: Container(
                            width: 44,
                            height: 5,
                            decoration: BoxDecoration(
                              color: _FarmPalette.green200,
                              borderRadius: BorderRadius.circular(3),
                            ),
                          ),
                        ),
                        const SizedBox(height: 14),

                        // Search Bar
                        Container(
                          decoration: BoxDecoration(
                            color: _FarmPalette.green50,
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(color: _FarmPalette.border),
                          ),
                          child: TextField(
                            controller: _searchController,
                            onChanged: (_) =>
                                _searchDebouncer.run(() => setState(() {})),
                            style: const TextStyle(
                              color: _FarmPalette.green900,
                              fontSize: 14,
                              fontWeight: FontWeight.w600,
                            ),
                            decoration: InputDecoration(
                              hintText: 'Cari lahan sawah, varietas, lokasi...',
                              hintStyle: const TextStyle(
                                color: _FarmPalette.green700,
                                fontSize: 13.5,
                              ),
                              prefixIcon: const Icon(
                                Icons.search_rounded,
                                color: _FarmPalette.green700,
                                size: 22,
                              ),
                              suffixIcon: _searchController.text.isNotEmpty
                                  ? IconButton(
                                      icon: const Icon(
                                        Icons.cancel_rounded,
                                        color: _FarmPalette.green700,
                                        size: 18,
                                      ),
                                      onPressed: () {
                                        _searchController.clear();
                                        setState(() {});
                                      },
                                    )
                                  : null,
                              border: InputBorder.none,
                              contentPadding: const EdgeInsets.symmetric(
                                horizontal: 14,
                                vertical: 12,
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(height: 14),

                        // Sub-header Row (e.g. ^ INDRAMAYU, JAWA BARAT  •  8 LAHAN)
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Expanded(
                              child: Row(
                                children: [
                                  const Icon(
                                    Icons.keyboard_arrow_up_rounded,
                                    size: 20,
                                    color: _FarmPalette.green700,
                                  ),
                                  const SizedBox(width: 4),
                                  Expanded(
                                    child: Text(
                                      _getRegionTitle(farms),
                                      maxLines: 1,
                                      overflow: TextOverflow.ellipsis,
                                      style: const TextStyle(
                                        fontSize: 11.5,
                                        fontWeight: FontWeight.w900,
                                        color: _FarmPalette.green800,
                                        letterSpacing: 0.6,
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            const SizedBox(width: 8),
                            Text(
                              '${filteredFarms.length} LAHAN SAWAH',
                              style: const TextStyle(
                                fontSize: 11.5,
                                fontWeight: FontWeight.w900,
                                color: _FarmPalette.green700,
                                letterSpacing: 0.4,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 12),

                        // List of Field Cards
                        if (filteredFarms.isEmpty)
                          const Padding(
                            padding: EdgeInsets.symmetric(vertical: 24),
                            child: Center(
                              child: Text(
                                'Tidak ada lahan yang cocok dengan pencarian.',
                                style: TextStyle(
                                  color: _FarmPalette.green700,
                                  fontSize: 13,
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                            ),
                          )
                        else
                          ...filteredFarms.map(
                            (farm) => _buildFarmCardItem(farm),
                          ),
                      ],
                    ),
                  );
                },
              ),
            ],
          );
        },
        loading: () => const Center(child: FarmSkeleton()),
        error: (error, stackTrace) => _buildErrorState(),
      ),
    );
  }

  Widget _buildFloatingButton({
    required IconData icon,
    required String tooltip,
    required VoidCallback onTap,
    double size = 48,
    double iconSize = 24,
  }) {
    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(
        color: _FarmPalette.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: _FarmPalette.border),
        boxShadow: const [
          BoxShadow(
            color: Color(0x24064E3B),
            blurRadius: 10,
            offset: Offset(0, 4),
          ),
        ],
      ),
      child: IconButton(
        tooltip: tooltip,
        icon: Icon(icon, color: _FarmPalette.green900, size: iconSize),
        padding: EdgeInsets.zero,
        onPressed: onTap,
      ),
    );
  }

  Widget _buildFarmCardItem(FarmModel farm) {
    final isSelected = _selectedFarm?.id == farm.id;

    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      decoration: BoxDecoration(
        color: isSelected ? _FarmPalette.green50 : _FarmPalette.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(
          color: isSelected ? _FarmPalette.green600 : _FarmPalette.border,
          width: isSelected ? 1.8 : 1.0,
        ),
        boxShadow: const [
          BoxShadow(
            color: Color(0x14064E3B),
            blurRadius: 8,
            offset: Offset(0, 2),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(18),
          onTap: () {
            setState(() => _selectedFarm = farm);
            final point = farm.latitude != 0 || farm.longitude != 0
                ? latlng.LatLng(farm.latitude, farm.longitude)
                : _calculateBoundaryCenter(farm);
            _mapController.move(point, 16.5);
          },
          child: Padding(
            padding: const EdgeInsets.all(12),
            child: Column(
              children: [
                Row(
                  children: [
                    // Mini Satellite Boundary Thumbnail
                    Container(
                      width: 62,
                      height: 62,
                      decoration: BoxDecoration(
                        color: _FarmPalette.green50,
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(color: _FarmPalette.green200),
                      ),
                      child: ClipRRect(
                        borderRadius: BorderRadius.circular(14),
                        child: Stack(
                          fit: StackFit.expand,
                          children: [
                            Container(color: _FarmPalette.green50),
                            if (farm.boundaryCoordinates.length >= 3)
                              CustomPaint(
                                painter: FarmPolygonThumbnailPainter(
                                  farm.boundaryCoordinates,
                                ),
                              )
                            else
                              const Center(
                                child: Icon(
                                  Icons.grass_rounded,
                                  color: _FarmPalette.green700,
                                  size: 26,
                                ),
                              ),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(width: 14),

                    // Farm Info (Name & Crop Details)
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            farm.name,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: const TextStyle(
                              fontSize: 15.5,
                              fontWeight: FontWeight.w900,
                              color: _FarmPalette.green900,
                            ),
                          ),
                          const SizedBox(height: 3),
                          Text(
                            '${_formatAreaHa(farm.areaHa)} Ha | ${_irrigationLabel(farm.irrigationType)}',
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: const TextStyle(
                              fontSize: 12.5,
                              color: _FarmPalette.green700,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                          if (farm.soilType != null &&
                              farm.soilType!.isNotEmpty) ...[
                            const SizedBox(height: 2),
                            Text(
                              'Tanah: ${farm.soilType}',
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: const TextStyle(
                                fontSize: 11.5,
                                color: _FarmPalette.green700,
                              ),
                            ),
                          ],
                        ],
                      ),
                    ),

                    // Edit Pencil Button
                    IconButton(
                      tooltip: 'Detail & Catat Sawah',
                      icon: const Icon(
                        Icons.edit_outlined,
                        color: _FarmPalette.green700,
                        size: 22,
                      ),
                      onPressed: () =>
                          context.push('/land/timeline?farmId=${farm.id}'),
                    ),

                    // Delete Farm Button
                    IconButton(
                      tooltip: 'Hapus Lahan Sawah',
                      icon: const Icon(
                        Icons.delete_outline_rounded,
                        color: _FarmPalette.green800,
                        size: 22,
                      ),
                      onPressed: () => _confirmDeleteFarm(farm),
                    ),
                  ],
                ),

                // Expanded Quick Action Buttons when Selected
                if (isSelected) ...[
                  const SizedBox(height: 10),
                  const Divider(height: 1, color: _FarmPalette.border),
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      Expanded(
                        child: _buildItemActionChip(
                          icon: Icons.calendar_month_rounded,
                          label: 'Kalender',
                          onTap: () => _showPlantingCalendar(farm),
                        ),
                      ),
                      const SizedBox(width: 6),
                      Expanded(
                        child: _buildItemActionChip(
                          icon: Icons.science_outlined,
                          label: 'Pupuk',
                          onTap: () =>
                              context.push('/fertilizer?farmId=${farm.id}'),
                        ),
                      ),
                      const SizedBox(width: 6),
                      Expanded(
                        child: _buildItemActionChip(
                          icon: Icons.edit_note_rounded,
                          label: 'Aktivitas',
                          onTap: () => context.push(
                            '/land/activity/add?farmId=${farm.id}',
                          ),
                        ),
                      ),
                      const SizedBox(width: 6),
                      Material(
                        color: _FarmPalette.white,
                        borderRadius: BorderRadius.circular(10),
                        child: InkWell(
                          borderRadius: BorderRadius.circular(10),
                          onTap: () => _confirmDeleteFarm(farm),
                          child: Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 10,
                              vertical: 7,
                            ),
                            decoration: BoxDecoration(
                              borderRadius: BorderRadius.circular(10),
                              border: Border.all(color: _FarmPalette.green200),
                            ),
                            child: const Icon(
                              Icons.delete_outline_rounded,
                              size: 17,
                              color: _FarmPalette.green800,
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildItemActionChip({
    required IconData icon,
    required String label,
    required VoidCallback onTap,
  }) {
    return Material(
      color: _FarmPalette.white,
      borderRadius: BorderRadius.circular(10),
      child: InkWell(
        borderRadius: BorderRadius.circular(10),
        onTap: onTap,
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 7),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(10),
            border: Border.all(color: _FarmPalette.green200),
          ),
          child: FittedBox(
            fit: BoxFit.scaleDown,
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(icon, size: 15, color: _FarmPalette.green800),
                const SizedBox(width: 5),
                Text(
                  label,
                  style: const TextStyle(
                    fontSize: 11.5,
                    fontWeight: FontWeight.w800,
                    color: _FarmPalette.green800,
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildErrorState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(28),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              width: 72,
              height: 72,
              decoration: BoxDecoration(
                color: _FarmPalette.green50,
                borderRadius: BorderRadius.circular(20),
              ),
              child: const Icon(
                Icons.cloud_off_rounded,
                size: 36,
                color: _FarmPalette.green700,
              ),
            ),
            const SizedBox(height: 16),
            const Text(
              'Gagal memuat data lahan',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.w900,
                color: _FarmPalette.green900,
              ),
            ),
            const SizedBox(height: 6),
            const Text(
              'Terjadi kendala saat menyambung ke server. Periksa jaringan Anda.',
              textAlign: TextAlign.center,
              style: TextStyle(fontSize: 13, color: _FarmPalette.green700),
            ),
            const SizedBox(height: 16),
            FilledButton.icon(
              onPressed: () => ref.invalidate(userFarmsProvider),
              icon: const Icon(Icons.refresh_rounded, size: 18),
              label: const Text('Coba Lagi'),
              style: FilledButton.styleFrom(
                backgroundColor: const Color(0xFF065F46),
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(
                  horizontal: 20,
                  vertical: 12,
                ),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
