import 'package:padi/core/network/api_client.dart';
import 'package:padi/features/cultivation/data/models/crop_season_model.dart';

class CropSeasonApiService {
  const CropSeasonApiService(this._apiClient);

  final ApiClient _apiClient;

  Future<List<CropSeasonModel>> fetchCropSeasons() async {
    final response = await _apiClient.dio.get('/crop-seasons');

    final data = response.data['data'];

    if (data is! Map<String, dynamic>) {
      return [];
    }

    final seasons = data['crop_seasons'];

    if (seasons is! List) {
      return [];
    }

    return seasons
        .map(
          (item) => CropSeasonModel.fromJson(
            Map<String, dynamic>.from(item as Map),
          ),
        )
        .toList();
  }

  Future<CropSeasonModel> createCropSeason({
    required int farmId,
    int? varietyId,
    String? plannedPlantingDate,
    String? plantingDate,
    String? estimatedHarvestDate,
    String? status,
  }) async {
    final payload = <String, dynamic>{
      'farm_id': farmId,
      'variety_id': varietyId,
      'planned_planting_date': plannedPlantingDate,
      'planting_date': plantingDate,
      'estimated_harvest_date': estimatedHarvestDate,
      'status': status ?? 'planned',
    };

    final response = await _apiClient.dio.post(
      '/crop-seasons',
      data: payload,
    );

    final data = response.data['data'];

    if (data is! Map<String, dynamic>) {
      throw Exception('Format response musim tanam tidak valid.');
    }

    final cropSeason = data['crop_season'];

    if (cropSeason is! Map) {
      throw Exception('Data musim tanam tidak ditemukan.');
    }

    return CropSeasonModel.fromJson(
      Map<String, dynamic>.from(cropSeason),
    );
  }
}