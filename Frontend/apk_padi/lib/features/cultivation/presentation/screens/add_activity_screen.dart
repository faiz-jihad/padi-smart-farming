import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import 'package:padi/core/providers/app_providers.dart';
import 'package:padi/features/home/presentation/tokens/home_tokens.dart';

class AddActivityScreen extends ConsumerStatefulWidget {
  const AddActivityScreen({
    super.key,
    this.cropSeasonId,
  });

  final int? cropSeasonId;

  @override
  ConsumerState<AddActivityScreen> createState() => _AddActivityScreenState();
}

class _AddActivityScreenState extends ConsumerState<AddActivityScreen> {
  final _formKey = GlobalKey<FormState>();
  final _noteController = TextEditingController();
  final _costController = TextEditingController();

  DateTime _selectedDate = DateTime.now();
  int? _selectedCropSeasonId;
  String _selectedActivityKey = 'fertilizing';

  bool _isLoadingSeasons = true;
  bool _isSaving = false;
  String? _errorMessage;

  List<Map<String, dynamic>> _activeSeasons = [];

  static const List<Map<String, dynamic>> _activityTypes = [
    {
      'key': 'fertilizing',
      'label': 'Pemupukan',
      'icon': Icons.science_rounded,
      'color': Color(0xFF0284C7),
      'desc': 'Pemberian pupuk dasar atau susulan (Urea, NPK, Organik)',
      'chips': [
        'Urea 50 kg',
        'NPK Phonska 25 kg',
        'Pupuk Organik 100 kg',
        'Pupuk Daun Cair',
        'SP-36 20 kg',
      ],
    },
    {
      'key': 'irrigation',
      'label': 'Pengairan & Irigasi',
      'icon': Icons.water_drop_rounded,
      'color': Color(0xFF0EA5E9),
      'desc': 'Pengaturan debit air sawah (macak-macak, genangan, pengeringan)',
      'chips': [
        'Tinggi air 3 cm (macak-macak)',
        'Penggenangan 5 cm',
        'Pengeringan berkala',
        'Buka pintu saluran irigasi',
      ],
    },
    {
      'key': 'spraying',
      'label': 'Penyemprotan Hama',
      'icon': Icons.sanitizer_rounded,
      'color': Color(0xFFF59E0B),
      'desc': 'Aplikasi pestisida, fungisida, atau vitamin nutrisi tanaman',
      'chips': [
        'Insektisida Wereng 200 ml',
        'Fungisida Blas Padi',
        'Pupuk Hayati / ZPT',
        'Bakterisida Kresek',
      ],
    },
    {
      'key': 'planting',
      'label': 'Penanaman / Tanam Bibit',
      'icon': Icons.spa_rounded,
      'color': Color(0xFF10B981),
      'desc': 'Pindah tanam bibit persemaian, sistem jajar legowo, atau tabela',
      'chips': [
        'Tanam bibit umur 15-20 HSS',
        'Sistem Jajar Legowo 2:1',
        'Sistem Jajar Legowo 4:1',
        'Tabela (Tanam Benih Langsung)',
      ],
    },
    {
      'key': 'land_preparation',
      'label': 'Pengolahan Lahan',
      'icon': Icons.agriculture_rounded,
      'color': Color(0xFF047857),
      'desc': 'Pembajakan traktor, penggaruan, perataan tanah, dan pematang',
      'chips': [
        'Bajak singkal traktor',
        'Perataan tanah (garu)',
        'Pembersihan jerami',
        'Perbaikan pematang sawah',
      ],
    },
    {
      'key': 'other',
      'label': 'Penyiangan & Pemeliharaan',
      'icon': Icons.grass_rounded,
      'color': Color(0xFF059669),
      'desc': 'Penyiangan gulma manual (matun), cek pH tanah, atau perapian',
      'chips': [
        'Penyiangan gulma (matun)',
        'Cek pH dan kesuburan tanah',
        'Penyulaman bibit mati',
        'Pembersihan saluran air',
      ],
    },
  ];

