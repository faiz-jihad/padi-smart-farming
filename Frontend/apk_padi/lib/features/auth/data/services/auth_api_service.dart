import 'package:padi/core/config/app_config.dart';
import 'package:padi/core/helpers/api_error_helper.dart';
import 'package:padi/core/network/api_client.dart';
import 'package:padi/features/auth/data/models/app_user_model.dart';
import 'package:padi/features/auth/data/models/auth_result.dart';

class AuthApiService {
  const AuthApiService(this._apiClient);

  final ApiClient _apiClient;

  Future<AuthResult> register({
    required String name,
    required String email,
    required String phone,
    required String accountType,
    required String password,
    required String passwordConfirmation,
  }) async {
    try {
      final response = await _apiClient.dio.post<Map<String, dynamic>>(
        '/auth/register',
        data: {
          'name': name,
          'email': email,
          'phone': phone,
          'account_type': accountType,
          'password': password,
          'password_confirmation': passwordConfirmation,
          'device_name': AppConfig.deviceName,
        },
      );

      return _authResultFromResponse(response.data);
    } catch (error) {
      throw mapDioException(error);
    }
  }

  Future<AuthResult> login({required String email, required String password}) async {
    try {
      final response = await _apiClient.dio.post<Map<String, dynamic>>(
        '/auth/login',
        data: {
          'email': email,
          'password': password,
          'device_name': AppConfig.deviceName,
        },
      );

      return _authResultFromResponse(response.data);
    } catch (error) {
      throw mapDioException(error);
    }
  }

  Future<AppUserModel> me() async {
    try {
      final response = await _apiClient.dio.get<Map<String, dynamic>>('/auth/me');
      return _userFromResponse(response.data);
    } catch (error) {
      throw mapDioException(error);
    }
  }

  Future<AppUserModel> updateProfile({required String name, required String phone}) async {
    try {
      final response = await _apiClient.dio.patch<Map<String, dynamic>>(
        '/profile',
        data: {
          'name': name,
          'phone': phone,
        },
      );
      return _userFromResponse(response.data);
    } catch (error) {
      throw mapDioException(error);
    }
  }

  Future<void> changePassword({
    required String currentPassword,
    required String password,
    required String passwordConfirmation,
  }) async {
    try {
      await _apiClient.dio.put<Map<String, dynamic>>(
        '/profile/password',
        data: {
          'current_password': currentPassword,
          'password': password,
          'password_confirmation': passwordConfirmation,
        },
      );
    } catch (error) {
      throw mapDioException(error);
    }
  }

  Future<void> forgotPassword(String email) async {
    try {
      await _apiClient.dio.post<Map<String, dynamic>>(
        '/auth/forgot-password',
        data: {'email': email},
      );
    } catch (error) {
      throw mapDioException(error);
    }
  }

  Future<void> logout() async {
    try {
      await _apiClient.dio.post<Map<String, dynamic>>('/auth/logout');
    } catch (error) {
      throw mapDioException(error);
    }
  }

  Future<void> logoutAll() async {
    try {
      await _apiClient.dio.post<Map<String, dynamic>>('/auth/logout-all');
    } catch (error) {
      throw mapDioException(error);
    }
  }
}

AuthResult _authResultFromResponse(Map<String, dynamic>? json) {
  final data = json?['data'] as Map<String, dynamic>? ?? {};
  return AuthResult(
    user: AppUserModel.fromJson(data['user'] as Map<String, dynamic>),
    token: data['token']?.toString(),
  );
}

AppUserModel _userFromResponse(Map<String, dynamic>? json) {
  final data = json?['data'] as Map<String, dynamic>? ?? {};
  return AppUserModel.fromJson(data['user'] as Map<String, dynamic>);
}
