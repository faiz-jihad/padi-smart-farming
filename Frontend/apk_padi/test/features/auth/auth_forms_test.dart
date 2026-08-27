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

    expect(find.text('Email'), findsOneWidget);
    expect(find.text('Password'), findsOneWidget);
    expect(find.text('Masuk Sekarang'), findsOneWidget);
    expect(find.text('Daftar Akun Baru'), findsOneWidget);
  });

  testWidgets('form register menampilkan pilihan Petani dan Pembeli', (tester) async {
    await tester.pumpWidget(_wrap(const RegisterScreen(initialRole: 'farmer')));

    expect(find.text('Nama Lengkap'), findsOneWidget);
    expect(find.text('Peran: Petani Mitra P.A.D.I.'), findsOneWidget);
    expect(find.text('Daftar sebagai Petani'), findsOneWidget);
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
