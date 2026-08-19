import 'package:flutter/material.dart';
import 'package:padi/features/auth/presentation/widgets/padi_theme.dart';
import 'package:padi/features/fertilizer/presentation/widgets/fertilizer_info_card.dart';
import 'package:padi/features/fertilizer/presentation/widgets/fertilizer_input_card.dart';
import 'package:padi/features/fertilizer/presentation/widgets/fertilizer_intro_card.dart';
import 'package:padi/features/fertilizer/presentation/widgets/fertilizer_result_card.dart';

class FertilizerCalculatorScreen extends StatefulWidget {
  const FertilizerCalculatorScreen({super.key});

  @override
  State<FertilizerCalculatorScreen> createState() =>
      _FertilizerCalculatorScreenState();
}

class _FertilizerCalculatorScreenState
    extends State<FertilizerCalculatorScreen> {
  final TextEditingController _areaController = TextEditingController();

  String _selectedFertilizer = 'Urea';

  double? _result;
  double? _areaResult;

  final Map<String, double> _fertilizerRates = {
    'Urea': 0.03,
    'NPK': 0.025,
    'SP-36': 0.02,
    'KCl': 0.015,
  };

  @override
  void dispose() {
    _areaController.dispose();
    super.dispose();
  }

  void _calculate() {
    final area = double.tryParse(
      _areaController.text.trim().replaceAll(',', '.'),
    );

    if (area == null || area <= 0) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Masukkan luas lahan yang valid.'),
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }

    final rate = _fertilizerRates[_selectedFertilizer] ?? 0;

    setState(() {
      _areaResult = area;
      _result = area * rate;
    });

    FocusScope.of(context).unfocus();
  }

  void _reset() {
    setState(() {
      _areaController.clear();
      _result = null;
      _areaResult = null;
    });
  }

  String _formatNumber(double value) {
    if (value == value.roundToDouble()) {
      return value.toInt().toString();
    }

    return value.toStringAsFixed(1);
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
          onPressed: () => Navigator.of(context).pop(),
          icon: const Icon(
            Icons.arrow_back_rounded,
            color: padiInk,
          ),
        ),
        title: const Text(
          'Kalkulator Pupuk',
          style: TextStyle(
            color: padiInk,
            fontSize: 20,
            fontWeight: FontWeight.w900,
          ),
        ),
      ),
      body: SafeArea(
        child: ListView(
          padding: const EdgeInsets.fromLTRB(20, 8, 20, 30),
          children: [
            const FertilizerIntroCard(),
            const SizedBox(height: 18),
            FertilizerInputCard(
              selectedFertilizer: _selectedFertilizer,
              areaController: _areaController,
              onFertilizerChanged: (value) {
                if (value == null) return;

                setState(() {
                  _selectedFertilizer = value;
                  _result = null;
                  _areaResult = null;
                });
              },
            ),
            const SizedBox(height: 16),
            SizedBox(
              width: double.infinity,
              height: 54,
              child: FilledButton.icon(
                onPressed: _calculate,
                icon: const Icon(
                  Icons.calculate_rounded,
                  size: 23,
                ),
                label: const Text(
                  'Hitung Kebutuhan',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w900,
                  ),
                ),
              ),
            ),
            if (_result != null && _areaResult != null) ...[
              const SizedBox(height: 22),
              FertilizerResultCard(
                fertilizer: _selectedFertilizer,
                area: _areaResult!,
                result: _result!,
                formatNumber: _formatNumber,
              ),
              const SizedBox(height: 14),
              SizedBox(
                width: double.infinity,
                height: 50,
                child: OutlinedButton.icon(
                  onPressed: _reset,
                  icon: const Icon(Icons.refresh_rounded),
                  label: const Text(
                    'Hitung Lagi',
                    style: TextStyle(
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                  style: OutlinedButton.styleFrom(
                    foregroundColor: padiGreen,
                    side: const BorderSide(
                      color: padiGreen,
                    ),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(18),
                    ),
                  ),
                ),
              ),
            ],
            const SizedBox(height: 22),
            const FertilizerInfoCard(),
          ],
        ),
      ),
    );
  }
}