import 'package:padi/features/auth/data/models/auth_result.dart';
import 'package:padi/features/auth/domain/entities/app_user.dart';
import 'package:padi/features/auth/domain/repositories/auth_repository.dart';

const testUser = AppUser(
  id: 1,
  name: 'Budi Santoso',
  email: 'budi@example.com',
  phone: '081234567890',
  role: 'farmer',
  roleLabel: 'Petani',
  status: 'active',
  statusLabel: 'Aktif',
);

class FakeAuthRepository implements AuthRepository {
  bool failMe = false;
  bool failLogin = false;
  bool logoutCalled = false;

  @override
  Future<AuthResult> login({required String email, required String password}) async {
    if (failLogin) {
      throw Exception('Login gagal');
    }
    return const AuthResult(user: testUser, token: 'token-login');
  }

  @override
  Future<AuthResult> register({
    required String name,
    required String email,
    required String phone,
    required String accountType,
    required String password,
    required String passwordConfirmation,
  }) async {
    return const AuthResult(user: testUser, token: 'token-register');
  }

  @override
  Future<AppUser> me() async {
    if (failMe) {
      throw Exception('Token tidak valid');
    }
    return testUser;
  }

  @override
  Future<AppUser> updateProfile({required String name, required String phone}) async {
    return testUser.copyWith(name: name, phone: phone);
  }

  @override
  Future<void> changePassword({
    required String currentPassword,
    required String password,
    required String passwordConfirmation,
  }) async {}

  @override
  Future<void> forgotPassword(String email) async {}

  @override
  Future<void> logout() async {
    logoutCalled = true;
  }

  @override
  Future<void> logoutAll() async {
    logoutCalled = true;
  }
}
