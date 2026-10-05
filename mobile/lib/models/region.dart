class Region {
  final int id;
  final String code;
  final String nom;

  Region({required this.id, required this.code, required this.nom});

  factory Region.fromJson(Map<String, dynamic> json) {
    return Region(
      id: json['id'] as int,
      code: json['code'] as String,
      nom: json['nom'] as String,
    );
  }
}
