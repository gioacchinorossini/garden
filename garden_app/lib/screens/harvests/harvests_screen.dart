import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../models/models.dart';
import '../../services/api_service.dart';
import '../../theme/app_theme.dart';
import '../../widgets/garden_crop_icon.dart';
import '../../widgets/stat_card.dart';

class HarvestsScreen extends StatefulWidget {
  const HarvestsScreen({super.key});

  @override
  State<HarvestsScreen> createState() => _HarvestsScreenState();
}

class _HarvestsScreenState extends State<HarvestsScreen> {
  List<HarvestItem> _harvests = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _fetchHarvests();
  }

  Future<void> _fetchHarvests() async {
    setState(() => _isLoading = true);
    final list = await ApiService().getHarvests();
    if (mounted) {
      setState(() {
        _harvests = list;
        _isLoading = false;
      });
    }
  }

  void _showAddHarvestDialog() {
    final cropController = TextEditingController();
    final qtyController = TextEditingController();
    final plotController = TextEditingController(text: 'Plot A-1');
    final notesController = TextEditingController();

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(26),
          side: const BorderSide(color: AppColors.cocoa, width: 2.5),
        ),
        backgroundColor: AppColors.surface,
        title: Text('Log New Harvest Yield', style: GoogleFonts.quicksand(fontWeight: FontWeight.w800)),
        content: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              TextField(
                controller: cropController,
                decoration: const InputDecoration(labelText: 'Crop Harvested', hintText: 'e.g. Cherry Tomatoes'),
              ),
              const SizedBox(height: 12),
              Row(
                children: [
                  Expanded(
                    child: TextField(
                      controller: qtyController,
                      keyboardType: TextInputType.number,
                      decoration: const InputDecoration(labelText: 'Quantity', suffixText: 'kg'),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: TextField(
                      controller: plotController,
                      decoration: const InputDecoration(labelText: 'Source Plot'),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              TextField(
                controller: notesController,
                maxLines: 2,
                decoration: const InputDecoration(
                  labelText: 'Harvest Notes & Observations',
                  hintText: 'e.g. Crisp texture, morning picking.',
                ),
              ),
            ],
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: Text('Cancel', style: GoogleFonts.quicksand(fontWeight: FontWeight.w700)),
          ),
          ElevatedButton(
            onPressed: () async {
              final crop = cropController.text.trim();
              final qty = double.tryParse(qtyController.text.trim()) ?? 0.0;
              final plot = plotController.text.trim();
              final notes = notesController.text.trim();

              if (crop.isNotEmpty && qty > 0 && plot.isNotEmpty) {
                await ApiService().addHarvest(
                  cropName: crop,
                  quantity: qty,
                  plotNum: plot,
                  notes: notes,
                );
                if (context.mounted) {
                  Navigator.of(context).pop();
                  _fetchHarvests();
                }
              }
            },
            child: const Text('Save Harvest'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final totalYield = _harvests.fold<double>(0.0, (sum, item) => sum + item.quantity);

    return Scaffold(
      backgroundColor: AppColors.canvas,
      appBar: AppBar(
        title: Text('Community Harvest Yields', style: GoogleFonts.quicksand(fontWeight: FontWeight.w800)),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _fetchHarvests,
              child: CustomScrollView(
                slivers: [
                  SliverToBoxAdapter(
                    child: Padding(
                      padding: const EdgeInsets.fromLTRB(16, 14, 16, 12),
                      child: Row(
                        children: [
                          Expanded(
                            child: StatCard(
                              title: 'Total Yield',
                              value: '${totalYield.toStringAsFixed(1)} kg',
                              icon: Icons.scale_rounded,
                              color: AppColors.primary,
                              subtitle: 'Active plot yields',
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: StatCard(
                              title: 'Harvest Entries',
                              value: '${_harvests.length}',
                              icon: Icons.inventory_2_outlined,
                              color: AppColors.honey,
                              subtitle: 'Logged batches',
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),

                  if (_harvests.isEmpty) ...[
                    SliverFillRemaining(
                      child: Center(
                        child: Text(
                          'No harvests recorded yet.',
                          style: GoogleFonts.nunito(fontSize: 14, color: AppColors.textMuted, fontWeight: FontWeight.w600),
                        ),
                      ),
                    ),
                  ] else ...[
                    SliverPadding(
                      padding: const EdgeInsets.fromLTRB(16, 4, 16, 100),
                      sliver: SliverList(
                        delegate: SliverChildBuilderDelegate(
                          (context, index) {
                            final h = _harvests[index];
                            return Container(
                              margin: const EdgeInsets.only(bottom: 12),
                              padding: const EdgeInsets.all(16),
                              decoration: BoxDecoration(
                                color: AppColors.surface,
                                borderRadius: BorderRadius.circular(22),
                                border: Border.all(color: AppColors.cocoa, width: 2.5),
                                boxShadow: AppColors.tactileShadow,
                              ),
                              child: Row(
                                children: [
                                  Container(
                                    padding: const EdgeInsets.all(12),
                                    decoration: BoxDecoration(
                                      color: AppColors.matchaLight,
                                      borderRadius: BorderRadius.circular(16),
                                      border: Border.all(color: AppColors.cocoa, width: 1.5),
                                    ),
                                    child: GardenCropIcon(crop: h.cropName, size: 26),
                                  ),
                                  const SizedBox(width: 14),
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                          h.cropName,
                                          style: GoogleFonts.quicksand(fontSize: 16, fontWeight: FontWeight.w800),
                                        ),
                                        const SizedBox(height: 2),
                                        Text(
                                          '${h.plotNum} • ${h.harvestDate}',
                                          style: GoogleFonts.nunito(fontSize: 12, color: AppColors.textSub, fontWeight: FontWeight.w600),
                                        ),
                                        if (h.notes.isNotEmpty) ...[
                                          const SizedBox(height: 4),
                                          Text(
                                            h.notes,
                                            style: GoogleFonts.nunito(fontSize: 12, color: AppColors.textMuted),
                                          ),
                                        ],
                                      ],
                                    ),
                                  ),
                                  Container(
                                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                                    decoration: BoxDecoration(
                                      color: AppColors.slotBg,
                                      borderRadius: BorderRadius.circular(99),
                                      border: Border.all(color: AppColors.cocoa, width: 1.5),
                                    ),
                                    child: Text(
                                      '+${h.quantity} ${h.unit}',
                                      style: GoogleFonts.quicksand(
                                        fontSize: 14,
                                        fontWeight: FontWeight.w900,
                                        color: AppColors.primary,
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            );
                          },
                          childCount: _harvests.length,
                        ),
                      ),
                    ),
                  ],
                ],
              ),
            ),
      floatingActionButton: Padding(
        padding: const EdgeInsets.only(bottom: 70), // Float above bottom dock
        child: FloatingActionButton.extended(
          backgroundColor: AppColors.primary,
          foregroundColor: AppColors.surface,
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(99),
            side: const BorderSide(color: AppColors.cocoaButtonBorder, width: 2),
          ),
          icon: const Icon(Icons.add_rounded),
          label: Text('Log Yield', style: GoogleFonts.quicksand(fontWeight: FontWeight.w800)),
          onPressed: _showAddHarvestDialog,
        ),
      ),
    );
  }
}
