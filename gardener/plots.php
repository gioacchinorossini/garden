<?php
$base_path = '../';
$page_title = "My Cultivated Plots";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION['active_role'] = 'gardener';
if (!isset($_SESSION['user_name'])) {
    $_SESSION['user_name'] = 'Gardener';
}

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/lands_helper.php';
require_once __DIR__ . '/../includes/crops_library.php';

// Direct to single plot management page if id is specified
if (isset($_GET['id']) && !empty($_GET['id'])) {
    header("Location: manage_plot.php?id=" . intval($_GET['id']));
    exit;
}

$gardener_id = $_SESSION['user_id'] ?? null;
$gardener_name = $_SESSION['user_name'] ?? 'Gardener';
$gardener_email = $_SESSION['user_email'] ?? '';

// Fallback to first active gardener if previewing without an explicit session
if (!$gardener_id) {
    try {
        $defaultGardenerStmt = $pdo->query("SELECT id, name, email FROM users WHERE role = 'gardener' ORDER BY id ASC LIMIT 1");
        if ($defaultUser = $defaultGardenerStmt->fetch(PDO::FETCH_ASSOC)) {
            $gardener_id = (int)$defaultUser['id'];
            $gardener_name = $defaultUser['name'];
            $gardener_email = $defaultUser['email'];
        }
    } catch (\Exception $e) {}
}

