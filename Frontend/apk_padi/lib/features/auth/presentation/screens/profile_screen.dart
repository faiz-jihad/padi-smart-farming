import 'package:padi/core/router/app_router.dart';
import 'package:padi/features/auth/presentation/widgets/auth_fields.dart';
import 'package:padi/features/auth/presentation/widgets/padi_theme.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

class ProfileScreen extends ConsumerStatefulWidget {
  const ProfileScreen({super.key});

  @override
  ConsumerState<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends ConsumerState<ProfileScreen> {
  final _nameController = TextEditingController();
  final _phoneController = TextEditingController();
  int? _loadedUserId;

  @override
  void dispose() {
    _nameController.dispose();
    _phoneController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final auth = ref.watch(authControllerProvider);
    final state = auth.state;
    final user = state.user;

    if (user != null && _loadedUserId != user.id) {
      _loadedUserId = user.id;
      _nameController.text = user.name;
      _phoneController.text = user.phone ?? '';
    }

    return Scaffold(
      appBar: AppBar(
        title: const Text('Profil'),
        leading: IconButton(
          tooltip: 'Kembali',
          onPressed: () => context.go('/home'),
          icon: const Icon(Icons.arrow_back_rounded),
        ),
      ),
      body: SafeArea(
        child: ListView(
          padding: const EdgeInsets.all(22),
          children: [
            const Text(
              'Data Dasar',
              style: TextStyle(fontSize: 24, fontWeight: FontWeight.w900, color: padiInk),
            ),
            const SizedBox(height: 8),
            Text(
              'Email tidak dapat diubah sebelum verifikasi email tersedia.',
              style: Theme.of(context).textTheme.bodyMedium?.copyWith(color: padiMuted),
            ),
            const SizedBox(height: 22),
            ErrorBanner(message: state.message ?? ''),
            if (state.message != null) const SizedBox(height: 12),
            PadiTextField(
              controller: _nameController,
              label: 'Nama lengkap',
              errorText: state.fieldErrors['name']?.first,
              prefixIcon: Icons.person_outline_rounded,
            ),
            const SizedBox(height: 14),
            PadiTextField(
              controller: _phoneController,
              label: 'Nomor telepon',
              keyboardType: TextInputType.phone,
              errorText: state.fieldErrors['phone']?.first,
              prefixIcon: Icons.phone_rounded,
            ),
            const SizedBox(height: 14),
            TextField(
              enabled: false,
              controller: TextEditingController(text: user?.email ?? ''),
              decoration: const InputDecoration(
                labelText: 'Email',
                prefixIcon: Icon(Icons.alternate_email_rounded),
              ),
            ),
            const SizedBox(height: 22),
            FilledButton(
              onPressed: state.isSubmitting
                  ? null
                  : () => ref.read(authControllerProvider).updateProfile(
                        name: _nameController.text.trim(),
                        phone: _phoneController.text.trim(),
                      ),
              child: state.isSubmitting
                  ? const SizedBox.square(
                      dimension: 20,
                      child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                    )
                  : const Text('Simpan profil'),
            ),
            const SizedBox(height: 10),
            OutlinedButton.icon(
              onPressed: () => context.go('/profile/password'),
              icon: const Icon(Icons.password_rounded),
              label: const Text('Ubah password'),
            ),
            const SizedBox(height: 10),
            OutlinedButton.icon(
              onPressed: () => ref.read(authControllerProvider).logout(allDevices: true),
              icon: const Icon(Icons.devices_other_rounded),
              label: const Text('Logout semua perangkat'),
            ),
          ],
        ),
      ),
    );
  }
}
