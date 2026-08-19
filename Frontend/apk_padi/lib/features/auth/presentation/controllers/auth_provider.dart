import 'dart:async';

import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_riverpod/legacy.dart';
import 'package:padi/core/network/api_client.dart';
import 'package:padi/core/storage/token_storage.dart';
import 'package:padi/features/auth/data/repositories/auth_repository_impl.dart';
import 'package:padi/features/auth/data/services/auth_api_service.dart';
import 'package:padi/features/auth/domain/repositories/auth_repository.dart';
import 'package:padi/features/auth/presentation/controllers/auth_controller.dart';

final tokenStorageProvider = Provider<TokenStorage>(
  (ref) => const SecureTokenStorage(),
);

final apiClientProvider = Provider<ApiClient>((ref) {
  return ApiClient(
    ref.read(tokenStorageProvider),
  );
});

final authApiServiceProvider = Provider<AuthApiService>(
  (ref) {
    return AuthApiService(
      ref.read(apiClientProvider),
    );
  },
);

final authRepositoryProvider = Provider<AuthRepository>(
  (ref) {
    return AuthRepositoryImpl(
      ref.read(authApiServiceProvider),
    );
  },
);

final authControllerProvider =
    ChangeNotifierProvider<AuthController>((ref) {
  final controller = AuthController(
    ref.read(authRepositoryProvider),
    ref.read(tokenStorageProvider),
  );

  unawaited(
    controller.restoreSession(),
  );

  return controller;
});