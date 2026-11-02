<?php
$base_path = '../';
$page_title = "Rental Earnings Report";
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
require_once '../includes/db.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['active_role'] = 'landowner';
$owner_id = $_SESSION['user_id'] ?? 2;
$owner_name = $_SESSION['user_name'] ?? 'John Landowner';

// Fetch landowner's plots and properties
$plots_stmt = $pdo->prepare("
    SELECT p.*, l.title as land_title, l.address as land_address, l.area_sqm as land_total_area, l.status as land_status
    FROM `plots` p
    JOIN `lands` l ON p.land_id = l.id
    WHERE l.owner_id = ? OR l.landowner_name = ?
    ORDER BY l.id ASC, p.plot_number ASC
");
$plots_stmt->execute([$owner_id, $owner_name]);
$all_plots = $plots_stmt->fetchAll(PDO::FETCH_ASSOC);

// If user has no lands yet, fall back to all plots to preview demo data gracefully
if (empty($all_plots)) {
    $fallback_stmt = $pdo->query("
        SELECT p.*, l.title as land_title, l.address as land_address, l.area_sqm as land_total_area, l.status as land_status
        FROM `plots` p
        JOIN `lands` l ON p.land_id = l.id
        ORDER BY l.id ASC, p.plot_number ASC
    ");
    $all_plots = $fallback_stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Financial calculations
$occupied_plots = array_filter($all_plots, fn($p) => $p['status'] === 'occupied');
$available_plots = array_filter($all_plots, fn($p) => $p['status'] === 'available');

$total_occupied_annual = 0.0;
foreach ($occupied_plots as $p) {
    $total_occupied_annual += floatval($p['rental_price_per_year'] ?? 110000.00);
}
$total_occupied_monthly = $total_occupied_annual / 12;

$total_potential_annual = 0.0;
foreach ($all_plots as $p) {
    $total_potential_annual += floatval($p['rental_price_per_year'] ?? 110000.00);
}
$total_potential_monthly = $total_potential_annual / 12;

$total_plots_count = count($all_plots);
$occupied_count = count($occupied_plots);
$available_count = count($available_plots);
$occupancy_rate = $total_plots_count > 0 ? round(($occupied_count / $total_plots_count) * 100, 1) : 0;

// Group earnings by property
$property_earnings = [];
foreach ($all_plots as $p) {
    $lid = $p['land_id'];
    if (!isset($property_earnings[$lid])) {
        $property_earnings[$lid] = [
            'id' => $lid,
            'title' => $p['land_title'],
            'address' => $p['land_address'],
            'total_area' => $p['land_total_area'],
            'total_plots' => 0,
            'occupied_plots' => 0,
            'annual_earned' => 0.0,
            'annual_potential' => 0.0,
        ];
    }
    $property_earnings[$lid]['total_plots']++;
    $rate = floatval($p['rental_price_per_year'] ?? 110000.00);
    $property_earnings[$lid]['annual_potential'] += $rate;
    if ($p['status'] === 'occupied') {
        $property_earnings[$lid]['occupied_plots']++;
        $property_earnings[$lid]['annual_earned'] += $rate;
    }
}
?>

<style>
/* Modern Report View Modes & Chips */
.report-view-toggle-btn {
    border: 2px solid transparent !important;
    font-size: 0.8rem;
    font-weight: 700;
    padding: 6px 16px;
    border-radius: 9999px !important;
    color: var(--drive-text-sub);
    background: transparent;
    transition: all 0.2s ease;
}
.report-view-toggle-btn:hover {
    color: var(--drive-text-main) !important;
    background: #EAE1D2 !important;
}
.report-view-toggle-btn.active {
    background: #7FA668 !important;
    color: #FFFDF9 !important;
    border: 2px solid #523E31 !important;
    box-shadow: 0 2.5px 0 #523E31 !important;
}

.report-metric-card {
    background: #FFFDF9;
    border: 1.5px solid var(--drive-border-subtle, #DFCFC2);
    border-radius: 18px;
    padding: 18px 20px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.report-metric-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(82, 62, 49, 0.08);
}

.currency-symbol {
    font-size: 0.9em;
    font-weight: 600;
    opacity: 0.85;
}

@media print {
    body * {
        visibility: hidden;
    }
    #printableReportArea, #printableReportArea * {
        visibility: visible;
    }
    #printableReportArea {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    .no-print {
        display: none !important;
    }
}
</style>

<main class="workspace-surface">
    <!-- Desktop Toolbar -->
    <div class="toolbar d-none d-md-flex justify-content-between align-items-center no-print">
        <div>
            <h1 class="fs-5 fw-semibold m-0 text-dark">Rental Earnings Report</h1>
            <p class="text-muted mb-0" style="font-size: 0.78rem;">Financial performance and lease revenue across all your gardening plots</p>
        </div>

        <div class="d-flex align-items-center gap-3">
            <!-- View Mode Switcher: Monthly vs Annually -->
            <div class="filter-pill-track p-1 bg-light rounded-pill border" role="group">
                <button type="button" class="btn btn-sm report-view-toggle-btn active" id="btnModeMonthly" onclick="switchReportMode('monthly')">
                    <i class="bi bi-calendar-month me-1"></i> Monthly View
                </button>
                <button type="button" class="btn btn-sm report-view-toggle-btn" id="btnModeAnnual" onclick="switchReportMode('annual')">
                    <i class="bi bi-calendar-check me-1"></i> Annual View
                </button>
            </div>

            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold" onclick="window.print()">
                    <i class="bi bi-printer me-1"></i> Print
                </button>
                <button class="btn btn-sm btn-drive-primary rounded-pill px-3.5 py-1.5 fw-semibold text-white shadow-xs" onclick="exportEarningsCSV()">
                    <i class="bi bi-download me-1"></i> Export CSV
                </button>
            </div>
        </div>
    </div>

    <!-- Workspace Scrollable Area -->
    <div class="workspace-scroll p-3 px-md-4 pt-md-2 pb-5" id="printableReportArea">
        
        <!-- Mobile Controls (Visible only on < md) -->
        <div class="d-block d-md-none mb-3 no-print">
            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                <h1 class="fs-6 fw-bold m-0 text-dark">Rental Earnings</h1>
                <div class="d-flex gap-1.5">
                    <button class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 text-xs" onclick="window.print()">
                        <i class="bi bi-printer"></i>
                    </button>
                    <button class="btn btn-xs btn-drive-primary rounded-pill px-2.5 py-1 text-xs" onclick="exportEarningsCSV()">
                        <i class="bi bi-download"></i>
                    </button>
                </div>
            </div>
            <div class="d-flex p-1 bg-light rounded-pill border justify-content-between">
                <button type="button" class="btn btn-sm report-view-toggle-btn active flex-fill py-1 text-xs" id="mBtnModeMonthly" onclick="switchReportMode('monthly')">
                    Monthly
                </button>
                <button type="button" class="btn btn-sm report-view-toggle-btn flex-fill py-1 text-xs" id="mBtnModeAnnual" onclick="switchReportMode('annual')">
                    Annual
                </button>
            </div>
        </div>

        <!-- Print-only Header -->
        <div class="d-none d-print-block mb-4 pb-2 border-bottom">
            <h2 class="fw-bold fs-4 mb-1">Idle Land Community Gardening — Landowner Rental Report</h2>
            <p class="text-muted small mb-0">Generated on <?php echo date('F d, Y h:i A'); ?> for <strong><?php echo htmlspecialchars($owner_name); ?></strong></p>
        </div>

        <!-- 1. Key Performance Metric Cards -->
        <div class="row g-3 mb-4">
            <!-- Metric Card 1: Active Rental Income (Dynamic Monthly / Annual) -->
            <div class="col-sm-6 col-xl-3">
                <div class="report-metric-card h-100 d-flex flex-column justify-content-between shadow-xs">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-secondary fw-semibold text-xs text-uppercase" style="letter-spacing: 0.5px;" id="lblActiveIncome">ACTIVE MONTHLY INCOME</span>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5 text-xs">
                                <i class="bi bi-graph-up-arrow me-1"></i> Active
                            </span>
                        </div>
                        <h2 class="fw-bold m-0 text-success mb-1" id="valActiveIncome">
                            ₱<?php echo number_format($total_occupied_monthly, 2); ?>
                        </h2>
                    </div>
                    <div class="pt-2 border-top mt-2">
                        <small class="text-muted" id="subActiveIncome" style="font-size: 0.75rem;">
                            Annual equivalent: <strong>₱<?php echo number_format($total_occupied_annual, 2); ?>/yr</strong>
                        </small>
                    </div>
                </div>
            </div>

            <!-- Metric Card 2: Projected Potential at 100% Occupancy -->
            <div class="col-sm-6 col-xl-3">
                <div class="report-metric-card h-100 d-flex flex-column justify-content-between shadow-xs">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-secondary fw-semibold text-xs text-uppercase" style="letter-spacing: 0.5px;" id="lblPotentialIncome">MONTHLY CAPACITY (100%)</span>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-0.5 text-xs">
                                Capacity
                            </span>
                        </div>
                        <h2 class="fw-bold m-0 text-dark mb-1" id="valPotentialIncome">
                            ₱<?php echo number_format($total_potential_monthly, 2); ?>
                        </h2>
                    </div>
                    <div class="pt-2 border-top mt-2">
                        <small class="text-muted" id="subPotentialIncome" style="font-size: 0.75rem;">
                            Full Annual Capacity: <strong>₱<?php echo number_format($total_potential_annual, 2); ?>/yr</strong>
                        </small>
                    </div>
                </div>
            </div>

            <!-- Metric Card 3: Plot Occupancy Rate -->
            <div class="col-sm-6 col-xl-3">
                <div class="report-metric-card h-100 d-flex flex-column justify-content-between shadow-xs">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-secondary fw-semibold text-xs text-uppercase" style="letter-spacing: 0.5px;">OCCUPANCY RATE</span>
                            <span class="badge bg-light text-secondary border rounded-pill px-2 py-0.5 text-xs">
                                <?php echo $occupied_count; ?> of <?php echo $total_plots_count; ?> Plots
                            </span>
                        </div>
                        <h2 class="fw-bold m-0 text-dark mb-1">
                            <?php echo $occupancy_rate; ?>%
                        </h2>
                        <div class="w-100 bg-light rounded-pill overflow-hidden mt-2" style="height: 6px; background-color: #E2E8F0;">
                            <div class="h-100 bg-success" style="width: <?php echo min(100, $occupancy_rate); ?>%;"></div>
                        </div>
                    </div>
                    <div class="pt-2 border-top mt-2 d-flex align-items-center justify-content-between">
                        <small class="text-success fw-semibold" style="font-size: 0.72rem;"><?php echo $occupied_count; ?> Occupied</small>
                        <small class="text-muted" style="font-size: 0.72rem;"><?php echo $available_count; ?> Available for lease</small>
                    </div>
                </div>
            </div>

            <!-- Metric Card 4: Average Monthly Yield per Plot -->
            <div class="col-sm-6 col-xl-3">
                <div class="report-metric-card h-100 d-flex flex-column justify-content-between shadow-xs">
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-secondary fw-semibold text-xs text-uppercase" style="letter-spacing: 0.5px;">AVG RENT PER PLOT</span>
                            <span class="badge bg-light text-secondary border rounded-pill px-2 py-0.5 text-xs">
                                50m² Benchmark
                            </span>
                        </div>
                        <?php 
                        $avg_occupied_annual = $occupied_count > 0 ? ($total_occupied_annual / $occupied_count) : ($total_plots_count > 0 ? ($total_potential_annual / $total_plots_count) : 110000);
                        $avg_occupied_monthly = $avg_occupied_annual / 12;
                        ?>
                        <h2 class="fw-bold m-0 text-dark mb-1" id="valAvgPlot">
                            ₱<?php echo number_format($avg_occupied_monthly, 2); ?>
                        </h2>
                    </div>
                    <div class="pt-2 border-top mt-2">
                        <small class="text-muted" id="subAvgPlot" style="font-size: 0.75rem;">
                            Standard benchmark: <strong>₱9,166.67/mo (₱110,000/yr)</strong>
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Property Breakdown Cards -->
        <div class="card border rounded-4 bg-white p-3 p-md-4 shadow-xs mb-4" style="border-color: var(--drive-border) !important;">
            <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 border-bottom pb-3 mb-3">
                <div>
                    <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 1rem;">
                        <i class="bi bi-buildings-fill text-success"></i>
                        <span>Rental Earnings by Registered Property</span>
                    </h5>
                    <p class="text-muted mb-0 mt-0.5" style="font-size: 0.75rem;">Revenue breakdown across each of your registered land titles</p>
                </div>
                <span class="badge bg-light text-secondary border rounded-pill px-3 py-1.5 text-xs">
                    <?php echo count($property_earnings); ?> Properties
                </span>
            </div>

            <div class="row g-3">
                <?php foreach ($property_earnings as $prop): ?>
                    <?php 
                    $prop_occ_rate = $prop['total_plots'] > 0 ? round(($prop['occupied_plots'] / $prop['total_plots']) * 100) : 0;
                    $prop_earned_monthly = $prop['annual_earned'] / 12;
                    $prop_potential_monthly = $prop['annual_potential'] / 12;
                    ?>
                    <div class="col-md-6 col-xl-4">
                        <div class="p-3 rounded-4 border bg-light h-100 d-flex flex-column justify-content-between" style="border-color: var(--drive-border) !important;">
                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-1.5">
                                    <h6 class="fw-bold text-dark mb-0 text-truncate" title="<?php echo htmlspecialchars($prop['title']); ?>">
                                        <?php echo htmlspecialchars($prop['title']); ?>
                                    </h6>
                                    <span class="badge bg-white text-dark border rounded-pill text-[10px] px-2 py-0.5">
                                        <?php echo $prop['occupied_plots']; ?>/<?php echo $prop['total_plots']; ?> Leased
                                    </span>
                                </div>
                                <div class="text-muted text-xs mb-2.5 text-truncate" style="font-size: 0.75rem;">
                                    <i class="bi bi-geo-alt-fill text-danger me-1"></i><?php echo htmlspecialchars($prop['address'] ?? 'Land Location'); ?>
                                </div>

                                <div class="p-2.5 bg-white rounded-3 border mb-2">
                                    <div class="d-flex align-items-baseline justify-content-between">
                                        <span class="text-secondary text-xs fw-semibold prop-mode-lbl">MONTHLY EARNINGS:</span>
                                        <span class="fw-bold text-success prop-earned-display" data-monthly="<?php echo $prop_earned_monthly; ?>" data-annual="<?php echo $prop['annual_earned']; ?>">
                                            ₱<?php echo number_format($prop_earned_monthly, 2); ?>
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-baseline justify-content-between mt-1 pt-1 border-top" style="font-size: 0.75rem;">
                                        <span class="text-muted">Total Potential:</span>
                                        <span class="text-dark fw-medium prop-potential-display" data-monthly="<?php echo $prop_potential_monthly; ?>" data-annual="<?php echo $prop['annual_potential']; ?>">
                                            ₱<?php echo number_format($prop_potential_monthly, 2); ?>/mo
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <div class="d-flex align-items-center justify-content-between text-xs text-muted mb-1">
                                    <span>Occupancy</span>
                                    <span class="fw-semibold text-dark"><?php echo $prop_occ_rate; ?>%</span>
                                </div>
                                <div class="w-100 bg-white rounded-pill overflow-hidden" style="height: 5px; border: 1px solid #E2E8F0;">
                                    <div class="h-100 bg-success" style="width: <?php echo min(100, $prop_occ_rate); ?>%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- 3. Active Leases & Individual Plot Breakdown Table -->
        <div class="card border rounded-4 bg-white p-3 p-md-4 shadow-xs" style="border-color: var(--drive-border) !important;">
            <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 border-bottom pb-3 mb-3">
                <div>
                    <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 1rem;">
                        <i class="bi bi-file-earmark-text-fill text-success"></i>
                        <span>Plots &amp; Lease Agreements Ledger</span>
                    </h5>
                    <p class="text-muted mb-0 mt-0.5" style="font-size: 0.75rem;">Detailed schedule of individual plot base pricing, tenant gardeners, and rent collection amounts</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1.5 text-xs">
                        <?php echo $occupied_count; ?> Active Leased
                    </span>
                    <span class="badge bg-light text-secondary border rounded-pill px-3 py-1.5 text-xs">
                        <?php echo $available_count; ?> Vacant Plots
                    </span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="earningsLedgerTable">
                    <thead class="table-light text-xs text-secondary text-uppercase" style="letter-spacing: 0.5px;">
                        <tr>
                            <th style="width: 20%;" class="ps-3 py-2.5">Property &amp; Plot</th>
                            <th style="width: 12%;" class="py-2.5">Parcel Size</th>
                            <th style="width: 14%;" class="py-2.5">Lease Status</th>
                            <th style="width: 18%;" class="py-2.5">Tenant Gardener</th>
                            <th style="width: 18%;" class="py-2.5 text-end th-rent-col">Monthly Rent (₱)</th>
                            <th style="width: 18%;" class="pe-3 py-2.5 text-end">Annual Base Rate</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        <?php if (empty($all_plots)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No plots registered yet. Register a land to track earnings.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($all_plots as $plot): ?>
                                <?php 
                                $isOccupied = ($plot['status'] === 'occupied');
                                $annualRent = floatval($plot['rental_price_per_year'] ?? 110000.00);
                                $monthlyRent = $annualRent / 12;
                                ?>
                                <tr class="<?php echo $isOccupied ? 'table-success-subtle bg-opacity-25' : ''; ?>">
                                    <td class="ps-3 py-2.5">
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($plot['plot_number']); ?></div>
                                        <div class="text-muted text-xs text-truncate" style="max-width: 220px;">
                                            <?php echo htmlspecialchars($plot['land_title']); ?>
                                        </div>
                                    </td>
                                    <td class="py-2.5">
                                        <span class="badge bg-light text-secondary border rounded-pill px-2 py-0.5 text-xs">
                                            <?php echo number_format(floatval($plot['area_sqm']), 1); ?> m²
                                        </span>
                                    </td>
                                    <td class="py-2.5">
                                        <?php if ($isOccupied): ?>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 text-xs fw-bold d-inline-flex align-items-center gap-1">
                                                <i class="bi bi-check-circle-fill"></i> Leased
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1 text-xs font-medium d-inline-flex align-items-center gap-1">
                                                <i class="bi bi-circle"></i> Available
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-2.5">
                                        <?php if (!empty($plot['farmer_name'])): ?>
                                            <div class="fw-semibold text-dark d-flex align-items-center gap-1.5">
                                                <i class="bi bi-person-fill text-success"></i>
                                                <span><?php echo htmlspecialchars($plot['farmer_name']); ?></span>
                                            </div>
                                            <?php if (!empty($plot['lease_end'])): ?>
                                                <small class="text-muted" style="font-size: 11px;">Expires: <?php echo htmlspecialchars($plot['lease_end']); ?></small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted fst-italic text-xs">No active tenant</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-2.5 text-end fw-bold <?php echo $isOccupied ? 'text-success' : 'text-secondary'; ?>">
                                        ₱<?php echo number_format($monthlyRent, 2); ?>
                                        <span class="text-muted fw-normal text-xs">/mo</span>
                                    </td>
                                    <td class="pe-3 py-2.5 text-end text-dark fw-bold">
                                        ₱<?php echo number_format($annualRent, 2); ?>
                                        <span class="text-muted fw-normal text-xs">/yr</span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="table-light border-top">
                        <tr class="fw-bold">
                            <td colspan="4" class="ps-3 py-2.5 text-uppercase text-secondary text-xs">TOTAL ACTIVE RENT COLLECTED:</td>
                            <td class="py-2.5 text-end text-success fs-6">₱<?php echo number_format($total_occupied_monthly, 2); ?>/mo</td>
                            <td class="pe-3 py-2.5 text-end text-success fs-6">₱<?php echo number_format($total_occupied_annual, 2); ?>/yr</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

    </div>
</main>

<script>
let currentReportMode = 'monthly'; // 'monthly' or 'annual'

const reportData = {
    monthly: {
        active: '₱<?php echo number_format($total_occupied_monthly, 2); ?>',
        activeSub: 'Annual equivalent: <strong>₱<?php echo number_format($total_occupied_annual, 2); ?>/yr</strong>',
        activeLbl: 'ACTIVE MONTHLY INCOME',
        potential: '₱<?php echo number_format($total_potential_monthly, 2); ?>',
        potentialSub: 'Full Annual Capacity: <strong>₱<?php echo number_format($total_potential_annual, 2); ?>/yr</strong>',
        potentialLbl: 'MONTHLY CAPACITY (100%)',
        avgPlot: '₱<?php echo number_format($avg_occupied_monthly, 2); ?>',
        avgSub: 'Standard benchmark: <strong>₱9,166.67/mo (₱110,000/yr)</strong>'
    },
    annual: {
        active: '₱<?php echo number_format($total_occupied_annual, 2); ?>',
        activeSub: 'Monthly equivalent: <strong>₱<?php echo number_format($total_occupied_monthly, 2); ?>/mo</strong>',
        activeLbl: 'ACTIVE ANNUAL REVENUE',
        potential: '₱<?php echo number_format($total_potential_annual, 2); ?>',
        potentialSub: 'Monthly equivalent: <strong>₱<?php echo number_format($total_potential_monthly, 2); ?>/mo</strong>',
        potentialLbl: 'ANNUAL CAPACITY (100%)',
        avgPlot: '₱<?php echo number_format($avg_occupied_annual, 2); ?>',
        avgSub: 'Monthly benchmark: <strong>₱9,166.67/mo</strong>'
    }
};

function switchReportMode(mode) {
    currentReportMode = mode;

    // Toggle button styles
    const btnM = document.getElementById('btnModeMonthly');
    const btnA = document.getElementById('btnModeAnnual');
    const mBtnM = document.getElementById('mBtnModeMonthly');
    const mBtnA = document.getElementById('mBtnModeAnnual');

    if (mode === 'monthly') {
        if (btnM) { btnM.classList.add('active'); }
        if (btnA) { btnA.classList.remove('active'); }
        if (mBtnM) { mBtnM.classList.add('active'); }
        if (mBtnA) { mBtnA.classList.remove('active'); }
    } else {
        if (btnA) { btnA.classList.add('active'); }
        if (btnM) { btnM.classList.remove('active'); }
        if (mBtnA) { mBtnA.classList.add('active'); }
        if (mBtnM) { mBtnM.classList.remove('active'); }
    }

    // Update KPI card labels and figures
    const d = reportData[mode];
    document.getElementById('lblActiveIncome').textContent = d.activeLbl;
    document.getElementById('valActiveIncome').innerHTML = d.active;
    document.getElementById('subActiveIncome').innerHTML = d.activeSub;

    document.getElementById('lblPotentialIncome').textContent = d.potentialLbl;
    document.getElementById('valPotentialIncome').innerHTML = d.potential;
    document.getElementById('subPotentialIncome').innerHTML = d.potentialSub;

    document.getElementById('valAvgPlot').innerHTML = d.avgPlot;
    document.getElementById('subAvgPlot').innerHTML = d.avgSub;

    // Update property card labels and values
    document.querySelectorAll('.prop-mode-lbl').forEach(el => {
        el.textContent = mode === 'monthly' ? 'MONTHLY EARNINGS:' : 'ANNUAL EARNINGS:';
    });
    document.querySelectorAll('.prop-earned-display').forEach(el => {
        const val = parseFloat(mode === 'monthly' ? el.getAttribute('data-monthly') : el.getAttribute('data-annual')) || 0;
        el.textContent = `₱${val.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
    });
    document.querySelectorAll('.prop-potential-display').forEach(el => {
        const val = parseFloat(mode === 'monthly' ? el.getAttribute('data-monthly') : el.getAttribute('data-annual')) || 0;
        el.textContent = `₱${val.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}${mode === 'monthly' ? '/mo' : '/yr'}`;
    });
}

function exportEarningsCSV() {
    const table = document.getElementById('earningsLedgerTable');
    if (!table) return;

    let csv = [];
    const rows = table.querySelectorAll('tr');
    for (let i = 0; i < rows.length; i++) {
        let row = [], cols = rows[i].querySelectorAll('td, th');
        for (let j = 0; j < cols.length; j++) {
            // Clean text
            let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, ' ').replace(/\s+/g, ' ').trim();
            data = data.replace(/"/g, '""');
            row.push('"' + data + '"');
        }
        csv.push(row.join(','));
    }

    const csvFile = new Blob([csv.join('\n')], { type: 'text/csv' });
    const downloadLink = document.createElement('a');
    downloadLink.download = `landowner_earnings_report_${new Date().toISOString().slice(0, 10)}.csv`;
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = 'none';
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}

document.addEventListener('DOMContentLoaded', function () {
    if (window.lucide) {
        lucide.createIcons();
    }
});
</script>

<?php include '../includes/footer.php'; ?>
