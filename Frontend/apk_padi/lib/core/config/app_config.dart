import 'package:flutter/foundation.dart';

class AppConfig {
  const AppConfig._();

  static const _apiScheme = String.fromEnvironment(
    'API_SCHEME',
    defaultValue: 'http',
  );
  static const _configuredApiPort = int.fromEnvironment(
    'API_PORT',
    defaultValue: 8000,
  );
  static const _apiBaseUrlOverride = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: '',
  );
  static const _apiHostOverride = String.fromEnvironment(
    'API_HOST',
    defaultValue: '',
  );
  static const _apiHostsOverride = String.fromEnvironment(
    'API_HOSTS',
    defaultValue: '',
  );
  static const _apiLanHost = String.fromEnvironment(
    'API_LAN_HOST',
    defaultValue: '192.168.100.10',
  );
  static const _connectTimeoutSeconds = int.fromEnvironment(
    'API_CONNECT_TIMEOUT_SECONDS',
    defaultValue: 30,
  );

  static final List<String> candidateHosts = _resolveCandidateHosts();

  static String activeHost = candidateHosts.first;

  static bool get hasExplicitBaseUrl => _apiBaseUrlOverride.trim().isNotEmpty;

  static List<String> get candidateBaseUrls {
    if (hasExplicitBaseUrl) {
      return [_normalizeBaseUrl(_apiBaseUrlOverride)];
    }

    return candidateHosts.map(_baseUrlForHost).toList(growable: false);
  }

  static void useHost(String host) {
    if (candidateHosts.contains(host)) {
      activeHost = host;
    }
  }

  static void switchToNextHost() {
    if (candidateHosts.length <= 1) {
      return;
    }

    final currentIndex = candidateHosts.indexOf(activeHost);
    final nextIndex = (currentIndex + 1) % candidateHosts.length;
    activeHost = candidateHosts[nextIndex];
    debugPrint('[AppConfig] Switching API host to: $activeHost');
  }

  static String get apiBaseUrl {
    if (hasExplicitBaseUrl) {
      return _normalizeBaseUrl(_apiBaseUrlOverride);
    }

    if (kIsWeb) {
      final browserHost = Uri.base.host;
      final apiHost =
          browserHost.isEmpty || browserHost == 'localhost'
              ? '127.0.0.1'
              : browserHost;

      return _baseUrlForHost(apiHost);
    }

    return switch (defaultTargetPlatform) {
      TargetPlatform.android ||
      TargetPlatform.iOS =>
        _baseUrlForHost(activeHost),
      TargetPlatform.macOS ||
      TargetPlatform.linux ||
      TargetPlatform.windows ||
      TargetPlatform.fuchsia => _baseUrlForHost('127.0.0.1'),
    };
  }

  static String get apiHealthUrl {
    return '${apiBaseUrl.replaceFirst('/api/v1', '')}/api/v1/health';
  }

  static Duration get apiConnectTimeout {
    return Duration(
      seconds: _connectTimeoutSeconds < 5 ? 5 : _connectTimeoutSeconds,
    );
  }

  static const deviceName = String.fromEnvironment(
    'DEVICE_NAME',
    defaultValue: 'P.A.D.I Mobile',
  );

  static List<String> _resolveCandidateHosts() {
    final overrideHosts = [
      ..._splitHosts(_apiHostsOverride),
      if (_apiHostOverride.trim().isNotEmpty) _apiHostOverride.trim(),
    ];

    if (overrideHosts.isNotEmpty) {
      return _dedupe(overrideHosts);
    }

    if (!kIsWeb && defaultTargetPlatform == TargetPlatform.android) {
      return _dedupe([
        if (_apiLanHost.trim().isNotEmpty) _apiLanHost.trim(),
        '10.0.2.2',
      ]);
    }

    if (!kIsWeb && defaultTargetPlatform == TargetPlatform.iOS) {
      return _dedupe([
        '127.0.0.1',
        'localhost',
      ]);
    }

    return _dedupe([
      '127.0.0.1',
      'localhost',
      '10.0.2.2',
    ]);
  }

  static Iterable<String> _splitHosts(String rawHosts) sync* {
    for (final host in rawHosts.split(',')) {
      final trimmedHost = host.trim();
      if (trimmedHost.isNotEmpty) {
        yield trimmedHost;
      }
    }
  }

  static List<String> _dedupe(Iterable<String> hosts) {
    return hosts.toSet().toList(growable: false);
  }

  static String _baseUrlForHost(String host) {
    return '$_apiScheme://$host:$_backendApiPort/api/v1';
  }

  static String _normalizeBaseUrl(String baseUrl) {
    final normalized = baseUrl.trim().replaceFirst(RegExp(r'/+$'), '');
    final uri = Uri.tryParse(normalized);

    if (uri != null && uri.port == 8001) {
      final correctedUri = uri.replace(port: 8000);

      if (kDebugMode) {
        debugPrint(
          '[AppConfig] API_BASE_URL uses 8001, which is reserved for the AI '
          'service. Using Laravel backend URL: $correctedUri',
        );
      }

      return correctedUri.toString().replaceFirst(RegExp(r'/+$'), '');
    }

    return normalized;
  }

  static int get _backendApiPort {
    if (_configuredApiPort == 8001) {
      if (kDebugMode) {
        debugPrint(
          '[AppConfig] API_PORT=8001 points to the AI service. '
          'Using Laravel backend port 8000.',
        );
      }

      return 8000;
    }

    return _configuredApiPort;
  }
}
