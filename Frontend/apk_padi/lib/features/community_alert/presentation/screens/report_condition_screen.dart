import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:padi/features/auth/presentation/widgets/padi_theme.dart';

class ReportConditionScreen extends StatefulWidget {
  const ReportConditionScreen({super.key});

  @override
  State<ReportConditionScreen> createState() =>
      _ReportConditionScreenState();
}

class _ReportConditionScreenState
    extends State<ReportConditionScreen> {
  final TextEditingController _descriptionController =
      TextEditingController();

  String _selectedType = 'Penyakit';
  String _selectedLocation = 'Sawah Blok A';

  @override
  void dispose() {
    _descriptionController.dispose();
    super.dispose();
  }

  void _submitReport() {
    if (_descriptionController.text.trim().isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Jelaskan kondisi yang ingin dilaporkan.'),
          behavior: SnackBarBehavior.floating,
        ),
      );
      return;
    }

    ScaffoldMessenger.of(context).showSnackBar(
      const SnackBar(
        content: Text('Laporan berhasil dibuat.'),
        behavior: SnackBarBehavior.floating,
      ),
    );

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
          'Lapor Kondisi',
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
            'Ada kondisi yang perlu dilaporkan?',
            style: TextStyle(
              color: padiInk,
              fontSize: 23,
              fontWeight: FontWeight.w900,
            ),
          ),
          const SizedBox(height: 7),
          const Text(
            'Bantu petani lain dengan melaporkan kondisi yang Anda temukan di sawah.',
            style: TextStyle(
              color: padiMuted,
              fontSize: 13,
              height: 1.4,
            ),
          ),
          const SizedBox(height: 22),
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(24),
              border: Border.all(
                color: Colors.black.withValues(alpha: 0.05),
              ),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Jenis Kondisi',
                  style: TextStyle(
                    color: padiInk,
                    fontSize: 14,
                    fontWeight: FontWeight.w800,
                  ),
                ),
                const SizedBox(height: 8),
                DropdownButtonFormField<String>(
                  value: _selectedType,
                  decoration: const InputDecoration(
                    prefixIcon: Icon(
                      Icons.warning_amber_rounded,
                      color: padiGreen,
                    ),
                  ),
                  items: const [
                    DropdownMenuItem(
                      value: 'Penyakit',
                      child: Text('Penyakit Tanaman'),
                    ),
                    DropdownMenuItem(
                      value: 'Hama',
                      child: Text('Serangan Hama'),
                    ),
                    DropdownMenuItem(
                      value: 'Cuaca',
                      child: Text('Kondisi Cuaca'),
                    ),
                    DropdownMenuItem(
                      value: 'Lainnya',
                      child: Text('Lainnya'),
                    ),
                  ],
                  onChanged: (value) {
                    if (value == null) return;

                    setState(() {
                      _selectedType = value;
                    });
                  },
                ),
                const SizedBox(height: 18),
                const Text(
                  'Lokasi',
                  style: TextStyle(
                    color: padiInk,
                    fontSize: 14,
                    fontWeight: FontWeight.w800,
                  ),
                ),
                const SizedBox(height: 8),
                DropdownButtonFormField<String>(
                  value: _selectedLocation,
                  decoration: const InputDecoration(
                    prefixIcon: Icon(
                      Icons.location_on_rounded,
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
                  onChanged: (value) {
                    if (value == null) return;

                    setState(() {
                      _selectedLocation = value;
                    });
                  },
                ),
                const SizedBox(height: 18),
                const Text(
                  'Deskripsi',
                  style: TextStyle(
                    color: padiInk,
                    fontSize: 14,
                    fontWeight: FontWeight.w800,
                  ),
                ),
                const SizedBox(height: 8),
                TextField(
                  controller: _descriptionController,
                  maxLines: 5,
                  decoration: const InputDecoration(
                    hintText:
                        'Contoh: Banyak daun padi mulai menguning...',
                    alignLabelWithHint: true,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 22),
          SizedBox(
            width: double.infinity,
            height: 54,
            child: FilledButton.icon(
              onPressed: _submitReport,
              icon: const Icon(Icons.send_rounded),
              label: const Text(
                'Kirim Laporan',
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