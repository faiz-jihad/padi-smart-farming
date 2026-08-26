import 'dart:async';

import 'package:padi/core/errors/api_exception.dart';
import 'package:padi/core/storage/token_storage.dart';
import 'package:padi/features/auth/data/models/auth_result.dart';
import 'package:padi/features/auth/domain/entities/app_user.dart';
import 'package:padi/features/auth/domain/repositories/auth_repository.dart';
import 'package:flutter/foundation.dart';

enum AuthStatus { checking, authenticated, unauthenticated }

class AuthState {
  const AuthState({
    required this.status,
    this.user,
    this.isSubmitting = false,
    this.message,
    this.fieldErrors = const {},
  });

  const AuthState.checking() : this(status: AuthStatus.checking);

  const AuthState.unauthenticated({String? message})
    : this(status: AuthStatus.unauthenticated, message: message);

  const AuthState.authenticated(AppUser user)
    : this(status: AuthStatus.authenticated, user: user);

  final AuthStatus status;
  final AppUser? user;
  final bool isSubmitting;
  final String? message;
  final Map<String, List<String>> fieldErrors;

  AuthState copyWith({
    AuthStatus? status,
    AppUser? user,
    bool? isSubmitting,
    String? message,
    Map<String, List<String>>? fieldErrors,
    bool clearMessage = false,
  }) {
    return AuthState(
      status: status ?? this.status,
      user: user ?? this.user,
      isSubmitting: isSubmitting ?? this.isSubmitting,
      message: clearMessage ? null : message ?? this.message,
      fieldErrors: fieldErrors ?? this.fieldErrors,
    );
  }
}

class AuthController extends ChangeNotifier {
  AuthController(this._repository, this._tokenStorage);

  final AuthRepository _repository;
  final TokenStorage _tokenStorage;

  AuthState _state = const AuthState.checking();
  AuthState get state => _state;

  bool get isAuthenticated => _state.status == AuthStatus.authenticated;
  bool get isChecking => _state.status == AuthStatus.checking;

  Future<void> restoreSession() async {
    _setState(const AuthState.checking());

    final token = await _tokenStorage.readToken();
    if (token == null || token.isEmpty) {
      _setState(const AuthState.unauthenticated());
      return;
    }

    try {
      final user = await _repository.me();
      _setState(AuthState.authenticated(user));
    } catch (error) {
      if (_isInvalidSessionError(error)) {
        await _tokenStorage.clearToken();
      }
      _setState(AuthState.unauthenticated(message: _messageFromError(error)));
    }
  }

  Future<bool> login({required String email, required String password}) async {
    return _submitAuth(() => _repository.login(email: email, password: password));
  }

  Future<bool> register({
    required String name,
    required String email,
    required String phone,
    required String accountType,
    required String password,
    required String passwordConfirmation,
  }) async {
    return _submitAuth(
      () => _repository.register(
        name: name,
        email: email,
        phone: phone,
        accountType: accountType,
        password: password,
        passwordConfirmation: passwordConfirmation,
      ),
    );
  }

  Future<void> updateProfile({required String name, required String phone}) async {
    if (_state.isSubmitting) {
      return;
    }

    _setState(_state.copyWith(isSubmitting: true, clearMessage: true, fieldErrors: {}));

    try {
      final user = await _repository.updateProfile(name: name, phone: phone);
      _setState(AuthState.authenticated(user).copyWith(message: 'Profil berhasil diperbarui.'));
    } catch (error) {
      _applyError(error);
    }
  }

  Future<void> changePassword({
    required String currentPassword,
    required String password,
    required String passwordConfirmation,
  }) async {
    if (_state.isSubmitting) {
      return;
    }

    _setState(_state.copyWith(isSubmitting: true, clearMessage: true, fieldErrors: {}));

    try {
      await _repository.changePassword(
        currentPassword: currentPassword,
        password: password,
        passwordConfirmation: passwordConfirmation,
      );
      _setState(_state.copyWith(isSubmitting: false, message: 'Password berhasil diubah.'));
    } catch (error) {
      _applyError(error);
    }
  }

  Future<void> forgotPassword(String email) async {
    if (_state.isSubmitting) {
      return;
    }

    _setState(_state.copyWith(isSubmitting: true, clearMessage: true, fieldErrors: {}));

    try {
      await _repository.forgotPassword(email);
      _setState(_state.copyWith(isSubmitting: false, message: 'Instruksi reset password telah dikirim.'));
    } catch (error) {
      _applyError(error);
    }
  }

  Future<void> logout({bool allDevices = false}) async {
    try {
      if (allDevices) {
        await _repository.logoutAll();
      } else {
        await _repository.logout();
      }
    } catch (_) {
      // Token tetap dibersihkan agar pengguna tidak terjebak pada sesi rusak.
    }

    await clearSession();
  }

  Future<void> clearSession() async {
    await _tokenStorage.clearToken();
    _setState(const AuthState.unauthenticated());
  }

  Future<bool> _submitAuth(Future<AuthResult> Function() submit) async {
    if (_state.isSubmitting) {
      return false;
    }

    _setState(_state.copyWith(isSubmitting: true, clearMessage: true, fieldErrors: {}));

    try {
      final result = await submit();
      if (result.token != null) {
        await _tokenStorage.saveToken(result.token!);
      }
      _setState(AuthState.authenticated(result.user));
      return true;
    } catch (error) {
      _applyError(error);
      return false;
    }
  }

  void _applyError(Object error) {
    if (error is ApiException) {
      if (error.statusCode == 401) {
        unawaited(_tokenStorage.clearToken());
        _setState(AuthState.unauthenticated(message: error.message));
        return;
      }

      _setState(
        _state.copyWith(
          isSubmitting: false,
          message: error.message,
          fieldErrors: error.errors,
        ),
      );
      return;
    }

    _setState(_state.copyWith(isSubmitting: false, message: _messageFromError(error)));
  }

  String _messageFromError(Object error) {
    return error is ApiException ? error.message : 'Terjadi kesalahan. Silakan coba lagi.';
  }

  bool _isInvalidSessionError(Object error) {
    return error is ApiException &&
        (error.statusCode == 401 || error.statusCode == 403);
  }

  void _setState(AuthState state) {
    _state = state;
    notifyListeners();
  }
}
