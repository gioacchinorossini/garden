<?php
$base_path = '../';
$page_title = "Register Land";
include '../includes/header.php';
include '../includes/navbar.php';
include '../includes/sidebar.php';
require_once '../includes/db.php';
require_once '../includes/lands_helper.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$success_message = "";
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $title = htmlspecialchars(trim($_POST['title']));
    $address = htmlspecialchars(trim($_POST['address']));
    $lat = floatval($_POST['latitude']);
    $lng = floatval($_POST['longitude']);
    $area = floatval($_POST['area']);
    $desc = htmlspecialchars(trim($_POST['description']));
    $plot_count = max(1, intval($_POST['plot_count'] ?? 4));
    $has_3d = isset($_POST['enable_3d_view']) ? 1 : 0;
    $polygon = isset($_POST['lot_polygon']) ? htmlspecialchars(trim($_POST['lot_polygon'])) : '';

    // Harvest sharing configuration (defaulted/optional)
    $harvest_share_type = 'percentage';
    $landowner_share_percentage = null;
    $landowner_share_kg = null;

    $seeds = isset($_POST['allowed_seeds']) ? array_map('htmlspecialchars', $_POST['allowed_seeds']) : [];
    if (empty($seeds)) {
        $seeds = ['Vegetables', 'Herbs'];
    }

    $amenities = isset($_POST['amenities']) ? array_map('htmlspecialchars', (array)$_POST['amenities']) : [];
    $amenities = array_values(array_filter(array_map('trim', $amenities)));
    $amenities_json = json_encode($amenities);

    // Handle Document Upload
    $title_doc_path = '';
    $oct_file = $_FILES['oct_document'] ?? $_FILES['title_document'] ?? null;
    if ($oct_file && $oct_file['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($oct_file['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['pdf', 'jpg', 'jpeg', 'png', 'svg'])) {
            $uploadDir = __DIR__ . '/../uploads/titles/';
            if (!is_dir($uploadDir)) {
                @mkdir($uploadDir, 0777, true);
            }
            $fileName = 'oct_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            $uploadTarget = $uploadDir . $fileName;
            if (move_uploaded_file($oct_file['tmp_name'], $uploadTarget)) {
                $title_doc_path = 'uploads/titles/' . $fileName;
            }
        }
    }

    $owner_id = $_SESSION['user_id'] ?? 2;
    $landowner_name = $_SESSION['user_name'] ?? 'John Landowner';
    $seeds_json = json_encode(array_values($seeds));

    try {
        $stmt = $pdo->prepare("
            INSERT INTO `lands` 
            (owner_id, title, title_type, title_number, rod_name, epeb_type, epeb_no, title_document_path, oct_document_path, lra_receipt_path, is_lra_verified, address, latitude, longitude, area_sqm, status, description, allowed_seeds, amenities, plot_count, landowner_name, has_3d_view, polygon, harvest_share_type, landowner_share_percentage, landowner_share_kg)
            VALUES 
            (?, ?, '', '', '', '', '', ?, ?, '', 0, ?, ?, ?, ?, 'pending', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $owner_id,
            $title,
            $title_doc_path,
            $title_doc_path,
            $address,
            $lat,
            $lng,
            $area,
            $desc,
            $seeds_json,
            $amenities_json,
            $plot_count,
            $landowner_name,
            $has_3d,
            $polygon,
            $harvest_share_type,
            $landowner_share_percentage,
            $landowner_share_kg
        ]);
        $new_id = (int) $pdo->lastInsertId();

        // Insert partition plots into MySQL plots table with specified base rent
        $plot_area = round($area / $plot_count, 1);
        $stmtPlot = $pdo->prepare("
            INSERT INTO `plots` (land_id, plot_number, area_sqm, rental_price_per_year, status, crop, crop_icon, allowed_seeds, farmer_name)
            VALUES (?, ?, ?, ?, 'available', ?, ?, ?, '')
        ");

        if (!empty($_POST['plot_numbers']) && is_array($_POST['plot_numbers'])) {
            $submitted_plots = count($_POST['plot_numbers']);
            for ($i = 0; $i < $submitted_plots; $i++) {
                $pNum = htmlspecialchars(trim($_POST['plot_numbers'][$i] ?? ('Plot A-' . ($i + 1))));
                $pArea = max(1.0, floatval($_POST['plot_areas'][$i] ?? $plot_area));
                if (isset($_POST['plot_monthly_rents'][$i]) && is_numeric($_POST['plot_monthly_rents'][$i])) {
                    $pMonthly = max(0.0, floatval($_POST['plot_monthly_rents'][$i]));
                    $pRent = round($pMonthly * 12, 2);
                } else {
                    $pRent = max(0.0, floatval($_POST['plot_base_rents'][$i] ?? 110000.00));
                }
                // Newly created plots are unplanted; gardener chooses crop upon renting
                $stmtPlot->execute([
                    $new_id,
                    $pNum,
                    $pArea,
                    $pRent,
                    '',
                    '',
                    $seeds_json
                ]);
            }
        } else {
            // Fallback: auto-create plots with default ₱110k/yr base rent, unplanted
            for ($i = 1; $i <= $plot_count; $i++) {
                $stmtPlot->execute([
                    $new_id,
                    'Plot A-' . $i,
                    $plot_area,
                    110000.00,
                    '',
                    '',
                    $seeds_json
                ]);
            }
        }

        $success_message = "Land request registered successfully! It is now pending administrative approval and visible across all devices.";
    } catch (\Exception $e) {
        $error_message = "Failed to register land: " . $e->getMessage();
    }
}
?>

<main class="workspace-surface">
    <!-- Desktop Toolbar (Title and actions, borderless) -->
    <div class="toolbar d-none d-md-flex justify-content-between align-items-center">
        <div>
            <h1 class="fs-5 fw-semibold m-0 text-dark">Register Land</h1>
        </div>
    </div>

    <!-- Workspace Scrollable Area -->
    <div class="workspace-scroll px-4 pt-2 pb-4">
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success d-flex align-items-center gap-2 border-0  mb-4" role="alert"
                style="border-radius: 12px; background-color: #d1e7dd; color: #0f5132;">
                <i class="bi bi-check-circle-fill fs-5"></i>
                <div><?php echo $success_message; ?></div>
            </div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger d-flex align-items-center gap-2 border-0 mb-4" role="alert"
                style="border-radius: 12px; background-color: #f8d7da; color: #842029;">
                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                <div><?php echo $error_message; ?></div>
            </div>
        <?php endif; ?>

        <!-- Top: Interactive Map & 3D Digital Twin Card (Positioned Above the Form) -->
        <div class="card border rounded-4 overflow-hidden mb-4 shadow-sm"
            style="border-color: var(--drive-border) !important;">
            <!-- Step-by-Step Guided Process Indicator Bar -->
            <div class="border-bottom bg-light bg-opacity-75 px-3 px-md-4 py-2 d-flex flex-wrap align-items-center justify-content-between gap-2"
                id="registrationStepBar">
                <div class="d-flex align-items-center gap-1 gap-sm-2 flex-wrap text-xs">
                    <!-- Step 1 Item -->
                    <div class="d-flex align-items-center gap-1.5 px-2.5 py-1 rounded-pill bg-white border border-success shadow-2xs transition-all"
                        id="step1Item" style="cursor: pointer;" onclick="goToStep(1)"
                        title="Step 1: Pinpoint your land location">
                        <span
                            class="rounded-circle d-flex align-items-center justify-content-center text-white bg-success fw-bold"
                            style="width: 20px; height: 20px; font-size: 0.7rem;" id="step1Badge">1</span>
                        <span id="step1Title" class="fw-bold text-dark">Pinpoint Location</span>
                    </div>
                    <i class="bi bi-chevron-right text-muted opacity-50" style="font-size: 0.65rem;"></i>
                    <!-- Step 2 Item -->
                    <div class="d-flex align-items-center gap-1.5 px-2.5 py-1 rounded-pill bg-transparent text-muted transition-all"
                        id="step2Item" style="cursor: pointer;" onclick="goToStep(2)"
                        title="Step 2: Draw your plot boundary">
                        <span
                            class="rounded-circle d-flex align-items-center justify-content-center border bg-white text-secondary fw-bold"
                            style="width: 20px; height: 20px; font-size: 0.7rem;" id="step2Badge">2</span>
                        <span id="step2Title">Draw Plot</span>
                    </div>
                    <i class="bi bi-chevron-right text-muted opacity-50" style="font-size: 0.65rem;"></i>
                    <!-- Step 3 Item -->
                    <div class="d-flex align-items-center gap-1.5 px-2.5 py-1 rounded-pill bg-transparent text-muted transition-all"
                        id="step3Item" style="cursor: pointer;" onclick="goToStep(3)"
                        title="Step 3: Partition plots and submit details">
                        <span
                            class="rounded-circle d-flex align-items-center justify-content-center border bg-white text-secondary fw-bold"
                            style="width: 20px; height: 20px; font-size: 0.7rem;" id="step3Badge">3</span>
                        <span id="step3Title">Partitions & Info</span>
                    </div>
                </div>

                <!-- Active Step Guidance Pill -->
                <div class="d-flex align-items-center">
                    <span
                        class="badge bg-white border text-dark fw-normal rounded-pill px-2.5 py-1.5 shadow-2xs d-flex align-items-center gap-1.5"
                        id="stepGuideText" style="font-size: 0.72rem;">
                        <i class="bi bi-geo-alt-fill text-danger"></i>
                        <span><strong>Step 1:</strong> Search location or click anywhere on the map to pinpoint.</span>
                    </span>
                </div>
            </div>

            <div class="map-picker-wrapper position-relative">
                <!-- Top Black Gradient Scrim / Dim Overlay (behind searchbar and controls) -->
                <div class="map-top-gradient-scrim" id="mapTopGradientScrim"></div>

                <!-- Floating Location Search Bar (Compact UI) -->
                <div class="map-picker-search-box">
                    <div class="map-picker-input-group">
                        <i class="bi bi-search text-muted flex-shrink-0" style="font-size:0.75rem;"></i>
                        <input type="text" id="mapSearchInput" placeholder="Search location..." autocomplete="off">
                        <button type="button" class="map-picker-btn-icon d-none" id="mapSearchClearBtn"
                            title="Clear search" onclick="clearMapSearch()">
                            <i class="bi bi-x-circle-fill" style="font-size:0.75rem;"></i>
                        </button>
                        <button type="button" class="map-picker-btn-icon text-success" id="mapSearchGpsBtn"
                            title="Use my current GPS location" onclick="useCurrentLocation()">
                            <i class="bi bi-crosshair" style="font-size:0.85rem;"></i>
                        </button>
                    </div>
                    <!-- Autocomplete Dropdown List -->
                    <div class="map-picker-results" id="mapSearchResults" style="font-size: 0.78rem;"></div>
                </div>

                <!-- Floating Map Action Buttons Overlay (Drawing Tools, Area Badge, 2D/3D Mode) -->
                <div class="map-floating-toolbar d-flex align-items-center gap-1.5 flex-wrap"
                    style="position: absolute; top: 10px; right: 10px; z-index: 1000; justify-content: flex-end;">
                    <!-- Plot Controls -->
                    <div class="btn-group btn-group-sm p-0.5 bg-white bg-opacity-95 border rounded-pill shadow-sm"
                        role="group" id="plotDrawToolbar"
                        style="backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); box-shadow: 0 4px 14px rgba(0,0,0,0.12);">
                        <button type="button" class="btn btn-xs rounded-pill px-2 py-0.5 fw-semibold text-dark active"
                            id="drawModePanBtn" onclick="setPlotDrawMode('pan')" title="Pin Point Location">
                            <i class="bi bi-geo-alt-fill me-1 text-danger"></i>Pin Point
                        </button>
                    </div>



                    <button type="button"
                        class="btn btn-xs btn-outline-danger bg-white bg-opacity-95 rounded-pill px-2 py-0.5 text-xs d-none shadow-sm"
                        id="btnClearDrawnPlot" onclick="clearDrawnPlot()" title="Clear drawn plot boundary"
                        style="backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); box-shadow: 0 4px 14px rgba(0,0,0,0.12);">
                        <i class="bi bi-trash3 me-1"></i>Clear
                    </button>

                    <!-- 2D / 3D Switcher -->
                    <div class="btn-group btn-group-sm p-0.5 bg-white bg-opacity-95 border rounded-pill shadow-sm"
                        role="group"
                        style="backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); box-shadow: 0 4px 14px rgba(0,0,0,0.12);">
                        <button type="button" class="btn btn-xs rounded-pill px-2 py-0.5 fw-bold text-success active"
                            id="viewMode2DBtn" onclick="switchPickerMode('2d')">
                            <i class="bi bi-map me-1"></i>2D
                        </button>
                        <button type="button" class="btn btn-xs rounded-pill px-2 py-0.5 text-muted" id="viewMode3DBtn"
                            onclick="switchPickerMode('3d')">
                            <i class="bi bi-box-fill me-1 text-primary"></i>3D
                        </button>
                    </div>
                </div>

                <!-- Floating Drawing Status & Instruction Helper -->
                <div class="map-drawing-helper-pill d-none" id="mapDrawingHelper"
                    style="position: absolute; top: 48px; left: 10px; z-index: 1000; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border: 1px solid rgba(0,0,0,0.14); box-shadow: 0 4px 14px rgba(0,0,0,0.12); border-radius: 50rem; padding: 2px 10px; font-size: 0.72rem; max-width: calc(100% - 20px);">
                    <div class="d-flex align-items-center gap-1.5 text-nowrap">
                        <span id="mapDrawingHelperText" class="fw-semibold text-dark text-truncate" style="font-size: 0.72rem;">Click on the map to add boundary corners</span>
                        <button type="button"
                            class="btn btn-xs btn-success rounded-pill px-2 py-0 fw-bold d-none flex-shrink-0"
                            id="btnFinishPolygon" onclick="finishCurrentPolygon()" style="font-size: 0.7rem; height: 22px;">
                            <i class="bi bi-check-circle me-1"></i>Finish
                        </button>
                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-0 flex-shrink-0"
                            onclick="cancelPlotDrawing()" style="font-size: 0.7rem; height: 22px;">
                            Cancel
                        </button>
                    </div>
                </div>

                <!-- Floating Partition Plots Control Overlay (Centered horizontally over map area, elevated above status bar) -->
                <div class="position-absolute" id="plotControlOverlay"
                    style="bottom: 54px; left: 50%; transform: translateX(-50%); z-index: 1001; width: auto; min-width: 210px; max-width: 240px; pointer-events: auto;">
                    <div class="bg-white rounded-3 p-2 shadow-sm border"
                        style="border-color: var(--drive-border-subtle, #DFCFC2) !important;">
                        <div class="d-flex align-items-center justify-content-between gap-1 mb-1.5">
                            <span class="fw-bold text-dark d-flex align-items-center gap-1" style="font-size: 0.72rem;">
                                <i class="bi bi-grid-3x3-gap-fill text-success"></i>
                                <span>Plots</span>
                            </span>
                            <span class="badge bg-success-subtle text-success rounded-pill px-1.5 py-0.5"
                                id="plotCalcBadge" style="font-size: 0.65rem;">
                                4 plots (~62.5 m²)
                            </span>
                        </div>
                        <!-- Plot Count Buttons -->
                        <div class="d-flex align-items-center justify-content-between gap-1 mb-1.5"
                            role="group" aria-label="Partition plots count">
                            <button type="button" class="plot-count-btn" data-count="1" onclick="selectPlotCount(1, this)">1</button>
                            <button type="button" class="plot-count-btn" data-count="2" onclick="selectPlotCount(2, this)">2</button>
                            <button type="button" class="plot-count-btn active" data-count="4" onclick="selectPlotCount(4, this)">4</button>
                            <button type="button" class="plot-count-btn" data-count="6" onclick="selectPlotCount(6, this)">6</button>
                            <button type="button" class="plot-count-btn" data-count="8" onclick="selectPlotCount(8, this)">8</button>
                        </div>
                        <div class="pt-1.5 border-top d-flex align-items-center justify-content-between"
                            style="font-size: 0.66rem; border-color: var(--drive-border-subtle, #DFCFC2) !important;">
                            <span class="text-muted" id="drawnBoundarySummary">Standard Grid</span>
                            <button type="button" class="btn btn-link text-success p-0 text-decoration-none fw-semibold"
                                style="font-size: 0.68rem; box-shadow: none !important; transform: none !important;" onclick="openLandAreaModal()" title="Resize plot area">
                                <i class="bi bi-rulers me-0.5"></i>Change Area
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 2D Leaflet Map Canvas (Above the Form - Expanded Size) -->
                <div id="pickerMap" class="register-picker-map" style="height: 100%; min-height: 520px; background-color: #e9f2ff;"></div>

                <!-- Floating Map Controls: Satellite Toggle & GPS Go To My Location Button -->
                <div class="position-absolute d-flex flex-column gap-2"
                    style="bottom: 24px; right: 14px; z-index: 1001;" id="mapFloatingFabControls">
                    <button type="button" class="map-fab-btn" onclick="toggleSatelliteLayer()" title="Toggle Satellite View"
                        id="btnToggleSatellite">
                        <i class="bi bi-layers text-primary fs-5" id="btnToggleSatelliteIcon"></i>
                    </button>
                    <button type="button" class="map-fab-btn" onclick="useCurrentLocation()" title="Go to My Location"
                        id="btnGoToMyLocation">
                        <i class="bi bi-crosshair text-success fs-5"></i>
                    </button>
                </div>

                <!-- 3D City & Garden Visualization Container -->
                <div id="register3DMapContainer" class="map3d-container" style="height: 100%; min-height: 520px;">
                </div>

                <!-- Pinned Address / Coordinates Floating Status Pill -->
                <div class="map-picker-status-bar position-absolute shadow-sm" id="mapStatusBar"
                    style="bottom: 14px; left: 14px; z-index: 1000; background: rgba(255, 255, 255, 0.96); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border: 1.5px solid rgba(0, 0, 0, 0.14); border-radius: 50rem; padding: 4px 12px; font-size: 0.76rem; font-weight: 600; color: var(--drive-text-main, #3E2B1E); max-width: min(420px, calc(100% - 150px)); pointer-events: auto; display: flex; align-items: center; gap: 8px;">
                    <div class="d-flex align-items-center gap-1.5 text-truncate">
                        <i class="bi bi-pin-map-fill text-danger flex-shrink-0"></i>
                        <span class="text-truncate" id="pinnedAddressText">No location selected yet</span>
                    </div>
                    <span class="badge bg-success-subtle text-success ms-2 flex-shrink-0" id="coordsBadge"
                        style="font-size:0.68rem; display:none;"></span>
                </div>
            </div>
        </div>

        <!-- Below: Property Registration Form -->
        <div class="card border rounded-4 bg-white p-3 p-md-4 shadow-sm mb-4"
            style="border-color: var(--drive-border) !important;">
            <form action="register.php" method="POST" enctype="multipart/form-data" id="landRegistrationForm">
                <input type="hidden" name="action" value="register">
                <input type="hidden" name="lot_polygon" id="lot_polygon">
                <input type="hidden" id="form_plot_count" value="4">

                <div class="d-flex align-items-center justify-content-between border-bottom pb-2.5 mb-3">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 0.95rem;">
                        <i class="bi bi-card-checklist text-success fs-5"></i>
                        <span>Property Details & Registration Specifications</span>
                    </h6>
                    <span class="text-muted text-xs">Fill in your land details below</span>
                </div>

                <div class="row g-3">
                    <!-- Land Title -->
                    <div class="col-md-6">
                        <label for="title" class="form-label text-secondary fw-semibold text-xs mb-1">PROPERTY
                            NAME</label>
                        <input type="text" class="form-control drive-form-control w-100" id="title" name="title"
                            required placeholder="">
                    </div>

                    <!-- Address -->
                    <div class="col-md-6">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <label for="address"
                                class="form-label text-secondary m-0 fw-semibold text-xs">ADDRESS</label>
                            <button type="button"
                                class="btn btn-link text-success text-decoration-none p-0 text-xs fw-semibold"
                                onclick="searchAddressFromInput()">
                                <i class="bi bi-geo-alt-fill me-1"></i>Find on map
                            </button>
                        </div>
                        <div class="input-group">
                            <input type="text" class="form-control drive-form-control" id="address" name="address"
                                required placeholder=""
                                onkeydown="if(event.key==='Enter'){event.preventDefault();searchAddressFromInput();}">
                            <button class="btn btn-sm btn-outline-success px-2.5" type="button"
                                onclick="searchAddressFromInput()" title="Locate this address on map">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Size & Coordinates -->
                    <div class="col-md-4">
                        <label for="area" class="form-label text-secondary fw-semibold text-xs mb-1">TOTAL AREA
                            (m²)</label>
                        <input type="number" step="0.1" class="form-control drive-form-control w-100" id="area"
                            name="area" required placeholder="250" oninput="updatePlotCalculation()">
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <label for="latitude"
                                class="form-label text-secondary m-0 fw-semibold text-xs">LATITUDE</label>
                            <span class="text-success text-xs fw-semibold"><i class="bi bi-pencil me-0.5"></i>Type or
                                click map</span>
                        </div>
                        <input type="number" step="any" class="form-control drive-form-control w-100" id="latitude"
                            name="latitude" required placeholder="e.g. 14.599512">
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <label for="longitude"
                                class="form-label text-secondary m-0 fw-semibold text-xs">LONGITUDE</label>
                            <span class="text-success text-xs fw-semibold"><i class="bi bi-pencil me-0.5"></i>Type or
                                click map</span>
                        </div>
                        <input type="number" step="any" class="form-control drive-form-control w-100" id="longitude"
                            name="longitude" required placeholder="e.g. 120.984222">
                    </div>


                    <!-- Description (col-md-6) -->
                    <div class="col-md-6">
                        <label for="description"
                            class="form-label text-secondary fw-semibold text-xs mb-1">DESCRIPTION</label>
                        <textarea class="form-control drive-form-control w-100" id="description" name="description"
                            rows="4" placeholder=""></textarea>
                    </div>

                    <!-- Crops That Can Grow (col-md-6) -->
                    <div class="col-md-6">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <label class="form-label text-secondary m-0 fw-semibold text-xs">CROPS</label>
                            <button type="button"
                                class="btn btn-sm btn-drive-secondary rounded-pill px-2.5 py-1 d-flex align-items-center gap-1.5 text-xs"
                                data-bs-toggle="modal" data-bs-target="#permittedCropsModal">
                                <i data-lucide="search" style="width: 13px; height: 13px;"></i>
                                <span>Select Crops</span>
                            </button>
                        </div>

                        <!-- Selected Crops Summary Box -->
                        <div id="selectedCropsSummary"
                            class="p-3 border rounded-4 bg-light d-flex flex-wrap gap-2 align-items-center"
                            style="min-height: 100px; max-height: 120px; overflow-y: auto; border-color: var(--drive-border) !important;">
                            <span class="text-muted text-xs italic" id="emptyCropsNotice">No specific crops selected.
                                Click "Select Crops" to specify crops that can grow on this land.</span>
                        </div>

                        <!-- Hidden container for checked inputs submitted with form -->
                        <div id="hiddenSeedInputs"></div>
                    </div>

                    <!-- Available Amenities & Utilities (col-md-6) -->
                    <div class="col-md-6">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <label class="form-label text-secondary m-0 fw-semibold text-xs">AVAILABLE AMENITIES &amp; UTILITIES</label>
                            <button type="button"
                                class="btn btn-sm btn-drive-secondary rounded-pill px-2.5 py-1 d-flex align-items-center gap-1.5 text-xs"
                                data-bs-toggle="modal" data-bs-target="#amenitiesModal">
                                <i data-lucide="search" style="width: 13px; height: 13px;"></i>
                                <span>Select Amenities</span>
                            </button>
                        </div>

                        <!-- Selected Amenities Summary Box -->
                        <div id="selectedAmenitiesSummary"
                            class="p-3 border rounded-4 bg-light d-flex flex-wrap gap-2 align-items-center"
                            style="min-height: 100px; max-height: 120px; overflow-y: auto; border-color: var(--drive-border) !important;">
                            <span class="text-muted text-xs italic" id="emptyAmenitiesNotice">No amenities selected.
                                Click "Select Amenities" to specify available water, electricity, and site facilities.</span>
                        </div>

                        <!-- Hidden container for submitted amenities[] -->
                        <div id="hiddenAmenitiesInputs"></div>
                    </div>

                    <!-- REQUIRED PLOT PARTITIONING & BASE RENT CONFIGURATION (col-12) -->
                    <div class="col-12 mt-3 pt-3 border-top">
                        <div
                            class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-3">
                            <div>
                                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2"
                                    style="font-size: 0.95rem;">
                                    <i class="bi bi-grid-3x3-gap-fill text-success fs-5"></i>
                                    <span>Plot Partitioning &amp; Base Rent Specification</span>
                                    <span
                                        class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill"
                                        style="font-size: 10px; padding: 3px 8px;">Required</span>
                                </h6>
                                <p class="text-secondary mb-0 mt-1" style="font-size: 0.78rem;">
                                    Partition this land into individual plots and set the required base rent (₱/year)
                                    for each plot. Gardeners will see this base rent and may submit counter-offers
                                    during lease negotiations.
                                </p>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                                <span
                                    class="badge bg-light text-secondary border rounded-pill px-3 py-1.5 text-xs font-medium"
                                    id="plotCalcSummaryBadge">
                                    <i class="bi bi-info-circle text-primary me-1"></i> Standard: ₱110,000/yr for ~50m²
                                </span>
                            </div>
                        </div>

                        <!-- Plot Count Selector & Quick Presets -->
                        <div class="p-3 rounded-4 bg-light border mb-3"
                            style="border-color: var(--drive-border) !important;">
                            <div class="row g-3 align-items-center">
                                <div class="col-sm-5 col-md-4">
                                    <label class="form-label text-secondary fw-semibold text-xs mb-1">NUMBER OF PLOTS
                                        (MIN: 1)</label>
                                    <div class="input-group">
                                        <button type="button" class="btn btn-outline-secondary px-3"
                                            onclick="adjustPlotCount(-1)">
                                            <i class="bi bi-dash-lg"></i>
                                        </button>
                                        <input type="number" id="plot_count" name="plot_count"
                                            class="form-control text-center fw-bold text-dark" value="4" min="1"
                                            max="20" onchange="onPlotCountChanged(this.value)">
                                        <button type="button" class="btn btn-outline-secondary px-3"
                                            onclick="adjustPlotCount(1)">
                                            <i class="bi bi-plus-lg"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-sm-7 col-md-8">
                                    <label class="form-label text-secondary fw-semibold text-xs mb-1">QUICK PARTITION
                                        PRESETS</label>
                                    <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                        <button type="button"
                                            class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 text-xs plot-preset-btn"
                                            onclick="selectPlotCountPreset(1, this)">1 Plot (Single)</button>
                                        <button type="button"
                                            class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 text-xs plot-preset-btn"
                                            onclick="selectPlotCountPreset(2, this)">2 Plots</button>
                                        <button type="button"
                                            class="btn btn-sm btn-success rounded-pill px-3 py-1 text-xs text-white plot-preset-btn active"
                                            onclick="selectPlotCountPreset(4, this)">4 Plots</button>
                                        <button type="button"
                                            class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 text-xs plot-preset-btn"
                                            onclick="selectPlotCountPreset(6, this)">6 Plots</button>
                                        <button type="button"
                                            class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 text-xs plot-preset-btn"
                                            onclick="selectPlotCountPreset(8, this)">8 Plots</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Interactive Plot Breakdown Cards Table -->
                        <div class="border rounded-4 overflow-hidden mb-3 bg-white"
                            style="border-color: var(--drive-border) !important;">
                            <div class="p-2.5 bg-light border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2"
                                style="border-color: var(--drive-border) !important;">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-xs fw-bold text-dark text-uppercase"
                                        style="letter-spacing: 0.5px;">Partition Sizing &amp; Base Rent</span>
                                    <span id="allocatedAreaBadge"
                                        class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-0.5 text-xs fw-semibold">
                                        <i class="bi bi-check-circle-fill me-1"></i>250 / 250 m² (100% Allocated)
                                    </span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button"
                                        class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-0.5 text-xs fw-semibold"
                                        onclick="resetPlotsToEqualSizes()"
                                        title="Redistribute total land area equally across all plots">
                                        <i class="bi bi-distribute-vertical me-1 text-success"></i>Distribute Equally
                                    </button>
                                </div>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="plotsConfigTable">
                                    <thead class="table-light text-xs text-secondary text-uppercase"
                                        style="letter-spacing: 0.5px;">
                                        <tr>
                                            <th style="width: 25%;" class="ps-3 py-2.5">Plot Identifier</th>
                                            <th style="width: 22%;" class="py-2.5">Area (m²) <span
                                                    class="text-success text-xxs font-normal ms-1">(Auto-balancing)</span>
                                            </th>
                                            <th style="width: 33%;" class="py-2.5">Monthly Fee (₱ / Month) <span
                                                    class="text-danger">*</span></th>
                                            <th style="width: 20%;" class="pe-3 py-2.5 text-end">Annual Projection</th>
                                        </tr>
                                    </thead>
                                    <tbody id="plotsConfigTableBody" class="text-sm">
                                        <!-- Rendered dynamically by JS -->
                                    </tbody>
                                </table>
                            </div>
                            <div class="p-3 bg-light border-top d-flex flex-wrap align-items-center justify-content-between gap-3"
                                style="border-color: var(--drive-border) !important;">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-secondary text-xs fw-semibold">TOTAL MONTHLY EARNINGS:</span>
                                    <span class="fs-5 fw-bold text-success"
                                        id="totalProjectedMonthlyRent">₱36,666.67</span>
                                    <span class="text-muted text-xs">/ month</span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="text-secondary text-xs fw-semibold">ANNUAL PROJECTION:</span>
                                    <span class="fs-6 fw-bold text-dark"
                                        id="totalProjectedAnnualRent">₱440,000.00</span>
                                    <span class="text-muted text-xs">/ year</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- OCT Document Upload (col-12) -->
                    <div class="col-12">
                        <label class="form-label text-secondary fw-semibold text-xs mb-1">UPLOAD OCT DOCUMENT (ORIGINAL
                            CERTIFICATE OF TITLE)</label>
                        <div class="border rounded-4 p-4 text-center bg-light"
                            style="border-style: dashed !important; border-color: var(--drive-border) !important;">
                            <i class="bi bi-file-earmark-pdf fs-1 text-primary mb-2 d-inline-block"></i>
                            <p class="mb-1 text-dark fw-medium" style="font-size: 0.85rem;">Upload Original Certificate
                                of Title (OCT)</p>
                            <span class="text-secondary d-block mb-3" style="font-size: 0.75rem;">Accepted formats: PDF,
                                JPEG, or PNG (Max 10MB)</span>
                            <input type="file" id="oct_document" name="oct_document" accept=".pdf,.jpg,.jpeg,.png"
                                class="d-none" onchange="updateUploadLabel(this)">
                            <button type="button" class="btn btn-sm btn-drive-secondary rounded-pill px-3 py-1 text-xs"
                                onclick="document.getElementById('oct_document').click()">
                                <i class="bi bi-upload me-1"></i>Select OCT File
                            </button>
                            <span id="file-count" class="d-block mt-2 text-success fw-medium"
                                style="font-size: 12px;"></span>
                        </div>
                    </div>

                    <!-- Form Action Buttons -->
                    <div class="col-12 d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="lands.php"
                            class="btn btn-sm btn-drive-secondary rounded-pill px-3 py-1.5 text-xs">Cancel</a>
                        <button type="submit"
                            class="btn btn-sm btn-drive-primary rounded-pill px-3.5 py-1.5 text-xs fw-semibold">Submit
                            Property</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</main>



