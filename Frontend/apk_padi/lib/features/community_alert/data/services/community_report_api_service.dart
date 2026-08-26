import 'package:padi/core/network/api_client.dart';
import 'package:padi/features/community_alert/data/models/community_report_model.dart';

class CommunityReportApiService {
  const CommunityReportApiService(this._apiClient);

  final ApiClient _apiClient;

  Future<List<CommunityReportModel>> fetchReports() async {
    try {
      final response = await _apiClient.dio.get<Map<String, dynamic>>('/community-reports');
      final data = response.data?['data'];
      if (data is List) {
        return data
            .whereType<Map>()
            .map((e) => CommunityReportModel.fromJson(Map<String, dynamic>.from(e)))
            .toList();
      }
      return [];
    } catch (_) {
      return [];
    }
  }

  Future<CommunityReportModel> createReport({
    required int scanId,
    required double latitude,
    required double longitude,
    required double radiusKm,
    required bool consentGiven,
  }) async {
    final response = await _apiClient.dio.post(
      '/community-reports',
      data: {
        'scan_id': scanId,
        'latitude': latitude,
        'longitude': longitude,
        'radius_km': radiusKm,
        'consent_given': consentGiven,
      },
    );

    final responseData = response.data;

    if (responseData is! Map<String, dynamic>) {
      throw Exception('Respons server tidak valid.');
    }

    if (responseData['success'] != true && responseData['data'] == null) {
      throw Exception(
        responseData['message']?.toString() ??
            'Gagal mengirim laporan.',
      );
    }

    final data = responseData['data'] ?? responseData;

    if (data is! Map<String, dynamic>) {
      throw Exception('Data laporan tidak ditemukan.');
    }

    return CommunityReportModel.fromJson(data);
  }
}