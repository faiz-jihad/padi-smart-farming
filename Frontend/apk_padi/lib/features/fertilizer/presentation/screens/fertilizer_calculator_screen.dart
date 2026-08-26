import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:padi/core/providers/app_providers.dart';
import 'package:padi/features/farm/data/models/farm_model.dart';
import 'package:padi/features/farm/data/services/farm_api_service.dart';
import 'package:padi/features/fertilizer/data/models/fertilizer_rule_model.dart';
import 'package:padi/features/fertilizer/data/services/fertilizer_api_service.dart';
import 'package:padi/features/home/presentation/tokens/home_tokens.dart';

enum AreaUnit {
  m2('Meter Persegi (m²)', 'm²', 0.0001),
  ha('Hektar (Ha)', 'Ha', 1.0),
  bata('Bata / Ubin (14 m²)', 'bata', 0.0014);

  final String label;
  final String suffix;
  final double haMultiplier;
  const AreaUnit(this.label, this.suffix, this.haMultiplier);
}

class FertilizerPlanItem {
  final String name;
  final String formula;
  final double totalKg;
  final double basalKg;
  final double firstTopDressKg;
  final double secondTopDressKg;
  final double pricePerKg;
  final Color color;
  final String note;

  const FertilizerPlanItem({
    required this.name,
    required this.formula,
    required this.totalKg,
    required this.basalKg,
    required this.firstTopDressKg,
    required this.secondTopDressKg,
    required this.pricePerKg,
    required this.color,
    required this.note,
  });

  int get bags50kg => (totalKg / 50).floor();
  double get remainingKg => totalKg % 50;
  double get estimatedCost => totalKg * pricePerKg;
}

class FertilizerCalculatorScreen extends ConsumerStatefulWidget {
  const FertilizerCalculatorScreen({
    super.key,
    this.farmId,
    this.cropSeasonId,
    this.setupFlow = false,
  });

  final int? farmId;
  final int? cropSeasonId;
  final bool setupFlow;

  @override
  ConsumerState<FertilizerCalculatorScreen> createState() =>
      _FertilizerCalculatorScreenState();
}

