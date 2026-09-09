import 'face_capture_models.dart';
import 'face_capture_service_stub.dart'
    if (dart.library.html) 'face_capture_service_web.dart'
    as platform;

FaceCaptureService createFaceCaptureService() {
  return platform.createFaceCaptureService();
}
