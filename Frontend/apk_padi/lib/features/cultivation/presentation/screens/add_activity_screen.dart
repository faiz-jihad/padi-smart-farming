import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

const Color activityGreen = Color(0xFF075C3D);
const Color activityBackground = Color(0xFFF7F9F4);
const Color activityText = Color(0xFF183D2D);

class AddActivityScreen extends StatefulWidget {
  const AddActivityScreen({super.key});

  @override
  State<AddActivityScreen> createState() =>
      _AddActivityScreenState();
}

class _AddActivityScreenState
    extends State<AddActivityScreen> {
  final _noteController = TextEditingController();

  String selectedActivity = 'Pemupukan';

  final activities = const [
    (
      'Pemupukan',
      Icons.science_rounded,
    ),
    (
      'Pengairan',
      Icons.water_drop_rounded,
    ),
    (
      'Penyemprotan',
      Icons.sanitizer_rounded,
    ),
    (
      'Penyiangan',
      Icons.grass_rounded,
    ),
    (
      'Lainnya',
      Icons.more_horiz_rounded,
    ),
  ];

  @override
  void dispose() {
    _noteController.dispose();
    super.dispose();
  }

  void _saveActivity() {
    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text(
          'Kegiatan berhasil dicatat.',
        ),
      ),
    );

    context.go('/land/timeline');
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
          onPressed: () => context.pop(),
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
              borderRadius: BorderRadius.circular(24),
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
                          fontWeight: FontWeight.w900,
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
                padding: const EdgeInsets.only(
                  bottom: 11,
                ),
                child: Material(
                  color: selected
                      ? const Color(0xFFEAF5EF)
                      : Colors.white,
                  borderRadius:
                      BorderRadius.circular(20),
                  child: InkWell(
                    onTap: () {
                      setState(() {
                        selectedActivity = name;
                      });
                    },
                    borderRadius:
                        BorderRadius.circular(20),
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Row(
                        children: [
                          Container(
                            width: 54,
                            height: 54,
                            decoration: BoxDecoration(
                              color: selected
                                  ? activityGreen
                                  : const Color(
                                      0xFFEAF5EF,
                                    ),
                              borderRadius:
                                  BorderRadius.circular(
                                17,
                              ),
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
                              style: const TextStyle(
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
              enabledBorder: OutlineInputBorder(
                borderRadius:
                    BorderRadius.circular(18),
                borderSide: const BorderSide(
                  color: Color(0xFFE1E7E2),
                ),
              ),
              focusedBorder: OutlineInputBorder(
                borderRadius:
                    BorderRadius.circular(18),
                borderSide: const BorderSide(
                  color: activityGreen,
                  width: 2,
                ),
              ),
            ),
          ),
          const SizedBox(height: 25),
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: const Color(0xFFFFF8DF),
              borderRadius: BorderRadius.circular(20),
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
                      fontWeight: FontWeight.w600,
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
              onPressed: _saveActivity,
              icon: const Icon(
                Icons.check_rounded,
                size: 28,
              ),
              label: const Text(
                'Simpan Kegiatan',
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.w900,
                ),
              ),
              style: ElevatedButton.styleFrom(
                backgroundColor: activityGreen,
                foregroundColor: Colors.white,
                elevation: 0,
                shape: RoundedRectangleBorder(
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