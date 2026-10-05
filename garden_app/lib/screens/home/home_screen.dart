import 'dart:math' as math;
import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../../models/models.dart';
import '../../services/api_service.dart';
import '../../services/auth_state.dart';
import '../../theme/app_theme.dart';
import '../../widgets/garden_crop_icon.dart';

class HomeScreen extends StatefulWidget {
  final VoidCallback? onOpenProfile;
  final void Function(int index)? onNavigateTo;

  const HomeScreen({super.key, this.onOpenProfile, this.onNavigateTo});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen>
    with SingleTickerProviderStateMixin {
  List<Land> _lands = [];
  List<HarvestItem> _harvests = [];
  List<ScheduleItem> _schedules = [];
  bool _isLoading = true;

  late AnimationController _heroAnim;
  late Animation<double> _heroFade;
  late Animation<Offset> _heroSlide;
  late Animation<double> _cloudFloat;

  @override
  void initState() {
    super.initState();
    _heroAnim = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1600),
    );
    _heroFade = CurvedAnimation(parent: _heroAnim, curve: Curves.easeOut);
    _heroSlide = Tween<Offset>(
      begin: const Offset(0, 0.06),
      end: Offset.zero,
    ).animate(CurvedAnimation(parent: _heroAnim, curve: Curves.easeOut));
    _cloudFloat = Tween<double>(begin: -6, end: 6).animate(
      CurvedAnimation(parent: _heroAnim, curve: Curves.easeInOut),
    );
    _loadData();
  }

  @override
  void dispose() {
    _heroAnim.dispose();
    super.dispose();
  }

  Future<void> _loadData() async {
    setState(() => _isLoading = true);
    final api = ApiService();
    final results = await Future.wait([
      api.getLands(),
      api.getHarvests(),
      api.getSchedules(),
    ]);
    if (mounted) {
      setState(() {
        _lands = results[0] as List<Land>;
        _harvests = results[1] as List<HarvestItem>;
        _schedules = results[2] as List<ScheduleItem>;
        _isLoading = false;
      });
      _heroAnim.forward();
    }
  }

  int get _totalCrops {
    final cropSet = <String>{};
    for (final land in _lands) {
      for (final c in land.crops.split(',')) {
        final t = c.trim();
        if (t.isNotEmpty) cropSet.add(t.toLowerCase());
      }
    }
    return cropSet.isEmpty ? 12 : cropSet.length;
  }

  int get _activeLands => _lands.where((l) => l.status == 'approved').length;

  double get _totalHarvestKg =>
      _harvests.fold(0.0, (sum, h) => sum + h.quantity);

  int get _totalPlots => _lands.fold(0, (sum, l) => sum + l.totalPlots);

  double get _soilMoistureEstimate {
    if (_totalPlots == 0) return 72;
    final occ = _lands.fold(0, (sum, l) => sum + l.occupiedPlots);
    return ((occ / math.max(_totalPlots, 1)) * 100).clamp(45, 95);
  }

  int get _farmHealthScore {
    if (_lands.isEmpty) return 92;
    final approved = _lands.where((l) => l.status == 'approved').length;
    return ((approved / _lands.length) * 100).round().clamp(60, 100);
  }

  String _greeting() {
    final h = DateTime.now().hour;
    if (h < 12) return 'Good morning';
    if (h < 17) return 'Good afternoon';
    return 'Good evening';
  }

  @override
  Widget build(BuildContext context) {
    final user = AuthState().currentUser;
    final firstName = (user?.name ?? 'Guest').split(' ').first;
    final topInset = MediaQuery.of(context).padding.top;
    final illustrationHeight = topInset + 295.0;
    final totalHeroHeight = illustrationHeight + 50.0;

    return Scaffold(
      backgroundColor: const Color(0xFFF9F6F0), // Clean warm canvas
      body: RefreshIndicator(
        color: AppColors.primary,
        onRefresh: _loadData,
        child: CustomScrollView(
          physics: const BouncingScrollPhysics(),
          slivers: [
            // ── Hero Section with Rounded Bottom & Overlapping Cards ──────
            SliverToBoxAdapter(
              child: SizedBox(
                height: totalHeroHeight,
                child: Stack(
                  clipBehavior: Clip.none,
                  children: [
                    // 1. The Farm Illustration Container with Rounded Bottom
                    Positioned(
                      top: 0,
                      left: 0,
                      right: 0,
                      height: illustrationHeight,
                      child: ClipPath(
                        clipper: _BottomRoundedClipper(radius: 32),
                        child: _buildHeroIllustration(
                          user,
                          firstName,
                          illustrationHeight,
                          topInset,
                        ),
                      ),
                    ),

                    // 2. Health & Weather Cards (Overlapping the rounded bottom edge)
                    Positioned(
                      top: illustrationHeight - 48,
                      left: 16,
                      right: 16,
                      child: FadeTransition(
                        opacity: _heroFade,
                        child: SlideTransition(
                          position: _heroSlide,
                          child: Row(
                            children: [
                              Expanded(child: _buildFarmHealthCard()),
                              const SizedBox(width: 12),
                              Expanded(child: _buildWeatherCard()),
                            ],
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),

            // Spacing below the hero block
            const SliverToBoxAdapter(
              child: SizedBox(height: 18),
            ),

            // ── Quick Stats Section Header ────────────────────────────────
            SliverPadding(
              padding: const EdgeInsets.fromLTRB(18, 0, 18, 12),
              sliver: SliverToBoxAdapter(
                child: FadeTransition(
                  opacity: _heroFade,
                  child: Text(
                    'Quick Stats',
                    style: GoogleFonts.quicksand(
                      fontSize: 19,
                      fontWeight: FontWeight.w800,
                      color: const Color(0xFF1E293B),
                    ),
                  ),
                ),
              ),
            ),

            // ── 2×2 Quick Stat Tiles ──────────────────────────────────────
            SliverPadding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              sliver: SliverToBoxAdapter(
                child: FadeTransition(
                  opacity: _heroFade,
                  child: SlideTransition(
                    position: _heroSlide,
                    child: _isLoading ? _buildSkeletonGrid() : _buildStatGrid(),
                  ),
                ),
              ),
            ),

            // ── Upcoming Schedule Card ─────────────────────────────────────
            if (_schedules.isNotEmpty)
              SliverPadding(
                padding: const EdgeInsets.fromLTRB(16, 16, 16, 0),
                sliver: SliverToBoxAdapter(
                  child: FadeTransition(
                    opacity: _heroFade,
                    child: _buildUpcomingScheduleCard(),
                  ),
                ),
              ),

            // ── Growing Crops Section ──────────────────────────────────────
            if (!_isLoading && _lands.isNotEmpty)
              SliverPadding(
                padding: const EdgeInsets.fromLTRB(16, 16, 16, 0),
                sliver: SliverToBoxAdapter(
                  child: FadeTransition(
                    opacity: _heroFade,
                    child: _buildRecentCropsSection(),
                  ),
                ),
              ),

            // Bottom padding for full navigation dock
            const SliverToBoxAdapter(child: SizedBox(height: 90)),
          ],
        ),
      ),
    );
  }

  // ────────────────────────────────────────────────────────────
  //  HERO ILLUSTRATION (Sky at top, green grass fills to bottom)
  // ────────────────────────────────────────────────────────────
  Widget _buildHeroIllustration(
    User? user,
    String firstName,
    double height,
    double topInset,
  ) {
    return SizedBox(
      height: height,
      width: double.infinity,
      child: Stack(
        clipBehavior: Clip.none,
        children: [
          // Extended Sky gradient background (extends 400px upward so pull-to-refresh & overscroll never cuts off)
          Positioned(
            top: -400,
            left: 0,
            right: 0,
            height: 400 + topInset + 160,
            child: Container(
              decoration: const BoxDecoration(
                gradient: LinearGradient(
                  begin: Alignment.topCenter,
                  end: Alignment.bottomCenter,
                  colors: [
                    Color(0xFFA0D8EF),
                    Color(0xFFA0D8EF),
                    Color(0xFFC4EBF9),
                    Color(0xFFDCF4D2),
                  ],
                  stops: [0.0, 0.45, 0.8, 1.0],
                ),
              ),
            ),
          ),

          // Sun (upper right glow)
          Positioned(
            top: topInset + 18,
            right: 80,
            child: _buildSunGlow(),
          ),

          // Floating clouds
          AnimatedBuilder(
            animation: _heroAnim,
            builder: (context, child) {
              return Positioned(
                top: topInset + 22 + _cloudFloat.value * 0.4,
                left: 20,
                child: Opacity(opacity: 0.88, child: _buildCloud(70)),
              );
            },
          ),
          AnimatedBuilder(
            animation: _heroAnim,
            builder: (context, child) {
              return Positioned(
                top: topInset + 32 - _cloudFloat.value * 0.3,
                right: 32,
                child: Opacity(opacity: 0.75, child: _buildCloud(48)),
              );
            },
          ),

          // Distant rolling hills (horizon between sky and grass)
          Positioned(
            top: topInset + 65,
            left: 0,
            right: 0,
            bottom: 0,
            child: SizedBox.expand(
              child: CustomPaint(
                painter: _DistantHillsPainter(),
              ),
            ),
          ),

          // Wind Turbines on hills (tall white towers + 3 spinning blades)
          Positioned(
            left: 64,
            top: topInset + 65,
            child: _buildWindTurbine(54),
          ),
          Positioned(
            right: 48,
            top: topInset + 70,
            child: _buildWindTurbine(58),
          ),

          // SOLID GREEN GROUND with rolling hill top (Fills all the way to the bottom edge!)
          Positioned(
            top: topInset + 120,
            left: 0,
            right: 0,
            bottom: 0,
            child: SizedBox.expand(
              child: CustomPaint(
                painter: _RollingLawnPainter(),
              ),
            ),
          ),

          // Pine tree next to barn
          Positioned(
            left: 110,
            top: topInset + 105,
            child: _buildPineTree(44),
          ),

          // Red Barn with Silver Silo (center-left)
          Positioned(
            left: 120,
            top: topInset + 98,
            child: _buildBarnAndSilo(),
          ),

          // Deciduous green tree
          Positioned(
            left: 220,
            top: topInset + 102,
            child: _buildDeciduousTree(46),
          ),

          // Wooden fence
          Positioned(
            left: 155,
            top: topInset + 152,
            child: _buildFence(105),
          ),

          // Spotted dairy cow
          Positioned(
            left: 172,
            top: topInset + 144,
            child: const Text('🐄', style: TextStyle(fontSize: 22)),
          ),

          // Plowed crop field furrow (left foreground, clearly visible above card)
          Positioned(
            left: 8,
            top: topInset + 148,
            child: _buildPlowedField(),
          ),

          // Farmer with conical straw hat (right foreground, clearly visible above card)
          Positioned(
            right: 6,
            top: topInset + 130,
            child: _buildFarmerWithHat(),
          ),

          // Top Header & Greeting Row
          Positioned(
            top: 0,
            left: 0,
            right: 0,
            child: SafeArea(
              bottom: false,
              child: Padding(
                padding: const EdgeInsets.fromLTRB(16, 8, 16, 0),
                child: _buildTopGreetingRow(user, firstName),
              ),
            ),
          ),
        ],
      ),
    );
  }

  // ────────────────────────────────────────────────────────────
  //  TOP GREETING ROW (Matches sample image)
  // ────────────────────────────────────────────────────────────
  Widget _buildTopGreetingRow(User? user, String firstName) {
    return Row(
      children: [
        // Avatar circle with white border
        GestureDetector(
          onTap: widget.onOpenProfile,
          child: Container(
            width: 44,
            height: 44,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              color: Colors.white,
              border: Border.all(color: const Color(0xFFE2E8F0), width: 2),
            ),
            child: ClipOval(
              child: Container(
                color: const Color(0xFFE2E8F0),
                alignment: Alignment.center,
                child: const Text('👨‍🌾', style: TextStyle(fontSize: 24)),
              ),
            ),
          ),
        ),
        const SizedBox(width: 10),

        // Greeting text + farm title
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                '${_greeting()}, $firstName',
                style: GoogleFonts.nunito(
                  fontSize: 13,
                  fontWeight: FontWeight.w600,
                  color: const Color(0xFF2C3E50),
                ),
              ),
              Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(
                    _activeLands > 0
                        ? (_lands.first.title.split(' ').take(2).join(' '))
                        : 'Green Valley Farm',
                    style: GoogleFonts.quicksand(
                      fontSize: 17,
                      fontWeight: FontWeight.w900,
                      color: const Color(0xFF1E293B),
                    ),
                  ),
                  const SizedBox(width: 4),
                  const Icon(
                    Icons.keyboard_arrow_down_rounded,
                    color: Color(0xFF1E293B),
                    size: 20,
                  ),
                ],
              ),
            ],
          ),
        ),

        // White circular notification button with alert dot
        GestureDetector(
          onTap: () => widget.onNavigateTo?.call(5),
          child: Container(
            width: 40,
            height: 40,
            decoration: BoxDecoration(
              color: Colors.white,
              shape: BoxShape.circle,
              border: Border.all(color: const Color(0xFFE2E8F0), width: 1.5),
            ),
            child: Stack(
              alignment: Alignment.center,
              children: [
                const Icon(
                  Icons.notifications_none_rounded,
                  color: Color(0xFF1E293B),
                  size: 21,
                ),
                Positioned(
                  top: 9,
                  right: 10,
                  child: Container(
                    width: 7,
                    height: 7,
                    decoration: const BoxDecoration(
                      color: Color(0xFFE2735D),
                      shape: BoxShape.circle,
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }

  // ────────────────────────────────────────────────────────────
  //  FARM HEALTH CARD (Matches sample image)
  // ────────────────────────────────────────────────────────────
  Widget _buildFarmHealthCard() {
    final score = _farmHealthScore;
    return Container(
      height: 98,
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: const Color(0xFFEDE8DE), width: 1.5),
      ),
      child: Row(
        children: [
          // Circular progress ring
          SizedBox(
            width: 50,
            height: 50,
            child: Stack(
              alignment: Alignment.center,
              children: [
                SizedBox(
                  width: 50,
                  height: 50,
                  child: CircularProgressIndicator(
                    value: score / 100,
                    strokeWidth: 5.5,
                    backgroundColor: const Color(0xFFE8F1EC),
                    valueColor: const AlwaysStoppedAnimation<Color>(
                      Color(0xFF2E6B4F), // Deep forest green
                    ),
                    strokeCap: StrokeCap.round,
                  ),
                ),
                Text(
                  '$score%',
                  style: GoogleFonts.quicksand(
                    fontSize: 12.5,
                    fontWeight: FontWeight.w800,
                    color: const Color(0xFF1E293B),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(width: 12),

          // Info labels
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Text(
                  'Farm Health',
                  style: GoogleFonts.nunito(
                    fontSize: 11,
                    fontWeight: FontWeight.w600,
                    color: const Color(0xFF8E8E93),
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  'Healthy',
                  style: GoogleFonts.quicksand(
                    fontSize: 15,
                    fontWeight: FontWeight.w800,
                    color: const Color(0xFF1E293B),
                  ),
                ),
                const SizedBox(height: 2),
                Row(
                  children: [
                    Container(
                      width: 6.5,
                      height: 6.5,
                      decoration: const BoxDecoration(
                        color: Color(0xFF2E6B4F),
                        shape: BoxShape.circle,
                      ),
                    ),
                    const SizedBox(width: 4),
                    Text(
                      'All good',
                      style: GoogleFonts.nunito(
                        fontSize: 11,
                        fontWeight: FontWeight.w600,
                        color: const Color(0xFF64748B),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  // ────────────────────────────────────────────────────────────
  //  WEATHER CARD (Matches sample image)
  // ────────────────────────────────────────────────────────────
  Widget _buildWeatherCard() {
    return Container(
      height: 98,
      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: const Color(0xFFEDE8DE), width: 1.5),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Row(
            children: [
              const Text('☀️', style: TextStyle(fontSize: 20)),
              const SizedBox(width: 6),
              Text(
                '28°C',
                style: GoogleFonts.quicksand(
                  fontSize: 19,
                  fontWeight: FontWeight.w900,
                  color: const Color(0xFF1E293B),
                ),
              ),
            ],
          ),
          const SizedBox(height: 1),
          Text(
            'Sunny',
            style: GoogleFonts.nunito(
              fontSize: 11.5,
              fontWeight: FontWeight.w600,
              color: const Color(0xFF8E8E93),
            ),
          ),
          const SizedBox(height: 4),
          Row(
            children: [
              _buildWeatherStat('🌧️', '15%'),
              const SizedBox(width: 10),
              _buildWeatherStat('💧', '68%'),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildWeatherStat(String icon, String val) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Text(icon, style: const TextStyle(fontSize: 11)),
        const SizedBox(width: 3),
        Text(
          val,
          style: GoogleFonts.nunito(
            fontSize: 11,
            fontWeight: FontWeight.w700,
            color: const Color(0xFF475569),
          ),
        ),
      ],
    );
  }

  // ────────────────────────────────────────────────────────────
  //  2×2 QUICK STATS GRID (Matches sample image exactly)
  // ────────────────────────────────────────────────────────────
  Widget _buildStatGrid() {
    final stats = [
      _StatTileData(
        emoji: '🌱',
        value: '$_totalCrops',
        label: 'Crops',
        badgeBg: const Color(0xFFE8F5E9),
        onTap: () => widget.onNavigateTo?.call(1),
      ),
      _StatTileData(
        emoji: '🐄',
        value: '48',
        label: 'Animals',
        badgeBg: const Color(0xFFFFF3E0),
        onTap: () => widget.onNavigateTo?.call(1),
      ),
      _StatTileData(
        emoji: '💧',
        value: '${_soilMoistureEstimate.toStringAsFixed(0)}%',
        label: 'Soil Moisture',
        badgeBg: const Color(0xFFE1F5FE),
        onTap: () => widget.onNavigateTo?.call(1),
      ),
      _StatTileData(
        emoji: '🧺',
        value: _totalHarvestKg > 0
            ? '${_totalHarvestKg.toStringAsFixed(0)} kg'
            : '1,280 kg',
        label: 'Harvest',
        badgeBg: const Color(0xFFFFF8E1),
        onTap: () => widget.onNavigateTo?.call(4),
      ),
    ];

    return GridView.count(
      crossAxisCount: 2,
      childAspectRatio: 1.35,
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      crossAxisSpacing: 12,
      mainAxisSpacing: 12,
      children: stats.map((s) => _buildStatTile(s)).toList(),
    );
  }

  Widget _buildStatTile(_StatTileData data) {
    return GestureDetector(
      onTap: data.onTap,
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(22),
          border: Border.all(color: const Color(0xFFEDE8DE), width: 1.5),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                // Icon square
                Container(
                  width: 38,
                  height: 38,
                  decoration: BoxDecoration(
                    color: data.badgeBg,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Center(
                    child: Text(
                      data.emoji,
                      style: const TextStyle(fontSize: 19),
                    ),
                  ),
                ),
                // Soft circular chevron button
                Container(
                  width: 26,
                  height: 26,
                  decoration: const BoxDecoration(
                    color: Color(0xFFF6F3EE),
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(
                    Icons.chevron_right_rounded,
                    size: 16,
                    color: Color(0xFF94A3B8),
                  ),
                ),
              ],
            ),
            const Spacer(),
            Text(
              data.value,
              style: GoogleFonts.quicksand(
                fontSize: 21,
                fontWeight: FontWeight.w900,
                color: const Color(0xFF1E293B),
              ),
            ),
            const SizedBox(height: 1),
            Text(
              data.label,
              style: GoogleFonts.nunito(
                fontSize: 12,
                fontWeight: FontWeight.w600,
                color: const Color(0xFF64748B),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSkeletonGrid() {
    return GridView.count(
      crossAxisCount: 2,
      childAspectRatio: 1.35,
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      crossAxisSpacing: 12,
      mainAxisSpacing: 12,
      children: List.generate(4, (i) {
        return Container(
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(22),
            border: Border.all(color: const Color(0xFFEDE8DE), width: 1.5),
          ),
          child: const Center(
            child: SizedBox(
              width: 20,
              height: 20,
              child: CircularProgressIndicator(
                strokeWidth: 2,
                color: AppColors.primary,
              ),
            ),
          ),
        );
      }),
    );
  }

  // ────────────────────────────────────────────────────────────
  //  UPCOMING SCHEDULE CARD
  // ────────────────────────────────────────────────────────────
  Widget _buildUpcomingScheduleCard() {
    final next = _schedules.firstWhere(
      (s) => s.status == 'in_progress' || s.status == 'pending',
      orElse: () => _schedules.first,
    );
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(22),
        border: Border.all(color: const Color(0xFFEDE8DE), width: 1.5),
      ),
      child: Row(
        children: [
          Container(
            width: 44,
            height: 44,
            decoration: BoxDecoration(
              color: const Color(0xFFE8F5E9),
              borderRadius: BorderRadius.circular(14),
            ),
            child: const Center(
              child: Text('📅', style: TextStyle(fontSize: 22)),
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  'Up Next',
                  style: GoogleFonts.nunito(
                    fontSize: 10.5,
                    fontWeight: FontWeight.w700,
                    color: const Color(0xFF94A3B8),
                    letterSpacing: 0.3,
                  ),
                ),
                Text(
                  next.title,
                  style: GoogleFonts.quicksand(
                    fontSize: 13.5,
                    fontWeight: FontWeight.w800,
                    color: const Color(0xFF1E293B),
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
                Text(
                  '${next.startTime} – ${next.endTime}  •  ${next.landTitle}',
                  style: GoogleFonts.nunito(
                    fontSize: 11,
                    fontWeight: FontWeight.w600,
                    color: const Color(0xFF64748B),
                  ),
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                ),
              ],
            ),
          ),
          GestureDetector(
            onTap: () => widget.onNavigateTo?.call(3),
            child: Container(
              width: 32,
              height: 32,
              decoration: const BoxDecoration(
                color: Color(0xFF2E6B4F),
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.chevron_right_rounded,
                color: Colors.white,
                size: 18,
              ),
            ),
          ),
        ],
      ),
    );
  }

  // ────────────────────────────────────────────────────────────
  //  GROWING CROPS HORIZONTAL SCROLL
  // ────────────────────────────────────────────────────────────
  Widget _buildRecentCropsSection() {
    final cropSet = <String>{};
    for (final land in _lands) {
      for (final c in land.crops.split(',')) {
        final t = c.trim();
        if (t.isNotEmpty) cropSet.add(t);
      }
    }
    final crops = cropSet.take(8).toList();
    if (crops.isEmpty) return const SizedBox.shrink();

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Growing Crops',
          style: GoogleFonts.quicksand(
            fontSize: 16,
            fontWeight: FontWeight.w800,
            color: const Color(0xFF1E293B),
          ),
        ),
        const SizedBox(height: 10),
        SingleChildScrollView(
          scrollDirection: Axis.horizontal,
          padding: const EdgeInsets.only(bottom: 4),
          child: Row(
            children: crops.map((crop) {
              return Container(
                margin: const EdgeInsets.only(right: 10),
                padding:
                    const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                decoration: BoxDecoration(
                  color: Colors.white,
                  borderRadius: BorderRadius.circular(18),
                  border:
                      Border.all(color: const Color(0xFFEDE8DE), width: 1.5),
                ),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    GardenCropIcon(crop: crop, size: 28),
                    const SizedBox(height: 5),
                    Text(
                      crop.split(' ').first,
                      style: GoogleFonts.nunito(
                        fontSize: 11,
                        fontWeight: FontWeight.w700,
                        color: const Color(0xFF475569),
                      ),
                    ),
                  ],
                ),
              );
            }).toList(),
          ),
        ),
      ],
    );
  }

  // ────────────────────────────────────────────────────────────
  //  FARM ILLUSTRATION COMPONENTS (Matching sample image)
  // ────────────────────────────────────────────────────────────
  Widget _buildSunGlow() {
    return Container(
      width: 44,
      height: 44,
      decoration: const BoxDecoration(
        color: Color(0xFFFDB813),
        shape: BoxShape.circle,
      ),
    );
  }

  Widget _buildCloud(double width) {
    return CustomPaint(
      painter: _CloudPainter(),
      size: Size(width, width * 0.45),
    );
  }

  Widget _buildWindTurbine(double height) {
    return SizedBox(
      width: height * 0.4,
      height: height,
      child: CustomPaint(painter: _WindTurbinePainter()),
    );
  }

  Widget _buildBarnAndSilo() {
    return SizedBox(
      width: 95,
      height: 70,
      child: CustomPaint(painter: _BarnAndSiloPainter()),
    );
  }

  Widget _buildPineTree(double height) {
    return SizedBox(
      width: height * 0.45,
      height: height,
      child: CustomPaint(painter: _PineTreePainter()),
    );
  }

  Widget _buildDeciduousTree(double height) {
    return SizedBox(
      width: height * 0.55,
      height: height,
      child: CustomPaint(painter: _DeciduousTreePainter()),
    );
  }

  Widget _buildFence(double width) {
    return SizedBox(
      width: width,
      height: 18,
      child: CustomPaint(painter: _FencePainter()),
    );
  }

  Widget _buildPlowedField() {
    return SizedBox(
      width: 140,
      height: 65,
      child: CustomPaint(painter: _PlowedFieldPainter()),
    );
  }

  Widget _buildFarmerWithHat() {
    return SizedBox(
      width: 110,
      height: 100,
      child: CustomPaint(painter: _FarmerWithHatPainter()),
    );
  }
}

// ──────────────────────────────────────────────────────────────
//  HELPER DATA CLASS
// ──────────────────────────────────────────────────────────────
class _StatTileData {
  final String emoji;
  final String value;
  final String label;
  final Color badgeBg;
  final VoidCallback? onTap;

  const _StatTileData({
    required this.emoji,
    required this.value,
    required this.label,
    required this.badgeBg,
    this.onTap,
  });
}

// ──────────────────────────────────────────────────────────────
//  CUSTOM PAINTERS FOR FARM SCENE
// ──────────────────────────────────────────────────────────────
class _CloudPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()..color = Colors.white;
    canvas.drawCircle(
      Offset(size.width * 0.35, size.height * 0.55),
      size.height * 0.42,
      paint,
    );
    canvas.drawCircle(
      Offset(size.width * 0.6, size.height * 0.45),
      size.height * 0.5,
      paint,
    );
    canvas.drawCircle(
      Offset(size.width * 0.82, size.height * 0.58),
      size.height * 0.38,
      paint,
    );
    canvas.drawRRect(
      RRect.fromRectAndRadius(
        Rect.fromLTWH(
          size.width * 0.1,
          size.height * 0.55,
          size.width * 0.8,
          size.height * 0.45,
        ),
        const Radius.circular(8),
      ),
      paint,
    );
  }

  @override
  bool shouldRepaint(covariant CustomPainter _) => false;
}

class _DistantHillsPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    // Background lighter hill
    final p1 = Paint()..color = const Color(0xFF8DC97E);
    final path1 = Path()
      ..moveTo(0, size.height)
      ..lineTo(0, size.height * 0.35)
      ..quadraticBezierTo(size.width * 0.25, 0, size.width * 0.55, size.height * 0.35)
      ..quadraticBezierTo(size.width * 0.8, size.height * 0.6, size.width, size.height * 0.2)
      ..lineTo(size.width, size.height)
      ..close();
    canvas.drawPath(path1, p1);

    // Foreground mid hill
    final p2 = Paint()..color = const Color(0xFF75BC64);
    final path2 = Path()
      ..moveTo(0, size.height)
      ..lineTo(0, size.height * 0.55)
      ..quadraticBezierTo(size.width * 0.35, size.height * 0.25, size.width * 0.7, size.height * 0.5)
      ..quadraticBezierTo(size.width * 0.9, size.height * 0.65, size.width, size.height * 0.4)
      ..lineTo(size.width, size.height)
      ..close();
    canvas.drawPath(path2, p2);
  }

  @override
  bool shouldRepaint(covariant CustomPainter _) => false;
}

class _WindTurbinePainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final white = Paint()..color = Colors.white.withValues(alpha: 0.90);

    // Slim tower
    final pole = Path()
      ..moveTo(size.width * 0.44, size.height)
      ..lineTo(size.width * 0.48, size.height * 0.28)
      ..lineTo(size.width * 0.52, size.height * 0.28)
      ..lineTo(size.width * 0.56, size.height)
      ..close();
    canvas.drawPath(pole, white);

    // Center hub
    canvas.drawCircle(
      Offset(size.width / 2, size.height * 0.28),
      size.width * 0.06,
      white,
    );

    // 3 slender blades (120 degrees apart)
    for (int i = 0; i < 3; i++) {
      canvas.save();
      canvas.translate(size.width / 2, size.height * 0.28);
      canvas.rotate(i * 2 * math.pi / 3);
      final blade = Path()
        ..moveTo(-size.width * 0.03, 0)
        ..lineTo(0, -size.height * 0.36)
        ..lineTo(size.width * 0.03, 0)
        ..close();
      canvas.drawPath(blade, white);
      canvas.restore();
    }
  }

  @override
  bool shouldRepaint(covariant CustomPainter _) => false;
}

class _BarnAndSiloPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final barnRed = Paint()..color = const Color(0xFFC0392B);
    final barnRoof = Paint()..color = const Color(0xFF8B2519);
    final siloBody = Paint()..color = const Color(0xFFCFD8DC);
    final siloDome = Paint()..color = const Color(0xFF90A4AE);
    final white = Paint()..color = Colors.white;

    // Silo (behind right of barn)
    final siloX = size.width * 0.72;
    final siloW = size.width * 0.24;
    canvas.drawRRect(
      RRect.fromRectAndRadius(
        Rect.fromLTWH(siloX, size.height * 0.18, siloW, size.height * 0.82),
        const Radius.circular(4),
      ),
      siloBody,
    );
    // Silo dome cap
    canvas.drawArc(
      Rect.fromLTWH(siloX, size.height * 0.04, siloW, size.height * 0.28),
      math.pi,
      math.pi,
      true,
      siloDome,
    );

    // Barn Main body
    final barnW = size.width * 0.68;
    canvas.drawRect(
      Rect.fromLTWH(0, size.height * 0.38, barnW, size.height * 0.62),
      barnRed,
    );

    // Barn Roof
    final roof = Path()
      ..moveTo(-size.width * 0.04, size.height * 0.40)
      ..lineTo(barnW / 2, size.height * 0.10)
      ..lineTo(barnW + size.width * 0.04, size.height * 0.40)
      ..close();
    canvas.drawPath(roof, barnRoof);

    // White loft window
    canvas.drawRect(
      Rect.fromCenter(
        center: Offset(barnW / 2, size.height * 0.27),
        width: barnW * 0.22,
        height: size.height * 0.12,
      ),
      white,
    );

    // White X double doors
    final doorW = barnW * 0.38;
    final doorH = size.height * 0.36;
    final doorRect = Rect.fromLTWH(
      (barnW - doorW) / 2,
      size.height - doorH,
      doorW,
      doorH,
    );
    canvas.drawRect(doorRect, white);

    // X strokes on door
    final strokePaint = Paint()
      ..color = const Color(0xFFC0392B)
      ..strokeWidth = 2.0;
    canvas.drawLine(doorRect.topLeft, doorRect.bottomRight, strokePaint);
    canvas.drawLine(doorRect.topRight, doorRect.bottomLeft, strokePaint);
  }

  @override
  bool shouldRepaint(covariant CustomPainter _) => false;
}

class _PineTreePainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final darkGreen = Paint()..color = const Color(0xFF2E7D32);
    final midGreen = Paint()..color = const Color(0xFF388E3C);
    final lightGreen = Paint()..color = const Color(0xFF43A047);

    // Bottom tier
    final p1 = Path()
      ..moveTo(0, size.height)
      ..lineTo(size.width / 2, size.height * 0.52)
      ..lineTo(size.width, size.height)
      ..close();
    canvas.drawPath(p1, darkGreen);

    // Mid tier
    final p2 = Path()
      ..moveTo(size.width * 0.1, size.height * 0.65)
      ..lineTo(size.width / 2, size.height * 0.25)
      ..lineTo(size.width * 0.9, size.height * 0.65)
      ..close();
    canvas.drawPath(p2, midGreen);

    // Top tier
    final p3 = Path()
      ..moveTo(size.width * 0.2, size.height * 0.38)
      ..lineTo(size.width / 2, 0)
      ..lineTo(size.width * 0.8, size.height * 0.38)
      ..close();
    canvas.drawPath(p3, lightGreen);
  }

  @override
  bool shouldRepaint(covariant CustomPainter _) => false;
}

class _DeciduousTreePainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final trunk = Paint()..color = const Color(0xFF6D4C41);
    final crown = Paint()..color = const Color(0xFF558B2F);
    final crownLight = Paint()..color = const Color(0xFF689F38);

    // Trunk
    canvas.drawRect(
      Rect.fromLTWH(
        size.width * 0.44,
        size.height * 0.6,
        size.width * 0.12,
        size.height * 0.4,
      ),
      trunk,
    );

    // Foliage puffs
    canvas.drawCircle(
      Offset(size.width * 0.5, size.height * 0.35),
      size.width * 0.36,
      crown,
    );
    canvas.drawCircle(
      Offset(size.width * 0.36, size.height * 0.32),
      size.width * 0.25,
      crownLight,
    );
    canvas.drawCircle(
      Offset(size.width * 0.64, size.height * 0.38),
      size.width * 0.24,
      crown,
    );
  }

  @override
  bool shouldRepaint(covariant CustomPainter _) => false;
}

class _FencePainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final wood = Paint()..color = const Color(0xFF8D6E63);

    // 2 horizontal rails
    canvas.drawRRect(
      RRect.fromRectAndRadius(
        Rect.fromLTWH(0, size.height * 0.2, size.width, 3),
        const Radius.circular(1.5),
      ),
      wood,
    );
    canvas.drawRRect(
      RRect.fromRectAndRadius(
        Rect.fromLTWH(0, size.height * 0.65, size.width, 3),
        const Radius.circular(1.5),
      ),
      wood,
    );

    // Vertical posts
    final postCount = 4;
    for (int i = 0; i < postCount; i++) {
      final x = (size.width / (postCount - 1)) * i;
      canvas.drawRRect(
        RRect.fromRectAndRadius(
          Rect.fromLTWH(x - 2, 0, 4, size.height),
          const Radius.circular(1.5),
        ),
        wood,
      );
    }
  }

  @override
  bool shouldRepaint(covariant CustomPainter _) => false;
}

class _PlowedFieldPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final soilLight = Paint()..color = const Color(0xFF8D6E63);
    final soilDark = Paint()..color = const Color(0xFF6D4C41);
    final sprout = Paint()..color = const Color(0xFF7CB342);

    // Trapezoidal soil patch
    final soilPath = Path()
      ..moveTo(size.width * 0.15, 0)
      ..lineTo(size.width * 0.95, 0)
      ..lineTo(size.width, size.height)
      ..lineTo(0, size.height)
      ..close();
    canvas.drawPath(soilPath, soilDark);

    // Furrow lines
    for (int i = 1; i <= 4; i++) {
      final y = (size.height / 5) * i;
      canvas.drawLine(
        Offset(size.width * 0.08 * (5 - i) / 5, y),
        Offset(size.width * (0.95 + 0.05 * i / 5), y),
        soilLight..strokeWidth = 3.5,
      );

      // Seedling dots
      for (int s = 1; s <= 5; s++) {
        final x = (size.width / 6) * s;
        canvas.drawCircle(Offset(x, y - 2), 2.5, sprout);
      }
    }
  }

  @override
  bool shouldRepaint(covariant CustomPainter _) => false;
}

