import 'dart:convert';

import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:http/http.dart' as http;

import '../config/api_config.dart';

class ApiException implements Exception {
  final String message;
  final int? statusCode;
  final Map<String, dynamic>? errors;

  ApiException(this.message, {this.statusCode, this.errors});

  @override
  String toString() => message;
}

/// Client HTTP central : ajoute automatiquement le token d'authentification
/// et normalise les erreurs de l'API Laravel (Sanctum).
class ApiClient {
  static const _storage = FlutterSecureStorage();
  static const _tokenKey = 'samu_auth_token';

  String? _cachedToken;

  Future<String?> get token async {
    _cachedToken ??= await _storage.read(key: _tokenKey);
    return _cachedToken;
  }

  Future<void> setToken(String? token) async {
    _cachedToken = token;
    if (token == null) {
      await _storage.delete(key: _tokenKey);
    } else {
      await _storage.write(key: _tokenKey, value: token);
    }
  }

  Future<Map<String, String>> _headers() async {
    final t = await token;
    return {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      if (t != null) 'Authorization': 'Bearer $t',
    };
  }

  Uri _uri(String path, [Map<String, dynamic>? query]) {
    final cleaned = <String, String>{};
    query?.forEach((k, v) {
      if (v != null) cleaned[k] = v.toString();
    });
    return Uri.parse('${ApiConfig.baseUrl}$path').replace(
      queryParameters: cleaned.isNotEmpty ? cleaned : null,
    );
  }

  Future<dynamic> get(String path, {Map<String, dynamic>? query}) async {
    final response = await http.get(_uri(path, query), headers: await _headers());
    return _handle(response);
  }

  Future<dynamic> post(String path, Map<String, dynamic> body) async {
    final response = await http.post(
      _uri(path),
      headers: await _headers(),
      body: jsonEncode(body),
    );
    return _handle(response);
  }

  Future<dynamic> put(String path, Map<String, dynamic> body) async {
    final response = await http.put(
      _uri(path),
      headers: await _headers(),
      body: jsonEncode(body),
    );
    return _handle(response);
  }

  dynamic _handle(http.Response response) {
    final body = response.body.isNotEmpty ? jsonDecode(response.body) : null;

    if (response.statusCode >= 200 && response.statusCode < 300) {
      return body;
    }

    if (response.statusCode == 401) {
      throw ApiException('Session expirée, veuillez vous reconnecter.', statusCode: 401);
    }

    if (response.statusCode == 403) {
      throw ApiException("Vous n'avez pas accès à cette action.", statusCode: 403);
    }

    if (response.statusCode == 422 && body is Map<String, dynamic>) {
      final errors = body['errors'] as Map<String, dynamic>?;
      final premiereErreur = errors != null && errors.isNotEmpty
          ? (errors.values.first as List).first as String
          : (body['message'] as String? ?? 'Requête invalide.');
      throw ApiException(premiereErreur, statusCode: 422, errors: errors);
    }

    final message = (body is Map<String, dynamic> ? body['message'] as String? : null) ??
        'Une erreur est survenue (${response.statusCode}).';
    throw ApiException(message, statusCode: response.statusCode);
  }
}
