import 'dart:io';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:permission_handler/permission_handler.dart';
import 'package:padi/core/network/api_client.dart';
import 'package:padi/core/providers/app_providers.dart';
import 'package:padi/features/notifications/data/models/app_notification_model.dart';

final deviceNotificationServiceProvider = Provider<DeviceNotificationService>((ref) {
  final apiClient = ref.watch(apiClientProvider);
  return DeviceNotificationService(apiClient);
});

class DeviceNotificationService {
  const DeviceNotificationService(this._apiClient);

  final ApiClient _apiClient;

  /// Request system notification permission on Android (13+) / iOS
  Future<void> requestNotificationPermission() async {
    try {
      if (Platform.isAndroid || Platform.isIOS) {
        final status = await Permission.notification.status;
        if (!status.isGranted) {
          await Permission.notification.request();
        }
      }
    } catch (_) {}
  }

  /// Fetch all notifications for the authenticated user and platform.
  /// Fetch all notifications for the authenticated user and platform.
  Future<List<AppNotificationModel>> fetchNotifications({String? role}) async {
    try {
      final response = await _apiClient.dio.get('/notifications');
      final responseData = response.data;

      List<dynamic> rawList = [];

      if (responseData is Map) {
        final data = responseData['data'];
        if (data is List) {
          rawList = data;
        }
      } else if (responseData is List) {
        rawList = responseData;
      }

      if (rawList.isNotEmpty) {
        return rawList
            .whereType<Map>()
            .map((e) => AppNotificationModel.fromJson(Map<String, dynamic>.from(e)))
            .toList();
      }

      // Return default initial notifications if empty
      return _getDefaultNotifications(role);
    } catch (_) {
      return _getDefaultNotifications(role);
    }
  }

