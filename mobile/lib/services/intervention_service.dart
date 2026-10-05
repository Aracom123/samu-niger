import '../models/intervention.dart';
import 'api_client.dart';

class InterventionService {
  InterventionService(this._client);

  final ApiClient _client;

  Future<List<Intervention>> mesInterventions({String? statut}) async {
    final data = await _client.get('/interventions', query: {
      if (statut != null) 'statut': statut,
      'per_page': 50,
    }) as Map<String, dynamic>;

    final items = data['data'] as List? ?? [];
    return items
        .map((json) => Intervention.fromJson(json as Map<String, dynamic>))
        .toList();
  }

  Future<Intervention> creer({
    required int patientId,
    required int regionId,
    String? lieuPriseEnCharge,
    String? motif,
    String niveauUrgence = 'modere',
  }) async {
    final data = await _client.post('/interventions', {
      'patient_id': patientId,
      'region_id': regionId,
      if (lieuPriseEnCharge != null) 'lieu_prise_en_charge': lieuPriseEnCharge,
      if (motif != null) 'motif': motif,
      'niveau_urgence': niveauUrgence,
    }) as Map<String, dynamic>;

    return Intervention.fromJson(data);
  }

  Future<Intervention> detail(int id) async {
    final data = await _client.get('/interventions/$id') as Map<String, dynamic>;
    return Intervention.fromJson(data);
  }

  Future<Intervention> changerStatut(int id, String statut) async {
    final data = await _client.post('/interventions/$id/statut', {'statut': statut})
        as Map<String, dynamic>;
    return Intervention.fromJson(data);
  }
}
