import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:provider/provider.dart';

import '../models/intervention.dart';
import '../providers/auth_provider.dart';
import '../services/api_client.dart';
import '../services/intervention_service.dart';
import '../widgets/badges.dart';
import 'intervention_detail_screen.dart';
import 'new_intervention_screen.dart';

class InterventionsScreen extends StatefulWidget {
  const InterventionsScreen({super.key});

  @override
  State<InterventionsScreen> createState() => _InterventionsScreenState();
}

class _InterventionsScreenState extends State<InterventionsScreen> {
  late final InterventionService _service;
  late Future<List<Intervention>> _future;

  @override
  void initState() {
    super.initState();
    _service = InterventionService(context.read<AuthProvider>().client);
    _future = _charger();
  }

  Future<List<Intervention>> _charger() {
    return _service.mesInterventions();
  }

  Future<void> _rafraichir() async {
    setState(() => _future = _charger());
    await _future;
  }

  Future<void> _nouvelleIntervention() async {
    await Navigator.of(context).push(
      MaterialPageRoute(builder: (_) => const NewInterventionScreen()),
    );
    if (mounted) _rafraichir();
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();

    return Scaffold(
      appBar: AppBar(
        title: const Text('Mes interventions'),
        actions: [
          IconButton(
            icon: const Icon(Icons.logout),
            tooltip: 'Déconnexion',
            onPressed: () => auth.deconnecter(),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: _nouvelleIntervention,
        icon: const Icon(Icons.add),
        label: const Text('Nouvelle intervention'),
      ),
      body: RefreshIndicator(
        onRefresh: _rafraichir,
        child: FutureBuilder<List<Intervention>>(
          future: _future,
          builder: (context, snapshot) {
            if (snapshot.connectionState == ConnectionState.waiting) {
              return const Center(child: CircularProgressIndicator());
            }

            if (snapshot.hasError) {
              final message = snapshot.error is ApiException
                  ? (snapshot.error as ApiException).message
                  : 'Impossible de charger les interventions.';
              return _EtatVide(
                icone: Icons.error_outline,
                message: message,
                surAction: _rafraichir,
              );
            }

            final interventions = snapshot.data ?? [];

            if (interventions.isEmpty) {
              return _EtatVide(
                icone: Icons.task_alt,
                message: 'Aucune intervention pour le moment. '
                    'Créez-en une dès réception d\'un appel.',
                surAction: _rafraichir,
              );
            }

            return ListView.separated(
              padding: const EdgeInsets.all(16),
              itemCount: interventions.length,
              separatorBuilder: (_, _) => const SizedBox(height: 12),
              itemBuilder: (context, index) {
                final intervention = interventions[index];
                return _InterventionCard(
                  intervention: intervention,
                  onTap: () async {
                    await Navigator.of(context).push(
                      MaterialPageRoute(
                        builder: (_) => InterventionDetailScreen(interventionId: intervention.id),
                      ),
                    );
                    if (mounted) _rafraichir();
                  },
                );
              },
            );
          },
        ),
      ),
    );
  }
}

class _InterventionCard extends StatelessWidget {
  const _InterventionCard({required this.intervention, required this.onTap});

  final Intervention intervention;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: InkWell(
        borderRadius: BorderRadius.circular(12),
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(intervention.numero,
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                  UrgenceBadge(niveau: intervention.niveauUrgence),
                ],
              ),
              const SizedBox(height: 8),
              Row(
                children: [
                  const Icon(Icons.person_outline, size: 16, color: Colors.black54),
                  const SizedBox(width: 4),
                  Text(intervention.patient?.nomComplet ?? 'Patient inconnu'),
                ],
              ),
              if (intervention.lieuPriseEnCharge != null) ...[
                const SizedBox(height: 4),
                Row(
                  children: [
                    const Icon(Icons.location_on_outlined, size: 16, color: Colors.black54),
                    const SizedBox(width: 4),
                    Expanded(child: Text(intervention.lieuPriseEnCharge!)),
                  ],
                ),
              ],
              const SizedBox(height: 12),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  StatutBadge(statut: intervention.statut),
                  if (intervention.demandeRecueAt != null)
                    Text(
                      DateFormat('dd/MM HH:mm').format(intervention.demandeRecueAt!),
                      style: const TextStyle(fontSize: 12, color: Colors.black45),
                    ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _EtatVide extends StatelessWidget {
  const _EtatVide({required this.icone, required this.message, required this.surAction});

  final IconData icone;
  final String message;
  final Future<void> Function() surAction;

  @override
  Widget build(BuildContext context) {
    return LayoutBuilder(
      builder: (context, constraints) => SingleChildScrollView(
        physics: const AlwaysScrollableScrollPhysics(),
        child: ConstrainedBox(
          constraints: BoxConstraints(minHeight: constraints.maxHeight),
          child: Center(
            child: Padding(
              padding: const EdgeInsets.all(32),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(icone, size: 48, color: Colors.black26),
                  const SizedBox(height: 16),
                  Text(message, textAlign: TextAlign.center),
                  const SizedBox(height: 16),
                  OutlinedButton(onPressed: surAction, child: const Text('Réessayer')),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
