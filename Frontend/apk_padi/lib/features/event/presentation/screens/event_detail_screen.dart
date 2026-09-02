import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:padi/features/event/data/models/event_model.dart';
import 'package:padi/features/event/data/providers/event_providers.dart';
import 'package:padi/features/event/presentation/widgets/event_ticket_dialog.dart';
import 'package:padi/features/home/presentation/tokens/home_tokens.dart';

class EventDetailScreen extends ConsumerStatefulWidget {
  const EventDetailScreen({super.key, required this.event});

  final EventModel event;

  @override
  ConsumerState<EventDetailScreen> createState() => _EventDetailScreenState();
}

class _EventDetailScreenState extends ConsumerState<EventDetailScreen> {
  bool _isRegistering = false;

  void _handleRegistration(EventModel currentEvent) async {
    if (currentEvent.isRegistered || _isRegistering) {
      _showTicketDialog(currentEvent);
      return;
    }

    setState(() => _isRegistering = true);

    try {
      final updated = await ref.read(eventsProvider.notifier).registerForEvent(currentEvent.id);
      final activeEvent = updated ?? currentEvent.copyWith(isRegistered: true);

      if (!mounted) return;

      showDialog<void>(
        context: context,
        builder: (dialogCtx) => AlertDialog(
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(HomeRadius.xl),
          ),
          backgroundColor: Colors.white,
          title: const Row(
            children: [
              Icon(
                Icons.check_circle_rounded,
                color: HomeColors.primaryGreen,
                size: 26,
              ),
              SizedBox(width: 10),
              Expanded(
                child: Text(
                  'Pendaftaran Berhasil!',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w900,
                    color: HomeColors.textPrimary,
                  ),
                ),
              ),
            ],
          ),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                'Anda telah resmi terdaftar pada acara "${activeEvent.title}". Tiket digital Anda telah aktif!',
                style: const TextStyle(
                  fontSize: 13,
                  height: 1.45,
                  color: HomeColors.textPrimary,
                ),
              ),
              const SizedBox(height: 12),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                decoration: BoxDecoration(
                  color: HomeColors.primaryGreen.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: HomeColors.primaryGreen.withValues(alpha: 0.3)),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.confirmation_number_rounded, color: HomeColors.primaryGreen, size: 18),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        activeEvent.ticketCode ?? 'TKT-PAD-${activeEvent.id.toString().padLeft(3, '0')}-ACTIVE',
                        style: const TextStyle(
                          fontFamily: 'monospace',
                          fontWeight: FontWeight.w800,
                          fontSize: 12.5,
                          color: HomeColors.primaryGreen,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(dialogCtx),
              child: const Text('Nanti Saja', style: TextStyle(color: HomeColors.textSecondary)),
            ),
            FilledButton.icon(
              onPressed: () {
                Navigator.pop(dialogCtx);
                _showTicketDialog(activeEvent);
              },
              icon: const Icon(Icons.qr_code_rounded, size: 16),
              label: const Text('Buka E-Tiket', style: TextStyle(fontWeight: FontWeight.w800)),
              style: FilledButton.styleFrom(
                backgroundColor: HomeColors.primaryGreen,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(HomeRadius.md),
                ),
              ),
            ),
          ],
        ),
      );
    } finally {
      if (mounted) {
        setState(() => _isRegistering = false);
      }
    }
  }

  void _showTicketDialog(EventModel event) {
    EventTicketDialog.show(
      context,
      event: event,
    );
  }

  @override
  Widget build(BuildContext context) {
    // Watch eventsProvider reaktif agar jika event diupdate di state, detail langsung sinkron
    final allEvents = ref.watch(eventsProvider);
    final event = allEvents.firstWhere(
      (e) => e.id == widget.event.id,
      orElse: () => widget.event,
    );

    final isRegistered = event.isRegistered;
    final registeredCount = event.registeredCount;
    final safeQuota = event.quota > 0 ? event.quota : 50;
    final progress = (registeredCount / safeQuota).clamp(0.0, 1.0).toDouble();
    final remainingQuota = (safeQuota - registeredCount)
        .clamp(0, safeQuota)
        .toInt();
    final isFull = remainingQuota <= 0 && !isRegistered;

    return Scaffold(
      backgroundColor: HomeColors.background,
      appBar: AppBar(
        backgroundColor: HomeColors.primaryGreen,
        foregroundColor: Colors.white,
        elevation: 0,
        scrolledUnderElevation: 0,
        leading: IconButton(
          tooltip: 'Kembali',
          icon: const Icon(Icons.arrow_back_rounded, size: 28),
          onPressed: () {
            if (context.canPop()) {
              context.pop();
            } else {
              context.go('/events');
            }
          },
        ),
        title: Text(
          event.categoryLabel,
          maxLines: 1,
          overflow: TextOverflow.ellipsis,
          style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900),
        ),
        actions: [
          if (isRegistered)
            IconButton(
              tooltip: 'Lihat E-Tiket',
              icon: const Icon(Icons.qr_code_2_rounded, size: 24),
              onPressed: () => _showTicketDialog(event),
            ),
        ],
      ),
      body: SafeArea(
        top: false,
        child: Center(
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 680),
            child: ListView(
              physics: const AlwaysScrollableScrollPhysics(
                parent: BouncingScrollPhysics(),
              ),
              padding: const EdgeInsets.fromLTRB(16, 16, 16, 24),
              children: [
                if (isRegistered) ...[
                  Container(
                    margin: const EdgeInsets.only(bottom: 14),
                    padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF0FDF4),
                      borderRadius: BorderRadius.circular(HomeRadius.md),
                      border: Border.all(color: const Color(0xFF86EFAC)),
                    ),
                    child: Row(
                      children: [
                        const Icon(Icons.check_circle_rounded, color: HomeColors.primaryGreen, size: 22),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              const Text(
                                'Anda Sudah Terdaftar pada Acara Ini',
                                style: TextStyle(
                                  fontSize: 13,
                                  fontWeight: FontWeight.w800,
                                  color: Color(0xFF166534),
                                ),
                              ),
                              Text(
                                'Nomor Tiket: ${event.ticketCode ?? "TKT-PAD-RESMI"}',
                                style: const TextStyle(
                                  fontSize: 11.5,
                                  fontFamily: 'monospace',
                                  fontWeight: FontWeight.w700,
                                  color: Color(0xFF15803D),
                                ),
                              ),
                            ],
                          ),
                        ),
                        TextButton.icon(
                          onPressed: () => _showTicketDialog(event),
                          icon: const Icon(Icons.open_in_new_rounded, size: 14, color: HomeColors.primaryGreen),
                          label: const Text(
                            'E-Tiket',
                            style: TextStyle(fontSize: 12, fontWeight: FontWeight.w800, color: HomeColors.primaryGreen),
                          ),
                          style: TextButton.styleFrom(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                            minimumSize: Size.zero,
                            tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
                _buildHeroCard(event),
                const SizedBox(height: HomeSpacing.md),
                _buildInfoCard(event),
                const SizedBox(height: HomeSpacing.md),
                _buildQuotaCard(
                  registeredCount: registeredCount,
                  safeQuota: safeQuota,
                  remainingQuota: remainingQuota,
                  progress: progress,
                ),
                const SizedBox(height: HomeSpacing.md),
                _buildDescriptionCard(event),
              ],
            ),
          ),
        ),
      ),
      bottomNavigationBar: SafeArea(
        minimum: const EdgeInsets.fromLTRB(16, 10, 16, 16),
        child: Center(
          heightFactor: 1,
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 680),
            child: SizedBox(
              width: double.infinity,
              height: 52,
              child: FilledButton.icon(
                onPressed: _isRegistering
                    ? null
                    : (isRegistered
                        ? () => _showTicketDialog(event)
                        : (isFull ? null : () => _handleRegistration(event))),
                icon: Icon(
                  isRegistered
                      ? Icons.qr_code_scanner_rounded
                      : (isFull ? Icons.block_rounded : Icons.how_to_reg_rounded),
                  size: 20,
                ),
                label: Text(
                  _isRegistering
                      ? 'Memproses Pendaftaran...'
                      : (isRegistered
                          ? 'Lihat E-Tiket Saya'
                          : (isFull ? 'Kuota Penuh' : 'Daftar Acara (Gratis)')),
                  style: const TextStyle(
                    fontSize: 14.5,
                    fontWeight: FontWeight.w800,
                  ),
                ),
                style: FilledButton.styleFrom(
                  backgroundColor: isRegistered
                      ? const Color(0xFF15803D)
                      : (isFull ? Colors.grey.shade400 : HomeColors.primaryGreen),
                  foregroundColor: Colors.white,
                  elevation: 0,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(HomeRadius.md),
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildHeroCard(EventModel event) {
    return Container(
      clipBehavior: Clip.antiAlias,
      decoration: BoxDecoration(
        color: HomeColors.deepGreen,
        borderRadius: BorderRadius.circular(HomeRadius.xl),
        boxShadow: HomeShadows.subtle,
      ),
      child: Stack(
        children: [
          AspectRatio(aspectRatio: 16 / 9, child: _buildHeroImage(event)),
          Positioned.fill(
            child: DecoratedBox(
              decoration: BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.topCenter,
                  end: Alignment.bottomCenter,
                  colors: [
                    Colors.transparent,
                    Colors.black.withOpacity(0.22),
                    Colors.black.withOpacity(0.82),
                  ],
                  stops: const [0, 0.45, 1],
                ),
              ),
            ),
          ),
          Positioned(
            left: 16,
            right: 16,
            bottom: 16,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Wrap(
                  spacing: 8,
                  runSpacing: 6,
                  children: [
                    _buildHeroBadge(
                      event.countdownText,
                      backgroundColor: const Color(0xFFF59E0B),
                    ),
                    _buildHeroBadge(
                      event.categoryLabel.toUpperCase(),
                      backgroundColor: Colors.white.withOpacity(0.24),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                Text(
                  event.title,
                  maxLines: 3,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(
                    color: Colors.white,
                    fontSize: 18,
                    fontWeight: FontWeight.w900,
                    height: 1.22,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildInfoCard(EventModel event) {
    return _buildSurfaceCard(
      child: Column(
        children: [
          _buildInfoRow(
            icon: Icons.calendar_month_rounded,
            title: event.formattedDate,
            subtitle: 'Pukul: ${event.eventTime}',
          ),
          const Divider(height: 20, color: HomeColors.borderSubtle),
          _buildInfoRow(
            icon: Icons.location_on_rounded,
            title: event.locationName,
            subtitle: event.locationAddress ?? 'Indramayu, Jawa Barat',
          ),
          const Divider(height: 20, color: HomeColors.borderSubtle),
          _buildInfoRow(
            icon: Icons.groups_rounded,
            title: event.organizer,
            subtitle: event.speaker != null
                ? 'Pakar: ${event.speaker}'
                : 'Penyelenggara Resmi Pertanian',
          ),
        ],
      ),
    );
  }

  Widget _buildQuotaCard({
    required int registeredCount,
    required int safeQuota,
    required int remainingQuota,
    required double progress,
  }) {
    return _buildSurfaceCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Expanded(
                child: Text(
                  'Kuota Pendaftaran Peserta',
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(
                    fontWeight: FontWeight.w800,
                    fontSize: 13.5,
                    color: HomeColors.textPrimary,
                  ),
                ),
              ),
              const SizedBox(width: 10),
              _buildStatusPill('GRATIS'),
            ],
          ),
          const SizedBox(height: 10),
          ClipRRect(
            borderRadius: BorderRadius.circular(HomeRadius.pill),
            child: LinearProgressIndicator(
              value: progress,
              minHeight: 8,
              backgroundColor: HomeColors.surfaceMuted,
              valueColor: const AlwaysStoppedAnimation<Color>(
                HomeColors.primaryGreen,
              ),
            ),
          ),
          const SizedBox(height: 8),
          Row(
            children: [
              Expanded(
                child: Text(
                  'Terisi $registeredCount dari $safeQuota kursi',
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  style: HomeTypography.supporting,
                ),
              ),
              const SizedBox(width: 10),
              Flexible(
                child: Text(
                  'Sisa $remainingQuota slot',
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  textAlign: TextAlign.end,
                  style: const TextStyle(
                    color: HomeColors.primaryGreen,
                    fontSize: 11.5,
                    fontWeight: FontWeight.w800,
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildDescriptionCard(EventModel event) {
    return _buildSurfaceCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Tentang Acara & Materi',
            style: TextStyle(
              color: HomeColors.textPrimary,
              fontSize: 15,
              fontWeight: FontWeight.w800,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            event.description,
            style: const TextStyle(
              color: HomeColors.textPrimary,
              fontSize: 13.5,
              height: 1.55,
            ),
          ),
          if (event.contactPerson != null) ...[
            const SizedBox(height: 14),
            const Divider(height: 1, color: HomeColors.borderSubtle),
            const SizedBox(height: 14),
            Row(
              children: [
                const Icon(
                  Icons.support_agent_rounded,
                  color: HomeColors.primaryGreen,
                  size: 20,
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(
                    'Kontak Panitia: ${event.contactPerson}',
                    style: const TextStyle(
                      fontSize: 12.5,
                      fontWeight: FontWeight.w700,
                      color: HomeColors.textPrimary,
                    ),
                  ),
                ),
              ],
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildSurfaceCard({required Widget child}) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(HomeSpacing.cardPadding),
      decoration: BoxDecoration(
        color: HomeColors.surface,
        borderRadius: BorderRadius.circular(HomeRadius.xl),
        border: Border.all(color: HomeColors.border),
        boxShadow: HomeShadows.subtle,
      ),
      child: child,
    );
  }

  Widget _buildInfoRow({
    required IconData icon,
    required String title,
    required String subtitle,
  }) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Container(
          padding: const EdgeInsets.all(8),
          decoration: BoxDecoration(
            color: HomeColors.lightGreen,
            borderRadius: BorderRadius.circular(HomeRadius.sm),
          ),
          child: Icon(icon, color: HomeColors.primaryGreen, size: 20),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                title,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(
                  color: HomeColors.textPrimary,
                  fontSize: 14,
                  fontWeight: FontWeight.w800,
                ),
              ),
              const SizedBox(height: 2),
              Text(
                subtitle,
                maxLines: 3,
                overflow: TextOverflow.ellipsis,
                style: HomeTypography.supporting,
              ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildHeroBadge(String label, {required Color backgroundColor}) {
    return ConstrainedBox(
      constraints: const BoxConstraints(maxWidth: 260),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
        decoration: BoxDecoration(
          color: backgroundColor,
          borderRadius: BorderRadius.circular(HomeRadius.pill),
        ),
        child: Text(
          label,
          maxLines: 1,
          overflow: TextOverflow.ellipsis,
          style: const TextStyle(
            color: Colors.white,
            fontSize: 10,
            fontWeight: FontWeight.w900,
          ),
        ),
      ),
    );
  }

  Widget _buildStatusPill(String label) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2.5),
      decoration: BoxDecoration(
        color: HomeColors.lightGreen,
        borderRadius: BorderRadius.circular(HomeRadius.pill),
      ),
      child: Text(
        label,
        style: const TextStyle(
          color: HomeColors.primaryGreen,
          fontSize: 10.5,
          fontWeight: FontWeight.w900,
        ),
      ),
    );
  }

  Widget _buildHeroImage(EventModel event) {
    final imageUrl = event.imageUrl;
    if (imageUrl != null && imageUrl.startsWith('http')) {
      return Image.network(
        imageUrl,
        fit: BoxFit.cover,
        errorBuilder: (context, error, stackTrace) => _buildFallbackHeroImage(),
      );
    }

    final assetImage = event.assetImage.isNotEmpty
        ? event.assetImage
        : 'assets/images/onboarding_1.jpeg';

    return Image.asset(
      assetImage,
      fit: BoxFit.cover,
      errorBuilder: (context, error, stackTrace) => _buildFallbackHeroImage(),
    );
  }

  Widget _buildFallbackHeroImage() {
    return Container(
      color: HomeColors.deepGreen,
      child: const Center(
        child: Icon(
          Icons.event_available_rounded,
          color: Colors.white70,
          size: 48,
        ),
      ),
    );
  }
}
