import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_riverpod/legacy.dart';

import 'package:go_router/go_router.dart';

import 'package:padi/core/network/api_client.dart';
import 'package:padi/core/storage/token_storage.dart';

import 'package:padi/features/admin/data/models/admin_overview.dart';
import 'package:padi/features/admin/data/services/admin_api_service.dart';
import 'package:padi/features/admin/presentation/screens/admin_overview_screen.dart';

import 'package:padi/features/auth/data/repositories/auth_repository_impl.dart';
import 'package:padi/features/auth/data/services/auth_api_service.dart';
import 'package:padi/features/auth/domain/repositories/auth_repository.dart';
import 'package:padi/features/auth/presentation/controllers/auth_controller.dart';
import 'package:padi/features/auth/presentation/screens/change_password_screen.dart';
import 'package:padi/features/auth/presentation/screens/forgot_password_screen.dart';
import 'package:padi/features/auth/presentation/screens/login_screen.dart';
import 'package:padi/features/auth/presentation/screens/onboarding_screen.dart';
import 'package:padi/features/auth/presentation/screens/profile_screen.dart';
import 'package:padi/features/auth/presentation/screens/register_screen.dart';
import 'package:padi/features/auth/presentation/screens/splash_screen.dart';

import 'package:padi/features/home/presentation/screens/home_screen.dart';
import 'package:padi/features/community_alert/presentation/screens/community_alert_screen.dart';
import 'package:padi/features/community_alert/presentation/screens/report_condition_screen.dart';
import 'package:padi/features/cultivation/presentation/screens/add_activity_screen.dart';
import 'package:padi/features/cultivation/presentation/screens/cultivation_timeline_screen.dart';
import 'package:padi/features/cultivation/presentation/screens/start_planting_season_screen.dart';
import 'package:padi/features/farm/presentation/screens/add_farm_screen.dart';
import 'package:padi/features/farm/presentation/screens/farm_list_screen.dart';
import 'package:padi/features/finance/presentation/screens/add_transaction_screen.dart';
import 'package:padi/features/finance/presentation/screens/finance_screen.dart';
import 'package:padi/features/fertilizer/presentation/screens/fertilizer_calculator_screen.dart';
import 'package:padi/features/harvest/presentation/screens/add_harvest_screen.dart';
import 'package:padi/features/harvest/presentation/screens/harvest_screen.dart';
import 'package:padi/features/map/presentation/screens/planting_calendar_map_page.dart';
import 'package:padi/features/marketplace/presentation/screens/marketplace_screen.dart';
import 'package:padi/features/marketplace/presentation/screens/market_listing_detail_screen.dart';
import 'package:padi/features/marketplace/presentation/screens/create_market_listing_screen.dart';
import 'package:padi/features/plant_check/presentation/screens/plant_check_screen.dart';
import 'package:padi/features/planting_calendar/presentation/screens/planting_calendar_screen.dart';
import 'package:padi/features/marketplace/presentation/screens/create_market_offer_screen.dart';
import 'package:padi/features/marketplace/presentation/screens/market_offers_screen.dart';

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

