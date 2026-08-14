import 'package:padi/features/auth/presentation/widgets/auth_header.dart';
import 'package:padi/features/auth/presentation/widgets/padi_theme.dart';
import 'package:flutter/material.dart';

class SplashScreen extends StatelessWidget {
  const SplashScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return const Scaffold(
      backgroundColor: padiGreen,
      body: Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            PadiLogo(size: 86),
            SizedBox(height: 24),
            Text(
              'P.A.D.I.',
              style: TextStyle(color: Colors.white, fontSize: 30, fontWeight: FontWeight.w900),
            ),
            SizedBox(height: 10),
            Text(
              'Memulihkan sesi pengguna',
              style: TextStyle(color: Colors.white70, fontWeight: FontWeight.w600),
            ),
            SizedBox(height: 28),
            SizedBox(
              width: 28,
              height: 28,
              child: CircularProgressIndicator(color: Colors.white, strokeWidth: 3),
            ),
          ],
        ),
      ),
    );
  }
}
