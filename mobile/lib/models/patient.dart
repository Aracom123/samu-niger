class Patient {
  final int id;
  final String identifiant;
  final String? nom;
  final String? prenom;
  final String sexe;
  final int? ageApproximatif;
  final String? telephone;
  final String? localisationPriseEnCharge;
  final String? motif;
  final String niveauUrgence;
  final String? observations;

  Patient({
    required this.id,
    required this.identifiant,
    this.nom,
    this.prenom,
    required this.sexe,
    this.ageApproximatif,
    this.telephone,
    this.localisationPriseEnCharge,
    this.motif,
    required this.niveauUrgence,
    this.observations,
  });

  String get nomComplet {
    final complet = [nom, prenom].where((v) => v != null && v.isNotEmpty).join(' ');
    return complet.isEmpty ? identifiant : complet;
  }

  factory Patient.fromJson(Map<String, dynamic> json) {
    return Patient(
      id: json['id'] as int,
      identifiant: json['identifiant'] as String,
      nom: json['nom'] as String?,
      prenom: json['prenom'] as String?,
      sexe: json['sexe'] as String? ?? 'inconnu',
      ageApproximatif: json['age_approximatif'] as int?,
      telephone: json['telephone'] as String?,
      localisationPriseEnCharge: json['localisation_prise_en_charge'] as String?,
      motif: json['motif'] as String?,
      niveauUrgence: json['niveau_urgence'] as String? ?? 'modere',
      observations: json['observations'] as String?,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      if (nom != null) 'nom': nom,
      if (prenom != null) 'prenom': prenom,
      'sexe': sexe,
      if (ageApproximatif != null) 'age_approximatif': ageApproximatif,
      if (telephone != null) 'telephone': telephone,
      if (localisationPriseEnCharge != null)
        'localisation_prise_en_charge': localisationPriseEnCharge,
      if (motif != null) 'motif': motif,
      'niveau_urgence': niveauUrgence,
      if (observations != null) 'observations': observations,
    };
  }
}
