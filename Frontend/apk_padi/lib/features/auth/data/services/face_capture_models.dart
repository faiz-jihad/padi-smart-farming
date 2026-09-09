abstract class FaceCaptureService {
  Future<FaceCaptureResult> captureDescriptor();

  Future<FaceEnrollmentResult> captureEnrollment();
}

class FaceCaptureResult {
  const FaceCaptureResult({
    required this.descriptor,
    required this.sourceLabel,
  });

  final List<double> descriptor;
  final String sourceLabel;
}

class FaceCaptureException implements Exception {
  const FaceCaptureException(this.message);

  final String message;

  @override
  String toString() => message;
}

class FaceEnrollmentResult {
  const FaceEnrollmentResult({
    required this.descriptors,
    required this.sourceLabel,
  });

  final List<List<double>> descriptors;
  final String sourceLabel;
}
