import 'package:flutter/material.dart';
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

    final themeColor = isDanger
        ? const Color(0xFFDC2626)
        : isWarning
            ? const Color(0xFFD97706)
            : const Color(0xFF059669);

    final bgGradient = isDanger
        ? const LinearGradient(
            colors: [Color(0xFFFEF2F2), Colors.white],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
          )
        : isWarning
            ? const LinearGradient(
                colors: [Color(0xFFFFFBEB), Colors.white],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              )
            : const LinearGradient(
                colors: [Color(0xFFF0FDF4), Colors.white],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              );

    final iconBg = isDanger
        ? const Color(0xFFFEE2E2)
        : isWarning
            ? const Color(0xFFFEF3C7)
            : const Color(0xFFDCFCE7);

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        gradient: bgGradient,
        borderRadius: BorderRadius.circular(18),
        border: Border.all(
          color: themeColor.withOpacity(0.20),
          width: 1.2,
        ),
        boxShadow: [
          BoxShadow(
            color: themeColor.withOpacity(0.04),
            blurRadius: 8,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Top Row: Type Pill & Icon
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: 44,
                height: 44,
                decoration: BoxDecoration(
                  color: iconBg,
                  borderRadius: BorderRadius.circular(14),
                ),
                child: Icon(
                  isDanger
                      ? Icons.warning_rounded
                      : isWarning
                          ? Icons.crisis_alert_rounded
                          : Icons.info_rounded,
                  color: themeColor,
                  size: 24,
                ),
              ),
              const SizedBox(width: 12),
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
                            color: themeColor.withOpacity(0.12),
                            borderRadius: BorderRadius.circular(8),
                          ),
                          child: Text(
                            alert.typeLabel,
                            style: TextStyle(
                              color: themeColor,
                              fontSize: 11,
                              fontWeight: FontWeight.w900,
                            ),
                          ),
                        ),
                        if (alert.publishedAt != null)
                          Row(
                            children: [
                              const Icon(Icons.schedule_rounded, size: 12, color: Color(0xFF64748B)),
                              const SizedBox(width: 4),
                              Text(
                                _formatShortDate(alert.publishedAt!),
                                style: const TextStyle(fontSize: 11, color: Color(0xFF64748B), fontWeight: FontWeight.w600),
                              ),
                            ],
                          ),
                      ],
                    ),
                    const SizedBox(height: 6),
                    Text(
                      alert.title,
                      style: const TextStyle(
                        color: Color(0xFF0F172A),
                        fontSize: 15,
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),

          const SizedBox(height: 12),

          Text(
            alert.message,
            style: const TextStyle(
              color: Color(0xFF334155),
              fontSize: 13,
              height: 1.45,
            ),
          ),
        ],
      ),
    );
  }

  String _formatShortDate(String raw) {
    final dt = DateTime.tryParse(raw);
    if (dt == null) return raw;
    final now = DateTime.now();
    final diff = now.difference(dt);
    if (diff.inMinutes < 60) return '${diff.inMinutes} mnt lalu';
    if (diff.inHours < 24) return '${diff.inHours} jam lalu';
    if (diff.inDays < 7) return '${diff.inDays} hr lalu';
    return '${dt.day}/${dt.month}/${dt.year}';
  }
}