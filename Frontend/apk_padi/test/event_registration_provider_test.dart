import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:padi/features/event/data/models/event_model.dart';
import 'package:padi/features/event/data/providers/event_providers.dart';

void main() {
  group('EventsNotifier Idempotency & Ticketing Tests', () {
    test('registerForEvent handles already registered state and returns result', () async {
      final container = ProviderContainer();
      addTearDown(container.dispose);

      final sampleEvent = EventModel(
        id: 101,
        title: 'Pelatihan Drone Pupuk',
        description: 'Demo drone penyemprot pupuk cair.',
        category: 'workshop',
        eventDate: DateTime.now().add(const Duration(days: 3)),
        eventTime: '08:00 - 11:00 WIB',
        locationName: 'BPP Karawang',
        organizer: 'Dinas Pertanian',
        quota: 30,
        registeredCount: 5,
        isRegistered: true,
        ticketCode: 'TKT-PAD-101-0005',
        ticketStatus: 'active',
      );

      container.read(eventsProvider.notifier).updateEvent(sampleEvent);

      final result = await container.read(eventsProvider.notifier).registerForEvent(101);

      expect(result.success, isTrue);
      expect(result.alreadyRegistered, isTrue);
      expect(result.event?.ticketCode, equals('TKT-PAD-101-0005'));
    });
  });
}
