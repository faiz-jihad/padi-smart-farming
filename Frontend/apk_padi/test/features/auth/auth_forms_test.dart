import 'package:padi/core/router/app_router.dart';
import 'package:padi/core/storage/token_storage.dart';
import 'package:padi/features/auth/presentation/controllers/auth_controller.dart';
import 'package:padi/features/auth/presentation/screens/login_screen.dart';
import 'package:padi/features/auth/presentation/screens/register_screen.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';

import 'auth_test_helpers.dart';

void main() {
  testWidgets('form login menampilkan field utama', (tester) async {
    await tester.pumpWidget(_wrap(const LoginScreen()));

    expect(find.text('Masuk ke P.A.D.I.'), findsOneWidget);
    expect(find.text('Email'), findsOneWidget);
    expect(find.text('Password'), findsOneWidget);
    expect(find.text('Masuk'), findsOneWidget);
  });

  testWidgets('form register menampilkan pilihan Petani dan Pembeli', (tester) async {
    await tester.pumpWidget(_wrap(const RegisterScreen()));

    expect(find.text('Buat akun P.A.D.I.'), findsOneWidget);
    expect(find.text('Nama lengkap'), findsOneWidget);
    expect(find.text('Petani'), findsOneWidget);
    expect(find.text('Pembeli'), findsOneWidget);
    expect(find.text('Daftar'), findsOneWidget);
  });
}

Widget _wrap(Widget child) {
  final controller = AuthController(FakeAuthRepository(), InMemoryTokenStorage());

  return ProviderScope(
    overrides: [
      authControllerProvider.overrideWith((ref) => controller),
    ],
    child: MaterialApp(home: child),
  );
}
