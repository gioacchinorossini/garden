import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../models/models.dart';
import '../../services/api_service.dart';
import '../../theme/app_theme.dart';
import '../../widgets/garden_crop_icon.dart';

class ApplyPlotDialog extends StatefulWidget {
  final Land land;
  final Plot? selectedPlot;
  final VoidCallback onRequestSubmitted;

  const ApplyPlotDialog({
    super.key,
    required this.land,
    this.selectedPlot,
    required this.onRequestSubmitted,
  });

  @override
  State<ApplyPlotDialog> createState() => _ApplyPlotDialogState();
}

class _ApplyPlotDialogState extends State<ApplyPlotDialog> {
  int? _selectedPlotId;
  final _purposeController = TextEditingController();
  String _selectedDuration = '6 months';
  bool _isSubmitting = false;

  @override
  void initState() {
    super.initState();
    if (widget.selectedPlot != null) {
      _selectedPlotId = widget.selectedPlot!.id;
    } else {
      final available = widget.land.plots.where((p) => p.isAvailable).toList();
      if (available.isNotEmpty) {
        _selectedPlotId = available.first.id;
      }
    }
  }

  @override
  void dispose() {
    _purposeController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    final purpose = _purposeController.text.trim();
    if (_selectedPlotId == null || purpose.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please select a plot and explain your gardening purpose.')),
      );
      return;
    }

    setState(() => _isSubmitting = true);

    await ApiService().submitRequest(
      landId: widget.land.id,
      plotId: _selectedPlotId!,
      purpose: purpose,
      duration: _selectedDuration,
    );

    if (mounted) {
      widget.onRequestSubmitted();
      Navigator.of(context).pop();
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Application submitted! The landowner has been notified.'),
          backgroundColor: AppColors.primary,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final availablePlots = widget.land.plots.where((p) => p.isAvailable).toList();

    return AlertDialog(
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(26),
        side: const BorderSide(color: AppColors.cocoa, width: 2.5),
      ),
      backgroundColor: AppColors.surface,
      title: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: AppColors.matchaLight,
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: AppColors.cocoa, width: 1.5),
            ),
            child: GardenCropIcon(crop: widget.land.crops, size: 22),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              'Apply for Plot',
              style: GoogleFonts.quicksand(fontSize: 18, fontWeight: FontWeight.w800),
            ),
          ),
        ],
      ),
      content: SingleChildScrollView(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Property: ${widget.land.title}',
              style: GoogleFonts.quicksand(fontSize: 14, fontWeight: FontWeight.w800, color: AppColors.textMain),
            ),
            const SizedBox(height: 14),

            if (availablePlots.isEmpty) ...[
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: const Color(0xFFFEE2E2),
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: AppColors.coral, width: 1.5),
                ),
                child: Text(
                  'No plots are currently available in this lot.',
                  style: GoogleFonts.nunito(color: AppColors.coral, fontSize: 13, fontWeight: FontWeight.w700),
                ),
              ),
            ] else ...[
              DropdownButtonFormField<int>(
                initialValue: _selectedPlotId,
                decoration: const InputDecoration(labelText: 'Select Available Plot'),
                items: availablePlots.map((plot) {
                  return DropdownMenuItem(
                    value: plot.id,
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        if (plot.crop.isNotEmpty) ...[
                          GardenCropIcon(crop: plot.crop, size: 16),
                          const SizedBox(width: 6),
                        ],
                        Text('${plot.plotNumber} (${plot.area} sq.m)', style: GoogleFonts.quicksand(fontWeight: FontWeight.w700)),
                      ],
                    ),
                  );
                }).toList(),
                onChanged: (val) => setState(() => _selectedPlotId = val),
              ),
              const SizedBox(height: 14),

              DropdownButtonFormField<String>(
                initialValue: _selectedDuration,
                decoration: const InputDecoration(labelText: 'Intended Lease Duration'),
                items: ['3 months', '6 months', '1 year', '2 years'].map((d) {
                  return DropdownMenuItem(value: d, child: Text(d, style: GoogleFonts.quicksand(fontWeight: FontWeight.w700)));
                }).toList(),
                onChanged: (val) {
                  if (val != null) setState(() => _selectedDuration = val);
                },
              ),
              const SizedBox(height: 14),

              TextField(
                controller: _purposeController,
                maxLines: 3,
                decoration: const InputDecoration(
                  labelText: 'Gardening Purpose & Crops Intended',
                  hintText: 'e.g. Planting bell peppers and herbs for local community soup kitchen.',
                ),
              ),
            ],
          ],
        ),
      ),
      actions: [
        TextButton(
          onPressed: () => Navigator.of(context).pop(),
          child: Text('Cancel', style: GoogleFonts.quicksand(fontWeight: FontWeight.w700)),
        ),
        if (availablePlots.isNotEmpty)
          ElevatedButton(
            onPressed: _isSubmitting ? null : _submit,
            child: _isSubmitting
                ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                : Text('Submit Application', style: GoogleFonts.quicksand(fontWeight: FontWeight.w800)),
          ),
      ],
    );
  }
}