  @override
  void initState() {
    super.initState();
    _loadCropSeasons();
  }

  @override
  void dispose() {
    _noteController.dispose();
    _costController.dispose();
    super.dispose();
  }

  Future<void> _loadCropSeasons() async {
    setState(() {
      _isLoadingSeasons = true;
      _errorMessage = null;
    });

    try {
      final apiClient = ref.read(apiClientProvider);
      final response = await apiClient.dio.get('/crop-seasons');
      final data = response.data;

      List<Map<String, dynamic>> list = [];

      if (data is Map) {
        final innerData = data['data'];
        if (innerData is Map && innerData['crop_seasons'] is List) {
          list = (innerData['crop_seasons'] as List)
              .whereType<Map>()
              .map((e) => Map<String, dynamic>.from(e))
              .toList();
        } else if (innerData is List) {
          list = innerData
              .whereType<Map>()
              .map((e) => Map<String, dynamic>.from(e))
              .toList();
        }
      }

      if (!mounted) return;

      int? defaultId = widget.cropSeasonId;
      if (defaultId == null && list.isNotEmpty) {
        final active = list.firstWhere(
          (s) => s['status'] == 'active',
          orElse: () => list.first,
        );
        defaultId = int.tryParse(active['id']?.toString() ?? '');
      }

      setState(() {
        _activeSeasons = list;
        _selectedCropSeasonId = defaultId;
        _isLoadingSeasons = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _isLoadingSeasons = false;
        _selectedCropSeasonId = widget.cropSeasonId ?? 1;
      });
    }
  }