<?php
require_once __DIR__ . '/../includes/crops_library.php';
$seedTypes = getPermittedCropsSeedList('../');
$cropCategories = getCropCategories();
$amenityTypes = get_standard_amenities('../');
?>

<!-- Permitted Crops Selection Modal -->
<div class="modal fade" id="permittedCropsModal" tabindex="-1" aria-hidden="true" style="z-index: 9999;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content drive-modal-content rounded-4 overflow-hidden border-0 shadow">
            <div class="modal-header border-bottom px-4 py-3 bg-white">
                <div>
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" style="font-size: 1rem;">
                        <img src="../assets/crop-icons/generic-plant/generic-plant.svg" class="w-6 h-6 object-contain"
                            alt="Crops">
                        Select Crops
                    </h5>
                    <p class="text-secondary mb-0" style="font-size: 0.75rem;">Choose the crops and plants you know can
                        grow well on this land.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4 bg-light">
                <!-- Search & Quick Selection Actions -->
                <div class="d-flex flex-column flex-sm-row gap-3 align-items-center justify-content-between mb-3">
                    <div class="position-relative w-100 me-sm-2">
                        <i data-lucide="search" class="position-absolute text-secondary"
                            style="left: 12px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px;"></i>
                        <input type="text" id="cropSearchInput"
                            class="form-control drive-form-control ps-5 py-2 text-xs rounded-pill"
                            placeholder="Search by English or local name (e.g. Mango, Mangga, Pechay, Ampalaya)..."
                            onkeyup="filterCropChips()">
                    </div>
                    <div class="d-flex gap-1.5 flex-shrink-0">
                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 text-xs"
                            onclick="selectAllCrops(true)">Select All</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 text-xs"
                            onclick="selectAllCrops(false)">Clear All</button>
                    </div>
                </div>

                <!-- Category Filters Scrollable Pills -->
                <div class="d-flex gap-1.5 overflow-x-auto pb-2 mb-3" id="cropCategoryFilters"
                    style="white-space: nowrap;">
                    <button type="button"
                        class="btn btn-xs btn-primary rounded-pill px-2.5 py-1 text-xs crop-cat-pill active flex-shrink-0"
                        onclick="setCropCategoryFilter('all', this)">All (<?php echo count($seedTypes); ?>)</button>
                    <?php foreach ($cropCategories as $catId => $cat): ?>
                        <button type="button"
                            class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 text-xs crop-cat-pill flex-shrink-0"
                            onclick="setCropCategoryFilter('<?php echo $catId; ?>', this)">
                            <span><?php echo $cat['icon']; ?>     <?php echo $cat['name']; ?></span>
                            <span class="badge bg-light text-dark ms-1 rounded-pill"
                                style="font-size: 10px;"><?php echo $cat['count']; ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>

                <!-- Crop Grid Container -->
                <div class="row g-2 overflow-y-auto" id="modalCropGrid" style="max-height: 340px;">
                    <?php foreach ($seedTypes as $seed): ?>
                        <div class="col-6 col-sm-4 col-md-3 crop-grid-item"
                            data-name="<?php echo strtolower($seed['label'] . ' ' . $seed['value'] . ' ' . ($seed['local_name'] ?? '')); ?>"
                            data-category="<?php echo $seed['category_id'] ?? ''; ?>">
                            <div class="modal-crop-chip p-2.5 border rounded-3 bg-white d-flex align-items-center justify-content-between gap-2"
                                data-value="<?php echo $seed['value']; ?>" data-label="<?php echo $seed['label']; ?>"
                                data-icon="<?php echo $seed['icon']; ?>"
                                style="cursor: pointer; transition: all 0.15s; border-color: var(--drive-border) !important; user-select: none;"
                                onclick="toggleCropSelection('<?php echo $seed['value']; ?>', '<?php echo $seed['label']; ?>', '<?php echo $seed['icon']; ?>', this)">
                                <div class="d-flex align-items-center gap-2 text-truncate">
                                    <div
                                        class="w-8 h-8 rounded-full flex items-center justify-center bg-drive-canvas border border-drive-border flex-shrink-0">
                                        <img src="<?php echo $seed['icon']; ?>" alt="<?php echo $seed['label']; ?>"
                                            class="w-5 h-5 object-contain" loading="lazy">
                                    </div>
                                    <span class="fw-semibold text-dark text-xs truncate"
                                        title="<?php echo $seed['label']; ?>"><?php echo $seed['label']; ?></span>
                                </div>
                                <div class="crop-check-icon text-primary hidden">
                                    <i data-lucide="check-circle-2" style="width: 16px; height: 16px; color: #1a73e8;"></i>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="modal-footer border-top bg-white px-3 py-2.5 d-flex justify-content-between align-items-center">
                <span class="text-secondary text-xs fw-semibold"><span id="selectedCountText">0</span> crops
                    selected</span>
                <button type="button" class="btn btn-sm btn-drive-primary rounded-pill px-3.5 py-1.5 text-xs"
                    data-bs-dismiss="modal">Done</button>
            </div>
        </div>
    </div>
</div>

<!-- Available Amenities Selection Modal -->
<div class="modal fade" id="amenitiesModal" tabindex="-1" aria-hidden="true" style="z-index: 9999;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content drive-modal-content rounded-4 overflow-hidden border-0 shadow">
            <div class="modal-header border-bottom px-4 py-3 bg-white">
                <div>
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" style="font-size: 1rem;">
                        <img src="../assets/amenity-icons/general.svg" class="w-6 h-6 object-contain" alt="Amenities">
                        Select Amenities &amp; Utilities
                    </h5>
                    <p class="text-secondary mb-0" style="font-size: 0.75rem;">Choose the utilities and infrastructure available for gardeners on this land.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4 bg-light">
                <!-- Search & Quick Selection Actions -->
                <div class="d-flex flex-column flex-sm-row gap-3 align-items-center justify-content-between mb-3">
                    <div class="position-relative w-100 me-sm-2">
                        <i data-lucide="search" class="position-absolute text-secondary"
                            style="left: 12px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px;"></i>
                        <input type="text" id="amenitySearchInput"
                            class="form-control drive-form-control ps-5 py-2 text-xs rounded-pill"
                            placeholder="Search amenities (e.g. Water, Electricity, Irrigation, Storage, Fencing)..."
                            onkeyup="filterAmenityChips()">
                    </div>
                    <div class="d-flex gap-1.5 flex-shrink-0">
                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 text-xs"
                            onclick="selectAllAmenities(true)">Select All</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 text-xs"
                            onclick="selectAllAmenities(false)">Clear All</button>
                    </div>
                </div>

                <!-- Custom Amenity Quick Adder -->
                <div class="d-flex align-items-center gap-2 mb-3 bg-white p-2.5 border rounded-3" style="border-color: var(--drive-border) !important;">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <i data-lucide="plus" style="width: 14px; height: 14px;" class="text-success"></i>
                        </span>
                        <input type="text" id="modalCustomAmenityInput" class="form-control drive-form-control border-start-0 text-xs"
                            placeholder="Add other amenity (e.g. CCTV, Deep Well, Greenhouse, Solar Lights)..."
                            onkeydown="if(event.key==='Enter'){event.preventDefault();addCustomAmenityFromModal();}">
                        <button type="button" class="btn btn-drive-secondary px-3 text-xs fw-semibold" onclick="addCustomAmenityFromModal()">
                            Add Amenity
                        </button>
                    </div>
                </div>

                <!-- Amenity Grid Container -->
                <div class="row g-2 overflow-y-auto" id="modalAmenityGrid" style="max-height: 320px;">
                    <?php foreach ($amenityTypes as $amenity): ?>
                        <div class="col-6 col-sm-4 col-md-3 amenity-grid-item"
                            data-name="<?php echo strtolower($amenity['label'] . ' ' . ($amenity['desc'] ?? '')); ?>">
                            <div class="modal-amenity-chip p-2.5 border rounded-3 bg-white d-flex align-items-center justify-content-between gap-2 h-100"
                                data-value="<?php echo htmlspecialchars($amenity['value']); ?>"
                                data-label="<?php echo htmlspecialchars($amenity['label']); ?>"
                                data-icon="<?php echo htmlspecialchars($amenity['icon']); ?>"
                                style="cursor: pointer; transition: all 0.15s; border-color: var(--drive-border) !important; user-select: none;"
                                onclick="toggleAmenitySelection('<?php echo htmlspecialchars(addslashes($amenity['value'])); ?>', '<?php echo htmlspecialchars(addslashes($amenity['label'])); ?>', '<?php echo htmlspecialchars($amenity['icon']); ?>', this)">
                                <div class="d-flex align-items-center gap-2 text-truncate">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center bg-drive-canvas border border-drive-border flex-shrink-0">
                                        <img src="<?php echo $amenity['icon']; ?>" alt="<?php echo htmlspecialchars($amenity['label']); ?>"
                                            class="w-5 h-5 object-contain" loading="lazy">
                                    </div>
                                    <span class="fw-semibold text-dark text-xs truncate"
                                        title="<?php echo htmlspecialchars($amenity['label']); ?>"><?php echo htmlspecialchars($amenity['label']); ?></span>
                                </div>
                                <div class="amenity-check-icon text-primary hidden">
                                    <i data-lucide="check-circle-2" style="width: 16px; height: 16px; color: #1a73e8;"></i>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="modal-footer border-top bg-white px-3 py-2.5 d-flex justify-content-between align-items-center">
                <span class="text-secondary text-xs fw-semibold"><span id="selectedAmenitiesCountText">0</span> amenities selected</span>
                <button type="button" class="btn btn-sm btn-drive-primary rounded-pill px-3.5 py-1.5 text-xs"
                    data-bs-dismiss="modal">Done</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal to Set Land Area and Individual Plot Sizes Before Drawing -->
