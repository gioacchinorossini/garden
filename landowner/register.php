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

    // Harvest sharing configuration
    $harvest_share_type = $_POST['harvest_share_type'] ?? 'percentage';
    if (!in_array($harvest_share_type, ['percentage', 'fixed_kg', 'negotiated'], true)) {
        $harvest_share_type = 'percentage';
    }
    $landowner_share_percentage = null;
    $landowner_share_kg = null;

    if ($harvest_share_type === 'percentage') {
        $landowner_share_percentage = max(0, min(100, floatval($_POST['landowner_share_percentage'] ?? 25)));
    } elseif ($harvest_share_type === 'fixed_kg') {
        $landowner_share_kg = max(0, floatval($_POST['landowner_share_kg'] ?? 0));
    }

    $seeds = isset($_POST['allowed_seeds']) ? array_map('htmlspecialchars', $_POST['allowed_seeds']) : [];
    if (empty($seeds)) {
        $seeds = ['Vegetables', 'Herbs'];
    }

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
            (owner_id, title, title_type, title_number, rod_name, epeb_type, epeb_no, title_document_path, oct_document_path, lra_receipt_path, is_lra_verified, address, latitude, longitude, area_sqm, status, description, allowed_seeds, plot_count, landowner_name, has_3d_view, polygon, harvest_share_type, landowner_share_percentage, landowner_share_kg)
            VALUES 
            (?, ?, '', '', '', '', '', ?, ?, '', 0, ?, ?, ?, ?, 'pending', ?, ?, ?, ?, ?, ?, ?, ?, ?)
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
            $plot_count,
            $landowner_name,
            $has_3d,
            $polygon,
            $harvest_share_type,
            $landowner_share_percentage,
            $landowner_share_kg
        ]);
        $new_id = (int) $pdo->lastInsertId();

        // Auto-create partition plots in MySQL plots table
        $plot_area = round($area / $plot_count, 1);
        $stmtPlot = $pdo->prepare("
            INSERT INTO `plots` (land_id, plot_number, area_sqm, status, crop, crop_icon, allowed_seeds, farmer_name)
            VALUES (?, ?, ?, 'available', ?, ?, ?, '')
        ");

        for ($i = 1; $i <= $plot_count; $i++) {
            $crop = !empty($seeds) ? $seeds[($i - 1) % count($seeds)] : 'Vegetables';
            $stmtPlot->execute([
                $new_id,
                'Plot A-' . $i,
                $plot_area,
                $crop,
                'tomato/tomato.svg',
                $seeds_json
            ]);
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
                        class="btn btn-xs bg-white bg-opacity-95 border rounded-pill px-2 py-0.5 text-xs fw-semibold text-dark shadow-sm d-none"
                        id="btnRotateLeft" onclick="rotatePlot(-15)" title="Rotate plot 15° Counter-Clockwise"
                        style="backdrop-filter: blur(10px);">
                        <i class="bi bi-arrow-counterclockwise text-success me-0.5"></i>-15°
                    </button>
                    <button type="button"
                        class="btn btn-xs bg-white bg-opacity-95 border rounded-pill px-2 py-0.5 text-xs fw-semibold text-dark shadow-sm d-none"
                        id="btnRotateRight" onclick="rotatePlot(15)" title="Rotate plot 15° Clockwise"
                        style="backdrop-filter: blur(10px);">
                        <i class="bi bi-arrow-clockwise text-success me-0.5"></i>+15°
                    </button>

                    <button type="button"
                        class="btn btn-xs btn-outline-danger bg-white bg-opacity-95 rounded-pill px-2 py-0.5 text-xs d-none shadow-sm"
                        id="btnClearDrawnPlot" onclick="clearDrawnPlot()" title="Clear drawn plot boundary"
                        style="backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); box-shadow: 0 4px 14px rgba(0,0,0,0.12);">
                        <i class="bi bi-trash3 me-1"></i>Clear
                    </button>

                    <button type="button"
                        class="btn btn-xs btn-outline-success bg-white bg-opacity-95 rounded-pill px-2 py-0.5 text-xs d-none fw-semibold shadow-sm"
                        id="btnEditPlotArea" onclick="openLandAreaModal()"
                        title="Click to change target land area in square meters"
                        style="backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); box-shadow: 0 4px 14px rgba(0,0,0,0.12);">
                        <i class="bi bi-rulers me-1"></i><span id="targetAreaBadge">250 m²</span>
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
                    style="position: absolute; top: 48px; left: 10px; z-index: 1000; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border: 1px solid rgba(0,0,0,0.14); box-shadow: 0 6px 18px rgba(0,0,0,0.15); border-radius: 50rem; padding: 4px 12px;">
                    <div class="d-flex align-items-center gap-2">
                        <span id="mapDrawingHelperText" class="text-xs fw-semibold text-dark">Click on the map to add
                            boundary corners</span>
                        <button type="button"
                            class="btn btn-xs btn-success rounded-pill px-2 py-0.5 text-xs fw-bold d-none"
                            id="btnFinishPolygon" onclick="finishCurrentPolygon()">
                            <i class="bi bi-check-circle me-1"></i>Finish
                        </button>
                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-0.5 text-xs"
                            onclick="cancelPlotDrawing()">
                            Cancel
                        </button>
                    </div>
                </div>

                <!-- Floating Partition Plots Control Overlay (Pops up after plot boundary is drawn - Bottom Left) -->
                <div class="position-absolute shadow-sm d-none" id="plotControlOverlay"
                    style="bottom: 48px; left: 10px; z-index: 1000; width: auto; min-width: 190px; max-width: 220px;">
                    <div class="card border rounded-3 bg-white bg-opacity-95 p-2 shadow-sm"
                        style="backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border-color: rgba(22, 163, 74, 0.25) !important;">
                        <div class="d-flex align-items-center justify-content-between gap-1 mb-1">
                            <span class="fw-bold text-dark d-flex align-items-center gap-1" style="font-size: 0.72rem;">
                                <i class="bi bi-grid-3x3-gap-fill text-success"></i>
                                <span>Plots</span>
                            </span>
                            <span class="badge bg-success-subtle text-success rounded-pill px-1.5 py-0.5"
                                id="plotCalcBadge" style="font-size: 0.65rem;">
                                4 plots (~62.5 m²)
                            </span>
                        </div>
                        <input type="hidden" id="plot_count" name="plot_count" value="4">
                        <input type="hidden" id="singlePlotArea" value="62.5">
                        <!-- Compact Plot Buttons instead of Dropdown -->
                        <div class="d-flex align-items-center justify-content-between gap-1 mb-1">
                            <div class="btn-group btn-group-sm w-100 p-0.5 bg-light rounded-2 border" role="group"
                                aria-label="Partition plots count">
                                <button type="button"
                                    class="btn btn-xs plot-count-btn py-0.5 px-1 rounded-1 fw-bold btn-light text-secondary border-0"
                                    data-count="1" onclick="selectPlotCount(1, this)"
                                    style="font-size: 0.72rem;">1</button>
                                <button type="button"
                                    class="btn btn-xs plot-count-btn py-0.5 px-1 rounded-1 fw-bold btn-light text-secondary border-0"
                                    data-count="2" onclick="selectPlotCount(2, this)"
                                    style="font-size: 0.72rem;">2</button>
                                <button type="button"
                                    class="btn btn-xs plot-count-btn py-0.5 px-1 rounded-1 fw-bold btn-success active text-white border-0"
                                    data-count="4" onclick="selectPlotCount(4, this)"
                                    style="font-size: 0.72rem;">4</button>
                                <button type="button"
                                    class="btn btn-xs plot-count-btn py-0.5 px-1 rounded-1 fw-bold btn-light text-secondary border-0"
                                    data-count="6" onclick="selectPlotCount(6, this)"
                                    style="font-size: 0.72rem;">6</button>
                                <button type="button"
                                    class="btn btn-xs plot-count-btn py-0.5 px-1 rounded-1 fw-bold btn-light text-secondary border-0"
                                    data-count="8" onclick="selectPlotCount(8, this)"
                                    style="font-size: 0.72rem;">8</button>
                            </div>
                        </div>
                        <div class="pt-1 border-top d-flex align-items-center justify-content-between"
                            style="font-size: 0.66rem;">
                            <span class="text-muted" id="drawnBoundarySummary">Standard Grid</span>
                            <button type="button" class="btn btn-link text-success p-0 text-decoration-none fw-semibold"
                                style="font-size: 0.68rem;" onclick="openLandAreaModal()" title="Resize plot area">
                                <i class="bi bi-rulers me-0.5"></i>Change Area
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 2D Leaflet Map Canvas (Above the Form - Expanded Size) -->
                <div id="pickerMap" style="height: 400px; min-height: 500px; background-color: #e9f2ff;"></div>

                <!-- Floating GPS Go To My Location Button -->
                <div class="position-absolute d-flex flex-column gap-2"
                    style="bottom: 46px; right: 14px; z-index: 1001;">
                    <button type="button" class="map-fab-btn" onclick="useCurrentLocation()" title="Go to My Location"
                        id="btnGoToMyLocation">
                        <i class="bi bi-crosshair text-success fs-5"></i>
                    </button>
                </div>

                <!-- 3D City & Garden Visualization Container -->
                <div id="register3DMapContainer" class="map3d-container" style="height: 40f0px; min-height: 500px;">
                </div>

                <!-- Pinned Address / Coordinates Status Bar -->
                <div class="map-picker-status-bar" id="mapStatusBar">
                    <div class="d-flex align-items-center gap-1 text-truncate">
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
                <input type="hidden" name="plot_count" id="form_plot_count" value="4">

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

                    <!-- Allowed Seed Types / Permitted Crops (col-md-6) -->
                    <div class="col-md-6">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <label class="form-label text-secondary m-0 fw-semibold text-xs">PERMITTED CROPS</label>
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
                            <span class="text-muted text-xs italic" id="emptyCropsNotice">No specific crops selected
                                (All crops permitted by default). Click "Select Crops" to restrict allowed crop
                                types.</span>
                        </div>

                        <!-- Hidden container for checked inputs submitted with form -->
                        <div id="hiddenSeedInputs"></div>
                    </div>



                    <!-- Harvest Sharing Configuration -->
                    <div class="col-12">
                        <div class="border rounded-4 p-3 p-md-4 bg-light"
                            style="border-color: var(--drive-border) !important;">
                            <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                                <div>
                                    <label class="form-label text-secondary fw-semibold text-xs mb-1">HARVEST SHARING
                                        AGREEMENT</label>
                                    <div class="text-muted text-xs">Configure how the harvest from this land will be
                                        divided between the landowner and farmer.</div>
                                </div>
                                <span class="badge bg-success-subtle text-success rounded-pill">Landowner Share</span>
                            </div>

                            <div class="row g-3 align-items-end">
                                <div class="col-md-4">
                                    <label for="harvest_share_type"
                                        class="form-label text-secondary fw-semibold text-xs mb-1">SHARING
                                        METHOD</label>
                                    <select class="form-select drive-form-control" id="harvest_share_type"
                                        name="harvest_share_type" onchange="updateHarvestShareFields()">
                                        <option value="percentage" selected>Percentage of harvest</option>
                                        <option value="fixed_kg">Fixed kilograms per harvest</option>
                                        <option value="negotiated">Negotiated / manual</option>
                                    </select>
                                </div>

                                <div class="col-md-4" id="percentageShareField">
                                    <label for="landowner_share_percentage"
                                        class="form-label text-secondary fw-semibold text-xs mb-1">LANDOWNER SHARE
                                        (%)</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control drive-form-control"
                                            id="landowner_share_percentage" name="landowner_share_percentage" min="0"
                                            max="100" step="0.01" value="25" oninput="updateHarvestSharePreview()">
                                        <span class="input-group-text">%</span>
                                    </div>
                                </div>

                                <div class="col-md-4 d-none" id="fixedKgShareField">
                                    <label for="landowner_share_kg"
                                        class="form-label text-secondary fw-semibold text-xs mb-1">LANDOWNER SHARE
                                        (KG)</label>
                                    <div class="input-group">
                                        <input type="number" class="form-control drive-form-control"
                                            id="landowner_share_kg" name="landowner_share_kg" min="0" step="0.01"
                                            value="0" oninput="updateHarvestSharePreview()">
                                        <span class="input-group-text">kg</span>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="p-3 bg-white border rounded-3 h-100"
                                        style="border-color: var(--drive-border) !important;">
                                        <div class="text-muted text-xs mb-1">SHARING PREVIEW</div>
                                        <div id="harvestSharePreview" class="fw-semibold text-dark text-sm">Landowner:
                                            25% · Farmer: 75%</div>
                                        <div class="text-muted mt-1" style="font-size: 0.7rem;">Actual kilograms will be
                                            calculated when a harvest is recorded.</div>
                                    </div>
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

<script>
    function updateHarvestShareFields() {
        const type = document.getElementById('harvest_share_type')?.value;
        const percentageField = document.getElementById('percentageShareField');
        const fixedKgField = document.getElementById('fixedKgShareField');
        const percentageInput = document.getElementById('landowner_share_percentage');
        const fixedKgInput = document.getElementById('landowner_share_kg');

        if (!percentageField || !fixedKgField) return;

        percentageField.classList.toggle('d-none', type !== 'percentage');
        fixedKgField.classList.toggle('d-none', type !== 'fixed_kg');

        if (percentageInput) percentageInput.required = type === 'percentage';
        if (fixedKgInput) fixedKgInput.required = type === 'fixed_kg';

        updateHarvestSharePreview();
    }

    function updateHarvestSharePreview() {
        const type = document.getElementById('harvest_share_type')?.value;
        const preview = document.getElementById('harvestSharePreview');
        if (!preview) return;

        if (type === 'percentage') {
            let percentage = parseFloat(document.getElementById('landowner_share_percentage')?.value || 0);
            percentage = Math.max(0, Math.min(100, percentage));
            const farmer = (100 - percentage).toFixed(2).replace(/\.00$/, '');
            const owner = percentage.toFixed(2).replace(/\.00$/, '');
            preview.textContent = `Landowner: ${owner}% · Farmer: ${farmer}%`;
        } else if (type === 'fixed_kg') {
            const kg = Math.max(0, parseFloat(document.getElementById('landowner_share_kg')?.value || 0));
            preview.textContent = `Landowner: ${kg} kg per harvest · Farmer: Remaining harvest`;
        } else {
            preview.textContent = 'Landowner: Negotiated · Farmer: Negotiated';
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        updateHarvestShareFields();
    });
</script>

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

<!-- Permitted Crops Selection Modal -->
<div class="modal fade" id="permittedCropsModal" tabindex="-1" aria-hidden="true" style="z-index: 9999;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content drive-modal-content rounded-4 overflow-hidden border-0 shadow">
            <div class="modal-header border-bottom px-4 py-3 bg-white">
                <div>
                    <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" style="font-size: 1rem;">
                        <img src="../assets/crop-icons/generic-plant/generic-plant.svg" class="w-6 h-6 object-contain"
                            alt="Crops">
                        Select Permitted Crops
                    </h5>
                    <p class="text-secondary mb-0" style="font-size: 0.75rem;">Choose the crop types gardeners are
                        allowed to cultivate on this land.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4 bg-light">
                <!-- Search & Quick Selection Actions -->
                <div class="d-flex flex-column flex-sm-row gap-3 align-items-center justify-content-between mb-4">
                    <div class="position-relative w-100 me-sm-2">
                        <i data-lucide="search" class="position-absolute text-secondary"
                            style="left: 12px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px;"></i>
                        <input type="text" id="cropSearchInput"
                            class="form-control drive-form-control ps-5 py-2 text-xs rounded-pill"
                            placeholder="Search crop types (e.g., Tomato, Lettuce, Carrot)..."
                            onkeyup="filterCropChips()">
                    </div>
                    <div class="d-flex gap-1.5 flex-shrink-0">
                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 text-xs"
                            onclick="selectAllCrops(true)">Select All</button>
                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 text-xs"
                            onclick="selectAllCrops(false)">Clear All</button>
                    </div>
                </div>

                <!-- Crop Grid Container -->
                <div class="row g-2 overflow-y-auto" id="modalCropGrid" style="max-height: 340px;">
                    <?php foreach ($seedTypes as $seed): ?>
                        <div class="col-6 col-sm-4 col-md-3 crop-grid-item"
                            data-name="<?php echo strtolower($seed['label']); ?>">
                            <div class="modal-crop-chip p-2.5 border rounded-3 bg-white d-flex align-items-center justify-content-between gap-2"
                                data-value="<?php echo $seed['value']; ?>" data-label="<?php echo $seed['label']; ?>"
                                data-icon="<?php echo $seed['icon']; ?>"
                                style="cursor: pointer; transition: all 0.15s; border-color: var(--drive-border) !important; user-select: none;"
                                onclick="toggleCropSelection('<?php echo $seed['value']; ?>', '<?php echo $seed['label']; ?>', '<?php echo $seed['icon']; ?>', this)">
                                <div class="d-flex align-items-center gap-2 text-truncate">
                                    <div
                                        class="w-8 h-8 rounded-full flex items-center justify-center bg-drive-canvas border border-drive-border flex-shrink-0">
                                        <img src="<?php echo $seed['icon']; ?>" alt="<?php echo $seed['label']; ?>"
                                            class="w-5 h-5 object-contain">
                                    </div>
                                    <span
                                        class="fw-semibold text-dark text-xs truncate"><?php echo $seed['label']; ?></span>
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

<!-- Modal to Ask for Square Meters Before Drawing -->
<div class="modal fade" id="landAreaModal" tabindex="-1" aria-labelledby="landAreaModalLabel" aria-hidden="true"
    style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="modal-content border-0 rounded-4 shadow-xl overflow-hidden">
            <div class="modal-header border-0 bg-success text-white py-3 px-4">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-circle bg-white text-success d-flex align-items-center justify-content-center flex-shrink-0"
                        style="width: 34px; height: 34px;">
                        <i class="bi bi-rulers fs-6"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold text-white mb-0" id="landAreaModalLabel">Set Land Area (m²)</h6>
                        <span class="text-white-50" style="font-size: 0.72rem;">Calibrate plot boundary size</span>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"
                    onclick="closeLandAreaModal()"></button>
            </div>
            <div class="modal-body p-4 bg-white">
                <p class="text-secondary text-xs mb-3">
                    Enter the total area of your land in square meters. Your plot drawing on the map will automatically
                    be constrained to this exact size.
                </p>

                <div class="mb-3">
                    <label for="modalAreaInput" class="form-label text-dark fw-bold text-xs mb-1">TOTAL SQUARE METERS
                        (m²)</label>
                    <div class="input-group">
                        <input type="number" step="1" min="10" max="100000"
                            class="form-control drive-form-control form-control-lg fw-bold text-success fs-4"
                            id="modalAreaInput" value="250" placeholder="e.g. 250"
                            oninput="updateModalButtonLabel(this.value)"
                            onkeydown="if(event.key==='Enter'){event.preventDefault();confirmLandAreaAndDraw();}"
                            required>
                        <span class="input-group-text bg-light border text-muted fw-bold">m²</span>
                    </div>
                </div>

                <!-- Quick Presets -->
                <div class="mb-3">
                    <span class="text-muted text-xs d-block mb-1.5 fw-semibold">Quick Presets:</span>
                    <div class="d-flex flex-wrap gap-1.5" id="presetAreaGroup">
                        <button type="button"
                            class="btn btn-xs preset-area-btn btn-outline-secondary rounded-pill px-2 py-0.5"
                            data-val="100" onclick="setPresetArea(100)">100 m²</button>
                        <button type="button"
                            class="btn btn-xs preset-area-btn btn-outline-secondary rounded-pill px-2 py-0.5"
                            data-val="150" onclick="setPresetArea(150)">150 m²</button>
                        <button type="button"
                            class="btn btn-xs preset-area-btn btn-outline-secondary rounded-pill px-2 py-0.5"
                            data-val="200" onclick="setPresetArea(200)">200 m²</button>
                        <button type="button"
                            class="btn btn-xs preset-area-btn btn-success text-white active rounded-pill px-2 py-0.5"
                            data-val="250" onclick="setPresetArea(250)">250 m²</button>
                        <button type="button"
                            class="btn btn-xs preset-area-btn btn-outline-secondary rounded-pill px-2 py-0.5"
                            data-val="500" onclick="setPresetArea(500)">500 m²</button>
                        <button type="button"
                            class="btn btn-xs preset-area-btn btn-outline-secondary rounded-pill px-2 py-0.5"
                            data-val="1000" onclick="setPresetArea(1000)">1,000 m²</button>
                    </div>
                </div>

                <div class="d-grid gap-2 pt-2 border-top">
                    <button type="button"
                        class="btn btn-sm btn-success rounded-pill py-2 fw-semibold d-flex align-items-center justify-content-center gap-2 shadow-sm text-xs"
                        onclick="confirmLandAreaAndDraw()">
                        <i class="bi bi-bounding-box"></i>
                        <span>Set Area & Place Plot (<span id="modalBtnAreaText">250 m²</span>)</span>
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

    function filterCropChips() {
        const query = document.getElementById('cropSearchInput').value.toLowerCase().trim();
        document.querySelectorAll('.crop-grid-item').forEach(item => {
            const name = item.getAttribute('data-name');
            if (!query || name.includes(query)) {
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
            summaryBox.innerHTML = `<span class="text-muted text-xs italic" id="emptyCropsNotice">No specific crops selected (All crops permitted by default). Click "Select Crops" to restrict allowed crop types.</span>`;
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

        L.control.layers({
            "Map (OSM)": osmLayer,
            "Satellite (High-Res)": satelliteLayer
        }, null, { position: 'bottomright' }).addTo(map);

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

        function updatePresetButtons(val) {
            const num = parseFloat(val);
            document.querySelectorAll('#presetAreaGroup .preset-area-btn').forEach(btn => {
                const btnVal = parseFloat(btn.getAttribute('data-val'));
                if (btnVal === num) {
                    btn.classList.add('btn-success', 'text-white', 'active');
                    btn.classList.remove('btn-outline-secondary');
                } else {
                    btn.classList.remove('btn-success', 'text-white', 'active');
                    btn.classList.add('btn-outline-secondary');
                }
            });
        }

        window.openLandAreaModal = function (preferredMode = null) {
            const currentVal = targetSquareMeters || parseFloat(document.getElementById('area')?.value) || 250;
            const input = document.getElementById('modalAreaInput');
            if (input) {
                input.value = currentVal;
            }
            updatePresetButtons(currentVal);
            updateModalButtonLabel(currentVal);

            const modal = getLandAreaModal();
            if (modal) {
                if (preferredMode) {
                    modal._preferredMode = preferredMode;
                }
                modal.show();
            }
        };

        window.setPresetArea = function (val) {
            const input = document.getElementById('modalAreaInput');
            if (input) {
                input.value = val;
            }
            updatePresetButtons(val);
            updateModalButtonLabel(val);
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

            const modal = getLandAreaModal();
            if (modal) modal.hide();

            // Automatically put the drawing immediately with fixed square meters!
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
                helperText.innerHTML = `<i class="bi bi-arrows-move text-success me-1"></i><strong>${targetSquareMeters} m² Fixed</strong> &mdash; Drag plot or corners to position &bull; Drag top handle or use ↺ ↻ to rotate`;
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

                // Bilinear mapping function on the irregular 4-point quadrilateral
                const interpPoint = (u, v) => {
                    const lat = (1 - v) * ((1 - u) * quadPts[0].lat + u * quadPts[1].lat) +
                        v * ((1 - u) * quadPts[3].lat + u * quadPts[2].lat);
                    const lng = (1 - v) * ((1 - u) * quadPts[0].lng + u * quadPts[1].lng) +
                        v * ((1 - u) * quadPts[3].lng + u * quadPts[2].lng);
                    return [lat, lng];
                };

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
                const cropName = permittedCrops.length > 0 ? permittedCrops[(index - 1) % permittedCrops.length] : 'Available for Gardeners';

                plotPoly.bindTooltip(
                    `<div style="font-family:'Outfit',sans-serif;font-size:11px;line-height:1.3;padding:1px;">
                        <strong class="text-success"><i class="bi bi-grid-3x3-gap-fill me-1"></i>${plotName}</strong><br>
                        <span class="text-dark font-monospace">${singlePlotArea} m²</span><br>
                        <span class="badge bg-success-subtle text-success mt-1" style="font-size:9px;">${cropName}</span>
                    </div>`,
                    { permanent: false, direction: 'center' }
                );

                const centerLat = points.reduce((sum, p) => sum + (Array.isArray(p) ? p[0] : p.lat), 0) / points.length;
                const centerLng = points.reduce((sum, p) => sum + (Array.isArray(p) ? p[1] : p.lng), 0) / points.length;

                const centerLabel = L.marker([centerLat, centerLng], {
                    icon: L.divIcon({
                        className: 'partition-plot-label',
                        html: `<div style="background:rgba(22,163,74,0.85);color:#fff;font-size:9.5px;font-weight:700;padding:1px 6px;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,0.25);pointer-events:none;white-space:nowrap;transform:translate(-50%,-50%);">${plotName}</div>`,
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

        // ─── Partition Plots Setup & Calculation ───
        window.selectPlotCount = function (count, btn) {
            const hiddenInput = document.getElementById('plot_count');
            if (hiddenInput) hiddenInput.value = count;
            const formPlotCount = document.getElementById('form_plot_count');
            if (formPlotCount) formPlotCount.value = count;

            document.querySelectorAll('.plot-count-btn').forEach(b => {
                b.classList.remove('btn-success', 'active', 'text-white');
                b.classList.add('btn-light', 'text-secondary');
            });
            if (btn) {
                btn.classList.remove('btn-light', 'text-secondary');
                btn.classList.add('btn-success', 'active', 'text-white');
            }
            updatePlotCalculation();
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
            const singleAreaInput = document.getElementById('singlePlotArea');
            const badge = document.getElementById('plotCalcBadge');
            const previewGrid = document.getElementById('plotGridPreviewBadges');

            const formPlotCount = document.getElementById('form_plot_count');
            if (formPlotCount) formPlotCount.value = count;

            // Sync button active state with count
            const activeBtn = document.querySelector(`.plot-count-btn[data-count="${count}"]`);
            if (activeBtn) {
                document.querySelectorAll('.plot-count-btn').forEach(b => {
                    b.classList.remove('btn-success', 'active', 'text-white');
                    b.classList.add('btn-light', 'text-secondary');
                });
                activeBtn.classList.remove('btn-light', 'text-secondary');
                activeBtn.classList.add('btn-success', 'active', 'text-white');
            }

            const singleArea = totalArea > 0 ? (totalArea / count).toFixed(1) : '—';
            if (singleAreaInput) singleAreaInput.value = singleArea;
            if (badge) {
                badge.textContent = totalArea > 0
                    ? `${count} plot${count > 1 ? 's' : ''} (~${singleArea} m²)`
                    : `${count} plot${count > 1 ? 's' : ''}`;
            }

            if (previewGrid) {
                let badgesHtml = '';
                for (let i = 1; i <= count; i++) {
                    badgesHtml += `
                        <span class="badge bg-white border text-dark rounded-pill px-2 py-1 text-xs d-inline-flex align-items-center gap-1 shadow-xs">
                            <i class="bi bi-grid-3x3-gap-fill text-success" style="font-size:10px;"></i>
                            <span>Plot A-${i}</span>
                            <span class="text-muted" style="font-size:9px;">(${singleArea} m²)</span>
                        </span>
                    `;
                }
                previewGrid.innerHTML = badgesHtml;
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