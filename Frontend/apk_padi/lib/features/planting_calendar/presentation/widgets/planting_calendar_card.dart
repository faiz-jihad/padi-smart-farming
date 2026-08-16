import 'package:flutter/material.dart';
import 'package:padi/features/planting_calendar/data/models/planting_calendar_model.dart';

class PlantingCalendarCard extends StatelessWidget {
  const PlantingCalendarCard({
    super.key,
    required this.calendar,
    this.farmName,
  });

  final PlantingCalendarModel calendar;
  final String? farmName;

  @override
  Widget build(BuildContext context) {
    final isWindow = calendar.isPlantingWindow;

    return Card(
      elevation: 2,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(16),
        side: BorderSide(
          color: isWindow ? const Color(0xFF16A34A) : Colors.grey.shade300,
          width: isWindow ? 1.5 : 1.0,
        ),
      ),
      child: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                  decoration: BoxDecoration(
                    color: isWindow ? const Color(0xFFDCFCE7) : Colors.grey.shade100,
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Row(
                    children: [
                      Icon(
                        isWindow ? Icons.check_circle_outline : Icons.schedule,
                        size: 14,
                        color: isWindow ? const Color(0xFF16A34A) : Colors.grey.shade700,
                      ),
                      const SizedBox(width: 4),
                      Text(
                        isWindow
                            ? 'Jendela Tanam Aktif'
                            : (calendar.daysUntilStart > 0
                                ? '${calendar.daysUntilStart} Hari Menuju Tanam'
                                : 'Musim Selesai'),
                        style: TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.w600,
                          color: isWindow ? const Color(0xFF16A34A) : Colors.grey.shade700,
                        ),
                      ),
                    ],
                  ),
                ),
                if (calendar.isFallback)
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                    decoration: BoxDecoration(
                      color: Colors.amber.shade50,
                      border: Border.all(color: Colors.amber.shade300),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Text(
                      'Tingkat ${calendar.resolvedLevel}',
                      style: TextStyle(fontSize: 10, color: Colors.amber.shade900),
                    ),
                  ),
              ],
            ),
            const SizedBox(height: 12),
            Text(
              calendar.seasonLabel,
              style: const TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
                color: Color(0xFF0F172A),
              ),
            ),
            const SizedBox(height: 4),
            Text(
              calendar.regionTitle,
              style: TextStyle(
                fontSize: 13,
                color: Colors.grey.shade600,
              ),
            ),
            const Divider(height: 24),
            Row(
              children: [
                Expanded(
                  child: _buildInfoItem(
                    icon: Icons.calendar_today,
                    label: 'Rentang Tanam',
                    value: '${calendar.plantingStart} s/d ${calendar.plantingEnd}',
                  ),
                ),
              ],
            ),
            const SizedBox(height: 8),
            Row(
              children: [
                Expanded(
                  child: _buildInfoItem(
                    icon: Icons.grass,
                    label: 'Varietas Dianjurkan',
                    value: calendar.riceVariety ?? 'Tidak dispesifikasi',
                  ),
                ),
                if (calendar.plantingPattern != null)
                  Expanded(
                    child: _buildInfoItem(
                      icon: Icons.sync_alt,
                      label: 'Pola Tanam',
                      value: calendar.plantingPattern!,
                    ),
                  ),
              ],
            ),
            if (calendar.notes != null && calendar.notes!.isNotEmpty) ...[
              const SizedBox(height: 12),
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: Colors.blue.shade50,
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Icon(Icons.info_outline, size: 16, color: Colors.blue.shade700),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        calendar.notes!,
                        style: TextStyle(fontSize: 12, color: Colors.blue.shade900),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildInfoItem({
    required IconData icon,
    required String label,
    required String value,
  }) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Icon(icon, size: 16, color: Colors.grey.shade600),
        const SizedBox(width: 6),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                label,
                style: TextStyle(fontSize: 11, color: Colors.grey.shade500),
              ),
              Text(
                value,
                style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w500),
              ),
            ],
          ),
        ),
      ],
    );
  }
}
