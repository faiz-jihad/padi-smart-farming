import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:padi/features/auth/presentation/widgets/padi_theme.dart';
import 'package:padi/features/community_alert/presentation/widgets/alert_card.dart';
import 'package:padi/features/community_alert/presentation/widgets/alert_filter.dart';
import 'package:padi/features/community_alert/presentation/widgets/alert_summary_card.dart';

class CommunityAlertScreen extends StatefulWidget {
  const CommunityAlertScreen({super.key});

  @override
  State<CommunityAlertScreen> createState() => _CommunityAlertScreenState();
}

class _CommunityAlertScreenState extends State<CommunityAlertScreen> {
  String _selectedFilter = 'Semua';

  final List<Map<String, dynamic>> _alerts = [
    {
      'title': 'Waspada Penyakit Hawar Daun',
      'description':
          'Ditemukan laporan tanaman padi mengalami gejala hawar daun di sekitar wilayah Anda.',
      'location': 'Desa Karanganyar',
      'date': 'Hari ini',
      'type': 'Penyakit',
      'level': 'Waspada',
      'icon': Icons.coronavirus_rounded,
    },
    {
      'title': 'Peringatan Hujan Lebat',
      'description':
          'Petani disarankan memperhatikan kondisi sawah dan saluran air setelah hujan.',
      'location': 'Kec. Indramayu',
      'date': 'Hari ini',
      'type': 'Cuaca',
      'level': 'Informasi',
      'icon': Icons.cloud_rounded,
    },
    {
      'title': 'Waspada Serangan Wereng',
      'description':
          'Terdapat laporan peningkatan populasi wereng pada beberapa lahan padi.',
      'location': 'Desa Sukamaju',
      'date': 'Kemarin',
      'type': 'Hama',
      'level': 'Waspada',
      'icon': Icons.bug_report_rounded,
    },
  ];

  List<Map<String, dynamic>> get _filteredAlerts {
    if (_selectedFilter == 'Semua') {
      return _alerts;
    }

    return _alerts
        .where((alert) => alert['type'] == _selectedFilter)
        .toList();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: padiField,
      appBar: AppBar(
        backgroundColor: padiField,
        elevation: 0,
        scrolledUnderElevation: 0,
        leading: IconButton(
          onPressed: () => context.go('/home'),
          icon: const Icon(
            Icons.arrow_back_rounded,
            color: padiInk,
          ),
        ),
        title: const Text(
          'Community Alert',
          style: TextStyle(
            color: padiInk,
            fontSize: 20,
            fontWeight: FontWeight.w900,
          ),
        ),
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => context.push('/community-alert/report'),
        backgroundColor: padiGreen,
        foregroundColor: Colors.white,
        icon: const Icon(Icons.campaign_rounded),
        label: const Text(
          'Lapor Kondisi',
          style: TextStyle(
            fontWeight: FontWeight.w800,
          ),
        ),
      ),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(20, 8, 20, 100),
        children: [
          const AlertSummaryCard(),
          const SizedBox(height: 22),
          const Text(
            'Peringatan di Sekitar Anda',
            style: TextStyle(
              color: padiInk,
              fontSize: 19,
              fontWeight: FontWeight.w900,
            ),
          ),
          const SizedBox(height: 12),
          AlertFilter(
            selectedFilter: _selectedFilter,
            onChanged: (value) {
              setState(() {
                _selectedFilter = value;
              });
            },
          ),
          const SizedBox(height: 14),
          ..._filteredAlerts.map(
            (alert) => Padding(
              padding: const EdgeInsets.only(bottom: 10),
              child: AlertCard(
                title: alert['title'],
                description: alert['description'],
                location: alert['location'],
                date: alert['date'],
                type: alert['type'],
                level: alert['level'],
                icon: alert['icon'],
              ),
            ),
          ),
        ],
      ),
    );
  }
}