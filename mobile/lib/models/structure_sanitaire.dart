import 'capacite.dart';

class StructureSanitaire {
  final int id;
  final String code;
  final String nom;
  final String? regionNom;
  final String? typeStructureNom;
  final String? telephone;
  final String? adresse;
  final String statut;
  final Capacite? capacite;

  StructureSanitaire({
    required this.id,
    required this.code,
    required this.nom,
    this.regionNom,
    this.typeStructureNom,
    this.telephone,
    this.adresse,
    required this.statut,
    this.capacite,
  });

  factory StructureSanitaire.fromJson(Map<String, dynamic> json) {
    return StructureSanitaire(
      id: json['id'] as int,
      code: json['code'] as String,
      nom: json['nom'] as String,
      regionNom: json['region'] != null ? json['region']['nom'] as String? : null,
      typeStructureNom: json['type_structure'] != null
          ? json['type_structure']['nom'] as String?
          : null,
      telephone: json['telephone'] as String?,
      adresse: json['adresse'] as String?,
      statut: json['statut'] as String? ?? 'active',
      capacite: json['capacite'] != null
          ? Capacite.fromJson(json['capacite'] as Map<String, dynamic>)
          : null,
    );
  }
}
