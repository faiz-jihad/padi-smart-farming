import 'dart:async';
import 'dart:convert';
import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:padi/core/config/app_config.dart';
import 'package:padi/core/providers/app_providers.dart';
import 'package:padi/features/notifications/data/models/app_notification_model.dart';

/// App key configured in Laravel Reverb (.env)
const String kReverbAppKey = 'klsd3w5iuxsesicvziis';
const int kReverbPort = 8080;

final reverbWebSocketServiceProvider = Provider<ReverbWebSocketService>((ref) {
  final isBuyer = ref.watch(isBuyerRoleProvider);
  final role = isBuyer ? 'buyer' : 'farmer';
  final service = ReverbWebSocketService(role: role);

  // Connect on provider initialization
  service.connect();

  ref.onDispose(() {
    service.dispose();
  });

  return service;
});

class ReverbWebSocketService {
  ReverbWebSocketService({
    this.role = 'farmer',
    this.userId,
  });

  final String role;
  final int? userId;

  WebSocket? _socket;
  Timer? _reconnectTimer;
  Timer? _pingTimer;
  bool _isDisposed = false;
  bool _isConnected = false;
  int _reconnectAttempts = 0;

  final _notificationController = StreamController<AppNotificationModel>.broadcast();
  final _disasterAlertController = StreamController<Map<String, dynamic>>.broadcast();
  final _connectionStateController = StreamController<bool>.broadcast();

  Stream<AppNotificationModel> get notificationStream => _notificationController.stream;
  Stream<Map<String, dynamic>> get disasterAlertStream => _disasterAlertController.stream;
  Stream<bool> get connectionStateStream => _connectionStateController.stream;

  bool get isConnected => _isConnected;

  /// Connect to the Laravel Reverb WebSocket server
  Future<void> connect() async {
    if (_isDisposed) return;
    if (_isConnected && _socket != null) return;

    if (kIsWeb) {
      debugPrint('[Reverb WS] Platform Web terdeteksi. WebSocket dart:io dinonaktifkan di browser untuk mencegah Unsupported operation.');
      return;
    }

    _reconnectTimer?.cancel();

    final host = AppConfig.activeHost;
    final uri = Uri.parse(
      'ws://$host:$kReverbPort/app/$kReverbAppKey?protocol=7&client=js&version=8.4.0-reverb&flash=false',
    );

    debugPrint('[Reverb WS] Menghubungkan ke $uri...');

    try {
      _socket = await WebSocket.connect(
        uri.toString(),
      ).timeout(const Duration(seconds: 8));

      _isConnected = true;
      _reconnectAttempts = 0;
      _connectionStateController.add(true);
      debugPrint('[Reverb WS] Terhubung ke Laravel Reverb WebSocket!');

      _socket!.listen(
        _onMessage,
        onError: _onError,
        onDone: _onDone,
        cancelOnError: true,
      );

      // Heartbeat check every 30s
      _pingTimer?.cancel();
      _pingTimer = Timer.periodic(const Duration(seconds: 30), (_) {
        _sendPing();
      });
    } on UnsupportedError catch (e) {
      debugPrint('[Reverb WS] WebSocket tidak didukung di platform ini: $e. Reconnect dibatalkan.');
      _connectionStateController.add(false);
    } catch (e) {
      if (e.toString().contains('Unsupported operation') || e.toString().contains('Platform._version')) {
        debugPrint('[Reverb WS] WebSocket tidak didukung di platform ini: $e. Reconnect dibatalkan.');
        _connectionStateController.add(false);
        return;
      }
      debugPrint('[Reverb WS] Gagal konek ke Reverb: $e');
      _scheduleReconnect();
    }
  }

