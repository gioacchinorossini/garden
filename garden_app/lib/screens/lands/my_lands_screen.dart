import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../models/models.dart';
import '../../services/api_service.dart';
import '../../theme/app_theme.dart';
import '../../widgets/garden_crop_icon.dart';
import '../../widgets/status_badge.dart';
import 'land_detail_screen.dart';
import 'register_land_dialog.dart';

class MyLandsScreen extends StatefulWidget {
  final VoidCallback onOpenProfile;

  const MyLandsScreen({super.key, required this.onOpenProfile});

  @override
  State<MyLandsScreen> createState() => _MyLandsScreenState();
}

class _MyLandsScreenState extends State<MyLandsScreen> {
  List<Land> _lands = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _fetchLands();
  }

  Future<void> _fetchLands() async {
    setState(() => _isLoading = true);
    final list = await ApiService().getLands();
    if (mounted) {
      setState(() {
        _lands = list;
        _isLoading = false;
      });
    }
  }

  void _openRegisterDialog() {
    showDialog(
      context: context,
      builder: (context) => RegisterLandDialog(
        onLandCreated: _fetchLands,
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.canvas,
      appBar: AppBar(
        title: Text('My Registered Lands', style: GoogleFonts.quicksand(fontWeight: FontWeight.w800)),
        actions: [
          IconButton(
            icon: const Icon(Icons.add_location_alt_rounded, color: AppColors.primary),
            tooltip: 'Register Land',
            onPressed: _openRegisterDialog,
          ),
          IconButton(
            icon: const Icon(Icons.person_outline_rounded, color: AppColors.textMain),
            tooltip: 'Account Profile',
            onPressed: widget.onOpenProfile,
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _fetchLands,
              child: _lands.isEmpty
                  ? Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Container(
                            padding: const EdgeInsets.all(16),
                            decoration: BoxDecoration(
                              color: AppColors.matchaLight,
                              shape: BoxShape.circle,
                              border: Border.all(color: AppColors.cocoa, width: 2),
                            ),
                            child: const Icon(Icons.park_rounded, size: 40, color: AppColors.matchaDarkText),
                          ),
                          const SizedBox(height: 16),
                          Text(
                            'No Registered Lands Yet',
                            style: GoogleFonts.quicksand(fontSize: 18, fontWeight: FontWeight.w800),
                          ),
                          const SizedBox(height: 6),
                          Text(
                            'List an idle property to partition plots for gardeners.',
                            style: GoogleFonts.nunito(fontSize: 13, color: AppColors.textSub, fontWeight: FontWeight.w600),
                          ),
                          const SizedBox(height: 18),
                          ElevatedButton.icon(
                            onPressed: _openRegisterDialog,
                            icon: const Icon(Icons.add_rounded, size: 18),
                            label: const Text('Register First Land'),
                          ),
                        ],
                      ),
                    )
                  : ListView.builder(
                      padding: const EdgeInsets.fromLTRB(16, 12, 16, 100),
                      itemCount: _lands.length,
                      itemBuilder: (context, i) {
                        final land = _lands[i];
                        final availCount = land.plots.where((p) => p.isAvailable).length;

                        return Container(
                          margin: const EdgeInsets.only(bottom: 14),
                          decoration: BoxDecoration(
                            color: AppColors.surface,
                            borderRadius: BorderRadius.circular(22),
                            border: Border.all(color: AppColors.cocoa, width: 2.5),
                            boxShadow: AppColors.tactileShadow,
                          ),
                          child: InkWell(
                            borderRadius: BorderRadius.circular(20),
                            onTap: () {
                              Navigator.of(context).push(
                                MaterialPageRoute(
                                  builder: (_) => LandDetailScreen(
                                    land: land,
                                    onDataChanged: _fetchLands,
                                  ),
                                ),
                              );
                            },
                            child: Padding(
                              padding: const EdgeInsets.all(16),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Container(
                                        padding: const EdgeInsets.all(8),
                                        decoration: BoxDecoration(
                                          color: AppColors.matchaLight,
                                          borderRadius: BorderRadius.circular(14),
                                          border: Border.all(color: AppColors.cocoa, width: 1.5),
                                        ),
                                        child: GardenCropIcon(crop: land.crops, size: 28),
                                      ),
                                      const SizedBox(width: 12),
                                      Expanded(
                                        child: Column(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          children: [
                                            Text(
                                              land.title,
                                              style: GoogleFonts.quicksand(fontSize: 17, fontWeight: FontWeight.w800),
                                            ),
                                            const SizedBox(height: 2),
                                            Text(
                                              land.address,
                                              style: GoogleFonts.nunito(fontSize: 12, color: AppColors.textSub, fontWeight: FontWeight.w600),
                                            ),
                                          ],
                                        ),
                                      ),
                                      StatusBadge(status: land.status),
                                    ],
                                  ),
                                  const SizedBox(height: 14),
                                  const Divider(color: AppColors.borderSubtle, height: 1, thickness: 1.2),
                                  const SizedBox(height: 12),
                                  Row(
                                    children: [
                                      Expanded(
                                        child: Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                                          decoration: BoxDecoration(
                                            color: AppColors.slotBg,
                                            borderRadius: BorderRadius.circular(12),
                                            border: Border.all(color: AppColors.borderSubtle, width: 1),
                                          ),
                                          child: Column(
                                            crossAxisAlignment: CrossAxisAlignment.start,
                                            children: [
                                              Text(
                                                'TOTAL LOT AREA',
                                                style: GoogleFonts.quicksand(fontSize: 9, fontWeight: FontWeight.w800, color: AppColors.textMuted),
                                              ),
                                              const SizedBox(height: 2),
                                              Text(
                                                '${land.area} m²',
                                                style: GoogleFonts.quicksand(fontSize: 14, fontWeight: FontWeight.w900, color: AppColors.textMain),
                                              ),
                                            ],
                                          ),
                                        ),
                                      ),
                                      const SizedBox(width: 8),
                                      Expanded(
                                        child: Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
                                          decoration: BoxDecoration(
                                            color: AppColors.matchaLight.withValues(alpha: 0.5),
                                            borderRadius: BorderRadius.circular(12),
                                            border: Border.all(color: AppColors.cocoa, width: 1),
                                          ),
                                          child: Column(
                                            crossAxisAlignment: CrossAxisAlignment.start,
                                            children: [
                                              Text(
                                                'AVAILABLE PLOTS',
                                                style: GoogleFonts.quicksand(fontSize: 9, fontWeight: FontWeight.w800, color: AppColors.matchaDarkText),
                                              ),
                                              const SizedBox(height: 2),
                                              Text(
                                                '$availCount of ${land.plots.isNotEmpty ? land.plots.length : land.totalPlots} Lots',
                                                style: GoogleFonts.quicksand(fontSize: 13, fontWeight: FontWeight.w900, color: AppColors.primary),
                                              ),
                                            ],
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                ],
                              ),
                            ),
                          ),
                        );
                      },
                    ),
            ),
    );
  }
}
