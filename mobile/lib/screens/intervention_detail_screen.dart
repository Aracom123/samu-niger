import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../models/intervention.dart';
import '../providers/auth_provider.dart';
import '../services/api_client.dart';
import '../services/intervention_service.dart';
import '../theme/app_theme.dart';
import '../widgets/badges.dart';
import 'patient_form_screen.dart';
import 'structures_screen.dart';

class InterventionDetailScreen extends StatefulWidget {
  const InterventionDetailScreen({super.key, required this.interventionId});

  final int interventionId;

  @override
  State<InterventionDetailScreen> createState() => _InterventionDetailScreenState();
}

class _InterventionDetailScreenState extends State<InterventionDetailScreen> {
  late final InterventionService _service;
  late Future<Intervention> _future;
  bool _actionEnCours = false;

  @override
  void initState() {
    super.initState();
    _service = InterventionService(context.read<AuthProvider>().client);
    _future = _service.detail(widget.interventionId);
  }

  void _recharger() {
    setState(() => _future = _service.detail(widget.interventionId));
  }

  Future<void> _changerStatut(String statut) async {
    setState(() => _actionEnCours = true);
    try {
      await _service.changerStatut(widget.interventionId, statut);
      _recharger();
    } on ApiException catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
      }
    } finally {
      if (mounted) setState(() => _actionEnCours = false);
    }
  }

  Future<void> _ouvrirOrientation() async {
    final oriente = await Navigator.of(context).push<bool>(
      MaterialPageRoute(
        builder: (_) => StructuresScreen(interventionId: widget.interventionId),
      ),
    );
    if (oriente == true) _recharger();
  }

  Future<void> _modifierPatient(Intervention intervention) async {
    if (intervention.patient == null) return;
    final modifie = await Navigator.of(context).push<bool>(
      MaterialPageRoute(
        builder: (_) => PatientFormScreen(patient: intervention.patient!),
      ),
    );
    if (modifie == true) _recharger();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Détail de l\'intervention')),
      body: FutureBuilder<Intervention>(
        future: _future,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          }

          if (snapshot.hasError) {
            final message = snapshot.error is ApiException
                ? (snapshot.error as ApiException).message
                : 'Impossible de charger l\'intervention.';
            return Center(child: Text(message));
          }

          final intervention = snapshot.data!;

          return RefreshIndicator(
            onRefresh: () async {
              _recharger();
              await _future;
            },
            child: ListView(
              padding: const EdgeInsets.all(16),
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(intervention.numero,
                        style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                    UrgenceBadge(niveau: intervention.niveauUrgence),
                  ],
                ),
                const SizedBox(height: 8),
                StatutBadge(statut: intervention.statut),
                const SizedBox(height: 20),
                _Section(
                  titre: 'Patient',
                  action: intervention.patient != null
                      ? IconButton(
                          icon: const Icon(Icons.edit_outlined),
                          tooltip: 'Modifier les informations patient',
                          onPressed: () => _modifierPatient(intervention),
                        )
                      : null,
                  enfant: intervention.patient == null
                      ? const Text('Aucun patient rattaché.')
                      : _InfoLigne(items: {
                          'Identifiant': intervention.patient!.identifiant,
                          'Nom': intervention.patient!.nomComplet,
                          'Sexe': intervention.patient!.sexe,
                          if (intervention.patient!.ageApproximatif != null)
                            'Âge approx.': '${intervention.patient!.ageApproximatif} ans',
                          if (intervention.patient!.telephone != null)
                            'Téléphone': intervention.patient!.telephone!,
                          if (intervention.patient!.motif != null)
                            'Motif': intervention.patient!.motif!,
                        }),
                ),
                const SizedBox(height: 16),
                _Section(
                  titre: 'Intervention',
                  enfant: _InfoLigne(items: {
                    if (intervention.regionNom != null) 'Région': intervention.regionNom!,
                    if (intervention.lieuPriseEnCharge != null)
                      'Lieu de prise en charge': intervention.lieuPriseEnCharge!,
                    if (intervention.motif != null) 'Motif': intervention.motif!,
                    if (intervention.demandeRecueAt != null)
                      'Demande reçue le':
                          DateFormat('dd/MM/yyyy à HH:mm').format(intervention.demandeRecueAt!),
                  }),
                ),
                const SizedBox(height: 16),
                _Section(
                  titre: 'Structure de destination',
                  enfant: intervention.structureDestinationNom != null
                      ? Text(intervention.structureDestinationNom!,
                          style: const TextStyle(fontWeight: FontWeight.w600))
                      : const Text('Pas encore orientée.'),
                ),
                const SizedBox(height: 28),
                _ProchaineAction(
                  intervention: intervention,
                  enCours: _actionEnCours,
                  onChangerStatut: _changerStatut,
                  onOrienter: _ouvrirOrientation,
                ),
              ],
            ),
          );
        },
      ),
    );
  }
}

