import 'package:flutter/material.dart';
import 'package:padi/features/home/presentation/tokens/home_tokens.dart';

class FarmSkeleton extends StatefulWidget {
  const FarmSkeleton({super.key});

  @override
  State<FarmSkeleton> createState() => _FarmSkeletonState();
}

class _FarmSkeletonState extends State<FarmSkeleton>
    with SingleTickerProviderStateMixin {
  AnimationController? _controller;

  AnimationController get controller {
    return _controller ??= AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1400),
    )..repeat(reverse: true);
  }

  @override
  void initState() {
    super.initState();
    _initController();
  }

  void _initController() {
    _controller ??= AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1400),
    )..repeat(reverse: true);
  }

  @override
  void dispose() {
    _controller?.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    _initController();
    final anim = controller;

    return AnimatedBuilder(
      animation: anim,
      builder: (context, child) {
        final opacity = 0.4 + (anim.value * 0.45);

        return Opacity(
          opacity: opacity,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            mainAxisSize: MainAxisSize.min,
            children: [
              // Stats Card Skeleton
              Container(
                width: double.infinity,
                height: 110,
                decoration: BoxDecoration(
                  color: HomeColors.surface,
                  borderRadius: BorderRadius.circular(HomeRadius.xl),
                  border: Border.all(color: HomeColors.border),
                ),
              ),
              const SizedBox(height: 12),

              // Search Bar Skeleton
              Container(
                width: double.infinity,
                height: 48,
                decoration: BoxDecoration(
                  color: HomeColors.surface,
                  borderRadius: BorderRadius.circular(HomeRadius.md),
                  border: Border.all(color: HomeColors.border),
                ),
              ),
              const SizedBox(height: 16),

              // Farm Card Skeletons
              ...List.generate(
                2,
                (index) => Container(
                  width: double.infinity,
                  margin: const EdgeInsets.only(bottom: 12),
                  padding: const EdgeInsets.all(HomeSpacing.cardPadding),
                  decoration: BoxDecoration(
                    color: HomeColors.surface,
                    borderRadius: BorderRadius.circular(HomeRadius.xl),
                    border: Border.all(color: HomeColors.border),
                  ),
                  child: Column(
                    children: [
                      Row(
                        children: [
                          Container(
                            width: 68,
                            height: 68,
                            decoration: BoxDecoration(
                              color: HomeColors.border,
                              borderRadius: BorderRadius.circular(HomeRadius.md),
                            ),
                          ),
                          const SizedBox(width: HomeSpacing.sm),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Container(
                                  width: 140,
                                  height: 16,
                                  decoration: BoxDecoration(
                                    color: HomeColors.border,
                                    borderRadius: BorderRadius.circular(4),
                                  ),
                                ),
                                const SizedBox(height: 8),
                                Container(
                                  width: 90,
                                  height: 14,
                                  decoration: BoxDecoration(
                                    color: HomeColors.border,
                                    borderRadius: BorderRadius.circular(4),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 14),
                      Container(
                        width: double.infinity,
                        height: 32,
                        decoration: BoxDecoration(
                          color: HomeColors.borderSubtle,
                          borderRadius: BorderRadius.circular(HomeRadius.sm),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        );
      },
    );
  }
}
