class EventModel {
  const EventModel({
    required this.id,
    required this.title,
    required this.description,
    required this.category,
    required this.eventDate,
    required this.eventTime,
    required this.locationName,
    this.locationAddress,
    this.isOnline = false,
    this.onlineUrl,
    required this.organizer,
    this.quota = 50,
    this.registeredCount = 0,
    this.priceType = 'free',
    this.price = 0,
    this.imageUrl,
    this.assetImage = 'assets/images/onboarding_1.jpeg',
    this.contactPerson,
    this.status = 'upcoming',
    this.source = 'official',
    this.approvalStatus = 'approved',
    this.rejectionReason,
    this.approvedAt,
    this.createdBy,
    this.speaker,
    this.isRegistered = false,
    this.isEventCreator = false,
    this.canRegister = true,
    this.ticketCode,
    this.ticketStatus,
    this.registeredAt,
  });

  final int id;
  final String title;
  final String description;
  final String category; // 'workshop', 'field_day', 'bazaar', 'webinar', 'irrigation'
  final DateTime eventDate;
  final String eventTime;
  final String locationName;
  final String? locationAddress;
  final bool isOnline;
  final String? onlineUrl;
  final String organizer;
  final int quota;
  final int registeredCount;
  final String priceType; // 'free', 'paid'
  final double price;
  final String? imageUrl;
  final String assetImage;
  final String? contactPerson;
  final String status; // 'upcoming', 'ongoing', 'completed', 'cancelled'
  final String source; // 'official', 'farmer_submission'
  final String approvalStatus; // 'pending', 'approved', 'rejected'
  final String? rejectionReason;
  final DateTime? approvedAt;
  final int? createdBy;
  final String? speaker;
  final bool isRegistered;
  final bool isEventCreator;
  final bool canRegister;
  final String? ticketCode;
  final String? ticketStatus;
  final DateTime? registeredAt;

  bool get isPending => approvalStatus == 'pending';
  bool get isApproved => approvalStatus == 'approved';
  bool get isRejected => approvalStatus == 'rejected';
  bool get isFarmerSubmission => source == 'farmer_submission';

  factory EventModel.fromJson(Map<String, dynamic> json) {
    DateTime parsedDate;
    try {
      parsedDate = DateTime.parse(json['event_date']?.toString() ?? '');
    } catch (_) {
      parsedDate = DateTime.now().add(const Duration(days: 3));
    }

    DateTime? parsedRegisteredAt;
    if (json['registered_at'] != null) {
      try {
        parsedRegisteredAt = DateTime.parse(json['registered_at'].toString());
      } catch (_) {}
    }

    DateTime? parsedApprovedAt;
    if (json['approved_at'] != null) {
      try {
        parsedApprovedAt = DateTime.parse(json['approved_at'].toString());
      } catch (_) {}
    }

    final categoryStr = json['category']?.toString() ?? 'workshop';
    String defaultAsset = 'assets/images/onboarding_1.jpeg';
    if (categoryStr == 'field_day') {
      defaultAsset = 'assets/images/onboarding_2.jpeg';
    } else if (categoryStr == 'bazaar') {
      defaultAsset = 'assets/images/onboarding_3.jpeg';
    }

    final isReg = json['is_registered'] == true;
    final idVal = _toInt(json['id']);
    final rawTicketCode = json['ticket_code']?.toString();
    final derivedTicketCode = (rawTicketCode != null && rawTicketCode.isNotEmpty)
        ? rawTicketCode
        : (isReg ? 'TKT-PAD-${idVal.toString().padLeft(3, '0')}-0921' : null);

    return EventModel(
      id: idVal,
      title: json['title']?.toString() ?? '',
      description: json['description']?.toString() ?? '',
      category: categoryStr,
      eventDate: parsedDate,
      eventTime: json['event_time']?.toString() ?? '08:30 - 12:30 WIB',
      locationName: json['location_name']?.toString() ?? 'Indramayu',
      locationAddress: json['location_address']?.toString(),
      isOnline: json['is_online'] == true || json['is_online'] == 1,
      onlineUrl: json['online_url']?.toString(),
      organizer: json['organizer']?.toString() ?? 'Dinas Pertanian',
      quota: _toInt(json['quota']) > 0 ? _toInt(json['quota']) : 50,
      registeredCount: _toInt(json['registered_count']),
      priceType: json['price_type']?.toString() ?? 'free',
      price: _toDouble(json['price']),
      imageUrl: json['image_url']?.toString(),
      assetImage: json['asset_image']?.toString().isNotEmpty == true
          ? json['asset_image'].toString()
          : defaultAsset,
      contactPerson: json['contact_person']?.toString(),
      status: json['status']?.toString() ?? 'upcoming',
      source: json['source']?.toString() ?? 'official',
      approvalStatus: json['approval_status']?.toString() ?? 'approved',
      rejectionReason: json['rejection_reason']?.toString(),
      approvedAt: parsedApprovedAt,
      createdBy: json['created_by'] != null ? _toInt(json['created_by']) : null,
      speaker: json['speaker']?.toString(),
      isRegistered: isReg,
      isEventCreator: json['is_event_creator'] == true,
      canRegister: json['can_register'] != false && json['can_register'] != 0,
      ticketCode: derivedTicketCode,
      ticketStatus: json['ticket_status']?.toString() ?? (isReg ? 'active' : null),
      registeredAt: parsedRegisteredAt ?? (isReg ? DateTime.now() : null),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'title': title,
      'description': description,
      'category': category,
      'event_date': '${eventDate.year.toString().padLeft(4, '0')}-${eventDate.month.toString().padLeft(2, '0')}-${eventDate.day.toString().padLeft(2, '0')}',
      'event_time': eventTime,
      'location_name': locationName,
      'location_address': locationAddress,
      'is_online': isOnline,
      'organizer': organizer,
      'speaker': speaker,
      'quota': quota,
      'price_type': priceType,
      'asset_image': assetImage,
      'contact_person': contactPerson,
    };
  }

