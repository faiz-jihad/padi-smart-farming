  import 'package:flutter/material.dart';
  import 'package:flutter_riverpod/flutter_riverpod.dart';

  import 'package:padi/core/localization/app_language.dart';
  import 'package:padi/features/event/data/models/event_model.dart';
  import 'package:padi/features/event/data/providers/event_providers.dart';
  import 'package:padi/features/event/presentation/screens/create_event_screen.dart';
  import 'package:padi/features/home/presentation/tokens/home_tokens.dart';

  class UpcomingEventsBanner extends ConsumerStatefulWidget {
    const UpcomingEventsBanner({
      super.key,
      required this.onEventTap,
      required this.onCreateEventTap,
      required this.onViewAllTap,
      this.isAdmin = false,
    });

    final ValueChanged<EventModel> onEventTap;
    final VoidCallback onCreateEventTap;
    final VoidCallback onViewAllTap;
    final bool isAdmin;

    @override
    ConsumerState<UpcomingEventsBanner> createState() =>
        _UpcomingEventsBannerState();
  }

  class _UpcomingEventsBannerState
      extends ConsumerState<UpcomingEventsBanner> {
    final PageController _pageController = PageController(
      viewportFraction: 0.92,
    );

    int _currentPage = 0;

    @override
    void dispose() {
      _pageController.dispose();
      super.dispose();
    }

    // ============================================================
    // CREATE EVENT
    // ============================================================
    void _openCreateEvent() {
      Navigator.of(context).push(
        MaterialPageRoute(
          builder: (_) => const CreateEventScreen(),
        ),
      );
    }

    @override
    Widget build(BuildContext context) {
      final lang = ref.watch(languageProvider);
      final s = AppStrings(lang);

      final events = ref.watch(eventsProvider);

      final upcomingEvents = events
          .where((e) => e.isUpcoming)
          .toList();

      if (upcomingEvents.isEmpty) {
        return const SizedBox.shrink();
      }

      return Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // ========================================================
          // SECTION HEADER
          // ========================================================
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 2),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(5),
                      decoration: BoxDecoration(
                        color: HomeColors.lightGreen,
                        borderRadius: BorderRadius.circular(
                          HomeRadius.sm,
                        ),
                      ),
                      child: const Icon(
                        Icons.event_available_rounded,
                        color: HomeColors.primaryGreen,
                        size: 17,
                      ),
                    ),
                    const SizedBox(width: 8),
                    Text(
                      s.agroEventsTitle,
                      style: HomeTypography.sectionTitle,
                    ),
                  ],
                ),

                // ==================================================
                // RIGHT SIDE
                // + CREATE EVENT
                // + VIEW ALL
                // ==================================================
                Row(
                  children: [
                    IconButton(
                      tooltip: 'Buat Acara Baru',

                      // =================================================
                      // PENTING:
                      // LANGSUNG BUKA CreateEventScreen
                      // TIDAK MELALUI /events/create
                      // =================================================
                      onPressed: _openCreateEvent,

                      style: IconButton.styleFrom(
                        backgroundColor: HomeColors.surfaceMuted,
                        padding: const EdgeInsets.all(6),
                        minimumSize: const Size(32, 32),
                      ),

                      icon: const Icon(
                        Icons.add_circle_outline_rounded,
                        color: HomeColors.primaryGreen,
                        size: 18,
                      ),
                    ),

                    const SizedBox(width: 4),

                    InkWell(
                      onTap: widget.onViewAllTap,
                      borderRadius: BorderRadius.circular(
                        HomeRadius.pill,
                      ),
                      child: Padding(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 6,
                          vertical: 4,
                        ),
                        child: Text(
                          s.viewAll,
                          style: const TextStyle(
                            color: HomeColors.primaryGreen,
                            fontSize: 12.5,
                            fontWeight: FontWeight.w800,
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),

          const SizedBox(height: HomeSpacing.sm),

          // ========================================================
          // CAROUSEL
          // ========================================================
          SizedBox(
            height: 200,
            child: PageView.builder(
              controller: _pageController,
              itemCount: upcomingEvents.length,

              onPageChanged: (index) {
                setState(() {
                  _currentPage = index;
                });
              },

              itemBuilder: (context, index) {
                final event = upcomingEvents[index];

                return Padding(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 4,
                  ),
                  child: _buildEventCard(event),
                );
              },
            ),
          ),

          const SizedBox(height: 8),

          // ========================================================
          // INDICATOR DOTS
          // ========================================================
          if (upcomingEvents.length > 1)
            Center(
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: List.generate(
                  upcomingEvents.length,
                  (index) => AnimatedContainer(
                    duration: const Duration(
                      milliseconds: 200,
                    ),
                    margin: const EdgeInsets.symmetric(
                      horizontal: 3,
                    ),
                    width: _currentPage == index ? 20 : 6,
                    height: 6,
                    decoration: BoxDecoration(
                      color: _currentPage == index
                          ? HomeColors.primaryGreen
                          : HomeColors.border,
                      borderRadius: BorderRadius.circular(
                        HomeRadius.pill,
                      ),
                    ),
                  ),
                ),
              ),
            ),
        ],
      );
    }

    // ============================================================
    // EVENT CARD
    // ============================================================
    Widget _buildEventCard(EventModel event) {
      return ClipRRect(
        borderRadius: BorderRadius.circular(
          HomeRadius.xl,
        ),
        child: SizedBox(
          height: 200,
          child: Stack(
            fit: StackFit.expand,
            children: [
              // ======================================================
              // BACKGROUND IMAGE
              // ======================================================
              Image.asset(
                event.assetImage,
                fit: BoxFit.cover,

                errorBuilder: (
                  context,
                  error,
                  stackTrace,
                ) {
                  return Container(
                    color: HomeColors.deepGreen,
                    child: const Icon(
                      Icons.landscape_rounded,
                      color: Colors.white24,
                      size: 60,
                    ),
                  );
                },
              ),

              // ======================================================
              // DARK GRADIENT
              // ======================================================
              DecoratedBox(
                decoration: BoxDecoration(
                  gradient: LinearGradient(
                    begin: Alignment.topCenter,
                    end: Alignment.bottomCenter,
                    colors: [
                      Colors.black.withOpacity(0.25),
                      const Color(0xFF042F1E)
                          .withOpacity(0.88),
                      const Color(0xFF021B11)
                          .withOpacity(0.98),
                    ],
                    stops: const [
                      0.0,
                      0.55,
                      1.0,
                    ],
                  ),
                ),
              ),

              // ======================================================
              // TAPPABLE EVENT CARD
              // ======================================================
              Material(
                color: Colors.transparent,

                child: InkWell(
                  onTap: () {
                    widget.onEventTap(event);
                  },

                  child: Padding(
                    padding: const EdgeInsets.all(14),

                    child: Column(
                      crossAxisAlignment:
                          CrossAxisAlignment.start,

                      mainAxisAlignment:
                          MainAxisAlignment.spaceBetween,

                      children: [
                        // ==================================================
                        // TOP ROW
                        // ==================================================
                        Row(
                          mainAxisAlignment:
                              MainAxisAlignment.spaceBetween,

                          children: [
                            // CATEGORY
                            Container(
                              padding:
                                  const EdgeInsets.symmetric(
                                horizontal: 8,
                                vertical: 3,
                              ),

                              decoration:
                                  BoxDecoration(
                                color: Colors.white
                                    .withOpacity(0.22),

                                borderRadius:
                                    BorderRadius.circular(
                                  HomeRadius.pill,
                                ),
                              ),

                              child: Text(
                                event.categoryLabel
                                    .toUpperCase(),

                                style:
                                    const TextStyle(
                                  color: Colors.white,
                                  fontSize: 9,
                                  fontWeight:
                                      FontWeight.w900,
                                  letterSpacing: 0.4,
                                ),
                              ),
                            ),

                            // COUNTDOWN
                            Container(
                              padding:
                                  const EdgeInsets.symmetric(
                                horizontal: 8,
                                vertical: 3,
                              ),

                              decoration:
                                  BoxDecoration(
                                color:
                                    const Color(0xFFF59E0B),

                                borderRadius:
                                    BorderRadius.circular(
                                  HomeRadius.pill,
                                ),
                              ),

                              child: Text(
                                event.countdownText,

                                style:
                                    const TextStyle(
                                  color: Colors.white,
                                  fontSize: 9.5,
                                  fontWeight:
                                      FontWeight.w900,
                                ),
                              ),
                            ),
                          ],
                        ),

                        // ==================================================
                        // BOTTOM CONTENT
                        // ==================================================
                        Column(
                          crossAxisAlignment:
                              CrossAxisAlignment.start,

                          children: [
                            // TITLE
                            Text(
                              event.title,

                              maxLines: 2,

                              overflow:
                                  TextOverflow.ellipsis,

                              style:
                                  const TextStyle(
                                color: Colors.white,
                                fontSize: 15,
                                fontWeight:
                                    FontWeight.w900,
                                height: 1.2,
                              ),
                            ),

                            const SizedBox(height: 6),

                            // =================================================
                            // DATE & TIME
                            // =================================================
                            Row(
                              children: [
                                const Icon(
                                  Icons
                                      .calendar_today_rounded,
                                  color:
                                      Color(0xFFFDE68A),
                                  size: 12,
                                ),

                                const SizedBox(width: 4),

                                Expanded(
                                  child: Text(
                                    '${event.formattedDate} • ${event.eventTime}',

                                    maxLines: 1,

                                    overflow:
                                        TextOverflow.ellipsis,

                                    style:
                                        const TextStyle(
                                      color:
                                          Color(0xFFFDE68A),
                                      fontSize: 11,
                                      fontWeight:
                                          FontWeight.w700,
                                    ),
                                  ),
                                ),
                              ],
                            ),

                            const SizedBox(height: 3),

                            // =================================================
                            // LOCATION + DETAIL
                            // =================================================
                            Row(
                              children: [
                                const Icon(
                                  Icons
                                      .location_on_rounded,
                                  color:
                                      Colors.white70,
                                  size: 12,
                                ),

                                const SizedBox(width: 4),

                                Expanded(
                                  child: Text(
                                    event.locationName,

                                    maxLines: 1,

                                    overflow:
                                        TextOverflow.ellipsis,

                                    style: TextStyle(
                                      color: Colors.white
                                          .withOpacity(
                                        0.85,
                                      ),
                                      fontSize: 11,
                                    ),
                                  ),
                                ),

                                const SizedBox(width: 8),

                                // DETAIL BUTTON
                                Container(
                                  padding:
                                      const EdgeInsets
                                          .symmetric(
                                    horizontal: 10,
                                    vertical: 4,
                                  ),

                                  decoration:
                                      BoxDecoration(
                                    color: Colors.white,

                                    borderRadius:
                                        BorderRadius.circular(
                                      HomeRadius.pill,
                                    ),
                                  ),

                                  child: const Row(
                                    mainAxisSize:
                                        MainAxisSize.min,

                                    children: [
                                      Text(
                                        'Detail',

                                        style:
                                            TextStyle(
                                          color:
                                              HomeColors
                                                  .deepGreen,
                                          fontSize: 11,
                                          fontWeight:
                                              FontWeight
                                                  .w900,
                                        ),
                                      ),

                                      SizedBox(width: 2),

                                      Icon(
                                        Icons
                                            .chevron_right_rounded,
                                        color:
                                            HomeColors
                                                .deepGreen,
                                        size: 14,
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      );
    }
  }