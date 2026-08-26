import 'dart:typed_data';

import 'package:dio/dio.dart';
import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:image_picker/image_picker.dart';

import 'package:padi/core/network/api_client.dart';
import 'package:padi/core/storage/token_storage.dart';
import 'package:padi/features/home/presentation/tokens/home_tokens.dart';
import 'package:padi/features/marketplace/data/services/marketplace_api_service.dart';

class CreateMarketListingScreen extends StatefulWidget {
  const CreateMarketListingScreen({super.key});

  @override
  State<CreateMarketListingScreen> createState() =>
      _CreateMarketListingScreenState();
}

class _CreateMarketListingScreenState extends State<CreateMarketListingScreen> {
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
  Uint8List? _selectedImageBytes;

  List<Map<String, dynamic>> _farms = [];
  List<Map<String, dynamic>> _allFarms = [];
  List<Map<String, dynamic>> _activeSeasons = [];

  int? _farmId;
  String _unit = 'kg';

  bool _isLoading = false;
  bool _isLoadingFarms = true;
  String? _farmError;
  bool _showLivePreview = true;

  // Preset Komoditas Populer
  static const List<_CommodityPreset> _commodityPresets = [
    _CommodityPreset(
      name: 'Gabah Kering Panen (GKP)',
      shortName: 'GKP',
      icon: Icons.grass_rounded,
      defaultPrice: '7200',
      descriptionSuggestion:
          'Gabah Kering Panen segar langsung dari sawah, kadar air standar panen, butir berisi.',
    ),
    _CommodityPreset(
      name: 'Gabah Kering Giling (GKG)',
      shortName: 'GKG',
      icon: Icons.grain_rounded,
      defaultPrice: '8500',
      descriptionSuggestion:
          'Gabah Kering Giling siap giling, kadar air < 14%, hampa/kotoran minimal.',
    ),
    _CommodityPreset(
      name: 'Beras Premium',
      shortName: 'Beras Premium',
      icon: Icons.rice_bowl_rounded,
      defaultPrice: '14800',
      descriptionSuggestion:
          'Beras kualitas premium, derajat sosoh 100%, butir utuh dan putih mengkilap.',
    ),
    _CommodityPreset(
      name: 'Beras Medium',
      shortName: 'Beras Medium',
      icon: Icons.soup_kitchen_rounded,
      defaultPrice: '12800',
      descriptionSuggestion:
          'Beras medium bersih, derajat sosoh 95%, pulen dan bebas pengawet.',
    ),
    _CommodityPreset(
      name: 'Benih Padi Bersertifikat',
      shortName: 'Benih Padi',
      icon: Icons.spa_rounded,
      defaultPrice: '18000',
      descriptionSuggestion:
          'Benih padi bersertifikat resmi, daya berkecambah tinggi > 85%, siap semai.',
    ),
    _CommodityPreset(
      name: 'Beras Organik / Khusus',
      shortName: 'Beras Organik',
      icon: Icons.eco_rounded,
      defaultPrice: '22000',
      descriptionSuggestion:
          'Beras budidaya organik bebas pestisida kimia, varietas wangi dan kaya nutrisi.',
    ),
  ];

  // Quick Tags untuk deskripsi mutu
  static const List<String> _qualityTags = [
    'Kadar Air < 14%',
    'Kadar Hampa < 3%',
    'Derajat Sosoh 100%',
    'Bebas Kutu & Jamur',
    'Siap Giling',
    'Bisa Nego',
    'Pengiriman Tersedia',
    'Ambil di Gudang/Sawah',
  ];

