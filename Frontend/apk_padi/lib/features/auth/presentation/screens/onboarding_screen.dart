import 'package:flutter/material.dart';
import 'package:flutter_tts/flutter_tts.dart';
import 'package:go_router/go_router.dart';

class OnboardingData {
  const OnboardingData({
    required this.title,
    required this.description,
    required this.image,
    required this.speech,
  });

  final String title;
  final String description;
  final String image;
  final String speech;
}

class OnboardingScreen extends StatefulWidget {
  const OnboardingScreen({super.key});

  @override
  State<OnboardingScreen> createState() => _OnboardingScreenState();
}

class _OnboardingScreenState extends State<OnboardingScreen> {
  final PageController _pageController = PageController();
  final FlutterTts _tts = FlutterTts();

  int _currentPage = 0;
  bool _isSpeaking = false;

  final List<OnboardingData> _pages = const [
    OnboardingData(
      title: 'Selamat Datang di P.A.D.I.',
      description:
          'Teman untuk membantu Anda mengelola sawah dengan lebih mudah.',
      image: 'assets/images/onboarding_1.jpeg',
      speech:
          'Selamat datang di P.A.D.I. Teman Anda untuk membantu mengelola sawah dengan lebih mudah.',
    ),
    OnboardingData(
      title: 'Pantau Sawah dari HP',
      description:
          'Lihat kondisi lahan, jadwal tanam, cuaca, dan kebutuhan tanaman dari satu aplikasi.',
      image: 'assets/images/onboarding_2.jpeg',
      speech:
          'Dengan P.A.D.I., Anda dapat memantau sawah langsung dari HP. Lihat kondisi lahan, jadwal tanam, cuaca, dan kebutuhan tanaman dengan lebih mudah.',
    ),
    OnboardingData(
      title: 'Panen Lebih Siap, Jual Lebih Mudah',
      description:
          'Kelola hasil panen dan temukan peluang penjualan dengan lebih mudah.',
      image: 'assets/images/onboarding_3.jpeg',
      speech:
          'P.A.D.I. membantu Anda mempersiapkan panen dan menjual hasil pertanian dengan lebih mudah.',
    ),
  ];

  @override
  void initState() {
    super.initState();
    _setupTts();
  }

  Future<void> _setupTts() async {
    await _tts.setLanguage('id-ID');
    await _tts.setSpeechRate(0.42);
    await _tts.setVolume(1.0);
    await _tts.setPitch(1.0);

    await _tts.awaitSpeakCompletion(true);

    _tts.setStartHandler(() {
      if (mounted) {
        setState(() {
          _isSpeaking = true;
        });
      }
    });

    _tts.setCompletionHandler(() {
      if (mounted) {
        setState(() {
          _isSpeaking = false;
        });
      }
    });

    _tts.setCancelHandler(() {
      if (mounted) {
        setState(() {
          _isSpeaking = false;
        });
      }
    });

    _tts.setErrorHandler((message) {
      if (mounted) {
        setState(() {
          _isSpeaking = false;
        });
      }
    });
  }

  Future<void> _speakCurrentPage() async {
    await _tts.stop();

    if (!mounted) {
      return;
    }

    setState(() {
      _isSpeaking = true;
    });

    final text = _pages[_currentPage].speech;

    await _tts.speak(text);
  }

  Future<void> _stopSpeaking() async {
    await _tts.stop();

    if (mounted) {
      setState(() {
        _isSpeaking = false;
      });
    }
  }

  Future<void> _changePage(int page) async {
    await _stopSpeaking();

    if (!_pageController.hasClients) {
      return;
    }

    await _pageController.animateToPage(
      page,
      duration: const Duration(milliseconds: 400),
      curve: Curves.easeInOut,
    );
  }

  void _finishOnboarding() {
    _tts.stop();
    context.go('/login');
  }

