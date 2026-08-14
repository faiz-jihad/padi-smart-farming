class AppUser {
  const AppUser({
    required this.id,
    required this.name,
    required this.email,
    required this.role,
    required this.status,
    this.phone,
    this.roleLabel,
    this.statusLabel,
  });

  final int id;
  final String name;
  final String email;
  final String? phone;
  final String role;
  final String status;
  final String? roleLabel;
  final String? statusLabel;

  AppUser copyWith({String? name, String? phone}) {
    return AppUser(
      id: id,
      name: name ?? this.name,
      email: email,
      phone: phone ?? this.phone,
      role: role,
      status: status,
      roleLabel: roleLabel,
      statusLabel: statusLabel,
    );
  }
}
