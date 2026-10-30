<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/lands_helper.php';

$all_db_lands = get_all_lands($pdo);
$all_db_plots = get_all_plots($pdo);

// Determine active tab
$default_tab = isset($default_tab) ? $default_tab : 'browse';
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : $default_tab;
if (!in_array($active_tab, ['browse', 'requests'])) {
    $active_tab = $default_tab;
}

// Handle request submission
$success_msg = "";
$error_msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_request') {
    $land_id = intval($_POST['land_id'] ?? 0);
    $plot_id = intval($_POST['plot_id'] ?? 0);
    $purpose = htmlspecialchars(trim($_POST['purpose'] ?? ''));
    $duration = htmlspecialchars(trim($_POST['duration'] ?? '6 Months'));

    $land_title = "Unknown Land";
    foreach ($all_db_lands as $land) {
        if ($land['id'] === $land_id) {
            $land_title = $land['title'];
            break;
        }
    }

    $plot_num = "Any Plot";
    foreach ($all_db_plots as $plot) {
        if ($plot['id'] === $plot_id) {
            $plot_num = $plot['plot_number'];
            break;
        }
    }

    $gardener_id = $_SESSION['user_id'] ?? 3;
    $gardener_name = $_SESSION['user_name'] ?? 'Mary Gardener';

    try {
        $stmt = $pdo->prepare("
            INSERT INTO `requests` 
            (gardener_id, gardener_name, land_id, plot_id, land_title, plot_number, purpose, message, requested_duration, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
        ");
        $stmt->execute([
            $gardener_id,
            $gardener_name,
            $land_id,
            $plot_id,
            $land_title,
            $plot_num,
            $purpose,
            $purpose,
            $duration
        ]);
        $success_msg = "Plot application submitted successfully! Tracking is available under 'My Requests'.";
        $active_tab = 'requests'; // Seamlessly show newly submitted request
    } catch (\Exception $e) {
        $error_msg = "Failed to submit request: " . $e->getMessage();
    }
}

if (!function_exists('get_land_image_url')) {
    function get_land_image_url($id)
    {
        $images = [
            1 => 'https://images.unsplash.com/photo-1466692476868-aef1dfb1e735?auto=format&fit=crop&q=80&w=400',
            2 => 'https://images.unsplash.com/photo-1530595467537-0b5996c41f2d?auto=format&fit=crop&q=80&w=400',
            3 => 'https://images.unsplash.com/photo-1500937386664-56d1dfef3854?auto=format&fit=crop&q=80&w=400'
        ];
        return $images[$id] ?? 'https://images.unsplash.com/photo-1466692476868-aef1dfb1e735?auto=format&fit=crop&q=80&w=400';
    }
}

$approved_lands = array_filter($all_db_lands, function ($l) {
    return $l['status'] === 'approved';
});
?>

<style>
    /* Segmented Pill Tabs for Mobile */
    .tab-switcher-btn {
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        color: #5f6368;
        background: transparent;
    }

    .tab-switcher-btn.active-tab {
        background: #198754 !important;
        color: #ffffff !important;
    }

    .tab-switcher-btn.active-tab #requestsCountBadge {
        background: rgba(255, 255, 255, 0.25) !important;
        color: #ffffff !important;
    }

    @media (max-width: 767.98px) {
        .gardener-mobile-pane.hidden-on-mobile {
            display: none !important;
        }

        .gardener-mobile-pane.visible-on-mobile {
            display: block !important;
        }
    }

    @media (min-width: 768px) {

        <?php if ($default_tab === 'browse'): ?>
            #browseSection {
                display: block !important;
            }

            #requestsSection {
                display: none !important;
            }

        <?php else: ?>
            #browseSection {
                display: none !important;
            }

            #requestsSection {
                display: block !important;
            }

        <?php endif; ?>
    }
</style>

