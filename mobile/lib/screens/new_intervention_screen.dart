import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../models/patient.dart';
import '../models/region.dart';
import '../providers/auth_provider.dart';
import '../services/api_client.dart';
import '../services/intervention_service.dart';
import '../services/patient_service.dart';
import '../services/structure_service.dart';
import 'intervention_detail_screen.dart';

/// Permet à une équipe mobile de créer elle-même une nouvelle intervention
/// lorsqu'elle reçoit un appel, sans attendre qu'un administrateur la crée.
class NewInterventionScreen extends StatefulWidget {
  const NewInterventionScreen({super.key});

  @override
  State<NewInterventionScreen> createState() => _NewInterventionScreenState();
}

class _NewInterventionScreenState extends State<NewInterventionScreen> {
  final _formKey = GlobalKey<FormState>();
  late final StructureService _structureService;
  late final PatientService _patientService;
  late final InterventionService _interventionService;
  late Future<List<Region>> _regionsFuture;

  final _lieuController = TextEditingController();
  final _motifController = TextEditingController();
  final _nomController = TextEditingController();
  final _prenomController = TextEditingController();
  final _ageController = TextEditingController();

  int? _regionId;
  String _sexe = 'inconnu';
  String _niveauUrgence = 'modere';
  bool _enregistrement = false;

  @override
  void initState() {
    super.initState();
    final client = context.read<AuthProvider>().client;
    _structureService = StructureService(client);
    _patientService = PatientService(client);
    _interventionService = InterventionService(client);
    _regionsFuture = _structureService.regions();
  }

  @override
  void dispose() {
    _lieuController.dispose();
    _motifController.dispose();
    _nomController.dispose();
    _prenomController.dispose();
    _ageController.dispose();
    super.dispose();
  }

  Future<void> _creer() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _enregistrement = true);

    try {
      final patientProvisoire = Patient(
        id: 0,
        identifiant: '',
        nom: _nomController.text.trim().isEmpty ? null : _nomController.text.trim(),
        prenom: _prenomController.text.trim().isEmpty ? null : _prenomController.text.trim(),
        sexe: _sexe,
        ageApproximatif: int.tryParse(_ageController.text.trim()),
        niveauUrgence: _niveauUrgence,
      );

      final patient = await _patientService.enregistrer(patientProvisoire);

      final intervention = await _interventionService.creer(
        patientId: patient.id,
        regionId: _regionId!,
        lieuPriseEnCharge:
            _lieuController.text.trim().isEmpty ? null : _lieuController.text.trim(),
        motif: _motifController.text.trim().isEmpty ? null : _motifController.text.trim(),
        niveauUrgence: _niveauUrgence,
      );

      if (!mounted) return;

      await Navigator.of(context).pushReplacement<void, void>(
        MaterialPageRoute(
          builder: (_) => InterventionDetailScreen(interventionId: intervention.id),
        ),
      );
    } on ApiException catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
      }
    } finally {
      if (mounted) setState(() => _enregistrement = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Nouvelle intervention')),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            Text('Intervention', style: Theme.of(context).textTheme.titleMedium),
            const SizedBox(height: 12),
            FutureBuilder<List<Region>>(
              future: _regionsFuture,
              builder: (context, snapshot) {
                final regions = snapshot.data ?? [];
                return DropdownButtonFormField<int>(
                  initialValue: _regionId,
                  decoration: const InputDecoration(labelText: 'Région'),
                  items: regions
                      .map((r) => DropdownMenuItem(value: r.id, child: Text(r.nom)))
                      .toList(),
                  onChanged: (v) => setState(() => _regionId = v),
                  validator: (v) => v == null ? 'Région requise' : null,
                );
              },
            ),
            const SizedBox(height: 16),
            TextFormField(
              controller: _lieuController,
              decoration: const InputDecoration(labelText: 'Lieu de prise en charge'),
            ),
            const SizedBox(height: 16),
            TextFormField(
              controller: _motifController,
              decoration: const InputDecoration(labelText: 'Motif'),
            ),
            const SizedBox(height: 16),
            DropdownButtonFormField<String>(
              initialValue: _niveauUrgence,
              decoration: const InputDecoration(labelText: 'Niveau d\'urgence'),
              items: const [
                DropdownMenuItem(value: 'critique', child: Text('Critique')),
                DropdownMenuItem(value: 'urgent', child: Text('Urgent')),
                DropdownMenuItem(value: 'modere', child: Text('Modéré')),
                DropdownMenuItem(value: 'faible', child: Text('Faible')),
              ],
              onChanged: (v) => setState(() => _niveauUrgence = v ?? 'modere'),
            ),
            const SizedBox(height: 28),
            Text('Patient', style: Theme.of(context).textTheme.titleMedium),
            const SizedBox(height: 4),
            const Text(
              'Les informations peuvent être complétées plus tard si elles ne sont pas encore connues.',
              style: TextStyle(color: Colors.black54, fontSize: 13),
            ),
            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(
                  child: TextFormField(
                    controller: _nomController,
                    decoration: const InputDecoration(labelText: 'Nom'),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: TextFormField(
                    controller: _prenomController,
                    decoration: const InputDecoration(labelText: 'Prénom'),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),
            Row(
              children: [
                Expanded(
                  child: DropdownButtonFormField<String>(
                    initialValue: _sexe,
                    decoration: const InputDecoration(labelText: 'Sexe'),
                    items: const [
                      DropdownMenuItem(value: 'M', child: Text('Masculin')),
                      DropdownMenuItem(value: 'F', child: Text('Féminin')),
                      DropdownMenuItem(value: 'inconnu', child: Text('Inconnu')),
                    ],
                    onChanged: (v) => setState(() => _sexe = v ?? 'inconnu'),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: TextFormField(
                    controller: _ageController,
                    decoration: const InputDecoration(labelText: 'Âge approx.'),
                    keyboardType: TextInputType.number,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 28),
            ElevatedButton(
              onPressed: _enregistrement ? null : _creer,
              child: _enregistrement
                  ? const SizedBox(
                      height: 20,
                      width: 20,
                      child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                    )
                  : const Text('Créer l\'intervention'),
            ),
          ],
        ),
      ),
    );
  }
}