// Automatically sync any approved requests so plots table always reflects real occupancy & gardener assignment
try {
    $pdo->exec("
        UPDATE plots p
        JOIN requests r ON r.plot_id = p.id AND r.status = 'approved'
        SET p.status = 'occupied',
            p.gardener_id = COALESCE(p.gardener_id, r.gardener_id),
            p.farmer_name = CASE WHEN (p.farmer_name IS NULL OR p.farmer_name = '') THEN r.gardener_name ELSE p.farmer_name END,
            p.lease_end = COALESCE(p.lease_end, DATE_ADD(CURDATE(), INTERVAL 1 YEAR))
        WHERE p.status != 'occupied' OR p.gardener_id IS NULL
    ");
} catch (\Exception $e) {}

// Fetch all assigned / occupied plots for this gardener (including any plots from approved requests)
$assigned_plots = [];
try {
    $isAdmin = ($gardener_id === 1 || $gardener_name === 'System Administrator') ? 1 : 0;
    $pStmt = $pdo->prepare("
        SELECT DISTINCT p.*, l.id as land_id, l.title as land_title, l.address as land_address, 
               l.latitude, l.longitude, l.polygon, l.has_3d_view, l.landowner_name,
               l.allowed_seeds as land_allowed_seeds
        FROM plots p
        JOIN lands l ON p.land_id = l.id
        LEFT JOIN requests r ON r.plot_id = p.id AND r.status = 'approved'
        WHERE (p.gardener_id = :gid1 AND :gid2 > 0)
           OR (p.farmer_name = :gname1 AND :gname2 != '' AND p.status = 'occupied')
           OR (r.gardener_id = :gid3 AND :gid4 > 0)
           OR (r.gardener_name = :gname3 AND :gname4 != '')
           OR (r.email = :gemail1 AND :gemail2 != '')
           OR (:isAdmin = 1 AND (p.gardener_id = 1 OR p.farmer_name = 'System Administrator' OR r.gardener_id = 1))
        ORDER BY p.id ASC
    ");
    $pStmt->execute([
        'gid1' => $gardener_id, 'gid2' => $gardener_id, 'gid3' => $gardener_id, 'gid4' => $gardener_id,
        'gname1' => $gardener_name, 'gname2' => $gardener_name, 'gname3' => $gardener_name, 'gname4' => $gardener_name,
        'gemail1' => $gardener_email, 'gemail2' => $gardener_email,
        'isAdmin' => $isAdmin
    ]);
    $assigned_plots = $pStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (\Exception $e) {
    $assigned_plots = [];
}

// Demo fallback: if the active test session user has 0 plots assigned (e.g. testing as landowner or guest),
// show occupied/active plots so the gardener view is populated for preview
if (empty($assigned_plots) && (($_SESSION['role'] ?? '') !== 'gardener' || !isset($_SESSION['user_id']))) {
    try {
        $demoStmt = $pdo->query("
            SELECT p.*, l.id as land_id, l.title as land_title, l.address as land_address, 
                   l.latitude, l.longitude, l.polygon, l.has_3d_view, l.landowner_name,
                   l.allowed_seeds as land_allowed_seeds
            FROM plots p
            JOIN lands l ON p.land_id = l.id
            WHERE p.status = 'occupied' OR (p.crop IS NOT NULL AND p.crop != '')
            ORDER BY p.id ASC
        ");
        $assigned_plots = $demoStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (\Exception $e) {
        $assigned_plots = [];
    }
}

// Fetch all plots on these lands for the top-down farm layout
$land_ids = array_unique(array_filter(array_column($assigned_plots, 'land_id')));
$all_farm_plots = [];
if (!empty($land_ids)) {
    try {
        $inClause = implode(',', array_map('intval', $land_ids));
        $allPlotsStmt = $pdo->query("SELECT * FROM plots WHERE land_id IN ($inClause) ORDER BY id ASC");
        $all_farm_plots = $allPlotsStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (\Exception $e) {
        $all_farm_plots = [];
    }
}

// Metrics
$total_plots = count($assigned_plots);
$total_area = 0;
foreach ($assigned_plots as $p) {
    $total_area += (float)($p['area_sqm'] ?? $p['area'] ?? 50);
}
$unique_lands = count(array_unique(array_column($assigned_plots, 'land_id')));
$planted_plots_count = 0;
$distinct_crops = [];
foreach ($assigned_plots as $p) {
    if (!empty($p['crop'])) {
        $planted_plots_count++;
        $distinct_crops[] = $p['crop'];
    }
}
$distinct_crops = array_values(array_unique($distinct_crops));

// Crop icon helper
function resolveCropIcon($cropName, $explicitIcon = '') {
    if (!empty($explicitIcon)) {
        $clean = preg_replace('/^(\.\.\/|assets\/)/', '', $explicitIcon);
        if (!str_starts_with($clean, 'crop-icons/')) {
            $clean = 'crop-icons/' . $clean;
        }
        return '../assets/' . $clean;
    }
    if (empty($cropName)) {
        return '../assets/crop-icons/generic-mushroom/generic-mushroom.svg';
    }
    if (function_exists('getCropsLibrary')) {
        $lib = getCropsLibrary();
        $lower = strtolower(trim($cropName));
        foreach ($lib as $item) {
            if (
                strtolower($item['name']) === $lower ||
                strtolower($item['value']) === $lower ||
                (!empty($item['local_name']) && strtolower($item['local_name']) === $lower)
            ) {
                $iconPath = $item['icon'];
                $clean = preg_replace('/^(\.\.\/|assets\/)/', '', $iconPath);
                return '../assets/' . $clean;
            }
        }
    }
    return '../assets/crop-icons/tomato/tomato.svg';
}

include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>

<style>
/* Aesthetic Cats & Soup + Drive System Styling */
.myplots-hero {
    background: linear-gradient(135deg, #FFFDF9 0%, #F5EFE6 100%);
    border: 1.5px solid #DFCFC2;
    border-radius: 24px;
    box-shadow: 0 4px 16px rgba(82, 62, 49, 0.05);
}

.stat-chip-card {
    background: #FFFDF9;
    border: 1.5px solid #DFCFC2;
    border-radius: 18px;
    padding: 14px 18px;
    transition: all 0.2s ease;
}
.stat-chip-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(82, 62, 49, 0.08);
    border-color: #7FA668;
}

.plot-item-card {
    background: #FFFDF9;
    border: 2px solid #523E31;
    border-radius: 20px;
    box-shadow: 0 4px 0 #523E31;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
    overflow: visible !important;
    position: relative;
}
.plot-item-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 0 #523E31;
}

/* Individual Top-down Soil Bed (Exact match from Landowner Plots Page) */
.dynamic-plot-bed {
    position: relative;
    background: radial-gradient(ellipse at center, #6A4426 0%, #4D2E16 85%, #381E0C 100%);
    border: 3.5px solid #84532B;
    border-radius: 18px;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.5), inset 0 -3px 0 rgba(40,20,5,0.6), 0 4px 8px rgba(0,0,0,0.3);
    padding: 12px 10px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: transform 0.18s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.18s ease, border-color 0.18s ease;
    user-select: none;
    overflow: visible !important;
    min-height: 155px;
    height: 155px;
    width: 100%;
}

.dynamic-plot-bed::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background-image: repeating-linear-gradient(
        0deg,
        rgba(0, 0, 0, 0.2) 0px,
        rgba(0, 0, 0, 0.2) 3px,
        transparent 3px,
        transparent 14px
    );
    pointer-events: none;
    border-radius: 14px;
    opacity: 0.85;
}

.dynamic-plot-bed:hover {
    transform: translateY(-2px) scale(1.02);
    border-color: #FACC15;
    box-shadow: inset 0 2px 5px rgba(0,0,0,0.4), 0 8px 16px rgba(0,0,0,0.4), 0 0 0 3px rgba(250, 204, 21, 0.4);
    z-index: 5;
}

/* Overlay Crop Name Pill at Top Edge of each plot */
.map-crop-pill {
    position: absolute;
    top: 0;
    left: 50%;
    transform: translate(-50%, -50%);
    z-index: 10;
    background: #FFFDF9;
    border: 2px solid #523E31;
    border-radius: 9999px;
    box-shadow: 0 2.5px 0 #523E31;
    padding: 3px 14px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.82rem;
    font-weight: 800;
    color: #3E2B1E;
    white-space: nowrap;
}
.map-crop-pill img {
    width: 18px;
    height: 18px;
    object-fit: contain;
}
.map-crop-pill.available-pill {
    color: #166534;
}

/* Furrow plants growing in rows filling the entire bed */
.bed-furrow-plants {
    position: relative;
    z-index: 2;
    display: flex;
    flex-direction: column;
    justify-content: space-evenly;
    width: 100%;
    height: 100%;
    padding: 10px 4px 4px;
    gap: 4px;
}

.bed-furrow-row {
    display: flex;
    align-items: center;
    justify-content: space-evenly;
    width: 100%;
}

.bed-sprout-icon {
    width: 26px;
    height: 26px;
    object-fit: contain;
    filter: drop-shadow(0 2px 3px rgba(0,0,0,0.6));
    transition: transform 0.18s ease;
}
.dynamic-plot-bed:hover .bed-sprout-icon {
    transform: scale(1.15);
}

/* Empty furrow soil lines for available / unplanted beds */
.bed-empty-soil-lines {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-evenly;
    padding: 12px 6px 6px;
}

.furrow-soil-line {
    width: 85%;
    height: 7px;
    margin: 0 auto;
    background: rgba(40, 20, 8, 0.55);
    border-radius: 9999px;
    box-shadow: inset 0 1px 3px rgba(0,0,0,0.65), 0 1px 0 rgba(140, 90, 48, 0.4);
}

.plot-filter-chip {
    font-size: 0.8rem;
    font-weight: 600;
    color: #6B5446;
    background: #FFFDF9;
    border: 1.5px solid #DFCFC2;
    padding: 6px 14px;
    border-radius: 9999px;
    transition: all 0.15s ease;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.plot-filter-chip:hover {
    background: #F7EFE6;
    color: #3E2B1E;
}
.plot-filter-chip.active {
    background: #7FA668 !important;
    color: #FFFDF9 !important;
    border-color: #558B2F !important;
    box-shadow: 0 2px 6px rgba(127, 166, 104, 0.3);
}

.seed-tag {
    font-size: 11px;
    font-weight: 600;
    color: #2D5A27;
    background: #EBF5E9;
    border: 1px solid #BDDAB8;
    padding: 2.5px 8px;
    border-radius: 9999px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.seed-tag img {
    width: 13px;
    height: 13px;
    object-fit: contain;
    flex-shrink: 0;
}

/* Modal crop selection grid */
.crop-select-radio-card {
    border: 1.5px solid #DFCFC2;
    border-radius: 14px;
    padding: 10px 12px;
    background: #FFFDF9;
    cursor: pointer;
    transition: all 0.15s ease;
    display: flex;
    align-items: center;
    gap: 10px;
}
.crop-select-radio-card:hover {
    border-color: #7FA668;
    background: #F7FAF5;
}
.crop-select-radio-card.selected {
    border: 2px solid #7FA668;
    background: #F0F7EE;
    box-shadow: 0 2px 8px rgba(127, 166, 104, 0.2);
}

.crop-chk-indicator {
    width: 20px;
    height: 20px;
    border-radius: 6px;
    border: 1.5px solid #D1D5DB;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #FFFFFF;
    transition: all 0.15s ease;
    flex-shrink: 0;
}
.crop-select-radio-card.selected .crop-chk-indicator {
    background: #2D5A27;
    border-color: #2D5A27;
}

/* Farm Schematic Top-Down Grid */
.topdown-farm-preview {
    background: #F4EDE2;
    border: 1.5px solid #DFCFC2;
    border-radius: 20px;
    padding: 14px;
}

.farm-grid-plot-cell {
    background: #FFFDF9;
    border: 1.5px solid #DFCFC2;
    border-radius: 12px;
    padding: 8px 10px;
    font-size: 11px;
    transition: all 0.2s ease;
}
.farm-grid-plot-cell.is-mine {
    background: #EBF5E9;
    border: 2px solid #7FA668;
    box-shadow: 0 0 0 3px rgba(127, 166, 104, 0.2);
    font-weight: 700;
}
</style>

<main class="workspace-surface">
    <!-- Desktop Toolbar -->
    <div class="toolbar d-none d-md-flex justify-content-between align-items-center">
        <div>
            <h1 class="fs-5 fw-bold m-0 text-dark flex align-items-center gap-2 font-['Outfit']">
                <i data-lucide="grid-3x3" style="color:#7FA668; width:22px; height:22px;"></i>
                My Cultivated Plots
            </h1>
            <p class="text-muted mb-0" style="font-size: 0.78rem;">
                Manage your leased plots, track active crops, and coordinate farm tasks
            </p>
        </div>

        <div class="d-flex align-items-center gap-2">
            <!-- Search bar -->
            <div class="position-relative" style="width: 250px;">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" style="font-size: 0.8rem; pointer-events: none;"></i>
                <input type="text" id="plotSearchInput" class="form-control form-control-sm ps-5 rounded-pill border-light-subtle bg-light"
                    placeholder="Search plot, crop, land...">
            </div>

            <a href="browse.php" class="btn btn-sm btn-drive-primary rounded-pill px-3.5 py-1.5 fw-semibold d-flex align-items-center gap-1.5">
                <i data-lucide="plus-circle" style="width:15px; height:15px;"></i>
                <span>Lease New Plot</span>
            </a>
        </div>
    </div>

    <!-- Workspace Scrollable Area -->
    <div class="workspace-scroll p-3 px-md-4 pt-md-3 pb-5 pb-md-5">

        <!-- Mobile Header (< md) -->
        <div class="d-block d-md-none mb-3">
            <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                <h1 class="fs-6 fw-bold m-0 text-dark font-['Outfit'] flex align-items-center gap-1.5">
                    <i data-lucide="grid-3x3" style="color:#7FA668; width:18px; height:18px;"></i>
                    My Cultivated Plots
                </h1>
                <a href="browse.php" class="btn btn-sm btn-drive-primary rounded-pill px-3 py-1 fw-semibold text-xs">
                    + Lease Plot
                </a>
            </div>
            <div class="position-relative mb-2">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" style="font-size: 0.85rem; pointer-events: none;"></i>
                <input type="text" id="mobilePlotSearchInput" class="form-control form-control-sm ps-5 py-2 rounded-pill border-light-subtle bg-white shadow-xs"
                    placeholder="Search plot, crop, land...">
            </div>
        </div>



        <?php if (empty($assigned_plots)): ?>
            <!-- Empty State -->
            <div class="text-center py-5 px-3 bg-white rounded-4 border border-light-subtle shadow-xs my-4">
                <div class="rounded-circle d-inline-flex p-4 mb-3" style="background:#F7EFE6;">
                    <i data-lucide="sprout" style="width:48px; height:48px; color:#7FA668;"></i>
                </div>
                <h3 class="fw-bold text-dark mb-1">No Active Plots Leased Yet</h3>
                <p class="text-secondary small mb-4 mx-auto" style="max-width: 420px;">
                    You are not currently cultivating any plots. Browse approved community idle lands, select an available plot, and submit an application to start planting!
                </p>
                <div class="d-flex justify-content-center gap-2">
                    <a href="browse.php" class="btn btn-drive-primary rounded-pill px-4 py-2 fw-semibold">
                        <i data-lucide="compass" class="me-1" style="width:16px; height:16px;"></i>
                        Browse Available Lands
                    </a>
                    <a href="requests.php" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-semibold">
                        View Pending Applications
                    </a>
                </div>
            </div>
        <?php else: ?>

            <!-- Plots Grid -->
            <div class="row g-4" id="plotsCardGrid">
                <?php foreach ($assigned_plots as $plot): ?>
                    <?php
                    $plot_id = (int)$plot['id'];
                    $plot_num = $plot['plot_number'] ?? 'Plot A-1';
                    $land_title = $plot['land_title'] ?? 'Community Garden';
                    $land_address = $plot['land_address'] ?? 'General Santos City';
                    $area = (float)($plot['area_sqm'] ?? $plot['area'] ?? 50);
                    $rent_rate = (float)($plot['rental_price_per_year'] ?? 110000);
                    $crop = trim($plot['crop'] ?? '');
                    $crop_icon = $plot['crop_icon'] ?? '';
                    $resolved_icon = resolveCropIcon($crop, $crop_icon);
                    $is_planted = !empty($crop);

                    // Decode allowed seeds
                    $seeds = json_decode($plot['allowed_seeds'] ?? '[]', true);
                    if (!is_array($seeds) || empty($seeds)) {
                        $seeds = json_decode($plot['land_allowed_seeds'] ?? '[]', true);
                    }
                    if (!is_array($seeds) || empty($seeds)) {
                        $seeds = ['Tomato', 'Eggplant', 'Lettuce', 'Sweet Corn'];
                    }

                    // Lease timeline hint
                    $lease_end = !empty($plot['lease_end']) ? date('M d, Y', strtotime($plot['lease_end'])) : '1 Year Active';
                    ?>

                    <div class="col-12 col-md-6 col-xl-4 plot-grid-col"
                         data-plot-id="<?php echo $plot_id; ?>"
                         data-plot-num="<?php echo htmlspecialchars(strtolower($plot_num)); ?>"
                         data-land="<?php echo htmlspecialchars(strtolower($land_title)); ?>"
                         data-crop="<?php echo htmlspecialchars(strtolower($crop)); ?>"
                         data-planted="<?php echo $is_planted ? '1' : '0'; ?>">

                        <div class="plot-item-card h-100 d-flex flex-column p-4">
                            <!-- Card Header: Land Title & Plot Badge -->
                            <div class="d-flex align-items-start justify-content-between gap-2 mb-3 min-w-0">
                                <div class="min-w-0 flex-grow-1">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <span class="badge rounded-pill px-2.5 py-1 text-xs fw-bold" style="background:#EBF3E8; color:#2D5A27; border:1px solid #BDDAB8;">
                                            <?php echo htmlspecialchars($plot_num); ?>
                                        </span>
                                    </div>
                                    <h3 class="fs-6 fw-bold text-dark mb-0 text-truncate font-['Outfit']" title="<?php echo htmlspecialchars($land_title); ?>">
                                        <?php echo htmlspecialchars($land_title); ?>
                                    </h3>
                                    <div class="text-secondary text-xs text-truncate mt-0.5" title="<?php echo htmlspecialchars($land_address); ?>">
                                        <i class="bi bi-geo-alt-fill text-success me-1"></i>
                                        <?php echo htmlspecialchars($land_address); ?>
                                    </div>
                                </div>

                                <div class="text-end flex-shrink-0">
                                    <span class="d-block fw-bold text-dark text-sm"><?php echo number_format($area, 0); ?> m²</span>
                                    <span class="text-muted text-xs">Partition Area</span>
                                </div>
                            </div>

                            <!-- Visual Top-Down Plot Bed (From Landowner Page) -->
                            <div class="my-3 pt-1">
                                <div class="dynamic-plot-bed" id="plotBed_<?php echo $plot_id; ?>"
                                     onclick="openPlantCropModal(<?php echo $plot_id; ?>, '<?php echo htmlspecialchars($plot_num, ENT_QUOTES); ?>', '<?php echo htmlspecialchars($land_title, ENT_QUOTES); ?>', <?php echo htmlspecialchars(json_encode($seeds), ENT_QUOTES); ?>, document.querySelector('.plot-grid-col[data-plot-id=\'<?php echo $plot_id; ?>\']')?.getAttribute('data-crop') || '<?php echo htmlspecialchars($crop, ENT_QUOTES); ?>')"
                                     title="Click to <?php echo $is_planted ? 'change crop' : 'plant crop'; ?> for <?php echo htmlspecialchars($plot_num); ?>">

                                    <?php 
                                    $crop_items = array_values(array_filter(array_map('trim', explode(',', $crop))));
                                    $is_planted = !empty($crop_items);
                                    if ($is_planted): 
                                        $primary_crop = $crop_items[0];
                                        $primary_icon = resolveCropIcon($primary_crop, $crop_icon);
                                        $others_count = count($crop_items) - 1;
                                        $pill_text = $others_count > 0 ? htmlspecialchars(ucfirst($primary_crop)) . ' & + ' . $others_count . ' other' . ($others_count > 1 ? 's' : '') : htmlspecialchars(ucfirst($primary_crop));
                                    ?>
                                        <div class="map-crop-pill">
                                            <img src="<?php echo htmlspecialchars($primary_icon); ?>" alt="<?php echo htmlspecialchars(ucfirst($primary_crop)); ?>">
                                            <span><?php echo $pill_text; ?></span>
                                        </div>
                                        <div class="bed-furrow-plants">
                                            <?php for ($r = 0; $r < 3; $r++): 
                                                $row_c = $crop_items[$r % count($crop_items)];
                                                $row_ic = resolveCropIcon($row_c);
                                            ?>
                                                <div class="bed-furrow-row">
                                                    <img src="<?php echo htmlspecialchars($row_ic); ?>" class="bed-sprout-icon" alt="<?php echo htmlspecialchars($row_c); ?>">
                                                    <img src="<?php echo htmlspecialchars($row_ic); ?>" class="bed-sprout-icon" alt="<?php echo htmlspecialchars($row_c); ?>">
                                                    <img src="<?php echo htmlspecialchars($row_ic); ?>" class="bed-sprout-icon" alt="<?php echo htmlspecialchars($row_c); ?>">
                                                    <img src="<?php echo htmlspecialchars($row_ic); ?>" class="bed-sprout-icon" alt="<?php echo htmlspecialchars($row_c); ?>">
                                                </div>
                                            <?php endfor; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="map-crop-pill available-pill">
                                            <i class="bi bi-plus-circle-fill text-success" style="font-size:0.85rem;"></i>
                                            <span>Ready to Plant</span>
                                        </div>
                                        <div class="bed-empty-soil-lines">
                                            <div class="furrow-soil-line"></div>
                                            <div class="furrow-soil-line"></div>
                                            <div class="furrow-soil-line"></div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Plot Specifications & Planted Crops -->
                            <div class="mb-3">
                                <div class="d-flex align-items-center justify-content-between text-xs text-secondary mb-1.5">
                                    <span>Crops:</span>
                                    <span>Annual Lease: <strong>₱<?php echo number_format($rent_rate, 0); ?>/yr</strong></span>
                                </div>
                                <div class="d-flex flex-wrap gap-1" id="plotPlantedCropsTags_<?php echo $plot_id; ?>">
                                    <?php if (!empty($crop_items)): ?>
                                        <?php foreach ($crop_items as $c_item): ?>
                                            <?php $c_icon = resolveCropIcon($c_item); ?>
                                            <span class="seed-tag">
                                                <img src="<?php echo htmlspecialchars($c_icon); ?>" alt="<?php echo htmlspecialchars($c_item); ?>">
                                                <span><?php echo htmlspecialchars(ucfirst($c_item)); ?></span>
                                            </span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="text-muted text-xs">No crops planted yet</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Lease Period & Landowner Info -->
                            <div class="pt-2 border-top d-flex align-items-center justify-content-between text-xs text-muted mt-auto mb-3">
                                <div>
                                    <i class="bi bi-calendar-event me-1"></i>
                                    <span>Lease: <?php echo htmlspecialchars($lease_end); ?></span>
                                </div>
                                <div>
                                    <i class="bi bi-person me-1"></i>
                                    <span>Owner: <?php echo htmlspecialchars($plot['landowner_name'] ?? 'Landowner'); ?></span>
                                </div>
                            </div>

                            <!-- Manage Plot Button -->
                            <div class="pt-2 border-top d-flex align-items-center justify-content-end">
                                <a href="manage_plot.php?id=<?php echo $plot_id; ?>" class="btn btn-sm btn-drive-primary text-white rounded-pill px-3 py-1.5 text-decoration-none d-inline-flex align-items-center gap-1.5 fw-semibold shadow-xs" style="color: #ffffff !important; font-size: 0.8rem;">
                                    <span>Manage Plot</span>
                                    <i class="bi bi-arrow-right ms-0.5 text-white"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>

    </div>
</main>

<!-- Plant / Change Crop Modal -->
<div class="modal fade" id="plantCropModal" tabindex="-1" aria-hidden="true" style="z-index: 10050;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 24px; background: #FFFDF9;">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h5 class="modal-title fw-bold text-dark font-['Outfit']">Plant or Change Crop</h5>
                    <p class="text-secondary text-xs mb-0" id="plantModalSubtitle">Plot A-1 · Green Fields Garden</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body pt-3">
                <input type="hidden" id="plantModalPlotId" value="0">
                <input type="hidden" id="selectedCropName" value="">
                <input type="hidden" id="selectedCropIcon" value="">

                <div class="d-flex align-items-center justify-content-between mb-2">
                    <label class="form-label text-secondary fw-bold text-xs text-uppercase mb-0">
                        Select Crops to Plant:
                    </label>
                    <span id="selectedCropsCountBadge" class="badge rounded-pill bg-light text-secondary border px-2 py-0.5 text-xs">
                        0 selected
                    </span>
                </div>

                <!-- Search Input for Crops -->
                <div class="position-relative mb-3">
                    <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" style="font-size: 0.8rem;"></i>
                    <input type="text" id="cropModalSearchInput" class="form-control form-control-sm ps-5 rounded-pill border-light-subtle"
                        placeholder="Search crop variety...">
                </div>

                <!-- Crop Option Radio Cards Container -->
                <div class="row g-2 mb-3" id="cropOptionCardsContainer" style="max-height: 280px; overflow-y: auto;">
                    <!-- Populated dynamically -->
                </div>

                <!-- Feedback Alert Container -->
                <div id="plantModalAlert" class="d-none alert alert-danger py-2 px-3 text-xs rounded-3 mb-0"></div>
            </div>

            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-sm btn-light border rounded-pill px-3 py-1.5 fw-semibold text-secondary" data-bs-dismiss="modal">
                    Cancel
                </button>
                <button type="button" class="btn btn-sm btn-drive-primary rounded-pill px-4 py-1.5 fw-semibold shadow-xs" id="btnSavePlantedCrop" onclick="savePlantedCrop()">
                    <i data-lucide="check" style="width:14px; height:14px;" class="me-1"></i>
                    Confirm Planted Crop
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Toast Feedback -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11000">
    <div id="plotToast" class="toast align-items-center text-white bg-success border-0 rounded-4 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2 text-sm fw-medium" id="plotToastMsg">
                <i class="bi bi-check-circle-fill fs-5"></i>
                <span>Planted crop updated successfully!</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<script>
// Catalog and Full Library
const allAssignedPlots = <?php echo json_encode($assigned_plots); ?>;
const allFarmPlots = <?php echo json_encode($all_farm_plots); ?>;
const fullCropsLibrary = <?php echo json_encode(function_exists('getCropsLibrary') ? getCropsLibrary() : []); ?>;

// Standard Crop Catalog fallback
const standardCropCatalog = [
    { name: 'Tomato', icon: 'tomato/tomato.svg', local: 'Kamatis' },
    { name: 'Lettuce', icon: 'romaine/romaine.svg', local: 'Litsugas' },
    { name: 'Sweet Corn', icon: 'corn/corn.svg', local: 'Mais' },
    { name: 'Carrot', icon: 'carrot/carrot.svg', local: 'Karot' },
    { name: 'Bell Pepper', icon: 'red-bell-pepper/red-bell-pepper.svg', local: 'Lara' },
    { name: 'Eggplant', icon: 'eggplant/eggplant.svg', local: 'Talong' },
    { name: 'Cucumber', icon: 'cucumber/cucumber.svg', local: 'Pipino' },
    { name: 'Spinach', icon: 'spinach/spinach.svg', local: 'Kangkong' },
    { name: 'Basil & Herbs', icon: 'basil/basil.svg', local: 'Herbs' },
    { name: 'Beans', icon: 'broad-bean/broad-bean.svg', local: 'Sitaw' },
    { name: 'Red Onion', icon: 'red-onion/red-onion.svg', local: 'Sibuyas' },
    { name: 'Garlic', icon: 'garlic/garlic.svg', local: 'Bawang' },
    { name: 'Potato', icon: 'russet-potato/russet-potato.svg', local: 'Patatas' },
    { name: 'Mushroom', icon: 'generic-mushroom/generic-mushroom.svg', local: 'Kabute' }
];

let plantCropModalInst = null;
let currentFilter = 'all';
let currentSearchQuery = '';

document.addEventListener('DOMContentLoaded', () => {
    plantCropModalInst = new bootstrap.Modal(document.getElementById('plantCropModal'));

    // Search input handlers
    const searchInput = document.getElementById('plotSearchInput');
    const mobileSearchInput = document.getElementById('mobilePlotSearchInput');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            currentSearchQuery = this.value.toLowerCase().trim();
            if (mobileSearchInput) mobileSearchInput.value = this.value;
            applyPlotFilters();
        });
    }
    if (mobileSearchInput) {
        mobileSearchInput.addEventListener('input', function() {
            currentSearchQuery = this.value.toLowerCase().trim();
            if (searchInput) searchInput.value = this.value;
            applyPlotFilters();
        });
    }

    // Modal Crop Search
    const cropSearch = document.getElementById('cropModalSearchInput');
    if (cropSearch) {
        cropSearch.addEventListener('input', function() {
            filterModalCrops(this.value.toLowerCase().trim());
        });
    }
});

// Filter chip handler
function filterPlots(filterKey) {
    currentFilter = filterKey;
    document.querySelectorAll('.plot-filter-chip').forEach(btn => {
        if (btn.getAttribute('data-filter') === filterKey) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
    applyPlotFilters();
}

// Apply both search query and chip filters
function applyPlotFilters() {
    const cards = document.querySelectorAll('.plot-grid-col');
    let visibleCount = 0;

    cards.forEach(card => {
        const plotNum = card.getAttribute('data-plot-num') || '';
        const land = card.getAttribute('data-land') || '';
        const crop = card.getAttribute('data-crop') || '';
        const isPlanted = card.getAttribute('data-planted') === '1';

        // Check filter
        let matchesFilter = true;
        if (currentFilter === 'planted') {
            matchesFilter = isPlanted;
        } else if (currentFilter === 'unplanted') {
            matchesFilter = !isPlanted;
        }

        // Check search query
        let matchesSearch = true;
        if (currentSearchQuery !== '') {
            matchesSearch = plotNum.includes(currentSearchQuery) ||
                            land.includes(currentSearchQuery) ||
                            crop.includes(currentSearchQuery);
        }

        if (matchesFilter && matchesSearch) {
            card.classList.remove('d-none');
            visibleCount++;
        } else {
            card.classList.add('d-none');
        }
    });

    const countLabel = document.getElementById('filteredCountLabel');
    if (countLabel) {
        countLabel.textContent = visibleCount;
    }
}

// Multi-crop selection state
let selectedModalCrops = [];

// Open modal to plant or change crop
function openPlantCropModal(plotId, plotNum, landTitle, allowedSeeds, currentCrop) {
    document.getElementById('plantModalPlotId').value = plotId;
    document.getElementById('plantModalSubtitle').textContent = `${plotNum} · ${landTitle}`;
    document.getElementById('cropModalSearchInput').value = '';

    // Parse existing crops
    selectedModalCrops = currentCrop ? currentCrop.split(',').map(s => s.trim()).filter(Boolean) : [];

    const container = document.getElementById('cropOptionCardsContainer');
    container.innerHTML = '';

    // Build crop list: Combine allowed seeds + standard crops
    let list = [];

    // Allowed seeds first
    if (Array.isArray(allowedSeeds) && allowedSeeds.length > 0) {
        allowedSeeds.forEach(sName => {
            const foundLib = findCropInLibrary(sName);
            list.push({
                name: sName,
                icon: foundLib ? foundLib.icon : resolveCatalogIcon(sName),
                isRecommended: true
            });
        });
    }

    // Add standard crops if not already in list
    standardCropCatalog.forEach(sc => {
        if (!list.some(item => item.name.toLowerCase() === sc.name.toLowerCase())) {
            list.push({
                name: sc.name,
                icon: `crop-icons/${sc.icon}`,
                isRecommended: false
            });
        }
    });

    // Render multi-select cards
    list.forEach(c => {
        const isSelected = selectedModalCrops.some(sc => sc.toLowerCase() === c.name.toLowerCase());
        const iconSrc = formatCropIconUrl(c.icon);

        const col = document.createElement('div');
        col.className = 'col-6 crop-option-item';
        col.setAttribute('data-name', c.name.toLowerCase());
        col.innerHTML = `
            <div class="crop-select-radio-card ${isSelected ? 'selected' : ''}" onclick="toggleModalCrop(this, '${escapeHtml(c.name)}', '${escapeHtml(c.icon)}')">
                <div class="crop-chk-indicator">
                    ${isSelected ? '<i class="bi bi-check text-white fw-bold"></i>' : ''}
                </div>
                <img src="${iconSrc}" style="width:28px; height:28px; object-fit:contain;" alt="${escapeHtml(c.name)}">
                <div class="min-w-0 flex-fill">
                    <div class="fw-bold text-dark text-xs text-truncate">${escapeHtml(c.name)}</div>
                    ${c.isRecommended ? '<span class="text-[10px] text-success fw-semibold">✓ Recommended</span>' : '<span class="text-[10px] text-muted">Variety</span>'}
                </div>
            </div>
        `;
        container.appendChild(col);
    });

    updateModalCropSummary();
    plantCropModalInst.show();
}

// Toggle a crop option in modal (Multi-select)
function toggleModalCrop(cardEl, cropName, cropIcon) {
    const idx = selectedModalCrops.findIndex(c => c.toLowerCase() === cropName.toLowerCase());
    if (idx > -1) {
        selectedModalCrops.splice(idx, 1);
        cardEl.classList.remove('selected');
        const ind = cardEl.querySelector('.crop-chk-indicator');
        if (ind) ind.innerHTML = '';
    } else {
        selectedModalCrops.push(cropName);
        cardEl.classList.add('selected');
        const ind = cardEl.querySelector('.crop-chk-indicator');
        if (ind) ind.innerHTML = '<i class="bi bi-check text-white fw-bold"></i>';
    }
    updateModalCropSummary();
}

function updateModalCropSummary() {
    const count = selectedModalCrops.length;
    const badge = document.getElementById('selectedCropsCountBadge');
    if (badge) {
        badge.textContent = count > 0 ? `${count} selected` : '0 selected';
        badge.className = count > 0 ? 'badge rounded-pill bg-success text-white px-2 py-0.5 text-xs' : 'badge rounded-pill bg-light text-secondary border px-2 py-0.5 text-xs';
    }
    const saveBtn = document.getElementById('btnSavePlantedCrop');
    if (saveBtn) {
        saveBtn.innerHTML = `<i data-lucide="check" style="width:14px; height:14px;" class="me-1"></i> Confirm Planted Crop${count > 1 ? 's' : ''} ${count > 0 ? '(' + count + ')' : ''}`;
        if (window.lucide) lucide.createIcons();
    }
    const alertBox = document.getElementById('plantModalAlert');
    if (alertBox) alertBox.classList.add('d-none');
}

// Filter crops in modal
function filterModalCrops(q) {
    document.querySelectorAll('.crop-option-item').forEach(el => {
        const name = el.getAttribute('data-name') || '';
        if (q === '' || name.includes(q)) {
            el.classList.remove('d-none');
        } else {
            el.classList.add('d-none');
        }
    });
}

// Save planted crops via API
async function savePlantedCrop() {
    const plotId = document.getElementById('plantModalPlotId').value;
    const alertBox = document.getElementById('plantModalAlert');
    const saveBtn = document.getElementById('btnSavePlantedCrop');

    if (selectedModalCrops.length === 0) {
        alertBox.textContent = 'Please select at least one crop to plant.';
        alertBox.classList.remove('d-none');
        return;
    }

    const primaryCrop = selectedModalCrops[0];
    const primaryIcon = resolveCatalogIcon(primaryCrop);

    saveBtn.disabled = true;
    saveBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Saving...`;

    try {
        const res = await fetch('../api/lands.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'gardener_plant_crop',
                plot_id: plotId,
                crops: selectedModalCrops,
                crop: selectedModalCrops.join(', '),
                crop_icon: primaryIcon
            })
        });

        const data = await res.json();
        if (data.status === 'success') {
            // Update soil bed visual dynamically
            const bedEl = document.getElementById(`plotBed_${plotId}`);
            if (bedEl) {
                const primaryIconUrl = formatCropIconUrl(primaryIcon);
                const primaryDisplay = primaryCrop.charAt(0).toUpperCase() + primaryCrop.slice(1);
                const othersCount = selectedModalCrops.length - 1;
                const pillLabel = othersCount > 0 ? `${escapeHtml(primaryDisplay)} & +${othersCount} other${othersCount > 1 ? 's' : ''}` : escapeHtml(primaryDisplay);

                const furrowRows = [0, 1, 2].map((rIdx) => {
                    const rowCrop = selectedModalCrops[rIdx % selectedModalCrops.length];
                    const rowIconUrl = formatCropIconUrl(resolveCatalogIcon(rowCrop));
                    return `
                        <div class="bed-furrow-row">
                            <img src="${rowIconUrl}" class="bed-sprout-icon" alt="${escapeHtml(rowCrop)}">
                            <img src="${rowIconUrl}" class="bed-sprout-icon" alt="${escapeHtml(rowCrop)}">
                            <img src="${rowIconUrl}" class="bed-sprout-icon" alt="${escapeHtml(rowCrop)}">
                            <img src="${rowIconUrl}" class="bed-sprout-icon" alt="${escapeHtml(rowCrop)}">
                        </div>
                    `;
                }).join('');

                bedEl.innerHTML = `
                    <div class="map-crop-pill">
                        <img src="${primaryIconUrl}" alt="${escapeHtml(primaryDisplay)}">
                        <span>${pillLabel}</span>
                    </div>
                    <div class="bed-furrow-plants">
                        ${furrowRows}
                    </div>
                `;
                bedEl.title = `Click to change crop for Plot (${selectedModalCrops.join(', ')})`;
            }

            // Update data attributes on card
            const colCard = document.querySelector(`.plot-grid-col[data-plot-id="${plotId}"]`);
            if (colCard) {
                colCard.setAttribute('data-crop', selectedModalCrops.join(', ').toLowerCase());
                colCard.setAttribute('data-planted', '1');
            }

            // Update crops tags on card
            const cardTags = document.getElementById(`plotPlantedCropsTags_${plotId}`);
            if (cardTags) {
                if (selectedModalCrops.length > 0) {
                    cardTags.innerHTML = selectedModalCrops.map(cName => {
                        const iconUrl = formatCropIconUrl(resolveCatalogIcon(cName));
                        const cDisplay = cName.charAt(0).toUpperCase() + cName.slice(1);
                        return `
                            <span class="seed-tag">
                                <img src="${iconUrl}" alt="${escapeHtml(cDisplay)}">
                                <span>${escapeHtml(cDisplay)}</span>
                            </span>
                        `;
                    }).join('');
                } else {
                    cardTags.innerHTML = `<span class="text-muted text-xs">No crops planted yet</span>`;
                }
            }

            plantCropModalInst.hide();
            showPlotToast(`Planted ${selectedModalCrops.join(', ')} successfully!`);
        } else {
            alertBox.textContent = data.message || 'Failed to update planted crops.';
            alertBox.classList.remove('d-none');
        }
    } catch (err) {
        alertBox.textContent = 'Network error while saving planted crops.';
        alertBox.classList.remove('d-none');
    } finally {
        saveBtn.disabled = false;
        updateModalCropSummary();
    }
}
        alertBox.textContent = 'Network error while saving planted crop.';
        alertBox.classList.remove('d-none');
    } finally {
        saveBtn.disabled = false;
        saveBtn.innerHTML = `<i data-lucide="check" style="width:14px; height:14px;" class="me-1"></i> Confirm Planted Crop`;
        lucide.createIcons();
    }
}



// Toast helper
function showPlotToast(msg) {
    const toastEl = document.getElementById('plotToast');
    const msgEl = document.getElementById('plotToastMsg');
    if (toastEl && msgEl) {
        msgEl.innerHTML = `<i class="bi bi-check-circle-fill fs-5"></i> <span>${escapeHtml(msg)}</span>`;
        const toast = new bootstrap.Toast(toastEl, { delay: 3500 });
        toast.show();
    }
}

// Helpers
function findCropInLibrary(name) {
    if (!name || !Array.isArray(fullCropsLibrary)) return null;
    const lower = name.toLowerCase().trim();
    return fullCropsLibrary.find(c => 
        (c.name && c.name.toLowerCase() === lower) ||
        (c.value && c.value.toLowerCase() === lower) ||
        (c.local_name && c.local_name.toLowerCase() === lower)
    );
}

function resolveCatalogIcon(name) {
    const lower = (name || '').toLowerCase().trim();
    const match = standardCropCatalog.find(c => lower.includes(c.name.toLowerCase()) || c.name.toLowerCase().includes(lower));
    return match ? `crop-icons/${match.icon}` : 'crop-icons/tomato/tomato.svg';
}

function formatCropIconUrl(icon) {
    if (!icon) return '../assets/crop-icons/tomato/tomato.svg';
    let clean = icon.replace(/^(\.\.\/|assets\/)/, '');
    if (!clean.startsWith('crop-icons/')) {
        clean = 'crop-icons/' + clean;
    }
    return '../assets/' + clean;
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
</script>

<?php include '../includes/footer.php'; ?>
