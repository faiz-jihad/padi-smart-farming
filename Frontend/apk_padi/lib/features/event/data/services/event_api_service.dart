import 'package:dio/dio.dart';
import 'package:padi/core/network/api_client.dart';
import 'package:padi/features/event/data/models/event_model.dart';

class EventCreateResult {
  const EventCreateResult({
    required this.success,
    required this.message,
    this.event,
    this.errorMessage,
  });

  final bool success;
  final String message;
  final EventModel? event;
  final String? errorMessage;
}

class EventRegisterResult {
  const EventRegisterResult({
    required this.success,
    required this.message,
    this.event,
    this.alreadyRegistered = false,
  });

  final bool success;
  final String message;
  final EventModel? event;
  final bool alreadyRegistered;
}

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

  Future<List<EventModel>> fetchMySubmissions() async {
    try {
      final response = await _apiClient.dio.get('/events/my-submissions');

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

  Future<EventCreateResult> createEvent(EventModel event) async {
    try {
      final response = await _apiClient.dio.post(
        '/events',
        data: event.toJson(),
      );

      final data = response.data;
      if (data is Map) {
        final message = data['message']?.toString() ?? 'Pengajuan agenda berhasil dikirim.';
        EventModel? createdEvent;
        if (data['data'] is Map) {
          createdEvent = EventModel.fromJson(Map<String, dynamic>.from(data['data'] as Map));
        }
        return EventCreateResult(
          success: true,
          message: message,
          event: createdEvent,
        );
      }
      return const EventCreateResult(
        success: false,
        message: 'Gagal mengirim pengajuan agenda.',
        errorMessage: 'Response tidak valid.',
      );
    } on DioException catch (e) {
      String errorMsg = 'Terjadi kesalahan saat mengirim pengajuan.';
      if (e.response?.data is Map && e.response?.data['message'] != null) {
        errorMsg = e.response!.data['message'].toString();
      }
      return EventCreateResult(
        success: false,
        message: errorMsg,
        errorMessage: errorMsg,
      );
    } catch (e) {
      return EventCreateResult(
        success: false,
        message: 'Gagal mengajukan acara: ${e.toString()}',
        errorMessage: e.toString(),
      );
    }
  }

  Future<EventRegisterResult> registerForEvent(int eventId, {String? notes}) async {
    try {
      final response = await _apiClient.dio.post(
        '/events/$eventId/register',
        data: notes != null ? {'notes': notes} : null,
      );

      final data = response.data;
      if (data is Map) {
        final message = data['message']?.toString() ?? 'Pendaftaran berhasil!';
        final alreadyRegistered = data['already_registered'] == true;
        EventModel? updatedEvent;
        if (data['data'] is Map) {
          updatedEvent = EventModel.fromJson(Map<String, dynamic>.from(data['data'] as Map));
        }
        return EventRegisterResult(
          success: true,
          message: message,
          event: updatedEvent,
          alreadyRegistered: alreadyRegistered,
        );
      }
      return const EventRegisterResult(
        success: false,
        message: 'Gagal mendaftar acara.',
      );
    } on DioException catch (e) {
      String errorMsg = 'Gagal melakukan pendaftaran.';
      if (e.response?.data is Map && e.response?.data['message'] != null) {
        errorMsg = e.response!.data['message'].toString();
      }
      return EventRegisterResult(
        success: false,
        message: errorMsg,
      );
    } catch (e) {
      return EventRegisterResult(
        success: false,
        message: 'Pendaftaran gagal: ${e.toString()}',
      );
    }
  }
}
