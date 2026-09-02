import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:padi/features/event/data/models/event_model.dart';
import 'package:padi/features/event/presentation/widgets/event_ticket_dialog.dart';

void main() {
  testWidgets('EventTicketDialog renders ticket code, QR mockup, and event details', (tester) async {
    tester.view.physicalSize = const Size(800, 1600);
    tester.view.devicePixelRatio = 1.0;
    addTearDown(tester.view.resetPhysicalSize);

    final eventWithTicket = EventModel(
      id: 42,
      title: 'Pelatihan Smart Farming & Sensor Tanah',
      description: 'Pelatihan penggunaan sensor NPK digital untuk efisiensi pupuk.',
      category: 'workshop',
      eventDate: DateTime.now().add(const Duration(days: 4)),
      eventTime: '08:30 - 11:30 WIB',
      locationName: 'Balai Desa Lohbener',
      locationAddress: 'Jl. Lohbener Timur No. 10, Indramayu',
      organizer: 'P.A.D.I. AgriTech',
      quota: 40,
      registeredCount: 15,
      isRegistered: true,
      ticketCode: 'TKT-PAD-042-0016',
      ticketStatus: 'active',
    );

    await tester.pumpWidget(
      MaterialApp(
        home: Scaffold(
          body: Builder(
            builder: (context) => ElevatedButton(
              onPressed: () {
                EventTicketDialog.show(
                  context,
                  event: eventWithTicket,
                  attendeeName: 'Pak Tani Sudirman',
                );
              },
              child: const Text('Buka Tiket'),
            ),
          ),
        ),
      ),
    );

    await tester.pumpAndSettle();

    // Tap button to open dialog
    await tester.tap(find.text('Buka Tiket'));
    await tester.pumpAndSettle();

    // Verify ticket dialog contents
    expect(find.text('E-TIKET MASUK RESMI'), findsOneWidget);
    expect(find.text('AKTIF'), findsOneWidget);
    expect(find.text('TKT-PAD-042-0016'), findsOneWidget);
    expect(find.text('Pelatihan Smart Farming & Sensor Tanah'), findsOneWidget);
    expect(find.text('Pak Tani Sudirman'), findsOneWidget);
    expect(find.text('Salin Kode'), findsOneWidget);
  });
}
