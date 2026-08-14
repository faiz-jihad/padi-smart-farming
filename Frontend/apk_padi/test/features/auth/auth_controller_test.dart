import 'package:padi/core/storage/token_storage.dart';
import 'package:padi/features/auth/presentation/controllers/auth_controller.dart';
import 'package:flutter_test/flutter_test.dart';

import 'auth_test_helpers.dart';

void main() {
  test('restoreSession tanpa token menjadi unauthenticated', () async {
    final controller = AuthController(FakeAuthRepository(), InMemoryTokenStorage());

    await controller.restoreSession();

    expect(controller.state.status, AuthStatus.unauthenticated);
  });

  test('restoreSession token valid mengisi user', () async {
    final storage = InMemoryTokenStorage();
    await storage.saveToken('stored-token');
    final controller = AuthController(FakeAuthRepository(), storage);

    await controller.restoreSession();

    expect(controller.state.status, AuthStatus.authenticated);
    expect(controller.state.user?.email, 'budi@example.com');
  });

  test('restoreSession token invalid membersihkan storage', () async {
    final storage = InMemoryTokenStorage();
    await storage.saveToken('stale-token');
    final repository = FakeAuthRepository()..failMe = true;
    final controller = AuthController(repository, storage);

    await controller.restoreSession();

    expect(controller.state.status, AuthStatus.unauthenticated);
    expect(await storage.readToken(), isNull);
  });

  test('login berhasil menyimpan token', () async {
    final storage = InMemoryTokenStorage();
    final controller = AuthController(FakeAuthRepository(), storage);

    final success = await controller.login(email: 'budi@example.com', password: 'password');

    expect(success, isTrue);
    expect(controller.state.status, AuthStatus.authenticated);
    expect(await storage.readToken(), 'token-login');
  });

  test('logout menghapus token', () async {
    final storage = InMemoryTokenStorage();
    await storage.saveToken('stored-token');
    final repository = FakeAuthRepository();
    final controller = AuthController(repository, storage);

    await controller.logout();

    expect(repository.logoutCalled, isTrue);
    expect(await storage.readToken(), isNull);
    expect(controller.state.status, AuthStatus.unauthenticated);
  });
}
