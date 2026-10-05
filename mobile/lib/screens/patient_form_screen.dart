import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../models/patient.dart';
import '../providers/auth_provider.dart';
import '../services/api_client.dart';
import '../services/patient_service.dart';

/// Complète les informations du patient rattaché à une intervention
/// (section 14 : "enregistrement du patient").
class PatientFormScreen extends StatefulWidget {
  const PatientFormScreen({super.key, required this.patient});

  final Patient patient;

  @override
  State<PatientFormScreen> createState() => _PatientFormScreenState();
}

class _PatientFormScreenState extends State<PatientFormScreen> {
  final _formKey = GlobalKey<FormState>();
  late final PatientService _service;

  late final _nomController = TextEditingController(text: widget.patient.nom);
  late final _prenomController = TextEditingController(text: widget.patient.prenom);
  late final _ageController =
      TextEditingController(text: widget.patient.ageApproximatif?.toString() ?? '');
  late final _telephoneController = TextEditingController(text: widget.patient.telephone);
  late final _localisationController =
      TextEditingController(text: widget.patient.localisationPriseEnCharge);
  late final _motifController = TextEditingController(text: widget.patient.motif);
  late final _observationsController = TextEditingController(text: widget.patient.observations);

  late String _sexe = widget.patient.sexe;
  late String _niveauUrgence = widget.patient.niveauUrgence;
  bool _enregistrement = false;

  @override
  void initState() {
    super.initState();
    _service = PatientService(context.read<AuthProvider>().client);
  }

  @override
  void dispose() {
    _nomController.dispose();
    _prenomController.dispose();
    _ageController.dispose();
    _telephoneController.dispose();
    _localisationController.dispose();
    _motifController.dispose();
    _observationsController.dispose();
    super.dispose();
  }

  Future<void> _enregistrer() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _enregistrement = true);

    final patientMisAJour = Patient(
      id: widget.patient.id,
      identifiant: widget.patient.identifiant,
      nom: _nomController.text.trim().isEmpty ? null : _nomController.text.trim(),
      prenom: _prenomController.text.trim().isEmpty ? null : _prenomController.text.trim(),
      sexe: _sexe,
      ageApproximatif: int.tryParse(_ageController.text.trim()),
      telephone: _telephoneController.text.trim().isEmpty ? null : _telephoneController.text.trim(),
      localisationPriseEnCharge: _localisationController.text.trim().isEmpty
          ? null
          : _localisationController.text.trim(),
      motif: _motifController.text.trim().isEmpty ? null : _motifController.text.trim(),
      niveauUrgence: _niveauUrgence,
      observations:
          _observationsController.text.trim().isEmpty ? null : _observationsController.text.trim(),
    );

    try {
      await _service.mettreAJour(widget.patient.id, patientMisAJour);
      if (mounted) Navigator.of(context).pop(true);
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
      appBar: AppBar(title: Text('Patient ${widget.patient.identifiant}')),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
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
            const SizedBox(height: 16),
            TextFormField(
              controller: _telephoneController,
              decoration: const InputDecoration(labelText: 'Téléphone'),
              keyboardType: TextInputType.phone,
            ),
            const SizedBox(height: 16),
            TextFormField(
              controller: _localisationController,
              decoration: const InputDecoration(labelText: 'Lieu de prise en charge'),
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
            const SizedBox(height: 16),
            TextFormField(
              controller: _motifController,
              decoration: const InputDecoration(labelText: 'Motif'),
            ),
            const SizedBox(height: 16),
            TextFormField(
              controller: _observationsController,
              decoration: const InputDecoration(labelText: 'Observations'),
              maxLines: 3,
            ),
            const SizedBox(height: 24),
            ElevatedButton(
              onPressed: _enregistrement ? null : _enregistrer,
              child: _enregistrement
                  ? const SizedBox(
                      height: 20,
                      width: 20,
                      child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                    )
                  : const Text('Enregistrer'),
            ),
          ],
        ),
      ),
    );
  }
}