  @override
  void initState() {
    super.initState();
    _apiClient = ApiClient(const SecureTokenStorage());
    _service = MarketplaceApiService(_apiClient);

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

  double get _currentQuantity {
    final text = _quantityController.text.trim().replaceAll(',', '.');
    return double.tryParse(text) ?? 0;
  }

  double get _currentPrice {
    final text = _priceController.text.trim().replaceAll(',', '.');
    return double.tryParse(text) ?? 0;
  }

  double get _estimatedTotalValue {
    final qty = _currentQuantity;
    final price = _currentPrice;
    if (qty <= 0 || price <= 0) return 0;

    if (_unit.toLowerCase() == 'ton') {
      return (qty * 1000) * price;
    }
    return qty * price;
  }

  String _formatRupiah(double amount) {
    if (amount <= 0) return 'Rp 0';
    final str = amount.toStringAsFixed(0);
    final formatted = str.replaceAllMapped(
      RegExp(r'\B(?=(\d{3})+(?!\d))'),
      (match) => '.',
    );
    return 'Rp $formatted';
  }

  String _formatNumber(double val) {
    if (val == val.roundToDouble()) {
      return val.toInt().toString();
    }
    return val.toStringAsFixed(1);
  }

  Future<void> _loadFarms() async {
    setState(() {
      _isLoadingFarms = true;
      _farmError = null;
    });

    try {
      final farmsResponse = await _apiClient.dio.get('/farms');
      final farms = _extractList(
        farmsResponse.data,
        possibleKeys: const ['farms', 'data'],
      );

      final seasonsResponse = await _apiClient.dio.get('/crop-seasons');
      final seasons = _extractList(
        seasonsResponse.data,
        possibleKeys: const ['crop_seasons', 'data'],
      );

      final activeSeasons = seasons.where((item) {
        return item['status']?.toString().toLowerCase() == 'active';
      }).toList();

      final validFarmIds = activeSeasons
          .map((season) => _toInt(season['farm_id']))
          .whereType<int>()
          .toSet();

      final availableFarms = farms.where((farm) {
        final id = _toInt(farm['id']);
        return id != null && validFarmIds.contains(id);
      }).toList();

      if (!mounted) return;

      setState(() {
        _allFarms = farms;
        _farms = availableFarms;
        _activeSeasons = activeSeasons;
        _isLoadingFarms = false;

        // Auto-select first farm if available and none selected
        if (_farmId == null && availableFarms.isNotEmpty) {
          _farmId = _toInt(availableFarms.first['id']);
        }
      });
    } catch (e) {
      if (!mounted) return;
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
    if (responseData is! Map) return [];

    dynamic data = responseData['data'];
    if (data is List) {
      return data
          .whereType<Map>()
          .map((item) => Map<String, dynamic>.from(item))
          .toList();
    }

    if (data is Map) {
      for (final key in possibleKeys) {
        final nested = data[key];
        if (nested is List) {
          return nested
              .whereType<Map>()
              .map((item) => Map<String, dynamic>.from(item))
              .toList();
        }
      }
    }

    for (final key in possibleKeys) {
      final value = responseData[key];
      if (value is List) {
        return value
            .whereType<Map>()
            .map((item) => Map<String, dynamic>.from(item))
            .toList();
      }
    }

    return [];
  }

  int? _toInt(dynamic value) {
    if (value is num) return value.toInt();
    return int.tryParse(value?.toString() ?? '');
  }

  String _farmName(Map<String, dynamic> farm) {
    return farm['name']?.toString() ??
        farm['farm_name']?.toString() ??
        farm['nama']?.toString() ??
        'Lahan Pertanian';
  }

  String _farmLocation(Map<String, dynamic> farm) {
    final location = farm['location']?.toString() ??
        farm['address']?.toString() ??
        farm['desa']?.toString();
    if (location != null && location.isNotEmpty) {
      return location;
    }
    final area = farm['area_hectares'] ?? farm['luas_lahan'];
    if (area != null) {
      return 'Luas: $area Ha';
    }
    return 'Lahan Terverifikasi';
  }

  int? _activeSeasonIdForFarm(int farmId) {
    for (final season in _activeSeasons) {
      final seasonFarmId = _toInt(season['farm_id']);
      if (seasonFarmId == farmId) {
        return _toInt(season['id']);
      }
    }
    return null;
  }

  String _marketplaceCreateReturnRoute() {
    return Uri(path: '/marketplace/create').toString();
  }

  void _openStartSeasonFlow() {
    final farmId = _allFarms.length == 1 ? _toInt(_allFarms.first['id']) : null;
    final returnTo = Uri.encodeComponent(_marketplaceCreateReturnRoute());
    final farmQuery = farmId == null ? '' : 'farmId=$farmId&';
    context.push('/land/season/start?${farmQuery}returnTo=$returnTo');
  }

  Future<void> _openAddFarmFlow() async {
    await context.push('/farms/add');
    if (!mounted) return;
    await _loadFarms();
  }

  Future<void> _pickImageSource(ImageSource source) async {
    try {
      final image = await _picker.pickImage(
        source: source,
        imageQuality: 82,
        maxWidth: 1600,
        maxHeight: 1600,
      );

      if (image == null) return;

      final imageBytes = await image.readAsBytes();

      if (!mounted) return;

      setState(() {
        _selectedImage = image;
        _selectedImageBytes = imageBytes;
      });
    } catch (e) {
      _showMessage('Gagal memilih foto: ${_cleanError(e)}');
    }
  }

  void _showImagePickerSheet() {
    showModalBottomSheet(
      context: context,
      backgroundColor: HomeColors.surface,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(HomeRadius.xl)),
      ),
      builder: (ctx) {
        return SafeArea(
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Center(
                  child: Container(
                    width: 40,
                    height: 4,
                    decoration: BoxDecoration(
                      color: HomeColors.border,
                      borderRadius: BorderRadius.circular(HomeRadius.pill),
                    ),
                  ),
                ),
                const SizedBox(height: 16),
                const Text(
                  'Unggah Foto Panen',
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.w800,
                    color: HomeColors.textPrimary,
                  ),
                ),
                const SizedBox(height: 4),
                const Text(
                  'Pilih sumber foto bukti kualitas hasil panen',
                  style: HomeTypography.supporting,
                ),
                const SizedBox(height: 20),
                ListTile(
                  leading: Container(
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: HomeColors.lightGreen,
                      borderRadius: BorderRadius.circular(HomeRadius.md),
                    ),
                    child: const Icon(
                      Icons.camera_alt_rounded,
                      color: HomeColors.primaryGreen,
                    ),
                  ),
                  title: const Text(
                    'Ambil Foto Kamera',
                    style: TextStyle(fontWeight: FontWeight.w700),
                  ),
                  subtitle: const Text('Foto langsung dari sawah atau gudang'),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(HomeRadius.md),
                  ),
                  onTap: () {
                    Navigator.pop(ctx);
                    _pickImageSource(ImageSource.camera);
                  },
                ),
                const SizedBox(height: 8),
                ListTile(
                  leading: Container(
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: HomeColors.surfaceMuted,
                      borderRadius: BorderRadius.circular(HomeRadius.md),
                    ),
                    child: const Icon(
                      Icons.photo_library_rounded,
                      color: HomeColors.textPrimary,
                    ),
                  ),
                  title: const Text(
                    'Pilih dari Galeri',
                    style: TextStyle(fontWeight: FontWeight.w700),
                  ),
                  subtitle: const Text('Pilih foto yang tersimpan di perangkat'),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(HomeRadius.md),
                  ),
                  onTap: () {
                    Navigator.pop(ctx);
                    _pickImageSource(ImageSource.gallery);
                  },
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  void _applyCommodityPreset(_CommodityPreset preset) {
    setState(() {
      _commodityController.text = preset.name;
      if (_priceController.text.trim().isEmpty) {
        _priceController.text = preset.defaultPrice;
      }
      if (_descriptionController.text.trim().isEmpty) {
        _descriptionController.text = preset.descriptionSuggestion;
      }
    });
  }

  void _appendQualityTag(String tag) {
    final currentText = _descriptionController.text.trim();
    if (currentText.contains(tag)) return;

    setState(() {
      if (currentText.isEmpty) {
        _descriptionController.text = '• $tag';
      } else {
        _descriptionController.text = '$currentText\n• $tag';
      }
    });
  }

