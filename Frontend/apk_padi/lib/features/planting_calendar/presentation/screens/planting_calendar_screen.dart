import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:padi/core/network/api_client.dart';
import 'package:padi/core/storage/token_storage.dart';
import 'package:padi/features/auth/presentation/widgets/padi_theme.dart';
import 'package:padi/features/planting_calendar/data/models/planting_calendar_model.dart';
import 'package:padi/features/planting_calendar/data/services/planting_calendar_api_service.dart';
import 'package:padi/features/planting_calendar/presentation/widgets/planting_calendar_card.dart';

class PlantingCalendarScreen extends StatefulWidget {
  const PlantingCalendarScreen({
    super.key,
    this.farmId,
    this.setupFlow = false,
  });

  final int? farmId;
  final bool setupFlow;

  @override
  State<PlantingCalendarScreen> createState() =>
      _PlantingCalendarScreenState();
}

class _PlantingCalendarScreenState
    extends State<PlantingCalendarScreen> {
  late final PlantingCalendarApiService _apiService;

  List<PlantingCalendarModel> _calendars = [];

  PlantingCalendarModel? _farmCalendar;

  bool _isLoading = true;

  String? _errorMessage;

  @override
  void initState() {
    super.initState();

    _apiService = PlantingCalendarApiService(
      ApiClient(
        const SecureTokenStorage(),
      ),
    );

    _loadCalendar();
  }

  Future<void> _loadCalendar() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      if (widget.farmId != null) {
        final calendar = await _apiService.getCalendarForFarm(
          widget.farmId!,
        );

        if (!mounted) return;

        setState(() {
          _farmCalendar = calendar;
          _isLoading = false;
        });

        return;
      }

      final calendars = await _apiService.fetchCalendars();

      if (!mounted) return;

      setState(() {
        _calendars = calendars;
        _isLoading = false;
      });
    } catch (e) {
      if (!mounted) return;

      setState(() {
        _isLoading = false;
        _errorMessage = 'Gagal mengambil data kalender tanam.';
      });
    }
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
          onPressed: () {
            if (context.canPop()) {
              context.pop();
            } else {
              context.go('/home');
            }
          },
          icon: const Icon(
            Icons.arrow_back_rounded,
            color: padiInk,
          ),
        ),
        title: const Text(
          'Kalender Tanam',
          style: TextStyle(
            color: padiInk,
            fontSize: 20,
            fontWeight: FontWeight.w900,
          ),
        ),
      ),
      body: _buildBody(),
    );
  }

  Widget _buildBody() {
    if (_isLoading) {
      return const Center(
        child: CircularProgressIndicator(),
      );
    }

    if (_errorMessage != null) {
      return Center(
        child: Padding(
          padding: const EdgeInsets.all(20),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(
                Icons.error_outline_rounded,
                color: Colors.redAccent,
                size: 50,
              ),
              const SizedBox(height: 12),
              Text(
                _errorMessage!,
                textAlign: TextAlign.center,
                style: const TextStyle(
                  color: padiInk,
                  fontWeight: FontWeight.w700,
                ),
              ),
              const SizedBox(height: 16),
              FilledButton(
                onPressed: _loadCalendar,
                child: const Text('Coba Lagi'),
              ),
            ],
          ),
        ),
      );
    }

    if (widget.farmId != null) {
      if (_farmCalendar == null) {
        return _buildEmptyState(
          'Belum ada kalender tanam untuk lahan ini.',
        );
      }

      return RefreshIndicator(
        onRefresh: _loadCalendar,
        child: ListView(
          padding: const EdgeInsets.fromLTRB(
            20,
            12,
            20,
            30,
          ),
          children: [
            const Text(
              'Rekomendasi Tanam',
              style: TextStyle(
                color: padiInk,
                fontSize: 22,
                fontWeight: FontWeight.w900,
              ),
            ),
            const SizedBox(height: 8),
            const Text(
              'Informasi waktu tanam yang sesuai untuk lahan ini.',
              style: TextStyle(
                color: padiMuted,
                fontSize: 13,
              ),
            ),
            const SizedBox(height: 20),
            PlantingCalendarCard(
              calendar: _farmCalendar!,
            ),
            if (widget.setupFlow) ...[
              const SizedBox(height: 16),
              _MapAction(onPressed: () => context.go('/map/calendar')),
            ],
          ],
        ),
      );
    }

    if (_calendars.isEmpty) {
      return _buildEmptyState(
        'Belum ada data kalender tanam.',
      );
    }

    return RefreshIndicator(
      onRefresh: _loadCalendar,
      child: ListView(
        padding: const EdgeInsets.fromLTRB(
          20,
          12,
          20,
          30,
        ),
        children: [
          const Text(
            'Kalender Tanam',
            style: TextStyle(
              color: padiInk,
              fontSize: 22,
              fontWeight: FontWeight.w900,
            ),
          ),
          const SizedBox(height: 8),
          const Text(
            'Pantau rekomendasi waktu tanam berdasarkan musim dan wilayah.',
            style: TextStyle(
              color: padiMuted,
              fontSize: 13,
              height: 1.4,
            ),
          ),
          const SizedBox(height: 20),
          ..._calendars.map(
            (calendar) => Padding(
              padding: const EdgeInsets.only(bottom: 12),
              child: PlantingCalendarCard(
                calendar: calendar,
              ),
            ),
          ),
          if (widget.setupFlow) ...[
            const SizedBox(height: 4),
            _MapAction(onPressed: () => context.go('/map/calendar')),
          ],
        ],
      ),
    );
  }

  Widget _buildEmptyState(String message) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(30),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              Icons.calendar_month_outlined,
              size: 60,
              color: padiGreen,
            ),
            const SizedBox(height: 16),
            Text(
              message,
              textAlign: TextAlign.center,
              style: const TextStyle(
                color: padiInk,
                fontSize: 17,
                fontWeight: FontWeight.w800,
              ),
            ),
            if (widget.setupFlow) ...[
              const SizedBox(height: 18),
              _MapAction(onPressed: () => context.go('/map/calendar')),
            ],
          ],
        ),
      ),
    );
  }
}

class _MapAction extends StatelessWidget {
  const _MapAction({required this.onPressed});

  final VoidCallback onPressed;

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: double.infinity,
      height: 50,
      child: FilledButton.icon(
        onPressed: onPressed,
        icon: const Icon(Icons.map_outlined),
        label: const Text(
          'Lanjut ke Peta',
          style: TextStyle(fontWeight: FontWeight.w800),
        ),
      ),
    );
  }
}
