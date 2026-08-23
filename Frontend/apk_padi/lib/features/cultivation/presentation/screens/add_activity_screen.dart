import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import 'package:padi/core/network/api_client.dart';
import 'package:padi/core/storage/token_storage.dart';

const Color activityGreen = Color(0xFF075C3D);
const Color activityBackground = Color(0xFFF7F9F4);
const Color activityText = Color(0xFF183D2D);

class AddActivityScreen extends StatefulWidget {
  const AddActivityScreen({
    super.key,
    this.cropSeasonId,
  });

  final int? cropSeasonId;

  @override
  State<AddActivityScreen> createState() => _AddActivityScreenState();
}

class _AddActivityScreenState extends State<AddActivityScreen> {
  final _noteController = TextEditingController();

  late final ApiClient _apiClient;

  bool _isSaving = false;
  String? _errorMessage;

  String selectedActivity = 'Pemupukan';

  final activities = const [
    ('Pemupukan', Icons.science_rounded, 'fertilizing'),
    ('Pengairan', Icons.water_drop_rounded, 'irrigation'),
    ('Penyemprotan', Icons.sanitizer_rounded, 'spraying'),
    ('Penyiangan', Icons.grass_rounded, 'other'),
    ('Lainnya', Icons.more_horiz_rounded, 'other'),
  ];

  @override
  void initState() {
    super.initState();

    _apiClient = ApiClient(
      const SecureTokenStorage(),
    );
  }

  @override
  void dispose() {
    _noteController.dispose();
    super.dispose();
  }

  Future<int?> _getCropSeasonId() async {
    if (widget.cropSeasonId != null) {
      return widget.cropSeasonId;
    }

    final response = await _apiClient.dio.get(
      '/crop-seasons',
    );

    final responseData = response.data['data'];

    if (responseData is! Map<String, dynamic>) {
      return null;
    }

    final seasons = responseData['crop_seasons'];

    if (seasons is! List || seasons.isEmpty) {
      return null;
    }

    Map<String, dynamic>? selectedSeason;

    for (final item in seasons) {
      if (item is Map<String, dynamic> &&
          item['status'] == 'active') {
        selectedSeason = item;
        break;
      }
    }

    selectedSeason ??= seasons.first
        as Map<String, dynamic>;

    final id = selectedSeason['id'];

    if (id is int) {
      return id;
    }

    if (id is num) {
      return id.toInt();
    }

    return int.tryParse(
      id.toString(),
    );
  }

  Future<void> _saveActivity() async {
    if (_isSaving) {
      return;
    }

    setState(() {
      _isSaving = true;
      _errorMessage = null;
    });

    try {
      final cropSeasonId = await _getCropSeasonId();

      if (cropSeasonId == null) {
        throw Exception(
          'Musim tanam tidak ditemukan.',
        );
      }

      final selected = activities.firstWhere(
        (activity) => activity.$1 == selectedActivity,
      );

      final payload = <String, dynamic>{
        'crop_season_id': cropSeasonId,
        'type': selected.$3,
        'occurred_at':
            DateTime.now().toIso8601String(),
        'notes':
            _noteController.text.trim().isEmpty
                ? null
                : _noteController.text.trim(),
        'cost': 0,
      };

      final response = await _apiClient.dio.post(
        '/farm-activities',
        data: payload,
      );

      if (!mounted) {
        return;
      }

      if (response.statusCode == 200 ||
          response.statusCode == 201) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text(
              'Kegiatan berhasil dicatat.',
            ),
          ),
        );

        context.go(
          '/land/timeline?cropSeasonId=$cropSeasonId',
        );

