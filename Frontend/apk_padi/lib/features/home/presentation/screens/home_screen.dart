import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import 'package:padi/core/localization/app_language.dart';
import 'package:padi/core/providers/app_providers.dart';
import 'package:padi/features/cultivation/data/models/crop_season_model.dart';
import 'package:padi/features/farm/data/models/farm_model.dart';
import 'package:padi/features/home/presentation/screens/buyer_home_screen.dart';
import 'package:padi/features/home/presentation/tokens/senior_tokens.dart';
import 'package:padi/features/home/presentation/widgets/community_alert_card.dart'
    show AlertSeverity;
import 'package:padi/features/home/presentation/widgets/daily_priority_section.dart'
    show DailyPriorityItem;
import 'package:padi/features/home/presentation/widgets/home_skeleton.dart';
import 'package:padi/features/home/presentation/widgets/senior/senior_daily_priority_card.dart';
import 'package:padi/features/home/presentation/widgets/senior/senior_farm_hero_card.dart';
import 'package:padi/features/home/presentation/widgets/senior/senior_harvest_cta_card.dart';
import 'package:padi/features/home/presentation/widgets/senior/senior_home_header.dart';
import 'package:padi/features/home/presentation/widgets/senior/senior_quick_actions.dart';
import 'package:padi/features/home/presentation/widgets/senior/senior_recent_activities_card.dart';
import 'package:padi/features/home/presentation/widgets/senior/senior_warning_banner.dart';
import 'package:padi/features/home/presentation/widgets/senior/senior_weather_card.dart';
import 'dart:math' as math;

// --- Daily Priority Family Provider ---
final _dailyPriorityFamilyProvider =
    FutureProvider.family<
      ({int? hst, List<DailyPriorityItem> priorities}),
      int?
    >((ref, farmId) async {
      if (farmId == null || farmId <= 0) {
        return (hst: null, priorities: <DailyPriorityItem>[]);
      }
      final apiClient = ref.read(apiClientProvider);
      try {
        final res = await apiClient.dio.get('/farms/$farmId/daily-priority');
        final data = res.data?['data'] as Map<String, dynamic>? ?? {};
        final rawList = data['priorities'] as List? ?? [];
        final hst = (data['hst'] as num?)?.toInt();
        final list = rawList
            .whereType<Map>()
            .map(
              (e) => DailyPriorityItem.fromJson(Map<String, dynamic>.from(e)),
            )
            .toList();
        return (hst: hst, priorities: list);
      } catch (_) {
        return (hst: null, priorities: <DailyPriorityItem>[]);
      }
    });

