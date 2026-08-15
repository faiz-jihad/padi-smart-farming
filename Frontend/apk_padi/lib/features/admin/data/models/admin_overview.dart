class AdminOverview {
  const AdminOverview({
    required this.summary,
    required this.users,
    required this.broadcasts,
    required this.auditLogs,
  });

  final AdminSummary summary;
  final List<AdminUserPreview> users;
  final List<AdminBroadcastPreview> broadcasts;
  final List<AdminAuditLogPreview> auditLogs;

  factory AdminOverview.fromJson(Map<String, dynamic>? json) {
    final data = _payload(json);

    return AdminOverview(
      summary: AdminSummary.fromJson(data['summary'] as Map<String, dynamic>?),
      users: readAdminUsers(data['users']),
      broadcasts: readAdminBroadcasts(data['broadcasts']),
      auditLogs: readAdminAuditLogs(data['audit_logs']),
    );
  }
}

class AdminSummary {
  const AdminSummary({
    required this.usersTotal,
    required this.usersActive,
    required this.farmersTotal,
    required this.buyersTotal,
    required this.farmsTotal,
    required this.cropSeasonsTotal,
    required this.marketListingsTotal,
    required this.communityReportsTotal,
    required this.alertSubscriptionsTotal,
    required this.broadcastsTotal,
    required this.auditLogsTotal,
  });

  final int usersTotal;
  final int usersActive;
  final int farmersTotal;
  final int buyersTotal;
  final int farmsTotal;
  final int cropSeasonsTotal;
  final int marketListingsTotal;
  final int communityReportsTotal;
  final int alertSubscriptionsTotal;
  final int broadcastsTotal;
  final int auditLogsTotal;

  factory AdminSummary.fromJson(Map<String, dynamic>? json) {
    return AdminSummary(
      usersTotal: _readInt(json, 'users_total'),
      usersActive: _readInt(json, 'users_active'),
      farmersTotal: _readInt(json, 'farmers_total'),
      buyersTotal: _readInt(json, 'buyers_total'),
      farmsTotal: _readInt(json, 'farms_total'),
      cropSeasonsTotal: _readInt(json, 'crop_seasons_total'),
      marketListingsTotal: _readInt(json, 'market_listings_total'),
      communityReportsTotal: _readInt(json, 'community_reports_total'),
      alertSubscriptionsTotal: _readInt(json, 'alert_subscriptions_total'),
      broadcastsTotal: _readInt(json, 'broadcasts_total'),
      auditLogsTotal: _readInt(json, 'audit_logs_total'),
    );
  }
}

class AdminUserPreview {
  const AdminUserPreview({
    required this.id,
    required this.name,
    required this.email,
    required this.role,
    required this.status,
    this.phone,
  });

  final int id;
  final String name;
  final String email;
  final String? phone;
  final String role;
  final String status;

  factory AdminUserPreview.fromJson(Map<String, dynamic> json) {
    return AdminUserPreview(
      id: _readInt(json, 'id'),
      name: json['name']?.toString() ?? '-',
      email: json['email']?.toString() ?? '-',
      phone: json['phone']?.toString(),
      role: _normaliseRole(json['role']?.toString() ?? '-'),
      status: json['status']?.toString() ?? '-',
    );
  }

  String get roleLabel => _roleLabels[role] ?? role;

  String get statusLabel => _statusLabels[status] ?? status;
}

class AdminBroadcastPreview {
  const AdminBroadcastPreview({
    required this.id,
    required this.title,
    required this.message,
    required this.type,
    required this.status,
  });

  final int id;
  final String title;
  final String message;
  final String type;
  final String status;

  factory AdminBroadcastPreview.fromJson(Map<String, dynamic> json) {
    return AdminBroadcastPreview(
      id: _readInt(json, 'id'),
      title: json['title']?.toString() ?? '-',
      message: json['message']?.toString() ?? '',
      type: json['type']?.toString() ?? '-',
      status: json['status']?.toString() ?? '-',
    );
  }

  String get typeLabel => _typeLabels[type] ?? type;

  String get statusLabel => _broadcastStatusLabels[status] ?? status;
}

class AdminAuditLogPreview {
  const AdminAuditLogPreview({
    required this.action,
    required this.entityType,
    required this.ipAddress,
  });

  final String action;
  final String entityType;
  final String ipAddress;

  factory AdminAuditLogPreview.fromJson(Map<String, dynamic> json) {
    return AdminAuditLogPreview(
      action: json['action']?.toString() ?? '-',
      entityType: json['entity_type']?.toString() ?? '-',
      ipAddress: json['ip_address']?.toString() ?? '-',
    );
  }
}

List<T> _readList<T>(Object? value, T Function(Map<String, dynamic>) fromJson) {
  if (value is! List) {
    return const [];
  }

  return value
      .whereType<Map<String, dynamic>>()
      .map(fromJson)
      .toList(growable: false);
}

int _readInt(Map<String, dynamic>? json, String key) {
  final value = json?[key];
  if (value is int) {
    return value;
  }
  return int.tryParse(value?.toString() ?? '') ?? 0;
}

Map<String, dynamic> _payload(Map<String, dynamic>? json) {
  return json?['data'] as Map<String, dynamic>? ?? {};
}

List<AdminUserPreview> readAdminUsers(Object? value) {
  return _readList(value, AdminUserPreview.fromJson);
}

List<AdminBroadcastPreview> readAdminBroadcasts(Object? value) {
  return _readList(value, AdminBroadcastPreview.fromJson);
}

List<AdminAuditLogPreview> readAdminAuditLogs(Object? value) {
  return _readList(value, AdminAuditLogPreview.fromJson);
}

AdminUserPreview readAdminUser(Map<String, dynamic>? json) {
  return AdminUserPreview.fromJson(
    _payload(json)['user'] as Map<String, dynamic>? ?? {},
  );
}

AdminBroadcastPreview readAdminBroadcast(Map<String, dynamic>? json) {
  return AdminBroadcastPreview.fromJson(
    _payload(json)['broadcast'] as Map<String, dynamic>? ?? {},
  );
}

const adminRoleOptions = {
  'farmer': 'Petani',
  'buyer': 'Pembeli',
  'extension_officer': 'Penyuluh',
  'admin': 'Admin',
};

const adminUserStatusOptions = {
  'active': 'Aktif',
  'inactive': 'Tidak aktif',
  'suspended': 'Ditangguhkan',
};

const adminBroadcastTypeOptions = {
  'info': 'Info',
  'warning': 'Peringatan',
  'announcement': 'Pengumuman',
  'system': 'Sistem',
};

const adminBroadcastStatusOptions = {
  'draft': 'Draft',
  'published': 'Terbit',
  'expired': 'Kedaluwarsa',
};

const _roleLabels = adminRoleOptions;
const _statusLabels = adminUserStatusOptions;
const _typeLabels = adminBroadcastTypeOptions;
const _broadcastStatusLabels = adminBroadcastStatusOptions;

String _normaliseRole(String role) {
  return switch (role) {
    'partner' => 'buyer',
    'ppl' => 'extension_officer',
    _ => role,
  };
}
