import 'package:padi/features/auth/data/models/auth_result.dart';
import 'package:padi/features/auth/data/services/auth_api_service.dart';
import 'package:padi/features/auth/domain/entities/app_user.dart';
import 'package:padi/features/auth/domain/repositories/auth_repository.dart';

class AuthRepositoryImpl implements AuthRepository {
  const AuthRepositoryImpl(this._authApiService);

  final AuthApiService _authApiService;

  @override
  Future<AuthResult> register({
    required String name,
    required String email,
    required String phone,
    required String accountType,
    required String password,
    required String passwordConfirmation,
  }) {
    return _authApiService.register(
      name: name,
      email: email,
      phone: phone,
      accountType: accountType,
      password: password,
      passwordConfirmation: passwordConfirmation,
    );
  }

  @override
  Future<AuthResult> login({
    required String email,
    required String password,
  }) {
    return _authApiService.login(
      email: email,
      password: password,
    );
  }

  @override
  Future<AppUser> me() {
    return _authApiService.me();
  }

  @override
  Future<AppUser> updateProfile({
    required String name,
    required String phone,
  }) {
    return _authApiService.updateProfile(
      name: name,
      phone: phone,
    );
  }

  @override
  Future<void> changePassword({
    required String currentPassword,
    required String password,
    required String passwordConfirmation,
  }) {
    return _authApiService.changePassword(
      currentPassword: currentPassword,
      password: password,
      passwordConfirmation: passwordConfirmation,
    );
  }

  @override
  Future<void> forgotPassword(String email) {
    return _authApiService.forgotPassword(email);
  }

  @override
  Future<bool> verifyResetCode({
    required String email,
    required String code,
  }) {
    return _authApiService.verifyResetCode(
      email: email,
      code: code,
    );
  }

  @override
  Future<void> resetPassword({
    required String email,
    required String code,
    required String password,
    required String passwordConfirmation,
  }) {
    return _authApiService.resetPassword(
      email: email,
      code: code,
      password: password,
      passwordConfirmation: passwordConfirmation,
    );
  }

  @override
  Future<void> logout() {
    return _authApiService.logout();
  }

  @override
  Future<void> logoutAll() {
    return _authApiService.logoutAll();
  }
}