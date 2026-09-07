import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:padi/core/providers/app_providers.dart';
import 'package:padi/features/admin/data/models/admin_overview.dart';
import 'package:padi/features/auth/presentation/widgets/padi_theme.dart';
import 'package:padi/features/plant_check/data/services/plant_check_api_service.dart';

final adminDiseaseValidationsProvider =
    FutureProvider.autoDispose<List<Map<String, dynamic>>>((ref) async {
      final service = ref.read(plantCheckApiServiceProvider);
      return service.fetchPplValidations();
    });

class AdminOverviewScreen extends ConsumerWidget {
  const AdminOverviewScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return DefaultTabController(
      length: 5,
      child: Scaffold(
        appBar: AppBar(
          title: const Text('Admin'),
          leading: IconButton(
            tooltip: 'Kembali',
            onPressed: () => context.go('/home'),
            icon: const Icon(Icons.arrow_back_rounded),
          ),
          actions: [
            IconButton(
              tooltip: 'Muat ulang',
              onPressed: () => _refreshAdmin(ref),
              icon: const Icon(Icons.refresh_rounded),
            ),
          ],
          bottom: const TabBar(
            isScrollable: true,
            tabs: [
              Tab(icon: Icon(Icons.dashboard_rounded), text: 'Ringkasan'),
              Tab(icon: Icon(Icons.eco_rounded), text: 'Penyakit'),
              Tab(icon: Icon(Icons.people_alt_rounded), text: 'Pengguna'),
              Tab(icon: Icon(Icons.campaign_rounded), text: 'Broadcast'),
              Tab(icon: Icon(Icons.history_rounded), text: 'Audit'),
            ],
          ),
        ),
        body: const SafeArea(
          child: TabBarView(
            children: [
              _OverviewTab(),
              _DiseaseReportsTab(),
              _UsersTab(),
              _BroadcastsTab(),
              _AuditLogsTab(),
            ],
          ),
        ),
      ),
    );
  }
}

class _DiseaseReportsTab extends ConsumerWidget {
  const _DiseaseReportsTab();

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final reports = ref.watch(adminDiseaseValidationsProvider);

    return reports.when(
      data: (items) => _AdminDiseaseReports(items: items),
      loading: () => const Center(child: CircularProgressIndicator()),
      error: (error, _) => _AdminError(
        message: error.toString(),
        onRetry: () => ref.invalidate(adminDiseaseValidationsProvider),
      ),
    );
  }
}

class _OverviewTab extends ConsumerWidget {
  const _OverviewTab();

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final overview = ref.watch(adminOverviewProvider);

    return overview.when(
      data: (data) => _AdminOverviewContent(data: data),
      loading: () => const Center(child: CircularProgressIndicator()),
      error: (error, _) => _AdminError(
        message: error.toString(),
        onRetry: () => ref.invalidate(adminOverviewProvider),
      ),
    );
  }
}

class _UsersTab extends ConsumerWidget {
  const _UsersTab();

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final users = ref.watch(adminUsersProvider);

    return users.when(
      data: (items) => _ListPane(
        emptyText: 'Belum ada pengguna.',
        children: items.map((user) => _UserCard(user: user)).toList(),
      ),
      loading: () => const Center(child: CircularProgressIndicator()),
      error: (error, _) => _AdminError(
        message: error.toString(),
        onRetry: () => ref.invalidate(adminUsersProvider),
      ),
    );
  }
}

class _BroadcastsTab extends ConsumerWidget {
  const _BroadcastsTab();

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final broadcasts = ref.watch(adminBroadcastsProvider);

    return broadcasts.when(
      data: (items) => _ListPane(
        header: FilledButton.icon(
          onPressed: () => _showBroadcastDialog(context, ref),
          icon: const Icon(Icons.add_rounded),
          label: const Text('Broadcast baru'),
        ),
        emptyText: 'Belum ada broadcast.',
        children: items.map((item) => _BroadcastCard(item: item)).toList(),
      ),
      loading: () => const Center(child: CircularProgressIndicator()),
      error: (error, _) => _AdminError(
        message: error.toString(),
        onRetry: () => ref.invalidate(adminBroadcastsProvider),
      ),
    );
  }
}

