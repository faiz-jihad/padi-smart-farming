import 'package:flutter/foundation.dart';
import 'package:padi/core/network/api_client.dart';
import 'package:padi/features/community_alert/data/models/community_alert_model.dart';

class CommunityAlertApiService {
  const CommunityAlertApiService(this._apiClient);

  final ApiClient _apiClient;

  Future<List<CommunityAlertModel>> fetchAlerts() async {
    final response = await _apiClient.dio.get('/admin-broadcasts');

    debugPrint('COMMUNITY ALERT STATUS: ${response.statusCode}');
    debugPrint('COMMUNITY ALERT RESPONSE: ${response.data}');

    final responseData = response.data;

    if (responseData is! Map<String, dynamic>) {
      debugPrint('RESPONSE BUKAN MAP');
      return [];
    }

    final data = responseData['data'];

    debugPrint('COMMUNITY ALERT DATA: $data');
    debugPrint('COMMUNITY ALERT DATA TYPE: ${data.runtimeType}');

    if (data is! List) {
      debugPrint('DATA BUKAN LIST');
      return [];
    }

    final alerts = data
        .map(
          (item) => CommunityAlertModel.fromJson(
            Map<String, dynamic>.from(item),
          ),
        )
        .toList();

    debugPrint('COMMUNITY ALERT TOTAL: ${alerts.length}');

    return alerts;
  }
}

