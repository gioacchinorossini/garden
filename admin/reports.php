<?php
$base_path = '../';
$page_title = "System Reports";
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
require_once '../includes/db.php';

// Calculate realistic metrics from database
$total_land_area = (float)$pdo->query("SELECT COALESCE(SUM(area_sqm), 0) FROM lands")->fetchColumn();
$occupied_plot_area = (float)$pdo->query("SELECT COALESCE(SUM(area_sqm), 0) FROM plots WHERE status = 'occupied'")->fetchColumn();
$utilization_pct = ($total_land_area > 0) ? round(($occupied_plot_area / $total_land_area) * 100, 1) : 0;

$active_plots = (int)$pdo->query("SELECT COUNT(*) FROM plots WHERE status = 'occupied'")->fetchColumn();
$total_plots = (int)$pdo->query("SELECT COUNT(*) FROM plots")->fetchColumn();
$available_plots = (int)$pdo->query("SELECT COUNT(*) FROM plots WHERE status = 'available'")->fetchColumn();
$plot_occupancy_pct = ($total_plots > 0) ? round(($active_plots / $total_plots) * 100) : 0;

$active_gardeners_count = (int)$pdo->query("SELECT COUNT(*) FROM users WHERE role = 'gardener'")->fetchColumn();
$total_harvest_kg = (float)$pdo->query("SELECT COALESCE(SUM(quantity_kg), 0) FROM harvests")->fetchColumn();
$avg_yield_per_gardener = ($active_gardeners_count > 0) ? round($total_harvest_kg / $active_gardeners_count, 1) : 0;

// Harvest by crop
$crops_data = [];
try {
    $stmtCrops = $pdo->query("SELECT crop_name, SUM(quantity_kg) as total_kg FROM harvests GROUP BY crop_name ORDER BY total_kg DESC");
    $crops_data = $stmtCrops->fetchAll(PDO::FETCH_ASSOC);
} catch (\Exception $e) {}

