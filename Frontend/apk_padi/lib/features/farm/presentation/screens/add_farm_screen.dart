import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:padi/features/farm/presentation/screens/farm_list_screen.dart';
import 'package:padi/features/map/presentation/screens/planting_calendar_map_page.dart';
import 'package:padi/features/region/data/models/region_models.dart';

class AddFarmScreen extends ConsumerStatefulWidget {
  const AddFarmScreen({super.key});

  @override
  ConsumerState<AddFarmScreen> createState() => _AddFarmScreenState();
}

class _AddFarmScreenState extends ConsumerState<AddFarmScreen> {
  final _formKey = GlobalKey<FormState>();

  final _nameController = TextEditingController();
  final _areaController = TextEditingController();
  final _latController = TextEditingController();
  final _lngController = TextEditingController();
  final _notesController = TextEditingController();

  String _irrigationType = 'irrigated';
  String? _soilType;

  bool _isLocating = false;
  bool _isSubmitting = false;
  ResolvedLocationModel? _resolvedLocation;

  @override
  void dispose() {
    _nameController.dispose();
    _areaController.dispose();
    _latController.dispose();
    _lngController.dispose();
    _notesController.dispose();
    super.dispose();
  }

  Future<void> _detectGps() async {
    setState(() => _isLocating = true);
    final locService = ref.read(locationServiceProvider);
    final position = await locService.getCurrentPosition();

    if (position != null) {
      _latController.text = position.latitude.toStringAsFixed(6);
      _lngController.text = position.longitude.toStringAsFixed(6);

      // Auto resolve coordinates
      final regionApi = ref.read(regionApiServiceProvider);
      final resolved = await regionApi.resolveCoordinates(position.latitude, position.longitude);

      setState(() {
        _resolvedLocation = resolved;
        _isLocating = false;
      });
    } else {
      setState(() => _isLocating = false);
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Gagal mendeteksi lokasi GPS. Pastikan izin lokasi aktif.')),
        );
      }
    }
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isSubmitting = true);

    try {
      final farmApi = ref.read(farmApiServiceProvider);
      await farmApi.createFarm(
        name: _nameController.text.trim(),
        areaHa: double.parse(_areaController.text),
        latitude: double.parse(_latController.text),
        longitude: double.parse(_lngController.text),
        irrigationType: _irrigationType,
        irrigationNotes: _notesController.text.trim().isEmpty ? null : _notesController.text.trim(),
        soilType: _soilType,
        provinceId: _resolvedLocation?.province?.id,
        regencyId: _resolvedLocation?.regency?.id,
        districtId: _resolvedLocation?.district?.id,
        villageId: _resolvedLocation?.village?.id,
      );

      ref.invalidate(userFarmsProvider);

      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('Lahan berhasil didaftarkan!')),
        );
        context.pop();
      }
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Gagal mendaftarkan lahan: $e')),
        );
      }
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Daftarkan Lahan Baru'),
        backgroundColor: const Color(0xFF16A34A),
        foregroundColor: Colors.white,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              TextFormField(
                controller: _nameController,
                decoration: const InputDecoration(
                  labelText: 'Nama Lahan / Blok Sawah',
                  hintText: 'Misal: Sawah Blok Ceplik',
                  border: OutlineInputBorder(),
                  prefixIcon: Icon(Icons.badge_outlined),
                ),
                validator: (val) => val == null || val.isEmpty ? 'Nama lahan wajib diisi' : null,
              ),
              const SizedBox(height: 16),
              TextFormField(
                controller: _areaController,
                keyboardType: const TextInputType.numberWithOptions(decimal: true),
                decoration: const InputDecoration(
                  labelText: 'Luas Lahan (Hektar)',
                  hintText: 'Misal: 1.5',
                  border: OutlineInputBorder(),
                  prefixIcon: Icon(Icons.straighten_outlined),
                  suffixText: 'Ha',
                ),
                validator: (val) {
                  if (val == null || val.isEmpty) return 'Luas lahan wajib diisi';
                  if (double.tryParse(val) == null || double.parse(val) <= 0) {
                    return 'Masukkan angka luas lahan yang valid';
                  }
                  return null;
                },
              ),
              const SizedBox(height: 20),

              // GPS Geolocation Box
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.green.shade50,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: Colors.green.shade200),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text(
                          'Koordinat Geografis',
                          style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                        ),
                        ElevatedButton.icon(
                          onPressed: _isLocating ? null : _detectGps,
                          icon: _isLocating
                              ? const SizedBox(
                                  width: 14,
                                  height: 14,
                                  child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                                )
                              : const Icon(Icons.my_location, size: 16),
                          label: Text(_isLocating ? 'Mendeteksi...' : 'Ambil GPS'),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: const Color(0xFF16A34A),
                            foregroundColor: Colors.white,
                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                            textStyle: const TextStyle(fontSize: 12),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Expanded(
                          child: TextFormField(
                            controller: _latController,
                            keyboardType: const TextInputType.numberWithOptions(decimal: true, signed: true),
                            decoration: const InputDecoration(
                              labelText: 'Latitude',
                              border: OutlineInputBorder(),
                              isDense: true,
                            ),
                            validator: (val) => val == null || val.isEmpty ? 'Wajib' : null,
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: TextFormField(
                            controller: _lngController,
                            keyboardType: const TextInputType.numberWithOptions(decimal: true, signed: true),
                            decoration: const InputDecoration(
                              labelText: 'Longitude',
                              border: OutlineInputBorder(),
                              isDense: true,
                            ),
                            validator: (val) => val == null || val.isEmpty ? 'Wajib' : null,
                          ),
                        ),
                      ],
                    ),
                    if (_resolvedLocation != null) ...[
                      const SizedBox(height: 12),
                      Container(
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(color: Colors.green.shade300),
                        ),
                        child: Row(
                          children: [
                            const Icon(Icons.verified_outlined, color: Color(0xFF16A34A), size: 20),
                            const SizedBox(width: 8),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  const Text(
                                    'Wilayah Terdeteksi:',
                                    style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF16A34A)),
                                  ),
                                  Text(
                                    _resolvedLocation!.formattedAddress,
                                    style: const TextStyle(fontSize: 12, color: Color(0xFF0F172A)),
                                  ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ],
                ),
              ),

              const SizedBox(height: 20),
              DropdownButtonFormField<String>(
                initialValue: _irrigationType,
                decoration: const InputDecoration(
                  labelText: 'Sistem Irigasi',
                  border: OutlineInputBorder(),
                  prefixIcon: Icon(Icons.water_drop_outlined),
                ),
                items: const [
                  DropdownMenuItem(value: 'irrigated', child: Text('Irigasi Teknis')),
                  DropdownMenuItem(value: 'semi_irrigated', child: Text('Irigasi Setengah Teknis')),
                  DropdownMenuItem(value: 'rainfed', child: Text('Tadah Hujan')),
                  DropdownMenuItem(value: 'tidal', child: Text('Pasang Surut')),
                ],
                onChanged: (val) => setState(() => _irrigationType = val!),
              ),
              const SizedBox(height: 16),
              DropdownButtonFormField<String>(
                initialValue: _soilType,
                decoration: const InputDecoration(
                  labelText: 'Tipe Tanah (Opsional)',
                  border: OutlineInputBorder(),
                  prefixIcon: Icon(Icons.terrain_outlined),
                ),
                hint: const Text('Pilih tipe tanah'),
                items: const [
                  DropdownMenuItem(value: 'Alluvial', child: Text('Aluvial (Endapan)')),
                  DropdownMenuItem(value: 'Latosol', child: Text('Latosol')),
                  DropdownMenuItem(value: 'Grumusol', child: Text('Grumusol (Lempung Hitam)')),
                  DropdownMenuItem(value: 'Regosol', child: Text('Regosol (Pasir/Vulkanik)')),
                ],
                onChanged: (val) => setState(() => _soilType = val),
              ),
              const SizedBox(height: 16),
              TextFormField(
                controller: _notesController,
                maxLines: 2,
                decoration: const InputDecoration(
                  labelText: 'Catatan Irigasi & Lahan (Opsional)',
                  hintText: 'Misal: Sumber air dari saluran sekunder blok timur',
                  border: OutlineInputBorder(),
                ),
              ),
              const SizedBox(height: 28),
              SizedBox(
                width: double.infinity,
                height: 50,
                child: ElevatedButton(
                  onPressed: _isSubmitting ? null : _submit,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFF16A34A),
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  child: _isSubmitting
                      ? const SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                        )
                      : const Text('Daftarkan Lahan', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
