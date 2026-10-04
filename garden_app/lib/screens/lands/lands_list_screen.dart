import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:latlong2/latlong.dart';
import '../../models/models.dart';
import '../../services/api_service.dart';
import '../../services/auth_state.dart';
import '../../theme/app_theme.dart';
import '../../widgets/floating_map_header.dart';
import '../../widgets/garden_crop_icon.dart';
import '../../widgets/land_bottom_sheet.dart';
import '../../widgets/status_badge.dart';
import '../requests/apply_plot_dialog.dart';
import 'land_detail_screen.dart';
import 'register_land_dialog.dart';

class LandsListScreen extends StatefulWidget {
  final VoidCallback? onOpenProfile;

  const LandsListScreen({super.key, this.onOpenProfile});

  @override
  State<LandsListScreen> createState() => _LandsListScreenState();
}

class _LandsListScreenState extends State<LandsListScreen> {
  final TextEditingController _searchController = TextEditingController();
  final MapController _mapController = MapController();

  List<Land> _lands = [];
  bool _isLoading = true;
  String _selectedFilter = 'all';
  bool _isMapView = true; // Toggle between visual map and list view
  Land? _selectedLandForSheet;

  // Default initial coordinates matching dashboard.php:572
  static const LatLng _initialCenter = LatLng(14.6010, 120.9890);

  @override
  void initState() {
    super.initState();
    _fetchLands();
  }

  @override
  void dispose() {
    _searchController.dispose();
    super.dispose();
  }

  Future<void> _fetchLands() async {
    setState(() => _isLoading = true);
    final list = await ApiService().getLands();
    if (mounted) {
      setState(() {
        _lands = list;
        _isLoading = false;
        // Do NOT auto-open the sheet so the full map is visible immediately
        _selectedLandForSheet = null;
      });
    }
  }

  List<Land> get _filteredLands {
    final query = _searchController.text.trim().toLowerCase();
    return _lands.where((land) {
      final matchesSearch = query.isEmpty ||
          land.title.toLowerCase().contains(query) ||
          land.address.toLowerCase().contains(query) ||
          land.crops.toLowerCase().contains(query);

      if (!matchesSearch) return false;

      if (_selectedFilter == 'all') return true;
      if (_selectedFilter == 'available') {
        return land.plots.any((p) => p.isAvailable) || land.availablePlots > 0;
      }
      if (_selectedFilter == 'myleased') {
        return land.plots.any((p) => p.farmerName.toLowerCase().contains('mary'));
      }
      return land.crops.toLowerCase().contains(_selectedFilter.toLowerCase());
    }).toList();
  }

  void _recenterMap() {
    if (_filteredLands.isNotEmpty) {
      final first = _filteredLands.first;
      _mapController.move(LatLng(first.latitude, first.longitude), 14.0);
    } else {
      _mapController.move(_initialCenter, 13.0);
    }
  }


  void _openApplyDialog(Land land, [Plot? plot]) {
    showDialog(
      context: context,
      builder: (context) => ApplyPlotDialog(
        land: land,
        selectedPlot: plot,
        onRequestSubmitted: _fetchLands,
      ),
    );
  }

  void _openRegisterDialog() {
    showDialog(
      context: context,
      builder: (context) => RegisterLandDialog(onLandCreated: _fetchLands),
    );
  }

