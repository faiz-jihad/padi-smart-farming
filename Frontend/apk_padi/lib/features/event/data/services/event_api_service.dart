import 'package:padi/core/network/api_client.dart';
import 'package:padi/features/event/data/models/event_model.dart';

class EventApiService {
  const EventApiService(this._apiClient);

  final ApiClient _apiClient;

  Future<List<EventModel>> fetchEvents({String? category}) async {
    try {
      final response = await _apiClient.dio.get(
        '/events',
        queryParameters: {
          if (category != null && category != 'all') 'category': category,
        },
      );

      final data = response.data;
      if (data is Map && data['data'] is List) {
        return (data['data'] as List)
            .whereType<Map>()
            .map((item) => EventModel.fromJson(Map<String, dynamic>.from(item)))
            .toList();
      }
      return [];
    } catch (_) {
      return [];
    }
  }

  Future<EventModel?> createEvent(EventModel event) async {
    try {
      final response = await _apiClient.dio.post(
        '/events',
        data: event.toJson(),
      );

      final data = response.data;
      if (data is Map && data['data'] is Map) {
        return EventModel.fromJson(Map<String, dynamic>.from(data['data'] as Map));
      }
      return null;
    } catch (_) {
      return null;
    }
  }

  Future<EventModel?> registerForEvent(int eventId, {String? notes}) async {
    try {
      final response = await _apiClient.dio.post(
        '/events/$eventId/register',
        data: {
          if (notes != null) 'notes': notes,
        },
      );

      final data = response.data;
      if (data is Map && data['data'] is Map) {
        return EventModel.fromJson(Map<String, dynamic>.from(data['data'] as Map));
      }
      return null;
    } catch (_) {
      return null;
    }
  }
}
