import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../models/models.dart';
import '../../services/api_service.dart';
import '../../services/auth_state.dart';
import '../../theme/app_theme.dart';
import '../../widgets/status_badge.dart';

class RequestsScreen extends StatefulWidget {
  const RequestsScreen({super.key});

  @override
  State<RequestsScreen> createState() => _RequestsScreenState();
}

class _RequestsScreenState extends State<RequestsScreen> with SingleTickerProviderStateMixin {
  late TabController _tabController;
  List<RequestModel> _requests = [];
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 4, vsync: this);
    _fetchRequests();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  Future<void> _fetchRequests() async {
    setState(() => _isLoading = true);
    final list = await ApiService().getRequests();
    if (mounted) {
      setState(() {
        _requests = list;
        _isLoading = false;
      });
    }
  }

  Future<void> _updateStatus(RequestModel req, String status) async {
    final noteController = TextEditingController();

    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(26),
          side: const BorderSide(color: AppColors.cocoa, width: 2.5),
        ),
        backgroundColor: AppColors.surface,
        title: Text(
          '${status == 'approved' ? 'Approve' : 'Reject'} Application',
          style: GoogleFonts.quicksand(fontWeight: FontWeight.w800),
        ),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Action for ${req.gardenerName}\'s lease of ${req.plotNumber} (${req.landTitle}).',
              style: GoogleFonts.nunito(fontSize: 13, color: AppColors.textSub, fontWeight: FontWeight.w600),
            ),
            const SizedBox(height: 14),
            TextField(
              controller: noteController,
              decoration: const InputDecoration(
                labelText: 'Response Notes (Optional)',
                hintText: 'e.g. Approved. Keys available Saturday.',
              ),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(false),
            child: Text('Cancel', style: GoogleFonts.quicksand(fontWeight: FontWeight.w700)),
          ),
          ElevatedButton(
            style: ElevatedButton.styleFrom(
              backgroundColor: status == 'approved' ? AppColors.primary : AppColors.coral,
            ),
            onPressed: () => Navigator.of(context).pop(true),
            child: Text(
              status == 'approved' ? 'Approve' : 'Reject',
              style: GoogleFonts.quicksand(fontWeight: FontWeight.w800),
            ),
          ),
        ],
      ),
    );

    if (confirmed == true) {
      await ApiService().updateRequestStatus(req.id, status, notes: noteController.text.trim());
      _fetchRequests();
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text('Request marked as ${status.toUpperCase()}'),
            backgroundColor: status == 'approved' ? AppColors.primary : AppColors.coral,
          ),
        );
      }
    }
  }

  List<RequestModel> _filterList(int tabIndex) {
    switch (tabIndex) {
      case 1:
        return _requests.where((r) => r.isPending).toList();
      case 2:
        return _requests.where((r) => r.isApproved).toList();
      case 3:
        return _requests.where((r) => r.isRejected).toList();
      default:
        return _requests;
    }
  }

  @override
  Widget build(BuildContext context) {
    final user = AuthState().currentUser;
    final isLandowner = user?.isLandowner ?? false;

    return Scaffold(
      backgroundColor: AppColors.canvas,
      appBar: AppBar(
        title: Text(
          isLandowner ? 'Incoming Applications' : 'My Plot Requests',
          style: GoogleFonts.quicksand(fontSize: 18, fontWeight: FontWeight.w900),
        ),
        bottom: TabBar(
          controller: _tabController,
          labelColor: AppColors.matchaDarkText,
          unselectedLabelColor: AppColors.textSub,
          indicatorSize: TabBarIndicatorSize.tab,
          indicator: BoxDecoration(
            color: AppColors.matchaLight,
            borderRadius: BorderRadius.circular(99),
            border: Border.all(color: AppColors.cocoa, width: 1.5),
          ),
          labelStyle: GoogleFonts.quicksand(fontWeight: FontWeight.w800, fontSize: 12),
          unselectedLabelStyle: GoogleFonts.quicksand(fontWeight: FontWeight.w600, fontSize: 12),
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
          tabs: const [
            Tab(text: 'All'),
            Tab(text: 'Pending'),
            Tab(text: 'Approved'),
            Tab(text: 'Rejected'),
          ],
        ),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _fetchRequests,
              child: TabBarView(
                controller: _tabController,
                children: List.generate(4, (index) {
                  final list = _filterList(index);
                  if (list.isEmpty) {
                    return Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(Icons.assignment_outlined, size: 48, color: AppColors.textMuted.withValues(alpha: 0.5)),
                          const SizedBox(height: 12),
                          Text(
                            'No applications found',
                            style: GoogleFonts.quicksand(fontSize: 16, fontWeight: FontWeight.w800, color: AppColors.textSub),
                          ),
                        ],
                      ),
                    );
                  }

                  return ListView.builder(
                    padding: const EdgeInsets.fromLTRB(16, 16, 16, 100),
                    itemCount: list.length,
                    itemBuilder: (context, i) {
                      final req = list[i];
                      return Container(
                        margin: const EdgeInsets.only(bottom: 14),
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
                                  padding: const EdgeInsets.all(10),
                                  decoration: BoxDecoration(
                                    color: AppColors.matchaLight,
                                    borderRadius: BorderRadius.circular(14),
                                    border: Border.all(color: AppColors.cocoa, width: 1.5),
                                  ),
                                  child: const Icon(Icons.assignment_rounded, color: AppColors.matchaDarkText, size: 22),
                                ),
                                const SizedBox(width: 12),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        '${req.landTitle} • ${req.plotNumber}',
                                        style: GoogleFonts.quicksand(fontSize: 16, fontWeight: FontWeight.w800),
                                      ),
                                      const SizedBox(height: 2),
                                      Text(
                                        'Applicant: ${req.gardenerName} • Duration: ${req.duration}',
                                        style: GoogleFonts.nunito(fontSize: 12, color: AppColors.textSub, fontWeight: FontWeight.w600),
                                      ),
                                    ],
                                  ),
                                ),
                                StatusBadge(status: req.status),
                              ],
                            ),
                            const SizedBox(height: 12),

                            Container(
                              width: double.infinity,
                              padding: const EdgeInsets.all(12),
                              decoration: BoxDecoration(
                                color: AppColors.slotBg,
                                borderRadius: BorderRadius.circular(14),
                                border: Border.all(color: AppColors.borderSubtle, width: 1),
                              ),
                              child: Text(
                                '"${req.purpose}"',
                                style: GoogleFonts.nunito(
                                  fontSize: 13,
                                  fontWeight: FontWeight.w600,
                                  fontStyle: FontStyle.italic,
                                  color: AppColors.textMain,
                                ),
                              ),
                            ),

                            if (req.notes.isNotEmpty) ...[
                              const SizedBox(height: 10),
                              Row(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  const Icon(Icons.comment_outlined, size: 14, color: AppColors.textMuted),
                                  const SizedBox(width: 6),
                                  Expanded(
                                    child: Text(
                                      'Response: ${req.notes}',
                                      style: GoogleFonts.nunito(fontSize: 12, color: AppColors.textSub, fontWeight: FontWeight.w600),
                                    ),
                                  ),
                                ],
                              ),
                            ],

                            if (isLandowner && req.isPending) ...[
                              const SizedBox(height: 14),
                              const Divider(color: AppColors.borderSubtle, height: 1, thickness: 1.5),
                              const SizedBox(height: 12),
                              Row(
                                mainAxisAlignment: MainAxisAlignment.end,
                                children: [
                                  OutlinedButton(
                                    style: OutlinedButton.styleFrom(
                                      foregroundColor: AppColors.coral,
                                      side: const BorderSide(color: AppColors.coral, width: 1.5),
                                    ),
                                    onPressed: () => _updateStatus(req, 'rejected'),
                                    child: const Text('Reject'),
                                  ),
                                  const SizedBox(width: 10),
                                  ElevatedButton(
                                    onPressed: () => _updateStatus(req, 'approved'),
                                    child: const Text('Approve'),
                                  ),
                                ],
                              ),
                            ],
                          ],
                        ),
                      );
                    },
                  );
                }),
              ),
            ),
    );
  }
}
