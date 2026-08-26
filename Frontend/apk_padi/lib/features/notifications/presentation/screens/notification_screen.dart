import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:padi/core/providers/app_providers.dart';
import 'package:padi/features/notifications/data/models/app_notification_model.dart';
import 'package:padi/features/notifications/data/services/device_notification_service.dart';

class NotificationScreen extends ConsumerStatefulWidget {
  const NotificationScreen({super.key});

  @override
  ConsumerState<NotificationScreen> createState() => _NotificationScreenState();
}

class _NotificationScreenState extends ConsumerState<NotificationScreen> {
  late final DeviceNotificationService _notificationService;

  List<AppNotificationModel> _notifications = [];
  bool _isLoading = true;
  String? _errorMessage;
  String _selectedCategory = 'all'; // 'all', 'unread', 'crop', 'radar', 'market', 'ppl'

  @override
  void initState() {
    super.initState();
    final apiClient = ref.read(apiClientProvider);
    _notificationService = DeviceNotificationService(apiClient);
    _loadNotifications();
  }

  Future<void> _loadNotifications() async {
    if (mounted) {
      setState(() {
        _isLoading = true;
        _errorMessage = null;
      });
    }

    try {
      final list = await _notificationService.fetchNotifications();
      if (!mounted) return;

      setState(() {
        _notifications = list;
        _isLoading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _isLoading = false;
        _errorMessage = 'Gagal memuat notifikasi.';
      });
    }
  }