<main class="workspace-surface">
    <!-- Desktop Toolbar (Hidden on mobile) -->
    <?php if ($default_tab === 'browse'): ?>
        <div class="toolbar border-bottom d-none d-md-flex">
            <div>
                <h1 class="fs-5 fw-semibold m-0 text-dark">Available Lands</h1>
            </div>
        </div>
    <?php else: ?>
        <div class="toolbar border-bottom d-none d-md-flex">
            <div>
                <h1 class="fs-5 fw-semibold m-0 text-dark">Request Log</h1>
            </div>
        </div>
    <?php endif; ?>

    <!-- Mobile Segmented Tabs Switcher (Visible on mobile only) -->
    <div class="d-md-none sticky-top mt-3 px-3 py-2 shadow-xs" style="z-index: 1020;">
        <div class="d-flex p-1 rounded-pill" style="background-color: #f1f3f4;" role="tablist">
            <button type="button"
                class="btn btn-sm rounded-pill flex-fill py-1.5 px-3 fw-semibold border-0 tab-switcher-btn <?php echo $active_tab === 'browse' ? 'active-tab shadow-xs' : ''; ?>"
                id="mobileTabBtnBrowse" onclick="switchGardenerTab('browse')">
                <i class="bi bi-compass me-1"></i> Browse Lands
            </button>
            <button type="button"
                class="btn btn-sm rounded-pill flex-fill py-1.5 px-3 fw-semibold border-0 tab-switcher-btn <?php echo $active_tab === 'requests' ? 'active-tab shadow-xs' : ''; ?>"
                id="mobileTabBtnRequests" onclick="switchGardenerTab('requests')">
                <i class="bi bi-send me-1"></i> My Requests
                <span id="requestsCountBadge" class="badge rounded-pill ms-1 d-none" style="font-size: 10px;">0</span>
            </button>
        </div>
    </div>

    <!-- Workspace Scrollable Area -->
    <div class="workspace-scroll" style="padding-bottom: 100px !important;">

        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success d-flex align-items-center gap-2 border-0 mb-4" role="alert"
                style="border-radius: 12px; background-color: #d1e7dd; color: #0f5132;">
                <i class="bi bi-check-circle-fill fs-5 flex-shrink-0"></i>
                <div class="text-sm"><?php echo $success_msg; ?></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2 border-0 mb-4" role="alert"
                style="border-radius: 12px; background-color: #f8d7da; color: #842029;">
                <i class="bi bi-exclamation-triangle-fill fs-5 flex-shrink-0"></i>
                <div class="text-sm"><?php echo $error_msg; ?></div>
            </div>
        <?php endif; ?>

        <!-- ======================= ① BROWSE LANDS SECTION ======================= -->
        <div id="browseSection"
            class="gardener-mobile-pane <?php echo $active_tab === 'browse' ? 'visible-on-mobile' : 'hidden-on-mobile'; ?>">
            <div class="row g-4">
                <?php if (empty($approved_lands)): ?>
                    <div class="col-12 text-center py-5 text-muted">
                        <i class="bi bi-tree fs-1 d-block mb-2"></i>
                        <span>No approved lands available for community gardening right now.</span>
                    </div>
                <?php else: ?>
                    <?php foreach ($approved_lands as $land): ?>
                        <?php
                        $land_plots = array_filter($all_db_plots, function ($p) use ($land) {
                            return $p['land_id'] == $land['id'];
                        });
                        $available_count = count(array_filter($land_plots, fn($p) => $p['status'] === 'available'));
                        $total_count = count($land_plots);
                        ?>
                        <div class="col-md-6 col-xl-4">
                            <div
                                class="land-listing-card rounded-4 overflow-hidden border h-100 d-flex flex-column bg-white shadow-xs">
                                <!-- Hero Image with map toggle overlay -->
                                <div class="position-relative" style="height: 200px; overflow: hidden;">
                                    <div id="map-preview-<?php echo $land['id']; ?>" style="height: 200px; z-index: 1;"></div>
                                    <div id="image-preview-<?php echo $land['id']; ?>" class="d-none"
                                        style="height:200px; overflow:hidden;">
                                        <img src="<?php echo get_land_image_url($land['id']); ?>"
                                            alt="<?php echo htmlspecialchars($land['title']); ?>" class="w-100 h-100"
                                            style="object-fit: cover;">
                                    </div>

                                    <!-- Toggle pill -->
                                    <button type="button"
                                        class="btn btn-sm rounded-pill d-flex align-items-center gap-1 position-absolute"
                                        style="bottom: 10px; left: 10px; z-index: 10; font-size: 11px; padding: 4px 12px; background: rgba(255,255,255,0.92); border: 1px solid rgba(255,255,255,0.6); backdrop-filter: blur(4px);"
                                        onclick="toggleMediaPreview(<?php echo $land['id']; ?>, this)">
                                        <i class="bi bi-image text-success"></i>
                                        <span>View Photo</span>
                                    </button>

                                    <!-- Status Badge -->
                                    <div class="position-absolute d-flex flex-column align-items-end gap-1"
                                        style="top: 12px; right: 12px; z-index: 5;">
                                        <span class="badge"
                                            style="font-size: 10px; padding: 5px 10px; background: rgba(25,135,84,0.15); color: #0f5132; border: 1px solid rgba(25,135,84,0.25); backdrop-filter: blur(4px);">
                                            ✓ Approved
                                        </span>
                                    </div>
                                </div>

                                <!-- Card Body -->
                                <div class="p-3 pb-2 flex-grow-1 d-flex flex-column">
                                    <h3 class="fw-bold text-dark mb-0" style="font-size: 1.05rem; line-height: 1.3;">
                                        <?php echo htmlspecialchars($land['title']); ?>
                                    </h3>

                                    <p class="text-muted mb-2 mt-1" style="font-size: 0.78rem;">
                                        <i class="bi bi-geo-alt-fill me-1" style="color: var(--drive-primary, #198754);"></i>
                                        <?php echo htmlspecialchars($land['address']); ?>
                                    </p>

                                    <!-- Feature Chips Row -->
                                    <div class="d-flex gap-2 py-2 mb-2"
                                        style="border-top: 1px solid var(--drive-border, #eee); border-bottom: 1px solid var(--drive-border, #eee);">
                                        <div class="text-center flex-fill">
                                            <i class="bi bi-tree d-block mb-1 text-success" style="font-size: 1.1rem;"></i>
                                            <span style="font-size: 0.7rem; color: #6c757d;">Garden</span>
                                        </div>
                                        <div class="text-center flex-fill">
                                            <i class="bi bi-grid-3x3-gap d-block mb-1 text-success"
                                                style="font-size: 1.1rem;"></i>
                                            <span style="font-size: 0.7rem; color: #6c757d;"><?php echo $total_count; ?>
                                                plots</span>
                                        </div>
                                        <div class="text-center flex-fill">
                                            <i class="bi bi-check2-square d-block mb-1 text-success"
                                                style="font-size: 1.1rem;"></i>
                                            <span style="font-size: 0.7rem; color: #6c757d;"><?php echo $available_count; ?>
                                                free</span>
                                        </div>
                                        <div class="text-center flex-fill">
                                            <i class="bi bi-rulers d-block mb-1 text-success" style="font-size: 1.1rem;"></i>
                                            <span
                                                style="font-size: 0.7rem; color: #6c757d;"><?php echo number_format($land['area'], 0); ?>
                                                m²</span>
                                        </div>
                                    </div>

                                    <!-- Collapsible Description -->
                                    <div class="mb-2">
                                        <p class="text-secondary mb-0 land-desc-text" id="desc-text-<?php echo $land['id']; ?>"
                                            style="font-size: 0.82rem; line-height: 1.6; display: none;">
                                            <?php echo htmlspecialchars($land['description']); ?>
                                        </p>
                                        <button class="btn btn-link p-0 mt-1 desc-toggle"
                                            style="font-size: 0.75rem; color: var(--drive-primary, #198754); font-weight: 500; text-decoration: none;"
                                            onclick="toggleDesc(<?php echo $land['id']; ?>, this)">
                                            Description <i class="bi bi-chevron-down" style="font-size: 0.65rem;"></i>
                                        </button>
                                    </div>

                                    <!-- Landowner info -->
                                    <div class="d-flex align-items-center gap-2 mb-3"
                                        style="font-size: 0.75rem; color: #6c757d;">
                                        <div class="rounded-circle bg-success-subtle d-flex align-items-center justify-content-center"
                                            style="width: 24px; height: 24px; min-width:24px;">
                                            <i class="bi bi-person-fill text-success" style="font-size: 0.7rem;"></i>
                                        </div>
                                        <span><?php echo htmlspecialchars($land['landowner']); ?></span>
                                    </div>

                                    <!-- Partitioned Plots -->
                                    <?php if (!empty($land_plots)): ?>
                                        <div class="mt-auto">
                                            <p class="fw-semibold text-secondary mb-2"
                                                style="font-size: 0.7rem; letter-spacing: 0.5px; text-transform: uppercase;">
                                                Available Plots</p>
                                            <div class="row g-2">
                                                <?php foreach ($land_plots as $plot): ?>
                                                    <div class="col-3">
                                                        <div class="p-2 rounded-3 text-center d-flex flex-column align-items-center justify-content-between"
                                                            style="font-size: 0.7rem; min-height: 80px; border: 1px solid var(--drive-border, #eee); background: <?php echo $plot['status'] === 'available' ? '#f0fdf6' : '#f8f9fa'; ?>;">
                                                            <span class="fw-semibold text-dark text-truncate w-100"
                                                                style="font-size: 0.72rem;"><?php echo htmlspecialchars($plot['plot_number']); ?></span>
                                                            <span class="text-muted d-block"
                                                                style="font-size: 0.65rem;"><?php echo $plot['area']; ?> m²</span>
                                                            <?php if ($plot['status'] === 'available'): ?>
                                                                <button class="btn btn-drive-primary py-1 px-1 w-100 mt-1"
                                                                    style="font-size: 9px;" data-bs-toggle="modal"
                                                                    data-bs-target="#requestPlotModal<?php echo $plot['id']; ?>">
                                                                    Request
                                                                </button>
                                                            <?php else: ?>
                                                                <span class="badge rounded-pill mt-1 w-100"
                                                                    style="font-size: 7.5px; padding: 3px 4px; background: rgba(108,117,125,0.1); color: #6c757d;">
                                                                    <?php echo ucfirst($plot['status']); ?>
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>

                                                    <!-- Plot Request Modal -->
                                                    <div class="modal fade" id="requestPlotModal<?php echo $plot['id']; ?>"
                                                        tabindex="-1" aria-hidden="true">
                                                        <div class="modal-dialog modal-dialog-centered">
                                                            <div class="modal-content drive-modal-content">
                                                                <div class="modal-header border-0 pb-0">
                                                                    <h5 class="modal-title fw-semibold text-dark">Apply for Plot</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                        aria-label="Close"></button>
                                                                </div>
                                                                <div class="modal-body text-start">
                                                                    <form
                                                                        action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?tab=requests"
                                                                        method="POST">
                                                                        <input type="hidden" name="action" value="submit_request">
                                                                        <input type="hidden" name="land_id"
                                                                            value="<?php echo $land['id']; ?>">
                                                                        <input type="hidden" name="plot_id"
                                                                            value="<?php echo $plot['id']; ?>">

                                                                        <div class="p-3 bg-light rounded-3 mb-3"
                                                                            style="font-size: 0.8rem;">
                                                                            <strong>Land:</strong>
                                                                            <?php echo htmlspecialchars($land['title']); ?><br>
                                                                            <strong>Selected Plot:</strong>
                                                                            <?php echo htmlspecialchars($plot['plot_number']); ?>
                                                                            (<?php echo $plot['area']; ?> m²)
                                                                        </div>

                                                                        <div class="mb-3">
                                                                            <label for="duration_<?php echo $plot['id']; ?>"
                                                                                class="form-label text-secondary"
                                                                                style="font-size: 0.75rem; font-weight:600;">DURATION</label>
                                                                            <select class="form-select drive-form-control w-100"
                                                                                id="duration_<?php echo $plot['id']; ?>" name="duration"
                                                                                required>
                                                                                <option value="3 Months">3 Months</option>
                                                                                <option value="6 Months" selected>6 Months</option>
                                                                                <option value="1 Year">1 Year</option>
                                                                                <option value="Indefinite">Indefinite</option>
                                                                            </select>
                                                                        </div>

                                                                        <div class="mb-4">
                                                                            <label for="purpose_<?php echo $plot['id']; ?>"
                                                                                class="form-label text-secondary"
                                                                                style="font-size: 0.75rem; font-weight:600;">PURPOSE</label>
                                                                            <textarea class="form-control drive-form-control w-100"
                                                                                id="purpose_<?php echo $plot['id']; ?>" name="purpose"
                                                                                rows="3" required
                                                                                placeholder="Describe your gardening plans (e.g. vegetable patches, herbs, organic composting)..."></textarea>
                                                                        </div>

                                                                        <div class="d-flex justify-content-end gap-2">
                                                                            <button type="button" class="btn btn-drive-secondary"
                                                                                data-bs-dismiss="modal">Cancel</button>
                                                                            <button type="submit" class="btn btn-drive-primary">Submit
                                                                                Application</button>
                                                                        </div>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-muted mb-2 mt-auto" style="font-size: 0.78rem;">
                                            <i class="bi bi-info-circle me-1"></i>No plots partitioned yet.
                                        </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- ======================= ② MY REQUESTS SECTION ======================= -->
        <div id="requestsSection"
            class="gardener-mobile-pane <?php echo $active_tab === 'requests' ? 'visible-on-mobile' : 'hidden-on-mobile'; ?>">
            
            <!-- Filter Bar for Requests (Horizontal scrolling chips) -->
            <div class="d-flex align-items-center justify-content-between gap-2 mb-3">
                <div class="requests-filter-scroll-wrapper">
                    <div class="requests-filter-scroll" id="requestsFilterChips">
                        <button type="button" class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold request-filter-chip active flex-shrink-0" data-filter="all" onclick="filterGardenerRequests('all')">
                            All <span class="badge rounded-pill bg-white text-dark ms-1 shadow-xs" id="filterCountAll">0</span>
                        </button>
                        <button type="button" class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold request-filter-chip flex-shrink-0" data-filter="pending" onclick="filterGardenerRequests('pending')">
                            Pending <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis ms-1" id="filterCountPending">0</span>
                        </button>
                        <button type="button" class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold request-filter-chip flex-shrink-0" data-filter="approved" onclick="filterGardenerRequests('approved')">
                            Approved <span class="badge rounded-pill bg-success-subtle text-success ms-1" id="filterCountApproved">0</span>
                        </button>
                        <button type="button" class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold request-filter-chip flex-shrink-0" data-filter="rejected" onclick="filterGardenerRequests('rejected')">
                            Rejected <span class="badge rounded-pill bg-danger-subtle text-danger ms-1" id="filterCountRejected">0</span>
                        </button>
                    </div>
                </div>
                <div class="text-secondary small fw-medium text-nowrap d-none d-lg-block flex-shrink-0 ms-2" id="requestsCountLabel">
                    Showing all applications
                </div>
            </div>

            <!-- Card List Container populated by gardener_requests.js -->
            <div id="requestsTableBody" class="gardener-requests-card-list">
                <div class="text-center py-5 text-muted bg-white rounded-4 border border-light-subtle shadow-xs">
                    <div class="spinner-border spinner-border-sm text-secondary me-2" role="status"></div>
                    <span>Loading your applications...</span>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Details/Response Modal (Single dynamic modal instance) -->
