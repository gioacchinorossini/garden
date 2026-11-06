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

    $gardener_id = $_SESSION['user_id'] ?? null;
    $gardener_name = $_SESSION['user_name'] ?? 'Gardener';

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
        <div class="toolbar requests-toolbar d-none d-md-flex">
            <div class="toolbar-title-wrap">
                <h1 class="fs-5 fw-semibold m-0 text-dark">Plot Requests</h1>
                <p class="text-muted mb-0" style="font-size: 0.78rem;">Track your plot applications and manage lease offers</p>
            </div>

            <div class="toolbar-actions-wrap">
                <!-- Search bar -->
                <div class="position-relative toolbar-search-wrap">
                    <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"
                        style="font-size: 0.8rem; pointer-events: none;"></i>
                    <input type="text" id="requestsSearchInput"
                        class="form-control rounded-pill ps-5"
                        placeholder="Search land, plot...">
                </div>

                <!-- Filter tabs (Approved, Pending, Rejected) -->
                <div class="filter-pill-track" role="group">
                    <button type="button" class="btn btn-sm rounded-pill request-filter-btn text-secondary"
                        data-filter="approved" id="filterApprovedBtn">
                        Approved <span class="badge rounded-pill bg-success ms-1" id="countApproved">0</span>
                    </button>
                    <button type="button" class="btn btn-sm rounded-pill request-filter-btn text-secondary"
                        data-filter="pending" id="filterPendingBtn">
                        Pending <span class="badge rounded-pill bg-warning text-dark ms-1" id="countPending">0</span>
                    </button>
                    <button type="button" class="btn btn-sm rounded-pill request-filter-btn text-secondary"
                        data-filter="rejected" id="filterRejectedBtn">
                        Rejected <span class="badge rounded-pill bg-danger ms-1" id="countRejected">0</span>
                    </button>
                </div>
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
    <div class="workspace-scroll p-3 px-md-4 pt-md-2" style="padding-bottom: 100px !important;">

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
                                    <h3 class="fw-bold text-dark mb-0 text-truncate" style="font-size: 1.05rem; line-height: 1.3;" title="<?php echo htmlspecialchars($land['title']); ?>">
                                        <?php echo htmlspecialchars($land['title']); ?>
                                    </h3>

                                    <p class="text-muted mb-2 mt-1" style="font-size: 0.78rem; word-break: break-word; overflow-wrap: anywhere; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.35;" title="<?php echo htmlspecialchars($land['address']); ?>">
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

                                    <!-- Available Amenities & Utilities Row -->
                                    <?php if (!empty($land['amenities']) && is_array($land['amenities'])): ?>
                                        <div class="d-flex align-items-center gap-1.5 flex-wrap mb-2">
                                            <?php foreach ($land['amenities'] as $amenity): 
                                                $iconUrl = function_exists('resolve_amenity_icon') ? resolve_amenity_icon($amenity, $base_path) : ($base_path . 'assets/amenity-icons/general.svg');
                                            ?>
                                                <span class="badge bg-white text-dark border rounded-pill px-2.5 py-1 d-inline-flex align-items-center gap-1.5 shadow-2xs" style="font-size: 10px; font-weight: 600;">
                                                    <img src="<?php echo htmlspecialchars($iconUrl); ?>" alt="<?php echo htmlspecialchars($amenity); ?>" style="width: 14px; height: 14px; object-fit: contain;">
                                                    <span><?php echo htmlspecialchars($amenity); ?></span>
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php endif; ?>

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
                                                                <button type="button" class="btn btn-drive-primary py-1 px-1 w-100 mt-1"
                                                                    style="font-size: 9px;" data-bs-toggle="modal"
                                                                    data-bs-target="#requestPlotModal"
                                                                    data-land-id="<?php echo $land['id']; ?>"
                                                                    data-land-title="<?php echo htmlspecialchars($land['title'], ENT_QUOTES); ?>"
                                                                    data-plot-id="<?php echo $plot['id']; ?>"
                                                                    data-plot-number="<?php echo htmlspecialchars($plot['plot_number'], ENT_QUOTES); ?>"
                                                                    data-plot-area="<?php echo htmlspecialchars($plot['area'], ENT_QUOTES); ?>"
                                                                    data-landowner="<?php echo htmlspecialchars($land['landowner'], ENT_QUOTES); ?>">
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

            <!-- Mobile Header Controls for Requests (Visible only on mobile < md) -->
            <div class="d-block d-md-none mb-3">
                <div class="position-relative mb-2.5">
                    <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"
                        style="font-size: 0.85rem; pointer-events: none;"></i>
                    <input type="text" id="mobileSearchInput"
                        class="form-control form-control-sm ps-5 py-2 rounded-pill border-light-subtle bg-white shadow-xs"
                        placeholder="Search requests...">
                </div>

                <div class="d-flex align-items-center gap-2 overflow-x-auto py-1.5 px-0.5 no-scrollbar">
                    <button type="button"
                        class="btn btn-sm rounded-pill request-filter-btn bg-white border flex-shrink-0 text-secondary"
                        data-filter="approved">
                        Approved <span class="badge rounded-pill bg-success ms-1" id="mCountApproved">0</span>
                    </button>
                    <button type="button"
                        class="btn btn-sm rounded-pill request-filter-btn bg-white border flex-shrink-0 text-secondary"
                        data-filter="pending">
                        Pending <span class="badge rounded-pill bg-warning text-dark ms-1" id="mCountPending">0</span>
                    </button>
                    <button type="button"
                        class="btn btn-sm rounded-pill request-filter-btn bg-white border flex-shrink-0 text-secondary"
                        data-filter="rejected">
                        Rejected <span class="badge rounded-pill bg-danger ms-1" id="mCountRejected">0</span>
                    </button>
                </div>
            </div>


            <!-- Desktop Table View (Hidden on mobile < md, identical to Landowner) -->
            <div class="d-none d-md-block request-table-container mb-4">
                <!-- Header Row -->
                <div class="d-flex align-items-center justify-content-between px-4 py-3 request-table-header"
                    style="font-size: 0.75rem; font-weight: 800; letter-spacing: 0.5px;">
                    <div style="width: 23%; min-width: 170px;">TARGET PROPERTY &amp; OWNER</div>
                    <div style="width: 23%; min-width: 170px;">PLOT &amp; DETAILS</div>
                    <div style="width: 17%; min-width: 130px;">PURPOSE &amp; DURATION</div>
                    <div style="width: 13%; min-width: 100px;">STATUS</div>
                    <div style="width: 24%; min-width: 215px;" class="text-end pe-1">ACTION</div>
                </div>

                <!-- Request Rows Placeholder -->
                <div id="requestsTableBody">
                    <div class="text-center py-5 text-muted">
                        <div class="spinner-border spinner-border-sm text-secondary me-2" role="status"></div>
                        <span>Loading your applications...</span>
                    </div>
                </div>
            </div>

            <!-- Mobile Card List View (Visible only on mobile < md) -->
            <div class="d-md-none" id="requestsMobileCards">
                <!-- Dynamic cards injected here -->
            </div>
        </div>
    </div>
