import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:padi/features/event/data/models/event_model.dart';
import 'package:padi/features/event/data/providers/event_providers.dart';

void main() {
  group('EventsNotifier Idempotency & Ticketing Tests', () {
    test('registerForEvent increases quota only once and issues unique ticket', () async {
      final container = ProviderContainer();
      addTearDown(container.dispose);

      final initialEvents = container.read(eventsProvider);
      final targetEvent = initialEvents.first;
      final originalCount = targetEvent.registeredCount;

      expect(targetEvent.isRegistered, isFalse);
      expect(targetEvent.ticketCode, isNull);

      // Registration 1
      final registeredFirst = await container.read(eventsProvider.notifier).registerForEvent(targetEvent.id);
      expect(registeredFirst, isNotNull);
      expect(registeredFirst!.isRegistered, isTrue);
      expect(registeredFirst.registeredCount, originalCount + 1);
      expect(registeredFirst.ticketCode, startsWith('TKT-PAD-'));

      final stateAfterFirst = container.read(eventsProvider).firstWhere((e) => e.id == targetEvent.id);
      expect(stateAfterFirst.registeredCount, originalCount + 1);
      expect(stateAfterFirst.isRegistered, isTrue);
      expect(stateAfterFirst.ticketCode, isNotNull);

      // Registration 2 (Repeat call on same event)
      final registeredSecond = await container.read(eventsProvider.notifier).registerForEvent(targetEvent.id);
      expect(registeredSecond, isNotNull);

      // Quota MUST NOT increase again!
      final stateAfterSecond = container.read(eventsProvider).firstWhere((e) => e.id == targetEvent.id);
      expect(stateAfterSecond.registeredCount, originalCount + 1, reason: 'Registration count should NOT increment again');
      expect(stateAfterSecond.ticketCode, stateAfterFirst.ticketCode, reason: 'Ticket code should remain intact');
    });
  });
}
