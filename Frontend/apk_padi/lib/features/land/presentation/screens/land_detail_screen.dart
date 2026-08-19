import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

const Color detailGreen = Color(0xFF075C3D);
const Color detailBackground = Color(0xFFF7F9F4);
const Color detailText = Color(0xFF183D2D);

class LandDetailScreen extends StatelessWidget {
  const LandDetailScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: detailBackground,
      appBar: AppBar(
        backgroundColor: detailBackground,
        elevation: 0,
        surfaceTintColor: Colors.transparent,
        leading: IconButton(
          onPressed: () => context.pop(),
          icon: const Icon(
            Icons.arrow_back_rounded,
            color: detailGreen,
            size: 32,
          ),
        ),
        title: const Text(
          'Detail Lahan',
          style: TextStyle(
            color: detailText,
            fontSize: 24,
            fontWeight: FontWeight.w900,
          ),
        ),
        actions: [
          IconButton(
            onPressed: () {},
            icon: const Icon(
              Icons.more_vert_rounded,
              color: detailGreen,
              size: 30,
            ),
          ),
        ],
      ),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(20, 8, 20, 30),
        children: [
          Container(
            padding: const EdgeInsets.all(22),
            decoration: BoxDecoration(
              color: detailGreen,
              borderRadius: BorderRadius.circular(28),
            ),
            child: const Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Icon(
                      Icons.grass_rounded,
                      color: Color(0xFFF2C94C),
                      size: 38,
                    ),
                    SizedBox(width: 12),
                    Expanded(
                      child: Text(
                        'Sawah Blok A',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 23,
                          fontWeight: FontWeight.w900,
                        ),
                      ),
                    ),
                  ],
                ),
                SizedBox(height: 18),
                Row(
                  children: [
                    Icon(
                      Icons.location_on_outlined,
                      color: Colors.white70,
                      size: 21,
                    ),
                    SizedBox(width: 7),
                    Expanded(
                      child: Text(
                        'Desa Karanganyar',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 15,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                  ],
                ),
                SizedBox(height: 10),
                Row(
                  children: [
                    Icon(
                      Icons.straighten_rounded,
                      color: Colors.white70,
                      size: 21,
                    ),
                    SizedBox(width: 7),
                    Text(
                      '1.200 m²',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 15,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),

          const SizedBox(height: 20),

          const _SectionTitle(
            title: 'Kondisi lahan',
          ),

          const SizedBox(height: 12),

          Row(
            children: [
              Expanded(
                child: _InfoCard(
                  icon: Icons.spa_rounded,
                  title: 'Tanaman',
                  value: 'Padi',
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: _InfoCard(
                  icon: Icons.check_circle_outline_rounded,
                  title: 'Status',
                  value: 'Aktif',
                ),
              ),
            ],
          ),

          const SizedBox(height: 24),

          const _SectionTitle(
            title: 'Yang bisa dilakukan',
          ),

          const SizedBox(height: 12),

          _ActionCard(
            icon: Icons.camera_alt_rounded,
            title: 'Periksa kondisi padi',
            description: 'Foto daun padi untuk mengecek kondisi tanaman.',
            onTap: () {
              context.push('/plant-check');
            },
          ),

          const SizedBox(height: 12),

          _ActionCard(
            icon: Icons.spa_rounded,
            title: 'Mulai musim tanam',
            description: 'Catat musim tanam baru untuk lahan ini.',
            onTap: () {},
          ),

          const SizedBox(height: 12),

          _ActionCard(
            icon: Icons.timeline_rounded,
            title: 'Lihat kegiatan',
            description: 'Lihat kegiatan yang sudah dilakukan di sawah.',
            onTap: () {},
          ),

          const SizedBox(height: 24),

          Container(
            padding: const EdgeInsets.all(18),
            decoration: BoxDecoration(
              color: const Color(0xFFFFF8DF),
              borderRadius: BorderRadius.circular(20),
            ),
            child: const Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Icon(
                  Icons.volume_up_rounded,
                  color: Color(0xFF946E00),
                  size: 29,
                ),
                SizedBox(width: 12),
                Expanded(
                  child: Text(
                    'Pilih kegiatan yang ingin Anda lakukan pada lahan ini.',
                    style: TextStyle(
                      color: Color(0xFF5B4808),
                      fontSize: 14,
                      height: 1.4,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _SectionTitle extends StatelessWidget {
  const _SectionTitle({
    required this.title,
  });

  final String title;

  @override
  Widget build(BuildContext context) {
    return Text(
      title,
      style: const TextStyle(
        color: detailText,
        fontSize: 19,
        fontWeight: FontWeight.w900,
      ),
    );
  }
}

class _InfoCard extends StatelessWidget {
  const _InfoCard({
    required this.icon,
    required this.title,
    required this.value,
  });

  final IconData icon;
  final String title;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(17),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(22),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(
            icon,
            color: detailGreen,
            size: 30,
          ),
          const SizedBox(height: 12),
          Text(
            title,
            style: const TextStyle(
              color: Color(0xFF69766F),
              fontSize: 13,
            ),
          ),
          const SizedBox(height: 3),
          Text(
            value,
            style: const TextStyle(
              color: detailText,
              fontSize: 17,
              fontWeight: FontWeight.w900,
            ),
          ),
        ],
      ),
    );
  }
}

class _ActionCard extends StatelessWidget {
  const _ActionCard({
    required this.icon,
    required this.title,
    required this.description,
    required this.onTap,
  });

  final IconData icon;
  final String title;
  final String description;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.white,
      borderRadius: BorderRadius.circular(22),
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(22),
        child: Padding(
          padding: const EdgeInsets.all(17),
          child: Row(
            children: [
              Container(
                width: 56,
                height: 56,
                decoration: BoxDecoration(
                  color: const Color(0xFFEAF5EF),
                  borderRadius: BorderRadius.circular(17),
                ),
                child: Icon(
                  icon,
                  color: detailGreen,
                  size: 30,
                ),
              ),
              const SizedBox(width: 14),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      title,
                      style: const TextStyle(
                        color: detailText,
                        fontSize: 16,
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      description,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        color: Color(0xFF69766F),
                        fontSize: 12,
                        height: 1.35,
                      ),
                    ),
                  ],
                ),
              ),
              const Icon(
                Icons.arrow_forward_ios_rounded,
                color: Color(0xFF69766F),
                size: 17,
              ),
            ],
          ),
        ),
      ),
    );
  }
}