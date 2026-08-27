import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

class RoleRightsCard extends StatefulWidget {
  const RoleRightsCard({
    super.key,
    required this.isBuyer,
    required this.userName,
    this.unreadCount = 2,
  });

  final bool isBuyer;
  final String userName;
  final int unreadCount;

  @override
  State<RoleRightsCard> createState() => _RoleRightsCardState();
}

class _RoleRightsCardState extends State<RoleRightsCard> {
  bool _isExpanded = false;

  @override
  Widget build(BuildContext context) {
    final isBuyer = widget.isBuyer;
    final primaryColor = isBuyer ? const Color(0xFF0F5132) : const Color(0xFF059669);
    final lightColor = isBuyer ? const Color(0xFFD1FAE5) : const Color(0xFFDCFCE7);
    final borderColor = isBuyer ? const Color(0xFF6EE7B7) : const Color(0xFFA7F3D0);

    final title = isBuyer
        ? 'Hak & Jaminan Resmi Pembeli B2B'
        : 'Hak & Fasilitas Resmi Petani P.A.D.I.';
    final subtitle = isBuyer
        ? 'Akun Terverifikasi • Jaminan Tera & Transaksi Legal'
        : 'Akun Terverifikasi • Akses Penuh AI & Bursa Panen';

    final rights = isBuyer
        ? const [
            (
              Icons.inventory_2_outlined,
              'Akses Bursa Gabah Nasional',
              'Hak memantau stok panen raya gabah GKP/GKG riil langsung dari kelompok tani.',
            ),
            (
              Icons.scale_rounded,
              'Jaminan Timbangan Tera Sah',
              'Hak penimbangan sawah berkalibrasi tera resmi dan sertifikasi uji kadar air.',
            ),
            (
              Icons.local_shipping_outlined,
              'Fasilitas Armada Logistik Truk',
              'Hak memesan dan melacak penjemputan gabah langsung dari sawah ke penggilingan.',
            ),
            (
              Icons.verified_outlined,
              'Nota & Faktur Kontrak Legal',
              'Hak penerbitan dokumen jual-beli sah untuk kepatuhan pembukuan dan hukum.',
            ),
          ]
        : const [
            (
              Icons.biotech_rounded,
              'Diagnostik Kamera AI Bebas Biaya',
              'Hak memindai penyakit daun & hama tanaman padi tanpa batasan kuota berkala.',
            ),
            (
              Icons.calendar_month_rounded,
              'Rekomendasi Pemupukan Akurat',
              'Hak menerima takaran pupuk NPK Phonska dan Urea berbasis luas lahan & fase tanam.',
            ),
            (
              Icons.storefront_rounded,
              'Penjualan Bebas Calo ke Bursa',
              'Hak memasarkan hasil panen gabah langsung ke mitra industri tanpa potongan perantara.',
            ),
            (
              Icons.sensors_rounded,
              'Monitoring Cuaca & Kalender Sawah',
              'Hak pemantauan agroklimat lokal dan panduan fase vegetatif serta generatif.',
            ),
          ];

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: borderColor, width: 1.2),
        boxShadow: [
          BoxShadow(
            color: primaryColor.withOpacity(0.06),
            blurRadius: 14,
            offset: const Offset(0, 3),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Card Header
          InkWell(
            onTap: () => setState(() => _isExpanded = !_isExpanded),
            borderRadius: BorderRadius.vertical(
              top: const Radius.circular(16),
              bottom: Radius.circular(_isExpanded ? 0 : 16),
            ),
            child: Padding(
              padding: const EdgeInsets.all(14),
              child: Row(
                children: [
                  Container(
                    width: 38,
                    height: 38,
                    decoration: BoxDecoration(
                      color: lightColor,
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Icon(
                      Icons.shield_outlined,
                      color: primaryColor,
                      size: 20,
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
                                title,
                                style: const TextStyle(
                                  fontSize: 13.5,
                                  fontWeight: FontWeight.w800,
                                  color: Color(0xFF0F172A),
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                            const SizedBox(width: 6),
                            Container(
                              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                              decoration: BoxDecoration(
                                color: lightColor,
                                borderRadius: BorderRadius.circular(4),
                              ),
                              child: Text(
                                'AKTIF',
                                style: TextStyle(
                                  fontSize: 9,
                                  fontWeight: FontWeight.w900,
                                  color: primaryColor,
                                  letterSpacing: 0.3,
                                ),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 2),
                        Text(
                          subtitle,
                          style: const TextStyle(
                            fontSize: 11.5,
                            color: Color(0xFF64748B),
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ],
                    ),
                  ),
                  Icon(
                    _isExpanded
                        ? Icons.keyboard_arrow_up_rounded
                        : Icons.keyboard_arrow_down_rounded,
                    color: const Color(0xFF64748B),
                    size: 20,
                  ),
                ],
              ),
            ),
          ),

          // Collapsible Rights Detail Grid
          if (_isExpanded) ...[
            const Divider(height: 1, color: Color(0xFFF1F5F9)),
            Padding(
              padding: const EdgeInsets.fromLTRB(14, 12, 14, 10),
              child: Column(
                children: rights.map((r) {
                  return Padding(
                    padding: const EdgeInsets.only(bottom: 10),
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Container(
                          margin: const EdgeInsets.only(top: 2),
                          padding: const EdgeInsets.all(4),
                          decoration: BoxDecoration(
                            color: lightColor,
                            borderRadius: BorderRadius.circular(6),
                          ),
                          child: Icon(r.$1, size: 14, color: primaryColor),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                r.$2,
                                style: const TextStyle(
                                  fontSize: 12,
                                  fontWeight: FontWeight.w800,
                                  color: Color(0xFF1E293B),
                                ),
                              ),
                              const SizedBox(height: 1),
                              Text(
                                r.$3,
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
                  );
                }).toList(),
              ),
            ),
          ],

          // Footer Action: Direct link to Notifications
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
            decoration: BoxDecoration(
              color: const Color(0xFFF8FAFC),
              borderRadius: const BorderRadius.vertical(bottom: Radius.circular(16)),
              border: Border(top: BorderSide(color: borderColor.withOpacity(0.5))),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Row(
                  children: [
                    Icon(
                      Icons.notifications_active_outlined,
                      size: 14,
                      color: primaryColor,
                    ),
                    const SizedBox(width: 6),
                    Text(
                      'Pemberitahuan Wewenang & Status Akun',
                      style: TextStyle(
                        fontSize: 11.5,
                        fontWeight: FontWeight.w700,
                        color: primaryColor,
                      ),
                    ),
                  ],
                ),
                InkWell(
                  onTap: () => context.push('/notifications'),
                  child: Row(
                    children: [
                      Text(
                        'Buka',
                        style: TextStyle(
                          fontSize: 11.5,
                          fontWeight: FontWeight.w800,
                          color: primaryColor,
                        ),
                      ),
                      const SizedBox(width: 2),
                      Icon(Icons.chevron_right_rounded, size: 16, color: primaryColor),
                    ],
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
