import 'package:flutter/material.dart';

class HomeMenu extends StatelessWidget {
  const HomeMenu({
    super.key,
    required this.onFarmTap,
    required this.onSeasonTap,
    required this.onFertilizerTap,
    required this.onHarvestTap,
  });

  final VoidCallback onFarmTap;
  final VoidCallback onSeasonTap;
  final VoidCallback onFertilizerTap;
  final VoidCallback onHarvestTap;

  @override
  Widget build(BuildContext context) {
    return GridView.count(
      crossAxisCount: 2,
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      crossAxisSpacing: 12,
      mainAxisSpacing: 12,
      childAspectRatio: 1.45,
      children: [
        _MenuItem(
          icon: Icons.grass_rounded,
          title: 'Lahan Saya',
          subtitle: 'Kelola lahan',
          onTap: onFarmTap,
        ),
        _MenuItem(
          icon: Icons.spa_rounded,
          title: 'Musim Tanam',
          subtitle: 'Pantau tanaman',
          onTap: onSeasonTap,
        ),
        _MenuItem(
          icon: Icons.calculate_rounded,
          title: 'Hitung Pupuk',
          subtitle: 'Kebutuhan pupuk',
          onTap: onFertilizerTap,
        ),
        _MenuItem(
          icon: Icons.agriculture_rounded,
          title: 'Catatan Panen',
          subtitle: 'Kelola hasil panen',
          onTap: onHarvestTap,
        ),
      ],
    );
  }
}

class _MenuItem extends StatelessWidget {
  const _MenuItem({
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
      borderRadius: BorderRadius.circular(20),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(20),
        child: Padding(
          padding: const EdgeInsets.all(15),
          child: Row(
            children: [
              Container(
                width: 45,
                height: 45,
                decoration: BoxDecoration(
                  color: const Color(0xFFE8F2EC),
                  borderRadius: BorderRadius.circular(14),
                ),
                child: Icon(
                  icon,
                  color: const Color(0xFF075C3D),
                  size: 25,
                ),
              ),
              const SizedBox(width: 11),
              Expanded(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      title,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        color: Color(0xFF173D2D),
                        fontSize: 14,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      subtitle,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        color: Color(0xFF7A857F),
                        fontSize: 11,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}