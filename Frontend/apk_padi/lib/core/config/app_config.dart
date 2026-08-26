import 'package:flutter/foundation.dart';

class AppConfig {
  const AppConfig._();

  static const _localApiPort = 8000;

  static final List<String> candidateHosts = [
    const String.fromEnvironment('API_HOST', defaultValue: ''),
    '192.168.100.10',
    '10.0.2.2',
    '127.0.0.1',
  ].where((h) => h.isNotEmpty).toSet().toList();

  static String activeHost = candidateHosts.first;

  static void switchToNextHost() {
    final currentIndex = candidateHosts.indexOf(activeHost);
    final nextIndex = (currentIndex + 1) % candidateHosts.length;
    activeHost = candidateHosts[nextIndex];
    debugPrint('🔄 [AppConfig] Switching API Host to: $activeHost');
  }

  static String get apiBaseUrl {
    const override = String.fromEnvironment('API_BASE_URL');
    if (override.isNotEmpty) {
      return override;
    }

    if (kIsWeb) {
      final browserHost = Uri.base.host;
      final apiHost =
          browserHost.isEmpty || browserHost == '127.0.0.1'
              ? 'localhost'
              : browserHost;

      return 'http://$apiHost:$_localApiPort/api/v1';
    }

    return switch (defaultTargetPlatform) {
      TargetPlatform.android ||
      TargetPlatform.iOS =>
        'http://$activeHost:$_localApiPort/api/v1',
      TargetPlatform.macOS ||
      TargetPlatform.linux ||
      TargetPlatform.windows ||
      TargetPlatform.fuchsia => 'http://localhost:$_localApiPort/api/v1',
    };
  }

  static const deviceName = String.fromEnvironment(
    'DEVICE_NAME',
    defaultValue: 'P.A.D.I Mobile',
  );
}
