<?php
$base_path = '../';
$page_title = "Farm & Partition Plots";
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
?>

<style>
/* Cozy Farm Theme Styling */
.farm-container {
    font-family: 'Outfit', 'Quicksand', sans-serif;
}

.workspace-surface.farm-container {
    overflow: hidden !important;
    height: 100% !important;
    flex: 1 !important;
    display: flex !important;
    flex-direction: column !important;
}

.workspace-surface.farm-container .workspace-scroll {
    flex: 1 !important;
    overflow-y: auto !important;
    overflow-x: hidden !important;
    height: 100% !important;
    -webkit-overflow-scrolling: touch;
}

.farm-card {
    background: #FFFDF9;
    border: 2px solid #523E31;
    border-radius: 20px;
    box-shadow: 0 4px 0 #523E31;
    transition: transform 0.15s ease, box-shadow 0.15s ease;
}

.farm-badge-pill {
    background: #FFFFFF;
    color: #3E2B1E;
    border: 1.5px solid #523E31;
    border-radius: 9999px;
    box-shadow: 0 2px 0 #523E31;
    font-weight: 700;
    font-size: 0.78rem;
    padding: 4px 12px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.farm-btn {
    font-weight: 700;
    border: 2px solid #523E31 !important;
    border-radius: 9999px !important;
    box-shadow: 0 2.5px 0 #523E31 !important;
    transition: all 0.15s ease;
}
.farm-btn:active {
    transform: translateY(2px);
    box-shadow: 0 0.5px 0 #523E31 !important;
}

.farm-btn-primary {
    background: #7FA668 !important;
    color: #FFFDF9 !important;
}
.farm-btn-primary:hover {
    background: #71975B !important;
    color: #FFFDF9 !important;
}

.farm-btn-secondary {
    background: #FFFDF9 !important;
    color: #3E2B1E !important;
}
.farm-btn-secondary:hover {
    background: #EAE1D2 !important;
    color: #3E2B1E !important;
}

/* Filter Chips */
.plot-filter-chip {
    border: 1.5px solid #DFCFC2 !important;
    font-size: 0.78rem !important;
    font-weight: 700 !important;
    padding: 5px 14px !important;
    border-radius: 9999px !important;
    color: #6B5446 !important;
    background: #FFFDF9 !important;
    transition: all 0.15s ease !important;
    cursor: pointer;
    line-height: 1.2;
}
.plot-filter-chip:hover {
    background: #EAE1D2 !important;
    color: #3E2B1E !important;
    border-color: #523E31 !important;
}
.plot-filter-chip.active {
    background: #7FA668 !important;
    color: #FFFDF9 !important;
    border: 2px solid #523E31 !important;
    box-shadow: 0 2px 0 #523E31 !important;
}

/* Unified Farm Property Selector Pill */
.farm-property-pill {
    display: inline-flex;
    align-items: center;
    background: #FFFDF9 !important;
    border: 2px solid #523E31 !important;
    border-radius: 9999px !important;
    box-shadow: 0 2.5px 0 #523E31 !important;
    padding: 3px 14px 3px 12px !important;
    transition: all 0.15s ease;
}

.farm-property-select {
    border: none !important;
    background-color: transparent !important;
    box-shadow: none !important;
    border-radius: 0 !important;
    font-size: 0.85rem !important;
    font-weight: 700 !important;
    color: #3E2B1E !important;
    padding: 3px 26px 3px 4px !important;
    margin: 0 !important;
    cursor: pointer;
    appearance: none !important;
    -webkit-appearance: none !important;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23523E31' stroke-linecap='round' stroke-linejoin='round' stroke-width='2.5' d='m2 5 6 6 6-6'/%3e%3c/svg%3e") !important;
    background-repeat: no-repeat !important;
    background-position: right center !important;
    background-size: 11px 11px !important;
}
.farm-property-select:focus {
    outline: none !important;
    box-shadow: none !important;
    border: none !important;
}

/* Unified Farm Search Bar */
.farm-search-wrapper {
    position: relative;
    flex: 1;
}

.farm-search-input {
    width: 100% !important;
    background-color: #FFFDF9 !important;
    border: 2px solid #523E31 !important;
    border-radius: 9999px !important;
    box-shadow: 0 2px 0 #523E31 !important;
    font-size: 0.82rem !important;
    font-weight: 600 !important;
    color: #3E2B1E !important;
    padding: 7px 16px 7px 38px !important;
    transition: all 0.15s ease !important;
}
.farm-search-input::placeholder {
    color: #968172 !important;
    font-weight: 500 !important;
}
.farm-search-input:focus {
    outline: none !important;
    border-color: #7FA668 !important;
    box-shadow: 0 2.5px 0 #523E31, 0 0 0 3px rgba(127, 166, 104, 0.25) !important;
    background-color: #FFFFFF !important;
}

/* View Mode Switcher Buttons (Borderless, No Container) */
.view-mode-btn {
    border: none !important;
    font-size: 0.78rem !important;
    font-weight: 700 !important;
    color: #6B5446 !important;
    background: transparent !important;
    box-shadow: none !important;
    transition: all 0.15s ease !important;
}
.view-mode-btn:hover {
    color: #3E2B1E !important;
    background: #EAE1D2 !important;
}
.view-mode-btn.active {
    background: #7FA668 !important;
    color: #FFFDF9 !important;
    box-shadow: none !important;
}

/* Interactive Visual Farm Map (Borderless) */
.farm-map-canvas-container {
    position: relative;
    border-radius: 20px;
    overflow: hidden;
    border: none !important;
    box-shadow: none !important;
    background: transparent;
    width: 100%;
    max-width: 640px;
    margin: 0 auto;
}

.farm-map-image {
    width: 100%;
    height: auto;
    display: block;
    user-select: none;
    -webkit-user-drag: none;
    border: none !important;
    box-shadow: none !important;
    border-radius: 20px;
}

#cropsMapViewContainer {
    position: relative;
    overflow: hidden;
    border-radius: 20px;
    transition: height 0.25s ease;
}

/* Crop Name Pill (covers baked-in static label on crop_plots_map.jpg) */
.map-crop-pill {
    position: absolute;
    background: #FFFDF9;
    border: 2px solid #523E31;
    border-radius: 9999px;
    box-shadow: 0 2px 0 #523E31;
    padding: 3px 14px;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 0.8rem;
    font-weight: 800;
    color: #3E2B1E;
    z-index: 15;
    pointer-events: none;
    white-space: nowrap;
    transform: translateX(-50%);
    transition: all 0.15s ease;
}

.map-crop-pill img {
    width: 17px;
    height: 17px;
    object-fit: contain;
}

/* Interactive Plot Bed Hotspot on top of the image */
.map-plot-hotspot {
    position: absolute;
    cursor: pointer;
    border-radius: 18px;
    transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
    z-index: 10;
    border: 2.5px solid transparent;
}
.map-plot-hotspot:hover {
    background: rgba(255, 255, 255, 0.15);
    border-color: rgba(255, 255, 255, 0.75);
    box-shadow: 0 0 0 3px rgba(127, 166, 104, 0.45), 0 6px 14px rgba(0,0,0,0.18);
    z-index: 20;
}
.map-plot-hotspot.selected {
    background: rgba(127, 166, 104, 0.18);
    border-color: #7FA668;
    box-shadow: 0 0 0 3.5px #7FA668, 0 8px 18px rgba(0,0,0,0.22);
    z-index: 25;
}

