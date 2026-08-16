import 'package:padi/core/network/api_client.dart';

class MapApiService {
  const MapApiService(this._apiClient);

  final ApiClient _apiClient;

  Future<Map<String, dynamic>?> fetchDistrictBoundaries(int regencyId) async {
    try {
      final res = await _apiClient.dio.get(
        '/maps/districts',
        queryParameters: {'regency_id': regencyId},
      );
      if (res.data['success'] == true && res.data['data'] != null) {
        return res.data['data'] as Map<String, dynamic>;
      }
      return null;
    } catch (_) {
      return null;
    }
  }

  Future<Map<String, dynamic>?> fetchVillageBoundaries(int districtId) async {
    try {
      final res = await _apiClient.dio.get(
        '/maps/villages',
        queryParameters: {'district_id': districtId},
      );
      if (res.data['success'] == true && res.data['data'] != null) {
        return res.data['data'] as Map<String, dynamic>;
      }
      return null;
    } catch (_) {
      return null;
    }
  }
}
