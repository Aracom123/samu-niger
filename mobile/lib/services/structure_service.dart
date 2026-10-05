import '../models/region.dart';
import '../models/structure_sanitaire.dart';
import 'api_client.dart';

class StructureService {
  StructureService(this._client);

  final ApiClient _client;

  Future<List<StructureSanitaire>> liste({int? regionId, int? typeStructureId}) async {
    final data = await _client.get('/structures', query: {
      if (regionId != null) 'region_id': regionId,
      if (typeStructureId != null) 'type_structure_id': typeStructureId,
      'per_page': 100,
    }) as Map<String, dynamic>;

    final items = data['data'] as List? ?? [];
    return items
        .map((json) => StructureSanitaire.fromJson(json as Map<String, dynamic>))
        .toList();
  }

  Future<List<Region>> regions() async {
    final data = await _client.get('/regions') as List;
    return data.map((json) => Region.fromJson(json as Map<String, dynamic>)).toList();
  }
}
