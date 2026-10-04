import 'package:flutter/material.dart';
import 'screens/auth/login_screen.dart';
import 'screens/main_shell_screen.dart';
import 'services/api_service.dart';
import 'services/auth_state.dart';
import 'theme/app_theme.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // Initialize service settings and authentication cache
  await ApiService().init();
  await AuthState().init();

  runApp(const GardenFlutterApp());
}

class GardenFlutterApp extends StatelessWidget {
  const GardenFlutterApp({super.key});

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: AuthState(),
      builder: (context, _) {
        final auth = AuthState();

        return MaterialApp(
          title: 'Idle Land Gardening',
          debugShowCheckedModeBanner: false,
          theme: AppTheme.lightTheme,
          home: auth.isAuthenticated ? const MainShellScreen() : const LoginScreen(),
        );
      },
    );
  }
}
