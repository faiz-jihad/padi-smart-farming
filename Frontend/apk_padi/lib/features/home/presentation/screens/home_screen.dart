import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:padi/features/auth/presentation/controllers/auth_provider.dart';

const Color primaryGreen = Color(0xFF075C3D);
const Color backgroundColor = Color(0xFFF7F9F4);
const Color textDark = Color(0xFF183D2D);
const Color yellow = Color(0xFFF2C94C);

class HomeScreen extends ConsumerStatefulWidget {
  const HomeScreen({super.key});

  @override
  ConsumerState<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends ConsumerState<HomeScreen> {
  int _currentIndex = 0;

  void _handleNavigation(int index) {
    if (index == 1) {
      context.go('/farms');
      return;
    }
    if (index == 2) {
      context.go('/plant-check');
      return;
    }

    if (index == 3) {
      context.go('/marketplace');
      return;
    }

    if (index == 4) {
      context.go('/profile');
      return;
    }

    setState(() {
      _currentIndex = index;
    });
  }

  void _openPlantCheck() {
    context.go('/plant-check');
  }

  @override
  Widget build(BuildContext context) {
    final auth = ref.watch(authControllerProvider);
    final user = auth.state.user;

    final userName = user?.name.trim().isNotEmpty == true
        ? user!.name.trim()
        : 'Petani';

    return Scaffold(
      backgroundColor: backgroundColor,
      body: SafeArea(
        child: RefreshIndicator(
          color: primaryGreen,
          onRefresh: () async {
            await ref.read(authControllerProvider).restoreSession();
          },
          child: CustomScrollView(
            physics: const AlwaysScrollableScrollPhysics(),
            slivers: [
              SliverPadding(
                padding: const EdgeInsets.fromLTRB(20, 18, 20, 0),
                sliver: SliverToBoxAdapter(
                  child: _HomeHeader(name: userName, onNotificationTap: () {}),
                ),
              ),
              SliverPadding(
                padding: const EdgeInsets.fromLTRB(20, 20, 20, 0),
                sliver: SliverToBoxAdapter(child: _WelcomeCard(name: userName)),
              ),
              SliverPadding(
                padding: const EdgeInsets.fromLTRB(20, 18, 20, 0),
                sliver: SliverToBoxAdapter(
                  child: _PlantCheckCard(onTap: _openPlantCheck),
                ),
              ),
              SliverPadding(
                padding: const EdgeInsets.fromLTRB(20, 18, 20, 0),
                sliver: const SliverToBoxAdapter(child: _SummaryCard()),
              ),
              SliverPadding(
                padding: const EdgeInsets.fromLTRB(20, 24, 20, 12),
                sliver: const SliverToBoxAdapter(
                  child: Text(
                    'Yang ingin dilakukan?',
                    style: TextStyle(
                      color: textDark,
                      fontSize: 20,
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                ),
              ),
              SliverPadding(
                padding: const EdgeInsets.fromLTRB(20, 0, 20, 20),
                sliver: SliverToBoxAdapter(
                  child: _HomeMenu(
                    onFarmTap: () {
                      context.go('/farms');
                    },
                    onSeasonTap: () {
                      context.push('/land/season/start');
                    },
                    onFertilizerTap: () {
                      context.push('/fertilizer');
                    },
                    onHarvestTap: () {
                      context.push('/harvest');
                    },
                    onCalendarTap: () {
                      context.push('/planting-calendar');
                    },
                  ),
                ),
              ),

              SliverPadding(
                padding: const EdgeInsets.fromLTRB(20, 0, 20, 12),
                sliver: SliverToBoxAdapter(
                  child: _CommunityAlertButton(
                    onTap: () {
                      context.push('/community-alert');
                    },
                  ),
                ),
              ),
              SliverPadding(
                padding: const EdgeInsets.fromLTRB(20, 0, 20, 12),
                sliver: SliverToBoxAdapter(
                  child: _ReportConditionButton(
                    onTap: () {
                      context.push('/community-alert/report');
                    },
                  ),
                ),
              ),
              SliverPadding(
                padding: const EdgeInsets.fromLTRB(20, 0, 20, 30),
                sliver: const SliverToBoxAdapter(child: _HelpCard()),
              ),
            ],
          ),
        ),
      ),
      bottomNavigationBar: _BottomNavigation(
        currentIndex: _currentIndex,
        onTap: _handleNavigation,
        onCameraTap: _openPlantCheck,
      ),
    );
  }
}

class _HomeHeader extends StatelessWidget {
  const _HomeHeader({required this.name, required this.onNotificationTap});

  final String name;
  final VoidCallback onNotificationTap;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Container(
          width: 62,
          height: 62,
          padding: const EdgeInsets.all(7),
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(20),
          ),
          child: Image.asset(
            'assets/images/padi-logo.png',
            fit: BoxFit.contain,
            errorBuilder: (context, error, stackTrace) {
              return const Icon(
                Icons.eco_rounded,
                color: primaryGreen,
                size: 38,
              );
            },
          ),
        ),
        const SizedBox(width: 14),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'P.A.D.I.',
                style: TextStyle(
                  color: primaryGreen,
                  fontSize: 26,
                  fontWeight: FontWeight.w900,
                ),
              ),
              const SizedBox(height: 2),
              Text(
                'Halo, $name 👋',
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(
                  color: Color(0xFF617068),
                  fontSize: 17,
                  fontWeight: FontWeight.w700,
                ),
              ),
            ],
          ),
        ),
        Material(
          color: Colors.white,
          borderRadius: BorderRadius.circular(20),
          child: InkWell(
            onTap: onNotificationTap,
            borderRadius: BorderRadius.circular(20),
            child: const SizedBox(
              width: 62,
              height: 62,
              child: Icon(
                Icons.notifications_none_rounded,
                color: primaryGreen,
                size: 34,
              ),
            ),
          ),
        ),
      ],
    );
  }
}

