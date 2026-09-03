import 'dart:typed_data';

import 'package:dio/dio.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:padi/core/helpers/api_error_helper.dart';
import 'package:padi/core/network/api_client.dart';
import 'package:padi/core/providers/app_providers.dart';

final plantCheckApiServiceProvider = Provider<PlantCheckApiService>((ref) {
  return PlantCheckApiService(ref.read(apiClientProvider));
});

class PlantCheckApiService {
  const PlantCheckApiService(this._apiClient);

  final ApiClient _apiClient;

  Future<PlantCheckResult> scanDisease({
    int? farmId,
    required String imagePath,
    Uint8List? imageBytes,
    String? fileName,
    int? plantAgeDays,
    double? latitude,
    double? longitude,
  }) async {
    try {
      final uploadFileName = _uploadFileName(fileName, imagePath);
      final MultipartFile uploadFile;

      if (imageBytes != null) {
        uploadFile = MultipartFile.fromBytes(
          imageBytes,
          filename: uploadFileName,
        );
      } else {
        uploadFile = await MultipartFile.fromFile(
          imagePath,
          filename: uploadFileName,
        );
      }

      final response = await _apiClient.dio.post<Map<String, dynamic>>(
        '/disease-scans',
        data: FormData.fromMap({
          if (farmId != null && farmId > 0) 'farm_id': farmId,
          if (plantAgeDays != null) 'plant_age_days': plantAgeDays,
          if (latitude != null) 'latitude': latitude,
          if (longitude != null) 'longitude': longitude,
          'image': uploadFile,
        }),
      );

      final data = response.data?['data'] as Map<String, dynamic>? ?? {};
      final scan = (data['scan'] as Map<String, dynamic>?) ?? data;

      return PlantCheckResult.fromJson(scan);
    } catch (error) {
      throw mapDioException(error);
    }
  }

