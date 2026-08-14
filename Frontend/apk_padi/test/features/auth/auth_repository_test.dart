import 'package:padi/features/auth/data/models/auth_result.dart';
import 'package:padi/features/auth/data/repositories/auth_repository_impl.dart';
import 'package:padi/features/auth/data/services/auth_api_service.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mocktail/mocktail.dart';

import 'auth_test_helpers.dart';

class MockAuthApiService extends Mock implements AuthApiService {}

void main() {
  test('AuthRepository meneruskan login ke AuthApiService', () async {
    final service = MockAuthApiService();
    final repository = AuthRepositoryImpl(service);

    when(() => service.login(email: 'budi@example.com', password: 'password'))
        .thenAnswer((_) async => const AuthResult(user: testUser, token: 'token-login'));

    final result = await repository.login(email: 'budi@example.com', password: 'password');

    expect(result.token, 'token-login');
    verify(() => service.login(email: 'budi@example.com', password: 'password')).called(1);
  });

  test('AuthRepository meneruskan register ke AuthApiService', () async {
    final service = MockAuthApiService();
    final repository = AuthRepositoryImpl(service);

    when(
      () => service.register(
        name: 'Budi Santoso',
        email: 'budi@example.com',
        phone: '081234567890',
        accountType: 'farmer',
        password: 'PasswordKuat123',
        passwordConfirmation: 'PasswordKuat123',
      ),
    ).thenAnswer((_) async => const AuthResult(user: testUser, token: 'token-register'));

    final result = await repository.register(
      name: 'Budi Santoso',
      email: 'budi@example.com',
      phone: '081234567890',
      accountType: 'farmer',
      password: 'PasswordKuat123',
      passwordConfirmation: 'PasswordKuat123',
    );

    expect(result.user.role, 'farmer');
    expect(result.token, 'token-register');
  });
}
