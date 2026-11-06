<?php
$base_path = '../';
$page_title = "Manage Plot";

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

$plot_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// If no plot ID provided, redirect back to plots list
if ($plot_id <= 0) {
    header("Location: plots.php");
    exit;
}

// Fetch plot details
$plot = null;
try {
    $stmt = $pdo->prepare("
        SELECT p.*, l.id as land_id, l.title as land_title, l.address as land_address, 
               l.latitude, l.longitude, l.polygon, l.has_3d_view, l.landowner_name,
               l.allowed_seeds as land_allowed_seeds, l.description as land_description
        FROM plots p
        JOIN lands l ON p.land_id = l.id
        WHERE p.id = ?
    ");
    $stmt->execute([$plot_id]);
    $plot = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (\Exception $e) {
    $plot = null;
}

// Fetch recent harvests for this plot
$plot_harvests = [];
if ($plot) {
    try {
        $hStmt = $pdo->prepare("SELECT * FROM harvests WHERE plot_id = ? ORDER BY harvest_date DESC LIMIT 5");
        $hStmt->execute([$plot_id]);
        $plot_harvests = $hStmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (\Exception $e) {
        $plot_harvests = [];
    }
}

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
                $clean = preg_replace('/^(\.\.\/|assets\/)/', '', $item['icon']);
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
.manage-plot-container {
    font-family: 'Outfit', sans-serif;
}

.farm-card {
    background: #FFFDF9;
    border: 2px solid #523E31;
    border-radius: 20px;
    box-shadow: 0 4px 0 #523E31;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}

/* Individual Top-down Soil Bed (Exact match from Landowner & Plots Page) */
.dynamic-plot-bed {
    position: relative;
    background: radial-gradient(ellipse at center, #6A4426 0%, #4D2E16 85%, #381E0C 100%);
    border: 3.5px solid #84532B;
    border-radius: 20px;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.5), inset 0 -3px 0 rgba(40,20,5,0.6), 0 4px 8px rgba(0,0,0,0.3);
    padding: 24px 16px 16px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: transform 0.18s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.18s ease, border-color 0.18s ease;
    user-select: none;
    overflow: visible !important;
    min-height: 230px;
    height: 230px;
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
        transparent 16px
    );
    pointer-events: none;
    border-radius: 16px;
    opacity: 0.85;
}

.dynamic-plot-bed:hover {
    transform: translateY(-2px) scale(1.01);
    border-color: #FACC15;
    box-shadow: inset 0 2px 5px rgba(0,0,0,0.4), 0 8px 16px rgba(0,0,0,0.4), 0 0 0 3px rgba(250, 204, 21, 0.4);
}

/* Overlay Crop Name Pill at Top Edge of plot bed */
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
    padding: 4px 18px;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 0.9rem;
    font-weight: 800;
    color: #3E2B1E;
    white-space: nowrap;
}
.map-crop-pill img {
    width: 22px;
    height: 22px;
    object-fit: contain;
}
.map-crop-pill.available-pill {
    color: #166534;
}

/* Furrow plants growing in rows */
.bed-furrow-plants {
    position: relative;
    z-index: 2;
    display: flex;
    flex-direction: column;
    justify-content: space-evenly;
    width: 100%;
    height: 100%;
    padding: 12px 8px 4px;
    gap: 8px;
}

.bed-furrow-row {
    display: flex;
    align-items: center;
    justify-content: space-evenly;
    width: 100%;
}

.bed-sprout-icon {
    width: 34px;
    height: 34px;
    object-fit: contain;
    filter: drop-shadow(0 2px 3px rgba(0,0,0,0.6));
    transition: transform 0.18s ease;
}
.dynamic-plot-bed:hover .bed-sprout-icon {
    transform: scale(1.15);
}

/* Empty furrow soil lines */
.bed-empty-soil-lines {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-evenly;
    padding: 16px 8px 8px;
}

.furrow-soil-line {
    width: 85%;
    height: 9px;
    margin: 0 auto;
    background: rgba(40, 20, 8, 0.55);
    border-radius: 9999px;
    box-shadow: inset 0 1px 3px rgba(0,0,0,0.65), 0 1px 0 rgba(140, 90, 48, 0.4);
}

.seed-tag {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 10px;
    background: #FAF6EF;
    color: #4A3E36;
    border: 1px solid #DFCFC2;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
}

.seed-tag img {
    width: 14px;
    height: 14px;
    object-fit: contain;
    flex-shrink: 0;
}

/* Chore task checkbox item */
.chore-item {
    border: 1.5px solid #E5E7EB;
    border-radius: 12px;
    padding: 12px 14px;
    background: #FFFFFF;
    transition: all 0.15s ease;
}
.chore-item:hover {
    border-color: #10B981;
    background: #F0FDF4;
}
.chore-item.completed {
    background: #F9FAFB;
    border-color: #E5E7EB;
    opacity: 0.7;
}
.chore-item.completed .chore-title {
    text-decoration: line-through;
    color: #6B7280;
}

/* Modal crop selection grid */
.crop-select-radio-card {
    border: 2px solid #E8DFD8;
    border-radius: 14px;
    padding: 10px;
    cursor: pointer;
    transition: all 0.2s ease;
    background: #FFFFFF;
}
.crop-select-radio-card:hover {
    border-color: #7FA668;
    background: #FAFDF8;
    transform: translateY(-2px);
}
.crop-select-radio-card.selected {
    border-color: #2D5A27;
    background: #F2F8F0;
    box-shadow: 0 0 0 1px #2D5A27;
}
.crop-chk-indicator {
    width: 20px;
    height: 20px;
    border-radius: 6px;
    border: 2px solid #D1D5DB;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s ease;
    flex-shrink: 0;
    background: #FFF;
}
.crop-select-radio-card.selected .crop-chk-indicator {
    background: #2D5A27;
    border-color: #2D5A27;
    color: #FFF;
}
</style>

<main class="workspace-surface manage-plot-container flex-1 flex flex-col overflow-hidden h-full">
    <!-- Workspace Scrollable Area -->
    <div class="workspace-scroll p-0 overflow-y-auto">
        <!-- Toolbar/Title Bar (Natural scroll, transparent background, without Change Crop button) -->
        <div class="toolbar border-bottom px-4 py-3 d-flex align-items-center justify-content-between flex-shrink-0">
            <div class="d-flex align-items-center gap-3 min-w-0 flex-grow-1 me-3">
                <a href="plots.php" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 d-flex align-items-center gap-1 text-decoration-none flex-shrink-0">
                    <i class="bi bi-arrow-left"></i>
                    <span class="d-none d-sm-inline">Back to My Plots</span>
                </a>

                <div class="min-w-0">
                    <div class="d-flex align-items-center gap-2 min-w-0">
                        <span class="badge rounded-pill px-2.5 py-0.5 text-xs fw-bold flex-shrink-0" style="background:#EBF3E8; color:#2D5A27; border:1px solid #BDDAB8;">
                            <?php echo htmlspecialchars($plot['plot_number'] ?? 'Plot'); ?>
                        </span>
                        <h1 class="fs-6 fw-bold m-0 text-dark text-truncate">
                            <?php echo htmlspecialchars($plot['land_title'] ?? 'Farm Plot'); ?>
                        </h1>
                    </div>
                    <span class="text-secondary text-xs text-truncate d-block" title="<?php echo htmlspecialchars($plot['land_address'] ?? 'General Santos City'); ?>">
                        <i class="bi bi-geo-alt-fill text-success me-0.5"></i>
                        <?php echo htmlspecialchars($plot['land_address'] ?? 'General Santos City'); ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="p-4">
            <?php if (!$plot): ?>
            <!-- Not Found State -->
            <div class="text-center py-5">
                <div class="rounded-circle p-3 bg-light d-inline-flex mb-3">
                    <i data-lucide="alert-circle" class="text-danger" style="width:40px; height:40px;"></i>
                </div>
                <h3 class="fw-bold text-dark mb-1">Plot Not Found</h3>
                <p class="text-muted small mb-4">The requested partition plot could not be located or you may not have access to it.</p>
                <a href="plots.php" class="btn btn-drive-primary rounded-pill px-4 py-2 fw-semibold">
                    Return to My Plots
                </a>
            </div>
        <?php else: ?>
            <?php
            $plot_num = $plot['plot_number'] ?? 'Plot A-1';
            $land_title = $plot['land_title'] ?? 'Community Garden';
            $land_address = $plot['land_address'] ?? 'General Santos City';
            $area = (float)($plot['area_sqm'] ?? $plot['area'] ?? 50);
            $rent_rate = (float)($plot['rental_price_per_year'] ?? 110000);
            $crop = trim($plot['crop'] ?? '');
            $crop_icon = $plot['crop_icon'] ?? '';
            $crop_items = array_values(array_filter(array_map('trim', explode(',', $crop))));
            $is_planted = !empty($crop_items);
            $primary_crop = $is_planted ? $crop_items[0] : '';
            $resolved_icon = resolveCropIcon($primary_crop, $crop_icon);
            $others_count = max(0, count($crop_items) - 1);
            $pill_text = $others_count > 0 ? htmlspecialchars(ucfirst($primary_crop)) . ' & + ' . $others_count . ' other' . ($others_count > 1 ? 's' : '') : htmlspecialchars(ucfirst($primary_crop));

            // Seeds configured for this land
            $seeds = json_decode($plot['allowed_seeds'] ?? '[]', true);
            if (!is_array($seeds) || empty($seeds)) {
                $seeds = json_decode($plot['land_allowed_seeds'] ?? '[]', true);
            }
            if (!is_array($seeds) || empty($seeds)) {
                $seeds = ['Tomato', 'Eggplant', 'Lettuce', 'Sweet Corn'];
            }

            $lease_end = !empty($plot['lease_end']) ? date('M d, Y', strtotime($plot['lease_end'])) : '1 Year Active';
            ?>

            <div class="row g-4">
                <!-- Left Column: Visual Plot Bed & Land Specifications -->
                <div class="col-lg-7">
                    <!-- 1. Visual Top-Down Cultivation Bed Card -->
                    <div class="farm-card p-4 mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <i data-lucide="grid-3x3" class="text-success" style="width: 20px; height: 20px;"></i>
                                <h2 class="h6 fw-bold text-dark mb-0">Plot Cultivation Bed</h2>
                            </div>
                        </div>

                        <!-- Top-down Soil Bed Canvas -->
                        <div class="my-2 pt-2">
                            <div class="dynamic-plot-bed" id="mainPlotBed" onclick="openPlantModal()" title="Click to <?php echo $is_planted ? 'change crop' : 'plant crop'; ?>">
                                <?php if ($is_planted): ?>
                                    <div class="map-crop-pill">
                                        <img src="<?php echo htmlspecialchars($resolved_icon); ?>" alt="<?php echo htmlspecialchars(ucfirst($primary_crop)); ?>">
                                        <span><?php echo $pill_text; ?></span>
                                    </div>
                                    <div class="bed-furrow-plants">
                                        <?php for ($r = 0; $r < 4; $r++): 
                                            $row_c = $crop_items[$r % count($crop_items)];
                                            $row_ic = resolveCropIcon($row_c);
                                        ?>
                                            <div class="bed-furrow-row">
                                                <img src="<?php echo htmlspecialchars($row_ic); ?>" class="bed-sprout-icon" alt="<?php echo htmlspecialchars($row_c); ?>">
                                                <img src="<?php echo htmlspecialchars($row_ic); ?>" class="bed-sprout-icon" alt="<?php echo htmlspecialchars($row_c); ?>">
                                                <img src="<?php echo htmlspecialchars($row_ic); ?>" class="bed-sprout-icon" alt="<?php echo htmlspecialchars($row_c); ?>">
                                                <img src="<?php echo htmlspecialchars($row_ic); ?>" class="bed-sprout-icon" alt="<?php echo htmlspecialchars($row_c); ?>">
                                                <img src="<?php echo htmlspecialchars($row_ic); ?>" class="bed-sprout-icon" alt="<?php echo htmlspecialchars($row_c); ?>">
                                            </div>
                                        <?php endfor; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="map-crop-pill available-pill">
                                        <i class="bi bi-plus-circle-fill text-success" style="font-size:0.95rem;"></i>
                                        <span>Ready to Plant</span>
                                    </div>
                                    <div class="bed-empty-soil-lines">
                                        <div class="furrow-soil-line"></div>
                                        <div class="furrow-soil-line"></div>
                                        <div class="furrow-soil-line"></div>
                                        <div class="furrow-soil-line"></div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between mt-3 pt-2 text-xs text-muted border-top">
                            <span><i class="bi bi-cursor-fill text-success me-1"></i> Tap on the soil bed above to plant or switch crops</span>
                            <button type="button" class="btn btn-sm btn-light border rounded-pill px-3 py-1 fw-semibold text-xs" onclick="openPlantModal()">
                                <i class="bi bi-pencil me-1"></i> <?php echo $is_planted ? 'Change Crop' : 'Plant Crop'; ?>
                            </button>
                        </div>
                    </div>

                    <!-- 2. Plot Specifications & Lease Summary Card -->
                    <div class="farm-card p-4">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <i data-lucide="info" class="text-primary" style="width: 18px; height: 18px;"></i>
                            <h2 class="h6 fw-bold text-dark mb-0">Plot &amp; Lease Specifications</h2>
                        </div>

                        <div class="row g-3">
                            <div class="col-6 col-md-3">
                                <div class="p-2.5 rounded-3 bg-light border">
                                    <div class="text-muted text-xs uppercase fw-bold">Partition Area</div>
                                    <div class="fw-bold text-dark fs-6 mt-0.5"><?php echo number_format($area, 0); ?> m²</div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="p-2.5 rounded-3 bg-light border">
                                    <div class="text-muted text-xs uppercase fw-bold">Annual Lease</div>
                                    <div class="fw-bold text-dark fs-6 mt-0.5">₱<?php echo number_format($rent_rate, 0); ?></div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="p-2.5 rounded-3 bg-light border">
                                    <div class="text-muted text-xs uppercase fw-bold">Lease Expiry</div>
                                    <div class="fw-bold text-dark fs-6 mt-0.5"><?php echo htmlspecialchars($lease_end); ?></div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="p-2.5 rounded-3 bg-light border">
                                    <div class="text-muted text-xs uppercase fw-bold">Landowner</div>
                                    <div class="fw-bold text-dark fs-6 mt-0.5 text-truncate" title="<?php echo htmlspecialchars($plot['landowner_name'] ?? 'Landowner'); ?>">
                                        <?php echo htmlspecialchars($plot['landowner_name'] ?? 'Landowner'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Crops list -->
                        <div class="mt-3 pt-3 border-top">
                            <div class="text-secondary text-xs fw-semibold mb-2">Crops:</div>
                            <div class="d-flex flex-wrap gap-1.5" id="plotPlantedCropsTags">
                                <?php if (!empty($crop_items)): ?>
                                    <?php foreach ($crop_items as $c_item): ?>
                                        <?php $c_icon = resolveCropIcon($c_item); ?>
                                        <span class="seed-tag">
                                            <img src="<?php echo htmlspecialchars($c_icon); ?>" alt="<?php echo htmlspecialchars($c_item); ?>" style="width: 14px; height: 14px; object-fit: contain;">
                                            <span><?php echo htmlspecialchars(ucfirst($c_item)); ?></span>
                                        </span>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <span class="text-muted text-xs">No crops planted yet</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Chores & Harvest Tracking -->
                <div class="col-lg-5">
                    <!-- 1. Daily Chores & Plot Care -->
                    <div class="farm-card p-4 mb-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <i data-lucide="check-circle-2" class="text-success" style="width: 18px; height: 18px;"></i>
                                <h2 class="h6 fw-bold text-dark mb-0">Daily Chores &amp; Tasks</h2>
                            </div>
                        </div>

                        <div class="d-flex flex-col gap-2" id="choresContainer">
                            <label class="chore-item d-flex align-items-center justify-content-between cursor-pointer" onclick="toggleChore(this, 'Watering')">
                                <div class="d-flex align-items-center gap-2.5">
                                    <input type="checkbox" class="form-check-input mt-0" style="accent-color:#10B981; width:17px; height:17px;">
                                    <div>
                                        <div class="fw-semibold text-dark text-xs chore-title">Morning Soil Hydration</div>
                                        <div class="text-muted" style="font-size:0.7rem;">Water bed thoroughly at dawn</div>
                                    </div>
                                </div>
                            </label>

                            <label class="chore-item d-flex align-items-center justify-content-between cursor-pointer" onclick="toggleChore(this, 'Weeding')">
                                <div class="d-flex align-items-center gap-2.5">
                                    <input type="checkbox" class="form-check-input mt-0" style="accent-color:#10B981; width:17px; height:17px;">
                                    <div>
                                        <div class="fw-semibold text-dark text-xs chore-title">Weed &amp; Furrow Aeration</div>
                                        <div class="text-muted" style="font-size:0.7rem;">Remove wild sprouts around bed</div>
                                    </div>
                                </div>
                            </label>

                            <label class="chore-item d-flex align-items-center justify-content-between cursor-pointer" onclick="toggleChore(this, 'Inspection')">
                                <div class="d-flex align-items-center gap-2.5">
                                    <input type="checkbox" class="form-check-input mt-0" style="accent-color:#10B981; width:17px; height:17px;">
                                    <div>
                                        <div class="fw-semibold text-dark text-xs chore-title">Leaf &amp; Pest Inspection</div>
                                        <div class="text-muted" style="font-size:0.7rem;">Check underside of leaves for pests</div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- 2. Plot Harvest Records -->
                    <div class="farm-card p-4">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <i data-lucide="shopping-bag" class="text-success" style="width: 18px; height: 18px;"></i>
                                <h2 class="h6 fw-bold text-dark mb-0">Harvest History</h2>
                            </div>
                            <a href="harvests.php" class="btn btn-sm btn-outline-success rounded-pill px-2.5 py-1 text-xs d-flex align-items-center gap-1">
                                <i class="bi bi-plus"></i> Record Harvest
                            </a>
                        </div>

                        <?php if (empty($plot_harvests)): ?>
                            <div class="text-center py-4 text-muted bg-light rounded-3 border">
                                <i class="bi bi-basket fs-4 d-block mb-1 text-secondary opacity-50"></i>
                                <div class="text-xs fw-medium text-secondary">No harvests recorded yet for this plot</div>
                                <span style="font-size: 0.72rem;">Log yield once crops are gathered to compute profit</span>
                            </div>
                        <?php else: ?>
                            <div class="d-flex flex-col gap-2">
                                <?php foreach ($plot_harvests as $h): ?>
                                    <div class="p-2.5 rounded-3 border bg-white d-flex align-items-center justify-content-between">
                                        <div>
                                            <div class="fw-bold text-dark text-xs"><?php echo htmlspecialchars($h['crop_name']); ?></div>
                                            <span class="text-muted" style="font-size:0.7rem;"><?php echo date('M d, Y', strtotime($h['harvest_date'])); ?></span>
                                        </div>
                                        <div class="text-end">
                                            <span class="fw-bold text-success text-xs"><?php echo number_format($h['quantity_kg'], 1); ?> kg</span>
                                            <?php if (!empty($h['net_profit'])): ?>
                                                <div class="text-muted" style="font-size:0.68rem;">₱<?php echo number_format($h['net_profit'], 0); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Plant / Change Crop Modal -->
            <div class="modal fade" id="plantCropModal" tabindex="-1" aria-hidden="true" style="z-index: 10050;">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg" style="border-radius: 24px; background: #FFFDF9;">
                        <div class="modal-header border-0 pb-0">
                            <div>
                                <h5 class="modal-title fw-bold text-dark font-['Outfit']">Plant or Change Crop</h5>
                                <p class="text-secondary text-xs mb-0"><?php echo htmlspecialchars($plot_num); ?> · <?php echo htmlspecialchars($land_title); ?></p>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body pt-3">
                            <input type="hidden" id="selectedCropName" value="<?php echo htmlspecialchars($crop); ?>">
                            <input type="hidden" id="selectedCropIcon" value="<?php echo htmlspecialchars($crop_icon); ?>">

                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <label class="form-label text-secondary fw-bold text-xs text-uppercase mb-0">
                                    Select Crops to Plant:
                                </label>
                                <span class="badge rounded-pill bg-success-subtle text-success text-xs px-2 py-0.5" id="selectedCropsCountBadge">0 selected</span>
                            </div>

                            <!-- Search Input for Crops -->
                            <div class="position-relative mb-3">
                                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" style="font-size: 0.8rem;"></i>
                                <input type="text" id="cropModalSearchInput" class="form-control form-control-sm ps-5 rounded-pill border-light-subtle"
                                    placeholder="Search crop variety..." oninput="filterModalCrops(this.value.toLowerCase().trim())">
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
            const currentPlotId = <?php echo json_encode($plot_id); ?>;
            const plotConfiguredSeeds = <?php echo json_encode($seeds); ?>;
            const fullCropsLibrary = <?php echo json_encode(function_exists('getCropsLibrary') ? getCropsLibrary() : []); ?>;

            let plantCropModalInst = null;
            let plotToastInst = null;
            let selectedModalCrops = [];

            document.addEventListener('DOMContentLoaded', function() {
                const modalEl = document.getElementById('plantCropModal');
                if (modalEl) plantCropModalInst = new bootstrap.Modal(modalEl);

                const toastEl = document.getElementById('plotToast');
                if (toastEl) plotToastInst = new bootstrap.Toast(toastEl, { delay: 3500 });

                if (window.lucide) lucide.createIcons();
            });

            function openPlantModal() {
                const currentCropStr = document.getElementById('selectedCropName').value || '';
                selectedModalCrops = currentCropStr ? currentCropStr.split(',').map(s => s.trim()).filter(Boolean) : [];
                
                const container = document.getElementById('cropOptionCardsContainer');
                container.innerHTML = '';

                // Build options list
                let list = [];
                if (Array.isArray(plotConfiguredSeeds) && plotConfiguredSeeds.length > 0) {
                    plotConfiguredSeeds.forEach(sName => {
                        const found = findCropInLibrary(sName);
                        list.push({
                            name: sName,
                            icon: found ? found.icon : 'crop-icons/generic-plant/generic-plant.svg',
                            isConfigured: true
                        });
                    });
                }

                // Add remaining library crops
                if (Array.isArray(fullCropsLibrary)) {
                    fullCropsLibrary.forEach(libCrop => {
                        if (!list.some(item => item.name.toLowerCase() === libCrop.name.toLowerCase())) {
                            list.push({
                                name: libCrop.name,
                                icon: libCrop.icon,
                                isConfigured: false
                            });
                        }
                    });
                }

                list.forEach(c => {
                    const col = document.createElement('div');
                    col.className = 'col-6 crop-option-item';
                    col.setAttribute('data-name', c.name.toLowerCase());

                    const isSelected = selectedModalCrops.some(item => item.toLowerCase() === c.name.toLowerCase());
                    const iconUrl = formatCropIconUrl(c.icon);

                    col.innerHTML = `
                        <div class="crop-select-radio-card d-flex align-items-center justify-content-between p-2 ${isSelected ? 'selected' : ''}"
                             onclick="toggleModalCrop('${escapeHtml(c.name)}', this)">
                            <div class="d-flex align-items-center gap-2 min-w-0">
                                <img src="${iconUrl}" style="width:28px; height:28px; object-fit:contain;" alt="${escapeHtml(c.name)}">
                                <div class="min-w-0 flex-grow-1">
                                    <div class="fw-bold text-dark text-xs text-truncate">${escapeHtml(c.name)}</div>
                                    <span class="text-muted" style="font-size:9px;">${c.isConfigured ? '✓ Suitable' : 'Option'}</span>
                                </div>
                            </div>
                            <div class="crop-chk-indicator">
                                <i class="bi bi-check" style="font-size:14px; display:${isSelected ? 'block' : 'none'};"></i>
                            </div>
                        </div>
                    `;
                    container.appendChild(col);
                });

                updateModalCropSummary();
                const alertBox = document.getElementById('plantModalAlert');
                if (alertBox) alertBox.classList.add('d-none');

                if (plantCropModalInst) plantCropModalInst.show();
            }

            function toggleModalCrop(name, cardEl) {
                const lowerName = name.toLowerCase();
                const existingIdx = selectedModalCrops.findIndex(c => c.toLowerCase() === lowerName);

                if (existingIdx >= 0) {
                    selectedModalCrops.splice(existingIdx, 1);
                    if (cardEl) {
                        cardEl.classList.remove('selected');
                        const chk = cardEl.querySelector('.crop-chk-indicator i');
                        if (chk) chk.style.display = 'none';
                    }
                } else {
                    selectedModalCrops.push(name);
                    if (cardEl) {
                        cardEl.classList.add('selected');
                        const chk = cardEl.querySelector('.crop-chk-indicator i');
                        if (chk) chk.style.display = 'block';
                    }
                }

                document.getElementById('selectedCropName').value = selectedModalCrops.join(', ');
                updateModalCropSummary();
                const alertBox = document.getElementById('plantModalAlert');
                if (alertBox) alertBox.classList.add('d-none');
            }

            function updateModalCropSummary() {
                const badge = document.getElementById('selectedCropsCountBadge');
                const saveBtn = document.getElementById('btnSavePlantedCrop');
                const count = selectedModalCrops.length;

                if (badge) {
                    badge.textContent = `${count} selected`;
                    if (count > 0) {
                        badge.className = 'badge rounded-pill bg-success text-white text-xs px-2.5 py-0.5';
                    } else {
                        badge.className = 'badge rounded-pill bg-secondary-subtle text-secondary text-xs px-2.5 py-0.5';
                    }
                }

                if (saveBtn) {
                    if (count === 0) {
                        saveBtn.innerHTML = `<i data-lucide="plus" style="width:14px; height:14px;" class="me-1"></i> Plant Crops`;
                    } else if (count === 1) {
                        saveBtn.innerHTML = `<i data-lucide="check" style="width:14px; height:14px;" class="me-1"></i> Plant 1 Crop (${escapeHtml(selectedModalCrops[0])})`;
                    } else {
                        saveBtn.innerHTML = `<i data-lucide="check" style="width:14px; height:14px;" class="me-1"></i> Plant ${count} Crops`;
                    }
                    if (window.lucide) lucide.createIcons();
                }
            }

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

            async function savePlantedCrop() {
                const alertBox = document.getElementById('plantModalAlert');
                const saveBtn = document.getElementById('btnSavePlantedCrop');

                if (selectedModalCrops.length === 0) {
                    alertBox.textContent = 'Please choose at least one crop to plant.';
                    alertBox.classList.remove('d-none');
                    return;
                }

                const primaryCrop = selectedModalCrops[0];
                const primaryFound = findCropInLibrary(primaryCrop);
                const primaryIcon = primaryFound ? primaryFound.icon : 'crop-icons/tomato/tomato.svg';

                saveBtn.disabled = true;
                saveBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Saving...`;

                try {
                    const res = await fetch('../api/lands.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            action: 'gardener_plant_crop',
                            plot_id: currentPlotId,
                            crops: selectedModalCrops,
                            crop: selectedModalCrops.join(', '),
                            crop_icon: primaryIcon
                        })
                    });

                    const data = await res.json();
                    if (data.status === 'success') {
                        // Update soil bed visual dynamically
                        const bedEl = document.getElementById('mainPlotBed');
                        if (bedEl) {
                            const primaryIconUrl = formatCropIconUrl(primaryIcon);
                            const primaryDisplay = primaryCrop.charAt(0).toUpperCase() + primaryCrop.slice(1);
                            const othersCount = selectedModalCrops.length - 1;
                            const pillLabel = othersCount > 0 ? `${escapeHtml(primaryDisplay)} & +${othersCount} other${othersCount > 1 ? 's' : ''}` : escapeHtml(primaryDisplay);

                            const furrowRows = [0, 1, 2, 3].map((rIdx) => {
                                const rowCrop = selectedModalCrops[rIdx % selectedModalCrops.length];
                                const rowFound = findCropInLibrary(rowCrop);
                                const rowIconUrl = formatCropIconUrl(rowFound ? rowFound.icon : 'crop-icons/tomato/tomato.svg');
                                return `
                                    <div class="bed-furrow-row">
                                        <img src="${rowIconUrl}" class="bed-sprout-icon" alt="${escapeHtml(rowCrop)}">
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
                            bedEl.title = `Click to change crop (${selectedModalCrops.join(', ')})`;
                        }

                        // Update crops tags under Plot & Lease Specifications
                        const tagsContainer = document.getElementById('plotPlantedCropsTags');
                        if (tagsContainer) {
                            if (selectedModalCrops.length > 0) {
                                tagsContainer.innerHTML = selectedModalCrops.map(cName => {
                                    const found = findCropInLibrary(cName);
                                    const iconUrl = formatCropIconUrl(found ? found.icon : 'crop-icons/tomato/tomato.svg');
                                    const cDisplay = cName.charAt(0).toUpperCase() + cName.slice(1);
                                    return `
                                        <span class="seed-tag">
                                            <img src="${iconUrl}" alt="${escapeHtml(cDisplay)}" style="width: 14px; height: 14px; object-fit: contain;">
                                            <span>${escapeHtml(cDisplay)}</span>
                                        </span>
                                    `;
                                }).join('');
                            } else {
                                tagsContainer.innerHTML = `<span class="text-muted text-xs">No crops planted yet</span>`;
                            }
                        }

                        document.getElementById('selectedCropName').value = selectedModalCrops.join(', ');
                        document.getElementById('selectedCropIcon').value = primaryIcon;

                        if (plantCropModalInst) plantCropModalInst.hide();
                        showPlotToast(`Planted ${selectedModalCrops.join(', ')} successfully!`);
                    } else {
                        alertBox.textContent = data.message || 'Failed to update planted crop.';
                        alertBox.classList.remove('d-none');
                    }
                } catch (err) {
                    alertBox.textContent = 'Network error while saving planted crop.';
                    alertBox.classList.remove('d-none');
                } finally {
                    saveBtn.disabled = false;
                    updateModalCropSummary();
                }
            }

            function toggleChore(labelEl, choreName) {
                const chk = labelEl.querySelector('input[type="checkbox"]');
                if (chk) {
                    if (chk.checked) {
                        labelEl.classList.add('completed');
                        showPlotToast(`Task completed: ${choreName}`);
                    } else {
                        labelEl.classList.remove('completed');
                    }
                }
            }

            function showPlotToast(msg) {
                const msgEl = document.getElementById('plotToastMsg');
                if (msgEl) msgEl.querySelector('span').textContent = msg;
                if (plotToastInst) plotToastInst.show();
            }

            function findCropInLibrary(name) {
                if (!Array.isArray(fullCropsLibrary)) return null;
                const lower = name.toLowerCase().trim();
                return fullCropsLibrary.find(c => c.name.toLowerCase() === lower || (c.local_name && c.local_name.toLowerCase() === lower));
            }

            function formatCropIconUrl(icon) {
                if (!icon) return '../assets/crop-icons/tomato/tomato.svg';
                let clean = icon.replace(/^\/+/, '');
                if (!clean.startsWith('crop-icons/') && !clean.startsWith('assets/')) {
                    clean = 'crop-icons/' + clean;
                }
                if (!clean.startsWith('assets/')) {
                    clean = 'assets/' + clean;
                }
                return '../' + clean;
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
        <?php endif; ?>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
