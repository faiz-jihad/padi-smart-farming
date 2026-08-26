import 'package:flutter/material.dart';
import 'package:padi/features/home/presentation/tokens/home_tokens.dart';

class HomeSkeleton extends StatefulWidget {
  const HomeSkeleton({super.key});

  @override
  State<HomeSkeleton> createState() => _HomeSkeletonState();
}

class _HomeSkeletonState extends State<HomeSkeleton> with SingleTickerProviderStateMixin {
  late final AnimationController _controller;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1400),
    )..repeat(reverse: true);
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final anim = _controller;

    return AnimatedBuilder(
      animation: anim,
      builder: (context, child) {
        final opacity = 0.4 + (anim.value * 0.45);

        return Opacity(
          opacity: opacity,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const SizedBox(height: HomeSpacing.xs),

              // Header Skeleton
              Row(
                children: [
                  _buildBox(width: 48, height: 48, radius: HomeRadius.md),
                  const SizedBox(width: HomeSpacing.sm),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        _buildBox(width: 100, height: 12, radius: 4),
                        const SizedBox(height: 6),
                        _buildBox(width: 160, height: 18, radius: 4),
                      ],
                    ),
                  ),
                  _buildBox(width: 44, height: 44, radius: HomeRadius.pill),
                ],
              ),

              const SizedBox(height: HomeSpacing.lg),

              // Hero Card Skeleton
              _buildBox(width: double.infinity, height: 180, radius: HomeRadius.xl),

              const SizedBox(height: HomeSpacing.md),

              // Weather Card Skeleton
              _buildBox(width: double.infinity, height: 150, radius: HomeRadius.xl),

              const SizedBox(height: HomeSpacing.lg),

              // Quick Action Skeleton
              Row(
                children: List.generate(
                  4,
                  (index) => Expanded(
                    child: Padding(
                      padding: EdgeInsets.only(
                        right: index == 3 ? 0 : 8,
                      ),
                      child: _buildBox(height: 90, radius: HomeRadius.lg),
                    ),
                  ),
                ),
              ),

              const SizedBox(height: HomeSpacing.lg),

              // Activity Skeleton
              _buildBox(width: double.infinity, height: 130, radius: HomeRadius.xl),
            ],
          ),
        );
      },
    );
  }

  Widget _buildBox({
    double? width,
    required double height,
    required double radius,
  }) {
    return Container(
      width: width,
      height: height,
      decoration: BoxDecoration(
        color: HomeColors.border,
        borderRadius: BorderRadius.circular(radius),
      ),
    );
  }
}
