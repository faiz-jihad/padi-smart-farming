import 'package:padi/features/auth/presentation/widgets/padi_theme.dart';
import 'package:flutter/material.dart';

class AuthScaffold extends StatelessWidget {
  const AuthScaffold({super.key, required this.child});

  final Widget child;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Stack(
        children: [
          const Positioned.fill(child: _FarmBackdrop()),
          SafeArea(
            child: LayoutBuilder(
              builder: (context, constraints) {
                return SingleChildScrollView(
                  keyboardDismissBehavior: ScrollViewKeyboardDismissBehavior.onDrag,
                  padding: const EdgeInsets.fromLTRB(22, 24, 22, 24),
                  child: ConstrainedBox(
                    constraints: BoxConstraints(minHeight: constraints.maxHeight - 48),
                    child: Align(
                      alignment: Alignment.bottomCenter,
                      child: Container(
                        width: double.infinity,
                        constraints: const BoxConstraints(maxWidth: 440),
                        padding: const EdgeInsets.all(22),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(28),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withValues(alpha: 0.13),
                              blurRadius: 26,
                              offset: const Offset(0, 16),
                            ),
                          ],
                        ),
                        child: child,
                      ),
                    ),
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}

class _FarmBackdrop extends StatelessWidget {
  const _FarmBackdrop();

  @override
  Widget build(BuildContext context) {
    return CustomPaint(
      painter: _FarmBackdropPainter(),
      child: Container(color: padiGreen),
    );
  }
}

class _FarmBackdropPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final sky = Paint()..color = const Color(0xFFBDEBDC);
    final hillFar = Paint()..color = const Color(0xFF8DD664);
    final hillNear = Paint()..color = const Color(0xFF41B956);
    final water = Paint()..color = const Color(0xFF71C9C8);
    final sun = Paint()..color = padiCream;

    canvas.drawRect(Rect.fromLTWH(0, 0, size.width, size.height * 0.44), sky);
    canvas.drawCircle(Offset(size.width * 0.72, size.height * 0.2), 46, sun);

    final far = Path()
      ..moveTo(0, size.height * 0.38)
      ..quadraticBezierTo(size.width * 0.28, size.height * 0.22, size.width * 0.55, size.height * 0.34)
      ..quadraticBezierTo(size.width * 0.82, size.height * 0.46, size.width, size.height * 0.28)
      ..lineTo(size.width, size.height)
      ..lineTo(0, size.height)
      ..close();
    canvas.drawPath(far, hillFar);

    final near = Path()
      ..moveTo(0, size.height * 0.52)
      ..quadraticBezierTo(size.width * 0.32, size.height * 0.38, size.width * 0.62, size.height * 0.5)
      ..quadraticBezierTo(size.width * 0.84, size.height * 0.6, size.width, size.height * 0.44)
      ..lineTo(size.width, size.height)
      ..lineTo(0, size.height)
      ..close();
    canvas.drawPath(near, hillNear);

    canvas.drawOval(
      Rect.fromCenter(
        center: Offset(size.width * 0.5, size.height * 0.62),
        width: size.width * 1.1,
        height: size.height * 0.28,
      ),
      water,
    );
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}