// --- Global Data Provider for Smart Home Dashboard ---
final _homeDashboardProvider = FutureProvider.autoDispose<_HomeDashboardData>((
  ref,
) async {
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

  dynamic broadcastsResponse;
  try {
    final res = await apiClient.dio.get('/admin-broadcasts');
    broadcastsResponse = res.data;
  } catch (_) {}

  try {
    final res = await apiClient.dio.get('/market-listings');
    listingsResponse = res.data;
  } catch (_) {}

  final farms = _parseFarms(farmsResponse);
  final seasons = _parseSeasons(seasonsResponse);
  final activities = _parseActivities(activitiesResponse);
  final alertData = _parseLatestAlert(
    reportsResponse,
    broadcastsResponse,
    farms,
  );
  final marketPrices = _parseMarketPrices(listingsResponse);

  return _HomeDashboardData(
    farms: farms,
    seasons: seasons,
    activities: activities,
    alertTitle: alertData.title,
    alertSubtitle: alertData.subtitle,
    alertSeverity: alertData.severity,
    nearbyDiseaseName: alertData.nearbyDiseaseName,
    nearbyDiseaseDistanceKm: alertData.nearbyDistanceKm,
    nearbyLocation: alertData.nearbyLocation,
    nearbyDiseaseAdvice: alertData.nearbyAdvice,
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

List<dynamic> _parseActivities(dynamic response) {
  if (response is! Map) return const [];
  final data = response['data'];
  if (data is List) return data;
  return const [];
}

class _AlertData {
  const _AlertData({
    this.title,
    this.subtitle,
    required this.severity,
    this.nearbyDiseaseName,
    this.nearbyDistanceKm,
    this.nearbyLocation,
    this.nearbyAdvice,
  });

  final String? title;
  final String? subtitle;
  final AlertSeverity severity;
  final String? nearbyDiseaseName;
  final double? nearbyDistanceKm;
  final String? nearbyLocation;
  final String? nearbyAdvice;
}

_AlertData _parseLatestAlert(
  dynamic reportsResponse,
  dynamic broadcastsResponse,
  List<FarmModel> farms,
) {
  if (reportsResponse is Map &&
      reportsResponse['data'] is List &&
      (reportsResponse['data'] as List).isNotEmpty) {
    final list = (reportsResponse['data'] as List).whereType<Map>().where((
      item,
    ) {
      final status = item['status']?.toString().toLowerCase() ?? '';
      return status != 'rejected' && status != 'resolved';
    }).toList();
    if (list.isNotEmpty) {
      final first = list.first;
      final diseaseName =
          first['disease_name']?.toString() ??
          first['pest_name']?.toString() ??
          first['title']?.toString() ??
          'Laporan penyakit tanaman';

      final radius = (first['radius_km'] as num?)?.toDouble();
      final repLat = (first['latitude'] as num?)?.toDouble();
      final repLon = (first['longitude'] as num?)?.toDouble();

      double? distance = radius;
      String? location =
          first['location_name']?.toString() ??
          first['village_name']?.toString();

      if (farms.isNotEmpty && repLat != null && repLon != null) {
        double minDistance = double.infinity;
        for (final f in farms) {
          if (f.latitude != 0 && f.longitude != 0) {
            final d = _calculateDistanceKm(
              f.latitude,
              f.longitude,
              repLat,
              repLon,
            );
            if (d < minDistance) {
              minDistance = d;
              location ??= f.name;
            }
          }
        }
        if (minDistance.isFinite) {
          distance = minDistance;
        }
      }

      location ??= farms.isNotEmpty ? farms.first.name : null;
      final status = first['status']?.toString().toLowerCase() ?? 'verified';
      final severity =
          (status == 'verified' || (distance != null && distance <= 3.0))
          ? AlertSeverity.high
          : AlertSeverity.medium;

      return _AlertData(
        title: '$diseaseName Terdeteksi',
        subtitle: distance != null
            ? 'Laporan aktif ${distance.toStringAsFixed(1)} km dari lahan${location != null ? ' di sekitar $location' : ''}.'
            : 'Laporan aktif dari area sekitar lahan Anda.',
        severity: severity,
        nearbyDiseaseName: diseaseName,
        nearbyDistanceKm: distance,
        nearbyLocation: location,
        nearbyAdvice:
            'Periksa daun pada petak terdekat dan cocokkan gejala sebelum melakukan tindakan pengendalian.',
      );
    }
  }

  if (broadcastsResponse is Map &&
      broadcastsResponse['data'] is List &&
      (broadcastsResponse['data'] as List).isNotEmpty) {
    final bList = (broadcastsResponse['data'] as List)
        .whereType<Map>()
        .toList();
    if (bList.isNotEmpty) {
      final active = bList.firstWhere(
        (b) => b['type'] == 'danger' || b['type'] == 'warning',
        orElse: () => bList.first,
      );
      final title =
          active['title']?.toString() ?? 'Peringatan Siaga Hama & Penyakit';
      final message =
          active['message']?.toString() ??
          'Waspadai potensi penyebaran serangan hama di hamparan sawah sekitar.';
      final isDanger = active['type'] == 'danger';

      return _AlertData(
        title: title,
        subtitle: message,
        severity: isDanger ? AlertSeverity.high : AlertSeverity.medium,
        nearbyAdvice: message,
      );
    }
  }

  return _AlertData(
    title: farms.isEmpty
        ? 'Belum ada lahan untuk dipantau'
        : 'Belum ada laporan penyakit sekitar',
    subtitle: farms.isEmpty
        ? 'Tambahkan lahan agar laporan komunitas dapat dihitung berdasarkan lokasi sawah.'
        : 'Tidak ada laporan aktif dari komunitas di sekitar lahan yang tersimpan.',
    severity: AlertSeverity.low,
  );
}

double _calculateDistanceKm(
  double lat1,
  double lon1,
  double lat2,
  double lon2,
) {
  const p = 0.017453292519943295;
  final a =
      0.5 -
      math.cos((lat2 - lat1) * p) / 2 +
      math.cos(lat1 * p) *
          math.cos(lat2 * p) *
          (1 - math.cos((lon2 - lon1) * p)) /
          2;
  return 12742 * math.asin(math.sqrt(a));
}

(String?, String?) _parseMarketPrices(dynamic response) {
  final currencyFmt = NumberFormat.currency(
    locale: 'id_ID',
    symbol: 'Rp ',
    decimalDigits: 0,
  );

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
        return (
          currencyFmt.format(avgGkp.round()),
          currencyFmt.format(avgGkg.round()),
        );
      }
    }
  }

  return (null, null);
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
    this.nearbyDiseaseName,
    this.nearbyDiseaseDistanceKm,
    this.nearbyLocation,
    this.nearbyDiseaseAdvice,
    this.gkpPrice,
    this.gkgPrice,
  });

  final List<FarmModel> farms;
  final List<CropSeasonModel> seasons;
  final List<dynamic> activities;
  final String? alertTitle;
  final String? alertSubtitle;
  final AlertSeverity alertSeverity;
  final String? nearbyDiseaseName;
  final double? nearbyDiseaseDistanceKm;
  final String? nearbyLocation;
  final String? nearbyDiseaseAdvice;
  final String? gkpPrice;
  final String? gkgPrice;
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
    final isBuyer = ref.watch(isBuyerRoleProvider);
    if (isBuyer) {
      return const BuyerHomeScreen();
    }
    final rawName = user?.name.trim();
    final userName = rawName != null && rawName.isNotEmpty
        ? rawName
        : s.defaultUserName;

    return Scaffold(
      backgroundColor: SeniorColors.background,
      body: SafeArea(
        bottom: false,
        child: RefreshIndicator(
          color: SeniorColors.primaryGreen,
          backgroundColor: SeniorColors.surface,
          onRefresh: _handleRefresh,
          child: Center(
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 540),
              child: ListView(
                physics: const AlwaysScrollableScrollPhysics(
                  parent: BouncingScrollPhysics(),
                ),
                padding: const EdgeInsets.symmetric(
                  horizontal: SeniorSpacing.screenHorizontal,
                  vertical: SeniorSpacing.xs,
                ),
                children: [
                  // A. Top App Bar Header Ramah Lansia
                  SeniorHomeHeader(
                    name: userName,
                    onNotificationTap: () => context.push('/notifications'),
                  ),

                  const SizedBox(height: SeniorSpacing.sectionGap),

                  // PPL Verification Desk Banner (for Extension Officers)
                  if (user?.role == 'extension_officer') ...[
                    InkWell(
                      onTap: () => context.push('/ppl-cases'),
                      borderRadius: BorderRadius.circular(
                        SeniorDimensions.buttonRadius,
                      ),
                      child: Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 18,
                          vertical: 16,
                        ),
                        decoration: BoxDecoration(
                          color: SeniorColors.primaryGreen,
                          borderRadius: BorderRadius.circular(
                            SeniorDimensions.buttonRadius,
                          ),
                          boxShadow: [
                            BoxShadow(
                              color: SeniorColors.primaryGreen.withValues(
                                alpha: 0.18,
                              ),
                              blurRadius: 10,
                              offset: const Offset(0, 3),
                            ),
                          ],
                        ),
                        child: Row(
                          children: [
                            Container(
                              padding: const EdgeInsets.all(10),
                              decoration: const BoxDecoration(
                                color: Colors.white,
                                shape: BoxShape.circle,
                              ),
                              child: const Icon(
                                Icons.assignment_turned_in_rounded,
                                color: SeniorColors.primaryGreen,
                                size: 24,
                              ),
                            ),
                            const SizedBox(width: 14),
                            const Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    'Meja Validasi Kasus PPL',
                                    style: TextStyle(
                                      fontSize: 17,
                                      fontWeight: FontWeight.w800,
                                      color: Colors.white,
                                    ),
                                  ),
                                  SizedBox(height: 2),
                                  Text(
                                    'Buka antrean diagnosa daun petani untuk validasi',
                                    style: TextStyle(
                                      fontSize: 15,
                                      color: SeniorColors.textOnDarkMuted,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                            const Icon(
                              Icons.chevron_right_rounded,
                              color: Colors.white,
                              size: 26,
                            ),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(height: SeniorSpacing.sectionGap),
                  ],

                  // Dashboard Content with State Management
                  dashboardAsync.when(
                    data: (data) => _buildDashboardContent(data, s),
                    loading: () => const HomeSkeleton(),
                    error: (error, stack) => _buildErrorFallback(s),
                  ),

                  const SizedBox(height: SeniorSpacing.xxl),
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
        : (selectedFarm?.name.isNotEmpty == true ? selectedFarm!.name : '');

    final dailyPriorityAsync = ref.watch(
      _dailyPriorityFamilyProvider(selectedFarm?.id),
    );

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        // 1. Peringatan Penyakit Sekitar (Jika Ada Laporan Terdeteksi)
        if (data.nearbyDiseaseName != null) ...[
          SeniorWarningBanner(
            diseaseName: data.nearbyDiseaseName!,
            distanceKm: data.nearbyDiseaseDistanceKm,
            locationName: data.nearbyLocation,
            farmerAdvice: data.nearbyDiseaseAdvice,
            onScanTap: () => context.push('/plant-check'),
          ),
          const SizedBox(height: SeniorSpacing.sectionGap),
        ],

        // 2. Kartu Lahan Utama & Tombol Raksasa Periksa Tanaman
        SeniorFarmHeroCard(
          farms: data.farms,
          seasons: data.seasons,
          selectedIndex: farmIndex,
          onFarmIndexChanged: (index) {
            setState(() => _selectedFarmIndex = index);
          },
          onFarmTap: (farm) => context.go('/farms'),
          onAddFarmTap: () => context.push('/farms/add'),
          onScanTap: () => context.push('/plant-check'),
        ),

        const SizedBox(height: SeniorSpacing.sectionGap),

        // 3. 4 Menu Aksi Utama Petani (Grid 2x2 Kartu Besar)
        SeniorQuickActions(
          onScanTap: () => context.push('/plant-check'),
          onActivityTap: () => context.push('/land/activity/add'),
          onMarketTap: () => context.push('/marketplace'),
          onCalendarTap: () => context.push('/planting-calendar'),
        ),

        const SizedBox(height: SeniorSpacing.sectionGap),

        // 4. Kartu Cuaca & Anjuran Kerja Tani (Ikon Besar 48px, Suhu 32px, Audio TTS)
        SeniorWeatherCard(
          locationName: weatherLocation,
          farmId: selectedFarm?.id,
          onTapCalendar: () => context.push('/planting-calendar'),
        ),

        const SizedBox(height: SeniorSpacing.sectionGap),

        // 5. Tugas Utama Hari Ini (Maksimal 2 Prioritas Teks Besar)
        dailyPriorityAsync.when(
          data: (pData) => SeniorDailyPriorityCard(
            priorities: pData.priorities,
            hst: pData.hst,
            farmName: selectedFarm?.name,
          ),
          loading: () =>
              const SeniorDailyPriorityCard(priorities: [], isLoading: true),
          error: (_, _) => const SizedBox.shrink(),
        ),

        const SizedBox(height: SeniorSpacing.sectionGap),

        // 6. Catatan Kegiatan Terakhir (Maksimal 3 + Tombol Tambah 56px)
        SeniorRecentActivitiesCard(
          activities: data.activities,
          onAddActivity: () => context.push('/land/activity/add'),
          onViewAll: () => context.push('/land/timeline'),
        ),

        // 7. Ajakan Jual Hasil Panen (Hanya jika mendekati panen <= 14 hari atau belum ada lahan)
        if (isNearHarvest || !hasFarms) ...[
          const SizedBox(height: SeniorSpacing.sectionGap),
          SeniorHarvestCtaCard(
            onTapMarketplace: () => context.push('/marketplace'),
            gkpPrice: data.gkpPrice,
            gkgPrice: data.gkgPrice,
          ),
        ],
      ],
    );
  }

  Widget _buildErrorFallback(AppStrings s) {
    return Container(
      padding: const EdgeInsets.all(SeniorSpacing.cardPadding),
      decoration: BoxDecoration(
        color: SeniorColors.surface,
        borderRadius: BorderRadius.circular(SeniorDimensions.cardRadius),
        border: Border.all(color: SeniorColors.border, width: 2),
      ),
      child: Column(
        children: [
          const Icon(
            Icons.cloud_off_rounded,
            color: SeniorColors.textSecondary,
            size: 44,
          ),
          const SizedBox(height: SeniorSpacing.md),
          Text(
            s.friendlyErrorMessage,
            style: SeniorTypography.subtitle,
            textAlign: TextAlign.center,
          ),
          const SizedBox(height: SeniorSpacing.lg),
          SizedBox(
            height: SeniorDimensions.buttonHeight,
            child: ElevatedButton.icon(
              onPressed: () => ref.refresh(_homeDashboardProvider.future),
              icon: const Icon(Icons.refresh_rounded, size: 24),
              label: Text(s.tryAgain, style: SeniorTypography.button),
              style: ElevatedButton.styleFrom(
                backgroundColor: SeniorColors.primaryGreen,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(
                    SeniorDimensions.buttonRadius,
                  ),
                ),
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
