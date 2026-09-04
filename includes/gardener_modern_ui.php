<?php
// Modern Botanical Studio UI for Gardener
?>
<main class="modern-workspace p-4">
    <!-- Hero Banner -->
    <div class="modern-hero-banner mb-4">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <span class="badge bg-white text-dark rounded-pill px-3 py-1 text-uppercase mb-2 font-semibold" style="font-size: 0.7rem; letter-spacing: 1px;">
                    <i class="bi bi-flower1 text-success me-1"></i> Gardener Botanical Studio V2
                </span>
                <h1 class="fw-bold mb-2 text-white" style="font-size: 1.75rem;">Happy Gardening, Mary!</h1>
                <p class="mb-0 text-white-50 text-sm">
                    Monitor crop growth stages, schedule watering tasks, log fresh harvests, and earn Green Guardian eco rewards.
                </p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                    <a href="harvests.php" class="btn btn-light text-success fw-bold rounded-pill px-3 py-2 shadow-sm d-inline-flex align-items-center gap-2 text-sm">
                        <i class="bi bi-basket-fill"></i>
                        <span>Log Harvest</span>
                    </a>
                    <a href="search.php" class="btn btn-outline-light rounded-pill px-3 py-2 text-sm">
                        <i class="bi bi-search me-1"></i> Find Plots
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 3 Key Stat Cards -->
    <div class="row g-3 mb-4">
        <!-- Stat 1: Leased Plots -->
        <div class="col-md-4">
            <div class="stat-card-v2">
                <div class="stat-icon-wrapper" style="background-color: #ecfdf5; color: #059669;">
                    <i class="bi bi-grid-fill"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="text-muted fw-semibold text-xs text-uppercase">Leased Plots</span>
                        <span class="badge badge-emerald">2 Active</span>
                    </div>
                    <h3 class="fw-bold m-0 mt-1" style="font-size: 1.75rem; color: #0f172a;">2 <span class="text-xs fw-normal text-muted">Plots</span></h3>
                    <span class="text-xs text-muted mt-2 d-block">
                        <i class="bi bi-check-circle-fill text-success me-1"></i> Downtown Rooftop (B-1, B-2)
                    </span>
                </div>
            </div>
        </div>

        <!-- Stat 2: Harvest Yield -->
        <div class="col-md-4">
            <div class="stat-card-v2">
                <div class="stat-icon-wrapper" style="background-color: #f3e8ff; color: #7e22ce;">
                    <i class="bi bi-bag-check-fill"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="text-muted fw-semibold text-xs text-uppercase">Total Yield</span>
                        <span class="badge badge-purple">Top 5%</span>
                    </div>
                    <h3 class="fw-bold m-0 mt-1" style="font-size: 1.75rem; color: #581c87;">57.5 <span class="text-sm font-semibold">kg</span></h3>
                    <span class="text-xs text-muted mt-2 d-block">
                        <i class="bi bi-cash-stack text-success me-1"></i> ~$230 Fresh Organic Value
                    </span>
                </div>
            </div>
        </div>

        <!-- Stat 3: Upcoming Tasks -->
        <div class="col-md-4">
            <div class="stat-card-v2" style="border-left: 4px solid #3b82f6;">
                <div class="stat-icon-wrapper" style="background-color: #dbeafe; color: #1d4ed8;">
                    <i class="bi bi-calendar-check-fill"></i>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="text-muted fw-semibold text-xs text-uppercase">Schedule Tasks</span>
                        <span class="badge badge-blue">2 Pending</span>
                    </div>
                    <h3 class="fw-bold m-0 mt-1 text-primary" style="font-size: 1.75rem;">2 <span class="text-xs fw-normal text-muted">Tasks</span></h3>
                    <span class="text-xs text-muted mt-2 d-block">
                        <i class="bi bi-droplet-fill text-primary me-1"></i> Water Tomatoes tomorrow at 9 AM
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Green Guardian Eco-Tracker Integration -->
    <?php include '../includes/eco_tracker.php'; ?>

    <!-- Active Crops & Plot Visualizer Section -->
    <div class="d-flex align-items-center justify-content-between mb-3 mt-4">
        <div>
            <h2 class="fw-bold m-0 text-slate-800" style="font-size: 1.15rem;">My Active Garden Plots</h2>
            <p class="text-muted text-xs mb-0">Live crop status, growth progress, and care action items</p>
        </div>
        <a href="schedules.php" class="btn btn-sm btn-outline-success rounded-pill px-3 font-semibold">View Full Calendar</a>
    </div>

    <div class="row g-4 mb-4">
        <!-- Plot Card 1: Tomato -->
        <div class="col-md-6">
            <div class="modern-card p-3">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <img src="../assets/crop-icons/tomato/tomato.svg" style="width: 32px; height: 32px;" alt="Tomato">
                        <div>
                            <h3 class="fw-bold text-dark mb-0" style="font-size: 1.05rem;">Downtown Rooftop - Plot B-1</h3>
                            <span class="text-xs text-muted">20.0 m² • High Sun Exposure</span>
                        </div>
                    </div>
                    <span class="badge badge-emerald"><span class="pulse-dot emerald me-1"></span> Healthy (98%)</span>
                </div>

                <div class="bg-light p-3 rounded-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1 text-xs">
                        <span class="fw-semibold text-dark">Crop: Organic Tomatoes</span>
                        <span class="text-emerald-700 font-semibold">Stage: Fruiting (75%)</span>
                    </div>
                    <div class="crop-progress-track">
                        <div class="crop-progress-fill" style="width: 75%;"></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center text-xs text-muted mt-2">
                        <span>Planted: July 10</span>
                        <span>Est. Harvest: Sept 15</span>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between">
                    <span class="text-xs text-muted"><i class="bi bi-droplet-fill text-primary me-1"></i> Watered 4h ago</span>
                    <div class="d-flex gap-2">
                        <a href="harvests.php" class="btn btn-sm btn-success rounded-pill px-3 font-semibold">Record Yield</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Plot Card 2: Romaine Lettuce -->
        <div class="col-md-6">
            <div class="modern-card p-3">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                    <div class="d-flex align-items-center gap-2">
                        <img src="../assets/crop-icons/romaine/romaine.svg" style="width: 32px; height: 32px;" alt="Lettuce">
                        <div>
                            <h3 class="fw-bold text-dark mb-0" style="font-size: 1.05rem;">Downtown Rooftop - Plot B-2</h3>
                            <span class="text-xs text-muted">20.0 m² • Partial Shade</span>
                        </div>
                    </div>
                    <span class="badge badge-amber"><i class="bi bi-check-circle-fill me-1"></i> Ready to Harvest</span>
                </div>

                <div class="bg-light p-3 rounded-3 mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1 text-xs">
                        <span class="fw-semibold text-dark">Crop: Romaine Lettuce</span>
                        <span class="text-amber-700 font-semibold">Stage: Mature (90%)</span>
                    </div>
                    <div class="crop-progress-track">
                        <div class="crop-progress-fill" style="width: 90%; background: linear-gradient(90deg, #f59e0b 0%, #10b981 100%);"></div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center text-xs text-muted mt-2">
                        <span>Planted: June 20</span>
                        <span>Harvest Window: Active Now</span>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-between">
                    <span class="text-xs text-muted"><i class="bi bi-exclamation-triangle-fill text-warning me-1"></i> Harvest recommended today</span>
                    <div class="d-flex gap-2">
                        <a href="harvests.php" class="btn btn-sm btn-success rounded-pill px-3 font-semibold">Harvest Now</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
