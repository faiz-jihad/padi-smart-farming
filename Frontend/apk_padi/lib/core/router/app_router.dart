
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import 'package:padi/core/providers/app_providers.dart';

import 'package:padi/features/admin/presentation/screens/admin_overview_screen.dart';

import 'package:padi/features/auth/presentation/screens/change_password_screen.dart';
import 'package:padi/features/auth/presentation/screens/forgot_password_screen.dart';
import 'package:padi/features/auth/presentation/screens/reset_password_screen.dart';
import 'package:padi/features/auth/presentation/screens/new_password_screen.dart';
import 'package:padi/features/auth/presentation/screens/language_selection_screen.dart';
import 'package:padi/features/auth/presentation/screens/login_screen.dart';
import 'package:padi/features/auth/presentation/screens/onboarding_screen.dart';
import 'package:padi/features/auth/presentation/screens/profile_screen.dart';
import 'package:padi/features/auth/presentation/screens/register_screen.dart';
import 'package:padi/features/auth/presentation/screens/role_selection_screen.dart';
import 'package:padi/features/auth/presentation/screens/splash_screen.dart';

import 'package:padi/features/home/presentation/screens/home_screen.dart';
import 'package:padi/features/home/presentation/widgets/home_bottom_navigation.dart';
import 'package:padi/features/notifications/presentation/screens/notification_screen.dart';
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
import 'package:padi/features/plant_check/presentation/screens/ppl_case_list_screen.dart';
import 'package:padi/features/plant_check/presentation/screens/ppl_case_detail_screen.dart';
import 'package:padi/features/cart/data/models/cart_item_model.dart';
import 'package:padi/features/cart/presentation/screens/cart_screen.dart';
import 'package:padi/features/cart/presentation/screens/checkout_screen.dart';
import 'package:padi/features/marketplace/presentation/screens/buyer_orders_screen.dart';
import 'package:padi/features/planting_calendar/presentation/screens/planting_calendar_screen.dart';
import 'package:padi/features/marketplace/data/models/purchase_contract_model.dart';
import 'package:padi/features/marketplace/presentation/screens/create_market_offer_screen.dart';
import 'package:padi/features/marketplace/presentation/screens/market_offers_screen.dart';
import 'package:padi/features/marketplace/presentation/screens/purchase_invoice_screen.dart';
import 'package:padi/features/marketplace/presentation/screens/farmer_sales_report_screen.dart';
import 'package:padi/features/event/data/models/event_model.dart';
import 'package:padi/features/event/data/providers/event_providers.dart';
import 'package:padi/features/event/presentation/screens/create_event_screen.dart';
import 'package:padi/features/event/presentation/screens/event_detail_screen.dart';
import 'package:padi/features/event/presentation/screens/event_list_screen.dart';
import 'package:padi/core/widgets/app_error_screen.dart';

export 'package:padi/core/providers/app_providers.dart';

final rootScaffoldMessengerKey = GlobalKey<ScaffoldMessengerState>();

class _ClearSnackBarRouteObserver extends NavigatorObserver {
  @override
  void didPush(Route<dynamic> route, Route<dynamic>? previousRoute) {
    super.didPush(route, previousRoute);
    rootScaffoldMessengerKey.currentState?.clearSnackBars();
  }

  @override
  void didPop(Route<dynamic> route, Route<dynamic>? previousRoute) {
    super.didPop(route, previousRoute);
    rootScaffoldMessengerKey.currentState?.clearSnackBars();
  }

  @override
  void didReplace({
    Route<dynamic>? newRoute,
    Route<dynamic>? oldRoute,
  }) {
    super.didReplace(newRoute: newRoute, oldRoute: oldRoute);
    rootScaffoldMessengerKey.currentState?.clearSnackBars();
  }
}

