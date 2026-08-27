import 'dart:async';
import 'package:flutter/foundation.dart';

/// A utility class to debounce rapid invocations such as search input keystrokes.
class Debouncer {
  Debouncer({this.milliseconds = 350});

  final int milliseconds;
  Timer? _timer;

  /// Runs the provided [action] after [milliseconds] delay, cancelling any pending action.
  void run(VoidCallback action) {
    _timer?.cancel();
    _timer = Timer(Duration(milliseconds: milliseconds), action);
  }

  /// Cancels any scheduled debounce timer.
  void cancel() {
    _timer?.cancel();
    _timer = null;
  }

  /// Disposes the debouncer and cancels any active timer.
  void dispose() {
    cancel();
  }
}
