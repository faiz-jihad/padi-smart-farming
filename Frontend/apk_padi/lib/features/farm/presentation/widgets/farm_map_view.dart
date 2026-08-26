import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:latlong2/latlong.dart' as latlng;
import 'package:padi/features/farm/data/models/farm_model.dart';
import 'package:padi/features/home/presentation/tokens/home_tokens.dart';

class FarmMapView extends StatefulWidget {
  const FarmMapView({
    super.key,
    required this.farms,
    required this.onTapCalendar,
    required this.onTapFertilizer,
    required this.onTapAddActivity,
    required this.onCloseMap,
    this.initialSelectedFarm,
  });

  final List<FarmModel> farms;
  final ValueChanged<FarmModel> onTapCalendar;
  final ValueChanged<FarmModel> onTapFertilizer;
  final ValueChanged<FarmModel> onTapAddActivity;
  final VoidCallback onCloseMap;
  final FarmModel? initialSelectedFarm;

  @override
  State<FarmMapView> createState() => _FarmMapViewState();
}

class _FarmMapViewState extends State<FarmMapView> {
  late final MapController _mapController;
  FarmModel? _selectedFarm;

  @override
  void initState() {
    super.initState();
    _mapController = MapController();
    _selectedFarm =
        widget.initialSelectedFarm ??
        (widget.farms.isNotEmpty ? widget.farms.first : null);
  }

