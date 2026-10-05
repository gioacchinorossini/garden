import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../models/models.dart';
import '../services/auth_state.dart';
import '../theme/app_theme.dart';
import 'garden_crop_icon.dart';

class LandBottomSheet extends StatelessWidget {
  final Land land;
  final VoidCallback onClose;
  final void Function(Plot? plot) onApply;
  final VoidCallback? onPrevious;
  final VoidCallback? onNext;
  final VoidCallback? onOpenSchedules;
  final VoidCallback? onOpenHarvests;
  final VoidCallback? onOpenRequests;

  const LandBottomSheet({
    super.key,
    required this.land,
    required this.onClose,
    required this.onApply,
    this.onPrevious,
    this.onNext,
    this.onOpenSchedules,
    this.onOpenHarvests,
    this.onOpenRequests,
  });

  @override
  Widget build(BuildContext context) {
    final user = AuthState().currentUser;
    final isLandowner = user?.isLandowner ?? false;
    final availableCount = land.plots.where((p) => p.isAvailable).length;
    final isAvailable = availableCount > 0 || land.availablePlots > 0;

    final cropList = land.crops
        .split(',')
        .map((c) => c.trim())
        .where((c) => c.isNotEmpty)
        .toList();

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
            color: Color(0x25000000),
            offset: Offset(0, -12),
            blurRadius: 30,
          ),
        ],
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          // Color status strip (matching #sheetStatusStrip on web)
          Container(
            height: 4,
            margin: const EdgeInsets.symmetric(horizontal: 24),
            decoration: BoxDecoration(
              color: isAvailable ? const Color(0xFF198754) : AppColors.primary,
              borderRadius: BorderRadius.circular(99),
            ),
          ),

          // Drag handle (matching .sheet-drag-handle on web)
          Container(
            margin: const EdgeInsets.only(top: 8, bottom: 8),
            width: 38,
            height: 4,
            decoration: BoxDecoration(
              color: AppColors.cocoa.withValues(alpha: 0.35),
              borderRadius: BorderRadius.circular(99),
            ),
          ),

          // Header Row
          Padding(
            padding: const EdgeInsets.fromLTRB(18, 0, 14, 8),
            child: Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Status & Owner Badges (matching web #sheetStatusBadge & #sheetOwnerBadge)
                      Row(
                        children: [
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                            decoration: BoxDecoration(
                              color: isAvailable ? const Color(0xFF198754) : const Color(0xFF6C757D),
                              borderRadius: BorderRadius.circular(99),
                            ),
                            child: Text(
                              isAvailable ? '$availableCount Plots Available' : 'Fully Leased',
                              style: GoogleFonts.quicksand(
                                fontSize: 10.5,
                                fontWeight: FontWeight.w800,
                                color: Colors.white,
                              ),
                            ),
                          ),
                          const SizedBox(width: 6),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                            decoration: BoxDecoration(
                              color: const Color(0xFFF8F9FA),
                              borderRadius: BorderRadius.circular(99),
                              border: Border.all(color: const Color(0xFFDEE2E6), width: 1),
                            ),
                            child: Text(
                              isLandowner ? 'Owner' : 'Landowner',
                              style: GoogleFonts.quicksand(
                                fontSize: 10.5,
                                fontWeight: FontWeight.w700,
                                color: const Color(0xFF6C757D),
                              ),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 6),

                      // Title & Metric Badges
                      Wrap(
                        crossAxisAlignment: WrapCrossAlignment.center,
                        spacing: 6,
                        runSpacing: 4,
                        children: [
                          Text(
                            land.title,
                            style: GoogleFonts.quicksand(
                              fontSize: 16.5,
                              fontWeight: FontWeight.w800,
                              color: AppColors.textMain,
                              height: 1.2,
                            ),
                          ),
                          // Area Badge (matching web #sheetLandArea)
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                            decoration: BoxDecoration(
                              color: const Color(0xFFF8F9FA),
                              borderRadius: BorderRadius.circular(99),
                              border: Border.all(color: const Color(0xFFDEE2E6), width: 1),
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                const Icon(Icons.straighten_rounded, size: 12, color: Color(0xFF6C757D)),
                                const SizedBox(width: 3),
                                Text(
                                  '${land.area.toStringAsFixed(0)} m²',
                                  style: GoogleFonts.nunito(
                                    fontSize: 11,
                                    fontWeight: FontWeight.w700,
                                    color: AppColors.textMain,
                                  ),
                                ),
                              ],
                            ),
                          ),
                          // Plots Count Badge (matching web #sheetPlotsCount)
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                            decoration: BoxDecoration(
                              color: const Color(0xFFE8F5E9),
                              borderRadius: BorderRadius.circular(99),
                              border: Border.all(color: const Color(0xFFC8E6C9), width: 1),
                            ),
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                const Icon(Icons.grid_3x3_rounded, size: 12, color: Color(0xFF198754)),
                                const SizedBox(width: 3),
                                Text(
                                  '${land.occupiedPlots} / ${land.totalPlots > 0 ? land.totalPlots : land.plots.length} plots',
                                  style: GoogleFonts.nunito(
                                    fontSize: 11,
                                    fontWeight: FontWeight.w700,
                                    color: const Color(0xFF198754),
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 4),

                      // Address (matching web #sheetLandAddress)
                      Row(
                        children: [
                          const Icon(Icons.location_on, size: 13, color: Color(0xFF198754)),
                          const SizedBox(width: 3),
                          Expanded(
                            child: Text(
                              land.address.isNotEmpty ? land.address : 'Address not specified',
                              style: GoogleFonts.nunito(
                                fontSize: 11.5,
                                fontWeight: FontWeight.w600,
                                color: const Color(0xFF6C757D),
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

                // Top Right Navigation & Close controls (matching web chevron-left, chevron-right, btn-close)
                Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    if (onPrevious != null)
                      _buildHeaderNavButton(
                        icon: Icons.chevron_left_rounded,
                        tooltip: 'Previous Garden',
                        onTap: onPrevious!,
                      ),
                    if (onNext != null) ...[
                      const SizedBox(width: 5),
                      _buildHeaderNavButton(
                        icon: Icons.chevron_right_rounded,
                        tooltip: 'Next Garden',
                        onTap: onNext!,
                      ),
                    ],
                    const SizedBox(width: 5),
                    _buildHeaderNavButton(
                      icon: Icons.close_rounded,
                      tooltip: 'Close',
                      onTap: onClose,
                    ),
                  ],
                ),
              ],
            ),
          ),

          const Divider(color: AppColors.borderSubtle, height: 1, thickness: 1.2),

          // Scrollable Body
          Flexible(
            child: SingleChildScrollView(
              padding: const EdgeInsets.fromLTRB(18, 12, 18, 18),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Permitted Crops Section (matching web #sheetPermittedCropsContainer)
                  Text(
                    'PERMITTED CROPS',
                    style: GoogleFonts.quicksand(
                      fontSize: 10.5,
                      fontWeight: FontWeight.w800,
                      color: const Color(0xFF6C757D),
                      letterSpacing: 0.5,
                    ),
                  ),
                  const SizedBox(height: 6),
                  if (cropList.isEmpty)
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                      decoration: BoxDecoration(
                        color: const Color(0xFFF8F9FA),
                        borderRadius: BorderRadius.circular(99),
                        border: Border.all(color: const Color(0xFFDEE2E6), width: 1),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const GardenCropIcon(crop: 'generic', size: 14),
                          const SizedBox(width: 5),
                          Text(
                            'All Crops Permitted',
                            style: GoogleFonts.nunito(
                              fontSize: 11.5,
                              fontWeight: FontWeight.w600,
                              color: const Color(0xFF6C757D),
                            ),
                          ),
                        ],
                      ),
                    )
                  else
                    Wrap(
                      spacing: 6,
                      runSpacing: 6,
                      children: cropList.map((crop) {
                        return Container(
                          padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(99),
                            border: Border.all(color: const Color(0xFFDEE2E6), width: 1.2),
                            boxShadow: const [
                              BoxShadow(
                                color: Color(0x10000000),
                                offset: Offset(0, 1),
                                blurRadius: 2,
                              ),
                            ],
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              GardenCropIcon(crop: crop, size: 15),
                              const SizedBox(width: 5),
                              Text(
                                crop,
                                style: GoogleFonts.nunito(
                                  fontSize: 11.5,
                                  fontWeight: FontWeight.w700,
                                  color: AppColors.textMain,
                                ),
                              ),
                            ],
                          ),
                        );
                      }).toList(),
                    ),
                  const SizedBox(height: 12),

                  // Description (matching web #sheetLandDesc)
                  if (land.description.isNotEmpty) ...[
                    Text(
                      land.description,
                      style: GoogleFonts.nunito(
                        fontSize: 12.5,
                        fontWeight: FontWeight.w600,
                        color: const Color(0xFF6C757D),
                        height: 1.4,
                      ),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                    ),
                    const SizedBox(height: 12),
                  ],

                  // Partition Plots Section (matching web #sheetPlotsGrid)
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        'PARTITION PLOTS',
                        style: GoogleFonts.quicksand(
                          fontSize: 10.5,
                          fontWeight: FontWeight.w800,
                          color: const Color(0xFF6C757D),
                          letterSpacing: 0.5,
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
                        decoration: BoxDecoration(
                          color: const Color(0xFFE8F5E9),
                          borderRadius: BorderRadius.circular(99),
                        ),
                        child: Text(
                          '$availableCount / ${land.plots.length} Available',
                          style: GoogleFonts.quicksand(
                            fontSize: 10,
                            fontWeight: FontWeight.w800,
                            color: const Color(0xFF198754),
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 7),

                  // Single-line horizontal scrollable plots (exact match to web #sheetPlotsGrid)
                  if (land.plots.isEmpty)
                    Padding(
                      padding: const EdgeInsets.symmetric(vertical: 8),
                      child: Text(
                        'No partition plots configured yet.',
                        style: GoogleFonts.nunito(fontSize: 12, color: AppColors.textMuted),
                      ),
                    )
                  else
                    SingleChildScrollView(
                      scrollDirection: Axis.horizontal,
                      padding: const EdgeInsets.only(bottom: 4),
                      child: Row(
                        children: land.plots.map((plot) {
                          final isAvail = plot.isAvailable;
                          return Padding(
                            padding: const EdgeInsets.only(right: 6),
                            child: GestureDetector(
                              onTap: () => onApply(plot),
                              child: Container(
                                height: 32,
                                padding: const EdgeInsets.symmetric(horizontal: 9),
                                decoration: BoxDecoration(
                                  color: isAvail ? const Color(0xFFE8F5E9) : const Color(0xFFF1F5F9),
                                  borderRadius: BorderRadius.circular(99),
                                  border: Border.all(
                                    color: isAvail ? const Color(0xFFA5D6A7) : const Color(0xFFDEE2E6),
                                    width: 1.2,
                                  ),
                                  boxShadow: const [
                                    BoxShadow(
                                      color: Color(0x10000000),
                                      offset: Offset(0, 1),
                                      blurRadius: 1,
                                    ),
                                  ],
                                ),
                                child: Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    if (plot.crop.isNotEmpty) ...[
                                      GardenCropIcon(crop: plot.crop, size: 15),
                                      const SizedBox(width: 4),
                                    ] else ...[
                                      const Icon(Icons.eco_rounded, size: 13, color: Color(0xFF198754)),
                                      const SizedBox(width: 4),
                                    ],
                                    Text(
                                      plot.plotNumber,
                                      style: GoogleFonts.quicksand(
                                        fontSize: 12,
                                        fontWeight: FontWeight.w800,
                                        color: AppColors.textMain,
                                      ),
                                    ),
                                    const SizedBox(width: 4),
                                    Text(
                                      '${plot.area.toStringAsFixed(0)}m²',
                                      style: GoogleFonts.nunito(
                                        fontSize: 10.5,
                                        fontWeight: FontWeight.w600,
                                        color: const Color(0xFF6C757D),
                                      ),
                                    ),
                                    const SizedBox(width: 5),
                                    Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1.5),
                                      decoration: BoxDecoration(
                                        color: isAvail ? const Color(0xFF198754) : const Color(0xFF6C757D),
                                        borderRadius: BorderRadius.circular(99),
                                      ),
                                      child: Text(
                                        isAvail ? 'Avail' : 'Leased',
                                        style: GoogleFonts.quicksand(
                                          fontSize: 9,
                                          fontWeight: FontWeight.w800,
                                          color: Colors.white,
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ),
                          );
                        }).toList(),
                      ),
                    ),

                  const SizedBox(height: 18),

                  // Bottom Action CTA Buttons (matching web CTA buttons)
                  Row(
                    children: [
                      // Primary Button: Rent a Plot / Manage Plots
                      Expanded(
                        child: GestureDetector(
                          onTap: () => onApply(null),
                          child: Container(
                            height: 44,
                            decoration: BoxDecoration(
                              color: AppColors.primary,
                              borderRadius: BorderRadius.circular(99),
                              border: Border.all(color: AppColors.cocoaButtonBorder, width: 2),
                              boxShadow: const [
                                BoxShadow(
                                  color: AppColors.cocoaButtonBorder,
                                  offset: Offset(0, 2.5),
                                  blurRadius: 0,
                                ),
                              ],
                            ),
                            child: Row(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Icon(
                                  isLandowner ? Icons.layers_rounded : Icons.shopping_bag_outlined,
                                  size: 16,
                                  color: AppColors.surface,
                                ),
                                const SizedBox(width: 6),
                                Text(
                                  isLandowner ? 'Manage Plots' : 'Rent a Plot',
                                  style: GoogleFonts.quicksand(
                                    fontSize: 14,
                                    fontWeight: FontWeight.w800,
                                    color: AppColors.surface,
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),

                      // Secondary Button: Schedules / Requests
                      GestureDetector(
                        onTap: () {
                          if (isLandowner) {
                            onOpenRequests?.call();
                          } else {
                            onOpenSchedules?.call();
                          }
                        },
                        child: Container(
                          height: 44,
                          padding: const EdgeInsets.symmetric(horizontal: 14),
                          decoration: BoxDecoration(
                            color: AppColors.surface,
                            borderRadius: BorderRadius.circular(99),
                            border: Border.all(color: AppColors.cocoa, width: 2),
                            boxShadow: AppColors.tactileShadow,
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(
                                isLandowner ? Icons.description_outlined : Icons.calendar_month_rounded,
                                size: 16,
                                color: AppColors.textMain,
                              ),
                              const SizedBox(width: 5),
                              Text(
                                isLandowner ? 'Requests' : 'Schedules',
                                style: GoogleFonts.quicksand(
                                  fontSize: 12.5,
                                  fontWeight: FontWeight.w800,
                                  color: AppColors.textMain,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),

                      // Tertiary Button: Harvests / Profile
                      GestureDetector(
                        onTap: () {
                          if (isLandowner) {
                            // landowner profile
                          } else {
                            onOpenHarvests?.call();
                          }
                        },
                        child: Container(
                          width: 44,
                          height: 44,
                          decoration: BoxDecoration(
                            color: const Color(0xFFF0FDF4),
                            borderRadius: BorderRadius.circular(99),
                            border: Border.all(color: const Color(0xFFC3E6CB), width: 1.5),
                          ),
                          alignment: Alignment.center,
                          child: Icon(
                            isLandowner ? Icons.person_rounded : Icons.eco_rounded,
                            size: 18,
                            color: const Color(0xFF198754),
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildHeaderNavButton({
    required IconData icon,
    required String tooltip,
    required VoidCallback onTap,
  }) {
    return Tooltip(
      message: tooltip,
      child: GestureDetector(
        onTap: onTap,
        child: Container(
          width: 30,
          height: 30,
          decoration: BoxDecoration(
            color: const Color(0xFFF8F9FA),
            shape: BoxShape.circle,
            border: Border.all(color: const Color(0xFFDEE2E6), width: 1),
            boxShadow: const [
              BoxShadow(
                color: Color(0x10000000),
                offset: Offset(0, 1),
                blurRadius: 1,
              ),
            ],
          ),
          alignment: Alignment.center,
          child: Icon(icon, size: 16, color: const Color(0xFF212529)),
        ),
      ),
    );
  }
}
