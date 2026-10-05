/// Adresse de base de l'API Laravel. En développement web (flutter run -d
/// chrome), le backend tourne sur http://127.0.0.1:8099. À adapter selon
/// l'environnement (ex: via --dart-define=API_BASE_URL=... en production).
class ApiConfig {
  ApiConfig._();

  static const baseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'http://127.0.0.1:8099/api',
  );
}