// Top harvests table
$top_harvests = [];
try {
    $stmtHarvests = $pdo->query("
        SELECT h.*, u.name as gardener_name, l.title as land_title
        FROM harvests h
        LEFT JOIN users u ON h.gardener_id = u.id
        LEFT JOIN plots p ON h.plot_id = p.id
        LEFT JOIN lands l ON p.land_id = l.id
        ORDER BY h.quantity_kg DESC
        LIMIT 10
    ");
    $top_harvests = $stmtHarvests->fetchAll(PDO::FETCH_ASSOC);
} catch (\Exception $e) {}

// Recent land milestone activations
$milestones = [];
try {
    $stmtM = $pdo->query("SELECT title, created_at, status FROM lands ORDER BY id ASC LIMIT 5");
    $milestones = $stmtM->fetchAll(PDO::FETCH_ASSOC);
} catch (\Exception $e) {}
?>

<main class="workspace-surface">
    <!-- Toolbar/Title Bar -->
    <div class="toolbar border-bottom">
        <div>
            <h1 class="fs-5 fw-semibold m-0 text-dark">Reports</h1>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-outline-secondary rounded-pill btn-sm px-3" onclick="window.print()">
                <i class="bi bi-printer-fill me-1"></i> Print
            </button>
            <button class="btn btn-drive-primary btn-sm px-3">
                <i class="bi bi-download me-1"></i> Export CSV
            </button>
        </div>
    </div>

    <!-- Workspace Scrollable Area -->
    <div class="workspace-scroll">
        <!-- 1. Grid of Highlights -->
        <div class="row g-3 mb-4">
            <!-- Metric Card 1: Land Utilization -->
            <div class="col-md-4">
                <div class="drive-card">
                    <span class="text-secondary fw-semibold" style="font-size: 0.75rem; letter-spacing:0.5px;">LAND UTILIZATION</span>
                    <div class="d-flex align-items-center justify-content-between mt-2">
                        <h3 class="fw-bold m-0" style="color: var(--drive-primary);"><?php echo $utilization_pct; ?>%</h3>
                    </div>
                    <div class="w-full bg-[#e1e3e1] h-2 rounded-full overflow-hidden mt-3 mb-1"
                        style="height: 6px; background-color: var(--drive-border);">
                        <div class="bg-primary h-full"
                            style="width: <?php echo min(100, $utilization_pct); ?>%; height:100%; background-color: var(--drive-primary) !important;"></div>
                    </div>
                    <small class="text-muted" style="font-size: 0.7rem;"><?php echo number_format($occupied_plot_area, 1); ?> m² / <?php echo number_format($total_land_area, 1); ?> m² occupied</small>
                </div>
            </div>

            <!-- Metric Card 2: Active plots -->
            <div class="col-md-4">
                <div class="drive-card">
                    <span class="text-secondary fw-semibold" style="font-size: 0.75rem; letter-spacing:0.5px;">ACTIVE PLOTS</span>
                    <div class="d-flex align-items-center justify-content-between mt-2">
                        <h3 class="fw-bold m-0" style="color: var(--drive-text-main);"><?php echo $active_plots; ?> / <?php echo $total_plots; ?></h3>
                        <span class="text-muted" style="font-size: 0.8rem;"><?php echo $available_plots; ?> available</span>
                    </div>
                    <div class="w-full bg-[#e1e3e1] h-2 rounded-full overflow-hidden mt-3 mb-1"
                        style="height: 6px; background-color: var(--drive-border);">
                        <div class="bg-success h-full" style="width: <?php echo min(100, $plot_occupancy_pct); ?>%; height:100%;"></div>
                    </div>
                    <small class="text-muted" style="font-size: 0.7rem;"><?php echo $plot_occupancy_pct; ?>% occupied</small>
                </div>
            </div>

            <!-- Metric Card 3: Gardener productivity -->
            <div class="col-md-4">
                <div class="drive-card">
                    <span class="text-secondary fw-semibold" style="font-size: 0.75rem; letter-spacing:0.5px;">AVG YIELD / GARDENER</span>
                    <div class="d-flex align-items-center justify-content-between mt-2">
                        <h3 class="fw-bold m-0 text-success"><?php echo number_format($avg_yield_per_gardener, 1); ?> kg</h3>
                    </div>
                    <div class="w-full bg-[#e1e3e1] h-2 rounded-full overflow-hidden mt-3 mb-1"
                        style="height: 6px; background-color: var(--drive-border);">
                        <div class="bg-success h-full" style="width: <?php echo ($total_harvest_kg > 0 ? 100 : 0); ?>%; height:100%;"></div>
                    </div>
                    <small class="text-muted" style="font-size: 0.7rem;"><?php echo $active_gardeners_count; ?> active gardeners</small>
                </div>
            </div>
        </div>

        <!-- 2. Yield Details -->
        <div class="row g-4 mb-4">
            <!-- Left Graph: Crop Distribution -->
            <div class="col-md-6">
                <div class="card border rounded-4 p-4 h-100" style="border-color: var(--drive-border) !important;">
                    <h2 class="fs-6 fw-semibold text-secondary mb-3"
                        style="letter-spacing: 0.5px; text-transform: uppercase;">Harvest by Crop</h2>

                    <?php if (empty($crops_data) || $total_harvest_kg <= 0): ?>
                        <div class="text-center py-5 text-muted">
                            <i data-lucide="sprout" class="mx-auto mb-2 text-secondary" style="width: 28px; height: 28px;"></i>
                            <p class="mb-0 text-xs text-secondary">No crop harvests logged yet.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($crops_data as $crop): ?>
                            <?php 
                            $cKg = (float)$crop['total_kg'];
                            $cPct = ($total_harvest_kg > 0) ? round(($cKg / $total_harvest_kg) * 100, 1) : 0;
                            ?>
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1 align-items-center" style="font-size: 0.8rem;">
                                    <span class="d-flex align-items-center gap-1.5 fw-semibold text-dark">
                                        <?php echo htmlspecialchars($crop['crop_name']); ?>
                                    </span>
                                    <strong><?php echo number_format($cKg, 1); ?> kg (<?php echo $cPct; ?>%)</strong>
                                </div>
                                <div class="progress" style="height: 10px; border-radius: 50rem;">
                                    <div class="progress-bar bg-success" role="progressbar"
                                        style="width: <?php echo min(100, $cPct); ?>%; border-radius: 50rem;"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right: System Activity Timeline -->
            <div class="col-md-6">
                <div class="card border rounded-4 p-4 h-100" style="border-color: var(--drive-border) !important;">
                    <h2 class="fs-6 fw-semibold text-secondary mb-3"
                        style="letter-spacing: 0.5px; text-transform: uppercase;">Milestones</h2>

                    <?php if (empty($milestones)): ?>
                        <div class="text-center py-5 text-muted">
                            <i data-lucide="flag" class="mx-auto mb-2 text-secondary" style="width: 28px; height: 28px;"></i>
                            <p class="mb-0 text-xs text-secondary">No milestones recorded yet.</p>
                        </div>
                    <?php else: ?>
                        <div class="position-relative ps-4 border-start"
                            style="border-color: var(--drive-border) !important; font-size: 0.825rem;">
                            <?php foreach ($milestones as $idx => $m): ?>
                                <div class="<?php echo ($idx < count($milestones) - 1) ? 'mb-3' : ''; ?> position-relative">
                                    <span class="position-absolute bg-primary rounded-circle"
                                        style="width: 10px; height: 10px; left: -29px; top: 5px;"></span>
                                    <strong class="text-dark d-block"><?php echo htmlspecialchars($m['title']); ?></strong>
                                    <span class="text-muted d-block" style="font-size:0.75rem;">
                                        <?php echo !empty($m['created_at']) ? date('M Y', strtotime($m['created_at'])) : 'Recent'; ?> • <?php echo ucfirst(htmlspecialchars($m['status'])); ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- 3. Top Harvest Logs Table -->
        <h2 class="fs-6 fw-semibold text-secondary mb-3" style="letter-spacing: 0.5px; text-transform: uppercase;">Top Harvests</h2>
        <div class="border rounded-4 bg-white overflow-hidden border-light-subtle">
            <!-- Header Row -->
            <div class="d-flex align-items-center justify-content-between px-4 py-2 bg-light border-bottom text-secondary"
                style="font-size: 0.75rem; font-weight: 600;">
                <div class="w-25">CROP</div>
                <div class="w-25">GARDENER</div>
                <div class="w-25">LAND</div>
                <div class="w-15">YIELD</div>
                <div class="w-10 text-end">DATE</div>
            </div>

            <?php if (empty($top_harvests)): ?>
                <div class="text-center py-5 text-muted">
                    <i data-lucide="shopping-bag" class="mx-auto mb-2 text-secondary" style="width: 28px; height: 28px;"></i>
                    <p class="mb-0 text-xs text-secondary">No harvest records found.</p>
                </div>
            <?php else: ?>
                <?php foreach ($top_harvests as $th): ?>
                    <div class="d-flex align-items-center justify-content-between px-4 py-2 border-bottom hover:bg-[#f8faff] text-sm text-dark transition-colors"
                        style="height: 48px;">
                        <div class="w-25 d-flex align-items-center gap-2 fw-semibold">
                            <i data-lucide="sprout" style="width: 18px; height: 18px;" class="text-success"></i>
                            <?php echo htmlspecialchars($th['crop_name']); ?>
                        </div>
                        <div class="w-25 text-secondary"><?php echo htmlspecialchars($th['gardener_name'] ?? 'Gardener'); ?></div>
                        <div class="w-25 text-secondary"><?php echo htmlspecialchars($th['land_title'] ?? 'Community Garden'); ?></div>
                        <div class="w-15 fw-bold text-success"><?php echo number_format($th['quantity_kg'], 1); ?> kg</div>
                        <div class="w-10 text-end text-muted" style="font-size: 0.75rem;">
                            <?php echo !empty($th['harvest_date']) ? date('M d, Y', strtotime($th['harvest_date'])) : ''; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>