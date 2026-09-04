<?php
// Gamified Environmental Contribution Tracker component
// Dynamic eco calculations based on user role or default mock state
$eco_level = isset($eco_level_override) ? $eco_level_override : 3;
$eco_xp = isset($eco_xp_override) ? $eco_xp_override : 850;
$eco_max_xp = 1000;
$co2_offset = isset($co2_offset_override) ? $co2_offset_override : 142.8; // kg
$water_saved = isset($water_saved_override) ? $water_saved_override : 1250; // Liters
$organic_yield = isset($organic_yield_override) ? $organic_yield_override : 57.5; // kg
$streak_days = isset($streak_days_override) ? $streak_days_override : 7;
?>

<div class="card border rounded-4 overflow-hidden mb-4 bg-white shadow-xs" style="border-color: var(--drive-border) !important;">
    <!-- Tracker Header with Level & Streak -->
    <div class="card-header bg-gradient-to-r from-emerald-600 to-teal-700 text-white p-4 border-0">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-md d-flex align-items-center justify-content-center shadow-inner border border-white/30 flex-shrink-0">
                    <span class="fs-2">🌿</span>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge rounded-pill bg-white/20 text-white border border-white/30 text-uppercase tracking-wider text-[10px] px-2.5 py-0.5">Level <?php echo $eco_level; ?> Rank</span>
                        <span class="badge rounded-pill bg-amber-400 text-amber-950 font-bold text-[10px] px-2.5 py-0.5 d-flex align-items-center gap-1">
                            🔥 <?php echo $streak_days; ?>-Day Streak
                        </span>
                    </div>
                    <h3 class="fs-5 fw-bold mb-0 text-white">Green Guardian Eco Tracker</h3>
                </div>
            </div>
            
            <!-- XP Progress Bar -->
            <div class="w-100 w-md-auto min-w-[220px]">
                <div class="d-flex justify-content-between text-xs fw-semibold text-white/90 mb-1">
                    <span>Eco Progress</span>
                    <span><?php echo $eco_xp; ?> / <?php echo $eco_max_xp; ?> XP</span>
                </div>
                <div class="progress rounded-pill bg-white/20 overflow-hidden" style="height: 10px;">
                    <div class="progress-bar bg-amber-300 rounded-pill transition-all" role="progressbar" style="width: <?php echo ($eco_xp / $eco_max_xp) * 100; ?>%;"></div>
                </div>
                <span class="text-[10px] text-white/75 d-block mt-1 text-end">+<?php echo ($eco_max_xp - $eco_xp); ?> XP to Level <?php echo ($eco_level + 1); ?></span>
            </div>
        </div>
    </div>

    <!-- Impact Metrics Grid -->
    <div class="card-body p-4 bg-slate-50/50">
        <div class="row g-3 mb-4">
            <!-- Metric 1: CO2 Offset -->
            <div class="col-6 col-md-3">
                <div class="p-3 rounded-3 bg-white border border-drive-border d-flex align-items-center gap-3 hover:shadow-sm transition-all">
                    <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 d-flex align-items-center justify-content-center flex-shrink-0">
                        <i data-lucide="leaf" style="width: 20px; height: 20px;"></i>
                    </div>
                    <div>
                        <span class="text-secondary text-[11px] font-semibold d-block uppercase tracking-wider">CO₂ Offset</span>
                        <h4 class="fw-bold mb-0 text-dark fs-6"><?php echo $co2_offset; ?> <span class="text-xs text-muted font-normal">kg</span></h4>
                        <span class="text-[10px] text-emerald-600 font-semibold">≈ <?php echo max(1, round($co2_offset * 0.04)); ?> trees planted</span>
                    </div>
                </div>
            </div>

            <!-- Metric 2: Water Saved -->
            <div class="col-6 col-md-3">
                <div class="p-3 rounded-3 bg-white border border-drive-border d-flex align-items-center gap-3 hover:shadow-sm transition-all">
                    <div class="w-10 h-10 rounded-xl bg-cyan-100 text-cyan-700 d-flex align-items-center justify-content-center flex-shrink-0">
                        <i data-lucide="droplets" style="width: 20px; height: 20px;"></i>
                    </div>
                    <div>
                        <span class="text-secondary text-[11px] font-semibold d-block uppercase tracking-wider">Water Saved</span>
                        <h4 class="fw-bold mb-0 text-dark fs-6"><?php echo number_format($water_saved); ?> <span class="text-xs text-muted font-normal">L</span></h4>
                        <span class="text-[10px] text-cyan-600 font-semibold">Drip efficiency</span>
                    </div>
                </div>
            </div>

            <!-- Metric 3: Organic Yield -->
            <div class="col-6 col-md-3">
                <div class="p-3 rounded-3 bg-white border border-drive-border d-flex align-items-center gap-3 hover:shadow-sm transition-all">
                    <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 d-flex align-items-center justify-content-center flex-shrink-0">
                        <i data-lucide="apple" style="width: 20px; height: 20px;"></i>
                    </div>
                    <div>
                        <span class="text-secondary text-[11px] font-semibold d-block uppercase tracking-wider">Zero Food Miles</span>
                        <h4 class="fw-bold mb-0 text-dark fs-6"><?php echo $organic_yield; ?> <span class="text-xs text-muted font-normal">kg</span></h4>
                        <span class="text-[10px] text-purple-600 font-semibold">Local harvest</span>
                    </div>
                </div>
            </div>

            <!-- Metric 4: Eco Score -->
            <div class="col-6 col-md-3">
                <div class="p-3 rounded-3 bg-white border border-drive-border d-flex align-items-center gap-3 hover:shadow-sm transition-all">
                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 d-flex align-items-center justify-content-center flex-shrink-0">
                        <i data-lucide="sun" style="width: 20px; height: 20px;"></i>
                    </div>
                    <div>
                        <span class="text-secondary text-[11px] font-semibold d-block uppercase tracking-wider">Biodiversity Index</span>
                        <h4 class="fw-bold mb-0 text-dark fs-6">94 / 100</h4>
                        <span class="text-[10px] text-amber-600 font-semibold">Poly-culture soil</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Badges & Achievements Section -->
        <div>
            <div class="d-flex align-items-center justify-content-between mb-3">
                <span class="text-xs font-bold text-secondary text-uppercase tracking-wider">Environmental Badges</span>
                <span class="text-xs text-primary font-semibold">3 of 4 Unlocked</span>
            </div>
            
            <div class="row g-2">
                <!-- Badge 1 -->
                <div class="col-6 col-md-3">
                    <div class="p-3 rounded-3 bg-emerald-50/60 border border-emerald-200 d-flex align-items-center gap-2.5">
                        <div class="w-9 h-9 rounded-full bg-emerald-500 text-white d-flex align-items-center justify-content-center text-sm shadow-xs flex-shrink-0">
                            🥇
                        </div>
                        <div>
                            <span class="fw-bold text-xs text-emerald-950 d-block leading-tight">Zero Food Miles</span>
                            <span class="text-[10px] text-emerald-700 font-medium">50kg+ Local Yield</span>
                        </div>
                    </div>
                </div>

                <!-- Badge 2 -->
                <div class="col-6 col-md-3">
                    <div class="p-3 rounded-3 bg-cyan-50/60 border border-cyan-200 d-flex align-items-center gap-2.5">
                        <div class="w-9 h-9 rounded-full bg-cyan-500 text-white d-flex align-items-center justify-content-center text-sm shadow-xs flex-shrink-0">
                            💧
                        </div>
                        <div>
                            <span class="fw-bold text-xs text-cyan-950 d-block leading-tight">Aqua Guardian</span>
                            <span class="text-[10px] text-cyan-700 font-medium">1,000L Water Saved</span>
                        </div>
                    </div>
                </div>

                <!-- Badge 3 -->
                <div class="col-6 col-md-3">
                    <div class="p-3 rounded-3 bg-amber-50/60 border border-amber-200 d-flex align-items-center gap-2.5">
                        <div class="w-9 h-9 rounded-full bg-amber-500 text-white d-flex align-items-center justify-content-center text-sm shadow-xs flex-shrink-0">
                            🐝
                        </div>
                        <div>
                            <span class="fw-bold text-xs text-amber-950 d-block leading-tight">Pollinator Ally</span>
                            <span class="text-[10px] text-amber-700 font-medium">3+ Crop Varieties</span>
                        </div>
                    </div>
                </div>

                <!-- Badge 4 (Locked) -->
                <div class="col-6 col-md-3">
                    <div class="p-3 rounded-3 bg-slate-100 border border-slate-200 opacity-75 d-flex align-items-center gap-2.5">
                        <div class="w-9 h-9 rounded-full bg-slate-300 text-slate-600 d-flex align-items-center justify-content-center text-sm flex-shrink-0">
                            🔒
                        </div>
                        <div>
                            <span class="fw-bold text-xs text-slate-700 d-block leading-tight">Carbon Buster</span>
                            <span class="text-[10px] text-slate-500 font-medium">Reach 200kg CO₂ (71%)</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
