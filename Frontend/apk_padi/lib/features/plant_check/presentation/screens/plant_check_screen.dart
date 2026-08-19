import 'dart:io';

import 'package:camera/camera.dart';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

class PlantCheckScreen extends StatefulWidget {
  const PlantCheckScreen({super.key});

  @override
  State<PlantCheckScreen> createState() => _PlantCheckScreenState();
}

class _PlantCheckScreenState extends State<PlantCheckScreen> {
  CameraController? _controller;
  XFile? _image;

  bool _isInitializing = true;
  bool _isClosing = false;
  String? _errorMessage;

  static const Color primaryGreen = Color(0xFF075C3D);
  static const Color background = Color(0xFFF7F9F4);
  static const Color textDark = Color(0xFF183D2D);
  static const Color textGrey = Color(0xFF617068);
  static const Color yellow = Color(0xFFF2C94C);

  @override
  void initState() {
    super.initState();
    _initializeCamera();
  }

  Future<void> _initializeCamera() async {
    try {
      if (mounted) {
        setState(() {
          _isInitializing = true;
          _errorMessage = null;
        });
      }

      final cameras = await availableCameras();

      if (cameras.isEmpty) {
        throw Exception('Kamera tidak ditemukan.');
      }

      final camera = cameras.firstWhere(
        (camera) => camera.lensDirection == CameraLensDirection.back,
        orElse: () => cameras.first,
      );

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

      setState(() {
        _isInitializing = false;
      });
    } on CameraException catch (error) {
      if (!mounted) return;

      setState(() {
        _isInitializing = false;

        if (error.code == 'CameraAccessDenied') {
          _errorMessage =
              'Izin kamera ditolak. Izinkan kamera di pengaturan HP.';
        } else {
          _errorMessage = 'Kamera tidak dapat digunakan.';
        }
      });
    } catch (_) {
      if (!mounted) return;

      setState(() {
        _isInitializing = false;
        _errorMessage = 'Kamera tidak dapat digunakan.';
      });
    }
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

      if (!mounted) return;

      setState(() {
        _image = image;
      });
    } on CameraException {
      if (!mounted) return;

      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text(
            'Foto gagal diambil. Silakan coba lagi.',
          ),
        ),
      );
    }
  }

  void _retakePicture() {
    setState(() {
      _image = null;
    });
  }

  void _usePicture() {
    if (_image == null) return;

    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text(
          'Foto siap untuk diperiksa.',
        ),
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

  @override
  void dispose() {
    final controller = _controller;
    _controller = null;

    controller?.dispose();

    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return PopScope(
      canPop: false,
      onPopInvokedWithResult: (didPop, result) {
        if (didPop) return;

        _goHome();
      },
      child: Scaffold(
        backgroundColor: background,
        appBar: AppBar(
          backgroundColor: background,
          elevation: 0,
          scrolledUnderElevation: 0,
          leading: IconButton(
            onPressed: _goHome,
            icon: const Icon(
              Icons.arrow_back_rounded,
              color: primaryGreen,
              size: 32,
            ),
          ),
          title: const Text(
            'Periksa Kondisi Padi',
            style: TextStyle(
              color: textDark,
              fontSize: 24,
              fontWeight: FontWeight.w900,
            ),
          ),
        ),
        body: SafeArea(
          child: _image == null
              ? _buildCameraView()
              : _buildImagePreview(),
        ),
      ),
    );
  }

  Widget _buildCameraView() {
    if (_isInitializing) {
      return const Center(
        child: CircularProgressIndicator(
          color: primaryGreen,
        ),
      );
    }

    if (_errorMessage != null) {
      return _buildCameraError();
    }

    final controller = _controller;

    if (controller == null ||
        !controller.value.isInitialized) {
      return _buildCameraError();
    }

    return Column(
      children: [
        const Padding(
          padding: EdgeInsets.fromLTRB(24, 12, 24, 18),
          child: Text(
            'Arahkan kamera ke daun padi yang ingin diperiksa.',
            textAlign: TextAlign.center,
            style: TextStyle(
              color: textGrey,
              fontSize: 17,
              height: 1.4,
              fontWeight: FontWeight.w500,
            ),
          ),
        ),
        Expanded(
          child: Padding(
            padding: const EdgeInsets.symmetric(
              horizontal: 18,
            ),
            child: ClipRRect(
              borderRadius: BorderRadius.circular(28),
              child: CameraPreview(controller),
            ),
          ),
        ),
        const SizedBox(height: 18),
        const Padding(
          padding: EdgeInsets.symmetric(horizontal: 24),
          child: Text(
            'Pastikan daun terlihat jelas dan mendapat cukup cahaya.',
            textAlign: TextAlign.center,
            style: TextStyle(
              color: textGrey,
              fontSize: 15,
              height: 1.4,
              fontWeight: FontWeight.w500,
            ),
          ),
        ),
        const SizedBox(height: 18),
        Padding(
          padding: const EdgeInsets.fromLTRB(
            18,
            0,
            18,
            20,
          ),
          child: SizedBox(
            width: double.infinity,
            height: 72,
            child: ElevatedButton.icon(
              onPressed: _takePicture,
              icon: const Icon(
                Icons.camera_alt_rounded,
                size: 32,
              ),
              label: const Text(
                'Ambil Foto',
                style: TextStyle(
                  fontSize: 21,
                  fontWeight: FontWeight.w900,
                ),
              ),
              style: ElevatedButton.styleFrom(
                backgroundColor: primaryGreen,
                foregroundColor: Colors.white,
                elevation: 0,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(24),
                ),
              ),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildImagePreview() {
    return Column(
      children: [
        const Padding(
          padding: EdgeInsets.fromLTRB(24, 12, 24, 18),
          child: Text(
            'Apakah foto daun sudah terlihat jelas?',
            textAlign: TextAlign.center,
            style: TextStyle(
              color: textDark,
              fontSize: 21,
              fontWeight: FontWeight.w900,
            ),
          ),
        ),
        Expanded(
          child: Padding(
            padding: const EdgeInsets.symmetric(
              horizontal: 18,
            ),
            child: ClipRRect(
              borderRadius: BorderRadius.circular(28),
              child: Image.file(
                File(_image!.path),
                width: double.infinity,
                fit: BoxFit.cover,
              ),
            ),
          ),
        ),
        const SizedBox(height: 18),
        Padding(
          padding: const EdgeInsets.symmetric(
            horizontal: 18,
          ),
          child: Row(
            children: [
              Expanded(
                child: SizedBox(
                  height: 64,
                  child: OutlinedButton.icon(
                    onPressed: _retakePicture,
                    icon: const Icon(
                      Icons.refresh_rounded,
                      size: 28,
                    ),
                    label: const Text(
                      'Foto Ulang',
                      style: TextStyle(
                        fontSize: 17,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                    style: OutlinedButton.styleFrom(
                      foregroundColor: primaryGreen,
                      side: const BorderSide(
                        color: primaryGreen,
                        width: 2,
                      ),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(20),
                      ),
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: SizedBox(
                  height: 64,
                  child: ElevatedButton.icon(
                    onPressed: _usePicture,
                    icon: const Icon(
                      Icons.check_rounded,
                      size: 28,
                    ),
                    label: const Text(
                      'Gunakan Foto',
                      style: TextStyle(
                        fontSize: 17,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: primaryGreen,
                      foregroundColor: Colors.white,
                      elevation: 0,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(20),
                      ),
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 20),
      ],
    );
  }

  Widget _buildCameraError() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(30),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(
              Icons.no_photography_rounded,
              size: 80,
              color: primaryGreen,
            ),
            const SizedBox(height: 24),
            Text(
              _errorMessage ?? 'Kamera belum siap.',
              textAlign: TextAlign.center,
              style: const TextStyle(
                color: textDark,
                fontSize: 19,
                height: 1.4,
                fontWeight: FontWeight.w700,
              ),
            ),
            const SizedBox(height: 24),
            SizedBox(
              width: double.infinity,
              height: 64,
              child: ElevatedButton.icon(
                onPressed: _initializeCamera,
                icon: const Icon(
                  Icons.refresh_rounded,
                ),
                label: const Text(
                  'Coba Lagi',
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.w800,
                  ),
                ),
                style: ElevatedButton.styleFrom(
                  backgroundColor: primaryGreen,
                  foregroundColor: Colors.white,
                  elevation: 0,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(20),
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