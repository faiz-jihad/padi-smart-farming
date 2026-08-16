import 'package:padi/core/network/api_client.dart';
import 'package:padi/features/region/data/models/region_models.dart';

class RegionApiService {
  const RegionApiService(this._apiClient);

  final ApiClient _apiClient;

  Future<List<ProvinceModel>> fetchProvinces() async {
    final res = await _apiClient.dio.get('/regions/provinces');
    final data = res.data['data'] as List<dynamic>? ?? [];
    return data.map((e) => ProvinceModel.fromJson(e as Map<String, dynamic>)).toList();
  }

  Future<List<RegencyModel>> fetchRegencies(int provinceId) async {
    final res = await _apiClient.dio.get(
      '/regions/regencies',
      queryParameters: {'province_id': provinceId},
    );
    final data = res.data['data'] as List<dynamic>? ?? [];
    return data.map((e) => RegencyModel.fromJson(e as Map<String, dynamic>)).toList();
  }

  Future<List<DistrictModel>> fetchDistricts(int regencyId) async {
    final res = await _apiClient.dio.get(
      '/regions/districts',
      queryParameters: {'regency_id': regencyId},
    );
    final data = res.data['data'] as List<dynamic>? ?? [];
    return data.map((e) => DistrictModel.fromJson(e as Map<String, dynamic>)).toList();
  }

  Future<List<VillageModel>> fetchVillages(int districtId) async {
    final res = await _apiClient.dio.get(
      '/regions/villages',
      queryParameters: {'district_id': districtId},
    );
    final data = res.data['data'] as List<dynamic>? ?? [];
    return data.map((e) => VillageModel.fromJson(e as Map<String, dynamic>)).toList();
  }

  Future<ResolvedLocationModel?> resolveCoordinates(double latitude, double longitude) async {
    try {
      final res = await _apiClient.dio.get(
        '/location/resolve',
        queryParameters: {
          'latitude': latitude,
          'longitude': longitude,
        },
      );
      if (res.data['success'] == true && res.data['data'] != null) {
        return ResolvedLocationModel.fromJson(res.data['data'] as Map<String, dynamic>);
      }
      return null;
    } catch (_) {
      return null;
    }
  }
}
