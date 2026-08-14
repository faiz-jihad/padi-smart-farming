import 'package:padi/core/router/app_router.dart';
import 'package:padi/features/auth/presentation/widgets/padi_theme.dart';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

void main() {
  runApp(const ProviderScope(child: PadiApp()));
}

class PadiApp extends ConsumerWidget {
  const PadiApp({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final router = ref.watch(appRouterProvider);

    return MaterialApp.router(
      title: 'P.A.D.I.',
      debugShowCheckedModeBanner: false,
      theme: buildPadiTheme(),
      routerConfig: router,
    );
  }
}
