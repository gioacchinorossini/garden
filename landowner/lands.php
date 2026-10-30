<?php
$base_path = '../';
$page_title = "My Lands";
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>

<style>
/* Filter Buttons */
.land-filter-btn {
    border: 2px solid transparent !important;
    font-size: 0.78rem;
    font-weight: 700;
    padding: 6px 14px;
    border-radius: 99px !important;
    color: var(--drive-text-sub, #6B5446);
    background: transparent;
    transition: all 0.15s ease;
}
.land-filter-btn:hover {
    color: var(--drive-text-main, #3E2B1E);
    background: #F4EFE6;
}
.land-filter-btn.active {
    background: #7FA668 !important;
    color: #FFFDF9 !important;
    border: 2px solid #523E31 !important;
    box-shadow: 0 2px 0 #523E31 !important;
}
.land-filter-btn.active .badge {
    background: rgba(255, 255, 255, 0.3) !important;
    color: #ffffff !important;
}

/* Mobile only: show one land card at a time with arrows/swipe. Desktop remains the normal grid. */
@media (max-width: 767.98px) {
    .mobile-lands-carousel {
        position: relative;
        width: 100%;
        max-width: 450px;
        margin: 0 auto;
        padding: 0 8px 30px;
    }

    .mobile-land-carousel-viewport {
        width: 100%;
        overflow: hidden;
        touch-action: pan-y;
    }

    #mobileLandsCarousel #landsContainer {
        margin: 0 !important;
        display: block !important;
    }

    #mobileLandsCarousel #landsContainer > [class*="col-"] {
        width: 100% !important;
        max-width: 100% !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
        display: none;
        margin: 0 !important;
    }

    #mobileLandsCarousel #landsContainer > .mobile-land-active {
        display: block !important;
    }

    .mobile-land-nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        z-index: 20;
        width: 38px;
        height: 38px;
        border: 1px solid rgba(0,0,0,.08);
        border-radius: 50%;
        background: rgba(255,255,255,.96);
        color: #198754;
        box-shadow: 0 3px 12px rgba(0,0,0,.14);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
    }

    .mobile-land-nav:disabled {
        opacity: .35;
        pointer-events: none;
    }

    .mobile-land-prev { left: -2px; }
    .mobile-land-next { right: -2px; }

    .mobile-land-dots {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 4px;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 5px;
    }

    .mobile-land-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #d1d5db;
        transition: all .18s ease;
    }

    .mobile-land-dot.active {
        width: 18px;
        border-radius: 999px;
        background: #198754;
    }
}

@media (min-width: 768px) {
    .mobile-lands-carousel {
        position: static;
        width: 100%;
    }
    .mobile-land-nav, .mobile-land-dots {
        display: none !important;
    }
}
</style>

