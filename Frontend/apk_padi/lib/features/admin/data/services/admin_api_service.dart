import 'package:padi/core/helpers/api_error_helper.dart';
import 'package:padi/core/network/api_client.dart';
import 'package:padi/features/admin/data/models/admin_overview.dart';

class AdminApiService {
  const AdminApiService(this._apiClient);

  final ApiClient _apiClient;

  Future<AdminOverview> fetchOverview() async {
    try {
      final response = await _apiClient.dio.get<Map<String, dynamic>>('/admin');
      return AdminOverview.fromJson(response.data);
    } catch (error) {
      throw mapDioException(error);
    }
  }

  Future<List<AdminUserPreview>> fetchUsers() async {
    try {
      final response = await _apiClient.dio.get<Map<String, dynamic>>(
        '/admin/users',
      );
      final data = response.data?['data'] as Map<String, dynamic>? ?? {};
      return readAdminUsers(data['users']);
    } catch (error) {
      throw mapDioException(error);
    }
  }

  Future<AdminUserPreview> updateUser({
    required int id,
    required String role,
    required String status,
  }) async {
    try {
      final response = await _apiClient.dio.patch<Map<String, dynamic>>(
        '/admin/users/$id',
        data: {'role': role, 'status': status},
      );
      return readAdminUser(response.data);
    } catch (error) {
      throw mapDioException(error);
    }
  }

  Future<List<AdminBroadcastPreview>> fetchBroadcasts() async {
    try {
      final response = await _apiClient.dio.get<Map<String, dynamic>>(
        '/admin/broadcasts',
      );
      final data = response.data?['data'] as Map<String, dynamic>? ?? {};
      return readAdminBroadcasts(data['broadcasts']);
    } catch (error) {
      throw mapDioException(error);
    }
  }

  Future<AdminBroadcastPreview> createBroadcast({
    required String title,
    required String message,
    required String type,
    required String status,
  }) async {
    try {
      final response = await _apiClient.dio.post<Map<String, dynamic>>(
        '/admin/broadcasts',
        data: {
          'title': title,
          'message': message,
          'type': type,
          'status': status,
        },
      );
      return readAdminBroadcast(response.data);
    } catch (error) {
      throw mapDioException(error);
    }
  }

  Future<AdminBroadcastPreview> updateBroadcast({
    required int id,
    required String title,
    required String message,
    required String type,
    required String status,
  }) async {
    try {
      final response = await _apiClient.dio.patch<Map<String, dynamic>>(
        '/admin/broadcasts/$id',
        data: {
          'title': title,
          'message': message,
          'type': type,
          'status': status,
        },
      );
      return readAdminBroadcast(response.data);
    } catch (error) {
      throw mapDioException(error);
    }
  }

  Future<void> deleteBroadcast(int id) async {
    try {
      await _apiClient.dio.delete<Map<String, dynamic>>(
        '/admin/broadcasts/$id',
      );
    } catch (error) {
      throw mapDioException(error);
    }
  }

  Future<List<AdminAuditLogPreview>> fetchAuditLogs() async {
    try {
      final response = await _apiClient.dio.get<Map<String, dynamic>>(
        '/admin/audit-logs',
      );
      final data = response.data?['data'] as Map<String, dynamic>? ?? {};
      return readAdminAuditLogs(data['audit_logs']);
    } catch (error) {
      throw mapDioException(error);
    }
  }
}
