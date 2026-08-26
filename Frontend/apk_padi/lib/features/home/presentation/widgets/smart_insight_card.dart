import 'package:flutter/material.dart';
import 'package:padi/features/home/presentation/tokens/home_tokens.dart';

class SmartInsightCard extends StatelessWidget {
  const SmartInsightCard({
    super.key,
    required this.title,
    required this.description,
    required this.actionLabel,
    required this.onActionTap,
    this.badgeText = 'Dokter Tanaman AI',
  });

  final String title;
  final String description;
  final String actionLabel;
  final VoidCallback onActionTap;
  final String badgeText;

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: HomeColors.surface,
        borderRadius: BorderRadius.circular(HomeRadius.xl),
        border: Border.all(color: HomeColors.border),
        boxShadow: HomeShadows.subtle,
      ),
      child: Material(
        color: Colors.transparent,
        borderRadius: BorderRadius.circular(HomeRadius.xl),
        child: InkWell(
          onTap: onActionTap,
          borderRadius: BorderRadius.circular(HomeRadius.xl),
          child: Padding(
            padding: const EdgeInsets.all(HomeSpacing.cardPadding),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.center,
              children: [
                // Photographic Leaf Thumbnail with Scanning Border
                Container(
                  width: 60,
                  height: 60,
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(HomeRadius.md),
                    border: Border.all(color: HomeColors.primaryGreen, width: 2),
                    boxShadow: [
                      BoxShadow(
                        color: HomeColors.primaryGreen.withOpacity(0.18),
                        blurRadius: 8,
                        offset: const Offset(0, 3),
                      ),
                    ],
                    image: const DecorationImage(
                      image: AssetImage('assets/images/onboarding_2.jpeg'),
                      fit: BoxFit.cover,
                    ),
                  ),
                  child: Container(
                    decoration: BoxDecoration(
                      color: HomeColors.deepGreen.withOpacity(0.25),
                      borderRadius: BorderRadius.circular(HomeRadius.md - 2),
                    ),
                    child: const Center(
                      child: Icon(
                        Icons.crop_free_rounded,
                        color: Colors.white,
                        size: 28,
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: HomeSpacing.sm),

                // Text Content
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                            decoration: BoxDecoration(
                              color: HomeColors.lightGreen,
                              borderRadius: BorderRadius.circular(HomeRadius.pill),
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                const Icon(
                                  Icons.auto_awesome_rounded,
                                  color: HomeColors.primaryGreen,
                                  size: 11,
                                ),
                                const SizedBox(width: 3),
                                Text(
                                  badgeText.toUpperCase(),
                                  style: const TextStyle(
                                    color: HomeColors.primaryGreen,
                                    fontSize: 9,
                                    fontWeight: FontWeight.w900,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 4),

                      Text(
                        title,
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                        style: const TextStyle(
                          color: HomeColors.textPrimary,
                          fontSize: 14.5,
                          fontWeight: FontWeight.w800,
                          letterSpacing: -0.2,
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        description,
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        style: HomeTypography.supporting.copyWith(
                          fontSize: 11.5,
                        ),
                      ),
                      const SizedBox(height: 6),

                      // Action CTA Link
                      Row(
                        children: [
                          Text(
                            actionLabel,
                            style: const TextStyle(
                              color: HomeColors.primaryGreen,
                              fontSize: 12,
                              fontWeight: FontWeight.w800,
                            ),
                          ),
                          const SizedBox(width: 3),
                          const Icon(
                            Icons.arrow_forward_rounded,
                            color: HomeColors.primaryGreen,
                            size: 13,
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
