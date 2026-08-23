import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import 'package:padi/core/network/api_client.dart';
import 'package:padi/core/storage/token_storage.dart';

import 'package:padi/features/auth/presentation/widgets/padi_theme.dart';

import 'package:padi/features/farm/data/models/farm_model.dart';
import 'package:padi/features/farm/data/services/farm_api_service.dart';

import 'package:padi/features/fertilizer/data/models/fertilizer_rule_model.dart';
import 'package:padi/features/fertilizer/data/services/fertilizer_api_service.dart';

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
  final TextEditingController _areaController =
      TextEditingController();

  late final FarmApiService _farmApiService;
  late final FertilizerApiService _fertilizerApiService;

  List<FarmModel> _farms = [];
  List<FertilizerRuleModel> _rules = [];

  FarmModel? _selectedFarm;

  String _selectedFertilizer = '';

  double? _result;
  double? _areaResult;
  double? _rateResult;

  bool _isLoading = true;
  String? _errorMessage;

  @override
  void initState() {
    super.initState();

    final apiClient = ApiClient(
      const SecureTokenStorage(),
    );

    _farmApiService = FarmApiService(apiClient);
    _fertilizerApiService = FertilizerApiService(apiClient);

    _loadData();
  }

  Future<void> _loadData() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      final results = await Future.wait([
        _farmApiService.fetchFarms(),
        _fertilizerApiService.fetchRules(),
      ]);

      final farms = results[0] as List<FarmModel>;
      final rules = results[1] as List<FertilizerRuleModel>;

      if (!mounted) return;

      setState(() {
        _farms = farms;
        _rules = rules;

        if (farms.isNotEmpty) {
          _selectedFarm = farms.first;

          _areaController.text =
              farms.first.areaHa.toString();
        }

        if (rules.isNotEmpty) {
          _selectedFertilizer = rules.first.nutrient;
        }

        _isLoading = false;
      });
    } catch (e) {
      if (!mounted) return;

      setState(() {
        _isLoading = false;
        _errorMessage =
            'Gagal mengambil data kalkulator pupuk.';
      });
    }
  }

  List<String> get _fertilizerOptions {
    final nutrients = _rules
        .map((rule) => rule.nutrient)
        .where((nutrient) => nutrient.trim().isNotEmpty)
        .toSet()
        .toList();

    return nutrients;
  }

  FertilizerRuleModel? get _selectedRule {
    for (final rule in _rules) {
      if (rule.nutrient == _selectedFertilizer) {
        return rule;
      }
    }

    return null;
  }

  void _selectFarm(FarmModel farm) {
    setState(() {
      _selectedFarm = farm;
      _areaController.text = farm.areaHa.toString();
      _result = null;
      _areaResult = null;
      _rateResult = null;
    });
  }

  void _calculate() {
    final area = double.tryParse(
      _areaController.text.trim().replaceAll(',', '.'),
    );

    if (area == null || area <= 0) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text(
            'Masukkan luas lahan yang valid.',
          ),
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }

    final rule = _selectedRule;

    if (rule == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text(
            'Aturan pupuk belum tersedia.',
          ),
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }

    final result = area * rule.kgPerHa;

    setState(() {
      _areaResult = area;
      _rateResult = rule.kgPerHa;
      _result = result;
    });

    FocusScope.of(context).unfocus();
  }

  void _reset() {
    setState(() {
      if (_selectedFarm != null) {
        _areaController.text =
            _selectedFarm!.areaHa.toString();
      } else {
        _areaController.clear();
      }

      _result = null;
      _areaResult = null;
      _rateResult = null;
    });
  }

  String _formatNumber(double value) {
    if (value == value.roundToDouble()) {
      return value.toInt().toString();
    }

    return value.toStringAsFixed(2);
  }

  @override
  void dispose() {
    _areaController.dispose();
    super.dispose();
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
          onPressed: () {
            if (context.canPop()) {
              context.pop();
            } else {
              context.go('/home');
            }
          },
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
        child: _buildBody(),
      ),
    );
  }

  Widget _buildBody() {
    if (_isLoading) {
      return const Center(
        child: CircularProgressIndicator(),
      );
    }

    if (_errorMessage != null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(
                Icons.error_outline_rounded,
                color: padiGreen,
                size: 50,
              ),
              const SizedBox(height: 15),
              Text(
                _errorMessage!,
                textAlign: TextAlign.center,
                style: const TextStyle(
                  color: padiInk,
                  fontSize: 16,
                  fontWeight: FontWeight.w700,
                ),
              ),
              const SizedBox(height: 18),
              FilledButton.icon(
                onPressed: _loadData,
                icon: const Icon(
                  Icons.refresh_rounded,
                ),
                label: const Text(
                  'Coba Lagi',
                ),
              ),
            ],
          ),
        ),
      );
    }

    if (_farms.isEmpty) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(
                Icons.grass_rounded,
                color: padiGreen,
                size: 55,
              ),
              const SizedBox(height: 15),
              const Text(
                'Belum ada lahan',
                style: TextStyle(
                  color: padiInk,
                  fontSize: 19,
                  fontWeight: FontWeight.w900,
                ),
              ),
              const SizedBox(height: 8),
              const Text(
                'Tambahkan lahan terlebih dahulu untuk menghitung kebutuhan pupuk.',
                textAlign: TextAlign.center,
                style: TextStyle(
                  color: Color(0xFF69766F),
                  fontSize: 14,
                ),
              ),
              const SizedBox(height: 18),
              FilledButton.icon(
                onPressed: () {
                  context.push('/farms');
                },
                icon: const Icon(
                  Icons.landscape_rounded,
                ),
                label: const Text(
                  'Lihat Lahan',
                ),
              ),
            ],
          ),
        ),
      );
    }

    if (_rules.isEmpty) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Icon(
                Icons.science_outlined,
                color: padiGreen,
                size: 55,
              ),
              const SizedBox(height: 15),
              const Text(
                'Aturan pupuk belum tersedia',
                textAlign: TextAlign.center,
                style: TextStyle(
                  color: padiInk,
                  fontSize: 19,
                  fontWeight: FontWeight.w900,
                ),
              ),
              const SizedBox(height: 8),
              const Text(
                'Data aturan pupuk belum tersedia dari database.',
                textAlign: TextAlign.center,
                style: TextStyle(
                  color: Color(0xFF69766F),
                  fontSize: 14,
                ),
              ),
            ],
          ),
        ),
      );
    }

    return ListView(
      padding: const EdgeInsets.fromLTRB(
        20,
        8,
        20,
        30,
      ),
      children: [
        const FertilizerIntroCard(),
        const SizedBox(height: 18),
        _buildFarmSelector(),
        const SizedBox(height: 16),
        FertilizerInputCard(
          selectedFertilizer: _selectedFertilizer,
          areaController: _areaController,
          onFertilizerChanged: (value) {
            if (value == null) return;

            setState(() {
              _selectedFertilizer = value;
              _result = null;
              _areaResult = null;
              _rateResult = null;
            });
          },
        ),
        const SizedBox(height: 16),
        _buildRateInfo(),
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
        if (_result != null &&
            _areaResult != null) ...[
          const SizedBox(height: 22),
          FertilizerResultCard(
            fertilizer: _selectedFertilizer,
            area: _areaResult!,
            result: _result!,
            formatNumber: _formatNumber,
          ),
          if (_rateResult != null) ...[
            const SizedBox(height: 10),
            Center(
              child: Text(
                '${_formatNumber(_rateResult!)} kg/Ha dari aturan database',
                style: const TextStyle(
                  color: Color(0xFF69766F),
                  fontSize: 13,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ),
          ],
          const SizedBox(height: 14),
          SizedBox(
            width: double.infinity,
            height: 50,
            child: OutlinedButton.icon(
              onPressed: _reset,
              icon: const Icon(
                Icons.refresh_rounded,
              ),
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
    );
  }

  Widget _buildFarmSelector() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Pilih lahan',
          style: TextStyle(
            color: padiInk,
            fontSize: 18,
            fontWeight: FontWeight.w900,
          ),
        ),
        const SizedBox(height: 5),
        const Text(
          'Luas lahan diambil dari data lahan Anda.',
          style: TextStyle(
            color: Color(0xFF69766F),
            fontSize: 13,
          ),
        ),
        const SizedBox(height: 10),
        Container(
          padding: const EdgeInsets.symmetric(
            horizontal: 16,
          ),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(18),
            border: Border.all(
              color: const Color(0xFFE1E7E2),
            ),
          ),
          child: DropdownButtonHideUnderline(
            child: DropdownButton<FarmModel>(
              value: _selectedFarm,
              isExpanded: true,
              icon: const Icon(
                Icons.keyboard_arrow_down_rounded,
                color: padiGreen,
                size: 30,
              ),
              style: const TextStyle(
                color: padiInk,
                fontSize: 16,
                fontWeight: FontWeight.w700,
              ),
              items: _farms.map((farm) {
                return DropdownMenuItem<FarmModel>(
                  value: farm,
                  child: Text(
                    farm.name,
                    overflow: TextOverflow.ellipsis,
                  ),
                );
              }).toList(),
              onChanged: (farm) {
                if (farm != null) {
                  _selectFarm(farm);
                }
              },
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildRateInfo() {
    final rule = _selectedRule;

    if (rule == null) {
      return const SizedBox.shrink();
    }

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFFEAF5EF),
        borderRadius: BorderRadius.circular(18),
      ),
      child: Row(
        children: [
          const Icon(
            Icons.science_rounded,
            color: padiGreen,
            size: 28,
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              '${rule.nutrient}: ${_formatNumber(rule.kgPerHa)} kg/Ha',
              style: const TextStyle(
                color: padiInk,
                fontSize: 14,
                fontWeight: FontWeight.w800,
              ),
            ),
          ),
        ],
      ),
    );
  }
}