class _AuditLogsTab extends ConsumerWidget {
  const _AuditLogsTab();

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final logs = ref.watch(adminAuditLogsProvider);

    return logs.when(
      data: (items) => _ListPane(
        emptyText: 'Belum ada audit log.',
        children: items
            .map(
              (item) => _InfoCard(
                title: item.action,
                subtitle: item.entityType,
                trailing: item.ipAddress,
                icon: Icons.manage_history_rounded,
              ),
            )
            .toList(),
      ),
      loading: () => const Center(child: CircularProgressIndicator()),
      error: (error, _) => _AdminError(
        message: error.toString(),
        onRetry: () => ref.invalidate(adminAuditLogsProvider),
      ),
    );
  }
}

class _AdminOverviewContent extends StatelessWidget {
  const _AdminOverviewContent({required this.data});

  final AdminOverview data;

  @override
  Widget build(BuildContext context) {
    final summary = data.summary;

    return ListView(
      padding: const EdgeInsets.all(20),
      children: [
        Text(
          'Ringkasan Sistem',
          style: Theme.of(context).textTheme.headlineSmall?.copyWith(
            color: padiInk,
            fontWeight: FontWeight.w900,
          ),
        ),
        const SizedBox(height: 16),
        GridView.count(
          crossAxisCount: MediaQuery.sizeOf(context).width > 520 ? 3 : 2,
          childAspectRatio: 1.55,
          crossAxisSpacing: 12,
          mainAxisSpacing: 12,
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          children: [
            _MetricTile(label: 'Pengguna', value: summary.usersTotal),
            _MetricTile(label: 'Aktif', value: summary.usersActive),
            _MetricTile(label: 'Petani', value: summary.farmersTotal),
            _MetricTile(label: 'Pembeli', value: summary.buyersTotal),
            _MetricTile(label: 'Lahan', value: summary.farmsTotal),
            _MetricTile(label: 'Musim Tanam', value: summary.cropSeasonsTotal),
            _MetricTile(
              label: 'Marketplace',
              value: summary.marketListingsTotal,
            ),
            _MetricTile(label: 'Laporan', value: summary.communityReportsTotal),
            _MetricTile(label: 'Broadcast', value: summary.broadcastsTotal),
            _MetricTile(label: 'Audit', value: summary.auditLogsTotal),
          ],
        ),
        const SizedBox(height: 22),
        if (data.disasterSummary != null ||
            data.disasterThreats.isNotEmpty) ...[
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(
                color: (data.disasterSummary?.systemStatus == 'danger')
                    ? const Color(0xFFFCA5A5)
                    : (data.disasterSummary?.systemStatus == 'warning'
                          ? const Color(0xFFFED7AA)
                          : const Color(0xFFBBF7D0)),
                width: 1.5,
              ),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Row(
                      children: [
                        Container(
                          width: 8,
                          height: 8,
                          decoration: const BoxDecoration(
                            color: padiGreen,
                            shape: BoxShape.circle,
                          ),
                        ),
                        const SizedBox(width: 6),
                        const Text(
                          'RADAR CUACA & BENCANA',
                          style: TextStyle(
                            color: padiGreen,
                            fontSize: 11,
                            fontWeight: FontWeight.w800,
                            letterSpacing: 0.4,
                          ),
                        ),
                      ],
                    ),
                    Text(
                      data.disasterSummary?.systemStatus == 'danger'
                          ? 'BAHAYA'
                          : (data.disasterSummary?.systemStatus == 'warning'
                                ? 'SIAGA'
                                : 'NORMAL'),
                      style: TextStyle(
                        color: data.disasterSummary?.systemStatus == 'danger'
                            ? const Color(0xFFDC2626)
                            : (data.disasterSummary?.systemStatus == 'warning'
                                  ? const Color(0xFFEA580C)
                                  : padiGreen),
                        fontSize: 11,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 10),
                Text(
                  data.disasterSummary?.statusHeadline ??
                      'Radar Ancaman Bencana Pertanian',
                  style: const TextStyle(
                    color: padiInk,
                    fontWeight: FontWeight.w900,
                    fontSize: 15,
                  ),
                ),
                const SizedBox(height: 14),
                ...data.disasterThreats.map(
                  (threat) => Container(
                    margin: const EdgeInsets.only(bottom: 10),
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF8FAFC),
                      borderRadius: BorderRadius.circular(12),
                      border: Border.all(color: const Color(0xFFE2E8F0)),
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          mainAxisAlignment: MainAxisAlignment.spaceBetween,
                          children: [
                            Text(
                              '${threat.categoryLabel} • ${threat.severityLabel}',
                              style: TextStyle(
                                color: threat.severity == 'danger'
                                    ? const Color(0xFFDC2626)
                                    : (threat.severity == 'warning'
                                          ? const Color(0xFFEA580C)
                                          : padiGreen),
                                fontSize: 11,
                                fontWeight: FontWeight.w800,
                              ),
                            ),
                            Text(
                              threat.probability,
                              style: const TextStyle(
                                color: padiMuted,
                                fontSize: 11,
                                fontWeight: FontWeight.w700,
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 4),
                        Text(
                          threat.title,
                          style: const TextStyle(
                            color: padiInk,
                            fontWeight: FontWeight.w800,
                            fontSize: 13,
                          ),
                        ),
                        const SizedBox(height: 6),
                        Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: 8,
                            vertical: 6,
                          ),
                          decoration: BoxDecoration(
                            color: const Color(0xFFF8FAFC),
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Row(
                            children: [
                              const Icon(
                                Icons.lightbulb_outline_rounded,
                                size: 14,
                                color: padiGreen,
                              ),
                              const SizedBox(width: 6),
                              Expanded(
                                child: Text(
                                  threat.recommendation,
                                  style: const TextStyle(
                                    color: padiInk,
                                    fontSize: 11,
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 18),
        ],
        _Section(
          title: 'Pengguna Terbaru',
          emptyText: 'Belum ada pengguna.',
          children: data.users
              .map(
                (user) => _InfoRow(
                  title: user.name,
                  subtitle: user.email,
                  trailing: '${user.roleLabel} / ${user.statusLabel}',
                ),
              )
              .toList(),
        ),
        const SizedBox(height: 18),
        _Section(
          title: 'Broadcast Terbaru',
          emptyText: 'Belum ada broadcast.',
          children: data.broadcasts
              .map(
                (item) => _InfoRow(
                  title: item.title,
                  subtitle: item.typeLabel,
                  trailing: item.statusLabel,
                ),
              )
              .toList(),
        ),
        const SizedBox(height: 18),
        _Section(
          title: 'Audit Terbaru',
          emptyText: 'Belum ada audit log.',
          children: data.auditLogs
              .map(
                (item) => _InfoRow(
                  title: item.action,
                  subtitle: item.entityType,
                  trailing: item.ipAddress,
                ),
              )
              .toList(),
        ),
      ],
    );
  }
}

class _AdminDiseaseReports extends ConsumerWidget {
  const _AdminDiseaseReports({required this.items});

  final List<Map<String, dynamic>> items;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final pending = items.where((item) => item['status'] == 'pending').length;
    final revisit = items
        .where((item) => item['status'] == 'needs_revisit')
        .length;
    final done = items.length - pending - revisit;

    return RefreshIndicator(
      onRefresh: () async =>
          ref.refresh(adminDiseaseValidationsProvider.future),
      color: padiGreen,
      child: ListView(
        padding: const EdgeInsets.all(20),
        children: [
          Text(
            'Laporan Penyakit Petani',
            style: Theme.of(context).textTheme.headlineSmall?.copyWith(
              color: padiInk,
              fontWeight: FontWeight.w900,
            ),
          ),
          const SizedBox(height: 6),
          const Text(
            'Pantau laporan masuk, penanganan PPL, dan status akhir yang diterima petani.',
            style: TextStyle(
              color: padiMuted,
              fontSize: 13,
              fontWeight: FontWeight.w600,
              height: 1.35,
            ),
          ),
          const SizedBox(height: 16),
          Row(
            children: [
              Expanded(
                child: _StatusCountTile(
                  label: 'Masuk',
                  value: pending,
                  icon: Icons.inbox_rounded,
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: _StatusCountTile(
                  label: 'Tinjauan',
                  value: revisit,
                  icon: Icons.route_rounded,
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: _StatusCountTile(
                  label: 'Selesai',
                  value: done,
                  icon: Icons.verified_rounded,
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          if (items.isEmpty)
            const _EmptyState(text: 'Belum ada laporan penyakit petani.')
          else
            ...items.map((item) => _AdminDiseaseReportCard(item: item)),
        ],
      ),
    );
  }
}

class _StatusCountTile extends StatelessWidget {
  const _StatusCountTile({
    required this.label,
    required this.value,
    required this.icon,
  });

  final String label;
  final int value;
  final IconData icon;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: padiGreen.withValues(alpha: 0.14)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, color: padiGreen, size: 20),
          const SizedBox(height: 8),
          Text(
            value.toString(),
            style: const TextStyle(
              color: padiInk,
              fontSize: 20,
              fontWeight: FontWeight.w900,
            ),
          ),
          Text(
            label,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: const TextStyle(
              color: padiMuted,
              fontSize: 11,
              fontWeight: FontWeight.w800,
            ),
          ),
        ],
      ),
    );
  }
}

class _AdminDiseaseReportCard extends StatelessWidget {
  const _AdminDiseaseReportCard({required this.item});

  final Map<String, dynamic> item;

  @override
  Widget build(BuildContext context) {
    final scan = item['scan'] as Map<String, dynamic>? ?? {};
    final farmer = scan['farmer'] as Map<String, dynamic>? ?? {};
    final farm = scan['farm'] as Map<String, dynamic>? ?? {};
    final ppl = item['ppl'] as Map?;
    final status = item['status']?.toString() ?? 'pending';
    final disease = scan['predicted_class']?.toString() ?? 'Penyakit Tanaman';
    final confidence = scan['confidence'] != null
        ? '${((double.tryParse(scan['confidence'].toString()) ?? 0) * 100).toStringAsFixed(1)}%'
        : '-';
    final farmerName = farmer['name']?.toString() ?? 'Petani';
    final farmName = farm['name']?.toString() ?? 'Lahan Petani';
    final officerName = ppl?['name']?.toString();
    final statusInfo = _reportStatusInfo(status);

    return InkWell(
      onTap: () => context.push('/ppl-cases/detail', extra: item),
      borderRadius: BorderRadius.circular(14),
      child: Container(
        margin: const EdgeInsets.only(bottom: 12),
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: padiGreen.withValues(alpha: 0.14)),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 9,
                    vertical: 5,
                  ),
                  decoration: BoxDecoration(
                    color: padiSoftGreen,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    statusInfo.label,
                    style: const TextStyle(
                      color: padiGreen,
                      fontSize: 11,
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                ),
                const Spacer(),
                Text(
                  'Keyakinan $confidence',
                  style: const TextStyle(
                    color: padiMuted,
                    fontSize: 11,
                    fontWeight: FontWeight.w800,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 10),
            Text(
              disease,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: const TextStyle(
                color: padiInk,
                fontSize: 15,
                fontWeight: FontWeight.w900,
              ),
            ),
            const SizedBox(height: 8),
            _AdminReportMeta(
              icon: Icons.person_outline_rounded,
              label: 'Petani',
              value: farmerName,
            ),
            const SizedBox(height: 5),
            _AdminReportMeta(
              icon: Icons.grass_rounded,
              label: 'Lahan',
              value: farmName,
            ),
            const SizedBox(height: 5),
            _AdminReportMeta(
              icon: Icons.badge_outlined,
              label: 'PPL/Admin',
              value: officerName == null || officerName.isEmpty
                  ? 'Belum ditangani'
                  : officerName,
            ),
            const SizedBox(height: 10),
            _StatusFlow(status: status),
            const SizedBox(height: 8),
            Text(
              statusInfo.description,
              style: const TextStyle(
                color: padiMuted,
                fontSize: 11.5,
                fontWeight: FontWeight.w600,
                height: 1.35,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _AdminReportMeta extends StatelessWidget {
  const _AdminReportMeta({
    required this.icon,
    required this.label,
    required this.value,
  });

  final IconData icon;
  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Icon(icon, color: padiGreen, size: 15),
        const SizedBox(width: 6),
        Text(
          '$label: ',
          style: const TextStyle(
            color: padiMuted,
            fontSize: 12,
            fontWeight: FontWeight.w700,
          ),
        ),
        Expanded(
          child: Text(
            value,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: const TextStyle(
              color: padiInk,
              fontSize: 12,
              fontWeight: FontWeight.w800,
            ),
          ),
        ),
      ],
    );
  }
}

class _StatusFlow extends StatelessWidget {
  const _StatusFlow({required this.status});

  final String status;

  @override
  Widget build(BuildContext context) {
    final step = switch (status) {
      'pending' => 1,
      'needs_revisit' => 2,
      'validated' || 'rejected' => 3,
      _ => 1,
    };

    return Row(
      children: [
        _FlowStep(label: 'Petani', active: step >= 1),
        _FlowLine(active: step >= 2),
        _FlowStep(label: 'PPL/Admin', active: step >= 2),
        _FlowLine(active: step >= 3),
        _FlowStep(label: 'Petani', active: step >= 3),
      ],
    );
  }
}

class _FlowStep extends StatelessWidget {
  const _FlowStep({required this.label, required this.active});

  final String label;
  final bool active;

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Icon(
          active ? Icons.check_circle_rounded : Icons.circle_outlined,
          size: 18,
          color: active ? padiGreen : padiMuted,
        ),
        const SizedBox(height: 3),
        Text(
          label,
          style: TextStyle(
            color: active ? padiGreen : padiMuted,
            fontSize: 10,
            fontWeight: FontWeight.w800,
          ),
        ),
      ],
    );
  }
}

class _FlowLine extends StatelessWidget {
  const _FlowLine({required this.active});

  final bool active;

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: Container(
        height: 2,
        margin: const EdgeInsets.only(bottom: 18),
        color: active ? padiGreen : padiGreen.withValues(alpha: 0.15),
      ),
    );
  }
}

({String label, String description}) _reportStatusInfo(String status) {
  return switch (status) {
    'validated' => (
      label: 'Valid',
      description: 'PPL/Admin mengonfirmasi penyakit sesuai laporan.',
    ),
    'rejected' => (
      label: 'Tidak Sesuai',
      description: 'PPL/Admin menolak atau menemukan anomali lain.',
    ),
    'needs_revisit' => (
      label: 'Tinjauan Ulang',
      description: 'Perlu kunjungan atau foto tambahan dari lapangan.',
    ),
    _ => (
      label: 'Menunggu Petugas',
      description: 'Laporan petani sudah masuk dan belum diputuskan.',
    ),
  };
}

class _UserCard extends ConsumerWidget {
  const _UserCard({required this.user});

  final AdminUserPreview user;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return _InfoCard(
      title: user.name,
      subtitle: '${user.email}${user.phone == null ? '' : ' • ${user.phone}'}',
      trailing: '${user.roleLabel}\n${user.statusLabel}',
      icon: Icons.person_rounded,
      actions: [
        _OptionMenu(
          label: 'Role',
          icon: Icons.badge_rounded,
          options: adminRoleOptions,
          onSelected: (role) =>
              _updateUser(context, ref, user, role: role, status: user.status),
        ),
        _OptionMenu(
          label: 'Status',
          icon: Icons.verified_user_rounded,
          options: adminUserStatusOptions,
          onSelected: (status) =>
              _updateUser(context, ref, user, role: user.role, status: status),
        ),
      ],
    );
  }
}

class _BroadcastCard extends ConsumerWidget {
  const _BroadcastCard({required this.item});

  final AdminBroadcastPreview item;

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return _InfoCard(
      title: item.title,
      subtitle: item.message.isEmpty ? item.typeLabel : item.message,
      trailing: '${item.typeLabel}\n${item.statusLabel}',
      icon: Icons.campaign_rounded,
      actions: [
        IconButton(
          tooltip: 'Edit',
          onPressed: () => _showBroadcastDialog(context, ref, item),
          icon: const Icon(Icons.edit_rounded),
        ),
        IconButton(
          tooltip: 'Hapus',
          onPressed: () => _deleteBroadcast(context, ref, item),
          icon: const Icon(Icons.delete_outline_rounded),
        ),
      ],
    );
  }
}

class _ListPane extends StatelessWidget {
  const _ListPane({
    required this.emptyText,
    required this.children,
    this.header,
  });

  final String emptyText;
  final List<Widget> children;
  final Widget? header;

  @override
  Widget build(BuildContext context) {
    return ListView(
      padding: const EdgeInsets.all(20),
      children: [
        if (header != null) ...[
          Align(alignment: Alignment.centerLeft, child: header),
          const SizedBox(height: 16),
        ],
        if (children.isEmpty) _EmptyState(text: emptyText) else ...children,
      ],
    );
  }
}

class _InfoCard extends StatelessWidget {
  const _InfoCard({
    required this.title,
    required this.subtitle,
    required this.trailing,
    required this.icon,
    this.actions = const [],
  });

  final String title;
  final String subtitle;
  final String trailing;
  final IconData icon;
  final List<Widget> actions;

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.black.withValues(alpha: 0.06)),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, color: padiGreen),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: const TextStyle(
                    color: padiInk,
                    fontWeight: FontWeight.w900,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  subtitle,
                  style: const TextStyle(color: padiMuted, fontSize: 12),
                ),
                if (actions.isNotEmpty) ...[
                  const SizedBox(height: 10),
                  Wrap(spacing: 8, runSpacing: 8, children: actions),
                ],
              ],
            ),
          ),
          const SizedBox(width: 10),
          Flexible(
            child: Text(
              trailing,
              textAlign: TextAlign.right,
              style: const TextStyle(
                color: padiGreen,
                fontWeight: FontWeight.w800,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _OptionMenu extends StatelessWidget {
  const _OptionMenu({
    required this.label,
    required this.icon,
    required this.options,
    required this.onSelected,
  });

  final String label;
  final IconData icon;
  final Map<String, String> options;
  final ValueChanged<String> onSelected;

  @override
  Widget build(BuildContext context) {
    return PopupMenuButton<String>(
      tooltip: label,
      onSelected: onSelected,
      itemBuilder: (context) => options.entries
          .map(
            (entry) =>
                PopupMenuItem(value: entry.key, child: Text(entry.value)),
          )
          .toList(),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(8),
          border: Border.all(color: Colors.black.withValues(alpha: 0.14)),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, size: 18),
            const SizedBox(width: 6),
            Text(label),
            const SizedBox(width: 4),
            const Icon(Icons.expand_more_rounded, size: 18),
          ],
        ),
      ),
    );
  }
}

