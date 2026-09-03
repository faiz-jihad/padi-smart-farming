import 'dart:convert';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class NotificationSettings {
  final bool pushEnabled;
  final bool pestAlerts;
  final bool plantingReminders;
  final bool marketPriceUpdates;
  final bool orderUpdates;

  const NotificationSettings({
    this.pushEnabled = true,
    this.pestAlerts = true,
    this.plantingReminders = true,
    this.marketPriceUpdates = true,
    this.orderUpdates = true,
  });

  NotificationSettings copyWith({
    bool? pushEnabled,
    bool? pestAlerts,
    bool? plantingReminders,
    bool? marketPriceUpdates,
    bool? orderUpdates,
  }) {
    return NotificationSettings(
      pushEnabled: pushEnabled ?? this.pushEnabled,
      pestAlerts: pestAlerts ?? this.pestAlerts,
      plantingReminders: plantingReminders ?? this.plantingReminders,
      marketPriceUpdates: marketPriceUpdates ?? this.marketPriceUpdates,
      orderUpdates: orderUpdates ?? this.orderUpdates,
    );
  }

  Map<String, dynamic> toJson() => {
        'pushEnabled': pushEnabled,
        'pestAlerts': pestAlerts,
        'plantingReminders': plantingReminders,
        'marketPriceUpdates': marketPriceUpdates,
        'orderUpdates': orderUpdates,
      };

  factory NotificationSettings.fromJson(Map<String, dynamic> json) {
    return NotificationSettings(
      pushEnabled: json['pushEnabled'] as bool? ?? true,
      pestAlerts: json['pestAlerts'] as bool? ?? true,
      plantingReminders: json['plantingReminders'] as bool? ?? true,
      marketPriceUpdates: json['marketPriceUpdates'] as bool? ?? true,
      orderUpdates: json['orderUpdates'] as bool? ?? true,
    );
  }
}

class NotificationSettingsNotifier extends Notifier<NotificationSettings> {
  static const _storageKey = 'padi_notification_settings';
  static const _storage = FlutterSecureStorage();

  @override
  NotificationSettings build() {
    _loadSettings();
    return const NotificationSettings();
  }

  Future<void> _loadSettings() async {
    try {
      final jsonStr = await _storage.read(key: _storageKey);
      if (jsonStr != null) {
        final map = jsonDecode(jsonStr) as Map<String, dynamic>;
        state = NotificationSettings.fromJson(map);
      }
    } catch (_) {}
  }

  Future<void> _saveSettings(NotificationSettings newSettings) async {
    state = newSettings;
    try {
      await _storage.write(
        key: _storageKey,
        value: jsonEncode(newSettings.toJson()),
      );
    } catch (_) {}
  }

  Future<void> togglePush(bool val) async {
    await _saveSettings(state.copyWith(pushEnabled: val));
  }

  Future<void> togglePestAlerts(bool val) async {
    await _saveSettings(state.copyWith(pestAlerts: val));
  }

  Future<void> togglePlantingReminders(bool val) async {
    await _saveSettings(state.copyWith(plantingReminders: val));
  }

  Future<void> toggleMarketPrice(bool val) async {
    await _saveSettings(state.copyWith(marketPriceUpdates: val));
  }

  Future<void> toggleOrderUpdates(bool val) async {
    await _saveSettings(state.copyWith(orderUpdates: val));
  }
}

final notificationSettingsProvider =
    NotifierProvider<NotificationSettingsNotifier, NotificationSettings>(() {
  return NotificationSettingsNotifier();
});
