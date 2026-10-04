import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../services/auth_state.dart';
import '../../theme/app_theme.dart';
import '../../widgets/server_config_dialog.dart';
import '../../widgets/status_badge.dart';

class ProfileScreen extends StatelessWidget {
  const ProfileScreen({super.key});

  @override
  Widget build(BuildContext context) {
    final auth = AuthState();

    return ListenableBuilder(
      listenable: auth,
      builder: (context, _) {
        final user = auth.currentUser;
        final isLandowner = user?.isLandowner ?? false;

        return Scaffold(
          backgroundColor: AppColors.canvas,
          appBar: AppBar(
            title: Text('Account & Roles', style: GoogleFonts.quicksand(fontWeight: FontWeight.w800)),
          ),
          body: SingleChildScrollView(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 100),
            child: Center(
              child: ConstrainedBox(
                constraints: const BoxConstraints(maxWidth: 500),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    // User Card
                    Container(
                      padding: const EdgeInsets.all(22),
                      decoration: BoxDecoration(
                        color: AppColors.surface,
                        borderRadius: BorderRadius.circular(26),
                        border: Border.all(color: AppColors.cocoa, width: 2.5),
                        boxShadow: AppColors.tactileShadow,
                      ),
                      child: Column(
                        children: [
                          Container(
                            width: 72,
                            height: 72,
                            decoration: BoxDecoration(
                              color: isLandowner ? AppColors.slotBg : AppColors.matchaLight,
                              shape: BoxShape.circle,
                              border: Border.all(color: AppColors.cocoa, width: 2.5),
                              boxShadow: const [
                                BoxShadow(
                                  color: AppColors.cocoa,
                                  offset: Offset(0, 3),
                                  blurRadius: 0,
                                ),
                              ],
                            ),
                            alignment: Alignment.center,
                            child: Text(
                              user != null && user.name.isNotEmpty ? user.name[0].toUpperCase() : 'G',
                              style: GoogleFonts.quicksand(
                                fontSize: 30,
                                fontWeight: FontWeight.w900,
                                color: AppColors.textMain,
                              ),
                            ),
                          ),
                          const SizedBox(height: 12),
                          Text(
                            user?.name ?? 'Community Member',
                            style: GoogleFonts.quicksand(fontSize: 20, fontWeight: FontWeight.w900),
                          ),
                          const SizedBox(height: 2),
                          Text(
                            user?.email ?? '',
                            style: GoogleFonts.nunito(fontSize: 13, color: AppColors.textSub, fontWeight: FontWeight.w600),
                          ),
                          const SizedBox(height: 10),
                          StatusBadge(status: user?.role ?? 'gardener'),
                        ],
                      ),
                    ),
                    const SizedBox(height: 18),

                    // Role Switcher Card
                    Container(
                      padding: const EdgeInsets.all(20),
                      decoration: BoxDecoration(
                        color: AppColors.surface,
                        borderRadius: BorderRadius.circular(24),
                        border: Border.all(color: AppColors.cocoa, width: 2.5),
                        boxShadow: AppColors.tactileShadow,
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              const Icon(Icons.swap_horiz_rounded, size: 20, color: AppColors.primary),
                              const SizedBox(width: 6),
                              Text(
                                'SWITCH ACCOUNT ROLE',
                                style: GoogleFonts.quicksand(
                                  fontSize: 12,
                                  fontWeight: FontWeight.w800,
                                  color: AppColors.textMuted,
                                  letterSpacing: 0.5,
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 6),
                          Text(
                            'Toggle perspective to use the app as a Gardener or Landowner:',
                            style: GoogleFonts.nunito(fontSize: 12, color: AppColors.textSub, fontWeight: FontWeight.w600),
                          ),
                          const SizedBox(height: 14),
                          Row(
                            children: [
                              Expanded(
                                child: _buildRoleButton(
                                  label: 'Gardener',
                                  subtitle: 'Rent plots & log harvests',
                                  icon: Icons.yard_rounded,
                                  isSelected: !isLandowner,
                                  onTap: () {
                                    auth.switchRole('gardener');
                                    ScaffoldMessenger.of(context).showSnackBar(
                                      const SnackBar(
                                        content: Text('Switched to Gardener perspective'),
                                        duration: Duration(seconds: 2),
                                        backgroundColor: AppColors.primary,
                                      ),
                                    );
                                  },
                                ),
                              ),
                              const SizedBox(width: 10),
                              Expanded(
                                child: _buildRoleButton(
                                  label: 'Landowner',
                                  subtitle: 'List lots & manage leases',
                                  icon: Icons.landscape_rounded,
                                  isSelected: isLandowner,
                                  onTap: () {
                                    auth.switchRole('landowner');
                                    ScaffoldMessenger.of(context).showSnackBar(
                                      const SnackBar(
                                        content: Text('Switched to Landowner perspective'),
                                        duration: Duration(seconds: 2),
                                        backgroundColor: AppColors.primary,
                                      ),
                                    );
                                  },
                                ),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 18),

                    // Options List
                    Container(
                      decoration: BoxDecoration(
                        color: AppColors.surface,
                        borderRadius: BorderRadius.circular(24),
                        border: Border.all(color: AppColors.cocoa, width: 2.5),
                        boxShadow: AppColors.tactileShadow,
                      ),
                      child: Column(
                        children: [
                          ListTile(
                            leading: const Icon(Icons.dns_rounded, color: AppColors.primary),
                            title: Text('Server REST API Settings', style: GoogleFonts.quicksand(fontSize: 14, fontWeight: FontWeight.w800)),
                            subtitle: Text('Change backend host or test connection', style: GoogleFonts.nunito(fontSize: 12)),
                            trailing: const Icon(Icons.chevron_right_rounded, color: AppColors.textMuted),
                            onTap: () {
                              showDialog(
                                context: context,
                                builder: (context) => const ServerConfigDialog(),
                              );
                            },
                          ),
                          const Divider(color: AppColors.borderSubtle, height: 1, thickness: 1.5),
                          ListTile(
                            leading: const Icon(Icons.logout_rounded, color: AppColors.coral),
                            title: Text('Sign Out', style: GoogleFonts.quicksand(fontSize: 14, fontWeight: FontWeight.w800, color: AppColors.coral)),
                            trailing: const Icon(Icons.chevron_right_rounded, color: AppColors.coral),
                            onTap: () => auth.logout(),
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
      },
    );
  }

  Widget _buildRoleButton({
    required String label,
    required String subtitle,
    required IconData icon,
    required bool isSelected,
    required VoidCallback onTap,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 150),
        padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 10),
        decoration: BoxDecoration(
          color: isSelected ? AppColors.matchaLight : AppColors.surface,
          borderRadius: BorderRadius.circular(18),
          border: Border.all(
            color: isSelected ? AppColors.cocoaButtonBorder : AppColors.cocoa,
            width: isSelected ? 2.5 : 1.5,
          ),
          boxShadow: isSelected
              ? const [
                  BoxShadow(
                    color: AppColors.cocoaButtonBorder,
                    offset: Offset(0, 3),
                    blurRadius: 0,
                  ),
                ]
              : AppColors.tactileShadow,
        ),
        child: Column(
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(
                  icon,
                  size: 18,
                  color: isSelected ? AppColors.matchaDarkText : AppColors.textSub,
                ),
                const SizedBox(width: 6),
                Text(
                  label,
                  style: GoogleFonts.quicksand(
                    fontSize: 14,
                    fontWeight: FontWeight.w900,
                    color: isSelected ? AppColors.matchaDarkText : AppColors.textMain,
                  ),
                ),
                if (isSelected) ...[
                  const SizedBox(width: 4),
                  const Icon(Icons.check_circle, size: 14, color: AppColors.primary),
                ],
              ],
            ),
            const SizedBox(height: 4),
            Text(
              subtitle,
              textAlign: TextAlign.center,
              style: GoogleFonts.nunito(
                fontSize: 10,
                fontWeight: FontWeight.w600,
                color: isSelected ? AppColors.matchaDarkText.withValues(alpha: 0.8) : AppColors.textMuted,
              ),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ],
        ),
      ),
    );
  }
}
