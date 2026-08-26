import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import 'package:padi/core/localization/app_language.dart';
import 'package:padi/core/providers/app_providers.dart';
import 'package:padi/features/cultivation/data/models/crop_season_model.dart';
import 'package:padi/features/farm/data/models/farm_model.dart';
import 'package:padi/features/home/presentation/tokens/home_tokens.dart';
import 'package:padi/features/home/presentation/widgets/community_alert_card.dart';
import 'package:padi/features/home/presentation/widgets/crop_journey_card.dart';
import 'package:padi/features/home/presentation/widgets/farm_hero_card.dart';
import 'package:padi/features/home/presentation/widgets/harvest_marketplace_cta.dart';
import 'package:padi/features/home/presentation/widgets/home_header.dart';
import 'package:padi/features/home/presentation/widgets/home_skeleton.dart';
import 'package:padi/features/home/presentation/widgets/market_price_card.dart';
import 'package:padi/features/home/presentation/widgets/quick_action_grid.dart';
import 'package:padi/features/home/presentation/widgets/smart_insight_card.dart';
import 'package:padi/features/home/presentation/widgets/today_activity_section.dart';
import 'package:padi/features/home/presentation/widgets/upcoming_events_banner.dart';
import 'package:padi/features/home/presentation/widgets/weather_card.dart';

// --- Global Data Provider for Smart Home Dashboard ---
final _homeDashboardProvider = FutureProvider.autoDispose<_HomeDashboardData>((ref) async {
  final apiClient = ref.read(apiClientProvider);

  dynamic farmsResponse;
  dynamic seasonsResponse;
  dynamic activitiesResponse;
  dynamic reportsResponse;
  dynamic listingsResponse;

  try {
    final res = await apiClient.dio.get('/farms');
    farmsResponse = res.data;
  } catch (_) {}

  try {
    final res = await apiClient.dio.get('/crop-seasons');
    seasonsResponse = res.data;
  } catch (_) {}

  try {
    final res = await apiClient.dio.get('/farm-activities');
    activitiesResponse = res.data;
  } catch (_) {}

  try {
    final res = await apiClient.dio.get('/community-reports');
    reportsResponse = res.data;
  } catch (_) {}

  try {
    final res = await apiClient.dio.get('/market-listings');
    listingsResponse = res.data;
  } catch (_) {}

  final farms = _parseFarms(farmsResponse);
  final seasons = _parseSeasons(seasonsResponse);
  final activities = _parseActivities(activitiesResponse);
  final alertData = _parseLatestAlert(reportsResponse);
  final marketPrices = _parseMarketPrices(listingsResponse);

  return _HomeDashboardData(
    farms: farms,
    seasons: seasons,
    activities: activities,
    alertTitle: alertData.$1,
    alertSubtitle: alertData.$2,
    alertSeverity: alertData.$3,
    gkpPrice: marketPrices.$1,
    gkgPrice: marketPrices.$2,
  );
});

List<FarmModel> _parseFarms(dynamic response) {
  if (response is! Map) return const [];
  final data = response['data'];
  if (data is! List) return const [];

  return data
      .whereType<Map>()
      .map((item) => FarmModel.fromJson(Map<String, dynamic>.from(item)))
      .toList();
}

List<CropSeasonModel> _parseSeasons(dynamic response) {
  if (response is! Map) return const [];
  final data = response['data'];
  if (data is! Map) return const [];
  final items = data['crop_seasons'];
  if (items is! List) return const [];

  return items
      .whereType<Map>()
      .map((item) => CropSeasonModel.fromJson(Map<String, dynamic>.from(item)))
      .toList();
}

CropSeasonModel? _selectCurrentSeason(List<CropSeasonModel> seasons) {
  if (seasons.isEmpty) return null;

  final today = DateTime.now();
  final active = seasons.where((season) {
    final status = season.status?.toLowerCase();
    if (status == 'completed' || status == 'cancelled') {
      return false;
    }

    final start = season.startDate;
    if (start == null) {
      return status == 'active';
    }

    final harvest = _parseDate(season.estimatedHarvestDate) ??
        start.add(const Duration(days: 109));

    return !today.isBefore(start) &&
        !today.isAfter(harvest.add(const Duration(days: 7)));
  }).toList();

  final candidates = active.isNotEmpty ? active : seasons;

  candidates.sort((a, b) {
    final aStart = a.startDate ?? DateTime(1900);
    final bStart = b.startDate ?? DateTime(1900);
    return bStart.compareTo(aStart);
  });

  return candidates.first;
}