<div class="modal fade" id="landAreaModal" tabindex="-1" aria-labelledby="landAreaModalLabel" aria-hidden="true"
    style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 520px;">
        <div class="modal-content border-0 rounded-4 shadow-xl overflow-hidden">
            <div class="modal-header border-0 bg-success text-white py-3 px-4">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-circle bg-white text-success d-flex align-items-center justify-content-center flex-shrink-0"
                        style="width: 34px; height: 34px;">
                        <i class="bi bi-grid-3x3-gap-fill fs-6"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold text-white mb-0" id="landAreaModalLabel">Set Land Area &amp; Plot
                            Sizes</h6>
                        <span class="text-white-50" style="font-size: 0.72rem;">Configure total parcel &amp; individual
                            plot sizes</span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"
                    onclick="closeLandAreaModal()"></button>
            </div>
            <div class="modal-body p-4 bg-white">
                <!-- Total Land Area Input -->
                <div class="mb-3">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <label for="modalAreaInput" class="form-label text-dark fw-bold text-xs mb-0">TOTAL LAND AREA
                            (m²)</label>
                        <span class="text-muted text-xxs">Overall boundary size</span>
                    </div>
                    <div class="input-group">
                        <input type="number" step="1" min="10" max="100000"
                            class="form-control drive-form-control form-control-lg fw-bold text-success fs-4"
                            id="modalAreaInput" value="250" placeholder="e.g. 250"
                            oninput="onModalTotalAreaChanged(this.value)"
                            onkeydown="if(event.key==='Enter'){event.preventDefault();confirmLandAreaAndDraw();}"
                            required>
                        <span class="input-group-text fw-bold">m²</span>
                    </div>
                </div>

                <!-- Number of Plots Selection inside Modal -->
                <div class="mb-3 p-2.5 bg-light rounded-3 border">
                    <div class="d-flex align-items-center justify-content-between mb-1.5">
                        <span class="text-secondary fw-semibold text-xs">NUMBER OF PLOTS:</span>
                        <div class="btn-group btn-group-sm" role="group" id="modalPlotCountButtons">
                            <button type="button"
                                class="btn btn-xs modal-count-btn btn-outline-secondary rounded-pill px-2.5 py-0.5 text-xs fw-bold"
                                data-count="1" onclick="setModalPlotCount(1, this)">1</button>
                            <button type="button"
                                class="btn btn-xs modal-count-btn btn-outline-secondary rounded-pill px-2.5 py-0.5 text-xs fw-bold"
                                data-count="2" onclick="setModalPlotCount(2, this)">2</button>
                            <button type="button"
                                class="btn btn-xs modal-count-btn btn-success text-white active rounded-pill px-2.5 py-0.5 text-xs fw-bold"
                                data-count="4" onclick="setModalPlotCount(4, this)">4</button>
                            <button type="button"
                                class="btn btn-xs modal-count-btn btn-outline-secondary rounded-pill px-2.5 py-0.5 text-xs fw-bold"
                                data-count="6" onclick="setModalPlotCount(6, this)">6</button>
                            <button type="button"
                                class="btn btn-xs modal-count-btn btn-outline-secondary rounded-pill px-2.5 py-0.5 text-xs fw-bold"
                                data-count="8" onclick="setModalPlotCount(8, this)">8</button>
                        </div>
                    </div>
                    <p class="text-muted mb-0" style="font-size: 0.72rem;">
                        Choose partition count, then modify any plot size below. Other plots auto-balance to total.
                    </p>
                </div>

                <!-- Partition Plot Sizes Breakdown inside Modal -->
                <div class="mb-3">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div class="d-flex align-items-center gap-1.5">
                            <span class="fw-bold text-dark text-xs text-uppercase" style="letter-spacing: 0.5px;">Plot
                                Sizes</span>
                            <span id="modalAllocatedBadge"
                                class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5 text-xxs fw-semibold">
                                250 / 250 m²
                            </span>
                        </div>
                        <button type="button"
                            class="btn btn-xs btn-link text-success p-0 text-decoration-none fw-semibold"
                            style="font-size: 0.72rem;" onclick="resetModalPlotsEqual()">
                            <i class="bi bi-distribute-vertical me-0.5"></i>Distribute Equally
                        </button>
                    </div>

                    <!-- Scrollable Plot Size Rows -->
                    <div id="modalPlotsList" class="d-flex flex-column gap-2 p-2 bg-light rounded-3 border"
                        style="max-height: 200px; overflow-y: auto;">
                        <!-- Dynamically populated by renderModalPlotsList() -->
                    </div>
                </div>

                <div class="d-grid gap-2 pt-2 border-top">
                    <button type="button"
                        class="btn btn-sm btn-success rounded-pill py-2 fw-semibold d-flex align-items-center justify-content-center gap-2 shadow-sm text-xs"
                        onclick="confirmLandAreaAndDraw()">
                        <i class="bi bi-bounding-box"></i>
                        <span>Apply &amp; Place Plots (<span id="modalBtnAreaText">250 m²</span>)</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>



