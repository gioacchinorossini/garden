import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

class AppColors {
  // Cats & Soup Design System (from style.css and header.php)
  static const Color canvas = Color(0xFFF8F3EC); // Parchment background
  static const Color surface = Color(0xFFFFFDF9); // Warm cream surface
  static const Color surfaceHover = Color(0xFFF4EFE6);
  static const Color surfaceActive = Color(0xFFEAE1D2);
  static const Color surfaceSelected = Color(0xFFEFE6DA);

  // Matcha Brand Accents
  static const Color primary = Color(0xFF7FA668); // Matcha Dark
  static const Color primaryHover = Color(0xFF6F9557);
  static const Color matchaLight = Color(0xFFBBD6B8); // Pastel matcha active pill
  static const Color matchaDarkText = Color(0xFF2F4D22);

  // Cocoa Tones & Borders
  static const Color cocoa = Color(0xFF785D4D); // Standard 2.5px outline
  static const Color cocoaDark = Color(0xFF4A3528);
  static const Color cocoaButtonBorder = Color(0xFF523E31);
  static const Color borderSubtle = Color(0xFFDFCFC2); // Dashed lines

  // Text Colors
  static const Color textMain = Color(0xFF3E2B1E); // Espresso cocoa
  static const Color textSub = Color(0xFF6B5446);
  static const Color textMuted = Color(0xFF968172);

  // Accents
  static const Color coral = Color(0xFFE2735D);
  static const Color honey = Color(0xFFF4B342);
  static const Color slotBg = Color(0xFFF7EFE6);
  static const Color availableGreen = Color(0xFF22C55E);

  // Helper box shadow for Cats & Soup 3D tactile feel
  static List<BoxShadow> get tactileShadow => const [
    BoxShadow(
      color: Color(0xFF785D4D),
      offset: Offset(0, 3),
      blurRadius: 0,
    ),
  ];

  static List<BoxShadow> get tactileShadowLg => const [
    BoxShadow(
      color: Color(0xFF785D4D),
      offset: Offset(0, 4),
      blurRadius: 0,
    ),
    BoxShadow(
      color: Color(0x15000000),
      offset: Offset(0, 10),
      blurRadius: 20,
    ),
  ];
}

class AppTheme {
  static ThemeData get lightTheme {
    final textTheme = GoogleFonts.quicksandTextTheme().copyWith(
      displayLarge: GoogleFonts.quicksand(fontSize: 32, fontWeight: FontWeight.w800, color: AppColors.textMain),
      displayMedium: GoogleFonts.quicksand(fontSize: 28, fontWeight: FontWeight.w800, color: AppColors.textMain),
      titleLarge: GoogleFonts.quicksand(fontSize: 20, fontWeight: FontWeight.w700, color: AppColors.textMain),
      titleMedium: GoogleFonts.quicksand(fontSize: 16, fontWeight: FontWeight.w700, color: AppColors.textMain),
      titleSmall: GoogleFonts.quicksand(fontSize: 14, fontWeight: FontWeight.w700, color: AppColors.textSub),
      bodyLarge: GoogleFonts.nunito(fontSize: 15, fontWeight: FontWeight.w600, color: AppColors.textMain),
      bodyMedium: GoogleFonts.nunito(fontSize: 13, fontWeight: FontWeight.w600, color: AppColors.textSub),
      bodySmall: GoogleFonts.nunito(fontSize: 12, fontWeight: FontWeight.w500, color: AppColors.textMuted),
      labelLarge: GoogleFonts.quicksand(fontSize: 14, fontWeight: FontWeight.w700, color: AppColors.textMain),
    );

    return ThemeData(
      useMaterial3: true,
      scaffoldBackgroundColor: AppColors.canvas,
      colorScheme: const ColorScheme.light(
        primary: AppColors.primary,
        onPrimary: AppColors.surface,
        surface: AppColors.surface,
        onSurface: AppColors.textMain,
        outline: AppColors.cocoa,
      ),
      textTheme: textTheme,
      appBarTheme: AppBarTheme(
        backgroundColor: AppColors.surface,
        foregroundColor: AppColors.textMain,
        elevation: 0,
        centerTitle: false,
        titleTextStyle: GoogleFonts.quicksand(
          fontSize: 18,
          fontWeight: FontWeight.w800,
          color: AppColors.textMain,
        ),
      ),
      cardTheme: CardThemeData(
        color: AppColors.surface,
        elevation: 0,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(20),
          side: const BorderSide(color: AppColors.cocoa, width: 2),
        ),
        margin: EdgeInsets.zero,
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: AppColors.surface,
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: const BorderSide(color: AppColors.cocoa, width: 2),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: const BorderSide(color: AppColors.cocoa, width: 2),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: const BorderSide(color: AppColors.primary, width: 2.5),
        ),
        hintStyle: GoogleFonts.nunito(color: AppColors.textMuted, fontSize: 13, fontWeight: FontWeight.w600),
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: AppColors.primary,
          foregroundColor: AppColors.surface,
          elevation: 0,
          padding: const EdgeInsets.symmetric(horizontal: 22, vertical: 12),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(99),
            side: const BorderSide(color: AppColors.cocoaButtonBorder, width: 2),
          ),
          textStyle: GoogleFonts.quicksand(
            fontSize: 15,
            fontWeight: FontWeight.w800,
          ),
        ),
      ),
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          foregroundColor: AppColors.textMain,
          side: const BorderSide(color: AppColors.cocoa, width: 2),
          padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 12),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(99),
          ),
          textStyle: GoogleFonts.quicksand(
            fontSize: 14,
            fontWeight: FontWeight.w700,
          ),
        ),
      ),
    );
  }
}
