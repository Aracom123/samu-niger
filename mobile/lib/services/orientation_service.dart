import '../models/orientation.dart';
import 'api_client.dart';

class OrientationService {
  OrientationService(this._client);

  final ApiClient _client;

  Future<Orientation> orienter({
    required int interventionId,
    required int structureSanitaireId,
  }) async {
    final data = await _client.post('/orientations', {
      'intervention_id': interventionId,
      'structure_sanitaire_id': structureSanitaireId,
    }) as Map<String, dynamic>;

    return Orientation.fromJson(data);
  }
}
