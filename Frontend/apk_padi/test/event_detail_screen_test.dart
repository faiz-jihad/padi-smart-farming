import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:padi/features/event/data/models/event_model.dart';
import 'package:padi/features/event/presentation/screens/event_detail_screen.dart';

void main() {
  testWidgets('EventDetailScreen renders all sections without errors', (tester) async {
    tester.view.physicalSize = const Size(800, 1600);
    tester.view.devicePixelRatio = 1.0;
    addTearDown(tester.view.resetPhysicalSize);

    final sampleEvent = EventModel(
      id: 98,
      title: 'Bazar Tani & Temu Usaha Kemitraan',
      description: 'Pertemuan langsung antara petani dan pembeli off-taker gabah.',
      category: 'bazaar',
      eventDate: DateTime.now().add(const Duration(days: 12)),
      eventTime: '09:00 - 15:00 WIB',
      locationName: 'Pasar Induk Beras Sindang',
      locationAddress: 'Kawasan Agribisnis Terpadu Sindang, Indramayu',
      organizer: 'P.A.D.I. Marketplace & Koperasi Tani',
      quota: 120,
      registeredCount: 88,
      assetImage: 'assets/images/onboarding_3.jpeg',
      isRegistered: false,
    );

    await tester.pumpWidget(
      ProviderScope(
        child: MaterialApp(
          home: EventDetailScreen(event: sampleEvent),
        ),
      ),
    );

    await tester.pumpAndSettle();

    // Verify key titles and buttons render
    expect(find.text('Bazar & Pasar Tani'), findsOneWidget);
    expect(find.text('Bazar Tani & Temu Usaha Kemitraan'), findsOneWidget);
    expect(find.text('Pasar Induk Beras Sindang'), findsOneWidget);
    expect(find.text('Daftar Acara (Gratis)'), findsOneWidget);
    expect(find.text('Tentang Acara & Materi'), findsOneWidget);
  });

  testWidgets('EventDetailScreen renders E-Tiket button when already registered', (tester) async {
    final registeredEvent = EventModel(
      id: 99,
      title: 'Pelatihan Pupuk Hayati Mandiri',
      description: 'Pembuatan mikroorganisme lokal dan pupuk hayati cair.',
      category: 'workshop',
      eventDate: DateTime.now().add(const Duration(days: 5)),
      eventTime: '08:00 - 12:00 WIB',
      locationName: 'Balai Tani Sindang',
      organizer: 'Dinas Pertanian',
      quota: 50,
      registeredCount: 20,
      isRegistered: true,
      ticketCode: 'TKT-PAD-099-0021',
    );

    await tester.pumpWidget(
      ProviderScope(
        child: MaterialApp(
          home: EventDetailScreen(event: registeredEvent),
        ),
      ),
    );

    await tester.pumpAndSettle();

    expect(find.text('Lihat E-Tiket Saya'), findsOneWidget);
    expect(find.text('Anda Sudah Terdaftar pada Acara Ini'), findsOneWidget);
  });
}
