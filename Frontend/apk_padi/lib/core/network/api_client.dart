import 'package:dio/dio.dart';
import 'package:flutter/foundation.dart';
import 'package:padi/core/config/app_config.dart';
import 'package:padi/core/storage/token_storage.dart';

class ApiClient {
  ApiClient(this._tokenStorage, {void Function()? onUnauthorized, Dio? dio})
    : dio =
          dio ??
          Dio(
            BaseOptions(
              baseUrl: AppConfig.apiBaseUrl,
              connectTimeout: const Duration(seconds: 15),
              receiveTimeout: const Duration(seconds: 30),
              sendTimeout: const Duration(seconds: 30),
            ),
          ) {
    this.dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) async {
          options.baseUrl = AppConfig.apiBaseUrl;
          options.headers['Accept'] = 'application/json';

          final token = await _tokenStorage.readToken();
          if (token != null && token.isNotEmpty) {
            options.headers['Authorization'] = 'Bearer $token';
          } else {
            options.headers.remove('Authorization');
          }

          if (kDebugMode) {
            debugPrint(
              '[API REQ] ${options.method} ${options.baseUrl}${options.path}',
            );
          }

          handler.next(options);
        },
        onResponse: (response, handler) {
          if (kDebugMode) {
            debugPrint(
              '[API RES] ${response.statusCode} ${response.requestOptions.uri}',
            );
          }

          handler.next(response);
        },
        onError: (error, handler) async {
          if (kDebugMode) {
            debugPrint(
              '[API ERR] ${error.type} ${error.message} on '
              '${error.requestOptions.uri}\nResponse: ${error.response?.data}',
            );
          }

          if (error.response?.statusCode == 401) {
            await _tokenStorage.clearToken();
            onUnauthorized?.call();
          }

          if (!_shouldRetryWithAnotherHost(error)) {
            handler.next(error);
            return;
          }

          final retryResponse = await _retryWithAvailableHosts(error);
          if (retryResponse != null) {
            handler.resolve(retryResponse);
            return;
          }

          handler.next(error);
        },
      ),
    );
  }

  final TokenStorage _tokenStorage;
  final Dio dio;

  bool _shouldRetryWithAnotherHost(DioException error) {
    if (AppConfig.hasExplicitBaseUrl ||
        AppConfig.candidateHosts.length <= 1 ||
        error.requestOptions.extra['skip_api_host_retry'] == true) {
      return false;
    }

    final errStr = '${error.type} ${error.message} ${error.error}'.toLowerCase();
    return error.type == DioExceptionType.connectionTimeout ||
        error.type == DioExceptionType.connectionError ||
        error.type == DioExceptionType.sendTimeout ||
        error.type == DioExceptionType.receiveTimeout ||
        error.type == DioExceptionType.unknown ||
        errStr.contains('lookup') ||
        errStr.contains('socket') ||
        errStr.contains('refused') ||
        errStr.contains('network') ||
        errStr.contains('failed host lookup');
  }

  Future<Response<dynamic>?> _retryWithAvailableHosts(DioException error) async {
    final triedHosts =
        (error.requestOptions.extra['tried_api_hosts'] as List?)
            ?.map((host) => host.toString())
            .toSet() ??
        <String>{};

    triedHosts.add(AppConfig.activeHost);

    for (final host in AppConfig.candidateHosts) {
      if (triedHosts.contains(host)) {
        continue;
      }

      triedHosts.add(host);
      AppConfig.useHost(host);
      error.requestOptions
        ..baseUrl = AppConfig.apiBaseUrl
        ..extra['tried_api_hosts'] = triedHosts.toList(growable: false)
        ..extra['skip_api_host_retry'] = true;

      if (kDebugMode) {
        debugPrint(
          '[API RETRY] Trying ${error.requestOptions.method} '
          '${error.requestOptions.uri}',
        );
      }

      try {
        return await dio.fetch<dynamic>(error.requestOptions);
      } on DioException catch (_) {
        error.requestOptions.extra.remove('skip_api_host_retry');
      }
    }

    return null;
  }
}
