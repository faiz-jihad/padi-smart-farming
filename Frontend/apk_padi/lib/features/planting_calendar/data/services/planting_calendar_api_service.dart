import 'package:padi/core/network/api_client.dart';
import 'package:padi/features/planting_calendar/data/models/planting_calendar_model.dart';

class PlantingCalendarApiService {
  const PlantingCalendarApiService(this._apiClient);

  final ApiClient _apiClient;

  Future<PlantingCalendarModel?> getCalendarByDistrict(int districtId, {String? season, int? year}) async {
    try {
      final queryParams = <String, dynamic>{};
      if (season != null) queryParams['season'] = season;
      if (year != null) queryParams['year'] = year;

      final res = await _apiClient.dio.get(
        '/districts/$districtId/planting-calendar',
        queryParameters: queryParams.isEmpty ? null : queryParams,
      );
      if (res.data['success'] == true && res.data['data'] != null) {
        return PlantingCalendarModel.fromJson(res.data['data'] as Map<String, dynamic>);
      }
      return null;
    } catch (_) {
      return null;
    }
  }

  Future<PlantingCalendarModel?> getCalendarForFarm(int farmId, {String? season, int? year}) async {
    try {
      final queryParams = <String, dynamic>{};
      if (season != null) queryParams['season'] = season;
      if (year != null) queryParams['year'] = year;

      final res = await _apiClient.dio.get(
        '/farms/$farmId/planting-calendar',
        queryParameters: queryParams.isEmpty ? null : queryParams,
      );
      if (res.data['success'] == true && res.data['data'] != null) {
        return PlantingCalendarModel.fromJson(res.data['data'] as Map<String, dynamic>);
      }
      return null;
    } catch (_) {
      return null;
    }
  }

  Future<List<PlantingCalendarModel>> fetchCalendars({
    int? provinceId,
    int? regencyId,
    int? districtId,
    String? season,
    int? year,
  }) async {
    final queryParams = <String, dynamic>{};
    if (provinceId != null) queryParams['province_id'] = provinceId;
    if (regencyId != null) queryParams['regency_id'] = regencyId;
    if (districtId != null) queryParams['district_id'] = districtId;
    if (season != null) queryParams['season'] = season;
    if (year != null) queryParams['year'] = year;

    final res = await _apiClient.dio.get(
      '/planting-calendars',
      queryParameters: queryParams.isEmpty ? null : queryParams,
    );
    final data = res.data['data'] as List<dynamic>? ?? [];
    return data.map((e) => PlantingCalendarModel.fromJson(e as Map<String, dynamic>)).toList();
  }
}
