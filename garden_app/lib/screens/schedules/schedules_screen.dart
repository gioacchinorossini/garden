import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../models/models.dart';
import '../../services/api_service.dart';
import '../../theme/app_theme.dart';
import '../../widgets/garden_crop_icon.dart';
import '../../widgets/status_badge.dart';

class SchedulesScreen extends StatefulWidget {
  const SchedulesScreen({super.key});

  @override
  State<SchedulesScreen> createState() => _SchedulesScreenState();
}

class _SchedulesScreenState extends State<SchedulesScreen> {
  List<ScheduleItem> _tasks = [];
  bool _isLoading = true;
  String _selectedCategory = 'all';

  final List<Map<String, dynamic>> _categories = [
    {'id': 'all', 'label': 'All Tasks'},
    {'id': 'watering', 'label': 'Watering', 'icon': Icons.water_drop_outlined},
    {'id': 'planting', 'label': 'Planting', 'icon': Icons.grass_outlined},
    {'id': 'weeding', 'label': 'Weeding', 'icon': Icons.content_cut_outlined},
    {'id': 'harvesting', 'label': 'Harvesting', 'icon': Icons.shopping_basket_outlined},
  ];

  @override
  void initState() {
    super.initState();
    _fetchSchedules();
  }

  Future<void> _fetchSchedules() async {
    setState(() => _isLoading = true);
    final list = await ApiService().getSchedules();
    if (mounted) {
      setState(() {
        _tasks = list;
        _isLoading = false;
      });
    }
  }

  Color _getTaskColor(String type) {
    switch (type.toLowerCase()) {
      case 'watering':
        return const Color(0xFF0284C7);
      case 'planting':
        return AppColors.primary;
      case 'weeding':
        return const Color(0xFFD97706);
      case 'harvesting':
        return const Color(0xFF8B5CF6);
      default:
        return AppColors.primary;
    }
  }

