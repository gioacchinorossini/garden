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
import '../harvests/harvests_screen.dart';
import '../requests/apply_plot_dialog.dart';
import '../requests/requests_screen.dart';
import '../schedules/schedules_screen.dart';
import 'land_detail_screen.dart';

class CropFilterOption {
  final String value;
  final String label;
  final String icon;

  const CropFilterOption({
    required this.value,
    required this.label,
    required this.icon,
  });
}

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
  String _selectedCropFilter = 'all';
  String _selectedCropLabel = 'All Crops';
  String _selectedCropIcon = 'generic';

  static const List<CropFilterOption> _cropFilterOptions = [
    CropFilterOption(value: 'all', label: 'All Crops', icon: 'generic'),
    CropFilterOption(value: 'tomato', label: 'Tomato', icon: 'tomato'),
    CropFilterOption(value: 'lettuce', label: 'Lettuce / Greens', icon: 'lettuce'),
    CropFilterOption(value: 'herbs', label: 'Herbs / Basil', icon: 'herbs'),
    CropFilterOption(value: 'carrot', label: 'Carrot / Root Vegs', icon: 'carrot'),
    CropFilterOption(value: 'potato', label: 'Potato / Tubers', icon: 'potato'),
    CropFilterOption(value: 'pepper', label: 'Pepper', icon: 'pepper'),
    CropFilterOption(value: 'eggplant', label: 'Eggplant', icon: 'eggplant'),
    CropFilterOption(value: 'cucumber', label: 'Cucumber', icon: 'cucumber'),
    CropFilterOption(value: 'spinach', label: 'Spinach', icon: 'spinach'),
    CropFilterOption(value: 'beans', label: 'Beans / Legumes', icon: 'beans'),
    CropFilterOption(value: 'corn', label: 'Corn', icon: 'corn'),
    CropFilterOption(value: 'strawberry', label: 'Fruits / Berries', icon: 'strawberry'),
  ];

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

      // Status / Ownership filter
      if (_selectedFilter == 'available') {
        final hasAvail = land.plots.any((p) => p.isAvailable) || land.availablePlots > 0;
        if (!hasAvail) return false;
      } else if (_selectedFilter == 'myleased') {
        final isMy = land.plots.any((p) => p.farmerName.toLowerCase().contains('mary'));
        if (!isMy) return false;
      } else if (_selectedFilter == 'approved') {
        if (land.status.toLowerCase() != 'approved') return false;
      }

      // Crop filter from Dropdown
      if (_selectedCropFilter != 'all') {
        final rawCrops = land.crops.toLowerCase();
        final plotCrops = land.plots.map((p) => p.crop.toLowerCase()).join(' ');
        final combined = '$rawCrops $plotCrops';

        final target = _selectedCropFilter.toLowerCase();
        final matchesCrop = combined.contains(target) ||
            (target == 'lettuce' && (combined.contains('greens') || combined.contains('romaine'))) ||
            (target == 'herbs' && combined.contains('basil')) ||
            (target == 'potato' && (combined.contains('tuber') || combined.contains('russet'))) ||
            (target == 'carrot' && combined.contains('root')) ||
            (target == 'beans' && combined.contains('legume')) ||
            (target == 'strawberry' && (combined.contains('fruit') || combined.contains('berr')));
        if (!matchesCrop) return false;
      }

      return true;
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
      barrierColor: Colors.black.withValues(alpha: 0.35),
      builder: (context) => StatefulBuilder(
        builder: (context, setSheetState) {
          final currentLand = _selectedLandForSheet ?? land;
          return LandBottomSheet(
            land: currentLand,
            onClose: () => Navigator.of(context).pop(),
            onPrevious: () {
              final idx = _filteredLands.indexWhere((l) => l.id == currentLand.id);
              if (idx > 0) {
                final prev = _filteredLands[idx - 1];
                setSheetState(() => _selectedLandForSheet = prev);
                setState(() => _selectedLandForSheet = prev);
                _mapController.move(LatLng(prev.latitude - 0.0035, prev.longitude), 14.5);
              } else if (_filteredLands.isNotEmpty) {
                final last = _filteredLands.last;
                setSheetState(() => _selectedLandForSheet = last);
                setState(() => _selectedLandForSheet = last);
                _mapController.move(LatLng(last.latitude - 0.0035, last.longitude), 14.5);
              }
            },
            onNext: () {
              final idx = _filteredLands.indexWhere((l) => l.id == currentLand.id);
              if (idx >= 0 && idx < _filteredLands.length - 1) {
                final next = _filteredLands[idx + 1];
                setSheetState(() => _selectedLandForSheet = next);
                setState(() => _selectedLandForSheet = next);
                _mapController.move(LatLng(next.latitude - 0.0035, next.longitude), 14.5);
              } else if (_filteredLands.isNotEmpty) {
                final first = _filteredLands.first;
                setSheetState(() => _selectedLandForSheet = first);
                setState(() => _selectedLandForSheet = first);
                _mapController.move(LatLng(first.latitude - 0.0035, first.longitude), 14.5);
              }
            },
            onApply: (plot) {
              Navigator.of(context).pop();
              _openApplyDialog(currentLand, plot);
            },
            onOpenSchedules: () {
              Navigator.of(context).pop();
              Navigator.of(context).push(
                MaterialPageRoute(builder: (context) => const SchedulesScreen()),
              );
            },
            onOpenHarvests: () {
              Navigator.of(context).pop();
              Navigator.of(context).push(
                MaterialPageRoute(builder: (context) => const HarvestsScreen()),
              );
            },
            onOpenRequests: () {
              Navigator.of(context).pop();
              Navigator.of(context).push(
                MaterialPageRoute(builder: (context) => const RequestsScreen()),
              );
            },
          );
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
    final topInset = MediaQuery.of(context).padding.top;

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

          // Top Black Gradient Scrim Overlay (reaching comfortably past the chips)
          if (_isMapView)
            Positioned(
              top: 0,
              left: 0,
              right: 0,
              height: topInset + 145,
              child: IgnorePointer(
                child: Container(
                  decoration: const BoxDecoration(
                    gradient: LinearGradient(
                      begin: Alignment.topCenter,
                      end: Alignment.bottomCenter,
                      colors: [
                        Color(0xD9000000), // rgba(0, 0, 0, 0.85) at top
                        Color(0xB3000000), // rgba(0, 0, 0, 0.70) behind search bar
                        Color(0x73000000), // rgba(0, 0, 0, 0.45) directly behind the chips
                        Color(0x26000000), // rgba(0, 0, 0, 0.15) just past chips
                        Colors.transparent,
                      ],
                      stops: [0.0, 0.45, 0.75, 0.90, 1.0],
                    ),
                  ),
                ),
              ),
            ),

          // Bottom Black Gradient Scrim Overlay (matching .map-bottom-gradient-scrim in style.css)
          if (_isMapView)
            Positioned(
              bottom: 0,
              left: 0,
              right: 0,
              height: 130,
              child: IgnorePointer(
                child: Container(
                  decoration: const BoxDecoration(
                    gradient: LinearGradient(
                      begin: Alignment.bottomCenter,
                      end: Alignment.topCenter,
                      colors: [
                        Color(0xB3000000), // rgba(0, 0, 0, 0.70)
                        Color(0x59000000), // rgba(0, 0, 0, 0.35)
                        Colors.transparent,
                      ],
                      stops: [0.0, 0.55, 1.0],
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

                  // Top Filter Bar matching .mobile-map-chips-bar on web
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
                    child: Row(
                      children: [
                        _buildAllFilterDropdown(isLandowner),
                        const SizedBox(width: 8),
                        _buildCropFilterDropdown(),
                        const Spacer(),
                        _buildViewToggleChip(),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),

          // Recenter FAB button (matching .map-fab-btn from web)
          if (_isMapView)
            Positioned(
              top: 135,
              right: 14,
              child: GestureDetector(
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

  String _getAllFilterLabel(bool isLandowner) {
    switch (_selectedFilter) {
      case 'available':
        return 'Available Plots';
      case 'myleased':
        return 'My Leased';
      case 'my':
        return 'Mine';
      case 'approved':
        return 'Approved';
      case 'all':
      default:
        return 'All (${_lands.length})';
    }
  }

  Widget _buildAllFilterDropdown(bool isLandowner) {
    final availableCount = _lands.where((l) => l.plots.any((p) => p.isAvailable) || l.availablePlots > 0).length;
    final myLeasedCount = _lands.where((l) => l.plots.any((p) => p.farmerName.toLowerCase().contains('mary'))).length;
    final approvedCount = _lands.where((l) => l.status.toLowerCase() == 'approved').length;

    return Theme(
      data: Theme.of(context).copyWith(
        cardColor: AppColors.surface,
        dividerTheme: DividerThemeData(
          color: AppColors.cocoa.withValues(alpha: 0.2),
          thickness: 1,
          space: 8,
        ),
      ),
      child: PopupMenuButton<String>(
        tooltip: 'Filter Gardens',
        offset: const Offset(0, 38),
        elevation: 8,
        color: AppColors.surface,
        shadowColor: AppColors.cocoa.withValues(alpha: 0.4),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(16),
          side: const BorderSide(color: AppColors.cocoa, width: 2),
        ),
        constraints: const BoxConstraints(
          minWidth: 185,
          maxWidth: 220,
        ),
        onSelected: (val) {
          setState(() => _selectedFilter = val);
          _recenterMap();
        },
        itemBuilder: (context) {
          if (isLandowner) {
            return [
              _buildFilterMenuItem(
                value: 'all',
                label: 'All Lands',
                icon: Icons.grid_view_rounded,
                iconColor: AppColors.textSub,
                count: _lands.length,
                isSelected: _selectedFilter == 'all',
              ),
              const PopupMenuDivider(height: 8),
              _buildFilterMenuItem(
                value: 'my',
                label: 'Mine',
                icon: Icons.person_rounded,
                iconColor: AppColors.primary,
                count: _lands.length,
                isSelected: _selectedFilter == 'my',
              ),
              _buildFilterMenuItem(
                value: 'approved',
                label: 'Approved',
                icon: Icons.check_circle_rounded,
                iconColor: AppColors.availableGreen,
                count: approvedCount,
                isSelected: _selectedFilter == 'approved',
              ),
            ];
          } else {
            return [
              _buildFilterMenuItem(
                value: 'all',
                label: 'All Gardens',
                icon: Icons.grid_view_rounded,
                iconColor: AppColors.textSub,
                count: _lands.length,
                isSelected: _selectedFilter == 'all',
              ),
              const PopupMenuDivider(height: 8),
              _buildFilterMenuItem(
                value: 'available',
                label: 'Available Plots',
                icon: Icons.check_circle_rounded,
                iconColor: AppColors.availableGreen,
                count: availableCount,
                isSelected: _selectedFilter == 'available',
              ),
              _buildFilterMenuItem(
                value: 'myleased',
                label: 'My Leased',
                icon: Icons.favorite_rounded,
                iconColor: AppColors.coral,
                count: myLeasedCount,
                isSelected: _selectedFilter == 'myleased',
              ),
            ];
          }
        },
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 150),
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 7),
          decoration: BoxDecoration(
            color: AppColors.primary,
            borderRadius: BorderRadius.circular(99),
            border: Border.all(
              color: AppColors.cocoaButtonBorder,
              width: 2,
            ),
            boxShadow: const [
              BoxShadow(
                color: AppColors.cocoaButtonBorder,
                offset: Offset(0, 3),
                blurRadius: 0,
              ),
            ],
          ),
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(
                _getAllFilterLabel(isLandowner),
                style: GoogleFonts.quicksand(
                  fontSize: 12,
                  fontWeight: FontWeight.w800,
                  color: AppColors.surface,
                ),
              ),
              const SizedBox(width: 4),
              const Icon(
                Icons.keyboard_arrow_down_rounded,
                size: 16,
                color: AppColors.surface,
              ),
            ],
          ),
        ),
      ),
    );
  }

  PopupMenuItem<String> _buildFilterMenuItem({
    required String value,
    required String label,
    required IconData icon,
    required Color iconColor,
    int? count,
    required bool isSelected,
  }) {
    return PopupMenuItem<String>(
      value: value,
      height: 38,
      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
        decoration: BoxDecoration(
          color: isSelected ? AppColors.primary : Colors.transparent,
          borderRadius: BorderRadius.circular(8),
        ),
        child: Row(
          children: [
            Icon(
              icon,
              size: 16,
              color: isSelected ? AppColors.surface : iconColor,
            ),
            const SizedBox(width: 8),
            Expanded(
              child: Text(
                label,
                style: GoogleFonts.quicksand(
                  fontSize: 12,
                  fontWeight: isSelected ? FontWeight.w800 : FontWeight.w700,
                  color: isSelected ? AppColors.surface : AppColors.textMain,
                ),
              ),
            ),
            if (count != null)
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1),
                decoration: BoxDecoration(
                  color: isSelected
                      ? AppColors.surface.withValues(alpha: 0.25)
                      : AppColors.cocoa.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Text(
                  '$count',
                  style: GoogleFonts.quicksand(
                    fontSize: 11,
                    fontWeight: FontWeight.w800,
                    color: isSelected ? AppColors.surface : AppColors.textSub,
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildViewToggleChip() {
    return GestureDetector(
      onTap: () => setState(() => _isMapView = !_isMapView),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 7),
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
              size: 15,
              color: AppColors.textMain,
            ),
            const SizedBox(width: 5),
            Text(
              _isMapView ? 'List' : 'Map',
              style: GoogleFonts.quicksand(
                fontSize: 12,
                fontWeight: FontWeight.w800,
                color: AppColors.textMain,
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildCropFilterDropdown() {
    return Theme(
      data: Theme.of(context).copyWith(
        cardColor: AppColors.surface,
        dividerTheme: DividerThemeData(
          color: AppColors.cocoa.withValues(alpha: 0.2),
          thickness: 1,
          space: 8,
        ),
      ),
      child: PopupMenuButton<CropFilterOption>(
        tooltip: 'Filter by Crop',
        offset: const Offset(0, 38),
        elevation: 8,
        color: AppColors.surface,
        shadowColor: AppColors.cocoa.withValues(alpha: 0.4),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(16),
          side: const BorderSide(color: AppColors.cocoa, width: 2),
        ),
        constraints: const BoxConstraints(
          minWidth: 190,
          maxWidth: 220,
          maxHeight: 280,
        ),
        onSelected: (option) {
          setState(() {
            _selectedCropFilter = option.value;
            _selectedCropLabel = option.label;
            _selectedCropIcon = option.icon;
          });
          _recenterMap();
        },
        itemBuilder: (context) {
          final items = <PopupMenuEntry<CropFilterOption>>[];

          // First item: All Crops
          final allOpt = _cropFilterOptions.first;
          final isAllActive = _selectedCropFilter == 'all';
          items.add(
            PopupMenuItem<CropFilterOption>(
              value: allOpt,
              height: 36,
              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                decoration: BoxDecoration(
                  color: isAllActive ? AppColors.primary : Colors.transparent,
                  borderRadius: BorderRadius.circular(8),
                ),
                child: Row(
                  children: [
                    GardenCropIcon(crop: allOpt.icon, size: 16),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(
                        allOpt.label,
                        style: GoogleFonts.quicksand(
                          fontSize: 12,
                          fontWeight: isAllActive ? FontWeight.w800 : FontWeight.w700,
                          color: isAllActive ? AppColors.surface : AppColors.textMain,
                        ),
                      ),
                    ),
                    if (isAllActive)
                      const Icon(Icons.check_rounded, size: 15, color: AppColors.surface),
                  ],
                ),
              ),
            ),
          );

          // Divider matching web hr.dropdown-divider.my-1
          items.add(const PopupMenuDivider(height: 8));

          // Remaining Crops
          for (final opt in _cropFilterOptions.skip(1)) {
            final isItemActive = _selectedCropFilter == opt.value;
            items.add(
              PopupMenuItem<CropFilterOption>(
                value: opt,
                height: 36,
                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                child: Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                  decoration: BoxDecoration(
                    color: isItemActive ? AppColors.primary : Colors.transparent,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Row(
                    children: [
                      GardenCropIcon(crop: opt.icon, size: 16),
                      const SizedBox(width: 8),
                      Expanded(
                        child: Text(
                          opt.label,
                          style: GoogleFonts.quicksand(
                            fontSize: 12,
                            fontWeight: isItemActive ? FontWeight.w800 : FontWeight.w700,
                            color: isItemActive ? AppColors.surface : AppColors.textMain,
                          ),
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      if (isItemActive)
                        const Icon(Icons.check_rounded, size: 15, color: AppColors.surface),
                    ],
                  ),
                ),
              ),
            );
          }

          return items;
        },
        child: _buildCropDropdownChip(),
      ),
    );
  }

  Widget _buildCropDropdownChip() {
    final isCropActive = _selectedCropFilter != 'all';

    return AnimatedContainer(
      duration: const Duration(milliseconds: 150),
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 7),
      decoration: BoxDecoration(
        color: isCropActive ? AppColors.primary : AppColors.surface,
        borderRadius: BorderRadius.circular(99),
        border: Border.all(
          color: isCropActive ? AppColors.cocoaButtonBorder : AppColors.cocoa,
          width: 2,
        ),
        boxShadow: [
          BoxShadow(
            color: isCropActive ? AppColors.cocoaButtonBorder : AppColors.cocoa,
            offset: const Offset(0, 3),
            blurRadius: 0,
          ),
        ],
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          GardenCropIcon(crop: _selectedCropIcon, size: 16),
          const SizedBox(width: 6),
          Text(
            _selectedCropLabel,
            style: GoogleFonts.quicksand(
              fontSize: 12,
              fontWeight: FontWeight.w800,
              color: isCropActive ? AppColors.surface : AppColors.textMain,
            ),
          ),
          const SizedBox(width: 4),
          Icon(
            Icons.keyboard_arrow_down_rounded,
            size: 16,
            color: isCropActive ? AppColors.surface : AppColors.textSub,
          ),
        ],
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
