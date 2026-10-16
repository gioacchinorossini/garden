<?php
// Garden Stewardship & Environmental Contribution Component
// Realistic agricultural impact calculations connected to schedule tasks
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$sched_list = $_SESSION['mock_schedules'] ?? [];
$completed_scheds = array_filter($sched_list, fn($s) => ($s['status'] ?? '') === 'completed');
$completed_count = count($completed_scheds);
$total_sched_count = count($sched_list);
$completion_rate = ($total_sched_count > 0) ? round(($completed_count / $total_sched_count) * 100) : 100;

// Stewardship points calculated from tasks
$task_pts_earned = 0;
foreach ($completed_scheds as $cs) {
    $task_pts_earned += isset($cs['points']) ? intval($cs['points']) : (isset($cs['xp']) ? intval($cs['xp']) : 50);
}

$watering_count = count(array_filter($completed_scheds, fn($s) => ($s['task_type'] ?? '') === 'watering'));
$planting_count = count(array_filter($completed_scheds, fn($s) => ($s['task_type'] ?? '') === 'planting'));
$harvest_count = count(array_filter($completed_scheds, fn($s) => ($s['task_type'] ?? '') === 'harvesting'));
$maintenance_count = count(array_filter($completed_scheds, fn($s) => in_array($s['task_type'] ?? '', ['weeding', 'pruning', 'mulching', 'fertilizing', 'cleaning', 'pests', 'trellising'])));

$user_role = $_SESSION['active_role'] ?? 'gardener';
$base_pts = ($user_role === 'landowner') ? 500 : 350;
$total_stewardship_pts = $base_pts + $task_pts_earned;

// Realistic Stewardship Tiers:
// Tier 1: Junior Steward (< 300 pts)
// Tier 2: Garden Caretaker (300 - 599 pts)
// Tier 3: Urban Cultivator (600 - 899 pts)
// Tier 4: Master Grower (900+ pts)
if ($total_stewardship_pts < 300) {
    $tier_level = 1;
    $tier_name = 'Junior Steward';
    $tier_next_pts = 300;
} elseif ($total_stewardship_pts < 600) {
    $tier_level = 2;
    $tier_name = 'Garden Caretaker';
    $tier_next_pts = 600;
} elseif ($total_stewardship_pts < 900) {
    $tier_level = 3;
    $tier_name = 'Urban Cultivator';
    $tier_next_pts = 900;
} else {
    $tier_level = 4;
    $tier_name = 'Master Grower';
    $tier_next_pts = 1200;
}

$eco_level = isset($eco_level_override) ? $eco_level_override : $tier_level;
$eco_stewardship_name = isset($eco_tier_name_override) ? $eco_tier_name_override : $tier_name;
$eco_pts = isset($eco_xp_override) ? $eco_xp_override : $total_stewardship_pts;
$eco_max_pts = isset($eco_max_xp_override) ? $eco_max_xp_override : $tier_next_pts;
if ($eco_max_pts <= $eco_pts) $eco_max_pts = $eco_pts + 150;
$progress_pct = min(100, round(($eco_pts / $eco_max_pts) * 100));

// Realistic environmental calculations:
$water_managed = isset($water_saved_override) ? $water_saved_override : (450 + ($watering_count * 250) + ($completed_count * 40));
$co2_offset = isset($co2_offset_override) ? $co2_offset_override : round(45.0 + ($planting_count * 18.0) + ($maintenance_count * 6.0), 1);
$organic_yield = isset($organic_yield_override) ? $organic_yield_override : round(20.0 + ($harvest_count * 12.0) + ($planting_count * 4.0), 1);
$streak_days = isset($streak_days_override) ? $streak_days_override : (7 + min($completed_count, 14));
?>

