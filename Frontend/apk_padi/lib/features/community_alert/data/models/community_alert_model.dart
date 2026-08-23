class CommunityAlertModel {
  const CommunityAlertModel({
    required this.id,
    required this.title,
    required this.message,
    required this.type,
    required this.status,
    this.publishedAt,
    this.expiresAt,
  });

  factory CommunityAlertModel.fromJson(Map<String, dynamic> json) {
    return CommunityAlertModel(
      id: (json['id'] as num).toInt(),
      title: json['title']?.toString() ?? '',
      message: json['message']?.toString() ?? '',
      type: json['type']?.toString() ?? 'info',
      status: json['status']?.toString() ?? '',
      publishedAt: json['published_at']?.toString(),
      expiresAt: json['expires_at']?.toString(),
    );
  }

  final int id;
  final String title;
  final String message;
  final String type;
  final String status;
  final String? publishedAt;
  final String? expiresAt;

  String get typeLabel {
    switch (type) {
      case 'danger':
        return 'Bahaya';
      case 'warning':
        return 'Waspada';
      case 'info':
        return 'Informasi';
      default:
        return 'Peringatan';
    }
  }
}