<main class="workspace-surface d-flex flex-column h-100 overflow-hidden">
    <!-- Desktop Toolbar -->
    <div class="toolbar border-bottom d-none d-md-flex justify-content-between align-items-center px-4 py-2.5 bg-white flex-shrink-0">
        <div style="min-width: 180px;">
            <h1 class="fs-5 fw-bold m-0 text-dark font-['Outfit']">My Lands</h1>
            <p class="text-secondary mb-0 small" style="font-size: 0.78rem;">Manage your registered properties & plots</p>
        </div>

        <!-- Filter Tabs: All, Pending, Approved centered at top -->
        <div class="d-flex justify-content-center flex-grow-1">
            <div class="btn-group btn-group-sm p-1 bg-light rounded-pill border shadow-xs" role="group">
                <button type="button" class="btn btn-sm rounded-pill land-filter-btn active" data-filter="all" id="filterAllBtn">
                    All <span class="badge rounded-pill bg-secondary ms-1" id="countAll">0</span>
                </button>
                <button type="button" class="btn btn-sm rounded-pill land-filter-btn text-secondary" data-filter="pending" id="filterPendingBtn">
                    Pending <span class="badge rounded-pill bg-warning text-dark ms-1" id="countPending">0</span>
                </button>
                <button type="button" class="btn btn-sm rounded-pill land-filter-btn text-secondary" data-filter="approved" id="filterApprovedBtn">
                    Approved <span class="badge rounded-pill bg-success ms-1" id="countApproved">0</span>
                </button>
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-end" style="min-width: 180px;">
            <a href="register.php"
                class="btn btn-drive-primary btn-sm px-3.5 d-flex align-items-center gap-2 rounded-pill shadow-xs">
                <i class="bi bi-plus-lg"></i>
                <span>Register Land</span>
            </a>
        </div>
    </div>

    <!-- Mobile Header Controls (Visible only on mobile < md) -->
    <div class="d-block d-md-none px-3 pt-3 pb-2 bg-white border-bottom flex-shrink-0">
        <div class="d-flex justify-content-between align-items-center mb-2.5">
            <div>
                <h1 class="fs-6 fw-bold m-0 text-dark font-['Outfit']">My Lands</h1>
                <p class="text-secondary mb-0" style="font-size: 0.72rem;">Manage properties & plots</p>
            </div>
            <a href="register.php"
                class="btn btn-drive-primary btn-sm px-3 py-1.5 d-flex align-items-center gap-1.5 rounded-pill text-xs shadow-xs">
                <i class="bi bi-plus-lg"></i>
                <span>Register</span>
            </a>
        </div>
        <!-- Mobile Filter Pills (Centered) -->
        <div class="d-flex justify-content-center">
            <div class="btn-group btn-group-sm p-1 bg-light rounded-pill border w-100" style="max-width: 340px;" role="group">
                <button type="button" class="btn btn-sm rounded-pill land-filter-btn active flex-fill" data-filter="all">
                    All <span class="badge rounded-pill bg-secondary ms-1" id="mCountAll">0</span>
                </button>
                <button type="button" class="btn btn-sm rounded-pill land-filter-btn text-secondary flex-fill" data-filter="pending">
                    Pending <span class="badge rounded-pill bg-warning text-dark ms-1" id="mCountPending">0</span>
                </button>
                <button type="button" class="btn btn-sm rounded-pill land-filter-btn text-secondary flex-fill" data-filter="approved">
                    Approved <span class="badge rounded-pill bg-success ms-1" id="mCountApproved">0</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Workspace Scrollable Area (Centered content) -->
    <div class="workspace-scroll flex-grow-1 px-3 px-md-4 pt-3 pb-4 d-flex flex-column justify-content-center">
        <div class="w-100 my-auto" style="max-width: 1240px; margin-left: auto; margin-right: auto;">
            <!-- Lands list: desktop stays a centered grid; mobile becomes a centered single-card carousel -->
            <div class="mobile-lands-carousel mx-auto" id="mobileLandsCarousel">
                <button type="button" class="mobile-land-nav mobile-land-prev" id="mobileLandPrev" aria-label="Previous land">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <div class="mobile-land-carousel-viewport">
                    <div class="row g-4 justify-content-center" id="landsContainer"></div>
                </div>
                <button type="button" class="mobile-land-nav mobile-land-next" id="mobileLandNext" aria-label="Next land">
                    <i class="bi bi-chevron-right"></i>
                </button>
                <div class="mobile-land-dots" id="mobileLandDots" aria-hidden="true"></div>
            </div>
        </div>
    </div>
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
$seedTypes = [
    ['value' => 'tomato', 'label' => 'Tomato', 'icon' => '../assets/crop-icons/tomato/tomato.svg'],
    ['value' => 'lettuce', 'label' => 'Lettuce', 'icon' => '../assets/crop-icons/romaine/romaine.svg'],
    ['value' => 'herbs', 'label' => 'Herbs', 'icon' => '../assets/crop-icons/basil/basil.svg'],
    ['value' => 'pepper', 'label' => 'Pepper', 'icon' => '../assets/crop-icons/red-bell-pepper/red-bell-pepper.svg'],
    ['value' => 'carrot', 'label' => 'Carrot', 'icon' => '../assets/crop-icons/carrot/carrot.svg'],
    ['value' => 'eggplant', 'label' => 'Eggplant', 'icon' => '../assets/crop-icons/eggplant/eggplant.svg'],
    ['value' => 'cucumber', 'label' => 'Cucumber', 'icon' => '../assets/crop-icons/cucumber/cucumber.svg'],
    ['value' => 'spinach', 'label' => 'Spinach', 'icon' => '../assets/crop-icons/spinach/spinach.svg'],
    ['value' => 'beans', 'label' => 'Beans', 'icon' => '../assets/crop-icons/broad-bean/broad-bean.svg'],
    ['value' => 'squash', 'label' => 'Squash', 'icon' => '../assets/crop-icons/yellow-squash/yellow-squash.svg'],
    ['value' => 'onion', 'label' => 'Onion', 'icon' => '../assets/crop-icons/red-onion/red-onion.svg'],
    ['value' => 'corn', 'label' => 'Corn', 'icon' => '../assets/crop-icons/corn/corn.svg'],
    ['value' => 'garlic', 'label' => 'Garlic', 'icon' => '../assets/crop-icons/garlic/garlic.svg'],
    ['value' => 'potato', 'label' => 'Potato', 'icon' => '../assets/crop-icons/russet-potato/russet-potato.svg'],
    ['value' => 'mushroom', 'label' => 'Mushroom', 'icon' => '../assets/crop-icons/generic-mushroom/generic-mushroom.svg'],
    ['value' => 'peas', 'label' => 'Peas', 'icon' => '../assets/crop-icons/snap-pea/snap-pea.svg'],
];
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

                            <!-- Allowed Seed Types / Permitted Crops -->
                            <div class="mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <label class="form-label text-secondary m-0"
                                        style="font-size:0.75rem;font-weight:600;">PERMITTED CROPS</label>
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
                                    <span class="text-muted text-xs italic" id="editEmptyCropsNotice">No specific crops
                                        selected (All crops permitted by default).</span>
                                </div>
                                <div id="editHiddenSeedInputs"></div>
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
                        Select Permitted Crops
                    </h5>
                    <p class="text-secondary mb-0" style="font-size: 0.75rem;">Choose the crop types gardeners are
                        allowed to cultivate on this land.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4 bg-light">
                <div class="d-flex flex-column flex-sm-row gap-3 align-items-center justify-content-between mb-4">
                    <div class="position-relative w-100 me-sm-2">
                        <i class="bi bi-search position-absolute text-secondary"
                            style="left: 12px; top: 50%; transform: translateY(-50%); font-size: 14px;"></i>
                        <input type="text" id="editCropSearchInput"
                            class="form-control drive-form-control ps-5 py-2 text-xs rounded-pill"
                            placeholder="Search crop types (e.g., Tomato, Lettuce, Carrot)..."
                            onkeyup="filterEditCropChips()">
                    </div>
                    <div class="d-flex gap-2 flex-shrink-0">
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 text-xs"
                            onclick="selectAllEditCrops(true)">Select All</button>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 text-xs"
                            onclick="selectAllEditCrops(false)">Clear All</button>
                    </div>
                </div>

                <div class="row g-2 overflow-y-auto" id="editModalCropGrid" style="max-height: 340px;">
                    <?php foreach ($seedTypes as $seed): ?>
                        <div class="col-6 col-sm-4 col-md-3 edit-crop-grid-item"
                            data-name="<?php echo strtolower($seed['label']); ?>">
                            <div class="edit-modal-crop-chip p-2.5 border rounded-3 bg-white d-flex align-items-center justify-content-between gap-2"
                                data-value="<?php echo $seed['value']; ?>" data-label="<?php echo $seed['label']; ?>"
                                data-icon="<?php echo $seed['icon']; ?>"
                                style="cursor: pointer; transition: all 0.15s; border-color: var(--drive-border) !important; user-select: none;"
                                onclick="toggleEditCropSelection('<?php echo $seed['value']; ?>', '<?php echo $seed['label']; ?>', '<?php echo $seed['icon']; ?>', this)">
                                <div class="d-flex align-items-center gap-2 text-truncate">
                                    <div class="w-8 h-8 rounded-circle d-flex align-items-center justify-content-center bg-light border flex-shrink-0"
                                        style="width:32px;height:32px;">
                                        <img src="<?php echo $seed['icon']; ?>" alt="<?php echo $seed['label']; ?>"
                                            style="width:20px;height:20px;object-fit:contain;">
                                    </div>
                                    <span
                                        class="fw-semibold text-dark text-xs truncate"><?php echo $seed['label']; ?></span>
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

        <!-- Edit Plot Crops Modal -->
        <div class="modal fade" id="editPlotCropsModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content drive-modal-content rounded-4 overflow-hidden border-0 shadow">
                    <div
                        class="modal-header border-bottom px-4 py-3 bg-white d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2"
                                style="font-size:1.05rem;">
                                <i class="bi bi-sprout text-success"></i> Permitted Crops for <span id="modalPlotTitle"
                                    class="text-primary"></span>
                            </h5>
                            <p class="text-muted mb-0" style="font-size:0.75rem;">Select which crop types gardeners are
                                allowed to plant in this specific plot grid.</p>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 bg-light">
                        <input type="hidden" id="edit_plot_id" value="">

                        <!-- Crop Grid Picker -->
                        <div class="row g-2 overflow-y-auto" style="max-height: 340px;" id="plotCropGridPicker">
                            <?php foreach ($seedTypes as $seed): ?>
                                <div class="col-6 col-sm-4 col-md-3">
                                    <div class="plot-crop-chip p-2.5 border rounded-3 bg-white d-flex align-items-center justify-content-between gap-2"
                                        data-value="<?php echo $seed['value']; ?>"
                                        data-label="<?php echo $seed['label']; ?>" data-icon="<?php echo $seed['icon']; ?>"
                                        style="cursor: pointer; transition: all 0.15s; border-color: var(--drive-border) !important; user-select: none;"
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
                        <span class="text-secondary text-xs fw-semibold"><span id="plotSelectedCropCount">0</span> crops
                            permitted for this plot</span>
                        <button type="button" class="btn btn-drive-primary rounded-pill px-4"
                            onclick="savePlotCrops()">Save Plot Crops</button>
                    </div>
                </div>
            </div>
        </div>

        <script src="<?php echo $base_path; ?>assets/js/landowner_lands.js"></script>
        <script>
            (function () {
                let mobileLandIndex = 0;
                let mobileLandItems = [];
                let touchStartX = 0;
                let touchStartY = 0;

                function setupMobileLandCarousel() {
                    if (window.innerWidth >= 768) return;

                    const container = document.getElementById('landsContainer');
                    const carousel = document.getElementById('mobileLandsCarousel');
                    const prev = document.getElementById('mobileLandPrev');
                    const next = document.getElementById('mobileLandNext');
                    const dots = document.getElementById('mobileLandDots');
                    if (!container || !carousel) return;

                    mobileLandItems = Array.from(container.children).filter(el => el.nodeType === 1);
                    if (!mobileLandItems.length) {
                        if (dots) dots.innerHTML = '';
                        return;
                    }

                    if (mobileLandIndex >= mobileLandItems.length) {
                        mobileLandIndex = mobileLandItems.length - 1;
                    }
                    if (mobileLandIndex < 0) mobileLandIndex = 0;

                    mobileLandItems.forEach((item, index) => {
                        item.classList.toggle('mobile-land-active', index === mobileLandIndex);
                    });

                    if (prev) prev.disabled = mobileLandIndex === 0;
                    if (next) next.disabled = mobileLandIndex === mobileLandItems.length - 1;

                    if (dots) {
                        dots.innerHTML = mobileLandItems.map((_, index) =>
                            `<span class="mobile-land-dot ${index === mobileLandIndex ? 'active' : ''}"></span>`
                        ).join('');
                    }
                }

                function goToMobileLand(index) {
                    if (!mobileLandItems.length) return;
                    mobileLandIndex = Math.max(0, Math.min(index, mobileLandItems.length - 1));
                    setupMobileLandCarousel();
                }

                document.addEventListener('DOMContentLoaded', function () {
                    const prev = document.getElementById('mobileLandPrev');
                    const next = document.getElementById('mobileLandNext');
                    const viewport = document.querySelector('.mobile-land-carousel-viewport');
                    const container = document.getElementById('landsContainer');

                    if (prev) prev.addEventListener('click', () => goToMobileLand(mobileLandIndex - 1));
                    if (next) next.addEventListener('click', () => goToMobileLand(mobileLandIndex + 1));

                    if (viewport) {
                        viewport.addEventListener('touchstart', function (e) {
                            if (!e.touches.length) return;
                            touchStartX = e.touches[0].clientX;
                            touchStartY = e.touches[0].clientY;
                        }, { passive: true });

                        viewport.addEventListener('touchend', function (e) {
                            if (!e.changedTouches.length) return;
                            const dx = e.changedTouches[0].clientX - touchStartX;
                            const dy = e.changedTouches[0].clientY - touchStartY;
                            if (Math.abs(dx) < 45 || Math.abs(dx) < Math.abs(dy)) return;
                            goToMobileLand(mobileLandIndex + (dx < 0 ? 1 : -1));
                        }, { passive: true });
                    }

                    // landowner_lands.js renders the cards asynchronously. Observe the
                    // container so the carousel activates as soon as the cards arrive.
                    if (container) {
                        const observer = new MutationObserver(function () {
                            setupMobileLandCarousel();
                        });
                        observer.observe(container, { childList: true });
                    }

                    setupMobileLandCarousel();
                    window.addEventListener('resize', setupMobileLandCarousel);
                });
            })();
        </script>

        <?php include '../includes/footer.php'; ?>