  Future<void> _pickDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _selectedDate,
      firstDate: DateTime(2020),
      lastDate: DateTime.now().add(const Duration(days: 30)),
      helpText: 'Pilih Tanggal Kegiatan',
      cancelText: 'Batal',
      confirmText: 'Pilih',
      builder: (context, child) {
        return Theme(
          data: Theme.of(context).copyWith(
            colorScheme: const ColorScheme.light(
              primary: HomeColors.primaryGreen,
              onPrimary: Colors.white,
              onSurface: Color(0xFF17251E),
            ),
          ),
          child: child!,
        );
      },
    );

    if (picked != null) {
      setState(() => _selectedDate = picked);
    }
  }

  void _addChipToNote(String chip) {
    final currentText = _noteController.text.trim();
    if (currentText.isEmpty) {
      _noteController.text = '• $chip';
    } else if (!currentText.contains(chip)) {
      _noteController.text = '$currentText\n• $chip';
    }
  }

  Future<void> _saveActivity() async {
    FocusScope.of(context).unfocus();

    final seasonId = _selectedCropSeasonId;
    if (seasonId == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Pilih musim tanam aktif terlebih dahulu.'),
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }

    setState(() {
      _isSaving = true;
      _errorMessage = null;
    });

    try {
      final costClean =
          _costController.text.replaceAll(RegExp(r'[^0-9]'), '');
      final cost = int.tryParse(costClean) ?? 0;

      final payload = <String, dynamic>{
        'crop_season_id': seasonId,
        'type': _selectedActivityKey,
        'occurred_at': _selectedDate.toIso8601String().substring(0, 10),
        'notes': _noteController.text.trim().isEmpty
            ? null
            : _noteController.text.trim(),
        'cost': cost,
      };

      final apiClient = ref.read(apiClientProvider);
      final response = await apiClient.dio.post(
        '/farm-activities',
        data: payload,
      );

      if (!mounted) return;

      if (response.statusCode == 200 || response.statusCode == 201) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: const Row(
              children: [
                Icon(Icons.check_circle_rounded, color: Colors.white),
                SizedBox(width: 10),
                Expanded(
                  child: Text(
                    'Kegiatan sawah berhasil dicatat!',
                    style: TextStyle(fontWeight: FontWeight.w700),
                  ),
                ),
              ],
            ),
            backgroundColor: HomeColors.primaryGreen,
            behavior: SnackBarBehavior.floating,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(10),
            ),
          ),
        );

        if (context.canPop()) {
          context.pop(true);
        } else {
          context.go('/land/timeline?cropSeasonId=$seasonId');
        }
        return;
      }

      throw Exception('Gagal menyimpan data aktivitas.');
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _errorMessage = e.toString().replaceFirst('Exception: ', '');
      });
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text('Gagal mencatat kegiatan: $_errorMessage'),
          backgroundColor: HomeColors.danger,
          behavior: SnackBarBehavior.floating,
        ),
      );
    } finally {
      if (mounted) setState(() => _isSaving = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final selectedActivity = _activityTypes.firstWhere(
      (a) => a['key'] == _selectedActivityKey,
      orElse: () => _activityTypes.first,
    );
    final chips = (selectedActivity['chips'] as List<String>?) ?? [];
    final dateFormatted =
        DateFormat('EEEE, d MMMM yyyy', 'id_ID').format(_selectedDate);

    return Scaffold(
      backgroundColor: const Color(0xFFF5F7F4),
      appBar: AppBar(
        backgroundColor: HomeColors.primaryGreen,
        elevation: 0,
        scrolledUnderElevation: 0,
        leading: IconButton(
          icon: const Icon(
            Icons.arrow_back_rounded,
            color: Colors.white,
            size: 22,
          ),
          onPressed: _isSaving ? null : () => context.pop(),
        ),
        title: const Text(
          'Catat Kegiatan Sawah',
          style: TextStyle(
            color: Colors.white,
            fontSize: 18,
            fontWeight: FontWeight.w800,
          ),
        ),
      ),
      bottomNavigationBar: Container(
        padding: const EdgeInsets.fromLTRB(16, 12, 16, 16),
        decoration: BoxDecoration(
          color: Colors.white,
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.06),
              blurRadius: 10,
              offset: const Offset(0, -3),
            ),
          ],
        ),
        child: SafeArea(
          top: false,
          child: FilledButton.icon(
            onPressed: _isSaving ? null : _saveActivity,
            icon: _isSaving
                ? const SizedBox.square(
                    dimension: 18,
                    child: CircularProgressIndicator(
                      color: Colors.white,
                      strokeWidth: 2,
                    ),
                  )
                : const Icon(Icons.check_circle_rounded, size: 20),
            label: Text(
              _isSaving ? 'Menyimpan Catatan...' : 'Simpan Kegiatan Sawah',
              style: const TextStyle(
                fontSize: 15,
                fontWeight: FontWeight.w800,
              ),
            ),
            style: FilledButton.styleFrom(
              backgroundColor: HomeColors.primaryGreen,
              foregroundColor: Colors.white,
              minimumSize: const Size(double.infinity, 48),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(10),
              ),
            ),
          ),
        ),
      ),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.fromLTRB(14, 12, 14, 30),
          children: [
            // 1. Pemilihan Lahan & Musim Tanam
            _buildSeasonSelector(),

            const SizedBox(height: 12),

            // 2. Pemilihan Jenis Kegiatan Sawah
            Container(
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: const Color(0xFFE5ECE3)),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Pilih Jenis Kegiatan Sawah',
                    style: TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w800,
                      color: Color(0xFF17251E),
                    ),
                  ),
                  const SizedBox(height: 10),
                  ..._activityTypes.map((activity) {
                    final key = activity['key'] as String;
                    final label = activity['label'] as String;
                    final icon = activity['icon'] as IconData;
                    final color = activity['color'] as Color;
                    final desc = activity['desc'] as String;
                    final isSelected = _selectedActivityKey == key;

                    return Container(
                      margin: const EdgeInsets.only(bottom: 8),
                      decoration: BoxDecoration(
                        color: isSelected
                            ? HomeColors.lightGreen
                            : const Color(0xFFFAFCF9),
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(
                          color: isSelected
                              ? HomeColors.primaryGreen
                              : const Color(0xFFE5ECE3),
                          width: isSelected ? 1.5 : 0.8,
                        ),
                      ),
                      child: InkWell(
                        onTap: () {
                          setState(() => _selectedActivityKey = key);
                        },
                        borderRadius: BorderRadius.circular(10),
                        child: Padding(
                          padding: const EdgeInsets.all(12),
                          child: Row(
                            children: [
                              Container(
                                width: 42,
                                height: 42,
                                decoration: BoxDecoration(
                                  color: isSelected
                                      ? HomeColors.primaryGreen
                                      : color.withOpacity(0.12),
                                  borderRadius: BorderRadius.circular(10),
                                ),
                                child: Icon(
                                  icon,
                                  color: isSelected ? Colors.white : color,
                                  size: 22,
                                ),
                              ),
                              const SizedBox(width: 12),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      label,
                                      style: TextStyle(
                                        color: isSelected
                                            ? HomeColors.deepGreen
                                            : const Color(0xFF17251E),
                                        fontSize: 13.5,
                                        fontWeight: FontWeight.w800,
                                      ),
                                    ),
                                    const SizedBox(height: 2),
                                    Text(
                                      desc,
                                      style: const TextStyle(
                                        color: Color(0xFF68766E),
                                        fontSize: 11,
                                        height: 1.3,
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                              Icon(
                                isSelected
                                    ? Icons.radio_button_checked_rounded
                                    : Icons.radio_button_off_rounded,
                                color: isSelected
                                    ? HomeColors.primaryGreen
                                    : const Color(0xFF9AA49E),
                                size: 20,
                              ),
                            ],
                          ),
                        ),
                      ),
                    );
                  }),
                ],
              ),
            ),

            const SizedBox(height: 12),

            // 3. Tanggal & Biaya Kegiatan
            Container(
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: const Color(0xFFE5ECE3)),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Waktu & Biaya Operasional',
                    style: TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w800,
                      color: Color(0xFF17251E),
                    ),
                  ),
                  const SizedBox(height: 12),

                  // Tanggal Pelaksanaan
                  InkWell(
                    onTap: _pickDate,
                    borderRadius: BorderRadius.circular(8),
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 12,
                        vertical: 12,
                      ),
                      decoration: BoxDecoration(
                        color: const Color(0xFFF9FAF8),
                        borderRadius: BorderRadius.circular(8),
                        border: Border.all(color: const Color(0xFFE5ECE3)),
                      ),
                      child: Row(
                        children: [
                          const Icon(
                            Icons.calendar_today_rounded,
                            size: 18,
                            color: HomeColors.primaryGreen,
                          ),
                          const SizedBox(width: 10),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const Text(
                                  'Tanggal Kegiatan',
                                  style: TextStyle(
                                    fontSize: 10.5,
                                    color: Color(0xFF68766E),
                                  ),
                                ),
                                Text(
                                  dateFormatted,
                                  style: const TextStyle(
                                    fontSize: 13,
                                    fontWeight: FontWeight.w700,
                                    color: Color(0xFF17251E),
                                  ),
                                ),
                              ],
                            ),
                          ),
                          const Icon(
                            Icons.arrow_forward_ios_rounded,
                            size: 14,
                            color: Color(0xFF888888),
                          ),
                        ],
                      ),
                    ),
                  ),

                  const SizedBox(height: 12),

                  // Biaya Pengeluaran (Opsional)
                  TextFormField(
                    controller: _costController,
                    keyboardType: TextInputType.number,
                    inputFormatters: [FilteringTextInputFormatter.digitsOnly],
                    decoration: const InputDecoration(
                      labelText: 'Biaya Kegiatan (Opsional)',
                      hintText: 'Contoh: 150000',
                      prefixText: 'Rp ',
                      prefixIcon: Icon(
                        Icons.payments_outlined,
                        color: HomeColors.primaryGreen,
                      ),
                    ),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 12),

            // 4. Catatan Detail & Rekomendasi Takaran
            Container(
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(color: const Color(0xFFE5ECE3)),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Catatan Detail / Takaran Bahan',
                    style: TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w800,
                      color: Color(0xFF17251E),
                    ),
                  ),
                  const SizedBox(height: 6),
                  const Text(
                    'Klik tag cepat di bawah untuk menambahkan takaran secara instan:',
                    style: TextStyle(
                      fontSize: 11.5,
                      color: Color(0xFF68766E),
                    ),
                  ),
                  const SizedBox(height: 10),

                  // Quick Suggestion Chips
                  Wrap(
                    spacing: 6,
                    runSpacing: 6,
                    children: chips.map((chip) {
                      return ActionChip(
                        label: Text(
                          '+ $chip',
                          style: const TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.w700,
                            color: HomeColors.primaryGreen,
                          ),
                        ),
                        backgroundColor: HomeColors.lightGreen,
                        side: BorderSide(
                          color: HomeColors.primaryGreen.withOpacity(0.3),
                          width: 0.8,
                        ),
                        onPressed: () => _addChipToNote(chip),
                      );
                    }).toList(),
                  ),

                  const SizedBox(height: 12),

                  // Form Input Catatan
                  TextFormField(
                    controller: _noteController,
                    minLines: 3,
                    maxLines: 5,
                    decoration: const InputDecoration(
                      labelText: 'Catatan Rinci Sawah',
                      hintText: 'Tulis takaran dosis pupuk, kondisi cuaca, atau catatan mandor...',
                      alignLabelWithHint: true,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSeasonSelector() {
    if (_isLoadingSeasons) {
      return Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: const Color(0xFFE5ECE3)),
        ),
        child: const Row(
          children: [
            SizedBox.square(
              dimension: 16,
              child: CircularProgressIndicator(
                strokeWidth: 2,
                color: HomeColors.primaryGreen,
              ),
            ),
            SizedBox(width: 10),
            Text(
              'Memuat data lahan...',
              style: TextStyle(fontSize: 12, color: Color(0xFF68766E)),
            ),
          ],
        ),
      );
    }

    if (_activeSeasons.isEmpty) {
      return Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: const Color(0xFFFEF3C7),
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: const Color(0xFFFDE68A)),
        ),
        child: Row(
          children: [
            const Icon(Icons.info_outline_rounded, color: Color(0xFFB45309)),
            const SizedBox(width: 10),
            const Expanded(
              child: Text(
                'Lahan aktif terdeteksi. Kegiatan akan dikaitkan ke musim tanam saat ini.',
                style: TextStyle(fontSize: 12, color: Color(0xFF92400E)),
              ),
            ),
          ],
        ),
      );
    }

    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFE5ECE3)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Lahan & Musim Tanam Terpilih',
            style: TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.w800,
              color: Color(0xFF17251E),
            ),
          ),
          const SizedBox(height: 8),
          DropdownButtonFormField<int>(
            initialValue: _selectedCropSeasonId,
            decoration: const InputDecoration(
              isDense: true,
              prefixIcon: Icon(Icons.landscape_rounded, color: HomeColors.primaryGreen),
            ),
            items: _activeSeasons.map((season) {
              final id = int.tryParse(season['id']?.toString() ?? '') ?? 0;
              final farmName = season['farm_name']?.toString() ??
                  season['name']?.toString() ??
                  'Lahan Sawah';
              final variety = season['variety_name']?.toString() ??
                  season['variety']?['name']?.toString() ??
                  'Padi';
              return DropdownMenuItem<int>(
                value: id,
                child: Text(
                  '$farmName ($variety)',
                  style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700),
                ),
              );
            }).toList(),
            onChanged: (val) {
              if (val != null) {
                setState(() => _selectedCropSeasonId = val);
              }
            },
          ),
        ],
      ),
    );
  }
}