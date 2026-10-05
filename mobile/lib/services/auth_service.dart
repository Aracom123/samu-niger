import '../models/user.dart';
import 'api_client.dart';

class AuthResult {
  final AppUser user;
  final String token;

  AuthResult(this.user, this.token);
}

class AuthService {
  AuthService(this._client);

  final ApiClient _client;

  Future<AuthResult> login(String email, String password) async {
    final data = await _client.post('/auth/login', {
      'email': email,
      'password': password,
    }) as Map<String, dynamic>;

    final roles = (data['roles'] as List?)?.map((r) => r.toString()).toList() ?? [];
    final user = AppUser.fromJson(data['user'] as Map<String, dynamic>, roles: roles);
    final token = data['token'] as String;

    await _client.setToken(token);

    return AuthResult(user, token);
  }

  Future<AppUser?> me() async {
    final token = await _client.token;
    if (token == null) return null;

    try {
      final data = await _client.get('/auth/me') as Map<String, dynamic>;
      final roles = (data['roles'] as List?)?.map((r) => r.toString()).toList() ?? [];
      return AppUser.fromJson(data['user'] as Map<String, dynamic>, roles: roles);
    } on ApiException {
      await _client.setToken(null);
      return null;
    }
  }

  Future<void> logout() async {
    try {
      await _client.post('/auth/logout', {});
    } on ApiException {
      // Le token est peut-être déjà invalide côté serveur : on déconnecte quand même localement.
    }
    await _client.setToken(null);
  }
}