  String get categoryLabel {
    return switch (category.toLowerCase()) {
      'workshop' => 'Pelatihan & Workshop',
      'field_day' => 'Sekolah Lapang',
      'bazaar' => 'Bazar & Pasar Tani',
      'webinar' => 'Webinar Tani',
      'irrigation' => 'Jadwal Gilir Air',
      _ => 'Agenda Tani',
    };
  }

  String get formattedDate {
    final months = [
      '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
      'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    final days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    final dayName = days[eventDate.weekday % 7];
    return '$dayName, ${eventDate.day} ${months[eventDate.month]} ${eventDate.year}';
  }

  String get countdownText {
    final now = DateTime.now();
    final today = DateTime(now.year, now.month, now.day);
    final evDate = DateTime(eventDate.year, eventDate.month, eventDate.day);
    final diff = evDate.difference(today).inDays;

    if (diff == 0) return 'Hari Ini';
    if (diff == 1) return 'Besok';
    if (diff > 1 && diff <= 7) return '$diff Hari Lagi';
    if (diff > 7) return '${(diff / 7).ceil()} Minggu Lagi';
    return 'Telah Selesai';
  }

  bool get isUpcoming => eventDate.isAfter(DateTime.now().subtract(const Duration(days: 1)));

  EventModel copyWith({
    int? id,
    String? title,
    String? description,
    String? category,
    DateTime? eventDate,
    String? eventTime,
    String? locationName,
    String? locationAddress,
    bool? isOnline,
    String? onlineUrl,
    String? organizer,
    int? quota,
    int? registeredCount,
    String? priceType,
    double? price,
    String? imageUrl,
    String? assetImage,
    String? contactPerson,
    String? status,
    String? source,
    String? approvalStatus,
    String? rejectionReason,
    DateTime? approvedAt,
    int? createdBy,
    String? speaker,
    bool? isRegistered,
    bool? isEventCreator,
    bool? canRegister,
    String? ticketCode,
    String? ticketStatus,
    DateTime? registeredAt,
  }) {
    return EventModel(
      id: id ?? this.id,
      title: title ?? this.title,
      description: description ?? this.description,
      category: category ?? this.category,
      eventDate: eventDate ?? this.eventDate,
      eventTime: eventTime ?? this.eventTime,
      locationName: locationName ?? this.locationName,
      locationAddress: locationAddress ?? this.locationAddress,
      isOnline: isOnline ?? this.isOnline,
      onlineUrl: onlineUrl ?? this.onlineUrl,
      organizer: organizer ?? this.organizer,
      quota: quota ?? this.quota,
      registeredCount: registeredCount ?? this.registeredCount,
      priceType: priceType ?? this.priceType,
      price: price ?? this.price,
      imageUrl: imageUrl ?? this.imageUrl,
      assetImage: assetImage ?? this.assetImage,
      contactPerson: contactPerson ?? this.contactPerson,
      status: status ?? this.status,
      source: source ?? this.source,
      approvalStatus: approvalStatus ?? this.approvalStatus,
      rejectionReason: rejectionReason ?? this.rejectionReason,
      approvedAt: approvedAt ?? this.approvedAt,
      createdBy: createdBy ?? this.createdBy,
      speaker: speaker ?? this.speaker,
      isRegistered: isRegistered ?? this.isRegistered,
      isEventCreator: isEventCreator ?? this.isEventCreator,
      canRegister: canRegister ?? this.canRegister,
      ticketCode: ticketCode ?? this.ticketCode,
      ticketStatus: ticketStatus ?? this.ticketStatus,
      registeredAt: registeredAt ?? this.registeredAt,
    );
  }
}

int _toInt(dynamic value) {
  if (value is num) return value.toInt();
  if (value is String) return int.tryParse(value.trim()) ?? 0;
  return 0;
}

double _toDouble(dynamic value) {
  if (value is num) return value.toDouble();
  if (value is String) return double.tryParse(value.trim().replaceAll(',', '.')) ?? 0.0;
  return 0.0;
}