  Future<Map<String, dynamic>> checkAiHealth() async {
    try {
      final response = await _apiClient.dio.get<Map<String, dynamic>>('/health');
      return response.data ?? {'status': 'ok'};
    } catch (e) {
      return {'status': 'offline', 'error': e.toString()};
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

  Future<bool> submitFeedback({
    required int scanId,
    required String status,
    String? correctedClass,
    String? notes,
  }) async {
    try {
      final response = await _apiClient.dio.post<Map<String, dynamic>>(
        '/disease-scans/$scanId/feedback',
        data: {
          'status': status,
          if (correctedClass != null) 'corrected_class': correctedClass,
          if (notes != null) 'notes': notes,
        },
      );
      return response.data?['success'] == true;
    } catch (_) {
      return false;
    }
  }

  Future<Map<String, dynamic>?> submitToPpl(int scanId, {String? notes}) async {
    try {
      final response = await _apiClient.dio.post<Map<String, dynamic>>(
        '/ppl-validations',
        data: {
          'scan_id': scanId,
          if (notes != null && notes.trim().isNotEmpty) 'notes': notes.trim(),
        },
      );
      if (response.data?['success'] == true) {
        return response.data?['data'] as Map<String, dynamic>?;
      }
      return null;
    } catch (error) {
      throw mapDioException(error);
    }
  }

  Future<List<Map<String, dynamic>>> fetchPplValidations() async {
    try {
      final response = await _apiClient.dio.get<Map<String, dynamic>>('/ppl-validations');
      final data = response.data?['data'] as Map<String, dynamic>? ?? {};
      final list = data['validations'] as List? ?? [];
      return list.whereType<Map>().map((e) => Map<String, dynamic>.from(e)).toList();
    } catch (_) {
      return [];
    }
  }

  Future<bool> updatePplValidation({
    required int validationId,
    required String status,
    String? notes,
  }) async {
    try {
      final response = await _apiClient.dio.patch<Map<String, dynamic>>(
        '/ppl-validations/$validationId',
        data: {
          'status': status,
          if (notes != null) 'notes': notes,
        },
      );
      return response.data?['success'] == true;
    } catch (error) {
      throw mapDioException(error);
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
    this.confidenceLevel,
    this.needsExpertReview = false,
    this.topPredictions = const [],
    this.predictionMargin,
    this.modelAccuracy,
    this.modelVersion,
    this.detectionStatus,
    this.statusMessage,
    this.imageUrl,
    this.recommendation,
    this.userFeedback,
    this.verifiedClass,
    this.isLearned = false,
    this.isSubmittedToPpl = false,
    this.pplValidation,
    this.pipelineStages,
    this.segmentation,
    this.features,
  });

  factory PlantCheckResult.fromJson(Map<String, dynamic> json) {
    final recRaw = json['recommendation'];
    GeminiRecommendationData? rec;
    if (recRaw is Map) {
      rec = GeminiRecommendationData.fromJson(Map<String, dynamic>.from(recRaw));
    }

    final pplValRaw = json['ppl_validation'];
    Map<String, dynamic>? pplVal;
    if (pplValRaw is Map) {
      pplVal = Map<String, dynamic>.from(pplValRaw);
    }

    final stagesRaw = json['pipeline_stages'] ?? (json['detection_metadata'] is Map ? (json['detection_metadata'] as Map)['pipeline_stages'] : null);
    Map<String, dynamic>? stages;
    if (stagesRaw is Map) {
      stages = Map<String, dynamic>.from(stagesRaw);
    }

    final segRaw = json['segmentation'] ?? (json['detection_metadata'] is Map ? (json['detection_metadata'] as Map)['segmentation'] : null);
    Map<String, dynamic>? seg;
    if (segRaw is Map) {
      seg = Map<String, dynamic>.from(segRaw);
    }

    final featRaw = json['features'] ?? (json['detection_metadata'] is Map ? (json['detection_metadata'] as Map)['features'] : null);
    Map<String, dynamic>? feat;
    if (featRaw is Map) {
      feat = Map<String, dynamic>.from(featRaw);
    }

    return PlantCheckResult(
      id: _toInt(json['id']),
      farmId: _toInt(json['farm_id']),
      farmName: json['farm_name']?.toString(),
      predictedClass: json['predicted_class']?.toString() ?? 'Tidak diketahui',
      qualityStatus: json['quality_status']?.toString() ?? 'unknown',
      confidence: _toNullableDouble(json['confidence']),
      confidenceLevel: json['confidence_level']?.toString(),
      needsExpertReview: json['needs_expert_review'] == true,
      topPredictions: _parsePredictionCandidates(json['top_predictions']),
      predictionMargin: _toNullableDouble(json['prediction_margin']),
      modelAccuracy: _toNullableDouble(json['model_accuracy']),
      modelVersion: json['model_version']?.toString(),
      detectionStatus: json['detection_status']?.toString(),
      statusMessage: json['status_message']?.toString(),
      imageUrl: json['image_url']?.toString(),
      recommendation: rec,
      userFeedback: json['user_feedback']?.toString(),
      verifiedClass: json['verified_class']?.toString(),
      isLearned: json['is_learned'] == true,
      isSubmittedToPpl: json['is_submitted_to_ppl'] == true || pplVal != null,
      pplValidation: pplVal,
      pipelineStages: stages,
      segmentation: seg,
      features: feat,
    );
  }

  final int id;
  final int farmId;
  final String? farmName;
  final String predictedClass;
  final String qualityStatus;
  final double? confidence;
  final String? confidenceLevel;
  final bool needsExpertReview;
  final List<PredictionCandidate> topPredictions;
  final double? predictionMargin;
  final double? modelAccuracy;
  final String? modelVersion;
  final String? detectionStatus;
  final String? statusMessage;
  final String? imageUrl;
  final GeminiRecommendationData? recommendation;
  final String? userFeedback;
  final String? verifiedClass;
  final bool isLearned;
  final bool isSubmittedToPpl;
  final Map<String, dynamic>? pplValidation;
  final Map<String, dynamic>? pipelineStages;
  final Map<String, dynamic>? segmentation;
  final Map<String, dynamic>? features;
}

class PredictionCandidate {
  const PredictionCandidate({
    required this.diseaseCode,
    required this.diseaseName,
    required this.confidence,
  });

  factory PredictionCandidate.fromJson(Map<String, dynamic> json) {
    return PredictionCandidate(
      diseaseCode: json['disease_code']?.toString() ?? '',
      diseaseName: json['disease_name']?.toString() ?? '',
      confidence: _toNullableDouble(json['confidence']) ?? 0,
    );
  }

  final String diseaseCode;
  final String diseaseName;
  final double confidence;
}

String _uploadFileName(String? fileName, String imagePath) {
  final name = (fileName?.trim().isNotEmpty ?? false)
      ? fileName!.trim()
      : _fileNameFromPath(imagePath);

  return RegExp(r'\.(jpe?g|png|webp)$', caseSensitive: false).hasMatch(name)
      ? name
      : '$name.jpg';
}

String _fileNameFromPath(String path) {
  final parts = path.split(RegExp(r'[\\/]'));
  final name = parts.isNotEmpty ? parts.last.trim() : '';
  return name.isNotEmpty ? name : 'daun-padi.jpg';
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

List<PredictionCandidate> _parsePredictionCandidates(dynamic value) {
  if (value is! List) return const [];
  return value
      .whereType<Map>()
      .map((item) => PredictionCandidate.fromJson(Map<String, dynamic>.from(item)))
      .where((item) => item.diseaseName.isNotEmpty)
      .toList(growable: false);
}
