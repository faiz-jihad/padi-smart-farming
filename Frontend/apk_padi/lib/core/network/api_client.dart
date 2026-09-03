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
              connectTimeout: AppConfig.apiConnectTimeout,
              receiveTimeout: AppConfig.apiReceiveTimeout,
              sendTimeout: AppConfig.apiSendTimeout,
              responseType: ResponseType.json,
              headers: {
                'Accept': 'application/json',
              },
            ),
          ) {
    this.dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (
          options,
          handler,
        ) async {
          // ============================================================
          // REQUEST
          // ============================================================

          options.baseUrl = AppConfig.apiBaseUrl;

          var path = options.path.trim();

          path = path.replaceFirst(
            RegExp(r'^/?api/v1/?'),
            '',
          );

          if (!path.startsWith('/')) {
            path = '/$path';
          }

          options.path = path;

          options.responseType = ResponseType.json;

          options.headers['Accept'] = 'application/json';

          // ============================================================
          // TOKEN
          // ============================================================

          final token = await _tokenStorage.readToken();

          if (kDebugMode) {
            final tokenPreview = token == null
                ? 'NULL'
                : '${token.substring(
                    0,
                    token.length > 15 ? 15 : token.length,
                  )}...';

            debugPrint(
              '🔑 TOKEN: $tokenPreview',
            );
          }

          if (token != null && token.isNotEmpty) {
            options.headers['Authorization'] =
                'Bearer $token';
          } else {
            options.headers.remove(
              'Authorization',
            );
          }

          // ============================================================
          // DEBUG REQUEST
          // ============================================================

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

        onResponse: (
          response,
          handler,
        ) {
          // ============================================================
          // DEBUG RESPONSE
          // ============================================================

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

            final dataString =
                response.data?.toString() ?? 'NULL';

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

        onError: (
          error,
          handler,
        ) async {
          // ============================================================
          // DEBUG ERROR
          // ============================================================

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

          // ============================================================
          // UNAUTHORIZED
          // ============================================================

          if (error.response?.statusCode == 401) {
            await _tokenStorage.clearToken();

            onUnauthorized?.call();

            handler.next(error);
            return;
          }

          // ============================================================
          // RETRY HOST
          // ============================================================

          if (!_shouldRetryWithAnotherHost(error)) {
            handler.next(error);
            return;
          }

          final retryResponse =
              await _retryWithAvailableHosts(error);

          if (retryResponse != null) {
            handler.resolve(retryResponse);
            return;
          }

          handler.next(error);
        },
      ),
    );
  }

  // ============================================================
  // FIELDS
  // ============================================================

  final TokenStorage _tokenStorage;

  final Dio dio;

  // ============================================================
  // HOST RETRY
  // ============================================================

  bool _shouldRetryWithAnotherHost(
    DioException error,
  ) {
    if (AppConfig.hasExplicitBaseUrl) {
      return false;
    }

    if (AppConfig.candidateHosts.length <= 1) {
      return false;
    }

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

  // ============================================================
  // WRONG BACKEND CHECK
  // ============================================================

  bool _isLikelyWrongBackend(
    DioException error,
  ) {
    final response = error.response;

    final data = response?.data;

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

    return detail == 'not found' &&
        !hasLaravelErrorShape;
  }

  // ============================================================
  // RETRY AVAILABLE HOSTS
  // ============================================================

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

    triedHosts.add(
      AppConfig.activeHost,
    );

    for (final host in AppConfig.candidateHosts) {
      if (triedHosts.contains(host)) {
        continue;
      }

      triedHosts.add(host);

      AppConfig.useHost(host);

      error.requestOptions
        ..baseUrl = AppConfig.apiBaseUrl
        ..responseType = ResponseType.json
        ..extra['tried_api_hosts'] =
            triedHosts.toList(
          growable: false,
        )
        ..extra['skip_api_host_retry'] = true;

      error.requestOptions.headers['Accept'] =
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

        error.requestOptions.extra.remove(
          'skip_api_host_retry',
        );
      }
    }

    return null;
  }
}