import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:padi/core/providers/app_providers.dart';
import 'package:padi/core/utils/debouncer.dart';
import 'package:padi/features/farm/data/models/farm_model.dart';
import 'package:padi/features/farm/data/services/farm_api_service.dart';
import 'package:padi/features/farm/presentation/widgets/farm_card.dart';
import 'package:padi/features/farm/presentation/widgets/farm_map_view.dart';
import 'package:padi/features/farm/presentation/widgets/farm_skeleton.dart';
import 'package:padi/features/farm/presentation/widgets/farm_stats_card.dart';
import 'package:padi/features/home/presentation/tokens/home_tokens.dart';
import 'package:padi/features/planting_calendar/data/services/planting_calendar_api_service.dart';
import 'package:padi/features/planting_calendar/presentation/widgets/planting_calendar_card.dart';

final farmApiServiceProvider = Provider<FarmApiService>(
  (ref) => FarmApiService(ref.read(apiClientProvider)),
);

final userFarmsProvider = FutureProvider.autoDispose<List<FarmModel>>((ref) {
  return ref.read(farmApiServiceProvider).fetchFarms();
});

final farmCalendarApiServiceProvider = Provider<PlantingCalendarApiService>(
  (ref) => PlantingCalendarApiService(ref.read(apiClientProvider)),
);

class FarmListScreen extends ConsumerStatefulWidget {
  const FarmListScreen({super.key});

  @override
  ConsumerState<FarmListScreen> createState() => _FarmListScreenState();
}

class _FarmListScreenState extends ConsumerState<FarmListScreen> {
  final TextEditingController _searchController = TextEditingController();
  final Debouncer _searchDebouncer = Debouncer(milliseconds: 300);
  bool _isMapMode = false;
  FarmModel? _focusedFarm;

  @override
  void dispose() {
    _searchDebouncer.dispose();
    _searchController.dispose();
    super.dispose();
  }

