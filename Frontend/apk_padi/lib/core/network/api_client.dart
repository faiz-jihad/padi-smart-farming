import 'package:flutter/foundation.dart';
import 'package:padi/core/config/app_config.dart';
import 'package:padi/core/storage/token_storage.dart';
import 'package:dio/dio.dart';

class ApiClient {
  ApiClient(this._tokenStorage, {void Function()? onUnauthorized, Dio? dio})
    : dio = dio ??
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
          if (kDebugMode) {
  debugPrint('🔑 TOKEN: ${token == null ? 'NULL' : '${token.substring(0, token.length > 15 ? 15 : token.length)}...'}');
}

if (token != null && token.isNotEmpty) {
  options.headers['Authorization'] = 'Bearer $token';
}
          if (token != null && token.isNotEmpty) {
            options.headers['Authorization'] = 'Bearer $token';
          }
          if (kDebugMode) {
            debugPrint('🌐 [API REQ] ${options.method} ${options.baseUrl}${options.path}');
          }
          handler.next(options);
        },
        onResponse: (response, handler) {
          if (kDebugMode) {
            debugPrint('✅ [API RES] ${response.statusCode} ${response.requestOptions.path}');
          }
          handler.next(response);
        },
        onError: (error, handler) async {
          if (kDebugMode) {
            debugPrint('❌ [API ERR] ${error.type} ${error.message} on ${error.requestOptions.uri}\nResponse: ${error.response?.data}');
          }

          // Auto-fallback to next candidate host on connection failure / lookup failed
          final errStr = '${error.type} ${error.message} ${error.error}'.toLowerCase();
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
                  ?.map((host) => host.toString())
                  .toSet() ??
              <String>{};
          triedHosts.add(AppConfig.activeHost);

          if (isConnectionFailure && AppConfig.candidateHosts.length > 1) {
            AppConfig.switchToNextHost();

            if (triedHosts.contains(AppConfig.activeHost)) {
              handler.next(error);
              return;
            }

            triedHosts.add(AppConfig.activeHost);
            error.requestOptions.extra['tried_api_hosts'] =
                triedHosts.toList(growable: false);
            error.requestOptions.baseUrl = AppConfig.apiBaseUrl;

            if (kDebugMode) {
              debugPrint('🔁 [API RETRY] Retrying with new host: ${AppConfig.apiBaseUrl}${error.requestOptions.path}');
            }

            try {
              final response = await this.dio.fetch(error.requestOptions);
              return handler.resolve(response);
            } catch (retryError) {
              if (retryError is DioException) {
                return handler.next(retryError);
              }
            }
          }

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
