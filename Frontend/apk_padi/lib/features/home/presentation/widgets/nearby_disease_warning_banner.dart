import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

enum NearbyDiseaseRiskLevel { pantau, waspada, siaga }

class NearbyDiseaseWarningBanner extends StatefulWidget {
  const NearbyDiseaseWarningBanner({
    super.key,
    required this.diseaseName,
    this.distanceKm,
    this.locationName,
    this.farmerAdvice,
    this.reportCount = 1,
    this.riskLevel,
    this.onDismiss,
  });

  final String diseaseName;
  final double? distanceKm;
  final String? locationName;
  final String? farmerAdvice;
  final int reportCount;
  final NearbyDiseaseRiskLevel? riskLevel;
  final VoidCallback? onDismiss;

  @override
  State<NearbyDiseaseWarningBanner> createState() =>
      _NearbyDiseaseWarningBannerState();
}

class _NearbyDiseaseWarningBannerState extends State<NearbyDiseaseWarningBanner>
    with SingleTickerProviderStateMixin {
  late final AnimationController _pulseController;
  late final Animation<double> _pulseAnimation;
  bool _isCollapsed = false;

  @override
  void initState() {
    super.initState();
    _pulseController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1600),
    )..repeat(reverse: true);

    _pulseAnimation = Tween<double>(begin: 0.95, end: 1.08).animate(
      CurvedAnimation(parent: _pulseController, curve: Curves.easeInOut),
    );
  }

  @override
  void dispose() {
    _pulseController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final distanceText = widget.distanceKm == null
        ? null
        : widget.distanceKm! < 1.0
        ? '${(widget.distanceKm! * 1000).round()} meter'
        : '${widget.distanceKm!.toStringAsFixed(1)} km';

    final adviceText =
        widget.farmerAdvice ?? _getDefaultAdvice(widget.diseaseName);
    final riskLevel = widget.riskLevel ?? _inferRiskLevel();
    final riskUi = _riskUi(riskLevel);

    return Container(
      margin: const EdgeInsets.only(bottom: 16),
      decoration: BoxDecoration(
        color: riskUi.background,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: riskUi.border, width: 1.5),
        boxShadow: [
          BoxShadow(
            color: riskUi.primary.withValues(alpha: 0.08),
            blurRadius: 16,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Header: Pulsing Badge & Dismiss/Collapse
              Row(
                crossAxisAlignment: CrossAxisAlignment.center,
                children: [
                  // Animated Radar Pulse Icon
                  AnimatedBuilder(
                    animation: _pulseAnimation,
                    builder: (context, child) {
                      return Transform.scale(
                        scale: _pulseAnimation.value,
                        child: child,
                      );
                    },
                    child: Container(
                      padding: const EdgeInsets.all(8),
                      decoration: BoxDecoration(
                        color: riskUi.primary,
                        shape: BoxShape.circle,
                        boxShadow: [
                          BoxShadow(
                            color: riskUi.primary.withValues(alpha: 0.28),
                            blurRadius: 8,
                            spreadRadius: 1,
                          ),
                        ],
                      ),
                      child: const Icon(
                        Icons.crisis_alert_rounded,
                        color: Colors.white,
                        size: 20,
                      ),
                    ),
                  ),
                  const SizedBox(width: 10),

                  // Pill Badge
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 8,
                                vertical: 3,
                              ),
                              decoration: BoxDecoration(
                                color: riskUi.primary,
                                borderRadius: BorderRadius.circular(6),
                              ),
                              child: Text(
                                riskUi.label,
                                style: TextStyle(
                                  color: Colors.white,
                                  fontSize: 10,
                                  fontWeight: FontWeight.w900,
                                  letterSpacing: 0.5,
                                ),
                              ),
                            ),
                            const SizedBox(width: 6),
                            Flexible(
                              child: Text(
                                distanceText != null
                                    ? 'Radius $distanceText'
                                    : 'Area sekitar lahan',
                                style: TextStyle(
                                  color: riskUi.primary,
                                  fontSize: 11,
                                  fontWeight: FontWeight.w800,
                                ),
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 2),
                        Text(
                          widget.locationName != null &&
                                  widget.locationName!.isNotEmpty
                              ? 'Terdeteksi di sekitar ${widget.locationName}'
                              : 'Terdeteksi di sekitar hamparan lahan Anda',
                          style: TextStyle(
                            color: riskUi.text,
                            fontSize: 11,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ],
                    ),
                  ),

                  // Minimize / Close Toggle
                  IconButton(
                    icon: Icon(
                      _isCollapsed
                          ? Icons.keyboard_arrow_down_rounded
                          : Icons.keyboard_arrow_up_rounded,
                      color: riskUi.primary,
                      size: 22,
                    ),
                    tooltip: _isCollapsed ? 'Perluas Peringatan' : 'Ciutkan',
                    onPressed: () {
                      setState(() => _isCollapsed = !_isCollapsed);
                    },
                  ),
                ],
              ),

              if (!_isCollapsed) ...[
                const SizedBox(height: 12),
                Divider(height: 1, color: riskUi.border),
                const SizedBox(height: 12),

                // Main Disease Alert Title
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            '${riskUi.shortLabel} ${widget.diseaseName}',
                            style: TextStyle(
                              fontSize: 15.5,
                              fontWeight: FontWeight.w900,
                              color: riskUi.text,
                              height: 1.25,
                            ),
                          ),
                          const SizedBox(height: 5),
                          Text(
                            adviceText,
                            style: TextStyle(
                              fontSize: 12.5,
                              color: riskUi.text,
                              height: 1.45,
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),

                const SizedBox(height: 14),

                // Action Buttons
                Row(
                  children: [
                    // Primary Action: Check Leaf Now
                    Expanded(
                      flex: 6,
                      child: FilledButton.icon(
                        onPressed: () => context.push('/plant-check'),
                        icon: const Icon(Icons.camera_alt_rounded, size: 16),
                        label: const Text(
                          'Pindai Daun Padi',
                          style: TextStyle(
                            fontSize: 12.5,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                        style: FilledButton.styleFrom(
                          backgroundColor: riskUi.primary,
                          foregroundColor: Colors.white,
                          elevation: 0,
                          padding: const EdgeInsets.symmetric(vertical: 11),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(width: 8),

                    // Secondary Action: View Radar Map
                    Expanded(
                      flex: 5,
                      child: OutlinedButton.icon(
                        onPressed: () => context.push('/community-alert'),
                        icon: const Icon(Icons.radar_rounded, size: 16),
                        label: const Text(
                          'Peta Radar',
                          style: TextStyle(
                            fontSize: 12,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                        style: OutlinedButton.styleFrom(
                          foregroundColor: riskUi.primary,
                          side: BorderSide(color: riskUi.border, width: 1.2),
                          backgroundColor: Colors.white.withValues(alpha: 0.68),
                          padding: const EdgeInsets.symmetric(vertical: 11),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12),
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
    );
  }

  String _getDefaultAdvice(String disease) {
    final lower = disease.toLowerCase();
    if (lower.contains('streak') || lower.contains('hawar')) {
      return 'Periksa ada tidaknya garis basah memanjang atau daun menguning cepat. Kurangi pupuk nitrogen berlebih dan pantau drainase.';
    } else if (lower.contains('blas')) {
      return 'Waspadai bercak berbentuk belah ketupat keabu-abuan. Semprot fungisida nabati atau protektan saat kelembapan tinggi.';
    } else if (lower.contains('bercak cokelat')) {
      return 'Periksa bintik cokelat oval pada helai daun. Tambahkan kalium (KCl) untuk meningkatkan kekebalan dinding sel tanaman.';
    } else if (lower.contains('tungro')) {
      return 'Waspadai daun menguning oranye dan tanaman kerdil. Segera kendalikan populasi wereng hijau sebagai vektor penular.';
    }
    return 'Ada laporan penyakit dari area sekitar. Lakukan inspeksi visual saat patroli lahan dan bandingkan gejala pada daun.';
  }

  NearbyDiseaseRiskLevel _inferRiskLevel() {
    final distanceKm = widget.distanceKm;
    if ((distanceKm != null && distanceKm <= 1.0) || widget.reportCount >= 3) {
      return NearbyDiseaseRiskLevel.siaga;
    }
    if ((distanceKm != null && distanceKm <= 5.0) || widget.reportCount >= 2) {
      return NearbyDiseaseRiskLevel.waspada;
    }
    return NearbyDiseaseRiskLevel.pantau;
  }

  ({
    Color background,
    Color border,
    Color primary,
    Color text,
    String label,
    String shortLabel,
  })
  _riskUi(NearbyDiseaseRiskLevel level) {
    return switch (level) {
      NearbyDiseaseRiskLevel.siaga => (
        background: const Color(0xFFEAF2EC),
        border: const Color(0xFF9BC9AD),
        primary: const Color(0xFF075C3D),
        text: const Color(0xFF1A2F25),
        label: 'SIAGA PENYAKIT',
        shortLabel: 'Siaga',
      ),
      NearbyDiseaseRiskLevel.waspada => (
        background: const Color(0xFFF3FAF5),
        border: const Color(0xFFB8DCC5),
        primary: const Color(0xFF2F8C62),
        text: const Color(0xFF1A2F25),
        label: 'WASPADA DINI',
        shortLabel: 'Waspada',
      ),
      NearbyDiseaseRiskLevel.pantau => (
        background: Colors.white,
        border: const Color(0xFFE1E8DF),
        primary: const Color(0xFF075C3D),
        text: const Color(0xFF1A2F25),
        label: 'PANTAU AREA',
        shortLabel: 'Pantau',
      ),
    };
  }
}