  @override
  void dispose() {
    _tts.stop();
    _pageController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final size = MediaQuery.of(context).size;
    final isSmallHeight = size.height < 700;

    return Scaffold(
      backgroundColor: const Color(0xFFF8FAF3),
      body: SafeArea(
        child: Column(
          children: [
            Padding(
              padding: const EdgeInsets.fromLTRB(20, 16, 20, 8),
              child: Row(
                children: [
                  Container(
                    width: 46,
                    height: 46,
                    padding: const EdgeInsets.all(7),
                    decoration: BoxDecoration(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(14),
                      boxShadow: [
                        BoxShadow(
                          color: Colors.black.withValues(alpha: 0.07),
                          blurRadius: 10,
                          offset: const Offset(0, 4),
                        ),
                      ],
                    ),
                    child: Image.asset(
                      'assets/images/padi-logo.png',
                      fit: BoxFit.contain,
                    ),
                  ),
                  const SizedBox(width: 12),
                  const Text(
                    'P.A.D.I.',
                    style: TextStyle(
                      color: Color(0xFF075C3D),
                      fontSize: 21,
                      fontWeight: FontWeight.w800,
                    ),
                  ),
                ],
              ),
            ),
            Expanded(
              child: PageView.builder(
                controller: _pageController,
                itemCount: _pages.length,
                onPageChanged: (index) {
                  _stopSpeaking();

                  setState(() {
                    _currentPage = index;
                  });
                },
                itemBuilder: (context, index) {
                  return _OnboardingPage(
                    data: _pages[index],
                    smallHeight: isSmallHeight,
                  );
                },
              ),
            ),
            Padding(
              padding: const EdgeInsets.fromLTRB(20, 4, 20, 20),
              child: Column(
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: List.generate(_pages.length, (index) {
                      final active = index == _currentPage;

                      return AnimatedContainer(
                        duration: const Duration(milliseconds: 250),
                        margin: const EdgeInsets.symmetric(horizontal: 4),
                        width: active ? 30 : 9,
                        height: 9,
                        decoration: BoxDecoration(
                          color: active
                              ? const Color(0xFF056B45)
                              : const Color(0xFFD3DDD7),
                          borderRadius: BorderRadius.circular(20),
                        ),
                      );
                    }),
                  ),
                  const SizedBox(height: 14),
                  SizedBox(
                    width: double.infinity,
                    height: 52,
                    child: OutlinedButton.icon(
                      onPressed: _speakCurrentPage,
                      icon: Icon(
                        _isSpeaking
                            ? Icons.stop_circle_outlined
                            : Icons.volume_up_rounded,
                        size: 24,
                      ),
                      label: Text(
                        _isSpeaking ? 'Berhentikan Suara' : 'Dengarkan Panduan',
                        style: const TextStyle(
                          fontSize: 15,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                      style: OutlinedButton.styleFrom(
                        foregroundColor: const Color(0xFF075C3D),
                        side: const BorderSide(
                          color: Color(0xFF075C3D),
                          width: 1.4,
                        ),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(16),
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(height: 10),
                  SizedBox(
                    width: double.infinity,
                    height: 56,
                    child: ElevatedButton(
                      onPressed: () {
                        if (_currentPage == _pages.length - 1) {
                          _finishOnboarding();
                          return;
                        }

                        _changePage(_currentPage + 1);
                      },
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF075C3D),
                        foregroundColor: Colors.white,
                        elevation: 0,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(16),
                        ),
                      ),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Text(
                            _currentPage == _pages.length - 1
                                ? 'Mulai Sekarang'
                                : 'Lanjut',
                            style: const TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.w800,
                            ),
                          ),
                          const SizedBox(width: 10),
                          const Icon(Icons.arrow_forward_rounded),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 8),
                  TextButton(
                    onPressed: () {
                      _finishOnboarding();
                    },
                    child: const Text(
                      'Lewati',
                      style: TextStyle(
                        color: Color(0xFF59645E),
                        fontSize: 14,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _OnboardingPage extends StatelessWidget {
  const _OnboardingPage({required this.data, required this.smallHeight});

  final OnboardingData data;
  final bool smallHeight;

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      physics: const BouncingScrollPhysics(),
      child: Column(
        children: [
          SizedBox(height: smallHeight ? 4 : 12),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20),
            child: ClipRRect(
              borderRadius: BorderRadius.circular(26),
              child: AspectRatio(
                aspectRatio: 1.55,
                child: Image.asset(
                  data.image,
                  fit: BoxFit.cover,
                  errorBuilder: (context, error, stackTrace) {
                    return Container(
                      color: const Color(0xFFE8EFE6),
                      alignment: Alignment.center,
                      child: const Icon(
                        Icons.image_not_supported_outlined,
                        size: 50,
                        color: Color(0xFF075C3D),
                      ),
                    );
                  },
                ),
              ),
            ),
          ),
          SizedBox(height: smallHeight ? 14 : 22),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 25),
            child: Text(
              data.title,
              textAlign: TextAlign.center,
              style: TextStyle(
                color: const Color(0xFF123C2B),
                fontSize: smallHeight ? 24 : 28,
                height: 1.15,
                fontWeight: FontWeight.w800,
              ),
            ),
          ),
          SizedBox(height: smallHeight ? 8 : 12),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 32),
            child: Text(
              data.description,
              textAlign: TextAlign.center,
              style: TextStyle(
                color: const Color(0xFF5E6B64),
                fontSize: smallHeight ? 14 : 16,
                height: 1.45,
                fontWeight: FontWeight.w500,
              ),
            ),
          ),
        ],
      ),
    );
  }
}