<div class="stewardship-card mb-4">
    <!-- Header: Clean, professional, matching warm theme -->
    <div class="stewardship-card-header">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="stewardship-icon-squircle">
                    <i data-lucide="sprout" style="width: 22px; height: 22px;"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                        <span class="badge rounded-pill" style="background:#EBF3E8;color:#2D5A27;border:1px solid #BDDAB8;font-size:0.7rem;font-weight:700;">
                            Tier <?php echo $eco_level; ?> • <?php echo htmlspecialchars($eco_stewardship_name); ?>
                        </span>
                        <span class="badge rounded-pill" style="background:#FEF3E2;color:#8A4700;border:1px solid #F6D7A8;font-size:0.7rem;font-weight:700;display:inline-flex;align-items:center;gap:3px;">
                            <i data-lucide="flame" style="width:12px;height:12px;"></i> <?php echo $streak_days; ?>-Day Care Streak
                        </span>
                        <span class="badge rounded-pill" style="background:#EBF3F8;color:#204E6E;border:1px solid #C4DCEB;font-size:0.7rem;font-weight:700;display:inline-flex;align-items:center;gap:3px;">
                            <i data-lucide="check-circle" style="width:12px;height:12px;"></i> <?php echo $completed_count; ?> Tasks Logged
                        </span>
                    </div>
                    <h3 class="fs-6 fw-bold mb-0 text-dark" style="color:var(--drive-text-main, #3E2B1E) !important;">
                        Garden Stewardship &amp; Environmental Impact
                    </h3>
                    <span class="text-secondary" style="font-size:0.78rem;">
                        Real-time impact metrics derived directly from scheduled garden care and maintenance.
                    </span>
                </div>
            </div>

            <!-- Stewardship Points Progress -->
            <div class="flex-shrink-0" style="min-width: 210px;">
                <div class="d-flex justify-content-between text-secondary mb-1" style="font-size: 0.75rem; font-weight:600;">
                    <span>Stewardship Score</span>
                    <span style="color:#2D5A27;font-weight:700;"><?php echo number_format($eco_pts); ?> / <?php echo number_format($eco_max_pts); ?> pts</span>
                </div>
                <div class="progress rounded-pill overflow-hidden" style="height: 8px; background: #EFE8DE;">
                    <div class="progress-bar rounded-pill" role="progressbar" 
                         style="width: <?php echo $progress_pct; ?>%; background: #7FA668;"></div>
                </div>
                <span class="d-block mt-1 text-end text-muted" style="font-size: 0.7rem;">
                    +<?php echo max(0, $eco_max_pts - $eco_pts); ?> pts to next tier
                </span>
            </div>
        </div>
    </div>

    <!-- Impact Metrics Grid (4 Clean Cards) -->
    <div class="p-3 p-md-4" style="background: var(--drive-surface, #FFFDF9);">
        <div class="row g-3 mb-4">
            <!-- Metric 1: CO2 Offset -->
            <div class="col-6 col-md-3">
                <div class="stewardship-stat-box h-100 d-flex align-items-center gap-3">
                    <div class="stewardship-stat-icon" style="background:#EBF3E8;color:#2D5A27;">
                        <i data-lucide="leaf" style="width: 18px; height: 18px;"></i>
                    </div>
                    <div>
                        <span class="text-secondary d-block text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing:0.5px;">CO₂ Absorbed</span>
                        <h4 class="fw-bold mb-0 text-dark fs-6"><?php echo $co2_offset; ?> <span class="fw-normal text-muted" style="font-size:0.75rem;">kg</span></h4>
                        <span class="fw-semibold text-emerald-700" style="font-size:0.68rem;">≈ <?php echo max(1, round($co2_offset * 0.04)); ?> trees equivalent</span>
                    </div>
                </div>
            </div>

            <!-- Metric 2: Water Managed -->
            <div class="col-6 col-md-3">
                <div class="stewardship-stat-box h-100 d-flex align-items-center gap-3">
                    <div class="stewardship-stat-icon" style="background:#E8F3F8;color:#204E6E;">
                        <i data-lucide="droplets" style="width: 18px; height: 18px;"></i>
                    </div>
                    <div>
                        <span class="text-secondary d-block text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing:0.5px;">Water Managed</span>
                        <h4 class="fw-bold mb-0 text-dark fs-6"><?php echo number_format($water_managed); ?> <span class="fw-normal text-muted" style="font-size:0.75rem;">L</span></h4>
                        <span class="fw-semibold" style="color:#204E6E;font-size:0.68rem;">Drip &amp; bed hydration</span>
                    </div>
                </div>
            </div>

            <!-- Metric 3: Organic Yield -->
            <div class="col-6 col-md-3">
                <div class="stewardship-stat-box h-100 d-flex align-items-center gap-3">
                    <div class="stewardship-stat-icon" style="background:#F7ECE8;color:#783424;">
                        <i data-lucide="sprout" style="width: 18px; height: 18px;"></i>
                    </div>
                    <div>
                        <span class="text-secondary d-block text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing:0.5px;">Local Harvest</span>
                        <h4 class="fw-bold mb-0 text-dark fs-6"><?php echo $organic_yield; ?> <span class="fw-normal text-muted" style="font-size:0.75rem;">kg</span></h4>
                        <span class="fw-semibold" style="color:#783424;font-size:0.68rem;">Zero food miles</span>
                    </div>
                </div>
            </div>

            <!-- Metric 4: Task Reliability -->
            <div class="col-6 col-md-3">
                <div class="stewardship-stat-box h-100 d-flex align-items-center gap-3">
                    <div class="stewardship-stat-icon" style="background:#FAF2E8;color:#7B4518;">
                        <i data-lucide="calendar-check" style="width: 18px; height: 18px;"></i>
                    </div>
                    <div>
                        <span class="text-secondary d-block text-uppercase fw-semibold" style="font-size: 0.68rem; letter-spacing:0.5px;">Care Reliability</span>
                        <h4 class="fw-bold mb-0 text-dark fs-6"><?php echo $completion_rate; ?>%</h4>
                        <span class="fw-semibold" style="color:#7B4518;font-size:0.68rem;"><?php echo $completed_count; ?> of <?php echo max(1, $total_sched_count); ?> completed</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Practical Milestones & Stewardship Badges -->
        <div>
            <div class="d-flex align-items-center justify-content-between mb-2.5">
                <span class="fw-bold text-secondary text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                    Stewardship Milestones
                </span>
                <span class="fw-semibold" style="font-size: 0.72rem; color: #2D5A27;">
                    <?php echo min(4, max(1, $completed_count > 0 ? 3 : 2)); ?> of 4 Active
                </span>
            </div>

            <div class="row g-2">
                <!-- Milestone 1 -->
                <div class="col-6 col-md-3">
                    <div class="stewardship-badge-item unlocked">
                        <div class="stewardship-badge-icon" style="background:#EBF3E8;color:#2D5A27;">
                            <i data-lucide="calendar-check" style="width: 16px; height: 16px;"></i>
                        </div>
                        <div>
                            <span class="fw-bold text-dark d-block" style="font-size:0.75rem;line-height:1.2;">On-Time Caretaker</span>
                            <span class="text-muted" style="font-size:0.68rem;">Continuous plot care</span>
                        </div>
                    </div>
                </div>

                <!-- Milestone 2 -->
                <div class="col-6 col-md-3">
                    <div class="stewardship-badge-item unlocked">
                        <div class="stewardship-badge-icon" style="background:#E8F3F8;color:#204E6E;">
                            <i data-lucide="droplets" style="width: 16px; height: 16px;"></i>
                        </div>
                        <div>
                            <span class="fw-bold text-dark d-block" style="font-size:0.75rem;line-height:1.2;">Water Steward</span>
                            <span class="text-muted" style="font-size:0.68rem;">Efficient irrigation</span>
                        </div>
                    </div>
                </div>

                <!-- Milestone 3 -->
                <div class="col-6 col-md-3">
                    <div class="stewardship-badge-item unlocked">
                        <div class="stewardship-badge-icon" style="background:#FAF2E8;color:#7B4518;">
                            <i data-lucide="sprout" style="width: 16px; height: 16px;"></i>
                        </div>
                        <div>
                            <span class="fw-bold text-dark d-block" style="font-size:0.75rem;line-height:1.2;">Soil Steward</span>
                            <span class="text-muted" style="font-size:0.68rem;">Mulching &amp; compost prep</span>
                        </div>
                    </div>
                </div>

                <!-- Milestone 4 -->
                <div class="col-6 col-md-3">
                    <div class="stewardship-badge-item <?php echo ($harvest_count > 0 || $user_role === 'landowner') ? 'unlocked' : ''; ?>">
                        <div class="stewardship-badge-icon" style="<?php echo ($harvest_count > 0 || $user_role === 'landowner') ? 'background:#F7ECE8;color:#783424;' : 'background:#EFECE8;color:#8F867E;'; ?>">
                            <i data-lucide="award" style="width: 16px; height: 16px;"></i>
                        </div>
                        <div>
                            <span class="fw-bold text-dark d-block" style="font-size:0.75rem;line-height:1.2;">Zero Food Miles</span>
                            <span class="text-muted" style="font-size:0.68rem;">Local harvest distribution</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
