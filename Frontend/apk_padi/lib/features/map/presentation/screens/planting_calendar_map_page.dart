import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:latlong2/latlong.dart';
import 'package:padi/core/location/location_service.dart';
import 'package:padi/core/providers/app_providers.dart';
import 'package:padi/features/map/data/services/map_api_service.dart';
import 'package:padi/features/planting_calendar/data/services/planting_calendar_api_service.dart';
import 'package:padi/features/planting_calendar/presentation/widgets/planting_calendar_card.dart';
import 'package:padi/features/region/data/models/region_models.dart';
import 'package:padi/features/region/data/services/region_api_service.dart';

final locationServiceProvider = Provider<LocationService>(
  (ref) => const LocationService(),
);

final regionApiServiceProvider = Provider<RegionApiService>(
  (ref) => RegionApiService(ref.read(apiClientProvider)),
);

final mapApiServiceProvider = Provider<MapApiService>(
  (ref) => MapApiService(ref.read(apiClientProvider)),
);

final plantingCalendarApiServiceProvider = Provider<PlantingCalendarApiService>(
  (ref) => PlantingCalendarApiService(ref.read(apiClientProvider)),
);

class PlantingCalendarMapPage extends ConsumerStatefulWidget {
  const PlantingCalendarMapPage({super.key});

  @override
  ConsumerState<PlantingCalendarMapPage> createState() =>
      _PlantingCalendarMapPageState();
}

