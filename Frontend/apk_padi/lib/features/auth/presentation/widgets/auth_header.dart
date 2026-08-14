import 'package:padi/features/auth/presentation/widgets/padi_theme.dart';
import 'package:flutter/material.dart';

class AuthHeader extends StatelessWidget {
  const AuthHeader({super.key, required this.title, required this.subtitle});

  final String title;
  final String subtitle;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const PadiLogo(size: 62),
        const SizedBox(height: 22),
        Text(
          title,
          style: Theme.of(context).textTheme.headlineSmall?.copyWith(
            color: padiInk,
            fontWeight: FontWeight.w800,
            height: 1.1,
          ),
        ),
        const SizedBox(height: 8),
        Text(
          subtitle,
          style: Theme.of(context).textTheme.bodyMedium?.copyWith(
            color: padiMuted,
            height: 1.4,
          ),
        ),
      ],
    );
  }
}

class PadiLogo extends StatelessWidget {
  const PadiLogo({super.key, this.size = 72});

  final double size;

  @override
  Widget build(BuildContext context) {
    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.08),
            blurRadius: 20,
            offset: const Offset(0, 10),
          ),
        ],
      ),
      child: Stack(
        alignment: Alignment.center,
        children: [
          Icon(Icons.eco_rounded, color: padiGreen, size: size * 0.54),
          Positioned(
            right: size * 0.22,
            bottom: size * 0.2,
            child: Container(
              width: size * 0.18,
              height: size * 0.18,
              decoration: const BoxDecoration(color: padiLeaf, shape: BoxShape.circle),
            ),
          ),
        ],
      ),
    );
  }
}
