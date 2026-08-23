import 'package:padi/core/network/api_client.dart';
import 'package:padi/features/harvest/data/models/harvest_model.dart';

class HarvestApiService {
  const HarvestApiService(this._apiClient);

  final ApiClient _apiClient;

  Future<List<HarvestModel>> fetchHarvests() async {
    final response =
        await _apiClient.dio.get('/harvests');

    final responseData = response.data;
    final data = responseData['data'];

    if (data is Map<String, dynamic>) {
      final harvests = data['harvests'];

      if (harvests is List) {
        return harvests
            .map(
              (item) => HarvestModel.fromJson(
                Map<String, dynamic>.from(item),
              ),
            )
            .toList();
      }
    }

    return [];
  }

  Future<HarvestModel> createHarvest({
    required int cropSeasonId,
    required String harvestDate,
    required double quantity,
    required String unit,
    String? qualityGrade,
    double? moisturePercent,
  }) async {
    final response =
        await _apiClient.dio.post(
      '/harvests',
      data: {
        'crop_season_id': cropSeasonId,
        'harvest_date': harvestDate,
        'quantity': quantity,
        'unit': unit,
        'quality_grade': qualityGrade,
        'moisture_percent': moisturePercent,
      },
    );

    final data = response.data['data'];

    return HarvestModel.fromJson(
      Map<String, dynamic>.from(
        data['harvest'],
      ),
    );
  }
}