class _MetricTile extends StatelessWidget {
  const _MetricTile({required this.label, required this.value});

  final String label;
  final int value;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.black.withValues(alpha: 0.06)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Text(
            label,
            style: const TextStyle(
              color: padiMuted,
              fontWeight: FontWeight.w700,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            value.toString(),
            style: Theme.of(context).textTheme.headlineSmall?.copyWith(
              color: padiInk,
              fontWeight: FontWeight.w900,
            ),
          ),
        ],
      ),
    );
  }
}

class _Section extends StatelessWidget {
  const _Section({
    required this.title,
    required this.emptyText,
    required this.children,
  });

  final String title;
  final String emptyText;
  final List<Widget> children;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.black.withValues(alpha: 0.06)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            title,
            style: const TextStyle(
              color: padiInk,
              fontSize: 18,
              fontWeight: FontWeight.w900,
            ),
          ),
          const SizedBox(height: 12),
          if (children.isEmpty)
            Text(emptyText, style: const TextStyle(color: padiMuted))
          else
            ...children,
        ],
      ),
    );
  }
}

class _InfoRow extends StatelessWidget {
  const _InfoRow({
    required this.title,
    required this.subtitle,
    required this.trailing,
  });

  final String title;
  final String subtitle;
  final String trailing;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Row(
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: const TextStyle(
                    color: padiInk,
                    fontWeight: FontWeight.w800,
                  ),
                ),
                const SizedBox(height: 3),
                Text(
                  subtitle,
                  style: const TextStyle(color: padiMuted, fontSize: 12),
                ),
              ],
            ),
          ),
          const SizedBox(width: 12),
          Flexible(
            child: Text(
              trailing,
              textAlign: TextAlign.right,
              style: const TextStyle(
                color: padiGreen,
                fontWeight: FontWeight.w800,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _EmptyState extends StatelessWidget {
  const _EmptyState({required this.text});

  final String text;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(32),
        child: Text(text, style: const TextStyle(color: padiMuted)),
      ),
    );
  }
}

