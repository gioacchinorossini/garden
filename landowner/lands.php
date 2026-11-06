<?php
$base_path = '../';
$page_title = "My Lands";
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>

<style>
/* Filter Buttons (Matching Requests Page) */
.land-filter-btn {
    border: 2px solid transparent !important;
    font-size: 0.78rem;
    font-weight: 700;
    padding: 5px 13px;
    border-radius: 9999px !important;
    border-top-left-radius: 9999px !important;
    border-top-right-radius: 9999px !important;
    border-bottom-left-radius: 9999px !important;
    border-bottom-right-radius: 9999px !important;
    color: var(--drive-text-sub);
    background: transparent;
    transition: all 0.15s ease;
    margin: 0 !important;
    overflow: visible !important;
}
.land-filter-btn:hover {
    color: var(--drive-text-main) !important;
    background: #EAE1D2 !important;
    border-color: transparent !important;
    border-radius: 9999px !important;
    border-top-left-radius: 9999px !important;
    border-top-right-radius: 9999px !important;
    border-bottom-left-radius: 9999px !important;
    border-bottom-right-radius: 9999px !important;
}
.land-filter-btn.active {
    background: #7FA668 !important;
    color: #FFFDF9 !important;
    border: 2px solid #523E31 !important;
    box-shadow: 0 2.5px 0 #523E31 !important;
    border-radius: 9999px !important;
}
.land-filter-btn.active:hover {
    background: #71975B !important;
    color: #FFFDF9 !important;
}
.land-filter-btn.active .badge {
    background: rgba(255, 255, 255, 0.3) !important;
    color: #ffffff !important;
}

.no-scrollbar::-webkit-scrollbar {
    display: none;
}
.no-scrollbar {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

/* Floating Plus Button (Register Land FAB) */
.floating-land-fab {
    position: fixed;
    bottom: 2rem;
    right: 2.25rem;
    width: 54px;
    height: 54px;
    background: #7FA668 !important;
    color: #FFFDF9 !important;
    border: 2px solid #523E31 !important;
    border-radius: 50% !important;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 4px 0 #523E31, 0 8px 24px rgba(82, 62, 49, 0.28);
    text-decoration: none !important;
    z-index: 1040;
    transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.2s ease, background-color 0.15s ease;
}

.floating-land-fab i {
    font-size: 1.5rem;
    line-height: 1;
    color: #FFFDF9;
    transition: transform 0.25s ease;
}

.floating-land-fab:hover {
    background: #6E9557 !important;
    transform: translateY(-3px) scale(1.06);
    box-shadow: 0 7px 0 #523E31, 0 14px 28px rgba(82, 62, 49, 0.35);
    color: #FFFDF9 !important;
}

.floating-land-fab:hover i {
    transform: rotate(90deg);
}

.floating-land-fab:active {
    transform: translateY(2px) scale(0.96);
    box-shadow: 0 1px 0 #523E31, 0 4px 10px rgba(82, 62, 49, 0.2);
}

/* Tooltip label appearing on hover */
.floating-land-fab .fab-tooltip {
    position: absolute;
    right: calc(100% + 12px);
    top: 50%;
    transform: translateY(-50%) scale(0.9);
    background: #523E31;
    color: #FFFDF9;
    padding: 6px 14px;
    border-radius: 99px;
    font-size: 0.8rem;
    font-weight: 700;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s ease, transform 0.2s ease;
    box-shadow: 0 4px 12px rgba(82, 62, 49, 0.2);
}

.floating-land-fab .fab-tooltip::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 100%;
    margin-top: -5px;
    border-width: 5px;
    border-style: solid;
    border-color: transparent transparent transparent #523E31;
}

.floating-land-fab:hover .fab-tooltip {
    opacity: 1;
    transform: translateY(-50%) scale(1);
}

@media (max-width: 767.98px) {
    .floating-land-fab {
        bottom: 5.5rem; /* Float above mobile bottom navigation dock */
        right: 1.25rem;
        width: 50px;
        height: 50px;
    }
    .floating-land-fab i {
        font-size: 1.35rem;
    }
    .floating-land-fab .fab-tooltip {
        display: none;
    }
}

