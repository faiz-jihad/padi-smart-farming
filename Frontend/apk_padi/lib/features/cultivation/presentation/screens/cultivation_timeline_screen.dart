import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import 'package:padi/core/providers/app_providers.dart';
import 'package:padi/features/home/presentation/tokens/home_tokens.dart';

class CultivationTimelineScreen extends ConsumerStatefulWidget {
  const CultivationTimelineScreen({
    super.key,
    this.cropSeasonId,
  });

  final int? cropSeasonId;

  @override
  ConsumerState<CultivationTimelineScreen> createState() =>
      _CultivationTimelineScreenState();
}

class _CultivationTimelineScreenState
    extends ConsumerState<CultivationTimelineScreen> {
  bool _isLoading = true;
  String? _errorMessage;

  List<Map<String, dynamic>> _seasons = [];
  Map<String, dynamic>? _selectedSeason;
  Map<String, dynamic>? _farm;
  List<Map<String, dynamic>> _activities = [];

  String _selectedFilter = 'all';

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    if (!mounted) return;
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final apiClient = ref.read(apiClientProvider);
      final seasonRes = await apiClient.dio.get('/crop-seasons');
      final seasonData = seasonRes.data;

      List<Map<String, dynamic>> list = [];
      if (seasonData is Map) {
        final inner = seasonData['data'];
        if (inner is Map && inner['crop_seasons'] is List) {
          list = (inner['crop_seasons'] as List)
              .whereType<Map>()
              .map((e) => Map<String, dynamic>.from(e))
              .toList();
        } else if (inner is List) {
          list = inner
              .whereType<Map>()
              .map((e) => Map<String, dynamic>.from(e))
              .toList();
        }
      }

      if (list.isEmpty) {
        if (!mounted) return;
        setState(() {
          _seasons = [];
          _selectedSeason = null;
          _farm = null;
          _activities = [];
          _isLoading = false;
        });
        return;
      }

      Map<String, dynamic>? current;
      if (widget.cropSeasonId != null) {
        current = list.firstWhere(
          (s) => int.tryParse(s['id']?.toString() ?? '') == widget.cropSeasonId,
          orElse: () => list.first,
        );
      } else {
        current = list.firstWhere(
          (s) => s['status'] == 'active',
          orElse: () => list.first,
        );
      }

      final seasonId = int.tryParse(current['id']?.toString() ?? '');
      final farmId = int.tryParse(current['farm_id']?.toString() ?? '');

      // Load activities for this season
      List<Map<String, dynamic>> activities = [];
      if (seasonId != null) {
        try {
          final actRes = await apiClient.dio.get('/farm-activities');
          final actData = actRes.data;
          List<Map<String, dynamic>> allActs = [];
          if (actData is Map) {
            final raw = actData['data'];
            if (raw is List) {
              allActs = raw
                  .whereType<Map>()
                  .map((e) => Map<String, dynamic>.from(e))
                  .toList();
            } else if (raw is Map && raw['farm_activities'] is List) {
              allActs = (raw['farm_activities'] as List)
                  .whereType<Map>()
                  .map((e) => Map<String, dynamic>.from(e))
                  .toList();
            }
          }
          activities = allActs.where((a) {
            final actSeasonId = int.tryParse(a['crop_season_id']?.toString() ?? '');
            return actSeasonId == seasonId;
          }).toList();

          // Sort descending by date
          activities.sort((a, b) {
            final dateA = DateTime.tryParse(a['occurred_at']?.toString() ?? '') ?? DateTime(2000);
            final dateB = DateTime.tryParse(b['occurred_at']?.toString() ?? '') ?? DateTime(2000);
            return dateB.compareTo(dateA);
          });
        } catch (_) {}
      }

      // Load Farm details
      Map<String, dynamic>? farm;
      if (farmId != null) {
        try {
          final farmRes = await apiClient.dio.get('/farms/$farmId');
          final fData = farmRes.data;
          if (fData is Map) {
            final rawF = fData['data'];
            if (rawF is Map) {
              farm = Map<String, dynamic>.from(rawF);
            }
          }
        } catch (_) {}
      }

      if (!mounted) return;
      setState(() {
        _seasons = list;
        _selectedSeason = current;
        _farm = farm;
        _activities = activities;
        _isLoading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _isLoading = false;
        _errorMessage = e.toString().replaceFirst('Exception: ', '');
      });
    }
  }

  Future<void> _deleteActivity(int activityId) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Hapus Catatan Kegiatan?'),
        content: const Text(
          'Kegiatan ini akan dihapus dari riwayat timeline budidaya.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Batal'),
          ),
          FilledButton(
            style: FilledButton.styleFrom(backgroundColor: HomeColors.danger),
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Hapus'),
          ),
        ],
      ),
    );

    if (confirmed != true) return;

    try {
      final apiClient = ref.read(apiClientProvider);
      await apiClient.dio.delete('/farm-activities/$activityId');
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Kegiatan berhasil dihapus.'),
          behavior: SnackBarBehavior.floating,
        ),
      );
      _loadData();
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Gagal menghapus kegiatan: $e'),
          backgroundColor: HomeColors.danger,
          behavior: SnackBarBehavior.floating,
        ),
      );
    }
  }

  List<Map<String, dynamic>> get _filteredActivities {
    if (_selectedFilter == 'all') return _activities;
    return _activities.where((a) => a['type'] == _selectedFilter).toList();
  }

  String _formatDate(dynamic value) {
    if (value == null) return '-';
    final date = DateTime.tryParse(value.toString());
    if (date == null) return value.toString();
    return DateFormat('d MMMM yyyy', 'id_ID').format(date);
  }

  int _calculateHst() {
    final plantingDate = DateTime.tryParse(
      _selectedSeason?['planting_date']?.toString() ??
          _selectedSeason?['planned_planting_date']?.toString() ??
          '',
    );
    if (plantingDate == null) return 0;
    final diff = DateTime.now().difference(plantingDate).inDays;
    return diff < 0 ? 0 : diff + 1;
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF5F7F4),
      appBar: AppBar(
        backgroundColor: HomeColors.primaryGreen,
        elevation: 0,
        scrolledUnderElevation: 0,
        leading: IconButton(
          onPressed: () {
            if (context.canPop()) {
              context.pop();
            } else {
              context.go('/home');
            }
          },
          icon: const Icon(
            Icons.arrow_back_rounded,
            size: 22,
            color: Colors.white,
          ),
          tooltip: 'Kembali',
        ),
        title: const Text(
          'Kegiatan Sawah',
          style: TextStyle(
            color: Colors.white,
            fontSize: 18,
            fontWeight: FontWeight.w800,
          ),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded, color: Colors.white),
            tooltip: 'Segarkan Data',
            onPressed: _loadData,
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _selectedSeason == null
            ? null
            : () async {
                final id = int.tryParse(_selectedSeason?['id']?.toString() ?? '');
                if (id == null) return;
                await context.push('/land/activity/add?cropSeasonId=$id');
                _loadData();
              },
        backgroundColor: HomeColors.primaryGreen,
        foregroundColor: Colors.white,
        icon: const Icon(Icons.add_rounded, size: 22),
        label: const Text(
          'Catat Kegiatan',
          style: TextStyle(fontWeight: FontWeight.w800),
        ),
      ),
      body: _buildBody(),
    );
  }

  Widget _buildBody() {
    if (_isLoading) {
      return const Center(
        child: CircularProgressIndicator(
          color: HomeColors.primaryGreen,
        ),
      );
    }

    if (_errorMessage != null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(
                Icons.cloud_off_rounded,
                color: HomeColors.danger,
                size: 48,
              ),
              const SizedBox(height: 12),
              const Text(
                'Gagal Memuat Kegiatan',
                style: TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.w800,
                ),
              ),
              const SizedBox(height: 6),
              Text(
                _errorMessage!,
                textAlign: TextAlign.center,
                style: const TextStyle(color: Color(0xFF68766E), fontSize: 12),
              ),
              const SizedBox(height: 16),
              FilledButton(
                onPressed: _loadData,
                style: FilledButton.styleFrom(
                  backgroundColor: HomeColors.primaryGreen,
                ),
                child: const Text('Coba Lagi'),
              ),
            ],
          ),
        ),
      );
    }

    if (_selectedSeason == null) {
      return RefreshIndicator(
        onRefresh: _loadData,
        color: HomeColors.primaryGreen,
        child: ListView(
          padding: const EdgeInsets.all(24),
          children: [
            const SizedBox(height: 100),
            const Icon(Icons.spa_outlined, color: HomeColors.primaryGreen, size: 64),
            const SizedBox(height: 16),
            const Center(
              child: Text(
                'Belum Ada Musim Tanam Aktif',
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800),
              ),
            ),
            const SizedBox(height: 8),
            const Center(
              child: Text(
                'Mulai musim tanam terlebih dahulu untuk mencatat kegiatan perawatan sawah.',
                textAlign: TextAlign.center,
                style: TextStyle(color: Color(0xFF68766E), fontSize: 13),
              ),
            ),
            const SizedBox(height: 20),
            Center(
              child: FilledButton.icon(
                onPressed: () => context.push('/land/season/start'),
                icon: const Icon(Icons.play_arrow_rounded),
                label: const Text('Mulai Musim Tanam'),
                style: FilledButton.styleFrom(
                  backgroundColor: HomeColors.primaryGreen,
                ),
              ),
            ),
          ],
        ),
      );
    }

    final hst = _calculateHst();
    final farmName = _farm?['name']?.toString() ??
        _selectedSeason?['farm']?['name']?.toString() ??
        'Lahan Sawah';
    final variety = _selectedSeason?['variety_name']?.toString() ??
        _selectedSeason?['variety']?['name']?.toString() ??
        'Varietas Padi';
    final filtered = _filteredActivities;

    return RefreshIndicator(
      onRefresh: _loadData,
      color: HomeColors.primaryGreen,
      child: ListView(
        padding: const EdgeInsets.fromLTRB(14, 12, 14, 90),
        children: [
          // 1. Season Hero Summary Card
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFF042F1E), Color(0xFF075E3B)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(16),
              boxShadow: [
                BoxShadow(
                  color: const Color(0xFF042F1E).withOpacity(0.2),
                  blurRadius: 12,
                  offset: const Offset(0, 4),
                ),
              ],
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: Colors.white.withOpacity(0.18),
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: Row(
                        children: [
                          const Icon(Icons.eco_rounded, color: Color(0xFFFDE68A), size: 14),
                          const SizedBox(width: 4),
                          Text(
                            variety,
                            style: const TextStyle(
                              color: Colors.white,
                              fontSize: 11.5,
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                        ],
                      ),
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: const Color(0xFFFBBF24),
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: Text(
                        'Hari ke-$hst HST',
                        style: const TextStyle(
                          color: Color(0xFF042F1E),
                          fontSize: 11.5,
                          fontWeight: FontWeight.w900,
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                Text(
                  farmName,
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 20,
                    fontWeight: FontWeight.w900,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  'Tanam: ${_formatDate(_selectedSeason?['planting_date'])} • Panen: ${_formatDate(_selectedSeason?['estimated_harvest_date'])}',
                  style: TextStyle(
                    color: Colors.white.withOpacity(0.8),
                    fontSize: 11.5,
                  ),
                ),
              ],
            ),
          ),

          const SizedBox(height: 14),

          // 2. Filter Category Tabs
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Row(
              children: [
                _buildFilterChip('all', 'Semua (${_activities.length})'),
                _buildFilterChip('fertilizing', 'Pemupukan'),
                _buildFilterChip('irrigation', 'Pengairan'),
                _buildFilterChip('spraying', 'Penyemprotan'),
                _buildFilterChip('planting', 'Penanaman'),
                _buildFilterChip('land_preparation', 'Olah Lahan'),
                _buildFilterChip('other', 'Lainnya'),
              ],
            ),
          ),

          const SizedBox(height: 14),

          // 3. Activity Timeline List
          if (filtered.isEmpty)
            Container(
              padding: const EdgeInsets.all(28),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: const Color(0xFFE5ECE3)),
              ),
              child: Column(
                children: [
                  const Icon(
                    Icons.event_note_rounded,
                    size: 48,
                    color: Color(0xFFB0BDB5),
                  ),
                  const SizedBox(height: 10),
                  const Text(
                    'Belum Ada Catatan Kegiatan',
                    style: TextStyle(
                      fontSize: 15,
                      fontWeight: FontWeight.w800,
                      color: Color(0xFF17251E),
                    ),
                  ),
                  const SizedBox(height: 4),
                  const Text(
                    'Catat setiap kali melakukan pemupukan, pengairan, atau penyemprotan.',
                    textAlign: TextAlign.center,
                    style: TextStyle(
                      fontSize: 12,
                      color: Color(0xFF68766E),
                    ),
                  ),
                ],
              ),
            )
          else
            ...List.generate(filtered.length, (idx) {
              final item = filtered[idx];
              final isLast = idx == filtered.length - 1;
              return _buildActivityCard(item, isLast: isLast);
            }),
        ],
      ),
    );
  }

  Widget _buildFilterChip(String key, String label) {
    final isSel = _selectedFilter == key;
    return Padding(
      padding: const EdgeInsets.only(right: 6),
      child: ChoiceChip(
        label: Text(
          label,
          style: TextStyle(
            fontSize: 12,
            fontWeight: FontWeight.w700,
            color: isSel ? Colors.white : const Color(0xFF425247),
          ),
        ),
        selected: isSel,
        selectedColor: HomeColors.primaryGreen,
        backgroundColor: Colors.white,
        showCheckmark: false,
        side: BorderSide(
          color: isSel ? HomeColors.primaryGreen : const Color(0xFFE5ECE3),
        ),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        onSelected: (_) => setState(() => _selectedFilter = key),
      ),
    );
  }

  Widget _buildActivityCard(Map<String, dynamic> act, {required bool isLast}) {
    final id = int.tryParse(act['id']?.toString() ?? '') ?? 0;
    final type = act['type']?.toString() ?? 'other';
    final notes = act['notes']?.toString();
    final cost = int.tryParse(act['cost']?.toString() ?? '') ?? 0;
    final dateStr = act['occurred_at']?.toString();
    final parsedDate = DateTime.tryParse(dateStr ?? '');
    final dateFormatted = parsedDate != null
        ? DateFormat('EEEE, d MMMM yyyy', 'id_ID').format(parsedDate)
        : dateStr ?? '-';

    final (label, icon, color) = _getActivityMeta(type);

    return Container(
      margin: const EdgeInsets.only(bottom: 10),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFE5ECE3)),
      ),
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  width: 38,
                  height: 38,
                  decoration: BoxDecoration(
                    color: color.withOpacity(0.12),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Icon(icon, color: color, size: 20),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        label,
                        style: const TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w800,
                          color: Color(0xFF17251E),
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        dateFormatted,
                        style: const TextStyle(
                          fontSize: 11,
                          color: Color(0xFF68766E),
                        ),
                      ),
                    ],
                  ),
                ),
                IconButton(
                  icon: const Icon(Icons.delete_outline_rounded, size: 18, color: Color(0xFFB0BDB5)),
                  visualDensity: VisualDensity.compact,
                  tooltip: 'Hapus Kegiatan',
                  onPressed: () => _deleteActivity(id),
                ),
              ],
            ),
            if (notes != null && notes.trim().isNotEmpty) ...[
              const SizedBox(height: 8),
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: const Color(0xFFF9FAF8),
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Text(
                  notes.trim(),
                  style: const TextStyle(
                    fontSize: 12,
                    color: Color(0xFF2C3E33),
                    height: 1.35,
                  ),
                ),
              ),
            ],
            if (cost > 0) ...[
              const SizedBox(height: 8),
              Row(
                children: [
                  const Icon(Icons.payments_outlined, size: 14, color: HomeColors.primaryGreen),
                  const SizedBox(width: 4),
                  Text(
                    'Biaya: ${NumberFormat.currency(locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0).format(cost)}',
                    style: const TextStyle(
                      fontSize: 11.5,
                      fontWeight: FontWeight.w700,
                      color: HomeColors.primaryGreen,
                    ),
                  ),
                ],
              ),
            ],
          ],
        ),
      ),
    );
  }

  (String, IconData, Color) _getActivityMeta(String type) {
    switch (type) {
      case 'fertilizing':
        return ('Pemupukan', Icons.science_rounded, const Color(0xFF0284C7));
      case 'irrigation':
        return ('Pengairan & Irigasi', Icons.water_drop_rounded, const Color(0xFF0EA5E9));
      case 'spraying':
        return ('Penyemprotan Hama', Icons.sanitizer_rounded, const Color(0xFFF59E0B));
      case 'planting':
        return ('Penanaman Bibit', Icons.spa_rounded, const Color(0xFF10B981));
      case 'land_preparation':
        return ('Pengolahan Lahan', Icons.agriculture_rounded, const Color(0xFF047857));
      default:
        return ('Penyiangan & Perawatan', Icons.grass_rounded, const Color(0xFF059669));
    }
  }
}