final appRouterProvider = Provider<GoRouter>((ref) {
  final auth = ref.read(authControllerProvider);

  return GoRouter(
    initialLocation: '/splash',
    refreshListenable: auth,
    observers: [_ClearSnackBarRouteObserver()],
    errorBuilder: (context, state) => Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text('P.A.D.I. Navigasi'),
        backgroundColor: Colors.white,
        elevation: 0,
      ),
      body: Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                padding: const EdgeInsets.all(16),
                decoration: const BoxDecoration(
                  color: Color(0xFFECFDF5),
                  shape: BoxShape.circle,
                ),
                child: const Icon(
                  Icons.navigation_rounded,
                  size: 44,
                  color: Color(0xFF059669),
                ),
              ),
              const SizedBox(height: 16),
              const Text(
                'Halaman Tidak Ditemukan',
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.w900,
                  color: Color(0xFF0F172A),
                ),
              ),
              const SizedBox(height: 6),
              Text(
                'Rute ${state.uri} sedang dialihkan.',
                textAlign: TextAlign.center,
                style: const TextStyle(
                  color: Color(0xFF64748B),
                  fontSize: 13,
                ),
              ),
              const SizedBox(height: 20),
              FilledButton.icon(
                onPressed: () => context.go('/home'),
                icon: const Icon(Icons.home_rounded),
                label: const Text('Kembali ke Beranda'),
                style: FilledButton.styleFrom(
                  backgroundColor: const Color(0xFF059669),
                  padding: const EdgeInsets.symmetric(
                    horizontal: 20,
                    vertical: 12,
                  ),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(14),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    ),
    routes: [
      GoRoute(
        path: '/',
        redirect: (context, state) => '/home',
      ),
      GoRoute(
        path: '/timeline',
        redirect: (context, state) => '/land/timeline',
      ),
      GoRoute(
        path: '/splash',
        builder: (context, state) => const SplashScreen(),
      ),
      GoRoute(
        path: '/onboarding',
        builder: (context, state) => const OnboardingScreen(),
      ),
      GoRoute(
        path: '/login',
        builder: (context, state) => const LoginScreen(),
      ),
      GoRoute(
        path: '/select-role',
        builder: (context, state) => const RoleSelectionScreen(),
      ),
      GoRoute(
        path: '/register',
        builder: (context, state) {
          final role = state.uri.queryParameters['role'];

          if (role == null || role.isEmpty) {
            return const RoleSelectionScreen();
          }

          return RegisterScreen(initialRole: role);
        },
      ),
      GoRoute(
        path: '/forgot-password',
        builder: (context, state) => const ForgotPasswordScreen(),
      ),
      GoRoute(
        path: '/reset-password',
        builder: (context, state) {
          final email = state.extra as String?;

          if (email == null || email.isEmpty) {
            return const ForgotPasswordScreen();
          }

          return ResetPasswordScreen(
            email: email,
          );
        },
      ),
      GoRoute(
        path: '/error/offline',
        builder: (context, state) => AppErrorScreen.offline(
          onRetry: () async {
            final returnTo = state.uri.queryParameters['returnTo'] ?? '/home';
            context.go(returnTo);
          },
        ),
      ),
      GoRoute(
        path: '/error/technical',
        builder: (context, state) {
          final message = state.uri.queryParameters['message'];
          return AppErrorScreen.technical(
            details: message,
            onRetry: () async {
              final returnTo = state.uri.queryParameters['returnTo'] ?? '/home';
              context.go(returnTo);
            },
          );
        },
      ),
      GoRoute(
        path: '/reset-password/new',
        builder: (context, state) {
          final extra = state.extra as Map<String, dynamic>?;

          final email = extra?['email']?.toString();
          final code = extra?['code']?.toString();

          if (email == null ||
              email.isEmpty ||
              code == null ||
              code.isEmpty) {
            return const ForgotPasswordScreen();
          }

          return NewPasswordScreen(
            email: email,
            code: code,
          );
        },
      ),
      GoRoute(
        path: '/error/maintenance',
        builder: (context, state) => AppErrorScreen.maintenance(
          onBack: () => context.go('/home'),
        ),
      ),
      GoRoute(
        path: '/home',
        builder: (context, state) =>
            const _MainTabScaffold(
              currentIndex: 0,
              child: HomeScreen(),
            ),
      ),
      GoRoute(
        path: '/admin',
        builder: (context, state) => const AdminOverviewScreen(),
      ),
      GoRoute(
        path: '/plant-check',
        builder: (context, state) =>
            const _MainTabScaffold(
              currentIndex: 2,
              child: PlantCheckScreen(),
            ),
      ),
      GoRoute(
        path: '/farms/add',
        builder: (context, state) {
          final setupFlow =
              state.uri.queryParameters['flow'] == 'setup';

          return AddFarmScreen(
            setupFlow: setupFlow,
          );
        },
      ),
      GoRoute(
        path: '/farms',
        builder: (context, state) =>
            const _MainTabScaffold(
              currentIndex: 1,
              child: FarmListScreen(),
            ),
      ),
      GoRoute(
        path: '/land/season/start',
        builder: (context, state) {
          final farmId = int.tryParse(
            state.uri.queryParameters['farmId'] ?? '',
          );

          final setupFlow =
              state.uri.queryParameters['flow'] == 'setup';

          final returnTo = state.uri.queryParameters['returnTo'];

          return StartPlantingSeasonScreen(
            farmId: farmId,
            setupFlow: setupFlow,
            returnTo: returnTo,
          );
        },
      ),
      GoRoute(
        path: '/land/timeline',
        builder: (context, state) {
          final cropSeasonId = int.tryParse(
            state.uri.queryParameters['cropSeasonId'] ?? '',
          );

          return CultivationTimelineScreen(
            cropSeasonId: cropSeasonId,
          );
        },
      ),
      GoRoute(
        path: '/land/activity/add',
        builder: (context, state) {
          final cropSeasonId = int.tryParse(
            state.uri.queryParameters['cropSeasonId'] ?? '',
          );

          return AddActivityScreen(
            cropSeasonId: cropSeasonId,
          );
        },
      ),
      GoRoute(
        path: '/ppl-cases',
        builder: (context, state) => const PplCaseListScreen(),
      ),
      GoRoute(
        path: '/ppl-cases/detail',
        builder: (context, state) {
          final caseData = state.extra as Map<String, dynamic>? ?? {};
          return PplCaseDetailScreen(caseData: caseData);
        },
      ),
      GoRoute(
        path: '/fertilizer',
        builder: (context, state) {
          final farmId = int.tryParse(
            state.uri.queryParameters['farmId'] ?? '',
          );

          final cropSeasonId = int.tryParse(
            state.uri.queryParameters['cropSeasonId'] ?? '',
          );

          final setupFlow =
              state.uri.queryParameters['flow'] == 'setup';

          return FertilizerCalculatorScreen(
            farmId: farmId,
            cropSeasonId: cropSeasonId,
            setupFlow: setupFlow,
          );
        },
      ),
      GoRoute(
        path: '/planting-calendar',
        builder: (context, state) {
          final setupFlow =
              state.uri.queryParameters['flow'] == 'setup';

          return PlantingCalendarScreen(
            setupFlow: setupFlow,
          );
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
        builder: (context, state) {
          final farmId = int.tryParse(
            state.uri.queryParameters['farmId'] ?? '',
          );

          final cropSeasonId = int.tryParse(
            state.uri.queryParameters['cropSeasonId'] ?? '',
          );

          final setupFlow =
              state.uri.queryParameters['flow'] == 'setup';

          return AddHarvestScreen(
            farmId: farmId,
            cropSeasonId: cropSeasonId,
            setupFlow: setupFlow,
          );
        },
      ),
      GoRoute(
        path: '/planting-calendar/:farmId',
        builder: (context, state) {
          final farmId = int.tryParse(
            state.pathParameters['farmId'] ?? '',
          );

          final setupFlow =
              state.uri.queryParameters['flow'] == 'setup';

          if (farmId == null) {
            return PlantingCalendarScreen(
              setupFlow: setupFlow,
            );
          }

          return PlantingCalendarScreen(
            farmId: farmId,
            setupFlow: setupFlow,
          );
        },
      ),
      GoRoute(
        path: '/notifications',
        builder: (context, state) =>
            const NotificationScreen(),
      ),
      GoRoute(
        path: '/notification',
        redirect: (context, state) => '/notifications',
      ),
      GoRoute(
        path: '/notif',
        redirect: (context, state) => '/notifications',
      ),
      GoRoute(
        path: '/notifikasi',
        redirect: (context, state) => '/notifications',
      ),
      GoRoute(
        path: '/radar',
        redirect: (context, state) => '/community-alert',
      ),
      GoRoute(
        path: '/alerts',
        redirect: (context, state) => '/community-alert',
      ),
      GoRoute(
        path: '/community-alert',
        builder: (context, state) =>
            const CommunityAlertScreen(),
      ),
      GoRoute(
        path: '/community-alert/report',
        builder: (context, state) {
          final scanId = int.tryParse(
            state.uri.queryParameters['scan_id'] ?? '',
          );

          return ReportConditionScreen(
            scanId: scanId,
          );
        },
      ),
      GoRoute(
        path: '/marketplace',
        builder: (context, state) {
          final isBuyer = ref.watch(isBuyerRoleProvider);

          return _MainTabScaffold(
            currentIndex: isBuyer ? 1 : 3,
            child: const MarketplaceScreen(),
          );
        },
      ),
      GoRoute(
        path: '/cart',
        builder: (context, state) {
          final isBuyer = ref.watch(isBuyerRoleProvider);

          if (isBuyer) {
            return const _MainTabScaffold(
              currentIndex: 2,
              child: CartScreen(),
            );
          }

          return const CartScreen();
        },
      ),
      GoRoute(
        path: '/checkout',
        builder: (context, state) {
          final directItem = state.extra as CartItemModel?;

          return CheckoutScreen(
            directItem: directItem,
          );
        },
      ),
      GoRoute(
        path: '/buyer/orders',
        builder: (context, state) {
          final isBuyer = ref.watch(isBuyerRoleProvider);

          if (isBuyer) {
            return const _MainTabScaffold(
              currentIndex: 3,
              child: BuyerOrdersScreen(),
            );
          }

          return const BuyerOrdersScreen();
        },
      ),
      GoRoute(
        path: '/marketplace/create',
        builder: (context, state) {
          return const CreateMarketListingScreen();
        },
      ),
      GoRoute(
        path: '/marketplace/offers',
        builder: (context, state) {
          return const MarketOffersScreen();
        },
      ),
      GoRoute(
        path: '/sales-report',
        builder: (context, state) {
          return const FarmerSalesReportScreen();
        },
      ),
      GoRoute(
        path: '/faktur/:id',
        builder: (context, state) {
          final id = int.tryParse(
                state.pathParameters['id'] ?? '',
              ) ??
              0;

          final extra = state.extra is PurchaseContractModel
              ? state.extra as PurchaseContractModel
              : null;

          return PurchaseInvoiceScreen(
            contractId: id,
            initialContract: extra,
          );
        },
      ),
      GoRoute(
        path: '/invoice/:id',
        redirect: (context, state) =>
            '/faktur/${state.pathParameters['id']}',
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
            unit: extra['unit']?.toString() ?? 'kg',
            maxQuantity:
                (extra['quantity'] as num?)?.toDouble() ?? 0,
            referencePrice:
                (extra['pricePerUnit'] as num?)?.toDouble() ??
                    0,
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
        builder: (context, state) =>
            const _MainTabScaffold(
              currentIndex: 4,
              child: ProfileScreen(),
            ),
      ),
      GoRoute(
        path: '/profile/language',
        builder: (context, state) =>
            const LanguageSelectionScreen(),
      ),
      GoRoute(
        path: '/language-selection',
        builder: (context, state) =>
            const LanguageSelectionScreen(),
      ),
      GoRoute(
        path: '/profile/password',
        builder: (context, state) =>
            const ChangePasswordScreen(),
      ),
      GoRoute(
        path: '/profile/change-password',
        builder: (context, state) =>
            const ChangePasswordScreen(),
      ),
      GoRoute(
        path: '/map/calendar',
        builder: (context, state) =>
            const PlantingCalendarMapPage(),
      ),
      GoRoute(
        path: '/events',
        builder: (context, state) =>
            const EventListScreen(),
      ),
      GoRoute(
        path: '/events/detail',
        builder: (context, state) {
          final event = state.extra as EventModel?;

          if (event != null) {
            return EventDetailScreen(event: event);
          }

          final events = ref.read(eventsProvider);

          if (events.isNotEmpty) {
            return EventDetailScreen(
              event: events.first,
            );
          }

          return const EventListScreen();
        },
      ),
      GoRoute(
        path: '/events/:id',
        builder: (context, state) {
          final extraEvent =
              state.extra as EventModel?;

          if (extraEvent != null) {
            return EventDetailScreen(
              event: extraEvent,
            );
          }

          final id = int.tryParse(
            state.pathParameters['id'] ?? '',
          );

          final events = ref.read(eventsProvider);

          final event = events.firstWhere(
            (e) => e.id == id,
            orElse: () => events.isNotEmpty
                ? events.first
                : EventModel(
                    id: id ?? 1,
                    title: 'Acara Pertanian P.A.D.I.',
                    description:
                        'Informasi dan agenda pelatihan pertanian.',
                    category: 'workshop',
                    eventDate: DateTime.now().add(
                      const Duration(days: 3),
                    ),
                    eventTime: '08:30 - 12:00 WIB',
                    locationName:
                        'Balai Pertanian Indramayu',
                    organizer: 'Dinas Pertanian',
                    quota: 50,
                    registeredCount: 20,
                    assetImage:
                        'assets/images/onboarding_1.jpeg',
                  ),
          );

          return EventDetailScreen(
            event: event,
          );
        },
      ),
      GoRoute(
        path: '/events/create',
        builder: (context, state) =>
            const CreateEventScreen(),
      ),
    ],
    redirect: (context, state) {
      final location = state.matchedLocation;

      final isPasswordResetRoute =
          location == '/forgot-password' ||
          location == '/reset-password' ||
          location == '/reset-password/new';

      final isAuthRoute =
          location == '/login' ||
          location == '/register' ||
          location == '/select-role' ||
          isPasswordResetRoute;

      if (auth.isChecking) {
        return location == '/splash' ? null : '/splash';
      }

      if (auth.isAuthenticated) {
        if (isPasswordResetRoute) {
          return null;
        }

        if (location == '/register') {
          return auth.state.user?.role == 'farmer'
              ? '/farms/add?flow=setup'
              : '/home';
        }

        if (location == '/splash' ||
            location == '/onboarding' ||
            location == '/login' ||
            location == '/select-role') {
          return '/home';
        }

        if (location == '/admin' &&
            auth.state.user?.role != 'admin') {
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

class _MainTabScaffold extends ConsumerWidget {
  const _MainTabScaffold({
    required this.currentIndex,
    required this.child,
  });

  static const double _navReservedHeight = 106;

  final int currentIndex;
  final Widget child;

  void _onTabSelected(
    BuildContext context,
    WidgetRef ref,
    int index,
  ) {
    final isBuyer = ref.read(isBuyerRoleProvider);

    final route = isBuyer
        ? switch (index) {
            0 => '/home',
            1 => '/marketplace',
            2 => '/cart',
            3 => '/buyer/orders',
            4 => '/profile',
            _ => '/home',
          }
        : switch (index) {
            0 => '/home',
            1 => '/farms',
            2 => '/plant-check',
            3 => '/marketplace',
            4 => '/profile',
            _ => '/home',
          };

    if (index == currentIndex) {
      return;
    }

    context.go(route);
  }

  @override
  Widget build(
    BuildContext context,
    WidgetRef ref,
  ) {
    return Scaffold(
      extendBody: true,
      body: Stack(
        children: [
          Positioned.fill(
            bottom: _navReservedHeight,
            child: child,
          ),
          Positioned(
            left: 0,
            right: 0,
            bottom: 0,
            child: HomeBottomNavigation(
              currentIndex: currentIndex,
              onTap: (index) => _onTabSelected(
                context,
                ref,
                index,
              ),
            ),
          ),
        ],
      ),
    );
  }
}