/* Search Input Fix: Ensure placeholder never overlaps search icon */
#landsSearchInput,
#mobileSearchInput {
    padding-left: 2.4rem !important;
}

/* Crop Selection Chips */
.plot-crop-chip,
.edit-modal-crop-chip,
.edit-modal-amenity-chip {
    cursor: pointer;
    transition: all 0.15s ease;
    border: 2px solid var(--drive-border-subtle, #DFCFC2) !important;
    background: #FFFDF9 !important;
    border-radius: 14px;
}

.plot-crop-chip:hover,
.edit-modal-crop-chip:hover,
.edit-modal-amenity-chip:hover {
    background: #F4EFE6 !important;
    border-color: #7FA668 !important;
}

.plot-crop-chip.selected,
.edit-modal-crop-chip.selected,
.edit-modal-amenity-chip.selected {
    background: #EAF3E7 !important;
    border-color: #523E31 !important;
    box-shadow: 0 2.5px 0 #523E31 !important;
}

/* Land Listing Cards (Tactile 3D Shadow Matching Theme) */
.land-listing-card {
    background: #FFFDF9 !important;
    border: 2px solid #523E31 !important;
    border-radius: 20px !important;
    box-shadow: 0 4px 0 #523E31 !important;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}
.land-listing-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 0 #523E31 !important;
}
.land-listing-card.is-selected {
    border-color: #7FA668 !important;
    box-shadow: 0 0 0 3px #BBD6B8, 0 5px 0 #523E31 !important;
}

/* Ensure button text in cards is pure white */
a.btn-drive-primary,
a.btn-drive-primary:hover,
a.btn-drive-primary:focus,
a.btn-drive-primary:active,
a.btn-drive-primary:visited {
    color: #ffffff !important;
}
</style>

<main class="workspace-surface">
    <!-- Desktop Toolbar (Title and actions, borderless matching requests.php) -->
    <div class="toolbar d-none d-md-flex justify-content-between align-items-center">
        <div>
            <h1 class="fs-5 fw-semibold m-0 text-dark">My Lands</h1>
            <p class="text-muted mb-0" style="font-size: 0.78rem;">Manage your registered properties & plots</p>
        </div>

        <div class="d-flex align-items-center gap-3">
            <!-- Search bar -->
            <div class="position-relative" style="width: 240px;">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" style="font-size: 0.8rem; pointer-events: none;"></i>
                <input type="text" id="landsSearchInput" class="form-control form-control-sm ps-5 rounded-pill border-light-subtle bg-light"
                    placeholder="Search land, address...">
            </div>

            <!-- Filter tabs -->
            <div class="filter-pill-track" role="group">
                <button type="button" class="btn btn-sm rounded-pill land-filter-btn text-secondary" data-filter="approved" id="filterApprovedBtn">
                    Approved <span class="badge rounded-pill bg-success ms-1" id="countApproved">0</span>
                </button>
                <button type="button" class="btn btn-sm rounded-pill land-filter-btn text-secondary" data-filter="pending" id="filterPendingBtn">
                    Pending <span class="badge rounded-pill bg-warning text-dark ms-1" id="countPending">0</span>
                </button>
                <button type="button" class="btn btn-sm rounded-pill land-filter-btn text-secondary" data-filter="rejected" id="filterRejectedBtn">
                    Rejected <span class="badge rounded-pill bg-danger ms-1" id="countRejected">0</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Workspace Scrollable Area -->
    <div class="workspace-scroll p-3 px-md-4 pt-md-2 pb-5 pb-md-5">
        <!-- Mobile Header Controls (Visible only on mobile < md) -->
        <div class="d-block d-md-none mb-3">
            <!-- Mobile Search -->
            <div class="position-relative mb-2.5">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" style="font-size: 0.85rem; pointer-events: none;"></i>
                <input type="text" id="mobileSearchInput" class="form-control form-control-sm ps-5 py-2 rounded-pill border-light-subtle bg-white shadow-xs"
                    placeholder="Search lands, crops...">
            </div>

            <!-- Mobile Filter Pills (Horizontal Scroll) -->
            <div class="d-flex align-items-center gap-2 overflow-x-auto py-1.5 px-0.5 no-scrollbar">
                <button type="button" class="btn btn-sm rounded-pill land-filter-btn bg-white border flex-shrink-0 text-secondary" data-filter="approved">
                    Approved <span class="badge rounded-pill bg-success ms-1" id="mCountApproved">0</span>
                </button>
                <button type="button" class="btn btn-sm rounded-pill land-filter-btn bg-white border flex-shrink-0 text-secondary" data-filter="pending">
                    Pending <span class="badge rounded-pill bg-warning text-dark ms-1" id="mCountPending">0</span>
                </button>
                <button type="button" class="btn btn-sm rounded-pill land-filter-btn bg-white border flex-shrink-0 text-secondary" data-filter="rejected">
                    Rejected <span class="badge rounded-pill bg-danger ms-1" id="mCountRejected">0</span>
                </button>
            </div>
        </div>

        <!-- Lands Grid -->
        <div class="row g-4" id="landsContainer"></div>
    </div>

    <!-- Floating Action Button: Register Land -->
    <a href="register.php" class="floating-land-fab" id="registerLandFab" title="Register Land" aria-label="Register Land">
        <i class="bi bi-plus-lg"></i>
        <span class="fab-tooltip">Register Land</span>
    </a>
</main>

<!-- Manage Plots Modal -->
<div class="modal fade" id="managePlotsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content drive-modal-content">
            <div class="modal-header border-0 pb-0 d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="modal-title fw-bold text-dark m-0 d-flex align-items-center gap-2">
                        <i class="bi bi-grid-3x3-gap text-success"></i>
                        <span>Manage Partition Plots</span>
                    </h5>
                    <p class="text-muted mb-0 mt-1" style="font-size:0.82rem;">
                        Property: <span id="modalLandTitle" class="fw-bold text-dark"></span>
                    </p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button
                        class="btn btn-sm btn-drive-primary py-1.5 px-3 d-flex align-items-center gap-1 rounded-pill"
                        onclick="openAddPlotModal()">
                        <i class="bi bi-plus-lg"></i> Add Plot
                    </button>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body pt-3">
                <!-- Plots dynamic list inside modal -->
                <div id="modalPlotsContainer"></div>
            </div>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/crops_library.php';
require_once __DIR__ . '/../includes/lands_helper.php';
$seedTypes = getPermittedCropsSeedList('../');
$cropCategories = getCropCategories();
$amenityTypes = get_standard_amenities('../');
?>

<!-- Single Edit Land Details Modal -->
<div class="modal fade" id="editLandModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content drive-modal-content rounded-4 overflow-hidden border-0 shadow">
            <div
                class="modal-header border-bottom px-4 py-3 bg-white d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2"
                        style="font-size:1.05rem;">
                        <i class="bi bi-pencil-square text-primary"></i> Edit Land Details
                    </h5>
                    <p class="text-muted mb-0" style="font-size:0.75rem;">Modify property location, boundaries,
                        description, and crop permissions.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <form id="editLandForm">
                    <input type="hidden" id="edit_land_id" name="land_id">

                    <div class="row g-4">
                        <!-- Left: Form Controls -->
                        <div class="col-lg-7">
                            <div class="mb-3">
                                <label for="edit_title" class="form-label text-secondary"
                                    style="font-size:0.75rem;font-weight:600;">PROPERTY NAME</label>
                                <input type="text" class="form-control drive-form-control w-100" id="edit_title"
                                    name="title" required>
                            </div>

                            <div class="mb-3">
                                <label for="edit_address" class="form-label text-secondary"
                                    style="font-size:0.75rem;font-weight:600;">ADDRESS</label>
                                <input type="text" class="form-control drive-form-control w-100" id="edit_address"
                                    name="address" required>
                            </div>

                            <!-- Size & Coordinates -->
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="edit_area" class="form-label text-secondary"
                                        style="font-size:0.75rem;font-weight:600;">AREA (m²)</label>
                                    <input type="number" step="0.1" class="form-control drive-form-control w-100"
                                        id="edit_area" name="area" required placeholder="250">
                                </div>
                                <div class="col-md-4">
                                    <label for="edit_latitude" class="form-label text-secondary"
                                        style="font-size:0.75rem;font-weight:600;">LATITUDE</label>
                                    <input type="text" class="form-control drive-form-control w-100" id="edit_latitude"
                                        name="latitude" required readonly placeholder="Click map">
                                </div>
                                <div class="col-md-4">
                                    <label for="edit_longitude" class="form-label text-secondary"
                                        style="font-size:0.75rem;font-weight:600;">LONGITUDE</label>
                                    <input type="text" class="form-control drive-form-control w-100" id="edit_longitude"
                                        name="longitude" required readonly placeholder="Click map">
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="mb-3">
                                <label for="edit_description" class="form-label text-secondary"
                                    style="font-size:0.75rem;font-weight:600;">DESCRIPTION</label>
                                <textarea class="form-control drive-form-control w-100" id="edit_description"
                                    name="description" rows="3"></textarea>
                            </div>

                            <!-- Crops That Can Grow -->
                            <div class="mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <label class="form-label text-secondary m-0"
                                        style="font-size:0.75rem;font-weight:600;">CROPS</label>
                                    <button type="button"
                                        class="btn btn-sm btn-drive-secondary rounded-pill px-3 py-1 d-flex align-items-center gap-1.5"
                                        data-bs-toggle="modal" data-bs-target="#editPermittedCropsModal"
                                        style="font-size:0.75rem;">
                                        <i class="bi bi-search" style="font-size:12px;"></i>
                                        <span>Select Crops</span>
                                    </button>
                                </div>

                                <div id="editSelectedCropsSummary"
                                    class="p-3 border rounded-4 bg-white d-flex flex-wrap gap-2 align-items-center"
                                    style="min-height:52px;border-color:var(--drive-border) !important;">
                                    <span class="text-muted text-xs italic" id="editEmptyCropsNotice">No specific crops selected.</span>
                                </div>
                                <div id="editHiddenSeedInputs"></div>
                            </div>

                            <!-- Available Amenities & Utilities in Edit Modal -->
                            <div class="mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <label class="form-label text-secondary m-0"
                                        style="font-size:0.75rem;font-weight:600;">AVAILABLE AMENITIES &amp; UTILITIES</label>
                                    <button type="button"
                                        class="btn btn-sm btn-drive-secondary rounded-pill px-3 py-1 d-flex align-items-center gap-1.5"
                                        data-bs-toggle="modal" data-bs-target="#editAmenitiesModal"
                                        style="font-size:0.75rem;">
                                        <i class="bi bi-search" style="font-size:12px;"></i>
                                        <span>Select Amenities</span>
                                    </button>
                                </div>

                                <div id="editSelectedAmenitiesSummary"
                                    class="p-3 border rounded-4 bg-white d-flex flex-wrap gap-2 align-items-center"
                                    style="min-height:52px;border-color:var(--drive-border) !important;">
                                    <span class="text-muted text-xs italic" id="editEmptyAmenitiesNotice">No amenities selected.</span>
                                </div>
                                <div id="editHiddenAmenitiesInputs"></div>
                            </div>
                        </div>

                        <!-- Right: Location Picker Map -->
                        <div class="col-lg-5">
                            <div class="card border rounded-4 overflow-hidden h-100 shadow-xs"
                                style="border-color:var(--drive-border) !important;">
                                <div
                                    class="card-header bg-white border-bottom py-2 d-flex align-items-center justify-content-between">
                                    <span class="fw-semibold text-secondary"
                                        style="font-size:0.75rem;letter-spacing:0.5px;text-transform:uppercase;">Location
                                        Map Picker</span>
                                    <i class="bi bi-geo-alt-fill text-primary"></i>
                                </div>
                                <div id="editPickerMap" style="height: 380px; background-color: #e9f2ff;"></div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <button type="button" class="btn btn-drive-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-drive-primary px-4">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Permitted Crops Selection Modal -->
<div class="modal fade" id="editPermittedCropsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content drive-modal-content rounded-4 overflow-hidden border-0 shadow">
            <div class="modal-header border-bottom px-4 py-3 bg-white">
                <div>
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" style="font-size: 1rem;">
                        <img src="../assets/crop-icons/generic-plant/generic-plant.svg" class="w-6 h-6 object-contain"
                            alt="Crops" style="width:20px;height:20px;">
                        Select Crops
                    </h5>
                    <p class="text-secondary mb-0" style="font-size: 0.75rem;">Choose the crops and plants for this land.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4 bg-light">
                <div class="d-flex flex-column flex-sm-row gap-3 align-items-center justify-content-between mb-3">
                    <div class="position-relative w-100 me-sm-2">
                        <i class="bi bi-search position-absolute text-secondary"
                            style="left: 12px; top: 50%; transform: translateY(-50%); font-size: 14px;"></i>
                        <input type="text" id="editCropSearchInput"
                            class="form-control drive-form-control ps-5 py-2 text-xs rounded-pill"
                            placeholder="Search by English or local name (e.g. Mango, Mangga, Pechay)..."
                            onkeyup="filterEditCropChips()">
                    </div>
                    <div class="d-flex gap-1.5 flex-shrink-0">
                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 text-xs"
                            onclick="selectAllEditCrops(true)">Select All</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 text-xs"
                            onclick="selectAllEditCrops(false)">Clear All</button>
                    </div>
                </div>

                <!-- Category Filters Scrollable Pills -->
                <div class="d-flex gap-1.5 overflow-x-auto pb-2 mb-3" id="editCropCategoryFilters" style="white-space: nowrap;">
                    <button type="button" class="btn btn-xs btn-primary rounded-pill px-2.5 py-1 text-xs edit-crop-cat-pill active flex-shrink-0"
                        onclick="setEditCropCategoryFilter('all', this)">All (<?php echo count($seedTypes); ?>)</button>
                    <?php foreach ($cropCategories as $catId => $cat): ?>
                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 text-xs edit-crop-cat-pill flex-shrink-0"
                            onclick="setEditCropCategoryFilter('<?php echo $catId; ?>', this)">
                            <span><?php echo $cat['icon']; ?> <?php echo $cat['name']; ?></span>
                            <span class="badge bg-light text-dark ms-1 rounded-pill" style="font-size: 10px;"><?php echo $cat['count']; ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>

                <div class="row g-2 overflow-y-auto" id="editModalCropGrid" style="max-height: 340px;">
                    <?php foreach ($seedTypes as $seed): ?>
                        <div class="col-6 col-sm-4 col-md-3 edit-crop-grid-item"
                            data-name="<?php echo strtolower($seed['label'] . ' ' . $seed['value'] . ' ' . ($seed['local_name'] ?? '')); ?>"
                            data-category="<?php echo $seed['category_id'] ?? ''; ?>">
                            <div class="edit-modal-crop-chip p-2.5 d-flex align-items-center justify-content-between gap-2"
                                data-value="<?php echo $seed['value']; ?>" data-label="<?php echo $seed['label']; ?>"
                                data-icon="<?php echo $seed['icon']; ?>"
                                style="user-select: none;"
                                onclick="toggleEditCropSelection('<?php echo $seed['value']; ?>', '<?php echo $seed['label']; ?>', '<?php echo $seed['icon']; ?>', this)">
                                <div class="d-flex align-items-center gap-2 text-truncate">
                                    <div class="w-8 h-8 rounded-circle d-flex align-items-center justify-content-center bg-light border flex-shrink-0"
                                        style="width:32px;height:32px;">
                                        <img src="<?php echo $seed['icon']; ?>" alt="<?php echo $seed['label']; ?>"
                                            style="width:20px;height:20px;object-fit:contain;" loading="lazy">
                                    </div>
                                    <span
                                        class="fw-semibold text-dark text-xs truncate" title="<?php echo $seed['label']; ?>"><?php echo $seed['label']; ?></span>
                                </div>
                                <div class="edit-crop-check-icon text-primary d-none">
                                    <i class="bi bi-check-circle-fill text-primary" style="font-size: 14px;"></i>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="modal-footer border-top bg-white px-4 py-3 d-flex justify-content-between align-items-center">
                <span class="text-secondary text-xs fw-semibold"><span id="editSelectedCountText">0</span> crops
                    selected</span>
                <button type="button" class="btn btn-drive-primary rounded-pill px-4"
                    data-bs-dismiss="modal">Done</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Available Amenities Modal -->
<div class="modal fade" id="editAmenitiesModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content drive-modal-content rounded-4 overflow-hidden border-0 shadow">
            <div class="modal-header border-bottom px-4 py-3 bg-white">
                <div>
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" style="font-size: 1rem;">
                        <img src="../assets/amenity-icons/general.svg" class="w-6 h-6 object-contain"
                            alt="Amenities" style="width:20px;height:20px;">
                        Select Amenities &amp; Utilities
                    </h5>
                    <p class="text-secondary mb-0" style="font-size: 0.75rem;">Choose the utilities and infrastructure available for gardeners on this land.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4 bg-light">
                <div class="d-flex flex-column flex-sm-row gap-3 align-items-center justify-content-between mb-3">
                    <div class="position-relative w-100 me-sm-2">
                        <i class="bi bi-search position-absolute text-secondary"
                            style="left: 12px; top: 50%; transform: translateY(-50%); font-size: 14px;"></i>
                        <input type="text" id="editAmenitySearchInput"
                            class="form-control drive-form-control ps-5 py-2 text-xs rounded-pill"
                            placeholder="Search amenities (e.g. Water, Electricity, Irrigation, Storage, Fencing)..."
                            onkeyup="filterEditAmenityChips()">
                    </div>
                    <div class="d-flex gap-1.5 flex-shrink-0">
                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 text-xs"
                            onclick="selectAllEditAmenities(true)">Select All</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 text-xs"
                            onclick="selectAllEditAmenities(false)">Clear All</button>
                    </div>
                </div>

                <!-- Custom Amenity Quick Adder -->
                <div class="d-flex align-items-center gap-2 mb-3 bg-white p-2.5 border rounded-3" style="border-color: var(--drive-border) !important;">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i class="bi bi-plus-circle text-success"></i>
                        </span>
                        <input type="text" id="editModalCustomAmenityInput" class="form-control drive-form-control border-start-0 text-xs"
                            placeholder="Add other amenity (e.g. CCTV, Deep Well, Solar Power, Greenhouse)..."
                            onkeydown="if(event.key==='Enter'){event.preventDefault();addEditCustomAmenityFromModal();}">
                        <button type="button" class="btn btn-drive-secondary px-3 text-xs fw-semibold" onclick="addEditCustomAmenityFromModal()">
                            Add Amenity
                        </button>
                    </div>
                </div>

                <div class="row g-2 overflow-y-auto" id="editModalAmenityGrid" style="max-height: 340px;">
                    <?php foreach ($amenityTypes as $amenity): ?>
                        <div class="col-6 col-sm-4 col-md-3 edit-amenity-grid-item"
                            data-name="<?php echo strtolower($amenity['label'] . ' ' . ($amenity['desc'] ?? '')); ?>">
                            <div class="edit-modal-amenity-chip p-2.5 d-flex align-items-center justify-content-between gap-2"
                                data-value="<?php echo htmlspecialchars($amenity['value']); ?>"
                                data-label="<?php echo htmlspecialchars($amenity['label']); ?>"
                                data-icon="<?php echo htmlspecialchars($amenity['icon']); ?>"
                                style="user-select: none;"
                                onclick="toggleEditAmenitySelection('<?php echo htmlspecialchars(addslashes($amenity['value'])); ?>', '<?php echo htmlspecialchars(addslashes($amenity['label'])); ?>', '<?php echo htmlspecialchars($amenity['icon']); ?>', this)">
                                <div class="d-flex align-items-center gap-2 text-truncate">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-light border flex-shrink-0"
                                        style="width:32px;height:32px;">
                                        <img src="<?php echo $amenity['icon']; ?>" alt="<?php echo htmlspecialchars($amenity['label']); ?>"
                                            style="width:20px;height:20px;object-fit:contain;" loading="lazy">
                                    </div>
                                    <span class="fw-semibold text-dark text-xs truncate"
                                        title="<?php echo htmlspecialchars($amenity['label']); ?>"><?php echo htmlspecialchars($amenity['label']); ?></span>
                                </div>
                                <div class="edit-amenity-check-icon text-primary d-none">
                                    <i class="bi bi-check-circle-fill text-primary" style="font-size: 14px;"></i>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="modal-footer border-top bg-white px-4 py-3 d-flex justify-content-between align-items-center">
                <span class="text-secondary text-xs fw-semibold"><span id="editSelectedAmenitiesCountText">0</span> amenities selected</span>
                <button type="button" class="btn btn-drive-primary rounded-pill px-4"
                    data-bs-dismiss="modal">Done</button>
            </div>
        </div>
    </div>
</div>

<!-- Add Plot Modal (Single instance) -->
<div class="modal fade" id="addPlotModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content drive-modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold text-dark">Add New Plot</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addPlotForm">
                    <input type="hidden" name="land_id" id="modalLandId" value="">

                    <div class="mb-3">
                        <label for="plot_number" class="form-label text-secondary"
                            style="font-size: 0.75rem; font-weight:600;">PLOT NUMBER</label>
                        <input type="text" class="form-control drive-form-control w-100" id="plot_number"
                            name="plot_number" required placeholder="Plot C-1">
                    </div>
                    <div class="mb-4">
                        <label for="plot_area" class="form-label text-secondary"
                            style="font-size: 0.75rem; font-weight:600;">AREA (m²)</label>
                        <input type="number" step="0.1" class="form-control drive-form-control w-100" id="plot_area"
                            name="plot_area" required placeholder="25.0">
                    </div>

                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-drive-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-drive-primary">Create Plot</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Edit Plot Crops Modal (Set Permitted Crops) -->
<div class="modal fade" id="editPlotCropsModal" tabindex="-1" aria-hidden="true" style="z-index: 1065;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content drive-modal-content rounded-4 overflow-hidden border-0 shadow">
            <div
                class="modal-header border-bottom px-4 py-3 bg-white d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2"
                        style="font-size:1.05rem;">
                        <i class="bi bi-sprout text-success"></i> Crops for <span id="modalPlotTitle"
                            class="text-primary"></span>
                    </h5>
                    <p class="text-muted mb-0" style="font-size:0.75rem;">Select crops for this plot.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <input type="hidden" id="edit_plot_id" value="">

                <!-- Crop Grid Picker -->
                <div class="row g-2 overflow-y-auto" style="max-height: 340px;" id="plotCropGridPicker">
                    <?php foreach ($seedTypes as $seed): ?>
                        <div class="col-6 col-sm-4 col-md-3">
                            <div class="plot-crop-chip p-2.5 rounded-3 d-flex align-items-center justify-content-between gap-2"
                                data-value="<?php echo $seed['value']; ?>"
                                data-label="<?php echo $seed['label']; ?>" data-icon="<?php echo $seed['icon']; ?>"
                                style="user-select: none;"
                                onclick="togglePlotCropSelection('<?php echo $seed['value']; ?>', '<?php echo $seed['label']; ?>', '<?php echo $seed['icon']; ?>', this)">
                                <div class="d-flex align-items-center gap-2 text-truncate">
                                    <div class="w-8 h-8 rounded-circle d-flex align-items-center justify-content-center bg-light border flex-shrink-0"
                                        style="width:32px;height:32px;">
                                        <img src="<?php echo $seed['icon']; ?>" alt="<?php echo $seed['label']; ?>"
                                            style="width:20px;height:20px;object-fit:contain;">
                                    </div>
                                    <span
                                        class="fw-semibold text-dark text-xs truncate"><?php echo $seed['label']; ?></span>
                                </div>
                                <div class="plot-crop-check-icon text-primary d-none">
                                    <i class="bi bi-check-circle-fill text-primary" style="font-size: 14px;"></i>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <div
                class="modal-footer border-top bg-white px-4 py-3 d-flex justify-content-between align-items-center">
                <span class="text-secondary text-xs fw-semibold"><span id="plotSelectedCropCount">0</span> crops selected for this plot</span>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-drive-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-drive-primary rounded-pill px-4"
                        onclick="savePlotCrops()">Save Plot Crops</button>
                </div>
            </div>
        </div>
    </div>
</div>

        <script src="<?php echo $base_path; ?>assets/js/landowner_lands.js"></script>


        <?php include '../includes/footer.php'; ?>