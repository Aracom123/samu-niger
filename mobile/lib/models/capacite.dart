class Capacite {
  final int id;
  final int structureSanitaireId;
  final int litsTotaux;
  final int litsOccupes;
  final int litsReserves;
  final int litsIndisponibles;
  final int litsDisponibles;
  final String statutSaturation;
  final DateTime? updatedAt;
  final bool perimee;

  Capacite({
    required this.id,
    required this.structureSanitaireId,
    required this.litsTotaux,
    required this.litsOccupes,
    required this.litsReserves,
    required this.litsIndisponibles,
    required this.litsDisponibles,
    required this.statutSaturation,
    this.updatedAt,
    this.perimee = false,
  });

  factory Capacite.fromJson(Map<String, dynamic> json) {
    return Capacite(
      id: json['id'] as int,
      structureSanitaireId: json['structure_sanitaire_id'] as int,
      litsTotaux: json['lits_totaux'] as int? ?? 0,
      litsOccupes: json['lits_occupes'] as int? ?? 0,
      litsReserves: json['lits_reserves'] as int? ?? 0,
      litsIndisponibles: json['lits_indisponibles'] as int? ?? 0,
      litsDisponibles: json['lits_disponibles'] as int? ?? 0,
      statutSaturation: json['statut_saturation'] as String? ?? 'saturee',
      updatedAt: json['updated_at'] != null
          ? DateTime.tryParse(json['updated_at'] as String)
          : null,
      perimee: json['perimee'] as bool? ?? false,
    );
  }
}
