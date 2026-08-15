import 'package:padi/core/router/app_router.dart';
import 'package:padi/features/auth/presentation/widgets/auth_header.dart';
import 'package:padi/features/auth/presentation/widgets/padi_theme.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class HomeScreen extends ConsumerWidget {
  const HomeScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final auth = ref.watch(authControllerProvider);
    final user = auth.state.user;

    return Scaffold(
      backgroundColor: const Color(0xFFF6FAF3),
      body: SafeArea(
        child: ListView(
          padding: const EdgeInsets.all(22),
          children: [
            Row(
              children: [
                const PadiLogo(size: 58),
                const SizedBox(width: 14),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'P.A.D.I.',
                        style: Theme.of(context).textTheme.titleLarge?.copyWith(
                          color: padiInk,
                          fontWeight: FontWeight.w900,
                        ),
                      ),
                      Text(
                        'Akses awal autentikasi',
                        style: Theme.of(context).textTheme.bodyMedium?.copyWith(color: padiMuted),
                      ),
                    ],
                  ),
                ),
                IconButton(
                  tooltip: 'Profil',
                  onPressed: () => context.go('/profile'),
                  icon: const Icon(Icons.account_circle_rounded),
                ),
              ],
            ),
            const SizedBox(height: 26),
            Container(
              padding: const EdgeInsets.all(22),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(24),
                border: Border.all(color: Colors.black.withValues(alpha: 0.05)),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Halo, ${user?.name ?? 'Pengguna'}',
                    style: Theme.of(context).textTheme.headlineSmall?.copyWith(
                      color: padiInk,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                  const SizedBox(height: 14),
                  _InfoRow(label: 'Role', value: user?.roleLabel ?? user?.role ?? '-'),
                  _InfoRow(label: 'Status', value: user?.statusLabel ?? user?.status ?? '-'),
                  _InfoRow(label: 'Email', value: user?.email ?? '-'),
                ],
              ),
            ),
            const SizedBox(height: 18),
            FilledButton.icon(
              onPressed: () => context.go('/profile'),
              icon: const Icon(Icons.manage_accounts_rounded),
              label: const Text('Kelola profil'),
            ),
            const SizedBox(height: 10),
            OutlinedButton.icon(
              onPressed: () => ref.read(authControllerProvider).logout(),
              icon: const Icon(Icons.logout_rounded),
              label: const Text('Logout perangkat ini'),
            ),
          ],
        ),
      ),
    );
  }
}

class _InfoRow extends StatelessWidget {
  const _InfoRow({required this.label, required this.value});

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: Row(
        children: [
          SizedBox(
            width: 72,
            child: Text(label, style: const TextStyle(color: padiMuted, fontWeight: FontWeight.w600)),
          ),
          Expanded(
            child: Text(value, style: const TextStyle(color: padiInk, fontWeight: FontWeight.w700)),
          ),
        ],
      ),
    );
  }
}
