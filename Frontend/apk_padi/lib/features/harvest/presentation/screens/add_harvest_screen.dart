import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:padi/features/auth/presentation/widgets/padi_theme.dart';
import 'package:padi/features/harvest/presentation/widgets/harvest_input_card.dart';

class AddHarvestScreen extends StatefulWidget {
  const AddHarvestScreen({super.key});

  @override
  State<AddHarvestScreen> createState() => _AddHarvestScreenState();
}

class _AddHarvestScreenState extends State<AddHarvestScreen> {
  final TextEditingController _harvestController =
      TextEditingController();

  final TextEditingController _priceController =
      TextEditingController();

  final TextEditingController _noteController =
      TextEditingController();

  String _selectedLand = 'Sawah Blok A';

  @override
  void dispose() {
    _harvestController.dispose();
    _priceController.dispose();
    _noteController.dispose();
    super.dispose();
  }

  void _saveHarvest() {
    final harvest = double.tryParse(
      _harvestController.text.trim().replaceAll(',', '.'),
    );

    final price = double.tryParse(
      _priceController.text.trim().replaceAll('.', ''),
    );

    if (harvest == null || harvest <= 0) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Masukkan jumlah hasil panen yang valid.'),
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }

    if (price == null || price <= 0) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Masukkan harga jual yang valid.'),
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }

    context.pop();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: padiField,
      appBar: AppBar(
        backgroundColor: padiField,
        elevation: 0,
        scrolledUnderElevation: 0,
        leading: IconButton(
          onPressed: () => context.pop(),
          icon: const Icon(
            Icons.arrow_back_rounded,
            color: padiInk,
          ),
        ),
        title: const Text(
          'Catat Panen',
          style: TextStyle(
            color: padiInk,
            fontSize: 20,
            fontWeight: FontWeight.w900,
          ),
        ),
      ),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(20, 8, 20, 30),
        children: [
          const Text(
            'Catat hasil panen',
            style: TextStyle(
              color: padiInk,
              fontSize: 24,
              fontWeight: FontWeight.w900,
            ),
          ),
          const SizedBox(height: 6),
          const Text(
            'Simpan hasil panen agar lebih mudah melihat perkembangan produksi.',
            style: TextStyle(
              color: padiMuted,
              fontSize: 13,
              height: 1.4,
            ),
          ),
          const SizedBox(height: 22),
          HarvestInputCard(
            selectedLand: _selectedLand,
            harvestController: _harvestController,
            priceController: _priceController,
            noteController: _noteController,
            onLandChanged: (value) {
              if (value == null) return;

              setState(() {
                _selectedLand = value;
              });
            },
          ),
          const SizedBox(height: 24),
          SizedBox(
            width: double.infinity,
            height: 54,
            child: FilledButton.icon(
              onPressed: _saveHarvest,
              icon: const Icon(Icons.save_rounded),
              label: const Text(
                'Simpan Catatan Panen',
                style: TextStyle(
                  fontWeight: FontWeight.w900,
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}