import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:padi/core/localization/app_language.dart';
import 'package:padi/core/router/app_router.dart';
import 'package:padi/features/auth/presentation/widgets/padi_theme.dart';

void main() {
  runApp(const ProviderScope(child: PadiApp()));
}

class PadiApp extends ConsumerWidget {
  const PadiApp({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final router = ref.watch(appRouterProvider);
    final currentLang = ref.watch(languageProvider);

    // Map system-level Material widgets safely (since Flutter SDK lacks built-in GlobalMaterialLocalizations for 'jv')
    final systemLocale = currentLang == AppLanguage.en ? const Locale('en') : const Locale('id');

    return MaterialApp.router(
      title: 'P.A.D.I.',
      debugShowCheckedModeBanner: false,
      theme: buildPadiTheme(),
      routerConfig: router,
      locale: systemLocale,
      supportedLocales: const [
        Locale('id'),
        Locale('en'),
      ],
      localizationsDelegates: const [
        GlobalMaterialLocalizations.delegate,
        GlobalWidgetsLocalizations.delegate,
        GlobalCupertinoLocalizations.delegate,
      ],
    );
  }
}
