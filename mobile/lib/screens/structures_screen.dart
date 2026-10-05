import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../models/structure_sanitaire.dart';
import '../providers/auth_provider.dart';
import '../services/api_client.dart';
import '../services/orientation_service.dart';
import '../services/structure_service.dart';
import '../theme/app_theme.dart';
import '../widgets/badges.dart';

/// Recherche des structures sanitaires et de leur disponibilité (section 11).
/// Si [interventionId] est fourni, permet de sélectionner une structure pour
/// y orienter le patient (section 11) ; sinon, écran de consultation seule.
class StructuresScreen extends StatefulWidget {
  const StructuresScreen({super.key, this.interventionId});

  final int? interventionId;

  @override
  State<StructuresScreen> createState() => _StructuresScreenState();
}

class _StructuresScreenState extends State<StructuresScreen> {
  late final StructureService _structureService;
  late final OrientationService _orientationService;
  late Future<List<StructureSanitaire>> _future;

  final _rechercheController = TextEditingController();
  String _terme = '';
  bool _orientationEnCours = false;

  @override
  void initState() {
    super.initState();
    final client = context.read<AuthProvider>().client;
    _structureService = StructureService(client);
    _orientationService = OrientationService(client);
    _future = _structureService.liste();
  }

  @override
  void dispose() {
    _rechercheController.dispose();
    super.dispose();
  }

  Future<void> _orienter(StructureSanitaire structure) async {
    final confirmer = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('Confirmer l\'orientation'),
        content: Text('Orienter le patient vers ${structure.nom} ?'),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(false),
            child: const Text('Annuler'),
          ),
          FilledButton(
            onPressed: () => Navigator.of(context).pop(true),
            child: const Text('Confirmer'),
          ),
        ],
      ),
    );

    if (confirmer != true) return;

    setState(() => _orientationEnCours = true);
    try {
      await _orientationService.orienter(
        interventionId: widget.interventionId!,
        structureSanitaireId: structure.id,
      );
      if (mounted) Navigator.of(context).pop(true);
    } on ApiException catch (e) {
      if (mounted) {
        setState(() => _orientationEnCours = false);
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final modeOrientation = widget.interventionId != null;

    return Scaffold(
      appBar: AppBar(
        title: Text(modeOrientation ? 'Choisir une structure' : 'Structures sanitaires'),
      ),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: TextField(
              controller: _rechercheController,
              decoration: InputDecoration(
                hintText: 'Rechercher une structure...',
                prefixIcon: const Icon(Icons.search),
                suffixIcon: _terme.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear),
                        onPressed: () {
                          _rechercheController.clear();
                          setState(() => _terme = '');
                        },
                      )
                    : null,
              ),
              onChanged: (v) => setState(() => _terme = v.trim().toLowerCase()),
            ),
          ),
          Expanded(
            child: FutureBuilder<List<StructureSanitaire>>(
              future: _future,
              builder: (context, snapshot) {
                if (snapshot.connectionState == ConnectionState.waiting) {
                  return const Center(child: CircularProgressIndicator());
                }

                if (snapshot.hasError) {
                  return Center(
                    child: Text(
                      snapshot.error is ApiException
                          ? (snapshot.error as ApiException).message
                          : 'Impossible de charger les structures.',
                    ),
                  );
                }

                var structures = snapshot.data ?? [];
                if (_terme.isNotEmpty) {
                  structures = structures
                      .where((s) =>
                          s.nom.toLowerCase().contains(_terme) ||
                          (s.regionNom?.toLowerCase().contains(_terme) ?? false))
                      .toList();
                }

                // Les plus disponibles en premier : utile pour orienter rapidement.
                structures.sort((a, b) {
                  final da = a.capacite?.litsDisponibles ?? -1;
                  final db = b.capacite?.litsDisponibles ?? -1;
                  return db.compareTo(da);
                });

                if (structures.isEmpty) {
                  return const Center(child: Text('Aucune structure trouvée.'));
                }

                return ListView.separated(
                  padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
                  itemCount: structures.length,
                  separatorBuilder: (_, _) => const SizedBox(height: 12),
                  itemBuilder: (context, index) {
                    final structure = structures[index];
                    return _StructureCard(
                      structure: structure,
                      modeOrientation: modeOrientation,
                      enCours: _orientationEnCours,
                      onOrienter: () => _orienter(structure),
                    );
                  },
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}

class _StructureCard extends StatelessWidget {
  const _StructureCard({
    required this.structure,
    required this.modeOrientation,
    required this.enCours,
    required this.onOrienter,
  });

  final StructureSanitaire structure;
  final bool modeOrientation;
  final bool enCours;
  final VoidCallback onOrienter;

  @override
  Widget build(BuildContext context) {
    final capacite = structure.capacite;

    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Text(structure.nom,
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 15)),
                ),
                if (capacite != null) SaturationBadge(statut: capacite.statutSaturation),
              ],
            ),
            const SizedBox(height: 4),
            Text(
              [structure.typeStructureNom, structure.regionNom]
                  .where((v) => v != null)
                  .join(' · '),
              style: const TextStyle(color: Colors.black54, fontSize: 13),
            ),
            const SizedBox(height: 12),
            if (capacite != null)
              Row(
                children: [
                  const Icon(Icons.bed_outlined, size: 18, color: AppColors.vert),
                  const SizedBox(width: 6),
                  Text(
                    '${capacite.litsDisponibles} lit(s) disponible(s) sur ${capacite.litsTotaux}',
                    style: const TextStyle(fontWeight: FontWeight.w500),
                  ),
                  if (capacite.perimee) ...[
                    const SizedBox(width: 8),
                    const Tooltip(
                      message: 'Donnée non actualisée récemment',
                      child: Icon(Icons.warning_amber_rounded, size: 18, color: AppColors.orange),
                    ),
                  ],
                ],
              )
            else
              const Text('Capacité non renseignée', style: TextStyle(color: Colors.black45)),
            if (modeOrientation) ...[
              const SizedBox(height: 12),
              SizedBox(
                width: double.infinity,
                child: OutlinedButton(
                  onPressed: enCours ? null : onOrienter,
                  child: const Text('Orienter vers cette structure'),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