        return;
      }

      throw Exception(
        'Gagal menyimpan kegiatan.',
      );
    } catch (e) {
      if (!mounted) {
        return;
      }

      setState(() {
        _errorMessage = e.toString();
      });

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            'Gagal menyimpan kegiatan: $e',
          ),
        ),
      );
    } finally {
      if (mounted) {
        setState(() {
          _isSaving = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: activityBackground,
      appBar: AppBar(
        backgroundColor: activityBackground,
        elevation: 0,
        surfaceTintColor: Colors.transparent,
        leading: IconButton(
          onPressed: _isSaving
              ? null
              : () => context.pop(),
          icon: const Icon(
            Icons.arrow_back_rounded,
            color: activityGreen,
            size: 32,
          ),
        ),
        title: const Text(
          'Tambah Kegiatan',
          style: TextStyle(
            color: activityText,
            fontSize: 23,
            fontWeight: FontWeight.w900,
          ),
        ),
      ),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(
          20,
          8,
          20,
          30,
        ),
        children: [
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: const Color(0xFFEAF5EF),
              borderRadius:
                  BorderRadius.circular(24),
            ),
            child: const Row(
              children: [
                Icon(
                  Icons.edit_note_rounded,
                  color: activityGreen,
                  size: 42,
                ),
                SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment:
                        CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Apa yang dilakukan hari ini?',
                        style: TextStyle(
                          color: activityText,
                          fontSize: 19,
                          fontWeight:
                              FontWeight.w900,
                        ),
                      ),
                      SizedBox(height: 5),
                      Text(
                        'Pilih kegiatan yang sudah dilakukan di sawah.',
                        style: TextStyle(
                          color: Color(0xFF69766F),
                          fontSize: 13,
                          height: 1.4,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 25),
          const Text(
            'Pilih kegiatan',
            style: TextStyle(
              color: activityText,
              fontSize: 18,
              fontWeight: FontWeight.w900,
            ),
          ),
          const SizedBox(height: 13),
          ...activities.map(
            (activity) {
              final name = activity.$1;
              final icon = activity.$2;
              final selected =
                  selectedActivity == name;

              return Padding(
                padding:
                    const EdgeInsets.only(
                  bottom: 11,
                ),
                child: Material(
                  color: selected
                      ? const Color(0xFFEAF5EF)
                      : Colors.white,
                  borderRadius:
                      BorderRadius.circular(20),
                  child: InkWell(
                    onTap: _isSaving
                        ? null
                        : () {
                            setState(() {
                              selectedActivity =
                                  name;
                            });
                          },
                    borderRadius:
                        BorderRadius.circular(20),
                    child: Padding(
                      padding:
                          const EdgeInsets.all(16),
                      child: Row(
                        children: [
                          Container(
                            width: 54,
                            height: 54,
                            decoration:
                                BoxDecoration(
                              color: selected
                                  ? activityGreen
                                  : const Color(
                                      0xFFEAF5EF,
                                    ),
                              borderRadius:
                                  BorderRadius
                                      .circular(17),
                            ),
                            child: Icon(
                              icon,
                              color: selected
                                  ? Colors.white
                                  : activityGreen,
                              size: 29,
                            ),
                          ),
                          const SizedBox(width: 14),
                          Expanded(
                            child: Text(
                              name,
                              style:
                                  const TextStyle(
                                color: activityText,
                                fontSize: 16,
                                fontWeight:
                                    FontWeight.w800,
                              ),
                            ),
                          ),
                          Icon(
                            selected
                                ? Icons
                                    .radio_button_checked_rounded
                                : Icons
                                    .radio_button_off_rounded,
                            color: selected
                                ? activityGreen
                                : const Color(
                                    0xFF9AA49E,
                                  ),
                            size: 27,
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
              );
            },
          ),
          const SizedBox(height: 15),
          const Text(
            'Catatan tambahan',
            style: TextStyle(
              color: activityText,
              fontSize: 18,
              fontWeight: FontWeight.w900,
            ),
          ),
          const SizedBox(height: 4),
          const Text(
            'Tidak wajib diisi.',
            style: TextStyle(
              color: Color(0xFF69766F),
              fontSize: 13,
            ),
          ),
          const SizedBox(height: 10),
          TextField(
            controller: _noteController,
            maxLines: 4,
            enabled: !_isSaving,
            style: const TextStyle(
              color: activityText,
              fontSize: 15,
            ),
            decoration: InputDecoration(
              hintText:
                  'Contoh: menggunakan pupuk urea...',
              hintStyle: const TextStyle(
                color: Color(0xFF9AA49E),
              ),
              filled: true,
              fillColor: Colors.white,
              contentPadding:
                  const EdgeInsets.all(17),
              border: OutlineInputBorder(
                borderRadius:
                    BorderRadius.circular(18),
                borderSide: BorderSide.none,
              ),
              enabledBorder:
                  OutlineInputBorder(
                borderRadius:
                    BorderRadius.circular(18),
                borderSide:
                    const BorderSide(
                  color: Color(0xFFE1E7E2),
                ),
              ),
              focusedBorder:
                  OutlineInputBorder(
                borderRadius:
                    BorderRadius.circular(18),
                borderSide:
                    const BorderSide(
                  color: activityGreen,
                  width: 2,
                ),
              ),
            ),
          ),
          const SizedBox(height: 25),
          if (_errorMessage != null)
            Container(
              margin:
                  const EdgeInsets.only(
                bottom: 20,
              ),
              padding:
                  const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: const Color(
                  0xFFFFEAEA,
                ),
                borderRadius:
                    BorderRadius.circular(15),
              ),
              child: Text(
                _errorMessage!,
                style: const TextStyle(
                  color: Colors.red,
                  fontSize: 13,
                ),
              ),
            ),
          Container(
            padding:
                const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: const Color(0xFFFFF8DF),
              borderRadius:
                  BorderRadius.circular(20),
            ),
            child: const Row(
              children: [
                Icon(
                  Icons.volume_up_rounded,
                  color: Color(0xFF946E00),
                  size: 29,
                ),
                SizedBox(width: 12),
                Expanded(
                  child: Text(
                    'Pilih saja kegiatan yang sesuai. Catatan boleh dikosongkan.',
                    style: TextStyle(
                      color: Color(0xFF5B4808),
                      fontSize: 13,
                      height: 1.4,
                      fontWeight:
                          FontWeight.w600,
                    ),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 25),
          SizedBox(
            height: 64,
            child: ElevatedButton.icon(
              onPressed:
                  _isSaving
                      ? null
                      : _saveActivity,
              icon: _isSaving
                  ? const SizedBox(
                      width: 25,
                      height: 25,
                      child:
                          CircularProgressIndicator(
                        strokeWidth: 3,
                        color: Colors.white,
                      ),
                    )
                  : const Icon(
                      Icons.check_rounded,
                      size: 28,
                    ),
              label: Text(
                _isSaving
                    ? 'Menyimpan...'
                    : 'Simpan Kegiatan',
                style: const TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.w900,
                ),
              ),
              style:
                  ElevatedButton.styleFrom(
                backgroundColor:
                    activityGreen,
                foregroundColor:
                    Colors.white,
                disabledBackgroundColor:
                    activityGreen.withValues(
                  alpha: 0.6,
                ),
                elevation: 0,
                shape:
                    RoundedRectangleBorder(
                  borderRadius:
                      BorderRadius.circular(20),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}