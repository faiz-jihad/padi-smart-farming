import 'package:flutter/material.dart';
import 'package:padi/features/auth/presentation/widgets/padi_theme.dart';
import 'package:padi/features/planting_calendar/data/models/planting_calendar_model.dart';

class PlantingCalendarCard extends StatelessWidget {
  const PlantingCalendarCard({super.key, required this.calendar});

  final PlantingCalendarModel calendar;

  @override
  Widget build(BuildContext context) {
    final isWindow = calendar.isPlantingWindow;

    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(24),
        border: Border.all(
          color: isWindow ? padiGreen : Colors.grey.shade200,
          width: isWindow ? 2 : 1,
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 48,
                height: 48,
                decoration: BoxDecoration(
                  color: const Color(0xFFE8F5EE),
                  borderRadius: BorderRadius.circular(24),
                ),
                child: const Icon(
                  Icons.calendar_month_rounded,
                  color: padiGreen,
                  size: 26,
                ),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      calendar.seasonLabel,
                      style: const TextStyle(
                        color: padiInk,
                        fontSize: 19,
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                    const SizedBox(height: 3),
                    Text(
                      '${calendar.year} • ${calendar.regionTitle}',
                      style: const TextStyle(color: padiMuted, fontSize: 13),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 18),
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: const Color(0xFFE8F5EC),
              borderRadius: BorderRadius.circular(24),
            ),
            child: Row(
              children: [
                Icon(
                  isWindow
                      ? Icons.check_circle_rounded
                      : Icons.schedule_rounded,
                  color: padiGreen,
                  size: 25,
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Text(
                    _statusText(),
                    style: const TextStyle(
                      color: padiInk,
                      fontSize: 14,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 20),
          _infoRow(
            icon: Icons.date_range_rounded,
            title: 'Rentang tanam',
            value:
                '${_formatDate(calendar.plantingStart)} - ${_formatDate(calendar.plantingEnd)}',
          ),
          const SizedBox(height: 15),
          _infoRow(
            icon: Icons.grass_rounded,
            title: 'Varietas',
            value: calendar.riceVariety ?? 'Tidak dispesifikasi',
          ),
          if (calendar.plantingPattern != null &&
              calendar.plantingPattern!.isNotEmpty) ...[
            const SizedBox(height: 15),
            _infoRow(
              icon: Icons.sync_alt_rounded,
              title: 'Pola tanam',
              value: calendar.plantingPattern!,
            ),
          ],
          if (calendar.recommendedArea != null) ...[
            const SizedBox(height: 15),
            _infoRow(
              icon: Icons.landscape_rounded,
              title: 'Luas rekomendasi',
              value: '${calendar.recommendedArea!.toStringAsFixed(1)} Ha',
            ),
          ],
          if (calendar.source != null && calendar.source!.isNotEmpty) ...[
            const SizedBox(height: 15),
            _infoRow(
              icon: Icons.source_rounded,
              title: 'Sumber',
              value: calendar.source!,
            ),
          ],
          if (calendar.notes != null && calendar.notes!.isNotEmpty) ...[
            const SizedBox(height: 18),
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: const Color(0xFFFFF8DC),
                borderRadius: BorderRadius.circular(16),
              ),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Icon(
                    Icons.info_outline_rounded,
                    color: Color(0xFF9A7A00),
                    size: 21,
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Text(
                      calendar.notes!,
                      style: const TextStyle(
                        color: Color(0xFF675300),
                        fontSize: 13,
                        height: 1.4,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ],
      ),
    );
  }

  String _statusText() {
    if (calendar.isPlantingWindow) {
      return 'Sekarang waktu yang direkomendasikan untuk mulai tanam.';
    }

    if (calendar.daysUntilStart > 0) {
      return '${calendar.daysUntilStart} hari menuju waktu tanam.';
    }

    if (calendar.daysUntilEnd > 0) {
      return 'Masih dalam periode tanam yang direkomendasikan.';
    }

    return 'Periode tanam sudah selesai.';
  }

  Widget _infoRow({
    required IconData icon,
    required String title,
    required String value,
  }) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Icon(icon, color: padiGreen, size: 22),
        const SizedBox(width: 12),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                title,
                style: const TextStyle(color: padiMuted, fontSize: 12),
              ),
              const SizedBox(height: 3),
              Text(
                value,
                style: const TextStyle(
                  color: padiInk,
                  fontSize: 14,
                  fontWeight: FontWeight.w800,
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }

  String _formatDate(String value) {
    final date = DateTime.tryParse(value);

    if (date == null) {
      return value.isEmpty ? '-' : value;
    }

    const months = [
      'Jan',
      'Feb',
      'Mar',
      'Apr',
      'Mei',
      'Jun',
      'Jul',
      'Agu',
      'Sep',
      'Okt',
      'Nov',
      'Des',
    ];

    return '${date.day} ${months[date.month - 1]} ${date.year}';
  }
}
