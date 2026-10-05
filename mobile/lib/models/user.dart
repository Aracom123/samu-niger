class AppUser {
  final int id;
  final String name;
  final String email;
  final String? telephone;
  final List<String> roles;

  AppUser({
    required this.id,
    required this.name,
    required this.email,
    this.telephone,
    required this.roles,
  });

  factory AppUser.fromJson(Map<String, dynamic> json, {List<String>? roles}) {
    return AppUser(
      id: json['id'] as int,
      name: json['name'] as String,
      email: json['email'] as String,
      telephone: json['telephone'] as String?,
      roles: roles ?? const [],
    );
  }
}