.plot-item-card {
    background: #FFFDF9;
    border: 2px solid #523E31;
    border-radius: 16px;
    box-shadow: 0 3px 0 #523E31;
    transition: all 0.15s ease;
}
.plot-item-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 0 #523E31;
}
.plot-item-card.highlighted {
    border-color: #7FA668;
    box-shadow: 0 0 0 3px #BBD6B8, 0 5px 0 #523E31;
}

/* Crop seed badge chip */
.seed-tag {
    font-size: 0.7rem;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 9999px;
    background: #F7F2E9;
    border: 1px solid #DFCFC2;
    color: #6B5446;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
</style>

<main class="workspace-surface farm-container" style="background: #F7F2E9;">
    <div class="workspace-scroll p-3 px-md-4 pt-md-3 pb-5">
        <div class="container-fluid max-w-7xl mx-auto px-0">

        <!-- Top Breadcrumb & Navigation -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
            <div class="d-flex align-items-center gap-2">
                <a href="lands.php" class="btn btn-sm farm-btn farm-btn-secondary d-flex align-items-center gap-1.5 px-3 py-1.5" title="Back to Lands">
                    <i class="bi bi-arrow-left"></i>
                    <span>My Lands</span>
                </a>
                <span class="text-muted">/</span>
                <span class="fw-bold text-dark fs-6">Farm & Plots Management</span>
            </div>

            <!-- Property Switcher Dropdown & Actions -->
            <div class="d-flex flex-wrap align-items-center gap-2">
                <div class="farm-property-pill">
                    <i class="bi bi-geo-alt-fill text-success me-1.5" style="font-size: 0.95rem;"></i>
                    <span class="fw-bold me-1 text-muted" style="font-size: 0.78rem;">Farm:</span>
                    <select id="farmPropertySelect" class="farm-property-select">
                        <option value="">Loading lands...</option>
                    </select>
                </div>

                <button class="btn btn-sm farm-btn farm-btn-primary d-flex align-items-center gap-1.5 px-3 py-1.5" onclick="openAddPlotModal()">
                    <i class="bi bi-plus-circle-fill"></i>
                    <span>Add Partition Plot</span>
                </button>
            </div>
        </div>

        <!-- Property Hero Banner -->
        <div class="farm-card p-3 p-md-4 mb-4" id="farmOverviewBanner">
            <div class="row align-items-center g-3">
                <div class="col-lg-8">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                        <h1 class="h3 fw-bold text-dark mb-0" id="currentLandTitle">Farm Plots Overview</h1>
                        <span id="currentLandStatusBadge" class="farm-badge-pill" style="background:#EBF3E8; color:#3F6212;">
                            <i class="bi bi-shield-check"></i> Approved
                        </span>
                        <span id="currentLandTypeBadge" class="farm-badge-pill">
                            <i class="bi bi-file-earmark-text"></i> TCT Title
                        </span>
                    </div>
                    <p class="text-muted mb-2 d-flex align-items-center gap-1.5" style="font-size: 0.88rem;">
                        <i class="bi bi-pin-map text-danger"></i>
                        <span id="currentLandAddress">Loading farm location...</span>
                    </p>
                    <p class="text-secondary mb-0" id="currentLandDescription" style="font-size: 0.84rem; max-width: 650px;">
                        Interactive top-down partition map and plots management. You can add, edit, configure permitted seed varieties, and review lease statuses.
                    </p>
                </div>
                <!-- Mini Stats Chips -->
                <div class="col-lg-4">
                    <div class="row g-2 text-center">
                        <div class="col-4">
                            <div class="p-2 rounded-3 bg-white border" style="border: 1.5px solid #523E31 !important; box-shadow: 0 2px 0 #523E31;">
                                <div class="text-muted fw-bold" style="font-size: 0.65rem; text-transform: uppercase;">Total Area</div>
                                <div class="fw-bold text-dark mt-0.5" style="font-size: 0.95rem;" id="statTotalArea">0 m²</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 rounded-3 bg-white border" style="border: 1.5px solid #523E31 !important; box-shadow: 0 2px 0 #523E31;">
                                <div class="text-muted fw-bold" style="font-size: 0.65rem; text-transform: uppercase;">Total Plots</div>
                                <div class="fw-bold text-dark mt-0.5" style="font-size: 0.95rem;" id="statTotalPlots">0</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 rounded-3 bg-white border" style="border: 1.5px solid #523E31 !important; box-shadow: 0 2px 0 #523E31;">
                                <div class="text-muted fw-bold" style="font-size: 0.65rem; text-transform: uppercase;">Available</div>
                                <div class="fw-bold text-success mt-0.5" style="font-size: 0.95rem;" id="statAvailPlots">0</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content: Visual Farm Map + Plots List Section -->
        <div class="row g-4 mb-4">
            <!-- Left Column: Visual Top-Down Farm Map -->
            <div class="col-xl-5 col-lg-6">
                <div class="farm-card p-3 p-md-4 h-100 d-flex flex-column">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <div>
                            <h2 class="h5 fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                <i class="bi bi-map-fill text-success"></i>
                                <span>Visual Farm Map</span>
                            </h2>
                            <p class="text-muted mb-0 mt-0.5" style="font-size: 0.78rem;">
                                Top-down game layout view. Click any crop partition to inspect.
                            </p>
                        </div>

                        <!-- View Mode Switcher: Crops Only (Default) vs Full Homestead (Option) -->
                        <div class="d-flex align-items-center gap-1">
                            <button type="button" class="btn btn-sm rounded-pill px-3 py-1 view-mode-btn active" id="modeCropsBtn" onclick="setMapViewMode('crops')">
                                <i class="bi bi-flower1 me-1"></i> Crops Only
                            </button>
                            <button type="button" class="btn btn-sm rounded-pill px-3 py-1 view-mode-btn" id="modeHomesteadBtn" onclick="setMapViewMode('homestead')">
                                <i class="bi bi-house-door me-1"></i> Full Homestead
                            </button>
                        </div>
                    </div>

                    <!-- Top-Down Cozy Game Farm Map Canvas -->
                    <div class="farm-map-canvas-container flex-grow-1 position-relative my-auto" id="farmMapCanvas">
                        <!-- VIEW 1: CROPS ONLY (Default - Uses crop_plots_map.jpg with dynamic plots & permitted crops overlay) -->
                        <div id="cropsMapViewContainer" class="position-relative">
                            <img src="../assets/images/crop_plots_map.jpg" alt="Farm Plots Layout" class="farm-map-image" id="farmMapImage">
                            <div id="dynamicCropOverlays"></div>
                        </div>

                        <!-- VIEW 2: FULL HOMESTEAD & AMENITIES (Optional View) -->
                        <div id="homesteadViewContainer" class="d-none position-relative">
                            <img src="../assets/images/farm_plots_map.jpg" alt="Full Homestead Layout" class="farm-map-image" id="homesteadMapImage">
                            <div id="hotspotsHomestead">
                                <!-- Zone 1: House / Main Facility (Top-Left) -->
                                <div class="map-plot-hotspot" style="top: 4%; left: 5%; width: 40%; height: 27%;"
                                     onclick="selectMapZone('zone_house', 'Farmhouse & Living Quarter', 'Residential', 'Residence', 'House')"
                                     title="Farmhouse & Living Quarter"></div>
                                <!-- Zone 2: Tomatoes Plot (Top-Right) -->
                                <div class="map-plot-hotspot" style="top: 4%; left: 50%; width: 45%; height: 27%;"
                                     onclick="selectMapZone('zone_tomatoes', 'Plot A-1 (Tomatoes)', 'Tomato', '500 m²', 'Tomatoes')"
                                     title="Plot A-1 (Tomatoes)"></div>
                                <!-- Zone 3: Vegetables Plot (Middle-Left) -->
                                <div class="map-plot-hotspot" style="top: 34%; left: 5%; width: 40%; height: 25%;"
                                     onclick="selectMapZone('zone_vegetables', 'Plot A-2 (Mixed Vegetables)', 'Vegetables', '450 m²', 'Vegetables')"
                                     title="Plot A-2 (Mixed Vegetables)"></div>
                                <!-- Zone 4: Corn Plot (Middle-Right) -->
                                <div class="map-plot-hotspot" style="top: 34%; left: 50%; width: 45%; height: 25%;"
                                     onclick="selectMapZone('zone_corn', 'Plot A-3 (Sweet Corn)', 'Corn', '600 m²', 'Corn')"
                                     title="Plot A-3 (Sweet Corn)"></div>
                                <!-- Zone 5: Animals & Pasture (Bottom-Left) -->
                                <div class="map-plot-hotspot" style="top: 63%; left: 5%; width: 50%; height: 32%;"
                                     onclick="selectMapZone('zone_animals', 'Pasture & Livestock Area', 'Livestock', '1,200 m²', 'Animals')"
                                     title="Pasture & Livestock Area"></div>
                                <!-- Zone 6: Water Facility (Bottom-Right Top) -->
                                <div class="map-plot-hotspot" style="top: 63%; left: 60%; width: 35%; height: 16%;"
                                     onclick="selectMapZone('zone_water', 'Irrigation Pond & Water Tank', 'Irrigation', '350 m²', 'Water')"
                                     title="Irrigation Pond & Water Tank"></div>
                                <!-- Zone 7: Storage & Barn (Bottom-Right Bottom) -->
                                <div class="map-plot-hotspot" style="top: 80%; left: 60%; width: 35%; height: 16%;"
                                     onclick="selectMapZone('zone_storage', 'Crop Silos & Equipment Storage', 'Storage', '300 m²', 'Storage')"
                                     title="Crop Silos & Equipment Storage"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Map Selected Zone Callout Card -->
                    <div class="mt-3 p-2.5 rounded-3 border bg-white d-flex align-items-center justify-content-between" style="border: 1.5px solid #523E31 !important;" id="mapZoneCallout">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle p-2 bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width:36px; height:36px;">
                                <i class="bi bi-info-circle-fill" id="calloutIcon"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark" style="font-size: 0.85rem;" id="calloutTitle">Click any zone or plot above</div>
                                <div class="text-muted" style="font-size: 0.74rem;" id="calloutSub">Hover or tap to inspect crops & partitions</div>
                            </div>
                        </div>
                        <button class="btn btn-sm farm-btn farm-btn-secondary px-2.5 py-1 text-nowrap" style="font-size:0.75rem;" onclick="focusMatchingPlotCard()">
                            Jump to Plot <i class="bi bi-arrow-down"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Right Column: Partition Plots Management Grid -->
            <div class="col-xl-7 col-lg-6">
                <div class="farm-card p-3 p-md-4 h-100 d-flex flex-column">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <div>
                            <h2 class="h5 fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                <i class="bi bi-grid-3x3-gap-fill text-success"></i>
                                <span>Partitioned Plots</span>
                                <span class="badge rounded-pill bg-dark text-white ms-1" style="font-size:0.72rem;" id="plotsBadgeCount">0</span>
                            </h2>
                            <p class="text-muted mb-0 mt-0.5" style="font-size: 0.78rem;">
                                Manage plot numbers, lease prices, crops, and gardener assignments.
                            </p>
                        </div>

                        <!-- Filter Chips -->
                        <div class="d-flex align-items-center gap-1.5 flex-wrap">
                            <button class="plot-filter-chip active" data-filter="all" onclick="setPlotFilter('all', this)">All</button>
                            <button class="plot-filter-chip" data-filter="available" onclick="setPlotFilter('available', this)">Available</button>
                            <button class="plot-filter-chip" data-filter="occupied" onclick="setPlotFilter('occupied', this)">Occupied</button>
                            <button class="plot-filter-chip" data-filter="maintenance" onclick="setPlotFilter('maintenance', this)">Maintenance</button>
                        </div>
                    </div>

                    <!-- Search & Quick Action bar -->
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="farm-search-wrapper">
                            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" style="pointer-events: none; font-size: 0.82rem;"></i>
                            <input type="text" id="plotSearchInput" class="farm-search-input" placeholder="Search by plot #, crop, or gardener..." oninput="filterPlotsList()">
                        </div>

                        <button class="btn btn-sm farm-btn farm-btn-secondary px-3 py-1.5 d-flex align-items-center gap-1 text-nowrap" onclick="loadFarmPlotsData()" title="Refresh plots list">
                            <i class="bi bi-arrow-clockwise"></i>
                            <span class="d-none d-sm-inline">Refresh</span>
                        </button>
                    </div>

                    <!-- Plots Grid Container -->
                    <div class="flex-grow-1 pe-1" id="plotsCardsContainer">
                        <div class="text-center py-5 text-muted">
                            <div class="spinner-border spinner-border-sm text-success me-2" role="status"></div>
                            Loading farm plots...
                        </div>
                    </div>
                </div>
            </div>
        </div>

        </div>
    </div>
</main>

<!-- ================= MODALS ================= -->

<!-- 1. Add Plot Modal -->
<div class="modal fade" id="addPlotModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content farm-card p-2" style="box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-plus-circle text-success"></i>
                    <span>Add Partition Plot</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <form id="addPlotForm" onsubmit="submitAddPlot(event)">
                    <input type="hidden" id="addPlotLandId">

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark" style="font-size:0.82rem;">Plot Number / Identifier <span class="text-danger">*</span></label>
                        <input type="text" id="addPlotNumber" class="form-control rounded-3" placeholder="e.g. Plot A-5" required style="border: 1.5px solid #523E31;">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold text-dark" style="font-size:0.82rem;">Area (sqm) <span class="text-danger">*</span></label>
                            <input type="number" step="0.1" id="addPlotArea" class="form-control rounded-3" placeholder="e.g. 50" required style="border: 1.5px solid #523E31;">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold text-dark" style="font-size:0.82rem;">Rental Fee / Year (₱)</label>
                            <input type="number" step="100" id="addPlotPrice" class="form-control rounded-3" value="110000" style="border: 1.5px solid #523E31;">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark" style="font-size:0.82rem;">Primary Crop</label>
                        <select id="addPlotCrop" class="form-select rounded-3" style="border: 1.5px solid #523E31;">
                            <option value="Tomato" data-icon="tomato/tomato.svg">🍅 Tomato</option>
                            <option value="Lettuce" data-icon="romaine/romaine.svg">🥬 Lettuce / Leafy Greens</option>
                            <option value="Corn" data-icon="corn/corn.svg">🌽 Sweet Corn</option>
                            <option value="Carrot" data-icon="carrot/carrot.svg">🥕 Carrot</option>
                            <option value="Pepper" data-icon="red-bell-pepper/red-bell-pepper.svg">🫑 Bell Pepper</option>
                            <option value="Eggplant" data-icon="eggplant/eggplant.svg">🍆 Eggplant</option>
                            <option value="Herbs" data-icon="basil/basil.svg">🌿 Basil & Herbs</option>
                            <option value="Cucumber" data-icon="cucumber/cucumber.svg">🥒 Cucumber</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark" style="font-size:0.82rem;">Permitted Crops for Tenant</label>
                        <div class="d-flex flex-wrap gap-2 p-2 bg-light rounded-3 border" id="addPlotSeedsCheckboxes" style="max-height: 120px; overflow-y: auto;">
                            <!-- Populated dynamically -->
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                        <button type="button" class="btn btn-sm farm-btn farm-btn-secondary px-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm farm-btn farm-btn-primary px-4" id="saveAddPlotBtn">
                            Create Plot
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 2. Edit Plot Details Modal -->
<div class="modal fade" id="editPlotModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content farm-card p-2" style="box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                    <i class="bi bi-pencil-square text-success"></i>
                    <span>Edit Partition Plot</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <form id="editPlotForm" onsubmit="submitEditPlot(event)">
                    <input type="hidden" id="editPlotId">

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark" style="font-size:0.82rem;">Plot Number / Identifier <span class="text-danger">*</span></label>
                        <input type="text" id="editPlotNumber" class="form-control rounded-3" required style="border: 1.5px solid #523E31;">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold text-dark" style="font-size:0.82rem;">Area (sqm) <span class="text-danger">*</span></label>
                            <input type="number" step="0.1" id="editPlotArea" class="form-control rounded-3" required style="border: 1.5px solid #523E31;">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold text-dark" style="font-size:0.82rem;">Rental Fee / Year (₱)</label>
                            <input type="number" step="100" id="editPlotPrice" class="form-control rounded-3" style="border: 1.5px solid #523E31;">
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-bold text-dark" style="font-size:0.82rem;">Lease Status</label>
                            <select id="editPlotStatus" class="form-select rounded-3" style="border: 1.5px solid #523E31;">
                                <option value="available">🌿 Available</option>
                                <option value="occupied">👨‍🌾 Occupied / Leased</option>
                                <option value="maintenance">🛠️ Maintenance</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-bold text-dark" style="font-size:0.82rem;">Primary Crop</label>
                            <select id="editPlotCrop" class="form-select rounded-3" style="border: 1.5px solid #523E31;">
                                <option value="Tomato" data-icon="tomato/tomato.svg">🍅 Tomato</option>
                                <option value="Lettuce" data-icon="romaine/romaine.svg">🥬 Lettuce</option>
                                <option value="Corn" data-icon="corn/corn.svg">🌽 Corn</option>
                                <option value="Carrot" data-icon="carrot/carrot.svg">🥕 Carrot</option>
                                <option value="Pepper" data-icon="red-bell-pepper/red-bell-pepper.svg">🫑 Pepper</option>
                                <option value="Eggplant" data-icon="eggplant/eggplant.svg">🍆 Eggplant</option>
                                <option value="Herbs" data-icon="basil/basil.svg">🌿 Herbs</option>
                                <option value="Cucumber" data-icon="cucumber/cucumber.svg">🥒 Cucumber</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark" style="font-size:0.82rem;">Assigned Gardener / Tenant Name</label>
                        <input type="text" id="editPlotFarmer" class="form-control rounded-3" placeholder="Leave empty if available" style="border: 1.5px solid #523E31;">
                    </div>

                    <div class="d-flex justify-content-end gap-2 pt-2 border-top">
                        <button type="button" class="btn btn-sm farm-btn farm-btn-secondary px-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm farm-btn farm-btn-primary px-4" id="saveEditPlotBtn">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- 3. Edit Crop Permissions Modal -->
<div class="modal fade" id="cropPermissionsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content farm-card p-2" style="box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                        <i class="bi bi-flower1 text-success"></i>
                        <span>Permitted Seed Varieties</span>
                    </h5>
                    <p class="text-muted mb-0 mt-0.5" style="font-size:0.78rem;" id="cropPermPlotTitle">Plot A-1</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <input type="hidden" id="cropPermPlotId">
                <p class="text-secondary mb-2" style="font-size:0.8rem;">
                    Check the seed types that gardeners are allowed to cultivate in this plot:
                </p>
                <div class="row g-2" id="cropPermGrid" style="max-height: 280px; overflow-y: auto;">
                    <!-- Populated dynamically -->
                </div>

                <div class="d-flex justify-content-end gap-2 pt-3 mt-3 border-top">
                    <button type="button" class="btn btn-sm farm-btn farm-btn-secondary px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-sm farm-btn farm-btn-primary px-4" onclick="saveCropPermissions()">
                        Save Permissions
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Client-side Logic -->
<script>
    // Standard crop catalog with SVG icons
    const cropCatalog = [
        { name: 'Tomato', icon: 'tomato/tomato.svg' },
        { name: 'Lettuce', icon: 'romaine/romaine.svg' },
        { name: 'Sweet Corn', icon: 'corn/corn.svg' },
        { name: 'Carrot', icon: 'carrot/carrot.svg' },
        { name: 'Bell Pepper', icon: 'red-bell-pepper/red-bell-pepper.svg' },
        { name: 'Eggplant', icon: 'eggplant/eggplant.svg' },
        { name: 'Cucumber', icon: 'cucumber/cucumber.svg' },
        { name: 'Spinach', icon: 'spinach/spinach.svg' },
        { name: 'Basil & Herbs', icon: 'basil/basil.svg' },
        { name: 'Beans', icon: 'broad-bean/broad-bean.svg' },
        { name: 'Yellow Squash', icon: 'yellow-squash/yellow-squash.svg' },
        { name: 'Red Onion', icon: 'red-onion/red-onion.svg' },
        { name: 'Garlic', icon: 'garlic/garlic.svg' },
        { name: 'Potato', icon: 'russet-potato/russet-potato.svg' },
        { name: 'Snap Peas', icon: 'snap-pea/snap-pea.svg' },
        { name: 'Mushroom', icon: 'generic-mushroom/generic-mushroom.svg' }
    ];

    let allLands = [];
    let allPlots = [];
    let currentLandId = null;
    let currentFilter = 'all';
    let selectedZoneKey = null;

    // Modals
    let addPlotModalInst = null;
    let editPlotModalInst = null;
    let cropPermModalInst = null;

    document.addEventListener('DOMContentLoaded', () => {
        addPlotModalInst = new bootstrap.Modal(document.getElementById('addPlotModal'));
        editPlotModalInst = new bootstrap.Modal(document.getElementById('editPlotModal'));
        cropPermModalInst = new bootstrap.Modal(document.getElementById('cropPermissionsModal'));

        // Populate seed checkbox list in Add Plot modal
        const seedContainer = document.getElementById('addPlotSeedsCheckboxes');
        if (seedContainer) {
            seedContainer.innerHTML = cropCatalog.map((c, i) => `
                <label class="form-check-label d-flex align-items-center gap-1.5 p-1 px-2 rounded bg-white border" style="font-size:0.75rem; cursor:pointer;">
                    <input type="checkbox" class="form-check-input mt-0 add-seed-chk" value="${c.name}" ${i < 4 ? 'checked' : ''}>
                    <img src="../assets/crop-icons/${c.icon}" style="width:14px; height:14px; object-fit:contain;">
                    <span>${c.name}</span>
                </label>
            `).join('');
        }

        // Initialize Property Select Change
        document.getElementById('farmPropertySelect').addEventListener('change', (e) => {
            if (e.target.value) {
                switchLand(e.target.value);
            }
        });

        // Window resize re-clips map image
        window.addEventListener('resize', () => {
            renderDynamicFarmMap();
        });

        loadFarmPlotsData();
    });

    // Comprehensive crops library from crops_library.php
    const fullCropsLibrary = <?php echo json_encode(function_exists('getCropsLibrary') ? getCropsLibrary() : []); ?>;

    // Helper for crop icons (resolves from full library and catalog)
    function getCropIconUrl(cropName, explicitIcon) {
        const lower = (cropName || '').toLowerCase().trim();

        // Check if explicitIcon is a custom non-default icon
        if (explicitIcon && explicitIcon !== 'tomato/tomato.svg' && explicitIcon !== 'crop-icons/tomato/tomato.svg' && explicitIcon !== 'assets/crop-icons/tomato/tomato.svg') {
            let clean = explicitIcon.replace(/^\.\.\//, '').replace(/^assets\//, '');
            if (!clean.startsWith('crop-icons/')) clean = 'crop-icons/' + clean;
            return `../assets/${clean}`;
        }

        // Search full library
        if (lower && Array.isArray(fullCropsLibrary) && fullCropsLibrary.length > 0) {
            const match = fullCropsLibrary.find(c =>
                (c.name && c.name.toLowerCase() === lower) ||
                (c.value && c.value.toLowerCase() === lower) ||
                (c.local_name && c.local_name.toLowerCase() === lower) ||
                (c.name && lower.includes(c.name.toLowerCase())) ||
                (c.value && lower.includes(c.value.toLowerCase()))
            );
            if (match && match.icon) {
                let clean = match.icon.replace(/^\.\.\//, '').replace(/^assets\//, '');
                if (!clean.startsWith('crop-icons/')) clean = 'crop-icons/' + clean;
                return `../assets/${clean}`;
            }
        }

        // Search standard crop catalog
        if (lower) {
            const matchCat = cropCatalog.find(c => lower.includes(c.name.toLowerCase()) || c.name.toLowerCase().includes(lower));
            if (matchCat) return `../assets/crop-icons/${matchCat.icon}`;
        }

        // Fallback to explicitIcon if provided
        if (explicitIcon) {
            let clean = explicitIcon.replace(/^\.\.\//, '').replace(/^assets\//, '');
            if (!clean.startsWith('crop-icons/')) clean = 'crop-icons/' + clean;
            return `../assets/${clean}`;
        }

        return '../assets/crop-icons/tomato/tomato.svg';
    }

    // Load lands and plots from API
    async function loadFarmPlotsData() {
        try {
            const res = await fetch('../api/lands.php');
            const data = await res.json();
            if (data.status === 'success') {
                allLands = data.data.lands || [];
                allPlots = data.data.plots || [];

                populateLandDropdown();

                // Check URL parameter for land selection (e.g. ?id=2)
                const urlParams = new URLSearchParams(window.location.search);
                const queryId = urlParams.get('id');

                if (queryId && allLands.some(l => l.id == queryId)) {
                    switchLand(queryId);
                } else if (allLands.length > 0) {
                    switchLand(allLands[0].id);
                } else {
                    renderEmptyState();
                }
            }
        } catch (err) {
            console.error('Failed to load lands/plots:', err);
        }
    }

    // Populate property dropdown
    function populateLandDropdown() {
        const select = document.getElementById('farmPropertySelect');
        if (!select) return;
        if (allLands.length === 0) {
            select.innerHTML = '<option value="">No lands registered</option>';
            return;
        }
        select.innerHTML = allLands.map(l => `
            <option value="${l.id}">${l.title} (${parseFloat(l.area).toLocaleString()} m²)</option>
        `).join('');
    }

    // Switch selected land
    function switchLand(landId) {
        currentLandId = parseInt(landId);
        const land = allLands.find(l => l.id == currentLandId);
        if (!land) return;

        // Sync dropdown
        const select = document.getElementById('farmPropertySelect');
        if (select) select.value = currentLandId;

        // Update URL query without full reload
        const newUrl = new URL(window.location);
        newUrl.searchParams.set('id', currentLandId);
        window.history.replaceState({}, '', newUrl);

        // Update Banner Details
        document.getElementById('currentLandTitle').textContent = land.title;
        document.getElementById('currentLandAddress').textContent = land.address || 'No address specified';
        document.getElementById('currentLandDescription').textContent = land.description || 'No description available for this farm property.';

        const statusBadge = document.getElementById('currentLandStatusBadge');
        if (land.status === 'approved') {
            statusBadge.className = 'farm-badge-pill';
            statusBadge.style.background = '#EBF3E8';
            statusBadge.style.color = '#3F6212';
            statusBadge.innerHTML = '<i class="bi bi-shield-check"></i> Approved';
        } else if (land.status === 'pending') {
            statusBadge.className = 'farm-badge-pill';
            statusBadge.style.background = '#FEF9C3';
            statusBadge.style.color = '#854D0E';
            statusBadge.innerHTML = '<i class="bi bi-hourglass-split"></i> Pending';
        } else {
            statusBadge.className = 'farm-badge-pill';
            statusBadge.style.background = '#FEE2E2';
            statusBadge.style.color = '#991B1B';
            statusBadge.innerHTML = '<i class="bi bi-x-circle"></i> Rejected';
        }

        const typeBadge = document.getElementById('currentLandTypeBadge');
        typeBadge.innerHTML = `<i class="bi bi-file-earmark-text"></i> ${land.title_type || 'Land Title'}`;

        renderCurrentPlots();
    }

    // Render Plots for current land
    function renderCurrentPlots() {
        const container = document.getElementById('plotsCardsContainer');
        if (!container) return;

        const landPlots = allPlots.filter(p => p.land_id == currentLandId);

        // Update Stats
        const land = allLands.find(l => l.id == currentLandId);
        document.getElementById('statTotalArea').textContent = `${(land ? parseFloat(land.area) : 0).toLocaleString()} m²`;
        document.getElementById('statTotalPlots').textContent = landPlots.length;
        const availCount = landPlots.filter(p => p.status === 'available').length;
        document.getElementById('statAvailPlots').textContent = availCount;
        document.getElementById('plotsBadgeCount').textContent = landPlots.length;

        // Apply Status Filter and Search
        const searchQuery = (document.getElementById('plotSearchInput')?.value || '').toLowerCase();
        const filtered = landPlots.filter(plot => {
            const matchesFilter = (currentFilter === 'all') || (plot.status === currentFilter);
            const matchesSearch = !searchQuery ||
                (plot.plot_number && plot.plot_number.toLowerCase().includes(searchQuery)) ||
                (plot.crop && plot.crop.toLowerCase().includes(searchQuery)) ||
                (plot.farmer_name && plot.farmer_name.toLowerCase().includes(searchQuery));
            return matchesFilter && matchesSearch;
        });

        if (filtered.length === 0) {
            container.innerHTML = `
                <div class="text-center py-5 border rounded-4 bg-white text-muted">
                    <img src="../assets/crop-icons/generic-plant/generic-plant.svg" style="width:48px; height:48px; opacity:0.6;" class="mb-2">
                    <h6 class="fw-bold text-dark">No Partition Plots Found</h6>
                    <p class="text-muted small mb-3">No plots match the selected criteria for this farm.</p>
                    <button class="btn btn-sm farm-btn farm-btn-primary px-3" onclick="openAddPlotModal()">
                        <i class="bi bi-plus-circle me-1"></i> Add Your First Plot
                    </button>
                </div>
            `;
            return;
        }

        container.innerHTML = `
            <div class="row g-3">
                ${filtered.map(plot => {
                    const iconUrl = getCropIconUrl(plot.crop, plot.crop_icon);
                    const isOccupied = plot.status === 'occupied';
                    const isMaintenance = plot.status === 'maintenance';

                    let statusBadgeHtml = '';
                    if (plot.status === 'available') {
                        statusBadgeHtml = `<span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2 py-0.5" style="font-size:0.72rem;">🌿 Available</span>`;
                    } else if (isOccupied) {
                        statusBadgeHtml = `<span class="badge rounded-pill bg-primary-subtle text-primary border border-primary-subtle px-2 py-0.5" style="font-size:0.72rem;">👨‍🌾 Rented</span>`;
                    } else {
                        statusBadgeHtml = `<span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-0.5" style="font-size:0.72rem;">🛠️ Maintenance</span>`;
                    }

                    // Seeds pills
                    const seeds = Array.isArray(plot.crops) ? plot.crops : (plot.crop ? [plot.crop] : ['Tomato']);
                    const seedsHtml = seeds.slice(0, 3).map(s => {
                        const sIcon = getCropIconUrl(s);
                        return `<span class="seed-tag"><img src="${sIcon}" style="width:12px; height:12px;"> ${s}</span>`;
                    }).join('');

                    return `
                        <div class="col-md-6" id="plot-card-${plot.id}">
                            <div class="plot-item-card p-3 h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle p-1.5 bg-light border d-flex align-items-center justify-content-center" style="width:36px; height:36px; border-color: #523E31 !important;">
                                                <img src="${iconUrl}" alt="${plot.crop}" style="width:22px; height:22px; object-fit:contain;">
                                            </div>
                                            <div>
                                                <h6 class="fw-bold text-dark mb-0" style="font-size:0.95rem;">${plot.plot_number}</h6>
                                                <span class="text-muted" style="font-size:0.76rem;">Crop: <strong class="text-dark">${plot.crop || 'Unassigned'}</strong></span>
                                            </div>
                                        </div>
                                        ${statusBadgeHtml}
                                    </div>

                                    <div class="p-2 rounded-3 bg-light border my-2" style="border-color: #DFCFC2 !important;">
                                        <div class="d-flex justify-content-between text-secondary" style="font-size:0.78rem;">
                                            <span>Area:</span>
                                            <strong class="text-dark">${parseFloat(plot.area).toLocaleString()} m²</strong>
                                        </div>
                                        <div class="d-flex justify-content-between text-secondary mt-1" style="font-size:0.78rem;">
                                            <span>Lease Fee:</span>
                                            <strong class="text-success">₱${parseFloat(plot.rental_price_per_year || 110000).toLocaleString()} / yr</strong>
                                        </div>
                                        ${isOccupied && plot.farmer_name ? `
                                            <div class="d-flex justify-content-between text-secondary mt-1" style="font-size:0.78rem;">
                                                <span>Tenant:</span>
                                                <strong class="text-primary">${plot.farmer_name}</strong>
                                            </div>
                                        ` : ''}
                                    </div>

                                    <!-- Allowed Seeds -->
                                    <div class="mb-3">
                                        <div class="text-muted fw-bold mb-1" style="font-size:0.7rem; text-transform:uppercase;">Allowed Crops:</div>
                                        <div class="d-flex flex-wrap gap-1">
                                            ${seedsHtml}
                                            ${seeds.length > 3 ? `<span class="seed-tag">+${seeds.length - 3} more</span>` : ''}
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="d-flex align-items-center justify-content-between gap-1.5 pt-2 border-top">
                                    <button class="btn btn-sm farm-btn farm-btn-secondary px-2.5 py-1 text-nowrap" style="font-size:0.74rem;" onclick="openCropPermissionsModal(${plot.id})">
                                        <i class="bi bi-seedling"></i> Crops
                                    </button>
                                    <div class="d-flex align-items-center gap-1">
                                        <button class="btn btn-sm farm-btn farm-btn-secondary px-2.5 py-1" style="font-size:0.74rem;" onclick="openEditPlotModal(${plot.id})">
                                            <i class="bi bi-pencil"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1" style="font-size:0.74rem; border:1.5px solid #DC2626;" onclick="deletePlot(${plot.id})" title="Delete Plot">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                }).join('')}
            </div>
        `;

        // Always render the dynamic top-down farm map for the selected farm
        renderDynamicFarmMap();
    }

    // Render the Crop Pills directly on crop_plots_map.jpg based on current farm and its plots
    function renderDynamicFarmMap() {
        const container = document.getElementById('dynamicCropOverlays');
        const viewContainer = document.getElementById('cropsMapViewContainer');
        const img = document.getElementById('farmMapImage');
        if (!container || !viewContainer) return;

        const land = allLands.find(l => l.id == currentLandId);
        const landPlots = allPlots.filter(p => p.land_id == currentLandId);
        const count = landPlots.length;

        // Clip container height so if managing 4 plots, it shows only the 4 plots!
        function updateMapClipHeight() {
            if (!img || !img.clientHeight) return;
            if (count <= 2) {
                viewContainer.style.height = (img.clientHeight * 0.34) + 'px';
            } else if (count <= 4) {
                viewContainer.style.height = (img.clientHeight * 0.655) + 'px';
            } else {
                viewContainer.style.height = 'auto';
            }
        }

        if (img && img.complete) {
            updateMapClipHeight();
        } else if (img) {
            img.onload = updateMapClipHeight;
        }

        // Coordinates of the 6 beds on crop_plots_map.jpg
        const bedCoords = [
            // Row 0: Top-Left, Top-Right
            { top: '3.5%', left: '5%', width: '43%', height: '28%', pillTop: '4.5%', pillLeft: '26.5%' },
            { top: '3.5%', left: '52%', width: '43%', height: '28%', pillTop: '4.5%', pillLeft: '73.5%' },
            // Row 1: Middle-Left, Middle-Right
            { top: '35.5%', left: '5%', width: '43%', height: '28%', pillTop: '36.5%', pillLeft: '26.5%' },
            { top: '35.5%', left: '52%', width: '43%', height: '28%', pillTop: '36.5%', pillLeft: '73.5%' },
            // Row 2: Bottom-Left, Bottom-Right
            { top: '67.5%', left: '5%', width: '43%', height: '28%', pillTop: '68.5%', pillLeft: '26.5%' },
            { top: '67.5%', left: '52%', width: '43%', height: '28%', pillTop: '68.5%', pillLeft: '73.5%' }
        ];

        let html = '';

        // Only render the beds belonging to this farm! (e.g. 4 beds for 4 plots)
        const visibleCount = Math.min(count, 6);
        for (let i = 0; i < visibleCount; i++) {
            const coord = bedCoords[i];
            const plot = landPlots[i];
            const cropName = plot.crop || (Array.isArray(plot.crops) && plot.crops[0]) || 'Vegetables';
            const cropIconUrl = getCropIconUrl(cropName, plot.crop_icon);

            html += `
                <!-- Hotspot for ${plot.plot_number} -->
                <div class="map-plot-hotspot" id="map-hotspot-${plot.id}"
                     style="top: ${coord.top}; left: ${coord.left}; width: ${coord.width}; height: ${coord.height};"
                     onclick="selectDynamicPlotBed(${plot.id}, this)"
                     title="${plot.plot_number} (${cropName})">
                </div>

                <!-- Crop Pill showing the crop name matching the farm plot -->
                <div class="map-crop-pill" style="top: ${coord.pillTop}; left: ${coord.pillLeft};">
                    <img src="${cropIconUrl}" alt="${cropName}">
                    <span>${cropName}</span>
                </div>
            `;
        }

        container.innerHTML = html;
        updateMapClipHeight();
    }

    // Select dynamic plot bed on map
    function selectDynamicPlotBed(plotId, element) {
        document.querySelectorAll('.map-plot-hotspot').forEach(b => b.classList.remove('selected'));
        if (element) {
            element.classList.add('selected');
        } else {
            const h = document.getElementById(`map-hotspot-${plotId}`);
            if (h) h.classList.add('selected');
        }

        const plot = allPlots.find(p => p.id == plotId);
        if (!plot) return;

        // Update callout card
        const calloutTitle = document.getElementById('calloutTitle');
        const calloutSub = document.getElementById('calloutSub');
        const seeds = Array.isArray(plot.crops) ? plot.crops.join(', ') : (plot.crop || 'None');
        if (calloutTitle) calloutTitle.textContent = `${plot.plot_number} (${plot.crop || 'Crop'})`;
        if (calloutSub) calloutSub.textContent = `Area: ${parseFloat(plot.area).toLocaleString()} m² • Permitted: ${seeds} • Click 'Jump to Plot' to manage`;

        // Highlight and scroll to matching plot card
        document.querySelectorAll('.plot-item-card').forEach(c => c.classList.remove('highlighted'));
        const card = document.getElementById(`plot-card-${plot.id}`);
        if (card) {
            const inner = card.querySelector('.plot-item-card');
            if (inner) inner.classList.add('highlighted');
            card.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    // Filter Chips click
    function setPlotFilter(filter, btn) {
        currentFilter = filter;
        document.querySelectorAll('.plot-filter-chip').forEach(c => c.classList.remove('active'));
        btn.classList.add('active');
        renderCurrentPlots();
    }

    function filterPlotsList() {
        renderCurrentPlots();
    }

    // Switch between Crop Plots Only (Default) and Full Homestead (Option)
    let currentMapViewMode = 'crops';
    function setMapViewMode(mode) {
        currentMapViewMode = mode;
        const cropsContainer = document.getElementById('cropsMapViewContainer');
        const homesteadContainer = document.getElementById('homesteadViewContainer');
        const cropsBtn = document.getElementById('modeCropsBtn');
        const homesteadBtn = document.getElementById('modeHomesteadBtn');
        const calloutTitle = document.getElementById('calloutTitle');
        const calloutSub = document.getElementById('calloutSub');
        const land = allLands.find(l => l.id == currentLandId);

        if (mode === 'crops') {
            if (cropsContainer) cropsContainer.classList.remove('d-none');
            if (homesteadContainer) homesteadContainer.classList.add('d-none');

            cropsBtn.classList.add('active');
            homesteadBtn.classList.remove('active');

            renderDynamicFarmMap();

            if (calloutTitle) calloutTitle.textContent = 'Crop Plots Map';
            if (calloutSub) calloutSub.textContent = `Showing active plots & permitted crop varieties for ${land ? land.title : 'this farm'}.`;
        } else {
            if (cropsContainer) cropsContainer.classList.add('d-none');
            if (homesteadContainer) homesteadContainer.classList.remove('d-none');

            homesteadBtn.classList.add('active');
            cropsBtn.classList.remove('active');

            if (calloutTitle) calloutTitle.textContent = 'Full Homestead & Facilities';
            if (calloutSub) calloutSub.textContent = 'Includes farmhouse residence, pasture, water reservoir, and storage silos.';
        }

        document.querySelectorAll('.map-plot-hotspot').forEach(h => h.classList.remove('selected'));
    }

    // Interactive Map Zone Selection
    function selectMapZone(zoneKey, title, crop, area, badge) {
        selectedZoneKey = zoneKey;

        // Visual feedback on callout
        const callout = document.getElementById('mapZoneCallout');
        const calloutTitle = document.getElementById('calloutTitle');
        const calloutSub = document.getElementById('calloutSub');

        calloutTitle.textContent = title;
        calloutSub.textContent = `Crop: ${crop} • Area: ${area} • Click 'Jump to Plot' to manage`;

        // Highlight hotspot
        document.querySelectorAll('.map-plot-hotspot').forEach(h => h.classList.remove('selected'));
        if (event && event.currentTarget) {
            event.currentTarget.classList.add('selected');
        }

        // Scroll or highlight matching plot card if exists
        const landPlots = allPlots.filter(p => p.land_id == currentLandId);
        const matchPlot = landPlots.find(p =>
            (p.crop && p.crop.toLowerCase().includes(badge.toLowerCase())) ||
            (p.plot_number && p.plot_number.toLowerCase().includes(badge.toLowerCase()))
        );

        document.querySelectorAll('.plot-item-card').forEach(c => c.classList.remove('highlighted'));

        if (matchPlot) {
            const card = document.getElementById(`plot-card-${matchPlot.id}`);
            if (card) {
                const inner = card.querySelector('.plot-item-card');
                if (inner) inner.classList.add('highlighted');
            }
        }
    }

    function focusMatchingPlotCard() {
        const highlighted = document.querySelector('.plot-item-card.highlighted');
        if (highlighted) {
            highlighted.scrollIntoView({ behavior: 'smooth', block: 'center' });
        } else {
            const firstCard = document.querySelector('.plot-item-card');
            if (firstCard) firstCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    // Add Plot Modal
    function openAddPlotModal() {
        if (!currentLandId) {
            alert('Please select a farm property first.');
            return;
        }
        document.getElementById('addPlotLandId').value = currentLandId;
        const landPlots = allPlots.filter(p => p.land_id == currentLandId);
        document.getElementById('addPlotNumber').value = `Plot A-${landPlots.length + 1}`;
        document.getElementById('addPlotArea').value = '50';
        addPlotModalInst.show();
    }

    async function submitAddPlot(e) {
        e.preventDefault();
        const landId = parseInt(document.getElementById('addPlotLandId').value);
        const plotNumber = document.getElementById('addPlotNumber').value.trim();
        const area = parseFloat(document.getElementById('addPlotArea').value);
        const price = parseFloat(document.getElementById('addPlotPrice').value);
        const cropSelect = document.getElementById('addPlotCrop');
        const crop = cropSelect.value;
        const cropIcon = cropSelect.options[cropSelect.selectedIndex].getAttribute('data-icon') || 'tomato/tomato.svg';

        const checkedSeeds = Array.from(document.querySelectorAll('.add-seed-chk:checked')).map(cb => cb.value);

        const btn = document.getElementById('saveAddPlotBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Creating...';

        try {
            const response = await fetch('../api/lands.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'create_plot',
                    land_id: landId,
                    plot_number: plotNumber,
                    area: area,
                    rental_price_per_year: price,
                    crop: crop,
                    crop_icon: cropIcon,
                    crops: checkedSeeds.length > 0 ? checkedSeeds : [crop]
                })
            });
            const res = await response.json();
            if (res.status === 'success') {
                addPlotModalInst.hide();
                await loadFarmPlotsData();
            } else {
                alert(res.message || 'Failed to create plot.');
            }
        } catch (err) {
            console.error('Error creating plot:', err);
            alert('An unexpected error occurred.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = 'Create Plot';
        }
    }

    // Edit Plot Modal
    function openEditPlotModal(plotId) {
        const plot = allPlots.find(p => p.id == plotId);
        if (!plot) return;

        document.getElementById('editPlotId').value = plot.id;
        document.getElementById('editPlotNumber').value = plot.plot_number;
        document.getElementById('editPlotArea').value = plot.area;
        document.getElementById('editPlotPrice').value = plot.rental_price_per_year || 110000;
        document.getElementById('editPlotStatus').value = plot.status || 'available';
        document.getElementById('editPlotCrop').value = plot.crop || 'Tomato';
        document.getElementById('editPlotFarmer').value = plot.farmer_name || '';

        editPlotModalInst.show();
    }

    async function submitEditPlot(e) {
        e.preventDefault();
        const plotId = parseInt(document.getElementById('editPlotId').value);
        const plotNumber = document.getElementById('editPlotNumber').value.trim();
        const area = parseFloat(document.getElementById('editPlotArea').value);
        const price = parseFloat(document.getElementById('editPlotPrice').value);
        const status = document.getElementById('editPlotStatus').value;
        const cropSelect = document.getElementById('editPlotCrop');
        const crop = cropSelect.value;
        const cropIcon = cropSelect.options[cropSelect.selectedIndex].getAttribute('data-icon') || 'tomato/tomato.svg';
        const farmerName = document.getElementById('editPlotFarmer').value.trim();

        const btn = document.getElementById('saveEditPlotBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

        try {
            const plot = allPlots.find(p => p.id == plotId);
            const crops = plot && Array.isArray(plot.crops) ? plot.crops : [crop];

            const response = await fetch('../api/lands.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'update_plot',
                    plot_id: plotId,
                    plot_number: plotNumber,
                    area: area,
                    rental_price_per_year: price,
                    status: status,
                    crop: crop,
                    crop_icon: cropIcon,
                    crops: crops,
                    farmer_name: farmerName
                })
            });
            const res = await response.json();
            if (res.status === 'success') {
                editPlotModalInst.hide();
                await loadFarmPlotsData();
            } else {
                alert(res.message || 'Failed to update plot.');
            }
        } catch (err) {
            console.error('Error updating plot:', err);
            alert('An unexpected error occurred.');
        } finally {
            btn.disabled = false;
            btn.innerHTML = 'Save Changes';
        }
    }

    // Crop Permissions Modal
    function openCropPermissionsModal(plotId) {
        const plot = allPlots.find(p => p.id == plotId);
        if (!plot) return;

        document.getElementById('cropPermPlotId').value = plot.id;
        document.getElementById('cropPermPlotTitle').textContent = `${plot.plot_number} (${plot.crop || 'Crop'})`;

        const allowedSeeds = Array.isArray(plot.crops) ? plot.crops : [plot.crop || 'Tomato'];

        const grid = document.getElementById('cropPermGrid');
        grid.innerHTML = cropCatalog.map(c => {
            const isChecked = allowedSeeds.includes(c.name);
            return `
                <div class="col-6 col-sm-4">
                    <label class="d-flex align-items-center gap-2 p-2 rounded-3 border bg-white h-100" style="cursor:pointer; border: 1.5px solid #DFCFC2 !important;">
                        <input type="checkbox" class="form-check-input mt-0 perm-seed-chk" value="${c.name}" ${isChecked ? 'checked' : ''}>
                        <img src="../assets/crop-icons/${c.icon}" style="width:20px; height:20px; object-fit:contain;">
                        <span style="font-size:0.78rem; font-weight:700; color:#3E2B1E;">${c.name}</span>
                    </label>
                </div>
            `;
        }).join('');

        cropPermModalInst.show();
    }

    async function saveCropPermissions() {
        const plotId = parseInt(document.getElementById('cropPermPlotId').value);
        const checkedSeeds = Array.from(document.querySelectorAll('.perm-seed-chk:checked')).map(cb => cb.value);

        if (checkedSeeds.length === 0) {
            alert('Please select at least one permitted crop.');
            return;
        }

        try {
            const primaryCrop = checkedSeeds[0];
            const primaryIcon = cropCatalog.find(c => c.name === primaryCrop)?.icon || 'tomato/tomato.svg';

            const response = await fetch('../api/lands.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'update_plot_crops',
                    plot_id: plotId,
                    crops: checkedSeeds,
                    crop: primaryCrop,
                    crop_icon: primaryIcon
                })
            });
            const res = await response.json();
            if (res.status === 'success') {
                cropPermModalInst.hide();
                await loadFarmPlotsData();
            } else {
                alert(res.message || 'Failed to update permitted crops.');
            }
        } catch (err) {
            console.error('Error updating crop permissions:', err);
            alert('An unexpected error occurred.');
        }
    }

    // Delete Plot
    async function deletePlot(plotId) {
        const plot = allPlots.find(p => p.id == plotId);
        const name = plot ? plot.plot_number : 'this plot';

        if (!confirm(`Are you sure you want to delete ${name}? This action cannot be undone.`)) {
            return;
        }

        try {
            const response = await fetch('../api/lands.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'delete_plot',
                    plot_id: plotId
                })
            });
            const res = await response.json();
            if (res.status === 'success') {
                await loadFarmPlotsData();
            } else {
                alert(res.message || 'Failed to delete plot.');
            }
        } catch (err) {
            console.error('Error deleting plot:', err);
            alert('An unexpected error occurred.');
        }
    }

    function renderEmptyState() {
        const container = document.getElementById('plotsCardsContainer');
        if (container) {
            container.innerHTML = `
                <div class="text-center py-5 border rounded-4 bg-white text-muted">
                    <i class="bi bi-geo-alt fs-1 text-secondary"></i>
                    <h5 class="fw-bold text-dark mt-2">No Registered Lands Found</h5>
                    <p class="text-muted small">You haven't registered any idle lands yet.</p>
                    <a href="register.php" class="btn btn-sm farm-btn farm-btn-primary px-3">
                        <i class="bi bi-plus-circle me-1"></i> Register Land Now
                    </a>
                </div>
            `;
        }
    }
</script>

<?php include '../includes/footer.php'; ?>