  Future<void> _submit() async {
    FocusScope.of(context).unfocus();

    final formState = _formKey.currentState;
    if (formState == null || !formState.validate()) {
      _showMessage('Mohon lengkapi seluruh isian wajib.');
      return;
    }

    final farmId = _farmId;
    if (farmId == null) {
      _showMessage('Pilih lahan terlebih dahulu.');
      return;
    }

    final activeSeasonId = _activeSeasonIdForFarm(farmId);
    if (activeSeasonId == null) {
      _showMessage('Lahan belum memiliki musim tanam aktif.');
      return;
    }

    final selectedImage = _selectedImage;
    if (selectedImage == null) {
      _showMessage('Pilih foto hasil panen terlebih dahulu.');
      return;
    }

    final quantity = _currentQuantity;
    final price = _currentPrice;

    if (quantity <= 0) {
      _showMessage('Jumlah hasil panen tidak valid.');
      return;
    }

    if (price <= 0) {
      _showMessage('Harga patokan tidak valid.');
      return;
    }

    setState(() {
      _isLoading = true;
    });

    try {
      await _service.createListing(
        farmId: farmId,
        cropSeasonId: activeSeasonId,
        commodity: _commodityController.text.trim(),
        quantity: quantity,
        unit: _unit,
        pricePerUnit: price,
        description: _descriptionController.text.trim().isEmpty
            ? null
            : _descriptionController.text.trim(),
        image: selectedImage,
        salesLink: _salesLinkController.text.trim().isEmpty
            ? null
            : _salesLinkController.text.trim(),
      );

      if (!mounted) return;

      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: const Row(
            children: [
              Icon(Icons.check_circle_rounded, color: Colors.white),
              SizedBox(width: 10),
              Expanded(
                child: Text(
                  'Iklan panen berhasil dipublikasikan ke Marketplace!',
                  style: TextStyle(fontWeight: FontWeight.w700),
                ),
              ),
            ],
          ),
          backgroundColor: HomeColors.primaryGreen,
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(HomeRadius.md),
          ),
        ),
      );

      context.pop(true);
    } catch (e) {
      if (!mounted) return;
      _showMessage(_cleanError(e));
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
        if (message != null && message.toString().trim().isNotEmpty) {
          return message.toString();
        }

        final errors = responseData['errors'];
        if (errors is Map) {
          final messages = <String>[];
          for (final value in errors.values) {
            if (value is List) {
              messages.addAll(value.map((item) => item.toString()));
            } else {
              messages.add(value.toString());
            }
          }
          if (messages.isNotEmpty) {
            return messages.join('\n');
          }
        }
      }

      final errorMessage = error.message;
      if (errorMessage != null && errorMessage.isNotEmpty) {
        return errorMessage;
      }

      return 'Terjadi kesalahan pada server.';
    }

    return error.toString().replaceFirst('Exception: ', '');
  }

  void _showMessage(String message) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(HomeRadius.md),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: HomeColors.background,
      appBar: AppBar(
        backgroundColor: HomeColors.background,
        foregroundColor: HomeColors.textPrimary,
        elevation: 0,
        scrolledUnderElevation: 0,
        leading: IconButton(
          tooltip: 'Kembali',
          onPressed: _isLoading
              ? null
              : () {
                  if (context.canPop()) {
                    context.pop();
                  } else {
                    context.go('/marketplace');
                  }
                },
          icon: Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: HomeColors.surface,
              shape: BoxShape.circle,
              border: Border.all(color: HomeColors.border),
            ),
            child: const Icon(
              Icons.arrow_back_rounded,
              color: HomeColors.textPrimary,
              size: 18,
            ),
          ),
        ),
        title: const Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Pasang Iklan Panen',
              style: TextStyle(
                color: HomeColors.textPrimary,
                fontSize: 18,
                fontWeight: FontWeight.w900,
                letterSpacing: -0.3,
              ),
            ),
            Text(
              'Publikasikan stok gabah & beras ke pembeli',
              style: TextStyle(
                color: HomeColors.textSecondary,
                fontSize: 11,
                fontWeight: FontWeight.w600,
              ),
            ),
          ],
        ),
        actions: [
          Padding(
            padding: const EdgeInsets.only(right: 14),
            child: Center(
              child: Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 10,
                  vertical: 5,
                ),
                decoration: BoxDecoration(
                  color: HomeColors.lightGreen,
                  borderRadius: BorderRadius.circular(HomeRadius.pill),
                  border: Border.all(
                    color: HomeColors.primaryGreen.withOpacity(0.2),
                  ),
                ),
                child: const Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Icon(
                      Icons.storefront_rounded,
                      color: HomeColors.primaryGreen,
                      size: 14,
                    ),
                    SizedBox(width: 4),
                    Text(
                      'Direct B2B',
                      style: TextStyle(
                        color: HomeColors.primaryGreen,
                        fontSize: 11,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
      body: Form(
        key: _formKey,
        child: SafeArea(
          top: false,
          bottom: false,
          child: SingleChildScrollView(
            physics: const AlwaysScrollableScrollPhysics(
              parent: BouncingScrollPhysics(),
            ),
            padding: const EdgeInsets.fromLTRB(16, 8, 16, 120),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                _buildHeaderHero(),
                const SizedBox(height: 16),
                _buildFarmSection(),
                const SizedBox(height: 16),
                _buildCommoditySection(),
                const SizedBox(height: 16),
                _buildPricingCalculatorSection(),
                const SizedBox(height: 16),
                _buildPhotoUploadSection(),
                const SizedBox(height: 16),
                _buildDescriptionSection(),
                const SizedBox(height: 16),
                _buildContactSection(),
                const SizedBox(height: 16),
                _buildLivePreviewToggleSection(),
              ],
            ),
          ),
        ),
      ),
      bottomNavigationBar: _buildStickyBottomBar(),
    );
  }

  Widget _buildHeaderHero() {
    return Container(
      clipBehavior: Clip.antiAlias,
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(HomeRadius.xl),
        gradient: const LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [
            Color(0xFF075E3B),
            Color(0xFF0E7C53),
            Color(0xFF0B6A45),
          ],
        ),
      ),
      child: Stack(
        children: [
          Positioned(
            right: -20,
            bottom: -24,
            child: Icon(
              Icons.agriculture_rounded,
              color: Colors.white.withOpacity(0.12),
              size: 150,
            ),
          ),
          Positioned(
            top: -30,
            right: 40,
            child: Container(
              width: 120,
              height: 120,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: const Color(0xFFD97706).withOpacity(0.18),
              ),
            ),
          ),
          Padding(
            padding: const EdgeInsets.all(HomeSpacing.cardPadding),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 10,
                        vertical: 5,
                      ),
                      decoration: BoxDecoration(
                        color: Colors.white.withOpacity(0.18),
                        borderRadius: BorderRadius.circular(HomeRadius.pill),
                        border: Border.all(
                          color: Colors.white.withOpacity(0.25),
                        ),
                      ),
                      child: const Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(
                            Icons.verified_rounded,
                            color: Colors.white,
                            size: 13,
                          ),
                          SizedBox(width: 5),
                          Text(
                            'BURSA PANEN P.A.D.I.',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 10,
                              fontWeight: FontWeight.w900,
                              letterSpacing: 0.5,
                            ),
                          ),
                        ],
                      ),
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 8,
                        vertical: 4,
                      ),
                      decoration: BoxDecoration(
                        color: const Color(0xFFD97706).withOpacity(0.9),
                        borderRadius: BorderRadius.circular(HomeRadius.sm),
                      ),
                      child: const Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(Icons.bolt_rounded, color: Colors.white, size: 13),
                          SizedBox(width: 3),
                          Text(
                            'Bebas Biaya Iklan',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 10.5,
                              fontWeight: FontWeight.w800,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 14),
                const Text(
                  'Tawarkan Panen Langsung ke Pembeli',
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 21,
                    fontWeight: FontWeight.w900,
                    letterSpacing: -0.4,
                    height: 1.2,
                  ),
                ),
                const SizedBox(height: 6),
                Text(
                  'Pasang iklan stok gabah atau beras dari lahan Anda. '
                  'Dapatkan penawaran harga terbaik dari tengkulak, penggilingan, dan mitra resmi.',
                  style: TextStyle(
                    color: Colors.white.withOpacity(0.92),
                    fontSize: 12.5,
                    fontWeight: FontWeight.w500,
                    height: 1.4,
                  ),
                ),
                const SizedBox(height: 14),
                Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: [
                    _buildHeroPill(Icons.handshake_outlined, 'Nego Langsung'),
                    _buildHeroPill(
                      Icons.local_shipping_outlined,
                      'Jangkauan Luas',
                    ),
                    _buildHeroPill(
                      Icons.security_outlined,
                      'Data Terverifikasi',
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildHeroPill(IconData icon, String label) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 5),
      decoration: BoxDecoration(
        color: Colors.white.withOpacity(0.14),
        borderRadius: BorderRadius.circular(HomeRadius.pill),
        border: Border.all(color: Colors.white.withOpacity(0.16)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, color: Colors.white, size: 13),
          const SizedBox(width: 5),
          Text(
            label,
            style: const TextStyle(
              color: Colors.white,
              fontSize: 11,
              fontWeight: FontWeight.w700,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildCardContainer({
    required IconData icon,
    required String title,
    required String subtitle,
    required Widget child,
  }) {
    return Container(
      padding: const EdgeInsets.all(HomeSpacing.cardPadding),
      decoration: BoxDecoration(
        color: HomeColors.surface,
        borderRadius: BorderRadius.circular(HomeRadius.xl),
        border: Border.all(color: HomeColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                padding: const EdgeInsets.all(9),
                decoration: BoxDecoration(
                  color: HomeColors.lightGreen,
                  borderRadius: BorderRadius.circular(HomeRadius.md),
                ),
                child: Icon(icon, color: HomeColors.primaryGreen, size: 20),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      title,
                      style: const TextStyle(
                        color: HomeColors.textPrimary,
                        fontSize: 16,
                        fontWeight: FontWeight.w800,
                        letterSpacing: -0.2,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(subtitle, style: HomeTypography.supporting),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          child,
        ],
      ),
    );
  }

  // 1. SECTION LAHAN & MUSIM TANAM
  Widget _buildFarmSection() {
    return _buildCardContainer(
      icon: Icons.landscape_rounded,
      title: '1. Sumber Lahan & Musim',
      subtitle: 'Pilih lahan yang memiliki musim tanam aktif',
      child: _buildFarmContent(),
    );
  }

  Widget _buildFarmContent() {
    if (_isLoadingFarms) {
      return Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: HomeColors.surfaceMuted,
          borderRadius: BorderRadius.circular(HomeRadius.md),
          border: Border.all(color: HomeColors.borderSubtle),
        ),
        child: const Row(
          children: [
            SizedBox(
              width: 20,
              height: 20,
              child: CircularProgressIndicator(
                strokeWidth: 2.2,
                color: HomeColors.primaryGreen,
              ),
            ),
            SizedBox(width: 12),
            Expanded(
              child: Text(
                'Memeriksa daftar lahan dan musim tanam aktif...',
                style: HomeTypography.supporting,
              ),
            ),
          ],
        ),
      );
    }

    if (_farmError != null) {
      return Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: HomeColors.dangerBg,
          borderRadius: BorderRadius.circular(HomeRadius.md),
          border: Border.all(color: HomeColors.danger.withOpacity(0.25)),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Row(
              children: [
                Icon(
                  Icons.error_outline_rounded,
                  color: HomeColors.danger,
                  size: 20,
                ),
                SizedBox(width: 8),
                Text(
                  'Gagal memuat data lahan',
                  style: TextStyle(
                    color: HomeColors.danger,
                    fontWeight: FontWeight.w800,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 6),
            Text(
              _farmError ?? 'Terjadi kesalahan.',
              style: const TextStyle(
                color: HomeColors.textSecondary,
                fontSize: 12.5,
              ),
            ),
            const SizedBox(height: 12),
            FilledButton.icon(
              onPressed: _isLoading ? null : _loadFarms,
              icon: const Icon(Icons.refresh_rounded, size: 16),
              label: const Text('Coba Lagi'),
              style: FilledButton.styleFrom(
                backgroundColor: HomeColors.danger,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(
                  horizontal: 14,
                  vertical: 8,
                ),
              ),
            ),
          ],
        ),
      );
    }

    if (_farms.isEmpty) {
      final hasAnyFarm = _allFarms.isNotEmpty;
      return Container(
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: HomeColors.warningBg,
          borderRadius: BorderRadius.circular(HomeRadius.lg),
          border: Border.all(
            color: HomeColors.warning.withOpacity(0.35),
          ),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  padding: const EdgeInsets.all(6),
                  decoration: BoxDecoration(
                    color: HomeColors.warning.withOpacity(0.15),
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(
                    Icons.warning_amber_rounded,
                    color: HomeColors.warning,
                    size: 24,
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        hasAnyFarm
                            ? 'Belum Ada Musim Tanam Aktif'
                            : 'Belum Ada Data Lahan',
                        style: const TextStyle(
                          color: HomeColors.textPrimary,
                          fontWeight: FontWeight.w800,
                          fontSize: 14,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        hasAnyFarm
                            ? 'Iklan panen memerlukan musim tanam aktif agar kualitas dan riwayat panen terverifikasi.'
                            : 'Tambahkan data lahan sawah Anda terlebih dahulu sebelum membuat penawaran panen.',
                        style: const TextStyle(
                          color: HomeColors.textSecondary,
                          fontSize: 12.5,
                          height: 1.35,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
            const SizedBox(height: 14),
            SizedBox(
              width: double.infinity,
              child: FilledButton.icon(
                onPressed: _isLoading
                    ? null
                    : hasAnyFarm
                    ? _openStartSeasonFlow
                    : _openAddFarmFlow,
                icon: Icon(
                  hasAnyFarm
                      ? Icons.play_circle_outline_rounded
                      : Icons.add_location_alt_rounded,
                  size: 18,
                ),
                label: Text(
                  hasAnyFarm
                      ? 'Mulai / Aktifkan Musim Tanam'
                      : 'Tambah Lahan Baru',
                  style: const TextStyle(fontWeight: FontWeight.w800),
                ),
                style: FilledButton.styleFrom(
                  backgroundColor: HomeColors.primaryGreen,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 12),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(HomeRadius.md),
                  ),
                ),
              ),
            ),
          ],
        ),
      );
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        for (int i = 0; i < _farms.length; i++) ...[
          if (i > 0) const SizedBox(height: 10),
          _buildFarmItemCard(_farms[i]),
        ],
      ],
    );
  }

  Widget _buildFarmItemCard(Map<String, dynamic> farm) {
    final farmId = _toInt(farm['id']);
    final isSelected = _farmId == farmId;

    return InkWell(
      onTap: _isLoading
          ? null
          : () {
              setState(() {
                _farmId = farmId;
              });
            },
      borderRadius: BorderRadius.circular(HomeRadius.lg),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: isSelected
              ? HomeColors.lightGreen.withOpacity(0.7)
              : HomeColors.surfaceMuted,
          borderRadius: BorderRadius.circular(HomeRadius.lg),
          border: Border.all(
            color: isSelected
                ? HomeColors.primaryGreen
                : HomeColors.borderSubtle,
            width: isSelected ? 1.8 : 1,
          ),
        ),
        child: Row(
          children: [
            Container(
              width: 38,
              height: 38,
              decoration: BoxDecoration(
                color: isSelected
                    ? HomeColors.primaryGreen
                    : Colors.white,
                shape: BoxShape.circle,
                border: Border.all(
                  color: isSelected
                      ? HomeColors.primaryGreen
                      : HomeColors.border,
                ),
              ),
              child: Icon(
                isSelected
                    ? Icons.check_rounded
                    : Icons.landscape_outlined,
                color: isSelected
                    ? Colors.white
                    : HomeColors.textSecondary,
                size: 20,
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    _farmName(farm),
                    style: TextStyle(
                      color: HomeColors.textPrimary,
                      fontWeight: isSelected
                          ? FontWeight.w800
                          : FontWeight.w700,
                      fontSize: 14.5,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Row(
                    children: [
                      const Icon(
                        Icons.pin_drop_outlined,
                        size: 12,
                        color: HomeColors.textTertiary,
                      ),
                      const SizedBox(width: 3),
                      Expanded(
                        child: Text(
                          _farmLocation(farm),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: HomeTypography.caption,
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(width: 8),
            Container(
              padding: const EdgeInsets.symmetric(
                horizontal: 8,
                vertical: 4,
              ),
              decoration: BoxDecoration(
                color: HomeColors.emerald.withOpacity(0.12),
                borderRadius: BorderRadius.circular(HomeRadius.pill),
              ),
              child: const Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(
                    Icons.eco_rounded,
                    size: 11,
                    color: HomeColors.emerald,
                  ),
                  SizedBox(width: 3),
                  Text(
                    'Musim Aktif',
                    style: TextStyle(
                      color: HomeColors.emerald,
                      fontSize: 10,
                      fontWeight: FontWeight.w800,
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

  // 2. SECTION INFORMASI KOMODITAS
  Widget _buildCommoditySection() {
    return _buildCardContainer(
      icon: Icons.grass_rounded,
      title: '2. Jenis Komoditas',
      subtitle: 'Pilih jenis hasil panen atau ketik varietas kustom',
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Pilihan Cepat Komoditas:',
            style: TextStyle(
              fontSize: 12.5,
              fontWeight: FontWeight.w700,
              color: HomeColors.textSecondary,
            ),
          ),
          const SizedBox(height: 8),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: _commodityPresets.map((preset) {
              final isSelected =
                  _commodityController.text.trim() == preset.name;
              return InkWell(
                onTap: () => _applyCommodityPreset(preset),
                borderRadius: BorderRadius.circular(HomeRadius.pill),
                child: AnimatedContainer(
                  duration: const Duration(milliseconds: 150),
                  padding: const EdgeInsets.symmetric(
                    horizontal: 12,
                    vertical: 8,
                  ),
                  decoration: BoxDecoration(
                    color: isSelected
                        ? HomeColors.primaryGreen
                        : HomeColors.surfaceMuted,
                    borderRadius: BorderRadius.circular(HomeRadius.pill),
                    border: Border.all(
                      color: isSelected
                          ? HomeColors.primaryGreen
                          : HomeColors.border,
                      width: isSelected ? 1.5 : 1,
                    ),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(
                        preset.icon,
                        size: 14,
                        color: isSelected
                            ? Colors.white
                            : HomeColors.primaryGreen,
                      ),
                      const SizedBox(width: 6),
                      Text(
                        preset.shortName,
                        style: TextStyle(
                          color: isSelected
                              ? Colors.white
                              : HomeColors.textPrimary,
                          fontSize: 12,
                          fontWeight:
                              isSelected ? FontWeight.w800 : FontWeight.w600,
                        ),
                      ),
                    ],
                  ),
                ),
              );
            }).toList(),
          ),
          const SizedBox(height: 16),
          TextFormField(
            controller: _commodityController,
            textInputAction: TextInputAction.next,
            onChanged: (_) => setState(() {}),
            decoration: InputDecoration(
              labelText: 'Nama Komoditas & Varietas *',
              hintText: 'Contoh: Gabah Kering Panen - Ciherang Super',
              prefixIcon: const Icon(Icons.inventory_2_outlined),
              suffixIcon: _commodityController.text.isNotEmpty
                  ? IconButton(
                      icon: const Icon(Icons.clear_rounded, size: 18),
                      onPressed: () {
                        setState(() {
                          _commodityController.clear();
                        });
                      },
                    )
                  : null,
            ),
            validator: (value) {
              if (value == null || value.trim().isEmpty) {
                return 'Nama komoditas wajib diisi';
              }
              return null;
            },
          ),
        ],
      ),
    );
  }

  // 3. SECTION HARGA & ESTIMASI TRANSAKSI
  Widget _buildPricingCalculatorSection() {
    return _buildCardContainer(
      icon: Icons.calculate_outlined,
      title: '3. Kuantitas & Harga',
      subtitle: 'Atur jumlah stok panen dan harga patokan per satuan',
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Row Kuantitas + Satuan
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(
                flex: 3,
                child: TextFormField(
                  controller: _quantityController,
                  keyboardType: const TextInputType.numberWithOptions(
                    decimal: true,
                  ),
                  textInputAction: TextInputAction.next,
                  onChanged: (_) => setState(() {}),
                  decoration: const InputDecoration(
                    labelText: 'Jumlah Stok *',
                    hintText: 'Misal: 5000',
                    prefixIcon: Icon(Icons.scale_rounded),
                  ),
                  validator: (value) {
                    if (value == null || value.trim().isEmpty) {
                      return 'Kuantitas wajib diisi';
                    }
                    final number = double.tryParse(
                      value.trim().replaceAll(',', '.'),
                    );
                    if (number == null || number <= 0) {
                      return 'Jumlah tidak valid';
                    }
                    return null;
                  },
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                flex: 2,
                child: Container(
                  height: 56,
                  padding: const EdgeInsets.all(4),
                  decoration: BoxDecoration(
                    color: HomeColors.surfaceMuted,
                    borderRadius: BorderRadius.circular(HomeRadius.md),
                    border: Border.all(color: HomeColors.border),
                  ),
                  child: Row(
                    children: [
                      Expanded(
                        child: InkWell(
                          onTap: () {
                            setState(() {
                              _unit = 'kg';
                            });
                          },
                          borderRadius: BorderRadius.circular(HomeRadius.sm),
                          child: Container(
                            alignment: Alignment.center,
                            decoration: BoxDecoration(
                              color: _unit == 'kg'
                                  ? HomeColors.primaryGreen
                                  : Colors.transparent,
                              borderRadius: BorderRadius.circular(
                                HomeRadius.sm,
                              ),
                            ),
                            child: Text(
                              'kg',
                              style: TextStyle(
                                color: _unit == 'kg'
                                    ? Colors.white
                                    : HomeColors.textSecondary,
                                fontWeight: FontWeight.w800,
                                fontSize: 13,
                              ),
                            ),
                          ),
                        ),
                      ),
                      Expanded(
                        child: InkWell(
                          onTap: () {
                            setState(() {
                              _unit = 'ton';
                            });
                          },
                          borderRadius: BorderRadius.circular(HomeRadius.sm),
                          child: Container(
                            alignment: Alignment.center,
                            decoration: BoxDecoration(
                              color: _unit == 'ton'
                                  ? HomeColors.primaryGreen
                                  : Colors.transparent,
                              borderRadius: BorderRadius.circular(
                                HomeRadius.sm,
                              ),
                            ),
                            child: Text(
                              'Ton',
                              style: TextStyle(
                                color: _unit == 'ton'
                                    ? Colors.white
                                    : HomeColors.textSecondary,
                                fontWeight: FontWeight.w800,
                                fontSize: 13,
                              ),
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 14),

          // Harga Satuan Field
          TextFormField(
            controller: _priceController,
            keyboardType: const TextInputType.numberWithOptions(decimal: true),
            textInputAction: TextInputAction.next,
            onChanged: (_) => setState(() {}),
            decoration: InputDecoration(
              labelText: 'Harga Patokan per Satuan *',
              hintText: 'Misal: 7500',
              prefixIcon: const Icon(Icons.payments_rounded),
              prefixText: 'Rp ',
              prefixStyle: const TextStyle(
                fontWeight: FontWeight.w800,
                color: HomeColors.textPrimary,
              ),
              suffixText: '/ $_unit',
              suffixStyle: const TextStyle(
                fontWeight: FontWeight.w700,
                color: HomeColors.primaryGreen,
              ),
            ),
            validator: (value) {
              if (value == null || value.trim().isEmpty) {
                return 'Harga wajib diisi';
              }
              final number = double.tryParse(value.trim().replaceAll(',', '.'));
              if (number == null || number <= 0) {
                return 'Harga tidak valid';
              }
              return null;
            },
          ),
          const SizedBox(height: 10),

          // Quick Price Helpers
          Wrap(
            spacing: 6,
            runSpacing: 6,
            crossAxisAlignment: WrapCrossAlignment.center,
            children: [
              const Text(
                'Rekomendasi: ',
                style: TextStyle(
                  fontSize: 11.5,
                  fontWeight: FontWeight.w600,
                  color: HomeColors.textTertiary,
                ),
              ),
              _buildPriceChip('6.800'),
              _buildPriceChip('7.500'),
              _buildPriceChip('8.500'),
              _buildPriceChip('13.500'),
              _buildPriceChip('15.000'),
            ],
          ),
          const SizedBox(height: 16),

          // Live Total Calculation Card
          _buildLiveCalculationCard(),
        ],
      ),
    );
  }

  Widget _buildPriceChip(String formattedVal) {
    final rawVal = formattedVal.replaceAll('.', '');
    return ActionChip(
      label: Text('Rp $formattedVal'),
      labelStyle: const TextStyle(
        fontSize: 11,
        fontWeight: FontWeight.w700,
        color: HomeColors.primaryGreen,
      ),
      backgroundColor: HomeColors.lightGreen,
      side: BorderSide(color: HomeColors.primaryGreen.withOpacity(0.2)),
      padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 0),
      onPressed: () {
        setState(() {
          _priceController.text = rawVal;
        });
      },
    );
  }

  Widget _buildLiveCalculationCard() {
    final total = _estimatedTotalValue;
    final qty = _currentQuantity;
    final price = _currentPrice;

    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: HomeColors.harvestGoldBg.withOpacity(0.7),
        borderRadius: BorderRadius.circular(HomeRadius.lg),
        border: Border.all(
          color: HomeColors.harvestGold.withOpacity(0.35),
        ),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: HomeColors.harvestGold.withOpacity(0.15),
              shape: BoxShape.circle,
            ),
            child: const Icon(
              Icons.monetization_on_rounded,
              color: HomeColors.harvestGold,
              size: 24,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Estimasi Nilai Total Transaksi',
                  style: TextStyle(
                    color: HomeColors.textSecondary,
                    fontSize: 11.5,
                    fontWeight: FontWeight.w600,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  total > 0 ? _formatRupiah(total) : 'Rp 0',
                  style: const TextStyle(
                    color: Color(0xFF92400E),
                    fontSize: 19,
                    fontWeight: FontWeight.w900,
                    letterSpacing: -0.3,
                  ),
                ),
                if (qty > 0 && price > 0)
                  Text(
                    '${_formatNumber(qty)} $_unit × ${_formatRupiah(price)}/$_unit',
                    style: const TextStyle(
                      color: HomeColors.textSecondary,
                      fontSize: 11,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  // 4. SECTION FOTO PANEN
  Widget _buildPhotoUploadSection() {
    final selectedImage = _selectedImage;
    final selectedImageBytes = _selectedImageBytes;

    return _buildCardContainer(
      icon: Icons.photo_camera_rounded,
      title: '4. Foto Hasil Panen',
      subtitle: 'Unggah bukti kualitas bulir, karung, atau gabah',
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            clipBehavior: Clip.antiAlias,
            decoration: BoxDecoration(
              color: HomeColors.surfaceMuted,
              borderRadius: BorderRadius.circular(HomeRadius.lg),
              border: Border.all(
                color: selectedImage != null
                    ? HomeColors.primaryGreen
                    : HomeColors.border,
                width: selectedImage != null ? 1.5 : 1,
              ),
            ),
            child: selectedImage == null
                ? InkWell(
                    onTap: _isLoading ? null : _showImagePickerSheet,
                    child: SizedBox(
                      width: double.infinity,
                      height: 190,
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Container(
                            width: 58,
                            height: 58,
                            decoration: const BoxDecoration(
                              color: HomeColors.lightGreen,
                              shape: BoxShape.circle,
                            ),
                            child: const Icon(
                              Icons.add_a_photo_rounded,
                              size: 28,
                              color: HomeColors.primaryGreen,
                            ),
                          ),
                          const SizedBox(height: 12),
                          const Text(
                            'Ketuk untuk Unggah Foto Panen',
                            style: TextStyle(
                              color: HomeColors.textPrimary,
                              fontSize: 14,
                              fontWeight: FontWeight.w800,
                            ),
                          ),
                          const SizedBox(height: 4),
                          const Text(
                            'Kamera atau Galeri (JPG, PNG, WebP maks. 5MB)',
                            style: HomeTypography.caption,
                          ),
                        ],
                      ),
                    ),
                  )
                : Stack(
                    children: [
                      if (selectedImageBytes != null)
                        Image.memory(
                          selectedImageBytes,
                          width: double.infinity,
                          height: 230,
                          fit: BoxFit.cover,
                        )
                      else
                        const SizedBox(
                          width: double.infinity,
                          height: 230,
                          child: Center(
                            child: CircularProgressIndicator(
                              strokeWidth: 2,
                              color: HomeColors.primaryGreen,
                            ),
                          ),
                        ),
                      Positioned(
                        top: 10,
                        right: 10,
                        child: Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 10,
                            vertical: 5,
                          ),
                          decoration: BoxDecoration(
                            color: Colors.black.withOpacity(0.65),
                            borderRadius: BorderRadius.circular(HomeRadius.pill),
                          ),
                          child: const Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(
                                Icons.check_circle_rounded,
                                color: Color(0xFF34D399),
                                size: 14,
                              ),
                              SizedBox(width: 4),
                              Text(
                                'Foto Siap',
                                style: TextStyle(
                                  color: Colors.white,
                                  fontSize: 11,
                                  fontWeight: FontWeight.w700,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                      Positioned(
                        left: 12,
                        right: 12,
                        bottom: 12,
                        child: Row(
                          children: [
                            Expanded(
                              child: FilledButton.icon(
                                onPressed: _isLoading
                                    ? null
                                    : _showImagePickerSheet,
                                icon: const Icon(Icons.refresh_rounded, size: 16),
                                label: const Text(
                                  'Ganti Foto',
                                  style: TextStyle(fontWeight: FontWeight.w800),
                                ),
                                style: FilledButton.styleFrom(
                                  backgroundColor: Colors.white,
                                  foregroundColor: HomeColors.textPrimary,
                                  padding: const EdgeInsets.symmetric(
                                    vertical: 10,
                                  ),
                                  shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(
                                      HomeRadius.md,
                                    ),
                                  ),
                                ),
                              ),
                            ),
                            const SizedBox(width: 8),
                            IconButton(
                              tooltip: 'Hapus Foto',
                              onPressed: _isLoading
                                  ? null
                                  : () {
                                      setState(() {
                                        _selectedImage = null;
                                        _selectedImageBytes = null;
                                      });
                                    },
                              style: IconButton.styleFrom(
                                backgroundColor: Colors.white,
                                foregroundColor: HomeColors.danger,
                              ),
                              icon: const Icon(Icons.delete_outline_rounded),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
          ),
          const SizedBox(height: 10),
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: HomeColors.lightGreen.withOpacity(0.6),
              borderRadius: BorderRadius.circular(HomeRadius.md),
            ),
            child: const Row(
              children: [
                Icon(
                  Icons.lightbulb_outline_rounded,
                  size: 18,
                  color: HomeColors.primaryGreen,
                ),
                SizedBox(width: 8),
                Expanded(
                  child: Text(
                    'Tips: Foto butir gabah/beras dengan pencahayaan terang meningkatkan respon pembeli hingga 3x lipat.',
                    style: TextStyle(
                      fontSize: 11.5,
                      color: HomeColors.primaryGreen,
                      fontWeight: FontWeight.w600,
                      height: 1.3,
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  // 5. SECTION DESKRIPSI & MUTU
  Widget _buildDescriptionSection() {
    return _buildCardContainer(
      icon: Icons.description_outlined,
      title: '5. Deskripsi & Mutu Panen',
      subtitle: 'Jelaskan kondisi panen, kadar air, atau catatan penting',
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Tambahkan Tag Mutu Cepat:',
            style: TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.w700,
              color: HomeColors.textSecondary,
            ),
          ),
          const SizedBox(height: 8),
          Wrap(
            spacing: 6,
            runSpacing: 6,
            children: _qualityTags.map((tag) {
              return ActionChip(
                label: Text('+ $tag'),
                labelStyle: const TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.w700,
                  color: HomeColors.textPrimary,
                ),
                backgroundColor: HomeColors.surfaceMuted,
                side: BorderSide(color: HomeColors.borderSubtle),
                padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 0),
                onPressed: () => _appendQualityTag(tag),
              );
            }).toList(),
          ),
          const SizedBox(height: 14),
          TextFormField(
            controller: _descriptionController,
            maxLines: 4,
            textInputAction: TextInputAction.newline,
            onChanged: (_) => setState(() {}),
            decoration: const InputDecoration(
              labelText: 'Catatan Deskripsi (Opsional)',
              hintText:
                  'Tuliskan detail varietas, kadar air, kemasan, atau ketentuan transaksi...',
              alignLabelWithHint: true,
            ),
          ),
        ],
      ),
    );
  }

  // 6. SECTION KONTAK & LINK PENJUALAN
  Widget _buildContactSection() {
    return _buildCardContainer(
      icon: Icons.link_rounded,
      title: '6. Tautan / Kontak Eksternal',
      subtitle: 'Opsional: Tautan toko Shopee, Tokopedia, atau WhatsApp',
      child: TextFormField(
        controller: _salesLinkController,
        keyboardType: TextInputType.url,
        textInputAction: TextInputAction.done,
        onChanged: (_) => setState(() {}),
        decoration: InputDecoration(
          labelText: 'Link Penjualan / Toko Online',
          hintText: 'https://wa.me/... atau https://shopee.co.id/...',
          prefixIcon: const Icon(Icons.link_rounded),
          suffixIcon: _salesLinkController.text.isNotEmpty
              ? const Icon(
                  Icons.check_circle_outline_rounded,
                  color: HomeColors.emerald,
                )
              : null,
        ),
      ),
    );
  }

  // 7. SECTION LIVE FEED PREVIEW
  Widget _buildLivePreviewToggleSection() {
    return Container(
      decoration: BoxDecoration(
        color: HomeColors.surface,
        borderRadius: BorderRadius.circular(HomeRadius.xl),
        border: Border.all(color: HomeColors.border),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          ListTile(
            contentPadding: const EdgeInsets.symmetric(
              horizontal: 16,
              vertical: 4,
            ),
            leading: Container(
              padding: const EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: HomeColors.skyBlueBg,
                borderRadius: BorderRadius.circular(HomeRadius.md),
              ),
              child: const Icon(
                Icons.visibility_rounded,
                color: HomeColors.skyBlue,
                size: 20,
              ),
            ),
            title: const Text(
              'Pratinjau Tampilan Iklan',
              style: TextStyle(
                fontWeight: FontWeight.w800,
                fontSize: 15,
                color: HomeColors.textPrimary,
              ),
            ),
            subtitle: const Text(
              'Bagaimana iklan terlihat oleh pembeli di feed',
              style: HomeTypography.caption,
            ),
            trailing: Switch(
              value: _showLivePreview,
              activeThumbColor: HomeColors.primaryGreen,
              onChanged: (val) {
                setState(() {
                  _showLivePreview = val;
                });
              },
            ),
          ),
          if (_showLivePreview) ...[
            const Divider(height: 1),
            Padding(
              padding: const EdgeInsets.all(16),
              child: _buildLiveListingPreviewCard(),
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildLiveListingPreviewCard() {
    final commodity = _commodityController.text.trim().isNotEmpty
        ? _commodityController.text.trim()
        : 'Nama Komoditas Panen';
    final qty = _currentQuantity > 0 ? _formatNumber(_currentQuantity) : '0';
    final price = _currentPrice > 0 ? _formatRupiah(_currentPrice) : 'Rp 0';
    final desc = _descriptionController.text.trim().isNotEmpty
        ? _descriptionController.text.trim()
        : 'Deskripsi kualitas panen akan muncul di sini...';

    String farmTitle = 'Lahan Terpilih';
    if (_farmId != null && _farms.isNotEmpty) {
      for (final f in _farms) {
        if (_toInt(f['id']) == _farmId) {
          farmTitle = _farmName(f);
          break;
        }
      }
    }

    return Container(
      decoration: BoxDecoration(
        color: HomeColors.background,
        borderRadius: BorderRadius.circular(HomeRadius.lg),
        border: Border.all(color: HomeColors.border),
      ),
      padding: const EdgeInsets.all(14),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Image preview thumb
              Container(
                width: 76,
                height: 76,
                clipBehavior: Clip.antiAlias,
                decoration: BoxDecoration(
                  color: HomeColors.surfaceMuted,
                  borderRadius: BorderRadius.circular(HomeRadius.md),
                  border: Border.all(color: HomeColors.border),
                ),
                child: _selectedImageBytes != null
                    ? Image.memory(_selectedImageBytes!, fit: BoxFit.cover)
                    : const Icon(
                        Icons.grass_rounded,
                        color: HomeColors.primaryGreen,
                        size: 32,
                      ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Expanded(
                          child: Text(
                            commodity,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: const TextStyle(
                              fontWeight: FontWeight.w900,
                              fontSize: 15,
                              color: HomeColors.textPrimary,
                            ),
                          ),
                        ),
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 7,
                            vertical: 3,
                          ),
                          decoration: BoxDecoration(
                            color: HomeColors.lightGreen,
                            borderRadius: BorderRadius.circular(
                              HomeRadius.pill,
                            ),
                          ),
                          child: const Text(
                            'Tersedia',
                            style: TextStyle(
                              color: HomeColors.primaryGreen,
                              fontSize: 10,
                              fontWeight: FontWeight.w800,
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 4),
                    Text(
                      '$price / $_unit',
                      style: const TextStyle(
                        color: HomeColors.primaryGreen,
                        fontWeight: FontWeight.w900,
                        fontSize: 16,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Row(
                      children: [
                        const Icon(
                          Icons.landscape_outlined,
                          size: 13,
                          color: HomeColors.textSecondary,
                        ),
                        const SizedBox(width: 4),
                        Expanded(
                          child: Text(
                            farmTitle,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: HomeTypography.caption,
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
            decoration: BoxDecoration(
              color: HomeColors.surface,
              borderRadius: BorderRadius.circular(HomeRadius.sm),
              border: Border.all(color: HomeColors.borderSubtle),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Row(
                  children: [
                    const Icon(
                      Icons.inventory_2_outlined,
                      size: 14,
                      color: HomeColors.textSecondary,
                    ),
                    const SizedBox(width: 6),
                    Text(
                      'Total Stok: $qty $_unit',
                      style: const TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w700,
                        color: HomeColors.textPrimary,
                      ),
                    ),
                  ],
                ),
                if (_salesLinkController.text.isNotEmpty)
                  const Row(
                    children: [
                      Icon(
                        Icons.link_rounded,
                        size: 14,
                        color: HomeColors.skyBlue,
                      ),
                      SizedBox(width: 4),
                      Text(
                        'Link Aktif',
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w700,
                          color: HomeColors.skyBlue,
                        ),
                      ),
                    ],
                  ),
              ],
            ),
          ),
          if (desc.isNotEmpty) ...[
            const SizedBox(height: 8),
            Text(
              desc,
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
              style: const TextStyle(
                fontSize: 11.5,
                color: HomeColors.textSecondary,
                height: 1.3,
              ),
            ),
          ],
        ],
      ),
    );
  }

  // 8. STICKY BOTTOM BAR
  Widget _buildStickyBottomBar() {
    final formReady = !_isLoadingFarms && _farms.isNotEmpty;
    final total = _estimatedTotalValue;
    final qty = _currentQuantity;

    return Container(
      decoration: BoxDecoration(
        color: HomeColors.surface,
        border: const Border(
          top: BorderSide(color: HomeColors.border, width: 1),
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.06),
            blurRadius: 12,
            offset: const Offset(0, -3),
          ),
        ],
      ),
      child: SafeArea(
        top: false,
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
          child: Row(
            children: [
              // Info ringkas sebelah kiri
              Expanded(
                flex: 3,
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'Total Estimasi:',
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w600,
                        color: HomeColors.textSecondary,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      total > 0 ? _formatRupiah(total) : 'Rp 0',
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.w900,
                        color: HomeColors.primaryGreen,
                        letterSpacing: -0.3,
                      ),
                    ),
                    if (qty > 0)
                      Text(
                        '${_formatNumber(qty)} $_unit panen',
                        style: HomeTypography.caption,
                      ),
                  ],
                ),
              ),
              const SizedBox(width: 12),

              // Tombol aksi sebelah kanan
              Expanded(
                flex: 4,
                child: SizedBox(
                  height: 50,
                  child: FilledButton.icon(
                    onPressed: formReady && !_isLoading ? _submit : null,
                    icon: _isLoading
                        ? const SizedBox(
                            width: 18,
                            height: 18,
                            child: CircularProgressIndicator(
                              strokeWidth: 2,
                              color: Colors.white,
                            ),
                          )
                        : const Icon(Icons.campaign_rounded, size: 18),
                    label: Text(
                      _isLoading ? 'Memproses...' : 'Pasang Iklan',
                      style: const TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                    style: FilledButton.styleFrom(
                      backgroundColor: HomeColors.primaryGreen,
                      foregroundColor: Colors.white,
                      disabledBackgroundColor: _isLoading
                          ? HomeColors.primaryGreen
                          : HomeColors.border,
                      disabledForegroundColor: _isLoading
                          ? Colors.white
                          : HomeColors.textTertiary,
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(HomeRadius.md),
                      ),
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _CommodityPreset {
  const _CommodityPreset({
    required this.name,
    required this.shortName,
    required this.icon,
    required this.defaultPrice,
    required this.descriptionSuggestion,
  });

  final String name;
  final String shortName;
  final IconData icon;
  final String defaultPrice;
  final String descriptionSuggestion;
}
