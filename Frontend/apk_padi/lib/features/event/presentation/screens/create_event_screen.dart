import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:padi/features/event/data/models/event_model.dart';
import 'package:padi/features/event/data/providers/event_providers.dart';
import 'package:padi/features/home/presentation/tokens/home_tokens.dart';

class CreateEventScreen extends ConsumerStatefulWidget {
  const CreateEventScreen({super.key});

  @override
  ConsumerState<CreateEventScreen> createState() => _CreateEventScreenState();
}

class _CreateEventScreenState extends ConsumerState<CreateEventScreen> {
  final _formKey = GlobalKey<FormState>();

  final _titleController = TextEditingController();
  final _timeController = TextEditingController(text: '08:30 - 12:00 WIB');
  final _locationController = TextEditingController();
  final _addressController = TextEditingController();
  final _organizerController = TextEditingController(text: 'Dinas Pertanian & P.A.D.I. AI');
  final _speakerController = TextEditingController();
  final _quotaController = TextEditingController(text: '50');
  final _contactController = TextEditingController();
  final _descriptionController = TextEditingController();

  String _category = 'workshop';
  DateTime _selectedDate = DateTime.now().add(const Duration(days: 5));
  String _selectedAssetImage = 'assets/images/onboarding_1.jpeg';
  bool _isLoading = false;

  final List<Map<String, String>> _categories = [
    {'value': 'workshop', 'label': 'Pelatihan & Workshop'},
    {'value': 'field_day', 'label': 'Sekolah Lapang'},
    {'value': 'bazaar', 'label': 'Bazar & Pasar Tani'},
    {'value': 'webinar', 'label': 'Webinar Tani'},
    {'value': 'irrigation', 'label': 'Jadwal Gilir Air'},
  ];

  final List<String> _bannerOptions = [
    'assets/images/onboarding_1.jpeg',
    'assets/images/onboarding_2.jpeg',
    'assets/images/onboarding_3.jpeg',
    'assets/images/splash_background.jpeg',
  ];

  @override
  void dispose() {
    _titleController.dispose();
    _timeController.dispose();
    _locationController.dispose();
    _addressController.dispose();
    _organizerController.dispose();
    _speakerController.dispose();
    _quotaController.dispose();
    _contactController.dispose();
    _descriptionController.dispose();
    super.dispose();
  }

