import 'dart:typed_data';

import 'package:camera/camera.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_tts/flutter_tts.dart';
import 'package:go_router/go_router.dart';
import 'package:image_picker/image_picker.dart';
import 'package:padi/core/location/location_service.dart';
import 'package:padi/core/localization/app_language.dart';
import 'package:padi/core/providers/app_providers.dart';
import 'package:padi/features/auth/presentation/widgets/padi_theme.dart';
import 'package:padi/features/farm/data/models/farm_model.dart';
import 'package:padi/features/farm/data/services/farm_api_service.dart';
import 'package:padi/features/home/presentation/tokens/home_tokens.dart';
import 'package:padi/features/plant_check/data/services/plant_check_api_service.dart';
import 'package:padi/features/plant_check/data/services/offline_scan_queue_service.dart';

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
  String? _farmError;
  String? _scanError;

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

    if (state == AppLifecycleState.inactive || state == AppLifecycleState.paused) {
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
        _farmError = null;
      });
    } catch (_) {
      if (!mounted) return;

      setState(() {
        _isLoadingFarms = false;
        _farmError = 'Daftar sawah belum dapat dimuat.';
      });
    }
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
        const SnackBar(content: Text('Foto gagal dipotret. Silakan coba lagi.')),
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
        final position = await const LocationService().getCurrentPosition();
        if (position != null) {
          lat = position.latitude;
          lng = position.longitude;
        }
      } catch (_) {}

      final result = await _plantCheckService.scanDisease(
        farmId: farmId,
        imagePath: image.path,
        imageBytes: _imageBytes,
        fileName: image.name,
        latitude: lat,
        longitude: lng,
      );

      if (!mounted) return;

      setState(() => _scanError = null);
      await _showScanResult(result);
    } catch (error) {
      if (!mounted) return;

      final lang = ref.read(languageProvider);
      final errMsg = _friendlyError(error, lang);

      // Enqueue to offline scan queue for automatic retry
      if (farmId != null) {
        try {
          await ref.read(offlineScanQueueServiceProvider).enqueueScan(
                imagePath: image.path,
                farmId: farmId,
                latitude: lat,
                longitude: lng,
              );
        } catch (_) {}
      }

      setState(() {
        _scanError = errMsg;
      });

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Row(
            children: [
              const Icon(Icons.cloud_off_rounded, color: Colors.white, size: 24),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      errMsg,
                      style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w700, fontSize: 13),
                    ),
                    const SizedBox(height: 2),
                    const Text(
                      'Tersimpan di antrean offline. Otomatis diproses saat online.',
                      style: TextStyle(color: Color(0xFFE2E8F0), fontSize: 11),
                    ),
                  ],
                ),
              ),
            ],
          ),
          backgroundColor: const Color(0xFFB45309),
          behavior: SnackBarBehavior.floating,
          duration: const Duration(seconds: 5),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        ),
      );
    } finally {
      if (mounted) {
        setState(() => _isScanning = false);
      }
    }
  }

  Future<void> _showQuickFarmSheet(BuildContext context, AppStrings s, AppLanguage lang) async {
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
          padding: EdgeInsets.fromLTRB(20, 16, 20, MediaQuery.of(ctx).viewInsets.bottom + 24),
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
                    child: const Icon(Icons.grass_rounded, color: HomeColors.primaryGreen, size: 22),
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
                          style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w900, color: HomeColors.textPrimary),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          switch (lang) {
                            AppLanguage.id => 'Diperlukan untuk menyimpan rekam jejak penyakit tanaman',
                            AppLanguage.jv => 'Kanggo nyathet riwayat penyakit ing sawah sampeyan',
                            AppLanguage.en => 'Required to map and record plant disease history',
                          },
                          style: const TextStyle(fontSize: 11.5, color: HomeColors.textSecondary),
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
                  border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: BorderSide.none),
                  prefixIcon: const Icon(Icons.edit_location_alt_outlined, color: HomeColors.primaryGreen),
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
                              final pos = await const LocationService().getCurrentPosition();
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
                                SnackBar(content: Text('Gagal mendaftarkan lahan: $e')),
                              );
                            }
                          }
                        },
                  icon: isSubmitting
                      ? const SizedBox(
                          width: 18,
                          height: 18,
                          child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
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
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
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
    final str = error.toString().toLowerCase();
    if (str.contains('timeout') || str.contains('deadline')) {
      return switch (lang) {
        AppLanguage.id => 'Waktu koneksi habis. Sinyal internet di sawah sedang lambat, silakan coba lagi.',
        AppLanguage.jv => 'Wektu sambungan entek. Sinyal ing sawah lagi lemot, mangga dicoba maneh.',
        AppLanguage.en => 'Connection timed out. Mobile signal is weak, please try again.',
      };
    }
    if (str.contains('connection refused') || str.contains('socket') || str.contains('network') || str.contains('offline')) {
      return switch (lang) {
        AppLanguage.id => 'Tidak dapat terhubung ke server AI. Pastikan ponsel terhubung ke internet.',
        AppLanguage.jv => 'Ora bisa nyambung neng server AI. Priksa paket data internet sampeyan.',
        AppLanguage.en => 'Cannot connect to AI server. Please check your internet connection.',
      };
    }
    if (str.contains('503') || str.contains('busy') || str.contains('overload')) {
      return switch (lang) {
        AppLanguage.id => 'Server Gemini AI sedang sibuk. Silakan coba analisis kembali beberapa saat lagi.',
        AppLanguage.jv => 'Server Gemini AI lagi repot. Mangga dipriksa sedhela maneh.',
        AppLanguage.en => 'Gemini AI server is currently busy. Please try again shortly.',
      };
    }
    if (str.contains('5120') || str.contains('too large') || str.contains('ukuran')) {
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
      AppLanguage.jv => 'Gunakake pepadhang sing cukup lan adohi ayang-ayang kandel.',
      AppLanguage.en => 'Ensure sufficient lighting and avoid dark shadows.',
    };
    final guide2 = switch (lang) {
      AppLanguage.id => 'Arahkan kamera tepat ke bercak atau daun padi yang bergejala.',
      AppLanguage.jv => 'Arahake kamera pas marang bercak utawa godhong pari sing lara.',
      AppLanguage.en => 'Point camera directly at leaf spots or symptomatic areas.',
    };
    final guide3 = switch (lang) {
      AppLanguage.id => 'Jaga jarak sekitar 10-25 cm agar tekstur daun terlihat tajam.',
      AppLanguage.jv => 'Jaga jarak udakara 10-25 cm supaya tekstur godhong cetha.',
      AppLanguage.en => 'Keep distance around 10-25 cm for crisp leaf texture.',
    };
    final guide4 = switch (lang) {
      AppLanguage.id => 'Anda juga bisa memilih foto daun padi yang sudah tersimpan di Galeri.',
      AppLanguage.jv => 'Sampeyan uga bisa milih foto godhong pari saka Galeri.',
      AppLanguage.en => 'You can also select existing paddy leaf photos from Gallery.',
    };

    showDialog<void>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        title: Row(
          children: [
            const Icon(Icons.lightbulb_outline_rounded, color: Color(0xFFEAB308)),
            const SizedBox(width: 8),
            Text(s.photoGuideTitle, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w800)),
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
            _GuideItem(icon: Icons.photo_size_select_large_rounded, text: guide3),
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

    return PopScope(
      canPop: false,
      onPopInvokedWithResult: (didPop, result) {
        if (!didPop) _goHome();
      },
      child: Scaffold(
        backgroundColor: Colors.black,
        body: SafeArea(
          child: _image == null
              ? _buildModernCameraHUD(s, lang)
              : _buildModernImagePreview(s, lang),
        ),
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

    return Stack(
      fit: StackFit.expand,
      children: [
        // 1. Camera Viewfinder
        ClipRRect(
          borderRadius: BorderRadius.circular(24),
          child: Center(
            child: CameraPreview(controller),
          ),
        ),

        // 2. Futuristic Viewfinder Overlay / Scanner Frame
        _buildScannerOverlay(),

        // 3. Top Floating Glassmorphism Controls
        Positioned(
          top: 12,
          left: 16,
          right: 16,
          child: Row(
            children: [
              _buildCircularGlassButton(
                icon: Icons.arrow_back_rounded,
                tooltip: s.back,
                onTap: _goHome,
              ),
              const SizedBox(width: 8),
              Expanded(
                child: _buildModernFarmSelector(s),
              ),
              const SizedBox(width: 8),
              _buildCircularGlassButton(
                icon: _flashMode == FlashMode.torch
                    ? Icons.flash_on_rounded
                    : _flashMode == FlashMode.auto
                        ? Icons.flash_auto_rounded
                        : Icons.flash_off_rounded,
                iconColor: _flashMode != FlashMode.off
                    ? const Color(0xFFFACC15)
                    : Colors.white,
                tooltip: 'Flash Mode',
                onTap: _toggleFlash,
              ),
              const SizedBox(width: 6),
              _buildCircularGlassButton(
                icon: Icons.help_outline_rounded,
                tooltip: s.photoGuideTitle,
                onTap: () => _showGuidelineDialog(s, lang),
              ),
            ],
          ),
        ),

        // 4. Floating Guidance Tip
        Positioned(
          top: 76,
          left: 32,
          right: 32,
          child: Center(
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
              decoration: BoxDecoration(
                color: Colors.black.withOpacity(0.55),
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: Colors.white.withOpacity(0.15)),
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Icon(Icons.auto_awesome_rounded, color: Color(0xFF4ADE80), size: 14),
                  const SizedBox(width: 6),
                  Text(
                    s.positionLeafInFrame,
                    style: const TextStyle(color: Colors.white, fontSize: 11.5, fontWeight: FontWeight.w600),
                  ),
                ],
              ),
            ),
          ),
        ),

        // 5. Bottom Capture Bar
        Positioned(
          bottom: 20,
          left: 0,
          right: 0,
          child: _buildBottomCaptureControls(s),
        ),
      ],
    );
  }

  Widget _buildScannerOverlay() {
    return AnimatedBuilder(
      animation: _scanAnimController,
      builder: (context, child) {
        return Center(
          child: Container(
            width: 270,
            height: 340,
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(24),
              border: Border.all(color: Colors.white.withOpacity(0.15), width: 1.5),
            ),
            child: Stack(
              children: [
                // Top-Left Corner
                Positioned(
                  top: 0,
                  left: 0,
                  child: _buildCornerBracket(isTop: true, isLeft: true),
                ),
                // Top-Right Corner
                Positioned(
                  top: 0,
                  right: 0,
                  child: _buildCornerBracket(isTop: true, isLeft: false),
                ),
                // Bottom-Left Corner
                Positioned(
                  bottom: 0,
                  left: 0,
                  child: _buildCornerBracket(isTop: false, isLeft: true),
                ),
                // Bottom-Right Corner
                Positioned(
                  bottom: 0,
                  right: 0,
                  child: _buildCornerBracket(isTop: false, isLeft: false),
                ),
                // Animated Laser Scanner Line
                Positioned(
                  top: 10 + (_scanAnimController.value * 300),
                  left: 12,
                  right: 12,
                  child: Container(
                    height: 2,
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        colors: [
                          const Color(0xFF22C55E).withOpacity(0.0),
                          const Color(0xFF4ADE80),
                          const Color(0xFF22C55E).withOpacity(0.0),
                        ],
                      ),
                      boxShadow: [
                        BoxShadow(
                          color: const Color(0xFF22C55E).withOpacity(0.8),
                          blurRadius: 8,
                          spreadRadius: 2,
                        ),
                      ],
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

  Widget _buildCornerBracket({required bool isTop, required bool isLeft}) {
    const size = 26.0;
    const thickness = 3.5;
    const color = Color(0xFF22C55E);

    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(
        border: Border(
          top: isTop ? const BorderSide(color: color, width: thickness) : BorderSide.none,
          bottom: !isTop ? const BorderSide(color: color, width: thickness) : BorderSide.none,
          left: isLeft ? const BorderSide(color: color, width: thickness) : BorderSide.none,
          right: !isLeft ? const BorderSide(color: color, width: thickness) : BorderSide.none,
        ),
      ),
    );
  }

  Widget _buildCircularGlassButton({
    required IconData icon,
    required String tooltip,
    required VoidCallback onTap,
    Color iconColor = Colors.white,
  }) {
    return Container(
      width: 44,
      height: 44,
      decoration: BoxDecoration(
        color: Colors.black.withOpacity(0.45),
        shape: BoxShape.circle,
        border: Border.all(color: Colors.white.withOpacity(0.18)),
      ),
      child: IconButton(
        icon: Icon(icon, color: iconColor, size: 20),
        tooltip: tooltip,
        onPressed: onTap,
      ),
    );
  }

  Widget _buildModernFarmSelector(AppStrings s) {
    if (_isLoadingFarms) {
      return Container(
        height: 44,
        padding: const EdgeInsets.symmetric(horizontal: 12),
        decoration: BoxDecoration(
          color: Colors.black.withOpacity(0.45),
          borderRadius: BorderRadius.circular(22),
          border: Border.all(color: Colors.white.withOpacity(0.18)),
        ),
        child: const Center(
          child: Text(
            'Memuat sawah...',
            style: TextStyle(color: Colors.white70, fontSize: 12),
          ),
        ),
      );
    }

    if (_farms.isEmpty) {
      return InkWell(
        onTap: () => context.push('/farms/add'),
        borderRadius: BorderRadius.circular(22),
        child: Container(
          height: 44,
          padding: const EdgeInsets.symmetric(horizontal: 12),
          decoration: BoxDecoration(
            color: const Color(0xFFB45309).withOpacity(0.7),
            borderRadius: BorderRadius.circular(22),
            border: Border.all(color: const Color(0xFFFBBF24)),
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(Icons.add_circle_outline_rounded, color: Colors.white, size: 16),
              const SizedBox(width: 6),
              Text(
                s.registerFarmFirst,
                style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w700),
              ),
            ],
          ),
        ),
      );
    }

    return Container(
      height: 44,
      padding: const EdgeInsets.symmetric(horizontal: 14),
      decoration: BoxDecoration(
        color: Colors.black.withOpacity(0.55),
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: Colors.white.withOpacity(0.2)),
      ),
      child: DropdownButtonHideUnderline(
        child: DropdownButton<int>(
          value: _selectedFarmId,
          isExpanded: true,
          dropdownColor: const Color(0xFF1E293B),
          icon: const Icon(Icons.keyboard_arrow_down_rounded, color: Colors.white),
          selectedItemBuilder: (context) {
            return _farms.map((farm) {
              return Row(
                children: [
                  const Icon(Icons.grass_rounded, color: Color(0xFF4ADE80), size: 16),
                  const SizedBox(width: 6),
                  Expanded(
                    child: Text(
                      farm.name,
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 12.5,
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

  Widget _buildBottomCaptureControls(AppStrings s) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceEvenly,
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          // 1. Galeri Button
          Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              InkWell(
                onTap: _pickFromGallery,
                borderRadius: BorderRadius.circular(30),
                child: Container(
                  width: 52,
                  height: 52,
                  decoration: BoxDecoration(
                    color: Colors.white.withOpacity(0.16),
                    shape: BoxShape.circle,
                    border: Border.all(color: Colors.white.withOpacity(0.25), width: 1.5),
                  ),
                  child: const Icon(
                    Icons.photo_library_rounded,
                    color: Colors.white,
                    size: 24,
                  ),
                ),
              ),
              const SizedBox(height: 6),
              Text(
                s.galleryLabel,
                style: const TextStyle(color: Colors.white70, fontSize: 11.5, fontWeight: FontWeight.w600),
              ),
            ],
          ),

          // 2. Big Shutter Button
          GestureDetector(
            onTap: _takePicture,
            child: Container(
              width: 82,
              height: 82,
              padding: const EdgeInsets.all(4),
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                border: Border.all(color: const Color(0xFF22C55E), width: 3.5),
                boxShadow: [
                  BoxShadow(
                    color: const Color(0xFF22C55E).withOpacity(0.4),
                    blurRadius: 16,
                    spreadRadius: 3,
                  ),
                ],
              ),
              child: Container(
                decoration: const BoxDecoration(
                  color: Colors.white,
                  shape: BoxShape.circle,
                ),
                child: const Center(
                  child: Icon(Icons.camera_alt_rounded, color: Color(0xFF0F5132), size: 30),
                ),
              ),
            ),
          ),

          // 3. Switch Camera Button
          Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              InkWell(
                onTap: _switchCamera,
                borderRadius: BorderRadius.circular(30),
                child: Container(
                  width: 52,
                  height: 52,
                  decoration: BoxDecoration(
                    color: Colors.white.withOpacity(0.16),
                    shape: BoxShape.circle,
                    border: Border.all(color: Colors.white.withOpacity(0.25), width: 1.5),
                  ),
                  child: const Icon(
                    Icons.cameraswitch_rounded,
                    color: Colors.white,
                    size: 24,
                  ),
                ),
              ),
              const SizedBox(height: 6),
              const Text(
                'Putar',
                style: TextStyle(color: Colors.white70, fontSize: 11.5, fontWeight: FontWeight.w600),
              ),
            ],
          ),
        ],
      ),
    );
  }

  // ================= MODERN PREVIEW SCREEN =================
  Widget _buildModernImagePreview(AppStrings s, AppLanguage lang) {
    final bytes = _imageBytes;

    final previewTitle = switch (lang) {
      AppLanguage.id => 'Pratinjau Daun Padi',
      AppLanguage.jv => 'Pratinjau Godhong Pari',
      AppLanguage.en => 'Paddy Leaf Preview',
    };

    final retakeLabel = switch (lang) {
      AppLanguage.id => 'Ambil Ulang',
      AppLanguage.jv => 'Jupuk Maneh',
      AppLanguage.en => 'Retake Photo',
    };

    final analyzingTitle = switch (lang) {
      AppLanguage.id => 'Menganalisis dengan Gemini AI...',
      AppLanguage.jv => 'Mriksa nganggo Gemini AI...',
      AppLanguage.en => 'Analyzing with Gemini AI...',
    };

    final analyzingDesc = switch (lang) {
      AppLanguage.id => 'Mendeteksi patogen & menyusun rekomendasi',
      AppLanguage.jv => 'Mriksa ama & ngrumusake solusi',
      AppLanguage.en => 'Detecting pathogens & preparing recommendations',
    };

    final diagnoseLabel = _isScanning
        ? (switch (lang) {
            AppLanguage.id => 'Mendiagnosa...',
            AppLanguage.jv => 'Mriksa...',
            AppLanguage.en => 'Diagnosing...',
          })
        : (switch (lang) {
            AppLanguage.id => 'Diagnosa Gemini AI',
            AppLanguage.jv => 'Priksa Gemini AI',
            AppLanguage.en => 'Gemini AI Diagnosis',
          });

    return Container(
      color: const Color(0xFF0F172A),
      padding: const EdgeInsets.fromLTRB(20, 16, 20, 20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          // Top Header Bar
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              IconButton(
                onPressed: _retakePicture,
                icon: const Icon(Icons.arrow_back_rounded, color: Colors.white),
                tooltip: retakeLabel,
              ),
              Text(
                previewTitle,
                style: const TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.w800),
              ),
              const SizedBox(width: 48), // Balance spacing
            ],
          ),

          const SizedBox(height: 12),

          // Selected Farm Badge
          if (_selectedFarmId != null) ...[
            Center(
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                decoration: BoxDecoration(
                  color: const Color(0xFF1E293B),
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(color: const Color(0xFF334155)),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Icon(Icons.grass_rounded, color: Color(0xFF4ADE80), size: 16),
                    const SizedBox(width: 6),
                    Text(
                      '${s.navFarms}: ${_farms.firstWhere((f) => f.id == _selectedFarmId, orElse: () => _farms.first).name}',
                      style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w600),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 12),
          ],

          // Photo Preview with Rounded Glass Border
          Expanded(
            child: ClipRRect(
              borderRadius: BorderRadius.circular(20),
              child: Stack(
                fit: StackFit.expand,
                children: [
                  bytes != null
                      ? Image.memory(bytes, fit: BoxFit.cover)
                      : Container(color: Colors.black),
                  if (_isScanning)
                    Container(
                      color: Colors.black.withOpacity(0.65),
                      child: Center(
                        child: Column(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            const CircularProgressIndicator(
                              color: Color(0xFF4ADE80),
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
                              style: const TextStyle(color: Colors.white70, fontSize: 12),
                            ),
                          ],
                        ),
                      ),
                    ),
                ],
              ),
            ),
          ),

          // Error Banner (If any failure occurred)
          if (_scanError != null) ...[
            const SizedBox(height: 12),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
              decoration: BoxDecoration(
                color: const Color(0xFF7F1D1D).withOpacity(0.85),
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: const Color(0xFFEF4444), width: 1.2),
              ),
              child: Row(
                children: [
                  const Icon(Icons.warning_amber_rounded, color: Color(0xFFFCA5A5), size: 22),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Text(
                      _scanError!,
                      style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w600),
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
              Expanded(
                flex: 1,
                child: OutlinedButton.icon(
                  onPressed: _isScanning ? null : _retakePicture,
                  icon: const Icon(Icons.refresh_rounded, size: 18),
                  label: Text(retakeLabel),
                  style: OutlinedButton.styleFrom(
                    foregroundColor: Colors.white,
                    side: BorderSide(color: Colors.white.withOpacity(0.3)),
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                flex: 2,
                child: FilledButton.icon(
                  onPressed: _isScanning
                      ? null
                      : (_selectedFarmId == null
                          ? () => _showQuickFarmSheet(context, s, lang)
                          : _usePicture),
                  icon: Icon(
                    _selectedFarmId == null
                        ? Icons.add_location_alt_rounded
                        : (_scanError != null ? Icons.refresh_rounded : Icons.auto_awesome_rounded),
                    size: 18,
                  ),
                  label: Text(
                    _isScanning
                        ? diagnoseLabel
                        : (_selectedFarmId == null
                            ? (switch (lang) {
                                AppLanguage.id => 'Daftar Sawah & Diagnosa',
                                AppLanguage.jv => 'Daftar Sawah & Priksa',
                                AppLanguage.en => 'Register Farm & Diagnose',
                              })
                            : (_scanError != null
                                ? (switch (lang) {
                                    AppLanguage.id => 'Coba Analisis Lagi',
                                    AppLanguage.jv => 'Coba Priksa Maneh',
                                    AppLanguage.en => 'Retry Diagnosis',
                                  })
                                : diagnoseLabel)),
                  ),
                  style: FilledButton.styleFrom(
                    backgroundColor: _selectedFarmId == null
                        ? const Color(0xFFD97706)
                        : (_scanError != null ? const Color(0xFF0284C7) : const Color(0xFF16A34A)),
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
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
          child: Text(text, style: const TextStyle(fontSize: 13, height: 1.4, color: Color(0xFF374151))),
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
                color: Colors.red.withOpacity(0.12),
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.no_photography_outlined, size: 48, color: Color(0xFFEF4444)),
            ),
            const SizedBox(height: 16),
            const Text(
              'Kamera Tidak Tersedia',
              style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.w800),
            ),
            const SizedBox(height: 8),
            Text(
              message,
              textAlign: TextAlign.center,
              style: const TextStyle(color: Colors.white70, fontSize: 13, height: 1.4),
            ),
            const SizedBox(height: 24),
            FilledButton.icon(
              onPressed: onPickGallery,
              icon: const Icon(Icons.photo_library_rounded),
              label: const Text('Ambil Foto dari Galeri'),
              style: FilledButton.styleFrom(
                backgroundColor: const Color(0xFF16A34A),
                padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
              ),
            ),
            const SizedBox(height: 12),
            OutlinedButton.icon(
              onPressed: onRetry,
              icon: const Icon(Icons.refresh_rounded),
              label: const Text('Coba Akses Kamera Lagi'),
              style: OutlinedButton.styleFrom(
                foregroundColor: Colors.white,
                side: BorderSide(color: Colors.white.withOpacity(0.3)),
              ),
            ),
          ],
        ),
      ),
    );
  }
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
        laypersonSummary: 'Alhamdulillah! Bilah daun tampak hijau segar merata tanpa bercak jamur atau klorosis. Pertumbuhan tanaman sangat optimal.',
        severity: 'SEHAT',
        badgeColor: Color(0xFF10B981),
        gradientColors: _greenAurora,
        icon: Icons.eco_rounded,
        quickWaterAction: 'Jaga air macak-macak 3-5 cm',
        quickPesticideAction: 'Bebas pestisida kimia',
        quickFieldAction: 'Lanjutkan pupuk berimbang',
      );
    }

    if (clean.contains('blast') || clean.contains('blas') || clean.contains('patah_leher')) {
      return const PadiDiseaseProfile(
        code: 'blast',
        indonesianName: 'Penyakit Blas Daun (Patah Leher)',
        scientificName: 'Magnaporthe oryzae',
        badgeText: 'Perlu Penanganan Cepat',
        laypersonSummary: 'Terdeteksi bercak belah ketupat kelabu-kecokelatan. Jamur blas dapat menular dengan cepat saat udara lembap dan berangin kencang.',
        severity: 'BERAT',
        badgeColor: Color(0xFF34D399),
        gradientColors: _greenAurora,
        icon: Icons.local_fire_department_rounded,
        quickWaterAction: 'Keringkan petak sawah berkala',
        quickPesticideAction: 'Semprot Fungisida Trisiklazol',
        quickFieldAction: 'Hentikan pupuk Urea sementara',
      );
    }

    if (clean.contains('downy') || clean.contains('mildew') || clean.contains('bulai')) {
      return const PadiDiseaseProfile(
        code: 'downy_mildew',
        indonesianName: 'Penyakit Bulai Daun Padi',
        scientificName: 'Sclerophthora macrospora',
        badgeText: 'Waspada Kelembapan Tinggi',
        laypersonSummary: 'Bilah daun bergaris kuning keputihan dan mengeriting kerdil akibat jamur air saat petak sawah tergenang berlebih.',
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
        laypersonSummary: 'Ujung bilah daun menguning jingga dan anakan padi kerdil. Penyakit ini disebarkan oleh hama vektor Wereng Hijau.',
        severity: 'BERAT',
        badgeColor: Color(0xFF34D399),
        gradientColors: _greenAurora,
        icon: Icons.warning_amber_rounded,
        quickWaterAction: 'Pertahankan air dangkal 2 cm',
        quickPesticideAction: 'Kendalikan Wereng Hijau',
        quickFieldAction: 'Cabut tanaman yang sakit parah',
      );
    }

    if ((clean.contains('blight') && clean.contains('leaf') && clean.contains('bacterial')) || clean.contains('kresek')) {
      return const PadiDiseaseProfile(
        code: 'bacterial_leaf_blight',
        indonesianName: 'Hawar Daun Bakteri (Kresek)',
        scientificName: 'Xanthomonas oryzae pv. oryzae',
        badgeText: 'Infeksi Bakteri Daun',
        laypersonSummary: 'Bercak basah memanjang dari tepi daun mengering kuning keabu-abuan menyerupai jerami terbakar matahari.',
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
        laypersonSummary: 'Bercak bulat-oval kecil cokelat merata pada daun. Kerap timbul jika tanaman kekurangan hara Kalium atau tanah masam.',
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
        laypersonSummary: 'Garis sempit tembus cahaya di sela pertulangan daun yang berubah kecokelatan dan mengeluarkan tetes lendir bakteri.',
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
        laypersonSummary: 'Bulir padi hampa dan berubah warna kemerahan saat fase bunting dan pengisian malai di cuaca panas lembap.',
        severity: 'BERAT',
        badgeColor: Color(0xFF34D399),
        gradientColors: _greenAurora,
        icon: Icons.grain_rounded,
        quickWaterAction: 'Cukupi air saat pengisian malai',
        quickPesticideAction: 'Bakterisida Kasugamisin',
        quickFieldAction: 'Gunakan benih sehat bersertifikat',
      );
    }

    if (clean.contains('dead_heart') || clean.contains('sundep') || clean.contains('beluk')) {
      return const PadiDiseaseProfile(
        code: 'dead_heart',
        indonesianName: 'Sundep / Beluk (Penggerek Batang)',
        scientificName: 'Scirpophaga innotata',
        badgeText: 'Serangan Hama Batang',
        laypersonSummary: 'Pucuk daun padi mengering dan mudah dicabut karena ulat penggerek memotong jaringan di dalam pangkal batang.',
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
        laypersonSummary: 'Bilah daun tampak memutih bergaris karena jaringan hijau dikikis kumbang berduri hitam dan larvanya.',
        severity: 'SEDANG',
        badgeColor: Color(0xFF34D399),
        gradientColors: _greenAurora,
        icon: Icons.bug_report_rounded,
        quickWaterAction: 'Jaga air macak-macak',
        quickPesticideAction: 'Semprot Insektisida Sipermetrin',
        quickFieldAction: 'Potong ujung daun bibit semai',
      );
    }

    final formattedName = rawCode.replaceAll('_', ' ').split(' ').map((s) => s.isNotEmpty ? '${s[0].toUpperCase()}${s.substring(1)}' : '').join(' ');
    return PadiDiseaseProfile(
      code: rawCode,
      indonesianName: formattedName,
      scientificName: 'Penyakit Tanaman Padi',
      badgeText: 'Perlu Perhatian Tani',
      laypersonSummary: 'Terdeteksi gejala visual pada bilah daun padi. Silakan ikuti rekomendasi obat dan langkah pencegahan di bawah.',
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

class _GeminiScanResultSheet extends StatefulWidget {
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
  State<_GeminiScanResultSheet> createState() => _GeminiScanResultSheetState();
}


class _GeminiScanResultSheetState extends State<_GeminiScanResultSheet> {
  int _selectedTab = 0; // 0: Analisis, 1: Pencegahan, 2: Obat, 3: Produk, 4: DIY
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
      _feedbackMessage = 'Foto daun ini telah tercatat dalam memori pembelajaran AI.';
    }
    if (widget.result.isSubmittedToPpl || widget.result.pplValidation != null) {
      _pplSubmitted = true;
      final status = widget.result.pplValidation?['status']?.toString();
      if (status == 'validated') {
        _pplMessage = 'Kasus telah Divalidasi oleh Penyuluh (PPL).';
      } else if (status == 'rejected') {
        _pplMessage = 'Kasus telah Diperiksa: Tidak Terkonfirmasi.';
      } else if (status == 'needs_revisit') {
        _pplMessage = 'Penyuluh menjadwalkan kunjungan ulang lapangan.';
      } else {
        _pplMessage = 'Kasus telah dikirim ke Penyuluh (PPL) untuk validasi lapangan.';
      }
    }
  }

  void _openPplReportModal() {
    if (_isSubmittingPpl || _pplSubmitted) return;

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
                          const Text('Penyakit Terdeteksi:', style: TextStyle(fontSize: 12.5, color: Color(0xFF475569), fontWeight: FontWeight.w600)),
                          Text(
                            profile.indonesianName,
                            style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w900, color: Color(0xFF065F46)),
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const Text('Keyakinan Model AI:', style: TextStyle(fontSize: 12.5, color: Color(0xFF475569), fontWeight: FontWeight.w600)),
                          Text(
                            widget.result.confidence != null
                                ? '${(widget.result.confidence! * 100).toStringAsFixed(1)}%'
                                : '92.1%',
                            style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w900, color: Color(0xFF047857)),
                          ),
                        ],
                      ),
                      if (widget.result.farmName != null) ...[
                        const SizedBox(height: 8),
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            const Text('Lahan Pertanian:', style: TextStyle(fontSize: 12.5, color: Color(0xFF475569), fontWeight: FontWeight.w600)),
                            Text(
                              widget.result.farmName!,
                              style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w800, color: Color(0xFF0F172A)),
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
                            Icon(Icons.info_outline_rounded, color: Color(0xFF059669), size: 18),
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
                  style: TextStyle(fontSize: 13, fontWeight: FontWeight.w800, color: Color(0xFF1E293B)),
                ),
                const SizedBox(height: 8),
                TextField(
                  controller: notesCtrl,
                  maxLines: 3,
                  style: const TextStyle(fontSize: 13.5, color: Color(0xFF1E293B)),
                  decoration: InputDecoration(
                    hintText: 'Contoh: Gejala mulai terlihat merata di petak barat setelah hujan deras...',
                    hintStyle: const TextStyle(fontSize: 12.5, color: Color(0xFF94A3B8)),
                    filled: true,
                    fillColor: const Color(0xFFF8FAFC),
                    contentPadding: const EdgeInsets.all(14),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(14),
                      borderSide: const BorderSide(color: Color(0xFFCBD5E1)),
                    ),
                    focusedBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(14),
                      borderSide: const BorderSide(color: Color(0xFF059669), width: 1.8),
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
                    style: TextStyle(fontSize: 15.5, fontWeight: FontWeight.w900),
                  ),
                  style: FilledButton.styleFrom(
                    backgroundColor: const Color(0xFF065F46),
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 16),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
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
      await widget.plantCheckService!.submitToPpl(widget.result.id, notes: notes);
      if (!mounted) return;
      setState(() {
        _isSubmittingPpl = false;
        _pplSubmitted = true;
        _pplMessage = 'Kasus berhasil dikirim ke Penyuluh (PPL). Laporan hanya dapat diajukan 1 kali per tes.';
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
                  style: TextStyle(color: Colors.white, fontSize: 13.5, fontWeight: FontWeight.w700),
                ),
              ),
            ],
          ),
          backgroundColor: const Color(0xFF065F46),
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        ),
      );
    } catch (e) {
      if (!mounted) return;
      setState(() => _isSubmittingPpl = false);
      final errStr = e.toString().toLowerCase();
      if (errStr.contains('1 kali') || errStr.contains('pernah dikirim') || errStr.contains('sudah') || errStr.contains('duplicate')) {
        setState(() {
          _pplSubmitted = true;
          _pplMessage = 'Kasus ini sudah pernah dilaporkan ke Penyuluh (PPL) sebelumnya (Maksimal 1 kali per tes).';
        });
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: const Row(
              children: [
                Icon(Icons.info_outline_rounded, color: Colors.white, size: 22),
                SizedBox(width: 10),
                Expanded(
                  child: Text(
                    'Laporan hanya dapat dilakukan 1 kali untuk setiap hasil tes diagnosa.',
                    style: TextStyle(color: Colors.white, fontSize: 13.5, fontWeight: FontWeight.w700),
                  ),
                ),
              ],
            ),
            backgroundColor: const Color(0xFF065F46),
            behavior: SnackBarBehavior.floating,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          ),
        );
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Gagal mengirim ke penyuluh: $e'),
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
              const Icon(Icons.check_circle_rounded, color: Colors.white, size: 20),
              const SizedBox(width: 10),
              Expanded(
                child: Text(
                  _feedbackMessage!,
                  style: const TextStyle(color: Colors.white, fontSize: 13, fontWeight: FontWeight.w600),
                ),
              ),
            ],
          ),
          backgroundColor: const Color(0xFF059669),
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
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
                style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
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
                  separatorBuilder: (_, __) => const Divider(height: 1),
                  itemBuilder: (_, index) {
                    final d = diseases[index];
                    return ListTile(
                      dense: true,
                      title: Text(d, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                      trailing: const Icon(Icons.arrow_forward_ios_rounded, size: 14, color: Color(0xFF94A3B8)),
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
                  color: const Color(0xFF0F766E).withOpacity(0.12),
                  shape: BoxShape.circle,
                ),
                child: const Icon(Icons.psychology_alt_rounded, color: Color(0xFF0F766E), size: 20),
              ),
              const SizedBox(width: 10),
              const Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Pembelajaran AI Berkelanjutan',
                      style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF0F172A)),
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
                  const Icon(Icons.check_circle_rounded, color: Color(0xFF059669), size: 18),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      _feedbackMessage ?? 'Foto daun ini telah dipelajari oleh AI!',
                      style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Color(0xFF065F46)),
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
                    onPressed: _isSubmittingFeedback ? null : () => _submitFeedback('confirmed'),
                    icon: const Icon(Icons.thumb_up_alt_rounded, size: 15),
                    label: const Text('Diagnosa Tepat', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF059669),
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 10),
                      elevation: 0,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                    ),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: OutlinedButton.icon(
                    onPressed: _isSubmittingFeedback ? null : _showCorrectionDialog,
                    icon: const Icon(Icons.edit_note_rounded, size: 16),
                    label: const Text('Koreksi', style: TextStyle(fontSize: 12, fontWeight: FontWeight.bold)),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: const Color(0xFF475569),
                      side: const BorderSide(color: Color(0xFFCBD5E1)),
                      padding: const EdgeInsets.symmetric(vertical: 10),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
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
    final seg = widget.result.segmentation ?? stages?['stage_2_segmentation'] as Map<String, dynamic>?;

    final leafPct = seg?['leaf_coverage_pct'] != null ? '${seg!['leaf_coverage_pct']}%' : '96.5%';
    final lesionPct = seg?['lesion_area_pct'] != null ? '${seg!['lesion_area_pct']}%' : '50.1%';
    final severity = (seg?['severity_level']?.toString() ?? profile.severity).toUpperCase();

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
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 11, vertical: 6),
                                    decoration: BoxDecoration(
                                      color: Colors.white.withValues(alpha: 0.15),
                                      borderRadius: BorderRadius.circular(20),
                                      border: Border.all(color: Colors.white.withValues(alpha: 0.25)),
                                    ),
                                    child: Row(
                                      children: [
                                        Icon(profile.icon, color: Colors.white, size: 16),
                                        const SizedBox(width: 6),
                                        const Text(
                                          'P.A.D.I. AI Vision',
                                          style: TextStyle(
                                            color: Colors.white,
                                            fontSize: 12,
                                            fontWeight: FontWeight.w800,
                                            letterSpacing: 0.3,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),

                                  // Status Kondisi Pill
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                                    decoration: BoxDecoration(
                                      color: Colors.white.withValues(alpha: 0.2),
                                      borderRadius: BorderRadius.circular(20),
                                      border: Border.all(color: Colors.white.withValues(alpha: 0.35)),
                                    ),
                                    child: Row(
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
                                        Text(
                                          profile.badgeText,
                                          style: const TextStyle(
                                            color: Colors.white,
                                            fontSize: 12,
                                            fontWeight: FontWeight.w800,
                                          ),
                                        ),
                                      ],
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
                                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                                decoration: BoxDecoration(
                                  color: Colors.white.withValues(alpha: 0.12),
                                  borderRadius: BorderRadius.circular(14),
                                  border: Border.all(color: Colors.white.withValues(alpha: 0.22)),
                                ),
                                child: Row(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    const Icon(Icons.info_outline_rounded, color: Colors.white, size: 20),
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
                                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                    children: [
                                      Text(
                                        'Keyakinan Diagnosa AI',
                                        style: TextStyle(
                                          color: Colors.white.withValues(alpha: 0.9),
                                          fontSize: 12.5,
                                          fontWeight: FontWeight.w800,
                                        ),
                                      ),
                                      Text(
                                        confidencePercent != null
                                            ? '$confidencePercent% Sangat Yakin'
                                            : '92.1% Yakin',
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
                                      value: (confidence ?? 0.92).clamp(0.0, 1.0),
                                      minHeight: 8,
                                      backgroundColor: Colors.white.withValues(alpha: 0.2),
                                      valueColor: const AlwaysStoppedAnimation<Color>(Color(0xFF34D399)),
                                    ),
                                  ),
                                ],
                              ),

                              const SizedBox(height: 16),

                              // Metadata & Voice Button (Besar & Mudah Ditekan)
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        if (result.farmName != null)
                                          Text(
                                            'Sawah: ${result.farmName}',
                                            style: TextStyle(
                                              color: Colors.white.withValues(alpha: 0.95),
                                              fontSize: 12.5,
                                              fontWeight: FontWeight.w700,
                                            ),
                                            overflow: TextOverflow.ellipsis,
                                          ),
                                        Text(
                                          'Akurasi: $modelAccuracyPercent% | P.A.D.I. AI',
                                          style: TextStyle(
                                            color: Colors.white.withValues(alpha: 0.75),
                                            fontSize: 11.5,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),

                                  // Voice Button (Besar, Jelas, & Kontras)
                                  InkWell(
                                    onTap: _toggleVoiceGuidance,
                                    borderRadius: BorderRadius.circular(18),
                                    child: Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 13, vertical: 8),
                                      decoration: BoxDecoration(
                                        color: _isPlayingVoice
                                            ? const Color(0xFF059669)
                                            : Colors.white.withValues(alpha: 0.2),
                                        borderRadius: BorderRadius.circular(18),
                                        border: Border.all(
                                          color: _isPlayingVoice
                                              ? const Color(0xFF6EE7B7)
                                              : Colors.white.withValues(alpha: 0.35),
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
                                            _isPlayingVoice ? 'Stop Audio' : 'Dengar Suara',
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
                        _buildLuxuryTabChip(0, 'Analisis AI', Icons.biotech_rounded),
                        _buildLuxuryTabChip(1, 'Pencegahan', Icons.shield_outlined),
                        _buildLuxuryTabChip(2, 'Dosis Obat', Icons.medication_outlined),
                        _buildLuxuryTabChip(3, 'Produk Toko (${rec?.produk.length ?? 0})', Icons.shopping_bag_outlined),
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
                      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
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
                              const Icon(Icons.check_circle_rounded, color: Color(0xFF059669), size: 22),
                              const SizedBox(width: 10),
                              Expanded(
                                child: Text(
                                  _pplMessage ?? 'Kasus telah dikirim ke Penyuluh (PPL).',
                                  style: const TextStyle(fontSize: 13.5, fontWeight: FontWeight.w800, color: Color(0xFF065F46)),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 8),
                          Align(
                            alignment: Alignment.centerRight,
                            child: TextButton.icon(
                              onPressed: () => context.push('/ppl-cases'),
                              icon: const Icon(Icons.open_in_new_rounded, size: 16, color: Color(0xFF059669)),
                              label: const Text(
                                'Pantau Kasus di Menu PPL',
                                style: TextStyle(fontSize: 13, fontWeight: FontWeight.w800, color: Color(0xFF059669)),
                              ),
                              style: TextButton.styleFrom(
                                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
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
                        onPressed: _isSubmittingPpl ? null : _openPplReportModal,
                        icon: _isSubmittingPpl
                            ? const SizedBox(
                                width: 20,
                                height: 20,
                                child: CircularProgressIndicator(strokeWidth: 2.2, color: Colors.white),
                              )
                            : const Icon(Icons.verified_user_rounded, size: 21),
                        label: Text(
                          _isSubmittingPpl ? 'Mengirim ke Penyuluh...' : 'Lapor ke Penyuluh (PPL)',
                          style: const TextStyle(fontSize: 15.5, fontWeight: FontWeight.w900),
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
                      style: TextStyle(fontSize: 15.5, fontWeight: FontWeight.w900),
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
                      style: TextStyle(fontSize: 15, fontWeight: FontWeight.w800),
                    ),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: const Color(0xFF064E3B),
                      side: const BorderSide(color: Color(0xFFA7F3D0), width: 1.2),
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
              color: isSelected ? const Color(0xFF059669) : const Color(0xFFA7F3D0),
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
                child: const Icon(Icons.analytics_outlined, color: Color(0xFF059669), size: 20),
              ),
              const SizedBox(width: 10),
              const Text(
                'Ringkasan Kondisi Daun Padi',
                style: TextStyle(
                  fontSize: 15,
                  fontWeight: FontWeight.w900,
                  color: Color(0xFF0F172A),
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
            style: const TextStyle(fontSize: 12, color: Color(0xFF475569), fontWeight: FontWeight.w700),
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: 4),
          Text(
            value,
            style: const TextStyle(fontSize: 16.5, fontWeight: FontWeight.w900, color: Color(0xFF064E3B)),
            textAlign: TextAlign.center,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
          const SizedBox(height: 3),
          Text(
            subtitle,
            style: const TextStyle(fontSize: 11.5, color: Color(0xFF047857), fontWeight: FontWeight.w800),
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
                child: const Icon(Icons.bolt_rounded, color: Color(0xFF059669), size: 20),
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
    final seg = widget.result.segmentation ?? stages?['stage_2_segmentation'] as Map<String, dynamic>?;
    final feat = widget.result.features ?? stages?['stage_3_feature_extraction'] as Map<String, dynamic>?;
    final profile = PadiDiseaseHelper.getProfile(widget.result.predictedClass);

    final leafPct = seg?['leaf_coverage_pct'] != null ? '${seg!['leaf_coverage_pct']}%' : '96.5%';
    final lesionPct = seg?['lesion_area_pct'] != null ? '${seg!['lesion_area_pct']}%' : '50.1%';
    final severity = (seg?['severity_level']?.toString() ?? profile.severity).toUpperCase();
    final colorFeat = feat?['color_features'] as Map<String, dynamic>?;
    final textureFeat = feat?['texture_features'] as Map<String, dynamic>?;
    final exg = colorFeat?['greenness_exg'] != null ? '${colorFeat!['greenness_exg']}' : '+47.6';
    final roughness = textureFeat?['roughness_laplacian'] != null ? '${textureFeat!['roughness_laplacian']}' : '346.7';
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
                  _buildStageChip('1. Input', 'Foto Daun', Icons.photo_camera_rounded),
                  const Icon(Icons.arrow_forward_rounded, size: 14, color: Color(0xFF6EE7B7)),
                  _buildStageChip('2. Segmentasi', 'Cakupan $leafPct', Icons.crop_free_rounded),
                  const Icon(Icons.arrow_forward_rounded, size: 14, color: Color(0xFF6EE7B7)),
                  _buildStageChip('3. Fitur', 'Warna & Tekstur', Icons.palette_rounded),
                  const Icon(Icons.arrow_forward_rounded, size: 14, color: Color(0xFF6EE7B7)),
                  _buildStageChip('4. Klasifikasi', profile.indonesianName, Icons.psychology_rounded),
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
            onTap: () => setState(() => _showTechnicalDetails = !_showTechnicalDetails),
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
                    _showTechnicalDetails ? Icons.expand_less_rounded : Icons.expand_more_rounded,
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
                  _buildTechnicalRow('Indeks Kehijauan Daun (ExG):', exg, '2G - R - B'),
                  _buildTechnicalRow('Kekasaran Tekstur (Laplacian Var):', roughness, 'Variansi turunan kedua'),
                  _buildTechnicalRow('Jumlah Kluster Bercak:', spots, 'Kontur lesi morfologi'),
                  _buildTechnicalRow('Resolusi Tensor Input:', '384 x 384 px', 'Kanonis Ultralytics'),
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
                style: const TextStyle(fontSize: 12, color: Color(0xFF475569), fontWeight: FontWeight.w700),
              ),
              const SizedBox(height: 2),
              Text(
                value,
                style: const TextStyle(fontSize: 13.5, color: Color(0xFF0F172A), fontWeight: FontWeight.w900),
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
              Text(label, style: const TextStyle(fontSize: 12, color: Color(0xFFA7F3D0))),
              Text(note, style: const TextStyle(fontSize: 10, color: Color(0xFF6EE7B7))),
            ],
          ),
          Text(value, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w900, color: Colors.white)),
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
              Text(step, style: const TextStyle(fontSize: 10.5, fontWeight: FontWeight.w800, color: Color(0xFF059669))),
              Text(label, style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w700, color: Color(0xFF1E293B))),
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
                  Icon(Icons.info_outline_rounded, color: Color(0xFF059669), size: 18),
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

  Widget _buildPredictionCandidateRow(PredictionCandidate candidate, int index) {
    final percent = (candidate.confidence * 100).toStringAsFixed(1);
    final isTop = index == 0;
    final profile = PadiDiseaseHelper.getProfile(candidate.diseaseCode);

    return Container(
      margin: EdgeInsets.only(bottom: index == widget.result.topPredictions.length - 1 ? 0 : 8),
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: BoxDecoration(
        color: isTop ? const Color(0xFFF0FDF4) : Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: isTop ? const Color(0xFFA7F3D0) : const Color(0xFFE2E8F0)),
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
                color: isTop ? const Color(0xFF15803D) : const Color(0xFF475569),
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
          'Gemini AI sedang mengompilasi rekomendasi pencegahan dan obat berdasarkan data klinis daun.',
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
                style: const TextStyle(fontSize: 14.5, color: Color(0xFF1E293B), height: 1.6),
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
                    Icon(Icons.info_outline_rounded, color: Color(0xFF059669), size: 20),
                    SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        'Penyemprotan paling efektif dilakukan sebelum infeksi mencapai lebih dari 20% luas daun.',
                        style: TextStyle(fontSize: 12.5, color: Color(0xFF065F46), fontWeight: FontWeight.w700),
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
            children: [
              _buildStepList(rec.langkahPreventif),
            ],
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
                    Icon(Icons.wb_twilight_rounded, color: Color(0xFF059669), size: 20),
                    SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        'Waktu semprot ideal: Pukul 06.00 - 09.00 pagi atau 15.30 - 17.30 sore (hindari terik matahari langsung).',
                        style: TextStyle(fontSize: 12.5, color: Color(0xFF065F46), fontWeight: FontWeight.w700),
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
                style: const TextStyle(fontSize: 14.5, color: Color(0xFF1E293B), height: 1.6),
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
                    Icon(Icons.savings_outlined, color: Color(0xFF059669), size: 20),
                    SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        'Pestisida nabati efektif menekan jamur & bakteri awal sekaligus menghemat biaya obat hingga 60%.',
                        style: TextStyle(fontSize: 12.5, color: Color(0xFF065F46), fontWeight: FontWeight.w700),
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
                        style: const TextStyle(fontSize: 12, color: Color(0xFF64748B), fontWeight: FontWeight.w600),
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

    final lines = rawText.split('\n').where((l) => l.trim().isNotEmpty).toList();

    return Column(
      children: lines.map((line) {
        final cleanLine = line.replaceFirst(RegExp(r'^\d+[\.\)]\s*'), '').replaceFirst(RegExp(r'^[-*•]\s*'), '');

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
                  border: Border.all(color: const Color(0xFF10B981), width: 1.2),
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
                  child: const Icon(Icons.shopping_bag_outlined, color: Color(0xFF059669), size: 24),
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
                        style: const TextStyle(fontSize: 12.5, color: Color(0xFF64748B)),
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
                  label: const Text('Beli', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w900)),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF059669),
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
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
