import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
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

  final List<Map<String, String>> _filterCategories = [
    {'value': 'all', 'label': 'Semua Acara'},
    {'value': 'workshop', 'label': 'Workshop'},
    {'value': 'field_day', 'label': 'Sekolah Lapang'},
    {'value': 'bazaar', 'label': 'Bazar Tani'},
    {'value': 'irrigation', 'label': 'Gilir Air'},
  ];

  @override
  Widget build(BuildContext context) {
    final allEvents = ref.watch(eventsProvider);
    final filteredEvents = _selectedCategory == 'all'
        ? allEvents
        : allEvents.where((e) => e.category == _selectedCategory).toList();

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
              child: filteredEvents.isEmpty
                  ? const Center(
                      child: Text('Tidak ada acara dalam kategori ini.'),
                    )
                  : ListView.separated(
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
        border: Border.all(color: HomeColors.border),
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
                              Colors.black.withOpacity(0.75),
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
                            color: Colors.black.withOpacity(0.5),
                            borderRadius: BorderRadius.circular(HomeRadius.pill),
                          ),
                          child: Text(
                            event.categoryLabel,
                            style: const TextStyle(color: Colors.white, fontSize: 10, fontWeight: FontWeight.w800),
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
