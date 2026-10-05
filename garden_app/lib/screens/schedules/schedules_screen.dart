import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../models/models.dart';
import '../../services/api_service.dart';
import '../../services/auth_state.dart';
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
    final isLandowner = AuthState().currentUser?.isLandowner ?? false;
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
                                    crossAxisAlignment: CrossAxisAlignment.center,
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
                                      const SizedBox(width: 8),
                                      Row(
                                        mainAxisSize: MainAxisSize.min,
                                        children: [
                                          Container(
                                            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
                                            decoration: BoxDecoration(
                                              color: const Color(0xFFFEE7AA),
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
                                                const Icon(Icons.bolt_rounded, size: 12, color: Color(0xFFE28C00)),
                                                const SizedBox(width: 2),
                                                Text(
                                                  '+${task.xp} XP',
                                                  style: GoogleFonts.quicksand(
                                                    fontSize: 11,
                                                    fontWeight: FontWeight.w800,
                                                    color: const Color(0xFF4A3528),
                                                  ),
                                                ),
                                              ],
                                            ),
                                          ),
                                          if (task.status.toLowerCase() == 'completed') ...[
                                            const SizedBox(width: 6),
                                            const StatusBadge(status: 'completed'),
                                          ],
                                        ],
                                      ),
                                    ],
                                  ),
                                  if (task.description.isNotEmpty) ...[
                                    const SizedBox(height: 10),
                                    Text(
                                      task.description,
                                      style: GoogleFonts.nunito(fontSize: 13, color: AppColors.textSub, height: 1.4, fontWeight: FontWeight.w600),
                                    ),
                                  ],
                                  const SizedBox(height: 14),
                                  Row(
                                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                    children: [
                                      Text(
                                        task.endTime.isNotEmpty ? 'Due: ${task.endTime}' : task.startTime,
                                        style: GoogleFonts.nunito(fontSize: 11, color: AppColors.textMuted, fontWeight: FontWeight.w700),
                                      ),
                                      if (!task.isCompleted)
                                        ElevatedButton.icon(
                                          style: ElevatedButton.styleFrom(
                                            backgroundColor: AppColors.primary,
                                            foregroundColor: AppColors.surface,
                                            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 7),
                                            shape: RoundedRectangleBorder(
                                              borderRadius: BorderRadius.circular(99),
                                              side: const BorderSide(color: AppColors.cocoaButtonBorder, width: 2),
                                            ),
                                            elevation: 0,
                                          ),
                                          onPressed: () => _showCompleteQuestDialog(task),
                                          icon: Icon(
                                            isLandowner ? Icons.verified_user_rounded : Icons.check_circle_rounded,
                                            size: 15,
                                          ),
                                          label: Text(
                                            isLandowner ? 'Mark Complete' : 'Complete Quest',
                                            style: GoogleFonts.quicksand(fontSize: 12, fontWeight: FontWeight.w800),
                                          ),
                                        )
                                      else
                                        OutlinedButton.icon(
                                          style: OutlinedButton.styleFrom(
                                            backgroundColor: AppColors.surface,
                                            foregroundColor: AppColors.textMain,
                                            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 7),
                                            shape: RoundedRectangleBorder(
                                              borderRadius: BorderRadius.circular(99),
                                              side: const BorderSide(color: AppColors.cocoa, width: 1.5),
                                            ),
                                          ),
                                          onPressed: () => _showViewQuestDialog(task),
                                          icon: const Icon(Icons.visibility_rounded, size: 15, color: AppColors.textSub),
                                          label: Text(
                                            'View Quest',
                                            style: GoogleFonts.quicksand(fontSize: 12, fontWeight: FontWeight.w800),
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

  void _showCompleteQuestDialog(ScheduleItem task) {
    final auth = AuthState();
    final isLandowner = auth.currentUser?.isLandowner ?? false;
    final notesController = TextEditingController();
    String? selectedProofImage;

    showDialog(
      context: context,
      builder: (dialogCtx) => StatefulBuilder(
        builder: (context, setModalState) {
          final isGardener = !isLandowner;

          return AlertDialog(
            backgroundColor: AppColors.surface,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(24),
              side: const BorderSide(color: AppColors.cocoa, width: 2.5),
            ),
            title: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 3),
                      decoration: BoxDecoration(
                        color: AppColors.matchaLight,
                        borderRadius: BorderRadius.circular(99),
                        border: Border.all(color: AppColors.cocoa, width: 1.5),
                      ),
                      child: Text(
                        task.taskType.toUpperCase(),
                        style: GoogleFonts.quicksand(fontSize: 11, fontWeight: FontWeight.w800, color: AppColors.matchaDarkText),
                      ),
                    ),
                    Text('+${task.xp} XP', style: GoogleFonts.quicksand(fontSize: 12, fontWeight: FontWeight.w900, color: AppColors.primary)),
                  ],
                ),
                const SizedBox(height: 8),
                Text(
                  isLandowner ? 'Mark Quest Complete' : 'Complete Quest',
                  style: GoogleFonts.quicksand(fontWeight: FontWeight.w900, fontSize: 18),
                ),
                Text(
                  task.title,
                  style: GoogleFonts.nunito(fontSize: 13, color: AppColors.textSub, fontWeight: FontWeight.w600),
                ),
              ],
            ),
            content: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  if (isLandowner) ...[
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: AppColors.matchaLight.withValues(alpha: 0.5),
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(color: AppColors.cocoa, width: 1.5),
                      ),
                      child: Row(
                        children: [
                          const Icon(Icons.verified_user_rounded, color: AppColors.matchaDarkText, size: 24),
                          const SizedBox(width: 10),
                          Expanded(
                            child: Text(
                              'As landowner, you are marking this quest complete for ${task.gardener ?? 'the assigned gardener'}. Only gardeners upload proof photos.',
                              style: GoogleFonts.nunito(fontSize: 12, color: AppColors.matchaDarkText, fontWeight: FontWeight.w700),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ] else ...[
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: const Color(0xFFFEF3C7),
                        borderRadius: BorderRadius.circular(16),
                        border: Border.all(color: AppColors.cocoa, width: 1.5),
                      ),
                      child: Row(
                        children: [
                          const Icon(Icons.camera_alt_rounded, color: Color(0xFF92400E), size: 24),
                          const SizedBox(width: 10),
                          Expanded(
                            child: Text(
                              'Photo proof required: As the gardener, attach a photo of your completed work to earn XP.',
                              style: GoogleFonts.nunito(fontSize: 12, color: const Color(0xFF92400E), fontWeight: FontWeight.w700),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 14),
                    Text(
                      'WORK PROOF PHOTO *',
                      style: GoogleFonts.quicksand(fontSize: 11, fontWeight: FontWeight.w800, color: AppColors.textSub),
                    ),
                    const SizedBox(height: 6),
                    GestureDetector(
                      onTap: () {
                        setModalState(() {
                          selectedProofImage = selectedProofImage == null
                              ? GardenIcons.getTaskPngAsset(task.taskType)
                              : null;
                        });
                      },
                      child: Container(
                        width: double.infinity,
                        height: 120,
                        decoration: BoxDecoration(
                          color: AppColors.slotBg,
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(color: AppColors.cocoa, width: 1.5, strokeAlign: BorderSide.strokeAlignInside),
                        ),
                        child: selectedProofImage != null
                            ? Stack(
                                alignment: Alignment.center,
                                children: [
                                  Image.asset(selectedProofImage!, width: 70, height: 70, fit: BoxFit.contain),
                                  Positioned(
                                    top: 6,
                                    right: 6,
                                    child: Container(
                                      padding: const EdgeInsets.all(4),
                                      decoration: const BoxDecoration(color: AppColors.coral, shape: BoxShape.circle),
                                      child: const Icon(Icons.close, size: 14, color: Colors.white),
                                    ),
                                  ),
                                  Positioned(
                                    bottom: 6,
                                    child: Container(
                                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                      decoration: BoxDecoration(color: Colors.black54, borderRadius: BorderRadius.circular(99)),
                                      child: Text('Proof Attached ✓', style: GoogleFonts.quicksand(fontSize: 10, color: Colors.white, fontWeight: FontWeight.w700)),
                                    ),
                                  ),
                                ],
                              )
                            : Column(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  const Icon(Icons.add_a_photo_rounded, size: 28, color: AppColors.textSub),
                                  const SizedBox(height: 6),
                                  Text(
                                    'Tap to attach work proof photo',
                                    style: GoogleFonts.quicksand(fontSize: 12, fontWeight: FontWeight.w700, color: AppColors.textMain),
                                  ),
                                  Text(
                                    'Select task verification photo',
                                    style: GoogleFonts.nunito(fontSize: 11, color: AppColors.textMuted),
                                  ),
                                ],
                              ),
                      ),
                    ),
                  ],
                  const SizedBox(height: 12),
                  TextField(
                    controller: notesController,
                    maxLines: 2,
                    decoration: InputDecoration(
                      labelText: isLandowner ? 'Verification Notes (Optional)' : 'Completion Notes (Optional)',
                      hintText: isLandowner ? 'e.g., Inspected and verified.' : 'e.g., Watered all beds thoroughly.',
                    ),
                  ),
                ],
              ),
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.of(dialogCtx).pop(),
                child: Text('Cancel', style: GoogleFonts.quicksand(fontWeight: FontWeight.w700)),
              ),
              ElevatedButton(
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppColors.primary,
                  foregroundColor: AppColors.surface,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(99),
                    side: const BorderSide(color: AppColors.cocoaButtonBorder, width: 2),
                  ),
                ),
                onPressed: (isGardener && selectedProofImage == null)
                    ? null
                    : () async {
                        final messenger = ScaffoldMessenger.of(context);
                        Navigator.of(dialogCtx).pop();
                        final role = isLandowner ? 'landowner' : 'gardener';
                        final finalProofImage = isLandowner ? null : selectedProofImage;
                        await ApiService().completeSchedule(
                          id: task.id,
                          role: role,
                          completedBy: auth.currentUser?.name,
                          proofImage: finalProofImage,
                          notes: notesController.text.trim(),
                        );
                        if (!mounted) return;
                        setState(() {
                          final idx = _tasks.indexWhere((t) => t.id == task.id);
                          if (idx != -1) {
                            _tasks[idx] = ScheduleItem(
                              id: task.id,
                              landTitle: task.landTitle,
                              gardener: task.gardener,
                              title: task.title,
                              description: task.description,
                              startTime: task.startTime,
                              endTime: task.endTime,
                              taskType: task.taskType,
                              status: 'completed',
                              difficulty: task.difficulty,
                              xp: task.xp,
                              proofImage: finalProofImage,
                              completedBy: auth.currentUser?.name ?? (isLandowner ? 'Landowner' : 'Gardener'),
                              completedAt: 'Just now',
                              completionNotes: notesController.text.trim(),
                            );
                          }
                        });
                        messenger.showSnackBar(
                          SnackBar(
                            content: Text(
                              isLandowner
                                  ? 'Quest marked as completed by Landowner!'
                                  : 'Quest completed! +${task.xp} XP earned!',
                              style: GoogleFonts.quicksand(fontWeight: FontWeight.w700),
                            ),
                            backgroundColor: AppColors.primary,
                          ),
                        );
                      },
                child: Text(
                  isLandowner ? 'Mark Completed' : 'Submit Proof & Complete',
                  style: GoogleFonts.quicksand(fontWeight: FontWeight.w800),
                ),
              ),
            ],
          );
        },
      ),
    );
  }

  void _showViewQuestDialog(ScheduleItem task) {
    showDialog(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: AppColors.surface,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(24),
          side: const BorderSide(color: AppColors.cocoa, width: 2.5),
        ),
        title: Row(
          children: [
            const StatusBadge(status: 'completed'),
            const Spacer(),
            Text('+${task.xp} XP', style: GoogleFonts.quicksand(fontWeight: FontWeight.w900, color: AppColors.primary)),
          ],
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(task.title, style: GoogleFonts.quicksand(fontSize: 17, fontWeight: FontWeight.w900)),
            const SizedBox(height: 2),
            Text('${task.landTitle} • ${task.startTime}', style: GoogleFonts.nunito(fontSize: 12, color: AppColors.textSub)),
            if (task.description.isNotEmpty) ...[
              const SizedBox(height: 10),
              Text(task.description, style: GoogleFonts.nunito(fontSize: 13, color: AppColors.textSub)),
            ],
            const SizedBox(height: 14),
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: AppColors.slotBg,
                borderRadius: BorderRadius.circular(16),
                border: Border.all(color: AppColors.cocoa, width: 1.5),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      const Icon(Icons.check_circle_rounded, color: AppColors.primary, size: 18),
                      const SizedBox(width: 8),
                      Text(
                        'Completed by: ${task.completedBy ?? task.gardener ?? 'Gardener'}',
                        style: GoogleFonts.quicksand(fontSize: 12, fontWeight: FontWeight.w800, color: AppColors.textMain),
                      ),
                    ],
                  ),
                  if (task.completionNotes != null && task.completionNotes!.isNotEmpty) ...[
                    const SizedBox(height: 6),
                    Text(
                      'Notes: ${task.completionNotes}',
                      style: GoogleFonts.nunito(fontSize: 12, color: AppColors.textSub, fontStyle: FontStyle.italic),
                    ),
                  ],
                ],
              ),
            ),
            if (task.proofImage != null && task.proofImage!.isNotEmpty) ...[
              const SizedBox(height: 12),
              Text(
                'GARDENER PROOF PHOTO',
                style: GoogleFonts.quicksand(fontSize: 10, fontWeight: FontWeight.w800, color: AppColors.textSub),
              ),
              const SizedBox(height: 6),
              ClipRRect(
                borderRadius: BorderRadius.circular(14),
                child: Container(
                  width: double.infinity,
                  height: 130,
                  decoration: BoxDecoration(
                    color: AppColors.slotBg,
                    border: Border.all(color: AppColors.cocoa, width: 1.5),
                    borderRadius: BorderRadius.circular(14),
                  ),
                  child: task.proofImage!.startsWith('http') || task.proofImage!.startsWith('uploads/')
                      ? Image.network(
                          task.proofImage!.startsWith('http') ? task.proofImage! : '${ApiService().baseUrl}/${task.proofImage!}',
                          fit: BoxFit.cover,
                          errorBuilder: (context, error, stackTrace) => Image.asset(
                            GardenIcons.getTaskPngAsset(task.taskType),
                            fit: BoxFit.contain,
                          ),
                        )
                      : Image.asset(task.proofImage!, fit: BoxFit.contain),
                ),
              ),
            ] else ...[
              const SizedBox(height: 10),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                decoration: BoxDecoration(
                  color: AppColors.matchaLight.withValues(alpha: 0.4),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Row(
                  children: [
                    const Icon(Icons.verified_user_rounded, size: 14, color: AppColors.matchaDarkText),
                    const SizedBox(width: 6),
                    Expanded(
                      child: Text(
                        'Marked completed directly by Landowner (no photo required).',
                        style: GoogleFonts.nunito(fontSize: 11, color: AppColors.matchaDarkText, fontWeight: FontWeight.w700),
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(ctx).pop(),
            child: Text('Close', style: GoogleFonts.quicksand(fontWeight: FontWeight.w700)),
          ),
        ],
      ),
    );
  }
}
