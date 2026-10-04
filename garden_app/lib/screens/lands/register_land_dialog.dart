import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../services/api_service.dart';
import '../../theme/app_theme.dart';
import '../../widgets/garden_crop_icon.dart';

class RegisterLandDialog extends StatefulWidget {
  final VoidCallback onLandCreated;

  const RegisterLandDialog({super.key, required this.onLandCreated});

  @override
  State<RegisterLandDialog> createState() => _RegisterLandDialogState();
}

class _RegisterLandDialogState extends State<RegisterLandDialog> {
  final _titleController = TextEditingController();
  final _addressController = TextEditingController();
  final _areaController = TextEditingController();
  final _descController = TextEditingController();
  final _cropsController = TextEditingController(text: 'Vegetables, Herbs, Leafy Greens');
  int _plotCount = 4;
  bool _isSubmitting = false;

  static const List<String> _suggestedCrops = [
    'Tomato',
    'Lettuce',
    'Herbs',
    'Carrot',
    'Potato',
    'Pepper',
    'Spinach',
    'Beans',
  ];

  void _toggleCrop(String crop) {
    final current = _cropsController.text
        .split(',')
        .map((e) => e.trim())
        .where((e) => e.isNotEmpty)
        .toList();
    if (current.any((c) => c.toLowerCase() == crop.toLowerCase())) {
      current.removeWhere((c) => c.toLowerCase() == crop.toLowerCase());
    } else {
      current.add(crop);
    }
    setState(() {
      _cropsController.text = current.join(', ');
    });
  }

  @override
  void dispose() {
    _titleController.dispose();
    _addressController.dispose();
    _areaController.dispose();
    _descController.dispose();
    _cropsController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    final title = _titleController.text.trim();
    final address = _addressController.text.trim();
    final area = double.tryParse(_areaController.text.trim()) ?? 0.0;
    final desc = _descController.text.trim();
    final cropsList = _cropsController.text.split(',').map((e) => e.trim()).where((e) => e.isNotEmpty).toList();

    if (title.isEmpty || address.isEmpty || area <= 0) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please fill title, address, and total area.')),
      );
      return;
    }

    setState(() => _isSubmitting = true);

    await ApiService().createLand(
      title: title,
      address: address,
      area: area,
      description: desc,
      crops: cropsList,
      plotCount: _plotCount,
    );

    if (mounted) {
      widget.onLandCreated();
      Navigator.of(context).pop();
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(
          content: Text('Land listed successfully! Available for gardeners to browse.'),
          backgroundColor: AppColors.primary,
        ),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
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
            child: const Icon(Icons.add_location_alt_rounded, color: AppColors.matchaDarkText, size: 22),
          ),
          const SizedBox(width: 12),
          Text(
            'List Idle Land',
            style: GoogleFonts.quicksand(fontSize: 18, fontWeight: FontWeight.w800),
          ),
        ],
      ),
      content: SingleChildScrollView(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Register an idle lot for community gardening and plot leasing.',
              style: GoogleFonts.nunito(fontSize: 13, color: AppColors.textSub, fontWeight: FontWeight.w600),
            ),
            const SizedBox(height: 16),
            TextField(
              controller: _titleController,
              decoration: const InputDecoration(
                labelText: 'Property Display Title',
                hintText: 'e.g. Sunnyvale Community Lot',
              ),
            ),
            const SizedBox(height: 14),
            TextField(
              controller: _addressController,
              decoration: const InputDecoration(
                labelText: 'Complete Address / Location',
                hintText: 'e.g. 124 Green Ave, Sunnyvale',
              ),
            ),
            const SizedBox(height: 14),
            Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _areaController,
                    keyboardType: TextInputType.number,
                    decoration: const InputDecoration(
                      labelText: 'Total Area',
                      suffixText: 'sq.m',
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: DropdownButtonFormField<int>(
                    initialValue: _plotCount,
                    decoration: const InputDecoration(labelText: 'Plots Subdivided'),
                    items: [2, 4, 6, 8, 12].map((n) {
                      return DropdownMenuItem(value: n, child: Text('$n Plots', style: GoogleFonts.quicksand(fontWeight: FontWeight.w700)));
                    }).toList(),
                    onChanged: (val) {
                      if (val != null) setState(() => _plotCount = val);
                    },
                  ),
                ),
              ],
            ),
            const SizedBox(height: 14),
            TextField(
              controller: _cropsController,
              decoration: const InputDecoration(
                labelText: 'Permitted Crops (comma-separated)',
                hintText: 'Vegetables, Herbs, Lettuce',
              ),
            ),
            const SizedBox(height: 8),
            Wrap(
              spacing: 6,
              runSpacing: 6,
              children: _suggestedCrops.map((c) {
                final isSelected = _cropsController.text
                    .toLowerCase()
                    .contains(c.toLowerCase());
                return GestureDetector(
                  onTap: () => _toggleCrop(c),
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                    decoration: BoxDecoration(
                      color: isSelected ? AppColors.matchaLight : AppColors.surface,
                      borderRadius: BorderRadius.circular(99),
                      border: Border.all(
                        color: isSelected ? AppColors.cocoaButtonBorder : AppColors.cocoa,
                        width: 1.5,
                      ),
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        GardenCropIcon(crop: c, size: 14),
                        const SizedBox(width: 4),
                        Text(
                          c,
                          style: GoogleFonts.quicksand(
                            fontSize: 11,
                            fontWeight: FontWeight.w700,
                            color: isSelected ? AppColors.matchaDarkText : AppColors.textMain,
                          ),
                        ),
                      ],
                    ),
                  ),
                );
              }).toList(),
            ),
            const SizedBox(height: 14),
            TextField(
              controller: _descController,
              maxLines: 3,
              decoration: const InputDecoration(
                labelText: 'Lot Description & Soil Condition',
                hintText: 'Describe irrigation, sunlight exposure, soil quality...',
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
          onPressed: _isSubmitting ? null : _submit,
          child: _isSubmitting
              ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
              : Text('List Property', style: GoogleFonts.quicksand(fontWeight: FontWeight.w800)),
        ),
      ],
    );
  }
}
