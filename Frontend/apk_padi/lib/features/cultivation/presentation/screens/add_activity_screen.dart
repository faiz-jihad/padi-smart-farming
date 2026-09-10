import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_tts/flutter_tts.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import 'package:padi/core/providers/app_providers.dart';
import 'package:padi/features/cultivation/data/services/activity_voice_parser.dart';
import 'package:padi/features/home/presentation/tokens/home_tokens.dart';
import 'package:padi/features/plant_check/data/services/plant_check_api_service.dart';
import 'package:speech_to_text/speech_to_text.dart';

class AddActivityScreen extends ConsumerStatefulWidget {
  const AddActivityScreen({super.key, this.cropSeasonId});

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
  bool _isLoadingAiRecommendation = false;
  bool _isSaving = false;
  bool _isSpeechSupported = false;
  bool _isListening = false;
  bool _isReadingRecommendation = false;
  bool _isVoiceActivity = false;
  bool _suppressNoteActivityAutoDetection = false;
  String? _errorMessage;
  String _voiceStatus = 'Berbicara siap digunakan.';

  List<Map<String, dynamic>> _activeSeasons = [];
  Map<String, dynamic>? _aiRecommendation;

  final SpeechToText _speechToText = SpeechToText();
  final FlutterTts _flutterTts = FlutterTts();

  static const Map<String, List<String>> _keywordActivityMap = {
    'planting': ['bibit', 'tanam', 'semai', 'penanaman'],
    'fertilizing': ['pupuk', 'urea', 'npk', 'organik'],
    'irrigation': ['siram', 'air', 'irigasi', 'genangan', 'pengairan'],
    'spraying': ['semprot', 'racun', 'pestisida', 'fungisida', 'insektisida'],
    'land_preparation': ['bajak', 'garu', 'pematang', 'olah lahan'],
    'other': ['gulma', 'matun', 'penyiangan'],
  };

