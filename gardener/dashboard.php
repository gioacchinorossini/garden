<?php
$base_path = '../';
$page_title = "Gardens & Plots Map";
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';

if (session_status() == PHP_SESSION_NONE)
    session_start();
$_SESSION['active_role'] = 'gardener';
if (!isset($_SESSION['user_name']))
    $_SESSION['user_name'] = 'Mary Gardener';

if (!isset($_SESSION['mock_lands'])) {
    $_SESSION['mock_lands'] = [
        ['id' => 1, 'title' => 'Sunnyvale Gardening Lot', 'landowner' => 'John Landowner', 'address' => '124 Green Ave, Sunnyvale', 'latitude' => 14.5995, 'longitude' => 120.9842, 'area' => 250.00, 'status' => 'approved', 'reason' => '', 'description' => 'A spacious lot with fertile soil and partial shade, perfect for root vegetables like carrots and potatoes.', 'crops' => ['Root Vegetables', 'Tuber Crops']],
        ['id' => 2, 'title' => 'Downtown Rooftop Garden', 'landowner' => 'John Landowner', 'address' => '45 Main St, Business District', 'latitude' => 14.6010, 'longitude' => 120.9890, 'area' => 85.50, 'status' => 'approved', 'reason' => '', 'description' => 'An elevated deck prepared with planters and drip irrigation, ideal for leafy greens and culinary herbs.', 'crops' => ['Leafy Greens', 'Herbs']],
        ['id' => 3, 'title' => 'Riverdale Acres', 'landowner' => 'Robert Johnson', 'address' => 'Riverside Dr, Block B', 'latitude' => 14.5950, 'longitude' => 120.9780, 'area' => 500.00, 'status' => 'approved', 'reason' => '', 'description' => 'Large idle pasture near the riverbed. High soil quality, direct sunlight access. Excellent for fruits, tomatoes and legumes.', 'crops' => ['Fruits', 'Legumes', 'Leafy Greens']],
        ['id' => 4, 'title' => 'Eastside Clay Meadows', 'landowner' => 'Sarah Connor', 'address' => '789 East Blvd, Clay District', 'latitude' => 14.6120, 'longitude' => 121.0020, 'area' => 180.00, 'status' => 'approved', 'reason' => '', 'description' => 'Rich heavy clay loam soil retaining moisture well. Best suited for cabbage, broccoli, and tuber crops.', 'crops' => ['Cruciferous', 'Tuber Crops']],
    ];
}
if (!isset($_SESSION['mock_plots'])) {
    $_SESSION['mock_plots'] = [
        ['id' => 1, 'land_id' => 2, 'plot_number' => 'Plot A-1', 'area' => 20.0, 'status' => 'occupied', 'crop' => 'Tomato', 'crop_icon' => 'tomato/tomato.svg'],
        ['id' => 2, 'land_id' => 2, 'plot_number' => 'Plot A-2', 'area' => 20.0, 'status' => 'occupied', 'crop' => 'Romaine', 'crop_icon' => 'romaine/romaine.svg'],
        ['id' => 3, 'land_id' => 2, 'plot_number' => 'Plot B-1', 'area' => 22.0, 'status' => 'available', 'crop' => 'Carrot', 'crop_icon' => 'carrot/carrot.svg'],
        ['id' => 4, 'land_id' => 2, 'plot_number' => 'Plot B-2', 'area' => 23.5, 'status' => 'available', 'crop' => 'Basil', 'crop_icon' => 'basil/basil.svg'],
        ['id' => 5, 'land_id' => 3, 'plot_number' => 'Plot R-1', 'area' => 100.0, 'status' => 'available', 'crop' => 'Strawberry', 'crop_icon' => 'strawberry/strawberry.svg'],
        ['id' => 6, 'land_id' => 4, 'plot_number' => 'Plot E-1', 'area' => 90.0, 'status' => 'available', 'crop' => 'Broccoli', 'crop_icon' => 'broccoli/broccoli.svg'],
        ['id' => 7, 'land_id' => 4, 'plot_number' => 'Plot E-2', 'area' => 90.0, 'status' => 'available', 'crop' => 'Corn', 'crop_icon' => 'corn/corn.svg'],
    ];
}

$lands_data = $_SESSION['mock_lands'];
$plots_data = $_SESSION['mock_plots'];
foreach ($lands_data as &$land) {
    $lp = array_filter($plots_data, fn($p) => $p['land_id'] == $land['id']);
    $land['total_plots'] = count($lp);
    $land['occupied_plots'] = count(array_filter($lp, fn($p) => $p['status'] === 'occupied'));
    $land['available_plots'] = count(array_filter($lp, fn($p) => $p['status'] === 'available'));
}
unset($land);
?>

