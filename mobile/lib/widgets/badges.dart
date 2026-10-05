import 'package:flutter/material.dart';

import '../theme/app_theme.dart';

class AppBadge extends StatelessWidget {
  const AppBadge({super.key, required this.label, required this.color});

  final String label;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.12),
        borderRadius: BorderRadius.circular(20),
      ),
      child: Text(
        label,
        style: TextStyle(color: color, fontWeight: FontWeight.w600, fontSize: 12),
      ),
    );
  }
}

class UrgenceBadge extends StatelessWidget {
  const UrgenceBadge({super.key, required this.niveau});

  final String niveau;

  @override
  Widget build(BuildContext context) {
    return AppBadge(label: libelleNiveauUrgence(niveau), color: couleurNiveauUrgence(niveau));
  }
}

class StatutBadge extends StatelessWidget {
  const StatutBadge({super.key, required this.statut});

  final String statut;

  @override
  Widget build(BuildContext context) {
    final color = switch (statut) {
      'intervention_terminee' => AppColors.disponible,
      'annulee' => AppColors.rouge,
      'demande_recue' => Colors.grey,
      _ => AppColors.orange,
    };
    return AppBadge(label: libelleStatutIntervention(statut), color: color);
  }
}

class SaturationBadge extends StatelessWidget {
  const SaturationBadge({super.key, required this.statut});

  final String statut;

  @override
  Widget build(BuildContext context) {
    return AppBadge(label: libelleSaturation(statut), color: couleurSaturation(statut));
  }
}