</main>

<!-- Single Dynamic Plot Application Modal (Root level for proper viewport centering) -->
<div class="modal fade" id="requestPlotModal" tabindex="-1" aria-hidden="true" style="z-index: 9999;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content drive-modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">Apply for Plot</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>?tab=requests" method="POST"
                    id="applyPlotForm">
                    <input type="hidden" name="action" value="submit_request">
                    <input type="hidden" name="land_id" id="apply_land_id" value="">
                    <input type="hidden" name="plot_id" id="apply_plot_id" value="">

                    <!-- Target Land & Plot Info Card -->
                    <div class="d-flex align-items-center justify-content-between p-3 rounded-4 mb-3"
                        style="background:#F7EFE6;border:1.5px solid #DFCFC2;">
                        <div class="d-flex align-items-center gap-3 min-w-0">
                            <div class="gardener-request-icon-wrap" style="width:44px;height:44px;min-width:44px;">
                                <img src="<?php echo isset($base_path) ? $base_path : '../'; ?>assets/images/land_icon.svg"
                                    alt="Land Icon" class="gardener-request-icon-img" width="44" height="44">
                            </div>
                            <div class="min-w-0">
                                <span class="text-secondary fw-bold text-uppercase d-block"
                                    style="font-size: 10px; letter-spacing:0.4px;">TARGET LAND</span>
                                <h6 id="apply_land_title" class="fw-bold text-dark mb-0 text-truncate">Land Title</h6>
                                <span id="apply_landowner" class="text-muted d-block small"
                                    style="font-size: 11px;"></span>
                            </div>
                        </div>
                        <div class="text-end flex-shrink-0 ms-2">
                            <span class="text-secondary fw-bold text-uppercase d-block"
                                style="font-size: 10px; letter-spacing:0.4px;">SELECTED PLOT</span>
                            <span id="apply_plot_details" class="badge rounded-pill px-2.5 py-1 text-xs fw-bold"
                                style="background:#DCFCE7;color:#15803D;border:1px solid #BBF7D0;">
                                Plot
                            </span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="apply_duration" class="form-label text-secondary fw-semibold"
                            style="font-size: 0.75rem; letter-spacing: 0.3px;">LEASE DURATION</label>
                        <select class="form-select drive-form-control w-100 rounded-3" id="apply_duration"
                            name="duration" required>
                            <option value="3 Months">3 Months</option>
                            <option value="6 Months" selected>6 Months</option>
                            <option value="1 Year">1 Year</option>
                            <option value="Indefinite">Indefinite</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label for="apply_purpose" class="form-label text-secondary fw-semibold"
                            style="font-size: 0.75rem; letter-spacing: 0.3px;">PURPOSE &amp; GARDENING INTENT</label>
                        <textarea class="form-control drive-form-control w-100 rounded-3" id="apply_purpose"
                            name="purpose" rows="3" required
                            placeholder="Describe your gardening plans (e.g. vegetable patches, herbs, organic composting)..."></textarea>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-1">
                        <button type="button" class="btn btn-drive-secondary rounded-pill px-3.5"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-drive-primary rounded-pill px-4 fw-semibold">Submit
                            Application</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Details/Response Modal (Single dynamic modal instance matching Landowner Request Details Modal) -->
