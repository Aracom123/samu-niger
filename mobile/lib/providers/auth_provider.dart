import 'package:flutter/foundation.dart';

import '../models/user.dart';
import '../services/api_client.dart';
import '../services/auth_service.dart';

enum AuthStatus { inconnu, verification, connecte, deconnecte }

class AuthProvider extends ChangeNotifier {
  AuthProvider(this._client) : _authService = AuthService(_client);

  final ApiClient _client;
  final AuthService _authService;

  AuthStatus status = AuthStatus.inconnu;
  AppUser? user;
  String? erreur;
  bool initialise = false;

  ApiClient get client => _client;

  Future<void> verifierSession() async {
    final u = await _authService.me();
    user = u;
    status = u != null ? AuthStatus.connecte : AuthStatus.deconnecte;
    initialise = true;
    notifyListeners();
  }

  Future<bool> connecter(String email, String motDePasse) async {
    erreur = null;
    status = AuthStatus.verification;
    notifyListeners();

    try {
      final result = await _authService.login(email, motDePasse);
      user = result.user;
      status = AuthStatus.connecte;
      notifyListeners();
      return true;
    } on ApiException catch (e) {
      erreur = e.message;
      status = AuthStatus.deconnecte;
      notifyListeners();
      return false;
    }
  }

  Future<void> deconnecter() async {
    await _authService.logout();
    user = null;
    status = AuthStatus.deconnecte;
    notifyListeners();
  }
}