<style>
    /* Filter chips positioned at top of map on desktop */
    .mobile-map-chips-bar {
        top: 14px !important;
    }

    .leaflet-top.leaflet-right {
        top: 60px !important;
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

        .workspace-surface.gardener-map-page {
            height: 100vh !important;
            border-radius: 0 !important;
            border: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .mobile-map-chips-bar {
            top: 64px !important;
        }

        .leaflet-top.leaflet-right {
            top: 110px !important;
        }
    }
</style>

<main class="workspace-surface gardener-map-page d-flex flex-column overflow-hidden h-100">

    <!-- Desktop Toolbar -->
    <div class="toolbar border-bottom d-none d-md-flex align-items-center justify-content-between px-4 py-2.5 bg-white flex-shrink-0">
        <h1 class="fs-5 fw-semibold m-0 text-dark d-flex align-items-center gap-2">
            <i data-lucide="map-pin" class="text-success" style="width:20px;height:20px;"></i>
            Gardens & Plots Map
        </h1>
        <div class="d-flex align-items-center gap-2">
            <a href="browse.php" class="btn btn-drive-primary btn-sm px-4 d-flex align-items-center gap-2 rounded-pill">
                <i data-lucide="search" style="width:15px;height:15px;"></i> Browse All Lands
            </a>
        </div>
    </div>

    <!-- Map Container -->
    <div class="flex-grow-1 position-relative overflow-hidden w-100 h-100" style="min-height:0;">
        <div class="landowner-map-wrapper">

            <!-- Floating Brand Overlay (Mobile Only) -->
            <div class="floating-map-header d-md-none">
                <div class="d-flex align-items-center gap-2">
                    <img src="<?php echo $base_path; ?>logo.jpeg" alt="IdleLand Logo" class="rounded-circle shadow-sm" style="width:32px;height:32px;object-fit:cover;">
                    <div>
                        <div class="fw-bold text-dark font-['Outfit'] d-flex align-items-center gap-1.5" style="font-size:0.92rem;line-height:1;">
                            <span class="text-success">Idle</span>Land
                            <span class="badge bg-success-subtle text-success rounded-pill px-2 py-0.5" style="font-size:0.68rem;font-weight:600;">Gardener Map</span>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <a href="browse.php" class="btn btn-drive-primary btn-sm rounded-pill px-3 py-1.5 text-xs d-flex align-items-center gap-1 shadow-sm">
                        <i data-lucide="search" style="width:14px;height:14px;"></i>
                        <span>Browse</span>
                    </a>
                </div>
            </div>

            <!-- Filter chips -->
            <div class="mobile-map-chips-bar d-flex align-items-center gap-2" id="mapChipsBar">
                <button type="button" class="map-chip active" onclick="filterGardenerMapLands('all',this)">
                    All Gardens (<?php echo count($lands_data); ?>)
                </button>
                <button type="button" class="map-chip" onclick="filterGardenerMapLands('available',this)">
                    <i class="bi bi-check-circle-fill me-1" style="color:#198754;"></i>Available Plots
                </button>
                <button type="button" class="map-chip" onclick="filterGardenerMapLands('my',this)">
                    <i class="bi bi-heart-fill me-1" style="color:#e8a000;"></i>My Leased
                </button>

                <!-- Crop Filter Dropdown -->
                <div class="dropdown d-inline-block">
                    <button class="map-chip dropdown-toggle d-flex align-items-center gap-1.5 border-0" type="button" id="cropFilterDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="border-radius:20px;padding:5px 12px;background:#fff;font-weight:500;box-shadow: 0 2px 6px rgba(0,0,0,0.1);">
                        <img id="selectedCropFilterIcon" src="<?php echo $base_path; ?>assets/crop-icons/generic-plant/generic-plant.svg" style="width:16px;height:16px;object-fit:contain;">
                        <span id="selectedCropFilterLabel">All Crops</span>
                    </button>
                    <ul class="dropdown-menu shadow-lg border-0 rounded-4 p-2" aria-labelledby="cropFilterDropdown" style="max-height: 280px; overflow-y: auto; min-width: 190px; font-size: 0.82rem; z-index: 1050;">
                        <li><a class="dropdown-item rounded-3 py-1.5 px-3 d-flex align-items-center gap-2 active" href="#" onclick="selectCropFilter('all', 'All Crops', 'generic-plant/generic-plant.svg', this)">
                            <img src="<?php echo $base_path; ?>assets/crop-icons/generic-plant/generic-plant.svg" style="width:16px;height:16px;"> <span>All Crops</span>
                        </a></li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li><a class="dropdown-item rounded-3 py-1.5 px-3 d-flex align-items-center gap-2" href="#" onclick="selectCropFilter('tomato', 'Tomato', 'tomato/tomato.svg', this)">
                            <img src="<?php echo $base_path; ?>assets/crop-icons/tomato/tomato.svg" style="width:16px;height:16px;"> <span>Tomato</span>
                        </a></li>
                        <li><a class="dropdown-item rounded-3 py-1.5 px-3 d-flex align-items-center gap-2" href="#" onclick="selectCropFilter('lettuce', 'Lettuce / Greens', 'romaine/romaine.svg', this)">
                            <img src="<?php echo $base_path; ?>assets/crop-icons/romaine/romaine.svg" style="width:16px;height:16px;"> <span>Lettuce / Greens</span>
                        </a></li>
                        <li><a class="dropdown-item rounded-3 py-1.5 px-3 d-flex align-items-center gap-2" href="#" onclick="selectCropFilter('herbs', 'Herbs / Basil', 'basil/basil.svg', this)">
                            <img src="<?php echo $base_path; ?>assets/crop-icons/basil/basil.svg" style="width:16px;height:16px;"> <span>Herbs / Basil</span>
                        </a></li>
                        <li><a class="dropdown-item rounded-3 py-1.5 px-3 d-flex align-items-center gap-2" href="#" onclick="selectCropFilter('carrot', 'Carrot / Root Vegs', 'carrot/carrot.svg', this)">
                            <img src="<?php echo $base_path; ?>assets/crop-icons/carrot/carrot.svg" style="width:16px;height:16px;"> <span>Carrot / Root Vegs</span>
                        </a></li>
                        <li><a class="dropdown-item rounded-3 py-1.5 px-3 d-flex align-items-center gap-2" href="#" onclick="selectCropFilter('potato', 'Potato / Tubers', 'russet-potato/russet-potato.svg', this)">
                            <img src="<?php echo $base_path; ?>assets/crop-icons/russet-potato/russet-potato.svg" style="width:16px;height:16px;"> <span>Potato / Tubers</span>
                        </a></li>
                        <li><a class="dropdown-item rounded-3 py-1.5 px-3 d-flex align-items-center gap-2" href="#" onclick="selectCropFilter('pepper', 'Pepper', 'red-bell-pepper/red-bell-pepper.svg', this)">
                            <img src="<?php echo $base_path; ?>assets/crop-icons/red-bell-pepper/red-bell-pepper.svg" style="width:16px;height:16px;"> <span>Pepper</span>
                        </a></li>
                        <li><a class="dropdown-item rounded-3 py-1.5 px-3 d-flex align-items-center gap-2" href="#" onclick="selectCropFilter('eggplant', 'Eggplant', 'eggplant/eggplant.svg', this)">
                            <img src="<?php echo $base_path; ?>assets/crop-icons/eggplant/eggplant.svg" style="width:16px;height:16px;"> <span>Eggplant</span>
                        </a></li>
                        <li><a class="dropdown-item rounded-3 py-1.5 px-3 d-flex align-items-center gap-2" href="#" onclick="selectCropFilter('cucumber', 'Cucumber', 'cucumber/cucumber.svg', this)">
                            <img src="<?php echo $base_path; ?>assets/crop-icons/cucumber/cucumber.svg" style="width:16px;height:16px;"> <span>Cucumber</span>
                        </a></li>
                        <li><a class="dropdown-item rounded-3 py-1.5 px-3 d-flex align-items-center gap-2" href="#" onclick="selectCropFilter('spinach', 'Spinach', 'spinach/spinach.svg', this)">
                            <img src="<?php echo $base_path; ?>assets/crop-icons/spinach/spinach.svg" style="width:16px;height:16px;"> <span>Spinach</span>
                        </a></li>
                        <li><a class="dropdown-item rounded-3 py-1.5 px-3 d-flex align-items-center gap-2" href="#" onclick="selectCropFilter('beans', 'Beans / Legumes', 'broad-bean/broad-bean.svg', this)">
                            <img src="<?php echo $base_path; ?>assets/crop-icons/broad-bean/broad-bean.svg" style="width:16px;height:16px;"> <span>Beans / Legumes</span>
                        </a></li>
                        <li><a class="dropdown-item rounded-3 py-1.5 px-3 d-flex align-items-center gap-2" href="#" onclick="selectCropFilter('corn', 'Corn', 'corn/corn.svg', this)">
                            <img src="<?php echo $base_path; ?>assets/crop-icons/corn/corn.svg" style="width:16px;height:16px;"> <span>Corn</span>
                        </a></li>
                        <li><a class="dropdown-item rounded-3 py-1.5 px-3 d-flex align-items-center gap-2" href="#" onclick="selectCropFilter('strawberry', 'Fruits / Berries', 'strawberry/strawberry.svg', this)">
                            <img src="<?php echo $base_path; ?>assets/crop-icons/strawberry/strawberry.svg" style="width:16px;height:16px;"> <span>Fruits / Berries</span>
                        </a></li>
                    </ul>
                </div>
            </div>

            <!-- Leaflet Map -->
            <div id="gardenerMainMap" style="width:100%;height:100%;min-height:450px;z-index:1;"></div>

            <!-- Desktop FAB stack (right side) -->
            <div class="position-absolute d-flex flex-column gap-2" style="bottom:28px;right:16px;z-index:1001;">
                <button type="button" class="map-fab-btn" onclick="recenterGardenerMap()" title="Fit all gardens">
                    <i data-lucide="locate" style="width:19px;height:19px;" class="text-success"></i>
                </button>
            </div>

            <!-- Backdrop (mobile, dims map behind sheet) -->
            <div class="map-sheet-backdrop" id="mapSheetBackdrop" onclick="closeLandCardSheet()"></div>

            <!-- Bottom Sheet -->
            <div id="mobileLandCardSheet" class="mobile-land-sheet hidden">
                <!-- Color status strip -->
                <div id="sheetStatusStrip" class="sheet-status-strip" style="background:#198754;"></div>

                <!-- Drag handle -->
                <div class="sheet-drag-handle"></div>

                <!-- Header row -->
                <div class="d-flex align-items-start justify-content-between mb-2 px-1">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span id="sheetStatusBadge" class="badge rounded-pill text-white px-2 py-1"
                                style="font-size:0.68rem;background:#198754;">Available</span>
                            <span id="sheetOwnerBadge" class="badge rounded-pill border text-secondary px-2 py-1"
                                style="font-size:0.68rem;background:#f8f9fa;">Landowner</span>
                        </div>
                        <h3 id="sheetLandTitle" class="fw-bold text-dark mb-0" style="font-size:1rem;line-height:1.2;">
                            Land Title</h3>
                        <p id="sheetLandAddress" class="text-secondary mb-0 d-flex align-items-center gap-1 mt-1"
                            style="font-size:0.75rem;">
                            <i data-lucide="map-pin" style="width:12px;height:12px;"
                                class="text-success flex-shrink-0"></i>
                            Address here
                        </p>
                    </div>
                    <div class="d-flex align-items-center gap-1.5 ms-2 flex-shrink-0">
                        <button type="button" class="btn btn-sm btn-light border rounded-circle p-0 d-flex align-items-center justify-content-center shadow-xs"
                            style="width:30px;height:30px;" onclick="navigateGardenCard(-1)" title="Previous Garden">
                            <i data-lucide="chevron-left" style="width:16px;height:16px;" class="text-dark"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-light border rounded-circle p-0 d-flex align-items-center justify-content-center shadow-xs"
                            style="width:30px;height:30px;" onclick="navigateGardenCard(1)" title="Next Garden">
                            <i data-lucide="chevron-right" style="width:16px;height:16px;" class="text-dark"></i>
                        </button>
                        <button type="button" class="btn-close ms-1" style="font-size:0.7rem;"
                            onclick="closeLandCardSheet()"></button>
                    </div>
                </div>

                <!-- Stat tiles -->
                <div class="d-flex gap-2 mb-2 mt-1">
                    <div class="sheet-stat-tile">
                        <span class="stat-label">Area</span>
                        <span id="sheetLandArea" class="stat-value">— m²</span>
                    </div>
                    <div class="sheet-stat-tile">
                        <span class="stat-label">Plots</span>
                        <span id="sheetPlotsCount" class="stat-value" style="color:#198754;">— / —</span>
                    </div>
                </div>

                <!-- Permitted Crops Section with Icons -->
                <div class="mb-2">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="text-secondary fw-semibold" style="font-size:0.68rem;letter-spacing:0.5px;text-transform:uppercase;">Permitted Crops</span>
                    </div>
                    <div id="sheetPermittedCropsContainer" class="d-flex flex-wrap gap-1.5 align-items-center"></div>
                </div>

                <!-- Description -->
                <p id="sheetLandDesc" class="text-secondary mb-2"
                    style="font-size:0.78rem;line-height:1.5;max-height:48px;overflow:hidden;"></p>

                <!-- Partition Plots Grid -->
                <div class="mb-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-secondary fw-semibold" style="font-size:0.7rem;letter-spacing:0.5px;text-transform:uppercase;">Partition Plots Grid</span>
                        <span id="sheetPlotsSummary" class="badge bg-success-subtle text-success rounded-pill px-2 py-0.5" style="font-size:0.65rem;"></span>
                    </div>
                    <div id="sheetPlotsGrid" class="row g-2" style="max-height: 140px; overflow-y: auto;"></div>
                </div>

                <!-- CTA buttons for Gardener -->
                <div class="d-flex gap-2">
                    <a id="sheetBrowseBtn" href="browse.php"
                        class="btn btn-drive-primary btn-sm rounded-pill flex-grow-1 d-flex align-items-center justify-content-center gap-1"
                        style="padding:10px;">
                        <i data-lucide="shopping-bag" style="width:14px;height:14px;"></i> Rent a Plot
                    </a>
                    <a href="schedules.php"
                        class="btn btn-drive-secondary btn-sm rounded-pill d-flex align-items-center gap-1 px-3"
                        style="padding:10px;">
                        <i data-lucide="calendar" style="width:14px;height:14px;"></i>
                        <span class="d-none d-sm-inline">Schedules</span>
                    </a>
                    <a href="harvests.php" class="btn btn-sm rounded-pill d-flex align-items-center gap-1 px-3"
                        style="padding:10px;background:#f0fdf4;border:1px solid #c3e6cb;color:#198754;" title="Harvests">
                        <i data-lucide="sprout" style="width:14px;height:14px;"></i>
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
    let gardenerMap = null;
    let mapMarkers = [];
    let activePlotLayers = [];

    function navigateGardenCard(direction) {
        if (!landsData || landsData.length === 0) return;
        let currentIndex = landsData.findIndex(l => l.id == currentLandId);
        if (currentIndex === -1) currentIndex = 0;

        let nextIndex = (currentIndex + direction + landsData.length) % landsData.length;
        openLandCardSheet(landsData[nextIndex]);
    }

    function focusOnSpecificPlot(landId, plotId) {
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

        const activeMap = gardenerMap;
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

    document.addEventListener('DOMContentLoaded', initGardenerMap);

    function initGardenerMap() {
        const el = document.getElementById('gardenerMainMap');
        if (!el) return;

        gardenerMap = L.map('gardenerMainMap', { zoomControl: false })
            .setView([14.6010, 120.9890], 13);

        L.control.zoom({ position: 'topright' }).addTo(gardenerMap);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 21,
            maxNativeZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(gardenerMap);

        renderGardenerMapPins(landsData);

        // Check if URL has ?id=X parameter
        const urlParams = new URLSearchParams(window.location.search);
        const paramId = urlParams.get('id');
        if (paramId) {
            const targetLand = landsData.find(l => l.id == paramId);
            if (targetLand) {
                setTimeout(() => openLandCardSheet(targetLand), 300);
            }
        }

        gardenerMap.on('click', closeLandCardSheet);
    }

    function renderGardenerMapPins(list) {
        if (!gardenerMap) return;

        mapMarkers.forEach(m => gardenerMap.removeLayer(m));
        mapMarkers = [];
        if (activePlotLayers) {
            activePlotLayers.forEach(l => gardenerMap.removeLayer(l));
        }
        activePlotLayers = [];

        const bounds = [];

        list.forEach(land => {
            const lat = parseFloat(land.latitude);
            const lng = parseFloat(land.longitude);
            if (isNaN(lat) || isNaN(lng)) return;
            bounds.push([lat, lng]);

            const pinColor = '#198754';

            // 1. Garden Square Perimeter
            const totalArea = parseFloat(land.area) || 100;
            const sideMeters = Math.sqrt(totalArea);
            const halfSide = sideMeters / 2;
            const deltaLat = halfSide / 111320;
            const deltaLng = halfSide / (111320 * Math.cos(lat * Math.PI / 180));

            const boundsSquare = [
                [lat + deltaLat, lng - deltaLng],
                [lat + deltaLat, lng + deltaLng],
                [lat - deltaLat, lng + deltaLng],
                [lat - deltaLat, lng - deltaLng]
            ];

            const gardenSquare = L.polygon(boundsSquare, {
                color: pinColor,
                weight: 2,
                fillColor: pinColor,
                fillOpacity: 0.12,
                dashArray: '5, 5'
            }).addTo(gardenerMap);
            mapMarkers.push(gardenSquare);

            // 2. Main Garden Pin with Plant Icon Badge support
            const activeCropIcon = selectedCropFilterVal !== 'all' 
                ? getCropIconPath(selectedCropFilterVal)
                : (land.crops && land.crops[0] ? getCropIconPath(land.crops[0]) : null);

            const cropPinContent = activeCropIcon
                ? `<img src="${basePath}assets/crop-icons/${activeCropIcon}" style="width:24px;height:24px;object-fit:contain;background:#fff;border-radius:50%;padding:2px;box-shadow: 0 2px 6px rgba(0,0,0,0.3);" alt="Crop Icon">`
                : `<i class="bi bi-tree-fill"></i>`;

            const icon = L.divIcon({
                className: '',
                html: `<div class="land-map-pin d-flex align-items-center justify-content-center" style="background:${pinColor};width:42px;height:42px;border-radius:50%;border:3px solid #fff;box-shadow: 0 4px 12px rgba(0,0,0,0.25);cursor:pointer;">
                       ${cropPinContent}
                   </div>`,
                iconSize: [42, 42],
                iconAnchor: [21, 21]
            });

            const marker = L.marker([lat, lng], { icon }).addTo(gardenerMap);
            marker.on('click', () => openLandCardSheet(land));
            mapMarkers.push(marker);

            // 3. Partition Plots Square Grid on Dashboard Map
            const landPlots = plotsData.filter(p => p.land_id == land.id);
            if (landPlots.length > 0) {
                const N = landPlots.length;
                const cols = Math.ceil(Math.sqrt(N));
                const rows = Math.ceil(N / cols);
                const plotW = (deltaLng * 2) / cols;
                const plotH = (deltaLat * 2) / rows;

                landPlots.forEach((p, idx) => {
                    const c = idx % cols;
                    const r = Math.floor(idx / cols);

                    const pMinLat = (lat + deltaLat) - ((r + 1) * plotH);
                    const pMaxLat = (lat + deltaLat) - (r * plotH);
                    const pMinLng = (lng - deltaLng) + (c * plotW);
                    const pMaxLng = (lng - deltaLng) + ((c + 1) * plotW);

                    const plotSquare = [
                        [pMaxLat, pMinLng],
                        [pMaxLat, pMaxLng],
                        [pMinLat, pMaxLng],
                        [pMinLat, pMinLng]
                    ];

                    const isAvail = p.status === 'available';
                    const isOccupied = p.status === 'occupied';
                    const plotColor = isAvail ? '#198754' : (isOccupied ? '#0d6efd' : '#6c757d');
                    const plotFill = isAvail ? '#25c974' : (isOccupied ? '#3d8bfd' : '#adb5bd');

                    const plotPoly = L.polygon(plotSquare, {
                        color: plotColor,
                        weight: 1.5,
                        fillColor: plotFill,
                        fillOpacity: 0.28
                    }).addTo(gardenerMap);

                    plotPoly.bindTooltip(
                        `<div style="font-family:'Outfit',sans-serif;font-size:11px;">
                            <strong>${p.plot_number}</strong> (${p.area} m²)<br>
                            <span class="text-secondary">${land.title}</span><br>
                            <span class="badge ${isAvail ? 'bg-success' : 'bg-primary'}" style="font-size:9px;margin-top:2px;">${p.status}</span>
                        </div>`,
                        { permanent: false, direction: 'center' }
                    );
                    plotPoly.on('click', () => openLandCardSheet(land));
                    mapMarkers.push(plotPoly);
                });
            }
        });

        if (bounds.length) {
            gardenerMap.fitBounds(bounds, { padding: [80, 80], maxZoom: 16 });
        }
    }

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

    function openLandCardSheet(land) {
        currentLandId = land.id;
        const sheet = document.getElementById('mobileLandCardSheet');
        const backdrop = document.getElementById('mapSheetBackdrop');
        if (!sheet) return;

        // Ultra high detail zoom level 20 animation
        const lat = parseFloat(land.latitude);
        const lng = parseFloat(land.longitude);
        const targetZoom = 20;

        if (!isNaN(lat) && !isNaN(lng) && gardenerMap) {
            const isMobile = window.innerWidth <= 768;
            const targetPoint = gardenerMap.project([lat, lng], targetZoom);

            if (isMobile) {
                const sheetHeight = sheet ? sheet.offsetHeight : 340;
                targetPoint.y += (sheetHeight / 2) + 30;
            } else {
                targetPoint.x -= 220;
                targetPoint.y += 40;
            }

            const offsetTarget = gardenerMap.unproject(targetPoint, targetZoom);

            gardenerMap.flyTo(offsetTarget, targetZoom, {
                animate: true,
                duration: 1.2
            });
        }

        document.getElementById('sheetStatusStrip').style.background = '#198754';
        document.getElementById('sheetStatusBadge').textContent = `${land.available_plots ?? 0} Plots Available`;
        document.getElementById('sheetStatusBadge').style.background = '#198754';
        document.getElementById('sheetStatusBadge').style.color = '#fff';
        document.getElementById('sheetOwnerBadge').textContent = land.landowner || 'Landowner';
        document.getElementById('sheetLandTitle').textContent = land.title;
        document.getElementById('sheetLandAddress').innerHTML =
            `<i data-lucide="map-pin" style="width:12px;height:12px;" class="text-success flex-shrink-0"></i> ${land.address}`;
        document.getElementById('sheetLandArea').textContent = `${parseFloat(land.area).toFixed(0)} m²`;
        document.getElementById('sheetPlotsCount').textContent = `${land.occupied_plots ?? 0} / ${land.total_plots ?? 0}`;

        // Render Permitted Crops as SVG icons inside sheet
        const rawCrops = Array.isArray(land.crops) ? land.crops : (land.allowed_seeds || []);
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
        document.getElementById('sheetBrowseBtn').href = `browse.php?id=${land.id}`;

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
                    const cropImg = p.crop_icon
                        ? `<img src="${basePath}assets/crop-icons/${p.crop_icon}" style="width:18px;height:18px;object-fit:contain;" alt="${p.crop || 'Plant'}">`
                        : `<i class="bi bi-sprout-fill text-success" style="font-size:12px;"></i>`;

                    return `
                        <div class="col-6 col-sm-4" onclick="focusOnSpecificPlot(${land.id}, ${p.id})" style="cursor:pointer;" title="${badgeText}">
                            <div class="p-2 rounded-3 border ${bgClass} d-flex flex-column justify-content-between h-100 shadow-xs hover-elevate transition-all">
                                <div class="fw-bold d-flex align-items-center justify-content-between">
                                    <span class="d-flex align-items-center gap-1">
                                        ${cropImg}
                                        <span style="font-size:0.75rem;">${p.plot_number}</span>
                                    </span>
                                    ${lockIcon}
                                </div>
                                <div class="mt-1 d-flex justify-content-between align-items-center text-muted" style="font-size:0.68rem;">
                                    <span>${parseFloat(p.area).toFixed(0)} m²</span>
                                    <span class="fw-medium text-capitalize">${p.crop ? p.crop : badgeText}</span>
                                </div>
                            </div>
                        </div>
                    `;
                }).join('');
            }
        }

        // Draw High Detail Plot Grid Polygons on Leaflet map
        if (activePlotLayers) {
            activePlotLayers.forEach(l => gardenerMap.removeLayer(l));
        }
        activePlotLayers = [];

        if (landPlots.length > 0 && !isNaN(lat) && !isNaN(lng) && gardenerMap) {
            const totalArea = parseFloat(land.area) || 100;
            const sideMeters = Math.sqrt(totalArea);
            const halfSide = sideMeters / 2;
            const deltaLat = halfSide / 111320;
            const deltaLng = halfSide / (111320 * Math.cos(lat * Math.PI / 180));

            // Outer boundary square polygon
            const boundsSquare = [
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
            }).addTo(gardenerMap);
            activePlotLayers.push(outerSquare);

            // Sub-partition square plot grid polygons
            const N = landPlots.length;
            const cols = Math.ceil(Math.sqrt(N));
            const rows = Math.ceil(N / cols);
            const plotW = (deltaLng * 2) / cols;
            const plotH = (deltaLat * 2) / rows;

            landPlots.forEach((p, idx) => {
                const c = idx % cols;
                const r = Math.floor(idx / cols);

                const pMinLat = (lat + deltaLat) - ((r + 1) * plotH);
                const pMaxLat = (lat + deltaLat) - (r * plotH);
                const pMinLng = (lng - deltaLng) + (c * plotW);
                const pMaxLng = (lng - deltaLng) + ((c + 1) * plotW);

                const plotSquare = [
                    [pMaxLat, pMinLng],
                    [pMaxLat, pMaxLng],
                    [pMinLat, pMaxLng],
                    [pMinLat, pMinLng]
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
                }).addTo(gardenerMap);

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
                const pCenterLat = (pMinLat + pMaxLat) / 2;
                const pCenterLng = (pMinLng + pMaxLng) / 2;

                const cropImgTag = p.crop_icon
                    ? `<img src="${basePath}assets/crop-icons/${p.crop_icon}" style="width:16px;height:16px;object-fit:contain;background:#fff;border-radius:50%;padding:1px;" alt="${p.crop}">`
                    : `<i class="bi bi-sprout-fill" style="color:#fff;font-size:11px;"></i>`;

                const lockIconTag = isOccupied ? `<i class="bi bi-lock-fill" style="font-size:9px;color:#ffc107;"></i>` : '';

                const plotTagIcon = L.divIcon({
                    className: '',
                    html: `<div class="shadow-sm px-2 py-1 rounded-pill fw-bold text-white text-center d-flex align-items-center gap-1.5" 
                        style="background:${plotColor};font-size:9.5px;border:1.5px solid #fff;white-space:nowrap;backdrop-filter:blur(4px);cursor:pointer;">
                        ${cropImgTag}
                        <span>${p.plot_number}</span>
                        ${lockIconTag}
                    </div>`,
                    iconSize: [92, 26],
                    iconAnchor: [46, 13]
                });

                const plotTagMarker = L.marker([pCenterLat, pCenterLng], { icon: plotTagIcon }).addTo(gardenerMap);
                plotTagMarker.on('click', () => focusOnSpecificPlot(land.id, p.id));
                activePlotLayers.push(plotTagMarker);
            });
        }

        sheet.classList.remove('hidden');
        if (backdrop) backdrop.classList.add('show');
        if (typeof lucide !== 'undefined') lucide.createIcons();
    }

    function closeLandCardSheet() {
        const sheet = document.getElementById('mobileLandCardSheet');
        const backdrop = document.getElementById('mapSheetBackdrop');
        if (sheet) sheet.classList.add('hidden');
        if (backdrop) backdrop.classList.remove('show');

        if (activePlotLayers) {
            activePlotLayers.forEach(l => gardenerMap.removeLayer(l));
            activePlotLayers = [];
        }
    }

    let currentGardenerFilterType = 'all';
    let selectedCropFilterVal = 'all';

    function selectCropFilter(val, label, iconRelPath, el) {
        selectedCropFilterVal = val;
        document.getElementById('selectedCropFilterLabel').textContent = label;
        document.getElementById('selectedCropFilterIcon').src = basePath + 'assets/crop-icons/' + iconRelPath;

        const parentMenu = el.closest('.dropdown-menu');
        if (parentMenu) {
            parentMenu.querySelectorAll('.dropdown-item').forEach(item => item.classList.remove('active'));
            el.classList.add('active');
        }

        applyCombinedGardenerMapFilters();
    }

    function recenterGardenerMap() {
        applyCombinedGardenerMapFilters();
        closeLandCardSheet();
    }

    function filterGardenerMapLands(type, btn) {
        document.querySelectorAll('#mapChipsBar > .map-chip').forEach(c => c.classList.remove('active'));
        if (btn) btn.classList.add('active');
        currentGardenerFilterType = type;
        applyCombinedGardenerMapFilters();
        closeLandCardSheet();
    }

    function applyCombinedGardenerMapFilters() {
        let list = landsData;
        if (currentGardenerFilterType === 'available') {
            list = list.filter(l => (l.available_plots ?? 0) > 0);
        } else if (currentGardenerFilterType === 'my') {
            list = list.filter(l => (l.occupied_plots ?? 0) > 0);
        }

        if (selectedCropFilterVal !== 'all') {
            list = list.filter(land => {
                const rawCrops = Array.isArray(land.crops) ? land.crops : (land.allowed_seeds || []);
                const landPlots = plotsData.filter(p => p.land_id == land.id);
                const plotCrops = landPlots.map(p => p.crop).filter(Boolean);
                
                const allCrops = [...rawCrops, ...plotCrops].map(c => c.toLowerCase());
                return allCrops.some(c => c.includes(selectedCropFilterVal.toLowerCase()) || selectedCropFilterVal.toLowerCase().includes(c));
            });
        }

        renderGardenerMapPins(list);
    }
</script>

<?php include '../includes/footer.php'; ?>