import 'package:flutter/material.dart';
import 'package:padi/features/auth/presentation/widgets/padi_theme.dart';

class AlertSummaryCard extends StatelessWidget {
  const AlertSummaryCard({super.key});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: padiGreen,
        borderRadius: BorderRadius.circular(26),
      ),
      child: const Row(
        children: [
          Expanded(
            child: _SummaryItem(
              icon: Icons.warning_amber_rounded,
              value: '2',
              label: 'Peringatan',
            ),
          ),
          Expanded(
            child: _SummaryItem(
              icon: Icons.location_on_rounded,
              value: '3',
              label: 'Wilayah',
            ),
          ),
          Expanded(
            child: _SummaryItem(
              icon: Icons.campaign_rounded,
              value: '1',
              label: 'Laporan Saya',
            ),
          ),
        ],
      ),
    );
  }
}

class _SummaryItem extends StatelessWidget {
  const _SummaryItem({
    required this.icon,
    required this.value,
    required this.label,
  });

  final IconData icon;
  final String value;
  final String label;

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Icon(
          icon,
          color: padiCream,
          size: 28,
        ),
        const SizedBox(height: 7),
        Text(
          value,
          style: const TextStyle(
            color: Colors.white,
            fontSize: 25,
            fontWeight: FontWeight.w900,
          ),
        ),
        const SizedBox(height: 2),
        Text(
          label,
          textAlign: TextAlign.center,
          style: const TextStyle(
            color: Colors.white70,
            fontSize: 10,
            fontWeight: FontWeight.w600,
          ),
        ),
      ],
    );
  }
}