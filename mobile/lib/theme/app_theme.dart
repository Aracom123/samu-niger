import 'package:flutter/material.dart';

/// Couleurs issues de la charte graphique du Ministère de la Santé et de
/// l'Hygiène Publique (MS/HP), extraites du logo officiel.
class AppColors {
  AppColors._();

  static const vert = Color(0xFF489048);
  static const vertFonce = Color(0xFF1E4620);
  static const rouge = Color(0xFFE6000D);
  static const orange = Color(0xFFF59100);

  static const disponible = Color(0xFF2E7D32);
  static const capaciteReduite = Color(0xFFF59100);
  static const presqueSaturee = Color(0xFFEF6C00);
  static const saturee = Color(0xFFE6000D);
}

class AppTheme {
  AppTheme._();

  static ThemeData get light {
    final base = ThemeData(
      useMaterial3: true,
      colorScheme: ColorScheme.fromSeed(
        seedColor: AppColors.vert,
        primary: AppColors.vert,
        error: AppColors.rouge,
      ),
    );

    return base.copyWith(
      appBarTheme: const AppBarTheme(
        backgroundColor: AppColors.vertFonce,
        foregroundColor: Colors.white,
        elevation: 0,
        centerTitle: false,
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: AppColors.vert,
          foregroundColor: Colors.white,
          padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 20),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
        filled: true,
        fillColor: Colors.white,
      ),
      scaffoldBackgroundColor: const Color(0xFFF5F7F5),
      cardTheme: CardThemeData(
        elevation: 0,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(12),
          side: BorderSide(color: Colors.black.withValues(alpha: 0.06)),
        ),
      ),
    );
  }
}

/// Couleur associée à un statut de saturation de capacité.
Color couleurSaturation(String statut) {
  switch (statut) {
    case 'disponible':
      return AppColors.disponible;
    case 'capacite_reduite':
      return AppColors.capaciteReduite;
    case 'presque_saturee':
      return AppColors.presqueSaturee;
    case 'saturee':
      return AppColors.saturee;
    default:
      return Colors.grey;
  }
}

String libelleSaturation(String statut) {
  switch (statut) {
    case 'disponible':
      return 'Disponible';
    case 'capacite_reduite':
      return 'Capacité réduite';
    case 'presque_saturee':
      return 'Presque saturée';
    case 'saturee':
      return 'Saturée';
    default:
      return statut;
  }
}

String libelleStatutIntervention(String statut) {
  const libelles = {
    'demande_recue': 'Demande reçue',
    'intervention_affectee': 'Intervention affectée',
    'equipe_en_route': 'Équipe en route',
    'patient_pris_en_charge': 'Patient pris en charge',
    'recherche_structure': 'Recherche de structure',
    'structure_selectionnee': 'Structure sélectionnée',
    'en_route_structure': 'En route vers la structure',
    'patient_arrive': 'Patient arrivé',
    'intervention_terminee': 'Intervention terminée',
    'annulee': 'Annulée',
  };
  return libelles[statut] ?? statut;
}

String libelleNiveauUrgence(String niveau) {
  const libelles = {
    'critique': 'Critique',
    'urgent': 'Urgent',
    'modere': 'Modéré',
    'faible': 'Faible',
  };
  return libelles[niveau] ?? niveau;
}

Color couleurNiveauUrgence(String niveau) {
  switch (niveau) {
    case 'critique':
      return AppColors.rouge;
    case 'urgent':
      return AppColors.orange;
    case 'modere':
      return Colors.blue;
    default:
      return Colors.grey;
  }
}
