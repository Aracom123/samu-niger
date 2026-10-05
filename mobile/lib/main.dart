import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:provider/provider.dart';

import 'providers/auth_provider.dart';
import 'screens/interventions_screen.dart';
import 'screens/login_screen.dart';
import 'services/api_client.dart';
import 'theme/app_theme.dart';

void main() {
  runApp(const SamuMobileApp());
}

class SamuMobileApp extends StatelessWidget {
  const SamuMobileApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        Provider<ApiClient>(create: (_) => ApiClient()),
        ChangeNotifierProvider<AuthProvider>(
          create: (context) => AuthProvider(context.read<ApiClient>())..verifierSession(),
        ),
      ],
      child: MaterialApp(
        title: 'SAMU Niger',
        debugShowCheckedModeBanner: false,
        theme: AppTheme.light,
        locale: const Locale('fr'),
        supportedLocales: const [Locale('fr')],
        localizationsDelegates: const [
          GlobalMaterialLocalizations.delegate,
          GlobalWidgetsLocalizations.delegate,
          GlobalCupertinoLocalizations.delegate,
        ],
        home: const _AuthGate(),
      ),
    );
  }
}

class _AuthGate extends StatelessWidget {
  const _AuthGate();

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthProvider>();

    if (!auth.initialise) {
      return const Scaffold(body: Center(child: CircularProgressIndicator()));
    }

    return auth.status == AuthStatus.connecte
        ? const InterventionsScreen()
        : const LoginScreen();
  }
}
