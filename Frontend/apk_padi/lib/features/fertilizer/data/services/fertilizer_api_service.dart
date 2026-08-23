import 'package:padi/core/network/api_client.dart';
import 'package:padi/features/fertilizer/data/models/fertilizer_rule_model.dart';

class FertilizerApiService {
  const FertilizerApiService(this._apiClient);

  final ApiClient _apiClient;

  Future<List<FertilizerRuleModel>> fetchRules() async {
    final response = await _apiClient.dio.get(
      '/fertilizer-rules',
    );

    final data = response.data['data'] as List<dynamic>? ?? [];

    return data
        .map(
          (item) => FertilizerRuleModel.fromJson(
            item as Map<String, dynamic>,
          ),
        )
        .toList();
  }
}