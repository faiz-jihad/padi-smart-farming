import 'package:flutter/material.dart';
import 'package:padi/features/auth/presentation/widgets/padi_theme.dart';

class HarvestInputCard extends StatelessWidget {
  const HarvestInputCard({
    super.key,
    required this.selectedLand,
    required this.harvestController,
    required this.priceController,
    required this.noteController,
    required this.onLandChanged,
  });

  final String selectedLand;
  final TextEditingController harvestController;
  final TextEditingController priceController;
  final TextEditingController noteController;
  final ValueChanged<String?> onLandChanged;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(24),
        border: Border.all(
          color: Colors.black.withOpacity(0.05),
        ),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Data Panen',
            style: TextStyle(
              color: padiInk,
              fontSize: 18,
              fontWeight: FontWeight.w900,
            ),
          ),
          const SizedBox(height: 18),
          const Text(
            'Pilih Lahan',
            style: TextStyle(
              color: padiInk,
              fontSize: 14,
              fontWeight: FontWeight.w800,
            ),
          ),
          const SizedBox(height: 8),
          DropdownButtonFormField<String>(
            value: selectedLand,
            decoration: const InputDecoration(
              prefixIcon: Icon(
                Icons.grass_rounded,
                color: padiGreen,
              ),
            ),
            items: const [
              DropdownMenuItem(
                value: 'Sawah Blok A',
                child: Text('Sawah Blok A'),
              ),
              DropdownMenuItem(
                value: 'Sawah Blok B',
                child: Text('Sawah Blok B'),
              ),
            ],
            onChanged: onLandChanged,
          ),
          const SizedBox(height: 18),
          const Text(
            'Hasil Panen',
            style: TextStyle(
              color: padiInk,
              fontSize: 14,
              fontWeight: FontWeight.w800,
            ),
          ),
          const SizedBox(height: 8),
          TextField(
            controller: harvestController,
            keyboardType: const TextInputType.numberWithOptions(
              decimal: true,
            ),
            decoration: const InputDecoration(
              prefixIcon: Icon(
                Icons.scale_rounded,
                color: padiGreen,
              ),
              suffixText: 'kg',
              hintText: 'Contoh: 1200',
            ),
          ),
          const SizedBox(height: 18),
          const Text(
            'Harga Jual',
            style: TextStyle(
              color: padiInk,
              fontSize: 14,
              fontWeight: FontWeight.w800,
            ),
          ),
          const SizedBox(height: 8),
          TextField(
            controller: priceController,
            keyboardType: TextInputType.number,
            decoration: const InputDecoration(
              prefixIcon: Icon(
                Icons.payments_rounded,
                color: padiGreen,
              ),
              prefixText: 'Rp ',
              suffixText: '/ kg',
              hintText: 'Contoh: 6000',
            ),
          ),
          const SizedBox(height: 18),
          const Text(
            'Catatan',
            style: TextStyle(
              color: padiInk,
              fontSize: 14,
              fontWeight: FontWeight.w800,
            ),
          ),
          const SizedBox(height: 8),
          TextField(
            controller: noteController,
            maxLines: 4,
            decoration: const InputDecoration(
              hintText: 'Tambahkan catatan jika diperlukan',
              alignLabelWithHint: true,
            ),
          ),
        ],
      ),
    );
  }
}