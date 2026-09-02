import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:padi/features/event/data/models/event_model.dart';
import 'package:padi/features/home/presentation/tokens/home_tokens.dart';

class EventTicketDialog extends StatelessWidget {
  const EventTicketDialog({
    super.key,
    required this.event,
    this.attendeeName,
    this.attendeePhone,
  });

  final EventModel event;
  final String? attendeeName;
  final String? attendeePhone;

  static Future<void> show(
    BuildContext context, {
    required EventModel event,
    String? attendeeName,
    String? attendeePhone,
  }) {
    return showDialog<void>(
      context: context,
      barrierDismissible: true,
      builder: (context) => EventTicketDialog(
        event: event,
        attendeeName: attendeeName,
        attendeePhone: attendeePhone,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final ticketCode = event.ticketCode ??
        'TKT-PAD-${event.id.toString().padLeft(3, '0')}-${event.registeredCount.toString().padLeft(4, '0')}';
    final name = (attendeeName != null && attendeeName!.trim().isNotEmpty)
        ? attendeeName!.trim()
        : 'Petani P.A.D.I.';

    return Dialog(
      backgroundColor: Colors.transparent,
      insetPadding: const EdgeInsets.symmetric(horizontal: 20, vertical: 24),
      child: Center(
        child: ConstrainedBox(
          constraints: const BoxConstraints(maxWidth: 420),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              // Ticket Container
              Container(
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(22),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withValues(alpha: 0.18),
                      blurRadius: 28,
                      offset: const Offset(0, 12),
                    ),
                  ],
                ),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    // Header Ticket (Green)
                    _buildHeader(),

                    // Event Information Section
                    Padding(
                      padding: const EdgeInsets.fromLTRB(20, 18, 20, 14),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(
                              horizontal: 10,
                              vertical: 4,
                            ),
                            decoration: BoxDecoration(
                              color: HomeColors.primaryGreen.withValues(alpha: 0.12),
                              borderRadius: BorderRadius.circular(6),
                            ),
                            child: Text(
                              event.categoryLabel.toUpperCase(),
                              style: const TextStyle(
                                color: HomeColors.primaryGreen,
                                fontSize: 10.5,
                                fontWeight: FontWeight.w800,
                                letterSpacing: 0.6,
                              ),
                            ),
                          ),
                          const SizedBox(height: 8),
                          Text(
                            event.title,
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                            style: const TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.w900,
                              color: HomeColors.textPrimary,
                              height: 1.3,
                            ),
                          ),
                          const SizedBox(height: 16),
                          _buildDetailRow(
                            icon: Icons.calendar_month_rounded,
                            label: 'Waktu Pelaksanaan',
                            value: '${event.formattedDate}\n${event.eventTime}',
                          ),
                          const SizedBox(height: 12),
                          _buildDetailRow(
                            icon: Icons.location_on_rounded,
                            label: 'Lokasi Kegiatan',
                            value: '${event.locationName}\n${event.locationAddress ?? "Kabupaten Indramayu"}',
                          ),
                          const SizedBox(height: 12),
                          _buildDetailRow(
                            icon: Icons.person_outline_rounded,
                            label: 'Nama Peserta / Petani',
                            value: name,
                          ),
                        ],
                      ),
                    ),

                    // Perforated Divider
                    _buildPerforationDivider(),

