import 'dart:convert';

import 'package:flutter_secure_storage/flutter_secure_storage.dart';

class OfflineActivityQueueItem {
  const OfflineActivityQueueItem({
    required this.id,
    required this.createdAt,
    required this.payload,
  });

  final String id;
  final DateTime createdAt;
  final Map<String, dynamic> payload;

  Map<String, dynamic> toJson() => {
    'id': id,
    'created_at': createdAt.toIso8601String(),
    'payload': payload,
  };

  factory OfflineActivityQueueItem.fromJson(Map<String, dynamic> json) =>
      OfflineActivityQueueItem(
        id: json['id']?.toString() ?? '',
        createdAt:
            DateTime.tryParse(json['created_at']?.toString() ?? '') ??
            DateTime.now(),
        payload: Map<String, dynamic>.from(
          json['payload'] is Map
              ? json['payload'] as Map
              : const <String, dynamic>{},
        ),
      );
}

class OfflineActivityQueueService {
  const OfflineActivityQueueService({FlutterSecureStorage? storage})
    : _storage = storage ?? const FlutterSecureStorage();

  final FlutterSecureStorage _storage;

  static const String _storageKey = 'padi_offline_activity_queue';

  Future<List<OfflineActivityQueueItem>> getQueue() async {
    try {
      final raw = await _storage.read(key: _storageKey);
      if (raw == null || raw.isEmpty) {
        return const [];
      }

      final decoded = jsonDecode(raw);
      if (decoded is! List) {
        return const [];
      }

      return decoded
          .whereType<Map>()
          .map(
            (item) => OfflineActivityQueueItem.fromJson(
              Map<String, dynamic>.from(item),
            ),
          )
          .toList();
    } catch (_) {
      return const [];
    }
  }

  Future<void> enqueueActivity(Map<String, dynamic> payload) async {
    final queue = await getQueue();
    final item = OfflineActivityQueueItem(
      id: 'activity_${DateTime.now().millisecondsSinceEpoch}',
      createdAt: DateTime.now(),
      payload: payload,
    );

    queue.add(item);
    await _saveQueue(queue);
  }

  Future<void> removeFromQueue(String id) async {
    final queue = await getQueue();
    final nextQueue = queue.where((item) => item.id != id).toList();
    await _saveQueue(nextQueue);
  }

  Future<int> getPendingCount() async {
    final queue = await getQueue();
    return queue.length;
  }

  Future<void> _saveQueue(List<OfflineActivityQueueItem> queue) async {
    final raw = jsonEncode(queue.map((item) => item.toJson()).toList());

    await _storage.write(key: _storageKey, value: raw);
  }
}
