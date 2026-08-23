import 'package:flutter/material.dart';

import 'package:padi/features/auth/presentation/widgets/padi_theme.dart';
import 'package:padi/features/community_alert/data/models/community_alert_model.dart';

class CommunityAlertCard extends StatelessWidget {
  const CommunityAlertCard({
    super.key,
    required this.alert,
  });

  final CommunityAlertModel alert;

  @override
  Widget build(BuildContext context) {
    final isDanger = alert.type == 'danger';
    final isWarning = alert.type == 'warning';

    final iconColor = isDanger
        ? const Color(0xFFB91C1C)
        : isWarning
            ? const Color(0xFF946E00)
            : padiGreen;

    final iconBackground = isDanger
        ? const Color(0xFFFEE2E2)
        : isWarning
            ? const Color(0xFFFFF7DC)
            : const Color(0xFFEAF5EF);

    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(22),
        border: Border.all(
          color: iconColor.withValues(alpha: 0.10),
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: 48,
                height: 48,
                decoration: BoxDecoration(
                  color: iconBackground,
                  borderRadius: BorderRadius.circular(15),
                ),
                child: Icon(
                  isDanger
                      ? Icons.dangerous_rounded
                      : isWarning
                          ? Icons.warning_amber_rounded
                          : Icons.info_outline_rounded,
                  color: iconColor,
                  size: 27,
                ),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      alert.typeLabel,
                      style: TextStyle(
                        color: iconColor,
                        fontSize: 12,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                    const SizedBox(height: 3),
                    Text(
                      alert.title,
                      style: const TextStyle(
                        color: padiInk,
                        fontSize: 17,
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          Text(
            alert.message,
            style: const TextStyle(
              color: padiMuted,
              fontSize: 14,
              height: 1.5,
            ),
          ),
          if (alert.publishedAt != null) ...[
            const SizedBox(height: 14),
            Row(
              children: [
                const Icon(
                  Icons.schedule_rounded,
                  size: 16,
                  color: padiMuted,
                ),
                const SizedBox(width: 6),
                Text(
                  _formatDate(alert.publishedAt!),
                  style: const TextStyle(
                    color: padiMuted,
                    fontSize: 12,
                    fontWeight: FontWeight.w600,
                  ),
                ),
              ],
            ),
          ],
        ],
      ),
    );
  }

  String _formatDate(String value) {
    final date = DateTime.tryParse(value);

    if (date == null) {
      return value;
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

    return '${date.day} ${months[date.month - 1]} ${date.year}';
  }
}