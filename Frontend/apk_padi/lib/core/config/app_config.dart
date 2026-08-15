class AppConfig {
  const AppConfig._();

  static const apiBaseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'http://10.0.2.2:8000/api/v1',
  );

  static const deviceName = String.fromEnvironment(
    'DEVICE_NAME',
    defaultValue: 'P.A.D.I Mobile',
  );
}
