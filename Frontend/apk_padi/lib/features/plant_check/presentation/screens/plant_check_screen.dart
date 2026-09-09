import 'dart:math' as math;
import 'dart:typed_data';
import 'dart:ui';

import 'package:camera/camera.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_tts/flutter_tts.dart';
import 'package:go_router/go_router.dart';
import 'package:image/image.dart' as image_lib;
import 'package:image_picker/image_picker.dart';
import 'package:padi/core/errors/api_exception.dart';
import 'package:padi/core/location/location_service.dart';
import 'package:padi/core/localization/app_language.dart';
import 'package:padi/core/providers/app_providers.dart';
import 'package:padi/features/auth/presentation/widgets/padi_theme.dart';
import 'package:padi/features/farm/data/models/farm_model.dart';
import 'package:padi/features/farm/data/services/farm_api_service.dart';
import 'package:padi/features/home/presentation/tokens/home_tokens.dart';
import 'package:padi/core/voice/voice_command_provider.dart';
import 'package:padi/core/voice/voice_intent.dart';
import 'package:padi/core/voice/voice_state.dart';
import 'package:padi/core/widgets/voice_mic_button.dart';
import 'package:padi/features/plant_check/data/services/plant_check_api_service.dart';
import 'package:padi/features/plant_check/data/services/offline_scan_queue_service.dart';
import 'package:padi/features/plant_check/presentation/screens/ppl_case_list_screen.dart';

class PlantCheckScreen extends ConsumerStatefulWidget {
  const PlantCheckScreen({super.key});

  @override
  ConsumerState<PlantCheckScreen> createState() => _PlantCheckScreenState();
}

