import 'dart:math' as math;
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import 'package:padi/core/providers/app_providers.dart';

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

  Map<String, dynamic>? _selectedSeason;
  Map<String, dynamic>? _farm;
  List<Map<String, dynamic>> _timelineEvents = [];

  String _selectedFilter = 'all';
  int _currentPage = 1;
  static const int _pageSize = 10;
  final ScrollController _scrollController = ScrollController();

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  @override
  void dispose() {
    _scrollController.dispose();
    super.dispose();
  }

  Future<void> _loadData() async {
    if (!mounted) return;
    setState(() {
      _isLoading = true;
      _errorMessage = null;
      _currentPage = 1;
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
          _selectedSeason = null;
          _farm = null;
          _timelineEvents = [];
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

      final farmId = int.tryParse(current['farm_id']?.toString() ?? '');

      // 1. Fetch aggregated farm timeline from backend
      List<Map<String, dynamic>> events = [];
      if (farmId != null) {
        try {
          final timelineRes = await apiClient.dio.get('/farms/$farmId/timeline');
          final tData = timelineRes.data;
          if (tData is Map && tData['data'] is Map) {
            final rawTimeline = tData['data']['timeline'];
            if (rawTimeline is List) {
              events = rawTimeline
                  .whereType<Map>()
                  .map((e) => Map<String, dynamic>.from(e))
                  .toList();
            }
          }
        } catch (_) {
          // Fallback to legacy activities if timeline fails
          try {
            final actRes = await apiClient.dio.get('/farm-activities');
            final actData = actRes.data;
            List<Map<String, dynamic>> allActs = [];
            if (actData is Map && actData['data'] is List) {
              allActs = (actData['data'] as List)
                  .whereType<Map>()
                  .map((e) => Map<String, dynamic>.from(e))
                  .toList();
            }
            events = allActs.map((act) => {
              'id': 'activity_${act['id']}',
              'category': 'activity',
              'title': act['type']?.toString() ?? 'Aktivitas',
              'description': act['notes']?.toString() ?? '',
              'occurred_at': act['occurred_at']?.toString(),
              'status': 'completed',
              'cost': act['cost'],
            }).toList();
          } catch (_) {}
        }
      }

      // Load Farm details
      Map<String, dynamic>? farm;
      if (farmId != null) {
        try {
          final farmRes = await apiClient.dio.get('/farms/$farmId');
          final fData = farmRes.data;
          if (fData is Map && fData['data'] is Map) {
            farm = Map<String, dynamic>.from(fData['data']);
          }
        } catch (_) {}
      }

      if (!mounted) return;
      setState(() {
        _selectedSeason = current;
        _farm = farm;
        _timelineEvents = events;
        _isLoading = false;
        _currentPage = 1;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _isLoading = false;
        _errorMessage = e.toString().replaceFirst('Exception: ', '');
      });
    }
  }

  int _calculateHst() {
    final plantingDateStr = _selectedSeason?['planting_date']?.toString();
    if (plantingDateStr == null || plantingDateStr.isEmpty) return 0;
    final plantingDate = DateTime.tryParse(plantingDateStr);
    if (plantingDate == null) return 0;
    return DateTime.now().difference(plantingDate).inDays.clamp(0, 150);
  }

  String _formatDate(dynamic dateStr) {
    if (dateStr == null || dateStr.toString().isEmpty) return '-';
    try {
      final date = DateTime.parse(dateStr.toString());
      return DateFormat('d MMM yyyy', 'id_ID').format(date);
    } catch (_) {
      return dateStr.toString();
    }
  }

  List<Map<String, dynamic>> get _filteredEvents {
    if (_selectedFilter == 'all') {
      return _timelineEvents;
    }
    return _timelineEvents
        .where((e) => e['category'] == _selectedFilter)
        .toList();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        backgroundColor: const Color(0xFF042F1E),
        elevation: 0,
        leading: IconButton(
          icon: const Icon(
            Icons.arrow_back_rounded,
            size: 22,
            color: Colors.white,
          ),
          onPressed: () => context.pop(),
        ),
        title: const Text(
          'Perjalanan Lahan & Budidaya',
          style: TextStyle(
            color: Colors.white,
            fontSize: 16,
            fontWeight: FontWeight.w800,
          ),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded, color: Colors.white),
            tooltip: 'Segarkan',
            onPressed: _loadData,
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        backgroundColor: const Color(0xFF059669),
        foregroundColor: Colors.white,
        icon: const Icon(Icons.edit_note_rounded),
        label: const Text('Catat Kegiatan', style: TextStyle(fontWeight: FontWeight.w800)),
        onPressed: () async {
          final farmId = _farm?['id'] ?? _selectedSeason?['farm_id'];
          final res = await context.push('/land/activity/add?farmId=$farmId');
          if (res == true) {
            _loadData();
          }
        },
      ),
      body: _buildBody(),
    );
  }

  Widget _buildBody() {
    if (_isLoading) {
      return const Center(
        child: CircularProgressIndicator(color: Color(0xFF059669)),
      );
    }

    if (_errorMessage != null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.cloud_off_rounded, color: Colors.red, size: 48),
              const SizedBox(height: 12),
              const Text('Gagal Memuat Timeline', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800)),
              const SizedBox(height: 6),
              Text(_errorMessage!, textAlign: TextAlign.center, style: const TextStyle(color: Color(0xFF64748B), fontSize: 12)),
              const SizedBox(height: 16),
              FilledButton(
                onPressed: _loadData,
                style: FilledButton.styleFrom(backgroundColor: const Color(0xFF059669)),
                child: const Text('Coba Lagi'),
              ),
            ],
          ),
        ),
      );
    }

    if (_selectedSeason == null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(Icons.spa_outlined, color: Color(0xFF059669), size: 64),
              const SizedBox(height: 16),
              const Text('Belum Ada Musim Tanam Aktif', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800)),
              const SizedBox(height: 8),
              const Text('Mulai musim tanam untuk melihat timeline terpadu perawatan dan diagnosa.', textAlign: TextAlign.center, style: TextStyle(color: Color(0xFF64748B), fontSize: 13)),
              const SizedBox(height: 20),
              FilledButton.icon(
                onPressed: () => context.push('/land/season/start'),
                icon: const Icon(Icons.play_arrow_rounded),
                label: const Text('Mulai Musim Tanam'),
                style: FilledButton.styleFrom(backgroundColor: const Color(0xFF059669)),
              ),
            ],
          ),
        ),
      );
    }

    final hst = _calculateHst();
    final farmName = _farm?['name']?.toString() ?? _selectedSeason?['farm']?['name']?.toString() ?? 'Lahan Sawah';
    final variety = _selectedSeason?['variety_name']?.toString() ?? _selectedSeason?['variety']?['name']?.toString() ?? 'Inpari 32';

    // Sub-counts
    final allCount = _timelineEvents.length;
    final activityCount = _timelineEvents.where((e) => e['category'] == 'activity').length;
    final diagCount = _timelineEvents.where((e) => e['category'] == 'diagnosis').length;
    final pplCount = _timelineEvents.where((e) => e['category'] == 'ppl_visit').length;
    final irrCount = _timelineEvents.where((e) => e['category'] == 'irrigation').length;
    final harvestCount = _timelineEvents.where((e) => e['category'] == 'harvest').length;

    // Filter & Pagination calculation
    final filtered = _filteredEvents;
    final totalPages = (filtered.length / _pageSize).ceil();
    final safeCurrentPage = totalPages == 0 ? 1 : _currentPage.clamp(1, totalPages);
    final startIndex = (safeCurrentPage - 1) * _pageSize;
    final endIndex = math.min(startIndex + _pageSize, filtered.length);
    final pagedEvents = filtered.isEmpty
        ? <Map<String, dynamic>>[]
        : filtered.sublist(startIndex, endIndex);

    return RefreshIndicator(
      onRefresh: _loadData,
      color: const Color(0xFF059669),
      child: ListView(
        controller: _scrollController,
        padding: const EdgeInsets.fromLTRB(16, 14, 16, 90),
        children: [
          // 1. Season Hero Summary Banner
          Container(
            padding: const EdgeInsets.all(18),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFF042F1E), Color(0xFF065F46)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(20),
              boxShadow: [
                BoxShadow(
                  color: const Color(0xFF042F1E).withValues(alpha: 0.2),
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
                        color: Colors.white.withValues(alpha: 0.15),
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: Row(
                        children: [
                          const Icon(Icons.eco_rounded, color: Color(0xFFFDE68A), size: 14),
                          const SizedBox(width: 4),
                          Text(variety, style: const TextStyle(color: Colors.white, fontSize: 11.5, fontWeight: FontWeight.w700)),
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
                        style: const TextStyle(color: Color(0xFF042F1E), fontSize: 11.5, fontWeight: FontWeight.w900),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                Text(
                  farmName,
                  style: const TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.w900),
                ),
                const SizedBox(height: 4),
                Text(
                  'Tanam: ${_formatDate(_selectedSeason?['planting_date'])} • Panen: ${_formatDate(_selectedSeason?['estimated_harvest_date'])}',
                  style: TextStyle(color: Colors.white.withValues(alpha: 0.8), fontSize: 11.5),
                ),
              ],
            ),
          ),

          const SizedBox(height: 16),

          // 2. Filter Category Pills (with live sub-counts)
          SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: Row(
              children: [
                _buildFilterChip('all', 'Semua ($allCount)'),
                _buildFilterChip('activity', 'Aktivitas Tani ($activityCount)'),
                _buildFilterChip('diagnosis', 'Diagnosa AI ($diagCount)'),
                _buildFilterChip('ppl_visit', 'Verifikasi PPL ($pplCount)'),
                _buildFilterChip('irrigation', 'Irigasi Air ($irrCount)'),
                _buildFilterChip('harvest', 'Panen Raya ($harvestCount)'),
              ],
            ),
          ),

          const SizedBox(height: 16),

          // 3. Multi-Event Timeline List (Paginated 10 per page)
          if (filtered.isEmpty)
            Container(
              padding: const EdgeInsets.all(32),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: const Color(0xFFE2E8F0)),
              ),
              child: const Column(
                children: [
                  Icon(Icons.event_note_rounded, size: 48, color: Color(0xFFCBD5E1)),
                  SizedBox(height: 12),
                  Text('Belum Ada Peristiwa', style: TextStyle(fontSize: 15, fontWeight: FontWeight.w800, color: Color(0xFF334155))),
                  SizedBox(height: 4),
                  Text('Peristiwa budidaya, diagnosa, dan kunjungan PPL akan muncul di sini.', textAlign: TextAlign.center, style: TextStyle(color: Color(0xFF64748B), fontSize: 12)),
                ],
              ),
            )
          else ...[
            ListView.separated(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              itemCount: pagedEvents.length,
              separatorBuilder: (_, _) => const SizedBox(height: 12),
              itemBuilder: (context, index) {
                final event = pagedEvents[index];
                return _buildTimelineCard(event);
              },
            ),

            // 4. Pagination Controls (muncul jika data > 10)
            if (filtered.length > _pageSize)
              _buildPaginationControls(
                currentPage: safeCurrentPage,
                totalPages: totalPages,
                startIndex: startIndex,
                endIndex: endIndex,
                totalItems: filtered.length,
              ),
          ],
        ],
      ),
    );
  }

  Widget _buildFilterChip(String key, String label) {
    final isSelected = _selectedFilter == key;
    return Padding(
      padding: const EdgeInsets.only(right: 8),
      child: FilterChip(
        selected: isSelected,
        label: Text(
          label,
          style: TextStyle(
            fontSize: 12,
            fontWeight: isSelected ? FontWeight.w800 : FontWeight.w600,
            color: isSelected ? Colors.white : const Color(0xFF475569),
          ),
        ),
        backgroundColor: Colors.white,
        selectedColor: const Color(0xFF042F1E),
        showCheckmark: false,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(20),
          side: BorderSide(
            color: isSelected ? const Color(0xFF042F1E) : const Color(0xFFE2E8F0),
          ),
        ),
        onSelected: (_) {
          setState(() {
            _selectedFilter = key;
            _currentPage = 1;
          });
        },
      ),
    );
  }

  Widget _buildPaginationControls({
    required int currentPage,
    required int totalPages,
    required int startIndex,
    required int endIndex,
    required int totalItems,
  }) {
    return Container(
      margin: const EdgeInsets.only(top: 18),
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: const [
          BoxShadow(
            color: Color(0x06000000),
            blurRadius: 10,
            offset: Offset(0, 3),
          ),
        ],
      ),
      child: Column(
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                'Data ${startIndex + 1}–$endIndex dari $totalItems riwayat',
                style: const TextStyle(
                  fontSize: 12.5,
                  fontWeight: FontWeight.w700,
                  color: Color(0xFF475569),
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: const Color(0xFFECFDF5),
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: const Color(0xFFA7F3D0)),
                ),
                child: Text(
                  'Hal $currentPage / $totalPages',
                  style: const TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w800,
                    color: Color(0xFF065F46),
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              // Previous Button
              IconButton.filledTonal(
                tooltip: 'Halaman Sebelumnya',
                icon: const Icon(Icons.chevron_left_rounded, size: 22),
                style: IconButton.styleFrom(
                  backgroundColor: currentPage > 1 ? const Color(0xFFF1F5F9) : const Color(0xFFF8FAFC),
                  foregroundColor: currentPage > 1 ? const Color(0xFF0F172A) : const Color(0xFFCBD5E1),
                ),
                onPressed: currentPage > 1
                    ? () {
                        setState(() => _currentPage = currentPage - 1);
                        _scrollController.animateTo(
                          0,
                          duration: const Duration(milliseconds: 250),
                          curve: Curves.easeInOut,
                        );
                      }
                    : null,
              ),
              const SizedBox(width: 8),

              // Page Numbers
              ...List.generate(totalPages, (index) {
                final pageNum = index + 1;
                final isCurrent = pageNum == currentPage;

                // Show ellipsis if too many pages
                if (totalPages > 5) {
                  if (pageNum != 1 && pageNum != totalPages && (pageNum < currentPage - 1 || pageNum > currentPage + 1)) {
                    if (pageNum == currentPage - 2 || pageNum == currentPage + 2) {
                      return const Padding(
                        padding: EdgeInsets.symmetric(horizontal: 4),
                        child: Text('...', style: TextStyle(color: Color(0xFF94A3B8), fontWeight: FontWeight.bold)),
                      );
                    }
                    return const SizedBox.shrink();
                  }
                }

                return InkWell(
                  borderRadius: BorderRadius.circular(10),
                  onTap: () {
                    setState(() => _currentPage = pageNum);
                    _scrollController.animateTo(
                      0,
                      duration: const Duration(milliseconds: 250),
                      curve: Curves.easeInOut,
                    );
                  },
                  child: Container(
                    width: 38,
                    height: 38,
                    margin: const EdgeInsets.symmetric(horizontal: 3),
                    decoration: BoxDecoration(
                      color: isCurrent ? const Color(0xFF059669) : Colors.white,
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(
                        color: isCurrent ? const Color(0xFF059669) : const Color(0xFFE2E8F0),
                        width: 1.5,
                      ),
                    ),
                    child: Center(
                      child: Text(
                        '$pageNum',
                        style: TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.w800,
                          color: isCurrent ? Colors.white : const Color(0xFF334155),
                        ),
                      ),
                    ),
                  ),
                );
              }),
              const SizedBox(width: 8),

              // Next Button
              IconButton.filledTonal(
                tooltip: 'Halaman Selanjutnya',
                icon: const Icon(Icons.chevron_right_rounded, size: 22),
                style: IconButton.styleFrom(
                  backgroundColor: currentPage < totalPages ? const Color(0xFFF1F5F9) : const Color(0xFFF8FAFC),
                  foregroundColor: currentPage < totalPages ? const Color(0xFF0F172A) : const Color(0xFFCBD5E1),
                ),
                onPressed: currentPage < totalPages
                    ? () {
                        setState(() => _currentPage = currentPage + 1);
                        _scrollController.animateTo(
                          0,
                          duration: const Duration(milliseconds: 250),
                          curve: Curves.easeInOut,
                        );
                      }
                    : null,
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildTimelineCard(Map<String, dynamic> event) {
    final category = event['category']?.toString() ?? 'activity';
    final title = event['title']?.toString() ?? 'Peristiwa';
    final description = event['description']?.toString() ?? '';
    final dateHuman = event['date_human']?.toString() ?? _formatDate(event['occurred_at']);

    final Color badgeColor;
    final Color badgeBg;
    final IconData iconData;
    final String categoryBadge;

    switch (category) {
      case 'diagnosis':
        final isDiseased = event['status'] == 'diseased' || title.toLowerCase().contains('terdeteksi');
        badgeColor = isDiseased ? const Color(0xFFDC2626) : const Color(0xFF059669);
        badgeBg = isDiseased ? const Color(0xFFFEF2F2) : const Color(0xFFECFDF5);
        iconData = Icons.biotech_rounded;
        categoryBadge = 'Diagnosa AI';
        break;
      case 'ppl_visit':
        badgeColor = const Color(0xFF0284C7);
        badgeBg = const Color(0xFFF0F9FF);
        iconData = Icons.verified_user_rounded;
        categoryBadge = 'Verifikasi PPL';
        break;
      case 'irrigation':
        badgeColor = const Color(0xFF0891B2);
        badgeBg = const Color(0xFFECFEFF);
        iconData = Icons.water_drop_rounded;
        categoryBadge = 'Irigasi';
        break;
      case 'harvest':
        badgeColor = const Color(0xFF7C3AED);
        badgeBg = const Color(0xFFF5F3FF);
        iconData = Icons.inventory_2_rounded;
        categoryBadge = 'Panen Raya';
        break;
      case 'activity':
      default:
        badgeColor = const Color(0xFF059669);
        badgeBg = const Color(0xFFECFDF5);
        iconData = Icons.spa_rounded;
        categoryBadge = 'Aktivitas';
        break;
    }

    final currencyFmt = NumberFormat.currency(locale: 'id_ID', symbol: 'Rp ', decimalDigits: 0);

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: const [
          BoxShadow(
            color: Color(0x06000000),
            blurRadius: 10,
            offset: Offset(0, 2),
          ),
        ],
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: badgeBg,
              shape: BoxShape.circle,
              border: Border.all(color: badgeColor.withValues(alpha: 0.3)),
            ),
            child: Icon(iconData, size: 20, color: badgeColor),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                      decoration: BoxDecoration(
                        color: badgeBg,
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Text(
                        categoryBadge,
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w800,
                          color: badgeColor,
                        ),
                      ),
                    ),
                    Text(
                      dateHuman,
                      style: const TextStyle(
                        fontSize: 11.5,
                        color: Color(0xFF64748B),
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                Text(
                  title,
                  style: const TextStyle(
                    fontSize: 15.5,
                    fontWeight: FontWeight.w900,
                    color: Color(0xFF0F172A),
                    letterSpacing: -0.2,
                  ),
                ),
                if (description.isNotEmpty) ...[
                  const SizedBox(height: 6),
                  _buildFormattedDescription(description),
                ],
                if (event['cost'] != null && (event['cost'] as num) > 0) ...[
                  const SizedBox(height: 8),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: const Color(0xFFECFDF5),
                      borderRadius: BorderRadius.circular(6),
                      border: Border.all(color: const Color(0xFFA7F3D0)),
                    ),
                    child: Text(
                      'Biaya: ${currencyFmt.format(event['cost'])}',
                      style: const TextStyle(
                        fontSize: 11.5,
                        fontWeight: FontWeight.w800,
                        color: Color(0xFF065F46),
                      ),
                    ),
                  ),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFormattedDescription(String text) {
    // If the text contains numbered recommendations (1. ... 2. ...)
    if (text.contains('1. ') && text.contains('2. ')) {
      final parts = text.split(RegExp(r'\s*(?=\d+\.\s)'));
      final intro = parts.isNotEmpty && !parts[0].trim().startsWith('1.') ? parts[0].trim() : '';
      final items = parts.where((p) => RegExp(r'^\d+\.\s').hasMatch(p.trim())).toList();

      return Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          if (intro.isNotEmpty)
            Padding(
              padding: const EdgeInsets.only(bottom: 6),
              child: Text(
                intro,
                style: const TextStyle(
                  fontSize: 12.5,
                  fontWeight: FontWeight.w700,
                  color: Color(0xFF1E293B),
                ),
              ),
            ),
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: const Color(0xFFF8FAFC),
              borderRadius: BorderRadius.circular(10),
              border: Border.all(color: const Color(0xFFE2E8F0)),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: items.map((item) {
                return Padding(
                  padding: const EdgeInsets.only(bottom: 4),
                  child: Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        '• ',
                        style: TextStyle(
                          color: Color(0xFF059669),
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      Expanded(
                        child: Text(
                          item.replaceFirst(RegExp(r'^\d+\.\s*'), '').trim(),
                          style: const TextStyle(
                            fontSize: 12,
                            color: Color(0xFF334155),
                            height: 1.35,
                          ),
                        ),
                      ),
                    ],
                  ),
                );
              }).toList(),
            ),
          ),
        ],
      );
    }

    return Text(
      text,
      style: const TextStyle(
        fontSize: 12.5,
        color: Color(0xFF475569),
        height: 1.4,
      ),
    );
  }
}