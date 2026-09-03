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
  static final List<EventModel> _initialEvents = [
    EventModel(
      id: 1,
      title: 'Workshop Pemupukan Berimbang & Uji Cepat Hara Tanah',
      description:
          'Pelatihan langsung bagi petani untuk menguji kadar NPK dan pH tanah sawah secara cepat dengan alat uji tanah sawah (PUTS) serta panduan pemupukan presisi berbasis aplikasi P.A.D.I. Peserta akan mendapatkan sampel pupuk organik dan sertifikat keikutsertaan.',
      category: 'workshop',
      eventDate: DateTime.now().add(const Duration(days: 3)),
      eventTime: '08:30 - 12:30 WIB',
      locationName: 'Balai Penyuluhan Pertanian (BPP) Karangampel',
      locationAddress: 'Jl. Raya Karangampel No. 45, Karangampel, Indramayu',
      isOnline: false,
      organizer: 'Dinas Ketahanan Pangan & Pertanian Indramayu',
      speaker: 'Dr. Ir. Hendro Wibowo (Pakar Agronomi IPB)',
      quota: 60,
      registeredCount: 42,
      priceType: 'free',
      assetImage: 'assets/images/onboarding_1.jpeg',
      contactPerson: '0812-3456-7890 (Pak Sugeng - Koordinator BPP)',
      status: 'upcoming',
    ),
    EventModel(
      id: 2,
      title: 'Sekolah Lapang: Deteksi Dini Penyakit & Pengendalian Wereng',
      description:
          'Praktik lapangan identifikasi gejala serangan wereng batang coklat (WBC) dan blas daun padi menggunakan kamera AI. Diskusi strategi pengendalian hayati terpadu tanpa merusak musuh alami tanaman.',
      category: 'field_day',
      eventDate: DateTime.now().add(const Duration(days: 7)),
      eventTime: '07:30 - 11:30 WIB',
      locationName: 'Hamparan Sawah Gapoktan Sri Rejeki',
      locationAddress: 'Desa Jatibarang Baru, Kec. Jatibarang, Indramayu',
      isOnline: false,
      organizer: 'POPT Balai Proteksi Tanaman Pangan & Hortikultura',
      speaker: 'H. Suwandi, S.P. (POPT Ahli Muda)',
      quota: 50,
      registeredCount: 35,
      priceType: 'free',
      assetImage: 'assets/images/onboarding_2.jpeg',
      contactPerson: '0857-9876-5432 (Ibu Ratna - PPL Wilayah)',
      status: 'upcoming',
    ),
    EventModel(
      id: 3,
      title: 'Bazar Tani & Temu Usaha Kemitraan Pembeli Gabah Panen Raya',
      description:
          'Pertemuan langsung antara gabungan kelompok tani (Gapoktan) produsen gabah kualitas premium dengan mitra penggilingan modern, Bulog, dan pembeli off-taker. Dapatkan kepastian harga beli di atas HPP resmi.',
      category: 'bazaar',
      eventDate: DateTime.now().add(const Duration(days: 12)),
      eventTime: '09:00 - 15:00 WIB',
      locationName: 'Pasar Induk Beras & Gedung Pertemuan Pertanian',
      locationAddress: 'Kawasan Agribisnis Terpadu Sindang, Indramayu',
      isOnline: false,
      organizer: 'P.A.D.I. Marketplace & Koperasi Tani Makmur',
      speaker: 'Direktur Pengadaan Pangan Bulog & Asosiasi Penggilingan Padi',
      quota: 120,
      registeredCount: 88,
      priceType: 'free',
      assetImage: 'assets/images/onboarding_3.jpeg',
      contactPerson: '0821-1122-3344 (Admin Marketplace PADI)',
      status: 'upcoming',
    ),
    EventModel(
      id: 4,
      title: 'Sosialisasi Jadwal Gilir Air Irigasi MT-II & Mitigasi Kemarau',
      description:
          'Musyawarah pembagian debit air saluran irigasi primer dan sekunder Rentang untuk memastikan ketersediaan pasokan air sawah merata di seluruh petak hilir selama masa bunting.',
      category: 'irrigation',
      eventDate: DateTime.now().add(const Duration(days: 18)),
      eventTime: '13:30 - 16:30 WIB',
      locationName: 'Kantor Balai Besar Wilayah Sungai (BBWS) Cimanuk',
      locationAddress: 'Jl. Ahmad Yani No. 12, Indramayu',
      isOnline: false,
      organizer: 'Perkumpulan Petani Pemakai Air (P3A) Mitra Cai',
      speaker: 'Koordinator Pengamat Pengairan Rentang',
      quota: 80,
      registeredCount: 54,
      priceType: 'free',
      assetImage: 'assets/images/splash_background.jpeg',
      contactPerson: '0813-4455-6677 (Bpk. H. Ahmad - Ketua P3A)',
      status: 'upcoming',
    ),
  ];

  @override
  List<EventModel> build() {
    // Automatically load from backend in background
    unawaited(loadEventsFromApi());
    return _initialEvents;
  }

  Future<void> loadEventsFromApi() async {
    try {
      final service = ref.read(eventApiServiceProvider);
      final apiEvents = await service.fetchEvents();
      if (apiEvents.isNotEmpty) {
        state = apiEvents;
      }
    } catch (_) {}
  }

  Future<void> addEvent(EventModel event) async {
    state = [event, ...state];
    try {
      final service = ref.read(eventApiServiceProvider);
      final created = await service.createEvent(event);
      if (created != null) {
        state = [
          created,
          ...state.where((e) => e.title != event.title),
        ];
      }
    } catch (_) {}
  }

  void updateEvent(EventModel updatedEvent) {
    state = [
      for (final event in state)
        if (event.id == updatedEvent.id) updatedEvent else event,
    ];
  }

  Future<EventModel?> registerForEvent(int eventId) async {
    // Cari event sasaran
    final existingIndex = state.indexWhere((e) => e.id == eventId);
    if (existingIndex == -1) return null;

    final targetEvent = state[existingIndex];

    // Jika sudah terdaftar, jangan lakukan penambahan kuota berulang!
    if (targetEvent.isRegistered) {
      return targetEvent;
    }

    // Generate kode tiket deterministik/unik
    final ticketNumber = (targetEvent.registeredCount + 1).toString().padLeft(4, '0');
    final generatedCode = 'TKT-PAD-${eventId.toString().padLeft(3, '0')}-$ticketNumber';

    final optimisticEvent = targetEvent.copyWith(
      registeredCount: targetEvent.registeredCount + 1,
      isRegistered: true,
      ticketCode: targetEvent.ticketCode ?? generatedCode,
      ticketStatus: 'active',
      registeredAt: DateTime.now(),
    );

    // Update state secara optimis
    state = [
      for (final event in state)
        if (event.id == eventId) optimisticEvent else event,
    ];

    try {
      final service = ref.read(eventApiServiceProvider);
      final apiUpdatedEvent = await service.registerForEvent(eventId);
      if (apiUpdatedEvent != null) {
        state = [
          for (final event in state)
            if (event.id == eventId)
              apiUpdatedEvent.copyWith(
                ticketCode: apiUpdatedEvent.ticketCode ?? optimisticEvent.ticketCode,
                ticketStatus: apiUpdatedEvent.ticketStatus ?? optimisticEvent.ticketStatus,
                registeredAt: apiUpdatedEvent.registeredAt ?? optimisticEvent.registeredAt,
                isRegistered: true,
              )
            else
              event,
        ];
        return apiUpdatedEvent;
      }
    } catch (_) {}

    return optimisticEvent;
  }
}