final appRouterProvider = Provider<GoRouter>((ref) {
  final auth = ref.watch(authControllerProvider);

  return GoRouter(
    initialLocation: '/splash',
    refreshListenable: auth,
    routes: [
      GoRoute(
        path: '/splash',
        builder: (context, state) => const SplashScreen(),
      ),
      GoRoute(
        path: '/onboarding',
        builder: (context, state) => const OnboardingScreen(),
      ),
      GoRoute(path: '/login', builder: (context, state) => const LoginScreen()),
      GoRoute(
        path: '/register',
        builder: (context, state) => const RegisterScreen(),
      ),
      GoRoute(
        path: '/forgot-password',
        builder: (context, state) => const ForgotPasswordScreen(),
      ),
      GoRoute(path: '/home', builder: (context, state) => const HomeScreen()),
      GoRoute(
        path: '/admin',
        builder: (context, state) => const AdminOverviewScreen(),
      ),
      GoRoute(
        path: '/plant-check',
        builder: (context, state) => const PlantCheckScreen(),
      ),
      GoRoute(
        path: '/farms',
        builder: (context, state) => const FarmListScreen(),
      ),
      GoRoute(
        path: '/farms/add',
        builder: (context, state) => const AddFarmScreen(),
      ),
      GoRoute(
        path: '/land/season/start',
        builder: (context, state) => const StartPlantingSeasonScreen(),
      ),
      GoRoute(
        path: '/land/timeline',
        builder: (context, state) {
          final cropSeasonId = int.tryParse(
            state.uri.queryParameters['cropSeasonId'] ?? '',
          );

          return CultivationTimelineScreen(cropSeasonId: cropSeasonId);
        },
      ),
      GoRoute(
        path: '/land/activity/add',
        builder: (context, state) {
          final cropSeasonId = int.tryParse(
            state.uri.queryParameters['cropSeasonId'] ?? '',
          );

          return AddActivityScreen(cropSeasonId: cropSeasonId);
        },
      ),
      GoRoute(
        path: '/fertilizer',
        builder: (context, state) => const FertilizerCalculatorScreen(),
      ),
      GoRoute(
        path: '/planting-calendar',
        builder: (context, state) {
          return const PlantingCalendarScreen();
        },
      ),
      GoRoute(
        path: '/finance',
        builder: (context, state) => const FinanceScreen(),
      ),
      GoRoute(
        path: '/finance/add',
        builder: (context, state) => const AddTransactionScreen(),
      ),
      GoRoute(
        path: '/harvest',
        builder: (context, state) => const HarvestScreen(),
      ),
      GoRoute(
        path: '/harvest/add',
        builder: (context, state) => const AddHarvestScreen(),
      ),
      GoRoute(
        path: '/planting-calendar/:farmId',
        builder: (context, state) {
          final farmId = int.tryParse(state.pathParameters['farmId'] ?? '');

          if (farmId == null) {
            return const PlantingCalendarScreen();
          }

          return PlantingCalendarScreen(farmId: farmId);
        },
      ),
      GoRoute(
        path: '/community-alert',
        builder: (context, state) => const CommunityAlertScreen(),
      ),
      GoRoute(
        path: '/community-alert/report',
        builder: (context, state) {
          final scanId = int.tryParse(
            state.uri.queryParameters['scan_id'] ?? '',
          );

          return ReportConditionScreen(scanId: scanId);
        },
      ),
      GoRoute(
        path: '/marketplace',
        builder: (context, state) {
          return const MarketplaceScreen();
        },
      ),

      GoRoute(
        path: '/marketplace/create',
        builder: (context, state) {
          return const CreateMarketListingScreen();
        },
      ),

      GoRoute(
        path: '/marketplace/:id/offers',
        builder: (context, state) {
          final listingId = int.tryParse(
            state.pathParameters['id'] ?? '',
          );

          if (listingId == null) {
            return const Scaffold(
              body: Center(
                child: Text(
                  'Data hasil panen tidak valid.',
                ),
              ),
            );
          }

          return MarketOffersScreen(
            listingId: listingId,
          );
        },
      ),

      GoRoute(
        path: '/marketplace/:id/offer',
        builder: (context, state) {
          final listingId = int.tryParse(
            state.pathParameters['id'] ?? '',
          );

          final extra =
              state.extra as Map<String, dynamic>?;

          if (listingId == null || extra == null) {
            return const Scaffold(
              body: Center(
                child: Text(
                  'Data listing tidak valid.',
                ),
              ),
            );
          }

          return CreateMarketOfferScreen(
            listingId: listingId,
            commodity:
                extra['commodity']?.toString() ?? '',
            unit:
                extra['unit']?.toString() ?? 'kg',
            maxQuantity:
                (extra['quantity'] as num?)?.toDouble() ?? 0,
            referencePrice:
                (extra['pricePerUnit'] as num?)?.toDouble() ?? 0,
          );
        },
      ),

      GoRoute(
        path: '/marketplace/:id',
        builder: (context, state) {
          final id = int.tryParse(
            state.pathParameters['id'] ?? '',
          );

          if (id == null) {
            return const MarketplaceScreen();
          }

          return MarketListingDetailScreen(
            listingId: id,
          );
        },
      ),
      GoRoute(
        path: '/profile',
        builder: (context, state) => const ProfileScreen(),
      ),
      GoRoute(
        path: '/profile/password',
        builder: (context, state) => const ChangePasswordScreen(),
      ),
      GoRoute(
        path: '/map/calendar',
        builder: (context, state) => const PlantingCalendarMapPage(),
      ),
    ],
    redirect: (context, state) {
      final location = state.matchedLocation;

      final isAuthRoute =
          location == '/login' ||
          location == '/register' ||
          location == '/forgot-password';

      if (auth.isChecking) {
        return location == '/splash' ? null : '/splash';
      }

      if (auth.isAuthenticated) {
        if (location == '/splash' || location == '/onboarding' || isAuthRoute) {
          return '/home';
        }

        if (location == '/admin' && auth.state.user?.role != 'admin') {
          return '/home';
        }

        return null;
      }

      if (location == '/splash') {
        return '/onboarding';
      }

      if (location == '/onboarding') {
        return null;
      }

      if (isAuthRoute) {
        return null;
      }

      return '/login';
    },
  );
});
