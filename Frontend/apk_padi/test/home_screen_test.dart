import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:padi/features/home/presentation/screens/home_screen.dart';

void main() {
  testWidgets('HomeScreen mounts and renders without type error', (tester) async {
    await tester.pumpWidget(
      const ProviderScope(
        child: MaterialApp(
          home: HomeScreen(),
        ),
      ),
    );

    // Initial pump
    await tester.pump();
    expect(find.byType(HomeScreen), findsOneWidget);
  });
}
