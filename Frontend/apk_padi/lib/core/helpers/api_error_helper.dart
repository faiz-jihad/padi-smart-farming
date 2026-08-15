import 'package:padi/core/errors/api_exception.dart';
import 'package:dio/dio.dart';

ApiException mapDioException(Object error) {
  if (error is DioException) {
    final response = error.response;
    final data = response?.data;

    if (data is Map<String, dynamic>) {
      return ApiException(
        data['message']?.toString() ?? _fallbackMessage(error),
        statusCode: response?.statusCode,
        errors: _parseErrors(data['errors']),
      );
    }

    return ApiException(_fallbackMessage(error), statusCode: response?.statusCode);
  }

  return const ApiException('Terjadi kesalahan. Silakan coba lagi.');
}

Map<String, List<String>> _parseErrors(Object? rawErrors) {
  if (rawErrors is! Map) {
    return {};
  }

  return rawErrors.map((key, value) {
    final messages = value is List ? value.map((item) => item.toString()).toList() : [value.toString()];
    return MapEntry(key.toString(), messages);
  });
}

String _fallbackMessage(DioException error) {
  return switch (error.type) {
    DioExceptionType.connectionTimeout ||
    DioExceptionType.sendTimeout ||
    DioExceptionType.receiveTimeout => 'Koneksi terlalu lama. Periksa internet lalu coba lagi.',
    DioExceptionType.connectionError => 'Tidak dapat terhubung ke server.',
    _ => 'Server belum dapat memproses permintaan.',
  };
}
