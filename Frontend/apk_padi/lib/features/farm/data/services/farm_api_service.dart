import 'package:padi/core/network/api_client.dart';
import 'package:padi/features/farm/data/models/farm_model.dart';

class FarmApiService {
  const FarmApiService(this._apiClient);

  final ApiClient _apiClient;

  Future<List<FarmModel>> fetchFarms() async {
    final res = await _apiClient.dio.get('/farms');
    final data = res.data['data'] as List<dynamic>? ?? [];
    return data.map((e) => FarmModel.fromJson(e as Map<String, dynamic>)).toList();
  }

  Future<FarmModel> getFarm(int id) async {
    final res = await _apiClient.dio.get('/farms/$id');
    return FarmModel.fromJson(res.data['data'] as Map<String, dynamic>);
  }

  Future<FarmModel> createFarm({
    required String name,
    required double areaHa,
    required double latitude,
    required double longitude,
    required String irrigationType,
    String? irrigationNotes,
    String? soilType,
    int? provinceId,
    int? regencyId,
    int? districtId,
    int? villageId,
  }) async {
    final payload = <String, dynamic>{
      'name': name,
      'area_ha': areaHa,
      'latitude': latitude,
      'longitude': longitude,
      'irrigation_type': irrigationType,
      'irrigation_notes': irrigationNotes,
      'soil_type': soilType,
    };

    if (provinceId != null) payload['province_id'] = provinceId;
    if (regencyId != null) payload['regency_id'] = regencyId;
    if (districtId != null) payload['district_id'] = districtId;
    if (villageId != null) payload['village_id'] = villageId;

    final res = await _apiClient.dio.post('/farms', data: payload);
    return FarmModel.fromJson(res.data['data'] as Map<String, dynamic>);
  }

  Future<void> deleteFarm(int id) async {
    await _apiClient.dio.delete('/farms/$id');
  }
}