  void _showAddTaskDialog() {
    final titleController = TextEditingController();
    final descController = TextEditingController();
    final landController = TextEditingController(text: 'Sunnyvale Lot A');
    String taskType = 'watering';

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(26),
          side: const BorderSide(color: AppColors.cocoa, width: 2.5),
        ),
        backgroundColor: AppColors.surface,
        title: Text('Schedule Garden Task', style: GoogleFonts.quicksand(fontWeight: FontWeight.w800)),
        content: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              TextField(
                controller: titleController,
                decoration: const InputDecoration(labelText: 'Task Title', hintText: 'e.g. Afternoon Soil Aeration'),
              ),
              const SizedBox(height: 12),
              DropdownButtonFormField<String>(
                initialValue: taskType,
                decoration: const InputDecoration(labelText: 'Task Category'),
                items: ['watering', 'planting', 'weeding', 'harvesting', 'meeting'].map((t) {
                  return DropdownMenuItem(value: t, child: Text(t.toUpperCase(), style: GoogleFonts.quicksand(fontWeight: FontWeight.w700)));
                }).toList(),
                onChanged: (val) {
                  if (val != null) taskType = val;
                },
              ),
              const SizedBox(height: 12),
              TextField(
                controller: landController,
                decoration: const InputDecoration(labelText: 'Location / Parcel'),
              ),
              const SizedBox(height: 12),
              TextField(
                controller: descController,
                maxLines: 2,
                decoration: const InputDecoration(labelText: 'Task Instructions'),
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
            onPressed: () {
              if (titleController.text.trim().isNotEmpty) {
                setState(() {
                  _tasks.insert(
                    0,
                    ScheduleItem(
                      id: _tasks.length + 1,
                      landTitle: landController.text.trim(),
                      gardener: 'You',
                      title: titleController.text.trim(),
                      description: descController.text.trim(),
                      startTime: 'Today, 04:00 PM',
                      endTime: 'Today, 05:30 PM',
                      taskType: taskType,
                      status: 'pending',
                      difficulty: 'medium',
                      xp: 150,
                    ),
                  );
                });
                Navigator.of(context).pop();
              }
            },
            child: const Text('Add Task'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final filtered = _tasks.where((t) {
      if (_selectedCategory == 'all') return true;
      return t.taskType.toLowerCase() == _selectedCategory.toLowerCase();
    }).toList();

    return Scaffold(
      backgroundColor: AppColors.canvas,
      appBar: AppBar(
        title: Text('Garden Tasks & Schedule', style: GoogleFonts.quicksand(fontWeight: FontWeight.w800)),
      ),
      body: RefreshIndicator(
        onRefresh: _fetchSchedules,
        child: Column(
          children: [
            // Category Chips Bar
            SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.fromLTRB(16, 12, 16, 8),
              child: Row(
                children: _categories.map((cat) {
                  final isSelected = _selectedCategory == cat['id'];
                  return Padding(
                    padding: const EdgeInsets.only(right: 8),
                    child: GestureDetector(
                      onTap: () => setState(() => _selectedCategory = cat['id']),
                      child: AnimatedContainer(
                        duration: const Duration(milliseconds: 150),
                        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 7),
                        decoration: BoxDecoration(
                          color: isSelected ? AppColors.primary : AppColors.surface,
                          borderRadius: BorderRadius.circular(99),
                          border: Border.all(
                            color: isSelected ? AppColors.cocoaButtonBorder : AppColors.cocoa,
                            width: 2,
                          ),
                          boxShadow: [
                            BoxShadow(
                              color: isSelected ? AppColors.cocoaButtonBorder : AppColors.cocoa,
                              offset: const Offset(0, 3),
                              blurRadius: 0,
                            ),
                          ],
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            if (cat['id'] == 'all') ...[
                              Icon(
                                Icons.tune_rounded,
                                size: 14,
                                color: isSelected ? AppColors.surface : AppColors.primary,
                              ),
                              const SizedBox(width: 6),
                            ] else ...[
                              Image.asset(
                                GardenIcons.getTaskPngAsset(cat['id'] as String),
                                width: 16,
                                height: 16,
                                fit: BoxFit.contain,
                              ),
                              const SizedBox(width: 6),
                            ],
                            Text(
                              cat['label'] as String,
                              style: GoogleFonts.quicksand(
                                fontSize: 12,
                                fontWeight: FontWeight.w800,
                                color: isSelected ? AppColors.surface : AppColors.textMain,
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

            Expanded(
              child: _isLoading
                  ? const Center(child: CircularProgressIndicator())
                  : filtered.isEmpty
                      ? Center(
                          child: Text(
                            'No scheduled tasks for this category',
                            style: GoogleFonts.nunito(fontSize: 14, color: AppColors.textMuted, fontWeight: FontWeight.w600),
                          ),
                        )
                      : ListView.builder(
                          padding: const EdgeInsets.fromLTRB(16, 8, 16, 100),
                          itemCount: filtered.length,
                          itemBuilder: (context, i) {
                            final task = filtered[i];
                            final color = _getTaskColor(task.taskType);

                            return Container(
                              margin: const EdgeInsets.only(bottom: 12),
                              padding: const EdgeInsets.all(16),
                              decoration: BoxDecoration(
                                color: AppColors.surface,
                                borderRadius: BorderRadius.circular(22),
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
                                        padding: const EdgeInsets.all(8),
                                        decoration: BoxDecoration(
                                          color: color.withValues(alpha: 0.15),
                                          borderRadius: BorderRadius.circular(14),
                                          border: Border.all(color: AppColors.cocoa, width: 1.5),
                                        ),
                                        child: Image.asset(
                                          GardenIcons.getTaskPngAsset(task.taskType),
                                          width: 24,
                                          height: 24,
                                          fit: BoxFit.contain,
                                        ),
                                      ),
                                      const SizedBox(width: 12),
                                      Expanded(
                                        child: Column(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          children: [
                                            Text(
                                              task.title,
                                              style: GoogleFonts.quicksand(fontSize: 16, fontWeight: FontWeight.w800),
                                            ),
                                            const SizedBox(height: 2),
                                            Text(
                                              '${task.landTitle} • ${task.startTime}',
                                              style: GoogleFonts.nunito(fontSize: 12, color: AppColors.textSub, fontWeight: FontWeight.w600),
                                            ),
                                          ],
                                        ),
                                      ),
                                      StatusBadge(status: task.status),
                                    ],
                                  ),
                                  if (task.description.isNotEmpty) ...[
                                    const SizedBox(height: 10),
                                    Text(
                                      task.description,
                                      style: GoogleFonts.nunito(fontSize: 13, color: AppColors.textSub, height: 1.4, fontWeight: FontWeight.w600),
                                    ),
                                  ],
                                  const SizedBox(height: 12),
                                  Row(
                                    children: [
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
                                        decoration: BoxDecoration(
                                          color: AppColors.matchaLight,
                                          borderRadius: BorderRadius.circular(99),
                                          border: Border.all(color: AppColors.cocoa, width: 1.5),
                                        ),
                                        child: Text(
                                          '+${task.xp} XP',
                                          style: GoogleFonts.quicksand(fontSize: 11, fontWeight: FontWeight.w800, color: AppColors.matchaDarkText),
                                        ),
                                      ),
                                      const SizedBox(width: 8),
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
                                        decoration: BoxDecoration(
                                          color: AppColors.slotBg,
                                          borderRadius: BorderRadius.circular(99),
                                          border: Border.all(color: AppColors.cocoa, width: 1.5),
                                        ),
                                        child: Text(
                                          task.difficulty.toUpperCase(),
                                          style: GoogleFonts.quicksand(fontSize: 11, fontWeight: FontWeight.w800, color: AppColors.textSub),
                                        ),
                                      ),
                                    ],
                                  ),
                                ],
                              ),
                            );
                          },
                        ),
            ),
          ],
        ),
      ),
      floatingActionButton: Padding(
        padding: const EdgeInsets.only(bottom: 70), // Float above bottom dock
        child: FloatingActionButton(
          backgroundColor: AppColors.primary,
          foregroundColor: AppColors.surface,
          shape: const CircleBorder(side: BorderSide(color: AppColors.cocoaButtonBorder, width: 2)),
          onPressed: _showAddTaskDialog,
          child: const Icon(Icons.add_task_rounded),
        ),
      ),
    );
  }
}