  void _showPlantingCalendar(FarmModel farm) async {
    final calendarApi = ref.read(farmCalendarApiServiceProvider);
    final calendar = await calendarApi.getCalendarForFarm(farm.id);

    if (!mounted) return;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) {
        return Container(
          padding: const EdgeInsets.fromLTRB(18, 12, 18, 24),
          decoration: const BoxDecoration(
            color: HomeColors.surface,
            borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
          ),
          child: SafeArea(
            top: false,
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Center(
                  child: Container(
                    width: 42,
                    height: 4,
                    decoration: BoxDecoration(
                      color: HomeColors.border,
                      borderRadius: BorderRadius.circular(HomeRadius.pill),
                    ),
                  ),
                ),
                const SizedBox(height: HomeSpacing.md),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            farm.name,
                            style: const TextStyle(
                              color: HomeColors.textPrimary,
                              fontSize: 17,
                              fontWeight: FontWeight.w900,
                            ),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            farm.locationDescription,
                            style: HomeTypography.supporting,
                          ),
                        ],
                      ),
                    ),
                    IconButton(
                      onPressed: () => Navigator.pop(context),
                      icon: const Icon(Icons.close_rounded),
                    ),
                  ],
                ),
                const SizedBox(height: HomeSpacing.md),
                if (calendar != null)
                  PlantingCalendarCard(calendar: calendar)
                else
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(
                      color: HomeColors.surfaceMuted,
                      borderRadius: BorderRadius.circular(HomeRadius.lg),
                      border: Border.all(color: HomeColors.borderSubtle),
                    ),
                    child: const Row(
                      children: [
                        Icon(Icons.event_busy_outlined, color: HomeColors.textSecondary, size: 20),
                        SizedBox(width: 10),
                        Expanded(
                          child: Text(
                            'Belum ada rekomendasi kalender tanam aktif untuk lahan ini.',
                            style: TextStyle(
                              color: HomeColors.textSecondary,
                              fontSize: 13,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
              ],
            ),
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    final farmsAsync = ref.watch(userFarmsProvider);

    return Scaffold(
      backgroundColor: HomeColors.background,
      appBar: AppBar(
        backgroundColor: HomeColors.background,
        elevation: 0,
        scrolledUnderElevation: 0,
        leading: IconButton(
          tooltip: 'Kembali',
          icon: const Icon(Icons.arrow_back_rounded, color: HomeColors.textPrimary, size: 24),
          onPressed: () {
            if (context.canPop()) {
              context.pop();
            } else {
              context.go('/home');
            }
          },
        ),
        title: const Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Lahan Pertanian',
              style: TextStyle(
                color: HomeColors.textPrimary,
                fontSize: 18,
                fontWeight: FontWeight.w900,
                letterSpacing: -0.3,
              ),
            ),
            Text(
              'Kelola petak sawah & GIS',
              style: TextStyle(
                color: HomeColors.textSecondary,
                fontSize: 11,
                fontWeight: FontWeight.w600,
              ),
            ),
          ],
        ),
        actions: [
          // Toggle View (Daftar / Peta)
          farmsAsync.maybeWhen(
            data: (farms) => farms.isNotEmpty
                ? IconButton(
                    tooltip: _isMapMode ? 'Buka Mode Daftar' : 'Buka Mode Peta GIS',
                    onPressed: () => setState(() => _isMapMode = !_isMapMode),
                    icon: Icon(
                      _isMapMode ? Icons.view_list_rounded : Icons.map_rounded,
                      color: HomeColors.primaryGreen,
                    ),
                  )
                : const SizedBox.shrink(),
            orElse: () => const SizedBox.shrink(),
          ),
          IconButton(
            tooltip: 'Segarkan data',
            onPressed: () => ref.invalidate(userFarmsProvider),
            icon: const Icon(Icons.refresh_rounded, color: HomeColors.textPrimary),
          ),
          const SizedBox(width: 4),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () async {
          final result = await context.push('/farms/add');
          if (result == true) {
            ref.invalidate(userFarmsProvider);
          }
        },
        backgroundColor: HomeColors.primaryGreen,
        foregroundColor: Colors.white,
        elevation: 4,
        icon: const Icon(Icons.add_location_alt_outlined, size: 20),
        label: const Text(
          'Tambah Lahan',
          style: TextStyle(fontWeight: FontWeight.w800, letterSpacing: -0.2),
        ),
      ),
      body: farmsAsync.when(
        data: (farms) {
          if (farms.isEmpty) {
            return _buildEmptyState();
          }

          if (_isMapMode) {
            return FarmMapView(
              farms: farms,
              initialSelectedFarm: _focusedFarm,
              onTapCalendar: (farm) => _showPlantingCalendar(farm),
              onTapFertilizer: (farm) => context.push('/fertilizer?farmId=${farm.id}'),
              onTapAddActivity: (farm) => context.push('/land/activity/add?farmId=${farm.id}'),
              onCloseMap: () => setState(() => _isMapMode = false),
            );
          }

          return _buildListView(farms);
        },
        loading: () => Center(
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 680),
            child: const Padding(
              padding: EdgeInsets.all(16),
              child: FarmSkeleton(),
            ),
          ),
        ),
        error: (error, stackTrace) => _buildErrorState(),
      ),
    );
  }

  Widget _buildListView(List<FarmModel> farms) {
    final keyword = _searchController.text.trim().toLowerCase();
    final filteredFarms = keyword.isEmpty
        ? farms
        : farms.where((farm) {
            final name = farm.name.toLowerCase();
            final loc = farm.locationDescription.toLowerCase();
            final soil = (farm.soilType ?? '').toLowerCase();
            final irr = farm.irrigationType.toLowerCase();
            return name.contains(keyword) || loc.contains(keyword) || soil.contains(keyword) || irr.contains(keyword);
          }).toList();

    return RefreshIndicator(
      color: HomeColors.primaryGreen,
      backgroundColor: HomeColors.surface,
      onRefresh: () async => ref.invalidate(userFarmsProvider),
      child: CustomScrollView(
        physics: const AlwaysScrollableScrollPhysics(
          parent: BouncingScrollPhysics(),
        ),
        slivers: [
          SliverToBoxAdapter(
            child: Center(
              child: ConstrainedBox(
                constraints: const BoxConstraints(maxWidth: 680),
                child: Padding(
                  padding: const EdgeInsets.fromLTRB(16, 8, 16, 12),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      // 1. Stats Overview Card
                      FarmStatsCard(
                        farms: farms,
                        onTapMap: () => setState(() => _isMapMode = true),
                      ),

                      const SizedBox(height: HomeSpacing.md),

                      // 2. Search Input Box
                      Container(
                        decoration: BoxDecoration(
                          color: HomeColors.surface,
                          borderRadius: BorderRadius.circular(HomeRadius.md),
                          border: Border.all(color: HomeColors.border),
                          boxShadow: HomeShadows.subtle,
                        ),
                        child: TextField(
                          controller: _searchController,
                          onChanged: (_) => _searchDebouncer.run(() => setState(() {})),
                          style: const TextStyle(
                            color: HomeColors.textPrimary,
                            fontSize: 14,
                            fontWeight: FontWeight.w600,
                          ),
                          decoration: InputDecoration(
                            hintText: 'Cari nama lahan, desa, atau jenis tanah...',
                            hintStyle: const TextStyle(
                              color: HomeColors.textSecondary,
                              fontSize: 13,
                            ),
                            prefixIcon: const Icon(
                              Icons.search_rounded,
                              color: HomeColors.primaryGreen,
                              size: 20,
                            ),
                            suffixIcon: _searchController.text.isNotEmpty
                                ? IconButton(
                                    onPressed: () {
                                      _searchController.clear();
                                      setState(() {});
                                    },
                                    icon: const Icon(
                                      Icons.cancel_rounded,
                                      color: HomeColors.textSecondary,
                                      size: 18,
                                    ),
                                  )
                                : null,
                            contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                            border: InputBorder.none,
                            enabledBorder: InputBorder.none,
                            focusedBorder: InputBorder.none,
                          ),
                        ),
                      ),

                      const SizedBox(height: HomeSpacing.sm),

                      // Filter Count
                      Text(
                        filteredFarms.length == farms.length
                            ? 'Menampilkan ${farms.length} petak lahan aktif'
                            : '${filteredFarms.length} dari ${farms.length} lahan ditemukan',
                        style: HomeTypography.caption.copyWith(
                          color: HomeColors.textSecondary,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),

          // 3. Farm Cards List
          if (filteredFarms.isEmpty)
            SliverFillRemaining(
              hasScrollBody: false,
              child: Center(
                child: Padding(
                  padding: const EdgeInsets.all(28),
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Icon(Icons.search_off_rounded, size: 42, color: HomeColors.textSecondary),
                      const SizedBox(height: 12),
                      const Text(
                        'Tidak ada lahan yang cocok',
                        style: HomeTypography.cardTitle,
                      ),
                      const SizedBox(height: 4),
                      const Text(
                        'Coba periksa kata kunci pencarian Anda.',
                        style: HomeTypography.supporting,
                      ),
                    ],
                  ),
                ),
              ),
            )
          else
            SliverPadding(
              padding: const EdgeInsets.fromLTRB(16, 0, 16, 100),
              sliver: SliverList.separated(
                itemCount: filteredFarms.length,
                separatorBuilder: (context, index) => const SizedBox(height: 12),
                itemBuilder: (context, index) {
                  final farm = filteredFarms[index];
                  return Center(
                    child: ConstrainedBox(
                      constraints: const BoxConstraints(maxWidth: 680),
                      child: FarmCard(
                        farm: farm,
                        onTapCalendar: () => _showPlantingCalendar(farm),
                        onTapFertilizer: () => context.push('/fertilizer?farmId=${farm.id}'),
                        onTapAddActivity: () => context.push('/land/activity/add?farmId=${farm.id}'),
                        onTapTimeline: () => context.push('/land/timeline?farmId=${farm.id}'),
                        onTapFocusMap: () {
                          setState(() {
                            _focusedFarm = farm;
                            _isMapMode = true;
                          });
                        },
                      ),
                    ),
                  );
                },
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: ConstrainedBox(
        constraints: const BoxConstraints(maxWidth: 480),
        child: Padding(
          padding: const EdgeInsets.all(28),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Container(
                width: 80,
                height: 80,
                decoration: BoxDecoration(
                  color: HomeColors.lightGreen,
                  borderRadius: BorderRadius.circular(HomeRadius.xl),
                ),
                child: const Icon(
                  Icons.landscape_rounded,
                  size: 40,
                  color: HomeColors.primaryGreen,
                ),
              ),
              const SizedBox(height: HomeSpacing.lg),
              const Text(
                'Belum Ada Lahan Terdaftar',
                style: TextStyle(
                  color: HomeColors.textPrimary,
                  fontSize: 20,
                  fontWeight: FontWeight.w900,
                ),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 6),
              const Text(
                'Daftarkan petak sawah Anda untuk mendapatkan rekomendasi kalender tanam, takaran pupuk, dan pemantauan satelit.',
                style: HomeTypography.supporting,
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: HomeSpacing.xl),
              FilledButton.icon(
                onPressed: () async {
                  final result = await context.push('/farms/add');
                  if (result == true) {
                    ref.invalidate(userFarmsProvider);
                  }
                },
                icon: const Icon(Icons.add_rounded, size: 18),
                label: const Text('Tambah Lahan Pertama'),
                style: FilledButton.styleFrom(
                  backgroundColor: HomeColors.primaryGreen,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(HomeRadius.sm),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildErrorState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(28),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              width: 72,
              height: 72,
              decoration: BoxDecoration(
                color: HomeColors.dangerBg,
                borderRadius: BorderRadius.circular(HomeRadius.xl),
              ),
              child: const Icon(
                Icons.cloud_off_rounded,
                size: 36,
                color: HomeColors.danger,
              ),
            ),
            const SizedBox(height: HomeSpacing.md),
            const Text(
              'Gagal memuat data lahan',
              style: HomeTypography.cardTitle,
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: 4),
            const Text(
              'Terjadi kendala saat menyambung ke server. Periksa jaringan Anda.',
              style: HomeTypography.supporting,
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: HomeSpacing.md),
            FilledButton.icon(
              onPressed: () => ref.invalidate(userFarmsProvider),
              icon: const Icon(Icons.refresh_rounded, size: 16),
              label: const Text('Coba Lagi'),
              style: FilledButton.styleFrom(
                backgroundColor: HomeColors.primaryGreen,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
