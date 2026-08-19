import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:padi/features/home/presentation/screens/home_screen.dart';

const Color timelineGreen = Color(0xFF075C3D);
const Color timelineBackground = Color(0xFFF7F9F4);
const Color timelineText = Color(0xFF183D2D);
const Color timelineYellow = Color(0xFFF2C94C);

class CultivationTimelineScreen extends StatelessWidget {
  const CultivationTimelineScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: timelineBackground,
      appBar: AppBar(
        backgroundColor: timelineBackground,
        elevation: 0,
        surfaceTintColor: Colors.transparent,
        leading: IconButton(
          onPressed: () {
            context.go('/land/season/start');
          },
          icon: const Icon(Icons.arrow_back_rounded, size: 34),
          color: primaryGreen,
          tooltip: 'Kembali',
        ),
        title: const Text(
          'Kegiatan Sawah',
          style: TextStyle(
            color: timelineText,
            fontSize: 23,
            fontWeight: FontWeight.w900,
          ),
        ),
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () {
          context.push('/land/activity/add');
        },
        backgroundColor: timelineGreen,
        foregroundColor: Colors.white,
        icon: const Icon(Icons.add_rounded, size: 26),
        label: const Text(
          'Tambah Kegiatan',
          style: TextStyle(fontWeight: FontWeight.w900),
        ),
      ),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(20, 8, 20, 100),
        children: [
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: timelineGreen,
              borderRadius: BorderRadius.circular(25),
            ),
            child: const Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Icon(Icons.grass_rounded, color: timelineYellow, size: 35),
                    SizedBox(width: 11),
                    Text(
                      'Sawah Blok A',
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 21,
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                  ],
                ),
                SizedBox(height: 14),
                Text(
                  'Musim Tanam 2026',
                  style: TextStyle(color: Colors.white70, fontSize: 14),
                ),
                SizedBox(height: 4),
                Text(
                  'Hari ke-24',
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 18,
                    fontWeight: FontWeight.w900,
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 25),
          const Text(
            'Perjalanan tanaman',
            style: TextStyle(
              color: timelineText,
              fontSize: 20,
              fontWeight: FontWeight.w900,
            ),
          ),
          const SizedBox(height: 16),
          _TimelineItem(
            icon: Icons.spa_rounded,
            title: 'Mulai tanam',
            description: 'Padi mulai ditanam di sawah.',
            date: '25 Juli 2026',
            completed: true,
            isLast: false,
          ),
          _TimelineItem(
            icon: Icons.water_drop_rounded,
            title: 'Pengairan',
            description: 'Sawah diberikan pengairan.',
            date: '28 Juli 2026',
            completed: true,
            isLast: false,
          ),
          _TimelineItem(
            icon: Icons.science_rounded,
            title: 'Pemupukan',
            description: 'Pupuk diberikan pada tanaman.',
            date: '5 Agustus 2026',
            completed: true,
            isLast: false,
          ),
          _TimelineItem(
            icon: Icons.camera_alt_rounded,
            title: 'Periksa kondisi padi',
            description: 'Belum dilakukan.',
            date: 'Selanjutnya',
            completed: false,
            isLast: true,
          ),
          const SizedBox(height: 20),
          Container(
            padding: const EdgeInsets.all(17),
            decoration: BoxDecoration(
              color: const Color(0xFFFFF8DF),
              borderRadius: BorderRadius.circular(20),
            ),
            child: const Row(
              children: [
                Icon(
                  Icons.lightbulb_outline_rounded,
                  color: Color(0xFF946E00),
                  size: 29,
                ),
                SizedBox(width: 12),
                Expanded(
                  child: Text(
                    'Catat kegiatan setiap kali merawat sawah agar Anda tidak lupa.',
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

class _TimelineItem extends StatelessWidget {
  const _TimelineItem({
    required this.icon,
    required this.title,
    required this.description,
    required this.date,
    required this.completed,
    required this.isLast,
  });

  final IconData icon;
  final String title;
  final String description;
  final String date;
  final bool completed;
  final bool isLast;

  @override
  Widget build(BuildContext context) {
    return IntrinsicHeight(
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 56,
            child: Column(
              children: [
                Container(
                  width: 48,
                  height: 48,
                  decoration: BoxDecoration(
                    color: completed ? timelineGreen : Colors.white,
                    shape: BoxShape.circle,
                    border: Border.all(
                      color: completed
                          ? timelineGreen
                          : const Color(0xFFD7DED8),
                      width: 2,
                    ),
                  ),
                  child: Icon(
                    icon,
                    color: completed ? Colors.white : const Color(0xFF7C8781),
                    size: 24,
                  ),
                ),
                if (!isLast)
                  Expanded(
                    child: Container(
                      width: 2,
                      color: completed
                          ? const Color(0xFFB7D5C5)
                          : const Color(0xFFDDE3DE),
                    ),
                  ),
              ],
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Container(
              margin: const EdgeInsets.only(bottom: 18),
              padding: const EdgeInsets.all(17),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(20),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: const TextStyle(
                      color: timelineText,
                      fontSize: 16,
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                  const SizedBox(height: 5),
                  Text(
                    description,
                    style: const TextStyle(
                      color: Color(0xFF69766F),
                      fontSize: 13,
                      height: 1.35,
                    ),
                  ),
                  const SizedBox(height: 9),
                  Text(
                    date,
                    style: TextStyle(
                      color: completed
                          ? timelineGreen
                          : const Color(0xFF946E00),
                      fontSize: 12,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}
