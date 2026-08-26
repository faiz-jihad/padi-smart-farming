import 'package:flutter/material.dart';
import 'package:padi/features/farm/data/models/farm_model.dart';
import 'package:padi/features/home/presentation/tokens/home_tokens.dart';

class FarmCard extends StatefulWidget {
  const FarmCard({
    super.key,
    required this.farm,
    required this.onTapCalendar,
    required this.onTapFertilizer,
    required this.onTapAddActivity,
    required this.onTapTimeline,
    required this.onTapFocusMap,
  });

  final FarmModel farm;
  final VoidCallback onTapCalendar;
  final VoidCallback onTapFertilizer;
  final VoidCallback onTapAddActivity;
  final VoidCallback onTapTimeline;
  final VoidCallback onTapFocusMap;

  @override
  State<FarmCard> createState() => _FarmCardState();
}

class _FarmCardState extends State<FarmCard> {
  bool _isPressed = false;

  String _irrigationLabel(String value) {
    return switch (value.toLowerCase()) {
      'irrigated' => 'Irigasi Teknis',
      'semi_irrigated' => 'Setengah Teknis',
      'rainfed' => 'Tadah Hujan',
      'tidal' => 'Pasang Surut',
      _ => value,
    };
  }

  String _statusLabel(String value) {
    return switch (value.toLowerCase()) {
      'active' => 'Aktif',
      'inactive' => 'Nonaktif',
      'fallow' => 'Bera',
      _ => value,
    };
  }

