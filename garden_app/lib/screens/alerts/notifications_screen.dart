import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../services/auth_state.dart';
import '../../theme/app_theme.dart';
import '../profile/profile_screen.dart';

class NotificationsScreen extends StatefulWidget {
  final VoidCallback? onNavigateToRequests;
  final VoidCallback? onNavigateToSchedules;
  final VoidCallback? onNavigateToLands;

  const NotificationsScreen({
    super.key,
    this.onNavigateToRequests,
    this.onNavigateToSchedules,
    this.onNavigateToLands,
  });

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  bool _allRead = false;

  final List<Map<String, dynamic>> _alerts = [
    {
      'id': 1,
      'title': 'New Plot Application',
      'body': 'Mary Gardener requested Plot #2 at Sunnyvale Garden.',
      'time': '10 mins ago',
      'icon': Icons.assignment_turned_in_rounded,
      'color': AppColors.primary,
      'type': 'requests',
      'unread': true,
    },
    {
      'id': 2,
      'title': 'Gardening Schedule Reminder',
      'body': 'Soil preparation and weeding routine set for Saturday 8:00 AM.',
      'time': '2 hours ago',
      'icon': Icons.calendar_month_rounded,
      'color': Color(0xFF0284C7),
      'type': 'schedules',
      'unread': true,
    },
    {
      'id': 3,
      'title': 'Land Registration Verified',
      'body': 'Riverdale Acres registration approved and listed on public map.',
      'time': '1 day ago',
      'icon': Icons.verified_rounded,
      'color': Color(0xFFD97706),
      'type': 'lands',
      'unread': true,
    },
  ];

