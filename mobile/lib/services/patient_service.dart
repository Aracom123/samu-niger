import '../models/patient.dart';
import 'api_client.dart';

class PatientService {
  PatientService(this._client);

  final ApiClient _client;

  Future<Patient> enregistrer(Patient patient) async {
    final data = await _client.post('/patients', patient.toJson()) as Map<String, dynamic>;
    return Patient.fromJson(data);
  }

  Future<Patient> mettreAJour(int id, Patient patient) async {
    final data = await _client.put('/patients/$id', patient.toJson()) as Map<String, dynamic>;
    return Patient.fromJson(data);
  }
}
