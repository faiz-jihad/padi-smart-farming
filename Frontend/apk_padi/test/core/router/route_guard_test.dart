import 'dart:async';

import 'package:padi/core/router/app_router.dart';
import 'package:padi/core/storage/token_storage.dart';
import 'package:padi/features/auth/data/models/auth_result.dart';
import 'package:padi/features/auth/presentation/controllers/auth_controller.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:go_router/go_router.dart';

import '../../features/auth/auth_test_helpers.dart';

void main() {
  testWidgets('route guard mengarahkan pengguna tanpa token ke onboarding', (
    tester,
  ) async {
    await tester.pumpWidget(
      ProviderScope(
        overrides: [
          tokenStorageProvider.overrideWithValue(InMemoryTokenStorage()),
          authRepositoryProvider.overrideWithValue(FakeAuthRepository()),
        ],
        child: Consumer(
          builder: (context, ref, child) {
            return MaterialApp.router(
              routerConfig: ref.watch(appRouterProvider),
            );
          },
        ),
      ),
    );

    await tester.pumpAndSettle();

    expect(find.text('Selamat Datang di P.A.D.I.'), findsOneWidget);
  });

  testWidgets('submit login tidak mengembalikan router ke splash', (
    tester,
  ) async {
    final storage = InMemoryTokenStorage();
    final repository = _HoldingAuthRepository();
    final controller = AuthController(repository, storage);
    await controller.restoreSession();
    late GoRouter router;

    await tester.pumpWidget(
      ProviderScope(
        overrides: [authControllerProvider.overrideWith((ref) => controller)],
        child: Consumer(
          builder: (context, ref, child) {
            router = ref.watch(appRouterProvider);
            return MaterialApp.router(routerConfig: router);
          },
        ),
      ),
    );

    await tester.pumpAndSettle();
    router.go('/login');
    await tester.pumpAndSettle();

    await tester.enterText(find.byType(TextField).at(0), 'budi@example.com');
    await tester.enterText(find.byType(TextField).at(1), 'password');
    await tester.tap(find.widgetWithText(FilledButton, 'Masuk'));
    await tester.pump();

    expect(find.text('Masuk ke P.A.D.I.'), findsOneWidget);
    expect(find.text('V1.0'), findsNothing);

    repository.completeLogin();
    await tester.pumpAndSettle();

    expect(router.routeInformationProvider.value.uri.path, '/home');
    expect(find.text('V1.0'), findsNothing);
  });
}

class _HoldingAuthRepository extends FakeAuthRepository {
  final _loginCompleter = Completer<AuthResult>();

  @override
  Future<AuthResult> login({required String email, required String password}) {
    return _loginCompleter.future;
  }

  void completeLogin() {
    _loginCompleter.complete(
      const AuthResult(user: testUser, token: 'token-login'),
    );
  }
}
