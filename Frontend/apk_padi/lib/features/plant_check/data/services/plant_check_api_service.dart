import 'package:dio/dio.dart';
import 'package:padi/core/helpers/api_error_helper.dart';
import 'package:padi/core/network/api_client.dart';

class PlantCheckApiService {
  const PlantCheckApiService(this._apiClient);

  final ApiClient _apiClient;

  Future<PlantCheckResult> scanDisease({
    required int farmId,
    required String imagePath,
    int? plantAgeDays,
    double? latitude,
    double? longitude,
  }) async {
    try {
      final response = await _apiClient.dio.post<Map<String, dynamic>>(
        '/disease-scans',
        data: FormData.fromMap({
          'farm_id': farmId,
          if (plantAgeDays != null) 'plant_age_days': plantAgeDays,
          if (latitude != null) 'latitude': latitude,
          if (longitude != null) 'longitude': longitude,
          'image': await MultipartFile.fromFile(imagePath),
        }),
      );

      final data = response.data?['data'] as Map<String, dynamic>? ?? {};
      final scan = data['scan'] as Map<String, dynamic>? ?? {};

      return PlantCheckResult.fromJson(scan);
    } catch (error) {
      throw mapDioException(error);
    }
  }

  Future<List<PlantCheckResult>> fetchScans() async {
    try {
      final response = await _apiClient.dio.get<Map<String, dynamic>>('/disease-scans');
      final data = response.data?['data'] as Map<String, dynamic>? ?? {};
      final rawScans = data['scans'] as List? ?? [];
      return rawScans
          .whereType<Map>()
          .map((e) => PlantCheckResult.fromJson(Map<String, dynamic>.from(e)))
          .toList();
    } catch (error) {
      return [];
    }
  }
}

class GeminiProduct {
  const GeminiProduct({
    required this.nama,
    required this.bahanAktif,
    required this.harga,
    required this.keyword,
  });

  factory GeminiProduct.fromJson(Map<String, dynamic> json) {
    return GeminiProduct(
      nama: json['nama']?.toString() ?? '',
      bahanAktif: json['bahan_aktif']?.toString() ?? '',
      harga: json['harga']?.toString() ?? '',
      keyword: json['keyword']?.toString() ?? '',
    );
  }

  final String nama;
  final String bahanAktif;
  final String harga;
  final String keyword;
}

class GeminiRecommendationData {
  const GeminiRecommendationData({
    required this.penyakit,
    required this.analisis,
    required this.langkahPreventif,
    required this.rekomendasiObat,
    required this.produk,
    required this.diy,
    required this.source,
  });

  factory GeminiRecommendationData.fromJson(Map<String, dynamic> json) {
    final rawProduk = json['produk'];
    List<GeminiProduct> produkList = [];
    if (rawProduk is List) {
      produkList = rawProduk
          .whereType<Map>()
          .map((e) => GeminiProduct.fromJson(Map<String, dynamic>.from(e)))
          .toList();
    }

    return GeminiRecommendationData(
      penyakit: json['penyakit']?.toString() ?? '',
      analisis: json['analisis']?.toString() ?? '',
      langkahPreventif: json['langkah_preventif']?.toString() ?? '',
      rekomendasiObat: json['rekomendasi_obat']?.toString() ?? '',
      produk: produkList,
      diy: json['diy']?.toString() ?? '',
      source: json['source']?.toString() ?? 'Gemini AI Agronomy',
    );
  }

  final String penyakit;
  final String analisis;
  final String langkahPreventif;
  final String rekomendasiObat;
  final List<GeminiProduct> produk;
  final String diy;
  final String source;
}

class PlantCheckResult {
  const PlantCheckResult({
    required this.id,
    required this.farmId,
    required this.predictedClass,
    required this.qualityStatus,
    this.farmName,
    this.confidence,
    this.modelVersion,
    this.imageUrl,
    this.recommendation,
  });

  factory PlantCheckResult.fromJson(Map<String, dynamic> json) {
    final recRaw = json['recommendation'];
    GeminiRecommendationData? rec;
    if (recRaw is Map) {
      rec = GeminiRecommendationData.fromJson(Map<String, dynamic>.from(recRaw));
    }

    return PlantCheckResult(
      id: _toInt(json['id']),
      farmId: _toInt(json['farm_id']),
      farmName: json['farm_name']?.toString(),
      predictedClass: json['predicted_class']?.toString() ?? 'Tidak diketahui',
      qualityStatus: json['quality_status']?.toString() ?? 'unknown',
      confidence: _toNullableDouble(json['confidence']),
      modelVersion: json['model_version']?.toString(),
      imageUrl: json['image_url']?.toString(),
      recommendation: rec,
    );
  }

  final int id;
  final int farmId;
  final String? farmName;
  final String predictedClass;
  final String qualityStatus;
  final double? confidence;
  final String? modelVersion;
  final String? imageUrl;
  final GeminiRecommendationData? recommendation;
}

int _toInt(dynamic value) {
  if (value is num) return value.toInt();
  if (value is String) return int.tryParse(value.trim()) ?? 0;
  return 0;
}

double? _toNullableDouble(dynamic value) {
  if (value == null) return null;
  if (value is num) return value.toDouble();
  if (value is String) return double.tryParse(value.trim().replaceAll(',', '.'));
  return null;
}
