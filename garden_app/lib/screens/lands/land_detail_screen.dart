import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../models/models.dart';
import '../../services/auth_state.dart';
import '../../theme/app_theme.dart';
import '../../widgets/garden_crop_icon.dart';
import '../../widgets/status_badge.dart';
import '../requests/apply_plot_dialog.dart';

class LandDetailScreen extends StatefulWidget {
  final Land land;
  final VoidCallback onDataChanged;

  const LandDetailScreen({
    super.key,
    required this.land,
    required this.onDataChanged,
  });

  @override
  State<LandDetailScreen> createState() => _LandDetailScreenState();
}

class _LandDetailScreenState extends State<LandDetailScreen> {
  late Land _land;

  @override
  void initState() {
    super.initState();
    _land = widget.land;
  }

  void _openApplyDialog([Plot? plot]) {
    showDialog(
      context: context,
      builder: (context) => ApplyPlotDialog(
        land: _land,
        selectedPlot: plot,
        onRequestSubmitted: () {
          widget.onDataChanged();
          setState(() {});
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final user = AuthState().currentUser;
    final cropsList = _land.crops.split(',').map((c) => c.trim()).where((c) => c.isNotEmpty).toList();

    return Scaffold(
      backgroundColor: AppColors.canvas,
      appBar: AppBar(
        title: Text(_land.title, style: GoogleFonts.quicksand(fontWeight: FontWeight.w800)),
        actions: [
          Padding(
            padding: const EdgeInsets.only(right: 16),
            child: Center(child: StatusBadge(status: _land.status)),
          ),
        ],
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        child: Center(
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 800),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Header Card
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
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Container(
                            padding: const EdgeInsets.all(12),
                            decoration: BoxDecoration(
                              color: AppColors.matchaLight,
                              borderRadius: BorderRadius.circular(16),
                              border: Border.all(color: AppColors.cocoa, width: 2),
                            ),
                            child: GardenCropIcon(crop: _land.crops, size: 28),
                          ),
                          const SizedBox(width: 14),
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  _land.title,
                                  style: GoogleFonts.quicksand(fontSize: 20, fontWeight: FontWeight.w900),
                                ),
                                const SizedBox(height: 2),
                                Row(
                                  children: [
                                    const Icon(Icons.location_on, size: 14, color: AppColors.coral),
                                    const SizedBox(width: 4),
                                    Expanded(
                                      child: Text(
                                        _land.address,
                                        style: GoogleFonts.nunito(fontSize: 13, color: AppColors.textSub, fontWeight: FontWeight.w600),
                                      ),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 18),
                      const Divider(color: AppColors.borderSubtle, thickness: 1.5),
                      const SizedBox(height: 14),

                      // Quick Stats
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceAround,
                        children: [
                          _buildQuickStat('Total Area', '${_land.area} m²', Icons.square_foot),
                          _buildQuickStat('Total Plots', '${_land.plots.isNotEmpty ? _land.plots.length : _land.totalPlots}', Icons.grid_view_rounded),
                          _buildQuickStat(
                            'Available',
                            '${_land.plots.where((p) => p.isAvailable).length}',
                            Icons.check_circle_outline,
                            color: AppColors.primary,
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 16),

                // Description
                if (_land.description.isNotEmpty) ...[
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(18),
                    decoration: BoxDecoration(
                      color: AppColors.surface,
                      borderRadius: BorderRadius.circular(22),
                      border: Border.all(color: AppColors.cocoa, width: 2),
                      boxShadow: AppColors.tactileShadow,
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('About this Lot', style: GoogleFonts.quicksand(fontSize: 15, fontWeight: FontWeight.w800)),
                        const SizedBox(height: 6),
                        Text(
                          _land.description,
                          style: GoogleFonts.nunito(fontSize: 13, color: AppColors.textSub, height: 1.5, fontWeight: FontWeight.w600),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 16),
                ],

                // Permitted Crops
                if (cropsList.isNotEmpty) ...[
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(18),
                    decoration: BoxDecoration(
                      color: AppColors.surface,
                      borderRadius: BorderRadius.circular(22),
                      border: Border.all(color: AppColors.cocoa, width: 2),
                      boxShadow: AppColors.tactileShadow,
                    ),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('Permitted Crops & Plantings', style: GoogleFonts.quicksand(fontSize: 15, fontWeight: FontWeight.w800)),
                        const SizedBox(height: 10),
                        Wrap(
                          spacing: 8,
                          runSpacing: 8,
                          children: cropsList.map((c) {
                            return Container(
                              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                              decoration: BoxDecoration(
                                color: AppColors.slotBg,
                                borderRadius: BorderRadius.circular(99),
                                border: Border.all(color: AppColors.cocoa, width: 1.5),
                              ),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  GardenCropIcon(crop: c, size: 16),
                                  const SizedBox(width: 6),
                                  Text(c, style: GoogleFonts.quicksand(fontSize: 12, fontWeight: FontWeight.w700)),
                                ],
                              ),
                            );
                          }).toList(),
                        ),
                      ],
                    ),
                  ),
                  const SizedBox(height: 16),
                ],

                // Plots Section
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(18),
                  decoration: BoxDecoration(
                    color: AppColors.surface,
                    borderRadius: BorderRadius.circular(22),
                    border: Border.all(color: AppColors.cocoa, width: 2),
                    boxShadow: AppColors.tactileShadow,
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text('Subdivided Plots', style: GoogleFonts.quicksand(fontSize: 15, fontWeight: FontWeight.w800)),
                          Text(
                            '${_land.plots.length} Lots',
                            style: GoogleFonts.nunito(fontSize: 12, color: AppColors.textMuted, fontWeight: FontWeight.w700),
                          ),
                        ],
                      ),
                      const SizedBox(height: 14),

                      if (_land.plots.isEmpty) ...[
                        const Padding(
                          padding: EdgeInsets.symmetric(vertical: 20),
                          child: Center(child: Text('No individual plots defined yet.')),
                        ),
                      ] else ...[
                        GridView.builder(
                          shrinkWrap: true,
                          physics: const NeverScrollableScrollPhysics(),
                          itemCount: _land.plots.length,
                          gridDelegate: const SliverGridDelegateWithMaxCrossAxisExtent(
                            maxCrossAxisExtent: 360,
                            mainAxisExtent: 110,
                            crossAxisSpacing: 10,
                            mainAxisSpacing: 10,
                          ),
                          itemBuilder: (context, index) {
                            final plot = _land.plots[index];
                            final isAvail = plot.isAvailable;

                            return Container(
                              padding: const EdgeInsets.all(12),
                              decoration: BoxDecoration(
                                color: isAvail ? AppColors.surface : AppColors.slotBg,
                                borderRadius: BorderRadius.circular(18),
                                border: Border.all(
                                  color: isAvail ? AppColors.cocoa : AppColors.borderSubtle,
                                  width: 1.5,
                                ),
                                boxShadow: isAvail ? AppColors.tactileShadow : null,
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
                                        style: GoogleFonts.quicksand(fontSize: 14, fontWeight: FontWeight.w800),
                                      ),
                                      StatusBadge(status: plot.status, fontSize: 10),
                                    ],
                                  ),
                                  Row(
                                    children: [
                                      if (plot.crop.isNotEmpty) ...[
                                        GardenCropIcon(crop: plot.crop, size: 14),
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
                                  if (!isAvail && plot.farmerName.isNotEmpty) ...[
                                    Text(
                                      'Gardener: ${plot.farmerName}',
                                      style: GoogleFonts.nunito(fontSize: 11, color: AppColors.textMuted),
                                    ),
                                  ] else if (isAvail && (user?.isGardener ?? true)) ...[
                                    Align(
                                      alignment: Alignment.centerRight,
                                      child: GestureDetector(
                                        onTap: () => _openApplyDialog(plot),
                                        child: Text(
                                          'Apply →',
                                          style: GoogleFonts.quicksand(
                                            fontSize: 12,
                                            fontWeight: FontWeight.w800,
                                            color: AppColors.primary,
                                          ),
                                        ),
                                      ),
                                    ),
                                  ],
                                ],
                              ),
                            );
                          },
                        ),
                      ],
                    ],
                  ),
                ),
                const SizedBox(height: 80),
              ],
            ),
          ),
        ),
      ),
      bottomNavigationBar: (user?.isGardener ?? true)
          ? Container(
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
              decoration: const BoxDecoration(
                color: AppColors.surface,
                border: Border(top: BorderSide(color: AppColors.cocoa, width: 2)),
              ),
              child: SafeArea(
                child: ElevatedButton.icon(
                  icon: const Icon(Icons.assignment_add),
                  label: Text('Apply for a Garden Plot', style: GoogleFonts.quicksand(fontWeight: FontWeight.w800)),
                  onPressed: () => _openApplyDialog(),
                ),
              ),
            )
          : null,
    );
  }

  Widget _buildQuickStat(String label, String value, IconData icon, {Color? color}) {
    return Column(
      children: [
        Icon(icon, size: 20, color: color ?? AppColors.textMuted),
        const SizedBox(height: 4),
        Text(
          value,
          style: GoogleFonts.quicksand(fontSize: 16, fontWeight: FontWeight.w800, color: color ?? AppColors.textMain),
        ),
        Text(label, style: GoogleFonts.nunito(fontSize: 11, color: AppColors.textMuted, fontWeight: FontWeight.w600)),
      ],
    );
  }
}
