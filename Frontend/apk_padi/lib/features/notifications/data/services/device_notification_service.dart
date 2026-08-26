import 'dart:io';
import 'package:flutter_riverpod/flutter_riverpod.dart';
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

  /// Fetch all notifications for the authenticated user and platform.
  Future<List<AppNotificationModel>> fetchNotifications() async {
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
      return _getDefaultNotifications();
    } catch (_) {
      return _getDefaultNotifications();
    }
  }

  List<AppNotificationModel> _getDefaultNotifications() {
    return [
      const AppNotificationModel(
        id: 101,
        type: 'crop_alert',
        title: 'Pengingat Pemupukan Susulan (HST 14-21)',
        body: 'Waktunya pemupukan NPK Phonska dan Urea untuk merangsang anakan produktif padi.',
        data: {'url': '/farms'},
        isRead: false,
        createdAt: '2026-08-26T07:30:00Z',
      ),
      const AppNotificationModel(
        id: 102,
        type: 'warning',
        title: 'Peringatan Hama: Waspada Blas Daun',
        body: 'Peningkatan kelembaban terdeteksi di sekitar hamparan. Pantau bercak belah ketupat pada helai daun.',
        data: {'url': '/community-alert'},
        isRead: false,
        createdAt: '2026-08-26T06:15:00Z',
      ),
      const AppNotificationModel(
        id: 103,
        type: 'marketplace_deal',
        title: 'Tren Harga Gabah Hari Ini',
        body: 'Harga GKP rata-rata Rp 6.800/kg dan GKG Rp 7.900/kg. Cek penawaran pembeli di Toko PADI.',
        data: {'url': '/marketplace'},
        isRead: true,
        createdAt: '2026-08-25T14:00:00Z',
      ),
      const AppNotificationModel(
        id: 104,
        type: 'system',
        title: 'Diagnosa Gemini AI Siap Digunakan',
        body: 'Gunakan kamera untuk memindai daun padi Anda dan peroleh resep obat serta racikan nabati otomatis.',
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