  Future<void> _selectDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _selectedDate,
      firstDate: DateTime.now(),
      lastDate: DateTime.now().add(const Duration(days: 365)),
      builder: (context, child) {
        return Theme(
          data: Theme.of(context).copyWith(
            colorScheme: const ColorScheme.light(
              primary: HomeColors.primaryGreen,
              onPrimary: Colors.white,
              surface: Colors.white,
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

  Future<void> _saveEvent() async {
    if (!_formKey.currentState!.validate() || _isLoading) return;

    setState(() => _isLoading = true);

    final newEvent = EventModel(
      id: 0,
      title: _titleController.text.trim(),
      description: _descriptionController.text.trim(),
      category: _category,
      eventDate: _selectedDate,
      eventTime: _timeController.text.trim(),
      locationName: _locationController.text.trim(),
      locationAddress: _addressController.text.trim().isEmpty ? null : _addressController.text.trim(),
      organizer: _organizerController.text.trim(),
      speaker: _speakerController.text.trim().isEmpty ? null : _speakerController.text.trim(),
      quota: int.tryParse(_quotaController.text.trim()) ?? 50,
      registeredCount: 0,
      priceType: 'free',
      assetImage: _selectedAssetImage,
      contactPerson: _contactController.text.trim().isEmpty ? null : _contactController.text.trim(),
      status: 'upcoming',
    );

    final result = await ref.read(eventsProvider.notifier).submitEvent(newEvent);

    if (!mounted) return;
    setState(() => _isLoading = false);

    if (result.success) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Row(
            children: [
              const Icon(Icons.check_circle_rounded, color: Colors.white),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  result.message.isNotEmpty
                      ? result.message
                      : 'Pengajuan agenda berhasil dikirim dan menunggu persetujuan admin.',
                ),
              ),
            ],
          ),
          backgroundColor: HomeColors.deepGreen,
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        ),
      );

      if (context.canPop()) {
        context.pop(true);
      } else {
        context.go('/home');
      }
    } else {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Row(
            children: [
              const Icon(Icons.error_outline_rounded, color: Colors.white),
              const SizedBox(width: 8),
              Expanded(child: Text(result.message)),
            ],
          ),
          backgroundColor: Colors.red.shade700,
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: HomeColors.background,
      appBar: AppBar(
        backgroundColor: HomeColors.background,
        elevation: 0,
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
        title: const Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Buat Acara Tani Baru',
              style: TextStyle(
                color: HomeColors.textPrimary,
                fontSize: 18,
                fontWeight: FontWeight.w900,
              ),
            ),
            Text(
              'Publikasikan agenda ke petani & penyuluh',
              style: TextStyle(
                color: HomeColors.textSecondary,
                fontSize: 11,
                fontWeight: FontWeight.w600,
              ),
            ),
          ],
        ),
      ),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.fromLTRB(18, 8, 18, 32),
          child: Center(
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 680),
              child: Form(
                key: _formKey,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    // Banner Image Picker
                    _buildSectionLabel('Pilih Foto Banner Acara'),
                    SizedBox(
                      height: 100,
                      child: ListView.separated(
                        scrollDirection: Axis.horizontal,
                        itemCount: _bannerOptions.length,
                        separatorBuilder: (context, index) => const SizedBox(width: 10),
                        itemBuilder: (context, index) {
                          final asset = _bannerOptions[index];
                          final isSelected = _selectedAssetImage == asset;
                          return GestureDetector(
                            onTap: () => setState(() => _selectedAssetImage = asset),
                            child: Container(
                              width: 140,
                              decoration: BoxDecoration(
                                borderRadius: BorderRadius.circular(HomeRadius.md),
                                border: Border.all(
                                  color: isSelected ? HomeColors.primaryGreen : HomeColors.border,
                                  width: isSelected ? 3 : 1,
                                ),
                              ),
                              child: ClipRRect(
                                borderRadius: BorderRadius.circular(HomeRadius.md - 2),
                                child: Stack(
                                  fit: StackFit.expand,
                                  children: [
                                    Image.asset(asset, fit: BoxFit.cover),
                                    if (isSelected)
                                      Container(
                                        color: HomeColors.primaryGreen.withValues(alpha: 0.3),
                                        child: const Center(
                                          child: Icon(Icons.check_circle_rounded, color: Colors.white, size: 28),
                                        ),
                                      ),
                                  ],
                                ),
                              ),
                            ),
                          );
                        },
                      ),
                    ),

                    const SizedBox(height: HomeSpacing.lg),

                    // Title
                    _buildSectionLabel('Judul Acara *'),
                    _buildTextFormField(
                      controller: _titleController,
                      hint: 'Contoh: Workshop Pengendalian Hama & Pemupukan Presisi',
                      icon: Icons.title_rounded,
                      validator: (val) => val == null || val.trim().isEmpty ? 'Judul acara wajib diisi' : null,
                    ),

                    const SizedBox(height: HomeSpacing.md),

                    // Category Selector
                    _buildSectionLabel('Kategori Acara *'),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 14),
                      decoration: BoxDecoration(
                        color: HomeColors.surface,
                        borderRadius: BorderRadius.circular(HomeRadius.md),
                        border: Border.all(color: HomeColors.border),
                      ),
                      child: DropdownButtonHideUnderline(
                        child: DropdownButton<String>(
                          value: _category,
                          isExpanded: true,
                          icon: const Icon(Icons.keyboard_arrow_down_rounded, color: HomeColors.textSecondary),
                          items: _categories.map((c) {
                            return DropdownMenuItem(
                              value: c['value'],
                              child: Text(
                                c['label']!,
                                style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 13.5),
                              ),
                            );
                          }).toList(),
                          onChanged: (val) {
                            if (val != null) setState(() => _category = val);
                          },
                        ),
                      ),
                    ),

                    const SizedBox(height: HomeSpacing.md),

                    // Date & Time Row
                    Row(
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              _buildSectionLabel('Tanggal *'),
                              InkWell(
                                onTap: _selectDate,
                                borderRadius: BorderRadius.circular(HomeRadius.md),
                                child: Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
                                  decoration: BoxDecoration(
                                    color: HomeColors.surface,
                                    borderRadius: BorderRadius.circular(HomeRadius.md),
                                    border: Border.all(color: HomeColors.border),
                                  ),
                                  child: Row(
                                    children: [
                                      const Icon(Icons.calendar_month_rounded, color: HomeColors.primaryGreen, size: 18),
                                      const SizedBox(width: 8),
                                      Text(
                                        '${_selectedDate.day}/${_selectedDate.month}/${_selectedDate.year}',
                                        style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 13),
                                      ),
                                    ],
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              _buildSectionLabel('Waktu Pelaksanaan *'),
                              _buildTextFormField(
                                controller: _timeController,
                                hint: '08:30 - 12:00 WIB',
                                icon: Icons.access_time_rounded,
                                validator: (val) => val == null || val.trim().isEmpty ? 'Waktu wajib diisi' : null,
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),

                    const SizedBox(height: HomeSpacing.md),

                    // Location Name & Address
                    _buildSectionLabel('Nama Lokasi / Tempat *'),
                    _buildTextFormField(
                      controller: _locationController,
                      hint: 'Contoh: Balai Penyuluhan Pertanian (BPP) Karangampel',
                      icon: Icons.place_rounded,
                      validator: (val) => val == null || val.trim().isEmpty ? 'Nama lokasi wajib diisi' : null,
                    ),

                    const SizedBox(height: HomeSpacing.md),

                    _buildSectionLabel('Alamat Detail Lokasi (Opsional)'),
                    _buildTextFormField(
                      controller: _addressController,
                      hint: 'Jl. Raya Karangampel No. 45, Indramayu',
                      icon: Icons.map_outlined,
                    ),

                    const SizedBox(height: HomeSpacing.md),

                    // Organizer & Speaker Row
                    Row(
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              _buildSectionLabel('Penyelenggara *'),
                              _buildTextFormField(
                                controller: _organizerController,
                                hint: 'Dinas Pertanian / Gapoktan',
                                icon: Icons.corporate_fare_rounded,
                                validator: (val) => val == null || val.trim().isEmpty ? 'Wajib diisi' : null,
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              _buildSectionLabel('Narasumber / Pakar'),
                              _buildTextFormField(
                                controller: _speakerController,
                                hint: 'Dr. Ir. Hendro W.',
                                icon: Icons.person_rounded,
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),

                    const SizedBox(height: HomeSpacing.md),

                    // Quota & Contact Person
                    Row(
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              _buildSectionLabel('Kuota Peserta'),
                              _buildTextFormField(
                                controller: _quotaController,
                                hint: '50',
                                icon: Icons.group_rounded,
                                keyboardType: TextInputType.number,
                              ),
                            ],
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              _buildSectionLabel('Kontak WhatsApp'),
                              _buildTextFormField(
                                controller: _contactController,
                                hint: '0812-3456-7890',
                                icon: Icons.phone_rounded,
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),

                    const SizedBox(height: HomeSpacing.md),

                    // Description
                    _buildSectionLabel('Deskripsi & Agenda Acara *'),
                    TextFormField(
                      controller: _descriptionController,
                      maxLines: 4,
                      validator: (val) => val == null || val.trim().isEmpty ? 'Deskripsi acara wajib diisi' : null,
                      style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600),
                      decoration: InputDecoration(
                        hintText: 'Jelaskan tujuan, materi yang akan dipelajari, dan fasilitas bagi peserta...',
                        filled: true,
                        fillColor: HomeColors.surface,
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(HomeRadius.md),
                          borderSide: const BorderSide(color: HomeColors.border),
                        ),
                        enabledBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(HomeRadius.md),
                          borderSide: const BorderSide(color: HomeColors.border),
                        ),
                        focusedBorder: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(HomeRadius.md),
                          borderSide: const BorderSide(color: HomeColors.primaryGreen, width: 1.5),
                        ),
                      ),
                    ),

                    const SizedBox(height: HomeSpacing.xl),

                    // Submit Button
                    FilledButton(
                      onPressed: _isLoading ? null : _saveEvent,
                      style: FilledButton.styleFrom(
                        backgroundColor: HomeColors.primaryGreen,
                        foregroundColor: Colors.white,
                        disabledBackgroundColor: HomeColors.primaryGreen.withValues(alpha: 0.6),
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(HomeRadius.md),
                        ),
                      ),
                      child: _isLoading
                          ? const SizedBox(
                              height: 20,
                              width: 20,
                              child: CircularProgressIndicator(
                                strokeWidth: 2,
                                color: Colors.white,
                              ),
                            )
                          : const Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Icon(Icons.send_rounded, size: 20),
                                SizedBox(width: 8),
                                Text(
                                  'Kirim Pengajuan Agenda',
                                  style: TextStyle(fontSize: 15, fontWeight: FontWeight.w900),
                                ),
                              ],
                            ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildSectionLabel(String label) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 6, left: 2),
      child: Text(
        label,
        style: const TextStyle(
          color: HomeColors.textPrimary,
          fontSize: 12,
          fontWeight: FontWeight.w800,
        ),
      ),
    );
  }

  Widget _buildTextFormField({
    required TextEditingController controller,
    required String hint,
    required IconData icon,
    String? Function(String?)? validator,
    TextInputType keyboardType = TextInputType.text,
  }) {
    return TextFormField(
      controller: controller,
      validator: validator,
      keyboardType: keyboardType,
      style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600),
      decoration: InputDecoration(
        hintText: hint,
        prefixIcon: Icon(icon, color: HomeColors.primaryGreen, size: 18),
        filled: true,
        fillColor: HomeColors.surface,
        contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(HomeRadius.md),
          borderSide: const BorderSide(color: HomeColors.border),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(HomeRadius.md),
          borderSide: const BorderSide(color: HomeColors.border),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(HomeRadius.md),
          borderSide: const BorderSide(color: HomeColors.primaryGreen, width: 1.5),
        ),
      ),
    );
  }
}
