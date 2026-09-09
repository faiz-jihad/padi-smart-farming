import 'dart:js_interop';
import 'dart:js_interop_unsafe';

import 'face_capture_models.dart';

@JS('PadiFaceApi')
external PadiFaceApi? get _padiFaceApi;

@JS()
extension type PadiFaceApi._(JSObject _) implements JSObject {
  external JSPromise<JSAny?> captureDescriptor();
  external JSPromise<JSAny?> captureEnrollment();
}

FaceCaptureService createFaceCaptureService() {
  return _WebFaceApiJsCaptureService();
}

class _WebFaceApiJsCaptureService implements FaceCaptureService {
  bool get _isFaceApiReady {
    try {
      if (!globalContext.hasProperty('PadiFaceApi'.toJS).toDart) return false;
      final api = _padiFaceApi;
      return api != null && !api.isUndefinedOrNull;
    } catch (_) {
      return false;
    }
  }

  @override
  Future<FaceCaptureResult> captureDescriptor() async {
    if (!_isFaceApiReady) {
      throw const FaceCaptureException(
        'Fitur wajah belum siap. Pastikan internet aktif lalu buka ulang aplikasi.',
      );
    }

    try {
      final api = _padiFaceApi;
      if (api == null || api.isUndefinedOrNull) {
        throw const FaceCaptureException(
          'Fitur wajah belum siap. Buka ulang aplikasi lalu coba lagi.',
        );
      }

      final promise = api.captureDescriptor();
      final result = await promise.toDart;

      if (result == null || result.isUndefinedOrNull) {
        throw const FaceCaptureException(
          'Wajah belum terbaca. Coba lagi di tempat terang.',
        );
      }

      final resultObject = result as JSObject;
      final descriptorArray = resultObject.getProperty<JSArray<JSNumber>?>(
        'descriptor'.toJS,
      );

      if (descriptorArray == null || descriptorArray.length != 128) {
        throw const FaceCaptureException(
          'Wajah belum terbaca. Pastikan wajah menghadap kamera.',
        );
      }

      final descriptor = List<double>.generate(
        128,
        (index) => descriptorArray[index].toDartDouble,
        growable: false,
      );
      final label = resultObject.getProperty<JSString?>('label'.toJS);

      return FaceCaptureResult(
        descriptor: descriptor,
        sourceLabel: label?.toDart ?? 'Wajah terbaca',
      );
    } catch (error) {
      if (error is FaceCaptureException) {
        rethrow;
      }

      final msg = error.toString();
      if (msg.contains('dibatalkan') ||
          msg.contains('cancelled') ||
          msg.contains('abort')) {
        throw const FaceCaptureException('Pengambilan wajah dibatalkan.');
      }

      throw const FaceCaptureException(
        'Kamera belum bisa dibuka. Izinkan kamera lalu coba lagi.',
      );
    }
  }

  @override
  Future<FaceEnrollmentResult> captureEnrollment() async {
    if (!_isFaceApiReady) {
      throw const FaceCaptureException(
        'Fitur wajah belum siap. Pastikan internet aktif lalu buka ulang aplikasi.',
      );
    }

    try {
      final api = _padiFaceApi;
      if (api == null || api.isUndefinedOrNull) {
        throw const FaceCaptureException(
          'Fitur wajah belum siap. Buka ulang aplikasi lalu coba lagi.',
        );
      }

      final promise = api.captureEnrollment();
      final result = await promise.toDart;

      if (result == null || result.isUndefinedOrNull) {
        throw const FaceCaptureException(
          'Wajah belum lengkap terbaca. Coba ulang di tempat terang.',
        );
      }

      final resultObject = result as JSObject;
      final descriptorGroups = resultObject
          .getProperty<JSArray<JSArray<JSNumber>>?>('descriptors'.toJS);

      if (descriptorGroups == null || descriptorGroups.length < 3) {
        throw const FaceCaptureException(
          'Wajah belum lengkap terbaca. Ikuti semua arahan kamera.',
        );
      }

      final descriptors = List<List<double>>.generate(descriptorGroups.length, (
        groupIndex,
      ) {
        final group = descriptorGroups[groupIndex];

        return List<double>.generate(
          group.length,
          (index) => group[index].toDartDouble,
          growable: false,
        );
      }, growable: false);
      final label = resultObject.getProperty<JSString?>('label'.toJS);

      return FaceEnrollmentResult(
        descriptors: descriptors,
        sourceLabel:
            label?.toDart ?? '${descriptors.length} foto wajah tersimpan',
      );
    } catch (error) {
      if (error is FaceCaptureException) {
        rethrow;
      }

      final msg = error.toString();
      if (msg.contains('dibatalkan') ||
          msg.contains('cancelled') ||
          msg.contains('abort')) {
        throw const FaceCaptureException('Pendaftaran wajah dibatalkan.');
      }

      throw const FaceCaptureException(
        'Kamera belum bisa dibuka. Izinkan kamera lalu coba lagi.',
      );
    }
  }
}
