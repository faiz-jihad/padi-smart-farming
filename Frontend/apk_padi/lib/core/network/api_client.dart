import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:padi/core/config/app_config.dart';
import 'package:padi/core/storage/token_storage.dart';

class ApiClient {
  ApiClient(
    this._tokenStorage, {
    void Function()? onUnauthorized,
    Dio? dio,
  }) : dio =
          dio ??
          Dio(
            BaseOptions(
              baseUrl: AppConfig.apiBaseUrl,

              // Gunakan konfigurasi dari AppConfig
              connectTimeout:
                  AppConfig.apiConnectTimeout,
              receiveTimeout:
                  AppConfig.apiReceiveTimeout,
              sendTimeout:
                  AppConfig.apiSendTimeout,

              // =====================================================
              // PENTING
              // Paksa Dio meminta dan decode response sebagai JSON.
              // Ini diperlukan untuk endpoint marketplace/invoice.
              // =====================================================
              responseType:
                  ResponseType.json,

              headers: {
                'Accept': 'application/json',
              },
            ),
          ) {
    this.dio.interceptors.add(
      InterceptorsWrapper(
        // ===========================================================
        // REQUEST
        // ===========================================================

        onRequest: (
          options,
          handler,
        ) async {
          // Selalu gunakan host aktif dari AppConfig.
          options.baseUrl =
              AppConfig.apiBaseUrl;

          // =========================================================
          // RESPONSE JSON
          // =========================================================

          options.responseType =
              ResponseType.json;

          options.headers['Accept'] =
              'application/json';

          // =========================================================
          // TOKEN
          // =========================================================

          final token =
              await _tokenStorage.readToken();

          if (kDebugMode) {
            final tokenPreview =
                token == null
                    ? 'NULL'
                    : '${token.substring(
                        0,
                        token.length > 15
                            ? 15
                            : token.length,
                      )}...';

            debugPrint(
              '🔑 TOKEN: $tokenPreview',
            );
          }

          if (token != null &&
              token.isNotEmpty) {
            options.headers['Authorization'] =
                'Bearer $token';
          } else {
            options.headers.remove(
              'Authorization',
            );
          }

          // =========================================================
          // DEBUG LOG
          // =========================================================

          if (kDebugMode) {
            debugPrint(
              '🌐 [API REQ] '
              '${options.method} '
              '${options.baseUrl}'
              '${options.path}',
            );

            debugPrint(
              '📦 [API REQ TYPE] '
              '${options.responseType}',
            );
          }

          handler.next(options);
        },

        // ===========================================================
        // RESPONSE
        // ===========================================================

        onResponse: (
          response,
          handler,
        ) {
          if (kDebugMode) {
            debugPrint(
              '======================================',
            );

            debugPrint(
              '✅ [API RES]',
            );

            debugPrint(
              'STATUS: ${response.statusCode}',
            );

            debugPrint(
              'URL: ${response.requestOptions.uri}',
            );

            debugPrint(
              'CONTENT-TYPE: '
              '${response.headers.value('content-type')}',
            );

            debugPrint(
              'RESPONSE TYPE: '
              '${response.data.runtimeType}',
            );

            // Jangan membuat log terlalu panjang.
            final dataString =
                response.data?.toString() ??
                'NULL';

            if (dataString.length > 3000) {
              debugPrint(
                'RESPONSE DATA: '
                '${dataString.substring(0, 3000)}...',
              );
            } else {
              debugPrint(
                'RESPONSE DATA: '
                '$dataString',
              );
            }

            debugPrint(
              '======================================',
            );
          }

          handler.next(response);
        },

        // ===========================================================
        // ERROR
        // ===========================================================

        onError: (
          error,
          handler,
        ) async {
          if (kDebugMode) {
            debugPrint(
              '======================================',
            );

            debugPrint(
              '❌ [API ERR]',
            );

            debugPrint(
              'TYPE: ${error.type}',
            );

            debugPrint(
              'MESSAGE: ${error.message}',
            );

            debugPrint(
              'URL: ${error.requestOptions.uri}',
            );

            debugPrint(
              'STATUS: ${error.response?.statusCode}',
            );

            debugPrint(
              'CONTENT-TYPE: '
              '${error.response?.headers.value('content-type')}',
            );

            debugPrint(
              'RESPONSE TYPE: '
              '${error.response?.data.runtimeType}',
            );

            debugPrint(
              'RESPONSE: '
              '${error.response?.data}',
            );

            debugPrint(
              '======================================',
            );
          }

          // =========================================================
          // UNAUTHORIZED
          // =========================================================

          if (error.response?.statusCode == 401) {
            await _tokenStorage.clearToken();

            onUnauthorized?.call();

            handler.next(error);
            return;
          }

          // =========================================================
          // CEK APAKAH PERLU PINDAH HOST
          // =========================================================

          if (!_shouldRetryWithAnotherHost(
            error,
          )) {
            handler.next(error);
            return;
          }

          // =========================================================
          // RETRY HOST
          // =========================================================

          final retryResponse =
              await _retryWithAvailableHosts(
            error,
          );

          if (retryResponse != null) {
            handler.resolve(
              retryResponse,
            );
            return;
          }

          handler.next(error);
        },
      ),
    );
  }

  // ===============================================================
  // FIELDS
  // ===============================================================

  final TokenStorage _tokenStorage;

  final Dio dio;

  // ===============================================================
  // CEK RETRY HOST
  // ===============================================================

  bool _shouldRetryWithAnotherHost(
    DioException error,
  ) {
    // Jika user memang menentukan base URL secara eksplisit,
    // jangan pindah-pindah host.
    if (AppConfig.hasExplicitBaseUrl) {
      return false;
    }

    // Tidak ada host lain untuk dicoba.
    if (AppConfig.candidateHosts.length <= 1) {
      return false;
    }

    // Jangan retry lagi jika request ini sudah merupakan retry.
    if (error.requestOptions
            .extra['skip_api_host_retry'] ==
        true) {
      return false;
    }

    final errStr =
        '${error.type} '
        '${error.message} '
        '${error.error}'
            .toLowerCase();

    return _isLikelyWrongBackend(error) ||
        error.type ==
            DioExceptionType.connectionTimeout ||
        error.type ==
            DioExceptionType.connectionError ||
        error.type ==
            DioExceptionType.sendTimeout ||
        error.type ==
            DioExceptionType.receiveTimeout ||
        error.type ==
            DioExceptionType.unknown ||
        errStr.contains('lookup') ||
        errStr.contains('socket') ||
        errStr.contains('refused') ||
        errStr.contains('network') ||
        errStr.contains(
          'failed host lookup',
        );
  }

  // ===============================================================
  // CEK RESPONSE YANG MUNGKIN BERASAL DARI BACKEND SALAH
  // ===============================================================

  bool _isLikelyWrongBackend(
    DioException error,
  ) {
    final response =
        error.response;

    final data =
        response?.data;

    // Hanya tangani 404 dengan response Map.
    if (response?.statusCode != 404 ||
        data is! Map) {
      return false;
    }

    final detail =
        data['detail']
            ?.toString()
            .toLowerCase();

    final hasLaravelErrorShape =
        data.containsKey('message') ||
        data.containsKey('errors');

    // Backend tertentu mengembalikan:
    //
    // {
    //   "detail": "Not Found"
    // }
    //
    // Ini kemungkinan bukan Laravel backend kita.
    return detail == 'not found' &&
        !hasLaravelErrorShape;
  }

  // ===============================================================
  // RETRY KE HOST BERIKUTNYA
  // ===============================================================

  Future<Response<dynamic>?>
      _retryWithAvailableHosts(
    DioException error,
  ) async {
    final triedHosts =
        (error.requestOptions
                    .extra['tried_api_hosts']
                as List?)
            ?.map(
              (host) => host.toString(),
            )
            .toSet() ??
        <String>{};

    // Host yang sedang digunakan dianggap sudah dicoba.
    triedHosts.add(
      AppConfig.activeHost,
    );

    // ===========================================================
    // COBA SEMUA CANDIDATE HOST
    // ===========================================================

    for (final host
        in AppConfig.candidateHosts) {
      if (triedHosts.contains(host)) {
        continue;
      }

      triedHosts.add(host);

      // Pindah host.
      AppConfig.useHost(host);

      // =========================================================
      // UPDATE REQUEST
      // =========================================================

      error.requestOptions
        ..baseUrl =
            AppConfig.apiBaseUrl
        ..responseType =
            ResponseType.json
        ..extra['tried_api_hosts'] =
            triedHosts.toList(
          growable: false,
        )
        ..extra['skip_api_host_retry'] =
            true;

      // Pastikan Accept JSON.
      error.requestOptions
          .headers['Accept'] =
          'application/json';

      if (kDebugMode) {
        debugPrint(
          '🔁 [API RETRY] '
          'Trying '
          '${error.requestOptions.method} '
          '${error.requestOptions.uri}',
        );
      }

      try {
        // =======================================================
        // REQUEST ULANG
        // =======================================================

        final response =
            await dio.fetch<dynamic>(
          error.requestOptions,
        );

        if (kDebugMode) {
          debugPrint(
            '✅ [API RETRY SUCCESS] '
            '${response.statusCode} '
            '${response.requestOptions.uri}',
          );

          debugPrint(
            '📦 [API RETRY RESPONSE TYPE] '
            '${response.data.runtimeType}',
          );
        }

        return response;
      } on DioException catch (
        retryError
      ) {
        if (kDebugMode) {
          debugPrint(
            '❌ [API RETRY FAILED] '
            '${retryError.type} '
            '${retryError.message}',
          );

          debugPrint(
            'URL: '
            '${retryError.requestOptions.uri}',
          );

          debugPrint(
            'RESPONSE: '
            '${retryError.response?.data}',
          );
        }

        // Izinkan proses retry berikutnya
        // untuk menentukan host selanjutnya.
        error.requestOptions
            .extra
            .remove(
          'skip_api_host_retry',
        );
      }
    }

    // Tidak ada host yang berhasil.
    return null;
  }
}