<div class="modal fade" id="viewResponseModal" tabindex="-1" aria-hidden="true" style="z-index: 9999;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content drive-modal-content border-0 shadow-lg" style="border-radius: 24px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">Application Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-start pt-2">
                <div class="d-flex align-items-center gap-3 p-3 rounded-4 mb-3" style="background:#F7EFE6;border:1.5px solid #DFCFC2;">
                    <div class="gardener-request-icon-wrap" style="width:46px;height:46px;min-width:46px;">
                        <img src="../assets/images/land_icon.svg" alt="Land Icon" class="gardener-request-icon-img" width="46" height="46">
                    </div>
                    <div class="min-w-0">
                        <span class="text-secondary fw-bold text-uppercase d-block" style="font-size: 10px; letter-spacing:0.4px;">TARGET PLOT &amp; LAND</span>
                        <h6 id="modal_land_title" class="fw-bold text-dark mb-0 text-truncate"></h6>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="text-secondary fw-semibold d-block mb-1" style="font-size: 11px;">PROPOSED PURPOSE</label>
                    <div class="p-3 bg-light rounded-3 text-secondary" style="font-size:0.85rem;" id="modal_purpose">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="text-secondary fw-semibold d-block mb-1" style="font-size: 11px;">LEASE DURATION</label>
                    <div id="modal_duration" class="text-dark fw-medium"></div>
                </div>
                <div class="mb-3 d-none" id="modal_feedback_section">
                    <label class="text-secondary fw-semibold d-block mb-1" style="font-size: 11px;">LANDOWNER RESPONSE &amp; GUIDELINES</label>
                    <div class="alert alert-info py-2" style="font-size: 0.85rem; border-radius: 8px;"
                        id="modal_feedback"></div>
                </div>
                <div class="d-flex justify-content-end pt-2">
                    <button type="button" class="btn btn-drive-primary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const _leafletMaps = {};

    function initLeafletPreviewMaps() {
        <?php foreach ($approved_lands as $land): ?>
                (function () {
                    const mapId = 'map-preview-<?php echo $land['id']; ?>';
                    const lat = <?php echo floatval($land['latitude']); ?>;
                    const lng = <?php echo floatval($land['longitude']); ?>;
                    const area = <?php echo floatval($land['area']); ?>;
                    const el = document.getElementById(mapId);
                    if (!el || el._leaflet_id) return;

                    if (typeof L === 'undefined') return;

                    const map = L.map(mapId, {
                        zoomControl: false, dragging: false, touchZoom: false,
                        doubleClickZoom: false, scrollWheelZoom: false,
                        boxZoom: false, keyboard: false, attributionControl: false
                    }).setView([lat, lng], 17);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(map);

                    const radius = Math.sqrt(area / Math.PI);
                    L.circle([lat, lng], {
                        radius: radius,
                        color: '#198754',
                        weight: 2,
                        fillColor: '#198754',
                        fillOpacity: 0.15
                    }).addTo(map);

                    const pin = L.divIcon({
                        className: '',
                        html: `<div style="background:#198754;width:10px;height:10px;border:2.5px solid #fff;border-radius:50%;margin:-5px 0 0 -5px;"></div>`,
                        iconSize: [0, 0]
                    });
                    L.marker([lat, lng], { icon: pin }).addTo(map);

                    _leafletMaps[mapId] = map;
                })();
        <?php endforeach; ?>
    }

    document.addEventListener("DOMContentLoaded", function () {
        initLeafletPreviewMaps();
    });

    function switchGardenerTab(tab) {
        const browseSec = document.getElementById('browseSection');
        const reqSec = document.getElementById('requestsSection');
        const browseBtn = document.getElementById('mobileTabBtnBrowse');
        const reqBtn = document.getElementById('mobileTabBtnRequests');

        if (tab === 'browse') {
            if (browseSec) {
                browseSec.classList.add('visible-on-mobile');
                browseSec.classList.remove('hidden-on-mobile');
            }
            if (reqSec) {
                reqSec.classList.add('hidden-on-mobile');
                reqSec.classList.remove('visible-on-mobile');
            }
            if (browseBtn) browseBtn.classList.add('active-tab', 'shadow-xs');
            if (reqBtn) reqBtn.classList.remove('active-tab', 'shadow-xs');

            setTimeout(() => {
                initLeafletPreviewMaps();
                Object.values(_leafletMaps).forEach(map => {
                    try { map.invalidateSize(); } catch (e) { }
                });
            }, 80);
        } else {
            if (browseSec) {
                browseSec.classList.add('hidden-on-mobile');
                browseSec.classList.remove('visible-on-mobile');
            }
            if (reqSec) {
                reqSec.classList.add('visible-on-mobile');
                reqSec.classList.remove('hidden-on-mobile');
            }
            if (reqBtn) reqBtn.classList.add('active-tab', 'shadow-xs');
            if (browseBtn) browseBtn.classList.remove('active-tab', 'shadow-xs');

            if (typeof window.loadRequests === 'function') {
                window.loadRequests();
            }
        }

        if (history.replaceState) {
            const url = new URL(window.location);
            url.searchParams.set('tab', tab);
            history.replaceState(null, '', url);
        }
    }

    function toggleMediaPreview(landId, btn) {
        const mapEl = document.getElementById(`map-preview-${landId}`);
        const imgEl = document.getElementById(`image-preview-${landId}`);
        const text = btn.querySelector('span');
        const icon = btn.querySelector('i');
        const showingMap = !mapEl.classList.contains('d-none');
        if (showingMap) {
            mapEl.classList.add('d-none');
            imgEl.classList.remove('d-none');
            text.innerText = 'View Map';
            icon.className = 'bi bi-map text-success';
        } else {
            imgEl.classList.add('d-none');
            mapEl.classList.remove('d-none');
            text.innerText = 'View Photo';
            icon.className = 'bi bi-image text-success';
            const mapInstance = _leafletMaps[`map-preview-${landId}`];
            if (mapInstance) setTimeout(() => mapInstance.invalidateSize(), 50);
        }
    }

    function toggleDesc(landId, btn) {
        const p = document.getElementById(`desc-text-${landId}`);
        const isHidden = p.style.display === 'none';
        if (isHidden) {
            p.style.display = 'block';
            btn.innerHTML = 'Hide description <i class="bi bi-chevron-up" style="font-size:.65rem;"></i>';
        } else {
            p.style.display = 'none';
            btn.innerHTML = 'Description <i class="bi bi-chevron-down" style="font-size:.65rem;"></i>';
        }
    }
</script>

<script src="<?php echo $base_path; ?>assets/js/gardener_requests.js?v=<?php echo time(); ?>"></script>