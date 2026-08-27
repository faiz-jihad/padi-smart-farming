import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:padi/core/providers/app_providers.dart';
import 'package:padi/features/notifications/data/models/app_notification_model.dart';
import 'package:padi/features/notifications/presentation/providers/notifications_provider.dart';

class NotificationScreen extends ConsumerStatefulWidget {
  const NotificationScreen({super.key});

  @override
  ConsumerState<NotificationScreen> createState() => _NotificationScreenState();
}

class _NotificationScreenState extends ConsumerState<NotificationScreen> {
  String _selectedCategory = 'all';

  @override
  void initState() {
    super.initState();
    // Trigger a fresh fetch when screen opens
    WidgetsBinding.instance.addPostFrameCallback((_) {
      ref.read(notificationsProvider.notifier).refresh();
    });
  }

  Future<void> _markAllAsRead() async {
    await ref.read(notificationsProvider.notifier).markAllAsRead();
    if (mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Row(
            children: [
              Icon(Icons.check_circle_rounded, color: Color(0xFF6EE7B7), size: 18),
              SizedBox(width: 8),
              Text('Semua notifikasi ditandai telah dibaca'),
            ],
          ),
        ),
      );
    }
  }

  Future<void> _onNotificationTap(AppNotificationModel item) async {
    if (!item.isRead) {
      await ref.read(notificationsProvider.notifier).markAsRead(item.id);
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
    final state = ref.watch(notificationsProvider);
    final notifications = state.notifications;
    final isBuyer = ref.watch(isBuyerRoleProvider);
    if (_selectedCategory == 'all') return notifications;
    if (_selectedCategory == 'unread') {
      return notifications.where((n) => !n.isRead).toList();
    }
    if (_selectedCategory == 'rights') {
      return notifications.where((n) => n.type == 'role_rights').toList();
    }

    if (isBuyer) {
      switch (_selectedCategory) {
        case 'order':
          return notifications.where((n) => n.type == 'order_status').toList();
        case 'logistics':
          return notifications.where((n) => n.type == 'logistics').toList();
        case 'market':
          return notifications.where((n) => n.type == 'marketplace_deal' || n.type == 'marketplace' || n.type == 'market_offer').toList();
        default:
          return notifications;
      }
    } else {
      switch (_selectedCategory) {
        case 'crop':
          return notifications.where((n) => n.type == 'crop_alert' || n.type == 'planting_reminder' || n.type == 'cultivation').toList();
        case 'radar':
          return notifications.where((n) => n.type == 'warning' || n.type == 'early_warning' || n.type == 'disease_outbreak').toList();
        case 'market':
          return notifications.where((n) => n.type == 'marketplace_deal' || n.type == 'market_offer' || n.type == 'marketplace').toList();
        default:
          return notifications;
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final notifState = ref.watch(notificationsProvider);
    final unreadCount = notifState.unreadCount;

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
          if (unreadCount > 0)
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
            onPressed: () => ref.read(notificationsProvider.notifier).refresh(),
          ),
        ],
      ),
      body: notifState.isLoading && notifState.notifications.isEmpty
          ? const Center(
              child: CircularProgressIndicator(color: Color(0xFF059669)),
            )
          : notifState.error != null && notifState.notifications.isEmpty
              ? _buildErrorState()
              : _buildBody(),
    );
  }

  Widget _buildBody() {
    final isBuyer = ref.watch(isBuyerRoleProvider);
    final filtered = _filteredNotifications;

    return RefreshIndicator(
      onRefresh: () => ref.read(notificationsProvider.notifier).refresh(),
      color: isBuyer ? const Color(0xFF0F5132) : const Color(0xFF059669),
      child: Column(
        children: [
          // 1. Role Rights & Facilities Notice Banner (Pure Green and White)
          Container(
            margin: const EdgeInsets.fromLTRB(16, 12, 16, 8),
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(
                color: isBuyer ? const Color(0xFF6EE7B7) : const Color(0xFFA7F3D0),
                width: 1.2,
              ),
              boxShadow: [
                BoxShadow(
                  color: (isBuyer ? const Color(0xFF0F5132) : const Color(0xFF059669)).withOpacity(0.04),
                  blurRadius: 10,
                  offset: const Offset(0, 2),
                ),
              ],
            ),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: isBuyer ? const Color(0xFFD1FAE5) : const Color(0xFFECFDF5),
                    borderRadius: BorderRadius.circular(10),
                  ),
                  child: Icon(
                    Icons.verified_user_rounded,
                    size: 20,
                    color: isBuyer ? const Color(0xFF0F5132) : const Color(0xFF059669),
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Flexible(
                            child: Text(
                              isBuyer
                                  ? 'Hak & Legalitas Pembeli B2B'
                                  : 'Hak & Fasilitas Resmi Petani',
                              style: const TextStyle(
                                fontSize: 13,
                                fontWeight: FontWeight.w800,
                                color: Color(0xFF0F172A),
                              ),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                          const SizedBox(width: 6),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1.5),
                            decoration: BoxDecoration(
                              color: isBuyer ? const Color(0xFFD1FAE5) : const Color(0xFFDCFCE7),
                              borderRadius: BorderRadius.circular(4),
                            ),
                            child: Text(
                              'HAK AKTIF',
                              style: TextStyle(
                                fontSize: 8.5,
                                fontWeight: FontWeight.w900,
                                color: isBuyer ? const Color(0xFF0F5132) : const Color(0xFF047857),
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 2),
                      Text(
                        isBuyer
                            ? 'Dilindungi jaminan timbangan tera resmi, armada logistik truk, & kontrak sah.'
                            : 'Bebas diagnosa AI agronomi gratis, kalender pupuk, & jual gabah tanpa calo.',
                        style: const TextStyle(
                          fontSize: 11,
                          color: Color(0xFF64748B),
                          height: 1.3,
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),

          // 2. Filter tabs
          Container(
            color: Colors.white,
            padding: const EdgeInsets.fromLTRB(16, 6, 16, 10),
            child: SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Builder(
                builder: (context) {
                  final notifState = ref.watch(notificationsProvider);
                  final totalCount = notifState.notifications.length;
                  final unread = notifState.unreadCount;
                  final isBuyerRole = ref.watch(isBuyerRoleProvider);
                  return Row(
                    children: [
                      _buildFilterChip('all', 'Semua ($totalCount)'),
                      if (unread > 0)
                        _buildFilterChip('unread', 'Belum Dibaca ($unread)'),
                      _buildFilterChip('rights', 'Hak Akun'),
                      if (isBuyerRole) ...[
                        _buildFilterChip('order', 'Pesanan & Timbang'),
                        _buildFilterChip('logistics', 'Logistik Truk'),
                        _buildFilterChip('market', 'Bursa & Stok'),
                      ] else ...[
                        _buildFilterChip('crop', 'Lahan & Pupuk'),
                        _buildFilterChip('radar', 'Hama & Cuaca'),
                        _buildFilterChip('market', 'Bursa Gabah'),
                      ],
                    ],
                  );
                },
              ),
            ),
          ),
          const Divider(height: 1, color: Color(0xFFE2E8F0)),

          // 3. List
          Expanded(
            child: filtered.isEmpty
                ? _buildEmptyState()
                : ListView.separated(
                    padding: const EdgeInsets.symmetric(vertical: 4),
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
      case 'role_rights':
        iconData = Icons.shield_outlined;
        iconColor = const Color(0xFF059669);
        iconBg = const Color(0xFFECFDF5);
        break;
      case 'order_status':
        iconData = Icons.scale_rounded;
        iconColor = const Color(0xFF0F5132);
        iconBg = const Color(0xFFD1FAE5);
        break;
      case 'logistics':
        iconData = Icons.local_shipping_outlined;
        iconColor = const Color(0xFF059669);
        iconBg = const Color(0xFFECFDF5);
        break;
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
        iconData = Icons.shield_outlined;
        iconColor = const Color(0xFF047857);
        iconBg = const Color(0xFFEAF5EF);
        break;
      case 'marketplace_deal':
      case 'market_offer':
      case 'marketplace':
        iconData = Icons.storefront_outlined;
        iconColor = const Color(0xFF0F5132);
        iconBg = const Color(0xFFECFDF5);
        break;
      case 'system':
      default:
        iconData = Icons.notifications_none_rounded;
        iconColor = const Color(0xFF059669);
        iconBg = const Color(0xFFF1F5F9);
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
    final error = ref.read(notificationsProvider).error;
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
              error ?? 'Periksa koneksi internet Anda.',
              textAlign: TextAlign.center,
              style: const TextStyle(fontSize: 12.5, color: Color(0xFF64748B)),
            ),
            const SizedBox(height: 16),
            FilledButton.icon(
              onPressed: () => ref.read(notificationsProvider.notifier).refresh(),
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
