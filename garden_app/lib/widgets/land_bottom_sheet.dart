import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../models/models.dart';
import '../services/auth_state.dart';
import '../theme/app_theme.dart';
import 'garden_crop_icon.dart';
import 'status_badge.dart';

class LandBottomSheet extends StatelessWidget {
  final Land land;
  final VoidCallback onClose;
  final void Function(Plot? plot) onApply;

  const LandBottomSheet({
    super.key,
    required this.land,
    required this.onClose,
    required this.onApply,
  });

  @override
  Widget build(BuildContext context) {
    final user = AuthState().currentUser;
    final availableCount = land.plots.where((p) => p.isAvailable).length;
    final cropList = land.crops.split(',').map((c) => c.trim()).where((c) => c.isNotEmpty).toList();

    return Container(
      constraints: BoxConstraints(
        maxHeight: MediaQuery.of(context).size.height * 0.75,
      ),
      decoration: const BoxDecoration(
        color: AppColors.surface,
        borderRadius: BorderRadius.vertical(top: Radius.circular(28)),
        border: Border(
          top: BorderSide(color: AppColors.cocoa, width: 3),
          left: BorderSide(color: AppColors.cocoa, width: 2),
          right: BorderSide(color: AppColors.cocoa, width: 2),
        ),
        boxShadow: [
          BoxShadow(
            color: Color(0x30785D4D),
            offset: Offset(0, -6),
            blurRadius: 0,
          ),
          BoxShadow(
            color: Color(0x20000000),
            offset: Offset(0, -12),
            blurRadius: 30,
          ),
        ],
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          // Pull handle
          Container(
            margin: const EdgeInsets.only(top: 10, bottom: 8),
            width: 44,
            height: 5,
            decoration: BoxDecoration(
              color: AppColors.cocoa.withValues(alpha: 0.35),
              borderRadius: BorderRadius.circular(99),
            ),
          ),

          // Header
          Padding(
            padding: const EdgeInsets.fromLTRB(20, 4, 14, 8),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  margin: const EdgeInsets.only(right: 12, top: 2),
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: AppColors.matchaLight,
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: AppColors.cocoa, width: 1.5),
                    boxShadow: AppColors.tactileShadow,
                  ),
                  child: GardenCropIcon(crop: land.crops, size: 24),
                ),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Flexible(
                            child: Text(
                              land.title,
                              style: GoogleFonts.quicksand(
                                fontSize: 18,
                                fontWeight: FontWeight.w900,
                                color: AppColors.textMain,
                              ),
                            ),
                          ),
                          const SizedBox(width: 8),
                          StatusBadge(status: land.status),
                        ],
                      ),
                      const SizedBox(height: 4),
                      Row(
                        children: [
                          const Icon(Icons.location_on, size: 14, color: AppColors.coral),
                          const SizedBox(width: 4),
                          Expanded(
                            child: Text(
                              land.address,
                              style: GoogleFonts.nunito(
                                fontSize: 12,
                                fontWeight: FontWeight.w600,
                                color: AppColors.textSub,
                              ),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
                IconButton(
                  icon: const Icon(Icons.close_rounded, color: AppColors.textSub),
                  onPressed: onClose,
                ),
              ],
            ),
          ),

          const Divider(color: AppColors.borderSubtle, height: 1, thickness: 1.5),

          // Scrollable Body
          Flexible(
            child: SingleChildScrollView(
              padding: const EdgeInsets.fromLTRB(20, 14, 20, 20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Badges: Area, Plots, 3D
                  Row(
                    children: [
                      _buildChip(
                        icon: Icons.square_foot,
                        label: '${land.area} sq.m',
                        color: AppColors.slotBg,
                      ),
                      const SizedBox(width: 8),
                      _buildChip(
                        icon: Icons.grid_view_rounded,
                        label: '$availableCount / ${land.plots.length} plots available',
                        color: availableCount > 0 ? AppColors.matchaLight : AppColors.slotBg,
                        textColor: availableCount > 0 ? AppColors.matchaDarkText : AppColors.textSub,
                      ),
                      if (land.has3dView) ...[
                        const SizedBox(width: 8),
                        _buildChip(
                          icon: Icons.view_in_ar_rounded,
                          label: '3D View',
                          color: const Color(0xFFFEF3C7),
                          textColor: const Color(0xFF92400E),
                        ),
                      ],
                    ],
                  ),
                  const SizedBox(height: 14),

                  // Permitted Crops
                  if (cropList.isNotEmpty) ...[
                    Text(
                      'PERMITTED CROPS',
                      style: GoogleFonts.quicksand(
                        fontSize: 11,
                        fontWeight: FontWeight.w800,
                        color: AppColors.textMuted,
                        letterSpacing: 0.5,
                      ),
                    ),
                    const SizedBox(height: 6),
                    Wrap(
                      spacing: 6,
                      runSpacing: 6,
                      children: cropList.map((crop) {
                        return Container(
                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                          decoration: BoxDecoration(
                            color: AppColors.surface,
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
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              GardenCropIcon(crop: crop, size: 16),
                              const SizedBox(width: 6),
                              Text(
                                crop,
                                style: GoogleFonts.nunito(
                                  fontSize: 12,
                                  fontWeight: FontWeight.w700,
                                  color: AppColors.textMain,
                                ),
                              ),
                            ],
                          ),
                        );
                      }).toList(),
                    ),
                    const SizedBox(height: 14),
                  ],

                  // Description
                  if (land.description.isNotEmpty) ...[
                    Text(
                      land.description,
                      style: GoogleFonts.nunito(
                        fontSize: 13,
                        fontWeight: FontWeight.w600,
                        color: AppColors.textSub,
                        height: 1.4,
                      ),
                    ),
                    const SizedBox(height: 16),
                  ],

                  // Subdivided Plots Grid
                  Text(
                    'SUBDIVIDED PLOTS',
                    style: GoogleFonts.quicksand(
                      fontSize: 11,
                      fontWeight: FontWeight.w800,
                      color: AppColors.textMuted,
                      letterSpacing: 0.5,
                    ),
                  ),
                  const SizedBox(height: 8),

                  if (land.plots.isEmpty)
                    Text(
                      'No individual plots listed.',
                      style: GoogleFonts.nunito(color: AppColors.textMuted, fontSize: 13),
                    )
                  else
                    GridView.builder(
                      shrinkWrap: true,
                      physics: const NeverScrollableScrollPhysics(),
                      itemCount: land.plots.length,
                      gridDelegate: const SliverGridDelegateWithMaxCrossAxisExtent(
                        maxCrossAxisExtent: 220,
                        mainAxisExtent: 96,
                        crossAxisSpacing: 10,
                        mainAxisSpacing: 10,
                      ),
                      itemBuilder: (context, i) {
                        final plot = land.plots[i];
                        final isAvail = plot.isAvailable;

                        return Container(
                          padding: const EdgeInsets.all(10),
                          decoration: BoxDecoration(
                            color: isAvail ? AppColors.surface : AppColors.slotBg,
                            borderRadius: BorderRadius.circular(16),
                            border: Border.all(
                              color: isAvail ? AppColors.cocoa : AppColors.borderSubtle,
                              width: 1.5,
                            ),
                            boxShadow: isAvail
                                ? const [
                                    BoxShadow(
                                      color: AppColors.cocoa,
                                      offset: Offset(0, 2),
                                      blurRadius: 0,
                                    ),
                                  ]
                                : null,
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Row(
                                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                children: [
                                  Text(
                                    plot.plotNumber,
                                    style: GoogleFonts.quicksand(fontSize: 13, fontWeight: FontWeight.w800),
                                  ),
                                  StatusBadge(status: plot.status, fontSize: 9),
                                ],
                              ),
                              Row(
                                children: [
                                  if (plot.crop.isNotEmpty) ...[
                                    GardenCropIcon(crop: plot.crop, size: 13),
                                    const SizedBox(width: 4),
                                  ],
                                  Expanded(
                                    child: Text(
                                      '${plot.area} sq.m ${plot.crop.isNotEmpty ? '• ${plot.crop}' : ''}',
                                      style: GoogleFonts.nunito(fontSize: 11, color: AppColors.textSub, fontWeight: FontWeight.w600),
                                      maxLines: 1,
                                      overflow: TextOverflow.ellipsis,
                                    ),
                                  ),
                                ],
                              ),
                              if (isAvail && (user?.isGardener ?? true))
                                Align(
                                  alignment: Alignment.centerRight,
                                  child: GestureDetector(
                                    onTap: () => onApply(plot),
                                    child: Text(
                                      'Apply →',
                                      style: GoogleFonts.quicksand(
                                        fontSize: 11,
                                        fontWeight: FontWeight.w800,
                                        color: AppColors.primary,
                                      ),
                                    ),
                                  ),
                                ),
                            ],
                          ),
                        );
                      },
                    ),

                  const SizedBox(height: 20),

                  // Apply for Plot Main Action Button
                  if (user?.isGardener ?? true)
                    ElevatedButton(
                      style: ElevatedButton.styleFrom(
                        backgroundColor: AppColors.primary,
                        foregroundColor: AppColors.surface,
                        minimumSize: const Size.fromHeight(48),
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(99),
                          side: const BorderSide(color: AppColors.cocoaButtonBorder, width: 2),
                        ),
                        elevation: 0,
                      ),
                      onPressed: () => onApply(null),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          const Icon(Icons.assignment_add, size: 18),
                          const SizedBox(width: 8),
                          Text(
                            'Apply for a Garden Plot',
                            style: GoogleFonts.quicksand(fontSize: 15, fontWeight: FontWeight.w800),
                          ),
                        ],
                      ),
                    ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildChip({
    required IconData icon,
    required String label,
    required Color color,
    Color? textColor,
  }) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
      decoration: BoxDecoration(
        color: color,
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
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 13, color: textColor ?? AppColors.textMain),
          const SizedBox(width: 4),
          Text(
            label,
            style: GoogleFonts.quicksand(
              fontSize: 11,
              fontWeight: FontWeight.w800,
              color: textColor ?? AppColors.textMain,
            ),
          ),
        ],
      ),
    );
  }
}