<script>
    const selectedCrops = new Map();

    function updateUploadLabel(input) {
        const file = input.files && input.files[0];
        const label = document.getElementById('file-count');
        if (file) {
            const sizeKB = (file.size / 1024).toFixed(1);
            label.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i>Selected: <strong>${file.name}</strong> (${sizeKB} KB)`;
        } else {
            label.textContent = '';
        }
    }

    function toggleCropSelection(val, label, icon, element) {
        const checkIcon = element.querySelector('.crop-check-icon');
        if (selectedCrops.has(val)) {
            selectedCrops.delete(val);
            element.classList.remove('border-primary', 'bg-blue-50/50');
            element.style.borderColor = 'var(--drive-border)';
            if (checkIcon) checkIcon.classList.add('hidden');
        } else {
            selectedCrops.set(val, { value: val, label: label, icon: icon });
            element.classList.add('border-primary', 'bg-blue-50/50');
            element.style.borderColor = '#1a73e8';
            if (checkIcon) checkIcon.classList.remove('hidden');
        }
        renderSelectedCropsSummary();
    }

    function selectAllCrops(select) {
        document.querySelectorAll('.modal-crop-chip').forEach(chip => {
            const val = chip.getAttribute('data-value');
            const label = chip.getAttribute('data-label');
            const icon = chip.getAttribute('data-icon');
            const checkIcon = chip.querySelector('.crop-check-icon');

            if (select) {
                selectedCrops.set(val, { value: val, label: label, icon: icon });
                chip.classList.add('border-primary', 'bg-blue-50/50');
                chip.style.borderColor = '#1a73e8';
                if (checkIcon) checkIcon.classList.remove('hidden');
            } else {
                selectedCrops.delete(val);
                chip.classList.remove('border-primary', 'bg-blue-50/50');
                chip.style.borderColor = 'var(--drive-border)';
                if (checkIcon) checkIcon.classList.add('hidden');
            }
        });
        renderSelectedCropsSummary();
    }

    let currentCropCategory = 'all';

    function setCropCategoryFilter(catId, btn) {
        currentCropCategory = catId;
        document.querySelectorAll('.crop-cat-pill').forEach(b => {
            b.classList.remove('active', 'btn-primary');
            b.classList.add('btn-outline-secondary');
        });
        if (btn) {
            btn.classList.add('active', 'btn-primary');
            btn.classList.remove('btn-outline-secondary');
        }
        filterCropChips();
    }

    function filterCropChips() {
        const query = (document.getElementById('cropSearchInput').value || '').toLowerCase().trim();
        document.querySelectorAll('.crop-grid-item').forEach(item => {
            const name = (item.getAttribute('data-name') || '').toLowerCase();
            const cat = item.getAttribute('data-category') || '';
            const matchesQuery = !query || name.includes(query);
            const matchesCategory = currentCropCategory === 'all' || cat === currentCropCategory;
            if (matchesQuery && matchesCategory) {
                item.classList.remove('d-none');
            } else {
                item.classList.add('d-none');
            }
        });
    }

    function renderSelectedCropsSummary() {
        const summaryBox = document.getElementById('selectedCropsSummary');
        const hiddenInputs = document.getElementById('hiddenSeedInputs');
        const countText = document.getElementById('selectedCountText');

        if (countText) countText.textContent = selectedCrops.size;

        if (selectedCrops.size === 0) {
            summaryBox.innerHTML = `<span class="text-muted text-xs italic" id="emptyCropsNotice">No specific crops selected. Click "Select Crops" to choose crops that can grow on this land.</span>`;
            hiddenInputs.innerHTML = '';
            return;
        }

        let summaryHtml = '';
        let inputsHtml = '';

        selectedCrops.forEach((crop) => {
            summaryHtml += `
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1.5 border rounded-pill bg-white text-xs fw-semibold text-dark shadow-xs" style="border-color: var(--drive-border) !important;">
                    <img src="${crop.icon}" alt="${crop.label}" class="w-4 h-4 object-contain">
                    <span>${crop.label}</span>
                    <button type="button" class="btn-close text-xs ms-1" style="width: 10px; height: 10px;" onclick="toggleCropSelection('${crop.value}', '${crop.label}', '${crop.icon}', document.querySelector('[data-value=\"${crop.value}\"]'))"></button>
                </div>
            `;
            inputsHtml += `<input type="checkbox" name="allowed_seeds[]" value="${crop.value}" checked class="d-none">`;
        });

        summaryBox.innerHTML = summaryHtml;
        hiddenInputs.innerHTML = inputsHtml;

        if (typeof renderSubdivisions === 'function') {
            renderSubdivisions();
        }
    }

    // Amenities & Utilities Selection Logic
    const selectedAmenities = new Map();

    window.getAmenityIcon = function(name) {
        const lower = (name || '').toLowerCase();
        let file = 'general.svg';
        if (lower.includes('water') || lower.includes('well') || lower.includes('drain') || lower.includes('spigot') || lower.includes('tap')) file = 'water-source.svg';
        else if (lower.includes('elect') || lower.includes('power') || lower.includes('grid') || lower.includes('light')) file = 'electricity.svg';
        else if (lower.includes('irrig') || lower.includes('sprink') || lower.includes('hose') || lower.includes('drip')) file = 'irrigation.svg';
        else if (lower.includes('tool') || lower.includes('shed') || lower.includes('stor') || lower.includes('barn') || lower.includes('equip')) file = 'tool-storage.svg';
        else if (lower.includes('fenc') || lower.includes('gate') || lower.includes('secur') || lower.includes('wall') || lower.includes('perim')) file = 'perimeter-fencing.svg';
        else if (lower.includes('compost') || lower.includes('recyc') || lower.includes('soil') || lower.includes('organic') || lower.includes('fertil')) file = 'compost-area.svg';
        else if (lower.includes('rest') || lower.includes('toilet') || lower.includes('wash') || lower.includes('wc') || lower.includes('cr') || lower.includes('comfort')) file = 'restroom.svg';
        else if (lower.includes('solar') || lower.includes('sun') || lower.includes('panel')) file = 'solar-power.svg';
        return `../assets/amenity-icons/${file}`;
    };

    window.toggleAmenitySelection = function(val, label, icon, element) {
        if (!icon) icon = getAmenityIcon(val);
        const checkIcon = element ? element.querySelector('.amenity-check-icon') : null;
        if (selectedAmenities.has(val)) {
            selectedAmenities.delete(val);
            if (element) {
                element.classList.remove('border-primary', 'bg-blue-50/50');
                element.style.borderColor = 'var(--drive-border)';
            }
            if (checkIcon) checkIcon.classList.add('hidden');
        } else {
            selectedAmenities.set(val, { value: val, label: label || val, icon: icon });
            if (element) {
                element.classList.add('border-primary', 'bg-blue-50/50');
                element.style.borderColor = '#1a73e8';
            }
            if (checkIcon) checkIcon.classList.remove('hidden');
        }
        renderSelectedAmenitiesSummary();
    };

    window.selectAllAmenities = function(select) {
        document.querySelectorAll('.modal-amenity-chip').forEach(chip => {
            const val = chip.getAttribute('data-value');
            const label = chip.getAttribute('data-label') || val;
            const icon = chip.getAttribute('data-icon') || getAmenityIcon(val);
            const checkIcon = chip.querySelector('.amenity-check-icon');

            if (select) {
                selectedAmenities.set(val, { value: val, label: label, icon: icon });
                chip.classList.add('border-primary', 'bg-blue-50/50');
                chip.style.borderColor = '#1a73e8';
                if (checkIcon) checkIcon.classList.remove('hidden');
            } else {
                selectedAmenities.delete(val);
                chip.classList.remove('border-primary', 'bg-blue-50/50');
                chip.style.borderColor = 'var(--drive-border)';
                if (checkIcon) checkIcon.classList.add('hidden');
            }
        });
        renderSelectedAmenitiesSummary();
    };

    window.filterAmenityChips = function() {
        const query = (document.getElementById('amenitySearchInput')?.value || '').toLowerCase().trim();
        const items = document.querySelectorAll('.amenity-grid-item');
        items.forEach(item => {
            const name = (item.getAttribute('data-name') || '').toLowerCase();
            if (!query || name.includes(query)) {
                item.classList.remove('d-none');
            } else {
                item.classList.add('d-none');
            }
        });
    };

    window.addCustomAmenityFromModal = function() {
        const input = document.getElementById('modalCustomAmenityInput');
        if (!input) return;
        const val = input.value.trim();
        if (!val) return;

        const formatted = val.charAt(0).toUpperCase() + val.slice(1);
        const icon = getAmenityIcon(formatted);

        let existingChip = document.querySelector(`.modal-amenity-chip[data-value="${CSS.escape(formatted)}"]`);
        if (!existingChip) {
            const grid = document.getElementById('modalAmenityGrid');
            if (grid) {
                const col = document.createElement('div');
                col.className = 'col-6 col-sm-4 col-md-3 amenity-grid-item';
                col.setAttribute('data-name', formatted.toLowerCase());
                col.innerHTML = `
                    <div class="modal-amenity-chip p-2.5 border rounded-3 bg-white d-flex align-items-center justify-content-between gap-2 h-100"
                        data-value="${escapeHtml(formatted)}"
                        data-label="${escapeHtml(formatted)}"
                        data-icon="${icon}"
                        style="cursor: pointer; transition: all 0.15s; border-color: var(--drive-border) !important; user-select: none;">
                        <div class="d-flex align-items-center gap-2 text-truncate">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center bg-drive-canvas border border-drive-border flex-shrink-0">
                                <img src="${icon}" alt="${escapeHtml(formatted)}" class="w-5 h-5 object-contain" loading="lazy">
                            </div>
                            <span class="fw-semibold text-dark text-xs truncate" title="${escapeHtml(formatted)}">${escapeHtml(formatted)}</span>
                        </div>
                        <div class="amenity-check-icon text-primary hidden">
                            <i data-lucide="check-circle-2" style="width: 16px; height: 16px; color: #1a73e8;"></i>
                        </div>
                    </div>
                `;
                existingChip = col.querySelector('.modal-amenity-chip');
                existingChip.onclick = function() {
                    toggleAmenitySelection(formatted, formatted, icon, existingChip);
                };
                grid.prepend(col);
                if (typeof lucide !== 'undefined' && lucide.createIcons) {
                    lucide.createIcons();
                }
            }
        }

        if (!selectedAmenities.has(formatted)) {
            toggleAmenitySelection(formatted, formatted, icon, existingChip);
        }
        input.value = '';
    };

    window.renderSelectedAmenitiesSummary = function() {
        const summaryBox = document.getElementById('selectedAmenitiesSummary');
        const hiddenInputs = document.getElementById('hiddenAmenitiesInputs');
        const countText = document.getElementById('selectedAmenitiesCountText');

        if (countText) countText.textContent = selectedAmenities.size;
        if (!summaryBox || !hiddenInputs) return;

        if (selectedAmenities.size === 0) {
            summaryBox.innerHTML = `<span class="text-muted text-xs italic" id="emptyAmenitiesNotice">No amenities selected. Click "Select Amenities" to specify available water, electricity, and site facilities.</span>`;
            hiddenInputs.innerHTML = '';
            return;
        }

        let summaryHtml = '';
        let inputsHtml = '';

        selectedAmenities.forEach((item) => {
            const iconSrc = item.icon || getAmenityIcon(item.value);
            summaryHtml += `
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1.5 border rounded-pill bg-white text-xs fw-semibold text-dark shadow-xs" style="border-color: var(--drive-border) !important;">
                    <img src="${iconSrc}" alt="${escapeHtml(item.label)}" class="w-4 h-4 object-contain">
                    <span>${escapeHtml(item.label)}</span>
                    <button type="button" class="btn-close text-xs ms-1" style="width: 10px; height: 10px;"
                        onclick="toggleAmenitySelection('${escapeHtml(item.value).replace(/'/g, "\\'")}', '${escapeHtml(item.label).replace(/'/g, "\\'")}', '${iconSrc}', document.querySelector('.modal-amenity-chip[data-value=\\'${CSS.escape(item.value)}\\']'))"></button>
                </div>
            `;
            inputsHtml += `<input type="hidden" name="amenities[]" value="${escapeHtml(item.value)}">`;
        });

        summaryBox.innerHTML = summaryHtml;
        hiddenInputs.innerHTML = inputsHtml;
    };

    // Leaflet map with geocoding search, coordinate typing & 3D digital twin preview
    document.addEventListener("DOMContentLoaded", function () {
        const defaultLat = 14.5995;
        const defaultLng = 120.9842;

        const initialLatInput = document.getElementById('latitude').value.trim();
        const initialLngInput = document.getElementById('longitude').value.trim();
        const hasInitialCoords = initialLatInput && initialLngInput && !isNaN(parseFloat(initialLatInput)) && !isNaN(parseFloat(initialLngInput));

        const startLat = hasInitialCoords ? parseFloat(initialLatInput) : defaultLat;
        const startLng = hasInitialCoords ? parseFloat(initialLngInput) : defaultLng;

        const map = L.map('pickerMap', {
            zoomControl: false,
            maxZoom: 24
        }).setView([startLat, startLng], hasInitialCoords ? 16 : 13);

        if (hasInitialCoords) {
            setLocation(startLat, startLng, null, 16);
            reverseGeocode(startLat, startLng);
        } else {
            const pinnedText = document.getElementById('pinnedAddressText');
            if (pinnedText) pinnedText.textContent = 'Click on the map or search to pinpoint your land location';

            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        // Do not hijack or move the map if user has already placed a pin or drawn a plot
                        if (marker || drawnPolygonLayer || (drawnPoints && drawnPoints.length > 0)) return;
                        const lat = pos.coords.latitude;
                        const lng = pos.coords.longitude;
                        map.setView([lat, lng], 17);
                    },
                    (err) => {
                        console.warn('Geolocation failed or denied, keeping default view:', err);
                    },
                    { enableHighAccuracy: true, timeout: 8000 }
                );
            }
        }

        const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 24,
            maxNativeZoom: 19,
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        const satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            maxZoom: 24,
            maxNativeZoom: 19,
            attribution: 'Tiles &copy; Esri &mdash; Source: Esri, Maxar, Earthstar Geographics'
        });

        // Satellite layer toggle matching the UI floating above GPS button
        let isSatelliteActive = false;
        window.toggleSatelliteLayer = function () {
            if (!map) return;
            isSatelliteActive = !isSatelliteActive;
            const btn = document.getElementById('btnToggleSatellite');
            const icon = document.getElementById('btnToggleSatelliteIcon');

            if (isSatelliteActive) {
                if (map.hasLayer(osmLayer)) map.removeLayer(osmLayer);
                if (!map.hasLayer(satelliteLayer)) map.addLayer(satelliteLayer);
                if (btn) {
                    btn.classList.add('border-primary', 'shadow');
                    btn.style.background = '#E8F0FE';
                    btn.setAttribute('title', 'Switch to Standard Map');
                }
                if (icon) {
                    icon.className = 'bi bi-map-fill text-primary fs-5';
                }
            } else {
                if (map.hasLayer(satelliteLayer)) map.removeLayer(satelliteLayer);
                if (!map.hasLayer(osmLayer)) map.addLayer(osmLayer);
                if (btn) {
                    btn.classList.remove('border-primary', 'shadow');
                    btn.style.background = '';
                    btn.setAttribute('title', 'Toggle Satellite View');
                }
                if (icon) {
                    icon.className = 'bi bi-layers text-primary fs-5';
                }
            }
        };

        // Prevent Leaflet map dragging/clicking from swallowing clicks on floating map overlays
        ['plotControlOverlay', 'plotDrawToolbar', 'mapDrawingHelper', 'mapFloatingFabControls', 'btnToggleSatellite', 'btnGoToMyLocation'].forEach(id => {
            const el = document.getElementById(id);
            if (el && typeof L !== 'undefined' && L.DomEvent) {
                L.DomEvent.disableClickPropagation(el);
                L.DomEvent.disableScrollPropagation(el);
            }
        });

        let marker = null;
        let boundaryPoly = null;
        let searchDebounceTimer = null;
        let coordDebounceTimer = null;
        let register3dEngine = null;
        let activeLayoutPlots = [];

        // ─── Direct Plot Drawing Engine Variables ───
        let currentDrawMode = 'pan'; // 'pan', 'polygon', 'box'
        let drawnPoints = []; // Array of L.LatLng
        let drawnPolygonLayer = null;
        let vertexMarkers = [];
        let subdivisionLayers = [];
        let drawingTempLine = null;
        let drawingGuideLine = null;
        let drawingStartMarker = null;
        let boxAnchor = null;
        let boxPreviewLayer = null;
        let rotationHandleMarker = null;
        let rotationStemLine = null;

        // Custom green pin icon
        const customPinIcon = L.divIcon({
            className: '',
            html: `<div style="display:flex;align-items:center;justify-content:center;width:34px;height:34px;background:#198754;color:#fff;border-radius:50%;border:3px solid #fff;box-shadow:0 4px 12px rgba(0,0,0,0.3);font-size:16px;cursor:grab;">
                <i class="bi bi-geo-alt-fill"></i>
            </div>`,
            iconSize: [34, 34],
            iconAnchor: [17, 34]
        });

        // ─── Target Land Area State & Modal ───
        let targetSquareMeters = parseFloat(document.getElementById('area')?.value) || 250;
        let landAreaModalInstance = null;

        function getLandAreaModal() {
            const el = document.getElementById('landAreaModal');
            if (!el) return null;
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                if (!landAreaModalInstance) {
                    landAreaModalInstance = bootstrap.Modal.getOrCreateInstance(el);
                }
                return landAreaModalInstance;
            }
            // Universal fallback if bootstrap JS is delayed or unavailable
            return {
                show: function () {
                    el.classList.add('show');
                    el.style.display = 'block';
                    el.removeAttribute('aria-hidden');
                    el.setAttribute('aria-modal', 'true');
                    document.body.classList.add('modal-open');
                    let backdrop = document.getElementById('landAreaCustomBackdrop');
                    if (!backdrop) {
                        backdrop = document.createElement('div');
                        backdrop.id = 'landAreaCustomBackdrop';
                        backdrop.className = 'modal-backdrop fade show';
                        backdrop.style.zIndex = '1055';
                        backdrop.onclick = closeLandAreaModal;
                        document.body.appendChild(backdrop);
                    }
                },
                hide: function () {
                    el.classList.remove('show');
                    el.style.display = 'none';
                    el.setAttribute('aria-hidden', 'true');
                    el.removeAttribute('aria-modal');
                    document.body.classList.remove('modal-open');
                    const backdrop = document.getElementById('landAreaCustomBackdrop');
                    if (backdrop) backdrop.remove();
                    const bsBackdrops = document.querySelectorAll('.modal-backdrop');
                    bsBackdrops.forEach(b => b.remove());
                }
            };
        }

        window.closeLandAreaModal = function () {
            const modal = getLandAreaModal();
            if (modal) modal.hide();
        };

        let modalActivePlotCount = 4;
        let modalPlotAreas = [];

        window.openLandAreaModal = function (preferredMode = null) {
            const currentTotal = targetSquareMeters || parseFloat(document.getElementById('area')?.value) || 250;
            const input = document.getElementById('modalAreaInput');
            if (input) {
                input.value = currentTotal;
            }

            // Sync current plot count
            const currentCount = parseInt(document.getElementById('plot_count')?.value) || 4;
            modalActivePlotCount = currentCount;

            // Highlight modal count buttons
            document.querySelectorAll('#modalPlotCountButtons .modal-count-btn').forEach(btn => {
                const bCount = parseInt(btn.getAttribute('data-count'));
                if (bCount === currentCount) {
                    btn.classList.remove('btn-outline-secondary');
                    btn.classList.add('btn-success', 'text-white', 'active');
                } else {
                    btn.classList.remove('btn-success', 'text-white', 'active');
                    btn.classList.add('btn-outline-secondary');
                }
            });

            // Read any existing plot areas from the main table
            const mainAreaInputs = Array.from(document.querySelectorAll('.plot-area-input'));
            if (mainAreaInputs.length === currentCount) {
                modalPlotAreas = mainAreaInputs.map(inp => parseFloat(inp.value) || Math.round((currentTotal / currentCount) * 10) / 10);
            } else {
                const single = (currentTotal / currentCount);
                let running = 0;
                modalPlotAreas = [];
                for (let i = 0; i < currentCount; i++) {
                    if (i === currentCount - 1) {
                        modalPlotAreas.push(Math.max(1, Math.round((currentTotal - running) * 10) / 10));
                    } else {
                        const a = Math.max(1, Math.round(single * 10) / 10);
                        running += a;
                        modalPlotAreas.push(a);
                    }
                }
            }

            renderModalPlotsList();
            updateModalButtonLabel(currentTotal);

            const modal = getLandAreaModal();
            if (modal) {
                if (preferredMode) modal._preferredMode = preferredMode;
                modal.show();
            }
        };

        window.renderModalPlotsList = function () {
            const container = document.getElementById('modalPlotsList');
            if (!container) return;

            const totalArea = parseFloat(document.getElementById('modalAreaInput')?.value) || 250;
            const count = modalActivePlotCount;

            if (!modalPlotAreas || modalPlotAreas.length !== count) {
                const single = (totalArea / count);
                let running = 0;
                modalPlotAreas = [];
                for (let i = 0; i < count; i++) {
                    if (i === count - 1) {
                        modalPlotAreas.push(Math.max(1, Math.round((totalArea - running) * 10) / 10));
                    } else {
                        const a = Math.max(1, Math.round(single * 10) / 10);
                        running += a;
                        modalPlotAreas.push(a);
                    }
                }
            }

            container.innerHTML = modalPlotAreas.map((area, i) => {
                const pct = totalArea > 0 ? Math.round((area / totalArea) * 100) : 0;
                return `
                    <div class="d-flex align-items-center justify-content-between p-2 px-3 bg-white rounded-3 border shadow-xs gap-2">
                        <div class="d-flex align-items-center gap-2 flex-shrink-0" style="min-width: 100px;">
                            <span class="badge bg-success text-white rounded-pill px-2.5 py-1 text-xs fw-bold font-monospace">Plot A-${i + 1}</span>
                            <span class="badge bg-light text-muted border text-xxs font-medium">${pct}%</span>
                        </div>
                        <div class="input-group input-group-sm" style="max-width: 170px;">
                            <input type="number" step="0.1" min="1" max="${totalArea}" 
                                class="form-control form-control-sm text-end fw-bold text-dark modal-plot-area-input" 
                                value="${area}" 
                                data-index="${i}" 
                                oninput="onModalPlotAreaChanged(${i}, this)">
                            <span class="input-group-text bg-light text-muted" style="font-size: 11px;">m²</span>
                        </div>
                    </div>
                `;
            }).join('');

            updateModalAllocatedBadge();
        };

        window.onModalPlotAreaChanged = function (changedIndex, changedInput) {
            const totalArea = parseFloat(document.getElementById('modalAreaInput')?.value) || 250;
            const allInputs = Array.from(document.querySelectorAll('.modal-plot-area-input'));
            const N = allInputs.length;
            if (N <= 1) {
                if (allInputs[0]) allInputs[0].value = totalArea.toFixed(1);
                modalPlotAreas = [totalArea];
                updateModalAllocatedBadge();
                return;
            }

            let rawVal = parseFloat(changedInput.value);
            if (isNaN(rawVal)) {
                updateModalAllocatedBadge();
                return;
            }

            const minArea = 1.0;
            const maxVal = Math.max(minArea, totalArea - (N - 1) * minArea);
            let val = Math.min(Math.max(rawVal, minArea), maxVal);
            val = Math.round(val * 10) / 10;
            changedInput.value = val;
            changedInput.setAttribute('data-manual', 'true');

            let remainingArea = totalArea - val;
            const otherInputs = allInputs.filter((inp, idx) => idx !== changedIndex);
            const uneditedOthers = otherInputs.filter(inp => inp.getAttribute('data-manual') !== 'true');

            if (uneditedOthers.length > 0) {
                let manualSum = 0;
                otherInputs.forEach(inp => {
                    if (inp.getAttribute('data-manual') === 'true') {
                        manualSum += (parseFloat(inp.value) || minArea);
                    }
                });

                let availableForUnedited = totalArea - val - manualSum;
                if (availableForUnedited < uneditedOthers.length * minArea) {
                    otherInputs.forEach(inp => inp.removeAttribute('data-manual'));
                    const perPlot = remainingArea / otherInputs.length;
                    let running = 0;
                    otherInputs.forEach((inp, i) => {
                        if (i === otherInputs.length - 1) {
                            inp.value = Math.max(minArea, Math.round((remainingArea - running) * 10) / 10).toFixed(1);
                        } else {
                            const a = Math.max(minArea, Math.round(perPlot * 10) / 10);
                            running += a;
                            inp.value = a.toFixed(1);
                        }
                    });
                } else {
                    const perPlot = availableForUnedited / uneditedOthers.length;
                    let running = 0;
                    uneditedOthers.forEach((inp, i) => {
                        if (i === uneditedOthers.length - 1) {
                            inp.value = Math.max(minArea, Math.round((availableForUnedited - running) * 10) / 10).toFixed(1);
                        } else {
                            const a = Math.max(minArea, Math.round(perPlot * 10) / 10);
                            running += a;
                            inp.value = a.toFixed(1);
                        }
                    });
                }
            } else {
                let sumOthers = 0;
                otherInputs.forEach(inp => sumOthers += (parseFloat(inp.value) || minArea));
                let running = 0;
                otherInputs.forEach((inp, i) => {
                    if (i === otherInputs.length - 1) {
                        inp.value = Math.max(minArea, Math.round((remainingArea - running) * 10) / 10).toFixed(1);
                    } else {
                        const ratio = sumOthers > 0 ? ((parseFloat(inp.value) || minArea) / sumOthers) : (1 / otherInputs.length);
                        const a = Math.max(minArea, Math.round(remainingArea * ratio * 10) / 10);
                        running += a;
                        inp.value = a.toFixed(1);
                    }
                });
            }

            modalPlotAreas = allInputs.map(inp => parseFloat(inp.value) || minArea);
            updateModalAllocatedBadge();
        };

        window.onModalTotalAreaChanged = function (val) {
            const num = parseFloat(val);
            if (isNaN(num) || num <= 0) return;
            updateModalButtonLabel(num);
            resetModalPlotsEqual();
        };

        window.setModalPlotCount = function (count, btn) {
            modalActivePlotCount = count;
            document.querySelectorAll('#modalPlotCountButtons .modal-count-btn').forEach(b => {
                b.classList.remove('btn-success', 'active', 'text-white');
                b.classList.add('btn-outline-secondary');
            });
            if (btn) {
                btn.classList.remove('btn-outline-secondary');
                btn.classList.add('btn-success', 'active', 'text-white');
            }
            modalPlotAreas = [];
            renderModalPlotsList();
        };

        window.resetModalPlotsEqual = function () {
            const totalArea = parseFloat(document.getElementById('modalAreaInput')?.value) || 250;
            const count = modalActivePlotCount;
            const single = (totalArea / count);
            let running = 0;
            modalPlotAreas = [];
            for (let i = 0; i < count; i++) {
                if (i === count - 1) {
                    modalPlotAreas.push(Math.max(1, Math.round((totalArea - running) * 10) / 10));
                } else {
                    const a = Math.max(1, Math.round(single * 10) / 10);
                    running += a;
                    modalPlotAreas.push(a);
                }
            }
            const allInputs = document.querySelectorAll('.modal-plot-area-input');
            allInputs.forEach((inp, idx) => {
                inp.removeAttribute('data-manual');
                if (modalPlotAreas[idx] !== undefined) {
                    inp.value = modalPlotAreas[idx].toFixed(1);
                }
            });
            updateModalAllocatedBadge();
        };

        window.updateModalAllocatedBadge = function () {
            const badge = document.getElementById('modalAllocatedBadge');
            if (!badge) return;
            const totalArea = parseFloat(document.getElementById('modalAreaInput')?.value) || 250;
            const allInputs = Array.from(document.querySelectorAll('.modal-plot-area-input'));
            let sum = 0;
            allInputs.forEach(inp => sum += (parseFloat(inp.value) || 0));
            sum = Math.round(sum * 10) / 10;
            const diff = Math.abs(sum - totalArea);
            if (diff <= 0.2) {
                badge.className = 'badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5 text-xxs fw-semibold';
                badge.innerHTML = `<i class="bi bi-check-circle-fill me-0.5"></i>${sum.toFixed(1)} / ${totalArea} m²`;
            } else {
                badge.className = 'badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-0.5 text-xxs fw-semibold';
                badge.innerHTML = `<i class="bi bi-info-circle-fill me-0.5"></i>${sum.toFixed(1)} / ${totalArea} m²`;
            }
        };

        window.updateModalButtonLabel = function (val) {
            const btnText = document.getElementById('modalBtnAreaText');
            if (btnText) {
                const num = parseFloat(val);
                btnText.textContent = (!isNaN(num) && num > 0) ? `${num.toLocaleString()} m²` : '—';
            }
        };

        window.confirmLandAreaAndDraw = function () {
            const input = document.getElementById('modalAreaInput');
            let val = parseFloat(input?.value);
            if (isNaN(val) || val <= 0) {
                alert('Please enter a valid positive land area in square meters (e.g. 250).');
                return;
            }
            targetSquareMeters = Math.round(val * 10) / 10;

            const areaInput = document.getElementById('area');
            if (areaInput) areaInput.value = targetSquareMeters;

            const badge = document.getElementById('targetAreaBadge');
            if (badge) badge.textContent = `${targetSquareMeters.toLocaleString()} m²`;
            const editBtn = document.getElementById('btnEditPlotArea');
            if (editBtn) editBtn.classList.remove('d-none');

            // Apply plot count from modal
            const plotCountInput = document.getElementById('plot_count');
            if (plotCountInput) plotCountInput.value = modalActivePlotCount;
            const formPlotCount = document.getElementById('form_plot_count');
            if (formPlotCount) formPlotCount.value = modalActivePlotCount;

            // Sync preset buttons in form and map overlay
            document.querySelectorAll('.plot-preset-btn').forEach(b => {
                const bCount = parseInt(b.textContent);
                if (bCount === modalActivePlotCount) {
                    b.classList.remove('btn-outline-secondary');
                    b.classList.add('btn-success', 'active', 'text-white');
                } else {
                    b.classList.remove('btn-success', 'active', 'text-white');
                    b.classList.add('btn-outline-secondary');
                }
            });
            document.querySelectorAll('.plot-count-btn').forEach(b => {
                const bCount = parseInt(b.getAttribute('data-count'));
                if (bCount === modalActivePlotCount) {
                    b.classList.add('active');
                } else {
                    b.classList.remove('active');
                }
            });

            // Read the modal's plot area inputs and sync to main form table
            const modalInputs = Array.from(document.querySelectorAll('.modal-plot-area-input'));
            const customAreas = modalInputs.map(inp => parseFloat(inp.value) || (targetSquareMeters / modalActivePlotCount));

            // Render main plots table with these exact areas
            renderPlotsConfigTable(true, customAreas);

            const modal = getLandAreaModal();
            if (modal) modal.hide();

            // Automatically place drawing with fixed square meters and custom subdivisions
            placeFixedPlotOnMap(targetSquareMeters);
        };

        window.placeFixedPlotOnMap = function (areaMeters) {
            let center = marker ? marker.getLatLng() : map.getCenter();
            if (!marker) {
                setLocation(center.lat, center.lng);
                center = marker.getLatLng();
            }

            // If a polygon already exists, preserve its current orientation/shape and scale to exact new square meters
            if (drawnPolygonLayer && drawnPoints && drawnPoints.length >= 3) {
                const curArea = calculatePolygonArea(drawnPoints);
                if (curArea > 0) {
                    const scaleFactor = Math.sqrt(areaMeters / curArea);
                    const centroid = calculateCentroid(drawnPoints);
                    drawnPoints = drawnPoints.map(p => {
                        return L.latLng(
                            centroid.lat + (p.lat - centroid.lat) * scaleFactor,
                            centroid.lng + (p.lng - centroid.lng) * scaleFactor
                        );
                    });
                    drawnPolygonLayer.setLatLngs(drawnPoints);
                    createVertexMarkers();
                    createRotationHandle();
                    updateDrawnPolygonStats(false);
                    renderSubdivisions();
                    return;
                }
            }

            // Generate initial square plot matching exact square meters centered at pin
            const sideMeters = Math.sqrt(areaMeters);
            const halfSide = sideMeters / 2;
            const deltaLat = halfSide / 111320;
            const deltaLng = halfSide / (111320 * Math.cos(center.lat * Math.PI / 180));

            drawnPoints = [
                L.latLng(center.lat + deltaLat, center.lng - deltaLng), // NW
                L.latLng(center.lat + deltaLat, center.lng + deltaLng), // NE
                L.latLng(center.lat - deltaLat, center.lng + deltaLng), // SE
                L.latLng(center.lat - deltaLat, center.lng - deltaLng)  // SW
            ];

            finishCurrentPolygon();
        };

        window.rotatePlot = function (degrees) {
            if (!drawnPoints || drawnPoints.length < 3) return;
            const rad = degrees * Math.PI / 180;
            const centroid = calculateCentroid(drawnPoints);
            const cosLat = Math.cos(centroid.lat * Math.PI / 180);

            const cosA = Math.cos(rad);
            const sinA = Math.sin(rad);

            drawnPoints = drawnPoints.map(p => {
                const x = (p.lng - centroid.lng) * cosLat;
                const y = p.lat - centroid.lat;
                const rx = x * cosA - y * sinA;
                const ry = x * sinA + y * cosA;
                return L.latLng(centroid.lat + ry, centroid.lng + rx / cosLat);
            });

            if (drawnPolygonLayer) {
                drawnPolygonLayer.setLatLngs(drawnPoints);
            }
            createVertexMarkers();
            createRotationHandle();
            updateDrawnPolygonStats(false);
            renderSubdivisions();
        };

        // ─── Step-by-Step Registration Process Controller ───
        let currentStep = 1;

        function setRegistrationStep(step) {
            currentStep = step;
            const step1Item = document.getElementById('step1Item');
            const step2Item = document.getElementById('step2Item');
            const step3Item = document.getElementById('step3Item');

            const step1Badge = document.getElementById('step1Badge');
            const step2Badge = document.getElementById('step2Badge');
            const step3Badge = document.getElementById('step3Badge');

            const step1Title = document.getElementById('step1Title');
            const step2Title = document.getElementById('step2Title');
            const step3Title = document.getElementById('step3Title');

            const guideText = document.getElementById('stepGuideText');

            const hasPin = (marker !== null);
            const hasPlot = (drawnPolygonLayer !== null && drawnPoints.length >= 3);

            // Step 1 styling
            if (step1Item && step1Badge && step1Title) {
                if (hasPin && step > 1) {
                    step1Item.className = 'd-flex align-items-center gap-1.5 px-2.5 py-1 rounded-pill bg-success-subtle border border-success-subtle transition-all';
                    step1Badge.className = 'rounded-circle d-flex align-items-center justify-content-center text-white bg-success fw-bold';
                    step1Badge.innerHTML = '<i class="bi bi-check-lg" style="font-size:0.68rem;"></i>';
                    step1Title.className = 'fw-bold text-success';
                } else if (step === 1) {
                    step1Item.className = 'd-flex align-items-center gap-1.5 px-2.5 py-1 rounded-pill bg-white border border-success shadow-2xs transition-all';
                    step1Badge.className = 'rounded-circle d-flex align-items-center justify-content-center text-white bg-success fw-bold';
                    step1Badge.innerHTML = '1';
                    step1Title.className = 'fw-bold text-dark';
                } else {
                    step1Item.className = 'd-flex align-items-center gap-1.5 px-2.5 py-1 rounded-pill bg-transparent text-muted transition-all';
                    step1Badge.className = 'rounded-circle d-flex align-items-center justify-content-center border bg-white text-secondary fw-bold';
                    step1Badge.innerHTML = '1';
                    step1Title.className = 'text-muted';
                }
            }

            // Step 2 styling
            if (step2Item && step2Badge && step2Title) {
                if (hasPlot && step > 2) {
                    step2Item.className = 'd-flex align-items-center gap-1.5 px-2.5 py-1 rounded-pill bg-success-subtle border border-success-subtle transition-all';
                    step2Badge.className = 'rounded-circle d-flex align-items-center justify-content-center text-white bg-success fw-bold';
                    step2Badge.innerHTML = '<i class="bi bi-check-lg" style="font-size:0.68rem;"></i>';
                    step2Title.className = 'fw-bold text-success';
                } else if (step === 2) {
                    step2Item.className = 'd-flex align-items-center gap-1.5 px-2.5 py-1 rounded-pill bg-white border border-success shadow-2xs transition-all';
                    step2Badge.className = 'rounded-circle d-flex align-items-center justify-content-center text-white bg-success fw-bold';
                    step2Badge.innerHTML = '2';
                    step2Title.className = 'fw-bold text-dark';
                } else {
                    step2Item.className = 'd-flex align-items-center gap-1.5 px-2.5 py-1 rounded-pill bg-transparent text-muted transition-all';
                    step2Badge.className = 'rounded-circle d-flex align-items-center justify-content-center border bg-white text-secondary fw-bold';
                    step2Badge.innerHTML = '2';
                    step2Title.className = 'text-muted';
                }
            }

            // Step 3 styling
            if (step3Item && step3Badge && step3Title) {
                if (step === 3) {
                    step3Item.className = 'd-flex align-items-center gap-1.5 px-2.5 py-1 rounded-pill bg-white border border-success shadow-2xs transition-all';
                    step3Badge.className = 'rounded-circle d-flex align-items-center justify-content-center text-white bg-success fw-bold';
                    step3Badge.innerHTML = '3';
                    step3Title.className = 'fw-bold text-dark';
                } else {
                    step3Item.className = 'd-flex align-items-center gap-1.5 px-2.5 py-1 rounded-pill bg-transparent text-muted transition-all';
                    step3Badge.className = 'rounded-circle d-flex align-items-center justify-content-center border bg-white text-secondary fw-bold';
                    step3Badge.innerHTML = '3';
                    step3Title.className = 'text-muted';
                }
            }

            // Guide Text update
            if (guideText) {
                if (step === 1) {
                    guideText.innerHTML = '<i class="bi bi-geo-alt-fill text-danger"></i><span><strong>Step 1:</strong> Search location or click anywhere on the map to pinpoint.</span>';
                } else if (step === 2) {
                    guideText.innerHTML = '<i class="bi bi-rulers text-success"></i><span><strong>Step 2:</strong> Set square meters & adjust plot boundary (rotate or drag corners).</span>';
                } else if (step === 3) {
                    guideText.innerHTML = '<i class="bi bi-grid-3x3-gap-fill text-success"></i><span><strong>Step 3:</strong> Boundary created! Choose partition plots & complete land details below.</span>';
                }
            }
        }

        window.goToStep = function (targetStep) {
            if (targetStep === 1) {
                setPlotDrawMode('pan');
                setRegistrationStep(1);
            } else if (targetStep === 2) {
                if (!marker) {
                    alert('Please pinpoint a location on the map first (Step 1).');
                    return;
                }
                openLandAreaModal();
                setRegistrationStep(2);
            } else if (targetStep === 3) {
                if (!drawnPolygonLayer || drawnPoints.length < 3) {
                    alert('Please draw and finish your plot boundary first (Step 2).');
                    return;
                }
                setRegistrationStep(3);
                const formEl = document.getElementById('landRegistrationForm');
                if (formEl) formEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        };

        function updateLotBoundary(lat, lng) {
            if (drawnPolygonLayer) return; // Do not draw square if custom polygon boundary is active
            const area = parseFloat(document.getElementById('area').value) || 200;
            const sideMeters = Math.sqrt(area);
            const halfSide = sideMeters / 2;
            const deltaLat = halfSide / 111320;
            const deltaLng = halfSide / (111320 * Math.cos(lat * Math.PI / 180));

            const bounds = [
                [lat - deltaLat, lng - deltaLng],
                [lat + deltaLat, lng + deltaLng]
            ];

            if (boundaryPoly) {
                boundaryPoly.setBounds(bounds);
            } else {
                boundaryPoly = L.rectangle(bounds, {
                    color: '#198754',
                    weight: 2,
                    fillColor: '#198754',
                    fillOpacity: 0.16,
                    dashArray: '5, 5'
                }).addTo(map);
            }

            renderSubdivisions();
        }

        function setLocation(lat, lng, addressText = null, zoom = null) {
            lat = parseFloat(lat).toFixed(6);
            lng = parseFloat(lng).toFixed(6);

            document.getElementById('latitude').value = lat;
            document.getElementById('longitude').value = lng;

            const latlng = [parseFloat(lat), parseFloat(lng)];

            if (marker) {
                marker.setLatLng(latlng);
            } else {
                marker = L.marker(latlng, { draggable: true, icon: customPinIcon }).addTo(map);

                let markerDragStart = null;
                marker.on('dragstart', function (e) {
                    markerDragStart = e.target.getLatLng();
                });
                marker.on('drag', function (e) {
                    if (drawnPolygonLayer && drawnPoints && drawnPoints.length >= 3 && markerDragStart) {
                        const curPos = e.target.getLatLng();
                        const dLat = curPos.lat - markerDragStart.lat;
                        const dLng = curPos.lng - markerDragStart.lng;
                        markerDragStart = curPos;

                        drawnPoints = drawnPoints.map(p => L.latLng(p.lat + dLat, p.lng + dLng));
                        drawnPolygonLayer.setLatLngs(drawnPoints);
                        vertexMarkers.forEach((vm, idx) => {
                            if (drawnPoints[idx]) vm.setLatLng(drawnPoints[idx]);
                        });
                        if (rotationHandleMarker && rotationStemLine) {
                            const newTopMidLat = (drawnPoints[0].lat + drawnPoints[1].lat) / 2;
                            const newTopMidLng = (drawnPoints[0].lng + drawnPoints[1].lng) / 2;
                            const hPos = rotationHandleMarker.getLatLng();
                            rotationHandleMarker.setLatLng([hPos.lat + dLat, hPos.lng + dLng]);
                            rotationStemLine.setLatLngs([[newTopMidLat, newTopMidLng], [hPos.lat + dLat, hPos.lng + dLng]]);
                        }
                        renderSubdivisions();
                    }
                });
                marker.on('dragend', function (e) {
                    const pos = e.target.getLatLng();
                    setLocation(pos.lat, pos.lng);
                    reverseGeocode(pos.lat, pos.lng);
                });
            }

            // If plot is not drawn yet, automatically zoom and prompt for square meters before drawing!
            if (!drawnPolygonLayer) {
                setRegistrationStep(2);

                // Automatically zoom in close to lot level (zoom 18)
                const targetZoom = 18;
                map.flyTo(latlng, targetZoom, { animate: true, duration: 0.85 });

                // Open modal promptly so user gets immediate response
                setTimeout(() => {
                    openLandAreaModal('polygon');
                }, 150);
            } else {
                // If plot already exists, translate the fixed plot to the new pin location
                if (drawnPoints && drawnPoints.length >= 3) {
                    const curCentroid = calculateCentroid(drawnPoints);
                    const dLat = parseFloat(lat) - curCentroid.lat;
                    const dLng = parseFloat(lng) - curCentroid.lng;

                    if (Math.abs(dLat) > 1e-7 || Math.abs(dLng) > 1e-7) {
                        drawnPoints = drawnPoints.map(p => L.latLng(p.lat + dLat, p.lng + dLng));
                        drawnPolygonLayer.setLatLngs(drawnPoints);
                        createVertexMarkers();
                        createRotationHandle();
                        updateDrawnPolygonStats(false);
                        renderSubdivisions();
                    }
                }

                if (zoom) {
                    map.flyTo(latlng, zoom, { animate: true, duration: 0.85 });
                }
            }

            // Update status bar
            const coordsBadge = document.getElementById('coordsBadge');
            if (coordsBadge) {
                coordsBadge.textContent = `${lat}, ${lng}`;
                coordsBadge.style.display = 'inline-block';
            }

            if (addressText) {
                const addrInput = document.getElementById('address');
                if (addrInput && (!addrInput.value.trim() || addrInput.value === addressText)) {
                    addrInput.value = addressText;
                }
                const pinnedText = document.getElementById('pinnedAddressText');
                if (pinnedText) pinnedText.textContent = addressText;
            }
        }

        map.on('click', function (e) {
            // Once plot is placed, do not allow adding points or jumping the location on click
            if (drawnPolygonLayer) {
                return;
            }
            if (currentDrawMode === 'polygon') {
                handlePolygonClick(e);
                return;
            }
            if (currentDrawMode === 'box') {
                handleBoxClick(e);
                return;
            }
            setLocation(e.latlng.lat, e.latlng.lng);
            reverseGeocode(e.latlng.lat, e.latlng.lng);
        });

        map.on('mousemove', function (e) {
            if (currentDrawMode === 'polygon') {
                handlePolygonMouseMove(e);
            } else if (currentDrawMode === 'box') {
                handleBoxMouseMove(e);
            }
        });

        map.on('dblclick', function (e) {
            if (currentDrawMode === 'polygon' && drawnPoints.length >= 3) {
                L.DomEvent.stopPropagation(e);
                finishCurrentPolygon();
            }
        });

        // Ensure Leaflet map sizes properly in the top full-width container
        setTimeout(() => {
            map.invalidateSize();
        }, 250);
        window.addEventListener('resize', () => {
            map.invalidateSize();
        });

        // ─── Direct Coordinate Typing Listeners ───
        function onCoordinateTyped() {
            const latVal = document.getElementById('latitude').value.trim();
            const lngVal = document.getElementById('longitude').value.trim();
            if (!latVal || !lngVal) return;

            const lat = parseFloat(latVal);
            const lng = parseFloat(lngVal);

            if (!isNaN(lat) && !isNaN(lng) && lat >= -90 && lat <= 90 && lng >= -180 && lng <= 180) {
                setLocation(lat, lng, null, 16);
                reverseGeocode(lat, lng);
            }
        }

        ['latitude', 'longitude'].forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.addEventListener('input', function () {
                    clearTimeout(coordDebounceTimer);
                    coordDebounceTimer = setTimeout(onCoordinateTyped, 400);
                });
            }
        });

        // ─── Nominatim Search & Autocomplete ───
        const searchInput = document.getElementById('mapSearchInput');
        const resultsContainer = document.getElementById('mapSearchResults');
        const clearBtn = document.getElementById('mapSearchClearBtn');

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const query = this.value.trim();
                if (clearBtn) clearBtn.classList.toggle('d-none', !query);

                // Check if user typed coordinates like "14.5995, 120.9842"
                const coordMatch = query.match(/^([-+]?\d+(\.\d+)?)[,\s]+([-+]?\d+(\.\d+)?)$/);
                if (coordMatch) {
                    const cLat = parseFloat(coordMatch[1]);
                    const cLng = parseFloat(coordMatch[3]);
                    if (!isNaN(cLat) && !isNaN(cLng) && cLat >= -90 && cLat <= 90 && cLng >= -180 && cLng <= 180) {
                        resultsContainer.classList.remove('active');
                        resultsContainer.innerHTML = '';
                        setLocation(cLat, cLng, `${cLat}, ${cLng}`, 17);
                        reverseGeocode(cLat, cLng);
                        return;
                    }
                }

                clearTimeout(searchDebounceTimer);
                if (query.length < 3) {
                    resultsContainer.classList.remove('active');
                    resultsContainer.innerHTML = '';
                    return;
                }

                searchDebounceTimer = setTimeout(() => {
                    performSearch(query);
                }, 400);
            });

            searchInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(searchDebounceTimer);
                    performSearch(this.value.trim(), true);
                }
            });
        }

        async function performSearch(query, selectFirst = false) {
            if (!query) return;

            // Direct coordinate check
            const coordMatch = query.match(/^([-+]?\d+(\.\d+)?)[,\s]+([-+]?\d+(\.\d+)?)$/);
            if (coordMatch) {
                const cLat = parseFloat(coordMatch[1]);
                const cLng = parseFloat(coordMatch[3]);
                if (!isNaN(cLat) && !isNaN(cLng) && cLat >= -90 && cLat <= 90 && cLng >= -180 && cLng <= 180) {
                    resultsContainer.classList.remove('active');
                    resultsContainer.innerHTML = '';
                    setLocation(cLat, cLng, `${cLat}, ${cLng}`, 17);
                    reverseGeocode(cLat, cLng);
                    return;
                }
            }

            resultsContainer.innerHTML = `<div class="p-3 text-center text-muted text-xs"><span class="spinner-border spinner-border-sm me-1.5 text-success"></span> Searching locations...</div>`;
            resultsContainer.classList.add('active');

            try {
                const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=6&addressdetails=1`;
                const res = await fetch(url);
                if (!res.ok) throw new Error('Search failed');
                const data = await res.json();

                if (!data || data.length === 0) {
                    resultsContainer.innerHTML = `<div class="p-3 text-center text-muted text-xs"><i class="bi bi-geo-alt me-1 text-danger"></i> No matching locations found. Try a different term.</div>`;
                    return;
                }

                if (selectFirst && data.length > 0) {
                    chooseResult(data[0]);
                    return;
                }

                resultsContainer.innerHTML = data.map((item, idx) => `
                    <div class="map-picker-result-item" data-idx="${idx}">
                        <i class="bi bi-geo-alt text-success mt-0.5 flex-shrink-0"></i>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="map-picker-result-title text-truncate">${escapeHtml(item.name || item.display_name.split(',')[0])}</div>
                            <div class="map-picker-result-sub text-truncate">${escapeHtml(item.display_name)}</div>
                        </div>
                    </div>
                `).join('');

                resultsContainer.querySelectorAll('.map-picker-result-item').forEach((el, idx) => {
                    el.addEventListener('click', () => {
                        chooseResult(data[idx]);
                    });
                });
            } catch (err) {
                resultsContainer.innerHTML = `<div class="p-3 text-center text-danger text-xs"><i class="bi bi-exclamation-triangle me-1"></i> Unable to connect to location search service.</div>`;
            }
        }

        function chooseResult(item) {
            resultsContainer.classList.remove('active');
            resultsContainer.innerHTML = '';
            if (searchInput) searchInput.value = item.display_name;

            const displayName = item.display_name;
            setLocation(item.lat, item.lon, displayName, 17);

            const addrInput = document.getElementById('address');
            if (addrInput) addrInput.value = displayName;
        }

        async function reverseGeocode(lat, lon) {
            const pinnedText = document.getElementById('pinnedAddressText');
            if (pinnedText) pinnedText.textContent = 'Resolving address...';

            try {
                const url = `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}`;
                const res = await fetch(url);
                if (!res.ok) throw new Error();
                const data = await res.json();
                if (data && data.display_name) {
                    if (pinnedText) pinnedText.textContent = data.display_name;
                    const addrInput = document.getElementById('address');
                    if (addrInput && !addrInput.value.trim()) {
                        addrInput.value = data.display_name;
                    }
                    const modalAddr = document.getElementById('modalAddressText');
                    if (modalAddr) modalAddr.textContent = data.display_name;
                } else {
                    if (pinnedText) pinnedText.textContent = `${lat}, ${lon}`;
                }
            } catch (e) {
                if (pinnedText) pinnedText.textContent = `${lat}, ${lon}`;
            }
        }

        window.clearMapSearch = function () {
            if (searchInput) searchInput.value = '';
            if (clearBtn) clearBtn.classList.add('d-none');
            if (resultsContainer) {
                resultsContainer.classList.remove('active');
                resultsContainer.innerHTML = '';
            }
        };

        window.searchAddressFromInput = function () {
            const addr = document.getElementById('address').value.trim();
            if (!addr) {
                alert('Please enter an address first to search on the map.');
                return;
            }
            if (searchInput) searchInput.value = addr;
            performSearch(addr, true);
        };

        window.useCurrentLocation = function () {
            if (!navigator.geolocation) {
                alert('Geolocation is not supported by your browser.');
                return;
            }
            const pinnedText = document.getElementById('pinnedAddressText');
            if (pinnedText) pinnedText.textContent = 'Locating GPS position...';

            navigator.geolocation.getCurrentPosition(
                (pos) => {
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    setLocation(lat, lng, null, 17);
                    reverseGeocode(lat, lng);
                },
                (err) => {
                    alert('Unable to retrieve your current location: ' + err.message);
                    if (pinnedText) pinnedText.textContent = 'GPS location unavailable';
                },
                { enableHighAccuracy: true, timeout: 8000 }
            );
        };

        // ─── Direct Interactive Plot Boundary Drawing Engine ───

        window.setPlotDrawMode = function (mode) {
            currentDrawMode = mode;
            cancelTempDrawing();

            const panBtn = document.getElementById('drawModePanBtn');
            const polyBtn = document.getElementById('drawModePolygonBtn');
            const boxBtn = document.getElementById('drawModeBoxBtn');
            const helper = document.getElementById('mapDrawingHelper');
            const helperText = document.getElementById('mapDrawingHelperText');
            const finishBtn = document.getElementById('btnFinishPolygon');

            [panBtn, polyBtn, boxBtn].forEach(btn => {
                if (btn) {
                    btn.classList.remove('active', 'btn-success', 'text-white', 'text-dark');
                    btn.classList.add('text-secondary');
                }
            });

            if (mode === 'pan') {
                if (panBtn) {
                    panBtn.classList.add('active', 'text-dark');
                    panBtn.classList.remove('text-secondary');
                }
                map.getContainer().style.cursor = '';
                if (helper) helper.classList.add('d-none');
                if (!drawnPolygonLayer || drawnPoints.length < 3) {
                    setRegistrationStep(1);
                } else {
                    setRegistrationStep(3);
                }
            } else if (mode === 'polygon') {
                if (polyBtn) {
                    polyBtn.classList.add('active', 'btn-success', 'text-white');
                    polyBtn.classList.remove('text-secondary');
                }
                map.getContainer().style.cursor = 'crosshair';
                if (helper) helper.classList.remove('d-none');
                const targetDisplay = targetSquareMeters ? `${targetSquareMeters.toLocaleString()} m²` : '250 m²';
                if (helperText) helperText.innerHTML = `<i class="bi bi-pentagon-fill text-success me-1"></i><strong>Step 2 (${targetDisplay}):</strong> Click on map to place corner 1`;
                if (finishBtn) finishBtn.classList.add('d-none');
                setRegistrationStep(2);
            } else if (mode === 'box') {
                if (boxBtn) {
                    boxBtn.classList.add('active', 'btn-success', 'text-white');
                    boxBtn.classList.remove('text-secondary');
                }
                map.getContainer().style.cursor = 'crosshair';
                if (helper) helper.classList.remove('d-none');
                const targetDisplay = targetSquareMeters ? `${targetSquareMeters.toLocaleString()} m²` : '250 m²';
                if (helperText) helperText.innerHTML = `<i class="bi bi-bounding-box text-success me-1"></i><strong>Step 2 (${targetDisplay}):</strong> Click on map to anchor corner 1 of the box`;
                if (finishBtn) finishBtn.classList.add('d-none');
                setRegistrationStep(2);
            }
        };

        function cancelTempDrawing() {
            if (drawingTempLine) { map.removeLayer(drawingTempLine); drawingTempLine = null; }
            if (drawingGuideLine) { map.removeLayer(drawingGuideLine); drawingGuideLine = null; }
            if (drawingStartMarker) { map.removeLayer(drawingStartMarker); drawingStartMarker = null; }
            if (boxPreviewLayer) { map.removeLayer(boxPreviewLayer); boxPreviewLayer = null; }
            boxAnchor = null;
        }

        window.cancelPlotDrawing = function () {
            cancelTempDrawing();
            if (!drawnPolygonLayer) {
                drawnPoints = [];
                setRegistrationStep(1);
            } else {
                setRegistrationStep(3);
            }
            setPlotDrawMode('pan');
        };

        function handlePolygonClick(e) {
            drawnPoints.push(e.latlng);
            const count = drawnPoints.length;
            const helperText = document.getElementById('mapDrawingHelperText');
            const finishBtn = document.getElementById('btnFinishPolygon');

            if (count === 1) {
                drawingStartMarker = L.circleMarker(e.latlng, {
                    radius: 9,
                    color: '#ffffff',
                    weight: 3,
                    fillColor: '#198754',
                    fillOpacity: 1
                }).addTo(map);

                drawingStartMarker.on('click', function (ev) {
                    L.DomEvent.stopPropagation(ev);
                    L.DomEvent.preventDefault(ev);
                    if (drawnPoints.length >= 3) {
                        finishCurrentPolygon();
                    } else {
                        alert('Please add at least 3 corner points to close your plot.');
                    }
                });
                drawingStartMarker.bindTooltip('Click here to close plot', { permanent: false, direction: 'top' });

                drawingTempLine = L.polyline([e.latlng], {
                    color: '#198754',
                    weight: 3
                }).addTo(map);

                if (helperText) helperText.textContent = 'Corner 1 placed. Click to place corner 2';
            } else if (count === 2) {
                if (drawingTempLine) drawingTempLine.setLatLngs(drawnPoints);
                if (helperText) helperText.textContent = 'Corner 2 placed. Click to place corner 3';
            } else {
                if (drawingTempLine) drawingTempLine.setLatLngs(drawnPoints);
                if (finishBtn) finishBtn.classList.remove('d-none');
                if (helperText) helperText.textContent = `Corner ${count} placed. Click next corner, or click corner 1 / "Finish" to close plot`;
            }
        }

        function handlePolygonMouseMove(e) {
            if (drawnPoints.length > 0) {
                const lastPt = drawnPoints[drawnPoints.length - 1];
                if (drawingGuideLine) {
                    drawingGuideLine.setLatLngs([lastPt, e.latlng]);
                } else {
                    drawingGuideLine = L.polyline([lastPt, e.latlng], {
                        color: '#198754',
                        weight: 1.5,
                        dashArray: '4, 5'
                    }).addTo(map);
                }
            }
        }

        function handleBoxClick(e) {
            const helperText = document.getElementById('mapDrawingHelperText');
            if (!boxAnchor) {
                boxAnchor = e.latlng;
                const targetArea = targetSquareMeters || parseFloat(document.getElementById('area')?.value) || 250;
                if (helperText) helperText.innerHTML = `<i class="bi bi-bounding-box text-success me-1"></i>Anchor set! Move mouse to size box (${targetArea.toLocaleString()} m² locked), then click to place`;
            } else {
                const targetArea = targetSquareMeters || parseFloat(document.getElementById('area')?.value) || 250;
                const cosLat = Math.cos(boxAnchor.lat * Math.PI / 180);
                const dxMeters = Math.max(3, Math.abs(e.latlng.lng - boxAnchor.lng) * 111320 * cosLat);
                const dyMeters = targetArea / dxMeters;
                const deltaLat = dyMeters / 111320;
                const latSign = e.latlng.lat >= boxAnchor.lat ? 1 : -1;
                const constrainedLat = boxAnchor.lat + (latSign * deltaLat);

                const minLat = Math.min(boxAnchor.lat, constrainedLat);
                const maxLat = Math.max(boxAnchor.lat, constrainedLat);
                const minLng = Math.min(boxAnchor.lng, e.latlng.lng);
                const maxLng = Math.max(boxAnchor.lng, e.latlng.lng);

                drawnPoints = [
                    L.latLng(maxLat, minLng), // NW
                    L.latLng(maxLat, maxLng), // NE
                    L.latLng(minLat, maxLng), // SE
                    L.latLng(minLat, minLng)  // SW
                ];

                if (boxPreviewLayer) {
                    map.removeLayer(boxPreviewLayer);
                    boxPreviewLayer = null;
                }
                boxAnchor = null;
                finishCurrentPolygon();
            }
        }

        function handleBoxMouseMove(e) {
            if (boxAnchor) {
                const targetArea = targetSquareMeters || parseFloat(document.getElementById('area')?.value) || 250;
                const cosLat = Math.cos(boxAnchor.lat * Math.PI / 180);
                const dxMeters = Math.max(3, Math.abs(e.latlng.lng - boxAnchor.lng) * 111320 * cosLat);
                const dyMeters = targetArea / dxMeters;
                const deltaLat = dyMeters / 111320;
                const latSign = e.latlng.lat >= boxAnchor.lat ? 1 : -1;
                const constrainedLat = boxAnchor.lat + (latSign * deltaLat);
                const constrainedCorner = L.latLng(constrainedLat, e.latlng.lng);

                const bounds = L.latLngBounds(boxAnchor, constrainedCorner);
                if (boxPreviewLayer) {
                    boxPreviewLayer.setBounds(bounds);
                } else {
                    boxPreviewLayer = L.rectangle(bounds, {
                        color: '#198754',
                        weight: 2,
                        dashArray: '5, 5',
                        fillColor: '#22c55e',
                        fillOpacity: 0.18
                    }).addTo(map);
                }

                const helperText = document.getElementById('mapDrawingHelperText');
                if (helperText) {
                    helperText.innerHTML = `<i class="bi bi-rulers text-success me-1"></i><strong>${Math.round(targetArea)} m² Locked</strong> (${Math.round(dxMeters)}m × ${Math.round(dyMeters)}m) &mdash; Click to place box`;
                }
            }
        }

        window.finishCurrentPolygon = function () {
            if (!drawnPoints || drawnPoints.length < 3) {
                alert('Please click at least 3 points on the map to create a closed plot boundary.');
                return;
            }

            // Calibrate/scale to exact targetSquareMeters so drawing matches inputted size!
            const curArea = calculatePolygonArea(drawnPoints);
            const targetArea = targetSquareMeters || parseFloat(document.getElementById('area')?.value) || 250;
            if (curArea > 0 && targetArea > 0) {
                const scaleFactor = Math.sqrt(targetArea / curArea);
                const centroid = calculateCentroid(drawnPoints);
                drawnPoints = drawnPoints.map(p => {
                    return L.latLng(
                        centroid.lat + (p.lat - centroid.lat) * scaleFactor,
                        centroid.lng + (p.lng - centroid.lng) * scaleFactor
                    );
                });
            }

            // Preserve drawn points so they are never wiped
            const finalPoints = drawnPoints.slice();

            // Clear temporary drawing overlays only
            cancelTempDrawing();

            // Restore the points
            drawnPoints = finalPoints;

            if (drawnPolygonLayer) {
                map.removeLayer(drawnPolygonLayer);
                drawnPolygonLayer = null;
            }
            if (boundaryPoly) {
                map.removeLayer(boundaryPoly);
                boundaryPoly = null;
            }

            // Create solid closed polygon layer
            drawnPolygonLayer = L.polygon(drawnPoints, {
                color: '#198754',
                weight: 3,
                fillColor: '#22c55e',
                fillOpacity: 0.25
            }).addTo(map);

            // Automatically focus and zoom into the plot drawing cleanly
            try {
                map.fitBounds(drawnPolygonLayer.getBounds(), {
                    padding: [60, 60],
                    maxZoom: 18,
                    animate: true
                });
            } catch (err) {
                console.warn('fitBounds error:', err);
            }

            // Make the entire polygon draggable so clicking and dragging anywhere on the plot moves it
            let isDraggingPlot = false;
            let plotDragStart = null;
            drawnPolygonLayer.on('mousedown', function (e) {
                isDraggingPlot = true;
                plotDragStart = e.latlng;
                map.dragging.disable();
            });
            map.on('mousemove', function (e) {
                if (isDraggingPlot && plotDragStart && drawnPoints && drawnPoints.length >= 3) {
                    const dLat = e.latlng.lat - plotDragStart.lat;
                    const dLng = e.latlng.lng - plotDragStart.lng;
                    plotDragStart = e.latlng;

                    drawnPoints = drawnPoints.map(p => L.latLng(p.lat + dLat, p.lng + dLng));
                    drawnPolygonLayer.setLatLngs(drawnPoints);
                    const c = calculateCentroid(drawnPoints);
                    if (marker) marker.setLatLng([c.lat, c.lng]);
                    vertexMarkers.forEach((vm, idx) => {
                        if (drawnPoints[idx]) vm.setLatLng(drawnPoints[idx]);
                    });
                    renderSubdivisions();
                }
            });
            map.on('mouseup', function () {
                if (isDraggingPlot) {
                    isDraggingPlot = false;
                    plotDragStart = null;
                    map.dragging.enable();
                    createVertexMarkers();
                    createRotationHandle();
                    updateDrawnPolygonStats(true);
                    renderSubdivisions();
                }
            });

            createVertexMarkers();
            createRotationHandle();
            updateDrawnPolygonStats(true);
            renderSubdivisions();

            const clearBtn = document.getElementById('btnClearDrawnPlot');
            if (clearBtn) clearBtn.classList.remove('d-none');
            const formClearBtn = document.getElementById('btnFormClearDrawn');
            if (formClearBtn) formClearBtn.classList.remove('d-none');
            const rotLeft = document.getElementById('btnRotateLeft');
            const rotRight = document.getElementById('btnRotateRight');
            if (rotLeft) rotLeft.classList.remove('d-none');
            if (rotRight) rotRight.classList.remove('d-none');

            // Pop up the Partition Plots Overlay now that plot boundary is placed
            const plotOverlay = document.getElementById('plotControlOverlay');
            if (plotOverlay) {
                plotOverlay.classList.remove('d-none');
            }

            // Display helpful guidance pill
            const helper = document.getElementById('mapDrawingHelper');
            const helperText = document.getElementById('mapDrawingHelperText');
            if (helper && helperText) {
                helperText.innerHTML = `<i class="bi bi-arrows-move text-success me-1"></i><strong>${targetSquareMeters} m²</strong> &bull; Drag to position &bull; Top handle to rotate`;
                helper.classList.remove('d-none');
            }

            // Switch to pan mode cleanly without clearing drawn points
            currentDrawMode = 'pan';
            map.getContainer().style.cursor = '';
            const panBtn = document.getElementById('drawModePanBtn');
            if (panBtn) {
                panBtn.classList.add('active', 'text-dark');
                panBtn.classList.remove('text-secondary');
            }

            setRegistrationStep(3);
        };

        function createVertexMarkers() {
            vertexMarkers.forEach(m => map.removeLayer(m));
            vertexMarkers = [];

            const vertexIcon = L.divIcon({
                className: 'polygon-vertex-handle',
                html: `<div style="width:16px;height:16px;background:#16a34a;border:2.5px solid #ffffff;border-radius:50%;box-shadow:0 2px 6px rgba(0,0,0,0.35);cursor:move;" title="Drag corner to reshape (area remains fixed)"></div>`,
                iconSize: [16, 16],
                iconAnchor: [8, 8]
            });

            drawnPoints.forEach((pt, idx) => {
                const vm = L.marker(pt, { icon: vertexIcon, draggable: true }).addTo(map);
                vm.on('drag', function (ev) {
                    drawnPoints[idx] = ev.target.getLatLng();
                    // Maintain strictly fixed square meters while reshaping
                    const curArea = calculatePolygonArea(drawnPoints);
                    if (curArea > 0 && targetSquareMeters > 0) {
                        const scale = Math.sqrt(targetSquareMeters / curArea);
                        const c = calculateCentroid(drawnPoints);
                        drawnPoints = drawnPoints.map(p => {
                            return L.latLng(
                                c.lat + (p.lat - c.lat) * scale,
                                c.lng + (p.lng - c.lng) * scale
                            );
                        });
                    }
                    if (drawnPolygonLayer) drawnPolygonLayer.setLatLngs(drawnPoints);
                    vertexMarkers.forEach((m, i) => {
                        if (i !== idx && drawnPoints[i]) m.setLatLng(drawnPoints[i]);
                    });
                    if (rotationStemLine && rotationHandleMarker) {
                        const newTopMidLat = (drawnPoints[0].lat + drawnPoints[1].lat) / 2;
                        const newTopMidLng = (drawnPoints[0].lng + drawnPoints[1].lng) / 2;
                        rotationStemLine.setLatLngs([[newTopMidLat, newTopMidLng], rotationHandleMarker.getLatLng()]);
                    }
                    updateDrawnPolygonStats(false);
                    renderSubdivisions();
                });
                vm.on('dragend', function () {
                    createVertexMarkers();
                    createRotationHandle();
                    updateDrawnPolygonStats(true);
                    renderSubdivisions();
                });
                vertexMarkers.push(vm);
            });
        }

        function createRotationHandle() {
            if (rotationHandleMarker) { map.removeLayer(rotationHandleMarker); rotationHandleMarker = null; }
            if (rotationStemLine) { map.removeLayer(rotationStemLine); rotationStemLine = null; }
            if (!drawnPoints || drawnPoints.length < 3) return;

            const centroid = calculateCentroid(drawnPoints);
            const topMidLat = (drawnPoints[0].lat + drawnPoints[1].lat) / 2;
            const topMidLng = (drawnPoints[0].lng + drawnPoints[1].lng) / 2;

            const cosLat = Math.cos(centroid.lat * Math.PI / 180);
            const dLat = topMidLat - centroid.lat;
            const dLng = (topMidLng - centroid.lng) * cosLat;
            const dist = Math.hypot(dLat, dLng);

            const extMeters = 16;
            const extDegLat = extMeters / 111320;
            const factor = dist > 0 ? (1 + extDegLat / dist) : 1.35;

            const handleLat = centroid.lat + dLat * factor;
            const handleLng = centroid.lng + (dLng * factor) / cosLat;

            // Draw stem line
            rotationStemLine = L.polyline([[topMidLat, topMidLng], [handleLat, handleLng]], {
                color: '#16a34a',
                weight: 2,
                dashArray: '3, 4',
                interactive: false
            }).addTo(map);

            const rotIcon = L.divIcon({
                className: 'plot-rotation-handle',
                html: `<div style="width:28px;height:28px;background:#ffffff;border:2.5px solid #16a34a;border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 3px 8px rgba(0,0,0,0.3);cursor:grab;color:#16a34a;font-size:14px;" title="Drag to rotate plot">
                    <i class="bi bi-arrow-repeat"></i>
                </div>`,
                iconSize: [28, 28],
                iconAnchor: [14, 14]
            });

            rotationHandleMarker = L.marker([handleLat, handleLng], {
                icon: rotIcon,
                draggable: true
            }).addTo(map);

            let initialAngle = Math.atan2(handleLat - centroid.lat, (handleLng - centroid.lng) * cosLat);

            rotationHandleMarker.on('dragstart', function () {
                const c = calculateCentroid(drawnPoints);
                const pos = rotationHandleMarker.getLatLng();
                initialAngle = Math.atan2(pos.lat - c.lat, (pos.lng - c.lng) * Math.cos(c.lat * Math.PI / 180));
            });

            rotationHandleMarker.on('drag', function (ev) {
                const c = calculateCentroid(drawnPoints);
                const pos = ev.target.getLatLng();
                const curCosLat = Math.cos(c.lat * Math.PI / 180);
                const currentAngle = Math.atan2(pos.lat - c.lat, (pos.lng - c.lng) * curCosLat);
                const deltaAngle = currentAngle - initialAngle;
                initialAngle = currentAngle;

                const cosA = Math.cos(deltaAngle);
                const sinA = Math.sin(deltaAngle);

                drawnPoints = drawnPoints.map(p => {
                    const x = (p.lng - c.lng) * curCosLat;
                    const y = p.lat - c.lat;
                    const rx = x * cosA - y * sinA;
                    const ry = x * sinA + y * cosA;
                    return L.latLng(c.lat + ry, c.lng + rx / curCosLat);
                });

                if (drawnPolygonLayer) drawnPolygonLayer.setLatLngs(drawnPoints);
                vertexMarkers.forEach((vm, idx) => {
                    if (drawnPoints[idx]) vm.setLatLng(drawnPoints[idx]);
                });

                const newTopMidLat = (drawnPoints[0].lat + drawnPoints[1].lat) / 2;
                const newTopMidLng = (drawnPoints[0].lng + drawnPoints[1].lng) / 2;
                if (rotationStemLine) {
                    rotationStemLine.setLatLngs([[newTopMidLat, newTopMidLng], pos]);
                }
                renderSubdivisions();
            });

            rotationHandleMarker.on('dragend', function () {
                createVertexMarkers();
                createRotationHandle();
                updateDrawnPolygonStats(false);
                renderSubdivisions();
            });
        }

        function calculatePolygonArea(latlngs) {
            if (!latlngs || latlngs.length < 3) return 0;
            const R = 6378137; // Earth's mean radius in meters
            let total = 0;
            const len = latlngs.length;
            for (let i = 0; i < len; i++) {
                const p1 = latlngs[i];
                const p2 = latlngs[(i + 1) % len];
                const lat1 = p1.lat * Math.PI / 180;
                const lat2 = p2.lat * Math.PI / 180;
                const lon1 = p1.lng * Math.PI / 180;
                const lon2 = p2.lng * Math.PI / 180;
                total += (lon2 - lon1) * (2 + Math.sin(lat1) + Math.sin(lat2));
            }
            return Math.abs(total * (R * R) / 2);
        }

        function calculateCentroid(latlngs) {
            let latSum = 0, lngSum = 0;
            latlngs.forEach(p => {
                latSum += p.lat;
                lngSum += p.lng;
            });
            return {
                lat: latSum / latlngs.length,
                lng: lngSum / latlngs.length
            };
        }

        function updateDrawnPolygonStats(updateAddress = false) {
            const area = calculatePolygonArea(drawnPoints);
            const roundedArea = Math.round(area * 10) / 10;
            const finalArea = targetSquareMeters > 0 ? targetSquareMeters : roundedArea;

            const areaInput = document.getElementById('area');
            if (areaInput) areaInput.value = finalArea;

            const badge = document.getElementById('targetAreaBadge');
            if (badge) badge.textContent = `${finalArea.toLocaleString()} m²`;
            const editBtn = document.getElementById('btnEditPlotArea');
            if (editBtn) editBtn.classList.remove('d-none');

            const polyInput = document.getElementById('lot_polygon');
            if (polyInput) {
                polyInput.value = JSON.stringify(drawnPoints.map(p => [p.lat, p.lng]));
            }

            const summaryEl = document.getElementById('drawnBoundarySummary');
            if (summaryEl) {
                summaryEl.textContent = `Custom Plot (${drawnPoints.length} corners, ${finalArea.toLocaleString()} m²)`;
            }

            const centroid = calculateCentroid(drawnPoints);
            const latFixed = centroid.lat.toFixed(6);
            const lngFixed = centroid.lng.toFixed(6);

            document.getElementById('latitude').value = latFixed;
            document.getElementById('longitude').value = lngFixed;

            if (marker) {
                marker.setLatLng([centroid.lat, centroid.lng]);
            } else {
                marker = L.marker([centroid.lat, centroid.lng], { draggable: true, icon: customPinIcon }).addTo(map);
                marker.on('dragend', function (e) {
                    const pos = e.target.getLatLng();
                    setLocation(pos.lat, pos.lng);
                    reverseGeocode(pos.lat, pos.lng);
                });
            }

            const coordsBadge = document.getElementById('coordsBadge');
            if (coordsBadge) {
                coordsBadge.textContent = `${latFixed}, ${lngFixed}`;
                coordsBadge.style.display = 'inline-block';
            }

            if (updateAddress) {
                reverseGeocode(centroid.lat, centroid.lng);
            }

            updatePlotCalculation(false);
        }

        function clipPolygonSutherlandHodgman(subjectPolygon, clipPolygon) {
            let outputList = subjectPolygon;
            const clipLen = clipPolygon.length;
            for (let c = 0; c < clipLen; c++) {
                const a = clipPolygon[c];
                const b = clipPolygon[(c + 1) % clipLen];
                const inputList = outputList;
                outputList = [];
                if (inputList.length === 0) break;

                let s = inputList[inputList.length - 1];
                for (let i = 0; i < inputList.length; i++) {
                    const p = inputList[i];
                    // Inside test: point on left side of directed edge a -> b
                    const pInside = (b[0] - a[0]) * (p[1] - a[1]) - (b[1] - a[1]) * (p[0] - a[0]) >= -1e-9;
                    const sInside = (b[0] - a[0]) * (s[1] - a[1]) - (b[1] - a[1]) * (s[0] - a[0]) >= -1e-9;

                    if (pInside) {
                        if (!sInside) {
                            outputList.push(intersectLineSegments(s, p, a, b));
                        }
                        outputList.push(p);
                    } else if (sInside) {
                        outputList.push(intersectLineSegments(s, p, a, b));
                    }
                    s = p;
                }
            }
            return outputList;
        }

        function intersectLineSegments(p1, p2, p3, p4) {
            const denom = (p4[1] - p3[1]) * (p2[0] - p1[0]) - (p4[0] - p3[0]) * (p2[1] - p1[1]);
            if (Math.abs(denom) < 1e-12) return p1;
            const ua = ((p4[0] - p3[0]) * (p1[1] - p3[1]) - (p4[1] - p3[1]) * (p1[0] - p3[0])) / denom;
            return [p1[0] + ua * (p2[0] - p1[0]), p1[1] + ua * (p2[1] - p1[1])];
        }

        function renderSubdivisions() {
            subdivisionLayers.forEach(l => map.removeLayer(l));
            subdivisionLayers = [];

            if (!drawnPolygonLayer || !drawnPoints || drawnPoints.length < 3) {
                return;
            }

            const count = parseInt(document.getElementById('plot_count').value) || 4;
            const totalArea = parseFloat(document.getElementById('area').value) || 200;
            const singlePlotArea = totalArea > 0 ? (totalArea / count).toFixed(1) : Math.round(totalArea / count);
            const permittedCrops = Array.from(selectedCrops.values()).map(c => c.label);

            const areaInputs = Array.from(document.querySelectorAll('.plot-area-input'));
            const customAreas = areaInputs.map(inp => parseFloat(inp.value)).filter(v => !isNaN(v) && v > 0);
            const hasCustomAreas = (customAreas.length === count);

            let subPlotPolygons = [];

            // Case A: 4-Point Quadrilateral (Standard or Irregularlot)
            let quadPts = drawnPoints.slice();
            if (quadPts.length === 3) {
                // Split longest edge into two to treat triangle as 4-point shape
                const d01 = Math.hypot(quadPts[1].lat - quadPts[0].lat, quadPts[1].lng - quadPts[0].lng);
                const d12 = Math.hypot(quadPts[2].lat - quadPts[1].lat, quadPts[2].lng - quadPts[1].lng);
                const d20 = Math.hypot(quadPts[0].lat - quadPts[2].lat, quadPts[0].lng - quadPts[2].lng);
                if (d01 >= d12 && d01 >= d20) {
                    quadPts = [quadPts[0], { lat: (quadPts[0].lat + quadPts[1].lat) / 2, lng: (quadPts[0].lng + quadPts[1].lng) / 2 }, quadPts[1], quadPts[2]];
                } else if (d12 >= d01 && d12 >= d20) {
                    quadPts = [quadPts[0], quadPts[1], { lat: (quadPts[1].lat + quadPts[2].lat) / 2, lng: (quadPts[1].lng + quadPts[2].lng) / 2 }, quadPts[2]];
                } else {
                    quadPts = [quadPts[0], quadPts[1], quadPts[2], { lat: (quadPts[2].lat + quadPts[0].lat) / 2, lng: (quadPts[2].lng + quadPts[0].lng) / 2 }];
                }
            }

            if (quadPts.length === 4) {
                // Determine orientation & aspect ratio along the polygon's edges
                const widthDist = (Math.hypot(quadPts[1].lat - quadPts[0].lat, quadPts[1].lng - quadPts[0].lng) +
                    Math.hypot(quadPts[2].lat - quadPts[3].lat, quadPts[2].lng - quadPts[3].lng)) / 2;
                const heightDist = (Math.hypot(quadPts[3].lat - quadPts[0].lat, quadPts[3].lng - quadPts[0].lng) +
                    Math.hypot(quadPts[2].lat - quadPts[1].lat, quadPts[2].lng - quadPts[1].lng)) / 2;
                const isTaller = heightDist > widthDist * 1.15;

                // Bilinear mapping function on the irregular 4-point quadrilateral
                const interpPoint = (u, v) => {
                    const lat = (1 - v) * ((1 - u) * quadPts[0].lat + u * quadPts[1].lat) +
                        v * ((1 - u) * quadPts[3].lat + u * quadPts[2].lat);
                    const lng = (1 - v) * ((1 - u) * quadPts[0].lng + u * quadPts[1].lng) +
                        v * ((1 - u) * quadPts[3].lng + u * quadPts[2].lng);
                    return [lat, lng];
                };

                if (count === 1) {
                    subPlotPolygons.push({
                        index: 1,
                        points: [interpPoint(0, 0), interpPoint(1, 0), interpPoint(1, 1), interpPoint(0, 1)]
                    });
                } else if (count === 2 && hasCustomAreas) {
                    const r0 = Math.min(0.9, Math.max(0.1, customAreas[0] / (customAreas[0] + customAreas[1] || 1)));
                    if (isTaller) {
                        subPlotPolygons.push({
                            index: 1,
                            points: [interpPoint(0, 0), interpPoint(1, 0), interpPoint(1, r0), interpPoint(0, r0)]
                        });
                        subPlotPolygons.push({
                            index: 2,
                            points: [interpPoint(0, r0), interpPoint(1, r0), interpPoint(1, 1), interpPoint(0, 1)]
                        });
                    } else {
                        subPlotPolygons.push({
                            index: 1,
                            points: [interpPoint(0, 0), interpPoint(r0, 0), interpPoint(r0, 1), interpPoint(0, 1)]
                        });
                        subPlotPolygons.push({
                            index: 2,
                            points: [interpPoint(r0, 0), interpPoint(1, 0), interpPoint(1, 1), interpPoint(r0, 1)]
                        });
                    }
                } else if (count === 4 && hasCustomAreas) {
                    const sumR0 = customAreas[0] + customAreas[1];
                    const sumR1 = customAreas[2] + customAreas[3];
                    const vSplit = Math.min(0.85, Math.max(0.15, sumR0 / (sumR0 + sumR1 || 1)));
                    const uSplit0 = Math.min(0.85, Math.max(0.15, customAreas[0] / (sumR0 || 1)));
                    const uSplit1 = Math.min(0.85, Math.max(0.15, customAreas[2] / (sumR1 || 1)));

                    subPlotPolygons.push({
                        index: 1,
                        points: [interpPoint(0, 0), interpPoint(uSplit0, 0), interpPoint(uSplit0, vSplit), interpPoint(0, vSplit)]
                    });
                    subPlotPolygons.push({
                        index: 2,
                        points: [interpPoint(uSplit0, 0), interpPoint(1, 0), interpPoint(1, vSplit), interpPoint(uSplit0, vSplit)]
                    });
                    subPlotPolygons.push({
                        index: 3,
                        points: [interpPoint(0, vSplit), interpPoint(uSplit1, vSplit), interpPoint(uSplit1, 1), interpPoint(0, 1)]
                    });
                    subPlotPolygons.push({
                        index: 4,
                        points: [interpPoint(uSplit1, vSplit), interpPoint(1, vSplit), interpPoint(1, 1), interpPoint(uSplit1, 1)]
                    });
                } else {
                    let cols, rows;
                    if (count === 2) { cols = isTaller ? 1 : 2; rows = isTaller ? 2 : 1; }
                    else if (count === 3) { cols = isTaller ? 1 : 3; rows = isTaller ? 3 : 1; }
                    else if (count === 4) { cols = 2; rows = 2; }
                    else if (count === 6) { cols = isTaller ? 2 : 3; rows = isTaller ? 3 : 2; }
                    else if (count === 8) { cols = isTaller ? 2 : 4; rows = isTaller ? 4 : 2; }
                    else {
                        cols = Math.ceil(Math.sqrt(count));
                        rows = Math.ceil(count / cols);
                    }

                    let idx = 1;
                    for (let r = 0; r < rows; r++) {
                        for (let c = 0; c < cols; c++) {
                            if (idx > count) break;
                            const u0 = c / cols;
                            const u1 = (c + 1) / cols;
                            const v0 = r / rows;
                            const v1 = (r + 1) / rows;

                            const cellQuad = [
                                interpPoint(u0, v0),
                                interpPoint(u1, v0),
                                interpPoint(u1, v1),
                                interpPoint(u0, v1)
                            ];
                            subPlotPolygons.push({ index: idx, points: cellQuad });
                            idx++;
                        }
                    }
                }
            } else {
                // Case B: Arbitrary N-sided irregular polygon (N > 4)
                const c = calculateCentroid(drawnPoints);
                const cosLat = Math.cos(c.lat * Math.PI / 180);

                // Align grid with first edge of the irregular polygon
                const ang = Math.atan2(drawnPoints[1].lat - drawnPoints[0].lat, (drawnPoints[1].lng - drawnPoints[0].lng) * cosLat);
                const cosA = Math.cos(-ang);
                const sinA = Math.sin(-ang);

                // Project to local coordinates
                const localPts = drawnPoints.map(p => {
                    const x = (p.lng - c.lng) * cosLat;
                    const y = p.lat - c.lat;
                    return [x * cosA - y * sinA, x * sinA + y * cosA];
                });

                let minX = Infinity, maxX = -Infinity, minY = Infinity, maxY = -Infinity;
                localPts.forEach(([x, y]) => {
                    if (x < minX) minX = x;
                    if (x > maxX) maxX = x;
                    if (y < minY) minY = y;
                    if (y > maxY) maxY = y;
                });

                const w = maxX - minX;
                const h = maxY - minY;
                const isTaller = h > w * 1.15;

                let cols, rows;
                if (count === 1) { cols = 1; rows = 1; }
                else if (count === 2) { cols = isTaller ? 1 : 2; rows = isTaller ? 2 : 1; }
                else if (count === 3) { cols = isTaller ? 1 : 3; rows = isTaller ? 3 : 1; }
                else if (count === 4) { cols = 2; rows = 2; }
                else if (count === 6) { cols = isTaller ? 2 : 3; rows = isTaller ? 3 : 2; }
                else if (count === 8) { cols = isTaller ? 2 : 4; rows = isTaller ? 4 : 2; }
                else {
                    cols = Math.ceil(Math.sqrt(count));
                    rows = Math.ceil(count / cols);
                }

                const dx = w / cols;
                const dy = h / rows;
                const invCosA = Math.cos(ang);
                const invSinA = Math.sin(ang);

                const toWorld = ([x, y]) => {
                    const rx = x * invCosA - y * invSinA;
                    const ry = x * invSinA + y * invCosA;
                    return [c.lat + ry, c.lng + rx / cosLat];
                };

                let idx = 1;
                for (let r = 0; r < rows; r++) {
                    for (let col = 0; col < cols; col++) {
                        if (idx > count) break;
                        const cellMinX = minX + col * dx;
                        const cellMaxX = minX + (col + 1) * dx;
                        const cellMinY = maxY - (r + 1) * dy;
                        const cellMaxY = maxY - r * dy;

                        const cellQuadLocal = [
                            [cellMinX, cellMaxY],
                            [cellMaxX, cellMaxY],
                            [cellMaxX, cellMinY],
                            [cellMinX, cellMinY]
                        ];

                        const clippedLocal = clipPolygonSutherlandHodgman(localPts, cellQuadLocal);
                        if (clippedLocal && clippedLocal.length >= 3) {
                            subPlotPolygons.push({
                                index: idx,
                                points: clippedLocal.map(toWorld)
                            });
                            idx++;
                        }
                    }
                }
            }

            subPlotPolygons.forEach(({ index, points }) => {
                const plotPoly = L.polygon(points, {
                    color: '#16a34a',
                    weight: 1.5,
                    fillColor: '#22c55e',
                    fillOpacity: 0.22,
                    dashArray: '3, 4'
                }).addTo(map);

                const plotName = `Plot A-${index}`;
                const thisPlotArea = (hasCustomAreas && customAreas[index - 1]) ? customAreas[index - 1] : singlePlotArea;
                const cropName = permittedCrops.length > 0 ? permittedCrops[(index - 1) % permittedCrops.length] : 'Available for Gardeners';

                plotPoly.bindTooltip(
                    `<div style="font-family:'Outfit',sans-serif;font-size:11px;line-height:1.3;padding:1px;">
                        <strong class="text-success"><i class="bi bi-grid-3x3-gap-fill me-1"></i>${plotName}</strong><br>
                        <span class="text-dark font-monospace fw-bold">${thisPlotArea} m²</span><br>
                        <span class="badge bg-success-subtle text-success mt-1" style="font-size:9px;">${cropName}</span>
                    </div>`,
                    { permanent: false, direction: 'center' }
                );

                const centerLat = points.reduce((sum, p) => sum + (Array.isArray(p) ? p[0] : p.lat), 0) / points.length;
                const centerLng = points.reduce((sum, p) => sum + (Array.isArray(p) ? p[1] : p.lng), 0) / points.length;

                const centerLabel = L.marker([centerLat, centerLng], {
                    icon: L.divIcon({
                        className: 'partition-plot-label',
                        html: `<div style="background:rgba(22,163,74,0.88);color:#fff;font-size:9.5px;font-weight:700;padding:2px 7px;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,0.25);pointer-events:none;white-space:nowrap;transform:translate(-50%,-50%);">${plotName} • ${thisPlotArea}m²</div>`,
                        iconSize: [0, 0]
                    }),
                    interactive: false
                }).addTo(map);

                subdivisionLayers.push(plotPoly);
                subdivisionLayers.push(centerLabel);
            });
        }

        window.clearDrawnPlot = function () {
            cancelTempDrawing();

            if (drawnPolygonLayer) {
                map.removeLayer(drawnPolygonLayer);
                drawnPolygonLayer = null;
            }
            if (rotationHandleMarker) {
                map.removeLayer(rotationHandleMarker);
                rotationHandleMarker = null;
            }
            if (rotationStemLine) {
                map.removeLayer(rotationStemLine);
                rotationStemLine = null;
            }
            vertexMarkers.forEach(m => map.removeLayer(m));
            vertexMarkers = [];
            subdivisionLayers.forEach(l => map.removeLayer(l));
            subdivisionLayers = [];
            drawnPoints = [];

            const polyInput = document.getElementById('lot_polygon');
            if (polyInput) polyInput.value = '';

            const summaryEl = document.getElementById('drawnBoundarySummary');
            if (summaryEl) {
                summaryEl.textContent = 'No custom boundary drawn.';
            }

            const clearBtn = document.getElementById('btnClearDrawnPlot');
            if (clearBtn) clearBtn.classList.add('d-none');
            const formClearBtn = document.getElementById('btnFormClearDrawn');
            if (formClearBtn) formClearBtn.classList.add('d-none');
            const rotLeft = document.getElementById('btnRotateLeft');
            const rotRight = document.getElementById('btnRotateRight');
            if (rotLeft) rotLeft.classList.add('d-none');
            if (rotRight) rotRight.classList.add('d-none');
            const editBtn = document.getElementById('btnEditPlotArea');
            if (editBtn) editBtn.classList.add('d-none');

            // Hide the Plot Control Overlay when plot is cleared
            const plotOverlay = document.getElementById('plotControlOverlay');
            if (plotOverlay) {
                plotOverlay.classList.add('d-none');
            }

            setRegistrationStep(1);
            setPlotDrawMode('pan');
            const helper = document.getElementById('mapDrawingHelper');
            const helperText = document.getElementById('mapDrawingHelperText');
            if (helper && helperText) {
                helperText.innerHTML = '<i class="bi bi-geo-alt-fill text-success me-1"></i>Plot cleared. Click anywhere on the map to pinpoint location and set area.';
                helper.classList.remove('d-none');
            }
        };

        function initLayoutPlots(count) {
            const totalArea = parseFloat(document.getElementById('area').value) || 250;
            const sideMeters = Math.max(16, Math.sqrt(totalArea));

            const cols = Math.ceil(Math.sqrt(count));
            const rows = Math.ceil(count / cols);
            const plotW = Math.max(4, (sideMeters - (cols + 1) * 0.8) / cols);
            const plotH = Math.max(4, (sideMeters - (rows + 1) * 0.8) / rows);
            const singleArea = Math.round((totalArea / count) * 10) / 10;
            const permittedCrops = Array.from(selectedCrops.values()).map(c => c.label);

            activeLayoutPlots = [];
            for (let i = 0; i < count; i++) {
                const c = i % cols;
                const r = Math.floor(i / cols);

                const posX = -sideMeters / 2 + 0.8 + (c + 0.5) * plotW + c * 0.8;
                const posZ = -sideMeters / 2 + 0.8 + (r + 0.5) * plotH + r * 0.8;

                activeLayoutPlots.push({
                    id: i + 1,
                    land_id: 9999,
                    plot_number: `Plot A-${i + 1}`,
                    customX: posX,
                    customZ: posZ,
                    width: plotW,
                    height: plotH,
                    area: singleArea,
                    crop: permittedCrops.length > 0 ? permittedCrops[i % permittedCrops.length] : 'Mixed Vegetables',
                    status: 'available'
                });
            }
        }

        function buildMockLand() {
            const lat = parseFloat(document.getElementById('latitude').value) || 14.5995;
            const lng = parseFloat(document.getElementById('longitude').value) || 120.9842;
            const areaVal = parseFloat(document.getElementById('area').value) || 250;
            const titleVal = document.getElementById('title').value.trim() || 'Community Garden Plot';
            const permittedCrops = Array.from(selectedCrops.values()).map(c => c.label);

            return {
                id: 9999,
                title: titleVal,
                latitude: lat,
                longitude: lng,
                area: areaVal,
                crops: permittedCrops.length > 0 ? permittedCrops : ['Vegetables', 'Herbs'],
                polygon: drawnPoints.length >= 3 ? drawnPoints.map(p => [p.lat, p.lng]) : null
            };
        }

        // ─── Partition Plots Setup, Table Rendering & Base Rent Calculation ───
        window.adjustPlotCount = function (delta) {
            const input = document.getElementById('plot_count');
            let count = parseInt(input ? input.value : 4) || 4;
            count = Math.max(1, Math.min(20, count + delta));
            if (input) input.value = count;
            onPlotCountChanged(count);
        };

        window.selectPlotCount = function (count, btn) {
            selectPlotCountPreset(count, btn);
        };

        window.selectPlotCountPreset = function (count, btn) {
            const input = document.getElementById('plot_count');
            if (input) input.value = count;
            onPlotCountChanged(count);
        };

        window.onPlotCountChanged = function (val) {
            let count = parseInt(val) || 4;
            count = Math.max(1, Math.min(20, count));
            const input = document.getElementById('plot_count');
            if (input) input.value = count;
            const formPlotCount = document.getElementById('form_plot_count');
            if (formPlotCount) formPlotCount.value = count;

            // Highlight corresponding preset button in form below
            document.querySelectorAll('.plot-preset-btn').forEach(b => {
                const bCount = parseInt(b.textContent);
                if (bCount === count) {
                    b.classList.remove('btn-outline-secondary');
                    b.classList.add('btn-success', 'active', 'text-white');
                } else {
                    b.classList.remove('btn-success', 'active', 'text-white');
                    b.classList.add('btn-outline-secondary');
                }
            });

            // Highlight corresponding button in map overlay (#plotControlOverlay)
            document.querySelectorAll('.plot-count-btn').forEach(b => {
                const bCount = parseInt(b.getAttribute('data-count'));
                if (bCount === count) {
                    b.classList.add('active');
                } else {
                    b.classList.remove('active');
                }
            });

            // Update badge on map overlay
            const plotCalcBadge = document.getElementById('plotCalcBadge');
            const areaInput = document.getElementById('area');
            const totalArea = parseFloat(areaInput ? areaInput.value : 0) || 250;
            const singleArea = (totalArea / count).toFixed(1);
            if (plotCalcBadge) {
                plotCalcBadge.textContent = `${count} plots (~${singleArea} m²)`;
            }

            renderPlotsConfigTable(false);
            updatePlotCalculation(false);
        };

        window.calculateTotalRent = function () {
            let totalMonthly = 0;
            const rentInputs = document.querySelectorAll('.plot-monthly-rent-input');
            rentInputs.forEach(input => {
                const val = parseFloat(input.value) || 0;
                totalMonthly += val;
                const row = input.closest('tr');
                if (row) {
                    const annualEl = row.querySelector('.plot-annual-rent-display');
                    const hiddenAnnual = row.querySelector('.plot-base-rent-hidden');
                    const annual = val > 0 ? (val * 12) : 0;
                    if (annualEl) {
                        annualEl.textContent = `₱${annual.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}/yr`;
                    }
                    if (hiddenAnnual) {
                        hiddenAnnual.value = annual.toFixed(2);
                    }
                }
            });

            const totalAnnual = totalMonthly * 12;
            const elAnnual = document.getElementById('totalProjectedAnnualRent');
            const elMonthly = document.getElementById('totalProjectedMonthlyRent');
            if (elMonthly) elMonthly.textContent = `₱${totalMonthly.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
            if (elAnnual) elAnnual.textContent = `₱${totalAnnual.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        };

        window.onPlotAreaChanged = function (changedIndex, changedInput) {
            const areaInput = document.getElementById('area');
            const totalArea = parseFloat(areaInput ? areaInput.value : 250) || 250;
            const allAreaInputs = Array.from(document.querySelectorAll('.plot-area-input'));
            const N = allAreaInputs.length;
            if (N <= 1) {
                if (allAreaInputs[0]) allAreaInputs[0].value = totalArea.toFixed(1);
                updateAllocatedAreaBadge();
                calculateTotalRent();
                renderSubdivisions();
                return;
            }

            let rawVal = parseFloat(changedInput.value);
            if (isNaN(rawVal)) {
                updateAllocatedAreaBadge();
                return; // User is in the middle of clearing or typing a number
            }

            const minArea = 1.0;
            const maxVal = Math.max(minArea, totalArea - (N - 1) * minArea);
            let val = Math.min(Math.max(rawVal, minArea), maxVal);
            val = Math.round(val * 10) / 10;
            changedInput.value = val;
            changedInput.setAttribute('data-manual', 'true');

            let remainingArea = totalArea - val;

            // Other inputs
            const otherInputs = allAreaInputs.filter((inp, idx) => idx !== changedIndex);
            const uneditedOthers = otherInputs.filter(inp => inp.getAttribute('data-manual') !== 'true');

            if (uneditedOthers.length > 0) {
                // Sum any other inputs that were already manually set
                let manualSum = 0;
                otherInputs.forEach(inp => {
                    if (inp.getAttribute('data-manual') === 'true') {
                        manualSum += (parseFloat(inp.value) || minArea);
                    }
                });

                let availableForUnedited = totalArea - val - manualSum;
                if (availableForUnedited < uneditedOthers.length * minArea) {
                    // Reset manual flag on others to avoid negative/zero areas
                    otherInputs.forEach(inp => inp.removeAttribute('data-manual'));
                    const perPlot = remainingArea / otherInputs.length;
                    let running = 0;
                    otherInputs.forEach((inp, i) => {
                        if (i === otherInputs.length - 1) {
                            inp.value = Math.max(minArea, Math.round((remainingArea - running) * 10) / 10).toFixed(1);
                        } else {
                            const a = Math.max(minArea, Math.round(perPlot * 10) / 10);
                            running += a;
                            inp.value = a.toFixed(1);
                        }
                    });
                } else {
                    const perPlot = availableForUnedited / uneditedOthers.length;
                    let running = 0;
                    uneditedOthers.forEach((inp, i) => {
                        if (i === uneditedOthers.length - 1) {
                            inp.value = Math.max(minArea, Math.round((availableForUnedited - running) * 10) / 10).toFixed(1);
                        } else {
                            const a = Math.max(minArea, Math.round(perPlot * 10) / 10);
                            running += a;
                            inp.value = a.toFixed(1);
                        }
                    });
                }
            } else {
                // All other inputs were previously edited: scale proportionally to absorb the delta
                let sumOthers = 0;
                otherInputs.forEach(inp => sumOthers += (parseFloat(inp.value) || minArea));
                let running = 0;
                otherInputs.forEach((inp, i) => {
                    if (i === otherInputs.length - 1) {
                        inp.value = Math.max(minArea, Math.round((remainingArea - running) * 10) / 10).toFixed(1);
                    } else {
                        const ratio = sumOthers > 0 ? ((parseFloat(inp.value) || minArea) / sumOthers) : (1 / otherInputs.length);
                        const a = Math.max(minArea, Math.round(remainingArea * ratio * 10) / 10);
                        running += a;
                        inp.value = a.toFixed(1);
                    }
                });
            }

            // Update recommended monthly rent automatically for rows where user hasn't explicitly entered a custom rent
            allAreaInputs.forEach(areaInp => {
                const row = areaInp.closest('tr');
                if (row) {
                    const rentInp = row.querySelector('.plot-monthly-rent-input');
                    if (rentInp && rentInp.getAttribute('data-custom-rent') !== 'true') {
                        const a = parseFloat(areaInp.value) || 0;
                        rentInp.value = Math.max(2000, Math.round(a * (2200 / 12)));
                    }
                }
            });

            updateAllocatedAreaBadge();
            calculateTotalRent();
            renderSubdivisions();
        };

        window.updateAllocatedAreaBadge = function () {
            const badge = document.getElementById('allocatedAreaBadge');
            if (!badge) return;
            const areaInput = document.getElementById('area');
            const totalArea = parseFloat(areaInput ? areaInput.value : 250) || 250;
            const allAreaInputs = Array.from(document.querySelectorAll('.plot-area-input'));
            let sumAllocated = 0;
            allAreaInputs.forEach(inp => sumAllocated += (parseFloat(inp.value) || 0));
            sumAllocated = Math.round(sumAllocated * 10) / 10;

            const diff = Math.abs(sumAllocated - totalArea);
            if (diff <= 0.2) {
                badge.className = 'badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-0.5 text-xs fw-semibold';
                badge.innerHTML = `<i class="bi bi-check-circle-fill me-1"></i>${sumAllocated.toFixed(1)} / ${totalArea} m² (100% Allocated)`;
            } else if (sumAllocated > totalArea) {
                badge.className = 'badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-0.5 text-xs fw-semibold';
                badge.innerHTML = `<i class="bi bi-exclamation-triangle-fill me-1"></i>${sumAllocated.toFixed(1)} / ${totalArea} m² (+${(sumAllocated - totalArea).toFixed(1)} m² Over)`;
            } else {
                badge.className = 'badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-0.5 text-xs fw-semibold';
                badge.innerHTML = `<i class="bi bi-info-circle-fill me-1"></i>${sumAllocated.toFixed(1)} / ${totalArea} m² (${(totalArea - sumAllocated).toFixed(1)} m² Remaining)`;
            }
        };

        window.resetPlotsToEqualSizes = function () {
            const areaInput = document.getElementById('area');
            const totalArea = parseFloat(areaInput ? areaInput.value : 250) || 250;
            const allAreaInputs = Array.from(document.querySelectorAll('.plot-area-input'));
            const N = allAreaInputs.length;
            if (N === 0) return;
            const single = (totalArea / N);
            let running = 0;
            allAreaInputs.forEach((inp, idx) => {
                inp.removeAttribute('data-manual');
                if (idx === N - 1) {
                    inp.value = Math.max(1, Math.round((totalArea - running) * 10) / 10).toFixed(1);
                } else {
                    const a = Math.max(1, Math.round(single * 10) / 10);
                    running += a;
                    inp.value = a.toFixed(1);
                }
                const row = inp.closest('tr');
                if (row) {
                    const rentInp = row.querySelector('.plot-monthly-rent-input');
                    if (rentInp && rentInp.getAttribute('data-custom-rent') !== 'true') {
                        rentInp.value = Math.max(2000, Math.round(parseFloat(inp.value) * (2200 / 12)));
                    }
                }
            });
            updateAllocatedAreaBadge();
            calculateTotalRent();
            renderSubdivisions();
        };

        window.renderPlotsConfigTable = function (preserveManual = true, explicitAreas = null) {
            const tableBody = document.getElementById('plotsConfigTableBody');
            if (!tableBody) return;

            const input = document.getElementById('plot_count');
            const count = parseInt(input ? input.value : 4) || 4;
            const areaInput = document.getElementById('area');
            const totalArea = parseFloat(areaInput ? areaInput.value : 250) || 250;
            const singleArea = (totalArea / count).toFixed(1);

            // Default benchmark monthly rent: 50m² = ~₱9,167/mo (₱2,200/yr per m²), minimum ₱2,000/mo
            const defaultMonthly = Math.max(2000, Math.round(parseFloat(singleArea) * (2200 / 12)));

            // Retain any existing values user typed
            const existingMonthlyRents = Array.from(document.querySelectorAll('.plot-monthly-rent-input')).map(i => i.value);
            const existingCustomRentFlags = Array.from(document.querySelectorAll('.plot-monthly-rent-input')).map(i => i.getAttribute('data-custom-rent'));
            const existingAreas = Array.from(document.querySelectorAll('.plot-area-input')).map(i => i.value);
            const existingManualFlags = Array.from(document.querySelectorAll('.plot-area-input')).map(i => i.getAttribute('data-manual'));
            const existingNames = Array.from(document.querySelectorAll('.plot-name-input')).map(i => i.value);

            let rowsHtml = '';
            for (let i = 0; i < count; i++) {
                const plotNum = existingNames[i] || `Plot A-${i + 1}`;
                const plotArea = (explicitAreas && explicitAreas[i] !== undefined)
                    ? parseFloat(explicitAreas[i]).toFixed(1)
                    : ((preserveManual && existingAreas[i]) ? existingAreas[i] : singleArea);
                const isManual = (explicitAreas && explicitAreas[i] !== undefined)
                    ? 'data-manual="true"'
                    : ((preserveManual && existingManualFlags[i] === 'true') ? 'data-manual="true"' : '');
                const isCustomRent = (preserveManual && existingCustomRentFlags[i] === 'true') ? 'data-custom-rent="true"' : '';
                const plotMonthly = existingMonthlyRents[i] || Math.max(2000, Math.round(parseFloat(plotArea) * (2200 / 12)));
                const plotAnnual = parseFloat(plotMonthly) * 12;

                rowsHtml += `
                    <tr>
                        <td class="ps-3 py-2">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light border-light-subtle text-success">
                                    <i class="bi bi-grid-3x3"></i>
                                </span>
                                <input type="text" name="plot_numbers[]" class="form-control form-control-sm plot-name-input fw-semibold" value="${escapeHtml(plotNum)}" required>
                            </div>
                        </td>
                        <td class="py-2">
                            <div class="input-group input-group-sm">
                                <input type="number" step="0.1" min="1" max="${totalArea}" name="plot_areas[]" 
                                    class="form-control form-control-sm plot-area-input fw-bold text-dark" 
                                    value="${plotArea}" ${isManual} required 
                                    oninput="onPlotAreaChanged(${i}, this)">
                                <span class="input-group-text bg-light text-muted" style="font-size: 11px;">m²</span>
                            </div>
                        </td>
                        <td class="py-2">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-success-subtle text-success fw-bold">₱</span>
                                <input type="number" step="50" min="100" name="plot_monthly_rents[]" 
                                    class="form-control form-control-sm plot-monthly-rent-input fw-bold text-dark" 
                                    value="${plotMonthly}" ${isCustomRent} required 
                                    oninput="this.setAttribute('data-custom-rent', 'true'); calculateTotalRent();">
                                <span class="input-group-text bg-light text-muted" style="font-size: 11px;">/ mo</span>
                            </div>
                            <input type="hidden" name="plot_base_rents[]" class="plot-base-rent-hidden" value="${plotAnnual.toFixed(2)}">
                        </td>
                        <td class="pe-3 py-2 text-end">
                            <span class="badge bg-light text-secondary border rounded-pill px-2.5 py-1 text-xs plot-annual-rent-display fw-semibold">
                                ₱${plotAnnual.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}/yr
                            </span>
                        </td>
                    </tr>
                `;
            }

            tableBody.innerHTML = rowsHtml;
            updateAllocatedAreaBadge();
            calculateTotalRent();
        };

        window.updatePlotCalculation = function (syncTarget = true) {
            const areaInput = document.getElementById('area');
            const totalArea = parseFloat(areaInput ? areaInput.value : 0) || 0;
            if (syncTarget && totalArea > 0) {
                targetSquareMeters = totalArea;
                const badge = document.getElementById('targetAreaBadge');
                if (badge) badge.textContent = `${totalArea.toLocaleString()} m²`;
                const editBtn = document.getElementById('btnEditPlotArea');
                if (editBtn) editBtn.classList.remove('d-none');
            }
            const plotCountSelect = document.getElementById('plot_count');
            const count = parseInt(plotCountSelect ? plotCountSelect.value : 4) || 4;
            const singleArea = totalArea > 0 ? (totalArea / count).toFixed(1) : '—';

            const summaryBadge = document.getElementById('plotCalcSummaryBadge');
            if (summaryBadge && totalArea > 0) {
                summaryBadge.innerHTML = `<i class="bi bi-info-circle text-primary me-1"></i> ${count} plots partition: ~${singleArea} m² each`;
            }

            // Sync plots config table
            if (syncTarget) {
                renderPlotsConfigTable();
            }

            if (drawnPolygonLayer) {
                renderSubdivisions();
            } else {
                const latVal = parseFloat(document.getElementById('latitude').value);
                const lngVal = parseFloat(document.getElementById('longitude').value);
                if (!isNaN(latVal) && !isNaN(lngVal)) {
                    updateLotBoundary(latVal, lngVal);
                }
            }
        };

        // ─── 2D / 3D View Switcher in Card ───
        window.switchPickerMode = function (mode) {
            const btn2D = document.getElementById('viewMode2DBtn');
            const btn3D = document.getElementById('viewMode3DBtn');
            const container3D = document.getElementById('register3DMapContainer');
            const map2D = document.getElementById('pickerMap');
            const searchBox = document.querySelector('.map-picker-search-box');
            const statusBar = document.getElementById('mapStatusBar');
            const drawToolbar = document.getElementById('plotDrawToolbar');
            const helper = document.getElementById('mapDrawingHelper');
            const clearBtn = document.getElementById('btnClearDrawnPlot');
            const plotOverlay = document.getElementById('plotControlOverlay');
            const scrim = document.getElementById('mapTopGradientScrim');

            if (mode === '3d') {
                const latVal = document.getElementById('latitude').value.trim();
                const lngVal = document.getElementById('longitude').value.trim();
                const lat = parseFloat(latVal);
                const lng = parseFloat(lngVal);

                if (isNaN(lat) || isNaN(lng)) {
                    alert('Please select or type location coordinates on the map before viewing in 3D.');
                    switchPickerMode('2d');
                    return;
                }

                btn2D.classList.remove('active', 'text-success', 'fw-bold');
                btn2D.classList.add('text-muted');
                btn3D.classList.add('active', 'text-primary', 'fw-bold');
                btn3D.classList.remove('text-muted');

                if (drawToolbar) drawToolbar.classList.add('d-none');
                if (helper) helper.classList.add('d-none');
                if (clearBtn) clearBtn.classList.add('d-none');
                if (searchBox) searchBox.classList.add('d-none');
                if (plotOverlay) plotOverlay.classList.add('d-none');
                if (scrim) scrim.classList.add('d-none');
                if (statusBar) statusBar.classList.add('d-none');
                const fabControls = document.getElementById('mapFloatingFabControls');
                if (fabControls) fabControls.classList.add('d-none');
                if (map2D) map2D.style.display = 'none';
                if (container3D) container3D.classList.add('active');

                const mockLand = buildMockLand();
                if (activeLayoutPlots.length === 0) {
                    initLayoutPlots(parseInt(document.getElementById('plot_count').value) || 4);
                }

                if (!register3dEngine) {
                    register3dEngine = new Map3DEngine({
                        container: container3D,
                        center: { lat: lat, lng: lng },
                        landsData: [mockLand],
                        plotsData: activeLayoutPlots,
                        enablePlotDragging: true,
                        basePath: '<?php echo $base_path; ?>',
                        onExit3D: function () {
                            switchPickerMode('2d');
                        },
                        onPlotRepositioned: function (plot, newX, newZ) {
                            const target = activeLayoutPlots.find(p => p.id === plot.id);
                            if (target) {
                                target.customX = newX;
                                target.customZ = newZ;
                            }
                        }
                    });
                } else {
                    register3dEngine.plotsData = activeLayoutPlots;
                }

                register3dEngine.generateForPlot(mockLand, null);
            } else {
                btn3D.classList.remove('active', 'text-primary', 'fw-bold');
                btn3D.classList.add('text-muted');
                btn2D.classList.add('active', 'text-success', 'fw-bold');
                btn2D.classList.remove('text-muted');

                if (drawToolbar) drawToolbar.classList.remove('d-none');
                if (clearBtn && drawnPolygonLayer) clearBtn.classList.remove('d-none');
                if (searchBox) searchBox.classList.remove('d-none');
                if (plotOverlay && drawnPolygonLayer) plotOverlay.classList.remove('d-none');
                if (scrim) scrim.classList.remove('d-none');
                if (statusBar) statusBar.classList.remove('d-none');
                const fabControls2D = document.getElementById('mapFloatingFabControls');
                if (fabControls2D) fabControls2D.classList.remove('d-none');
                if (container3D) container3D.classList.remove('active');
                if (map2D) {
                    map2D.style.display = 'block';
                    map.invalidateSize();
                }
            }
        };

        // Initialize plot calculation
        updatePlotCalculation();

        // Close dropdown when clicking outside
        document.addEventListener('click', function (e) {
            if (resultsContainer && !resultsContainer.contains(e.target) && e.target !== searchInput) {
                resultsContainer.classList.remove('active');
            }
        });

        function escapeHtml(str) {
            if (!str) return '';
            return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
        }

        if (window.lucide) {
            lucide.createIcons();
        }
    });
</script>

<script src="<?php echo $base_path; ?>assets/js/map3d_engine.js"></script>

<?php include '../includes/footer.php'; ?>