  latlng.LatLng _calculateCenter(List<FarmModel> farms) {
    final selectedFarm = _selectedFarm;

    if (selectedFarm != null) {
      if (selectedFarm.latitude != 0 || selectedFarm.longitude != 0) {
        return latlng.LatLng(selectedFarm.latitude, selectedFarm.longitude);
      }
      if (selectedFarm.boundaryCoordinates.isNotEmpty) {
        return _calculateBoundaryCenter(selectedFarm);
      }
    }

    if (farms.isEmpty) {
      return const latlng.LatLng(-6.3265, 108.3242);
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

  List<Polygon> _buildPolygons() {
    return widget.farms
        .where((farm) => farm.boundaryCoordinates.length >= 3)
        .map((farm) {
          final isSelected = _selectedFarm?.id == farm.id;
          final points = farm.boundaryCoordinates
              .map((p) => latlng.LatLng(p.lat, p.lng))
              .toList(growable: false);

          return Polygon(
            points: points,
            color: isSelected
                ? HomeColors.primaryGreen.withOpacity(0.35)
                : HomeColors.harvestGold.withOpacity(0.25),
            borderColor: isSelected
                ? HomeColors.primaryGreen
                : HomeColors.harvestGold,
            borderStrokeWidth: isSelected ? 3 : 2,
          );
        })
        .toList();
  }

  List<Marker> _buildMarkers() {
    return widget.farms.map((farm) {
      final point = farm.latitude != 0 || farm.longitude != 0
          ? latlng.LatLng(farm.latitude, farm.longitude)
          : _calculateBoundaryCenter(farm);
      final isSelected = _selectedFarm?.id == farm.id;

      return Marker(
        point: point,
        width: 140,
        height: 60,
        child: GestureDetector(
          onTap: () {
            setState(() => _selectedFarm = farm);
            _mapController.move(point, 16);
          },
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: isSelected ? HomeColors.primaryGreen : Colors.white,
                  borderRadius: BorderRadius.circular(HomeRadius.pill),
                  boxShadow: const [
                    BoxShadow(
                      color: Colors.black26,
                      blurRadius: 6,
                      offset: Offset(0, 2),
                    ),
                  ],
                ),
                child: Text(
                  farm.name,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    color: isSelected ? Colors.white : HomeColors.textPrimary,
                    fontSize: 10,
                    fontWeight: FontWeight.w800,
                  ),
                ),
              ),
              const SizedBox(height: 2),
              Container(
                width: 32,
                height: 32,
                decoration: BoxDecoration(
                  color: isSelected ? HomeColors.primaryGreen : Colors.white,
                  shape: BoxShape.circle,
                  border: Border.all(
                    color: isSelected ? Colors.white : HomeColors.primaryGreen,
                    width: 2,
                  ),
                  boxShadow: const [
                    BoxShadow(
                      color: Colors.black26,
                      blurRadius: 6,
                      offset: Offset(0, 2),
                    ),
                  ],
                ),
                child: Center(
                  child: Icon(
                    Icons.grass_rounded,
                    color: isSelected ? Colors.white : HomeColors.primaryGreen,
                    size: 16,
                  ),
                ),
              ),
            ],
          ),
        ),
      );
    }).toList();
  }

  @override
  Widget build(BuildContext context) {
    final initialCenter = _calculateCenter(widget.farms);
    final selectedFarm = _selectedFarm;

    return SizedBox.expand(
      child: Stack(
        children: [
          // 1. Flutter Map
          Positioned.fill(
            child: FlutterMap(
              mapController: _mapController,
              options: MapOptions(
                initialCenter: initialCenter,
                initialZoom: 15.5,
                minZoom: 5,
                maxZoom: 19,
              ),
              children: [
                TileLayer(
                  urlTemplate: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
                  userAgentPackageName: 'com.padi.app',
                ),
                PolygonLayer(polygons: _buildPolygons()),
                MarkerLayer(markers: _buildMarkers()),
              ],
            ),
          ),

          // 2. Top Bar Action Controls
          Positioned(
            top: 14,
            left: 14,
            right: 14,
            child: Row(
              children: [
                _buildRoundButton(
                  icon: Icons.list_alt_rounded,
                  tooltip: 'Tutup Peta / Kembali ke Daftar',
                  onTap: widget.onCloseMap,
                ),
                const SizedBox(width: 8),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 12,
                    vertical: 8,
                  ),
                  decoration: BoxDecoration(
                    color: HomeColors.surface,
                    borderRadius: BorderRadius.circular(HomeRadius.pill),
                    boxShadow: HomeShadows.subtle,
                    border: Border.all(color: HomeColors.border),
                  ),
                  child: Text(
                    '${widget.farms.length} Lahan di Peta GIS',
                    style: const TextStyle(
                      color: HomeColors.textPrimary,
                      fontSize: 12,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                ),
                const Spacer(),
                _buildRoundButton(
                  icon: Icons.my_location_rounded,
                  tooltip: 'Pusatkan Peta',
                  onTap: () {
                    _mapController.move(initialCenter, 15.5);
                  },
                ),
              ],
            ),
          ),

          // 3. Bottom Card for Selected Farm
          if (selectedFarm != null)
            Positioned(
              left: 14,
              right: 14,
              bottom: 20,
              child: Container(
                padding: const EdgeInsets.all(HomeSpacing.cardPadding),
                decoration: BoxDecoration(
                  color: HomeColors.surface,
                  borderRadius: BorderRadius.circular(HomeRadius.xl),
                  boxShadow: const [
                    BoxShadow(
                      color: Colors.black26,
                      blurRadius: 18,
                      offset: Offset(0, 6),
                    ),
                  ],
                  border: Border.all(color: HomeColors.border),
                ),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Expanded(
                          child: Text(
                            selectedFarm.name,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: const TextStyle(
                              color: HomeColors.textPrimary,
                              fontSize: 16,
                              fontWeight: FontWeight.w900,
                            ),
                          ),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 8,
                            vertical: 2,
                          ),
                          decoration: BoxDecoration(
                            color: HomeColors.lightGreen,
                            borderRadius: BorderRadius.circular(
                              HomeRadius.pill,
                            ),
                          ),
                          child: Text(
                            '${selectedFarm.areaHa} Ha',
                            style: const TextStyle(
                              color: HomeColors.primaryGreen,
                              fontSize: 11,
                              fontWeight: FontWeight.w800,
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 3),
                    Text(
                      selectedFarm.locationDescription,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: HomeTypography.supporting,
                    ),
                    const SizedBox(height: 10),
                    Row(
                      children: [
                        Expanded(
                          child: FilledButton.icon(
                            onPressed: () => widget.onTapCalendar(selectedFarm),
                            icon: const Icon(
                              Icons.calendar_month_rounded,
                              size: 15,
                            ),
                            label: const Text(
                              'Kalender Tanam',
                              style: TextStyle(
                                fontSize: 11.5,
                                fontWeight: FontWeight.w800,
                              ),
                            ),
                            style: FilledButton.styleFrom(
                              backgroundColor: HomeColors.primaryGreen,
                              foregroundColor: Colors.white,
                              padding: const EdgeInsets.symmetric(vertical: 8),
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(
                                  HomeRadius.sm,
                                ),
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(width: 8),
                        OutlinedButton.icon(
                          onPressed: () => widget.onTapFertilizer(selectedFarm),
                          icon: const Icon(Icons.science_outlined, size: 15),
                          label: const Text(
                            'Pupuk',
                            style: TextStyle(
                              fontSize: 11.5,
                              fontWeight: FontWeight.w800,
                            ),
                          ),
                          style: OutlinedButton.styleFrom(
                            foregroundColor: HomeColors.primaryGreen,
                            side: const BorderSide(color: HomeColors.border),
                            padding: const EdgeInsets.symmetric(
                              horizontal: 12,
                              vertical: 8,
                            ),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(
                                HomeRadius.sm,
                              ),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildRoundButton({
    required IconData icon,
    required String tooltip,
    required VoidCallback onTap,
  }) {
    return Container(
      width: 42,
      height: 42,
      decoration: BoxDecoration(
        color: HomeColors.surface,
        shape: BoxShape.circle,
        boxShadow: HomeShadows.subtle,
        border: Border.all(color: HomeColors.border),
      ),
      child: IconButton(
        tooltip: tooltip,
        onPressed: onTap,
        icon: Icon(icon, color: HomeColors.textPrimary, size: 20),
        padding: EdgeInsets.zero,
      ),
    );
  }
}
