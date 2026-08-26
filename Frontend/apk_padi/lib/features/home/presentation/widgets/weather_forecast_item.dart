import 'package:flutter/material.dart';
import 'package:padi/features/home/presentation/tokens/home_tokens.dart';

class WeatherForecastItem extends StatelessWidget {
  const WeatherForecastItem({
    super.key,
    required this.time,
    required this.temp,
    required this.icon,
    this.rainProb,
    this.isSelected = false,
    this.onTap,
  });

  final String time;
  final String temp;
  final IconData icon;
  final String? rainProb;
  final bool isSelected;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: Colors.transparent,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(HomeRadius.md),
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 180),
          width: 72,
          padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 6),
          decoration: BoxDecoration(
            color: isSelected ? HomeColors.lightGreen : HomeColors.surfaceMuted,
            borderRadius: BorderRadius.circular(HomeRadius.md),
            border: Border.all(
              color: isSelected ? HomeColors.primaryGreen : HomeColors.borderSubtle,
              width: isSelected ? 1.4 : 1.0,
            ),
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Text(
                time,
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: TextStyle(
                  color: isSelected ? HomeColors.primaryGreen : HomeColors.textSecondary,
                  fontSize: 11,
                  fontWeight: isSelected ? FontWeight.w800 : FontWeight.w600,
                ),
              ),
              const SizedBox(height: 6),
              Icon(
                icon,
                color: isSelected ? HomeColors.primaryGreen : HomeColors.skyBlue,
                size: 20,
              ),
              const SizedBox(height: 6),
              Text(
                temp,
                style: TextStyle(
                  color: isSelected ? HomeColors.primaryGreen : HomeColors.textPrimary,
                  fontSize: 13,
                  fontWeight: FontWeight.w800,
                ),
              ),
              if (rainProb != null) ...[
                const SizedBox(height: 4),
                Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Icon(
                      Icons.water_drop_rounded,
                      color: HomeColors.skyBlue,
                      size: 9,
                    ),
                    const SizedBox(width: 1),
                    Text(
                      rainProb!,
                      style: const TextStyle(
                        color: HomeColors.skyBlue,
                        fontSize: 9.5,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                  ],
                ),
              ],
            ],
          ),
        ),
      ),
    );
  }
}
