import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:provider/provider.dart';

import 'package:samu_mobile/providers/auth_provider.dart';
import 'package:samu_mobile/screens/login_screen.dart';
import 'package:samu_mobile/services/api_client.dart';
import 'package:samu_mobile/theme/app_theme.dart';

void main() {
  testWidgets('La page de connexion affiche le formulaire attendu', (tester) async {
    final client = ApiClient();

    await tester.pumpWidget(
      MultiProvider(
        providers: [
          Provider<ApiClient>.value(value: client),
          ChangeNotifierProvider<AuthProvider>(create: (_) => AuthProvider(client)),
        ],
        child: MaterialApp(theme: AppTheme.light, home: const LoginScreen()),
      ),
    );

    expect(find.text('SAMU Niger'), findsOneWidget);
    expect(find.text('Adresse e-mail'), findsOneWidget);
    expect(find.text('Mot de passe'), findsOneWidget);
    expect(find.text('Connexion'), findsOneWidget);
  });
}
