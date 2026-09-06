import 'dart:async';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:padi/core/providers/app_providers.dart';
import 'package:padi/features/event/data/models/event_model.dart';
import 'package:padi/features/event/data/services/event_api_service.dart';

final eventApiServiceProvider = Provider<EventApiService>((ref) {
  return EventApiService(ref.read(apiClientProvider));
});

final eventsProvider = NotifierProvider<EventsNotifier, List<EventModel>>(EventsNotifier.new);

class EventsNotifier extends Notifier<List<EventModel>> {
  Timer? _pollingTimer;

  @override
  List<EventModel> build() {
    ref.onDispose(() {
      _pollingTimer?.cancel();
    });
    unawaited(loadEventsFromApi());
    _pollingTimer = Timer.periodic(const Duration(seconds: 6), (_) {
      loadEventsFromApi();
    });
    return [];
  }

  Future<void> loadEventsFromApi() async {
    try {
      final service = ref.read(eventApiServiceProvider);
      final apiEvents = await service.fetchEvents();
      state = apiEvents;
    } catch (_) {}
  }

  Future<EventCreateResult> submitEvent(EventModel event) async {
    try {
      final service = ref.read(eventApiServiceProvider);
      final result = await service.createEvent(event);

      if (result.success && result.event != null) {
        final created = result.event!;
        // Only insert into public list if already approved (e.g. created by admin)
        if (created.isApproved) {
          state = [created, ...state.where((e) => e.id != created.id)];
        }
        // Always refresh submissions for the farmer
        ref.read(mySubmissionsProvider.notifier).loadSubmissions();
      }

      return result;
    } catch (e) {
      return EventCreateResult(
        success: false,
        message: 'Gagal mengirim pengajuan: ${e.toString()}',
      );
    }
  }

  void updateEvent(EventModel updatedEvent) {
    final idx = state.indexWhere((e) => e.id == updatedEvent.id);
    if (idx != -1) {
      state = [
        for (final event in state)
          if (event.id == updatedEvent.id) updatedEvent else event,
      ];
    } else {
      state = [updatedEvent, ...state];
    }
  }

  Future<EventRegisterResult> registerForEvent(int eventId, {String? notes}) async {
    final existingIndex = state.indexWhere((e) => e.id == eventId);
    final targetEvent = existingIndex != -1 ? state[existingIndex] : null;

    if (targetEvent != null && targetEvent.isRegistered) {
      return EventRegisterResult(
        success: true,
        message: 'Anda sudah terdaftar pada acara ini. Tiket Anda tetap aktif.',
        event: targetEvent,
        alreadyRegistered: true,
      );
    }

    try {
      final service = ref.read(eventApiServiceProvider);
      final result = await service.registerForEvent(eventId, notes: notes);

      if (result.success && result.event != null) {
        final updatedEvent = result.event!;
        state = [
          for (final event in state)
            if (event.id == eventId) updatedEvent else event,
        ];
      }

      return result;
    } catch (e) {
      return EventRegisterResult(
        success: false,
        message: 'Pendaftaran gagal: ${e.toString()}',
      );
    }
  }
}

final mySubmissionsProvider =
    NotifierProvider<MySubmissionsNotifier, List<EventModel>>(MySubmissionsNotifier.new);

class MySubmissionsNotifier extends Notifier<List<EventModel>> {
  Timer? _pollingTimer;

  @override
  List<EventModel> build() {
    ref.onDispose(() {
      _pollingTimer?.cancel();
    });
    unawaited(loadSubmissions());
    _pollingTimer = Timer.periodic(const Duration(seconds: 5), (_) {
      loadSubmissions();
    });
    return [];
  }

  Future<void> loadSubmissions() async {
    try {
      final service = ref.read(eventApiServiceProvider);
      final list = await service.fetchMySubmissions();
      state = list;
    } catch (_) {}
  }
}
