class AppNotificationModel {
  const AppNotificationModel({
    required this.id,
    this.userId,
    required this.type,
    required this.title,
    required this.body,
    this.data = const {},
    this.readAt,
    required this.isRead,
    this.createdAt,
  });

  factory AppNotificationModel.fromJson(Map<String, dynamic> json) {
    final rawData = json['data'] ?? json['data_json'];
    Map<String, dynamic> parsedData = {};
    if (rawData is Map) {
      parsedData = Map<String, dynamic>.from(rawData);
    }

    final readAtStr = json['read_at']?.toString();
    final isReadVal = json['is_read'] == true || readAtStr != null;

    return AppNotificationModel(
      id: _toInt(json['id']),
      userId: json['user_id'] != null ? _toInt(json['user_id']) : null,
      type: json['type']?.toString() ?? 'system',
      title: json['title']?.toString() ?? 'Notifikasi Baru',
      body: json['body']?.toString() ?? '',
      data: parsedData,
      readAt: readAtStr,
      isRead: isReadVal,
      createdAt: json['created_at']?.toString(),
    );
  }

  final int id;
  final int? userId;
  final String type;
  final String title;
  final String body;
  final Map<String, dynamic> data;
  final String? readAt;
  final bool isRead;
  final String? createdAt;

  String get categoryLabel {
    switch (type) {
      case 'role_rights':
        return 'Hak & Kewenangan';
      case 'order_status':
        return 'Pesanan & Kontrak';
      case 'logistics':
        return 'Logistik Truk';
      case 'crop_alert':
      case 'planting_reminder':
      case 'cultivation':
        return 'Budidaya & Jadwal';
      case 'warning':
      case 'early_warning':
      case 'disease_outbreak':
        return 'Radar & Peringatan';
      case 'marketplace_deal':
      case 'market_offer':
      case 'marketplace':
        return 'Pasar & Transaksi';
      case 'ppl_validation':
      case 'field_verification':
        return 'Validasi Lapangan';
      case 'system':
      default:
        return 'Informasi Sistem';
    }
  }

  AppNotificationModel copyWith({bool? isRead, String? readAt}) {
    return AppNotificationModel(
      id: id,
      userId: userId,
      type: type,
      title: title,
      body: body,
      data: data,
      readAt: readAt ?? this.readAt,
      isRead: isRead ?? this.isRead,
      createdAt: createdAt,
    );
  }
}

int _toInt(dynamic value) {
  if (value is num) return value.toInt();
  if (value is String) return int.tryParse(value.trim()) ?? 0;
  return 0;
}