class _WelcomeCard extends StatelessWidget {
  const _WelcomeCard({required this.name});

  final String name;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: const Color(0xFFEAF5EF),
        borderRadius: BorderRadius.circular(26),
      ),
      child: Row(
        children: [
          Container(
            width: 64,
            height: 64,
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(20),
            ),
            child: const Icon(
              Icons.agriculture_rounded,
              color: primaryGreen,
              size: 34,
            ),
          ),
          const SizedBox(width: 16),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Selamat datang,',
                  style: TextStyle(
                    color: Color(0xFF69766F),
                    fontSize: 15,
                    fontWeight: FontWeight.w600,
                  ),
                ),
                const SizedBox(height: 3),
                Text(
                  name,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(
                    color: Color(0xFF123C2B),
                    fontSize: 22,
                    fontWeight: FontWeight.w900,
                  ),
                ),
                const SizedBox(height: 5),
                const Text(
                  'Mari rawat sawah bersama P.A.D.I.',
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: TextStyle(color: Color(0xFF617068), fontSize: 13),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _PlantCheckCard extends StatelessWidget {
  const _PlantCheckCard({required this.onTap});

  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: primaryGreen,
      borderRadius: BorderRadius.circular(26),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(26),
        child: Padding(
          padding: const EdgeInsets.all(20),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Container(
                    width: 58,
                    height: 58,
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.15),
                      borderRadius: BorderRadius.circular(18),
                    ),
                    child: const Icon(
                      Icons.camera_alt_rounded,
                      color: yellow,
                      size: 32,
                    ),
                  ),
                  const SizedBox(width: 15),
                  const Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          'Periksa Kondisi Padi',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 20,
                            fontWeight: FontWeight.w900,
                          ),
                        ),
                        SizedBox(height: 4),
                        Text(
                          'Foto daun padi untuk mengetahui kondisi tanaman.',
                          style: TextStyle(
                            color: Colors.white70,
                            fontSize: 13,
                            height: 1.3,
                          ),
                        ),
                      ],
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 17),
              SizedBox(
                width: double.infinity,
                height: 54,
                child: ElevatedButton.icon(
                  onPressed: onTap,
                  icon: const Icon(Icons.camera_alt_rounded, size: 23),
                  label: const Text(
                    'Periksa Padi',
                    style: TextStyle(fontSize: 17, fontWeight: FontWeight.w900),
                  ),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: yellow,
                    foregroundColor: textDark,
                    elevation: 0,
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(17),
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _SummaryCard extends StatelessWidget {
  const _SummaryCard();

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.fromLTRB(20, 20, 20, 19),
      decoration: BoxDecoration(
        color: primaryGreen,
        borderRadius: BorderRadius.circular(26),
      ),
      child: const Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Ringkasan Pertanian',
            style: TextStyle(
              color: Colors.white,
              fontSize: 20,
              fontWeight: FontWeight.w900,
            ),
          ),
          SizedBox(height: 20),
          Row(
            children: [
              Expanded(
                child: _SummaryItem(
                  icon: Icons.grass_rounded,
                  value: '0',
                  label: 'Lahan',
                ),
              ),
              Expanded(
                child: _SummaryItem(
                  icon: Icons.spa_rounded,
                  value: '0',
                  label: 'Musim Tanam',
                ),
              ),
              Expanded(
                child: _SummaryItem(
                  icon: Icons.check_circle_outline_rounded,
                  value: '0',
                  label: 'Aktivitas',
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _SummaryItem extends StatelessWidget {
  const _SummaryItem({
    required this.icon,
    required this.value,
    required this.label,
  });

  final IconData icon;
  final String value;
  final String label;

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Icon(icon, color: yellow, size: 31),
        const SizedBox(height: 7),
        Text(
          value,
          style: const TextStyle(
            color: Colors.white,
            fontSize: 27,
            fontWeight: FontWeight.w900,
          ),
        ),
        const SizedBox(height: 2),
        Text(
          label,
          textAlign: TextAlign.center,
          maxLines: 1,
          overflow: TextOverflow.ellipsis,
          style: const TextStyle(
            color: Colors.white70,
            fontSize: 12,
            fontWeight: FontWeight.w600,
          ),
        ),
      ],
    );
  }
}

class _HomeMenu extends StatelessWidget {
  const _HomeMenu({
    required this.onFarmTap,
    required this.onSeasonTap,
    required this.onFertilizerTap,
    required this.onHarvestTap,
    required this.onCalendarTap,
  });

  final VoidCallback onFarmTap;
  final VoidCallback onSeasonTap;
  final VoidCallback onFertilizerTap;
  final VoidCallback onHarvestTap;
  final VoidCallback onCalendarTap;

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        GridView.count(
          crossAxisCount: 2,
          crossAxisSpacing: 14,
          mainAxisSpacing: 14,
          childAspectRatio: 1.0,
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          children: [
            _MenuCard(
              icon: Icons.grass_rounded,
              title: 'Lahan Saya',
              subtitle: 'Kelola sawah',
              onTap: onFarmTap,
            ),
            _MenuCard(
              icon: Icons.spa_rounded,
              title: 'Musim Tanam',
              subtitle: 'Pantau tanaman',
              onTap: onSeasonTap,
            ),
            _MenuCard(
              icon: Icons.calculate_rounded,
              title: 'Hitung Pupuk',
              subtitle: 'Kebutuhan pupuk',
              onTap: onFertilizerTap,
            ),
            _MenuCard(
              icon: Icons.agriculture_rounded,
              title: 'Catatan Panen',
              subtitle: 'Kelola hasil panen',
              onTap: onHarvestTap,
            ),
          ],
        ),
        const SizedBox(height: 14),
        _CalendarMenuCard(
          onTap: onCalendarTap,
        ),
      ],
    );
  }
}
class _CalendarMenuCard extends StatelessWidget {
  const _CalendarMenuCard({
    required this.onTap,
  });

  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.white,
      borderRadius: BorderRadius.circular(24),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(24),
        child: Padding(
          padding: const EdgeInsets.all(17),
          child: Row(
            children: [
              Container(
                width: 58,
                height: 58,
                decoration: BoxDecoration(
                  color: const Color(0xFFEAF5EF),
                  borderRadius: BorderRadius.circular(18),
                ),
                child: const Icon(
                  Icons.calendar_month_rounded,
                  color: primaryGreen,
                  size: 31,
                ),
              ),
              const SizedBox(width: 15),
              const Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Kalender Tanam',
                      style: TextStyle(
                        color: textDark,
                        fontSize: 16,
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                    SizedBox(height: 4),
                    Text(
                      'Lihat waktu tanam yang dianjurkan',
                      style: TextStyle(
                        color: Color(0xFF69766F),
                        fontSize: 12,
                      ),
                    ),
                  ],
                ),
              ),
              const Icon(
                Icons.arrow_forward_ios_rounded,
                color: primaryGreen,
                size: 17,
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _MenuCard extends StatelessWidget {
  const _MenuCard({
    required this.icon,
    required this.title,
    required this.subtitle,
    required this.onTap,
  });

  final IconData icon;
  final String title;
  final String subtitle;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.white,
      borderRadius: BorderRadius.circular(24),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(24),
        child: Padding(
          padding: const EdgeInsets.all(17),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: 58,
                height: 58,
                decoration: BoxDecoration(
                  color: const Color(0xFFEAF5EF),
                  borderRadius: BorderRadius.circular(18),
                ),
                child: Icon(icon, color: primaryGreen, size: 31),
              ),
              const SizedBox(height: 11),
              Text(
                title,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(
                  color: textDark,
                  fontSize: 16,
                  fontWeight: FontWeight.w900,
                ),
              ),
              const SizedBox(height: 4),
              Text(
                subtitle,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: const TextStyle(color: Color(0xFF69766F), fontSize: 12),
              ),
            ],
          ),
        ),
      ),
    );
  }
}