bool _isNearHarvest(CropSeasonModel? season) {
  if (season == null) return false;

  final start = season.startDate;
  if (start == null) return false;

  final harvest = _parseDate(season.estimatedHarvestDate) ??
      start.add(const Duration(days: 109));
  final today = DateTime.now();
  final daysUntilHarvest = harvest.difference(today).inDays;

  return daysUntilHarvest <= 21 && daysUntilHarvest >= -7;
}

DateTime? _parseDate(String? value) {
  if (value == null || value.trim().isEmpty) return null;
  return DateTime.tryParse(value.trim());
}

List<dynamic> _parseActivities(dynamic response) {
  if (response is! Map) return const [];
  final data = response['data'];
  if (data is List) return data;
  return const [];
}

(String, String, AlertSeverity) _parseLatestAlert(dynamic response) {
  if (response is Map &&
      response['data'] is List &&
      (response['data'] as List).isNotEmpty) {
    final first = (response['data'] as List).first;
    if (first is Map) {
      final pestName = first['pest_name']?.toString() ??
          first['title']?.toString() ??
          'Wereng Batang Cokelat';
      final location = first['location_name']?.toString() ??
          first['village_name']?.toString() ??
          'Kecamatan Tetangga';
      final severityStr = first['severity']?.toString().toLowerCase() ?? '';

      final severity =
          severityStr.contains('high') || severityStr.contains('critical')
          ? AlertSeverity.high
          : severityStr.contains('low')
              ? AlertSeverity.low
              : AlertSeverity.medium;

      return (
        '$pestName Terdeteksi',
        'Laporan terkonfirmasi di sekitar $location.',
        severity,
      );
    }
  }

  return (
    'Wereng Batang Cokelat Terdeteksi',
    '3 laporan terkonfirmasi dari kelompok tani tetangga dalam 24 jam.',
    AlertSeverity.medium,
  );
}

(String, String) _parseMarketPrices(dynamic response) {
  final currencyFmt = NumberFormat.currency(locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0);

  if (response is Map) {
    final list = _extractMarketItems(response).whereType<Map>().toList();
    if (list.isNotEmpty) {
      double totalGkp = 0;
      int countGkp = 0;
      for (final item in list) {
        final price =
            double.tryParse(item['price_per_unit']?.toString() ?? '0') ?? 0;
        if (price > 1000) {
          totalGkp += price;
          countGkp++;
        }
      }
      if (countGkp > 0) {
        final avgGkp = totalGkp / countGkp;
        final avgGkg = avgGkp * 1.09;
        return (currencyFmt.format(avgGkp.round()), currencyFmt.format(avgGkg.round()));
      }
    }
  }

  return ('Rp 6.800', 'Rp 7.400');
}

List<dynamic> _extractMarketItems(Map<dynamic, dynamic> response) {
  final data = response['data'];

  if (data is List) {
    return data;
  }

  if (data is Map) {
    for (final key in ['data', 'market_listings', 'listings', 'items']) {
      final nested = data[key];
      if (nested is List) {
        return nested;
      }
    }
  }

  for (final key in ['market_listings', 'listings', 'items']) {
    final nested = response[key];
    if (nested is List) {
      return nested;
    }
  }

  return const [];
}

class _HomeDashboardData {
  const _HomeDashboardData({
    required this.farms,
    required this.seasons,
    required this.activities,
    required this.alertTitle,
    required this.alertSubtitle,
    required this.alertSeverity,
    required this.gkpPrice,
    required this.gkgPrice,
  });

  final List<FarmModel> farms;
  final List<CropSeasonModel> seasons;
  final List<dynamic> activities;
  final String alertTitle;
  final String alertSubtitle;
  final AlertSeverity alertSeverity;
  final String gkpPrice;
  final String gkgPrice;
}

// --- Main Home Screen ---
class HomeScreen extends ConsumerStatefulWidget {
  const HomeScreen({super.key});

