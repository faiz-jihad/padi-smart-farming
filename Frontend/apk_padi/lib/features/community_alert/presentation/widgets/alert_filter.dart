import 'package:flutter/material.dart';
import 'package:padi/features/auth/presentation/widgets/padi_theme.dart';

class AlertFilter extends StatelessWidget {
  const AlertFilter({
    super.key,
    required this.selectedFilter,
    required this.onChanged,
  });

  final String selectedFilter;
  final ValueChanged<String> onChanged;

  @override
  Widget build(BuildContext context) {
    const filters = [
      'Semua',
      'Penyakit',
      'Hama',
      'Cuaca',
    ];

    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      child: Row(
        children: filters.map((filter) {
          final selected = selectedFilter == filter;

          return Padding(
            padding: const EdgeInsets.only(right: 8),
            child: ChoiceChip(
              label: Text(filter),
              selected: selected,
              onSelected: (_) => onChanged(filter),
              selectedColor: padiGreen,
              backgroundColor: Colors.white,
              labelStyle: TextStyle(
                color: selected ? Colors.white : padiInk,
                fontSize: 12,
                fontWeight: FontWeight.w700,
              ),
              side: BorderSide(
                color: selected
                    ? padiGreen
                    : Colors.black.withValues(alpha: 0.06),
              ),
            ),
          );
        }).toList(),
      ),
    );
  }
}