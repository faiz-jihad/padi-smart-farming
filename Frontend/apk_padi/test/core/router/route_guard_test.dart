import 'package:padi/core/router/app_router.dart';
import 'package:padi/core/storage/token_storage.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';

import '../../features/auth/auth_test_helpers.dart';

void main() {
  testWidgets('route guard mengarahkan pengguna tanpa token ke login', (tester) async {
    await tester.pumpWidget(
      ProviderScope(
        overrides: [
          tokenStorageProvider.overrideWithValue(InMemoryTokenStorage()),
          authRepositoryProvider.overrideWithValue(FakeAuthRepository()),
        ],
        child: Consumer(
          builder: (context, ref, child) {
            return MaterialApp.router(routerConfig: ref.watch(appRouterProvider));
          },
        ),
      ),
    );

    await tester.pumpAndSettle();

    expect(find.text('Masuk ke P.A.D.I.'), findsOneWidget);
  });
}