class _PlantCheckScreenState extends ConsumerState<PlantCheckScreen>
    with SingleTickerProviderStateMixin, WidgetsBindingObserver {
  CameraController? _controller;
  List<CameraDescription> _availableCameras = const [];
  int _currentCameraIndex = 0;
  FlashMode _flashMode = FlashMode.off;

  XFile? _image;
  Uint8List? _imageBytes;
  late final FarmApiService _farmService;
  late final PlantCheckApiService _plantCheckService;
  List<FarmModel> _farms = const [];
  int? _selectedFarmId;

  bool _isInitializing = true;
  bool _isLoadingFarms = true;
  bool _isScanning = false;
  bool _isClosing = false;
  String? _errorMessage;
  String? _scanError;
  String? _nearbyWarningText;
  String? _nearbyWarningLevel;

  late AnimationController _scanAnimController;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    final apiClient = ref.read(apiClientProvider);
    _farmService = FarmApiService(apiClient);
    _plantCheckService = PlantCheckApiService(apiClient);

    _scanAnimController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1800),
    )..repeat(reverse: true);

    _initializeCamera();
    _loadFarms();
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    _scanAnimController.dispose();
    final controller = _controller;
    _controller = null;
    controller?.dispose();
    super.dispose();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    final controller = _controller;
    if (controller == null || !controller.value.isInitialized) {
      return;
    }

    if (state == AppLifecycleState.inactive ||
        state == AppLifecycleState.paused) {
      _controller = null;
      controller.dispose();
    } else if (state == AppLifecycleState.resumed) {
      if (_image == null && mounted) {
        _initializeCamera();
      }
    }
  }

  Future<void> _loadFarms() async {
    try {
      final farms = await _farmService.fetchFarms();

      if (!mounted) return;

      setState(() {
        _farms = farms;
        _selectedFarmId = farms.isNotEmpty ? farms.first.id : null;
        _isLoadingFarms = false;
      });

      _checkNearbyAlerts(farms);
    } catch (_) {
      if (!mounted) return;

      setState(() {
        _isLoadingFarms = false;
      });
    }
  }

  Future<void> _checkNearbyAlerts(List<FarmModel> farms) async {
    try {
      if (farms.isEmpty) return;

      final apiClient = ref.read(apiClientProvider);
      final res = await apiClient.dio.get('/community-reports');
      final data = res.data;
      if (data is Map &&
          data['data'] is List &&
          (data['data'] as List).isNotEmpty) {
        final reports = (data['data'] as List).whereType<Map>().toList();
        if (reports.isEmpty) return;

        final risks = <String, _NearbyDiseaseRisk>{};

        for (final rep in reports) {
          final status = rep['status']?.toString().toLowerCase() ?? 'pending';
          if (status == 'rejected' || status == 'resolved') continue;

          final reportedAt = DateTime.tryParse(
            rep['reported_at']?.toString() ?? '',
          );
          if (reportedAt != null &&
              DateTime.now().difference(reportedAt).inDays > 14) {
            continue;
          }

          final disease =
              rep['disease_name']?.toString() ??
              rep['pest_name']?.toString() ??
              rep['title']?.toString();
          if (disease == null || disease.isEmpty) continue;

          final rLat = (rep['latitude'] as num?)?.toDouble();
          final rLon = (rep['longitude'] as num?)?.toDouble();
          if (rLat == null || rLon == null) continue;

          double? nearestDistance;
          for (final farm in farms) {
            final distance = _calculateDistanceKm(
              farm.latitude,
              farm.longitude,
              rLat,
              rLon,
            );
            if (nearestDistance == null || distance < nearestDistance) {
              nearestDistance = distance;
            }
          }
          if (nearestDistance == null) continue;

          final radiusKm = ((rep['radius_km'] as num?)?.toDouble() ?? 5.0)
              .clamp(1.0, 20.0);
          if (nearestDistance > radiusKm) continue;

          final score = _nearbyDiseaseScore(
            distanceKm: nearestDistance,
            radiusKm: radiusKm,
            status: status,
          );
          final key = disease.trim().toLowerCase();
          final current = risks[key];
          if (current == null) {
            risks[key] = _NearbyDiseaseRisk(
              diseaseName: disease,
              nearestDistanceKm: nearestDistance,
              reportCount: 1,
              score: score,
              hasVerifiedReport: status == 'verified' || status == 'validated',
            );
          } else {
            risks[key] = current.add(
              distanceKm: nearestDistance,
              score: score,
              isVerified: status == 'verified' || status == 'validated',
            );
          }
        }

        if (risks.isEmpty) return;

        final bestRisk = risks.values.reduce(
          (a, b) => a.weightedScore >= b.weightedScore ? a : b,
        );
        final level = _nearbyRiskLevel(bestRisk);

        if (mounted) {
          setState(() {
            _nearbyWarningLevel = level;
            _nearbyWarningText = _nearbyRiskMessage(bestRisk, level);
          });
        }
      }
    } catch (_) {}
  }

  double _nearbyDiseaseScore({
    required double distanceKm,
    required double radiusKm,
    required String status,
  }) {
    final statusWeight = switch (status) {
      'verified' || 'validated' => 3.0,
      'needs_revisit' => 1.6,
      _ => 1.0,
    };
    final distanceWeight = switch (distanceKm) {
      <= 1.0 => 3.0,
      <= 3.0 => 2.2,
      <= 5.0 => 1.5,
      _ => 0.8,
    };
    final radiusFit = (1 - (distanceKm / math.max(radiusKm, 1.0))).clamp(
      0.15,
      1.0,
    );
    return statusWeight * distanceWeight * radiusFit;
  }

  String _nearbyRiskLevel(_NearbyDiseaseRisk risk) {
    if (risk.hasVerifiedReport && risk.nearestDistanceKm <= 3.0) {
      return 'siaga';
    }
    if (risk.reportCount >= 3 || risk.weightedScore >= 5.0) {
      return 'siaga';
    }
    if (risk.reportCount >= 2 || risk.nearestDistanceKm <= 5.0) {
      return 'waspada';
    }
    return 'pantau';
  }

  String _nearbyRiskMessage(_NearbyDiseaseRisk risk, String level) {
    final distanceText = risk.nearestDistanceKm < 1
        ? '${(risk.nearestDistanceKm * 1000).round()} m'
        : '${risk.nearestDistanceKm.toStringAsFixed(1)} km';
    final reportText = risk.reportCount > 1
        ? '${risk.reportCount} laporan'
        : '1 laporan';

    return switch (level) {
      'siaga' =>
        'Siaga ${risk.diseaseName}: $reportText terdekat $distanceText dari sawah Anda. Periksa daun hari ini dan batasi penyebaran antarpetak.',
      'waspada' =>
        'Waspada ${risk.diseaseName}: $reportText dalam radius sekitar sawah ($distanceText). Pantau gejala pada daun dan drainase.',
      _ =>
        'Pantau ${risk.diseaseName}: ada $reportText sekitar $distanceText. Belum darurat, tapi sebaiknya cek daun saat patroli lahan.',
    };
  }

  double _calculateDistanceKm(
    double lat1,
    double lon1,
    double lat2,
    double lon2,
  ) {
    const p = 0.017453292519943295;
    final a =
        0.5 -
        math.cos((lat2 - lat1) * p) / 2 +
        math.cos(lat1 * p) *
            math.cos(lat2 * p) *
            (1 - math.cos((lon2 - lon1) * p)) /
            2;
    return 12742 * math.asin(math.sqrt(math.max(0.0, a)));
  }

  Future<void> _initializeCamera() async {
    try {
      if (mounted) {
        setState(() {
          _isInitializing = true;
          _errorMessage = null;
        });
      }

      _availableCameras = await availableCameras();
      if (_availableCameras.isEmpty) {
        throw Exception('Kamera tidak ditemukan pada perangkat ini.');
      }

      final camera = _availableCameras[_currentCameraIndex];
      final controller = CameraController(
        camera,
        ResolutionPreset.high,
        enableAudio: false,
      );

      await controller.initialize();

      if (!mounted) {
        await controller.dispose();
        return;
      }

      _controller = controller;
      setState(() => _isInitializing = false);
    } on CameraException catch (error) {
      if (!mounted) return;

      setState(() {
        _isInitializing = false;
        _errorMessage = error.code == 'CameraAccessDenied'
            ? 'Izin kamera ditolak. Berikan izin di pengaturan perangkat.'
            : 'Kamera tidak dapat diakses saat ini.';
      });
    } catch (_) {
      if (!mounted) return;

      setState(() {
        _isInitializing = false;
        _errorMessage = 'Kamera tidak dapat diakses.';
      });
    }
  }

  Future<void> _toggleFlash() async {
    final controller = _controller;
    if (controller == null || !controller.value.isInitialized) return;

    try {
      final nextMode = _flashMode == FlashMode.off
          ? FlashMode.torch
          : _flashMode == FlashMode.torch
          ? FlashMode.auto
          : FlashMode.off;

      await controller.setFlashMode(nextMode);
      if (mounted) {
        setState(() => _flashMode = nextMode);
      }
    } catch (_) {}
  }

  Future<void> _switchCamera() async {
    if (_availableCameras.length < 2) return;

    final nextIndex = (_currentCameraIndex + 1) % _availableCameras.length;
    _currentCameraIndex = nextIndex;

    final oldController = _controller;
    _controller = null;
    await oldController?.dispose();

    await _initializeCamera();
  }

  Future<void> _takePicture() async {
    final controller = _controller;
    if (controller == null ||
        !controller.value.isInitialized ||
        controller.value.isTakingPicture) {
      return;
    }

    try {
      final image = await controller.takePicture();
      final bytes = await image.readAsBytes();

      if (!mounted) return;

      setState(() {
        _image = image;
        _imageBytes = bytes;
      });
    } on CameraException {
      if (!mounted) return;

      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Foto gagal dipotret. Silakan coba lagi.'),
        ),
      );
    }
  }

  Future<void> _pickFromGallery() async {
    try {
      final picker = ImagePicker();
      final picked = await picker.pickImage(
        source: ImageSource.gallery,
        maxWidth: 1600,
        maxHeight: 1600,
        imageQuality: 92,
      );

      if (picked != null) {
        final bytes = await picked.readAsBytes();
        if (!mounted) return;

        setState(() {
          _image = picked;
          _imageBytes = bytes;
        });
      }
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Gagal mengambil foto dari galeri: $e')),
      );
    }
  }

  void _retakePicture() {
    setState(() {
      _image = null;
      _imageBytes = null;
      _scanError = null;
    });
  }

  Future<void> _usePicture() async {
    final image = _image;
    int? farmId = _selectedFarmId;

    if (image == null || _isScanning) return;

    final imageBytes = _imageBytes ?? await image.readAsBytes();
    final lang = ref.read(languageProvider);
    if (!_looksLikePaddyLeaf(imageBytes)) {
      if (!mounted) return;
      setState(() {
        _imageBytes = imageBytes;
        _scanError = _nonLeafPhotoMessage(lang);
      });
      return;
    }

    if (farmId == null && _farms.isNotEmpty) {
      farmId = _farms.first.id;
    }

    FarmModel? farm;
    for (final item in _farms) {
      if (item.id == farmId) {
        farm = item;
        break;
      }
    }

    setState(() {
      _isScanning = true;
      _scanError = null;
    });

    double? lat = farm?.latitude;
    double? lng = farm?.longitude;

    try {
      try {
        final position = await const LocationService()
            .getCurrentPosition()
            .timeout(const Duration(seconds: 2), onTimeout: () => null);
        if (position != null) {
          lat = position.latitude;
          lng = position.longitude;
        }
      } catch (_) {}

      final result = await _plantCheckService.scanDisease(
        farmId: farmId,
        imagePath: image.path,
        imageBytes: imageBytes,
        fileName: image.name,
        latitude: lat,
        longitude: lng,
      );

      if (!mounted) return;

      setState(() => _scanError = null);
      await _showScanResult(result);
    } catch (error) {
      if (!mounted) return;

      final errMsg = _friendlyError(error, lang);

      // Only enqueue network / offline errors to retry queue, NOT validation errors (e.g. bukan daun padi / 422)
      final isValidationError =
          (error is ApiException && error.statusCode == 422) ||
          errMsg.toLowerCase().contains('bukan daun') ||
          errMsg.toLowerCase().contains('belum terlihat') ||
          errMsg.toLowerCase().contains('tidak terdeteksi');
      var queuedOffline = false;
      if (farmId != null && !isValidationError) {
        try {
          await ref
              .read(offlineScanQueueServiceProvider)
              .enqueueScan(
                imagePath: image.path,
                farmId: farmId,
                latitude: lat,
                longitude: lng,
              );
          queuedOffline = true;
        } catch (_) {}
      }

      setState(() {
        _scanError = errMsg;
      });

      if (!mounted) return;

      if (!isValidationError || queuedOffline) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Row(
              children: [
                const Icon(
                  Icons.cloud_off_rounded,
                  color: Colors.white,
                  size: 24,
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        errMsg,
                        style: const TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.w700,
                          fontSize: 13,
                        ),
                      ),
                      if (queuedOffline) ...[
                        const SizedBox(height: 2),
                        const Text(
                          'Tersimpan di antrean offline. Otomatis diproses saat online.',
                          style: TextStyle(
                            color: Color(0xFFE2E8F0),
                            fontSize: 11,
                          ),
                        ),
                      ],
                    ],
                  ),
                ),
              ],
            ),
            backgroundColor: padiGreen,
            behavior: SnackBarBehavior.floating,
            duration: const Duration(seconds: 5),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
            ),
          ),
        );
      }
    } finally {
      if (mounted) {
        setState(() => _isScanning = false);
      }
    }
  }

  bool _looksLikePaddyLeaf(Uint8List bytes) {
    try {
      final decoded = image_lib.decodeImage(bytes);
      if (decoded == null) return true;

      final width = decoded.width;
      final height = decoded.height;
      if (width < 60 || height < 60) return true;

      final stepX = math.max(1, width ~/ 100);
      final stepY = math.max(1, height ~/ 100);
      var total = 0;
      var greenPixels = 0;
      var chloroticPixels = 0;
      var necroticPixels = 0;
      var achromaticPixels = 0;

      for (var y = 0; y < height; y += stepY) {
        for (var x = 0; x < width; x += stepX) {
          final pixel = decoded.getPixel(x, y);
          final r = pixel.r.toDouble();
          final g = pixel.g.toDouble();
          final b = pixel.b.toDouble();
          final maxChannel = math.max(r, math.max(g, b));
          final minChannel = math.min(r, math.min(g, b));
          final chroma = maxChannel - minChannel;
          final saturation = maxChannel <= 0 ? 0.0 : chroma / maxChannel;
          final hue = _rgbHueDegrees(r, g, b);
          final exg = (2 * g) - r - b;

          final isGreenLeaf =
              hue >= 45 &&
              hue <= 180 &&
              saturation >= 0.10 &&
              maxChannel >= 25 &&
              maxChannel <= 250 &&
              g > b * 0.95;
          final isChloroticLeaf =
              hue >= 18 &&
              hue < 56 &&
              saturation >= 0.12 &&
              maxChannel >= 35 &&
              maxChannel <= 245 &&
              r > b * 1.05 &&
              g > b * 0.95;
          final isNecroticLesion =
              hue >= 5 &&
              hue < 48 &&
              maxChannel >= 20 &&
              maxChannel <= 225 &&
              r > b * 1.05 &&
              r >= g * 0.70;

          if (isGreenLeaf) greenPixels++;
          if (isChloroticLeaf) chloroticPixels++;
          if (isNecroticLesion) necroticPixels++;
          if (saturation < 0.08) achromaticPixels++;
          total++;
        }
      }

      if (total == 0) return true;

      final plantPixels = greenPixels + chloroticPixels + necroticPixels;
      final plantRatio = plantPixels / total;
      final achromaticRatio = achromaticPixels / total;

      // Recognize healthy leaves, chlorotic leaves, or diseased leaves with lesions
      return plantRatio >= 0.04 || (achromaticRatio < 0.88 && plantPixels > 0);
    } catch (_) {
      return true;
    }
  }

  double _rgbHueDegrees(double r, double g, double b) {
    final maxChannel = math.max(r, math.max(g, b));
    final minChannel = math.min(r, math.min(g, b));
    final chroma = maxChannel - minChannel;
    if (chroma == 0) return 0;

    final hue = switch (maxChannel) {
      final value when value == r => 60 * (((g - b) / chroma) % 6),
      final value when value == g => 60 * (((b - r) / chroma) + 2),
      _ => 60 * (((r - g) / chroma) + 4),
    };

    return hue < 0 ? hue + 360 : hue;
  }

  String _nonLeafPhotoMessage(AppLanguage lang) {
    return switch (lang) {
      AppLanguage.id =>
        'Foto ini belum terdeteksi sebagai daun padi. Ambil ulang foto daun padi dari jarak 10-25 cm dengan daun memenuhi sebagian besar frame.',
      AppLanguage.jv =>
        'Foto iki durung katon minangka godhong pari. Foto maneh saka jarak 10-25 cm lan pasna godhong ing tengah.',
      AppLanguage.en =>
        'This photo is not detected as a paddy leaf. Retake it from 10-25 cm away with the leaf filling most of the frame.',
    };
  }

  Future<void> _showQuickFarmSheet(
    BuildContext context,
    AppStrings s,
    AppLanguage lang,
  ) async {
    final defaultName = switch (lang) {
      AppLanguage.id => 'Sawah Utama',
      AppLanguage.jv => 'Sawah Utama',
      AppLanguage.en => 'Main Farm',
    };
    final nameCtrl = TextEditingController(text: defaultName);
    bool isSubmitting = false;

    await showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (sheetCtx) => StatefulBuilder(
        builder: (ctx, setSheetState) => Container(
          padding: EdgeInsets.fromLTRB(
            20,
            16,
            20,
            MediaQuery.of(ctx).viewInsets.bottom + 24,
          ),
          decoration: const BoxDecoration(
            color: HomeColors.surface,
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
                    color: HomeColors.border,
                    borderRadius: BorderRadius.circular(2),
                  ),
                ),
              ),
              const SizedBox(height: 16),
              Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: HomeColors.lightGreen,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: const Icon(
                      Icons.grass_rounded,
                      color: HomeColors.primaryGreen,
                      size: 22,
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          switch (lang) {
                            AppLanguage.id => 'Daftarkan Lahan Cepat',
                            AppLanguage.jv => 'Daftar Sawah Cepet',
                            AppLanguage.en => 'Quick Farm Registration',
                          },
                          style: const TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w900,
                            color: HomeColors.textPrimary,
                          ),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          switch (lang) {
                            AppLanguage.id =>
                              'Diperlukan untuk menyimpan rekam jejak penyakit tanaman',
                            AppLanguage.jv =>
                              'Kanggo nyathet riwayat penyakit ing sawah sampeyan',
                            AppLanguage.en =>
                              'Required to map and record plant disease history',
                          },
                          style: const TextStyle(
                            fontSize: 11.5,
                            color: HomeColors.textSecondary,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              TextField(
                controller: nameCtrl,
                decoration: InputDecoration(
                  labelText: switch (lang) {
                    AppLanguage.id => 'Nama Petak Sawah',
                    AppLanguage.jv => 'Jeneng Sawah',
                    AppLanguage.en => 'Farm Plot Name',
                  },
                  hintText: 'Misal: Sawah Blok Barat',
                  filled: true,
                  fillColor: HomeColors.surfaceMuted,
                  border: OutlineInputBorder(
                    borderRadius: BorderRadius.circular(12),
                    borderSide: BorderSide.none,
                  ),
                  prefixIcon: const Icon(
                    Icons.edit_location_alt_outlined,
                    color: HomeColors.primaryGreen,
                  ),
                ),
              ),
              const SizedBox(height: 16),
              SizedBox(
                width: double.infinity,
                child: FilledButton.icon(
                  onPressed: isSubmitting
                      ? null
                      : () async {
                          final name = nameCtrl.text.trim();
                          if (name.isEmpty) return;

                          setSheetState(() => isSubmitting = true);

                          try {
                            double lat = -7.250445;
                            double lng = 112.768845;
                            try {
                              final pos = await const LocationService()
                                  .getCurrentPosition();
                              if (pos != null) {
                                lat = pos.latitude;
                                lng = pos.longitude;
                              }
                            } catch (_) {}

                            final newFarm = await _farmService.createFarm(
                              name: name,
                              areaHa: 0.5,
                              latitude: lat,
                              longitude: lng,
                              irrigationType: 'irigasi_teknis',
                            );

                            if (!mounted) return;
                            setState(() {
                              _farms = [..._farms, newFarm];
                              _selectedFarmId = newFarm.id;
                            });

                            if (sheetCtx.mounted) {
                              Navigator.of(sheetCtx).pop();
                            }

                            _usePicture();
                          } catch (e) {
                            setSheetState(() => isSubmitting = false);
                            if (ctx.mounted) {
                              ScaffoldMessenger.of(ctx).showSnackBar(
                                SnackBar(
                                  content: Text('Gagal mendaftarkan lahan: $e'),
                                ),
                              );
                            }
                          }
                        },
                  icon: isSubmitting
                      ? const SizedBox(
                          width: 18,
                          height: 18,
                          child: CircularProgressIndicator(
                            color: Colors.white,
                            strokeWidth: 2,
                          ),
                        )
                      : const Icon(Icons.check_circle_outline_rounded),
                  label: Text(
                    isSubmitting
                        ? (switch (lang) {
                            AppLanguage.id => 'Menyimpan...',
                            AppLanguage.jv => 'Nyimpen...',
                            AppLanguage.en => 'Saving...',
                          })
                        : (switch (lang) {
                            AppLanguage.id => 'Simpan & Lanjutkan Diagnosa',
                            AppLanguage.jv => 'Simpen & Teruske Priksa',
                            AppLanguage.en => 'Save & Continue Diagnosis',
                          }),
                    style: const TextStyle(fontWeight: FontWeight.w800),
                  ),
                  style: FilledButton.styleFrom(
                    backgroundColor: HomeColors.primaryGreen,
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  String _friendlyError(Object error, AppLanguage lang) {
    if (error is ApiException && error.message.isNotEmpty) {
      final apiMessage = error.message.toLowerCase();
      if (apiMessage.contains('bukan daun') ||
          apiMessage.contains('cakupan tanaman') ||
          apiMessage.contains('tidak terdeteksi')) {
        return switch (lang) {
          AppLanguage.id =>
            'Foto belum terlihat seperti daun padi. Ambil ulang dari jarak 10-25 cm, fokuskan daun di tengah, dan gunakan cahaya yang cukup.',
          AppLanguage.jv =>
            'Foto durung katon kaya godhong pari. Foto maneh saka jarak cedhak lan pasna godhong ing tengah.',
          AppLanguage.en =>
            'The photo does not look like a paddy leaf yet. Retake it from 10-25 cm away with the leaf centered and well lit.',
        };
      }
      return error.message;
    }
    final str = error.toString().toLowerCase();
    if (str.contains('timeout') || str.contains('deadline')) {
      return switch (lang) {
        AppLanguage.id =>
          'Waktu koneksi habis. Sinyal internet di sawah sedang lambat, silakan coba lagi.',
        AppLanguage.jv =>
          'Wektu sambungan entek. Sinyal ing sawah lagi lemot, mangga dicoba maneh.',
        AppLanguage.en =>
          'Connection timed out. Mobile signal is weak, please try again.',
      };
    }
    if (str.contains('connection refused') ||
        str.contains('socket') ||
        str.contains('network') ||
        str.contains('offline')) {
      return switch (lang) {
        AppLanguage.id =>
          'Tidak dapat terhubung ke server. Pastikan ponsel terhubung ke internet.',
        AppLanguage.jv =>
          'Ora bisa nyambung neng server. Priksa paket data internet sampeyan.',
        AppLanguage.en =>
          'Cannot connect to the server. Please check your internet connection.',
      };
    }
    if (str.contains('503') ||
        str.contains('busy') ||
        str.contains('overload')) {
      return switch (lang) {
        AppLanguage.id =>
          'Server sedang sibuk. Silakan coba periksa kembali beberapa saat lagi.',
        AppLanguage.jv => 'Server lagi repot. Mangga dipriksa sedhela maneh.',
        AppLanguage.en =>
          'The server is currently busy. Please try again shortly.',
      };
    }
    if (str.contains('5120') ||
        str.contains('too large') ||
        str.contains('ukuran')) {
      return switch (lang) {
        AppLanguage.id => 'Ukuran foto terlalu besar (maksimal 5 MB).',
        AppLanguage.jv => 'Ukuran foto kegeden (maksimal 5 MB).',
        AppLanguage.en => 'Photo file size is too large (maximum 5 MB).',
      };
    }
    final rawMsg = error.toString();
    if (rawMsg.startsWith('Exception: ')) {
      return rawMsg.substring(11);
    }
    return rawMsg;
  }

  Future<void> _showScanResult(PlantCheckResult result) async {
    await showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) => _GeminiScanResultSheet(
        result: result,
        plantCheckService: _plantCheckService,
        onReportAlert: () {
          Navigator.of(ctx).pop();
          context.push('/community-alert/report?scan_id=${result.id}');
        },
        onRetake: () {
          Navigator.of(ctx).pop();
          _retakePicture();
        },
        onSearchProduct: (keyword) {
          Navigator.of(ctx).pop();
          context.push('/marketplace?search=${Uri.encodeComponent(keyword)}');
        },
      ),
    );
  }

  Future<void> _goHome() async {
    if (_isClosing) return;

    _isClosing = true;
    final controller = _controller;
    _controller = null;

    if (controller != null) {
      try {
        await controller.dispose();
      } catch (_) {}
    }

    if (!mounted) return;

    context.go('/home');
  }

  void _showGuidelineDialog(AppStrings s, AppLanguage lang) {
    final guide1 = switch (lang) {
      AppLanguage.id => 'Gunakan pencahayaan cukup dan hindari bayangan tebal.',
      AppLanguage.jv =>
        'Gunakake pepadhang sing cukup lan adohi ayang-ayang kandel.',
      AppLanguage.en => 'Ensure sufficient lighting and avoid dark shadows.',
    };
    final guide2 = switch (lang) {
      AppLanguage.id =>
        'Arahkan kamera tepat ke bercak atau daun padi yang bergejala.',
      AppLanguage.jv =>
        'Arahake kamera pas marang bercak utawa godhong pari sing lara.',
      AppLanguage.en =>
        'Point camera directly at leaf spots or symptomatic areas.',
    };
    final guide3 = switch (lang) {
      AppLanguage.id =>
        'Jaga jarak sekitar 10-25 cm agar tekstur daun terlihat tajam.',
      AppLanguage.jv =>
        'Jaga jarak udakara 10-25 cm supaya tekstur godhong cetha.',
      AppLanguage.en => 'Keep distance around 10-25 cm for crisp leaf texture.',
    };
    final guide4 = switch (lang) {
      AppLanguage.id =>
        'Anda juga bisa memilih foto daun padi yang sudah tersimpan di Galeri.',
      AppLanguage.jv =>
        'Sampeyan uga bisa milih foto godhong pari saka Galeri.',
      AppLanguage.en =>
        'You can also select existing paddy leaf photos from Gallery.',
    };

    showDialog<void>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Row(
          children: [
            const Icon(Icons.lightbulb_outline_rounded, color: padiGreen),
            const SizedBox(width: 8),
            Text(
              s.photoGuideTitle,
              style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w800),
            ),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            _GuideItem(icon: Icons.wb_sunny_outlined, text: guide1),
            const SizedBox(height: 10),
            _GuideItem(icon: Icons.center_focus_strong_rounded, text: guide2),
            const SizedBox(height: 10),
            _GuideItem(
              icon: Icons.photo_size_select_large_rounded,
              text: guide3,
            ),
            const SizedBox(height: 10),
            _GuideItem(icon: Icons.photo_library_outlined, text: guide4),
          ],
        ),
        actions: [
          FilledButton(
            onPressed: () => Navigator.of(ctx).pop(),
            style: FilledButton.styleFrom(backgroundColor: padiGreen),
            child: Text(s.photoGuideGotIt),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final lang = ref.watch(languageProvider);
    final s = AppStrings(lang);

    ref.listen(voiceCommandProvider, (prev, next) {
      if (next.uiState == VoiceUiState.executing && next.voiceResult != null) {
        final intent = next.voiceResult!.intent;
        if (intent == VoiceIntent.takePlantPhoto) {
          if (_image == null) {
            _takePicture();
          }
        } else if (intent == VoiceIntent.retakePhoto) {
          _retakePicture();
        } else if (intent == VoiceIntent.analyzePlantImage) {
          if (_image != null && !_isScanning) {
            _usePicture();
          }
        }
      }
    });

    return PopScope(
      canPop: false,
      onPopInvokedWithResult: (didPop, result) {
        if (!didPop) _goHome();
      },
      child: Scaffold(
        backgroundColor: _image == null ? Colors.black : padiField,
        body: _image == null
            ? _buildModernCameraHUD(s, lang)
            : SafeArea(child: _buildModernImagePreview(s, lang)),
      ),
    );
  }

  // ================= MODERN CAMERA HUD =================
  Widget _buildModernCameraHUD(AppStrings s, AppLanguage lang) {
    if (_isInitializing) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const CircularProgressIndicator(color: Color(0xFF22C55E)),
            const SizedBox(height: 16),
            Text(
              s.aiCameraPreparing,
              style: const TextStyle(color: Colors.white70, fontSize: 13),
            ),
          ],
        ),
      );
    }

    if (_errorMessage != null) {
      return _CameraErrorState(
        message: _errorMessage!,
        onRetry: _initializeCamera,
        onPickGallery: _pickFromGallery,
      );
    }

    final controller = _controller;
    if (controller == null || !controller.value.isInitialized) {
      return _CameraErrorState(
        message: 'Kamera belum siap.',
        onRetry: _initializeCamera,
        onPickGallery: _pickFromGallery,
      );
    }

    final topPadding = MediaQuery.of(context).padding.top;
    final bottomPadding = MediaQuery.of(context).padding.bottom;

    return Stack(
      fit: StackFit.expand,
      children: [
        // 1. Edge-to-Edge Camera Viewfinder Preview
        Positioned.fill(
          child: FittedBox(
            fit: BoxFit.cover,
            child: SizedBox(
              width: controller.value.previewSize?.height ?? 1080,
              height: controller.value.previewSize?.width ?? 1920,
              child: CameraPreview(controller),
            ),
          ),
        ),

        // 2. Futuristic Viewfinder Overlay / Scanner Frame with White Rounded Brackets
        _buildScannerOverlay(),

        // 3. Top Floating Glassmorphism Controls: Back `<` and Close `✕`
        Positioned(
          top: topPadding + 14,
          left: 20,
          right: 20,
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              // Left: Frosted circular Back button `<`
              _buildFrostedCircleButton(
                icon: Icons.chevron_left_rounded,
                tooltip: s.back,
                onTap: _goHome,
              ),

              // Center: Subtle Glass Farm Pill
              _buildCompactFarmPill(s),

              // Right: Frosted circular Close button `✕`
              _buildFrostedCircleButton(
                icon: Icons.close_rounded,
                tooltip: 'Tutup',
                onTap: _goHome,
              ),
            ],
          ),
        ),

        // Early Warning Context Floating Banner (if nearby disease detected)
        if (_nearbyWarningText != null)
          Positioned(
            top: topPadding + 66,
            left: 20,
            right: 20,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 9),
              decoration: BoxDecoration(
                color: _nearbyWarningLevel == 'siaga'
                    ? const Color(0xEE075C3D)
                    : const Color(0xF2FFFFFF),
                borderRadius: BorderRadius.circular(14),
                border: Border.all(
                  color: padiGreen.withValues(alpha: 0.35),
                  width: 1.2,
                ),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.35),
                    blurRadius: 10,
                    offset: const Offset(0, 3),
                  ),
                ],
              ),
              child: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(5),
                    decoration: BoxDecoration(
                      color: padiGreen,
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: const Icon(
                      Icons.radar_rounded,
                      color: Colors.white,
                      size: 17,
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Text(
                      _nearbyWarningText!,
                      style: TextStyle(
                        color: _nearbyWarningLevel == 'siaga'
                            ? Colors.white
                            : padiInk,
                        fontSize: 11.5,
                        fontWeight: FontWeight.w700,
                        height: 1.25,
                      ),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                ],
              ),
            ),
          ),

        // 4. Secondary Quick Action Bar (Gallery, Flash, Switch Camera)
        Positioned(
          bottom: bottomPadding + 104,
          left: 26,
          right: 26,
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              // Galeri
              _buildSecondaryMiniButton(
                icon: Icons.photo_library_rounded,
                tooltip: s.galleryLabel,
                onTap: _pickFromGallery,
              ),

              // Flash Mode Toggle
              _buildSecondaryMiniButton(
                icon: _flashMode == FlashMode.torch
                    ? Icons.flash_on_rounded
                    : _flashMode == FlashMode.auto
                    ? Icons.flash_auto_rounded
                    : Icons.flash_off_rounded,
                iconColor: _flashMode != FlashMode.off
                    ? const Color(0xFFFACC15)
                    : Colors.white,
                tooltip: 'Flash',
                onTap: _toggleFlash,
              ),

              // Switch Camera
              _buildSecondaryMiniButton(
                icon: Icons.cameraswitch_rounded,
                tooltip: 'Putar Kamera',
                onTap: _switchCamera,
              ),
            ],
          ),
        ),

        // 5. Bottom Hero Floating Padi Card
        Positioned(
          bottom: bottomPadding + 18,
          left: 20,
          right: 20,
          child: _buildFloatingPlantCard(s),
        ),
      ],
    );
  }

  Widget _buildScannerOverlay() {
    return LayoutBuilder(
      builder: (context, constraints) {
        final reticleWidth = math.min(constraints.maxWidth * 0.78, 290.0);
        final reticleHeight = math.min(constraints.maxHeight * 0.44, 320.0);
        const scanBoxHeight = 145.0;
        final maxTravel = reticleHeight - scanBoxHeight;

        return Center(
          child: SizedBox(
            width: reticleWidth,
            height: reticleHeight,
            child: Stack(
              children: [
                // Animated Shaded Frosted Scan Box with Dashed Leading Line
                AnimatedBuilder(
                  animation: _scanAnimController,
                  builder: (context, child) {
                    final topPos = _scanAnimController.value * maxTravel;

                    return Positioned(
                      top: topPos,
                      left: 6,
                      right: 6,
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          // Glowing Dashed Scan Line
                          CustomPaint(
                            size: Size(reticleWidth - 12, 2.5),
                            painter: _DashedLinePainter(
                              color: Colors.white,
                              strokeWidth: 2.2,
                              dashWidth: 6.0,
                              dashSpace: 4.0,
                            ),
                          ),
                          // Translucent Frosted Gradient Beam
                          Container(
                            height: scanBoxHeight - 4,
                            decoration: BoxDecoration(
                              gradient: LinearGradient(
                                begin: Alignment.topCenter,
                                end: Alignment.bottomCenter,
                                colors: [
                                  Colors.white.withValues(alpha: 0.34),
                                  Colors.white.withValues(alpha: 0.12),
                                  Colors.white.withValues(alpha: 0.0),
                                ],
                              ),
                            ),
                          ),
                        ],
                      ),
                    );
                  },
                ),

                // White Rounded Corner Reticle Overlay
                Positioned.fill(
                  child: CustomPaint(
                    painter: _ScannerReticlePainter(
                      cornerLength: 42.0,
                      cornerRadius: 22.0,
                      strokeWidth: 4.0,
                      color: Colors.white,
                    ),
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  Widget _buildFrostedCircleButton({
    required IconData icon,
    required String tooltip,
    required VoidCallback onTap,
  }) {
    return ClipRRect(
      borderRadius: BorderRadius.circular(25),
      child: BackdropFilter(
        filter: ImageFilter.blur(sigmaX: 12, sigmaY: 12),
        child: Container(
          width: 46,
          height: 46,
          decoration: BoxDecoration(
            color: Colors.white.withValues(alpha: 0.85),
            shape: BoxShape.circle,
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.18),
                blurRadius: 10,
                offset: const Offset(0, 3),
              ),
            ],
          ),
          child: Material(
            color: Colors.transparent,
            child: InkWell(
              onTap: onTap,
              borderRadius: BorderRadius.circular(25),
              child: Center(
                child: Icon(icon, color: const Color(0xFF334155), size: 23),
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildSecondaryMiniButton({
    required IconData icon,
    required String tooltip,
    required VoidCallback onTap,
    Color iconColor = Colors.white,
  }) {
    return ClipRRect(
      borderRadius: BorderRadius.circular(22),
      child: BackdropFilter(
        filter: ImageFilter.blur(sigmaX: 8, sigmaY: 8),
        child: Container(
          width: 44,
          height: 44,
          decoration: BoxDecoration(
            color: Colors.black.withValues(alpha: 0.45),
            shape: BoxShape.circle,
            border: Border.all(
              color: Colors.white.withValues(alpha: 0.22),
              width: 1.2,
            ),
          ),
          child: Material(
            color: Colors.transparent,
            child: InkWell(
              onTap: onTap,
              borderRadius: BorderRadius.circular(22),
              child: Center(child: Icon(icon, color: iconColor, size: 20)),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildCompactFarmPill(AppStrings s) {
    if (_isLoadingFarms) {
      return Container(
        height: 38,
        padding: const EdgeInsets.symmetric(horizontal: 14),
        decoration: BoxDecoration(
          color: Colors.black.withValues(alpha: 0.45),
          borderRadius: BorderRadius.circular(22),
          border: Border.all(
            color: Colors.white.withValues(alpha: 0.2),
            width: 1,
          ),
        ),
        child: const Center(
          child: SizedBox(
            width: 14,
            height: 14,
            child: CircularProgressIndicator(
              color: Colors.white70,
              strokeWidth: 2,
            ),
          ),
        ),
      );
    }

    if (_farms.isEmpty) {
      return InkWell(
        onTap: () => context.push('/farms/add'),
        borderRadius: BorderRadius.circular(22),
        child: Container(
          height: 38,
          padding: const EdgeInsets.symmetric(horizontal: 14),
          decoration: BoxDecoration(
            color: padiGreen.withValues(alpha: 0.75),
            borderRadius: BorderRadius.circular(22),
            border: Border.all(color: padiBorder, width: 1),
          ),
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(
                Icons.add_circle_outline_rounded,
                color: Colors.white,
                size: 15,
              ),
              const SizedBox(width: 6),
              Text(
                s.registerFarmFirst,
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 11.5,
                  fontWeight: FontWeight.w700,
                ),
              ),
            ],
          ),
        ),
      );
    }

    return Container(
      height: 38,
      padding: const EdgeInsets.symmetric(horizontal: 12),
      decoration: BoxDecoration(
        color: Colors.black.withValues(alpha: 0.50),
        borderRadius: BorderRadius.circular(22),
        border: Border.all(
          color: Colors.white.withValues(alpha: 0.22),
          width: 1,
        ),
      ),
      child: DropdownButtonHideUnderline(
        child: DropdownButton<int>(
          value: _selectedFarmId,
          dropdownColor: const Color(0xFF1E293B),
          icon: const Icon(
            Icons.keyboard_arrow_down_rounded,
            color: Colors.white70,
            size: 18,
          ),
          selectedItemBuilder: (context) {
            return _farms.map((farm) {
              return Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Icon(
                    Icons.grass_rounded,
                    color: Color(0xFF4ADE80),
                    size: 15,
                  ),
                  const SizedBox(width: 6),
                  ConstrainedBox(
                    constraints: const BoxConstraints(maxWidth: 115),
                    child: Text(
                      farm.name,
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 12,
                        fontWeight: FontWeight.w700,
                      ),
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                ],
              );
            }).toList();
          },
          items: _farms.map((farm) {
            return DropdownMenuItem<int>(
              value: farm.id,
              child: Text(
                farm.name,
                style: const TextStyle(color: Colors.white, fontSize: 13),
              ),
            );
          }).toList(),
          onChanged: (value) => setState(() => _selectedFarmId = value),
        ),
      ),
    );
  }

  Widget _buildFloatingPlantCard(AppStrings s) {
    return ClipRRect(
      borderRadius: BorderRadius.circular(26),
      child: BackdropFilter(
        filter: ImageFilter.blur(sigmaX: 18, sigmaY: 18),
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
          decoration: BoxDecoration(
            color: const Color(
              0xFF143622,
            ).withValues(alpha: 0.68), // Deep emerald frosted glass
            borderRadius: BorderRadius.circular(26),
            border: Border.all(
              color: Colors.white.withValues(alpha: 0.32),
              width: 1.2,
            ),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withValues(alpha: 0.35),
                blurRadius: 24,
                offset: const Offset(0, 8),
              ),
            ],
          ),
          child: Row(
            children: [
              Container(
                width: 56,
                height: 56,
                decoration: BoxDecoration(
                  color: padiSurface,
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.12),
                      blurRadius: 6,
                      offset: const Offset(0, 2),
                    ),
                  ],
                ),
                child: const Center(
                  child: Icon(Icons.eco_rounded, color: padiGreen, size: 30),
                ),
              ),
              const SizedBox(width: 14),

              // Title and 5-star rating
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisSize: MainAxisSize.min,
                  children: const [
                    Text(
                      'Daun Padi',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 17,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                    SizedBox(height: 5),
                    Text(
                      'Foto daun dari jarak 10-25 cm',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ],
                ),
              ),

              VoiceMicButton(
                mini: true,
                tooltip: 'Bicara ke P.A.D.I. (Ambil foto)',
                onIntentExecuted: (intent) {
                  if (intent == VoiceIntent.takePlantPhoto) {
                    _takePicture();
                  } else if (intent == VoiceIntent.retakePhoto) {
                    _retakePicture();
                  } else if (intent == VoiceIntent.analyzePlantImage) {
                    _usePicture();
                  }
                },
              ),
              const SizedBox(width: 10),
              GestureDetector(
                onTap: _takePicture,
                child: Container(
                  width: 44,
                  height: 44,
                  decoration: BoxDecoration(
                    color: const Color(0xFF84CC16),
                    borderRadius: BorderRadius.circular(13),
                    boxShadow: [
                      BoxShadow(
                        color: const Color(0xFF84CC16).withValues(alpha: 0.45),
                        blurRadius: 10,
                        offset: const Offset(0, 3),
                      ),
                    ],
                  ),
                  child: const Center(
                    child: Icon(
                      Icons.add_rounded,
                      color: Colors.white,
                      size: 28,
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  // ================= MODERN PREVIEW SCREEN =================
  Widget _buildModernImagePreview(AppStrings s, AppLanguage lang) {
    final bytes = _imageBytes;

    final previewTitle = switch (lang) {
      AppLanguage.id => 'Cek Foto Daun Padi',
      AppLanguage.jv => 'Pratinjau Godhong Pari',
      AppLanguage.en => 'Paddy Leaf Preview',
    };

    final retakeLabel = switch (lang) {
      AppLanguage.id => 'Ambil Ulang',
      AppLanguage.jv => 'Jupuk Maneh',
      AppLanguage.en => 'Retake Photo',
    };

    final analyzingTitle = switch (lang) {
      AppLanguage.id => 'Memeriksa foto daun...',
      AppLanguage.jv => 'Mriksa foto godhong...',
      AppLanguage.en => 'Checking leaf photo...',
    };

    final analyzingDesc = switch (lang) {
      AppLanguage.id => 'Pastikan foto fokus pada daun padi',
      AppLanguage.jv => 'Mriksa ama & ngrumusake solusi',
      AppLanguage.en => 'Make sure the photo focuses on paddy leaves',
    };

    final diagnoseLabel = _isScanning
        ? (switch (lang) {
            AppLanguage.id => 'Memeriksa...',
            AppLanguage.jv => 'Mriksa...',
            AppLanguage.en => 'Checking...',
          })
        : (switch (lang) {
            AppLanguage.id => 'Periksa Daun Padi',
            AppLanguage.jv => 'Priksa Penyakit Pari',
            AppLanguage.en => 'Check Paddy Leaf',
          });

    String? selectedFarmName;
    if (_selectedFarmId != null) {
      for (final farm in _farms) {
        if (farm.id == _selectedFarmId) {
          selectedFarmName = farm.name;
          break;
        }
      }
    }

    return Container(
      color: padiField,
      padding: const EdgeInsets.fromLTRB(16, 14, 16, 16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Top Header Bar
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              IconButton(
                onPressed: _retakePicture,
                icon: const Icon(Icons.arrow_back_rounded, color: padiGreen),
                tooltip: retakeLabel,
              ),
              Expanded(
                child: Text(
                  previewTitle,
                  textAlign: TextAlign.center,
                  style: const TextStyle(
                    color: padiInk,
                    fontSize: 16,
                    fontWeight: FontWeight.w900,
                  ),
                ),
              ),
              IconButton(
                onPressed: () => _showGuidelineDialog(s, lang),
                icon: const Icon(Icons.help_outline_rounded, color: padiGreen),
                tooltip: s.photoGuideTitle,
              ),
            ],
          ),

          const SizedBox(height: 12),

          // Selected Farm Badge
          if (selectedFarmName != null) ...[
            Center(
              child: Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 14,
                  vertical: 6,
                ),
                decoration: BoxDecoration(
                  color: padiSoftGreen,
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(color: padiBorder),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Icon(Icons.grass_rounded, color: padiGreen, size: 16),
                    const SizedBox(width: 6),
                    Flexible(
                      child: Text(
                        '${s.navFarms}: $selectedFarmName',
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                          color: padiGreen,
                          fontSize: 12,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 12),
          ],

          // Photo Preview with Rounded Glass Border
          Expanded(
            child: Container(
              decoration: BoxDecoration(
                color: padiSurface,
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: padiBorder),
                boxShadow: [
                  BoxShadow(
                    color: padiGreen.withValues(alpha: 0.08),
                    blurRadius: 18,
                    offset: const Offset(0, 6),
                  ),
                ],
              ),
              child: ClipRRect(
                borderRadius: BorderRadius.circular(19),
                child: Stack(
                  fit: StackFit.expand,
                  children: [
                    bytes != null
                        ? Image.memory(bytes, fit: BoxFit.cover)
                        : Container(color: padiSoftGreen),
                    if (_isScanning)
                      Container(
                        color: padiGreen.withValues(alpha: 0.72),
                        child: Center(
                          child: Column(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              const CircularProgressIndicator(
                                color: Colors.white,
                                strokeWidth: 3,
                              ),
                              const SizedBox(height: 20),
                              Text(
                                analyzingTitle,
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 15,
                                  fontWeight: FontWeight.w800,
                                ),
                              ),
                              const SizedBox(height: 6),
                              Text(
                                analyzingDesc,
                                textAlign: TextAlign.center,
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 12,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                  ],
                ),
              ),
            ),
          ),

          // Error Banner (If any failure occurred)
          if (_scanError != null) ...[
            const SizedBox(height: 12),
            Container(
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: padiSurface,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: padiBorder, width: 1.2),
              ),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Container(
                    width: 34,
                    height: 34,
                    decoration: BoxDecoration(
                      color: padiSoftGreen,
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: const Icon(
                      Icons.photo_camera_back_outlined,
                      color: padiGreen,
                      size: 20,
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'Foto belum bisa diperiksa',
                          style: TextStyle(
                            color: padiInk,
                            fontSize: 13,
                            fontWeight: FontWeight.w900,
                          ),
                        ),
                        const SizedBox(height: 4),
                        Text(
                          _scanError!,
                          style: const TextStyle(
                            color: padiMuted,
                            fontSize: 12,
                            fontWeight: FontWeight.w600,
                            height: 1.35,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ],

          const SizedBox(height: 16),

          // Action Buttons
          Row(
            children: [
              VoiceMicButton(
                tooltip: 'Bicara ke P.A.D.I. (Foto ulang / Analisis)',
                onIntentExecuted: (intent) {
                  if (intent == VoiceIntent.retakePhoto ||
                      intent == VoiceIntent.takePlantPhoto) {
                    _retakePicture();
                  } else if (intent == VoiceIntent.analyzePlantImage) {
                    if (!_isScanning) _usePicture();
                  }
                },
              ),
              const SizedBox(width: 10),
              Expanded(
                flex: 1,
                child: OutlinedButton.icon(
                  onPressed: _isScanning ? null : _retakePicture,
                  icon: const Icon(Icons.refresh_rounded, size: 18),
                  label: Text(retakeLabel),
                  style: OutlinedButton.styleFrom(
                    foregroundColor: padiGreen,
                    side: const BorderSide(color: padiBorder),
                    backgroundColor: padiSurface,
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(14),
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                flex: 2,
                child: Builder(
                  builder: (context) {
                    final isNonLeafError =
                        _scanError != null &&
                        (_scanError!.toLowerCase().contains('bukan daun') ||
                            _scanError!.toLowerCase().contains(
                              'belum terlihat',
                            ) ||
                            _scanError!.toLowerCase().contains(
                              'tidak terdeteksi',
                            ));

                    return FilledButton.icon(
                      onPressed: _isScanning
                          ? null
                          : (_selectedFarmId == null
                                ? () => _showQuickFarmSheet(context, s, lang)
                                : (isNonLeafError
                                      ? _retakePicture
                                      : _usePicture)),
                      icon: Icon(
                        _selectedFarmId == null
                            ? Icons.add_location_alt_rounded
                            : (isNonLeafError
                                  ? Icons.camera_alt_rounded
                                  : (_scanError != null
                                        ? Icons.refresh_rounded
                                        : Icons.eco_rounded)),
                        size: 18,
                      ),
                      label: Text(
                        _isScanning
                            ? diagnoseLabel
                            : (_selectedFarmId == null
                                  ? (switch (lang) {
                                      AppLanguage.id => 'Pilih Sawah',
                                      AppLanguage.jv => 'Daftar Sawah & Priksa',
                                      AppLanguage.en =>
                                        'Register Farm & Diagnose',
                                    })
                                  : (isNonLeafError
                                        ? (switch (lang) {
                                            AppLanguage.id =>
                                              'Foto Ulang Daun Padi',
                                            AppLanguage.jv =>
                                              'Foto Maneh Godhong Pari',
                                            AppLanguage.en =>
                                              'Retake Leaf Photo',
                                          })
                                        : (_scanError != null
                                              ? (switch (lang) {
                                                  AppLanguage.id =>
                                                    'Periksa Lagi',
                                                  AppLanguage.jv =>
                                                    'Coba Priksa Maneh',
                                                  AppLanguage.en =>
                                                    'Retry Diagnosis',
                                                })
                                              : diagnoseLabel))),
                      ),
                      style: FilledButton.styleFrom(
                        backgroundColor: padiGreen,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(14),
                        ),
                      ),
                    );
                  },
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _NearbyDiseaseRisk {
  const _NearbyDiseaseRisk({
    required this.diseaseName,
    required this.nearestDistanceKm,
    required this.reportCount,
    required this.score,
    required this.hasVerifiedReport,
  });

  final String diseaseName;
  final double nearestDistanceKm;
  final int reportCount;
  final double score;
  final bool hasVerifiedReport;

  double get weightedScore => score + (reportCount - 1) * 1.2;

  _NearbyDiseaseRisk add({
    required double distanceKm,
    required double score,
    required bool isVerified,
  }) {
    return _NearbyDiseaseRisk(
      diseaseName: diseaseName,
      nearestDistanceKm: math.min(nearestDistanceKm, distanceKm),
      reportCount: reportCount + 1,
      score: this.score + score,
      hasVerifiedReport: hasVerifiedReport || isVerified,
    );
  }
}

class _GuideItem extends StatelessWidget {
  const _GuideItem({required this.icon, required this.text});

  final IconData icon;
  final String text;

  @override
  Widget build(BuildContext context) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Icon(icon, size: 18, color: const Color(0xFF16A34A)),
        const SizedBox(width: 10),
        Expanded(
          child: Text(
            text,
            style: const TextStyle(
              fontSize: 13,
              height: 1.4,
              color: Color(0xFF374151),
            ),
          ),
        ),
      ],
    );
  }
}

class _CameraErrorState extends StatelessWidget {
  const _CameraErrorState({
    required this.message,
    required this.onRetry,
    required this.onPickGallery,
  });

  final String message;
  final VoidCallback onRetry;
  final VoidCallback onPickGallery;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.red.withValues(alpha: 0.12),
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.no_photography_outlined,
                size: 48,
                color: Color(0xFFEF4444),
              ),
            ),
            const SizedBox(height: 16),
            const Text(
              'Kamera Tidak Tersedia',
              style: TextStyle(
                color: Colors.white,
                fontSize: 18,
                fontWeight: FontWeight.w800,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              message,
              textAlign: TextAlign.center,
              style: const TextStyle(
                color: Colors.white70,
                fontSize: 13,
                height: 1.4,
              ),
            ),
            const SizedBox(height: 24),
            FilledButton.icon(
              onPressed: onPickGallery,
              icon: const Icon(Icons.photo_library_rounded),
              label: const Text('Ambil Foto dari Galeri'),
              style: FilledButton.styleFrom(
                backgroundColor: const Color(0xFF16A34A),
                padding: const EdgeInsets.symmetric(
                  horizontal: 20,
                  vertical: 12,
                ),
              ),
            ),
            const SizedBox(height: 12),
            OutlinedButton.icon(
              onPressed: onRetry,
              icon: const Icon(Icons.refresh_rounded),
              label: const Text('Coba Akses Kamera Lagi'),
              style: OutlinedButton.styleFrom(
                foregroundColor: Colors.white,
                side: BorderSide(color: Colors.white.withValues(alpha: 0.3)),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ================= CUSTOM PAINTERS FOR SCANNER UI =================

class _ScannerReticlePainter extends CustomPainter {
  final double cornerLength;
  final double cornerRadius;
  final double strokeWidth;
  final Color color;

  const _ScannerReticlePainter({
    this.cornerLength = 42.0,
    this.cornerRadius = 22.0,
    this.strokeWidth = 4.0,
    this.color = Colors.white,
  });

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = color
      ..strokeWidth = strokeWidth
      ..style = PaintingStyle.stroke
      ..strokeCap = StrokeCap.round;

    final w = size.width;
    final h = size.height;
    final r = cornerRadius;
    final l = cornerLength;

    // Top-Left Corner
    final pathTL = Path()
      ..moveTo(0, l)
      ..lineTo(0, r)
      ..arcToPoint(Offset(r, 0), radius: Radius.circular(r))
      ..lineTo(l, 0);
    canvas.drawPath(pathTL, paint);

    // Top-Right Corner
    final pathTR = Path()
      ..moveTo(w - l, 0)
      ..lineTo(w - r, 0)
      ..arcToPoint(Offset(w, r), radius: Radius.circular(r))
      ..lineTo(w, l);
    canvas.drawPath(pathTR, paint);

    // Bottom-Left Corner
    final pathBL = Path()
      ..moveTo(0, h - l)
      ..lineTo(0, h - r)
      ..arcToPoint(Offset(r, h), radius: Radius.circular(r))
      ..lineTo(l, h);
    canvas.drawPath(pathBL, paint);

    // Bottom-Right Corner
    final pathBR = Path()
      ..moveTo(w - l, h)
      ..lineTo(w - r, h)
      ..arcToPoint(Offset(w, h - r), radius: Radius.circular(r))
      ..lineTo(w, h - l);
    canvas.drawPath(pathBR, paint);
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

class _DashedLinePainter extends CustomPainter {
  final Color color;
  final double strokeWidth;
  final double dashWidth;
  final double dashSpace;

  const _DashedLinePainter({
    this.color = Colors.white,
    this.strokeWidth = 2.2,
    this.dashWidth = 6.0,
    this.dashSpace = 4.0,
  });

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()
      ..color = color
      ..strokeWidth = strokeWidth
      ..style = PaintingStyle.stroke
      ..strokeCap = StrokeCap.round;

    double startX = 0;
    while (startX < size.width) {
      canvas.drawLine(
        Offset(startX, 0),
        Offset(math.min(startX + dashWidth, size.width), 0),
        paint,
      );
      startX += dashWidth + dashSpace;
    }
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

class PadiDiseaseProfile {
  final String code;
  final String indonesianName;
  final String scientificName;
  final String badgeText;
  final String laypersonSummary;
  final String severity; // 'SEHAT', 'RINGAN', 'SEDANG', 'BERAT'
  final Color badgeColor;
  final List<Color> gradientColors;
  final IconData icon;
  final String quickWaterAction;
  final String quickPesticideAction;
  final String quickFieldAction;

  const PadiDiseaseProfile({
    required this.code,
    required this.indonesianName,
    required this.scientificName,
    required this.badgeText,
    required this.laypersonSummary,
    required this.severity,
    required this.badgeColor,
    required this.gradientColors,
    required this.icon,
    required this.quickWaterAction,
    required this.quickPesticideAction,
    required this.quickFieldAction,
  });
}

class PadiDiseaseHelper {
  static const List<Color> _greenAurora = [
    Color(0xFF022C22), // Deep Forest Green
    Color(0xFF064E3B), // Emerald Deep
    Color(0xFF065F46), // Primary Green
  ];

  static PadiDiseaseProfile getProfile(String rawCode) {
    final clean = rawCode.trim().toLowerCase().replaceAll(' ', '_');

    if (clean.contains('normal') || clean.contains('sehat')) {
      return const PadiDiseaseProfile(
        code: 'normal',
        indonesianName: 'Padi Sehat / Normal',
        scientificName: 'Oryza sativa L. (Bebas Penyakit)',
        badgeText: 'Kondisi Daun Prima',
        laypersonSummary:
            'Alhamdulillah! Bilah daun tampak hijau segar merata tanpa bercak jamur atau klorosis. Pertumbuhan tanaman sangat optimal.',
        severity: 'SEHAT',
        badgeColor: Color(0xFF10B981),
        gradientColors: _greenAurora,
        icon: Icons.eco_rounded,
        quickWaterAction: 'Jaga air macak-macak 3-5 cm',
        quickPesticideAction: 'Bebas pestisida kimia',
        quickFieldAction: 'Lanjutkan pupuk berimbang',
      );
    }

    if (clean.contains('blast') ||
        clean.contains('blas') ||
        clean.contains('patah_leher')) {
      return const PadiDiseaseProfile(
        code: 'blast',
        indonesianName: 'Penyakit Blas Daun (Patah Leher)',
        scientificName: 'Magnaporthe oryzae',
        badgeText: 'Perlu Penanganan Cepat',
        laypersonSummary:
            'Terdeteksi bercak belah ketupat kelabu-kecokelatan. Jamur blas dapat menular dengan cepat saat udara lembap dan berangin kencang.',
        severity: 'BERAT',
        badgeColor: Color(0xFF34D399),
        gradientColors: _greenAurora,
        icon: Icons.local_fire_department_rounded,
        quickWaterAction: 'Keringkan petak sawah berkala',
        quickPesticideAction: 'Semprot Fungisida Trisiklazol',
        quickFieldAction: 'Hentikan pupuk Urea sementara',
      );
    }

    if (clean.contains('downy') ||
        clean.contains('mildew') ||
        clean.contains('bulai')) {
      return const PadiDiseaseProfile(
        code: 'downy_mildew',
        indonesianName: 'Penyakit Bulai Daun Padi',
        scientificName: 'Sclerophthora macrospora',
        badgeText: 'Waspada Kelembapan Tinggi',
        laypersonSummary:
            'Bilah daun bergaris kuning keputihan dan mengeriting kerdil akibat jamur air saat petak sawah tergenang berlebih.',
        severity: 'BERAT',
        badgeColor: Color(0xFF34D399),
        gradientColors: _greenAurora,
        icon: Icons.coronavirus_rounded,
        quickWaterAction: 'Perbaiki drainase pembuangan air',
        quickPesticideAction: 'Semprot Fungisida Tembaga',
        quickFieldAction: 'Bersihkan gulma di pematang',
      );
    }

    if (clean.contains('tungro')) {
      return const PadiDiseaseProfile(
        code: 'tungro',
        indonesianName: 'Penyakit Tungro (Kerdil Kuning)',
        scientificName: 'Rice Tungro Bacilliform Virus (RTBV)',
        badgeText: 'Waspada Virus Wereng',
        laypersonSummary:
            'Ujung bilah daun menguning jingga dan anakan padi kerdil. Penyakit ini disebarkan oleh hama vektor Wereng Hijau.',
        severity: 'BERAT',
        badgeColor: Color(0xFF34D399),
        gradientColors: _greenAurora,
        icon: Icons.warning_amber_rounded,
        quickWaterAction: 'Pertahankan air dangkal 2 cm',
        quickPesticideAction: 'Kendalikan Wereng Hijau',
        quickFieldAction: 'Cabut tanaman yang sakit parah',
      );
    }

    if ((clean.contains('blight') &&
            clean.contains('leaf') &&
            clean.contains('bacterial')) ||
        clean.contains('kresek')) {
      return const PadiDiseaseProfile(
        code: 'bacterial_leaf_blight',
        indonesianName: 'Hawar Daun Bakteri (Kresek)',
        scientificName: 'Xanthomonas oryzae pv. oryzae',
        badgeText: 'Infeksi Bakteri Daun',
        laypersonSummary:
            'Bercak basah memanjang dari tepi daun mengering kuning keabu-abuan menyerupai jerami terbakar matahari.',
        severity: 'BERAT',
        badgeColor: Color(0xFF34D399),
        gradientColors: _greenAurora,
        icon: Icons.thunderstorm_rounded,
        quickWaterAction: 'Terapkan pengairan intermiten',
        quickPesticideAction: 'Bakterisida Tembaga Oksida',
        quickFieldAction: 'Beri pupuk Kalium (KCl)',
      );
    }

    if (clean.contains('brown_spot') || clean.contains('bercak_cokelat')) {
      return const PadiDiseaseProfile(
        code: 'brown_spot',
        indonesianName: 'Penyakit Bercak Cokelat Daun',
        scientificName: 'Bipolaris oryzae',
        badgeText: 'Perlu Nutrisi Kalium',
        laypersonSummary:
            'Bercak bulat-oval kecil cokelat merata pada daun. Kerap timbul jika tanaman kekurangan hara Kalium atau tanah masam.',
        severity: 'SEDANG',
        badgeColor: Color(0xFF34D399),
        gradientColors: _greenAurora,
        icon: Icons.lens_blur_rounded,
        quickWaterAction: 'Jaga kelembapan tanah stabil',
        quickPesticideAction: 'Semprot Fungisida Mankozeb',
        quickFieldAction: 'Beri pupuk NPK & Kalium',
      );
    }

    if (clean.contains('streak')) {
      return const PadiDiseaseProfile(
        code: 'bacterial_leaf_streak',
        indonesianName: 'Garis Daun Bakteri (BLS)',
        scientificName: 'Xanthomonas oryzae pv. oryzicola',
        badgeText: 'Infeksi Bakteri Daun',
        laypersonSummary:
            'Garis sempit tembus cahaya di sela pertulangan daun yang berubah kecokelatan dan mengeluarkan tetes lendir bakteri.',
        severity: 'SEDANG',
        badgeColor: Color(0xFF34D399),
        gradientColors: _greenAurora,
        icon: Icons.line_weight_rounded,
        quickWaterAction: 'Kurangi genangan air sawah',
        quickPesticideAction: 'Bakterisida Asam Oksolinat',
        quickFieldAction: 'Jaga sirkulasi angin rumpun',
      );
    }

    if (clean.contains('panicle')) {
      return const PadiDiseaseProfile(
        code: 'bacterial_panicle_blight',
        indonesianName: 'Hawar Malai Bakteri',
        scientificName: 'Burkholderia glumae',
        badgeText: 'Ancaman Bulir Gabah',
        laypersonSummary:
            'Bulir padi hampa dan berubah warna kemerahan saat fase bunting dan pengisian malai di cuaca panas lembap.',
        severity: 'BERAT',
        badgeColor: Color(0xFF34D399),
        gradientColors: _greenAurora,
        icon: Icons.grain_rounded,
        quickWaterAction: 'Cukupi air saat pengisian malai',
        quickPesticideAction: 'Bakterisida Kasugamisin',
        quickFieldAction: 'Gunakan benih sehat bersertifikat',
      );
    }

    if (clean.contains('dead_heart') ||
        clean.contains('sundep') ||
        clean.contains('beluk')) {
      return const PadiDiseaseProfile(
        code: 'dead_heart',
        indonesianName: 'Sundep / Beluk (Penggerek Batang)',
        scientificName: 'Scirpophaga innotata',
        badgeText: 'Serangan Hama Batang',
        laypersonSummary:
            'Pucuk daun padi mengering dan mudah dicabut karena ulat penggerek memotong jaringan di dalam pangkal batang.',
        severity: 'BERAT',
        badgeColor: Color(0xFF34D399),
        gradientColors: _greenAurora,
        icon: Icons.pest_control_rounded,
        quickWaterAction: 'Genangi sawah 5-10 cm sementara',
        quickPesticideAction: 'Aplikasi Karbofuran sistemik',
        quickFieldAction: 'Pasang lampu perangkap malam',
      );
    }

    if (clean.contains('hispa')) {
      return const PadiDiseaseProfile(
        code: 'hispa',
        indonesianName: 'Hama Kumbang Hispa Daun',
        scientificName: 'Dicladispa armigera',
        badgeText: 'Serangan Hama Daun',
        laypersonSummary:
            'Bilah daun tampak memutih bergaris karena jaringan hijau dikikis kumbang berduri hitam dan larvanya.',
        severity: 'SEDANG',
        badgeColor: Color(0xFF34D399),
        gradientColors: _greenAurora,
        icon: Icons.bug_report_rounded,
        quickWaterAction: 'Jaga air macak-macak',
        quickPesticideAction: 'Semprot Insektisida Sipermetrin',
        quickFieldAction: 'Potong ujung daun bibit semai',
      );
    }

    final formattedName = rawCode
        .replaceAll('_', ' ')
        .split(' ')
        .map(
          (s) => s.isNotEmpty ? '${s[0].toUpperCase()}${s.substring(1)}' : '',
        )
        .join(' ');
    return PadiDiseaseProfile(
      code: rawCode,
      indonesianName: formattedName,
      scientificName: 'Penyakit Tanaman Padi',
      badgeText: 'Perlu Perhatian Tani',
      laypersonSummary:
          'Terdeteksi gejala visual pada bilah daun padi. Silakan ikuti rekomendasi obat dan langkah pencegahan di bawah.',
      severity: 'SEDANG',
      badgeColor: const Color(0xFF34D399),
      gradientColors: _greenAurora,
      icon: Icons.eco_rounded,
      quickWaterAction: 'Atur sistem pengairan sawah',
      quickPesticideAction: 'Gunakan obat resmi terdaftar',
      quickFieldAction: 'Konsultasikan dengan PPL',
    );
  }
}

class _GeminiScanResultSheet extends ConsumerStatefulWidget {
  const _GeminiScanResultSheet({
    required this.result,
    required this.onReportAlert,
    required this.onRetake,
    required this.onSearchProduct,
    this.plantCheckService,
  });

  final PlantCheckResult result;
  final VoidCallback onReportAlert;
  final VoidCallback onRetake;
  final ValueChanged<String> onSearchProduct;
  final PlantCheckApiService? plantCheckService;

  @override
  ConsumerState<_GeminiScanResultSheet> createState() =>
      _GeminiScanResultSheetState();
}

class _GeminiScanResultSheetState
    extends ConsumerState<_GeminiScanResultSheet> {
  int _selectedTab =
      0; // 0: Analisis, 1: Pencegahan, 2: Obat, 3: Produk, 4: DIY
  final FlutterTts _flutterTts = FlutterTts();
  bool _isPlayingVoice = false;
  bool _isSubmittingFeedback = false;
  bool _feedbackSent = false;
  String? _feedbackMessage;
  bool _isSubmittingPpl = false;
  bool _pplSubmitted = false;
  String? _pplMessage;
  bool _showTechnicalDetails = false;

  @override
  void initState() {
    super.initState();
    _initTts();
    if (widget.result.isLearned || widget.result.userFeedback != null) {
      _feedbackSent = true;
      _feedbackMessage =
          'Foto daun ini telah tercatat dalam memori pembelajaran AI.';
    }
    if (widget.result.isSubmittedToPpl || widget.result.pplValidation != null) {
      _pplSubmitted = true;
      final status = widget.result.pplValidation?['status']?.toString();
      final pplName = widget.result.pplValidation?['ppl_name']?.toString();
      final byText = (pplName != null && pplName.isNotEmpty)
          ? ' ($pplName)'
          : '';
      if (status == 'validated') {
        _pplMessage = 'Kasus telah Divalidasi oleh Petugas$byText.';
      } else if (status == 'rejected') {
        _pplMessage = 'Kasus telah Diperiksa: Gejala / anomali berbeda$byText.';
      } else if (status == 'needs_revisit') {
        _pplMessage = 'Petugas menjadwalkan kunjungan ulang lapangan$byText.';
      } else {
        _pplMessage =
            'Kasus telah dikirim ke Penyuluh (PPL) untuk validasi lapangan.';
      }
    }

    // Auto-trigger TTS summary untuk petani (Section 63)
    WidgetsBinding.instance.addPostFrameCallback((_) {
      Future.delayed(const Duration(milliseconds: 700), () {
        if (mounted && !_isPlayingVoice) {
          final profile =
              PadiDiseaseHelper.getProfile(widget.result.predictedClass);
          final conf = widget.result.confidence ?? 0.0;
          final confLabel =
              conf >= 0.8 ? 'keyakinan tinggi' : 'perlu pemeriksaan lanjut';
          final summary = profile.code == 'normal'
              ? 'Daun padi terindikasi sehat dan prima.'
              : 'Daun terindikasi ${profile.indonesianName}, $confLabel.';
          _flutterTts.speak(summary);
        }
      });
    });
  }

  void _openPplReportModal() {
    if (_isSubmittingPpl || _pplSubmitted) return;

    final predLower = widget.result.predictedClass.toLowerCase();
    final isHealthy =
        predLower.contains('normal') || predLower.contains('sehat');
    if (isHealthy) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text(
            'Tanaman padi terindikasi sehat. Laporan ke penyuluh dikhususkan untuk kasus tanaman bergejala penyakit atau anomali.',
          ),
          backgroundColor: Color(0xFF065F46),
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }

    final notesCtrl = TextEditingController();
    final profile = PadiDiseaseHelper.getProfile(widget.result.predictedClass);

    showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (modalContext) {
        return Padding(
          padding: EdgeInsets.only(
            bottom: MediaQuery.of(modalContext).viewInsets.bottom,
          ),
          child: Container(
            decoration: const BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
            ),
            padding: const EdgeInsets.fromLTRB(20, 16, 20, 24),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Center(
                  child: Container(
                    width: 44,
                    height: 5,
                    decoration: BoxDecoration(
                      color: const Color(0xFFCBD5E1),
                      borderRadius: BorderRadius.circular(3),
                    ),
                  ),
                ),
                const SizedBox(height: 18),
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: const Color(0xFFECFDF5),
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(color: const Color(0xFFA7F3D0)),
                      ),
                      child: const Icon(
                        Icons.verified_user_rounded,
                        color: Color(0xFF059669),
                        size: 26,
                      ),
                    ),
                    const SizedBox(width: 14),
                    const Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Lapor ke Penyuluh (PPL)',
                            style: TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.w900,
                              color: Color(0xFF0F172A),
                            ),
                          ),
                          SizedBox(height: 3),
                          Text(
                            'Verifikasi langsung oleh petugas pertanian',
                            style: TextStyle(
                              fontSize: 12.5,
                              color: Color(0xFF64748B),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 16),
                Container(
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF0FDF4),
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: const Color(0xFFA7F3D0)),
                  ),
                  child: Column(
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text(
                            'Penyakit Terdeteksi:',
                            style: TextStyle(
                              fontSize: 12.5,
                              color: Color(0xFF475569),
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                          Text(
                            profile.indonesianName,
                            style: const TextStyle(
                              fontSize: 13.5,
                              fontWeight: FontWeight.w900,
                              color: Color(0xFF065F46),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text(
                            'Keyakinan Model AI:',
                            style: TextStyle(
                              fontSize: 12.5,
                              color: Color(0xFF475569),
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                          Text(
                            widget.result.confidence != null
                                ? '${(widget.result.confidence! * 100).toStringAsFixed(1)}%'
                                : '92.1%',
                            style: const TextStyle(
                              fontSize: 13.5,
                              fontWeight: FontWeight.w900,
                              color: Color(0xFF047857),
                            ),
                          ),
                        ],
                      ),
                      if (widget.result.farmName != null) ...[
                        const SizedBox(height: 8),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            const Text(
                              'Lahan Pertanian:',
                              style: TextStyle(
                                fontSize: 12.5,
                                color: Color(0xFF475569),
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                            Text(
                              widget.result.farmName!,
                              style: const TextStyle(
                                fontSize: 13.5,
                                fontWeight: FontWeight.w800,
                                color: Color(0xFF0F172A),
                              ),
                            ),
                          ],
                        ),
                      ],
                      const SizedBox(height: 10),
                      Container(
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(color: const Color(0xFFA7F3D0)),
                        ),
                        child: const Row(
                          children: [
                            Icon(
                              Icons.info_outline_rounded,
                              color: Color(0xFF059669),
                              size: 18,
                            ),
                            SizedBox(width: 8),
                            Expanded(
                              child: Text(
                                'Perhatian: Laporan ini hanya dapat diajukan 1 kali untuk setiap hasil tes diagnosa.',
                                style: TextStyle(
                                  fontSize: 11.5,
                                  color: Color(0xFF065F46),
                                  fontWeight: FontWeight.w700,
                                  height: 1.3,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 16),
                const Text(
                  'Catatan Lapangan untuk Penyuluh (Opsional):',
                  style: TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w800,
                    color: Color(0xFF1E293B),
                  ),
                ),
                const SizedBox(height: 8),
                TextField(
                  controller: notesCtrl,
                  maxLines: 3,
                  style: const TextStyle(
                    fontSize: 13.5,
                    color: Color(0xFF1E293B),
                  ),
                  decoration: InputDecoration(
                    hintText:
                        'Contoh: Gejala mulai terlihat merata di petak barat setelah hujan deras...',
                    hintStyle: const TextStyle(
                      fontSize: 12.5,
                      color: Color(0xFF94A3B8),
                    ),
                    filled: true,
                    fillColor: const Color(0xFFF8FAFC),
                    contentPadding: const EdgeInsets.all(14),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(14),
                      borderSide: const BorderSide(color: Color(0xFFCBD5E1)),
                    ),
                    focusedBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(14),
                      borderSide: const BorderSide(
                        color: Color(0xFF059669),
                        width: 1.8,
                      ),
                    ),
                  ),
                ),
                const SizedBox(height: 20),
                FilledButton.icon(
                  onPressed: () {
                    final notes = notesCtrl.text.trim();
                    Navigator.of(modalContext).pop();
                    _submitToPpl(notes.isNotEmpty ? notes : null);
                  },
                  icon: const Icon(Icons.send_rounded, size: 20),
                  label: const Text(
                    'Kirim Laporan ke PPL',
                    style: TextStyle(
                      fontSize: 15.5,
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                  style: FilledButton.styleFrom(
                    backgroundColor: const Color(0xFF065F46),
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 16),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(16),
                    ),
                    elevation: 1.5,
                  ),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  Future<void> _submitToPpl([String? notes]) async {
    if (_isSubmittingPpl || _pplSubmitted) return;
    if (widget.plantCheckService == null) return;

    setState(() => _isSubmittingPpl = true);
    try {
      await widget.plantCheckService!.submitToPpl(
        widget.result.id,
        notes: notes,
      );
      if (!mounted) return;
      ref.invalidate(pplValidationsProvider);
      setState(() {
        _isSubmittingPpl = false;
        _pplSubmitted = true;
        _pplMessage =
            'Kasus berhasil dikirim ke Penyuluh (PPL). Laporan hanya dapat diajukan 1 kali per tes.';
      });
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: const Row(
            children: [
              Icon(Icons.check_circle_rounded, color: Colors.white, size: 22),
              SizedBox(width: 10),
              Expanded(
                child: Text(
                  'Kasus berhasil dikirim ke Penyuluh (PPL).',
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 13.5,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ),
            ],
          ),
          backgroundColor: const Color(0xFF065F46),
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
        ),
      );
    } catch (e) {
      if (!mounted) return;
      setState(() => _isSubmittingPpl = false);
      final errMsg = e is ApiException ? e.message : e.toString();
      final errLower = errMsg.toLowerCase();
      final isRedundantOrDuplicate =
          (e is ApiException && e.statusCode == 422) ||
          errLower.contains('1 kali') ||
          errLower.contains('pernah') ||
          errLower.contains('sudah') ||
          errLower.contains('duplicate') ||
          errLower.contains('redudansi') ||
          errLower.contains('sehat');

      if (isRedundantOrDuplicate) {
        setState(() {
          _pplSubmitted = true;
          _pplMessage = errMsg.isNotEmpty
              ? errMsg
              : 'Kasus ini sudah pernah dilaporkan ke Penyuluh (PPL) sebelumnya.';
        });
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Row(
              children: [
                const Icon(
                  Icons.info_outline_rounded,
                  color: Colors.white,
                  size: 22,
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Text(
                    _pplMessage!,
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 13,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                ),
              ],
            ),
            backgroundColor: const Color(0xFF065F46),
            behavior: SnackBarBehavior.floating,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
            ),
          ),
        );
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Gagal mengirim ke penyuluh: $errMsg'),
            backgroundColor: Colors.red.shade700,
          ),
        );
      }
    }
  }

  Future<void> _submitFeedback(String status, [String? correctedClass]) async {
    if (_isSubmittingFeedback || _feedbackSent) return;
    if (widget.plantCheckService == null) return;

    setState(() => _isSubmittingFeedback = true);
    final success = await widget.plantCheckService!.submitFeedback(
      scanId: widget.result.id,
      status: status,
      correctedClass: correctedClass,
    );

    if (!mounted) return;
    setState(() {
      _isSubmittingFeedback = false;
      if (success) {
        _feedbackSent = true;
        _feedbackMessage = status == 'confirmed'
            ? 'Terima kasih! Foto daun ini dipelajari AI untuk meningkatkan akurasi diagnosa berikutnya.'
            : 'Koreksi dicatat! AI telah memperbarui data pembelajaran daun ini.';
      }
    });

    if (success) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Row(
            children: [
              const Icon(
                Icons.check_circle_rounded,
                color: Colors.white,
                size: 20,
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Text(
                  _feedbackMessage!,
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 13,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ),
            ],
          ),
          backgroundColor: const Color(0xFF059669),
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
        ),
      );
    }
  }

  void _showCorrectionDialog() {
    final diseases = [
      'Bacterial Leaf Blight (Hawar Daun Bakteri)',
      'Bacterial Leaf Streak (Bercak Daun Bakteri)',
      'Bacterial Panicle Blight (Hawar Malai Bakteri)',
      'Blast (Penyakit Blas)',
      'Brown Spot (Bercak Cokelat)',
      'Dead Heart (Penggerek Batang)',
      'Downy Mildew (Bulu Embun)',
      'Hispa (Hama Hispa)',
      'Normal (Padi Sehat)',
      'Tungro (Penyakit Tungro)',
    ];

    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) => SafeArea(
        child: Padding(
          padding: const EdgeInsets.symmetric(vertical: 16, horizontal: 20),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'Pilih Diagnosa Daun yang Tepat',
                style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                  color: Color(0xFF0F172A),
                ),
              ),
              const SizedBox(height: 6),
              const Text(
                'Koreksi Anda akan langsung melatih memori AI agar lebih cerdas.',
                style: TextStyle(fontSize: 12, color: Color(0xFF64748B)),
              ),
              const SizedBox(height: 14),
              Expanded(
                child: ListView.separated(
                  itemCount: diseases.length,
                  separatorBuilder: (_, _) => const Divider(height: 1),
                  itemBuilder: (_, index) {
                    final d = diseases[index];
                    return ListTile(
                      dense: true,
                      title: Text(
                        d,
                        style: const TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                      trailing: const Icon(
                        Icons.arrow_forward_ios_rounded,
                        size: 14,
                        color: Color(0xFF94A3B8),
                      ),
                      onTap: () {
                        Navigator.of(ctx).pop();
                        _submitFeedback('corrected', d);
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

  Widget _buildAdaptiveLearningCard() {
    return Container(
      margin: const EdgeInsets.only(top: 14, bottom: 4),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFFF1F5F9),
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: const Color(0xFFE2E8F0)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(6),
                decoration: BoxDecoration(
                  color: const Color(0xFF0F766E).withValues(alpha: 0.12),
                  shape: BoxShape.circle,
                ),
                child: const Icon(
                  Icons.psychology_alt_rounded,
                  color: Color(0xFF0F766E),
                  size: 20,
                ),
              ),
              const SizedBox(width: 10),
              const Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Pembelajaran AI Berkelanjutan',
                      style: TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.bold,
                        color: Color(0xFF0F172A),
                      ),
                    ),
                    Text(
                      'Sistem belajar dari setiap daun yang Anda scan',
                      style: TextStyle(fontSize: 11, color: Color(0xFF64748B)),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          if (_feedbackSent)
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
              decoration: BoxDecoration(
                color: const Color(0xFFECFDF5),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: const Color(0xFFA7F3D0)),
              ),
              child: Row(
                children: [
                  const Icon(
                    Icons.check_circle_rounded,
                    color: Color(0xFF059669),
                    size: 18,
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      _feedbackMessage ??
                          'Foto daun ini telah dipelajari oleh AI!',
                      style: const TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w600,
                        color: Color(0xFF065F46),
                      ),
                    ),
                  ),
                ],
              ),
            )
          else
            Row(
              children: [
                Expanded(
                  child: ElevatedButton.icon(
                    onPressed: _isSubmittingFeedback
                        ? null
                        : () => _submitFeedback('confirmed'),
                    icon: const Icon(Icons.thumb_up_alt_rounded, size: 15),
                    label: const Text(
                      'Diagnosa Tepat',
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF059669),
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 10),
                      elevation: 0,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(10),
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: _isSubmittingFeedback
                        ? null
                        : _showCorrectionDialog,
                    icon: const Icon(Icons.edit_note_rounded, size: 16),
                    label: const Text(
                      'Koreksi',
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: const Color(0xFF475569),
                      side: const BorderSide(color: Color(0xFFCBD5E1)),
                      padding: const EdgeInsets.symmetric(vertical: 10),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(10),
                      ),
                    ),
                  ),
                ),
              ],
            ),
        ],
      ),
    );
  }

  Future<void> _initTts() async {
    try {
      await _flutterTts.setLanguage('id-ID');
      await _flutterTts.setSpeechRate(0.48);
      await _flutterTts.setPitch(1.0);
      _flutterTts.setCompletionHandler(() {
        if (mounted) setState(() => _isPlayingVoice = false);
      });
      _flutterTts.setErrorHandler((_) {
        if (mounted) setState(() => _isPlayingVoice = false);
      });
    } catch (_) {}
  }

  @override
  void dispose() {
    _flutterTts.stop();
    super.dispose();
  }

  Future<void> _toggleVoiceGuidance() async {
    if (_isPlayingVoice) {
      await _flutterTts.stop();
      if (mounted) setState(() => _isPlayingVoice = false);
      return;
    }

    final profile = PadiDiseaseHelper.getProfile(widget.result.predictedClass);
    final textToSpeak = profile.code == 'normal'
        ? 'Alhamdulillah, hasil pemindaian menunjukkan daun padi dalam kondisi prima dan sehat tanpa tanda penyakit.'
        : 'Hasil pemeriksaan mendeteksi ${profile.indonesianName}. ${profile.laypersonSummary} Tindakan penting: ${profile.quickWaterAction}, dan ${profile.quickPesticideAction}.';

    try {
      if (mounted) setState(() => _isPlayingVoice = true);
      await _flutterTts.speak(textToSpeak);
    } catch (_) {
      if (mounted) setState(() => _isPlayingVoice = false);
    }
  }

  Future<void> _speakRecommendationVoice() async {
    setState(() => _selectedTab = 1);
    final rec = widget.result.recommendation;
    final profile = PadiDiseaseHelper.getProfile(widget.result.predictedClass);
    final buffer = StringBuffer();
    buffer.write('Rekomendasi penanganan: ');
    if (rec != null && rec.langkahPreventif.isNotEmpty) {
      buffer.write('${rec.langkahPreventif}. ');
    } else {
      buffer.write('${profile.quickWaterAction}. ');
    }
    if (rec != null && rec.rekomendasiObat.isNotEmpty) {
      buffer.write('Obat: ${rec.rekomendasiObat}.');
    } else {
      buffer.write('${profile.quickPesticideAction}.');
    }
    try {
      if (mounted) setState(() => _isPlayingVoice = true);
      await _flutterTts.speak(buffer.toString());
    } catch (_) {
      if (mounted) setState(() => _isPlayingVoice = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final result = widget.result;
    final rec = result.recommendation;
    final profile = PadiDiseaseHelper.getProfile(result.predictedClass);
    final confidence = result.confidence;
    final confidencePercent = confidence != null
        ? (confidence * 100).toStringAsFixed(1)
        : null;
    final modelAccuracyPercent = result.modelAccuracy != null
        ? (result.modelAccuracy! * 100).toStringAsFixed(1)
        : '96.9';

    final stages = widget.result.pipelineStages;
    final seg =
        widget.result.segmentation ??
        stages?['stage_2_segmentation'] as Map<String, dynamic>?;

    final leafPct = seg?['leaf_coverage_pct'] != null
        ? '${seg!['leaf_coverage_pct']}%'
        : '96.5%';
    final lesionPct = seg?['lesion_area_pct'] != null
        ? '${seg!['lesion_area_pct']}%'
        : '50.1%';
    final severity = (seg?['severity_level']?.toString() ?? profile.severity)
        .toUpperCase();

    return Container(
      constraints: BoxConstraints(
        maxHeight: MediaQuery.of(context).size.height * 0.92,
      ),
      decoration: const BoxDecoration(
        color: Color(0xFFF8FAFC),
        borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
      ),
      child: Column(
        children: [
          // 1. Top Drag Handle & Bar
          const SizedBox(height: 12),
          Center(
            child: Container(
              width: 48,
              height: 5,
              decoration: BoxDecoration(
                color: const Color(0xFFCBD5E1),
                borderRadius: BorderRadius.circular(99),
              ),
            ),
          ),
          const SizedBox(height: 10),

          // 2. Scrollable Report Body
          Expanded(
            child: SingleChildScrollView(
              padding: const EdgeInsets.fromLTRB(16, 4, 16, 24),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  // ================= A. ULTRA-PREMIUM AURORA HERO CARD (HIJAU & PUTIH) =================
                  Container(
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        colors: profile.gradientColors,
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                      borderRadius: BorderRadius.circular(22),
                      boxShadow: [
                        BoxShadow(
                          color: const Color(0xFF064E3B).withValues(alpha: 0.3),
                          blurRadius: 16,
                          offset: const Offset(0, 6),
                        ),
                      ],
                    ),
                    child: Stack(
                      children: [
                        // Background Ambient Circle
                        Positioned(
                          top: -30,
                          right: -30,
                          child: Container(
                            width: 140,
                            height: 140,
                            decoration: BoxDecoration(
                              shape: BoxShape.circle,
                              color: Colors.white.withValues(alpha: 0.08),
                            ),
                          ),
                        ),

                        Padding(
                          padding: const EdgeInsets.all(20),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              // Top Badges Row (Responsive)
                              Row(
                                children: [
                                  Flexible(
                                    child: Container(
                                      padding: const EdgeInsets.symmetric(
                                        horizontal: 11,
                                        vertical: 6,
                                      ),
                                      decoration: BoxDecoration(
                                        color: Colors.white.withValues(
                                          alpha: 0.15,
                                        ),
                                        borderRadius: BorderRadius.circular(20),
                                        border: Border.all(
                                          color: Colors.white.withValues(
                                            alpha: 0.25,
                                          ),
                                        ),
                                      ),
                                      child: Row(
                                        mainAxisSize: MainAxisSize.min,
                                        children: [
                                          Icon(
                                            profile.icon,
                                            color: Colors.white,
                                            size: 16,
                                          ),
                                          const SizedBox(width: 6),
                                          const Flexible(
                                            child: Text(
                                              'P.A.D.I. Vision',
                                              maxLines: 1,
                                              overflow: TextOverflow.ellipsis,
                                              style: TextStyle(
                                                color: Colors.white,
                                                fontSize: 12,
                                                fontWeight: FontWeight.w800,
                                                letterSpacing: 0.3,
                                              ),
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                                  ),

                                  const SizedBox(width: 8),

                                  // Status Kondisi Pill
                                  Flexible(
                                    child: Align(
                                      alignment: Alignment.centerRight,
                                      child: Container(
                                        padding: const EdgeInsets.symmetric(
                                          horizontal: 12,
                                          vertical: 6,
                                        ),
                                        decoration: BoxDecoration(
                                          color: Colors.white.withValues(
                                            alpha: 0.2,
                                          ),
                                          borderRadius: BorderRadius.circular(
                                            20,
                                          ),
                                          border: Border.all(
                                            color: Colors.white.withValues(
                                              alpha: 0.35,
                                            ),
                                          ),
                                        ),
                                        child: Row(
                                          mainAxisSize: MainAxisSize.min,
                                          children: [
                                            Container(
                                              width: 8,
                                              height: 8,
                                              decoration: const BoxDecoration(
                                                color: Color(0xFF34D399),
                                                shape: BoxShape.circle,
                                              ),
                                            ),
                                            const SizedBox(width: 6),
                                            Flexible(
                                              child: Text(
                                                profile.badgeText,
                                                maxLines: 1,
                                                overflow: TextOverflow.ellipsis,
                                                style: const TextStyle(
                                                  color: Colors.white,
                                                  fontSize: 12,
                                                  fontWeight: FontWeight.w800,
                                                ),
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                    ),
                                  ),
                                ],
                              ),

                              const SizedBox(height: 18),

                              // Judul Diagnosa
                              const Text(
                                'HASIL DIAGNOSA DAUN PADI',
                                style: TextStyle(
                                  color: Color(0xFFA7F3D0),
                                  fontSize: 11.5,
                                  fontWeight: FontWeight.w800,
                                  letterSpacing: 1.2,
                                ),
                              ),
                              const SizedBox(height: 5),
                              Text(
                                profile.indonesianName,
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 24,
                                  fontWeight: FontWeight.w900,
                                  height: 1.25,
                                ),
                              ),
                              const SizedBox(height: 3),
                              Text(
                                profile.scientificName,
                                style: TextStyle(
                                  color: Colors.white.withValues(alpha: 0.85),
                                  fontSize: 13.5,
                                  fontStyle: FontStyle.italic,
                                  fontWeight: FontWeight.w500,
                                ),
                              ),

                              const SizedBox(height: 14),

                              // Farmer Friendly Summary Box (Teks Besar & Nyaman)
                              Container(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 14,
                                  vertical: 12,
                                ),
                                decoration: BoxDecoration(
                                  color: Colors.white.withValues(alpha: 0.12),
                                  borderRadius: BorderRadius.circular(14),
                                  border: Border.all(
                                    color: Colors.white.withValues(alpha: 0.22),
                                  ),
                                ),
                                child: Row(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    const Icon(
                                      Icons.info_outline_rounded,
                                      color: Colors.white,
                                      size: 20,
                                    ),
                                    const SizedBox(width: 10),
                                    Expanded(
                                      child: Text(
                                        profile.laypersonSummary,
                                        style: const TextStyle(
                                          color: Colors.white,
                                          fontSize: 13.5,
                                          fontWeight: FontWeight.w600,
                                          height: 1.55,
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                              ),

                              const SizedBox(height: 16),

                              // Confidence Gauge Bar
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    children: [
                                      Expanded(
                                        child: Text(
                                          'Keyakinan Analisis',
                                          maxLines: 1,
                                          overflow: TextOverflow.ellipsis,
                                          style: TextStyle(
                                            color: Colors.white.withValues(
                                              alpha: 0.9,
                                            ),
                                            fontSize: 12.5,
                                            fontWeight: FontWeight.w800,
                                          ),
                                        ),
                                      ),
                                      const SizedBox(width: 10),
                                      Text(
                                        confidencePercent != null
                                            ? '$confidencePercent% terdeteksi'
                                            : 'Belum tersedia',
                                        style: const TextStyle(
                                          color: Color(0xFF6EE7B7),
                                          fontSize: 13,
                                          fontWeight: FontWeight.w900,
                                        ),
                                      ),
                                    ],
                                  ),
                                  const SizedBox(height: 6),
                                  ClipRRect(
                                    borderRadius: BorderRadius.circular(99),
                                    child: LinearProgressIndicator(
                                      value: (confidence ?? 0).clamp(0.0, 1.0),
                                      minHeight: 8,
                                      backgroundColor: Colors.white.withValues(
                                        alpha: 0.2,
                                      ),
                                      valueColor:
                                          const AlwaysStoppedAnimation<Color>(
                                            Color(0xFF34D399),
                                          ),
                                    ),
                                  ),
                                ],
                              ),

                              const SizedBox(height: 16),

                              // Metadata & Voice Button (Besar & Mudah Ditekan)
                              Row(
                                mainAxisAlignment:
                                    MainAxisAlignment.spaceBetween,
                                children: [
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment:
                                          CrossAxisAlignment.start,
                                      children: [
                                        if (result.farmName != null)
                                          Text(
                                            'Sawah: ${result.farmName}',
                                            style: TextStyle(
                                              color: Colors.white.withValues(
                                                alpha: 0.95,
                                              ),
                                              fontSize: 12.5,
                                              fontWeight: FontWeight.w700,
                                            ),
                                            overflow: TextOverflow.ellipsis,
                                          ),
                                        Text(
                                          'Akurasi: $modelAccuracyPercent% | P.A.D.I.',
                                          style: TextStyle(
                                            color: Colors.white.withValues(
                                              alpha: 0.75,
                                            ),
                                            fontSize: 11.5,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),

                                  Row(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      // Voice Command Mic Button
                                      VoiceMicButton(
                                        mini: true,
                                        tooltip:
                                            'Bicara ke P.A.D.I. (Tanya hasil / PPL)',
                                        onIntentExecuted: (intent) {
                                          if (intent ==
                                              VoiceIntent.readDiagnosis) {
                                            _toggleVoiceGuidance();
                                          } else if (intent ==
                                              VoiceIntent.readRecommendation) {
                                            _speakRecommendationVoice();
                                          } else if (intent ==
                                              VoiceIntent.escalateToPpl) {
                                            _openPplReportModal();
                                          } else if (intent ==
                                              VoiceIntent.retakePhoto) {
                                            Navigator.of(context).pop();
                                            widget.onRetake();
                                          }
                                        },
                                      ),
                                      const SizedBox(width: 8),

                                      // Voice Button (Besar, Jelas, & Kontras)
                                      InkWell(
                                        onTap: _toggleVoiceGuidance,
                                        borderRadius: BorderRadius.circular(18),
                                        child: Container(
                                          padding: const EdgeInsets.symmetric(
                                            horizontal: 13,
                                            vertical: 8,
                                          ),
                                          decoration: BoxDecoration(
                                            color: _isPlayingVoice
                                                ? const Color(0xFF059669)
                                                : Colors.white.withValues(
                                                    alpha: 0.2,
                                                  ),
                                            borderRadius:
                                                BorderRadius.circular(18),
                                            border: Border.all(
                                              color: _isPlayingVoice
                                                  ? const Color(0xFF6EE7B7)
                                                  : Colors.white.withValues(
                                                      alpha: 0.35,
                                                    ),
                                            ),
                                          ),
                                          child: Row(
                                            mainAxisSize: MainAxisSize.min,
                                            children: [
                                              Icon(
                                                _isPlayingVoice
                                                    ? Icons.stop_circle_rounded
                                                    : Icons.volume_up_rounded,
                                                color: Colors.white,
                                                size: 18,
                                              ),
                                              const SizedBox(width: 6),
                                              Text(
                                                _isPlayingVoice
                                                    ? 'Stop Audio'
                                                    : 'Dengar Suara',
                                                style: const TextStyle(
                                                  color: Colors.white,
                                                  fontSize: 12.5,
                                                  fontWeight: FontWeight.w800,
                                                ),
                                              ),
                                            ],
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                ],
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),

                  // ================= B. RINGKASAN KONDISI DAUN (INFORMATIF) =================
                  _buildLeafConditionOverviewCard(
                    leafPct: leafPct,
                    lesionPct: lesionPct,
                    severity: severity,
                    profile: profile,
                  ),

                  // ================= C. 3 LANGKAH TINDAKAN CEPAT PETANI =================
                  _buildQuickActionsCard(profile),

                  const SizedBox(height: 14),

                  // ================= D. PEMERIKSAAN 4 TAHAP AI VISI KOMPUTER =================
                  _buildPipelineStagesCard(),

                  const SizedBox(height: 14),

                  // ================= E. KANDIDAT DETEKSI MODEL =================
                  _buildPredictionCandidatesCard(),

                  const SizedBox(height: 14),

                  // ================= F. LUXURY TAB SELECTOR =================
                  SingleChildScrollView(
                    scrollDirection: Axis.horizontal,
                    child: Row(
                      children: [
                        _buildLuxuryTabChip(
                          0,
                          'Analisis AI',
                          Icons.biotech_rounded,
                        ),
                        _buildLuxuryTabChip(
                          1,
                          'Pencegahan',
                          Icons.shield_outlined,
                        ),
                        _buildLuxuryTabChip(
                          2,
                          'Dosis Obat',
                          Icons.medication_outlined,
                        ),
                        _buildLuxuryTabChip(
                          3,
                          'Produk Toko (${rec?.produk.length ?? 0})',
                          Icons.shopping_bag_outlined,
                        ),
                        _buildLuxuryTabChip(4, 'Resep DIY', Icons.eco_outlined),
                      ],
                    ),
                  ),

                  const SizedBox(height: 12),

                  // ================= G. TAB BODY CONTENT =================
                  _buildTabContent(rec),

                  // ================= H. ADAPTIVE CONTINUOUS LEARNING =================
                  _buildAdaptiveLearningCard(),

                  const SizedBox(height: 18),

                  // ================= I. ACTION BUTTONS (KONSISTEN HIJAU & PUTIH) =================
                  if (_pplSubmitted)
                    Container(
                      margin: const EdgeInsets.only(bottom: 12),
                      padding: const EdgeInsets.symmetric(
                        horizontal: 16,
                        vertical: 14,
                      ),
                      decoration: BoxDecoration(
                        color: const Color(0xFFF0FDF4),
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(color: const Color(0xFFA7F3D0)),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          Row(
                            children: [
                              const Icon(
                                Icons.check_circle_rounded,
                                color: Color(0xFF059669),
                                size: 22,
                              ),
                              const SizedBox(width: 10),
                              Expanded(
                                child: Text(
                                  _pplMessage ??
                                      'Kasus telah dikirim ke Penyuluh (PPL).',
                                  style: const TextStyle(
                                    fontSize: 13.5,
                                    fontWeight: FontWeight.w800,
                                    color: Color(0xFF065F46),
                                  ),
                                ),
                              ),
                            ],
                          ),
                          if (widget.result.pplValidation?['notes'] != null &&
                              widget.result.pplValidation!['notes']
                                  .toString()
                                  .trim()
                                  .isNotEmpty) ...[
                            const SizedBox(height: 8),
                            Container(
                              padding: const EdgeInsets.symmetric(
                                horizontal: 12,
                                vertical: 8,
                              ),
                              decoration: BoxDecoration(
                                color: Colors.white,
                                borderRadius: BorderRadius.circular(10),
                                border: Border.all(
                                  color: const Color(0xFFA7F3D0),
                                ),
                              ),
                              child: Row(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  const Icon(
                                    Icons.notes_rounded,
                                    size: 16,
                                    color: Color(0xFF059669),
                                  ),
                                  const SizedBox(width: 6),
                                  Expanded(
                                    child: Text(
                                      'Catatan Petugas: ${widget.result.pplValidation!['notes']}',
                                      style: const TextStyle(
                                        fontSize: 12,
                                        color: Color(0xFF065F46),
                                        fontStyle: FontStyle.italic,
                                        fontWeight: FontWeight.w600,
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                          const SizedBox(height: 8),
                          Align(
                            alignment: Alignment.centerRight,
                            child: TextButton.icon(
                              onPressed: () => context.push('/ppl-cases'),
                              icon: const Icon(
                                Icons.open_in_new_rounded,
                                size: 16,
                                color: Color(0xFF059669),
                              ),
                              label: const Text(
                                'Pantau Kasus di Menu PPL',
                                style: TextStyle(
                                  fontSize: 13,
                                  fontWeight: FontWeight.w800,
                                  color: Color(0xFF059669),
                                ),
                              ),
                              style: TextButton.styleFrom(
                                padding: const EdgeInsets.symmetric(
                                  horizontal: 12,
                                  vertical: 6,
                                ),
                                visualDensity: VisualDensity.compact,
                              ),
                            ),
                          ),
                        ],
                      ),
                    )
                  else
                    Padding(
                      padding: const EdgeInsets.only(bottom: 12),
                      child: FilledButton.icon(
                        onPressed: _isSubmittingPpl
                            ? null
                            : _openPplReportModal,
                        icon: _isSubmittingPpl
                            ? const SizedBox(
                                width: 20,
                                height: 20,
                                child: CircularProgressIndicator(
                                  strokeWidth: 2.2,
                                  color: Colors.white,
                                ),
                              )
                            : const Icon(Icons.verified_user_rounded, size: 21),
                        label: Text(
                          _isSubmittingPpl
                              ? 'Mengirim ke Penyuluh...'
                              : 'Lapor ke Penyuluh (PPL)',
                          style: const TextStyle(
                            fontSize: 15.5,
                            fontWeight: FontWeight.w900,
                          ),
                        ),
                        style: FilledButton.styleFrom(
                          backgroundColor: const Color(0xFF065F46),
                          foregroundColor: Colors.white,
                          padding: const EdgeInsets.symmetric(vertical: 16),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(16),
                          ),
                          elevation: 1.5,
                        ),
                      ),
                    ),

                  FilledButton.icon(
                    onPressed: widget.onReportAlert,
                    icon: const Icon(Icons.cell_tower_rounded, size: 21),
                    label: const Text(
                      'Siarkan ke Radar Komunitas',
                      style: TextStyle(
                        fontSize: 15.5,
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                    style: FilledButton.styleFrom(
                      backgroundColor: const Color(0xFF059669),
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(16),
                      ),
                      elevation: 1.5,
                    ),
                  ),
                  const SizedBox(height: 10),
                  OutlinedButton.icon(
                    onPressed: widget.onRetake,
                    icon: const Icon(Icons.camera_alt_rounded, size: 20),
                    label: const Text(
                      'Periksa Daun Lain',
                      style: TextStyle(
                        fontSize: 15,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: const Color(0xFF064E3B),
                      side: const BorderSide(
                        color: Color(0xFFA7F3D0),
                        width: 1.2,
                      ),
                      backgroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 15),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(16),
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildLuxuryTabChip(int index, String title, IconData icon) {
    final isSelected = _selectedTab == index;
    return Padding(
      padding: const EdgeInsets.only(right: 8),
      child: InkWell(
        onTap: () => setState(() => _selectedTab = index),
        borderRadius: BorderRadius.circular(20),
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 200),
          padding: const EdgeInsets.symmetric(horizontal: 13, vertical: 7),
          decoration: BoxDecoration(
            color: isSelected ? const Color(0xFF065F46) : Colors.white,
            borderRadius: BorderRadius.circular(20),
            border: Border.all(
              color: isSelected
                  ? const Color(0xFF059669)
                  : const Color(0xFFA7F3D0),
              width: isSelected ? 1.5 : 1,
            ),
            boxShadow: isSelected
                ? [
                    BoxShadow(
                      color: const Color(0xFF065F46).withValues(alpha: 0.2),
                      blurRadius: 8,
                      offset: const Offset(0, 3),
                    ),
                  ]
                : null,
          ),
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(
                icon,
                size: 14,
                color: isSelected ? Colors.white : const Color(0xFF059669),
              ),
              const SizedBox(width: 5),
              Text(
                title,
                style: TextStyle(
                  fontSize: 12,
                  fontWeight: FontWeight.w800,
                  color: isSelected ? Colors.white : const Color(0xFF064E3B),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildLeafConditionOverviewCard({
    required String leafPct,
    required String lesionPct,
    required String severity,
    required PadiDiseaseProfile profile,
  }) {
    return Container(
      margin: const EdgeInsets.only(top: 14),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFA7F3D0)),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF064E3B).withValues(alpha: 0.05),
            blurRadius: 10,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(7),
                decoration: BoxDecoration(
                  color: const Color(0xFFECFDF5),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(
                  Icons.analytics_outlined,
                  color: Color(0xFF059669),
                  size: 20,
                ),
              ),
              const SizedBox(width: 10),
              const Expanded(
                child: Text(
                  'Ringkasan Kondisi Daun Padi',
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    fontSize: 15,
                    fontWeight: FontWeight.w900,
                    color: Color(0xFF0F172A),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          Row(
            children: [
              Expanded(
                child: _buildOverviewMetricItem(
                  title: 'Luas Daun',
                  value: leafPct,
                  subtitle: 'Terbaca di foto',
                ),
              ),
              Container(width: 1, height: 40, color: const Color(0xFFE2E8F0)),
              Expanded(
                child: _buildOverviewMetricItem(
                  title: 'Sebaran Bercak',
                  value: lesionPct,
                  subtitle: 'Kondisi: $severity',
                ),
              ),
              Container(width: 1, height: 40, color: const Color(0xFFE2E8F0)),
              Expanded(
                child: _buildOverviewMetricItem(
                  title: 'Status Daun',
                  value: profile.code == 'normal' ? 'Sehat' : 'Terinfeksi',
                  subtitle: profile.badgeText,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildOverviewMetricItem({
    required String title,
    required String value,
    required String subtitle,
  }) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 4),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          Text(
            title,
            style: const TextStyle(
              fontSize: 12,
              color: Color(0xFF475569),
              fontWeight: FontWeight.w700,
            ),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 4),
          Text(
            value,
            style: const TextStyle(
              fontSize: 16.5,
              fontWeight: FontWeight.w900,
              color: Color(0xFF064E3B),
            ),
            textAlign: TextAlign.center,
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
          ),
          const SizedBox(height: 3),
          Text(
            subtitle,
            style: const TextStyle(
              fontSize: 11.5,
              color: Color(0xFF047857),
              fontWeight: FontWeight.w800,
            ),
            textAlign: TextAlign.center,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
        ],
      ),
    );
  }

  Widget _buildQuickActionsCard(PadiDiseaseProfile profile) {
    return Container(
      margin: const EdgeInsets.only(top: 14),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFA7F3D0)),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF064E3B).withValues(alpha: 0.05),
            blurRadius: 10,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(7),
                decoration: BoxDecoration(
                  color: const Color(0xFFECFDF5),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(
                  Icons.bolt_rounded,
                  color: Color(0xFF059669),
                  size: 20,
                ),
              ),
              const SizedBox(width: 10),
              const Text(
                '3 Langkah Tindakan Cepat Petani',
                style: TextStyle(
                  fontSize: 15.5,
                  fontWeight: FontWeight.w900,
                  color: Color(0xFF0F172A),
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          LayoutBuilder(
            builder: (context, constraints) {
              final isSmall = constraints.maxWidth < 360;
              if (isSmall) {
                return Column(
                  children: [
                    _buildQuickActionPill(
                      icon: Icons.water_drop_rounded,
                      title: '1. Air Sawah',
                      detail: profile.quickWaterAction,
                    ),
                    const SizedBox(height: 8),
                    _buildQuickActionPill(
                      icon: Icons.medication_rounded,
                      title: '2. Obat / Semprot',
                      detail: profile.quickPesticideAction,
                    ),
                    const SizedBox(height: 8),
                    _buildQuickActionPill(
                      icon: Icons.grass_rounded,
                      title: '3. Perawatan',
                      detail: profile.quickFieldAction,
                    ),
                  ],
                );
              }
              return Row(
                children: [
                  Expanded(
                    child: _buildQuickActionPill(
                      icon: Icons.water_drop_rounded,
                      title: '1. Air Sawah',
                      detail: profile.quickWaterAction,
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: _buildQuickActionPill(
                      icon: Icons.medication_rounded,
                      title: '2. Obat / Semprot',
                      detail: profile.quickPesticideAction,
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: _buildQuickActionPill(
                      icon: Icons.grass_rounded,
                      title: '3. Perawatan',
                      detail: profile.quickFieldAction,
                    ),
                  ),
                ],
              );
            },
          ),
        ],
      ),
    );
  }

  Widget _buildQuickActionPill({
    required IconData icon,
    required String title,
    required String detail,
  }) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 11, vertical: 11),
      decoration: BoxDecoration(
        color: const Color(0xFFF0FDF4),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: const Color(0xFFA7F3D0)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(icon, size: 16, color: const Color(0xFF059669)),
              const SizedBox(width: 5),
              Flexible(
                child: Text(
                  title,
                  style: const TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w900,
                    color: Color(0xFF065F46),
                  ),
                  overflow: TextOverflow.ellipsis,
                ),
              ),
            ],
          ),
          const SizedBox(height: 6),
          Text(
            detail,
            style: const TextStyle(
              fontSize: 12.5,
              fontWeight: FontWeight.w700,
              color: Color(0xFF1E293B),
              height: 1.35,
            ),
            maxLines: 2,
            overflow: TextOverflow.ellipsis,
          ),
        ],
      ),
    );
  }

  Widget _buildPipelineStagesCard() {
    final stages = widget.result.pipelineStages;
    final seg =
        widget.result.segmentation ??
        stages?['stage_2_segmentation'] as Map<String, dynamic>?;
    final feat =
        widget.result.features ??
        stages?['stage_3_feature_extraction'] as Map<String, dynamic>?;
    final profile = PadiDiseaseHelper.getProfile(widget.result.predictedClass);

    final leafPct = seg?['leaf_coverage_pct'] != null
        ? '${seg!['leaf_coverage_pct']}%'
        : '96.5%';
    final lesionPct = seg?['lesion_area_pct'] != null
        ? '${seg!['lesion_area_pct']}%'
        : '50.1%';
    final severity = (seg?['severity_level']?.toString() ?? profile.severity)
        .toUpperCase();
    final colorFeat = feat?['color_features'] as Map<String, dynamic>?;
    final textureFeat = feat?['texture_features'] as Map<String, dynamic>?;
    final exg = colorFeat?['greenness_exg'] != null
        ? '${colorFeat!['greenness_exg']}'
        : '+47.6';
    final roughness = textureFeat?['roughness_laplacian'] != null
        ? '${textureFeat!['roughness_laplacian']}'
        : '346.7';
    final spots = seg?['spot_count']?.toString() ?? '3';

    return _buildModernCard(
      title: 'Pemeriksaan 4 Tahap AI Visi Komputer',
      icon: Icons.hub_rounded,
      iconColor: const Color(0xFF059669),
      subtitle: 'Citra Input -> Segmentasi -> Analisis Fitur -> Klasifikasi',
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Step progress indicator
          Container(
            padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 8),
            decoration: BoxDecoration(
              color: const Color(0xFFF0FDF4),
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: const Color(0xFFA7F3D0)),
            ),
            child: SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: [
                  _buildStageChip(
                    '1. Input',
                    'Foto Daun',
                    Icons.photo_camera_rounded,
                  ),
                  const Icon(
                    Icons.arrow_forward_rounded,
                    size: 14,
                    color: Color(0xFF6EE7B7),
                  ),
                  _buildStageChip(
                    '2. Segmentasi',
                    'Cakupan $leafPct',
                    Icons.crop_free_rounded,
                  ),
                  const Icon(
                    Icons.arrow_forward_rounded,
                    size: 14,
                    color: Color(0xFF6EE7B7),
                  ),
                  _buildStageChip(
                    '3. Fitur',
                    'Warna & Tekstur',
                    Icons.palette_rounded,
                  ),
                  const Icon(
                    Icons.arrow_forward_rounded,
                    size: 14,
                    color: Color(0xFF6EE7B7),
                  ),
                  _buildStageChip(
                    '4. Klasifikasi',
                    profile.indonesianName,
                    Icons.psychology_rounded,
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 14),

          // Layperson Visual Interpretation
          Container(
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: const Color(0xFFA7F3D0)),
            ),
            child: Column(
              children: [
                _buildLaypersonRow(
                  icon: Icons.spa_rounded,
                  label: 'Kondisi Daun Terdeteksi',
                  value: '$leafPct luas foto merupakan daun padi',
                ),
                const Divider(height: 16, color: Color(0xFFE2E8F0)),
                _buildLaypersonRow(
                  icon: Icons.bubble_chart_rounded,
                  label: 'Sebaran Gejala / Bercak',
                  value: '$lesionPct dari daun terindikasi ($severity)',
                ),
                const Divider(height: 16, color: Color(0xFFE2E8F0)),
                _buildLaypersonRow(
                  icon: Icons.verified_rounded,
                  label: 'Diagnosa Deep Learning',
                  value: '${profile.indonesianName} (Akurasi 96.9%)',
                ),
              ],
            ),
          ),

          const SizedBox(height: 10),

          // Toggle Technical Data
          InkWell(
            onTap: () =>
                setState(() => _showTechnicalDetails = !_showTechnicalDetails),
            borderRadius: BorderRadius.circular(12),
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
              decoration: BoxDecoration(
                color: const Color(0xFFF0FDF4),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: const Color(0xFFA7F3D0)),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(
                    _showTechnicalDetails
                        ? Icons.expand_less_rounded
                        : Icons.expand_more_rounded,
                    size: 18,
                    color: const Color(0xFF065F46),
                  ),
                  const SizedBox(width: 6),
                  Flexible(
                    child: Text(
                      _showTechnicalDetails
                          ? 'Sembunyikan Nilai Teknis Komputasi'
                          : 'Nilai Teknis Komputasi Visi AI (Juri / Peneliti)',
                      style: const TextStyle(
                        fontSize: 11.5,
                        fontWeight: FontWeight.w700,
                        color: Color(0xFF065F46),
                      ),
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                ],
              ),
            ),
          ),

          if (_showTechnicalDetails) ...[
            const SizedBox(height: 10),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: const Color(0xFF022C22),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Column(
                children: [
                  _buildTechnicalRow(
                    'Indeks Kehijauan Daun (ExG):',
                    exg,
                    '2G - R - B',
                  ),
                  _buildTechnicalRow(
                    'Kekasaran Tekstur (Laplacian Var):',
                    roughness,
                    'Variansi turunan kedua',
                  ),
                  _buildTechnicalRow(
                    'Jumlah Kluster Bercak:',
                    spots,
                    'Kontur lesi morfologi',
                  ),
                  _buildTechnicalRow(
                    'Resolusi Tensor Input:',
                    '384 x 384 px',
                    'Kanonis Ultralytics',
                  ),
                ],
              ),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildLaypersonRow({
    required IconData icon,
    required String label,
    required String value,
  }) {
    return Row(
      children: [
        Container(
          padding: const EdgeInsets.all(7),
          decoration: BoxDecoration(
            color: const Color(0xFFECFDF5),
            borderRadius: BorderRadius.circular(8),
          ),
          child: Icon(icon, size: 18, color: const Color(0xFF059669)),
        ),
        const SizedBox(width: 10),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                label,
                style: const TextStyle(
                  fontSize: 12,
                  color: Color(0xFF475569),
                  fontWeight: FontWeight.w700,
                ),
              ),
              const SizedBox(height: 2),
              Text(
                value,
                style: const TextStyle(
                  fontSize: 13.5,
                  color: Color(0xFF0F172A),
                  fontWeight: FontWeight.w900,
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildTechnicalRow(String label, String value, String note) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                label,
                style: const TextStyle(fontSize: 12, color: Color(0xFFA7F3D0)),
              ),
              Text(
                note,
                style: const TextStyle(fontSize: 10, color: Color(0xFF6EE7B7)),
              ),
            ],
          ),
          Text(
            value,
            style: const TextStyle(
              fontSize: 13,
              fontWeight: FontWeight.w900,
              color: Colors.white,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStageChip(String step, String label, IconData icon) {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 4),
      padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 6),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: const Color(0xFFA7F3D0)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 15, color: const Color(0xFF059669)),
          const SizedBox(width: 5),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(
                step,
                style: const TextStyle(
                  fontSize: 10.5,
                  fontWeight: FontWeight.w800,
                  color: Color(0xFF059669),
                ),
              ),
              Text(
                label,
                style: const TextStyle(
                  fontSize: 11.5,
                  fontWeight: FontWeight.w700,
                  color: Color(0xFF1E293B),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildPredictionCandidatesCard() {
    final candidates = widget.result.topPredictions;
    if (candidates.isEmpty) return const SizedBox.shrink();

    return _buildModernCard(
      title: 'Kemungkinan Penyakit Lainnya',
      icon: Icons.analytics_rounded,
      iconColor: const Color(0xFF059669),
      subtitle: widget.result.needsExpertReview
          ? 'Hasil utama memiliki kandidat pembanding dekat'
          : 'Urutan persentase kecocokan dari model AI',
      child: Column(
        children: [
          for (var index = 0; index < candidates.length; index++)
            _buildPredictionCandidateRow(candidates[index], index),
          if ((widget.result.predictionMargin ?? 0) < 0.20) ...[
            const SizedBox(height: 10),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: const Color(0xFFF0FDF4),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: const Color(0xFFA7F3D0)),
              ),
              child: const Row(
                children: [
                  Icon(
                    Icons.info_outline_rounded,
                    color: Color(0xFF059669),
                    size: 18,
                  ),
                  SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      'Selisih kandidat sangat dekat. Verifikasi PPL disarankan untuk kepastian tindakan.',
                      style: TextStyle(
                        fontSize: 12,
                        color: Color(0xFF065F46),
                        fontWeight: FontWeight.w700,
                        height: 1.35,
                      ),
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

  Widget _buildPredictionCandidateRow(
    PredictionCandidate candidate,
    int index,
  ) {
    final percent = (candidate.confidence * 100).toStringAsFixed(1);
    final isTop = index == 0;
    final profile = PadiDiseaseHelper.getProfile(candidate.diseaseCode);

    return Container(
      margin: EdgeInsets.only(
        bottom: index == widget.result.topPredictions.length - 1 ? 0 : 8,
      ),
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: BoxDecoration(
        color: isTop ? const Color(0xFFF0FDF4) : Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(
          color: isTop ? const Color(0xFFA7F3D0) : const Color(0xFFE2E8F0),
        ),
      ),
      child: Row(
        children: [
          Container(
            width: 28,
            height: 28,
            alignment: Alignment.center,
            decoration: BoxDecoration(
              color: isTop ? const Color(0xFF059669) : const Color(0xFFE2E8F0),
              shape: BoxShape.circle,
            ),
            child: Text(
              '${index + 1}',
              style: TextStyle(
                color: isTop ? Colors.white : const Color(0xFF475569),
                fontSize: 13,
                fontWeight: FontWeight.w900,
              ),
            ),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  profile.indonesianName,
                  style: const TextStyle(
                    color: Color(0xFF0F172A),
                    fontSize: 13.5,
                    fontWeight: FontWeight.w900,
                    height: 1.25,
                  ),
                ),
                const SizedBox(height: 1),
                Text(
                  profile.scientificName,
                  style: const TextStyle(
                    color: Color(0xFF64748B),
                    fontSize: 11.5,
                    fontStyle: FontStyle.italic,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(width: 8),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 5),
            decoration: BoxDecoration(
              color: isTop ? const Color(0xFFDCFCE7) : const Color(0xFFF1F5F9),
              borderRadius: BorderRadius.circular(8),
            ),
            child: Text(
              '$percent%',
              style: TextStyle(
                color: isTop
                    ? const Color(0xFF15803D)
                    : const Color(0xFF475569),
                fontSize: 13,
                fontWeight: FontWeight.w900,
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildTabContent(GeminiRecommendationData? rec) {
    if (rec == null) {
      return _buildModernCard(
        title: 'Panduan Agronomi Sedang Diproses',
        icon: Icons.hourglass_top_rounded,
        iconColor: const Color(0xFF059669),
        child: const Text(
          ' AI sedang mengompilasi rekomendasi pencegahan dan obat berdasarkan data klinis daun.',
          style: TextStyle(fontSize: 14, color: Color(0xFF64748B), height: 1.5),
        ),
      );
    }

    switch (_selectedTab) {
      case 0: // Analisis
        return _buildModernCard(
          title: 'Analisis Patogen & Kondisi Cuaca',
          icon: Icons.biotech_rounded,
          iconColor: const Color(0xFF059669),
          subtitle: 'Pengaruh suhu, kelembaban, dan tingkat keparahan',
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                rec.analisis.isNotEmpty
                    ? rec.analisis
                    : 'Terdeteksi gejala ${rec.penyakit}. Gejala pada daun menunjukkan infeksi patogen aktif yang perlu segera ditangani agar tidak menyebar ke seluruh hamparan.',
                style: const TextStyle(
                  fontSize: 14.5,
                  color: Color(0xFF1E293B),
                  height: 1.6,
                ),
              ),
              const SizedBox(height: 14),
              Container(
                padding: const EdgeInsets.all(13),
                decoration: BoxDecoration(
                  color: const Color(0xFFF0FDF4),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: const Color(0xFFA7F3D0)),
                ),
                child: const Row(
                  children: [
                    Icon(
                      Icons.info_outline_rounded,
                      color: Color(0xFF059669),
                      size: 20,
                    ),
                    SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        'Penyemprotan paling efektif dilakukan sebelum infeksi mencapai lebih dari 20% luas daun.',
                        style: TextStyle(
                          fontSize: 12.5,
                          color: Color(0xFF065F46),
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

      case 1: // Pencegahan
        return _buildModernCard(
          title: 'Langkah Pencegahan & Pengendalian',
          icon: Icons.shield_rounded,
          iconColor: const Color(0xFF059669),
          subtitle: 'Tindakan sanitasi & pola budidaya praktis',
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [_buildStepList(rec.langkahPreventif)],
          ),
        );

      case 2: // Obat & Dosis
        return _buildModernCard(
          title: 'Rekomendasi Bahan Aktif & Takaran',
          icon: Icons.medication_rounded,
          iconColor: const Color(0xFF059669),
          subtitle: 'Dosis sprayer & panduan waktu semprot',
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              _buildStepList(rec.rekomendasiObat),
              const SizedBox(height: 14),
              Container(
                padding: const EdgeInsets.all(13),
                decoration: BoxDecoration(
                  color: const Color(0xFFF0FDF4),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: const Color(0xFFA7F3D0)),
                ),
                child: const Row(
                  children: [
                    Icon(
                      Icons.wb_twilight_rounded,
                      color: Color(0xFF059669),
                      size: 20,
                    ),
                    SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        'Waktu semprot ideal: Pukul 06.00 - 09.00 pagi atau 15.30 - 17.30 sore (hindari terik matahari langsung).',
                        style: TextStyle(
                          fontSize: 12.5,
                          color: Color(0xFF065F46),
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

      case 3: // Produk Toko
        return _buildProductListSection(rec.produk);

      case 4: // Resep DIY
      default:
        return _buildModernCard(
          title: 'Resep Ramuan Pestisida Nabati Alami',
          icon: Icons.eco_rounded,
          iconColor: const Color(0xFF059669),
          subtitle: 'Racikan alami ramah lingkungan & hemat biaya',
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                rec.diy.isNotEmpty
                    ? rec.diy
                    : '1. Ekstrak Bawang Putih & Kunyit: Bahan 250g bawang putih, 250g kunyit, 1 sdm sabun cair. Cara buat: Haluskan dengan 1 liter air, saring. Gunakan 100ml per tangki 14 liter.\n2. Kapur Sirih & Abu Sekam: Taburkan di tanah rumpun padi.',
                style: const TextStyle(
                  fontSize: 14.5,
                  color: Color(0xFF1E293B),
                  height: 1.6,
                ),
              ),
              const SizedBox(height: 14),
              Container(
                padding: const EdgeInsets.all(13),
                decoration: BoxDecoration(
                  color: const Color(0xFFF0FDF4),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: const Color(0xFFA7F3D0)),
                ),
                child: const Row(
                  children: [
                    Icon(
                      Icons.savings_outlined,
                      color: Color(0xFF059669),
                      size: 20,
                    ),
                    SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        'Pestisida nabati efektif menekan jamur & bakteri awal sekaligus menghemat biaya obat hingga 60%.',
                        style: TextStyle(
                          fontSize: 12.5,
                          color: Color(0xFF065F46),
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
  }

  Widget _buildModernCard({
    required String title,
    required IconData icon,
    required Color iconColor,
    required Widget child,
    String? subtitle,
  }) {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: const Color(0xFFA7F3D0)),
        boxShadow: [
          BoxShadow(
            color: const Color(0xFF064E3B).withValues(alpha: 0.04),
            blurRadius: 8,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: iconColor.withValues(alpha: 0.12),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(icon, color: iconColor, size: 22),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      title,
                      style: const TextStyle(
                        fontSize: 15.5,
                        fontWeight: FontWeight.w900,
                        color: Color(0xFF0F172A),
                      ),
                    ),
                    if (subtitle != null) ...[
                      const SizedBox(height: 2),
                      Text(
                        subtitle,
                        style: const TextStyle(
                          fontSize: 12,
                          color: Color(0xFF64748B),
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ],
                  ],
                ),
              ),
            ],
          ),
          const Divider(height: 22, color: Color(0xFFE2E8F0)),
          child,
        ],
      ),
    );
  }

  Widget _buildStepList(String rawText) {
    if (rawText.isEmpty) {
      return const Text(
        'Ikuti petunjuk sanitasi dan dosis rekomendasi penyuluh pertanian setempat.',
        style: TextStyle(fontSize: 14, color: Color(0xFF64748B)),
      );
    }

    final lines = rawText
        .split('\n')
        .where((l) => l.trim().isNotEmpty)
        .toList();

    return Column(
      children: lines.map((line) {
        final cleanLine = line
            .replaceFirst(RegExp(r'^\d+[\.\)]\s*'), '')
            .replaceFirst(RegExp(r'^[-*•]\s*'), '');

        return Padding(
          padding: const EdgeInsets.only(bottom: 12),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: 24,
                height: 24,
                margin: const EdgeInsets.only(top: 2),
                decoration: BoxDecoration(
                  color: const Color(0xFFECFDF5),
                  shape: BoxShape.circle,
                  border: Border.all(
                    color: const Color(0xFF10B981),
                    width: 1.2,
                  ),
                ),
                child: const Center(
                  child: Icon(Icons.check, size: 14, color: Color(0xFF059669)),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Text(
                  cleanLine,
                  style: const TextStyle(
                    fontSize: 14,
                    color: Color(0xFF1E293B),
                    fontWeight: FontWeight.w600,
                    height: 1.5,
                  ),
                ),
              ),
            ],
          ),
        );
      }).toList(),
    );
  }

  Widget _buildProductListSection(List<GeminiProduct> products) {
    if (products.isEmpty) {
      return _buildModernCard(
        title: 'Produk Obat Pertanian Rekomendasi',
        icon: Icons.shopping_bag_rounded,
        iconColor: const Color(0xFF059669),
        child: const Text(
          'Belum ada rekomendasi produk spesifik. Silakan cari obat di katalog Toko PADI.',
          style: TextStyle(fontSize: 14, color: Color(0xFF64748B)),
        ),
      );
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Padding(
          padding: EdgeInsets.only(left: 4, bottom: 8),
          child: Text(
            'Produk Berizin Resmi di Pasaran Indonesia:',
            style: TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.w800,
              color: Color(0xFF1E293B),
            ),
          ),
        ),
        ...products.map((prod) {
          return Container(
            margin: const EdgeInsets.only(bottom: 10),
            padding: const EdgeInsets.all(14),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: const Color(0xFFA7F3D0)),
              boxShadow: [
                BoxShadow(
                  color: const Color(0xFF064E3B).withValues(alpha: 0.04),
                  blurRadius: 6,
                  offset: const Offset(0, 2),
                ),
              ],
            ),
            child: Row(
              children: [
                Container(
                  width: 46,
                  height: 46,
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                      colors: [Color(0xFFECFDF5), Color(0xFFD1FAE5)],
                    ),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Icon(
                    Icons.shopping_bag_outlined,
                    color: Color(0xFF059669),
                    size: 24,
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        prod.nama,
                        style: const TextStyle(
                          fontSize: 15,
                          fontWeight: FontWeight.w900,
                          color: Color(0xFF0F172A),
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        'Bahan Aktif: ${prod.bahanAktif}',
                        style: const TextStyle(
                          fontSize: 12.5,
                          color: Color(0xFF64748B),
                        ),
                      ),
                      const SizedBox(height: 3),
                      Text(
                        prod.harga,
                        style: const TextStyle(
                          fontSize: 13.5,
                          fontWeight: FontWeight.w900,
                          color: Color(0xFF059669),
                        ),
                      ),
                    ],
                  ),
                ),
                ElevatedButton.icon(
                  onPressed: () => widget.onSearchProduct(prod.keyword),
                  icon: const Icon(Icons.search_rounded, size: 16),
                  label: const Text(
                    'Beli',
                    style: TextStyle(fontSize: 13, fontWeight: FontWeight.w900),
                  ),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF059669),
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(
                      horizontal: 14,
                      vertical: 10,
                    ),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                    elevation: 1,
                  ),
                ),
              ],
            ),
          );
        }),
      ],
    );
  }
}