  @override
  ConsumerState<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends ConsumerState<HomeScreen> {
  int _selectedFarmIndex = 0;

  Future<void> _handleRefresh() async {
    try {
      await Future.wait([
        ref.refresh(_homeDashboardProvider.future),
        ref.read(authControllerProvider).restoreSession(),
      ]);
    } catch (_) {}
  }

  @override
  Widget build(BuildContext context) {
    final auth = ref.watch(authControllerProvider);
    final lang = ref.watch(languageProvider);
    final s = AppStrings(lang);
    final dashboardAsync = ref.watch(_homeDashboardProvider);
    final user = auth.state.user;
    final rawName = user?.name.trim();
    final userName = rawName != null && rawName.isNotEmpty ? rawName : s.defaultUserName;

    return Scaffold(
      backgroundColor: HomeColors.background,
      body: SafeArea(
        bottom: false,
        child: RefreshIndicator(
          color: HomeColors.primaryGreen,
          backgroundColor: HomeColors.surface,
          onRefresh: _handleRefresh,
          child: Center(
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 580),
              child: ListView(
                physics: const AlwaysScrollableScrollPhysics(
                  parent: BouncingScrollPhysics(),
                ),
                padding: const EdgeInsets.symmetric(
                  horizontal: HomeSpacing.screenHorizontal,
                  vertical: HomeSpacing.xs,
                ),
                children: [
                  // A. Top App Bar Header
                  HomeHeader(
                    name: userName,
                    onNotificationTap: () => context.push('/notifications'),
                  ),

                  const SizedBox(height: HomeSpacing.md),

                  // Dashboard Content with State Management
                  dashboardAsync.when(
                    data: (data) => _buildDashboardContent(data, s),
                    loading: () => const HomeSkeleton(),
                    error: (error, stack) => _buildErrorFallback(s),
                  ),

                  const SizedBox(height: HomeSpacing.xxxl),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildDashboardContent(_HomeDashboardData data, AppStrings s) {
    final hasFarms = data.farms.isNotEmpty;
    final farmIndex = _selectedFarmIndex.clamp(
      0,
      data.farms.isNotEmpty ? data.farms.length - 1 : 0,
    );
    final selectedFarm = hasFarms ? data.farms[farmIndex] : null;

    final activeSeason = _selectSeason(data.seasons, selectedFarm?.id);
    final isNearHarvest = _isNearHarvest(activeSeason);

    final districtName = selectedFarm?.district?.name;
    final regencyName = selectedFarm?.regency?.name;
    final weatherLocation = districtName != null && districtName.isNotEmpty
        ? (regencyName != null && regencyName.isNotEmpty
            ? '$districtName, $regencyName'
            : districtName)
        : (selectedFarm?.name.isNotEmpty == true
            ? selectedFarm!.name
            : 'Indramayu, Jawa Barat');

    final farmName = selectedFarm?.name ?? 'Lahan';
    final insightTitle = hasFarms
        ? switch (s.lang) {
            AppLanguage.id => 'Pemeriksaan Daun $farmName',
            AppLanguage.jv => 'Priksa Godhong $farmName',
            AppLanguage.en => 'Leaf Scan $farmName',
          }
        : switch (s.lang) {
            AppLanguage.id => 'Mulai Pantau Kesehatan Tanaman',
            AppLanguage.jv => 'Mulai Pantau Kasarasan Tanduran',
            AppLanguage.en => 'Start Monitoring Crop Health',
          };

    final insightDesc = hasFarms
        ? switch (s.lang) {
            AppLanguage.id =>
              'Perubahan kelembaban sore berpotensi memicu bercak daun pada $farmName. Lakukan foto sampel daun.',
            AppLanguage.jv =>
              'Owahan hawa sore isa marakake penyakit godhong. Foto godhong kanggo priksa.',
            AppLanguage.en =>
              'Humidity changes may trigger leaf spots. Take a leaf sample photo.',
          }
        : switch (s.lang) {
            AppLanguage.id =>
              'Ambil foto daun padi Anda untuk diagnosa instan berbasis kecerdasan buatan.',
            AppLanguage.jv =>
              'Jupuk foto godhong pari panjenengan kanggo priksa nganggo AI.',
            AppLanguage.en =>
              'Take a photo of your rice leaf for instant AI-powered crop diagnosis.',
          };

    final insightAction = switch (s.lang) {
      AppLanguage.id => 'Periksa Tanaman Sekarang',
      AppLanguage.jv => 'Priksa Tanduran Saiki',
      AppLanguage.en => 'Check Crops Now',
    };

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        // B. Smart Farm Hero Overview Card
        FarmHeroCard(
          farms: data.farms,
          seasons: data.seasons,
          selectedIndex: farmIndex,
          onFarmIndexChanged: (index) {
            setState(() => _selectedFarmIndex = index);
          },
          onFarmTap: (farm) => context.go('/farms'),
          onAddFarmTap: () => context.push('/farms/add'),
        ),

        const SizedBox(height: HomeSpacing.md),

        // C. Smart Contextual Insight (Single Priority Attention)
        SmartInsightCard(
          title: insightTitle,
          description: insightDesc,
          actionLabel: insightAction,
          onActionTap: () => context.push('/plant-check'),
        ),

        const SizedBox(height: HomeSpacing.md),

        // D. Modern Weather & Agroklimat Card
        WeatherCard(
          locationName: weatherLocation,
          onTapCalendar: () => context.push('/planting-calendar'),
        ),

        const SizedBox(height: HomeSpacing.lg),

        // E. Curated Super-App Quick Actions Grid (8 Core Tools)
        QuickActionGrid(
          onScanTap: () => context.push('/plant-check'),
          onActivityTap: () => context.push('/land/activity/add'),
          onFarmTap: () => context.go('/farms'),
          onMarketTap: () => context.push('/marketplace'),
          onFertilizerTap: () => context.push('/fertilizer'),
          onCalendarTap: () => context.push('/planting-calendar'),
          onAlertTap: () => context.push('/community-alert'),
          onTimelineTap: () => context.push('/land/timeline'),
        ),

        const SizedBox(height: HomeSpacing.lg),

        // F. Today's Farm Activity List
        TodayActivitySection(
          activities: data.activities,
          onAddActivity: () => context.push('/land/activity/add'),
          onViewTimeline: () => context.push('/land/timeline'),
        ),

        const SizedBox(height: HomeSpacing.lg),

        // G. Upcoming Agriculture Events Banner Carousel
        UpcomingEventsBanner(
          onEventTap: (event) => context.push('/events/detail', extra: event),
          onCreateEventTap: () => context.push('/events/create'),
          onViewAllTap: () => context.push('/events'),
        ),

        const SizedBox(height: HomeSpacing.lg),

        // H. Crop Journey Lifecycle
        if (hasFarms) ...[
          CropJourneyCard(
            season: activeSeason,
            farms: data.farms,
            selectedFarm: selectedFarm,
            onSelectFarm: (farm) {
              final idx = data.farms.indexOf(farm);
              if (idx != -1) {
                setState(() => _selectedFarmIndex = idx);
              }
            },
            onTapTimeline: () => context.push('/land/timeline'),
          ),
          const SizedBox(height: HomeSpacing.lg),
        ],

        // I. Harvest & Marketplace CTA (Prioritized if near harvest)
        if (isNearHarvest || !hasFarms) ...[
          HarvestMarketplaceCta(
            onTapMarketplace: () => context.push('/marketplace'),
            onTapCreateListing: () => context.push('/marketplace/create'),
          ),
          const SizedBox(height: HomeSpacing.lg),
        ],

        // J. Community Radar & Pests Alert (Dynamic from backend reports)
        CommunityAlertCard(
          title: data.alertTitle,
          subtitle: data.alertSubtitle,
          distanceKm: 3.2,
          severity: data.alertSeverity,
          onTapAlerts: () => context.push('/community-alert'),
        ),

        const SizedBox(height: HomeSpacing.lg),

        // K. Compact Commodity Market Price Index (Dynamic from marketplace)
        MarketPriceCard(
          onTapMarket: () => context.push('/marketplace'),
          gkpPrice: data.gkpPrice,
          gkgPrice: data.gkgPrice,
        ),
      ],
    );
  }

  Widget _buildErrorFallback(AppStrings s) {
    return Container(
      padding: const EdgeInsets.all(HomeSpacing.cardPadding),
      decoration: BoxDecoration(
        color: HomeColors.surface,
        borderRadius: BorderRadius.circular(HomeRadius.xl),
        border: Border.all(color: HomeColors.border),
      ),
      child: Column(
        children: [
          const Icon(
            Icons.cloud_off_rounded,
            color: HomeColors.textSecondary,
            size: 36,
          ),
          const SizedBox(height: HomeSpacing.xs),
          Text(
            s.friendlyErrorMessage,
            style: HomeTypography.cardTitle,
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: HomeSpacing.md),
          FilledButton.icon(
            onPressed: () => ref.refresh(_homeDashboardProvider.future),
            icon: const Icon(Icons.refresh_rounded, size: 16),
            label: Text(s.tryAgain),
            style: FilledButton.styleFrom(
              backgroundColor: HomeColors.primaryGreen,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(HomeRadius.sm),
              ),
            ),
          ),
        ],
      ),
    );
  }

  CropSeasonModel? _selectSeason(List<CropSeasonModel> seasons, int? farmId) {
    if (seasons.isEmpty) return null;
    if (farmId != null) {
      for (final s in seasons) {
        if (s.farmId == farmId && s.status == 'active') return s;
      }
      for (final s in seasons) {
        if (s.farmId == farmId) return s;
      }
    }
    for (final s in seasons) {
      if (s.status == 'active') return s;
    }
    return seasons.first;
  }

  bool _isNearHarvest(CropSeasonModel? season) {
    if (season == null) return false;
    final harvestDateStr = season.estimatedHarvestDate;
    if (harvestDateStr == null || harvestDateStr.isEmpty) return false;
    final harvestDate = DateTime.tryParse(harvestDateStr);
    if (harvestDate == null) return false;
    final diff = harvestDate.difference(DateTime.now()).inDays;
    return diff <= 14;
  }
}
