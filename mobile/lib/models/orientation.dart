class Orientation {
  final int id;
  final int interventionId;
  final int structureSanitaireId;
  final String? structureSanitaireNom;
  final String statut;
  final String? motifRefus;

  Orientation({
    required this.id,
    required this.interventionId,
    required this.structureSanitaireId,
    this.structureSanitaireNom,
    required this.statut,
    this.motifRefus,
  });

  factory Orientation.fromJson(Map<String, dynamic> json) {
    return Orientation(
      id: json['id'] as int,
      interventionId: json['intervention_id'] as int,
      structureSanitaireId: json['structure_sanitaire_id'] as int,
      structureSanitaireNom: json['structure_sanitaire'] != null
          ? json['structure_sanitaire']['nom'] as String?
          : null,
      statut: json['statut'] as String? ?? 'proposee',
      motifRefus: json['motif_refus'] as String?,
    );
  }
}