  static const List<Map<String, dynamic>> _activityTypes = [
    {
      'key': 'fertilizing',
      'label': 'Pemupukan',
      'icon': Icons.science_rounded,
      'color': Color(0xFF0284C7),
      'desc': 'Pupuk dasar atau susulan',
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
      'label': 'Pengairan',
      'icon': Icons.water_drop_rounded,
      'color': Color(0xFF0EA5E9),
      'desc': 'Air, genangan, dan irigasi',
      'chips': [
        'Tinggi air 3 cm (macak-macak)',
        'Penggenangan 5 cm',
        'Pengeringan berkala',
        'Buka pintu saluran irigasi',
      ],
    },
    {
      'key': 'spraying',
      'label': 'Semprot',
      'icon': Icons.sanitizer_rounded,
      'color': Color(0xFFF59E0B),
      'desc': 'Hama, penyakit, atau pupuk daun',
      'chips': [
        'Insektisida Wereng 200 ml',
        'Fungisida Blas Padi',
        'Pupuk Hayati / ZPT',
        'Bakterisida Kresek',
      ],
    },
    {
      'key': 'planting',
      'label': 'Tanam',
      'icon': Icons.spa_rounded,
      'color': Color(0xFF10B981),
      'desc': 'Bibit dan penanaman',
      'chips': [
        'Tanam bibit umur 15-20 HSS',
        'Sistem Jajar Legowo 2:1',
        'Sistem Jajar Legowo 4:1',
        'Tabela (Tanam Benih Langsung)',
      ],
    },
    {
      'key': 'land_preparation',
      'label': 'Olah Lahan',
      'icon': Icons.agriculture_rounded,
      'color': Color(0xFF047857),
      'desc': 'Bajak, garu, dan pematang',
      'chips': [
        'Bajak singkal traktor',
        'Perataan tanah (garu)',
        'Pembersihan jerami',
        'Perbaikan pematang sawah',
      ],
    },
    {
      'key': 'other',
      'label': 'Pemeliharaan',
      'icon': Icons.grass_rounded,
      'color': Color(0xFF059669),
      'desc': 'Penyiangan dan perawatan',
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
    _noteController.addListener(_handleNoteChanged);
    _loadCropSeasons();
    _initializeSpeech();
    _initializeTts();
  }

  @override
  void dispose() {
    _noteController.removeListener(_handleNoteChanged);
    _noteController.dispose();
    _costController.dispose();
    _flutterTts.stop();
    super.dispose();
  }

  Future<void> _initializeSpeech() async {
    try {
      final available = await _speechToText.initialize(
        onError: (error) {
          if (!mounted) return;
          setState(() {
            _isSpeechSupported = false;
            _isListening = false;
            _voiceStatus = 'Berbicara tidak tersedia: ${error.errorMsg}';
          });
        },
        onStatus: (status) {
          if (!mounted) return;

          final isListening = status == 'listening';

          setState(() {
            _isListening = isListening;
            _voiceStatus = isListening
                ? 'Mendengarkan perintah suara...'
                : 'Berbicara siap digunakan.';
          });
        },
      );

      if (!mounted) return;

      setState(() {
        _isSpeechSupported = available;
        _isListening = false;
        _voiceStatus = available
            ? 'Berbicara siap digunakan.'
            : 'Berbicara tidak tersedia di perangkat ini.';
      });
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _isSpeechSupported = false;
        _isListening = false;
        _voiceStatus = 'Berbicara tidak tersedia di perangkat ini.';
      });
    }
  }

  Future<void> _initializeTts() async {
    try {
      await _flutterTts.setLanguage('id-ID');
      // Kecepatan dan nada ini dibuat mendekati percakapan biasa, agar
      // instruksi lapangan tetap mudah diikuti.
      await _flutterTts.setSpeechRate(0.42);
      await _flutterTts.setPitch(1.0);
      await _flutterTts.setVolume(1.0);
      _flutterTts.setCompletionHandler(() {
        if (mounted) {
          setState(() => _isReadingRecommendation = false);
        }
      });
      _flutterTts.setCancelHandler(() {
        if (mounted) {
          setState(() => _isReadingRecommendation = false);
        }
      });
      _flutterTts.setErrorHandler((_) {
        if (mounted) {
          setState(() => _isReadingRecommendation = false);
        }
      });
    } catch (_) {
      // TTS dimungkinkan tidak tersedia di beberapa perangkat.
    }
  }

  Future<void> _toggleSpeechRecognition() async {
    if (!_isSpeechSupported) {
      setState(() {
        _isListening = false;
        _voiceStatus = 'Berbicara tidak tersedia di perangkat ini.';
      });
      return;
    }

    if (_isListening) {
      await _speechToText.stop();
      if (!mounted) return;
      setState(() {
        _isListening = false;
        _voiceStatus = 'Berbicara siap digunakan.';
      });
      return;
    }

    final localeId = await _speechToText.systemLocale();

    if (!mounted) return;

    setState(() {
      _isListening = true;
      _voiceStatus = 'Mendengarkan perintah suara...';
    });

    try {
      await _speechToText.listen(
        listenOptions: SpeechListenOptions(
          localeId: localeId?.localeId ?? 'id_ID',
        ),
        onResult: (result) async {
          if (!result.finalResult || result.recognizedWords.trim().isEmpty) {
            return;
          }

          final parsed = ActivityVoiceParser.parse(result.recognizedWords);

          if (!mounted) return;

          setState(() {
            if (parsed.hasRecognizedActivity) {
              _selectedActivityKey = parsed.activityKey;
              _isVoiceActivity = true;
            }
            if (parsed.cost != null) {
              _costController.text = parsed.cost.toString();
            }
            _voiceStatus = parsed.hasRecognizedActivity
                ? 'Suara terdeteksi: ${parsed.activityLabel.toLowerCase()}.'
                : parsed.cost != null
                ? 'Nominal biaya berhasil diisi.'
                : parsed.shouldSave
                ? 'Perintah simpan diterima.'
                : 'Suara diterima. Pilih aktivitas jika perlu.';
          });

          if (!parsed.shouldSave) {
            final existingText = _noteController.text.trim();
            final nextText = existingText.isEmpty
                ? parsed.transcript
                : '$existingText\n${parsed.transcript}';

            _noteController.text = nextText;
            _noteController.selection = TextSelection.collapsed(
              offset: _noteController.text.length,
            );
          }

          if (parsed.hasRecognizedActivity) {
            ScaffoldMessenger.of(context).showSnackBar(
              SnackBar(
                content: Text(
                  'Berbicara mengenali aktivitas ${parsed.activityLabel.toLowerCase()}.',
                ),
                behavior: SnackBarBehavior.floating,
                backgroundColor: HomeColors.primaryGreen,
              ),
            );
          }

          if (parsed.shouldSave) {
            await _speechToText.stop();
            if (mounted) await _saveActivity();
          }
        },
      );
    } catch (_) {
      if (!mounted) return;
      setState(() {
        _isListening = false;
        _voiceStatus = 'Berbicara sedang tidak tersedia saat ini.';
      });
    }
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

      await _loadAiRecommendation();
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _isLoadingSeasons = false;
        _selectedCropSeasonId = widget.cropSeasonId ?? 1;
      });
    }
  }

  void _handleNoteChanged() {
    if (_suppressNoteActivityAutoDetection) {
      return;
    }

    final text = _noteController.text.toLowerCase();
    if (text.trim().isEmpty) {
      return;
    }

    for (final entry in _keywordActivityMap.entries) {
      final matched = entry.value.any((keyword) => text.contains(keyword));
      if (matched) {
        setState(() => _selectedActivityKey = entry.key);
        return;
      }
    }
  }

  Future<void> _loadAiRecommendation() async {
    if (!mounted) return;

    final selectedSeason = _activeSeasons.isEmpty
        ? const <String, dynamic>{}
        : _activeSeasons.firstWhere(
            (season) =>
                (season['id']?.toString() ?? '') ==
                _selectedCropSeasonId.toString(),
            orElse: () => const <String, dynamic>{},
          );

    final farmId =
        int.tryParse(selectedSeason['farm_id']?.toString() ?? '') ?? 0;

    if (farmId <= 0) {
      if (mounted) {
        setState(() {
          _isLoadingAiRecommendation = false;
          _aiRecommendation = null;
        });
      }
      return;
    }

    setState(() {
      _isLoadingAiRecommendation = true;
      _aiRecommendation = null;
    });

    try {
      final apiClient = ref.read(apiClientProvider);
      final weatherResponse = await apiClient.dio.get(
        '/farms/$farmId/weather-advisory',
      );
      final weatherData =
          weatherResponse.data?['data'] as Map<String, dynamic>? ?? {};

      final weatherDescription =
          weatherData['weather'] is Map &&
              (weatherData['weather'] as Map).containsKey('description')
          ? (weatherData['weather'] as Map)['description']?.toString() ??
                'Cuaca umum'
          : 'Cuaca umum';

      final advisories = weatherData['advisories'];
      String weatherAction = '';
      if (advisories is List && advisories.isNotEmpty) {
        final firstAdvice = advisories.first;
        if (firstAdvice is Map<String, dynamic>) {
          weatherAction = firstAdvice['action']?.toString() ?? '';
        } else if (firstAdvice is Map) {
          weatherAction = firstAdvice['action']?.toString() ?? '';
        }
      }

      final phaseName = weatherData['phase_name']?.toString() ?? '';

      final scans = await ref.read(plantCheckApiServiceProvider).fetchScans();
      final relatedScans = scans
          .where((scan) => scan.farmId == farmId)
          .toList();
      PlantCheckResult? latestScan;
      if (relatedScans.isNotEmpty) {
        latestScan = relatedScans.reduce(
          (current, next) => current.id >= next.id ? current : next,
        );
      }

      final diseaseName = latestScan?.predictedClass ?? 'Tidak ada gejala';
      final recommendationText =
          latestScan?.recommendation?.langkahPreventif ??
          latestScan?.recommendation?.rekomendasiObat ??
          '';

      final suggestedActivityKey = _inferSuggestedActivityKey(
        weatherAction,
        recommendationText,
        diseaseName,
      );

      final suggestedActivity = _activityTypes.firstWhere(
        (activity) => activity['key'] == suggestedActivityKey,
        orElse: () => _activityTypes.first,
      );

      final recommendedDate = DateTime.now().add(const Duration(hours: 2));
      final aiNote = _buildAiRecommendationNote(
        weatherDescription,
        weatherAction,
        diseaseName,
        recommendationText,
        suggestedActivity['label'] as String,
      );

      if (!mounted) return;

      final speechText = _buildAiSpeechText(
        weatherDescription,
        weatherAction,
        diseaseName,
        recommendationText,
        suggestedActivity['label'] as String,
      );

      setState(() {
        _aiRecommendation = {
          'activityKey': suggestedActivityKey,
          'activityLabel': suggestedActivity['label'],
          'summary': _recommendationTitle(suggestedActivityKey),
          'note': aiNote,
          'speechText': speechText,
          'recommendedDate': recommendedDate,
          'phaseName': phaseName,
          'weatherAction': weatherAction,
          'diseaseName': diseaseName,
        };
      });
    } catch (_) {
      if (mounted) {
        setState(() {
          _aiRecommendation = null;
        });
      }
    } finally {
      if (mounted) {
        setState(() {
          _isLoadingAiRecommendation = false;
        });
      }
    }
  }

  String _inferSuggestedActivityKey(
    String weatherAction,
    String recommendationText,
    String diseaseName,
  ) {
    final combinedText = [
      weatherAction,
      recommendationText,
      diseaseName,
    ].join(' ').toLowerCase();

    if (combinedText.contains('semprot') ||
        combinedText.contains('pestisida') ||
        combinedText.contains('fungisida') ||
        combinedText.contains('insektisida') ||
        combinedText.contains('racun')) {
      return 'spraying';
    }

    if (combinedText.contains('siram') ||
        combinedText.contains('irigasi') ||
        combinedText.contains('genangan') ||
        combinedText.contains('pengairan') ||
        combinedText.contains('air')) {
      return 'irrigation';
    }

    if (combinedText.contains('pupuk') ||
        combinedText.contains('urea') ||
        combinedText.contains('npk') ||
        combinedText.contains('organik')) {
      return 'fertilizing';
    }

    if (combinedText.contains('bibit') ||
        combinedText.contains('tanam') ||
        combinedText.contains('semai') ||
        combinedText.contains('penanaman')) {
      return 'planting';
    }

    if (combinedText.contains('gulma') ||
        combinedText.contains('matun') ||
        combinedText.contains('penyiangan')) {
      return 'other';
    }

    if (combinedText.contains('bajak') ||
        combinedText.contains('garu') ||
        combinedText.contains('pematang')) {
      return 'land_preparation';
    }

    return 'fertilizing';
  }

  String _buildAiRecommendationNote(
    String weatherDescription,
    String weatherAction,
    String diseaseName,
    String recommendationText,
    String activityLabel,
  ) {
    final action = _cleanRecommendationText(weatherAction);
    final plantAdvice = _cleanRecommendationText(recommendationText);
    final condition = _cleanDiseaseName(diseaseName);
    final context = <String>[
      if (weatherDescription.trim().isNotEmpty) 'Cuaca: $weatherDescription.',
      if (condition != null) 'Kondisi tanaman: $condition.',
    ];
    final guidance = plantAdvice.isNotEmpty ? plantAdvice : action;

    return [
      'Rekomendasi $activityLabel.',
      if (guidance.isNotEmpty) guidance,
      ...context,
      _defaultActivityGuidance(activityLabel),
    ].join(' ');
  }

  String _buildAiSpeechText(
    String weatherDescription,
    String weatherAction,
    String diseaseName,
    String recommendationText,
    String activityLabel,
  ) {
    final action = _cleanRecommendationText(weatherAction);
    final plantAdvice = _cleanRecommendationText(recommendationText);
    final condition = _cleanDiseaseName(diseaseName);
    final guidance = plantAdvice.isNotEmpty ? plantAdvice : action;
    final weather = weatherDescription.trim();

    return [
      'Berikut rekomendasi untuk lahan Anda.',
      'Saat ini, disarankan melakukan ${activityLabel.toLowerCase()}.',
      if (weather.isNotEmpty && weather.toLowerCase() != 'cuaca umum')
        'Kondisi cuaca terpantau $weather.',
      if (condition != null) 'Kondisi tanaman yang tercatat adalah $condition.',
      if (guidance.isNotEmpty) guidance,
      _defaultActivityGuidance(activityLabel),
      'Silakan sesuaikan tindakan dengan kondisi lahan saat diperiksa langsung.',
    ].join(' ');
  }

  String _cleanRecommendationText(String value) {
    final text = value.trim();
    if (text.isEmpty || text.toLowerCase() == 'tidak ada') return '';
    return text.replaceAll(RegExp(r'\s+'), ' ');
  }

  String? _cleanDiseaseName(String value) {
    final text = _cleanRecommendationText(value);
    if (text.isEmpty ||
        text.toLowerCase().contains('tidak ada gejala') ||
        text.toLowerCase().contains('tidak terdeteksi')) {
      return null;
    }
    return text;
  }

  String _defaultActivityGuidance(String activityLabel) {
    return switch (activityLabel) {
      'Pemupukan' =>
        'Gunakan pupuk sesuai dosis dan fase pertumbuhan, lalu pastikan tanah cukup lembap.',
      'Pengairan' =>
        'Atur air secukupnya dan periksa saluran agar alirannya lancar.',
      'Semprot' =>
        'Lakukan penyemprotan saat cuaca tenang dan gunakan dosis sesuai label produk.',
      'Tanam' =>
        'Pilih bibit sehat dan jaga jarak tanam agar tanaman tumbuh merata.',
      'Olah Lahan' =>
        'Ratakan tanah dan perbaiki pematang sebelum kegiatan berikutnya dilakukan.',
      _ => 'Pantau kondisi lahan secara berkala dan catat perubahan yang ditemukan.',
    };
  }

  String _recommendationTitle(String activityKey) {
    return switch (activityKey) {
      'fertilizing' => 'PEMUPUKAN',
      'irrigation' => 'PENGAIRAN',
      'planting' => 'TANAM',
      'spraying' => 'SEMPROT',
      'land_preparation' => 'OLAH LAHAN',
      _ => 'PEMELIHARAAN',
    };
  }

  String _recommendationTitleFromLabel(String label) {
    final activity = _activityTypes.firstWhere(
      (item) => item['label'] == label,
      orElse: () => _activityTypes.last,
    );
    return _recommendationTitle(activity['key'] as String);
  }

  void _applyAiRecommendation() {
    final recommendation = _aiRecommendation;
    if (recommendation == null) {
      return;
    }

    final selectedActivity = recommendation['activityKey'] as String;
    final recommendedDate = recommendation['recommendedDate'] as DateTime?;

    setState(() {
      _selectedActivityKey = selectedActivity;
      if (recommendedDate != null) {
        _selectedDate = recommendedDate;
      }
    });

    _suppressNoteActivityAutoDetection = true;

    final existingText = _noteController.text.trim();
    final combinedText = existingText.isEmpty
        ? recommendation['note'] as String
        : '$existingText\n\n${recommendation['note']}';

    _noteController.text = combinedText;
    _noteController.selection = TextSelection.collapsed(
      offset: _noteController.text.length,
    );

    Future.microtask(() {
      if (mounted) {
        setState(() {
          _suppressNoteActivityAutoDetection = false;
        });
      }
    });

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(
          'Rekomendasi AI diterapkan: ${recommendation['activityLabel']}.',
        ),
        behavior: SnackBarBehavior.floating,
        backgroundColor: HomeColors.primaryGreen,
      ),
    );
  }

  Future<void> _toggleRecommendationSpeech() async {
    final recommendation = _aiRecommendation;
    if (recommendation == null) {
      return;
    }

    if (_isReadingRecommendation) {
      await _flutterTts.stop();
      if (mounted) {
        setState(() => _isReadingRecommendation = false);
      }
      return;
    }

    final text =
        recommendation['speechText'] as String? ??
        recommendation['summary'] as String;

    try {
      if (mounted) {
        setState(() => _isReadingRecommendation = true);
      }
      await _flutterTts.speak(text);
    } catch (_) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('Suara rekomendasi tidak tersedia saat ini.'),
            behavior: SnackBarBehavior.floating,
          ),
        );
      }
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
      final costClean = _costController.text.replaceAll(RegExp(r'[^0-9]'), '');
      final cost = int.tryParse(costClean) ?? 0;

      final payload = <String, dynamic>{
        'crop_season_id': seasonId,
        'type': _selectedActivityKey,
        'occurred_at': _selectedDate.toIso8601String().substring(0, 10),
        'notes': _noteController.text.trim().isEmpty
            ? null
            : _noteController.text.trim(),
        'cost': cost,
        'source': _isVoiceActivity ? 'VOICE' : 'MANUAL',
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
    final dateFormatted = DateFormat(
      'EEEE, d MMMM yyyy',
      'id_ID',
    ).format(_selectedDate);

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
          'Catat Kegiatan',
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
              color: Colors.black.withValues(alpha: 0.06),
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
              _isSaving ? 'Menyimpan...' : 'Simpan',
              style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w800),
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

            if (_isLoadingAiRecommendation || _aiRecommendation != null)
              Container(
                padding: const EdgeInsets.all(14),
                decoration: BoxDecoration(
                  color: HomeColors.lightGreen,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(
                    color: HomeColors.primaryGreen.withValues(alpha: 0.3),
                  ),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        const Icon(
                          Icons.auto_awesome_rounded,
                          color: HomeColors.primaryGreen,
                          size: 18,
                        ),
                        const SizedBox(width: 8),
                        const Expanded(
                          child: Text(
                            'Rekomendasi',
                            style: TextStyle(
                              fontSize: 14,
                              fontWeight: FontWeight.w800,
                              color: Color(0xFF17251E),
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    if (_isLoadingAiRecommendation)
                      const Row(
                        children: [
                          SizedBox.square(
                            dimension: 14,
                            child: CircularProgressIndicator(
                              strokeWidth: 2,
                              color: HomeColors.primaryGreen,
                            ),
                          ),
                          SizedBox(width: 10),
                          Text(
                            'Menganalisis data...',
                            style: TextStyle(
                              fontSize: 12,
                              color: Color(0xFF68766E),
                            ),
                          ),
                        ],
                      )
                    else if (_aiRecommendation != null) ...[
                      Text(
                        _aiRecommendation!['summary'] as String,
                        style: const TextStyle(
                          fontSize: 14,
                          fontWeight: FontWeight.w800,
                          color: Color(0xFF1F2A1F),
                          height: 1.4,
                        ),
                      ),
                      const SizedBox(height: 10),
                      Row(
                        mainAxisAlignment: MainAxisAlignment.end,
                        children: [
                          OutlinedButton.icon(
                            onPressed: _toggleRecommendationSpeech,
                            icon: Icon(
                              _isReadingRecommendation
                                  ? Icons.stop_circle_rounded
                                  : Icons.volume_up_rounded,
                              size: 18,
                            ),
                            label: Text(
                              _isReadingRecommendation ? 'Stop' : 'Baca',
                            ),
                            style: OutlinedButton.styleFrom(
                              foregroundColor: HomeColors.primaryGreen,
                              side: const BorderSide(
                                color: HomeColors.primaryGreen,
                              ),
                              minimumSize: const Size(0, 42),
                            ),
                          ),
                          const SizedBox(width: 8),
                          FilledButton.icon(
                            onPressed: _applyAiRecommendation,
                            icon: const Icon(
                              Icons.check_circle_rounded,
                              size: 18,
                            ),
                            label: const Text('Pakai'),
                            style: FilledButton.styleFrom(
                              backgroundColor: HomeColors.primaryGreen,
                              foregroundColor: Colors.white,
                              minimumSize: const Size(0, 42),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ],
                ),
              ),

            const SizedBox(height: 12),

            // 2. Berbicara & Pencatatan Suara
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
                  Row(
                    children: [
                      const Icon(
                        Icons.record_voice_over_rounded,
                        color: HomeColors.primaryGreen,
                        size: 18,
                      ),
                      const SizedBox(width: 8),
                      const Expanded(
                        child: Text(
                          'Berbicara',
                          style: TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.w800,
                            color: Color(0xFF17251E),
                          ),
                        ),
                      ),
                      if (_isListening)
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 10,
                            vertical: 6,
                          ),
                          decoration: BoxDecoration(
                            color: HomeColors.lightGreen,
                            borderRadius: BorderRadius.circular(999),
                            border: Border.all(
                              color: HomeColors.primaryGreen.withValues(
                                alpha: 0.3,
                              ),
                            ),
                          ),
                          child: const Text(
                            'Aktif',
                            style: TextStyle(
                              fontSize: 11,
                              fontWeight: FontWeight.w800,
                              color: HomeColors.primaryGreen,
                            ),
                          ),
                        ),
                    ],
                  ),
                  const SizedBox(height: 10),
                  Text(
                    _voiceStatus,
                    style: const TextStyle(
                      fontSize: 12,
                      color: Color(0xFF4B5E53),
                      height: 1.4,
                    ),
                  ),
                  const SizedBox(height: 8),
                  SizedBox(
                    width: double.infinity,
                    child: FilledButton.icon(
                      onPressed: _isSpeechSupported
                          ? _toggleSpeechRecognition
                          : null,
                      icon: Icon(
                        _isListening ? Icons.stop_rounded : Icons.mic_rounded,
                      ),
                      label: Text(_isListening ? 'Selesai' : 'Mulai'),
                      style: FilledButton.styleFrom(
                        minimumSize: const Size.fromHeight(50),
                        backgroundColor: _isListening
                            ? HomeColors.danger
                            : HomeColors.primaryGreen,
                        foregroundColor: Colors.white,
                      ),
                    ),
                  ),
                  const SizedBox(height: 8),
                  Container(
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: HomeColors.lightGreen,
                      borderRadius: BorderRadius.circular(10),
                      border: Border.all(
                        color: HomeColors.primaryGreen.withValues(alpha: 0.3),
                      ),
                    ),
                    child: const Text(
                      'Contoh: “Tanam bibit”, “Nominal harga 150000”, lalu “Simpan”.',
                      style: TextStyle(
                        fontSize: 11.5,
                        color: Color(0xFF1F2A1F),
                        height: 1.4,
                      ),
                    ),
                  ),
                ],
              ),
            ),

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
                    'Kegiatan',
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
                                      : color.withValues(alpha: 0.12),
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

            // 3. Waktu
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
                    'Waktu',
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
                                  'Tanggal',
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
                      labelText: 'Biaya (opsional)',
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

            // 4. Catatan
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
                    'Catatan',
                    style: TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.w800,
                      color: Color(0xFF17251E),
                    ),
                  ),
                  const SizedBox(height: 6),
                  const Text(
                    'Pilih detail cepat:',
                    style: TextStyle(fontSize: 11.5, color: Color(0xFF68766E)),
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
                          color: HomeColors.primaryGreen.withValues(alpha: 0.3),
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
                      labelText: 'Catatan',
                      hintText:
                          'Tulis detail kegiatan, dosis, atau kondisi lahan...',
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

  String _formatSeasonLabel(Map<String, dynamic> season) {
    final farmName =
        (season['farm_name'] ??
                season['name'] ??
                season['farm']?['name'] ??
                'Lahan')
            .toString()
            .trim();
    final varietyName =
        (season['variety_name'] ?? season['variety']?['name'] ?? 'Padi')
            .toString()
            .trim();

    final parts = <String>[];
    if (farmName.isNotEmpty && farmName != 'Lahan') {
      parts.add(farmName);
    }
    if (varietyName.isNotEmpty && varietyName != 'Padi') {
      parts.add(varietyName);
    }

    if (parts.isEmpty) {
      return 'Lahan Sawah';
    }

    return parts.join(' • ');
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
              'Memuat data...',
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
                'Belum ada lahan aktif.',
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
            'Lahan',
            style: TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.w800,
              color: Color(0xFF17251E),
            ),
          ),
          const SizedBox(height: 8),
          DropdownButtonFormField<int>(
            initialValue: _selectedCropSeasonId,
            isExpanded: true,
            decoration: const InputDecoration(
              isDense: true,
              prefixIcon: Icon(
                Icons.landscape_rounded,
                color: HomeColors.primaryGreen,
              ),
            ),
            items: _activeSeasons.map((season) {
              final id = int.tryParse(season['id']?.toString() ?? '') ?? 0;
              final label = _formatSeasonLabel(season);

              return DropdownMenuItem<int>(
                value: id,
                child: Text(
                  label,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              );
            }).toList(),
            onChanged: (val) async {
              if (val != null) {
                setState(() => _selectedCropSeasonId = val);
                await _loadAiRecommendation();
              }
            },
          ),
        ],
      ),
    );
  }
}