  void _onMessage(dynamic rawData) {
    if (rawData is! String) return;

    try {
      final Map<String, dynamic> message = jsonDecode(rawData);
      final event = message['event']?.toString() ?? '';
      final channel = message['channel']?.toString() ?? '';
      final dynamic dataField = message['data'];

      // Parse nested JSON if dataField is a string
      Map<String, dynamic> data = {};
      if (dataField is String) {
        try {
          final decoded = jsonDecode(dataField);
          if (decoded is Map) {
            data = Map<String, dynamic>.from(decoded);
          }
        } catch (_) {}
      } else if (dataField is Map) {
        data = Map<String, dynamic>.from(dataField);
      }

      // Handle Pusher protocol lifecycle
      if (event == 'pusher:connection_established') {
        debugPrint('[Reverb WS] Handshake berhasil. Mendaftarkan channel notifikasi...');
        _subscribeToChannels();
        return;
      }

      if (event == 'pusher:ping') {
        _sendPong();
        return;
      }

      // Handle notification events
      if (event == 'admin.notification.created' ||
          event == 'notification.created' ||
          channel.startsWith('notifications.')) {
        debugPrint('[Reverb WS] Menerima notifikasi baru via Reverb: ${data['title']}');
        final notification = AppNotificationModel.fromJson(data);
        _notificationController.add(notification);
        return;
      }

      // Handle disaster alerts
      if (event == 'disaster.alert' || channel == 'disaster-alerts') {
        debugPrint('[Reverb WS] Menerima Peringatan Dini Bencana/Hama: $data');
        _disasterAlertController.add(data);
        // Also map to notification if structured
        final alertMap = data['alert'] is Map ? Map<String, dynamic>.from(data['alert']) : data;
        final notification = AppNotificationModel(
          id: DateTime.now().millisecondsSinceEpoch ~/ 1000,
          type: 'warning',
          title: alertMap['title']?.toString() ?? 'Peringatan Dini Wilayah',
          body: alertMap['message']?.toString() ?? alertMap['body']?.toString() ?? 'Waspada potensi bahaya pada tanaman padi Anda.',
          data: alertMap,
          isRead: false,
          createdAt: DateTime.now().toIso8601String(),
        );
        _notificationController.add(notification);
        return;
      }
    } catch (e) {
      debugPrint('[Reverb WS] Error memproses pesan WS: $e');
    }
  }

  /// Subscribe to public broadcast, role-based, and user-specific channels
  void _subscribeToChannels() {
    // 1. General public broadcast for all farmers & users
    _sendSubscribe('notifications.broadcast');

    // 2. Early Warning & Disaster channel
    _sendSubscribe('disaster-alerts');

    // 3. Role-based channel (notifications.role.farmer, notifications.role.buyer, etc.)
    _sendSubscribe('notifications.role.$role');

    // 4. User-specific channel if logged in
    if (userId != null) {
      _sendSubscribe('notifications.user.$userId');
    }
  }

  void _sendSubscribe(String channelName) {
    _send({
      'event': 'pusher:subscribe',
      'data': {
        'channel': channelName,
      },
    });
    debugPrint('[Reverb WS] Subscribed to channel: $channelName');
  }

  void _sendPing() {
    _send({
      'event': 'pusher:ping',
      'data': {},
    });
  }

  void _sendPong() {
    _send({
      'event': 'pusher:pong',
      'data': {},
    });
  }

  void _send(Map<String, dynamic> payload) {
    if (_socket != null && _isConnected) {
      try {
        _socket!.add(jsonEncode(payload));
      } catch (_) {}
    }
  }

  void _onError(dynamic error) {
    debugPrint('[Reverb WS] Socket error: $error');
    _handleDisconnect();
  }

  void _onDone() {
    debugPrint('[Reverb WS] Socket disconnected.');
    _handleDisconnect();
  }

  void _handleDisconnect() {
    _isConnected = false;
    _connectionStateController.add(false);
    _pingTimer?.cancel();
    _socket = null;
    _scheduleReconnect();
  }

  void _scheduleReconnect() {
    if (_isDisposed) return;
    _reconnectTimer?.cancel();

    _reconnectAttempts++;
    final delaySeconds = (_reconnectAttempts * 3).clamp(3, 20);

    debugPrint('[Reverb WS] Menjadwalkan reconnect dalam $delaySeconds detik (Percobaan ke-$_reconnectAttempts)...');
    _reconnectTimer = Timer(Duration(seconds: delaySeconds), () {
      connect();
    });
  }

  void dispose() {
    _isDisposed = true;
    _reconnectTimer?.cancel();
    _pingTimer?.cancel();
    _socket?.close();
    _socket = null;
    _notificationController.close();
    _disasterAlertController.close();
    _connectionStateController.close();
  }
}