class _AdminError extends StatelessWidget {
  const _AdminError({required this.message, required this.onRetry});

  final String message;
  final VoidCallback onRetry;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(
              Icons.admin_panel_settings_outlined,
              size: 48,
              color: padiMuted,
            ),
            const SizedBox(height: 12),
            Text(
              message,
              textAlign: TextAlign.center,
              style: const TextStyle(color: padiMuted),
            ),
            const SizedBox(height: 16),
            FilledButton.icon(
              onPressed: onRetry,
              icon: const Icon(Icons.refresh_rounded),
              label: const Text('Coba lagi'),
            ),
          ],
        ),
      ),
    );
  }
}

Future<void> _updateUser(
  BuildContext context,
  WidgetRef ref,
  AdminUserPreview user, {
  required String role,
  required String status,
}) async {
  await _runAdminAction(
    context,
    () => ref
        .read(adminApiServiceProvider)
        .updateUser(id: user.id, role: role, status: status),
    onSuccess: () {
      ref.invalidate(adminUsersProvider);
      ref.invalidate(adminOverviewProvider);
      ref.invalidate(adminAuditLogsProvider);
    },
  );
}

Future<void> _showBroadcastDialog(
  BuildContext context,
  WidgetRef ref, [
  AdminBroadcastPreview? item,
]) async {
  final titleController = TextEditingController(text: item?.title ?? '');
  final messageController = TextEditingController(text: item?.message ?? '');
  var type = item?.type ?? 'info';
  var status = item?.status ?? 'draft';

  await showDialog<void>(
    context: context,
    builder: (dialogContext) {
      return StatefulBuilder(
        builder: (context, setDialogState) {
          return AlertDialog(
            title: Text(item == null ? 'Broadcast baru' : 'Edit broadcast'),
            content: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  TextField(
                    controller: titleController,
                    decoration: const InputDecoration(labelText: 'Judul'),
                  ),
                  const SizedBox(height: 12),
                  TextField(
                    controller: messageController,
                    minLines: 3,
                    maxLines: 5,
                    decoration: const InputDecoration(labelText: 'Pesan'),
                  ),
                  const SizedBox(height: 12),
                  DropdownButtonFormField<String>(
                    initialValue: type,
                    decoration: const InputDecoration(labelText: 'Tipe'),
                    items: adminBroadcastTypeOptions.entries
                        .map(
                          (entry) => DropdownMenuItem(
                            value: entry.key,
                            child: Text(entry.value),
                          ),
                        )
                        .toList(),
                    onChanged: (value) =>
                        setDialogState(() => type = value ?? type),
                  ),
                  const SizedBox(height: 12),
                  DropdownButtonFormField<String>(
                    initialValue: status,
                    decoration: const InputDecoration(labelText: 'Status'),
                    items: adminBroadcastStatusOptions.entries
                        .map(
                          (entry) => DropdownMenuItem(
                            value: entry.key,
                            child: Text(entry.value),
                          ),
                        )
                        .toList(),
                    onChanged: (value) =>
                        setDialogState(() => status = value ?? status),
                  ),
                ],
              ),
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.of(dialogContext).pop(),
                child: const Text('Batal'),
              ),
              FilledButton(
                onPressed: () async {
                  final title = titleController.text.trim();
                  final message = messageController.text.trim();
                  if (title.isEmpty || message.isEmpty) {
                    _showSnack(context, 'Judul dan pesan wajib diisi.');
                    return;
                  }

                  await _runAdminAction(
                    context,
                    () {
                      if (item == null) {
                        return ref
                            .read(adminApiServiceProvider)
                            .createBroadcast(
                              title: title,
                              message: message,
                              type: type,
                              status: status,
                            );
                      }

                      return ref
                          .read(adminApiServiceProvider)
                          .updateBroadcast(
                            id: item.id,
                            title: title,
                            message: message,
                            type: type,
                            status: status,
                          );
                    },
                    onSuccess: () {
                      ref.invalidate(adminBroadcastsProvider);
                      ref.invalidate(adminOverviewProvider);
                      ref.invalidate(adminAuditLogsProvider);
                      Navigator.of(dialogContext).pop();
                    },
                  );
                },
                child: const Text('Simpan'),
              ),
            ],
          );
        },
      );
    },
  );

  titleController.dispose();
  messageController.dispose();
}

