import 'package:dio/dio.dart';
import 'package:padi/core/errors/api_exception.dart';

ApiException mapDioException(Object error) {
  if (error is DioException) {
    final response = error.response;
    final data = response?.data;

    if (data is Map<String, dynamic>) {
      return ApiException(
        _messageFromResponse(data) ?? _fallbackMessage(error),
        statusCode: response?.statusCode,
        errors: _parseErrors(data['errors']),
      );
    }

    return ApiException(
      _fallbackMessage(error),
      statusCode: response?.statusCode,
    );
  }

  return const ApiException('Terjadi kesalahan. Silakan coba lagi.');
}

String? _messageFromResponse(Map<String, dynamic> data) {
  final message = data['message']?.toString();
  if (message != null && message.isNotEmpty) {
    return message;
  }

  final error = data['error'];
  if (error is Map && error['message'] != null) {
    return error['message'].toString();
  }

  final detail = data['detail'];
  if (detail is String && detail.isNotEmpty) {
    if (detail.toLowerCase() == 'not found') {
      return 'Endpoint backend tidak ditemukan. Pastikan Flutter terhubung ke Laravel di port 8000, bukan AI service.';
    }

    return detail;
  }

  if (detail is List && detail.isNotEmpty) {
    return detail
        .map((item) {
          if (item is Map && item['msg'] != null) {
            return item['msg'].toString();
          }

          return item.toString();
        })
        .join('\n');
  }

  return null;
}

Map<String, List<String>> _parseErrors(Object? rawErrors) {
  if (rawErrors is! Map) {
    return {};
  }

  return rawErrors.map((key, value) {
    final messages =
        value is List
            ? value.map((item) => item.toString()).toList()
            : [value.toString()];
    return MapEntry(key.toString(), messages);
  });
}

String _fallbackMessage(DioException error) {
  return switch (error.type) {
    DioExceptionType.connectionTimeout ||
    DioExceptionType.sendTimeout ||
    DioExceptionType.receiveTimeout =>
      'Koneksi terlalu lama. Pastikan Laravel berjalan di port 8000.',
    DioExceptionType.connectionError => 'Tidak dapat terhubung ke server.',
    _ => 'Server belum dapat memproses permintaan.',
  };
}