class _FertilizerCalculatorScreenState
    extends ConsumerState<FertilizerCalculatorScreen> {
  final TextEditingController _areaController = TextEditingController();

  List<FarmModel> _farms = [];
  List<FertilizerRuleModel> _rules = [];
  FarmModel? _selectedFarm;

  AreaUnit _selectedUnit = AreaUnit.m2;
  bool _isLoading = true;

  // Calculation Results State
  bool _hasCalculated = false;
  double _calculatedAreaHa = 0.0;
  double _rawInputArea = 0.0;
  List<FertilizerPlanItem> _planItems = [];

  // Standar Balitbangtan Kementan RI Default (Kg/Ha)
  static const double _defaultUreaKgPerHa = 250.0;
  static const double _defaultNpkKgPerHa = 300.0;
  static const double _defaultSp36KgPerHa = 100.0;
  static const double _defaultKclKgPerHa = 100.0;
  static const double _defaultOrganicKgPerHa = 1500.0;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  @override
  void dispose() {
    _areaController.dispose();
    super.dispose();
  }

  Future<void> _loadData() async {
    setState(() => _isLoading = true);

    try {
      final apiClient = ref.read(apiClientProvider);
      final farmService = FarmApiService(apiClient);
      final fertilizerService = FertilizerApiService(apiClient);

      final results = await Future.wait([
        farmService.fetchFarms().catchError((_) => <FarmModel>[]),
        fertilizerService.fetchRules().catchError((_) => <FertilizerRuleModel>[]),
      ]);

      final farms = results[0] as List<FarmModel>;
      final rules = results[1] as List<FertilizerRuleModel>;

      if (!mounted) return;

      setState(() {
        _farms = farms;
        _rules = rules;

        if (farms.isNotEmpty) {
          _selectedFarm = _findPreferredFarm(farms);
          if (_selectedFarm != null) {
            _selectedUnit = AreaUnit.ha;
            _areaController.text = _selectedFarm!.areaHa.toString();
          }
        } else {
          _selectedUnit = AreaUnit.m2;
          _areaController.text = '1000';
        }

        _isLoading = false;
      });

      // Auto hitung kalkulasi awal jika ada data
      _calculate();
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _isLoading = false;
        _selectedUnit = AreaUnit.m2;
        _areaController.text = '1000';
      });
      _calculate();
    }
  }

  FarmModel? _findPreferredFarm(List<FarmModel> farms) {
    if (widget.farmId != null) {
      final match = farms.where((f) => f.id == widget.farmId).toList();
      if (match.isNotEmpty) return match.first;
    }
    return farms.isNotEmpty ? farms.first : null;
  }

  void _onFarmSelected(FarmModel? farm) {
    setState(() {
      _selectedFarm = farm;
      if (farm != null) {
        _selectedUnit = AreaUnit.ha;
        _areaController.text = farm.areaHa.toString();
      }
    });
    _calculate();
  }

  void _calculate() {
    final rawInput = double.tryParse(_areaController.text.trim().replaceAll(',', '.'));
    if (rawInput == null || rawInput <= 0) {
      setState(() => _hasCalculated = false);
      return;
    }

    final areaHa = rawInput * _selectedUnit.haMultiplier;

    // Hitung per jenis pupuk berdasarkan standar dosis Kementan
    final ureaRate = _getRateForNutrient('Urea', _defaultUreaKgPerHa);
    final npkRate = _getRateForNutrient('NPK Phonska', _defaultNpkKgPerHa);
    final sp36Rate = _getRateForNutrient('SP-36', _defaultSp36KgPerHa);
    final kclRate = _getRateForNutrient('KCl', _defaultKclKgPerHa);
    final organicRate = _getRateForNutrient('Pupuk Organik', _defaultOrganicKgPerHa);

    final totalUrea = ureaRate * areaHa;
    final totalNpk = npkRate * areaHa;
    final totalSp36 = sp36Rate * areaHa;
    final totalKcl = kclRate * areaHa;
    final totalOrganic = organicRate * areaHa;

    final plan = <FertilizerPlanItem>[
      FertilizerPlanItem(
        name: 'Urea (Nitrogen 46%)',
        formula: '46-0-0',
        totalKg: totalUrea,
        basalKg: totalUrea * 0.30,
        firstTopDressKg: totalUrea * 0.40,
        secondTopDressKg: totalUrea * 0.30,
        pricePerKg: 2250, // Harga subsidi acuan
        color: const Color(0xFF16A34A),
        note: 'Memacu pertumbuhan vegetatif dan jumlah anakan produktif.',
      ),
      FertilizerPlanItem(
        name: 'NPK Phonska (15-15-15)',
        formula: '15-15-15',
        totalKg: totalNpk,
        basalKg: totalNpk * 0.50,
        firstTopDressKg: totalNpk * 0.50,
        secondTopDressKg: 0.0,
        pricePerKg: 2300,
        color: const Color(0xFF0284C7),
        note: 'Menyediakan nutrisi lengkap (N, P, K) untuk perakaran dan batang kokoh.',
      ),
      FertilizerPlanItem(
        name: 'SP-36 / TSP (Fosfat 36%)',
        formula: '0-36-0',
        totalKg: totalSp36,
        basalKg: totalSp36,
        firstTopDressKg: 0.0,
        secondTopDressKg: 0.0,
        pricePerKg: 2400,
        color: const Color(0xFFD97706),
        note: 'Diberikan seluruhnya saat olah tanah/tanam untuk perkembangan akar.',
      ),
      FertilizerPlanItem(
        name: 'KCl / MOP (Kalium 60%)',
        formula: '0-0-60',
        totalKg: totalKcl,
        basalKg: 0.0,
        firstTopDressKg: totalKcl * 0.50,
        secondTopDressKg: totalKcl * 0.50,
        pricePerKg: 6000,
        color: const Color(0xFF7C3AED),
        note: 'Mencegah tanaman rebah dan memaksimalkan pengisian butir gabah.',
      ),
      FertilizerPlanItem(
        name: 'Pupuk Organik / Kompos',
        formula: 'Organik',
        totalKg: totalOrganic,
        basalKg: totalOrganic,
        firstTopDressKg: 0.0,
        secondTopDressKg: 0.0,
        pricePerKg: 800,
        color: const Color(0xFF854D0E),
        note: 'Memperbaiki struktur biologi dan daya ikat air tanah sawah.',
      ),
    ];

    setState(() {
      _hasCalculated = true;
      _rawInputArea = rawInput;
      _calculatedAreaHa = areaHa;
      _planItems = plan;
    });
  }

  double _getRateForNutrient(String nutrientName, double fallback) {
    final matches = _rules.where(
      (r) => r.nutrient.toLowerCase().contains(nutrientName.toLowerCase()) &&
          r.phase.toLowerCase().contains('total'),
    );
    if (matches.isNotEmpty) {
      return matches.first.kgPerHa;
    }
    return fallback;
  }

  String _formatKg(double val) {
    if (val <= 0) return '0 kg';
    if (val < 1) return '${(val * 1000).toInt()} gram';
    return val >= 10 ? '${val.toStringAsFixed(1)} kg' : '${val.toStringAsFixed(2)} kg';
  }

  String _formatRupiah(double val) {
    final intVal = val.round();
    final str = intVal.toString();
    final buffer = StringBuffer();
    for (int i = 0; i < str.length; i++) {
      if (i > 0 && (str.length - i) % 3 == 0) {
        buffer.write('.');
      }
      buffer.write(str[i]);
    }
    return 'Rp $buffer';
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: HomeColors.background,
      appBar: AppBar(
        backgroundColor: HomeColors.surface,
        elevation: 0,
        title: const Text(
          'Kalkulator Dosis Pupuk',
          style: TextStyle(
            color: HomeColors.textPrimary,
            fontSize: 18,
            fontWeight: FontWeight.w800,
          ),
        ),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_rounded, color: HomeColors.textPrimary),
          onPressed: () {
            if (context.canPop()) {
              context.pop();
            } else {
              context.go('/home');
            }
          },
        ),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: HomeColors.primaryGreen))
          : ListView(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
              children: [
                // 1. Header Info Banner
                _buildIntroHeader(),

                const SizedBox(height: 16),

                // 2. Input Card
                _buildInputFormCard(),

                const SizedBox(height: 20),

                // 3. Calculation Result
                if (_hasCalculated) ...[
                  _buildResultsOverview(),
                  const SizedBox(height: 20),
                  _buildApplicationScheduleCard(),
                  const SizedBox(height: 20),
                  _buildDetailedFertilizerList(),
                  const SizedBox(height: 20),
                  _buildAgronomyTipsCard(),
                  const SizedBox(height: 24),
                ],
              ],
            ),
    );
  }

  Widget _buildIntroHeader() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: HomeColors.lightGreen,
        borderRadius: BorderRadius.circular(HomeRadius.xl),
        border: Border.all(color: HomeColors.border),
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: HomeColors.surface,
              borderRadius: BorderRadius.circular(HomeRadius.lg),
            ),
            child: const Icon(
              Icons.science_rounded,
              color: HomeColors.primaryGreen,
              size: 26,
            ),
          ),
          const SizedBox(width: 14),
          const Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Rekomendasi Pemupukan Presisi',
                  style: TextStyle(
                    color: HomeColors.deepGreen,
                    fontSize: 15,
                    fontWeight: FontWeight.w800,
                  ),
                ),
                SizedBox(height: 3),
                Text(
                  'Dihitung berdasarkan panduan pemupukan berimbang Balitbangtan Kementan RI.',
                  style: TextStyle(
                    color: HomeColors.textSecondary,
                    fontSize: 12,
                    height: 1.3,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildInputFormCard() {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: HomeColors.surface,
        borderRadius: BorderRadius.circular(HomeRadius.xl),
        border: Border.all(color: HomeColors.border),
        boxShadow: HomeShadows.subtle,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Row(
            children: [
              Icon(Icons.tune_rounded, color: HomeColors.primaryGreen, size: 18),
              SizedBox(width: 6),
              Text('Parameter Luas Lahan', style: HomeTypography.cardTitle),
            ],
          ),
          const SizedBox(height: 16),

          // Selector Lahan Terdaftar (Opsional)
          if (_farms.isNotEmpty) ...[
            const Text(
              'Pilih Lahan Terdaftar:',
              style: TextStyle(
                color: HomeColors.textSecondary,
                fontSize: 12,
                fontWeight: FontWeight.w700,
              ),
            ),
            const SizedBox(height: 6),
            DropdownButtonFormField<FarmModel?>(
              value: _selectedFarm,
              decoration: InputDecoration(
                isDense: true,
                filled: true,
                fillColor: HomeColors.background,
                contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(HomeRadius.md),
                  borderSide: const BorderSide(color: HomeColors.border),
                ),
                enabledBorder: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(HomeRadius.md),
                  borderSide: const BorderSide(color: HomeColors.border),
                ),
              ),
              items: [
                const DropdownMenuItem<FarmModel?>(
                  value: null,
                  child: Text('Input Manual Bebas', style: TextStyle(fontSize: 13)),
                ),
                ..._farms.map(
                  (farm) => DropdownMenuItem<FarmModel?>(
                    value: farm,
                    child: Text(
                      '${farm.name} (${farm.areaHa} Ha)',
                      style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600),
                    ),
                  ),
                ),
              ],
              onChanged: _onFarmSelected,
            ),
            const SizedBox(height: 14),
          ],

          // Satuan & Angka Luas
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(
                flex: 3,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Luas Lahan:',
                      style: TextStyle(
                        color: HomeColors.textSecondary,
                        fontSize: 12,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                    const SizedBox(height: 6),
                    TextField(
                      controller: _areaController,
                      keyboardType: const TextInputType.numberWithOptions(decimal: true),
                      style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w800),
                      decoration: InputDecoration(
                        isDense: true,
                        filled: true,
                        fillColor: HomeColors.background,
                        hintText: 'Contoh: 1000',
                        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(HomeRadius.md),
                          borderSide: const BorderSide(color: HomeColors.border),
                        ),
                        enabledBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(HomeRadius.md),
                          borderSide: const BorderSide(color: HomeColors.border),
                        ),
                      ),
                      onChanged: (_) => _calculate(),
                    ),
                  ],
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                flex: 2,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Satuan:',
                      style: TextStyle(
                        color: HomeColors.textSecondary,
                        fontSize: 12,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                    const SizedBox(height: 6),
                    DropdownButtonFormField<AreaUnit>(
                      value: _selectedUnit,
                      decoration: InputDecoration(
                        isDense: true,
                        filled: true,
                        fillColor: HomeColors.background,
                        contentPadding: const EdgeInsets.symmetric(horizontal: 10, vertical: 11),
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(HomeRadius.md),
                          borderSide: const BorderSide(color: HomeColors.border),
                        ),
                        enabledBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(HomeRadius.md),
                          borderSide: const BorderSide(color: HomeColors.border),
                        ),
                      ),
                      items: AreaUnit.values.map(
                        (unit) => DropdownMenuItem<AreaUnit>(
                          value: unit,
                          child: Text(unit.suffix, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700)),
                        ),
                      ).toList(),
                      onChanged: (unit) {
                        if (unit != null) {
                          setState(() => _selectedUnit = unit);
                          _calculate();
                        }
                      },
                    ),
                  ],
                ),
              ),
            ],
          ),

          const SizedBox(height: 16),

          // Tombol Hitung
          SizedBox(
            width: double.infinity,
            height: 46,
            child: FilledButton.icon(
              onPressed: _calculate,
              style: FilledButton.styleFrom(
                backgroundColor: HomeColors.primaryGreen,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(HomeRadius.md),
                ),
              ),
              icon: const Icon(Icons.calculate_rounded, size: 20),
              label: const Text(
                'Hitung Kebutuhan Pupuk',
                style: TextStyle(fontSize: 14, fontWeight: FontWeight.w800),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildResultsOverview() {
    final totalCost = _planItems.fold<double>(0.0, (acc, item) => acc + item.estimatedCost);

    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [
            const Color(0xFF042F1E),
            HomeColors.deepGreen,
          ],
        ),
        borderRadius: BorderRadius.circular(HomeRadius.xxl),
        boxShadow: HomeShadows.hero,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                decoration: BoxDecoration(
                  color: Colors.white.withOpacity(0.18),
                  borderRadius: BorderRadius.circular(HomeRadius.pill),
                ),
                child: Text(
                  'LUAS EFEKTIF: ${_calculatedAreaHa.toStringAsFixed(_calculatedAreaHa < 0.1 ? 4 : 2)} Ha (${_rawInputArea.toStringAsFixed(0)} ${_selectedUnit.suffix})',
                  style: const TextStyle(
                    color: Color(0xFFFDE68A),
                    fontSize: 10.5,
                    fontWeight: FontWeight.w900,
                    letterSpacing: 0.4,
                  ),
                ),
              ),
              const Icon(Icons.verified_rounded, color: Color(0xFF4ADE80), size: 18),
            ],
          ),
          const SizedBox(height: 14),

          const Text(
            'Total Anggaran Pupuk Rekomendasi',
            style: TextStyle(color: Colors.white70, fontSize: 12, fontWeight: FontWeight.w600),
          ),
          const SizedBox(height: 2),
          Text(
            _formatRupiah(totalCost),
            style: const TextStyle(
              color: Colors.white,
              fontSize: 26,
              fontWeight: FontWeight.w900,
              letterSpacing: -0.5,
            ),
          ),
          const SizedBox(height: 4),
          Text(
            '*Estimasi acuan harga eceran tertinggi subsidi/standar kelompok tani.',
            style: TextStyle(
              color: Colors.white.withOpacity(0.65),
              fontSize: 10.5,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildApplicationScheduleCard() {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: HomeColors.surface,
        borderRadius: BorderRadius.circular(HomeRadius.xl),
        border: Border.all(color: HomeColors.border),
        boxShadow: HomeShadows.subtle,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Row(
            children: [
              Icon(Icons.calendar_month_rounded, color: HomeColors.primaryGreen, size: 18),
              SizedBox(width: 6),
              Text('Jadwal & Tahapan Pemupukan', style: HomeTypography.cardTitle),
            ],
          ),
          const SizedBox(height: 14),

          // Tahap 1
          _buildStageTimelineItem(
            stageNumber: '1',
            stageTitle: 'Pupuk Dasar (0 - 7 HST)',
            subtitle: 'Olah tanah akhir & saat bibit baru ditancap',
            color: const Color(0xFFD97706),
            items: [
              'Urea: ${_formatKg(_planItems[0].basalKg)}',
              'NPK Phonska: ${_formatKg(_planItems[1].basalKg)}',
              'SP-36: ${_formatKg(_planItems[2].basalKg)} (100%)',
              'Pupuk Organik: ${_formatKg(_planItems[4].basalKg)} (100%)',
            ],
          ),

          const SizedBox(height: 12),

          // Tahap 2
          _buildStageTimelineItem(
            stageNumber: '2',
            stageTitle: 'Susulan I (20 - 25 HST)',
            subtitle: 'Fase anakan aktif / anakan maksimum',
            color: const Color(0xFF16A34A),
            items: [
              'Urea: ${_formatKg(_planItems[0].firstTopDressKg)}',
              'NPK Phonska: ${_formatKg(_planItems[1].firstTopDressKg)}',
              'KCl: ${_formatKg(_planItems[3].firstTopDressKg)} (50%)',
            ],
          ),

          const SizedBox(height: 12),

          // Tahap 3
          _buildStageTimelineItem(
            stageNumber: '3',
            stageTitle: 'Susulan II (40 - 45 HST)',
            subtitle: 'Fase primordia / bunting menjelang keluar malai',
            color: const Color(0xFF7C3AED),
            items: [
              'Urea: ${_formatKg(_planItems[0].secondTopDressKg)}',
              'KCl: ${_formatKg(_planItems[3].secondTopDressKg)} (50%)',
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildStageTimelineItem({
    required String stageNumber,
    required String stageTitle,
    required String subtitle,
    required Color color,
    required List<String> items,
  }) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: HomeColors.background,
        borderRadius: BorderRadius.circular(HomeRadius.lg),
        border: Border.all(color: HomeColors.border),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 28,
            height: 28,
            decoration: BoxDecoration(
              color: color,
              shape: BoxShape.circle,
            ),
            child: Center(
              child: Text(
                stageNumber,
                style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w900, fontSize: 13),
              ),
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  stageTitle,
                  style: const TextStyle(
                    color: HomeColors.textPrimary,
                    fontSize: 13.5,
                    fontWeight: FontWeight.w800,
                  ),
                ),
                Text(
                  subtitle,
                  style: const TextStyle(color: HomeColors.textSecondary, fontSize: 11),
                ),
                const SizedBox(height: 6),
                Wrap(
                  spacing: 6,
                  runSpacing: 4,
                  children: items.map((t) => Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(HomeRadius.pill),
                      border: Border.all(color: HomeColors.border),
                    ),
                    child: Text(
                      t,
                      style: const TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w700,
                        color: HomeColors.textPrimary,
                      ),
                    ),
                  )).toList(),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildDetailedFertilizerList() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Rincian Dosis Per Jenis Pupuk',
          style: HomeTypography.sectionTitle,
        ),
        const SizedBox(height: 10),
        ..._planItems.map((item) => _buildFertilizerItemCard(item)),
      ],
    );
  }

  Widget _buildFertilizerItemCard(FertilizerPlanItem item) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: HomeColors.surface,
        borderRadius: BorderRadius.circular(HomeRadius.xl),
        border: Border.all(color: HomeColors.border),
        boxShadow: HomeShadows.subtle,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                children: [
                  Container(
                    width: 12,
                    height: 12,
                    decoration: BoxDecoration(
                      color: item.color,
                      shape: BoxShape.circle,
                    ),
                  ),
                  const SizedBox(width: 8),
                  Text(
                    item.name,
                    style: const TextStyle(
                      color: HomeColors.textPrimary,
                      fontSize: 14.5,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                ],
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(
                  color: item.color.withOpacity(0.12),
                  borderRadius: BorderRadius.circular(HomeRadius.pill),
                ),
                child: Text(
                  _formatKg(item.totalKg),
                  style: TextStyle(
                    color: item.color,
                    fontSize: 13,
                    fontWeight: FontWeight.w900,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),

          Text(
            item.note,
            style: const TextStyle(color: HomeColors.textSecondary, fontSize: 11.5, height: 1.3),
          ),
          const SizedBox(height: 10),

          // Packaging bag conversion & estimate
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: HomeColors.background,
              borderRadius: BorderRadius.circular(HomeRadius.md),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Row(
                  children: [
                    const Icon(Icons.inventory_2_outlined, size: 15, color: HomeColors.textSecondary),
                    const SizedBox(width: 6),
                    Text(
                      item.totalKg >= 50
                          ? 'Konversi: ${item.bags50kg} Sak (50kg)${item.remainingKg > 0 ? " + ${_formatKg(item.remainingKg)}" : ""}'
                          : 'Kebutuhan: ${_formatKg(item.totalKg)} eceran',
                      style: const TextStyle(fontSize: 11.5, fontWeight: FontWeight.w700, color: HomeColors.textPrimary),
                    ),
                  ],
                ),
                Text(
                  _formatRupiah(item.estimatedCost),
                  style: const TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w800,
                    color: HomeColors.primaryGreen,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildAgronomyTipsCard() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFFFFFBEB),
        borderRadius: BorderRadius.circular(HomeRadius.xl),
        border: Border.all(color: const Color(0xFFFDE68A)),
      ),
      child: const Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Icon(Icons.lightbulb_rounded, color: Color(0xFFD97706), size: 20),
              SizedBox(width: 8),
              Text(
                'Panduan Praktis Aplikasi di Sawah',
                style: TextStyle(
                  color: Color(0xFF92400E),
                  fontSize: 13.5,
                  fontWeight: FontWeight.w800,
                ),
              ),
            ],
          ),
          SizedBox(height: 8),
          Text(
            '1. Tabur pupuk saat kondisi air sawah macak-macak (tinggi air 1-2 cm) agar pupuk tidak terbuang bersama aliran air.\n'
            '2. Hindari pemupukan Urea saat terik matahari siang untuk mencegah penguapan gas amonia berlebihan.\n'
            '3. Bersihkan gulma sebelum pemupukan susulan agar penyerapan hara oleh akar padi maksimal.',
            style: TextStyle(
              color: Color(0xFF78350F),
              fontSize: 11.5,
              height: 1.45,
            ),
          ),
        ],
      ),
    );
  }
}
