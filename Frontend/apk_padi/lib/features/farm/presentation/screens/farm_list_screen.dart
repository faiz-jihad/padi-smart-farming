import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:padi/core/router/app_router.dart';
import 'package:padi/features/farm/data/models/farm_model.dart';
import 'package:padi/features/farm/data/services/farm_api_service.dart';
import 'package:padi/features/map/presentation/screens/planting_calendar_map_page.dart';
import 'package:padi/features/planting_calendar/presentation/widgets/planting_calendar_card.dart';

final farmApiServiceProvider = Provider<FarmApiService>(
  (ref) => FarmApiService(ref.read(apiClientProvider)),
);

final userFarmsProvider = FutureProvider.autoDispose<List<FarmModel>>((ref) {
  return ref.read(farmApiServiceProvider).fetchFarms();
});

class FarmListScreen extends ConsumerWidget {
  const FarmListScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final farmsAsync = ref.watch(userFarmsProvider);

    return Scaffold(
    appBar: AppBar(
      title: const Text('Lahan Pertanian Saya'),
      backgroundColor: const Color(0xFF16A34A),
      foregroundColor: Colors.white,
      elevation: 0,
      leading: IconButton(
        icon: const Icon(
          Icons.arrow_back_rounded,
          size: 32,
        ),
        tooltip: 'Kembali',
        onPressed: () {
          if (context.canPop()) {
            context.pop();
          } else {
            context.go('/home');
          }
        },
      ),
      actions: [
        IconButton(
          icon: const Icon(Icons.map_outlined),
          tooltip: 'Peta GIS & Kalender',
          onPressed: () => context.push('/map/calendar'),
        ),
      ],
    ),
      body: farmsAsync.when(
        data: (farms) {
          if (farms.isEmpty) {
            return Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(Icons.landscape_outlined, size: 72, color: Colors.grey.shade400),
                  const SizedBox(height: 16),
                  const Text(
                    'Belum Ada Lahan Terdaftar',
                    style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'Daftarkan lahan sawah Anda untuk mendapatkan\nrekomendasi kalender tanam dan cuaca presisi.',
                    textAlign: TextAlign.center,
                    style: TextStyle(color: Colors.grey.shade600, fontSize: 13),
                  ),
                  const SizedBox(height: 24),
                  ElevatedButton.icon(
                    onPressed: () => context.push('/farms/add'),
                    icon: const Icon(Icons.add),
                    label: const Text('Tambah Lahan Baru'),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF16A34A),
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                    ),
                  ),
                ],
              ),
            );
          }

          return ListView.separated(
            padding: const EdgeInsets.all(16),
            itemCount: farms.length,
            separatorBuilder: (context, index) => const SizedBox(height: 16),
            itemBuilder: (context, index) {
              final farm = farms[index];
              return _FarmCard(farm: farm);
            },
          );
        },
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (err, _) => Center(
          child: Text('Gagal memuat lahan: $err'),
        ),
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => context.push('/farms/add'),
        backgroundColor: const Color(0xFF16A34A),
        foregroundColor: Colors.white,
        icon: const Icon(Icons.add_location_alt_outlined),
        label: const Text('Tambah Lahan'),
      ),
    );
  }
}

class _FarmCard extends ConsumerWidget {
  const _FarmCard({required this.farm});

  final FarmModel farm;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Text(
                    farm.name,
                    style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: Colors.green.shade50,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    '${farm.areaHa} Ha',
                    style: TextStyle(
                      color: Colors.green.shade800,
                      fontWeight: FontWeight.w600,
                      fontSize: 12,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 6),
            Row(
              children: [
                Icon(Icons.location_on_outlined, size: 14, color: Colors.grey.shade600),
                const SizedBox(width: 4),
                Expanded(
                  child: Text(
                    farm.locationDescription,
                    style: TextStyle(color: Colors.grey.shade600, fontSize: 12),
                  ),
                ),
              ],
            ),
            const Divider(height: 20),
            Row(
              children: [
                _buildTag(Icons.water_drop_outlined, farm.irrigationType),
                const SizedBox(width: 8),
                if (farm.soilType != null) _buildTag(Icons.terrain_outlined, farm.soilType!),
              ],
            ),
            const SizedBox(height: 12),
            SizedBox(
              width: double.infinity,
              child: OutlinedButton.icon(
                onPressed: () async {
                  final calendarApi = ref.read(plantingCalendarApiServiceProvider);
                  final calendar = await calendarApi.getCalendarForFarm(farm.id);

                  if (!context.mounted) return;

                  showModalBottomSheet(
                    context: context,
                    isScrollControlled: true,
                    backgroundColor: Colors.transparent,
                    builder: (context) {
                      return Container(
                        padding: const EdgeInsets.all(20),
                        decoration: const BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
                        ),
                        child: Column(
                          mainAxisSize: MainAxisSize.min,
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Center(
                              child: Container(
                                width: 40,
                                height: 4,
                                decoration: BoxDecoration(
                                  color: Colors.grey.shade300,
                                  borderRadius: BorderRadius.circular(2),
                                ),
                              ),
                            ),
                            const SizedBox(height: 16),
                            Text(
                              'Rekomendasi Tanam: ${farm.name}',
                              style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                            ),
                            const SizedBox(height: 12),
                            if (calendar != null)
                              PlantingCalendarCard(calendar: calendar)
                            else
                              Container(
                                padding: const EdgeInsets.all(16),
                                decoration: BoxDecoration(
                                  color: Colors.grey.shade100,
                                  borderRadius: BorderRadius.circular(12),
                                ),
                                child: Text(
                                  'Belum ada rekomendasi kalender tanam aktif untuk lokasi lahan ini.',
                                  style: TextStyle(color: Colors.grey.shade700),
                                ),
                              ),
                            const SizedBox(height: 16),
                          ],
                        ),
                      );
                    },
                  );
                },
                icon: const Icon(Icons.calendar_month_outlined, size: 16),
                label: const Text('Lihat Rekomendasi Kalender Tanam'),
                style: OutlinedButton.styleFrom(
                  foregroundColor: const Color(0xFF16A34A),
                  side: const BorderSide(color: Color(0xFF16A34A)),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildTag(IconData icon, String text) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      decoration: BoxDecoration(
        color: Colors.grey.shade100,
        borderRadius: BorderRadius.circular(6),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 12, color: Colors.grey.shade700),
          const SizedBox(width: 4),
          Text(text, style: TextStyle(fontSize: 11, color: Colors.grey.shade800)),
        ],
      ),
    );
  }
}
