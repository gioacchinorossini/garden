import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../services/auth_state.dart';
import '../../theme/app_theme.dart';
import '../../widgets/server_config_dialog.dart';
import 'register_screen.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _emailController = TextEditingController(text: 'gardener@garden.com');
  final _passwordController = TextEditingController(text: 'password123');
  bool _obscurePassword = true;
  String? _errorMessage;

  @override
  void dispose() {
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _handleLogin() async {
    setState(() => _errorMessage = null);
    final email = _emailController.text.trim();
    final password = _passwordController.text.trim();

    if (email.isEmpty || password.isEmpty) {
      setState(() => _errorMessage = 'Please enter your email and password');
      return;
    }

    final success = await AuthState().login(email, password);
    if (!success && mounted) {
      setState(() => _errorMessage = 'Invalid email or password. Please try again.');
    }
  }

  void _quickFill(String email) {
    setState(() {
      _emailController.text = email;
      _passwordController.text = 'password123';
      _errorMessage = null;
    });
    _handleLogin();
  }

  @override
  Widget build(BuildContext context) {
    final auth = AuthState();

    return Scaffold(
      backgroundColor: AppColors.canvas,
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        actions: [
          IconButton(
            icon: const Icon(Icons.dns_rounded, color: AppColors.textSub),
            tooltip: 'Server Settings',
            onPressed: () {
              showDialog(
                context: context,
                builder: (context) => const ServerConfigDialog(),
              );
            },
          ),
        ],
      ),
      body: Center(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 16),
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 420),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                // App Logo
                Center(
                  child: Container(
                    width: 76,
                    height: 76,
                    decoration: BoxDecoration(
                      color: AppColors.surface,
                      shape: BoxShape.circle,
                      border: Border.all(color: AppColors.cocoa, width: 2.5),
                      boxShadow: AppColors.tactileShadowLg,
                    ),
                    clipBehavior: Clip.antiAlias,
                    child: Image.asset(
                      'assets/images/logo.jpeg',
                      fit: BoxFit.cover,
                      errorBuilder: (context, error, stackTrace) => const Icon(
                        Icons.eco_rounded,
                        color: AppColors.primary,
                        size: 40,
                      ),
                    ),
                  ),
                ),
                const SizedBox(height: 16),

                // Title
                Center(
                  child: RichText(
                    text: TextSpan(
                      children: [
                        TextSpan(
                          text: 'Idle',
                          style: GoogleFonts.quicksand(
                            color: AppColors.primary,
                            fontSize: 28,
                            fontWeight: FontWeight.w900,
                          ),
                        ),
                        TextSpan(
                          text: 'Land',
                          style: GoogleFonts.quicksand(
                            color: AppColors.textMain,
                            fontSize: 28,
                            fontWeight: FontWeight.w900,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
                const SizedBox(height: 4),
                Center(
                  child: Text(
                    'Community Gardening System',
                    style: GoogleFonts.nunito(
                      fontSize: 14,
                      fontWeight: FontWeight.w600,
                      color: AppColors.textSub,
                    ),
                  ),
                ),
                const SizedBox(height: 24),

                // Form Container
                Container(
                  padding: const EdgeInsets.all(24),
                  decoration: BoxDecoration(
                    color: AppColors.surface,
                    borderRadius: BorderRadius.circular(26),
                    border: Border.all(color: AppColors.cocoa, width: 2.5),
                    boxShadow: AppColors.tactileShadowLg,
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      if (_errorMessage != null) ...[
                        Container(
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: const Color(0xFFFEE2E2),
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(color: AppColors.coral, width: 1.5),
                          ),
                          child: Text(
                            _errorMessage!,
                            style: GoogleFonts.nunito(color: AppColors.coral, fontSize: 13, fontWeight: FontWeight.w700),
                          ),
                        ),
                        const SizedBox(height: 16),
                      ],

                      Text(
                        'EMAIL ADDRESS',
                        style: GoogleFonts.quicksand(fontSize: 11, fontWeight: FontWeight.w800, color: AppColors.textMuted, letterSpacing: 0.5),
                      ),
                      const SizedBox(height: 6),
                      TextField(
                        controller: _emailController,
                        keyboardType: TextInputType.emailAddress,
                        decoration: const InputDecoration(
                          hintText: 'name@example.com',
                          prefixIcon: Icon(Icons.mail_outline_rounded, size: 20, color: AppColors.cocoa),
                        ),
                      ),
                      const SizedBox(height: 16),

                      Text(
                        'PASSWORD',
                        style: GoogleFonts.quicksand(fontSize: 11, fontWeight: FontWeight.w800, color: AppColors.textMuted, letterSpacing: 0.5),
                      ),
                      const SizedBox(height: 6),
                      TextField(
                        controller: _passwordController,
                        obscureText: _obscurePassword,
                        decoration: InputDecoration(
                          hintText: 'Enter your password',
                          prefixIcon: const Icon(Icons.lock_outline_rounded, size: 20, color: AppColors.cocoa),
                          suffixIcon: IconButton(
                            icon: Icon(
                              _obscurePassword ? Icons.visibility_outlined : Icons.visibility_off_outlined,
                              size: 20,
                              color: AppColors.textMuted,
                            ),
                            onPressed: () => setState(() => _obscurePassword = !_obscurePassword),
                          ),
                        ),
                      ),
                      const SizedBox(height: 22),

                      ElevatedButton(
                        style: ElevatedButton.styleFrom(
                          minimumSize: const Size.fromHeight(48),
                          backgroundColor: AppColors.primary,
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(99),
                            side: const BorderSide(color: AppColors.cocoaButtonBorder, width: 2),
                          ),
                        ),
                        onPressed: auth.isLoading ? null : _handleLogin,
                        child: auth.isLoading
                            ? const SizedBox(
                                height: 20,
                                width: 20,
                                child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                              )
                            : Text(
                                'Sign In to Garden',
                                style: GoogleFonts.quicksand(fontSize: 15, fontWeight: FontWeight.w800),
                              ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 20),

                // Quick Demo Accounts Bar
                Text(
                  'QUICK DEMO ROLES',
                  textAlign: TextAlign.center,
                  style: GoogleFonts.quicksand(
                    fontSize: 11,
                    fontWeight: FontWeight.w800,
                    color: AppColors.textMuted,
                    letterSpacing: 0.5,
                  ),
                ),
                const SizedBox(height: 10),
                Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  alignment: WrapAlignment.center,
                  children: [
                    _buildDemoChip('Mary Gardener', 'gardener@garden.com', Icons.yard, AppColors.matchaLight),
                    _buildDemoChip('John Landowner', 'landowner@garden.com', Icons.terrain, AppColors.slotBg),
                  ],
                ),
                const SizedBox(height: 20),

                // Register Link
                Wrap(
                  alignment: WrapAlignment.center,
                  crossAxisAlignment: WrapCrossAlignment.center,
                  children: [
                    Text(
                      "Don't have an account? ",
                      style: GoogleFonts.nunito(fontSize: 14, color: AppColors.textSub, fontWeight: FontWeight.w600),
                    ),
                    GestureDetector(
                      onTap: () {
                        Navigator.of(context).push(
                          MaterialPageRoute(builder: (context) => const RegisterScreen()),
                        );
                      },
                      child: Text(
                        'Create an account',
                        style: GoogleFonts.quicksand(
                          fontSize: 14,
                          fontWeight: FontWeight.w800,
                          color: AppColors.primary,
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildDemoChip(String name, String email, IconData icon, Color color) {
    return GestureDetector(
      onTap: () => _quickFill(email),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
        decoration: BoxDecoration(
          color: color,
          borderRadius: BorderRadius.circular(99),
          border: Border.all(color: AppColors.cocoa, width: 1.5),
          boxShadow: const [
            BoxShadow(
              color: AppColors.cocoa,
              offset: Offset(0, 2),
              blurRadius: 0,
            ),
          ],
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, size: 15, color: AppColors.textMain),
            const SizedBox(width: 6),
            Text(
              name,
              style: GoogleFonts.quicksand(
                fontSize: 12,
                fontWeight: FontWeight.w800,
                color: AppColors.textMain,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
