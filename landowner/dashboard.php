<?php
$base_path = '../';
$page_title = "Gardens";
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';

if (session_status() == PHP_SESSION_NONE)
    session_start();
$_SESSION['active_role'] = 'landowner';
if (!isset($_SESSION['user_name']))
    $_SESSION['user_name'] = 'John Landowner';

require_once '../includes/db.php';
require_once '../includes/lands_helper.php';

$lands_data = get_all_lands($pdo);
$plots_data = get_all_plots($pdo);

foreach ($lands_data as &$land) {
    $lp = array_filter($plots_data, fn($p) => $p['land_id'] == $land['id']);
    $land['total_plots'] = count($lp);
    $land['occupied_plots'] = count(array_filter($lp, fn($p) => $p['status'] === 'occupied'));
    $land['available_plots'] = count(array_filter($lp, fn($p) => $p['status'] === 'available'));
}
unset($land);

$approved_count = count(array_filter($lands_data, fn($l) => $l['status'] === 'approved'));
$pending_count = count(array_filter($lands_data, fn($l) => $l['status'] === 'pending'));
$my_count = count(array_filter($lands_data, fn($l) => ($l['landowner'] ?? '') === 'John Landowner'));
?>

<style>
    /* Desktop Full-Screen Map Setup */
    @media (min-width: 769px) {
        .mobile-map-chips-bar {
            top: 64px !important;
            left: 13rem !important;
            padding-left: 18px !important;
            transition: left 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body:has(#mainSidebar.sidebar-collapsed) .mobile-map-chips-bar {
            left: 4.25rem !important;
        }

        .leaflet-top.leaflet-right {
            top: 64px !important;
        }

        .map-top-gradient-scrim {
            display: none !important;
        }
    }

    /* Mobile edge-to-edge view */
    @media (max-width: 768px) {
        header {
            display: none !important;
        }

        body {
            overflow: hidden !important;
        }

        .flex.flex-1 {
            padding: 0 !important;
            height: 100vh !important;
        }

        .workspace-surface.landowner-map-page {
            height: 100vh !important;
            border-radius: 0 !important;
            border: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .floating-map-header {
            z-index: 1050 !important;
        }

        .mobile-map-chips-bar {
            top: 74px !important;
            z-index: 1000 !important;
        }

        .leaflet-top.leaflet-right {
            top: 124px !important;
        }
    }
</style>
<script>
    document.body.classList.add('has-fullscreen-map');
</script>

<main class="workspace-surface landowner-map-page d-flex flex-column overflow-hidden h-100">

    <!-- Header Search Bar Template (injected into #headerSearchSlot on desktop) -->
    <template id="landownerHeaderSearchTemplate">
        <div class="position-relative w-100" id="landownerUnifiedSearchWrapper" style="max-width:520px;">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-end-0 rounded-start-pill ps-3 text-muted">
                    <i class="bi bi-search" style="font-size:0.75rem;"></i>
                </span>
                <input type="text" id="landownerDesktopPlaceInput"
                    class="form-control bg-light border-start-0 border-end-0 py-1.5 focus-ring-none"
                    style="font-size:0.75rem;" placeholder="Search my lands, crops, places, or addresses..."
                    autocomplete="off" oninput="handleLandownerUnifiedSearch(this.value)"
                    onkeydown="if(event.key==='Enter'){event.preventDefault();handleLandownerUnifiedSearchEnter(this.value);}">
                <button type="button" id="landownerPlaceClearBtn"
                    class="input-group-text bg-light border-start-0 border-0 text-muted d-none"
                    onclick="clearLandownerUnifiedSearch()" style="cursor:pointer;" title="Clear">
                    <i class="bi bi-x-circle-fill" style="font-size:0.75rem;"></i>
                </button>
                <button type="button"
                    class="input-group-text bg-light border-start-0 rounded-end-pill pe-3 text-success border-0"
                    onclick="useMyLocationLandowner()" title="Use my GPS location" style="cursor:pointer;">
                    <i class="bi bi-crosshair" style="font-size:0.8rem;"></i>
                </button>
            </div>
            <!-- Unified Suggestions Dropdown -->
            <div id="landownerDesktopPlaceResults"
                class="position-absolute start-0 end-0 mt-1 rounded-3 border bg-white shadow-lg"
                style="display:none; z-index:2100; max-height:380px; overflow-y:auto; font-size:0.82rem; top:100%;">
            </div>
        </div>
    </template>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const slot = document.getElementById('headerSearchSlot');
            const tpl = document.getElementById('landownerHeaderSearchTemplate');
            if (slot && tpl) {
                slot.appendChild(tpl.content.cloneNode(true));
            }
        });
    </script>

    <!-- Map Container -->
    <div class="flex-grow-1 position-relative overflow-hidden w-100 h-100" style="min-height:0;">
        <div class="landowner-map-wrapper">

            <!-- Top Black Gradient Scrim Overlay -->
            <div class="map-top-gradient-scrim"></div>

            <!-- Bottom Black Gradient Scrim Overlay -->
            <div class="map-bottom-gradient-scrim"></div>

            <!-- Floating Search Bar & Profile Header (Mobile Only) -->
            <div class="floating-map-header d-md-none">
                <a href="<?php echo $base_path; ?>landowner/dashboard.php"
                    class="d-flex align-items-center gap-1.5 text-decoration-none flex-shrink-0">
                    <img src="<?php echo $base_path; ?>logo.jpeg" alt="IdleLand Logo" class="rounded-circle shadow-sm"
                        style="width:28px;height:28px;object-fit:cover;">
                    <span class="fw-bold text-dark font-['Outfit']" style="font-size:0.88rem;line-height:1;">
                        <span class="text-success">Idle</span>Land
                    </span>
                </a>
                <div class="vr mx-1 my-auto text-muted opacity-25" style="height:20px;"></div>
                <i class="bi bi-search text-muted fs-6 ms-0.5"></i>
                <input type="text" id="mobileMapSearchInput" class="floating-map-search-input"
                    placeholder="Search lands, crops, places..." oninput="handleMobileMapSearch(this.value)"
                    onkeydown="if(event.key==='Enter'){event.preventDefault();handleMobileMapSearchEnter(this.value);}"
                    autocomplete="off">
                <button type="button" id="mobileMapSearchClear" class="floating-map-search-clear"
                    onclick="clearMobileMapSearch()">
                    <i class="bi bi-x-circle-fill"></i>
                </button>
                <div class="dropdown flex-shrink-0">
                    <button class="floating-profile-btn" type="button" id="mobileProfileDropdown"
                        data-bs-toggle="dropdown" aria-expanded="false" title="Account & Profile">
                        <?php echo strtoupper(substr($_SESSION['user_name'] ?? 'L', 0, 1)); ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end border border-drive-border rounded-4 shadow-lg p-2 mt-2"
                        aria-labelledby="mobileProfileDropdown" style="min-width: 200px; z-index: 1060;">
                        <li>
                            <div class="px-3 py-2 border-bottom mb-1">
                                <div class="fw-bold text-dark text-sm">
                                    <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Landowner'); ?>
                                </div>
                                <span class="badge bg-success-subtle text-success text-xs">Landowner</span>
                            </div>
                        </li>
                        <li><a class="dropdown-item rounded-3 py-2 px-3 text-sm d-flex align-items-center gap-2"
                                href="<?php echo $base_path; ?>landowner/profile.php">
                                <i class="bi bi-person text-success"></i> My Profile
                            </a></li>
                        <li><a class="dropdown-item rounded-3 py-2 px-3 text-sm d-flex align-items-center gap-2"
                                href="<?php echo $base_path; ?>landowner/register.php">
                                <i class="bi bi-plus-circle text-primary"></i> Register Land
                            </a></li>
                        <li>
                            <hr class="dropdown-divider my-1">
                        </li>
                        <li><a class="dropdown-item rounded-3 py-2 px-3 text-sm text-danger d-flex align-items-center gap-2"
                                href="<?php echo $base_path; ?>index.php">
                                <i class="bi bi-box-arrow-right"></i> Sign Out
                            </a></li>
                    </ul>
                </div>

                <!-- Realtime Search Suggestions Dropdown -->
                <div id="mobileSearchSuggestions" class="floating-search-suggestions"></div>
            </div>

            <!-- Filter chips -->
            <div class="mobile-map-chips-bar d-flex align-items-center gap-2" id="mapChipsBar">
                <!-- Status/Ownership Filter Dropdown ("All") -->
                <div class="dropdown d-inline-block">
                    <button class="map-chip dropdown-toggle d-flex align-items-center gap-1.5 active" type="button"
                        id="allFilterDropdown" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy":"fixed"}'
                        aria-expanded="false">
                        <span id="selectedAllFilterLabel">All (<?php echo count($lands_data); ?>)</span>
                    </button>
                    <ul class="dropdown-menu shadow-lg border-0 rounded-4 p-2" aria-labelledby="allFilterDropdown"
                        style="min-width: 180px; font-size: 0.82rem; z-index: 1060;">
                        <li>
                            <a class="dropdown-item rounded-3 py-1.5 px-3 d-flex align-items-center justify-content-between active"
                                href="#"
                                onclick="selectAllFilter('all', 'All (<?php echo count($lands_data); ?>)', this); return false;">
                                <span class="d-flex align-items-center gap-2"><i
                                        class="bi bi-grid-fill text-muted"></i>All Lands</span>
                                <span
                                    class="badge bg-secondary-subtle text-secondary rounded-pill"><?php echo count($lands_data); ?></span>
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider my-1">
                        </li>
                        <li>
                            <a class="dropdown-item rounded-3 py-1.5 px-3 d-flex align-items-center justify-content-between"
                                href="#" onclick="selectAllFilter('my', 'Mine', this); return false;">
                                <span class="d-flex align-items-center gap-2"><i
                                        class="bi bi-person-fill text-primary"></i>Mine</span>
                                <span
                                    class="badge bg-primary-subtle text-primary rounded-pill"><?php echo $my_count; ?></span>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item rounded-3 py-1.5 px-3 d-flex align-items-center justify-content-between"
                                href="#" onclick="selectAllFilter('approved', 'Approved', this); return false;">
                                <span class="d-flex align-items-center gap-2"><i
                                        class="bi bi-check-circle-fill text-success"></i>Approved</span>
                                <span
                                    class="badge bg-success-subtle text-success rounded-pill"><?php echo $approved_count; ?></span>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Crop Filter Dropdown -->
                <div class="dropdown d-inline-block">
                    <button class="map-chip dropdown-toggle d-flex align-items-center gap-1.5" type="button"
                        id="cropFilterDropdown" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy":"fixed"}'
                        aria-expanded="false">
                        <img id="selectedCropFilterIcon"
                            src="<?php echo $base_path; ?>assets/crop-icons/generic-plant/generic-plant.svg"
                            style="width:16px;height:16px;object-fit:contain;">
                        <span id="selectedCropFilterLabel">All Crops</span>
                    </button>
                    <ul class="dropdown-menu shadow-lg border-0 rounded-4 p-2" aria-labelledby="cropFilterDropdown"
                        style="max-height: 280px; overflow-y: auto; min-width: 190px; font-size: 0.82rem; z-index: 1060;">
                        <li><a class="dropdown-item rounded-3 py-1.5 px-3 d-flex align-items-center gap-2 active"
                                href="#"
                                onclick="selectCropFilter('all', 'All Crops', 'generic-plant/generic-plant.svg', this)">
                                <img src="<?php echo $base_path; ?>assets/crop-icons/generic-plant/generic-plant.svg"
                                    style="width:16px;height:16px;"> <span>All Crops</span>
                            </a></li>
                        <li>
                            <hr class="dropdown-divider my-1">
                        </li>
                        <li><a class="dropdown-item rounded-3 py-1.5 px-3 d-flex align-items-center gap-2" href="#"
                                onclick="selectCropFilter('tomato', 'Tomato', 'tomato/tomato.svg', this)">
                                <img src="<?php echo $base_path; ?>assets/crop-icons/tomato/tomato.svg"
                                    style="width:16px;height:16px;"> <span>Tomato</span>
                            </a></li>
                        <li><a class="dropdown-item rounded-3 py-1.5 px-3 d-flex align-items-center gap-2" href="#"
                                onclick="selectCropFilter('lettuce', 'Lettuce / Greens', 'romaine/romaine.svg', this)">
                                <img src="<?php echo $base_path; ?>assets/crop-icons/romaine/romaine.svg"
                                    style="width:16px;height:16px;"> <span>Lettuce / Greens</span>
                            </a></li>
                        <li><a class="dropdown-item rounded-3 py-1.5 px-3 d-flex align-items-center gap-2" href="#"
                                onclick="selectCropFilter('herbs', 'Herbs / Basil', 'basil/basil.svg', this)">
                                <img src="<?php echo $base_path; ?>assets/crop-icons/basil/basil.svg"
                                    style="width:16px;height:16px;"> <span>Herbs / Basil</span>
                            </a></li>
                        <li><a class="dropdown-item rounded-3 py-1.5 px-3 d-flex align-items-center gap-2" href="#"
                                onclick="selectCropFilter('carrot', 'Carrot / Root Vegs', 'carrot/carrot.svg', this)">
                                <img src="<?php echo $base_path; ?>assets/crop-icons/carrot/carrot.svg"
                                    style="width:16px;height:16px;"> <span>Carrot / Root Vegs</span>
                            </a></li>
                        <li><a class="dropdown-item rounded-3 py-1.5 px-3 d-flex align-items-center gap-2" href="#"
                                onclick="selectCropFilter('potato', 'Potato / Tubers', 'russet-potato/russet-potato.svg', this)">
                                <img src="<?php echo $base_path; ?>assets/crop-icons/russet-potato/russet-potato.svg"
                                    style="width:16px;height:16px;"> <span>Potato / Tubers</span>
                            </a></li>
                        <li><a class="dropdown-item rounded-3 py-1.5 px-3 d-flex align-items-center gap-2" href="#"
                                onclick="selectCropFilter('pepper', 'Pepper', 'red-bell-pepper/red-bell-pepper.svg', this)">
                                <img src="<?php echo $base_path; ?>assets/crop-icons/red-bell-pepper/red-bell-pepper.svg"
                                    style="width:16px;height:16px;"> <span>Pepper</span>
                            </a></li>
                        <li><a class="dropdown-item rounded-3 py-1.5 px-3 d-flex align-items-center gap-2" href="#"
                                onclick="selectCropFilter('eggplant', 'Eggplant', 'eggplant/eggplant.svg', this)">
                                <img src="<?php echo $base_path; ?>assets/crop-icons/eggplant/eggplant.svg"
                                    style="width:16px;height:16px;"> <span>Eggplant</span>
                            </a></li>
                        <li><a class="dropdown-item rounded-3 py-1.5 px-3 d-flex align-items-center gap-2" href="#"
                                onclick="selectCropFilter('cucumber', 'Cucumber', 'cucumber/cucumber.svg', this)">
                                <img src="<?php echo $base_path; ?>assets/crop-icons/cucumber/cucumber.svg"
                                    style="width:16px;height:16px;"> <span>Cucumber</span>
                            </a></li>
                        <li><a class="dropdown-item rounded-3 py-1.5 px-3 d-flex align-items-center gap-2" href="#"
                                onclick="selectCropFilter('spinach', 'Spinach', 'spinach/spinach.svg', this)">
                                <img src="<?php echo $base_path; ?>assets/crop-icons/spinach/spinach.svg"
                                    style="width:16px;height:16px;"> <span>Spinach</span>
                            </a></li>
                        <li><a class="dropdown-item rounded-3 py-1.5 px-3 d-flex align-items-center gap-2" href="#"
                                onclick="selectCropFilter('beans', 'Beans / Legumes', 'broad-bean/broad-bean.svg', this)">
                                <img src="<?php echo $base_path; ?>assets/crop-icons/broad-bean/broad-bean.svg"
                                    style="width:16px;height:16px;"> <span>Beans / Legumes</span>
                            </a></li>
                        <li><a class="dropdown-item rounded-3 py-1.5 px-3 d-flex align-items-center gap-2" href="#"
                                onclick="selectCropFilter('corn', 'Corn', 'corn/corn.svg', this)">
                                <img src="<?php echo $base_path; ?>assets/crop-icons/corn/corn.svg"
                                    style="width:16px;height:16px;"> <span>Corn</span>
                            </a></li>
                        <li><a class="dropdown-item rounded-3 py-1.5 px-3 d-flex align-items-center gap-2" href="#"
                                onclick="selectCropFilter('strawberry', 'Fruits / Berries', 'strawberry/strawberry.svg', this)">
                                <img src="<?php echo $base_path; ?>assets/crop-icons/strawberry/strawberry.svg"
                                    style="width:16px;height:16px;"> <span>Fruits / Berries</span>
                            </a></li>
                    </ul>
                </div>

                <!-- 3D View Switcher -->
                <button type="button" class="map-chip view-toggle-chip ms-auto d-flex align-items-center gap-1.5"
                    id="toggle3DViewBtn" onclick="toggle3DMapMode()">
                    <i class="bi bi-box-fill"></i>
                    <span id="toggle3DViewLabel">3D</span>
                </button>
            </div>

            <!-- 3D City & Garden Map Container -->
            <div id="landowner3DMapContainer" class="map3d-container"></div>

            <!-- Leaflet Map -->
            <div id="landownerMainMap" style="width:100%;height:100%;min-height:450px;z-index:1;"></div>

            <!-- Desktop FAB stack (right side) -->
            <div class="position-absolute d-flex flex-column gap-2" style="bottom:28px;right:16px;z-index:1001;">
                <button type="button" class="map-fab-btn" onclick="recenterLandownerMap()" title="Fit all gardens">
                    <i data-lucide="locate" style="width:19px;height:19px;" class="text-success"></i>
                </button>
            </div>

            <!-- Bottom Sheet -->
            <div id="mobileLandCardSheet" class="mobile-land-sheet hidden">

                <!-- Drag handle -->
                <div class="sheet-drag-handle-container" id="sheetDragHandleContainer"
                    title="Drag down or tap to slide down">
                    <div class="sheet-drag-handle"></div>
                </div>

                <!-- Header row -->
                <div class="d-flex align-items-start justify-content-between mb-2 px-1">
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span id="sheetStatusBadge" class="badge rounded-pill px-2 py-0.5"
                                style="font-size:0.68rem;display:none;"></span>
                            <span id="sheetOwnerBadge" class="badge rounded-pill border text-secondary px-2 py-0.5"
                                style="font-size:0.68rem;background:#f8f9fa;">Owner</span>
                        </div>
                        <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                            <h3 id="sheetLandTitle" class="fw-bold text-dark mb-0"
                                style="font-size:1.05rem;line-height:1.2;">
                                Land Title</h3>
                            <span
                                class="badge rounded-pill bg-light border text-dark px-2 py-0.5 d-inline-flex align-items-center gap-1 shadow-xs"
                                style="font-size:0.72rem;font-weight:600;" title="Total Land Area">
                                <i class="bi bi-rulers text-secondary" style="font-size:0.75rem;"></i>
                                <span id="sheetLandArea">— m²</span>
                            </span>
                            <span
                                class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2 py-0.5 d-inline-flex align-items-center gap-1 shadow-xs"
                                style="font-size:0.72rem;font-weight:600;" title="Partition Plots">
                                <i class="bi bi-grid-3x3-gap-fill text-success" style="font-size:0.75rem;"></i>
                                <span id="sheetPlotsCount">— / —</span>
                                <span style="font-weight:500;">plots</span>
                            </span>
                        </div>
                        <p id="sheetLandAddress" class="text-secondary mb-0" style="font-size:0.75rem;">
                            <i data-lucide="map-pin" style="width:12px;height:12px;"
                                class="text-success flex-shrink-0"></i>
                            Address here
                        </p>
                    </div>
                    <div class="d-flex align-items-center gap-1.5 ms-2 flex-shrink-0">
                        <button type="button" class="sheet-nav-btn" onclick="navigateGardenCard(-1)"
                            title="Previous Garden">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                fill="none" stroke="#0f172a" stroke-width="2.8" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="m15 18-6-6 6-6" />
                            </svg>
                        </button>
                        <button type="button" class="sheet-nav-btn" onclick="navigateGardenCard(1)" title="Next Garden">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                fill="none" stroke="#0f172a" stroke-width="2.8" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="m9 18 6-6-6-6" />
                            </svg>
                        </button>
                        <button type="button" class="sheet-nav-btn ms-1" onclick="closeLandCardSheet()" title="Close">
                            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"
                                fill="none" stroke="#0f172a" stroke-width="2.8" stroke-linecap="round"
                                stroke-linejoin="round">
                                <path d="M18 6 6 18" />
                                <path d="m6 6 12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Permitted Crops Section with Icons -->
                <div class="mb-2">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-secondary fw-semibold"
                            style="font-size:0.68rem;letter-spacing:0.5px;text-transform:uppercase;">Permitted
                            Crops</span>
                    </div>
                    <div id="sheetPermittedCropsContainer" class="d-flex flex-wrap gap-1.5 align-items-center"></div>
                </div>

                <!-- Description -->
                <p id="sheetLandDesc" class="text-secondary mb-2"
                    style="font-size:0.78rem;line-height:1.5;max-height:48px;overflow:hidden;"></p>

                <!-- Partition Plots Grid -->
                <div class="mb-2.5">
                    <div class="d-flex align-items-center justify-content-between mb-1.5">
                        <span class="text-secondary fw-semibold"
                            style="font-size:0.68rem;letter-spacing:0.5px;text-transform:uppercase;">Partition
                            Plots</span>
                        <span id="sheetPlotsSummary"
                            class="badge bg-success-subtle text-success rounded-pill px-2 py-0.5"
                            style="font-size:0.65rem;"></span>
                    </div>
                    <div id="sheetPlotsGrid" class="d-flex align-items-center gap-1.5 overflow-x-auto pb-1 flex-nowrap"
                        style="scrollbar-width: thin;"></div>
                </div>


                <!-- CTA buttons -->
                <div class="d-flex gap-2">
                    <a id="sheetManageBtn" href="lands.php"
                        class="btn btn-drive-primary btn-sm rounded-pill flex-grow-1 d-flex align-items-center justify-content-center gap-1"
                        style="padding:10px;">
                        <i data-lucide="layers" style="width:14px;height:14px;"></i> Manage Plots
                    </a>
                    <a href="requests.php"
                        class="btn btn-drive-secondary btn-sm rounded-pill d-flex align-items-center gap-1 px-3"
                        style="padding:10px;">
                        <i data-lucide="file-text" style="width:14px;height:14px;"></i>
                        <span class="d-none d-sm-inline">Requests</span>
                    </a>
                    <a href="profile.php" class="btn btn-sm rounded-pill d-flex align-items-center gap-1 px-3"
                        style="padding:10px;background:#f0fdf4;border:1px solid #c3e6cb;color:#198754;">
                        <i data-lucide="user" style="width:14px;height:14px;"></i>
                    </a>
                </div>
            </div>

        </div>
    </div>
</main>

<script>
    const basePath = '<?php echo $base_path; ?>';
    const landsData = <?php echo json_encode($lands_data); ?>;
    const plotsData = <?php echo json_encode($plots_data); ?>;
    let currentLandId = null;
    let landownerMap = null;
    let mapMarkers = [];
    let activePlotLayers = [];
    let currentLandFilterType = 'all';
    let selectedCropFilterVal = 'all';

    const CROP_ICON_MAP = {
        'tomato': 'tomato/tomato.svg',
        'lettuce': 'romaine/romaine.svg',
        'leafy greens': 'romaine/romaine.svg',
        'romaine': 'romaine/romaine.svg',
        'herbs': 'basil/basil.svg',
        'basil': 'basil/basil.svg',
        'pepper': 'red-bell-pepper/red-bell-pepper.svg',
        'carrot': 'carrot/carrot.svg',
        'root vegetables': 'carrot/carrot.svg',
        'tuber crops': 'russet-potato/russet-potato.svg',
        'potato': 'russet-potato/russet-potato.svg',
        'eggplant': 'eggplant/eggplant.svg',
        'cucumber': 'cucumber/cucumber.svg',
        'spinach': 'spinach/spinach.svg',
        'beans': 'broad-bean/broad-bean.svg',
        'legumes': 'broad-bean/broad-bean.svg',
        'squash': 'yellow-squash/yellow-squash.svg',
        'onion': 'red-onion/red-onion.svg',
        'corn': 'corn/corn.svg',
        'garlic': 'garlic/garlic.svg',
        'mushroom': 'generic-mushroom/generic-mushroom.svg',
        'peas': 'snap-pea/snap-pea.svg',
        'fruits': 'strawberry/strawberry.svg',
        'strawberry': 'strawberry/strawberry.svg',
        'broccoli': 'broccoli/broccoli.svg'
    };

    function escapeHtml(str) {
        if (str === null || str === undefined) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function getCropIconPath(cropName) {
        if (!cropName) return 'generic-plant/generic-plant.svg';
        const lower = cropName.toLowerCase().trim();
        for (const key in CROP_ICON_MAP) {
            if (lower.includes(key) || key.includes(lower)) {
                return CROP_ICON_MAP[key];
            }
        }
        return 'generic-plant/generic-plant.svg';
    }

    function navigateGardenCard(direction) {
        if (!landsData || landsData.length === 0) return;
        let currentIndex = landsData.findIndex(l => l.id == currentLandId);
        if (currentIndex === -1) currentIndex = 0;

        let nextIndex = (currentIndex + direction + landsData.length) % landsData.length;
        openLandCardSheet(landsData[nextIndex]);
    }

    function focusOnSpecificPlot(landId, plotId) {
        currentLandId = landId;
        currentFocusedPlotId = plotId;
        const sheet3DText = document.getElementById('sheet3DText');
        if (sheet3DText) {
            const plot = plotsData.find(p => p.id == plotId);
            if (plot) sheet3DText.textContent = `Generate 3D Map for ${plot.plot_number}`;
        }

        const land = landsData.find(l => l.id == landId);
        if (!land) return;

        const landPlots = plotsData.filter(p => p.land_id == land.id);
        const plotIndex = landPlots.findIndex(p => p.id == plotId);
        if (plotIndex === -1) return;

        const lat = parseFloat(land.latitude);
        const lng = parseFloat(land.longitude);
        const totalArea = parseFloat(land.area) || 100;
        const sideMeters = Math.sqrt(totalArea);
        const halfSide = sideMeters / 2;
        const deltaLat = halfSide / 111320;
        const deltaLng = halfSide / (111320 * Math.cos(lat * Math.PI / 180));

        const N = landPlots.length;
        const cols = Math.ceil(Math.sqrt(N));
        const rows = Math.ceil(N / cols);
        const plotW = (deltaLng * 2) / cols;
        const plotH = (deltaLat * 2) / rows;

        const c = plotIndex % cols;
        const r = Math.floor(plotIndex / cols);

        const pMinLat = (lat + deltaLat) - ((r + 1) * plotH);
        const pMaxLat = (lat + deltaLat) - (r * plotH);
        const pMinLng = (lng - deltaLng) + (c * plotW);
        const pMaxLng = (lng - deltaLng) + ((c + 1) * plotW);

        const pCenterLat = (pMinLat + pMaxLat) / 2;
        const pCenterLng = (pMinLng + pMaxLng) / 2;

        const activeMap = landownerMap;
        if (!activeMap) return;

        const targetZoom = 21;
        const isMobile = window.innerWidth <= 768;
        const targetPoint = activeMap.project([pCenterLat, pCenterLng], targetZoom);

        if (isMobile) {
            targetPoint.y += 140;
        } else {
            targetPoint.x -= 240;
            targetPoint.y += 40;
        }

        const offsetTarget = activeMap.unproject(targetPoint, targetZoom);
        activeMap.flyTo(offsetTarget, targetZoom, {
            animate: true,
            duration: 1.2
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const sheetEl = document.getElementById('mobileLandCardSheet');
        if (sheetEl && sheetEl.parentElement !== document.body) {
            document.body.appendChild(sheetEl);
        }
        initLandownerMap();
        if (typeof initSheetSlider === 'function') {
            initSheetSlider({
                sheet: 'mobileLandCardSheet',
                handle: '#sheetDragHandleContainer',
                onClose: closeLandCardSheet
            });
        }
    });

    function initLandownerMap() {
        const el = document.getElementById('landownerMainMap');
        if (!el) return;

        landownerMap = L.map('landownerMainMap', { zoomControl: false })
            .setView([14.6010, 120.9890], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 21,
            maxNativeZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(landownerMap);

        setTimeout(() => {
            if (landownerMap) landownerMap.invalidateSize();
        }, 200);

        renderMapPins(landsData);

        // Check if URL has ?id=X parameter to focus & zoom into specific garden
        const urlParams = new URLSearchParams(window.location.search);
        const paramId = urlParams.get('id');
        if (paramId) {
            const targetLand = landsData.find(l => l.id == paramId);
            if (targetLand) {
                setTimeout(() => openLandCardSheet(targetLand), 300);
            }
        }

        // Hide register FAB when sheet is open
        landownerMap.on('click', closeLandCardSheet);
    }

    function renderMapPins(list) {
        if (!landownerMap) return;

        // Clear existing map markers and plot layers
        mapMarkers.forEach(m => landownerMap.removeLayer(m));
        mapMarkers = [];
        if (activePlotLayers) {
            activePlotLayers.forEach(l => landownerMap.removeLayer(l));
        }
        activePlotLayers = [];

        const bounds = [];

        list.forEach(land => {
            const lat = parseFloat(land.latitude);
            const lng = parseFloat(land.longitude);
            if (isNaN(lat) || isNaN(lng)) return;
            bounds.push([lat, lng]);

            const approved = land.status === 'approved';
            const pinColor = approved ? '#198754' : '#e8a000';

            // Main Garden Pin with Plant Icon Badge support
            let firstCrop = null;
            if (Array.isArray(land.crops) && land.crops.length > 0) {
                firstCrop = land.crops[0];
            } else if (typeof land.crops === 'string' && land.crops.trim()) {
                try {
                    const parsed = JSON.parse(land.crops);
                    if (Array.isArray(parsed) && parsed.length > 0) firstCrop = parsed[0];
                    else firstCrop = land.crops.split(',')[0].trim();
                } catch (e) {
                    firstCrop = land.crops.split(',')[0].trim();
                }
            } else if (Array.isArray(land.allowed_seeds) && land.allowed_seeds.length > 0) {
                firstCrop = land.allowed_seeds[0];
            } else if (typeof land.allowed_seeds === 'string' && land.allowed_seeds.trim()) {
                firstCrop = land.allowed_seeds.split(',')[0].trim();
            }

            const activeCropIcon = selectedCropFilterVal !== 'all'
                ? getCropIconPath(selectedCropFilterVal)
                : (firstCrop ? getCropIconPath(firstCrop) : null);

            const cropPinContent = activeCropIcon
                ? `<img src="${basePath}assets/crop-icons/${activeCropIcon}" style="width:24px;height:24px;object-fit:contain;background:#fff;border-radius:50%;padding:2px;box-shadow: 0 2px 6px rgba(0,0,0,0.3);" alt="Crop Icon">`
                : `<i class="bi ${approved ? 'bi-tree-fill' : 'bi-clock-fill'}"></i>`;

            const icon = L.divIcon({
                className: '',
                html: `<div class="land-map-pin d-flex align-items-center justify-content-center" style="background:${pinColor};width:42px;height:42px;border-radius:50%;border:3px solid #fff;box-shadow: 0 4px 12px rgba(0,0,0,0.25);cursor:pointer;">
                       ${cropPinContent}
                   </div>`,
                iconSize: [42, 42],
                iconAnchor: [21, 21]
            });

            const marker = L.marker([lat, lng], { icon }).addTo(landownerMap);
            marker.on('click', () => openLandCardSheet(land));
            mapMarkers.push(marker);
        });

        if (bounds.length) {
            landownerMap.fitBounds(bounds, { padding: [80, 80], maxZoom: 16 });
        }
    }

    function openLandCardSheet(land) {
        currentLandId = land.id;
        currentFocusedPlotId = null;
        const sheet3DText = document.getElementById('sheet3DText');
        if (sheet3DText) sheet3DText.textContent = 'Generate 3D Map for Garden';

        const sheet = document.getElementById('mobileLandCardSheet');
        if (!sheet) return;

        // Ultra high detail zoom level 20 animation
        const lat = parseFloat(land.latitude);
        const lng = parseFloat(land.longitude);
        const targetZoom = 20;

        if (!isNaN(lat) && !isNaN(lng) && landownerMap) {
            const isMobile = window.innerWidth <= 768;
            const targetPoint = landownerMap.project([lat, lng], targetZoom);

            if (isMobile) {
                const sheetHeight = sheet ? sheet.offsetHeight : 340;
                targetPoint.y += (sheetHeight / 2) + 30;
            } else {
                targetPoint.x -= 220;
                targetPoint.y += 40;
            }

            const offsetTarget = landownerMap.unproject(targetPoint, targetZoom);

            landownerMap.flyTo(offsetTarget, targetZoom, {
                animate: true,
                duration: 1.2
            });
        }

        const approved = land.status === 'approved';
        const stripColor = approved ? '#198754' : '#ffc107';

        const strip = document.getElementById('sheetStatusStrip');
        if (strip) strip.style.background = stripColor;
        const statusBadge = document.getElementById('sheetStatusBadge');
        if (statusBadge) {
            if (!approved) {
                statusBadge.textContent = 'Pending Review';
                statusBadge.style.background = '#ffc107';
                statusBadge.style.color = '#333';
                statusBadge.style.display = 'inline-block';
            } else {
                statusBadge.textContent = '';
                statusBadge.style.display = 'none';
            }
        }
        document.getElementById('sheetOwnerBadge').textContent = land.landowner || 'Landowner';
        document.getElementById('sheetLandTitle').textContent = land.title || 'Untitled Garden';
        const addressEl = document.getElementById('sheetLandAddress');
        if (addressEl) {
            addressEl.innerHTML = `<i data-lucide="map-pin" style="width:12px;height:12px;" class="text-success flex-shrink-0"></i> ${escapeHtml(land.address || 'Address not specified')}`;
            addressEl.title = land.address || '';
        }
        const landArea = parseFloat(land.area) || 0;
        document.getElementById('sheetLandArea').textContent = `${landArea > 0 ? landArea.toFixed(0) : '0'} m²`;
        document.getElementById('sheetPlotsCount').textContent = `${land.occupied_plots ?? 0} / ${land.total_plots ?? 0}`;

        // Render Permitted Crops as SVG icons inside sheet (support Array, JSON string, or comma-separated string)
        let rawCrops = [];
        if (Array.isArray(land.crops)) {
            rawCrops = land.crops;
        } else if (typeof land.crops === 'string' && land.crops.trim()) {
            try {
                const parsed = JSON.parse(land.crops);
                rawCrops = Array.isArray(parsed) ? parsed : [land.crops];
            } catch (e) {
                rawCrops = land.crops.split(',').map(s => s.trim()).filter(Boolean);
            }
        } else if (Array.isArray(land.allowed_seeds)) {
            rawCrops = land.allowed_seeds;
        } else if (typeof land.allowed_seeds === 'string' && land.allowed_seeds.trim()) {
            try {
                const parsed = JSON.parse(land.allowed_seeds);
                rawCrops = Array.isArray(parsed) ? parsed : [land.allowed_seeds];
            } catch (e) {
                rawCrops = land.allowed_seeds.split(',').map(s => s.trim()).filter(Boolean);
            }
        }

        const cropsContainer = document.getElementById('sheetPermittedCropsContainer');
        if (cropsContainer) {
            if (!rawCrops || rawCrops.length === 0) {
                cropsContainer.innerHTML = `<span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1" style="font-size:0.72rem;"><img src="${basePath}assets/crop-icons/generic-plant/generic-plant.svg" style="width:14px;height:14px;margin-right:4px;">All Crops Permitted</span>`;
            } else {
                cropsContainer.innerHTML = rawCrops.map(crop => {
                    const iconPath = getCropIconPath(crop);
                    return `
                        <span class="badge bg-white text-dark border rounded-pill px-2.5 py-1 d-inline-flex align-items-center gap-1.5 shadow-xs" style="font-size:0.72rem;font-weight:500;">
                            <img src="${basePath}assets/crop-icons/${iconPath}" style="width:15px;height:15px;object-fit:contain;" alt="${crop}">
                            <span>${crop}</span>
                        </span>
                    `;
                }).join('');
            }
        }

        document.getElementById('sheetLandDesc').textContent = land.description || '';
        document.getElementById('sheetManageBtn').href = `lands.php?id=${land.id}`;

        // Render Partition Plots Grid in sheet
        const landPlots = plotsData.filter(p => p.land_id == land.id);
        const availPlots = landPlots.filter(p => p.status === 'available').length;
        const summaryBadge = document.getElementById('sheetPlotsSummary');
        if (summaryBadge) {
            summaryBadge.textContent = `${availPlots} / ${landPlots.length} Available`;
        }

        const plotsGridEl = document.getElementById('sheetPlotsGrid');
        if (plotsGridEl) {
            if (landPlots.length === 0) {
                plotsGridEl.innerHTML = `<div class="col-12 text-center text-muted py-2" style="font-size:0.75rem;">No partition plots configured yet.</div>`;
            } else {
                plotsGridEl.innerHTML = landPlots.map(p => {
                    const isAvail = p.status === 'available';
                    const isOccupied = p.status === 'occupied';
                    const bgClass = isAvail ? 'bg-success-subtle border-success-subtle text-success' : (isOccupied ? 'bg-primary-subtle border-primary-subtle text-primary' : 'bg-light border text-secondary');
                    const badgeText = isAvail ? 'Available' : (isOccupied ? 'Leased (Locked)' : 'Maintenance');
                    const lockIcon = isOccupied ? `<i class="bi bi-lock-fill text-primary" style="font-size:10px;" title="Leased Plot (Locked)"></i>` : (isAvail ? `<i class="bi bi-check-circle-fill text-success" style="font-size:10px;"></i>` : `<i class="bi bi-tools text-secondary" style="font-size:10px;"></i>`);

                    const allowedCrops = (p.crops && p.crops.length > 0) ? p.crops : (p.crop ? [p.crop] : []);
                    let cropsBadgesHtml = allowedCrops.slice(0, 3).map(c => `
                        <img src="${basePath}assets/crop-icons/${getCropIconPath(c)}" style="width:14px;height:14px;object-fit:contain;" title="Permitted: ${c}" alt="${c}">
                    `).join('');
                    if (allowedCrops.length === 0) {
                        cropsBadgesHtml = `<span style="font-size:9px;" class="text-muted">All Permitted</span>`;
                    }

                    return `
                        <div class="flex-shrink-0" onclick="focusOnSpecificPlot(${land.id}, ${p.id})" style="cursor:pointer;" title="${badgeText} - ${parseFloat(p.area || 0).toFixed(0)} m²">
                            <div class="px-2.5 py-1 rounded-pill border ${bgClass} d-flex align-items-center gap-1.5 shadow-xs hover-elevate transition-all" style="font-size:0.72rem; white-space:nowrap; height: 28px;">
                                <i class="bi bi-grid-3x3-gap-fill text-success" style="font-size:11px;"></i>
                                <span class="fw-bold text-dark">${p.plot_number}</span>
                                <span class="text-secondary" style="font-size:0.68rem;">${parseFloat(p.area || 0).toFixed(0)}m²</span>
                                ${cropsBadgesHtml}
                            </div>
                        </div>
                    `;
                }).join('');
            }
        }

        // Draw High Detail Plot Grid Polygons on Leaflet map
        if (activePlotLayers) {
            activePlotLayers.forEach(l => landownerMap.removeLayer(l));
        }
        activePlotLayers = [];

        try {
            if (landPlots.length > 0 && !isNaN(lat) && !isNaN(lng) && landownerMap) {
                const totalArea = parseFloat(land.area) || 100;
                const sideMeters = Math.sqrt(totalArea);
                const halfSide = sideMeters / 2;
                const cosLat = (!isNaN(lat) && Math.abs(Math.cos(lat * Math.PI / 180)) > 0.0001) ? Math.abs(Math.cos(lat * Math.PI / 180)) : 1;
                const deltaLat = halfSide / 111320;
                const deltaLng = halfSide / (111320 * cosLat);

                let polyPts = null;
                if (land.polygon) {
                    try {
                        const parsed = typeof land.polygon === 'string' ? JSON.parse(land.polygon) : land.polygon;
                        if (Array.isArray(parsed) && parsed.length >= 3) {
                            polyPts = parsed.map(pt => ({
                                lat: Array.isArray(pt) ? pt[0] : pt.lat,
                                lng: Array.isArray(pt) ? pt[1] : pt.lng
                            }));
                        }
                    } catch (e) {
                        polyPts = null;
                    }
                }

                // Outer boundary polygon (custom polygon or default square)
                const boundsSquare = polyPts
                    ? polyPts.map(p => [p.lat, p.lng])
                    : [
                        [lat + deltaLat, lng - deltaLng],
                        [lat + deltaLat, lng + deltaLng],
                        [lat - deltaLat, lng + deltaLng],
                        [lat - deltaLat, lng - deltaLng]
                    ];

                const outerSquare = L.polygon(boundsSquare, {
                    color: '#198754',
                    weight: 3,
                    fillColor: '#198754',
                    fillOpacity: 0.08,
                    dashArray: '6, 6'
                }).addTo(landownerMap);
                activePlotLayers.push(outerSquare);

                // Sub-partition plot grid polygons
                const N = landPlots.length;
                let subPlotsSquares = [];

                if (polyPts && polyPts.length === 4) {
                    const widthDist = (Math.hypot(polyPts[1].lat - polyPts[0].lat, polyPts[1].lng - polyPts[0].lng) +
                        Math.hypot(polyPts[2].lat - polyPts[3].lat, polyPts[2].lng - polyPts[3].lng)) / 2;
                    const heightDist = (Math.hypot(polyPts[3].lat - polyPts[0].lat, polyPts[3].lng - polyPts[0].lng) +
                        Math.hypot(polyPts[2].lat - polyPts[1].lat, polyPts[2].lng - polyPts[1].lng)) / 2;
                    const isTaller = heightDist > widthDist * 1.15;

                    let cols, rows;
                    if (N === 1) { cols = 1; rows = 1; }
                    else if (N === 2) { cols = isTaller ? 1 : 2; rows = isTaller ? 2 : 1; }
                    else if (N === 3) { cols = isTaller ? 1 : 3; rows = isTaller ? 3 : 1; }
                    else if (N === 4) { cols = 2; rows = 2; }
                    else if (N === 6) { cols = isTaller ? 2 : 3; rows = isTaller ? 3 : 2; }
                    else if (N === 8) { cols = isTaller ? 2 : 4; rows = isTaller ? 4 : 2; }
                    else {
                        cols = Math.ceil(Math.sqrt(N));
                        rows = Math.ceil(N / cols);
                    }

                    const interp = (u, v) => {
                        const plat = (1 - v) * ((1 - u) * polyPts[0].lat + u * polyPts[1].lat) +
                            v * ((1 - u) * polyPts[3].lat + u * polyPts[2].lat);
                        const plng = (1 - v) * ((1 - u) * polyPts[0].lng + u * polyPts[1].lng) +
                            v * ((1 - u) * polyPts[3].lng + u * polyPts[2].lng);
                        return [plat, plng];
                    };

                    for (let r = 0; r < rows; r++) {
                        for (let c = 0; c < cols; c++) {
                            if (subPlotsSquares.length >= N) break;
                            const u0 = c / cols, u1 = (c + 1) / cols;
                            const v0 = r / rows, v1 = (r + 1) / rows;
                            subPlotsSquares.push([
                                interp(u0, v0),
                                interp(u1, v0),
                                interp(u1, v1),
                                interp(u0, v1)
                            ]);
                        }
                    }
                } else {
                    const cols = Math.ceil(Math.sqrt(N));
                    const rows = Math.ceil(N / cols);
                    const plotW = (deltaLng * 2) / cols;
                    const plotH = (deltaLat * 2) / rows;

                    for (let idx = 0; idx < N; idx++) {
                        const c = idx % cols;
                        const r = Math.floor(idx / cols);
                        const pMinLat = (lat + deltaLat) - ((r + 1) * plotH);
                        const pMaxLat = (lat + deltaLat) - (r * plotH);
                        const pMinLng = (lng - deltaLng) + (c * plotW);
                        const pMaxLng = (lng - deltaLng) + ((c + 1) * plotW);
                        subPlotsSquares.push([
                            [pMaxLat, pMinLng],
                            [pMaxLat, pMaxLng],
                            [pMinLat, pMaxLng],
                            [pMinLat, pMinLng]
                        ]);
                    }
                }

                landPlots.forEach((p, idx) => {
                    const plotSquare = subPlotsSquares[idx] || [
                        [lat + deltaLat, lng - deltaLng],
                        [lat + deltaLat, lng + deltaLng],
                        [lat - deltaLat, lng + deltaLng],
                        [lat - deltaLat, lng - deltaLng]
                    ];

                    const isAvail = p.status === 'available';
                    const isOccupied = p.status === 'occupied';
                    const plotColor = isAvail ? '#198754' : (isOccupied ? '#0d6efd' : '#6c757d');
                    const plotFill = isAvail ? '#25c974' : (isOccupied ? '#3d8bfd' : '#adb5bd');

                    const plotPoly = L.polygon(plotSquare, {
                        color: plotColor,
                        weight: 2,
                        fillColor: plotFill,
                        fillOpacity: 0.38
                    }).addTo(landownerMap);

                    const cropBadge = p.crop_icon
                        ? `<img src="${basePath}assets/crop-icons/${p.crop_icon}" style="width:14px;height:14px;vertical-align:middle;margin-right:3px;">`
                        : '';

                    const lockBadgeText = isOccupied ? '🔒 Leased (Locked)' : (isAvail ? '🟢 Available' : '⚙️ Maintenance');

                    plotPoly.bindTooltip(
                        `<div style="font-family:'Outfit',sans-serif;font-size:11px;">
                            <strong>${cropBadge}${p.plot_number}</strong> (${p.area} m²)<br>
                            ${p.crop ? `<span class="text-success fw-bold">${p.crop}</span><br>` : ''}
                            <span class="badge ${isAvail ? 'bg-success' : 'bg-primary'}" style="font-size:9px;">${lockBadgeText}</span>
                        </div>`,
                        { permanent: false, direction: 'center' }
                    );
                    plotPoly.on('click', () => focusOnSpecificPlot(land.id, p.id));
                    activePlotLayers.push(plotPoly);

                    // Center Plot Tag Marker with Plant Icon & Lock Badge
                    const pCenterLat = plotSquare.reduce((s, pt) => s + pt[0], 0) / plotSquare.length;
                    const pCenterLng = plotSquare.reduce((s, pt) => s + pt[1], 0) / plotSquare.length;

                    const cropImgTag = p.crop_icon
                        ? `<img src="${basePath}assets/crop-icons/${p.crop_icon}" style="width:16px;height:16px;object-fit:contain;background:#fff;border-radius:50%;padding:1px;" alt="${p.crop}">`
                        : `<i class="bi bi-sprout-fill" style="color:#fff;font-size:11px;"></i>`;

                    const plotTagIcon = L.divIcon({
                        className: '',
                        html: `<div class="shadow-sm px-2 py-1 rounded-pill fw-bold text-white text-center d-flex align-items-center gap-1.5" 
                            style="background:${plotColor};font-size:9.5px;border:1.5px solid #fff;white-space:nowrap;backdrop-filter:blur(4px);cursor:pointer;">
                            ${cropImgTag}
                            <span>${p.plot_number}</span>
                        </div>`,
                        iconSize: [92, 26],
                        iconAnchor: [46, 13]
                    });

                    const plotTagMarker = L.marker([pCenterLat, pCenterLng], { icon: plotTagIcon }).addTo(landownerMap);
                    plotTagMarker.on('click', () => focusOnSpecificPlot(land.id, p.id));
                    activePlotLayers.push(plotTagMarker);
                });
            }
        } catch (plotErr) {
            console.warn("Plot polygon calculation error:", plotErr);
        }

        sheet.style.transform = '';
        sheet.style.opacity = '';
        sheet.style.transition = '';

        sheet.classList.remove('hidden');
        document.body.classList.add('sheet-open');
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function closeLandCardSheet() {
        const sheet = document.getElementById('mobileLandCardSheet');
        document.body.classList.remove('sheet-open');
        if (sheet) {
            sheet.classList.add('hidden');
            sheet.style.transform = '';
            sheet.style.opacity = '';
            sheet.style.transition = '';
        }

        if (activePlotLayers) {
            activePlotLayers.forEach(l => landownerMap.removeLayer(l));
            activePlotLayers = [];
        }
    }

    function selectCropFilter(val, label, iconRelPath, el) {
        selectedCropFilterVal = val;
        document.getElementById('selectedCropFilterLabel').textContent = label;
        document.getElementById('selectedCropFilterIcon').src = basePath + 'assets/crop-icons/' + iconRelPath;

        const parentMenu = el.closest('.dropdown-menu');
        if (parentMenu) {
            parentMenu.querySelectorAll('.dropdown-item').forEach(item => item.classList.remove('active'));
            el.classList.add('active');
        }

        applyCombinedMapFilters();
    }

    function recenterLandownerMap() {
        applyCombinedMapFilters();
        closeLandCardSheet();
    }

    function selectAllFilter(type, label, el) {
        currentLandFilterType = type;
        const labelEl = document.getElementById('selectedAllFilterLabel');
        if (labelEl) labelEl.textContent = label;

        const parentMenu = el ? el.closest('.dropdown-menu') : null;
        if (parentMenu) {
            parentMenu.querySelectorAll('.dropdown-item').forEach(item => item.classList.remove('active'));
            if (el) el.classList.add('active');
        }

        const dropdownBtn = document.getElementById('allFilterDropdown');
        if (dropdownBtn) dropdownBtn.classList.add('active');

        applyCombinedMapFilters();
        closeLandCardSheet();
    }

    function filterMapLands(type, btn) {
        selectAllFilter(type, type === 'my' ? 'Mine' : (type === 'approved' ? 'Approved' : 'All'), btn);
    }

    let mobileSearchQuery = '';
    let _mobileMapPlaceDebounce = null;

    function handleMobileMapSearch(query) {
        mobileSearchQuery = (query || '').trim().toLowerCase();
        const clearBtn = document.getElementById('mobileMapSearchClear');
        if (clearBtn) {
            clearBtn.style.display = mobileSearchQuery ? 'inline-block' : 'none';
        }
        applyCombinedMapFilters();

        clearTimeout(_mobileMapPlaceDebounce);
        const box = document.getElementById('mobileSearchSuggestions');
        if (!box) return;

        if (!mobileSearchQuery) {
            box.innerHTML = '';
            box.style.display = 'none';
            return;
        }

        // Direct coordinate check (e.g. "14.5995, 120.9842")
        const coord = mobileSearchQuery.match(/^([-+]?\d+(\.\d+)?)[,\s]+([-+]?\d+(\.\d+)?)$/);
        if (coord) {
            const lat = parseFloat(coord[1]), lng = parseFloat(coord[3]);
            if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
                flyLandownerMapToPlace(lat, lng, query.trim());
                box.innerHTML = '';
                box.style.display = 'none';
                return;
            }
        }

        renderSearchSuggestions(query.trim());
    }

    function handleMobileMapSearchEnter(query) {
        if (!query || !query.trim()) return;
        const q = query.trim().toLowerCase();
        const match = landsData.find(land =>
            (land.title || '').toLowerCase().includes(q) ||
            (land.location || '').toLowerCase().includes(q)
        );
        if (match) {
            selectSearchSuggestion(match.id);
        } else {
            performMobileLandownerPlaceSearch(query.trim(), true);
        }
    }

    function renderSearchSuggestions(query) {
        const box = document.getElementById('mobileSearchSuggestions');
        if (!box) return;

        if (!query || query.length < 1) {
            box.innerHTML = '';
            box.style.display = 'none';
            return;
        }

        const q = query.toLowerCase();
        const matches = landsData.filter(land => {
            const title = (land.title || '').toLowerCase();
            const location = (land.location || '').toLowerCase();
            const owner = (land.landowner || '').toLowerCase();
            const rawCrops = Array.isArray(land.crops) ? land.crops : (land.allowed_seeds || []);
            const landPlots = plotsData.filter(p => p.land_id == land.id);
            const plotCrops = landPlots.map(p => p.crop).filter(Boolean);
            const allCrops = [...rawCrops, ...plotCrops].join(' ').toLowerCase();

            return title.includes(q) ||
                location.includes(q) ||
                owner.includes(q) ||
                allCrops.includes(q);
        }).slice(0, 4);

        let html = '';

        if (matches.length > 0) {
            html += `<div class="px-3 pt-2 pb-1" style="font-size:0.68rem;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:#6c757d;">My Lands</div>`;
            html += matches.map(land => {
                const cropList = Array.isArray(land.crops) ? land.crops.join(', ') : (land.allowed_seeds || []).join(', ');
                return `
                    <div class="search-suggestion-item" onclick="selectSearchSuggestion(${land.id})">
                        <div class="search-suggestion-icon">
                            <i class="bi bi-geo-alt-fill"></i>
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="search-suggestion-title text-truncate">${escapeHtml(land.title)}</div>
                            <div class="search-suggestion-sub text-truncate">
                                <span><i class="bi bi-pin-map me-1"></i>${escapeHtml(land.location || '')}</span>
                                ${cropList ? `<span class="ms-2 badge bg-success-subtle text-success py-0.5 px-1.5" style="font-size:0.68rem;">${escapeHtml(cropList)}</span>` : ''}
                            </div>
                        </div>
                        <i class="bi bi-chevron-right text-muted" style="font-size:0.75rem;"></i>
                    </div>
                `;
            }).join('');
        }

        // Places lookup section (same Nominatim lookup as register page map)
        html += `<div class="px-3 pt-2 pb-1 ${matches.length > 0 ? 'border-top' : ''}" style="font-size:0.68rem;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:#6c757d;">Places</div>`;
        html += `<div id="mobileLandownerPlaceResultsInline"><div class="p-3 text-center text-muted text-xs"><span class="spinner-border spinner-border-sm me-1 text-success"></span> Searching places...</div></div>`;

        box.innerHTML = html;
        box.style.display = 'block';

        clearTimeout(_mobileMapPlaceDebounce);
        _mobileMapPlaceDebounce = setTimeout(() => performMobileLandownerPlaceSearch(query, false), 350);
    }

    async function performMobileLandownerPlaceSearch(query, selectFirst) {
        const inlineBox = document.getElementById('mobileLandownerPlaceResultsInline');
        const box = document.getElementById('mobileSearchSuggestions');
        try {
            const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5&addressdetails=1`;
            const res = await fetch(url);
            if (!res.ok) throw new Error();
            const data = await res.json();

            if (selectFirst && data.length > 0) {
                if (box) { box.innerHTML = ''; box.style.display = 'none'; }
                flyLandownerMapToPlace(parseFloat(data[0].lat), parseFloat(data[0].lon), data[0].display_name);
                const input = document.getElementById('mobileMapSearchInput');
                if (input) input.value = data[0].name || data[0].display_name.split(',')[0];
                return;
            }

            if (!inlineBox) return;
            if (!data || data.length === 0) {
                inlineBox.innerHTML = `<div class="p-3 text-center text-muted text-xs"><i class="bi bi-geo-alt me-1 text-danger"></i> No matching places found.</div>`;
                return;
            }

            inlineBox.innerHTML = data.map((item, idx) => `
                <div class="px-3 py-2 d-flex align-items-start gap-2 landowner-mobile-place-item" style="cursor:pointer;transition:background .15s;border-top:1px solid #f0f0f0;" data-idx="${idx}"
                    onmouseover="this.style.background='#f0fdf4'" onmouseout="this.style.background=''">
                    <i class="bi bi-geo-alt text-success mt-1 flex-shrink-0" style="font-size:0.85rem;"></i>
                    <div class="overflow-hidden">
                        <div class="fw-semibold text-dark text-truncate" style="font-size:0.8rem;">${escapeHtml(item.name || item.display_name.split(',')[0])}</div>
                        <div class="text-muted text-truncate" style="font-size:0.72rem;">${escapeHtml(item.display_name)}</div>
                    </div>
                </div>
            `).join('');

            inlineBox.querySelectorAll('.landowner-mobile-place-item').forEach((el, idx) => {
                el.addEventListener('click', () => {
                    if (box) { box.innerHTML = ''; box.style.display = 'none'; }
                    const inp = document.getElementById('mobileMapSearchInput');
                    if (inp) {
                        inp.value = data[idx].name || data[idx].display_name.split(',')[0];
                        const clearBtn = document.getElementById('mobileMapSearchClear');
                        if (clearBtn) clearBtn.style.display = 'inline-block';
                    }
                    flyLandownerMapToPlace(parseFloat(data[idx].lat), parseFloat(data[idx].lon), data[idx].display_name);
                });
            });
        } catch (e) {
            if (inlineBox) inlineBox.innerHTML = `<div class="p-3 text-center text-danger text-xs"><i class="bi bi-exclamation-triangle me-1"></i> Unable to connect to location service.</div>`;
        }
    }

    function selectSearchSuggestion(landId) {
        const land = landsData.find(l => l.id == landId);
        const box = document.getElementById('mobileSearchSuggestions');
        if (box) {
            box.innerHTML = '';
            box.style.display = 'none';
        }

        if (land) {
            const input = document.getElementById('mobileMapSearchInput');
            if (input) input.value = land.title;
            mobileSearchQuery = land.title.toLowerCase();
            const clearBtn = document.getElementById('mobileMapSearchClear');
            if (clearBtn) clearBtn.style.display = 'inline-block';

            // Filter map to show this and open its card sheet
            applyCombinedMapFilters();
            openLandCardSheet(land);
            flyLandownerMapToPlace(parseFloat(land.latitude), parseFloat(land.longitude), land.title);
        }
    }

    function clearMobileMapSearch() {
        const input = document.getElementById('mobileMapSearchInput');
        if (input) input.value = '';
        const clearBtn = document.getElementById('mobileMapSearchClear');
        if (clearBtn) clearBtn.style.display = 'none';
        const box = document.getElementById('mobileSearchSuggestions');
        if (box) {
            box.innerHTML = '';
            box.style.display = 'none';
        }
        mobileSearchQuery = '';
        applyCombinedMapFilters();
        if (window._landownerPlaceMarker) {
            window._landownerPlaceMarker.remove();
            window._landownerPlaceMarker = null;
        }
    }

    // Close suggestion box when clicking outside
    document.addEventListener('click', function (e) {
        const box = document.getElementById('mobileSearchSuggestions');
        const header = document.querySelector('.floating-map-header');
        if (box && header && !header.contains(e.target)) {
            box.innerHTML = '';
            box.style.display = 'none';
        }
        // Close unified desktop dropdown
        const placeResults = document.getElementById('landownerDesktopPlaceResults');
        const placeWrapper = document.getElementById('landownerUnifiedSearchWrapper') ||
            document.getElementById('headerSearchSlot');
        if (placeResults && placeWrapper && !placeWrapper.contains(e.target)) {
            placeResults.innerHTML = '';
            placeResults.style.display = 'none';
        }
    });

    // ─── Unified Desktop Search (Lands + Nominatim Places) ───────────────────
    let _landownerUnifiedDebounce = null;

    function handleLandownerUnifiedSearch(val) {
        const clearBtn = document.getElementById('landownerPlaceClearBtn');
        if (clearBtn) clearBtn.classList.toggle('d-none', !val.trim());

        const query = val.trim();

        // Also sync mobile search query for map filtering
        mobileSearchQuery = query.toLowerCase();
        applyCombinedMapFilters();

        clearTimeout(_landownerUnifiedDebounce);
        if (!query) {
            const box = document.getElementById('landownerDesktopPlaceResults');
            if (box) { box.innerHTML = ''; box.style.display = 'none'; }
            return;
        }

        // Coordinate shortcut
        const coord = query.match(/^([-+]?\d+(\.\d+)?)[,\s]+([-+]?\d+(\.\d+)?)$/);
        if (coord) {
            const lat = parseFloat(coord[1]), lng = parseFloat(coord[3]);
            if (!isNaN(lat) && !isNaN(lng)) {
                flyLandownerMapToPlace(lat, lng, query);
                const box = document.getElementById('landownerDesktopPlaceResults');
                if (box) { box.innerHTML = ''; box.style.display = 'none'; }
                return;
            }
        }

        renderLandownerUnifiedSuggestions(query);
    }

    function handleLandownerUnifiedSearchEnter(query) {
        if (!query.trim()) return;
        const q = query.trim().toLowerCase();
        const match = landsData.find(land =>
            (land.title || '').toLowerCase().includes(q) ||
            (land.location || '').toLowerCase().includes(q)
        );
        if (match) {
            selectLandownerSearchSuggestion(match.id);
        } else {
            performLandownerUnifiedPlaceSearch(query.trim(), true);
        }
    }

    function selectLandownerSearchSuggestion(landId) {
        const land = landsData.find(l => l.id == landId);
        const box = document.getElementById('landownerDesktopPlaceResults');
        if (box) { box.innerHTML = ''; box.style.display = 'none'; }
        if (land) {
            openLandCardSheet(land);
            flyLandownerMapToPlace(parseFloat(land.latitude), parseFloat(land.longitude), land.title);
        }
    }

    function clearLandownerUnifiedSearch() {
        const input = document.getElementById('landownerDesktopPlaceInput');
        if (input) input.value = '';
        const clearBtn = document.getElementById('landownerPlaceClearBtn');
        if (clearBtn) clearBtn.classList.add('d-none');
        const box = document.getElementById('landownerDesktopPlaceResults');
        if (box) { box.innerHTML = ''; box.style.display = 'none'; }
        mobileSearchQuery = '';
        applyCombinedMapFilters();
        if (window._landownerPlaceMarker) { window._landownerPlaceMarker.remove(); window._landownerPlaceMarker = null; }
    }

    async function renderLandownerUnifiedSuggestions(query) {
        const box = document.getElementById('landownerDesktopPlaceResults');
        if (!box) return;

        const q = query.toLowerCase();
        const landMatches = landsData.filter(land => {
            const title = (land.title || '').toLowerCase();
            const location = (land.location || '').toLowerCase();
            const rawCrops = Array.isArray(land.crops) ? land.crops : (land.allowed_seeds || []);
            const landPlots = plotsData.filter(p => p.land_id == land.id);
            const plotCrops = landPlots.map(p => p.crop).filter(Boolean);
            const allCrops = [...rawCrops, ...plotCrops].join(' ').toLowerCase();
            return title.includes(q) || location.includes(q) || allCrops.includes(q);
        }).slice(0, 4);

        let html = '';

        if (landMatches.length > 0) {
            html += `<div class="px-3 pt-2 pb-1" style="font-size:0.68rem;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:#6c757d;">My Lands</div>`;
            html += landMatches.map(land => {
                const cropList = Array.isArray(land.crops) ? land.crops.join(', ') : (land.allowed_seeds || []).join(', ');
                return `
                    <div class="search-suggestion-item" onclick="selectLandownerSearchSuggestion(${land.id})">
                        <div class="search-suggestion-icon"><i class="bi bi-geo-alt-fill"></i></div>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="search-suggestion-title text-truncate">${escapeHtml(land.title)}</div>
                            <div class="search-suggestion-sub text-truncate">
                                <span><i class="bi bi-pin-map me-1"></i>${escapeHtml(land.location || '')}</span>
                                ${cropList ? `<span class="ms-2 badge bg-success-subtle text-success py-0.5 px-1.5" style="font-size:0.68rem;">${escapeHtml(cropList)}</span>` : ''}
                            </div>
                        </div>
                        <i class="bi bi-chevron-right text-muted" style="font-size:0.75rem;"></i>
                    </div>`;
            }).join('');
        }

        // Places section header + spinner
        html += `<div class="px-3 pt-2 pb-1 border-top" style="font-size:0.68rem;font-weight:700;letter-spacing:.6px;text-transform:uppercase;color:#6c757d;">Places</div>`;
        html += `<div id="landownerPlaceResultsInline"><div class="p-3 text-center text-muted text-xs"><span class="spinner-border spinner-border-sm me-1 text-success"></span> Searching places...</div></div>`;

        box.innerHTML = html;
        box.style.display = 'block';

        // Attach garden click events
        box.querySelectorAll('.search-suggestion-item').forEach(el => {
            // onclick already set inline
        });

        clearTimeout(_landownerUnifiedDebounce);
        _landownerUnifiedDebounce = setTimeout(() => performLandownerUnifiedPlaceSearch(query, false), 350);
    }

    async function performLandownerUnifiedPlaceSearch(query, selectFirst) {
        const inlineBox = document.getElementById('landownerPlaceResultsInline');
        const dropBox = document.getElementById('landownerDesktopPlaceResults');
        try {
            const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5&addressdetails=1`;
            const res = await fetch(url);
            if (!res.ok) throw new Error();
            const data = await res.json();

            if (selectFirst && data.length > 0) {
                if (dropBox) { dropBox.innerHTML = ''; dropBox.style.display = 'none'; }
                flyLandownerMapToPlace(parseFloat(data[0].lat), parseFloat(data[0].lon), data[0].display_name);
                return;
            }

            if (!inlineBox) return;
            if (!data || data.length === 0) {
                inlineBox.innerHTML = `<div class="p-3 text-center text-muted text-xs"><i class="bi bi-geo-alt me-1 text-danger"></i> No matching places found.</div>`;
                return;
            }
            inlineBox.innerHTML = data.map((item, idx) => `
                <div class="px-3 py-2 d-flex align-items-start gap-2 landowner-place-inline-item" style="cursor:pointer;transition:background .15s;border-top:1px solid #f0f0f0;" data-idx="${idx}"
                    onmouseover="this.style.background='#f0fdf4'" onmouseout="this.style.background=''">
                    <i class="bi bi-geo-alt text-success mt-1 flex-shrink-0" style="font-size:0.85rem;"></i>
                    <div class="overflow-hidden">
                        <div class="fw-semibold text-dark text-truncate" style="font-size:0.8rem;">${escapeHtml(item.name || item.display_name.split(',')[0])}</div>
                        <div class="text-muted text-truncate" style="font-size:0.72rem;">${escapeHtml(item.display_name)}</div>
                    </div>
                </div>
            `).join('');
            inlineBox.querySelectorAll('.landowner-place-inline-item').forEach((el, idx) => {
                el.addEventListener('click', () => {
                    if (dropBox) { dropBox.innerHTML = ''; dropBox.style.display = 'none'; }
                    const inp = document.getElementById('landownerDesktopPlaceInput');
                    if (inp) inp.value = data[idx].display_name;
                    flyLandownerMapToPlace(parseFloat(data[idx].lat), parseFloat(data[idx].lon), data[idx].display_name);
                });
            });
        } catch (e) {
            if (inlineBox) inlineBox.innerHTML = `<div class="p-3 text-center text-danger text-xs"><i class="bi bi-exclamation-triangle me-1"></i> Unable to connect to location service.</div>`;
        }
    }

    function flyLandownerMapToPlace(lat, lng, label) {
        if (!landownerMap || isNaN(lat) || isNaN(lng)) return;
        landownerMap.flyTo([lat, lng], 15, { animate: true, duration: 1.2 });
        // Place a temporary location marker
        if (window._landownerPlaceMarker) {
            window._landownerPlaceMarker.remove();
        }
        const icon = L.divIcon({
            className: '',
            html: `<div style="width:18px;height:18px;background:#198754;border:2.5px solid #fff;border-radius:50%;box-shadow:0 2px 8px rgba(0,0,0,0.25);"></div>`,
            iconSize: [18, 18],
            iconAnchor: [9, 9]
        });
        window._landownerPlaceMarker = L.marker([lat, lng], { icon })
            .addTo(landownerMap)
            .bindPopup(`<div style="font-size:0.8rem;"><b>📍 ${escapeHtml(label)}</b></div>`, { maxWidth: 280 })
            .openPopup();
    }

    function clearLandownerPlaceSearch() {
        const input = document.getElementById('landownerDesktopPlaceInput');
        if (input) input.value = '';
        const clearBtn = document.getElementById('landownerPlaceClearBtn');
        if (clearBtn) clearBtn.classList.add('d-none');
        const box = document.getElementById('landownerDesktopPlaceResults');
        if (box) { box.innerHTML = ''; box.style.display = 'none'; }
        if (window._landownerPlaceMarker) {
            window._landownerPlaceMarker.remove();
            window._landownerPlaceMarker = null;
        }
    }

    function useMyLocationLandowner() {
        if (!navigator.geolocation) { alert('Geolocation is not supported by your browser.'); return; }
        navigator.geolocation.getCurrentPosition(
            pos => flyLandownerMapToPlace(pos.coords.latitude, pos.coords.longitude, 'My Location'),
            err => alert('Unable to get your location: ' + err.message),
            { enableHighAccuracy: true, timeout: 8000 }
        );
    }

    function applyCombinedMapFilters() {
        let list = landsData;
        if (currentLandFilterType === 'my') list = list.filter(l => l.landowner === 'John Landowner');
        else if (currentLandFilterType === 'approved') list = list.filter(l => l.status === 'approved');
        else if (currentLandFilterType === 'pending') list = list.filter(l => l.status === 'pending');

        if (selectedCropFilterVal !== 'all') {
            list = list.filter(land => {
                const rawCrops = Array.isArray(land.crops) ? land.crops : (land.allowed_seeds || []);
                const landPlots = plotsData.filter(p => p.land_id == land.id);
                const plotCrops = landPlots.map(p => p.crop).filter(Boolean);

                const allCrops = [...rawCrops, ...plotCrops].map(c => c.toLowerCase());
                return allCrops.some(c => c.includes(selectedCropFilterVal.toLowerCase()) || selectedCropFilterVal.toLowerCase().includes(c));
            });
        }

        if (mobileSearchQuery) {
            list = list.filter(land => {
                const title = (land.title || '').toLowerCase();
                const location = (land.location || '').toLowerCase();
                const owner = (land.landowner || '').toLowerCase();
                const rawCrops = Array.isArray(land.crops) ? land.crops : (land.allowed_seeds || []);
                const landPlots = plotsData.filter(p => p.land_id == land.id);
                const plotCrops = landPlots.map(p => p.crop).filter(Boolean);
                const allCrops = [...rawCrops, ...plotCrops].join(' ').toLowerCase();

                return title.includes(mobileSearchQuery) ||
                    location.includes(mobileSearchQuery) ||
                    owner.includes(mobileSearchQuery) ||
                    allCrops.includes(mobileSearchQuery);
            });
        }

        renderMapPins(list);
    }

    // ─── 3D City & Garden Map View Handler ───
    let map3dEngine = null;
    let is3DMode = false;
    let currentFocusedPlotId = null;

    function generate3DForSelectedPlot(landId, plotId) {
        currentLandId = landId;
        currentFocusedPlotId = plotId;
        toggle3DMapMode(true);
    }

    function generate3DFromSheet() {
        if (!currentLandId && landsData.length > 0) {
            currentLandId = landsData[0].id;
        }
        toggle3DMapMode(true);
    }

    function toggle3DMapMode(forceOpen = null) {
        if (forceOpen !== null) {
            is3DMode = forceOpen;
        } else {
            is3DMode = !is3DMode;
        }
        const container3D = document.getElementById('landowner3DMapContainer');
        const toggleBtn = document.getElementById('toggle3DViewBtn');
        const toggleLabel = document.getElementById('toggle3DViewLabel');

        if (is3DMode) {
            container3D.classList.add('active');
            toggleBtn.classList.add('active-3d');
            toggleLabel.textContent = '2D';
            toggleBtn.querySelector('i').className = 'bi bi-map-fill';

            let targetLand = landsData.find(l => l.id == currentLandId) || landsData[0];
            let targetPlot = currentFocusedPlotId ? plotsData.find(p => p.id == currentFocusedPlotId) : null;

            if (!map3dEngine) {
                map3dEngine = new Map3DEngine({
                    container: container3D,
                    center: { lat: parseFloat(targetLand.latitude), lng: parseFloat(targetLand.longitude) },
                    landsData: landsData,
                    plotsData: plotsData,
                    basePath: basePath,
                    onLandClick: function (land) {
                        openLandCardSheet(land);
                    },
                    onExit3D: function () {
                        toggle3DMapMode(false);
                    }
                });
            }

            map3dEngine.generateForPlot(targetLand, targetPlot);
        } else {
            container3D.classList.remove('active');
            toggleBtn.classList.remove('active-3d');
            toggleLabel.textContent = '3D View';
            toggleBtn.querySelector('i').className = 'bi bi-box-fill';
            if (typeof landownerMap !== 'undefined' && landownerMap) {
                landownerMap.invalidateSize();
            }
        }
    }
</script>

<script src="<?php echo $base_path; ?>assets/js/sheet_slider.js"></script>
<script src="<?php echo $base_path; ?>assets/js/map3d_engine.js"></script>

<?php include '../includes/footer.php'; ?>