import 'dart:convert';
import 'dart:math' as math;

import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:padi/features/auth/domain/entities/app_user.dart';

class LocalFaceAuthStore {
  const LocalFaceAuthStore({FlutterSecureStorage? storage})
    : _storage = storage ?? const FlutterSecureStorage();

  static const _profileKey = 'padi_face_local_profile';
  static const _threshold = 0.46;
  static const _faceOnlyThreshold = 0.44;

  final FlutterSecureStorage _storage;

  Future<void> saveEnrollment({
    String? phone,
    String? pin,
    required AppUser user,
    required List<List<double>> descriptors,
  }) async {
    if (descriptors.isEmpty) {
      return;
    }

    final cleanedDescriptors = descriptors
        .where((descriptor) => descriptor.length == 128)
        .map(
          (descriptor) => descriptor.map((value) => value.toDouble()).toList(),
        )
        .toList(growable: false);

    if (cleanedDescriptors.isEmpty) return;

    await _storage.write(
      key: _profileKey,
      value: jsonEncode({
        'phone': _normalizePhone(phone ?? user.phone ?? ''),
        'pin': pin?.trim() ?? '',
        'descriptors': cleanedDescriptors,
        'user': {
          'id': user.id,
          'name': user.name,
          'email': user.email,
          'phone': user.phone,
          'role': user.role,
          'status': user.status,
          'role_label': user.roleLabel,
          'status_label': user.statusLabel,
        },
        'saved_at': DateTime.now().toIso8601String(),
      }),
    );
  }

  Future<AppUser?> match({
    required String phone,
    required String pin,
    required List<double> descriptor,
  }) async {
    final profile = await _readProfile();
    if (profile == null || descriptor.length != 128) return null;
    if (profile.phone.isNotEmpty && profile.phone != _normalizePhone(phone)) {
      return null;
    }
    if (profile.pin.isNotEmpty && profile.pin != pin.trim()) return null;

    var bestDistance = double.infinity;
    for (final savedDescriptor in profile.descriptors) {
      final distance = _euclideanDistance(savedDescriptor, descriptor);
      if (distance < bestDistance) bestDistance = distance;
    }

    return bestDistance <= _threshold ? profile.user : null;
  }

  Future<AppUser?> matchFaceOnly({required List<double> descriptor}) async {
    final profile = await _readProfile();
    if (profile == null || descriptor.length != 128) return null;

    var bestDistance = double.infinity;
    for (final savedDescriptor in profile.descriptors) {
      final distance = _euclideanDistance(savedDescriptor, descriptor);
      if (distance < bestDistance) bestDistance = distance;
    }

    return bestDistance <= _faceOnlyThreshold ? profile.user : null;
  }

  Future<AppUser?> readCachedUser() async {
    final profile = await _readProfile();
    return profile?.user;
  }

  Future<bool> hasEnrollment() async => (await _readProfile()) != null;

  Future<_LocalFaceProfile?> _readProfile() async {
    final raw = await _storage.read(key: _profileKey);
    if (raw == null || raw.isEmpty) return null;

    try {
      final json = jsonDecode(raw) as Map<String, dynamic>;
      final rawDescriptors = json['descriptors'];
      final rawUser = json['user'];

      if (rawDescriptors is! List || rawUser is! Map<String, dynamic>) {
        return null;
      }

      final descriptors = rawDescriptors
          .whereType<List>()
          .map(
            (item) => item
                .whereType<num>()
                .map((value) => value.toDouble())
                .toList(growable: false),
          )
          .where((item) => item.length == 128)
          .toList(growable: false);

      if (descriptors.isEmpty) return null;

      return _LocalFaceProfile(
        phone: json['phone']?.toString() ?? '',
        pin: json['pin']?.toString() ?? '',
        descriptors: descriptors,
        user: AppUser(
          id: _toInt(rawUser['id']),
          name: rawUser['name']?.toString() ?? 'Petani',
          email: rawUser['email']?.toString() ?? '',
          phone: rawUser['phone']?.toString(),
          role: rawUser['role']?.toString() ?? 'farmer',
          status: rawUser['status']?.toString() ?? 'active',
          roleLabel: rawUser['role_label']?.toString(),
          statusLabel: rawUser['status_label']?.toString(),
        ),
      );
    } catch (_) {
      return null;
    }
  }

  static String _normalizePhone(String phone) {
    final digits = phone.replaceAll(RegExp(r'[^0-9]'), '');
    if (digits.startsWith('62')) return '0${digits.substring(2)}';
    return digits;
  }

  static double _euclideanDistance(List<double> a, List<double> b) {
    if (a.length != b.length) return double.infinity;
    var sum = 0.0;
    for (var i = 0; i < a.length; i++) {
      final delta = a[i] - b[i];
      sum += delta * delta;
    }
    return math.sqrt(sum);
  }

  static int _toInt(dynamic value) {
    if (value is num) return value.toInt();
    if (value is String) return int.tryParse(value.trim()) ?? 0;
    return 0;
  }
}

class _LocalFaceProfile {
  const _LocalFaceProfile({
    required this.phone,
    required this.pin,
    required this.descriptors,
    required this.user,
  });

  final String phone;
  final String pin;
  final List<List<double>> descriptors;
  final AppUser user;
}
