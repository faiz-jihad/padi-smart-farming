import 'dart:io';

import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:image_picker/image_picker.dart';

import 'package:padi/core/network/api_client.dart';
import 'package:padi/core/storage/token_storage.dart';
import 'package:padi/features/auth/presentation/widgets/padi_theme.dart';
import 'package:padi/features/marketplace/data/services/marketplace_api_service.dart';

class CreateMarketListingScreen extends StatefulWidget {
  const CreateMarketListingScreen({
    super.key,
  });

  @override
  State<CreateMarketListingScreen> createState() =>
      _CreateMarketListingScreenState();
}

class _CreateMarketListingScreenState
    extends State<CreateMarketListingScreen> {
  late final ApiClient _apiClient;
  late final MarketplaceApiService _service;

  final _formKey = GlobalKey<FormState>();

  final _commodityController = TextEditingController();
  final _quantityController = TextEditingController();
  final _priceController = TextEditingController();
  final _descriptionController = TextEditingController();
  final _salesLinkController = TextEditingController();

  final ImagePicker _picker = ImagePicker();

  XFile? _selectedImage;

  List<Map<String, dynamic>> _farms = [];
  List<Map<String, dynamic>> _activeSeasons = [];

  int? _farmId;
  int? _cropSeasonId;

  String _unit = 'kg';

  bool _isLoading = false;
  bool _isLoadingFarms = true;

  String? _farmError;

  @override
  void initState() {
    super.initState();

    _apiClient = ApiClient(
      const SecureTokenStorage(),
    );

    _service = MarketplaceApiService(
      _apiClient,
    );

    _loadFarms();
  }

  @override
  void dispose() {
    _commodityController.dispose();
    _quantityController.dispose();
    _priceController.dispose();
    _descriptionController.dispose();
    _salesLinkController.dispose();
    super.dispose();
  }

  Future<void> _loadFarms() async {
    setState(() {
      _isLoadingFarms = true;
      _farmError = null;
    });

    try {
      final farmsResponse = await _apiClient.dio.get(
        '/farms',
      );

      final farmsResponseData = farmsResponse.data;

      final farms = _extractList(
        farmsResponseData,
        possibleKeys: const [
          'farms',
          'data',
        ],
      );

      final seasonsResponse = await _apiClient.dio.get(
        '/crop-seasons',
      );

      final seasonsResponseData = seasonsResponse.data;

      final seasons = _extractList(
        seasonsResponseData,
        possibleKeys: const [
          'crop_seasons',
          'data',
        ],
      );

      final activeSeasons = seasons.where((item) {
        return item['status']?.toString().toLowerCase() == 'active';
      }).toList();

      final validFarmIds = activeSeasons
          .map(
            (season) => _toInt(
              season['farm_id'],
            ),
          )
          .whereType<int>()
          .toSet();

      final availableFarms = farms.where((farm) {
        final id = _toInt(farm['id']);

        return id != null && validFarmIds.contains(id);
      }).toList();

      if (!mounted) {
        return;
      }

      setState(() {
        _farms = availableFarms;
        _activeSeasons = activeSeasons;
        _isLoadingFarms = false;
      });
    } catch (e) {
      if (!mounted) {
        return;
      }

      setState(() {
        _isLoadingFarms = false;
        _farmError = _cleanError(e);
      });
    }
  }

  List<Map<String, dynamic>> _extractList(
    dynamic responseData, {
    required List<String> possibleKeys,
  }) {
    if (responseData is! Map) {
      return [];
    }

    dynamic data = responseData['data'];

    if (data is List) {
      return data
          .whereType<Map>()
          .map(
            (item) => Map<String, dynamic>.from(item),
          )
          .toList();
    }

    if (data is Map) {
      for (final key in possibleKeys) {
        final nested = data[key];

        if (nested is List) {
          return nested
              .whereType<Map>()
              .map(
                (item) => Map<String, dynamic>.from(item),
              )
              .toList();
        }
      }
    }

    for (final key in possibleKeys) {
      final value = responseData[key];

      if (value is List) {
        return value
            .whereType<Map>()
            .map(
              (item) => Map<String, dynamic>.from(item),
            )
            .toList();
      }
    }

    return [];
  }

  int? _toInt(dynamic value) {
    if (value is num) {
      return value.toInt();
    }

    return int.tryParse(
      value?.toString() ?? '',
    );
  }

  String _farmName(Map<String, dynamic> farm) {
    return farm['name']?.toString() ??
        farm['farm_name']?.toString() ??
        farm['nama']?.toString() ??
        'Lahan';
  }

  int? _activeSeasonIdForFarm(int farmId) {
    for (final season in _activeSeasons) {
      final seasonFarmId = _toInt(
        season['farm_id'],
      );

      if (seasonFarmId == farmId) {
        return _toInt(
          season['id'],
        );
      }
    }

    return null;
  }

  Future<void> _pickImage() async {
    try {
      final image = await _picker.pickImage(
        source: ImageSource.gallery,
        imageQuality: 80,
        maxWidth: 1600,
        maxHeight: 1600,
      );

      if (image == null) {
        return;
      }

      if (!mounted) {
        return;
      }

      setState(() {
        _selectedImage = image;
      });
    } catch (e) {
      _showMessage(
        'Gagal memilih foto: ${_cleanError(e)}',
      );
    }
  }

  Future<void> _submit() async {
    FocusScope.of(context).unfocus();

    if (!_formKey.currentState!.validate()) {
      return;
    }

    if (_farmId == null) {
      _showMessage(
        'Pilih lahan terlebih dahulu.',
      );
      return;
    }

    final activeSeasonId = _activeSeasonIdForFarm(
      _farmId!,
    );

    if (activeSeasonId == null) {
      _showMessage(
        'Lahan belum memiliki musim tanam aktif.',
      );
      return;
    }

    if (_selectedImage == null) {
      _showMessage(
        'Pilih foto hasil panen terlebih dahulu.',
      );
      return;
    }

    final quantity = double.tryParse(
      _quantityController.text
          .trim()
          .replaceAll(',', '.'),
    );

    final price = double.tryParse(
      _priceController.text
          .trim()
          .replaceAll(',', '.'),
    );

    if (quantity == null || quantity <= 0) {
      _showMessage(
        'Jumlah hasil panen tidak valid.',
      );
      return;
    }

    if (price == null || price <= 0) {
      _showMessage(
        'Harga hasil panen tidak valid.',
      );
      return;
    }

    setState(() {
      _isLoading = true;
    });

    try {
      await _service.createListing(
        farmId: _farmId!,
        cropSeasonId: activeSeasonId,
        commodity: _commodityController.text.trim(),
        quantity: quantity,
        unit: _unit,
        pricePerUnit: price,
        description:
            _descriptionController.text.trim().isEmpty
                ? null
                : _descriptionController.text.trim(),
        image: _selectedImage!,
        salesLink:
            _salesLinkController.text.trim().isEmpty
                ? null
                : _salesLinkController.text.trim(),
      );

      if (!mounted) {
        return;
      }

      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text(
            'Hasil panen berhasil dipublikasikan.',
          ),
          backgroundColor: padiGreen,
        ),
      );

      context.pop(true);
    } catch (e) {
      if (!mounted) {
        return;
      }

      _showMessage(
        _cleanError(e),
      );
    } finally {
      if (mounted) {
        setState(() {
          _isLoading = false;
        });
      }
    }
  }

  String _cleanError(dynamic error) {
    if (error is DioException) {
      final responseData = error.response?.data;

      if (responseData is Map) {
        final message = responseData['message'];

        if (message != null &&
            message.toString().trim().isNotEmpty) {
          return message.toString();
        }

        final errors = responseData['errors'];

        if (errors is Map) {
          final messages = <String>[];

          for (final value in errors.values) {
            if (value is List) {
              messages.addAll(
                value.map(
                  (item) => item.toString(),
                ),
              );
            } else {
              messages.add(
                value.toString(),
              );
            }
          }

          if (messages.isNotEmpty) {
            return messages.join('\n');
          }
        }
      }

      if (error.message != null &&
          error.message!.isNotEmpty) {
        return error.message!;
      }

      return 'Terjadi kesalahan pada server.';
    }

    return error
        .toString()
        .replaceFirst(
          'Exception: ',
          '',
        );
  }

  void _showMessage(String message) {
    if (!mounted) {
      return;
    }

    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: padiField,
      appBar: AppBar(
        backgroundColor: padiGreen,
        foregroundColor: Colors.white,
        elevation: 0,
        leading: IconButton(
          onPressed: _isLoading
              ? null
              : () => context.pop(),
          icon: const Icon(
            Icons.arrow_back_rounded,
          ),
        ),
        title: const Text(
          'Jual Hasil Panen',
          style: TextStyle(
            fontWeight: FontWeight.w800,
          ),
        ),
      ),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.fromLTRB(
            20,
            20,
            20,
            32,
          ),
          children: [
            _buildIntro(),
            const SizedBox(height: 20),
            _buildFarmField(),
            const SizedBox(height: 16),
            _buildCommodityField(),
            const SizedBox(height: 16),
            _buildQuantityAndUnit(),
            const SizedBox(height: 16),
            _buildPriceField(),
            const SizedBox(height: 16),
            _buildDescriptionField(),
            const SizedBox(height: 16),
            _buildImageField(),
            const SizedBox(height: 16),
            _buildSalesLinkField(),
            const SizedBox(height: 28),
            _buildSubmitButton(),
          ],
        ),
      ),
    );
  }

  Widget _buildIntro() {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: padiGreen,
        borderRadius: BorderRadius.circular(24),
      ),
      child: const Row(
        children: [
          Icon(
            Icons.storefront_rounded,
            color: Colors.white,
            size: 36,
          ),
          SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment:
                  CrossAxisAlignment.start,
              children: [
                Text(
                  'Jual Hasil Panen',
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 19,
                    fontWeight: FontWeight.w900,
                  ),
                ),
                SizedBox(height: 5),
                Text(
                  'Tawarkan hasil panen kepada pembeli dan mitra.',
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 13,
                    height: 1.4,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildFarmField() {
    if (_isLoadingFarms) {
      return InputDecorator(
        decoration: const InputDecoration(
          labelText: 'Lahan',
          prefixIcon: Icon(
            Icons.landscape_rounded,
          ),
        ),
        child: const Row(
          children: [
            SizedBox(
              width: 20,
              height: 20,
              child: CircularProgressIndicator(
                strokeWidth: 2,
                color: padiGreen,
              ),
            ),
            SizedBox(width: 12),
            Text(
              'Memuat lahan...',
            ),
          ],
        ),
      );
    }

    if (_farmError != null) {
      return Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(18),
          border: Border.all(
            color: Colors.red.withValues(
              alpha: 0.25,
            ),
          ),
        ),
        child: Column(
          crossAxisAlignment:
              CrossAxisAlignment.start,
          children: [
            const Text(
              'Gagal memuat lahan',
              style: TextStyle(
                color: padiInk,
                fontWeight: FontWeight.w800,
              ),
            ),
            const SizedBox(height: 6),
            Text(
              _farmError!,
              style: const TextStyle(
                color: padiMuted,
              ),
            ),
            const SizedBox(height: 12),
            FilledButton.icon(
              onPressed:
                  _isLoading ? null : _loadFarms,
              icon: const Icon(
                Icons.refresh_rounded,
              ),
              label: const Text(
                'Coba Lagi',
              ),
            ),
          ],
        ),
      );
    }

    if (_farms.isEmpty) {
      return Container(
        padding: const EdgeInsets.all(18),
        decoration: BoxDecoration(
          color: const Color(0xFFFFF8E8),
          borderRadius: BorderRadius.circular(20),
          border: Border.all(
            color: const Color(0xFFE6D8A8),
          ),
        ),
        child: const Row(
          crossAxisAlignment:
              CrossAxisAlignment.start,
          children: [
            Icon(
              Icons.info_outline_rounded,
              color: Color(0xFF8A6D1D),
              size: 28,
            ),
            SizedBox(width: 12),
            Expanded(
              child: Text(
                'Belum ada lahan dengan musim tanam aktif. Aktifkan musim tanam terlebih dahulu sebelum menjual hasil panen.',
                style: TextStyle(
                  color: Color(0xFF5F501E),
                  height: 1.4,
                  fontWeight: FontWeight.w600,
                ),
              ),
            ),
          ],
        ),
      );
    }

    return DropdownButtonFormField<int>(
      value: _farmId,
      decoration: const InputDecoration(
        labelText: 'Lahan',
        hintText: 'Pilih lahan',
        prefixIcon: Icon(
          Icons.landscape_rounded,
        ),
      ),
      items: _farms.map((farm) {
        final farmId = _toInt(
          farm['id'],
        );

        return DropdownMenuItem<int>(
          value: farmId,
          child: Text(
            _farmName(farm),
          ),
        );
      }).toList(),
      onChanged: _isLoading
          ? null
          : (value) {
              if (value == null) {
                return;
              }

              final seasonId =
                  _activeSeasonIdForFarm(
                value,
              );

              setState(() {
                _farmId = value;
                _cropSeasonId = seasonId;
              });
            },
      validator: (value) {
        if (value == null) {
          return 'Pilih lahan terlebih dahulu';
        }

        return null;
      },
    );
  }

  Widget _buildCommodityField() {
    return TextFormField(
      controller: _commodityController,
      textInputAction: TextInputAction.next,
      decoration: const InputDecoration(
        labelText: 'Komoditas',
        hintText: 'Contoh: Gabah Padi',
        prefixIcon: Icon(
          Icons.grass_rounded,
        ),
      ),
      validator: (value) {
        if (value == null ||
            value.trim().isEmpty) {
          return 'Komoditas wajib diisi';
        }

        return null;
      },
    );
  }

  Widget _buildQuantityAndUnit() {
    return Row(
      children: [
        Expanded(
          flex: 3,
          child: TextFormField(
            controller: _quantityController,
            keyboardType:
                const TextInputType.numberWithOptions(
              decimal: true,
            ),
            textInputAction:
                TextInputAction.next,
            decoration: const InputDecoration(
              labelText: 'Jumlah',
              hintText: '5000',
              prefixIcon: Icon(
                Icons.scale_rounded,
              ),
            ),
            validator: (value) {
              if (value == null ||
                  value.trim().isEmpty) {
                return 'Jumlah wajib diisi';
              }

              final number =
                  double.tryParse(
                value
                    .trim()
                    .replaceAll(',', '.'),
              );

              if (number == null ||
                  number <= 0) {
                return 'Jumlah tidak valid';
              }

              return null;
            },
          ),
        ),
        const SizedBox(width: 12),
        Expanded(
          flex: 2,
          child:
              DropdownButtonFormField<String>(
            value: _unit,
            decoration:
                const InputDecoration(
              labelText: 'Satuan',
              prefixIcon: Icon(
                Icons.straighten_rounded,
              ),
            ),
            items: const [
              DropdownMenuItem(
                value: 'kg',
                child: Text('kg'),
              ),
              DropdownMenuItem(
                value: 'ton',
                child: Text('ton'),
              ),
            ],
            onChanged: _isLoading
                ? null
                : (value) {
                    if (value == null) {
                      return;
                    }

                    setState(() {
                      _unit = value;
                    });
                  },
          ),
        ),
      ],
    );
  }

  Widget _buildPriceField() {
    return TextFormField(
      controller: _priceController,
      keyboardType:
          const TextInputType.numberWithOptions(
        decimal: true,
      ),
      textInputAction:
          TextInputAction.next,
      decoration: InputDecoration(
        labelText: 'Harga Patokan',
        hintText: '7500',
        prefixIcon: const Icon(
          Icons.payments_rounded,
        ),
        suffixText: '/ $_unit',
      ),
      validator: (value) {
        if (value == null ||
            value.trim().isEmpty) {
          return 'Harga wajib diisi';
        }

        final number = double.tryParse(
          value
              .trim()
              .replaceAll(',', '.'),
        );

        if (number == null ||
            number <= 0) {
          return 'Harga tidak valid';
        }

        return null;
      },
    );
  }

  Widget _buildDescriptionField() {
    return TextFormField(
      controller: _descriptionController,
      maxLines: 4,
      textInputAction:
          TextInputAction.newline,
      decoration: const InputDecoration(
        labelText: 'Deskripsi',
        hintText:
            'Jelaskan kualitas atau kondisi hasil panen',
        prefixIcon: Padding(
          padding: EdgeInsets.only(
            bottom: 65,
          ),
          child: Icon(
            Icons.description_outlined,
          ),
        ),
      ),
    );
  }

  Widget _buildImageField() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: padiField,
        borderRadius:
            BorderRadius.circular(24),
        border: Border.all(
          color: padiGreen.withValues(
            alpha: 0.35,
          ),
        ),
      ),
      child: Column(
        crossAxisAlignment:
            CrossAxisAlignment.start,
        children: [
          const Text(
            'Foto Hasil Panen',
            style: TextStyle(
              color: padiMuted,
              fontSize: 15,
              fontWeight: FontWeight.w700,
            ),
          ),
          const SizedBox(height: 12),
          if (_selectedImage == null)
            Container(
              width: double.infinity,
              height: 180,
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius:
                    BorderRadius.circular(18),
              ),
              child: const Column(
                mainAxisAlignment:
                    MainAxisAlignment.center,
                children: [
                  Icon(
                    Icons
                        .add_photo_alternate_outlined,
                    size: 55,
                    color: padiMuted,
                  ),
                  SizedBox(height: 8),
                  Text(
                    'Belum ada foto',
                    style: TextStyle(
                      color: padiMuted,
                      fontWeight:
                          FontWeight.w600,
                    ),
                  ),
                ],
              ),
            )
          else
            ClipRRect(
              borderRadius:
                  BorderRadius.circular(18),
              child: Image.file(
                File(
                  _selectedImage!.path,
                ),
                width: double.infinity,
                height: 220,
                fit: BoxFit.cover,
              ),
            ),
          const SizedBox(height: 12),
          SizedBox(
            width: double.infinity,
            child: FilledButton.icon(
              onPressed:
                  _isLoading
                      ? null
                      : _pickImage,
              icon: const Icon(
                Icons.photo_library_outlined,
              ),
              label: Text(
                _selectedImage == null
                    ? 'Pilih Foto'
                    : 'Ganti Foto',
                style: const TextStyle(
                  fontWeight: FontWeight.w800,
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSalesLinkField() {
    return TextFormField(
      controller: _salesLinkController,
      keyboardType:
          TextInputType.url,
      decoration: const InputDecoration(
        labelText: 'Link Penjualan',
        hintText: 'Opsional',
        prefixIcon: Icon(
          Icons.link_rounded,
        ),
      ),
    );
  }

  Widget _buildSubmitButton() {
    final canSubmit =
        !_isLoading &&
        !_isLoadingFarms &&
        _farms.isNotEmpty;

    return SizedBox(
      height: 56,
      child: FilledButton(
        onPressed:
            canSubmit ? _submit : null,
        child: _isLoading
            ? const SizedBox(
                width: 24,
                height: 24,
                child:
                    CircularProgressIndicator(
                  strokeWidth: 2.5,
                  color: Colors.white,
                ),
              )
            : const Text(
                'Publikasikan Hasil Panen',
                style: TextStyle(
                  fontWeight: FontWeight.w900,
                ),
              ),
      ),
    );
  }
}