class _PlantingCalendarMapPageState
    extends ConsumerState<PlantingCalendarMapPage> {
  final MapController _mapController = MapController();

  final LatLng _currentCenter = const LatLng(
    -6.3271,
    108.3254,
  ); // Indramayu default
  LatLng? _userLocation;
  ResolvedLocationModel? _resolvedUserLocation;

  List<RegencyModel> _regencies = [];
  ProvinceModel? _selectedProvince;
  RegencyModel? _selectedRegency;

  List<Polygon> _polygonList = [];
  bool _isLoadingMap = false;

  @override
  void initState() {
    super.initState();
    _loadInitialRegions();
    _detectUserGps();
  }

  Future<void> _loadInitialRegions() async {
    final regionApi = ref.read(regionApiServiceProvider);
    try {
      final provinces = await regionApi.fetchProvinces();
      if (provinces.isNotEmpty) {
        _selectedProvince = provinces.first;
      }

      if (_selectedProvince != null) {
        final regencies = await regionApi.fetchRegencies(_selectedProvince!.id);
        setState(() {
          _regencies = regencies;
          if (regencies.isNotEmpty) {
            _selectedRegency = regencies.first;
          }
        });

        if (_selectedRegency != null) {
          await _loadDistrictsAndBoundaries(_selectedRegency!.id);
        }
      }
    } catch (_) {}
  }

  Future<void> _loadDistrictsAndBoundaries(int regencyId) async {
    setState(() => _isLoadingMap = true);
    final mapApi = ref.read(mapApiServiceProvider);

    try {
      final geojson = await mapApi.fetchDistrictBoundaries(regencyId);

      final polygons = <Polygon>[];

      if (geojson != null && geojson['features'] != null) {
        final features = geojson['features'] as List<dynamic>;
        for (final feature in features) {
          final properties =
              feature['properties'] as Map<String, dynamic>? ?? {};
          final geometry = feature['geometry'] as Map<String, dynamic>? ?? {};
          final districtId = properties['district_id'] as int? ?? 0;
          final districtName = properties['name'] as String? ?? 'Kecamatan';

          final parsedPolygons = _parseGeoJsonPolygon(
            geometry,
            districtId,
            districtName,
          );
          polygons.addAll(parsedPolygons);
        }
      }

      setState(() {
        _polygonList = polygons;
        _isLoadingMap = false;
      });
    } catch (_) {
      setState(() => _isLoadingMap = false);
    }
  }

  List<Polygon> _parseGeoJsonPolygon(
    Map<String, dynamic> geometry,
    int districtId,
    String districtName,
  ) {
    final type = geometry['type'] as String? ?? '';
    final coords = geometry['coordinates'] as List<dynamic>? ?? [];
    final polygons = <Polygon>[];

    if (type == 'Polygon') {
      if (coords.isNotEmpty) {
        final outerRing = coords[0] as List<dynamic>;
        final points = outerRing.map((p) {
          final lat = p is List && p.length > 1
              ? (p[1] is num
                    ? (p[1] as num).toDouble()
                    : double.tryParse(p[1].toString()) ?? 0.0)
              : 0.0;
          final lng = p is List && p.isNotEmpty
              ? (p[0] is num
                    ? (p[0] as num).toDouble()
                    : double.tryParse(p[0].toString()) ?? 0.0)
              : 0.0;
          return LatLng(lat, lng);
        }).toList();
        polygons.add(
          Polygon(
            points: points,
            color: const Color(0x3316A34A),
            borderColor: const Color(0xFF16A34A),
            borderStrokeWidth: 2.0,
          ),
        );
      }
    } else if (type == 'MultiPolygon') {
      for (final polyCoords in coords) {
        final ringList = polyCoords as List<dynamic>;
        if (ringList.isNotEmpty) {
          final outerRing = ringList[0] as List<dynamic>;
          final points = outerRing.map((p) {
            final lat = p is List && p.length > 1
                ? (p[1] is num
                      ? (p[1] as num).toDouble()
                      : double.tryParse(p[1].toString()) ?? 0.0)
                : 0.0;
            final lng = p is List && p.isNotEmpty
                ? (p[0] is num
                      ? (p[0] as num).toDouble()
                      : double.tryParse(p[0].toString()) ?? 0.0)
                : 0.0;
            return LatLng(lat, lng);
          }).toList();
          polygons.add(
            Polygon(
              points: points,
              color: const Color(0x3316A34A),
              borderColor: const Color(0xFF16A34A),
              borderStrokeWidth: 2.0,
            ),
          );
        }
      }
    }

    return polygons;
  }

  Future<void> _detectUserGps() async {
    final locService = ref.read(locationServiceProvider);
    final position = await locService.getCurrentPosition();

    if (position != null) {
      final userLatLng = LatLng(position.latitude, position.longitude);
      setState(() {
        _userLocation = userLatLng;
      });

      _mapController.move(userLatLng, 13.0);

      // Resolve GPS location to administrative region
      final regionApi = ref.read(regionApiServiceProvider);
      final resolved = await regionApi.resolveCoordinates(
        position.latitude,
        position.longitude,
      );

      if (resolved != null) {
        setState(() {
          _resolvedUserLocation = resolved;
        });

        if (mounted && resolved.district != null) {
          _showCalendarBottomSheet(
            resolved.district!.id,
            resolved.district!.name,
          );
        }
      }
    }
  }

  Future<void> _showCalendarBottomSheet(
    int districtId,
    String districtName,
  ) async {
    final calendarApi = ref.read(plantingCalendarApiServiceProvider);
    final calendar = await calendarApi.getCalendarByDistrict(districtId);

    if (!mounted) return;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) {
        return Container(
          padding: const EdgeInsets.all(20),
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Center(
                child: Container(
                  width: 40,
                  height: 4,
                  decoration: BoxDecoration(
                    color: Colors.grey.shade300,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: 16),
              Text(
                'Kalender Tanam Kec. $districtName',
                style: const TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 12),
              if (calendar != null)
                PlantingCalendarCard(calendar: calendar)
              else
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: Colors.grey.shade100,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Row(
                    children: [
                      Icon(Icons.info_outline, color: Colors.grey.shade600),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          'Belum ada data kalender tanam aktif untuk Kecamatan $districtName.',
                          style: TextStyle(color: Colors.grey.shade700),
                        ),
                      ),
                    ],
                  ),
                ),
              const SizedBox(height: 16),
            ],
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final resolvedUserLocation = _resolvedUserLocation;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Peta & Kalender Tanam'),
        backgroundColor: const Color(0xFF16A34A),
        foregroundColor: Colors.white,
        elevation: 0,
      ),
      body: SizedBox.expand(
        child: Stack(
          children: [
            Positioned.fill(
              child: FlutterMap(
                mapController: _mapController,
                options: MapOptions(
                  initialCenter: _currentCenter,
                  initialZoom: 11.0,
                  onTap: (tapPosition, point) async {
                    // Auto-resolve clicked point
                    final regionApi = ref.read(regionApiServiceProvider);
                    final resolved = await regionApi.resolveCoordinates(
                      point.latitude,
                      point.longitude,
                    );
                    if (resolved != null && resolved.district != null) {
                      _showCalendarBottomSheet(
                        resolved.district!.id,
                        resolved.district!.name,
                      );
                    }
                  },
                ),
                children: [
                  TileLayer(
                    urlTemplate:
                        'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                    userAgentPackageName: 'com.padi.app',
                  ),
                  if (_polygonList.isNotEmpty)
                    PolygonLayer(polygons: _polygonList),
                  if (_userLocation != null)
                    MarkerLayer(
                      markers: [
                        Marker(
                          point: _userLocation!,
                          width: 40,
                          height: 40,
                          child: Container(
                            decoration: BoxDecoration(
                              color: Colors.blue.shade600,
                              shape: BoxShape.circle,
                              border: Border.all(color: Colors.white, width: 3),
                              boxShadow: const [
                                BoxShadow(color: Colors.black26, blurRadius: 6),
                              ],
                            ),
                            child: const Icon(
                              Icons.person_pin_circle,
                              color: Colors.white,
                              size: 24,
                            ),
                          ),
                        ),
                      ],
                    ),
                ],
              ),
            ),

            // Top Region Filter Card
            Positioned(
              top: 16,
              left: 16,
              right: 16,
              child: Card(
                elevation: 4,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Padding(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 12,
                    vertical: 8,
                  ),
                  child: Row(
                    children: [
                      const Icon(Icons.location_on, color: Color(0xFF16A34A)),
                      const SizedBox(width: 8),
                      Expanded(
                        child: DropdownButtonHideUnderline(
                          child: DropdownButton<RegencyModel>(
                            isExpanded: true,
                            value: _selectedRegency,
                            hint: const Text('Pilih Kabupaten/Kota'),
                            items: _regencies.map((reg) {
                              return DropdownMenuItem<RegencyModel>(
                                value: reg,
                                child: Text(
                                  reg.name,
                                  style: const TextStyle(
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                              );
                            }).toList(),
                            onChanged: (val) {
                              if (val != null) {
                                setState(() {
                                  _selectedRegency = val;
                                  if (val.latitude != null &&
                                      val.longitude != null) {
                                    _mapController.move(
                                      LatLng(val.latitude!, val.longitude!),
                                      11.0,
                                    );
                                  }
                                });
                                _loadDistrictsAndBoundaries(val.id);
                              }
                            },
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),

            // GPS Button
            Positioned(
              bottom: 24,
              right: 16,
              child: FloatingActionButton(
                heroTag: 'gps_button',
                backgroundColor: const Color(0xFF16A34A),
                foregroundColor: Colors.white,
                onPressed: _detectUserGps,
                child: const Icon(Icons.my_location),
              ),
            ),

            // Resolved address badge
            if (resolvedUserLocation != null)
              Positioned(
                bottom: 24,
                left: 16,
                right: 84,
                child: Card(
                  elevation: 4,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12),
                  ),
                  color: const Color(0xFF0F172A),
                  child: Padding(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 12,
                      vertical: 10,
                    ),
                    child: Row(
                      children: [
                        const Icon(
                          Icons.navigation,
                          color: Color(0xFF4ADE80),
                          size: 18,
                        ),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Text(
                            resolvedUserLocation.formattedAddress,
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 12,
                            ),
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ),

            if (_isLoadingMap)
              const Positioned(
                top: 80,
                left: 0,
                right: 0,
                child: Center(
                  child: Card(
                    child: Padding(
                      padding: EdgeInsets.all(8.0),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          SizedBox(
                            width: 16,
                            height: 16,
                            child: CircularProgressIndicator(strokeWidth: 2),
                          ),
                          SizedBox(width: 8),
                          Text(
                            'Memuat batas wilayah...',
                            style: TextStyle(fontSize: 12),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }
}