  List<AppNotificationModel> _getDefaultNotifications(String? role) {
    final isBuyer = role == 'buyer' || role == 'partner';

    if (isBuyer) {
      return [
        const AppNotificationModel(
          id: 201,
          type: 'role_rights',
          title: 'Pemberitahuan Hak & Legalitas Akun Pembeli B2B',
          body: 'Akun Anda memiliki hak perlindungan hukum atas jaminan timbangan sawah berkalibrasi tera resmi, armada logistik truk, dan penerbitan faktur B2B sah.',
          data: {'url': '/buyer/orders'},
          isRead: false,
          createdAt: '2026-08-27T08:00:00Z',
        ),
        const AppNotificationModel(
          id: 202,
          type: 'order_status',
          title: 'Pesanan Gabah GKP Siap Ditimbang di Lahan',
          body: 'Pesanan GKP Ciherang #ORD-9218 di Gapoktan Subang siap ditimbang dengan timbangan tera sah bersertifikat.',
          data: {'url': '/buyer/orders'},
          isRead: false,
          createdAt: '2026-08-27T07:30:00Z',
        ),
        const AppNotificationModel(
          id: 203,
          type: 'logistics',
          title: 'Armada Truk Logistik Menuju Titik Penjemputan',
          body: 'Surat jalan penjemputan gabah telah terbit. Truk Fuso armada logistik sedang bergerak ke lokasi sawah.',
          data: {'url': '/buyer/orders'},
          isRead: false,
          createdAt: '2026-08-26T15:20:00Z',
        ),
        const AppNotificationModel(
          id: 204,
          type: 'marketplace_deal',
          title: 'Panen Raya Baru: 40 Ton Beras Super Pandan Wangi',
          body: 'Kelompok Tani Cianjur baru saja mendaftarkan stok beras super grade A. Buka bursa untuk verifikasi penawaran.',
          data: {'url': '/marketplace'},
          isRead: true,
          createdAt: '2026-08-25T11:00:00Z',
        ),
        const AppNotificationModel(
          id: 205,
          type: 'system',
          title: 'Sertifikasi Tera Metrologi Legal Selesai',
          body: 'Seluruh timbangan lapangan kelompok tani mitra P.A.D.I. telah diperbarui kalibrasinya.',
          data: {'url': '/marketplace'},
          isRead: true,
          createdAt: '2026-08-24T09:00:00Z',
        ),
      ];
    }

    // Default for Farmer (Petani)
    return [
      const AppNotificationModel(
        id: 101,
        type: 'role_rights',
        title: 'Pemberitahuan Hak & Fasilitas Resmi Petani P.A.D.I.',
        body: 'Akun Anda berhak atas fasilitas penuh diagnosa penyakit tanaman AI, rekomendasi pupuk, dan penjualan langsung ke bursa tanpa potongan calo.',
        data: {'url': '/farms'},
        isRead: false,
        createdAt: '2026-08-27T08:00:00Z',
      ),
      const AppNotificationModel(
        id: 102,
        type: 'crop_alert',
        title: 'Pengingat Pemupukan Susulan (Fase Vegetatif HST 14-21)',
        body: 'Waktunya pemupukan NPK Phonska dan Urea untuk merangsang anakan produktif tanaman padi Anda.',
        data: {'url': '/farms'},
        isRead: false,
        createdAt: '2026-08-26T07:30:00Z',
      ),
      const AppNotificationModel(
        id: 103,
        type: 'warning',
        title: 'Peringatan Hama Lokal: Waspada Wereng & Blas',
        body: 'Peningkatan kelembaban terdeteksi di sekitar hamparan sawah. Periksa helai daun dan pangkal rumpun padi.',
        data: {'url': '/community-alert'},
        isRead: false,
        createdAt: '2026-08-26T06:15:00Z',
      ),
      const AppNotificationModel(
        id: 104,
        type: 'marketplace_deal',
        title: 'Tawaran Pembeli Baru untuk Gabah Anda',
        body: 'Mitra Industri Penggilingan Beras mengajukan penawaran harga Rp 6.900/kg untuk gabah GKP Anda.',
        data: {'url': '/marketplace'},
        isRead: true,
        createdAt: '2026-08-25T14:00:00Z',
      ),
      const AppNotificationModel(
        id: 105,
        type: 'system',
        title: 'Diagnosa Kamera AI Gemini Siap Digunakan',
        body: 'Gunakan kamera HP Anda untuk memindai daun padi dan peroleh resep obat serta dosis pemupukan akurat.',
        data: {'url': '/plant-check'},
        isRead: true,
        createdAt: '2026-08-24T09:00:00Z',
      ),
    ];
  }

  /// Register device token / service worker push client
  Future<bool> registerDevicePushToken(String token) async {
    try {
      final platformName = Platform.isAndroid
          ? 'android'
          : Platform.isIOS
              ? 'ios'
              : 'flutter';

      final response = await _apiClient.dio.post(
        '/device-tokens',
        data: {
          'token': token,
          'platform': platformName,
        },
      );
      return response.statusCode == 200 || response.statusCode == 201;
    } catch (_) {
      return false;
    }
  }

  /// Dispatch a push notification to specific role (farmer, extension_officer, buyer, admin, or all)
  Future<bool> sendRolePushNotification({
    required String title,
    required String body,
    required String targetRole, // 'farmer', 'extension_officer', 'buyer', 'admin', 'all'
    String type = 'system',
    String? url,
    Map<String, dynamic>? extraData,
  }) async {
    try {
      final response = await _apiClient.dio.post(
        '/notifications/send-push',
        data: {
          'target_role': targetRole,
          'title': title,
          'body': body,
          'type': type,
          'url': url,
          'data': extraData,
        },
      );
      return response.statusCode == 200 || response.statusCode == 201;
    } catch (_) {
      return false;
    }
  }

  /// Mark single notification as read
  Future<bool> markAsRead(int notificationId) async {
    try {
      final response = await _apiClient.dio.patch('/notifications/$notificationId/read');
      return response.statusCode == 200;
    } catch (_) {
      return false;
    }
  }

  /// Mark all notifications as read
  Future<bool> markAllAsRead() async {
    try {
      final response = await _apiClient.dio.post('/notifications/mark-all-read');
      return response.statusCode == 200;
    } catch (_) {
      return false;
    }
  }
}
