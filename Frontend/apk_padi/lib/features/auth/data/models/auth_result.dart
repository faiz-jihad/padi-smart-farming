import 'package:padi/features/auth/domain/entities/app_user.dart';

class AuthResult {
  const AuthResult({required this.user, this.token});

  final AppUser user;
  final String? token;
}
