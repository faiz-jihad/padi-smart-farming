class CommunityReportModel {
  const CommunityReportModel({
    required this.id,
    required this.scanId,
    required this.farmerId,
    required this.latitude,
    required this.longitude,
    required this.radiusKm,
    required this.consentGiven,
    required this.status,
    required this.reportedAt,
  });

  factory CommunityReportModel.fromJson(Map<String, dynamic> json) {
    return CommunityReportModel(
      id: (json['id'] as num?)?.toInt() ?? 0,
      scanId: (json['scan_id'] as num?)?.toInt() ?? 0,
      farmerId: (json['farmer_id'] as num?)?.toInt() ?? 0,
      latitude: (json['latitude'] as num?)?.toDouble() ?? 0,
      longitude: (json['longitude'] as num?)?.toDouble() ?? 0,
      radiusKm: (json['radius_km'] as num?)?.toDouble() ?? 0,
      consentGiven: json['consent_given'] as bool? ?? false,
      status: json['status']?.toString() ?? 'pending',
      reportedAt: json['reported_at']?.toString(),
    );
  }

  final int id;
  final int scanId;
  final int farmerId;
  final double latitude;
  final double longitude;
  final double radiusKm;
  final bool consentGiven;
  final String status;
  final String? reportedAt;

  String get statusLabel {
    switch (status) {
      case 'pending':
        return 'Menunggu verifikasi';
      case 'verified':
        return 'Terverifikasi';
      case 'rejected':
        return 'Ditolak';
      default:
        return status;
    }
  }
}