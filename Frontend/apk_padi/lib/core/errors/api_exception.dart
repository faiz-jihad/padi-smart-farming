class ApiException implements Exception {
  const ApiException(this.message, {this.statusCode, this.errors = const {}});

  final String message;
  final int? statusCode;
  final Map<String, List<String>> errors;

  bool get isOffline =>
      statusCode == null &&
      (message.toLowerCase().contains('koneksi') ||
          message.toLowerCase().contains('network') ||
          message.toLowerCase().contains('socket') ||
          message.toLowerCase().contains('internet') ||
          message.toLowerCase().contains('connection'));

  bool get isTechnicalError => statusCode != null && statusCode! >= 500;

  bool get isNotFound => statusCode == 404;

  bool get isUnauthorized => statusCode == 401;

  String? fieldError(String field) {
    final messages = errors[field];
    return messages == null || messages.isEmpty ? null : messages.first;
  }

  @override
  String toString() => message;
}
