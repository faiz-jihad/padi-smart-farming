import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

const Color landPrimary = Color(0xFF075C3D);
const Color landBackground = Color(0xFFF7F9F4);
const Color landText = Color(0xFF183D2D);

class LandListScreen extends StatelessWidget {
  const LandListScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: landBackground,
      appBar: AppBar(
        backgroundColor: landBackground,
        elevation: 0,
        surfaceTintColor: Colors.transparent,
        leading: IconButton(
          onPressed: () {
            if (context.canPop()) {
              context.pop();
            } else {
              context.go('/home');
            }
          },
          icon: const Icon(Icons.arrow_back_rounded, size: 34),
          color: landPrimary,
          tooltip: 'Kembali',
        ),
        title: const Text(
          'Lahan Saya',
          style: TextStyle(
            color: landText,
            fontSize: 24,
            fontWeight: FontWeight.w900,
          ),
        ),
      ),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(20, 8, 20, 30),
        children: [
          const Text(
            'Daftar sawah yang Anda kelola.',
            style: TextStyle(
              color: Color(0xFF69766F),
              fontSize: 16,
              height: 1.4,
            ),
          ),
          const SizedBox(height: 22),

          _LandCard(
            name: 'Sawah Blok A',
            location: 'Desa Karanganyar',
            area: '1.200 m²',
            status: 'Sedang ditanami',
            onTap: () {
              context.push('/land/detail');
            },
          ),

          const SizedBox(height: 16),

          _LandCard(
            name: 'Sawah Blok B',
            location: 'Desa Karanganyar',
            area: '800 m²',
            status: 'Belum ditanami',
            onTap: () {
              context.push('/land/detail');
            },
          ),

          const SizedBox(height: 24),

          SizedBox(
            height: 62,
            child: ElevatedButton.icon(
              onPressed: () {
                context.push('/land/add');
              },
              icon: const Icon(Icons.add_rounded, size: 28),
              label: const Text(
                'Tambah Lahan',
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.w900),
              ),
              style: ElevatedButton.styleFrom(
                backgroundColor: landPrimary,
                foregroundColor: Colors.white,
                elevation: 0,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(20),
                ),
              ),
            ),
          ),

          const SizedBox(height: 18),

          Container(
            padding: const EdgeInsets.all(17),
            decoration: BoxDecoration(
              color: const Color(0xFFFFF8DF),
              borderRadius: BorderRadius.circular(20),
            ),
            child: const Row(
              children: [
                Icon(
                  Icons.volume_up_rounded,
                  color: Color(0xFF946E00),
                  size: 30,
                ),
                SizedBox(width: 13),
                Expanded(
                  child: Text(
                    'Anda bisa menambahkan sawah kapan saja.',
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

class _LandCard extends StatelessWidget {
  const _LandCard({
    required this.name,
    required this.location,
    required this.area,
    required this.status,
    required this.onTap,
  });

  final String name;
  final String location;
  final String area;
  final String status;
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
          padding: const EdgeInsets.all(19),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                children: [
                  Container(
                    width: 58,
                    height: 58,
                    decoration: BoxDecoration(
                      color: const Color(0xFFE8F4EC),
                      borderRadius: BorderRadius.circular(18),
                    ),
                    child: const Icon(
                      Icons.grass_rounded,
                      color: landPrimary,
                      size: 32,
                    ),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          name,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(
                            color: landText,
                            fontSize: 19,
                            fontWeight: FontWeight.w900,
                          ),
                        ),
                        const SizedBox(height: 5),
                        Row(
                          children: [
                            const Icon(
                              Icons.location_on_outlined,
                              color: Color(0xFF69766F),
                              size: 18,
                            ),
                            const SizedBox(width: 4),
                            Expanded(
                              child: Text(
                                location,
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: const TextStyle(
                                  color: Color(0xFF69766F),
                                  fontSize: 14,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                  const Icon(
                    Icons.chevron_right_rounded,
                    color: Color(0xFF69766F),
                    size: 30,
                  ),
                ],
              ),

              const SizedBox(height: 18),

              Container(height: 1, color: const Color(0xFFE8ECE8)),

              const SizedBox(height: 15),

              Row(
                children: [
                  const Icon(
                    Icons.straighten_rounded,
                    color: landPrimary,
                    size: 21,
                  ),
                  const SizedBox(width: 7),
                  Text(
                    area,
                    style: const TextStyle(
                      color: landText,
                      fontSize: 15,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                  const Spacer(),
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 11,
                      vertical: 7,
                    ),
                    decoration: BoxDecoration(
                      color: const Color(0xFFE8F4EC),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Text(
                      status,
                      style: const TextStyle(
                        color: landPrimary,
                        fontSize: 12,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                  ),
                ],
              ),

              const SizedBox(height: 15),

              const Row(
                mainAxisAlignment: MainAxisAlignment.end,
                children: [
                  Text(
                    'Lihat sawah',
                    style: TextStyle(
                      color: landPrimary,
                      fontSize: 14,
                      fontWeight: FontWeight.w900,
                    ),
                  ),
                  SizedBox(width: 5),
                  Icon(
                    Icons.arrow_forward_rounded,
                    color: landPrimary,
                    size: 19,
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}
