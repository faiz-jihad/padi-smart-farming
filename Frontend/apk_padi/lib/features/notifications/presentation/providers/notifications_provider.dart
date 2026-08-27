import 'dart:async';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:padi/core/providers/app_providers.dart';
import 'package:padi/features/notifications/data/models/app_notification_model.dart';
import 'package:padi/features/notifications/data/services/device_notification_service.dart';

// ── State ────────────────────────────────────────────────────
class NotificationsState {
  const NotificationsState({
    this.notifications = const [],
    this.isLoading = false,
    this.error,
  });

  final List<AppNotificationModel> notifications;
  final bool isLoading;
  final String? error;

  int get unreadCount => notifications.where((n) => !n.isRead).length;

  NotificationsState copyWith({
    List<AppNotificationModel>? notifications,
    bool? isLoading,
    String? error,
  }) {
    return NotificationsState(
      notifications: notifications ?? this.notifications,
      isLoading: isLoading ?? this.isLoading,
      error: error,
    );
  }
}

// ── Notifier (Riverpod 3.x Notifier API) ─────────────────────
class NotificationsNotifier extends Notifier<NotificationsState> {
  Timer? _pollTimer;

  @override
  NotificationsState build() {
    // Auto-cancel timer when provider is disposed
    ref.onDispose(() => _pollTimer?.cancel());

    final service = ref.watch(deviceNotificationServiceProvider);
    final isBuyer = ref.watch(isBuyerRoleProvider);

    // Start polling
    _pollTimer?.cancel();
    _pollTimer = Timer.periodic(const Duration(seconds: 60), (_) {
      _fetchNotifications(service, isBuyer);
    });

    // Fetch immediately on build
    Future.microtask(() => _fetchNotifications(service, isBuyer));

    return const NotificationsState();
  }

  Future<void> _fetchNotifications(
    DeviceNotificationService service,
    bool isBuyer,
  ) async {
    if (state.isLoading) return;
    state = state.copyWith(isLoading: true, error: null);
    try {
      final list = await service.fetchNotifications(
        role: isBuyer ? 'buyer' : 'farmer',
      );
      state = state.copyWith(notifications: list, isLoading: false);
    } catch (_) {
      state = state.copyWith(
        isLoading: false,
        error: 'Gagal memuat notifikasi.',
      );
    }
  }

  Future<void> refresh() async {
    final service = ref.read(deviceNotificationServiceProvider);
    final isBuyer = ref.read(isBuyerRoleProvider);
    await _fetchNotifications(service, isBuyer);
  }

  Future<void> markAsRead(int id) async {
    final service = ref.read(deviceNotificationServiceProvider);
    await service.markAsRead(id);
    state = state.copyWith(
      notifications: state.notifications
          .map((n) => n.id == id ? n.copyWith(isRead: true) : n)
          .toList(),
    );
  }

  Future<void> markAllAsRead() async {
    final service = ref.read(deviceNotificationServiceProvider);
    await service.markAllAsRead();
    state = state.copyWith(
      notifications:
          state.notifications.map((n) => n.copyWith(isRead: true)).toList(),
    );
  }
}

// ── Provider ─────────────────────────────────────────────────
final notificationsProvider =
    NotifierProvider<NotificationsNotifier, NotificationsState>(
  NotificationsNotifier.new,
);

/// Shortcut: just the unread count.
final unreadNotificationCountProvider = Provider<int>((ref) {
  return ref.watch(notificationsProvider).unreadCount;
});
