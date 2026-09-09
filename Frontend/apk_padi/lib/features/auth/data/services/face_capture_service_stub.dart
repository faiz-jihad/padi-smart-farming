import 'dart:math' as math;

import 'package:image_picker/image_picker.dart';

import 'face_capture_models.dart';

FaceCaptureService createFaceCaptureService() {
  return CameraFaceCaptureService();
}

class CameraFaceCaptureService implements FaceCaptureService {
  final ImagePicker _picker = ImagePicker();

  @override
  Future<FaceCaptureResult> captureDescriptor() async {
    final image = await _picker.pickImage(
      source: ImageSource.camera,
      preferredCameraDevice: CameraDevice.front,
      imageQuality: 72,
      maxWidth: 640,
    );

    if (image == null) {
      throw const FaceCaptureException('Pengambilan wajah dibatalkan.');
    }

    final bytes = await image.readAsBytes();

    if (bytes.length < 2048) {
      throw const FaceCaptureException(
        'Foto wajah kurang jelas. Coba ambil ulang.',
      );
    }

    return FaceCaptureResult(
      descriptor: _descriptorFromBytes(bytes),
      sourceLabel: 'Foto wajah tersimpan',
    );
  }

  @override
  Future<FaceEnrollmentResult> captureEnrollment() async {
    final descriptors = <List<double>>[];

    for (final label in ['depan', 'kiri', 'kanan']) {
      final image = await _picker.pickImage(
        source: ImageSource.camera,
        preferredCameraDevice: CameraDevice.front,
        imageQuality: 72,
        maxWidth: 640,
      );

      if (image == null) {
        throw FaceCaptureException('Pendaftaran wajah $label dibatalkan.');
      }

      final bytes = await image.readAsBytes();

      if (bytes.length < 2048) {
        throw FaceCaptureException(
          'Foto wajah $label kurang jelas. Coba ulang.',
        );
      }

      descriptors.add(_descriptorFromBytes(bytes));
    }

    return FaceEnrollmentResult(
      descriptors: descriptors,
      sourceLabel: '3 foto wajah tersimpan',
    );
  }

  List<double> _descriptorFromBytes(List<int> bytes) {
    final buckets = List<double>.filled(128, 0);
    final counts = List<int>.filled(128, 0);
    final step = math.max(1, bytes.length ~/ 8192);

    for (var i = 0; i < bytes.length; i += step) {
      final bucket = (i ~/ step) % buckets.length;
      buckets[bucket] += bytes[i] / 255;
      counts[bucket]++;
    }

    for (var i = 0; i < buckets.length; i++) {
      if (counts[i] > 0) {
        buckets[i] = (buckets[i] / counts[i]) - 0.5;
      }
    }

    return buckets;
  }
}
