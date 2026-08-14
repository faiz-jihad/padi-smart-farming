import 'dart:async';

import 'package:padi/core/network/api_client.dart';
import 'package:padi/core/storage/token_storage.dart';
import 'package:padi/features/auth/data/repositories/auth_repository_impl.dart';
import 'package:padi/features/auth/data/services/auth_api_service.dart';
import 'package:padi/features/auth/domain/repositories/auth_repository.dart';
import 'package:padi/features/auth/presentation/controllers/auth_controller.dart';
import 'package:padi/features/auth/presentation/screens/change_password_screen.dart';
import 'package:padi/features/auth/presentation/screens/forgot_password_screen.dart';
import 'package:padi/features/auth/presentation/screens/home_screen.dart';
import 'package:padi/features/auth/presentation/screens/login_screen.dart';
import 'package:padi/features/auth/presentation/screens/profile_screen.dart';
import 'package:padi/features/auth/presentation/screens/register_screen.dart';
import 'package:padi/features/auth/presentation/screens/splash_screen.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_riverpod/legacy.dart';
import 'package:go_router/go_router.dart';

final tokenStorageProvider = Provider<TokenStorage>((ref) => const SecureTokenStorage());

final apiClientProvider = Provider<ApiClient>((ref) {
  return ApiClient(ref.read(tokenStorageProvider));
});

final authApiServiceProvider = Provider<AuthApiService>(
  (ref) => AuthApiService(ref.read(apiClientProvider)),
);

final authRepositoryProvider = Provider<AuthRepository>(
  (ref) => AuthRepositoryImpl(ref.read(authApiServiceProvider)),
);

final authControllerProvider = ChangeNotifierProvider<AuthController>((ref) {
  final controller = AuthController(
    ref.read(authRepositoryProvider),
    ref.read(tokenStorageProvider),
  );
  unawaited(controller.restoreSession());
  return controller;
});

final appRouterProvider = Provider<GoRouter>((ref) {
  final auth = ref.watch(authControllerProvider);

  return GoRouter(
    initialLocation: '/splash',
    refreshListenable: auth,
    routes: [
      GoRoute(path: '/splash', builder: (context, state) => const SplashScreen()),
      GoRoute(path: '/login', builder: (context, state) => const LoginScreen()),
      GoRoute(path: '/register', builder: (context, state) => const RegisterScreen()),
      GoRoute(path: '/forgot-password', builder: (context, state) => const ForgotPasswordScreen()),
      GoRoute(path: '/home', builder: (context, state) => const HomeScreen()),
      GoRoute(path: '/profile', builder: (context, state) => const ProfileScreen()),
      GoRoute(path: '/profile/password', builder: (context, state) => const ChangePasswordScreen()),
    ],
    redirect: (context, state) {
      final location = state.matchedLocation;
      final isAuthRoute = location == '/login' || location == '/register' || location == '/forgot-password';

      if (auth.isChecking) {
        return location == '/splash' ? null : '/splash';
      }

      if (!auth.isAuthenticated) {
        return isAuthRoute ? null : '/login';
      }

      if (location == '/splash' || isAuthRoute) {
        return '/home';
      }

      return null;
    },
  );
});