                    // Bottom Ticket Stub (QR Code & Code)
                    Padding(
                      padding: const EdgeInsets.fromLTRB(20, 16, 20, 20),
                      child: Column(
                        children: [
                          // QR Code Container Mockup
                          Container(
                            padding: const EdgeInsets.all(12),
                            decoration: BoxDecoration(
                              color: Colors.grey.shade50,
                              borderRadius: BorderRadius.circular(16),
                              border: Border.all(color: Colors.grey.shade200),
                            ),
                            child: Row(
                              children: [
                                _buildQrSimulation(),
                                const SizedBox(width: 16),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      const Text(
                                        'KODE E-TIKET RESMI',
                                        style: TextStyle(
                                          fontSize: 10,
                                          fontWeight: FontWeight.w700,
                                          color: HomeColors.textSecondary,
                                          letterSpacing: 0.5,
                                        ),
                                      ),
                                      const SizedBox(height: 4),
                                      SelectableText(
                                        ticketCode,
                                        style: const TextStyle(
                                          fontFamily: 'monospace',
                                          fontSize: 14,
                                          fontWeight: FontWeight.w900,
                                          color: HomeColors.textPrimary,
                                        ),
                                      ),
                                      const SizedBox(height: 8),
                                      InkWell(
                                        onTap: () {
                                          Clipboard.setData(ClipboardData(text: ticketCode));
                                          ScaffoldMessenger.of(context).showSnackBar(
                                            const SnackBar(
                                              content: Text('Kode tiket disalin ke papan klip!'),
                                              duration: Duration(seconds: 2),
                                              backgroundColor: HomeColors.primaryGreen,
                                            ),
                                          );
                                        },
                                        borderRadius: BorderRadius.circular(6),
                                        child: Padding(
                                          padding: const EdgeInsets.symmetric(vertical: 2),
                                          child: Row(
                                            mainAxisSize: MainAxisSize.min,
                                            children: const [
                                              Icon(
                                                Icons.copy_rounded,
                                                size: 13,
                                                color: HomeColors.primaryGreen,
                                              ),
                                              SizedBox(width: 4),
                                              Text(
                                                'Salin Kode',
                                                style: TextStyle(
                                                  fontSize: 11.5,
                                                  fontWeight: FontWeight.w700,
                                                  color: HomeColors.primaryGreen,
                                                ),
                                              ),
                                            ],
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                          ),
                          const SizedBox(height: 12),
                          Row(
                            crossAxisAlignment: CrossAxisAlignment.center,
                            children: const [
                              Icon(
                                Icons.verified_user_rounded,
                                size: 14,
                                color: HomeColors.primaryGreen,
                              ),
                              SizedBox(width: 6),
                              Expanded(
                                child: Text(
                                  'Tunjukkan tiket ini kepada panitia saat registrasi ulang di lokasi kegiatan.',
                                  style: TextStyle(
                                    fontSize: 10.5,
                                    fontWeight: FontWeight.w600,
                                    color: HomeColors.textSecondary,
                                    height: 1.3,
                                  ),
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

              const SizedBox(height: 16),

              // Close Button
              IconButton.filled(
                onPressed: () => Navigator.of(context).pop(),
                icon: const Icon(Icons.close_rounded, color: Colors.white, size: 24),
                style: IconButton.styleFrom(
                  backgroundColor: Colors.black.withValues(alpha: 0.5),
                  padding: const EdgeInsets.all(12),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildHeader() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
      decoration: const BoxDecoration(
        color: HomeColors.primaryGreen,
        borderRadius: BorderRadius.vertical(top: Radius.circular(22)),
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.2),
              shape: BoxShape.circle,
            ),
            child: const Icon(
              Icons.confirmation_number_rounded,
              color: Colors.white,
              size: 22,
            ),
          ),
          const SizedBox(width: 12),
          const Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'E-TIKET MASUK RESMI',
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 14,
                    fontWeight: FontWeight.w900,
                    letterSpacing: 0.8,
                  ),
                ),
                Text(
                  'Platform Pertanian Digital P.A.D.I.',
                  style: TextStyle(
                    color: Colors.white70,
                    fontSize: 11,
                    fontWeight: FontWeight.w500,
                  ),
                ),
              ],
            ),
          ),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(12),
            ),
            child: const Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Icon(Icons.check_circle_rounded, color: HomeColors.primaryGreen, size: 12),
                SizedBox(width: 4),
                Text(
                  'AKTIF',
                  style: TextStyle(
                    color: HomeColors.primaryGreen,
                    fontSize: 10,
                    fontWeight: FontWeight.w900,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildDetailRow({
    required IconData icon,
    required String label,
    required String value,
  }) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Icon(icon, size: 18, color: HomeColors.primaryGreen),
        const SizedBox(width: 10),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                label,
                style: const TextStyle(
                  fontSize: 11,
                  color: HomeColors.textSecondary,
                  fontWeight: FontWeight.w600,
                ),
              ),
              const SizedBox(height: 2),
              Text(
                value,
                style: const TextStyle(
                  fontSize: 13,
                  color: HomeColors.textPrimary,
                  fontWeight: FontWeight.w700,
                  height: 1.3,
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildPerforationDivider() {
    return Stack(
      alignment: Alignment.center,
      children: [
        // Dashed line
        LayoutBuilder(
          builder: (context, constraints) {
            const dashWidth = 5.0;
            const dashSpace = 4.0;
            final dashCount = (constraints.maxWidth / (dashWidth + dashSpace)).floor();
            return Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: List.generate(dashCount, (_) {
                return const SizedBox(
                  width: dashWidth,
                  height: 1.5,
                  child: DecoratedBox(
                    decoration: BoxDecoration(color: Color(0xFFE2E8F0)),
                  ),
                );
              }),
            );
          },
        ),
        // Left notch cutout
        Positioned(
          left: -12,
          child: Container(
            width: 24,
            height: 24,
            decoration: const BoxDecoration(
              color: HomeColors.background,
              shape: BoxShape.circle,
            ),
          ),
        ),
        // Right notch cutout
        Positioned(
          right: -12,
          child: Container(
            width: 24,
            height: 24,
            decoration: const BoxDecoration(
              color: HomeColors.background,
              shape: BoxShape.circle,
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildQrSimulation() {
    return Container(
      width: 72,
      height: 72,
      padding: const EdgeInsets.all(6),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: Colors.grey.shade300),
      ),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              _qrBox(true),
              _qrBox(false),
              _qrBox(true),
            ],
          ),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              _qrBox(false),
              _qrCenterLogo(),
              _qrBox(false),
            ],
          ),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              _qrBox(true),
              _qrBox(false),
              _qrBox(true),
            ],
          ),
        ],
      ),
    );
  }

  Widget _qrBox(bool filled) {
    return Container(
      width: 16,
      height: 16,
      decoration: BoxDecoration(
        color: filled ? HomeColors.textPrimary : Colors.transparent,
        border: Border.all(color: HomeColors.textPrimary, width: 2),
        borderRadius: BorderRadius.circular(2),
      ),
      child: filled
          ? Center(
              child: Container(
                width: 6,
                height: 6,
                color: Colors.white,
              ),
            )
          : null,
    );
  }

  Widget _qrCenterLogo() {
    return Container(
      width: 16,
      height: 16,
      decoration: const BoxDecoration(
        color: HomeColors.primaryGreen,
        shape: BoxShape.circle,
      ),
      child: const Center(
        child: Icon(Icons.eco_rounded, color: Colors.white, size: 10),
      ),
    );
  }
}
