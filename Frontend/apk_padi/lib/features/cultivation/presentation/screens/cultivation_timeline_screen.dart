import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import 'package:padi/core/network/api_client.dart';
import 'package:padi/core/storage/token_storage.dart';

const Color timelineGreen = Color(0xFF075C3D);
const Color timelineBackground = Color(0xFFF7F9F4);
const Color timelineText = Color(0xFF183D2D);
const Color timelineYellow = Color(0xFFF2C94C);

class CultivationTimelineScreen extends StatefulWidget {
  const CultivationTimelineScreen({
    super.key,
    this.cropSeasonId,
  });

  final int? cropSeasonId;

  @override
  State<CultivationTimelineScreen> createState() =>
      _CultivationTimelineScreenState();
}

class _CultivationTimelineScreenState
    extends State<CultivationTimelineScreen> {
  late final ApiClient _apiClient;

  bool isLoading = true;
  String? errorMessage;

  Map<String, dynamic>? cropSeason;
  Map<String, dynamic>? farm;

  List<Map<String, dynamic>> activities = [];

  @override
  void initState() {
    super.initState();

    _apiClient = ApiClient(
      const SecureTokenStorage(),
    );

    _loadData();
  }

  Future<void> _loadData() async {
    if (!mounted) {
      return;
    }

    setState(() {
      isLoading = true;
      errorMessage = null;
    });

    try {
      final seasonResponse = await _apiClient.dio.get(
        '/crop-seasons',
      );

      final seasons = _extractList(
        seasonResponse.data,
        'crop_seasons',
      );

      if (seasons.isEmpty) {
        if (!mounted) {
          return;
        }

        setState(() {
          cropSeason = null;
          farm = null;
          activities = [];
          isLoading = false;
        });

        return;
      }

      Map<String, dynamic>? selectedSeason;

      if (widget.cropSeasonId != null) {
        for (final item in seasons) {
          final id = _toInt(item['id']);

          if (id == widget.cropSeasonId) {
            selectedSeason = item;
            break;
          }
        }
      }

      selectedSeason ??= _findLatestSeason(seasons);

      if (selectedSeason == null) {
        throw Exception(
          'Musim tanam tidak ditemukan.',
        );
      }

      final seasonId = _toInt(
        selectedSeason['id'],
      );

      final farmId = _toInt(
        selectedSeason['farm_id'],
      );

      final loadedActivities = seasonId == null
          ? <Map<String, dynamic>>[]
          : await _loadActivities(seasonId);

      Map<String, dynamic>? loadedFarm;

      if (farmId != null) {
        loadedFarm = await _loadFarm(farmId);
      }

      if (!mounted) {
        return;
      }

      setState(() {
        cropSeason = selectedSeason;
        activities = loadedActivities;
        farm = loadedFarm;
        isLoading = false;
      });
    } catch (e) {
      if (!mounted) {
        return;
      }

      setState(() {
        isLoading = false;
        errorMessage = _cleanErrorMessage(e);
      });
    }
  }

  Future<List<Map<String, dynamic>>> _loadActivities(
    int cropSeasonId,
  ) async {
    final response = await _apiClient.dio.get(
      '/farm-activities',
    );

    final items = _extractList(
      response.data,
      'farm_activities',
    );

    final result = items.where((item) {
      final activitySeasonId = _toInt(
        item['crop_season_id'],
      );

      return activitySeasonId == cropSeasonId;
    }).toList();

    result.sort((a, b) {
      final dateA =
          DateTime.tryParse(
            a['occurred_at']?.toString() ?? '',
          ) ??
          DateTime(2000);

      final dateB =
          DateTime.tryParse(
            b['occurred_at']?.toString() ?? '',
          ) ??
          DateTime(2000);

      return dateB.compareTo(dateA);
    });

    return result;
  }

  Future<Map<String, dynamic>?> _loadFarm(
    int farmId,
  ) async {
    try {
      final response = await _apiClient.dio.get(
        '/farms/$farmId',
      );

      return _extractMap(response.data);
    } catch (_) {
      return null;
    }
  }

  List<Map<String, dynamic>> _extractList(
    dynamic responseData,
    String key,
  ) {
    dynamic data = responseData;

    if (data is Map<String, dynamic>) {
      data = data['data'];
    }

    if (data is Map<String, dynamic>) {
      data = data[key];
    }

    if (data is List) {
      return data
          .whereType<Map>()
          .map(
            (item) => Map<String, dynamic>.from(item),
          )
          .toList();
    }

    return [];
  }

  Map<String, dynamic>? _extractMap(
    dynamic responseData,
  ) {
    dynamic data = responseData;

    if (data is Map<String, dynamic>) {
      data = data['data'];
    }

    if (data is Map<String, dynamic>) {
      if (data.containsKey('farm')) {
        data = data['farm'];
      }
    }

    if (data is Map) {
      return Map<String, dynamic>.from(data);
    }

    return null;
  }

  Map<String, dynamic>? _findLatestSeason(
    List<Map<String, dynamic>> seasons,
  ) {
    final validSeasons = seasons.where(
      (item) => item['id'] != null,
    );

    if (validSeasons.isEmpty) {
      return null;
    }

    final sorted = validSeasons.toList();

    sorted.sort((a, b) {
      final idA = _toInt(a['id']) ?? 0;
      final idB = _toInt(b['id']) ?? 0;

      return idB.compareTo(idA);
    });

    return sorted.first;
  }

  int? _toInt(dynamic value) {
    if (value is int) {
      return value;
    }

    if (value is num) {
      return value.toInt();
    }

    return int.tryParse(
      value?.toString() ?? '',
    );
  }

  String _cleanErrorMessage(dynamic error) {
    final message = error.toString();

    if (message.startsWith('Exception: ')) {
      return message.substring(11);
    }

    return message;
  }

  String _formatDate(dynamic value) {
    if (value == null ||
        value.toString().trim().isEmpty) {
      return '-';
    }

    final date = DateTime.tryParse(
      value.toString(),
    );

    if (date == null) {
      return value.toString();
    }

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

    return '${date.day} '
        '${months[date.month - 1]} '
        '${date.year}';
  }

  String _formatDateTime(dynamic value) {
    if (value == null ||
        value.toString().trim().isEmpty) {
      return '-';
    }

    final date = DateTime.tryParse(
      value.toString(),
    );

    if (date == null) {
      return value.toString();
    }

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

    final hour =
        date.hour.toString().padLeft(2, '0');

    final minute =
        date.minute.toString().padLeft(2, '0');

    return '${date.day} '
        '${months[date.month - 1]} '
        '${date.year}, '
        '$hour:$minute';
  }

  String _statusLabel(String? status) {
    switch (status) {
      case 'planned':
        return 'Direncanakan';
      case 'active':
        return 'Sedang berjalan';
      case 'completed':
        return 'Selesai';
      case 'cancelled':
        return 'Dibatalkan';
      default:
        return status ?? '-';
    }
  }

  String _activityLabel(String? type) {
    switch (type) {
      case 'land_preparation':
        return 'Persiapan Lahan';
      case 'planting':
        return 'Penanaman';
      case 'fertilizing':
        return 'Pemupukan';
      case 'spraying':
        return 'Penyemprotan';
      case 'irrigation':
        return 'Pengairan';
      case 'other':
        return 'Kegiatan Lainnya';
      default:
        return type ?? 'Kegiatan';
    }
  }

  IconData _activityIcon(String? type) {
    switch (type) {
      case 'land_preparation':
        return Icons.agriculture_rounded;
      case 'planting':
        return Icons.spa_rounded;
      case 'fertilizing':
        return Icons.science_rounded;
      case 'spraying':
        return Icons.sanitizer_rounded;
      case 'irrigation':
        return Icons.water_drop_rounded;
      case 'other':
        return Icons.edit_note_rounded;
      default:
        return Icons.edit_note_rounded;
    }
  }

  int _calculateDay() {
    final plantingDate = DateTime.tryParse(
      cropSeason?['planting_date']?.toString() ?? '',
    );

    final plannedDate = DateTime.tryParse(
      cropSeason?['planned_planting_date']?.toString() ?? '',
    );

    final startDate =
        plantingDate ?? plannedDate;

    if (startDate == null) {
      return 0;
    }

    final start = DateTime(
      startDate.year,
      startDate.month,
      startDate.day,
    );

    final today = DateTime.now();

    final current = DateTime(
      today.year,
      today.month,
      today.day,
    );

    final difference =
        current.difference(start).inDays;

    if (difference < 0) {
      return 0;
    }

    return difference + 1;
  }

  String _farmName() {
    final name = farm?['name']?.toString();

    if (name != null &&
        name.trim().isNotEmpty) {
      return name;
    }

    final seasonFarm =
        cropSeason?['farm'];

    if (seasonFarm is Map) {
      final name =
          seasonFarm['name']?.toString();

      if (name != null &&
          name.trim().isNotEmpty) {
        return name;
      }
    }

    return 'Lahan Pertanian';
  }

  Future<void> _openAddActivity() async {
    final id = _toInt(
      cropSeason?['id'],
    );

    if (id == null) {
      return;
    }

    await context.push(
      '/land/activity/add?cropSeasonId=$id',
    );

    await _loadData();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: timelineBackground,
      appBar: AppBar(
        backgroundColor: timelineBackground,
        elevation: 0,
        surfaceTintColor: Colors.transparent,
        leading: IconButton(
          onPressed: () {
            context.go('/land/season/start');
          },
          icon: const Icon(
            Icons.arrow_back_rounded,
            size: 34,
          ),
          color: timelineGreen,
          tooltip: 'Kembali',
        ),
        title: const Text(
          'Kegiatan Sawah',
          style: TextStyle(
            color: timelineText,
            fontSize: 23,
            fontWeight: FontWeight.w900,
          ),
        ),
      ),
      floatingActionButton:
          FloatingActionButton.extended(
        onPressed:
            isLoading || cropSeason == null
                ? null
                : _openAddActivity,
        backgroundColor: timelineGreen,
        foregroundColor: Colors.white,
        icon: const Icon(
          Icons.add_rounded,
          size: 26,
        ),
        label: const Text(
          'Tambah Kegiatan',
          style: TextStyle(
            fontWeight: FontWeight.w900,
          ),
        ),
      ),
      body: _buildBody(),
    );
  }

  Widget _buildBody() {
    if (isLoading) {
      return const Center(
        child: CircularProgressIndicator(
          color: timelineGreen,
        ),
      );
    }

    if (errorMessage != null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(
                Icons.error_outline_rounded,
                color: Colors.red,
                size: 50,
              ),
              const SizedBox(height: 15),
              const Text(
                'Gagal mengambil data',
                style: TextStyle(
                  color: timelineText,
                  fontSize: 18,
                  fontWeight: FontWeight.w900,
                ),
              ),
              const SizedBox(height: 8),
              Text(
                errorMessage!,
                textAlign: TextAlign.center,
                style: const TextStyle(
                  color: Color(0xFF69766F),
                  fontSize: 13,
                ),
              ),
              const SizedBox(height: 20),
              ElevatedButton(
                onPressed: _loadData,
                style: ElevatedButton.styleFrom(
                  backgroundColor:
                      timelineGreen,
                  foregroundColor:
                      Colors.white,
                ),
                child: const Text(
                  'Coba Lagi',
                ),
              ),
            ],
          ),
        ),
      );
    }

    if (cropSeason == null) {
      return RefreshIndicator(
        onRefresh: _loadData,
        color: timelineGreen,
        child: ListView(
          children: const [
            SizedBox(height: 180),
            Icon(
              Icons.spa_outlined,
              color: timelineGreen,
              size: 70,
            ),
            SizedBox(height: 15),
            Center(
              child: Text(
                'Belum ada musim tanam',
                style: TextStyle(
                  color: timelineText,
                  fontSize: 20,
                  fontWeight: FontWeight.w900,
                ),
              ),
            ),
            SizedBox(height: 8),
            Center(
              child: Text(
                'Mulai musim tanam terlebih dahulu.',
                style: TextStyle(
                  color: Color(0xFF69766F),
                ),
              ),
            ),
          ],
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: _loadData,
      color: timelineGreen,
      child: ListView(
        padding:
            const EdgeInsets.fromLTRB(
          20,
          8,
          20,
          110,
        ),
        children: [
          _buildSeasonHeader(),
          const SizedBox(height: 25),
          const Text(
            'Informasi Musim Tanam',
            style: TextStyle(
              color: timelineText,
              fontSize: 20,
              fontWeight: FontWeight.w900,
            ),
          ),
          const SizedBox(height: 15),
          _InfoCard(
            icon: Icons.calendar_month_rounded,
            title: 'Rencana mulai tanam',
            value: _formatDate(
              cropSeason?[
                'planned_planting_date'
              ],
            ),
          ),
          _InfoCard(
            icon: Icons.spa_rounded,
            title: 'Tanggal tanam',
            value: _formatDate(
              cropSeason?['planting_date'],
            ),
          ),
          _InfoCard(
            icon: Icons.agriculture_rounded,
            title: 'Perkiraan panen',
            value: _formatDate(
              cropSeason?[
                'estimated_harvest_date'
              ],
            ),
          ),
          const SizedBox(height: 10),
          Row(
            children: [
              const Expanded(
                child: Text(
                  'Kegiatan Musim Tanam',
                  style: TextStyle(
                    color: timelineText,
                    fontSize: 20,
                    fontWeight: FontWeight.w900,
                  ),
                ),
              ),
              if (activities.isNotEmpty)
                Container(
                  padding:
                      const EdgeInsets.symmetric(
                    horizontal: 12,
                    vertical: 6,
                  ),
                  decoration: BoxDecoration(
                    color:
                        const Color(0xFFEAF5EF),
                    borderRadius:
                        BorderRadius.circular(
                      20,
                    ),
                  ),
                  child: Text(
                    '${activities.length} kegiatan',
                    style: const TextStyle(
                      color: timelineGreen,
                      fontSize: 12,
                      fontWeight:
                          FontWeight.w800,
                    ),
                  ),
                ),
            ],
          ),
          const SizedBox(height: 15),
          if (activities.isEmpty)
            _buildEmptyActivities()
          else
            ...activities
                .asMap()
                .entries
                .map((entry) {
              final index = entry.key;
              final activity = entry.value;

              final type =
                  activity['type']?.toString();

              final notes =
                  activity['notes']
                      ?.toString()
                      .trim();

              return _TimelineItem(
                icon: _activityIcon(type),
                title: _activityLabel(type),
                description:
                    notes != null &&
                            notes.isNotEmpty
                        ? notes
                        : 'Tidak ada catatan tambahan.',
                date: _formatDateTime(
                  activity['occurred_at'],
                ),
                completed: true,
                isLast:
                    index ==
                        activities.length - 1,
              );
            }),
          const SizedBox(height: 20),
          Container(
            padding: const EdgeInsets.all(17),
            decoration: BoxDecoration(
              color: const Color(0xFFFFF8DF),
              borderRadius:
                  BorderRadius.circular(20),
            ),
            child: const Row(
              crossAxisAlignment:
                  CrossAxisAlignment.start,
              children: [
                Icon(
                  Icons.lightbulb_outline_rounded,
                  color: Color(0xFF946E00),
                  size: 29,
                ),
                SizedBox(width: 12),
                Expanded(
                  child: Text(
                    'Catat kegiatan setiap kali merawat sawah agar perjalanan musim tanam mudah dipantau.',
                    style: TextStyle(
                      color: Color(0xFF5B4808),
                      fontSize: 14,
                      height: 1.4,
                      fontWeight:
                          FontWeight.w600,
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSeasonHeader() {
    final status =
        cropSeason?['status']?.toString();

    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: timelineGreen,
        borderRadius:
            BorderRadius.circular(25),
      ),
      child: Column(
        crossAxisAlignment:
            CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Icon(
                Icons.grass_rounded,
                color: timelineYellow,
                size: 35,
              ),
              const SizedBox(width: 11),
              Expanded(
                child: Text(
                  _farmName(),
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 21,
                    fontWeight:
                        FontWeight.w900,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),
          Text(
            'Status: ${_statusLabel(status)}',
            style: const TextStyle(
              color: Colors.white70,
              fontSize: 14,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            'Hari ke-${_calculateDay()}',
            style: const TextStyle(
              color: Colors.white,
              fontSize: 18,
              fontWeight: FontWeight.w900,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildEmptyActivities() {
    return Container(
      padding: const EdgeInsets.all(22),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius:
            BorderRadius.circular(20),
      ),
      child: const Column(
        children: [
          Icon(
            Icons.edit_note_rounded,
            color: timelineGreen,
            size: 45,
          ),
          SizedBox(height: 10),
          Text(
            'Belum ada kegiatan',
            style: TextStyle(
              color: timelineText,
              fontSize: 17,
              fontWeight: FontWeight.w900,
            ),
          ),
          SizedBox(height: 5),
          Text(
            'Tambahkan kegiatan pertama untuk musim tanam ini.',
            textAlign: TextAlign.center,
            style: TextStyle(
              color: Color(0xFF69766F),
              fontSize: 13,
            ),
          ),
        ],
      ),
    );
  }
}

class _InfoCard extends StatelessWidget {
  const _InfoCard({
    required this.icon,
    required this.title,
    required this.value,
  });

  final IconData icon;
  final String title;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Container(
      margin:
          const EdgeInsets.only(bottom: 15),
      padding: const EdgeInsets.all(17),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius:
            BorderRadius.circular(20),
      ),
      child: Row(
        children: [
          Container(
            width: 64,
            height: 64,
            decoration: BoxDecoration(
              color: const Color(0xFFEAF5EF),
              borderRadius:
                  BorderRadius.circular(18),
            ),
            child: Icon(
              icon,
              color: timelineGreen,
              size: 30,
            ),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment:
                  CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: const TextStyle(
                    color: Color(0xFF69766F),
                    fontSize: 14,
                  ),
                ),
                const SizedBox(height: 5),
                Text(
                  value,
                  style: const TextStyle(
                    color: timelineText,
                    fontSize: 17,
                    fontWeight:
                        FontWeight.w900,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _TimelineItem extends StatelessWidget {
  const _TimelineItem({
    required this.icon,
    required this.title,
    required this.description,
    required this.date,
    required this.completed,
    required this.isLast,
  });

  final IconData icon;
  final String title;
  final String description;
  final String date;
  final bool completed;
  final bool isLast;

  @override
  Widget build(BuildContext context) {
    return IntrinsicHeight(
      child: Row(
        crossAxisAlignment:
            CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 56,
            child: Column(
              children: [
                Container(
                  width: 48,
                  height: 48,
                  decoration: BoxDecoration(
                    color: completed
                        ? timelineGreen
                        : Colors.white,
                    shape: BoxShape.circle,
                    border: Border.all(
                      color: completed
                          ? timelineGreen
                          : const Color(
                              0xFFD7DED8,
                            ),
                      width: 2,
                    ),
                  ),
                  child: Icon(
                    icon,
                    color: completed
                        ? Colors.white
                        : const Color(
                            0xFF7C8781,
                          ),
                    size: 24,
                  ),
                ),
                if (!isLast)
                  Expanded(
                    child: Container(
                      width: 2,
                      color: const Color(
                        0xFFB7D5C5,
                      ),
                    ),
                  ),
              ],
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Container(
              margin:
                  const EdgeInsets.only(
                bottom: 18,
              ),
              padding:
                  const EdgeInsets.all(17),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius:
                    BorderRadius.circular(20),
              ),
              child: Column(
                crossAxisAlignment:
                    CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: const TextStyle(
                      color: timelineText,
                      fontSize: 16,
                      fontWeight:
                          FontWeight.w900,
                    ),
                  ),
                  const SizedBox(height: 5),
                  Text(
                    description,
                    style: const TextStyle(
                      color: Color(0xFF69766F),
                      fontSize: 13,
                      height: 1.35,
                    ),
                  ),
                  const SizedBox(height: 9),
                  Text(
                    date,
                    style: const TextStyle(
                      color: timelineGreen,
                      fontSize: 12,
                      fontWeight:
                          FontWeight.w800,
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}