Future<void> _deleteBroadcast(
  BuildContext context,
  WidgetRef ref,
  AdminBroadcastPreview item,
) async {
  final confirmed = await showDialog<bool>(
    context: context,
    builder: (context) => AlertDialog(
      title: const Text('Hapus broadcast?'),
      content: Text(item.title),
      actions: [
        TextButton(
          onPressed: () => Navigator.of(context).pop(false),
          child: const Text('Batal'),
        ),
        FilledButton(
          onPressed: () => Navigator.of(context).pop(true),
          child: const Text('Hapus'),
        ),
      ],
    ),
  );

  if (confirmed != true || !context.mounted) {
    return;
  }

  await _runAdminAction(
    context,
    () => ref.read(adminApiServiceProvider).deleteBroadcast(item.id),
    onSuccess: () {
      ref.invalidate(adminBroadcastsProvider);
      ref.invalidate(adminOverviewProvider);
      ref.invalidate(adminAuditLogsProvider);
    },
  );
}

Future<void> _runAdminAction(
  BuildContext context,
  Future<Object?> Function() action, {
  required VoidCallback onSuccess,
}) async {
  try {
    await action();
    onSuccess();
    if (context.mounted) {
      _showSnack(context, 'Perubahan tersimpan.');
    }
  } catch (error) {
    if (context.mounted) {
      _showSnack(context, error.toString());
    }
  }
}

void _refreshAdmin(WidgetRef ref) {
  ref.invalidate(adminOverviewProvider);
  ref.invalidate(adminUsersProvider);
  ref.invalidate(adminBroadcastsProvider);
  ref.invalidate(adminAuditLogsProvider);
}

void _showSnack(BuildContext context, String message) {
  ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(message)));
}
