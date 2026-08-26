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
    this.farmerName,
    this.diseaseName,
    this.imageUrl,
    this.reportedAt,
  });

  factory CommunityReportModel.fromJson(Map<String, dynamic> json) {
    return CommunityReportModel(
      id: _toInt(json['id']),
      scanId: _toInt(json['scan_id']),
      farmerId: _toInt(json['farmer_id']),
      farmerName: json['farmer_name']?.toString() ?? 'Petani Hamparan',
      diseaseName: json['disease_name']?.toString() ?? 'Penyakit Padi',
      imageUrl: json['image_url']?.toString(),
      latitude: _toDouble(json['latitude']),
      longitude: _toDouble(json['longitude']),
      radiusKm: _toDouble(json['radius_km']),
      consentGiven: json['consent_given'] == true,
      status: json['status']?.toString() ?? 'verified',
      reportedAt: json['reported_at']?.toString(),
    );
  }

  final int id;
  final int scanId;
  final int farmerId;
  final String? farmerName;
  final String? diseaseName;
  final String? imageUrl;
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
        return 'Aktif';
    }
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