import 'orientation.dart';
import 'patient.dart';

/// Ordre du parcours d'une intervention (section 10 et 13 du cahier des charges).
const statutsIntervention = [
  'demande_recue',
  'intervention_affectee',
  'equipe_en_route',
  'patient_pris_en_charge',
  'recherche_structure',
  'structure_selectionnee',
  'en_route_structure',
  'patient_arrive',
  'intervention_terminee',
  'annulee',
];

class Intervention {
  final int id;
  final String numero;
  final Patient? patient;
  final String? regionNom;
  final String? lieuPriseEnCharge;
  final String? motif;
  final String niveauUrgence;
  final String? structureDestinationNom;
  final int? structureDestinationId;
  final String statut;
  final DateTime? demandeRecueAt;
  final DateTime createdAt;
  final List<Orientation> orientations;

  Intervention({
    required this.id,
    required this.numero,
    this.patient,
    this.regionNom,
    this.lieuPriseEnCharge,
    this.motif,
    required this.niveauUrgence,
    this.structureDestinationNom,
    this.structureDestinationId,
    required this.statut,
    this.demandeRecueAt,
    required this.createdAt,
    this.orientations = const [],
  });

  Orientation? get derniereOrientation => orientations.isEmpty ? null : orientations.last;

  factory Intervention.fromJson(Map<String, dynamic> json) {
    return Intervention(
      id: json['id'] as int,
      numero: json['numero'] as String,
      patient: json['patient'] != null
          ? Patient.fromJson(json['patient'] as Map<String, dynamic>)
          : null,
      regionNom: json['region'] != null ? json['region']['nom'] as String? : null,
      lieuPriseEnCharge: json['lieu_prise_en_charge'] as String?,
      motif: json['motif'] as String?,
      niveauUrgence: json['niveau_urgence'] as String? ?? 'modere',
      structureDestinationNom: json['structure_destination'] != null
          ? json['structure_destination']['nom'] as String?
          : null,
      structureDestinationId: json['structure_destination_id'] as int?,
      statut: json['statut'] as String? ?? 'demande_recue',
      demandeRecueAt: json['demande_recue_at'] != null
          ? DateTime.tryParse(json['demande_recue_at'] as String)
          : null,
      createdAt: DateTime.tryParse(json['created_at'] as String? ?? '') ?? DateTime.now(),
      orientations: (json['orientations'] as List?)
              ?.map((o) => Orientation.fromJson(o as Map<String, dynamic>))
              .toList() ??
          const [],
    );
  }
}
