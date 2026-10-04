import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../services/auth_state.dart';
import '../theme/app_theme.dart';
import 'lucide_icon.dart';

class FloatingBottomNavDock extends StatelessWidget {
  final int currentIndex;
  final ValueChanged<int> onTabSelected;
  final VoidCallback? onCenterAction;

  const FloatingBottomNavDock({
    super.key,
    required this.currentIndex,
    required this.onTabSelected,
    this.onCenterAction,
  });

  @override
  Widget build(BuildContext context) {
    final user = AuthState().currentUser;

    if (user?.isLandowner ?? false) {
      return _buildLandownerDock();
    } else {
      return _buildGardenerDock();
    }
  }

  Widget _buildDockContainer(List<Widget> children) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 5),
      decoration: BoxDecoration(
        color: AppColors.surface, // #FFFDF9 matching .mobile-bottom-nav-dock
        borderRadius: BorderRadius.circular(99),
        border: Border.all(color: AppColors.cocoa, width: 2.5),
        boxShadow: const [
          BoxShadow(
            color: AppColors.cocoa,
            offset: Offset(0, 4),
            blurRadius: 0,
          ),
          BoxShadow(
            color: Color(0x18000000),
            offset: Offset(0, 10),
            blurRadius: 24,
          ),
        ],
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        mainAxisAlignment: MainAxisAlignment.center,
        children: children,
      ),
    );
  }

  Widget _buildNavItem({
    required int index,
    required String lucideSvg,
    required String label,
    bool hasBadge = false,
  }) {
    final isActive = currentIndex == index;
    final itemColor = isActive ? AppColors.matchaDarkText : AppColors.textSub;

    return GestureDetector(
      onTap: () => onTabSelected(index),
      behavior: HitTestBehavior.opaque,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 150),
        padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 4),
        decoration: BoxDecoration(
          color: isActive ? AppColors.matchaLight : Colors.transparent, // #BBD6B8
          borderRadius: BorderRadius.circular(99),
          border: isActive ? Border.all(color: AppColors.cocoa, width: 1.5) : null,
          boxShadow: isActive
              ? const [
                  BoxShadow(
                    color: AppColors.cocoa,
                    offset: Offset(0, 1.5),
                    blurRadius: 0,
                  ),
                ]
              : null,
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Stack(
              clipBehavior: Clip.none,
              children: [
                LucideIcon(
                  lucideSvg,
                  size: 19,
                  color: itemColor,
                ),
                if (hasBadge)
                  Positioned(
                    top: -1,
                    right: -2,
                    child: Container(
                      width: 6.5,
                      height: 6.5,
                      decoration: const BoxDecoration(
                        color: Color(0xFF10B981), // Emerald 500 matching web ping dot
                        shape: BoxShape.circle,
                      ),
                    ),
                  ),
              ],
            ),
            const SizedBox(height: 2),
            Text(
              label,
              style: GoogleFonts.quicksand(
                fontSize: 9.5,
                fontWeight: isActive ? FontWeight.w800 : FontWeight.w600,
                color: itemColor,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildCenterActionButton({
    required String lucideSvg,
    required VoidCallback onTap,
    required String tooltip,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        margin: const EdgeInsets.symmetric(horizontal: 2),
        width: 36,
        height: 36,
        decoration: const BoxDecoration(
          color: AppColors.primary, // #7FA668
          shape: BoxShape.circle,
          border: Border.fromBorderSide(
            BorderSide(color: AppColors.cocoaButtonBorder, width: 2),
          ),
          boxShadow: [
            BoxShadow(
              color: AppColors.cocoaButtonBorder,
              offset: Offset(0, 2.5),
              blurRadius: 0,
            ),
          ],
        ),
        alignment: Alignment.center,
        child: LucideIcon(
          lucideSvg,
          size: 18,
          color: Colors.white,
        ),
      ),
    );
  }

  // Matches includes/footer.php:75-96 & 98-113 (Gardener mobile dock)
  Widget _buildGardenerDock() {
    return _buildDockContainer([
      // gardener/dashboard.php -> data-lucide="trees"
      _buildNavItem(index: 0, lucideSvg: LucideSVGs.trees, label: 'Gardens'),
      // gardener/requests.php -> data-lucide="send"
      _buildNavItem(index: 1, lucideSvg: LucideSVGs.send, label: 'Requests'),
      // gardener/schedules.php -> data-lucide="calendar-check"
      _buildNavItem(index: 2, lucideSvg: LucideSVGs.calendarCheck, label: 'Schedules'),
      // gardener/harvests.php -> data-lucide="shopping-bag"
      _buildNavItem(index: 3, lucideSvg: LucideSVGs.shoppingBag, label: 'Harvests'),
      // footer.php:98-113 -> data-lucide="bell"
      _buildNavItem(index: 4, lucideSvg: LucideSVGs.bell, label: 'Alerts', hasBadge: true),
    ]);
  }

  // Matches includes/footer.php:48-74 & 98-113 (Landowner mobile dock)
  Widget _buildLandownerDock() {
    return _buildDockContainer([
      // landowner/dashboard.php -> data-lucide="map-pin"
      _buildNavItem(index: 0, lucideSvg: LucideSVGs.mapPin, label: 'Gardens'),
      // landowner/lands.php -> data-lucide="trees"
      _buildNavItem(index: 1, lucideSvg: LucideSVGs.trees, label: 'My Lands'),
      // Action Trigger -> landowner/register.php -> data-lucide="plus"
      if (onCenterAction != null)
        _buildCenterActionButton(
          lucideSvg: LucideSVGs.plus,
          onTap: onCenterAction!,
          tooltip: 'Register Land',
        ),
      // landowner/requests.php -> data-lucide="file-text"
      _buildNavItem(index: 2, lucideSvg: LucideSVGs.fileText, label: 'Requests'),
      // landowner/schedules.php -> data-lucide="calendar"
      _buildNavItem(index: 3, lucideSvg: LucideSVGs.calendar, label: 'Schedules'),
      // footer.php:98-113 -> data-lucide="bell"
      _buildNavItem(index: 4, lucideSvg: LucideSVGs.bell, label: 'Alerts', hasBadge: true),
    ]);
  }
}