class _CommunityAlertButton extends StatelessWidget {
  const _CommunityAlertButton({required this.onTap});

  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.white,
      borderRadius: BorderRadius.circular(20),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(20),
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 15),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(20),
            border: Border.all(color: primaryGreen.withValues(alpha: 0.08)),
          ),
          child: Row(
            children: [
              Container(
                width: 50,
                height: 50,
                decoration: BoxDecoration(
                  color: const Color(0xFFFFF7DC),
                  borderRadius: BorderRadius.circular(16),
                ),
                child: const Icon(
                  Icons.warning_amber_rounded,
                  color: Color(0xFF946E00),
                  size: 27,
                ),
              ),
              const SizedBox(width: 14),
              const Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Community Alert',
                      style: TextStyle(
                        color: textDark,
                        fontSize: 16,
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                    SizedBox(height: 3),
                    Text(
                      'Lihat peringatan di sekitar Anda',
                      style: TextStyle(color: Color(0xFF69766F), fontSize: 12),
                    ),
                  ],
                ),
              ),
              const Icon(
                Icons.arrow_forward_ios_rounded,
                color: primaryGreen,
                size: 17,
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _ReportConditionButton extends StatelessWidget {
  const _ReportConditionButton({required this.onTap});

  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.white,
      borderRadius: BorderRadius.circular(20),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(20),
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 15),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(20),
            border: Border.all(color: primaryGreen.withValues(alpha: 0.08)),
          ),
          child: Row(
            children: [
              Container(
                width: 50,
                height: 50,
                decoration: BoxDecoration(
                  color: const Color(0xFFEAF5EF),
                  borderRadius: BorderRadius.circular(16),
                ),
                child: const Icon(
                  Icons.campaign_rounded,
                  color: primaryGreen,
                  size: 27,
                ),
              ),
              const SizedBox(width: 14),
              const Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Lapor Kondisi',
                      style: TextStyle(
                        color: textDark,
                        fontSize: 16,
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                    SizedBox(height: 3),
                    Text(
                      'Laporkan kondisi atau masalah di sawah',
                      style: TextStyle(color: Color(0xFF69766F), fontSize: 12),
                    ),
                  ],
                ),
              ),
              const Icon(
                Icons.arrow_forward_ios_rounded,
                color: primaryGreen,
                size: 17,
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _HelpCard extends StatelessWidget {
  const _HelpCard();

  @override
  Widget build(BuildContext context) {
    return Material(
      color: const Color(0xFFFFF7DC),
      borderRadius: BorderRadius.circular(22),
      child: InkWell(
        onTap: () {},
        borderRadius: BorderRadius.circular(22),
        child: const Padding(
          padding: EdgeInsets.all(17),
          child: Row(
            children: [
              SizedBox(
                width: 52,
                height: 52,
                child: Icon(
                  Icons.volume_up_rounded,
                  color: Color(0xFF946E00),
                  size: 29,
                ),
              ),
              SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Butuh bantuan?',
                      style: TextStyle(
                        color: Color(0xFF5B4808),
                        fontSize: 15,
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                    SizedBox(height: 4),
                    Text(
                      'Dengarkan panduan suara untuk menggunakan P.A.D.I.',
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: TextStyle(
                        color: Color(0xFF75652B),
                        fontSize: 12,
                        height: 1.3,
                      ),
                    ),
                  ],
                ),
              ),
              Icon(
                Icons.arrow_forward_ios_rounded,
                size: 17,
                color: Color(0xFF946E00),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _BottomNavigation extends StatelessWidget {
  const _BottomNavigation({
    required this.currentIndex,
    required this.onTap,
    required this.onCameraTap,
  });

  final int currentIndex;
  final ValueChanged<int> onTap;
  final VoidCallback onCameraTap;

  @override
  Widget build(BuildContext context) {
    return Container(
      color: Colors.white,
      child: SafeArea(
        top: false,
        child: SizedBox(
          height: 78,
          child: Row(
            children: [
              Expanded(
                child: _NavItem(
                  icon: Icons.home_rounded,
                  label: 'Beranda',
                  active: currentIndex == 0,
                  onTap: () => onTap(0),
                ),
              ),
              Expanded(
                child: _NavItem(
                  icon: Icons.grass_rounded,
                  label: 'Lahan',
                  active: currentIndex == 1,
                  onTap: () => onTap(1),
                ),
              ),
              Expanded(child: _CameraNavItem(onTap: onCameraTap)),
              Expanded(
                child: _NavItem(
                  icon: Icons.storefront_rounded,
                  label: 'Toko',
                  active: currentIndex == 3,
                  onTap: () => onTap(3),
                ),
              ),
              Expanded(
                child: _NavItem(
                  icon: Icons.person_outline_rounded,
                  label: 'Profil',
                  active: currentIndex == 4,
                  onTap: () => onTap(4),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _NavItem extends StatelessWidget {
  const _NavItem({
    required this.icon,
    required this.label,
    required this.active,
    required this.onTap,
  });

  final IconData icon;
  final String label;
  final bool active;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            icon,
            color: active ? primaryGreen : const Color(0xFF59645E),
            size: 27,
          ),
          const SizedBox(height: 3),
          Text(
            label,
            style: TextStyle(
              color: active ? primaryGreen : const Color(0xFF59645E),
              fontSize: 11,
              fontWeight: active ? FontWeight.w800 : FontWeight.w600,
            ),
          ),
        ],
      ),
    );
  }
}

class _CameraNavItem extends StatelessWidget {
  const _CameraNavItem({required this.onTap});

  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Container(
            width: 58,
            height: 48,
            decoration: BoxDecoration(
              color: primaryGreen,
              borderRadius: BorderRadius.circular(18),
            ),
            child: const Icon(
              Icons.camera_alt_rounded,
              color: Colors.white,
              size: 28,
            ),
          ),
          const SizedBox(height: 3),
          const Text(
            'Periksa',
            style: TextStyle(
              color: primaryGreen,
              fontSize: 11,
              fontWeight: FontWeight.w900,
            ),
          ),
        ],
      ),
    );
  }
}
