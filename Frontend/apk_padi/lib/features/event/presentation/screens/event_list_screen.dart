import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:padi/core/utils/debouncer.dart';
import 'package:padi/features/event/data/models/event_model.dart';
import 'package:padi/features/event/data/providers/event_providers.dart';
import 'package:padi/features/home/presentation/tokens/home_tokens.dart';

class EventListScreen extends ConsumerStatefulWidget {
  const EventListScreen({super.key});

  @override
  ConsumerState<EventListScreen> createState() => _EventListScreenState();
}

class _EventListScreenState extends ConsumerState<EventListScreen> {
  String _selectedCategory = 'all';
  final TextEditingController _searchController = TextEditingController();
  final Debouncer _searchDebouncer = Debouncer(milliseconds: 300);

  final List<Map<String, String>> _filterCategories = [
    {'value': 'all', 'label': 'Semua Acara'},
    {'value': 'my_submissions', 'label': 'Pengajuan Saya'},
    {'value': 'my_tickets', 'label': 'Tiket Saya'},
    {'value': 'workshop', 'label': 'Workshop'},
    {'value': 'field_day', 'label': 'Sekolah Lapang'},
    {'value': 'bazaar', 'label': 'Bazar Tani'},
    {'value': 'webinar', 'label': 'Webinar Tani'},
    {'value': 'irrigation', 'label': 'Gilir Air'},
  ];

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      ref.read(eventsProvider.notifier).loadEventsFromApi();
      ref.read(mySubmissionsProvider.notifier).loadSubmissions();
    });
  }

  @override
  void dispose() {
    _searchDebouncer.dispose();
    _searchController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final allEvents = ref.watch(eventsProvider);
    final mySubmissions = ref.watch(mySubmissionsProvider);
    final myTicketsCount = allEvents.where((e) => e.isRegistered).length;
    final keyword = _searchController.text.trim().toLowerCase();

    var filteredEvents = switch (_selectedCategory) {
      'all' => allEvents,
      'my_submissions' => mySubmissions,
      'my_tickets' => allEvents.where((e) => e.isRegistered).toList(),
      _ => allEvents.where((e) => e.category == _selectedCategory).toList(),
    };

    if (keyword.isNotEmpty) {
      filteredEvents = filteredEvents.where((e) {
        final title = e.title.toLowerCase();
        final desc = e.description.toLowerCase();
        final loc = e.locationName.toLowerCase();
        final speaker = (e.speaker ?? '').toLowerCase();
        return title.contains(keyword) ||
            desc.contains(keyword) ||
            loc.contains(keyword) ||
            speaker.contains(keyword);
      }).toList();
    }

    return Scaffold(
      backgroundColor: HomeColors.background,
      appBar: AppBar(
        backgroundColor: HomeColors.background,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_rounded, color: HomeColors.textPrimary),
          onPressed: () {
            if (Navigator.of(context).canPop()) {
              Navigator.of(context).pop();
            } else if (context.canPop()) {
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
              'Agenda & Acara Pertanian',
              style: TextStyle(
                color: HomeColors.textPrimary,
                fontSize: 18,
                fontWeight: FontWeight.w900,
              ),
            ),
            Text(
              'Pelatihan, sekolah lapang & pasar tani',
              style: TextStyle(
                color: HomeColors.textSecondary,
                fontSize: 11,
                fontWeight: FontWeight.w600,
              ),
            ),
          ],
        ),
        actions: [
          IconButton(
            tooltip: 'Tiket Saya',
            icon: Badge(
              isLabelVisible: myTicketsCount > 0,
              label: Text('$myTicketsCount'),
              backgroundColor: HomeColors.primaryGreen,
              child: const Icon(Icons.confirmation_number_outlined, color: HomeColors.textPrimary),
            ),
            onPressed: () {
              setState(() => _selectedCategory = 'my_tickets');
            },
          ),
          IconButton(
            tooltip: 'Admin: Buat Acara Baru',
            icon: const Icon(Icons.add_circle_outline_rounded, color: HomeColors.primaryGreen),
            onPressed: () => context.push('/events/create'),
          ),
          const SizedBox(width: 4),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => context.push('/events/create'),
        backgroundColor: HomeColors.primaryGreen,
        foregroundColor: Colors.white,
        icon: const Icon(Icons.add_rounded),
        label: const Text('Buat Acara Baru', style: TextStyle(fontWeight: FontWeight.w800)),
      ),
      body: SafeArea(
        child: Column(
          children: [
            // Search Input with Debounce
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 4, 16, 8),
              child: Container(
                height: 42,
                decoration: BoxDecoration(
                  color: HomeColors.surface,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: HomeColors.border, width: 1),
                ),
                child: TextField(
                  controller: _searchController,
                  onChanged: (_) => _searchDebouncer.run(() => setState(() {})),
                  style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: HomeColors.textPrimary),
                  decoration: InputDecoration(
                    isDense: true,
                    filled: true,
                    fillColor: HomeColors.surface,
                    hintText: 'Cari acara, topik, pembicara, atau lokasi...',
                    hintStyle: const TextStyle(fontSize: 12, color: HomeColors.textSecondary),
                    prefixIcon: const Icon(Icons.search_rounded, size: 20, color: HomeColors.primaryGreen),
                    suffixIcon: _searchController.text.isNotEmpty
                        ? IconButton(
                            icon: const Icon(Icons.cancel_rounded, size: 16, color: HomeColors.textSecondary),
                            onPressed: () {
                              _searchController.clear();
                              setState(() {});
                            },
                          )
                        : null,
                    border: InputBorder.none,
                    enabledBorder: InputBorder.none,
                    focusedBorder: InputBorder.none,
                    contentPadding: const EdgeInsets.symmetric(horizontal: 10, vertical: 10),
                  ),
                ),
              ),
            ),

            // Category Chips Bar
            SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.fromLTRB(16, 6, 16, 12),
              child: Row(
                children: _filterCategories.map((c) {
                  final isSelected = _selectedCategory == c['value'];
                  return Padding(
                    padding: const EdgeInsets.only(right: 8),
                    child: FilterChip(
                      selected: isSelected,
                      label: Text(c['label']!),
                      labelStyle: TextStyle(
                        color: isSelected ? Colors.white : HomeColors.textPrimary,
                        fontSize: 12,
                        fontWeight: FontWeight.w700,
                      ),
                      backgroundColor: HomeColors.surface,
                      selectedColor: HomeColors.primaryGreen,
                      side: BorderSide(
                        color: isSelected ? HomeColors.primaryGreen : HomeColors.border,
                      ),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(HomeRadius.pill),
                      ),
                      onSelected: (selected) {
                        setState(() => _selectedCategory = c['value']!);
                      },
                    ),
                  );
                }).toList(),
              ),
            ),

            // Events List
            Expanded(
              child: RefreshIndicator(
                color: HomeColors.primaryGreen,
                onRefresh: () async {
                  await ref.read(eventsProvider.notifier).loadEventsFromApi();
                  await ref.read(mySubmissionsProvider.notifier).loadSubmissions();
                },
                child: filteredEvents.isEmpty
                    ? ListView(
                        physics: const AlwaysScrollableScrollPhysics(),
                        children: [
                          SizedBox(
                            height: MediaQuery.of(context).size.height * 0.45,
                            child: Center(
                              child: Column(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Icon(
                                    _selectedCategory == 'my_tickets'
                                        ? Icons.confirmation_number_outlined
                                        : (_selectedCategory == 'my_submissions'
                                            ? Icons.assignment_outlined
                                            : Icons.event_busy_rounded),
                                    size: 56,
                                    color: Colors.grey.shade400,
                                  ),
                                  const SizedBox(height: 12),
                                  Text(
                                    _selectedCategory == 'my_tickets'
                                        ? 'Belum Ada Tiket Acara Terdaftar'
                                        : (_selectedCategory == 'my_submissions'
                                            ? 'Belum Ada Pengajuan Acara'
                                            : 'Tidak ada acara dalam kategori ini.'),
                                    style: const TextStyle(
                                      fontSize: 14,
                                      fontWeight: FontWeight.w700,
                                      color: HomeColors.textPrimary,
                                    ),
                                  ),
                                  const SizedBox(height: 4),
                                  Padding(
                                    padding: const EdgeInsets.symmetric(horizontal: 32),
                                    child: Text(
                                      _selectedCategory == 'my_tickets'
                                          ? 'Daftar pada pelatihan atau bazar pertanian untuk mendapatkan tiket digital Anda.'
                                          : (_selectedCategory == 'my_submissions'
                                              ? 'Ajukan agenda pelatihan atau pertemuan kelompok tani untuk ditinjau oleh dinas/admin.'
                                              : 'Silakan pilih kategori acara lain.'),
                                      textAlign: TextAlign.center,
                                      style: const TextStyle(
                                        fontSize: 12,
                                        color: HomeColors.textSecondary,
                                      ),
                                    ),
                                  ),
                                  if (_selectedCategory == 'my_tickets') ...[
                                    const SizedBox(height: 16),
                                    FilledButton.tonal(
                                      onPressed: () => setState(() => _selectedCategory = 'all'),
                                      style: FilledButton.styleFrom(
                                        foregroundColor: HomeColors.primaryGreen,
                                      ),
                                      child: const Text('Jelajahi Semua Acara'),
                                    ),
                                  ] else if (_selectedCategory == 'my_submissions') ...[
                                    const SizedBox(height: 16),
                                    FilledButton.icon(
                                      onPressed: () => context.push('/events/create'),
                                      icon: const Icon(Icons.add_rounded, size: 18),
                                      label: const Text('Ajukan Acara Baru'),
                                      style: FilledButton.styleFrom(
                                        backgroundColor: HomeColors.primaryGreen,
                                        foregroundColor: Colors.white,
                                      ),
                                    ),
                                  ],
                                ],
                              ),
                            ),
                          ),
                        ],
                      )
                    : ListView.separated(
                        physics: const AlwaysScrollableScrollPhysics(),
                        padding: const EdgeInsets.fromLTRB(16, 0, 16, 80),
                        itemCount: filteredEvents.length,
                        separatorBuilder: (context, index) => const SizedBox(height: 14),
                        itemBuilder: (context, index) {
                          final event = filteredEvents[index];
                          return Center(
                            child: ConstrainedBox(
                              constraints: const BoxConstraints(maxWidth: 680),
                              child: _buildEventItem(event),
                            ),
                          );
                        },
                      ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildEventItem(EventModel event) {
    return Container(
      decoration: BoxDecoration(
        color: HomeColors.surface,
        borderRadius: BorderRadius.circular(HomeRadius.xl),
        border: Border.all(
          color: event.isRegistered ? const Color(0xFF86EFAC) : HomeColors.border,
          width: event.isRegistered ? 1.5 : 1,
        ),
        boxShadow: HomeShadows.subtle,
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(HomeRadius.xl),
          onTap: () => context.push('/events/detail', extra: event),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Cover Image
              SizedBox(
                height: 130,
                width: double.infinity,
                child: ClipRRect(
                  borderRadius: const BorderRadius.vertical(top: Radius.circular(HomeRadius.xl - 1)),
                  child: Stack(
                    fit: StackFit.expand,
                    children: [
                      Image.asset(
                        event.assetImage,
                        fit: BoxFit.cover,
                        errorBuilder: (context, error, stackTrace) => Container(color: HomeColors.deepGreen),
                      ),
                      Container(
                        decoration: BoxDecoration(
                          gradient: LinearGradient(
                            begin: Alignment.topCenter,
                            end: Alignment.bottomCenter,
                            colors: [
                              Colors.transparent,
                              Colors.black.withValues(alpha: 0.75),
                            ],
                          ),
                        ),
                      ),
                      Positioned(
                        top: 10,
                        left: 10,
                        child: Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: Colors.black.withValues(alpha: 0.5),
                            borderRadius: BorderRadius.circular(HomeRadius.pill),
                          ),
                          child: Text(
                            event.categoryLabel,
                            style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.w800),
                          ),
                        ),
                      ),
                      if (event.isPending)
                        Positioned(
                          top: 10,
                          right: 10,
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
                            decoration: BoxDecoration(
                              color: const Color(0xFFD97706),
                              borderRadius: BorderRadius.circular(HomeRadius.pill),
                              boxShadow: [
                                BoxShadow(
                                  color: Colors.black.withValues(alpha: 0.2),
                                  blurRadius: 4,
                                ),
                              ],
                            ),
                            child: const Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Icon(Icons.hourglass_top_rounded, color: Colors.white, size: 12),
                                SizedBox(width: 4),
                                Text(
                                  'Menunggu Persetujuan',
                                  style: TextStyle(
                                    color: Colors.white,
                                    fontSize: 10.5,
                                    fontWeight: FontWeight.w800,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        )
                      else if (event.isRejected)
                        Positioned(
                          top: 10,
                          right: 10,
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
                            decoration: BoxDecoration(
                              color: const Color(0xFFDC2626),
                              borderRadius: BorderRadius.circular(HomeRadius.pill),
                              boxShadow: [
                                BoxShadow(
                                  color: Colors.black.withValues(alpha: 0.2),
                                  blurRadius: 4,
                                ),
                              ],
                            ),
                            child: const Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Icon(Icons.cancel_rounded, color: Colors.white, size: 12),
                                SizedBox(width: 4),
                                Text(
                                  'Ditolak',
                                  style: TextStyle(
                                    color: Colors.white,
                                    fontSize: 10.5,
                                    fontWeight: FontWeight.w800,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        )
                      else if (event.isRegistered)
                        Positioned(
                          top: 10,
                          right: 10,
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
                            decoration: BoxDecoration(
                              color: const Color(0xFF15803D),
                              borderRadius: BorderRadius.circular(HomeRadius.pill),
                              boxShadow: [
                                BoxShadow(
                                  color: Colors.black.withValues(alpha: 0.2),
                                  blurRadius: 4,
                                ),
                              ],
                            ),
                            child: const Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Icon(Icons.confirmation_number_rounded, color: Colors.white, size: 12),
                                SizedBox(width: 4),
                                Text(
                                  'Tiket Aktif',
                                  style: TextStyle(
                                    color: Colors.white,
                                    fontSize: 10.5,
                                    fontWeight: FontWeight.w800,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      Positioned(
                        bottom: 10,
                        left: 10,
                        child: Text(
                          event.countdownText,
                          style: const TextStyle(
                            color: Color(0xFFFDE68A),
                            fontSize: 12,
                            fontWeight: FontWeight.w900,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),

              // Content Details
              Padding(
                padding: const EdgeInsets.all(HomeSpacing.cardPadding),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      event.title,
                      style: const TextStyle(
                        fontSize: 15.5,
                        fontWeight: FontWeight.w900,
                        color: HomeColors.textPrimary,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        const Icon(Icons.calendar_month_rounded, size: 14, color: HomeColors.primaryGreen),
                        const SizedBox(width: 4),
                        Text(
                          '${event.formattedDate} • ${event.eventTime}',
                          style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w600, color: HomeColors.textSecondary),
                        ),
                      ],
                    ),
                    const SizedBox(height: 4),
                    Row(
                      children: [
                        const Icon(Icons.location_on_rounded, size: 14, color: HomeColors.textSecondary),
                        const SizedBox(width: 4),
                        Expanded(
                          child: Text(
                            event.locationName,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: HomeTypography.supporting,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