  Future<void> _markAllAsRead() async {
    final success = await _notificationService.markAllAsRead();
    if (success && mounted) {
      setState(() {
        _notifications = _notifications.map((n) => n.copyWith(isRead: true)).toList();
      });
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: const Text('Semua notifikasi ditandai telah dibaca'),
          backgroundColor: const Color(0xFF059669),
          behavior: SnackBarBehavior.floating,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        ),
      );
    }
  }

  Future<void> _onNotificationTap(AppNotificationModel item) async {
    if (!item.isRead) {
      await _notificationService.markAsRead(item.id);
      if (mounted) {
        setState(() {
          _notifications = _notifications.map((n) {
            if (n.id == item.id) return n.copyWith(isRead: true);
            return n;
          }).toList();
        });
      }
    }

    _showNotificationDetail(item);
  }

  void _showNotificationDetail(AppNotificationModel item) {
    showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) {
        return SafeArea(
          child: Padding(
            padding: const EdgeInsets.fromLTRB(20, 14, 20, 24),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Center(
                  child: Container(
                    width: 36,
                    height: 4,
                    decoration: BoxDecoration(
                      color: const Color(0xFFE2E8F0),
                      borderRadius: BorderRadius.circular(99),
                    ),
                  ),
                ),
                const SizedBox(height: 18),
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                      decoration: BoxDecoration(
                        color: const Color(0xFFF1F5F9),
                        borderRadius: BorderRadius.circular(6),
                      ),
                      child: Text(
                        item.categoryLabel,
                        style: const TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w700,
                          color: Color(0xFF475569),
                        ),
                      ),
                    ),
                    const Spacer(),
                    if (item.createdAt != null)
                      Text(
                        _formatShortDate(item.createdAt!),
                        style: const TextStyle(fontSize: 11.5, color: Color(0xFF94A3B8)),
                      ),
                  ],
                ),
                const SizedBox(height: 10),
                Text(
                  item.title,
                  style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w800,
                    color: Color(0xFF0F172A),
                  ),
                ),
                const SizedBox(height: 12),
                Text(
                  item.body,
                  style: const TextStyle(
                    fontSize: 13.5,
                    color: Color(0xFF475569),
                    height: 1.55,
                  ),
                ),
                const SizedBox(height: 24),
                Row(
                  children: [
                    Expanded(
                      child: OutlinedButton(
                        onPressed: () => Navigator.of(ctx).pop(),
                        style: OutlinedButton.styleFrom(
                          padding: const EdgeInsets.symmetric(vertical: 12),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                        ),
                        child: const Text('Tutup'),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: FilledButton(
                        onPressed: () {
                          Navigator.of(ctx).pop();
                          _navigateSafely(item);
                        },
                        style: FilledButton.styleFrom(
                          backgroundColor: const Color(0xFF059669),
                          padding: const EdgeInsets.symmetric(vertical: 12),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12),
                          ),
                        ),
                        child: const Text('Buka Halaman'),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  void _navigateSafely(AppNotificationModel item) {
    final targetUrl = item.data['url']?.toString();
    if (targetUrl != null && targetUrl.isNotEmpty) {
      if (targetUrl == '/' || targetUrl == '/home') {
        context.go('/home');
        return;
      } else if (targetUrl.startsWith('/')) {
        context.push(targetUrl);
        return;
      }
    }

    switch (item.type) {
      case 'crop_alert':
      case 'planting_reminder':
      case 'cultivation':
        context.push('/farms');
        break;
      case 'warning':
      case 'early_warning':
      case 'disease_outbreak':
        context.push('/community-alert');
        break;
      case 'marketplace_deal':
      case 'market_offer':
      case 'marketplace':
        context.push('/marketplace');
        break;
      case 'ppl_validation':
      case 'field_verification':
        context.push('/community-alert');
        break;
      default:
        context.go('/home');
        break;
    }
  }

  List<AppNotificationModel> get _filteredNotifications {
    if (_selectedCategory == 'all') return _notifications;
    if (_selectedCategory == 'unread') {
      return _notifications.where((n) => !n.isRead).toList();
    }

    return _notifications.where((n) {
      switch (_selectedCategory) {
        case 'crop':
          return n.type == 'crop_alert' || n.type == 'planting_reminder' || n.type == 'cultivation';
        case 'radar':
          return n.type == 'warning' || n.type == 'early_warning' || n.type == 'disease_outbreak';
        case 'market':
          return n.type == 'marketplace_deal' || n.type == 'market_offer' || n.type == 'marketplace';
        case 'ppl':
          return n.type == 'ppl_validation' || n.type == 'field_verification';
        default:
          return true;
      }
    }).toList();
  }

  int get _unreadCount => _notifications.where((n) => !n.isRead).length;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        scrolledUnderElevation: 1,
        leading: IconButton(
          onPressed: () => context.pop(),
          icon: const Icon(Icons.arrow_back_rounded, color: Color(0xFF1E293B)),
        ),
        title: const Text(
          'Notifikasi',
          style: TextStyle(
            color: Color(0xFF0F172A),
            fontSize: 18,
            fontWeight: FontWeight.w800,
          ),
        ),
        actions: [
          if (_unreadCount > 0)
            TextButton(
              onPressed: _markAllAsRead,
              child: const Text(
                'Tandai Dibaca',
                style: TextStyle(
                  color: Color(0xFF059669),
                  fontWeight: FontWeight.w700,
                  fontSize: 13,
                ),
              ),
            ),
          IconButton(
            icon: const Icon(Icons.refresh_rounded, color: Color(0xFF64748B), size: 22),
            tooltip: 'Segarkan',
            onPressed: _loadNotifications,
          ),
        ],
      ),
      body: _isLoading
          ? const Center(
              child: CircularProgressIndicator(color: Color(0xFF059669)),
            )
          : _errorMessage != null && _notifications.isEmpty
              ? _buildErrorState()
              : _buildBody(),
    );
  }

  Widget _buildBody() {
    final filtered = _filteredNotifications;

    return RefreshIndicator(
      onRefresh: _loadNotifications,
      color: const Color(0xFF059669),
      child: Column(
        children: [
          // Filter tabs
          Container(
            color: Colors.white,
            padding: const EdgeInsets.fromLTRB(16, 8, 16, 12),
            child: SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: [
                  _buildFilterChip('all', 'Semua (${_notifications.length})'),
                  if (_unreadCount > 0)
                    _buildFilterChip('unread', 'Belum Dibaca ($_unreadCount)'),
                  _buildFilterChip('crop', 'Budidaya'),
                  _buildFilterChip('radar', 'Peringatan Hama'),
                  _buildFilterChip('market', 'Pasar'),
                  _buildFilterChip('ppl', 'PPL'),
                ],
              ),
            ),
          ),
          const Divider(height: 1, color: Color(0xFFE2E8F0)),

          // List
          Expanded(
            child: filtered.isEmpty
                ? _buildEmptyState()
                : ListView.separated(
                    padding: const EdgeInsets.symmetric(vertical: 8),
                    itemCount: filtered.length,
                    separatorBuilder: (_, __) => const Divider(
                      height: 1,
                      indent: 68,
                      endIndent: 16,
                      color: Color(0xFFF1F5F9),
                    ),
                    itemBuilder: (context, index) {
                      return _buildNotificationItem(filtered[index]);
                    },
                  ),
          ),
        ],
      ),
    );
  }

  Widget _buildFilterChip(String key, String label) {
    final isSelected = _selectedCategory == key;
    return Padding(
      padding: const EdgeInsets.only(right: 8),
      child: InkWell(
        onTap: () => setState(() => _selectedCategory = key),
        borderRadius: BorderRadius.circular(20),
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
          decoration: BoxDecoration(
            color: isSelected ? const Color(0xFF059669) : const Color(0xFFF1F5F9),
            borderRadius: BorderRadius.circular(20),
          ),
          child: Text(
            label,
            style: TextStyle(
              fontSize: 12,
              fontWeight: isSelected ? FontWeight.w700 : FontWeight.w600,
              color: isSelected ? Colors.white : const Color(0xFF475569),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildNotificationItem(AppNotificationModel item) {
    final isUnread = !item.isRead;

    final IconData iconData;
    final Color iconColor;
    final Color iconBg;

    switch (item.type) {
      case 'crop_alert':
      case 'planting_reminder':
      case 'cultivation':
        iconData = Icons.spa_outlined;
        iconColor = const Color(0xFF059669);
        iconBg = const Color(0xFFECFDF5);
        break;
      case 'warning':
      case 'early_warning':
      case 'disease_outbreak':
        iconData = Icons.warning_amber_rounded;
        iconColor = const Color(0xFFDC2626);
        iconBg = const Color(0xFFFEF2F2);
        break;
      case 'marketplace_deal':
      case 'market_offer':
      case 'marketplace':
        iconData = Icons.storefront_outlined;
        iconColor = const Color(0xFFD97706);
        iconBg = const Color(0xFFFFFBEB);
        break;
      case 'ppl_validation':
      case 'field_verification':
        iconData = Icons.verified_user_outlined;
        iconColor = const Color(0xFF2563EB);
        iconBg = const Color(0xFFEFF6FF);
        break;
      default:
        iconData = Icons.notifications_none_rounded;
        iconColor = const Color(0xFF64748B);
        iconBg = const Color(0xFFF8FAFC);
        break;
    }

    return Container(
      color: isUnread ? const Color(0xFFF0FDF4) : Colors.white,
      child: ListTile(
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
        onTap: () => _onNotificationTap(item),
        leading: Container(
          width: 42,
          height: 42,
          decoration: BoxDecoration(
            color: iconBg,
            shape: BoxShape.circle,
          ),
          child: Icon(iconData, color: iconColor, size: 20),
        ),
        title: Row(
          children: [
            Expanded(
              child: Text(
                item.title,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: TextStyle(
                  fontSize: 14,
                  fontWeight: isUnread ? FontWeight.w800 : FontWeight.w600,
                  color: isUnread ? const Color(0xFF0F172A) : const Color(0xFF334155),
                ),
              ),
            ),
            if (isUnread) ...[
              const SizedBox(width: 8),
              Container(
                width: 7,
                height: 7,
                decoration: const BoxDecoration(
                  color: Color(0xFF059669),
                  shape: BoxShape.circle,
                ),
              ),
            ],
          ],
        ),
        subtitle: Padding(
          padding: const EdgeInsets.only(top: 4),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                item.body,
                maxLines: 2,
                overflow: TextOverflow.ellipsis,
                style: TextStyle(
                  fontSize: 12.5,
                  color: isUnread ? const Color(0xFF475569) : const Color(0xFF64748B),
                  height: 1.4,
                ),
              ),
              const SizedBox(height: 6),
              Row(
                children: [
                  Text(
                    item.categoryLabel,
                    style: TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w600,
                      color: iconColor,
                    ),
                  ),
                  if (item.createdAt != null) ...[
                    const Text(' • ', style: TextStyle(color: Color(0xFFCBD5E1), fontSize: 11)),
                    Text(
                      _formatShortDate(item.createdAt!),
                      style: const TextStyle(fontSize: 11, color: Color(0xFF94A3B8)),
                    ),
                  ],
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              padding: const EdgeInsets.all(16),
              decoration: const BoxDecoration(
                color: Color(0xFFF1F5F9),
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.notifications_off_outlined, color: Color(0xFF94A3B8), size: 36),
            ),
            const SizedBox(height: 14),
            const Text(
              'Tidak Ada Notifikasi',
              style: TextStyle(fontSize: 15, fontWeight: FontWeight.w800, color: Color(0xFF0F172A)),
            ),
            const SizedBox(height: 4),
            const Text(
              'Pemberitahuan baru akan muncul di sini.',
              style: TextStyle(fontSize: 12.5, color: Color(0xFF64748B)),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildErrorState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.cloud_off_rounded, color: Color(0xFFDC2626), size: 40),
            const SizedBox(height: 12),
            const Text('Gagal Memuat Notifikasi', style: TextStyle(fontSize: 15, fontWeight: FontWeight.w800)),
            const SizedBox(height: 4),
            Text(
              _errorMessage ?? 'Periksa koneksi internet Anda.',
              textAlign: TextAlign.center,
              style: const TextStyle(fontSize: 12.5, color: Color(0xFF64748B)),
            ),
            const SizedBox(height: 16),
            FilledButton.icon(
              onPressed: _loadNotifications,
              icon: const Icon(Icons.refresh_rounded, size: 16),
              label: const Text('Coba Lagi'),
              style: FilledButton.styleFrom(
                backgroundColor: const Color(0xFF059669),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
              ),
            ),
          ],
        ),
      ),
    );
  }

  String _formatShortDate(String raw) {
    final dt = DateTime.tryParse(raw);
    if (dt == null) return raw;
    final now = DateTime.now();
    final diff = now.difference(dt);
    if (diff.inMinutes < 60) return '${diff.inMinutes} mnt lalu';
    if (diff.inHours < 24) return '${diff.inHours} jam lalu';
    if (diff.inDays < 7) return '${diff.inDays} hr lalu';
    return '${dt.day}/${dt.month}/${dt.year}';
  }
}
