import 'dart:async';

import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_riverpod/legacy.dart';
import 'package:padi/core/network/api_client.dart';
import 'package:padi/core/storage/token_storage.dart';
import 'package:padi/features/admin/data/models/admin_overview.dart';
import 'package:padi/features/admin/data/services/admin_api_service.dart';
import 'package:padi/features/auth/data/repositories/auth_repository_impl.dart';
import 'package:padi/features/auth/data/services/auth_api_service.dart';
import 'package:padi/features/auth/domain/repositories/auth_repository.dart';
import 'package:padi/features/auth/presentation/controllers/auth_controller.dart';

final tokenStorageProvider = Provider<TokenStorage>(
  (ref) => const SecureTokenStorage(),
);

final apiClientProvider = Provider<ApiClient>((ref) {
  return ApiClient(ref.read(tokenStorageProvider));
});

final authApiServiceProvider = Provider<AuthApiService>(
  (ref) => AuthApiService(ref.read(apiClientProvider)),
);

final authRepositoryProvider = Provider<AuthRepository>(
  (ref) => AuthRepositoryImpl(ref.read(authApiServiceProvider)),
);

final adminApiServiceProvider = Provider<AdminApiService>(
  (ref) => AdminApiService(ref.read(apiClientProvider)),
);

final adminOverviewProvider = FutureProvider.autoDispose<AdminOverview>(
  (ref) => ref.read(adminApiServiceProvider).fetchOverview(),
);

final adminUsersProvider = FutureProvider.autoDispose<List<AdminUserPreview>>(
  (ref) => ref.read(adminApiServiceProvider).fetchUsers(),
);

final adminBroadcastsProvider =
    FutureProvider.autoDispose<List<AdminBroadcastPreview>>(
      (ref) => ref.read(adminApiServiceProvider).fetchBroadcasts(),
    );

final adminAuditLogsProvider =
    FutureProvider.autoDispose<List<AdminAuditLogPreview>>(
      (ref) => ref.read(adminApiServiceProvider).fetchAuditLogs(),
    );

final authControllerProvider = ChangeNotifierProvider<AuthController>((ref) {
  final controller = AuthController(
    ref.read(authRepositoryProvider),
    ref.read(tokenStorageProvider),
  );

  unawaited(controller.restoreSession());

  return controller;
});