class _FarmerWithHatPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final hatStraw = Paint()..color = const Color(0xFFE5B757);
    final hatBand = Paint()..color = const Color(0xFF8D6E63);
    final orangeShirt = Paint()..color = const Color(0xFFE65100);
    final neck = Paint()..color = const Color(0xFF795548);
    final tablet = Paint()..color = const Color(0xFF38BDF8);

    // Farmer Shoulders / Back
    final body = Path()
      ..moveTo(size.width * 0.10, size.height)
      ..quadraticBezierTo(
        size.width * 0.5,
        size.height * 0.45,
        size.width * 0.95,
        size.height,
      )
      ..close();
    canvas.drawPath(body, orangeShirt);

    // Neck
    canvas.drawRect(
      Rect.fromCenter(
        center: Offset(size.width * 0.55, size.height * 0.50),
        width: size.width * 0.14,
        height: size.height * 0.18,
      ),
      neck,
    );

    // Wide Conical Straw Hat
    // Brim oval
    canvas.drawOval(
      Rect.fromCenter(
        center: Offset(size.width * 0.55, size.height * 0.35),
        width: size.width * 0.85,
        height: size.height * 0.22,
      ),
      hatStraw,
    );

    // Hat brown band
    canvas.drawOval(
      Rect.fromCenter(
        center: Offset(size.width * 0.55, size.height * 0.31),
        width: size.width * 0.45,
        height: size.height * 0.12,
      ),
      hatBand,
    );

    // Hat cone top
    final cone = Path()
      ..moveTo(size.width * 0.32, size.height * 0.33)
      ..quadraticBezierTo(
        size.width * 0.55,
        size.height * 0.08,
        size.width * 0.78,
        size.height * 0.33,
      )
      ..close();
    canvas.drawPath(cone, hatStraw);

    // Blue Tablet in hands (bottom left of body)
    canvas.save();
    canvas.translate(size.width * 0.28, size.height * 0.70);
    canvas.rotate(-0.25);
    canvas.drawRRect(
      RRect.fromRectAndRadius(
        const Rect.fromLTWH(0, 0, 32, 22),
        const Radius.circular(3),
      ),
      tablet,
    );
    canvas.restore();
  }

  @override
  bool shouldRepaint(covariant CustomPainter _) => false;
}

