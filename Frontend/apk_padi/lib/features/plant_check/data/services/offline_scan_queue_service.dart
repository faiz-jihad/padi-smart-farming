import 'dart:convert';
import 'dart:io';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:padi/features/plant_check/data/services/plant_check_api_service.dart';

final offlineScanQueueServiceProvider = Provider<OfflineScanQueueService>((ref) {
  return OfflineScanQueueService(
    storage: const FlutterSecureStorage(),
    apiService: ref.read(plantCheckApiServiceProvider),
  );
});

class OfflineScanQueueItem {
  const OfflineScanQueueItem({
    required this.id,
    required this.imagePath,
    required this.farmId,
    required this.createdAt,
    this.cropSeasonId,
    this.latitude,
    this.longitude,
    this.notes,
  });

  final String id;
  final String imagePath;
  final int farmId;
  final DateTime createdAt;
  final int? cropSeasonId;
  final double? latitude;
  final double? longitude;
  final String? notes;

  Map<String, dynamic> toJson() => {
        'id': id,
        'image_path': imagePath,
        'farm_id': farmId,
        'created_at': createdAt.toIso8601String(),
        'crop_season_id': cropSeasonId,
        'latitude': latitude,
        'longitude': longitude,
        'notes': notes,
      };

  factory OfflineScanQueueItem.fromJson(Map<String, dynamic> json) =>
      OfflineScanQueueItem(
        id: json['id']?.toString() ?? '',
        imagePath: json['image_path']?.toString() ?? '',
        farmId: (json['farm_id'] as num?)?.toInt() ?? 0,
        createdAt: DateTime.tryParse(json['created_at']?.toString() ?? '') ??
            DateTime.now(),
        cropSeasonId: (json['crop_season_id'] as num?)?.toInt(),
        latitude: (json['latitude'] as num?)?.toDouble(),
        longitude: (json['longitude'] as num?)?.toDouble(),
        notes: json['notes']?.toString(),
      );
}

class OfflineScanQueueService {
  const OfflineScanQueueService({
    required this.storage,
    required this.apiService,
  });

  final FlutterSecureStorage storage;
  final PlantCheckApiService apiService;

  static const String _storageKey = 'padi_offline_scan_queue';

  Future<List<OfflineScanQueueItem>> getQueue() async {
    try {
      final raw = await storage.read(key: _storageKey);
      if (raw == null || raw.isEmpty) return [];
      final list = jsonDecode(raw);
      if (list is! List) return [];
      return list
          .whereType<Map>()
          .map((e) => OfflineScanQueueItem.fromJson(Map<String, dynamic>.from(e)))
          .toList();
    } catch (_) {
      return [];
    }
  }

  Future<void> enqueueScan({
    required String imagePath,
    required int farmId,
    int? cropSeasonId,
    double? latitude,
    double? longitude,
    String? notes,
  }) async {
    final queue = await getQueue();
    final item = OfflineScanQueueItem(
      id: 'scan_${DateTime.now().millisecondsSinceEpoch}',
      imagePath: imagePath,
      farmId: farmId,
      cropSeasonId: cropSeasonId,
      latitude: latitude,
      longitude: longitude,
      notes: notes,
      createdAt: DateTime.now(),
    );
    queue.add(item);
    await _saveQueue(queue);
  }

  Future<void> removeFromQueue(String id) async {
    final queue = await getQueue();
    queue.removeWhere((item) => item.id == id);
    await _saveQueue(queue);
  }

  Future<int> getPendingCount() async {
    final queue = await getQueue();
    return queue.length;
  }

  Future<int> syncAll() async {
    final queue = await getQueue();
    if (queue.isEmpty) return 0;

    int successCount = 0;
    final List<OfflineScanQueueItem> remaining = [];

    for (final item in queue) {
      final file = File(item.imagePath);
      if (!file.existsSync()) {
        // Skip missing files
        continue;
      }

      try {
        await apiService.scanDisease(
          imagePath: item.imagePath,
          farmId: item.farmId,
          latitude: item.latitude,
          longitude: item.longitude,
        );
        successCount++;
      } catch (_) {
        // Keep in queue for next retry
        remaining.add(item);
      }
    }

    await _saveQueue(remaining);
    return successCount;
  }

  Future<void> _saveQueue(List<OfflineScanQueueItem> queue) async {
    final raw = jsonEncode(queue.map((e) => e.toJson()).toList());
    await storage.write(key: _storageKey, value: raw);
  }
}
