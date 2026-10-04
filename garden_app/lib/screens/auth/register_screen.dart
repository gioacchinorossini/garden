import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../services/auth_state.dart';
import '../../theme/app_theme.dart';

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  final _phoneController = TextEditingController();
  final _passwordController = TextEditingController();
  String _selectedRole = 'gardener';
  String? _errorMessage;

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _phoneController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  Future<void> _handleRegister() async {
    setState(() => _errorMessage = null);
    final name = _nameController.text.trim();
    final email = _emailController.text.trim();
    final phone = _phoneController.text.trim();
    final password = _passwordController.text.trim();

    if (name.isEmpty || email.isEmpty || password.isEmpty) {
      setState(() => _errorMessage = 'Please complete all required fields.');
      return;
    }

    final success = await AuthState().register(
      name: name,
      email: email,
      password: password,
      role: _selectedRole,
      phone: phone,
    );

    if (success && mounted) {
      Navigator.of(context).pop();
    } else if (mounted) {
      setState(() => _errorMessage = 'Registration could not be completed. Please try again.');
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = AuthState();

    return Scaffold(
      backgroundColor: AppColors.canvas,
      appBar: AppBar(
        title: Text('Create Account', style: GoogleFonts.quicksand(fontWeight: FontWeight.w800)),
      ),
      body: Center(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 20),
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 440),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Text(
                  'Join Idle Land',
                  style: GoogleFonts.quicksand(
                    fontSize: 26,
                    fontWeight: FontWeight.w900,
                    color: AppColors.textMain,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  'Lease idle lots or cultivate organic community produce.',
                  style: GoogleFonts.nunito(fontSize: 13, color: AppColors.textSub, fontWeight: FontWeight.w600),
                ),
                const SizedBox(height: 20),

                Container(
                  padding: const EdgeInsets.all(22),
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
                          child: Text(_errorMessage!, style: GoogleFonts.nunito(color: AppColors.coral, fontSize: 13, fontWeight: FontWeight.w700)),
                        ),
                        const SizedBox(height: 16),
                      ],

                      Text('ACCOUNT ROLE', style: GoogleFonts.quicksand(fontSize: 11, fontWeight: FontWeight.w800, color: AppColors.textMuted, letterSpacing: 0.5)),
                      const SizedBox(height: 8),
                      Row(
                        children: [
                          Expanded(
                            child: _buildRoleSelector('gardener', 'Gardener', Icons.yard),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: _buildRoleSelector('landowner', 'Landowner', Icons.landscape),
                          ),
                        ],
                      ),
                      const SizedBox(height: 16),

                      Text('FULL NAME', style: GoogleFonts.quicksand(fontSize: 11, fontWeight: FontWeight.w800, color: AppColors.textMuted, letterSpacing: 0.5)),
                      const SizedBox(height: 6),
                      TextField(
                        controller: _nameController,
                        decoration: const InputDecoration(
                          hintText: 'e.g. Maria Santos',
                          prefixIcon: Icon(Icons.person_outline, size: 20, color: AppColors.cocoa),
                        ),
                      ),
                      const SizedBox(height: 16),

                      Text('EMAIL ADDRESS', style: GoogleFonts.quicksand(fontSize: 11, fontWeight: FontWeight.w800, color: AppColors.textMuted, letterSpacing: 0.5)),
                      const SizedBox(height: 6),
                      TextField(
                        controller: _emailController,
                        keyboardType: TextInputType.emailAddress,
                        decoration: const InputDecoration(
                          hintText: 'name@example.com',
                          prefixIcon: Icon(Icons.mail_outline, size: 20, color: AppColors.cocoa),
                        ),
                      ),
                      const SizedBox(height: 16),

                      Text('PHONE NUMBER', style: GoogleFonts.quicksand(fontSize: 11, fontWeight: FontWeight.w800, color: AppColors.textMuted, letterSpacing: 0.5)),
                      const SizedBox(height: 6),
                      TextField(
                        controller: _phoneController,
                        keyboardType: TextInputType.phone,
                        decoration: const InputDecoration(
                          hintText: '0917-000-0000',
                          prefixIcon: Icon(Icons.phone_outlined, size: 20, color: AppColors.cocoa),
                        ),
                      ),
                      const SizedBox(height: 16),

                      Text('PASSWORD', style: GoogleFonts.quicksand(fontSize: 11, fontWeight: FontWeight.w800, color: AppColors.textMuted, letterSpacing: 0.5)),
                      const SizedBox(height: 6),
                      TextField(
                        controller: _passwordController,
                        obscureText: true,
                        decoration: const InputDecoration(
                          hintText: 'Create a password',
                          prefixIcon: Icon(Icons.lock_outline, size: 20, color: AppColors.cocoa),
                        ),
                      ),
                      const SizedBox(height: 24),

                      ElevatedButton(
                        style: ElevatedButton.styleFrom(
                          minimumSize: const Size.fromHeight(48),
                        ),
                        onPressed: auth.isLoading ? null : _handleRegister,
                        child: auth.isLoading
                            ? const SizedBox(
                                height: 20,
                                width: 20,
                                child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                              )
                            : Text('Complete Registration', style: GoogleFonts.quicksand(fontSize: 15, fontWeight: FontWeight.w800)),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildRoleSelector(String role, String label, IconData icon) {
    final isSelected = _selectedRole == role;

    return GestureDetector(
      onTap: () => setState(() => _selectedRole = role),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 150),
        padding: const EdgeInsets.symmetric(vertical: 10),
        decoration: BoxDecoration(
          color: isSelected ? AppColors.matchaLight : AppColors.surface,
          borderRadius: BorderRadius.circular(99),
          border: Border.all(
            color: isSelected ? AppColors.cocoa : AppColors.borderSubtle,
            width: 2,
          ),
          boxShadow: isSelected
              ? const [
                  BoxShadow(
                    color: AppColors.cocoa,
                    offset: Offset(0, 2),
                    blurRadius: 0,
                  ),
                ]
              : null,
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(icon, size: 18, color: isSelected ? AppColors.matchaDarkText : AppColors.textSub),
            const SizedBox(width: 6),
            Text(
              label,
              style: GoogleFonts.quicksand(
                fontSize: 13,
                fontWeight: isSelected ? FontWeight.w800 : FontWeight.w600,
                color: isSelected ? AppColors.matchaDarkText : AppColors.textSub,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