  @override
  Widget build(BuildContext context) {
    final user = AuthState().currentUser;
    final isLandowner = user?.isLandowner ?? false;

    return Scaffold(
      backgroundColor: AppColors.canvas,
      appBar: AppBar(
        title: Text('Alerts & Notifications', style: GoogleFonts.quicksand(fontWeight: FontWeight.w800)),
        actions: [
          if (!_allRead)
            TextButton(
              onPressed: () => setState(() => _allRead = true),
              child: Text(
                'Mark all read',
                style: GoogleFonts.quicksand(
                  fontSize: 12,
                  fontWeight: FontWeight.w700,
                  color: AppColors.primary,
                ),
              ),
            ),
        ],
      ),
      body: ListView(
        padding: const EdgeInsets.fromLTRB(16, 12, 16, 100),
        children: [
          // Header summary badge matching footer.php
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
            decoration: BoxDecoration(
              color: AppColors.surface,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(color: AppColors.cocoa, width: 1.5),
              boxShadow: AppColors.tactileShadow,
            ),
            child: Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(6),
                  decoration: BoxDecoration(
                    color: AppColors.matchaLight,
                    shape: BoxShape.circle,
                    border: Border.all(color: AppColors.cocoa, width: 1.5),
                  ),
                  child: const Icon(Icons.notifications_active_rounded, color: AppColors.matchaDarkText, size: 18),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Text(
                    _allRead ? 'All caught up! No unread notifications.' : 'You have 3 unread activity alerts',
                    style: GoogleFonts.nunito(
                      fontSize: 13,
                      fontWeight: FontWeight.w700,
                      color: AppColors.textMain,
                    ),
                  ),
                ),
                if (!_allRead)
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                    decoration: BoxDecoration(
                      color: AppColors.primary,
                      borderRadius: BorderRadius.circular(99),
                    ),
                    child: Text(
                      '3 new',
                      style: GoogleFonts.quicksand(
                        fontSize: 10,
                        fontWeight: FontWeight.w800,
                        color: Colors.white,
                      ),
                    ),
                  ),
              ],
            ),
          ),
          const SizedBox(height: 14),

          // Notifications List matching footer.php:133-195
          ..._alerts.map((alert) {
            final isUnread = !_allRead && (alert['unread'] as bool);

            return Container(
              margin: const EdgeInsets.only(bottom: 12),
              decoration: BoxDecoration(
                color: AppColors.surface,
                borderRadius: BorderRadius.circular(20),
                border: Border.all(
                  color: isUnread ? AppColors.cocoa : AppColors.borderSubtle,
                  width: isUnread ? 2 : 1.5,
                ),
                boxShadow: isUnread ? AppColors.tactileShadow : null,
              ),
              child: InkWell(
                borderRadius: BorderRadius.circular(18),
                onTap: () {
                  if (alert['type'] == 'requests' && widget.onNavigateToRequests != null) {
                    widget.onNavigateToRequests!();
                  } else if (alert['type'] == 'schedules' && widget.onNavigateToSchedules != null) {
                    widget.onNavigateToSchedules!();
                  } else if (alert['type'] == 'lands' && widget.onNavigateToLands != null) {
                    widget.onNavigateToLands!();
                  }
                },
                child: Padding(
                  padding: const EdgeInsets.all(14),
                  child: Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Container(
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          color: (alert['color'] as Color).withValues(alpha: 0.15),
                          borderRadius: BorderRadius.circular(14),
                          border: Border.all(color: AppColors.cocoa, width: 1.5),
                        ),
                        child: Icon(alert['icon'] as IconData, size: 20, color: alert['color'] as Color),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Expanded(
                                  child: Text(
                                    alert['title'] as String,
                                    style: GoogleFonts.quicksand(
                                      fontSize: 14,
                                      fontWeight: FontWeight.w800,
                                      color: AppColors.textMain,
                                    ),
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                ),
                                if (isUnread)
                                  Container(
                                    width: 8,
                                    height: 8,
                                    decoration: const BoxDecoration(
                                      color: AppColors.primary,
                                      shape: BoxShape.circle,
                                    ),
                                  ),
                              ],
                            ),
                            const SizedBox(height: 3),
                            Text(
                              alert['body'] as String,
                              style: GoogleFonts.nunito(
                                fontSize: 12,
                                fontWeight: FontWeight.w600,
                                color: AppColors.textSub,
                                height: 1.35,
                              ),
                            ),
                            const SizedBox(height: 6),
                            Text(
                              alert['time'] as String,
                              style: GoogleFonts.nunito(
                                fontSize: 11,
                                fontWeight: FontWeight.w600,
                                color: AppColors.textMuted,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            );
          }),

          const SizedBox(height: 10),

          // Account & Profile Shortcut
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
              color: AppColors.slotBg,
              borderRadius: BorderRadius.circular(20),
              border: Border.all(color: AppColors.cocoa, width: 1.5),
            ),
            child: Row(
              children: [
                Container(
                  width: 38,
                  height: 38,
                  decoration: BoxDecoration(
                    color: AppColors.surface,
                    shape: BoxShape.circle,
                    border: Border.all(color: AppColors.cocoa, width: 1.5),
                  ),
                  alignment: Alignment.center,
                  child: Text(
                    user != null && user.name.isNotEmpty ? user.name[0].toUpperCase() : 'U',
                    style: GoogleFonts.quicksand(fontWeight: FontWeight.w900, fontSize: 16),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        user?.name ?? 'User Profile',
                        style: GoogleFonts.quicksand(fontSize: 14, fontWeight: FontWeight.w800),
                      ),
                      Text(
                        'Active role: ${(isLandowner ? 'Landowner' : 'Gardener')}',
                        style: GoogleFonts.nunito(fontSize: 12, color: AppColors.textSub, fontWeight: FontWeight.w600),
                      ),
                    ],
                  ),
                ),
                ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.surface,
                    foregroundColor: AppColors.textMain,
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                  ),
                  onPressed: () {
                    Navigator.of(context).push(
                      MaterialPageRoute(builder: (_) => const ProfileScreen()),
                    );
                  },
                  child: Text('Profile', style: GoogleFonts.quicksand(fontWeight: FontWeight.w800, fontSize: 12)),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
