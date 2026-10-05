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
      width: double.infinity,
      decoration: const BoxDecoration(
        color: AppColors.surface, // #FFFDF9
        border: Border(
          top: BorderSide(color: AppColors.cocoa, width: 2.0),
        ),
        boxShadow: [
          BoxShadow(
            color: Color(0x18000000),
            offset: Offset(0, -3),
            blurRadius: 10,
          ),
        ],
      ),
      child: SafeArea(
        top: false,
        bottom: true,
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 6),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceAround,
            children: children,
          ),
        ),
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

    return Expanded(
      child: GestureDetector(
        onTap: () => onTabSelected(index),
        behavior: HitTestBehavior.opaque,
        child: Column(
          mainAxisSize: MainAxisSize.min,
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            AnimatedContainer(
              duration: const Duration(milliseconds: 150),
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
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
              child: Stack(
                clipBehavior: Clip.none,
                children: [
                  LucideIcon(
                    lucideSvg,
                    size: 20,
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
            ),
            const SizedBox(height: 3),
            Text(
              label,
              style: GoogleFonts.quicksand(
                fontSize: 10,
                fontWeight: isActive ? FontWeight.w800 : FontWeight.w600,
                color: itemColor,
              ),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
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
    return Expanded(
      child: GestureDetector(
        onTap: onTap,
        behavior: HitTestBehavior.opaque,
        child: Center(
          child: Container(
            width: 38,
            height: 38,
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
              size: 19,
              color: Colors.white,
            ),
          ),
        ),
      ),
    );
  }

  // Matches includes/footer.php:75-96 & 98-113 (Gardener mobile dock)
  Widget _buildGardenerDock() {
    return _buildDockContainer([
      // 0: Home dashboard
      _buildNavItem(index: 0, lucideSvg: LucideSVGs.house, label: 'Home'),
      // 1: gardener/dashboard.php -> data-lucide="trees"
      _buildNavItem(index: 1, lucideSvg: LucideSVGs.trees, label: 'Gardens'),
      // 2: gardener/requests.php -> data-lucide="send"
      _buildNavItem(index: 2, lucideSvg: LucideSVGs.send, label: 'Requests'),
      // 3: gardener/schedules.php -> data-lucide="calendar-check"
      _buildNavItem(index: 3, lucideSvg: LucideSVGs.calendarCheck, label: 'Schedules'),
      // 4: gardener/harvests.php -> data-lucide="shopping-bag"
      _buildNavItem(index: 4, lucideSvg: LucideSVGs.shoppingBag, label: 'Harvests'),
      // 5: footer.php:98-113 -> data-lucide="bell"
      _buildNavItem(index: 5, lucideSvg: LucideSVGs.bell, label: 'Alerts', hasBadge: true),
    ]);
  }

  // Matches includes/footer.php:48-74 & 98-113 (Landowner mobile dock)
  Widget _buildLandownerDock() {
    return _buildDockContainer([
      // 0: Home dashboard
      _buildNavItem(index: 0, lucideSvg: LucideSVGs.house, label: 'Home'),
      // 1: landowner/dashboard.php -> data-lucide="map-pin"
      _buildNavItem(index: 1, lucideSvg: LucideSVGs.mapPin, label: 'Gardens'),
      // 2: landowner/lands.php -> data-lucide="trees"
      _buildNavItem(index: 2, lucideSvg: LucideSVGs.trees, label: 'My Lands'),
      // Action Trigger -> landowner/register.php -> data-lucide="plus"
      if (onCenterAction != null)
        _buildCenterActionButton(
          lucideSvg: LucideSVGs.plus,
          onTap: onCenterAction!,
          tooltip: 'Register Land',
        ),
      // 3: landowner/requests.php -> data-lucide="file-text"
      _buildNavItem(index: 3, lucideSvg: LucideSVGs.fileText, label: 'Requests'),
      // 4: landowner/schedules.php -> data-lucide="calendar"
      _buildNavItem(index: 4, lucideSvg: LucideSVGs.calendar, label: 'Schedules'),
      // 5: footer.php:98-113 -> data-lucide="bell"
      _buildNavItem(index: 5, lucideSvg: LucideSVGs.bell, label: 'Alerts', hasBadge: true),
    ]);
  }
}
