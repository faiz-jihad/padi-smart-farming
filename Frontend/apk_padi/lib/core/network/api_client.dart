import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:padi/core/config/app_config.dart';
import 'package:padi/core/storage/token_storage.dart';

class ApiClient {
  ApiClient(
    this._tokenStorage, {
    void Function()? onUnauthorized,
    Dio? dio,
  }) : dio = dio ??
          Dio(
            BaseOptions(
              baseUrl: AppConfig.apiBaseUrl,

              connectTimeout: const Duration(seconds: 15),
              receiveTimeout: const Duration(seconds: 30),
              sendTimeout: const Duration(seconds: 30),

              // =========================================================
              // PENTING
              // Paksa Dio melakukan decode JSON
              // =========================================================
              responseType: ResponseType.json,

              headers: {
                'Accept': 'application/json',
              },
            ),
          ) {
    this.dio.interceptors.add(
      InterceptorsWrapper(
        // =============================================================
        // REQUEST
        // =============================================================
        onRequest: (options, handler) async {
          options.baseUrl = AppConfig.apiBaseUrl;

          // Pastikan response diminta dalam format JSON.
          options.responseType = ResponseType.json;

          options.headers['Accept'] = 'application/json';

          final token = await _tokenStorage.readToken();

          if (kDebugMode) {
            final tokenPreview = token == null
                ? 'NULL'
                : '${token.substring(
                    0,
                    token.length > 15 ? 15 : token.length,
                  )}...';

            debugPrint('🔑 TOKEN: $tokenPreview');
          }

          // ===========================================================
          // AUTHORIZATION
          // ===========================================================

          if (token != null && token.isNotEmpty) {
            options.headers['Authorization'] = 'Bearer $token';
          } else {
            options.headers.remove('Authorization');
          }

          if (kDebugMode) {
            debugPrint(
              '🌐 [API REQ] '
              '${options.method} '
              '${options.baseUrl}${options.path}',
            );

            debugPrint(
              '📦 [API REQ TYPE] '
              '${options.responseType}',
            );
          }

          handler.next(options);
        },

        // =============================================================
        // RESPONSE
        // =============================================================
        onResponse: (response, handler) {
          if (kDebugMode) {
            debugPrint('======================================');
            debugPrint('✅ [API RES]');
            debugPrint('STATUS: ${response.statusCode}');
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

            // Jangan terlalu panjang untuk response besar.
            final dataString = response.data?.toString() ?? 'NULL';

            if (dataString.length > 3000) {
              debugPrint(
                'RESPONSE DATA: '
                '${dataString.substring(0, 3000)}...',
              );
            } else {
              debugPrint(
                'RESPONSE DATA: $dataString',
              );
            }

            debugPrint('======================================');
          }

          handler.next(response);
        },

        // =============================================================
        // ERROR
        // =============================================================
        onError: (error, handler) async {
          if (kDebugMode) {
            debugPrint('======================================');
            debugPrint('❌ [API ERR]');
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
              'RESPONSE: ${error.response?.data}',
            );
            debugPrint('======================================');
          }

          // ===========================================================
          // AUTO FALLBACK HOST
          // ===========================================================

          final errStr =
              '${error.type} ${error.message} ${error.error}'
                  .toLowerCase();

          final isConnectionFailure =
              error.type == DioExceptionType.connectionTimeout ||
              error.type == DioExceptionType.connectionError ||
              error.type == DioExceptionType.sendTimeout ||
              error.type == DioExceptionType.unknown ||
              errStr.contains('lookup') ||
              errStr.contains('socket') ||
              errStr.contains('refused') ||
              errStr.contains('network') ||
              errStr.contains('failed host lookup');

          final triedHosts =
              (error.requestOptions.extra['tried_api_hosts'] as List?)
                      ?.map(
                        (host) => host.toString(),
                      )
                      .toSet() ??
                  <String>{};

          triedHosts.add(
            AppConfig.activeHost,
          );

          if (
              isConnectionFailure &&
              AppConfig.candidateHosts.length > 1) {
            AppConfig.switchToNextHost();

            // Jangan mencoba host yang sama lagi.
            if (triedHosts.contains(
              AppConfig.activeHost,
            )) {
              handler.next(error);
              return;
            }

            triedHosts.add(
              AppConfig.activeHost,
            );

            error.requestOptions.extra['tried_api_hosts'] =
                triedHosts.toList(
              growable: false,
            );

            error.requestOptions.baseUrl =
                AppConfig.apiBaseUrl;

            // Pastikan retry juga menggunakan JSON.
            error.requestOptions.responseType =
                ResponseType.json;

            if (kDebugMode) {
              debugPrint(
                '🔁 [API RETRY] '
                '${AppConfig.apiBaseUrl}'
                '${error.requestOptions.path}',
              );
            }

            try {
              final response = await this.dio.fetch(
                error.requestOptions,
              );

              return handler.resolve(
                response,
              );
            } catch (retryError) {
              if (retryError is DioException) {
                return handler.next(
                  retryError,
                );
              }

              handler.next(error);
              return;
            }
          }

          // ===========================================================
          // UNAUTHORIZED
          // ===========================================================

          if (error.response?.statusCode == 401) {
            await _tokenStorage.clearToken();

            onUnauthorized?.call();
          }

          handler.next(error);
        },
      ),
    );
  }

  final TokenStorage _tokenStorage;

  final Dio dio;
}