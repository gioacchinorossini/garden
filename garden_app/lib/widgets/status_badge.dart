import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../theme/app_theme.dart';

class StatusBadge extends StatelessWidget {
  final String status;
  final double fontSize;

  const StatusBadge({
    super.key,
    required this.status,
    this.fontSize = 11,
  });

  @override
  Widget build(BuildContext context) {
    Color bg;
    Color fg;
    final lower = status.toLowerCase();

    switch (lower) {
      case 'approved':
      case 'available':
      case 'completed':
      case 'active':
        bg = AppColors.matchaLight;
        fg = AppColors.matchaDarkText;
        break;
      case 'pending':
      case 'in_progress':
      case 'scheduled':
        bg = const Color(0xFFFEF3C7);
        fg = const Color(0xFF92400E);
        break;
      case 'rejected':
      case 'occupied':
      case 'cancelled':
      case 'maintenance':
      case 'inactive':
        bg = const Color(0xFFFEE2E2);
        fg = const Color(0xFF991B1B);
        break;
      default:
        bg = AppColors.surfaceHover;
        fg = AppColors.textSub;
    }

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
      decoration: BoxDecoration(
        color: bg,
        borderRadius: BorderRadius.circular(99),
        border: Border.all(color: AppColors.cocoa, width: 1.5),
        boxShadow: const [
          BoxShadow(
            color: AppColors.cocoa,
            offset: Offset(0, 1.5),
            blurRadius: 0,
          ),
        ],
      ),
      child: Text(
        status.toUpperCase(),
        style: GoogleFonts.quicksand(
          color: fg,
          fontSize: fontSize,
          fontWeight: FontWeight.w800,
          letterSpacing: 0.4,
        ),
      ),
    );
  }
}