  @override
  Widget build(BuildContext context) {
    final farm = widget.farm;
    final areaM2 = (farm.areaHa * 10000).toInt();
    final isActive = farm.status.toLowerCase() == 'active';

    return GestureDetector(
      onTapDown: (_) => setState(() => _isPressed = true),
      onTapUp: (_) => setState(() => _isPressed = false),
      onTapCancel: () => setState(() => _isPressed = false),
      child: AnimatedScale(
        scale: _isPressed ? 0.98 : 1.0,
        duration: const Duration(milliseconds: 120),
        child: Container(
          width: double.infinity,
          decoration: BoxDecoration(
            color: HomeColors.surface,
            borderRadius: BorderRadius.circular(HomeRadius.xl),
            border: Border.all(color: HomeColors.border),
            boxShadow: HomeShadows.subtle,
          ),
          child: Padding(
            padding: const EdgeInsets.all(HomeSpacing.cardPadding),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Top Row: Thumbnail + Info + Status
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Polygon Thumbnail
                    _buildThumbnail(farm),
                    const SizedBox(width: HomeSpacing.sm),

                    // Farm Name & Location
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Expanded(
                                child: Text(
                                  farm.name,
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                  style: const TextStyle(
                                    color: HomeColors.textPrimary,
                                    fontSize: 16,
                                    fontWeight: FontWeight.w800,
                                    letterSpacing: -0.3,
                                  ),
                                ),
                              ),
                              const SizedBox(width: 6),
                              _buildStatusBadge(isActive, farm.status),
                            ],
                          ),
                          const SizedBox(height: 3),

                          // Area in Ha & m2
                          Row(
                            children: [
                              Text(
                                '${farm.areaHa.toStringAsFixed(farm.areaHa == farm.areaHa.roundToDouble() ? 0 : 1)} Ha',
                                style: const TextStyle(
                                  color: HomeColors.primaryGreen,
                                  fontSize: 13.5,
                                  fontWeight: FontWeight.w900,
                                ),
                              ),
                              const SizedBox(width: 4),
                              Text(
                                '(≈ $areaM2 m²)',
                                style: const TextStyle(
                                  color: HomeColors.textSecondary,
                                  fontSize: 11,
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 3),

                          // Location String
                          Row(
                            children: [
                              const Icon(
                                Icons.location_on_outlined,
                                color: HomeColors.textSecondary,
                                size: 13,
                              ),
                              const SizedBox(width: 3),
                              Expanded(
                                child: Text(
                                  farm.locationDescription,
                                  maxLines: 1,
                                  overflow: TextOverflow.ellipsis,
                                  style: HomeTypography.supporting,
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                  ],
                ),

                const SizedBox(height: HomeSpacing.sm),

                // Tags / Chips Row
                Wrap(
                  spacing: 6,
                  runSpacing: 6,
                  children: [
                    _buildChip(
                      icon: Icons.water_drop_outlined,
                      label: _irrigationLabel(farm.irrigationType),
                    ),
                    if (farm.soilType != null && farm.soilType!.isNotEmpty)
                      _buildChip(
                        icon: Icons.landscape_outlined,
                        label: 'Tanah: ${farm.soilType}',
                      ),
                    if (farm.boundaryCoordinates.length >= 3)
                      _buildChip(
                        icon: Icons.polyline_rounded,
                        label: '${farm.boundaryCoordinates.length} Titik Poligon',
                        highlight: true,
                      ),
                  ],
                ),

                const SizedBox(height: HomeSpacing.sm),
                const Divider(height: 1, color: HomeColors.borderSubtle),
                const SizedBox(height: HomeSpacing.sm),

                // Action Strip Buttons
                SingleChildScrollView(
                  scrollDirection: Axis.horizontal,
                  physics: const BouncingScrollPhysics(),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      // 1. Kalender Tanam
                      _buildActionButton(
                        icon: Icons.calendar_month_rounded,
                        label: 'Kalender',
                        onTap: widget.onTapCalendar,
                        isPrimary: true,
                      ),
                      const SizedBox(width: 6),

                      // 2. Kalkulator Pupuk
                      _buildActionButton(
                        icon: Icons.science_outlined,
                        label: 'Pupuk',
                        onTap: widget.onTapFertilizer,
                      ),
                      const SizedBox(width: 6),

                      // 3. Catat Aktivitas
                      _buildActionButton(
                        icon: Icons.edit_note_rounded,
                        label: 'Aktivitas',
                        onTap: widget.onTapAddActivity,
                      ),
                      const SizedBox(width: 6),

                      // 4. Timeline
                      _buildActionButton(
                        icon: Icons.timeline_rounded,
                        label: 'Timeline',
                        onTap: widget.onTapTimeline,
                      ),
                      const SizedBox(width: 6),

                      // 5. Fokus Peta
                      _buildActionButton(
                        icon: Icons.pin_drop_outlined,
                        label: 'Peta',
                        onTap: widget.onTapFocusMap,
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildThumbnail(FarmModel farm) {
    return Container(
      width: 68,
      height: 68,
      decoration: BoxDecoration(
        color: HomeColors.lightGreen,
        borderRadius: BorderRadius.circular(HomeRadius.md),
        border: Border.all(color: HomeColors.borderSubtle),
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(HomeRadius.md),
        child: Stack(
          fit: StackFit.expand,
          children: [
            Container(color: const Color(0xFFE2EFE7)),
            if (farm.boundaryCoordinates.length >= 3)
              CustomPaint(
                painter: _FarmPolygonThumbnailPainter(farm.boundaryCoordinates),
              )
            else
              const Center(
                child: Icon(
                  Icons.grass_rounded,
                  color: HomeColors.primaryGreen,
                  size: 32,
                ),
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildStatusBadge(bool isActive, String rawStatus) {
    final label = _statusLabel(rawStatus);
    final color = isActive ? HomeColors.primaryGreen : HomeColors.harvestGold;
    final bg = isActive ? HomeColors.lightGreen : HomeColors.harvestGoldBg;

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2.5),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(HomeRadius.pill),
      ),
      child: Text(
        label,
        style: TextStyle(
          color: color,
          fontSize: 10,
          fontWeight: FontWeight.w800,
        ),
      ),
    );
  }

  Widget _buildChip({
    required IconData icon,
    required String label,
    bool highlight = false,
  }) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: highlight ? HomeColors.lightGreen : HomeColors.surfaceMuted,
        borderRadius: BorderRadius.circular(HomeRadius.sm),
        border: Border.all(
          color: highlight ? const Color(0xFFBBF7D0) : HomeColors.borderSubtle,
        ),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(
            icon,
            size: 13,
            color: highlight ? HomeColors.primaryGreen : HomeColors.textSecondary,
          ),
          const SizedBox(width: 4),
          Text(
            label,
            style: TextStyle(
              color: highlight ? HomeColors.primaryGreen : HomeColors.textPrimary,
              fontSize: 11,
              fontWeight: FontWeight.w600,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildActionButton({
    required IconData icon,
    required String label,
    required VoidCallback onTap,
    bool isPrimary = false,
  }) {
    return Material(
      color: isPrimary ? HomeColors.primaryGreen : HomeColors.surfaceMuted,
      borderRadius: BorderRadius.circular(HomeRadius.sm),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(HomeRadius.sm),
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(
                icon,
                size: 14,
                color: isPrimary ? Colors.white : HomeColors.textPrimary,
              ),
              const SizedBox(width: 4),
              Text(
                label,
                style: TextStyle(
                  color: isPrimary ? Colors.white : HomeColors.textPrimary,
                  fontSize: 11.5,
                  fontWeight: FontWeight.w700,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _FarmPolygonThumbnailPainter extends CustomPainter {
  const _FarmPolygonThumbnailPainter(this.points);

  final List<FarmBoundaryPoint> points;

  @override
  void paint(Canvas canvas, Size size) {
    if (points.length < 3) return;

    final minLat = points.map((p) => p.lat).reduce((a, b) => a < b ? a : b);
    final maxLat = points.map((p) => p.lat).reduce((a, b) => a > b ? a : b);
    final minLng = points.map((p) => p.lng).reduce((a, b) => a < b ? a : b);
    final maxLng = points.map((p) => p.lng).reduce((a, b) => a > b ? a : b);
    final latRange = (maxLat - minLat).abs() < 0.000001 ? 1.0 : maxLat - minLat;
    final lngRange = (maxLng - minLng).abs() < 0.000001 ? 1.0 : maxLng - minLng;
    final padding = size.width * 0.20;
    final path = Path();

    for (var i = 0; i < points.length; i++) {
      final point = points[i];
      final x = padding + ((point.lng - minLng) / lngRange) * (size.width - padding * 2);
      final y = padding + ((maxLat - point.lat) / latRange) * (size.height - padding * 2);
      if (i == 0) {
        path.moveTo(x, y);
      } else {
        path.lineTo(x, y);
      }
    }
    path.close();

    canvas.drawPath(
      path,
      Paint()
        ..color = HomeColors.primaryGreen.withOpacity(0.25)
        ..style = PaintingStyle.fill,
    );
    canvas.drawPath(
      path,
      Paint()
        ..color = HomeColors.primaryGreen
        ..style = PaintingStyle.stroke
        ..strokeWidth = 2,
    );
  }

  @override
  bool shouldRepaint(covariant _FarmPolygonThumbnailPainter oldDelegate) {
    return oldDelegate.points != points;
  }
}
