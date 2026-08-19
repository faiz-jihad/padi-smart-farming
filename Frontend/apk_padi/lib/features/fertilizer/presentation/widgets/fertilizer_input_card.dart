import 'package:flutter/material.dart';
import 'package:padi/features/auth/presentation/widgets/padi_theme.dart';

class FertilizerInputCard extends StatelessWidget {
  const FertilizerInputCard({
    super.key,
    required this.selectedFertilizer,
    required this.areaController,
    required this.onFertilizerChanged,
  });

  final String selectedFertilizer;
  final TextEditingController areaController;
  final ValueChanged<String?> onFertilizerChanged;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(24),
        border: Border.all(
          color: Colors.black.withValues(alpha: 0.05),
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Data Lahan',
            style: TextStyle(
              color: padiInk,
              fontSize: 18,
              fontWeight: FontWeight.w900,
            ),
          ),
          const SizedBox(height: 18),
          const Text(
            'Jenis Pupuk',
            style: TextStyle(
              color: padiInk,
              fontSize: 14,
              fontWeight: FontWeight.w800,
            ),
          ),
          const SizedBox(height: 8),
          DropdownButtonFormField<String>(
            value: selectedFertilizer,
            decoration: const InputDecoration(
              prefixIcon: Icon(
                Icons.grass_rounded,
                color: padiGreen,
              ),
            ),
            items: const [
              DropdownMenuItem(
                value: 'Urea',
                child: Text('Urea'),
              ),
              DropdownMenuItem(
                value: 'NPK',
                child: Text('NPK'),
              ),
              DropdownMenuItem(
                value: 'SP-36',
                child: Text('SP-36'),
              ),
              DropdownMenuItem(
                value: 'KCl',
                child: Text('KCl'),
              ),
            ],
            onChanged: onFertilizerChanged,
          ),
          const SizedBox(height: 18),
          const Text(
            'Luas Lahan',
            style: TextStyle(
              color: padiInk,
              fontSize: 14,
              fontWeight: FontWeight.w800,
            ),
          ),
          const SizedBox(height: 8),
          TextField(
            controller: areaController,
            keyboardType: const TextInputType.numberWithOptions(
              decimal: true,
            ),
            decoration: const InputDecoration(
              prefixIcon: Icon(
                Icons.landscape_rounded,
                color: padiGreen,
              ),
              suffixText: 'm²',
              hintText: 'Contoh: 1200',
            ),
          ),
        ],
      ),
    );
  }
}