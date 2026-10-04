import 'package:flutter/material.dart';
import 'package:flutter_svg/flutter_svg.dart';
import '../theme/app_theme.dart';

class GardenIcons {
  static const Map<String, String> _cropMap = {
    'tomato': 'tomato.svg',
    'lettuce': 'romaine.svg',
    'leafy greens': 'romaine.svg',
    'romaine': 'romaine.svg',
    'herbs': 'basil.svg',
    'basil': 'basil.svg',
    'pepper': 'red-bell-pepper.svg',
    'chili': 'birds-eye-chili.svg',
    'carrot': 'carrot.svg',
    'root vegetables': 'carrot.svg',
    'tuber crops': 'russet-potato.svg',
    'potato': 'russet-potato.svg',
    'eggplant': 'eggplant.svg',
    'cucumber': 'cucumber.svg',
    'spinach': 'spinach.svg',
    'greens': 'green-cabbage.svg',
    'cabbage': 'green-cabbage.svg',
    'beans': 'broad-bean.svg',
    'legumes': 'broad-bean.svg',
    'squash': 'yellow-squash.svg',
    'onion': 'red-onion.svg',
    'corn': 'corn.svg',
    'garlic': 'garlic.svg',
    'mushroom': 'generic-mushroom.svg',
    'peas': 'snap-pea.svg',
    'fruits': 'strawberry.svg',
    'strawberry': 'strawberry.svg',
    'broccoli': 'broccoli.svg',
    'radish': 'cherry-bell-radish.svg',
    'kale': 'curly-kale.svg',
    'beet': 'beet.svg',
    'ginger': 'ginger.svg',
    'generic': 'generic-plant.svg',
  };

  static String getCropSvgFileName(String cropName) {
    if (cropName.trim().isEmpty) return 'generic-plant.svg';
    final lower = cropName.toLowerCase().trim();
    for (final entry in _cropMap.entries) {
      if (lower.contains(entry.key) || entry.key.contains(lower)) {
        return entry.value;
      }
    }
    return 'generic-plant.svg';
  }

  static String getCropSvgAsset(String cropName) {
    final fileName = getCropSvgFileName(cropName);
    return 'assets/crops/$fileName';
  }

  static String getTaskPngAsset(String taskType) {
    final t = taskType.toLowerCase().trim();
    if (t.contains('water') || t.contains('irrigation')) return 'assets/images/tasks/watering.png';
    if (t.contains('plant') || t.contains('seed') || t.contains('sow')) return 'assets/images/tasks/planting.png';
    if (t.contains('weed')) return 'assets/images/tasks/weeding.png';
    if (t.contains('prun') || t.contains('trim')) return 'assets/images/tasks/pruning.png';
    if (t.contains('mulch')) return 'assets/images/tasks/mulching.png';
    if (t.contains('fertiliz') || t.contains('compost') || t.contains('soil')) return 'assets/images/tasks/fertilizing.png';
    if (t.contains('trellis') || t.contains('stake')) return 'assets/images/tasks/trellising.png';
    if (t.contains('harvest') || t.contains('pick')) return 'assets/images/tasks/harvesting.png';
    if (t.contains('pest')) return 'assets/images/tasks/pests.png';
    if (t.contains('clean') || t.contains('tool')) return 'assets/images/tasks/cleaning.png';
    return 'assets/images/tasks/planting.png';
  }
}

/// Official Garden Crop Icon from open-crop-icons library
class GardenCropIcon extends StatelessWidget {
  final String crop;
  final double size;
  final Color? badgeBackgroundColor;
  final bool hasCircularBadge;

  const GardenCropIcon({
    super.key,
    required this.crop,
    this.size = 24,
    this.badgeBackgroundColor,
    this.hasCircularBadge = false,
  });

  @override
  Widget build(BuildContext context) {
    final assetPath = GardenIcons.getCropSvgAsset(crop);

    final svgWidget = SvgPicture.asset(
      assetPath,
      width: size,
      height: size,
      fit: BoxFit.contain,
      placeholderBuilder: (context) => SizedBox(
        width: size,
        height: size,
        child: const Icon(Icons.eco_rounded, color: AppColors.primary, size: 16),
      ),
    );

    if (hasCircularBadge) {
      return Container(
        width: size + 10,
        height: size + 10,
        decoration: BoxDecoration(
          color: badgeBackgroundColor ?? Colors.white,
          shape: BoxShape.circle,
          boxShadow: const [
            BoxShadow(
              color: Color(0x20000000),
              offset: Offset(0, 1.5),
              blurRadius: 4,
            ),
          ],
        ),
        alignment: Alignment.center,
        child: svgWidget,
      );
    }

    return svgWidget;
  }
}