  void _showLandDetailsSheet(Land land) {
    setState(() => _selectedLandForSheet = land);
    _mapController.move(
      LatLng(land.latitude - 0.0035, land.longitude),
      14.5,
    );

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      barrierColor: Colors.black38,
      builder: (context) => LandBottomSheet(
        land: land,
        onClose: () => Navigator.of(context).pop(),
        onApply: (plot) {
          Navigator.of(context).pop();
          _openApplyDialog(land, plot);
        },
      ),
    ).whenComplete(() {
      if (mounted) {
        setState(() => _selectedLandForSheet = null);
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final user = AuthState().currentUser;
    final isLandowner = user?.isLandowner ?? false;

    return Scaffold(
      backgroundColor: AppColors.canvas,
      body: Stack(
        fit: StackFit.expand,
        children: [
          // Background: Real Leaflet OpenStreetMap View or Card List
          Positioned.fill(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
                : (_isMapView ? _buildRealLeafletMap() : _buildCardListView()),
          ),

          // Top Scrim Gradient (matching .map-top-gradient-scrim in style.css)
          Positioned(
            top: 0,
            left: 0,
            right: 0,
            height: 140,
            child: IgnorePointer(
              child: Container(
                decoration: const BoxDecoration(
                  gradient: LinearGradient(
                    begin: Alignment.topCenter,
                    end: Alignment.bottomCenter,
                    colors: [
                      Color(0x993E2B1E),
                      Color(0x333E2B1E),
                      Colors.transparent,
                    ],
                  ),
                ),
              ),
            ),
          ),

          // Floating Top Header & Filter Chips Anchored at Top
          Positioned(
            top: 0,
            left: 0,
            right: 0,
            child: SafeArea(
              bottom: false,
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  FloatingMapHeader(
                    searchController: _searchController,
                    onSearchChanged: (val) => setState(() {}),
                    onClearSearch: () => setState(() => _searchController.clear()),
                    onProfileTap: widget.onOpenProfile,
                  ),

                  // Horizontal Map Filter Chips (matching .mobile-map-chips-bar & .map-chip)
                  SingleChildScrollView(
                    scrollDirection: Axis.horizontal,
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
                    child: Row(
                      children: [
                        _buildMapChip(
                          id: 'all',
                          label: 'All Gardens (${_lands.length})',
                          cropIcon: 'generic',
                        ),
                        const SizedBox(width: 8),
                        _buildMapChip(
                          id: 'available',
                          label: 'Available Plots',
                          icon: Icons.check_circle,
                          iconColor: AppColors.availableGreen,
                        ),
                        const SizedBox(width: 8),
                        _buildMapChip(
                          id: 'myleased',
                          label: 'My Leased',
                          icon: Icons.favorite,
                          iconColor: AppColors.coral,
                        ),
                        const SizedBox(width: 8),
                        _buildMapChip(
                          id: 'tomato',
                          label: 'Tomato',
                          cropIcon: 'tomato',
                        ),
                        const SizedBox(width: 8),
                        _buildMapChip(
                          id: 'herbs',
                          label: 'Herbs / Basil',
                          cropIcon: 'basil',
                        ),
                        const SizedBox(width: 8),
                        _buildMapChip(
                          id: 'carrot',
                          label: 'Carrots',
                          cropIcon: 'carrot',
                        ),
                        const SizedBox(width: 8),
                        _buildMapChip(
                          id: 'lettuce',
                          label: 'Lettuce / Greens',
                          cropIcon: 'romaine',
                        ),
                        const SizedBox(width: 8),
                        _buildMapChip(
                          id: 'potato',
                          label: 'Potato',
                          cropIcon: 'potato',
                        ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),

          // Map Action Floating Buttons (View Toggle & Recenter Map)
          Positioned(
            top: 135,
            right: 14,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.end,
              children: [
                // Toggle Map / List
                GestureDetector(
                  onTap: () => setState(() => _isMapView = !_isMapView),
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 7),
                    decoration: BoxDecoration(
                      color: AppColors.surface,
                      borderRadius: BorderRadius.circular(99),
                      border: Border.all(color: AppColors.cocoa, width: 2),
                      boxShadow: AppColors.tactileShadow,
                    ),
                    child: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(
                          _isMapView ? Icons.view_agenda_rounded : Icons.map_rounded,
                          size: 16,
                          color: AppColors.textMain,
                        ),
                        const SizedBox(width: 6),
                        Text(
                          _isMapView ? 'List View' : 'Map View',
                          style: GoogleFonts.quicksand(fontSize: 12, fontWeight: FontWeight.w800, color: AppColors.textMain),
                        ),
                      ],
                    ),
                  ),
                ),
                if (_isMapView) ...[
                  const SizedBox(height: 10),
                  // Recenter FAB button (matching .map-fab-btn from dashboard.php)
                  GestureDetector(
                    onTap: _recenterMap,
                    child: Container(
                      width: 40,
                      height: 40,
                      decoration: BoxDecoration(
                        color: AppColors.surface,
                        shape: BoxShape.circle,
                        border: Border.all(color: AppColors.cocoa, width: 2),
                        boxShadow: AppColors.tactileShadow,
                      ),
                      alignment: Alignment.center,
                      child: const Icon(Icons.my_location_rounded, color: AppColors.primary, size: 20),
                    ),
                  ),
                ],
              ],
            ),
          ),

          // Register Land Action Button (for Landowner)
          if (isLandowner)
            Positioned(
              bottom: 96,
              right: 16,
              child: GestureDetector(
                onTap: _openRegisterDialog,
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 12),
                  decoration: BoxDecoration(
                    color: AppColors.primary,
                    borderRadius: BorderRadius.circular(99),
                    border: Border.all(color: AppColors.cocoaButtonBorder, width: 2),
                    boxShadow: AppColors.tactileShadowLg,
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Icon(Icons.add_location_alt_rounded, color: AppColors.surface, size: 20),
                      const SizedBox(width: 6),
                      Text(
                        'List Idle Land',
                        style: GoogleFonts.quicksand(
                          fontSize: 14,
                          fontWeight: FontWeight.w800,
                          color: AppColors.surface,
                        ),
                      ),
                    ],
                  ),
                ),
              ),
            ),
        ],
      ),
    );
  }

  // Real OpenStreetMap Leaflet Map matching gardenerMainMap
  Widget _buildRealLeafletMap() {
    return FlutterMap(
      mapController: _mapController,
      options: MapOptions(
        initialCenter: _filteredLands.isNotEmpty
            ? LatLng(_filteredLands.first.latitude, _filteredLands.first.longitude)
            : _initialCenter,
        initialZoom: 13.0,
        minZoom: 4.0,
        maxZoom: 18.0,
        onTap: (tapPosition, point) {
          // Tapping anywhere on the map closes the sheet (matching gardenerMap.on('click', closeLandCardSheet))
          if (_selectedLandForSheet != null) {
            setState(() => _selectedLandForSheet = null);
          }
        },
      ),
      children: [
        // OpenStreetMap Tile Layer with CartoDB fallback (identical to dashboard.php:574)
        TileLayer(
          urlTemplate: 'https://tile.openstreetmap.org/{z}/{x}/{y}.png',
          fallbackUrl: 'https://a.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}@2x.png',
          userAgentPackageName: 'com.garden.app.garden_app',
          maxZoom: 19,
        ),

        // Custom Leaflet Pins matching .land-map-pin in dashboard.php:651
        MarkerLayer(
          markers: _filteredLands.map((land) {
            final isSelected = _selectedLandForSheet?.id == land.id;

            return Marker(
              point: LatLng(land.latitude, land.longitude),
              width: 140,
              height: 74,
              alignment: Alignment.topCenter,
              child: GestureDetector(
                onTap: () => _showLandDetailsSheet(land),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    // Circular Green Garden Pin (.land-map-pin) with White Plant Icon Badge
                    AnimatedContainer(
                      duration: const Duration(milliseconds: 200),
                      width: isSelected ? 46 : 40,
                      height: isSelected ? 46 : 40,
                      decoration: BoxDecoration(
                        color: isSelected ? AppColors.primary : const Color(0xFF198754),
                        shape: BoxShape.circle,
                        border: Border.all(color: Colors.white, width: 3),
                        boxShadow: const [
                          BoxShadow(
                            color: Color(0x40000000),
                            offset: Offset(0, 4),
                            blurRadius: 10,
                          ),
                        ],
                      ),
                      alignment: Alignment.center,
                      child: Container(
                        width: isSelected ? 30 : 26,
                        height: isSelected ? 30 : 26,
                        decoration: const BoxDecoration(
                          color: Colors.white,
                          shape: BoxShape.circle,
                          boxShadow: [
                            BoxShadow(
                              color: Color(0x20000000),
                              offset: Offset(0, 1.5),
                              blurRadius: 4,
                            ),
                          ],
                        ),
                        alignment: Alignment.center,
                        child: GardenCropIcon(
                          crop: land.crops,
                          size: isSelected ? 20 : 17,
                        ),
                      ),
                    ),
                    const SizedBox(height: 3),
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                      decoration: BoxDecoration(
                        color: AppColors.surface,
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(color: AppColors.cocoa, width: 1.5),
                        boxShadow: AppColors.tactileShadow,
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          GardenCropIcon(crop: land.crops, size: 13),
                          const SizedBox(width: 4),
                          Flexible(
                            child: Text(
                              land.title,
                              style: GoogleFonts.quicksand(
                                fontSize: 10,
                                fontWeight: FontWeight.w800,
                                color: AppColors.textMain,
                              ),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),
            );
          }).toList(),
        ),
      ],
    );
  }

  Widget _buildMapChip({
    required String id,
    required String label,
    IconData? icon,
    Color? iconColor,
    String? cropIcon,
  }) {
    final isSelected = _selectedFilter == id;

    return GestureDetector(
      onTap: () => setState(() => _selectedFilter = id),
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
            if (cropIcon != null) ...[
              GardenCropIcon(crop: cropIcon, size: 16),
              const SizedBox(width: 6),
            ] else if (icon != null) ...[
              Icon(icon, size: 14, color: isSelected ? AppColors.surface : (iconColor ?? AppColors.primary)),
              const SizedBox(width: 6),
            ],
            Text(
              label,
              style: GoogleFonts.quicksand(
                fontSize: 12,
                fontWeight: FontWeight.w800,
                color: isSelected ? AppColors.surface : AppColors.textMain,
              ),
            ),
          ],
        ),
      ),
    );
  }

  // Card List View (matching gardener/browse.php)
  Widget _buildCardListView() {
    return ListView.builder(
      padding: const EdgeInsets.fromLTRB(16, 175, 16, 110),
      itemCount: _filteredLands.length,
      itemBuilder: (context, i) {
        final land = _filteredLands[i];
        final avail = land.plots.where((p) => p.isAvailable).length;

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
                  builder: (context) => LandDetailScreen(
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
                        child: GardenCropIcon(
                          crop: land.crops,
                          size: 26,
                        ),
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
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: AppColors.slotBg,
                          borderRadius: BorderRadius.circular(99),
                          border: Border.all(color: AppColors.cocoa, width: 1.5),
                        ),
                        child: Text(
                          '${land.area} m²',
                          style: GoogleFonts.quicksand(fontSize: 11, fontWeight: FontWeight.w800),
                        ),
                      ),
                      const SizedBox(width: 8),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                        decoration: BoxDecoration(
                          color: avail > 0 ? AppColors.matchaLight : AppColors.slotBg,
                          borderRadius: BorderRadius.circular(99),
                          border: Border.all(color: AppColors.cocoa, width: 1.5),
                        ),
                        child: Text(
                          '$avail available plots',
                          style: GoogleFonts.quicksand(
                            fontSize: 11,
                            fontWeight: FontWeight.w800,
                            color: avail > 0 ? AppColors.matchaDarkText : AppColors.textSub,
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
    );
  }
}
