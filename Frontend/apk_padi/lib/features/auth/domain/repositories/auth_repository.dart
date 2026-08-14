import 'package:padi/features/auth/data/models/auth_result.dart';
import 'package:padi/features/auth/domain/entities/app_user.dart';

abstract class AuthRepository {
  Future<AuthResult> register({
    required String name,
    required String email,
    required String phone,
    required String accountType,
    required String password,
    required String passwordConfirmation,
  });

  Future<AuthResult> login({
    required String email,
    required String password,
  });

  Future<AppUser> me();

  Future<AppUser> updateProfile({
    required String name,
    required String phone,
  });

  Future<void> changePassword({
    required String currentPassword,
    required String password,
    required String passwordConfirmation,
  });

  Future<void> forgotPassword(String email);

  Future<void> logout();

  Future<void> logoutAll();
}