<div class="modal fade" id="viewResponseModal" tabindex="-1" aria-hidden="true" style="z-index: 9999;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content drive-modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="request-avatar bg-success-subtle text-success" id="modal_avatar">LD</div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark m-0" id="modal_request_title">Request Details</h5>
                        <small class="text-muted" id="modal_request_date"></small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <!-- Landowner & Land info card -->
                <div class="p-3 bg-light rounded-3 mb-3 border border-light-subtle">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <strong class="text-dark" id="modal_land_title"></strong>
                        <span id="modal_status_badge"></span>
                    </div>
                    <div class="text-muted small d-flex flex-wrap gap-3 mt-1" id="modal_landowner_contact">
                        <!-- landowner and address links -->
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-6">
                        <div class="p-2.5 bg-light rounded-3 border border-light-subtle">
                            <label class="text-secondary fw-semibold d-block mb-1" style="font-size: 11px;">TARGET PLOT</label>
                            <div class="text-dark fw-bold small" id="modal_plot"></div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-2.5 bg-light rounded-3 border border-light-subtle">
                            <label class="text-secondary fw-semibold d-block mb-1" style="font-size: 11px;">REQUESTED TERM</label>
                            <div class="text-dark fw-bold small" id="modal_duration"></div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="text-secondary fw-semibold d-block mb-1" style="font-size: 11px;">GARDENING PURPOSE / PROPOSAL</label>
                    <p class="text-dark bg-light p-3 rounded-3 mb-0 border border-light-subtle" style="font-size: 0.85rem; line-height: 1.5;" id="modal_purpose"></p>
                </div>

                <!-- Incoming Rental Offer Section (Panel Feedback: Gardener decides whether to approve offer) -->
                <div class="mb-3 d-none" id="modal_offer_section">
                    <div class="p-3 rounded-4 border" style="background:#FFFDF7;border-color:#FCD34D !important;">
                        <div class="d-flex align-items-center justify-content-between mb-1.5">
                            <span class="badge rounded-pill px-2.5 py-1 text-xs fw-bold"
                                style="background:#FEF3C7;color:#92400E;border:1px solid #FCD34D;">
                                <i class="bi bi-tag-fill me-1 text-warning"></i> Incoming Rental Offer
                            </span>
                            <span class="text-muted small" style="font-size:11px;" id="modal_offer_time"></span>
                        </div>
                        <div class="d-flex align-items-baseline gap-2 mb-1">
                            <span class="fs-4 fw-bolder text-dark" id="modal_offer_rate">₱9,166.67</span>
                            <span class="text-secondary small fw-medium" id="modal_offer_duration">/ Month</span>
                            <span class="text-muted small" id="modal_offer_annual">(₱110,000.00/yr)</span>
                        </div>
                        <div class="small text-secondary p-2.5 rounded-3 bg-white border border-light-subtle"
                            id="modal_offer_terms">
                            Standard community gardening lease. Organic vegetables, composting, and water access included.
                        </div>

                        <!-- Expandable Gardener Counter-Offer Form -->
                        <div class="mt-3 pt-3 border-top d-none" id="modal_counter_form_container">
                            <h6 class="fw-bold text-dark text-xs text-uppercase mb-2 d-flex align-items-center gap-1.5">
                                <i class="bi bi-pencil-square text-primary"></i> Propose Rental Counter-Offer
                            </h6>
                            <div class="mb-2.5">
                                <label class="form-label text-secondary fw-semibold text-xs mb-1">PROPOSED MONTHLY FEE (₱ / MONTH)</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light fw-bold text-dark">₱</span>
                                    <input type="number" step="50" min="100" class="form-control fw-bold text-dark"
                                        id="modal_counter_rate_input" placeholder="e.g. 8000"
                                        oninput="updateCounterMonthlyCalc()">
                                    <span class="input-group-text bg-light text-muted" id="modal_counter_monthly_calc">/ mo (Annual: ~₱96,000/yr)</span>
                                </div>
                            </div>
                            <div class="mb-2.5">
                                <label class="form-label text-secondary fw-semibold text-xs mb-1">PROPOSAL NOTES / TERMS (OPTIONAL)</label>
                                <textarea class="form-control form-control-sm text-xs" id="modal_counter_notes_input"
                                    rows="2"
                                    placeholder="Explain your proposed rate or rental payment preferences..."></textarea>
                            </div>
                            <div class="d-flex align-items-center justify-content-end gap-2">
                                <button type="button" class="btn btn-sm btn-light rounded-pill px-3 py-1 text-xs"
                                    onclick="toggleCounterOfferForm(false)">Cancel</button>
                                <button type="button"
                                    class="btn btn-sm btn-primary rounded-pill px-3.5 py-1.5 text-xs fw-semibold"
                                    id="btnSubmitCounterOffer" onclick="submitCurrentCounterOffer()">
                                    <i class="bi bi-send-fill me-1"></i> Send Counter-Offer
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Existing Counter-Offer Status Banner (When already counter_offered) -->
                <div class="mb-3 d-none" id="modal_counter_offered_section">
                    <div class="p-3 rounded-4 border" style="background:#F0FDF4;border-color:#BBF7D0 !important;">
                        <div class="d-flex align-items-center justify-content-between mb-1.5">
                            <span class="badge rounded-pill px-2.5 py-1 text-xs fw-bold"
                                style="background:#DCFCE7;color:#15803D;border:1px solid #BBF7D0;">
                                <i class="bi bi-arrow-left-right me-1 text-success"></i> Counter-Offer Submitted
                            </span>
                            <span class="text-muted small" style="font-size:11px;">Awaiting Landowner Response</span>
                        </div>
                        <div class="d-flex align-items-baseline gap-2 mb-1">
                            <span class="fs-4 fw-bolder text-dark" id="modal_counter_rate_display">₱7,916.67</span>
                            <span class="text-secondary small fw-medium">/ Month</span>
                            <span class="text-muted small" id="modal_counter_monthly_display">(₱95,000.00/yr)</span>
                        </div>
                        <div class="small text-secondary p-2.5 rounded-3 bg-white border border-light-subtle"
                            id="modal_counter_notes_display">
                            No notes attached.
                        </div>
                    </div>
                </div>

                <div class="mb-3 d-none" id="modal_feedback_section">
                    <label class="text-secondary fw-semibold d-block mb-1" style="font-size: 11px;">RESPONSE / FEEDBACK</label>
                    <div class="alert py-2 px-3 mb-0" style="font-size: 0.85rem; border-radius: 8px;" id="modal_feedback"></div>
                </div>

                <!-- Land Location Map inside Application Details -->
                <div class="mb-3 d-none" id="modal_map_section">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <label class="text-secondary fw-semibold mb-0" style="font-size: 11px;">LAND LOCATION</label>
                        <span class="text-muted small" id="modal_map_coords" style="font-size: 0.72rem;"></span>
                    </div>
                    <div class="rounded-4 overflow-hidden border mb-2" style="border-color:#DFCFC2 !important;">
                        <div id="modalDetailsLeafletMap" style="height: 180px; width: 100%; z-index: 1;"></div>
                    </div>
                    <div class="text-end">
                        <a id="modal_gmaps_link" href="#" target="_blank" rel="noopener"
                            class="btn btn-sm rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1.5 text-decoration-none"
                            style="background:#E8F3F8;color:#204E6E;border:1.5px solid #C4DCEB;font-size:0.75rem;">
                            <i class="bi bi-box-arrow-up-right"></i> Open in Google Maps
                        </a>
                    </div>
                </div>

                <!-- Modal footer actions matching landowner UI -->
                <div class="d-flex align-items-center justify-content-between pt-3 border-top flex-wrap gap-2">
                    <div id="modalOfferActionButtons" class="d-flex align-items-center gap-2 flex-wrap">
                        <!-- Dynamic Offer Actions (Approve, Counter, Decline) -->
                    </div>
                    <button type="button" class="btn btn-light rounded-pill px-4 ms-auto" data-bs-dismiss="modal">Close</button>
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

        const requestPlotModalEl = document.getElementById('requestPlotModal');
        if (requestPlotModalEl) {
            requestPlotModalEl.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                if (!button) return;

                const landId = button.getAttribute('data-land-id') || '';
                const landTitle = button.getAttribute('data-land-title') || 'Unknown Land';
                const plotId = button.getAttribute('data-plot-id') || '';
                const plotNumber = button.getAttribute('data-plot-number') || 'Plot';
                const plotArea = button.getAttribute('data-plot-area') || '0';
                const landowner = button.getAttribute('data-landowner') || '';

                const landIdInput = document.getElementById('apply_land_id');
                const plotIdInput = document.getElementById('apply_plot_id');
                const landTitleDisplay = document.getElementById('apply_land_title');
                const plotDetailsDisplay = document.getElementById('apply_plot_details');
                const landownerDisplay = document.getElementById('apply_landowner');

                if (landIdInput) landIdInput.value = landId;
                if (plotIdInput) plotIdInput.value = plotId;
                if (landTitleDisplay) landTitleDisplay.innerText = landTitle;
                if (plotDetailsDisplay) plotDetailsDisplay.innerText = `${plotNumber} (${plotArea} m²)`;
                if (landownerDisplay) landownerDisplay.innerText = landowner ? `Managed by ${landowner}` : '';

                const purposeInput = document.getElementById('apply_purpose');
                if (purposeInput) purposeInput.value = '';
                const durationSelect = document.getElementById('apply_duration');
                if (durationSelect) durationSelect.value = '6 Months';
            });
        }
    });

    // Programmatic trigger helper
    window.openApplyPlotModal = function (landId, landTitle, plotId, plotNumber, plotArea, landowner) {
        const landIdInput = document.getElementById('apply_land_id');
        const plotIdInput = document.getElementById('apply_plot_id');
        const landTitleDisplay = document.getElementById('apply_land_title');
        const plotDetailsDisplay = document.getElementById('apply_plot_details');
        const landownerDisplay = document.getElementById('apply_landowner');

        if (landIdInput) landIdInput.value = landId || '';
        if (plotIdInput) plotIdInput.value = plotId || '';
        if (landTitleDisplay) landTitleDisplay.innerText = landTitle || 'Unknown Land';
        if (plotDetailsDisplay) plotDetailsDisplay.innerText = `${plotNumber || 'Plot'} (${plotArea || '0'} m²)`;
        if (landownerDisplay) landownerDisplay.innerText = landowner ? `Managed by ${landowner}` : '';

        const modalEl = document.getElementById('requestPlotModal');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const bsModal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            bsModal.show();
        }
    };

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

<!-- Toast Feedback Container (Identical to Landowner Requests) -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 10500;">
    <div id="requestToast" class="toast align-items-center text-white bg-dark border-0 rounded-4 shadow-lg" role="alert"
        aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body py-2.5 px-3 d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill text-success" id="toastIcon"></i>
                <span id="toastMsg" class="small fw-medium">Action completed successfully.</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"
                aria-label="Close"></button>
        </div>
    </div>
</div>

<script src="<?php echo $base_path; ?>assets/js/gardener_requests.js?v=<?php echo time(); ?>"></script>