import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../services/api_service.dart';
import '../theme/app_theme.dart';

class ServerConfigDialog extends StatefulWidget {
  const ServerConfigDialog({super.key});

  @override
  State<ServerConfigDialog> createState() => _ServerConfigDialogState();
}

class _ServerConfigDialogState extends State<ServerConfigDialog> {
  late final TextEditingController _controller;
  bool _testing = false;
  String? _testResult;
  bool _isSuccess = false;

  @override
  void initState() {
    super.initState();
    _controller = TextEditingController(text: ApiService().baseUrl);
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  Future<void> _testConnection() async {
    setState(() {
      _testing = true;
      _testResult = null;
    });

    final currentUrl = _controller.text.trim();
    await ApiService().setBaseUrl(currentUrl);

    try {
      final lands = await ApiService().getLands();
      setState(() {
        _testing = false;
        _isSuccess = true;
        _testResult = 'Connected successfully! Received ${lands.length} lands.';
      });
    } catch (e) {
      setState(() {
        _testing = false;
        _isSuccess = false;
        _testResult = 'Connection failed: $e';
      });
    }
  }

  void _applyPreset(String url) {
    setState(() {
      _controller.text = url;
    });
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
            child: const Icon(Icons.dns_rounded, color: AppColors.matchaDarkText, size: 22),
          ),
          const SizedBox(width: 12),
          Text(
            'Server API Settings',
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
              'Enter the base URL for the Idle Land Garden PHP REST backend:',
              style: GoogleFonts.nunito(fontSize: 13, color: AppColors.textSub, fontWeight: FontWeight.w600),
            ),
            const SizedBox(height: 14),
            TextField(
              controller: _controller,
              decoration: InputDecoration(
                labelText: 'Backend API URL',
                hintText: 'http://localhost/garden/api',
                prefixIcon: const Icon(Icons.link, size: 20, color: AppColors.cocoa),
                suffixIcon: IconButton(
                  icon: const Icon(Icons.refresh, size: 20, color: AppColors.cocoa),
                  onPressed: _testConnection,
                  tooltip: 'Test Connection',
                ),
              ),
            ),
            const SizedBox(height: 12),
            Text(
              'Quick Presets:',
              style: GoogleFonts.quicksand(fontSize: 12, fontWeight: FontWeight.w700, color: AppColors.textSub),
            ),
            const SizedBox(height: 8),
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: [
                _buildPresetChip('Localhost (PC/Web)', 'http://localhost/garden/api'),
                _buildPresetChip('10.0.2.2 (Android Emu)', 'http://10.0.2.2/garden/api'),
                _buildPresetChip('Capacitor LAN IP', 'http://20.0.0.19/garden/api'),
              ],
            ),
            if (_testing) ...[
              const SizedBox(height: 16),
              const Center(
                child: SizedBox(
                  width: 20,
                  height: 20,
                  child: CircularProgressIndicator(strokeWidth: 2),
                ),
              ),
            ],
            if (_testResult != null) ...[
              const SizedBox(height: 14),
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: _isSuccess ? AppColors.matchaLight : const Color(0xFFFEE2E2),
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(
                    color: _isSuccess ? AppColors.cocoa : AppColors.coral,
                    width: 1.5,
                  ),
                ),
                child: Row(
                  children: [
                    Icon(
                      _isSuccess ? Icons.check_circle : Icons.error_outline,
                      color: _isSuccess ? AppColors.matchaDarkText : AppColors.coral,
                      size: 20,
                    ),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        _testResult!,
                        style: GoogleFonts.nunito(
                          color: _isSuccess ? AppColors.matchaDarkText : AppColors.coral,
                          fontSize: 12,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                    ),
                  ],
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
        ElevatedButton(
          onPressed: () async {
            await ApiService().setBaseUrl(_controller.text.trim());
            if (context.mounted) {
              Navigator.of(context).pop();
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(
                  content: Text('Server URL updated to ${_controller.text.trim()}'),
                  backgroundColor: AppColors.primary,
                ),
              );
            }
          },
          child: Text('Save & Apply', style: GoogleFonts.quicksand(fontWeight: FontWeight.w800)),
        ),
      ],
    );
  }

  Widget _buildPresetChip(String label, String url) {
    return GestureDetector(
      onTap: () => _applyPreset(url),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
        decoration: BoxDecoration(
          color: AppColors.slotBg,
          borderRadius: BorderRadius.circular(99),
          border: Border.all(color: AppColors.cocoa, width: 1.5),
        ),
        child: Text(
          label,
          style: GoogleFonts.quicksand(fontSize: 11, fontWeight: FontWeight.w700, color: AppColors.textMain),
        ),
      ),
    );
  }
}
