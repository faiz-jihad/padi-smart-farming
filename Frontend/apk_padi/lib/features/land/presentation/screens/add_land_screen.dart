import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

const Color addLandGreen = Color(0xFF075C3D);
const Color addLandBackground = Color(0xFFF7F9F4);
const Color addLandText = Color(0xFF183D2D);

class AddLandScreen extends StatefulWidget {
  const AddLandScreen({super.key});

  @override
  State<AddLandScreen> createState() => _AddLandScreenState();
}

class _AddLandScreenState extends State<AddLandScreen> {
  final _formKey = GlobalKey<FormState>();

  final _nameController = TextEditingController();
  final _locationController = TextEditingController();
  final _areaController = TextEditingController();

  @override
  void dispose() {
    _nameController.dispose();
    _locationController.dispose();
    _areaController.dispose();
    super.dispose();
  }

  void _saveLand() {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Lahan berhasil ditambahkan.'),
      ),
    );

    context.go('/land');
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: addLandBackground,
      appBar: AppBar(
        backgroundColor: addLandBackground,
        elevation: 0,
        surfaceTintColor: Colors.transparent,
        leading: IconButton(
          onPressed: () => context.pop(),
          icon: const Icon(
            Icons.arrow_back_rounded,
            color: addLandGreen,
            size: 32,
          ),
        ),
        title: const Text(
          'Tambah Lahan',
          style: TextStyle(
            color: addLandText,
            fontSize: 24,
            fontWeight: FontWeight.w900,
          ),
        ),
      ),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.fromLTRB(20, 8, 20, 30),
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
                    Icons.grass_rounded,
                    color: addLandGreen,
                    size: 42,
                  ),
                  SizedBox(width: 15),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Tambah sawah Anda',
                          style: TextStyle(
                            color: addLandText,
                            fontSize: 19,
                            fontWeight: FontWeight.w900,
                          ),
                        ),
                        SizedBox(height: 5),
                        Text(
                          'Isi informasi sederhana tentang lahan yang ingin dikelola.',
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

            const SizedBox(height: 26),

            const _FieldLabel(
              title: 'Nama lahan',
              description: 'Contoh: Sawah Belakang Rumah',
            ),

            const SizedBox(height: 9),

            TextFormField(
              controller: _nameController,
              textInputAction: TextInputAction.next,
              style: const TextStyle(
                color: addLandText,
                fontSize: 16,
                fontWeight: FontWeight.w600,
              ),
              decoration: _inputDecoration(
                hint: 'Nama sawah',
                icon: Icons.grass_rounded,
              ),
              validator: (value) {
                if (value == null || value.trim().isEmpty) {
                  return 'Nama lahan belum diisi';
                }

                return null;
              },
            ),

            const SizedBox(height: 22),

            const _FieldLabel(
              title: 'Lokasi sawah',
              description: 'Masukkan desa atau lokasi sawah',
            ),

            const SizedBox(height: 9),

            TextFormField(
              controller: _locationController,
              textInputAction: TextInputAction.next,
              style: const TextStyle(
                color: addLandText,
                fontSize: 16,
                fontWeight: FontWeight.w600,
              ),
              decoration: _inputDecoration(
                hint: 'Contoh: Desa Karanganyar',
                icon: Icons.location_on_outlined,
              ),
              validator: (value) {
                if (value == null || value.trim().isEmpty) {
                  return 'Lokasi lahan belum diisi';
                }

                return null;
              },
            ),

            const SizedBox(height: 22),

            const _FieldLabel(
              title: 'Luas lahan',
              description: 'Masukkan luas sawah dalam meter persegi',
            ),

            const SizedBox(height: 9),

            TextFormField(
              controller: _areaController,
              keyboardType: TextInputType.number,
              textInputAction: TextInputAction.done,
              style: const TextStyle(
                color: addLandText,
                fontSize: 16,
                fontWeight: FontWeight.w600,
              ),
              decoration: _inputDecoration(
                hint: 'Contoh: 1200',
                icon: Icons.straighten_rounded,
                suffixText: 'm²',
              ),
              validator: (value) {
                if (value == null || value.trim().isEmpty) {
                  return 'Luas lahan belum diisi';
                }

                final area = double.tryParse(value);

                if (area == null || area <= 0) {
                  return 'Masukkan luas yang benar';
                }

                return null;
              },
            ),

            const SizedBox(height: 28),

            Container(
              padding: const EdgeInsets.all(17),
              decoration: BoxDecoration(
                color: const Color(0xFFFFF8DF),
                borderRadius: BorderRadius.circular(20),
              ),
              child: const Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Icon(
                    Icons.lightbulb_outline_rounded,
                    color: Color(0xFF946E00),
                    size: 28,
                  ),
                  SizedBox(width: 12),
                  Expanded(
                    child: Text(
                      'Tidak perlu bingung. Isi saja sesuai informasi sawah Anda.',
                      style: TextStyle(
                        color: Color(0xFF5B4808),
                        fontSize: 14,
                        height: 1.4,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
                ],
              ),
            ),

            const SizedBox(height: 28),

            SizedBox(
              width: double.infinity,
              height: 64,
              child: ElevatedButton.icon(
                onPressed: _saveLand,
                icon: const Icon(
                  Icons.save_rounded,
                  size: 27,
                ),
                label: const Text(
                  'Simpan Lahan',
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.w900,
                  ),
                ),
                style: ElevatedButton.styleFrom(
                  backgroundColor: addLandGreen,
                  foregroundColor: Colors.white,
                  elevation: 0,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(20),
                  ),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  InputDecoration _inputDecoration({
    required String hint,
    required IconData icon,
    String? suffixText,
  }) {
    return InputDecoration(
      hintText: hint,
      hintStyle: const TextStyle(
        color: Color(0xFF9AA49E),
        fontSize: 15,
      ),
      prefixIcon: Icon(
        icon,
        color: addLandGreen,
        size: 25,
      ),
      suffixText: suffixText,
      suffixStyle: const TextStyle(
        color: addLandGreen,
        fontSize: 15,
        fontWeight: FontWeight.w800,
      ),
      filled: true,
      fillColor: Colors.white,
      contentPadding: const EdgeInsets.symmetric(
        horizontal: 17,
        vertical: 18,
      ),
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(18),
        borderSide: BorderSide.none,
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(18),
        borderSide: const BorderSide(
          color: Color(0xFFE1E7E2),
        ),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(18),
        borderSide: const BorderSide(
          color: addLandGreen,
          width: 2,
        ),
      ),
      errorBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(18),
        borderSide: const BorderSide(
          color: Colors.redAccent,
        ),
      ),
      focusedErrorBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(18),
        borderSide: const BorderSide(
          color: Colors.redAccent,
          width: 2,
        ),
      ),
    );
  }
}

class _FieldLabel extends StatelessWidget {
  const _FieldLabel({
    required this.title,
    required this.description,
  });

  final String title;
  final String description;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          title,
          style: const TextStyle(
            color: addLandText,
            fontSize: 17,
            fontWeight: FontWeight.w900,
          ),
        ),
        const SizedBox(height: 3),
        Text(
          description,
          style: const TextStyle(
            color: Color(0xFF69766F),
            fontSize: 13,
          ),
        ),
      ],
    );
  }
}