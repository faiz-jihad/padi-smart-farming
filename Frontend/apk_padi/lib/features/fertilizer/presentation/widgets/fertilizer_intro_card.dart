import 'package:flutter/material.dart';
import 'package:padi/features/auth/presentation/widgets/padi_theme.dart';

class FertilizerIntroCard extends StatelessWidget {
  const FertilizerIntroCard({super.key});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: padiField,
        borderRadius: BorderRadius.circular(24),
      ),
      child: Row(
        children: [
          Container(
            width: 58,
            height: 58,
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(18),
            ),
            child: const Icon(
              Icons.calculate_rounded,
              color: padiGreen,
              size: 31,
            ),
          ),
          const SizedBox(width: 15),
          const Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Hitung kebutuhan pupuk',
                  style: TextStyle(
                    color: padiInk,
                    fontSize: 18,
                    fontWeight: FontWeight.w900,
                  ),
                ),
                SizedBox(height: 5),
                Text(
                  'Masukkan luas lahan untuk mendapatkan perkiraan kebutuhan pupuk.',
                  style: TextStyle(
                    color: padiMuted,
                    fontSize: 13,
                    height: 1.35,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}