/// Détermine, à partir du statut courant, la prochaine action que l'équipe
/// mobile peut déclencher (section 13 : parcours du patient).
class _ProchaineAction extends StatelessWidget {
  const _ProchaineAction({
    required this.intervention,
    required this.enCours,
    required this.onChangerStatut,
    required this.onOrienter,
  });

  final Intervention intervention;
  final bool enCours;
  final void Function(String statut) onChangerStatut;
  final VoidCallback onOrienter;

  @override
  Widget build(BuildContext context) {
    final (label, action) = switch (intervention.statut) {
      'demande_recue' => ('Affecter l\'intervention', () => onChangerStatut('intervention_affectee')),
      'intervention_affectee' => ('Démarrer le trajet', () => onChangerStatut('equipe_en_route')),
      'equipe_en_route' => ('Patient pris en charge', () => onChangerStatut('patient_pris_en_charge')),
      'patient_pris_en_charge' => ('Orienter le patient', onOrienter),
      'recherche_structure' => ('Choisir une autre structure', onOrienter),
      'en_route_structure' => ('Marquer le patient arrivé', () => onChangerStatut('patient_arrive')),
      'patient_arrive' => ('Clôturer l\'intervention', () => onChangerStatut('intervention_terminee')),
      _ => (null, null),
    };

    if (intervention.statut == 'structure_selectionnee') {
      return const _MessageAttente(
        message: 'En attente de confirmation de la structure sélectionnée.',
      );
    }

    if (label == null || action == null) {
      return const SizedBox.shrink();
    }

    return SizedBox(
      width: double.infinity,
      child: ElevatedButton(
        onPressed: enCours ? null : action,
        child: enCours
            ? const SizedBox(
                height: 20,
                width: 20,
                child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
              )
            : Text(label),
      ),
    );
  }
}

class _MessageAttente extends StatelessWidget {
  const _MessageAttente({required this.message});

  final String message;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: AppColors.orange.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(10),
      ),
      child: Row(
        children: [
          const Icon(Icons.hourglass_top, color: AppColors.orange),
          const SizedBox(width: 12),
          Expanded(child: Text(message)),
        ],
      ),
    );
  }
}

class _Section extends StatelessWidget {
  const _Section({required this.titre, required this.enfant, this.action});

  final String titre;
  final Widget enfant;
  final Widget? action;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(titre,
                    style: const TextStyle(fontWeight: FontWeight.bold, color: AppColors.vertFonce)),
                if (action != null) action!,
              ],
            ),
            const SizedBox(height: 8),
            enfant,
          ],
        ),
      ),
    );
  }
}

class _InfoLigne extends StatelessWidget {
  const _InfoLigne({required this.items});

  final Map<String, String> items;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: items.entries
          .map((e) => Padding(
                padding: const EdgeInsets.symmetric(vertical: 3),
                child: Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    SizedBox(
                      width: 140,
                      child: Text(e.key, style: const TextStyle(color: Colors.black54)),
                    ),
                    Expanded(child: Text(e.value)),
                  ],
                ),
              ))
          .toList(),
    );
  }
}