class _RollingLawnPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final rect = Rect.fromLTWH(0, 0, size.width, size.height);
    final gradient = const LinearGradient(
      begin: Alignment.topCenter,
      end: Alignment.bottomCenter,
      colors: [
        Color(0xFF5BA83E),
        Color(0xFF4C9932),
        Color(0xFF3F8A28),
      ],
      stops: [0.0, 0.45, 1.0],
    ).createShader(rect);

    final lawnPaint = Paint()..shader = gradient;

    final path = Path()
      ..moveTo(0, size.height)
      ..lineTo(0, size.height * 0.16)
      ..quadraticBezierTo(
        size.width * 0.28,
        0,
        size.width * 0.62,
        size.height * 0.11,
      )
      ..quadraticBezierTo(
        size.width * 0.85,
        size.height * 0.18,
        size.width,
        size.height * 0.08,
      )
      ..lineTo(size.width, size.height)
      ..close();

    canvas.drawPath(path, lawnPaint);
  }

  @override
  bool shouldRepaint(covariant CustomPainter _) => false;
}

class _BottomRoundedClipper extends CustomClipper<Path> {
  final double radius;
  _BottomRoundedClipper({this.radius = 32});

  @override
  Path getClip(Size size) {
    final path = Path();
    // Start far above (-500) so overscrolling/pull-to-refresh never clips the sky
    path.moveTo(0, -500);
    path.lineTo(size.width, -500);
    path.lineTo(size.width, size.height - radius);
    path.quadraticBezierTo(
      size.width,
      size.height,
      size.width - radius,
      size.height,
    );
    path.lineTo(radius, size.height);
    path.quadraticBezierTo(0, size.height, 0, size.height - radius);
    path.close();
    return path;
  }

  @override
  bool shouldReclip(covariant _BottomRoundedClipper oldClipper) =>
      oldClipper.radius != radius;
}
