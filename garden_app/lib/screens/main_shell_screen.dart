import 'package:flutter/material.dart';
import '../services/auth_state.dart';
import '../theme/app_theme.dart';
import '../widgets/floating_bottom_nav_dock.dart';
import 'alerts/notifications_screen.dart';
import 'harvests/harvests_screen.dart';
import 'home/home_screen.dart';
import 'lands/lands_list_screen.dart';
import 'lands/my_lands_screen.dart';
import 'lands/register_land_dialog.dart';
import 'profile/profile_screen.dart';
import 'requests/requests_screen.dart';
import 'schedules/schedules_screen.dart';

class MainShellScreen extends StatefulWidget {
  const MainShellScreen({super.key});

  @override
  State<MainShellScreen> createState() => _MainShellScreenState();
}

class _MainShellScreenState extends State<MainShellScreen> {
  int _currentIndex = 0;

  void _openRegisterLandDialog() {
    showDialog(
      context: context,
      builder: (context) => RegisterLandDialog(onLandCreated: () {
        setState(() {});
      }),
    );
  }

  void _openProfileModal() {
    Navigator.of(context).push(
      MaterialPageRoute(builder: (_) => const ProfileScreen()),
    );
  }

  @override
  Widget build(BuildContext context) {
    final auth = AuthState();

    return ListenableBuilder(
      listenable: auth,
      builder: (context, _) {
        final user = auth.currentUser;
        final isLandowner = user?.isLandowner ?? false;

        // Matches web mobile views in includes/footer.php
        final List<Widget> screens = isLandowner
            ? [
                // 0: Home (new home dashboard)
                HomeScreen(
                  onOpenProfile: _openProfileModal,
                  onNavigateTo: (idx) => setState(() => _currentIndex = idx),
                ),
                // 1: Gardens (landowner/dashboard.php)
                LandsListScreen(onOpenProfile: _openProfileModal),
                // 2: My Lands (landowner/lands.php)
                MyLandsScreen(onOpenProfile: _openProfileModal),
                // 3: Requests (landowner/requests.php)
                const RequestsScreen(),
                // 4: Schedules (landowner/schedules.php)
                const SchedulesScreen(),
                // 5: Alerts (footer.php:98-115)
                NotificationsScreen(
                  onNavigateToRequests: () => setState(() => _currentIndex = 3),
                  onNavigateToSchedules: () => setState(() => _currentIndex = 4),
                  onNavigateToLands: () => setState(() => _currentIndex = 2),
                ),
              ]
            : [
                // 0: Home (new home dashboard)
                HomeScreen(
                  onOpenProfile: _openProfileModal,
                  onNavigateTo: (idx) => setState(() => _currentIndex = idx),
                ),
                // 1: Gardens (gardener/dashboard.php)
                LandsListScreen(onOpenProfile: _openProfileModal),
                // 2: Requests (gardener/requests.php)
                const RequestsScreen(),
                // 3: Schedules (gardener/schedules.php)
                const SchedulesScreen(),
                // 4: Harvests (gardener/harvests.php)
                const HarvestsScreen(),
                // 5: Alerts (footer.php:98-115)
                NotificationsScreen(
                  onNavigateToRequests: () => setState(() => _currentIndex = 2),
                  onNavigateToSchedules: () => setState(() => _currentIndex = 3),
                ),
              ];

        final activeIndex = _currentIndex < screens.length ? _currentIndex : 0;

        return Scaffold(
          backgroundColor: AppColors.canvas,
          body: Stack(
            children: [
              // Current Active Screen
              Positioned.fill(
                child: IndexedStack(
                  index: activeIndex,
                  children: screens,
                ),
              ),

              // Full-width Bottom Navigation Bar
              Positioned(
                left: 0,
                right: 0,
                bottom: 0,
                child: FloatingBottomNavDock(
                  currentIndex: activeIndex,
                  onTabSelected: (idx) => setState(() => _currentIndex = idx),
                  onCenterAction: isLandowner ? _openRegisterLandDialog : null,
                ),
              ),
            ],
          ),
        );
      },
    );
  }
}
