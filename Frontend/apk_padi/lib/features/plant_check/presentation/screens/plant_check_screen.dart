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

    try {
      double? lat = farm?.latitude;
      double? lng = farm?.longitude;

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

      setState(() {
        _scanError = errMsg;
      });

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Row(
            children: [
              const Icon(Icons.warning_amber_rounded, color: Colors.white, size: 24),
              const SizedBox(width: 12),
              Expanded(
                child: Text(
                  errMsg,
                  style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600, fontSize: 13),
                ),
              ),
            ],
          ),
          backgroundColor: Colors.red.shade800,
          behavior: SnackBarBehavior.floating,
          duration: const Duration(seconds: 4),
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

  @override
  void initState() {
    super.initState();
    _initTts();
    if (widget.result.isLearned || widget.result.userFeedback != null) {
      _feedbackSent = true;
      _feedbackMessage = 'Foto daun ini telah tercatat dalam memori pembelajaran AI.';
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

    final rec = widget.result.recommendation;
    final disease = widget.result.predictedClass;
    final textToSpeak = rec != null
        ? 'Diagnosa kecerdasan buatan Gemini mendeteksi $disease. ${rec.analisis}. Langkah pengendalian: ${rec.langkahPreventif}.'
        : 'Diagnosa kecerdasan buatan mendeteksi $disease.';

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
    final confidence = result.confidence;
    final confidencePercent = confidence != null
        ? (confidence * 100).toStringAsFixed(1)
        : null;
    final modelAccuracyPercent = result.modelAccuracy != null
        ? (result.modelAccuracy! * 100).toStringAsFixed(1)
        : null;

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
              padding: const EdgeInsets.fromLTRB(18, 4, 18, 24),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  // ================= A. ULTRA-PREMIUM AURORA HERO CARD =================
                  Container(
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [Color(0xFF022C22), Color(0xFF064E3B), Color(0xFF065F46)],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                      borderRadius: BorderRadius.circular(22),
                      boxShadow: [
                        BoxShadow(
                          color: const Color(0xFF064E3B).withOpacity(0.35),
                          blurRadius: 16,
                          offset: const Offset(0, 6),
                        ),
                      ],
                    ),
                    child: Stack(
                      children: [
                        // Background Glow Circle
                        Positioned(
                          top: -30,
                          right: -30,
                          child: Container(
                            width: 130,
                            height: 130,
                            decoration: BoxDecoration(
                              shape: BoxShape.circle,
                              color: const Color(0xFF10B981).withOpacity(0.15),
                            ),
                          ),
                        ),

                        Padding(
                          padding: const EdgeInsets.all(18),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              // Top Badges Row
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                                    decoration: BoxDecoration(
                                      color: Colors.white.withOpacity(0.15),
                                      borderRadius: BorderRadius.circular(20),
                                      border: Border.all(color: Colors.white.withOpacity(0.2)),
                                    ),
                                    child: const Row(
                                      children: [
                                        Icon(Icons.auto_awesome_rounded, color: Color(0xFFFDE68A), size: 14),
                                        SizedBox(width: 5),
                                        Text(
                                          'P.A.D.I. Vision AI',
                                          style: TextStyle(
                                            color: Colors.white,
                                            fontSize: 11.5,
                                            fontWeight: FontWeight.w800,
                                            letterSpacing: 0.2,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),

                                  // Confidence Pill
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                                    decoration: BoxDecoration(
                                      gradient: const LinearGradient(
                                        colors: [Color(0xFF10B981), Color(0xFF059669)],
                                      ),
                                      borderRadius: BorderRadius.circular(20),
                                      boxShadow: [
                                        BoxShadow(
                                          color: const Color(0xFF10B981).withOpacity(0.4),
                                          blurRadius: 8,
                                        ),
                                      ],
                                    ),
                                    child: Row(
                                      children: [
                                        Icon(
                                          result.needsExpertReview
                                              ? Icons.manage_search_rounded
                                              : Icons.verified_rounded,
                                          color: Colors.white,
                                          size: 13,
                                        ),
                                        const SizedBox(width: 4),
                                        Text(
                                          result.needsExpertReview
                                              ? 'Perlu review'
                                              : confidencePercent != null
                                              ? '$confidencePercent% yakin'
                                              : 'Perlu review',
                                          style: const TextStyle(
                                            color: Colors.white,
                                            fontSize: 11.5,
                                            fontWeight: FontWeight.w900,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),
                                ],
                              ),

                              const SizedBox(height: 16),

                              const Text(
                                'HASIL DIAGNOSA TANAMAN',
                                style: TextStyle(
                                  color: Color(0xFF6EE7B7),
                                  fontSize: 11,
                                  fontWeight: FontWeight.w800,
                                  letterSpacing: 1.1,
                                ),
                              ),
                              const SizedBox(height: 4),
                              Text(
                                result.predictedClass,
                                style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 21,
                                  fontWeight: FontWeight.w900,
                                  height: 1.25,
                                ),
                              ),
                              if (result.statusMessage != null && result.statusMessage!.isNotEmpty) ...[
                                const SizedBox(height: 10),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                                  decoration: BoxDecoration(
                                    color: const Color(0xFFF59E0B).withOpacity(0.18),
                                    borderRadius: BorderRadius.circular(12),
                                    border: Border.all(color: const Color(0xFFFCD34D).withOpacity(0.45)),
                                  ),
                                  child: Row(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      const Icon(Icons.info_outline_rounded, color: Color(0xFFFDE68A), size: 17),
                                      const SizedBox(width: 8),
                                      Expanded(
                                        child: Text(
                                          result.statusMessage!,
                                          style: const TextStyle(
                                            color: Color(0xFFFFF7ED),
                                            fontSize: 11.5,
                                            fontWeight: FontWeight.w700,
                                            height: 1.35,
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ],

                              const SizedBox(height: 14),

                              // Info Metadata Row & Voice Button
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        if (result.farmName != null)
                                          Row(
                                            children: [
                                              const Icon(Icons.grass_rounded, color: Color(0xFF4ADE80), size: 14),
                                              const SizedBox(width: 4),
                                              Expanded(
                                                child: Text(
                                                  'Sawah: ${result.farmName}',
                                                  style: TextStyle(
                                                    color: Colors.white.withOpacity(0.85),
                                                    fontSize: 12,
                                                    fontWeight: FontWeight.w600,
                                                  ),
                                                  overflow: TextOverflow.ellipsis,
                                                ),
                                              ),
                                            ],
                                          ),
                                        const SizedBox(height: 2),
                                        Text(
                                          modelAccuracyPercent != null
                                              ? 'Model: ${result.modelVersion ?? 'MobileNetV2 Fine-Tuned'} | Validasi: $modelAccuracyPercent%'
                                              : 'Model: ${result.modelVersion ?? 'MobileNetV2 Fine-Tuned'}',
                                          style: TextStyle(
                                            color: Colors.white.withOpacity(0.65),
                                            fontSize: 11,
                                          ),
                                        ),
                                      ],
                                    ),
                                  ),

                                  // Voice Button
                                  InkWell(
                                    onTap: _toggleVoiceGuidance,
                                    borderRadius: BorderRadius.circular(16),
                                    child: Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                                      decoration: BoxDecoration(
                                        color: _isPlayingVoice
                                            ? const Color(0xFFDC2626)
                                            : Colors.white.withOpacity(0.18),
                                        borderRadius: BorderRadius.circular(16),
                                        border: Border.all(
                                          color: _isPlayingVoice
                                              ? const Color(0xFFFCA5A5)
                                              : Colors.white.withOpacity(0.25),
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
                                            size: 15,
                                          ),
                                          const SizedBox(width: 5),
                                          Text(
                                            _isPlayingVoice ? 'Stop Audio' : 'Dengar Suara',
                                            style: const TextStyle(
                                              color: Colors.white,
                                              fontSize: 11,
                                              fontWeight: FontWeight.w700,
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

                  const SizedBox(height: 16),
                  _buildPredictionCandidatesCard(),
                  const SizedBox(height: 16),

                  // ================= B. LUXURY TAB SELECTOR =================
                  SingleChildScrollView(
                    scrollDirection: Axis.horizontal,
                    child: Row(
                      children: [
                        _buildLuxuryTabChip(0, '🔬 Analisis AI'),
                        _buildLuxuryTabChip(1, '🛡️ Pencegahan'),
                        _buildLuxuryTabChip(2, '💊 Dosis Obat'),
                        _buildLuxuryTabChip(3, '🛒 Produk Toko (${rec?.produk.length ?? 0})'),
                        _buildLuxuryTabChip(4, '🌿 Resep DIY'),
                      ],
                    ),
                  ),

                  const SizedBox(height: 14),

                  // ================= C. TAB BODY CONTENT =================
                  _buildTabContent(rec),

                  // ================= D. ADAPTIVE CONTINUOUS LEARNING =================
                  _buildAdaptiveLearningCard(),

                  const SizedBox(height: 20),

                  // ================= E. ACTION BUTTONS =================

                  FilledButton.icon(
                    onPressed: widget.onReportAlert,
                    icon: const Icon(Icons.cell_tower_rounded, size: 19),
                    label: const Text(
                      'Siarkan ke Radar Komunitas',
                      style: TextStyle(fontSize: 14.5, fontWeight: FontWeight.w900),
                    ),
                    style: FilledButton.styleFrom(
                      backgroundColor: const Color(0xFF059669),
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(16),
                      ),
                      elevation: 2,
                    ),
                  ),
                  const SizedBox(height: 8),
                  OutlinedButton.icon(
                    onPressed: widget.onRetake,
                    icon: const Icon(Icons.camera_alt_rounded, size: 18),
                    label: const Text(
                      'Periksa Daun Lain',
                      style: TextStyle(fontSize: 14, fontWeight: FontWeight.w700),
                    ),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: const Color(0xFF334155),
                      side: const BorderSide(color: Color(0xFFCBD5E1)),
                      padding: const EdgeInsets.symmetric(vertical: 14),
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

  Widget _buildLuxuryTabChip(int index, String title) {
    final isSelected = _selectedTab == index;
    return Padding(
      padding: const EdgeInsets.only(right: 8),
      child: InkWell(
        onTap: () => setState(() => _selectedTab = index),
        borderRadius: BorderRadius.circular(20),
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 200),
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
          decoration: BoxDecoration(
            color: isSelected ? const Color(0xFF065F46) : Colors.white,
            borderRadius: BorderRadius.circular(20),
            border: Border.all(
              color: isSelected ? const Color(0xFF059669) : const Color(0xFFE2E8F0),
              width: isSelected ? 1.5 : 1,
            ),
            boxShadow: isSelected
                ? [
                    BoxShadow(
                      color: const Color(0xFF065F46).withOpacity(0.2),
                      blurRadius: 8,
                      offset: const Offset(0, 3),
                    ),
                  ]
                : null,
          ),
          child: Text(
            title,
            style: TextStyle(
              fontSize: 12.5,
              fontWeight: FontWeight.w800,
              color: isSelected ? Colors.white : const Color(0xFF475569),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildPredictionCandidatesCard() {
    final candidates = widget.result.topPredictions;
    if (candidates.isEmpty) return const SizedBox.shrink();

    return _buildModernCard(
      title: 'Kandidat Deteksi Model',
      icon: Icons.analytics_rounded,
      iconColor: const Color(0xFF2563EB),
      subtitle: widget.result.needsExpertReview
          ? 'Hasil utama perlu dikonfirmasi ulang'
          : 'Urutan probabilitas dari model real',
      child: Column(
        children: [
          for (var index = 0; index < candidates.length; index++)
            _buildPredictionCandidateRow(candidates[index], index),
          if ((widget.result.predictionMargin ?? 0) < 0.20) ...[
            const SizedBox(height: 10),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: const Color(0xFFFFFBEB),
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: const Color(0xFFFDE68A)),
              ),
              child: const Row(
                children: [
                  Icon(Icons.info_outline_rounded, color: Color(0xFFD97706), size: 18),
                  SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      'Selisih kandidat dekat. Ambil foto daun lebih dekat dan fokus untuk hasil lebih kuat.',
                      style: TextStyle(
                        fontSize: 11.5,
                        color: Color(0xFF92400E),
                        fontWeight: FontWeight.w600,
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

    return Container(
      margin: EdgeInsets.only(bottom: index == widget.result.topPredictions.length - 1 ? 0 : 8),
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
      decoration: BoxDecoration(
        color: isTop ? const Color(0xFFECFDF5) : const Color(0xFFF8FAFC),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: isTop ? const Color(0xFFA7F3D0) : const Color(0xFFE2E8F0)),
      ),
      child: Row(
        children: [
          Container(
            width: 26,
            height: 26,
            alignment: Alignment.center,
            decoration: BoxDecoration(
              color: isTop ? const Color(0xFF059669) : const Color(0xFFE2E8F0),
              shape: BoxShape.circle,
            ),
            child: Text(
              '${index + 1}',
              style: TextStyle(
                color: isTop ? Colors.white : const Color(0xFF475569),
                fontSize: 12,
                fontWeight: FontWeight.w900,
              ),
            ),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              candidate.diseaseName,
              style: const TextStyle(
                color: Color(0xFF0F172A),
                fontSize: 12.5,
                fontWeight: FontWeight.w800,
                height: 1.25,
              ),
            ),
          ),
          const SizedBox(width: 8),
          Text(
            '$percent%',
            style: TextStyle(
              color: isTop ? const Color(0xFF047857) : const Color(0xFF64748B),
              fontSize: 12,
              fontWeight: FontWeight.w900,
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
        iconColor: const Color(0xFFD97706),
        child: const Text(
          'Gemini AI sedang mengompilasi rekomendasi pencegahan dan obat berdasarkan data klinis daun.',
          style: TextStyle(fontSize: 13, color: Color(0xFF64748B), height: 1.45),
        ),
      );
    }

    switch (_selectedTab) {
      case 0: // Analisis
        return _buildModernCard(
          title: 'Analisis Patogen & Kondisi Cuaca',
          icon: Icons.biotech_rounded,
          iconColor: const Color(0xFF2563EB),
          subtitle: 'Pengaruh suhu, kelembaban, dan tingkat keparahan',
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                rec.analisis.isNotEmpty
                    ? rec.analisis
                    : 'Terdeteksi gejala ${rec.penyakit}. Gejala pada daun menunjukkan infeksi patogen aktif yang perlu segera ditangani agar tidak menyebar ke seluruh hamparan.',
                style: const TextStyle(fontSize: 13.5, color: Color(0xFF334155), height: 1.55),
              ),
              const SizedBox(height: 12),
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: const Color(0xFFEFF6FF),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: const Color(0xFFBFDBFE)),
                ),
                child: const Row(
                  children: [
                    Icon(Icons.info_outline_rounded, color: Color(0xFF2563EB), size: 18),
                    SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        'Penyemprotan paling efektif dilakukan sebelum infeksi mencapai lebih dari 20% luas daun.',
                        style: TextStyle(fontSize: 11.5, color: Color(0xFF1E40AF), fontWeight: FontWeight.w600),
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
          iconColor: const Color(0xFFD97706),
          subtitle: 'Dosis sprayer & panduan waktu semprot',
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              _buildStepList(rec.rekomendasiObat),
              const SizedBox(height: 12),
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: const Color(0xFFFEF3C7),
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: const Color(0xFFFDE68A)),
                ),
                child: const Row(
                  children: [
                    Icon(Icons.wb_twilight_rounded, color: Color(0xFFB45309), size: 18),
                    SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        'Waktu semprot ideal: Pukul 06.00 - 09.00 pagi atau 15.30 - 17.30 sore (hindari terik matahari langsung).',
                        style: TextStyle(fontSize: 11.5, color: Color(0xFF92400E), fontWeight: FontWeight.w600),
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
          title: 'Resep Ramuan Pestisida Nabati DIY',
          icon: Icons.eco_rounded,
          iconColor: const Color(0xFF16A34A),
          subtitle: 'Racikan alami ramah lingkungan & hemat biaya',
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                rec.diy.isNotEmpty
                    ? rec.diy
                    : '1. **Ekstrak Bawang Putih & Kunyit** — Bahan: 250g bawang putih, 250g kunyit, 1 sdm sabun cair. Cara buat: Haluskan dengan 1L air, saring. Gunakan 100ml per tangki 14L.\n2. **Kapur Sirih & Abu Sekam** — Taburkan di tanah asam rumpun padi.',
                style: const TextStyle(fontSize: 13.5, color: Color(0xFF334155), height: 1.55),
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
                    Icon(Icons.savings_outlined, color: Color(0xFF16A34A), size: 18),
                    SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        'Pestisida nabati efektif menekan jamur & bakteri awal sekaligus menghemat biaya obat hingga 60%.',
                        style: TextStyle(fontSize: 11.5, color: Color(0xFF166534), fontWeight: FontWeight.w600),
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
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.02),
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
                  color: iconColor.withOpacity(0.12),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Icon(icon, color: iconColor, size: 20),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      title,
                      style: const TextStyle(
                        fontSize: 14.5,
                        fontWeight: FontWeight.w900,
                        color: Color(0xFF0F172A),
                      ),
                    ),
                    if (subtitle != null) ...[
                      const SizedBox(height: 1),
                      Text(
                        subtitle,
                        style: const TextStyle(fontSize: 11, color: Color(0xFF64748B)),
                      ),
                    ],
                  ],
                ),
              ),
            ],
          ),
          const Divider(height: 22, color: Color(0xFFF1F5F9)),
          child,
        ],
      ),
    );
  }

  Widget _buildStepList(String rawText) {
    if (rawText.isEmpty) {
      return const Text(
        'Ikuti petunjuk sanitasi dan dosis rekomendasi penyuluh pertanian setempat.',
        style: TextStyle(fontSize: 13, color: Color(0xFF64748B)),
      );
    }

    final lines = rawText.split('\n').where((l) => l.trim().isNotEmpty).toList();

    return Column(
      children: lines.map((line) {
        final cleanLine = line.replaceFirst(RegExp(r'^\d+[\.\)]\s*'), '').replaceFirst(RegExp(r'^[-*•]\s*'), '');

        return Padding(
          padding: const EdgeInsets.only(bottom: 10),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: 22,
                height: 22,
                margin: const EdgeInsets.only(top: 2),
                decoration: BoxDecoration(
                  color: const Color(0xFFECFDF5),
                  shape: BoxShape.circle,
                  border: Border.all(color: const Color(0xFF10B981), width: 1.2),
                ),
                child: const Center(
                  child: Icon(Icons.check, size: 13, color: Color(0xFF059669)),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Text(
                  cleanLine,
                  style: const TextStyle(
                    fontSize: 13,
                    color: Color(0xFF334155),
                    height: 1.45,
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
          style: TextStyle(fontSize: 13, color: Color(0xFF64748B)),
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
              fontSize: 13,
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
              border: Border.all(color: const Color(0xFFE2E8F0)),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withOpacity(0.02),
                  blurRadius: 6,
                  offset: const Offset(0, 2),
                ),
              ],
            ),
            child: Row(
              children: [
                Container(
                  width: 44,
                  height: 44,
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                      colors: [Color(0xFFECFDF5), Color(0xFFD1FAE5)],
                    ),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: const Icon(Icons.shopping_bag_outlined, color: Color(0xFF059669), size: 22),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        prod.nama,
                        style: const TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w900,
                          color: Color(0xFF0F172A),
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        'Bahan Aktif: ${prod.bahanAktif}',
                        style: const TextStyle(fontSize: 11.5, color: Color(0xFF64748B)),
                      ),
                      const SizedBox(height: 3),
                      Text(
                        prod.harga,
                        style: const TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w800,
                          color: Color(0xFF059669),
                        ),
                      ),
                    ],
                  ),
                ),
                ElevatedButton.icon(
                  onPressed: () => widget.onSearchProduct(prod.keyword),
                  icon: const Icon(Icons.search_rounded, size: 14),
                  label: const Text('Beli', style: TextStyle(fontSize: 11.5, fontWeight: FontWeight.w800)),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF059669),
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                    visualDensity: VisualDensity.compact,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
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
