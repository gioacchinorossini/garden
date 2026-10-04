import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../services/auth_state.dart';
import '../theme/app_theme.dart';
import 'server_config_dialog.dart';

class FloatingMapHeader extends StatelessWidget {
  final TextEditingController searchController;
  final ValueChanged<String> onSearchChanged;
  final VoidCallback onClearSearch;
  final VoidCallback? onProfileTap;

  const FloatingMapHeader({
    super.key,
    required this.searchController,
    required this.onSearchChanged,
    required this.onClearSearch,
    this.onProfileTap,
  });

  @override
  Widget build(BuildContext context) {
    final auth = AuthState();

    return ListenableBuilder(
      listenable: auth,
      builder: (context, _) {
        final user = auth.currentUser;
        final initial = (user != null && user.name.isNotEmpty) ? user.name[0].toUpperCase() : 'G';

    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.circular(99),
        border: Border.all(color: AppColors.cocoa, width: 2.5),
        boxShadow: AppColors.tactileShadowLg,
      ),
      child: Row(
        children: [
          // IdleLand Branding
          Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Container(
                width: 28,
                height: 28,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  border: Border.all(color: AppColors.cocoa, width: 1.5),
                ),
                clipBehavior: Clip.antiAlias,
                child: Image.asset(
                  'assets/images/logo.jpeg',
                  fit: BoxFit.cover,
                  errorBuilder: (context, error, stackTrace) => const Icon(
                    Icons.eco_rounded,
                    color: AppColors.primary,
                    size: 18,
                  ),
                ),
              ),
              const SizedBox(width: 6),
              RichText(
                text: TextSpan(
                  children: [
                    TextSpan(
                      text: 'Idle',
                      style: GoogleFonts.quicksand(
                        color: AppColors.primary,
                        fontSize: 15,
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                    TextSpan(
                      text: 'Land',
                      style: GoogleFonts.quicksand(
                        color: AppColors.textMain,
                        fontSize: 15,
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),

          // Vertical divider
          Container(
            height: 20,
            width: 1.5,
            color: AppColors.borderSubtle,
            margin: const EdgeInsets.symmetric(horizontal: 10),
          ),

          // Search Icon
          const Icon(Icons.search_rounded, size: 18, color: AppColors.textMuted),
          const SizedBox(width: 6),

          // Search Input
          Expanded(
            child: TextField(
              controller: searchController,
              onChanged: onSearchChanged,
              style: GoogleFonts.nunito(
                fontSize: 13,
                fontWeight: FontWeight.w700,
                color: AppColors.textMain,
              ),
              decoration: InputDecoration(
                isDense: true,
                hintText: 'Search gardens, crops...',
                hintStyle: GoogleFonts.nunito(
                  color: AppColors.textMuted,
                  fontSize: 13,
                  fontWeight: FontWeight.w600,
                ),
                border: InputBorder.none,
                enabledBorder: InputBorder.none,
                focusedBorder: InputBorder.none,
                contentPadding: EdgeInsets.zero,
                fillColor: Colors.transparent,
              ),
            ),
          ),

          if (searchController.text.isNotEmpty)
            GestureDetector(
              onTap: onClearSearch,
              child: const Padding(
                padding: EdgeInsets.symmetric(horizontal: 4),
                child: Icon(Icons.cancel, size: 16, color: AppColors.textMuted),
              ),
            ),

          const SizedBox(width: 6),

          // Floating Profile Button
          GestureDetector(
            onTap: onProfileTap ?? () => _showQuickAccountMenu(context),
            child: Container(
              width: 32,
              height: 32,
              decoration: BoxDecoration(
                color: AppColors.matchaLight,
                shape: BoxShape.circle,
                border: Border.all(color: AppColors.cocoa, width: 2),
                boxShadow: const [
                  BoxShadow(
                    color: AppColors.cocoa,
                    offset: Offset(0, 2),
                    blurRadius: 0,
                  ),
                ],
              ),
              alignment: Alignment.center,
              child: Text(
                initial,
                style: GoogleFonts.quicksand(
                  fontSize: 14,
                  fontWeight: FontWeight.w900,
                  color: AppColors.textMain,
                ),
              ),
            ),
          ),
        ],
      ),
    );
      },
    );
  }

  void _showQuickAccountMenu(BuildContext context) {
    final auth = AuthState();
    final user = auth.currentUser;

    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      builder: (context) {
        return Container(
          margin: const EdgeInsets.all(14),
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            color: AppColors.surface,
            borderRadius: BorderRadius.circular(28),
            border: Border.all(color: AppColors.cocoa, width: 2.5),
            boxShadow: AppColors.tactileShadowLg,
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Row(
                children: [
                  Container(
                    width: 44,
                    height: 44,
                    decoration: BoxDecoration(
                      color: AppColors.matchaLight,
                      shape: BoxShape.circle,
                      border: Border.all(color: AppColors.cocoa, width: 2),
                    ),
                    alignment: Alignment.center,
                    child: Text(
                      user != null && user.name.isNotEmpty ? user.name[0].toUpperCase() : 'G',
                      style: GoogleFonts.quicksand(fontSize: 18, fontWeight: FontWeight.w900, color: AppColors.textMain),
                    ),
                  ),
                  const SizedBox(width: 14),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          user?.name ?? 'User',
                          style: GoogleFonts.quicksand(fontSize: 16, fontWeight: FontWeight.w800, color: AppColors.textMain),
                        ),
                        Text(
                          user?.email ?? '',
                          style: GoogleFonts.nunito(fontSize: 12, color: AppColors.textSub),
                        ),
                      ],
                    ),
                  ),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                    decoration: BoxDecoration(
                      color: AppColors.matchaLight,
                      borderRadius: BorderRadius.circular(99),
                      border: Border.all(color: AppColors.cocoa, width: 1.5),
                    ),
                    child: Text(
                      (user?.role ?? 'gardener').toUpperCase(),
                      style: GoogleFonts.quicksand(fontSize: 11, fontWeight: FontWeight.w800, color: AppColors.matchaDarkText),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              const Divider(color: AppColors.borderSubtle, thickness: 1.5),
              const SizedBox(height: 8),

              Text(
                'Switch Account Role:',
                style: GoogleFonts.quicksand(fontSize: 12, fontWeight: FontWeight.w700, color: AppColors.textSub),
              ),
              const SizedBox(height: 8),
              Row(
                children: [
                  Expanded(
                    child: ElevatedButton(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: (user?.isGardener ?? true) ? AppColors.primary : AppColors.surface,
                        foregroundColor: (user?.isGardener ?? true) ? AppColors.surface : AppColors.textMain,
                        padding: const EdgeInsets.symmetric(vertical: 10),
                      ),
                      onPressed: () {
                        auth.switchRole('gardener');
                        Navigator.pop(context);
                      },
                      child: const Text('Gardener'),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: ElevatedButton(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: (user?.isLandowner ?? false) ? AppColors.primary : AppColors.surface,
                        foregroundColor: (user?.isLandowner ?? false) ? AppColors.surface : AppColors.textMain,
                        padding: const EdgeInsets.symmetric(vertical: 10),
                      ),
                      onPressed: () {
                        auth.switchRole('landowner');
                        Navigator.pop(context);
                      },
                      child: const Text('Landowner'),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 14),

              ListTile(
                dense: true,
                contentPadding: EdgeInsets.zero,
                leading: const Icon(Icons.dns_outlined, color: AppColors.primary, size: 20),
                title: Text('Server API Settings', style: GoogleFonts.quicksand(fontSize: 13, fontWeight: FontWeight.w700)),
                onTap: () {
                  Navigator.pop(context);
                  showDialog(context: context, builder: (_) => const ServerConfigDialog());
                },
              ),
              ListTile(
                dense: true,
                contentPadding: EdgeInsets.zero,
                leading: const Icon(Icons.logout_rounded, color: AppColors.coral, size: 20),
                title: Text('Sign Out', style: GoogleFonts.quicksand(fontSize: 13, fontWeight: FontWeight.w700, color: AppColors.coral)),
                onTap: () {
                  Navigator.pop(context);
                  auth.logout();
                },
              ),
            ],
          ),
        );
      },
    );
  }
}
