import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:padi/core/providers/app_providers.dart';
import 'package:padi/features/cultivation/data/services/crop_season_api_service.dart';
import 'package:padi/features/farm/data/models/farm_model.dart';
import 'package:padi/features/farm/data/services/farm_api_service.dart';

const Color seasonGreen = Color(0xFF075C3D);
const Color seasonBackground = Color(0xFFF7F9F4);
const Color seasonText = Color(0xFF183D2D);

class StartPlantingSeasonScreen extends ConsumerStatefulWidget {
  const StartPlantingSeasonScreen({
    super.key,
    this.farmId,
    this.setupFlow = false,
    this.returnTo,
  });

  final int? farmId;
  final bool setupFlow;
  final String? returnTo;

  @override
  ConsumerState<StartPlantingSeasonScreen> createState() =>
      _StartPlantingSeasonScreenState();
}

class _StartPlantingSeasonScreenState
    extends ConsumerState<StartPlantingSeasonScreen> {
  DateTime selectedDate = DateTime.now();

  FarmModel? selectedFarm;

  bool isLoadingFarms = true;
  bool isSaving = false;

  String? errorMessage;

  List<FarmModel> farms = [];

  @override
  void initState() {
    super.initState();
    _loadFarms();
  }

  Future<void> _loadFarms() async {
    setState(() {
      isLoadingFarms = true;
      errorMessage = null;
    });

    try {
      final apiClient = ref.read(apiClientProvider);

      final service = FarmApiService(apiClient);

      final result = await service.fetchFarms();

      if (!mounted) {
        return;
      }

      setState(() {
        farms = result;
        isLoadingFarms = false;

        if (result.isNotEmpty) {
          selectedFarm = _preferredFarm(result);
        }
      });
    } catch (error) {
      if (!mounted) {
        return;
      }

      setState(() {
        isLoadingFarms = false;
        errorMessage = 'Gagal mengambil data lahan.';
      });
    }
  }

  Future<void> _selectDate() async {
    final date = await showDatePicker(
      context: context,
      initialDate: selectedDate,
      firstDate: DateTime(2020),
      lastDate: DateTime(2100),
      helpText: 'Pilih tanggal mulai tanam',
      cancelText: 'Batal',
      confirmText: 'Pilih',
    );

    if (date == null) {
      return;
    }

    setState(() {
      selectedDate = date;
    });
  }

  Future<void> _startSeason() async {
    if (selectedFarm == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Pilih lahan terlebih dahulu.'),
        ),
      );

      return;
    }

    setState(() {
      isSaving = true;
    });

    try {
      final apiClient = ref.read(apiClientProvider);

      final service = CropSeasonApiService(apiClient);

      final cropSeason = await service.createCropSeason(
        farmId: selectedFarm!.id,
        plannedPlantingDate: _formatApiDate(selectedDate),
        plantingDate: _formatApiDate(selectedDate),
        estimatedHarvestDate: _formatApiDate(
          selectedDate.add(const Duration(days: 109)),
        ),
        status: 'active',
      );

      if (!mounted) {
        return;
      }

      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text(
            'Musim tanam berhasil ditambahkan.',
          ),
        ),
      );

      if (widget.returnTo != null && widget.returnTo!.trim().isNotEmpty) {
        context.go(widget.returnTo!);
      } else if (widget.setupFlow) {
        context.go(
          '/fertilizer?farmId=${selectedFarm!.id}&cropSeasonId=${cropSeason.id}&flow=setup',
        );
      } else {
        context.go('/land/timeline?cropSeasonId=${cropSeason.id}');
      }
    } catch (error) {
      if (!mounted) {
        return;
      }

      setState(() {
        isSaving = false;
      });

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            'Gagal menyimpan musim tanam: $error',
          ),
        ),
      );
    }
  }

  String _formatApiDate(DateTime date) {
    final month = date.month.toString().padLeft(2, '0');
    final day = date.day.toString().padLeft(2, '0');

    return '${date.year}-$month-$day';
  }

  FarmModel _preferredFarm(List<FarmModel> result) {
    if (widget.farmId == null) {
      return result.first;
    }

    return result.firstWhere(
      (farm) => farm.id == widget.farmId,
      orElse: () => result.first,
    );
  }

  String _formatDate(DateTime date) {
    const months = [
      'Januari',
      'Februari',
      'Maret',
      'April',
      'Mei',
      'Juni',
      'Juli',
      'Agustus',
      'September',
      'Oktober',
      'November',
      'Desember',
    ];

    return '${date.day} ${months[date.month - 1]} ${date.year}';
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: seasonBackground,
      appBar: AppBar(
        backgroundColor: seasonBackground,
        elevation: 0,
        surfaceTintColor: Colors.transparent,
        leading: IconButton(
          onPressed: () {
            if (widget.returnTo != null && widget.returnTo!.trim().isNotEmpty) {
              context.go(widget.returnTo!);
            } else if (context.canPop()) {
              context.pop();
            } else {
              context.go('/home');
            }
          },
          icon: const Icon(
            Icons.arrow_back_rounded,
            size: 34,
          ),
          color: seasonGreen,
          tooltip: 'Kembali',
        ),
        title: const Text(
          'Mulai Musim Tanam',
          style: TextStyle(
            color: seasonText,
            fontSize: 23,
            fontWeight: FontWeight.w900,
          ),
        ),
      ),
      body: RefreshIndicator(
        onRefresh: _loadFarms,
        child: ListView(
          physics: const AlwaysScrollableScrollPhysics(),
          padding: const EdgeInsets.fromLTRB(
            20,
            8,
            20,
            30,
          ),
          children: [
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: const Color(0xFFEAF5EF),
                borderRadius: BorderRadius.circular(25),
              ),
              child: const Row(
                children: [
                  Icon(
                    Icons.spa_rounded,
                    color: seasonGreen,
                    size: 42,
                  ),
                  SizedBox(width: 15),
                  Expanded(
                    child: Column(
                      crossAxisAlignment:
                          CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Siap mulai menanam?',
                          style: TextStyle(
                            color: seasonText,
                            fontSize: 19,
                            fontWeight: FontWeight.w900,
                          ),
                        ),
                        SizedBox(height: 5),
                        Text(
                          'Catat awal musim tanam agar kegiatan sawah lebih mudah dipantau.',
                          style: TextStyle(
                            color: Color(0xFF69766F),
                            fontSize: 13,
                            height: 1.4,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 25),
            const _Label(
              title: 'Pilih lahan',
              description: 'Lahan yang akan digunakan untuk musim tanam',
            ),
            const SizedBox(height: 10),
            _buildFarmSelector(),
            const SizedBox(height: 23),
            const _Label(
              title: 'Jenis tanaman',
              description: 'Tanaman yang akan ditanam',
            ),
            const SizedBox(height: 10),
            Container(
              padding: const EdgeInsets.symmetric(
                horizontal: 16,
              ),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(18),
                border: Border.all(
                  color: const Color(0xFFE1E7E2),
                ),
              ),
              child: const Padding(
                padding: EdgeInsets.symmetric(
                  vertical: 17,
                ),
                child: Row(
                  children: [
                    Icon(
                      Icons.grass_rounded,
                      color: seasonGreen,
                      size: 27,
                    ),
                    SizedBox(width: 12),
                    Expanded(
                      child: Text(
                        'Padi',
                        style: TextStyle(
                          color: seasonText,
                          fontSize: 16,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 23),
            const _Label(
              title: 'Tanggal mulai tanam',
              description: 'Kapan mulai menanam?',
            ),
            const SizedBox(height: 10),
            Material(
              color: Colors.white,
              borderRadius: BorderRadius.circular(18),
              child: InkWell(
                onTap: isSaving ? null : _selectDate,
                borderRadius: BorderRadius.circular(18),
                child: Padding(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 17,
                    vertical: 19,
                  ),
                  child: Row(
                    children: [
                      const Icon(
                        Icons.calendar_month_rounded,
                        color: seasonGreen,
                        size: 27,
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Text(
                          _formatDate(selectedDate),
                          style: const TextStyle(
                            color: seasonText,
                            fontSize: 16,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                      ),
                      const Icon(
                        Icons.edit_calendar_rounded,
                        color: seasonGreen,
                        size: 24,
                      ),
                    ],
                  ),
                ),
              ),
            ),
            const SizedBox(height: 25),
            Container(
              padding: const EdgeInsets.all(17),
              decoration: BoxDecoration(
                color: const Color(0xFFFFF8DF),
                borderRadius: BorderRadius.circular(20),
              ),
              child: const Row(
                crossAxisAlignment:
                    CrossAxisAlignment.start,
                children: [
                  Icon(
                    Icons.volume_up_rounded,
                    color: Color(0xFF946E00),
                    size: 29,
                  ),
                  SizedBox(width: 12),
                  Expanded(
                    child: Text(
                      'Setelah dimulai, alur akan lanjut ke rekomendasi pupuk, '
                      'catatan panen, kalender tanam, lalu peta.',
                      style: TextStyle(
                        color: Color(0xFF5B4808),
                        fontSize: 14,
                        height: 1.4,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 28),
            SizedBox(
              height: 64,
              width: double.infinity,
              child: ElevatedButton.icon(
                onPressed: isSaving ||
                        isLoadingFarms ||
                        selectedFarm == null
                    ? null
                    : _startSeason,
                icon: isSaving
                    ? const SizedBox(
                        width: 23,
                        height: 23,
                        child: CircularProgressIndicator(
                          strokeWidth: 2.5,
                          color: Colors.white,
                        ),
                      )
                    : const Icon(
                        Icons.play_arrow_rounded,
                        size: 29,
                      ),
                label: Text(
                  isSaving
                      ? 'Menyimpan...'
                      : 'Mulai Musim Tanam',
                  style: const TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.w900,
                  ),
                ),
                style: ElevatedButton.styleFrom(
                  backgroundColor: seasonGreen,
                  disabledBackgroundColor:
                      const Color(0xFF9AB8AA),
                  foregroundColor: Colors.white,
                  disabledForegroundColor: Colors.white,
                  elevation: 0,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(20),
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildFarmSelector() {
    if (isLoadingFarms) {
      return Container(
        padding: const EdgeInsets.all(18),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(18),
          border: Border.all(
            color: const Color(0xFFE1E7E2),
          ),
        ),
        child: const Row(
          children: [
            SizedBox(
              width: 22,
              height: 22,
              child: CircularProgressIndicator(
                strokeWidth: 2.5,
                color: seasonGreen,
              ),
            ),
            SizedBox(width: 14),
            Text(
              'Mengambil data lahan...',
              style: TextStyle(
                color: Color(0xFF69766F),
                fontSize: 15,
              ),
            ),
          ],
        ),
      );
    }

    if (errorMessage != null) {
      return Container(
        padding: const EdgeInsets.all(18),
        decoration: BoxDecoration(
          color: const Color(0xFFFFEEEE),
          borderRadius: BorderRadius.circular(18),
          border: Border.all(
            color: const Color(0xFFE5BABA),
          ),
        ),
        child: Column(
          crossAxisAlignment:
              CrossAxisAlignment.start,
          children: [
            Text(
              errorMessage!,
              style: const TextStyle(
                color: Color(0xFF9B2C2C),
                fontWeight: FontWeight.w700,
              ),
            ),
            const SizedBox(height: 10),
            TextButton.icon(
              onPressed: _loadFarms,
              icon: const Icon(Icons.refresh_rounded),
              label: const Text('Coba lagi'),
            ),
          ],
        ),
      );
    }

    if (farms.isEmpty) {
      return Container(
        padding: const EdgeInsets.all(18),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(18),
          border: Border.all(
            color: const Color(0xFFE1E7E2),
          ),
        ),
        child: const Text(
          'Belum ada lahan. Silakan daftarkan lahan terlebih dahulu.',
          style: TextStyle(
            color: Color(0xFF69766F),
            fontSize: 15,
            height: 1.4,
          ),
        ),
      );
    }

    return Container(
      padding: const EdgeInsets.symmetric(
        horizontal: 16,
      ),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(
          color: const Color(0xFFE1E7E2),
        ),
      ),
      child: DropdownButtonHideUnderline(
        child: DropdownButton<FarmModel>(
          value: selectedFarm,
          isExpanded: true,
          icon: const Icon(
            Icons.keyboard_arrow_down_rounded,
            color: seasonGreen,
            size: 30,
          ),
          style: const TextStyle(
            color: seasonText,
            fontSize: 16,
            fontWeight: FontWeight.w700,
          ),
          items: farms.map(
            (farm) {
              return DropdownMenuItem<FarmModel>(
                value: farm,
                child: Text(
                  farm.name,
                  overflow: TextOverflow.ellipsis,
                ),
              );
            },
          ).toList(),
          onChanged: isSaving
              ? null
              : (farm) {
                  setState(() {
                    selectedFarm = farm;
                  });
                },
        ),
      ),
    );
  }
}

class _Label extends StatelessWidget {
  const _Label({
    required this.title,
    required this.description,
  });

  final String title;
  final String description;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment:
          CrossAxisAlignment.start,
      children: [
        Text(
          title,
          style: const TextStyle(
            color: seasonText,
            fontSize: 17,
            fontWeight: FontWeight.w900,
          ),
        ),
        const SizedBox(height: 3),
        Text(
          description,
          style: const TextStyle(
            color: Color(0xFF69766F),
            fontSize: 13,
          ),
        ),
      ],
    );
  }
}
