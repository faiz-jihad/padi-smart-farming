import 'package:padi/core/network/api_client.dart';
import 'package:padi/features/planting_calendar/data/models/planting_calendar_model.dart';

class PlantingCalendarApiService {
  const PlantingCalendarApiService(this._apiClient);

  final ApiClient _apiClient;

  Future<PlantingCalendarModel?> getCalendarForFarm(
    int farmId, {
    String? season,
    int? year,
  }) async {
    final queryParams = <String, dynamic>{};

    if (season != null) {
      queryParams['season'] = season;
    }

    if (year != null) {
      queryParams['year'] = year;
    }

    final response = await _apiClient.dio.get(
      '/farms/$farmId/planting-calendar',
      queryParameters:
          queryParams.isEmpty ? null : queryParams,
    );

    final responseData = response.data;

    if (responseData is! Map<String, dynamic>) {
      return null;
    }

    final data = responseData['data'];

    if (data is! Map<String, dynamic>) {
      return null;
    }

    return PlantingCalendarModel.fromJson(data);
  }

  Future<PlantingCalendarModel?> getCalendarByDistrict(
    int districtId, {
    String? season,
    int? year,
  }) async {
    final queryParams = <String, dynamic>{};

    if (season != null) {
      queryParams['season'] = season;
    }

    if (year != null) {
      queryParams['year'] = year;
    }

    final response = await _apiClient.dio.get(
      '/districts/$districtId/planting-calendar',
      queryParameters:
          queryParams.isEmpty ? null : queryParams,
    );

    final responseData = response.data;

    if (responseData is! Map<String, dynamic>) {
      return null;
    }

    final data = responseData['data'];

    if (data is! Map<String, dynamic>) {
      return null;
    }

    return PlantingCalendarModel.fromJson(data);
  }

  Future<List<PlantingCalendarModel>> fetchCalendars({
    int? provinceId,
    int? regencyId,
    int? districtId,
    String? season,
    int? year,
  }) async {
    final queryParams = <String, dynamic>{};

    if (provinceId != null) {
      queryParams['province_id'] = provinceId;
    }

    if (regencyId != null) {
      queryParams['regency_id'] = regencyId;
    }

    if (districtId != null) {
      queryParams['district_id'] = districtId;
    }

    if (season != null) {
      queryParams['season'] = season;
    }

    if (year != null) {
      queryParams['year'] = year;
    }

    final response = await _apiClient.dio.get(
      '/planting-calendars',
      queryParameters:
          queryParams.isEmpty ? null : queryParams,
    );

    final responseData = response.data;

    if (responseData is! Map<String, dynamic>) {
      return [];
    }

    final data = responseData['data'];

    if (data is! List) {
      return [];
    }

    return data
        .map(
          (item) => PlantingCalendarModel.fromJson(
            Map<String, dynamic>.from(item),
          ),
        )
        .toList();
  }
}